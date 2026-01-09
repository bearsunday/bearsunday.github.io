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

## Demo

- [HTML](https://bearsunday.github.io/BEAR.ApiDoc/)
- [OpenAPI](https://bearsunday.github.io/BEAR.ApiDoc/openapi/)

## Installation

```bash
composer require bear/api-doc --dev
cp vendor/bear/api-doc/apidoc.xml.dist apidoc.xml
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
      format: 'html,openapi,alps'
      alps-profile: 'alps.json'
```

Enable GitHub Pages: Settings → Pages → Source: "GitHub Actions"

### Inputs

| Input | Default | Description |
|-------|---------|-------------|
| `php-version` | `'8.2'` | PHP version |
| `format` | `'html,openapi'` | Comma-separated: html, md, openapi, alps |
| `alps-profile` | `''` | ALPS profile path (required for alps format) |
| `docs-path` | `'docs/api'` | Output directory |
| `publish-to` | `'github-pages'` | `github-pages` or `artifact-only` |

### Output Structure

```
docs/
├── index.html          # API documentation
├── schemas/
│   ├── index.html      # Schema list
│   └── *.json          # JSON Schema
├── openapi/
│   ├── openapi.json    # OpenAPI spec
│   └── index.html      # Redocly HTML
└── alps/
    ├── alps.json       # ALPS profile
    └── index.html      # ASD state diagram
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
| `format` | Yes | `html`, `md`, `openapi` |
| `title` | | API title |
| `alps` | | ALPS profile path |

## Profile

[ALPS](http://alps.io/) profile defines your API vocabulary. Centralized definitions prevent inconsistencies and aid shared understanding.

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

- [ALPS](https://www.app-state-diagram.com/manuals/1.0/en/)
