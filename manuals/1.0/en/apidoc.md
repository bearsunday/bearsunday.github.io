---
layout: docs-en
title: API Doc
category: Manual
permalink: /manuals/1.0/en/apidoc.html
---

# API Doc

Your application is the documentation.

- **ApiDoc HTML**: Developer documentation
- **OpenAPI 3.1**: Tool chain integration
- **JSON Schema**: Information model
- **ALPS**: Vocabulary semantics for AI understanding
- **llms.txt**: AI-readable application overview

## Demo

- [HTML](https://bearsunday.github.io/BEAR.ApiDoc/)
- [OpenAPI](https://bearsunday.github.io/BEAR.ApiDoc/openapi/)

## Installation

```bash
composer require bear/api-doc --dev
./vendor/bin/apidoc init
```

The `init` command generates `apidoc.xml` from your `composer.json`. Edit it to customize.

```xml
<apidoc>
    <appName>MyVendor\MyProject</appName>  <!-- Application namespace -->
    <scheme>app</scheme>                    <!-- app or page -->
    <docDir>docs/api</docDir>
    <format>html</format>                   <!-- html, html,openapi, etc. -->
</apidoc>
```

The `format` accepts comma-separated values: `html`, `md`, `openapi`, `llms`.

## Usage

Generate documentation from the command line.

```bash
./vendor/bin/apidoc
```

### OpenAPI HTML Generation

When `openapi` format is specified, `openapi.json` is generated. Use Redocly CLI to convert it to HTML.

```bash
npm install -g @redocly/cli
redocly build-docs docs/api/openapi.json -o docs/api/openapi.html
```

### llms.txt

The `llms` format generates `llms.txt` following the [llms.txt specification](https://llmstxt.org/). Unlike typical API references that list endpoints, this provides a comprehensive application document—including infrastructure boundary interfaces (Query/Command), their query language (SQL), and domain ontology (Entities). It captures the full information architecture, enabling AI agents to understand not just what operations exist, but how the application is structured.

### Composer Scripts

Add scripts to `composer.json` for convenience.

```json
{
    "scripts": {
        "docs": "./vendor/bin/apidoc"
    },
    "scripts-descriptions": {
        "docs": "Generate API documentation"
    }
}
```

```bash
composer docs
```

## GitHub Actions

Push to main branch to automatically generate and publish API documentation to GitHub Pages. The reusable workflow handles HTML generation, OpenAPI conversion with Redocly, and ALPS state diagram creation.

```yaml
name: API Docs
on:
  push:
    branches: [main]

jobs:
  docs:
    uses: bearsunday/BEAR.ApiDoc/.github/workflows/apidoc.yml@v1
    with:
      format: 'html,openapi,llms,alps'
      alps-profile: 'alps.json'
```

Enable GitHub Pages: Settings → Pages → Source: "GitHub Actions"

### Inputs

| Input | Default | Description |
|-------|---------|-------------|
| `php-version` | `'8.2'` | PHP version |
| `format` | `'html,openapi,llms'` | Comma-separated: html, md, openapi, alps, llms |
| `alps-profile` | `''` | ALPS profile path (required for alps format) |
| `docs-path` | `'docs/api'` | Output directory |
| `publish-to` | `'github-pages'` | `github-pages` or `artifact-only` |

### Output Structure

```text
docs/
├── index.html          # API documentation
├── llms.txt            # AI-readable overview
├── openapi.json        # OpenAPI spec
└── schemas/
    ├── index.html      # Schema list
    └── *.json          # JSON Schema
```

## Configuration

```xml
<?xml version="1.0" encoding="UTF-8"?>
<apidoc
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="https://bearsunday.github.io/BEAR.ApiDoc/apidoc.xsd">
    <appName>MyVendor\MyProject</appName>
    <scheme>app</scheme>
    <docDir>docs</docDir>
    <format>html</format>
    <alps>alps.json</alps>
</apidoc>
```

| Option | Required | Description |
|--------|----------|-------------|
| `appName` | Yes | Application namespace |
| `scheme` | Yes | `app` or `page` |
| `docDir` | Yes | Output directory |
| `format` | Yes | `html`, `md`, `openapi`, `llms` |
| `title` | | API title |
| `alps` | | ALPS profile path |

## Profile

ALPS profile defines your API vocabulary. Centralized definitions prevent inconsistencies and aid shared understanding.

```json
{
    "$schema": "https://alps-io.github.io/schemas/alps.json",
    "alps": {
        "descriptor": [
            {"id": "firstName", "title": "The person's first name."},
            {"id": "familyName", "def": "https://schema.org/familyName"}
        ]
    }
}
```

## Application as Documentation

Code is the single source of truth. Documentation generated from your application never diverges from the implementation. JSON Schema publishes your information model—not just a list of endpoints—enabling client-side validation and form generation. ALPS defines vocabulary semantics, allowing AI agents to understand not just the structure, but the meaning of your API.

## Reference

- [BEAR.ApiDoc](https://github.com/bearsunday/BEAR.ApiDoc) - API documentation generator
- [ALPS](https://www.app-state-diagram.com/manuals/1.0/en/) - Application-Level Profile Semantics
- [JSON Schema](https://json-schema.org/) - Data validation and documentation
- [Redocly CLI](https://redocly.com/docs/cli/installation/) - OpenAPI to HTML conversion
