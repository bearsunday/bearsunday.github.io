# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is the documentation website for BEAR.Sunday, a resource-oriented PHP framework. The site is built with Jekyll and hosted on GitHub Pages at https://bearsunday.github.io/.

## Architecture

- **Jekyll Site**: Static site generator for documentation
- **Bilingual Documentation**: English (`manuals/1.0/en/`) and Japanese (`manuals/1.0/ja/`) versions
- **LLMs.txt Compliance**: Special handling for AI/LLM accessibility via custom scripts
- **GitHub Actions**: Automated deployment to GitHub Pages

## Development Commands

### Local Development
```bash
# Install dependencies (requires Ruby 3.2.3)
gem install jekyll bundler
bundle install

# Serve locally with live reload
./bin/serve_local.sh

# Serve with Docker
./bin/serve_docker.sh
```

### Build Process
```bash
# Build site
bundle exec jekyll build
```

## Key Scripts

- `bin/serve_local.sh`: Local development server with Jekyll watch mode
- `bin/serve_docker.sh`: Docker-based development
- `bin/copy_markdown_files.sh`: Copies markdown files to `_site` for llms.txt compliance (removes Jekyll frontmatter)

## File Structure

- `manuals/1.0/{en,ja}/`: Main documentation in markdown format
- `_includes/manuals/1.0/{en,ja}/contents.html`: Navigation structure that determines page order
- `_layouts/`: Jekyll templates for different page types
- `_config.yml`: Jekyll configuration with llms.txt specific settings
- `_site/`: Generated static site (includes both HTML and cleaned markdown files)

## Styling Conventions

### Beta Label

For features in beta status, use this inline style:

```html
# Title <sup style="font-size:0.5em; color:#666; font-weight:normal;">Beta</sup>
```

Example: `manuals/1.0/ja/security.md`

## Documentation Management

The site uses a sophisticated system for managing documentation:

1. Individual markdown files in `manuals/1.0/{en,ja}/`
2. Navigation order determined by `_includes/manuals/1.0/{language}/contents.html`
3. LLMs.txt compliance through frontmatter-stripped copies in `_site/manuals/`

## Jekyll Configuration

- Uses Kramdown markdown processor with Rouge syntax highlighting
- Custom plugin (`_plugins/copy_markdown.rb`) ensures markdown files are copied to `_site/`
- Special include/keep_files configuration for llms.txt standard compliance
- Navigation structure embedded in HTML files rather than configuration

## Deployment

- **GitHub Actions**: `.github/workflows/jekyll.yml` handles automatic deployment
- **Ruby Version**: 3.2.2 in CI, 3.2.3 locally (Jekyll compatibility requirement)
