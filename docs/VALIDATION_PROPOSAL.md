# Documentation Validation Proposal

## Overview
This document proposes automated validation for the BEAR.Sunday documentation to catch common issues early in the development process.

## Current Issues Identified
- YAML frontmatter formatting errors
- PHP syntax errors in code examples
- Typos in method names and variable names
- Duplicate content sections
- Inconsistent formatting

## Proposed Validation Tools

### 1. Markdown Linting
**Tool**: `markdownlint-cli`
**Purpose**: Validate markdown syntax and formatting

```yaml
# .markdownlint.json
{
  "MD013": false,  # Line length - disabled for code examples
  "MD033": false,  # HTML allowed for Jekyll
  "MD041": false   # First line doesn't need to be H1
}
```

### 2. YAML Frontmatter Validation
**Tool**: Custom script using `js-yaml`
**Purpose**: Ensure proper YAML frontmatter formatting

```javascript
// scripts/validate-frontmatter.js
const fs = require('fs');
const yaml = require('js-yaml');
const glob = require('glob');

function validateFrontmatter(filePath) {
  const content = fs.readFileSync(filePath, 'utf8');
  const frontmatterMatch = content.match(/^---\n([\s\S]*?)\n---/);
  
  if (!frontmatterMatch) {
    throw new Error(`Missing frontmatter in ${filePath}`);
  }
  
  try {
    yaml.load(frontmatterMatch[1]);
  } catch (e) {
    throw new Error(`Invalid YAML in ${filePath}: ${e.message}`);
  }
}
```

### 3. PHP Code Example Validation
**Tool**: `php -l` (lint) for syntax checking
**Purpose**: Validate PHP code blocks in documentation

```bash
# scripts/validate-php-examples.sh
#!/bin/bash
find manuals/ -name "*.md" -exec grep -l "```php" {} \; | while read file; do
  echo "Checking PHP examples in $file"
  # Extract and validate PHP code blocks
  sed -n '/```php/,/```/p' "$file" | grep -v '```' | php -l
done
```

### 4. Spell Checking
**Tool**: `cspell` with custom dictionary
**Purpose**: Catch typos while allowing technical terms

```json
// .cspell.json
{
  "version": "0.2",
  "language": "en",
  "words": [
    "BEAR",
    "Sunday",
    "ResourceObject",
    "onGet",
    "onPost",
    "namespace",
    "symfony",
    "twig"
  ],
  "ignorePaths": [
    "_site/**",
    "node_modules/**"
  ]
}
```

## Implementation Plan

### Phase 1: GitHub Actions Integration
Add validation to the existing Jekyll workflow:

```yaml
# Addition to .github/workflows/jekyll.yml
- name: Validate Documentation
  run: |
    # Install validation tools
    npm install -g markdownlint-cli cspell js-yaml
    
    # Run validations
    markdownlint manuals/
    cspell "manuals/**/*.md"
    node scripts/validate-frontmatter.js
    bash scripts/validate-php-examples.sh
```

### Phase 2: Pre-commit Hooks
Add local validation using `pre-commit`:

```yaml
# .pre-commit-config.yaml
repos:
  - repo: https://github.com/igorshubovych/markdownlint-cli
    rev: v0.37.0
    hooks:
      - id: markdownlint
        args: ['--config', '.markdownlint.json']
  
  - repo: local
    hooks:
      - id: validate-php-examples
        name: Validate PHP Examples
        entry: scripts/validate-php-examples.sh
        language: script
        files: '\.md$'
```

### Phase 3: VS Code Integration
Add workspace settings for immediate feedback:

```json
// .vscode/settings.json
{
  "markdownlint.config": {
    "MD013": false,
    "MD033": false,
    "MD041": false
  },
  "cSpell.words": [
    "BEAR",
    "Sunday",
    "ResourceObject"
  ]
}
```

## Benefits
1. **Early Detection**: Catch issues before they reach production
2. **Consistency**: Maintain uniform formatting across all documentation
3. **Quality**: Ensure code examples are syntactically correct
4. **Automation**: Reduce manual review overhead

## Rollout Strategy
1. Start with GitHub Actions integration (non-blocking)
2. Monitor for false positives and adjust rules
3. Enable blocking validation once rules are stable
4. Add pre-commit hooks for local development

## Maintenance
- Review and update validation rules quarterly
- Add new technical terms to spell-check dictionary as needed
- Monitor validation performance and optimize as necessary