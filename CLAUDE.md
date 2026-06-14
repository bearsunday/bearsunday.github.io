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

# Build with custom scripts (production)
ruby bin/merge_md_files.rb
bundle exec jekyll build
```

## Key Scripts

- `bin/serve_local.sh`: Local development server with Jekyll watch mode
- `bin/serve_docker.sh`: Docker-based development
- `bin/merge_md_files.rb`: Generates combined documentation files (`1page.md`) from individual markdown files
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
3. Combined documentation generated via `merge_md_files.rb`
4. LLMs.txt compliance through frontmatter-stripped copies in `_site/manuals/`

## Jekyll Configuration

- Uses Kramdown markdown processor with Rouge syntax highlighting
- Custom plugin (`_plugins/copy_markdown.rb`) ensures markdown files are copied to `_site/`
- Special include/keep_files configuration for llms.txt standard compliance
- Navigation structure embedded in HTML files rather than configuration

## Deployment

- **GitHub Actions**: `.github/workflows/pages.yml` handles automatic deployment (single Pages workflow; do not add a second Pages-deploy workflow — both would race on the `pages` concurrency group)
- **Ruby Version**: 3.2.2 in CI, 3.2.3 locally (Jekyll compatibility requirement)
- **Custom Build Steps**: Runs `merge_md_files.rb` + `gen_llms.php` before Jekyll build in CI
- **Production source**: the live site deploys from `bearsunday/bearsunday.github.io` (`upstream`) `master`. A personal fork (e.g. `koriym/…`, often the local `origin`) does NOT drive the production deploy — push there and nothing redeploys.

## Learn Site (`/learn/`)

The marketing "Learn" site lives in a separate repo (`bearsunday/site-bear-sunday`, a Vite + RSC app; fully static, no `"use client"`) and is bundled under `/learn/` by the same `pages.yml` workflow. The canonical copy of that workflow is `site-bear-sunday/deploy/bearsunday.github.io/pages.yml` — **keep both in sync** when editing the Learn build step. The build crawls the running app with `wget` into a static snapshot, copies `public/` assets, then `sed`-rewrites absolute root paths to resolve under `/learn/`.

### Pitfall: `wget -k` mangles inline-style `url()` (recurrence prevention)

**Symptom:** an asset 200s at `/learn/<file>` but does not render (e.g. the hero `bear-logo.png` background was invisible while `/learn/bear-logo.png` returned 200).

**Cause:** the source uses an inline style — `style={{ backgroundImage: "url('/bear-logo.png')" }}`. React serializes the quotes as HTML entities, so SSR emits `url(&#x27;/bear-logo.png&#x27;)`. `wget -k` (`--convert-links`) cannot parse that as a CSS `url()` and rewrites the **whole token** to the crawl base, producing `url(http://localhost:4399/&)` — the filename is dropped. The later `sed s#/bear-logo.png#…#` then never matches the rendered `<div>` (the path only survives untouched inside the JSON RSC payload). The result is an unreachable `localhost` URL, so the image never paints. This is a snapshot-time corruption, **not** a hydration/cache issue.

**Guardrails:**
- The `pages.yml` `sed` includes `s#url(http://localhost:4399/[^)]*)#url('bear-logo.png')#g` to restore that one mangled token. Do **not** remove it.
- Any **new** inline-style `url(...)` asset in the Learn app will hit the same bug. Prefer fixing it at the source (unquoted `url(/path)`, which `wget -k` relativizes cleanly) or extend the `sed`, and verify in a real browser (headless Chrome screenshot) that the asset actually paints — not just that it returns 200.
