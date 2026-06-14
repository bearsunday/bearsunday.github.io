# Repository Guidelines

## Project Structure & Module Organization

This is the documentation website for **BEAR.Sunday**, a resource-oriented PHP framework. The site is built with **Jekyll 4.4** and hosted on GitHub Pages.

```
.
├── _config.yml              # Jekyll configuration
├── _layouts/                # Page templates (docs-en.html, docs-ja.html, index.html)
├── _includes/manuals/       # Navigation (contents.html per language) and shared partials
├── _plugins/                # Custom Jekyll plugin (copy_markdown.rb)
├── manuals/1.0/
│   ├── en/                  # English documentation (markdown)
│   └── ja/                  # Japanese documentation (markdown)
├── bin/                     # Build and serve scripts
├── scripts/                 # Validation tools
├── css/, js/, images/       # Static assets
├── Dockerfile & docker-compose.yml
└── .github/workflows/       # CI/CD (pages.yml)
```

Each manual page requires Jekyll frontmatter with `layout`, `title`, `category`, and `permalink`.

## Build, Test, and Development Commands

| Command | Description |
|---|---|
| `./bin/serve_local.sh` | Full local dev: merges docs, generates LLMs.txt, builds site, then serves with watch mode on port 4001 |
| `./bin/serve_docker.sh` | Serve via Docker Compose on port 4001 |
| `bundle exec jekyll build` | Build the static site into `_site/` |
| `ruby bin/merge_md_files.rb` | Generate combined `1page.md` per language from navigation order |
| `php bin/gen_llms.php` | Generate `llms.txt` / `llms-full.txt` for AI accessibility |
| `./bin/copy_markdown_files.sh` | Strip frontmatter and copy raw markdown into `_site/` |
| `node scripts/validate-frontmatter.js` | Validate frontmatter on all manual pages |

Requires **Ruby 3.2.x** (Jekyll compatibility), PHP 8.x for `gen_llms.php`, and Node.js for the frontmatter validator.

## Coding Style & Naming Conventions

- **Markdown files**: kebab-case filenames (e.g., `content-negotiation.md`). Each file starts with YAML frontmatter delimited by `---`.
- **Permalink pattern**: `/manuals/1.0/{language}/{basename}.html` (e.g., `/manuals/1.0/en/resource.html`).
- **Navigation**: page order is defined in `_includes/manuals/1.0/{language}/contents.html`, not in config. When adding a new page, add a link entry to the appropriate `contents.html`.
- **Beta features**: use `<sup style="font-size:0.5em; color:#666; font-weight:normal;">Beta</sup>` in headings.

## Testing Guidelines

No automated test suite for the site itself. Before committing:

1. Run `node scripts/validate-frontmatter.js` to ensure all manual pages have valid frontmatter.
2. Run `./bin/serve_local.sh` and spot-check the rendered pages locally.
3. Verify generated files (`1page.md`, LLMs.txt) are not committed — they are build artifacts.

## Commit & Pull Request Guidelines

- **Commit messages**: use imperative mood, short summary line. Conventional commit prefixes (`fix:`, `docs:`) are used occasionally but not strictly enforced. Example: `Add Input DTO guidance to resource parameter docs`, `Fix PDO pool module references`.
- **Merge commits**: standard GitHub merge style — `Merge pull request #XXX from {branch}`.
- **Pull requests**: target the `master` branch. Link related issues. For documentation changes, include a brief description of what was changed and why.
- **Deployment**: CI runs on push to `master` via `.github/workflows/pages.yml`. Do not create a second Pages workflow — they share a concurrency group (`pages`) and will race.

## Architecture Notes

- **/learn/ path**: the marketing site is built from a separate repo (`bearsunday/site-bear-sunday`) and bundled under `/learn/`. The canonical CI workflow lives in that repo at `deploy/bearsunday.github.io/pages.yml` — keep both copies in sync.
- **CI build order**: merge docs → gen LLMs.txt → Jekyll build → build Learn site (static snapshot via wget) → upload Pages artifact.
- **Deploy source**: production deploys from `bearsunday/bearsunday.github.io` (`upstream`) `master`. Personal forks do not trigger deploys.
