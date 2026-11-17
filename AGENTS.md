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
| `./bin/serve_local.sh` | Local dev: generates `llms-full.txt`, builds once, generates the Pagefind index, then serves with watch mode on port 4001 |
| `./bin/serve_docker.sh` | Serve via Docker Compose on port 4001 |
| `bundle exec jekyll build` | Build the static site into `_site/` |
| `php bin/gen_llms.php` | Expand the hand-maintained `llms.txt` index into `llms-full.txt` (build artifact, gitignored) |
| `./bin/copy_markdown_files.sh` | Strip frontmatter and copy raw markdown into `_site/` |
| `node scripts/validate-frontmatter.js` | Validate frontmatter on all manual pages |

Requires **Ruby 3.2.x** (Jekyll compatibility; 3.2.2 in CI, 3.2.3 locally), PHP 8.x for `gen_llms.php`, and Node.js for the frontmatter validator. The Pagefind index is generated with `npx pagefind --site _site` after the Jekyll build.

## Coding Style & Naming Conventions

- **Markdown files**: kebab-case filenames (e.g., `content-negotiation.md`). Each file starts with YAML frontmatter delimited by `---`.
- **Permalink pattern**: `/manuals/1.0/{language}/{basename}.html` (e.g., `/manuals/1.0/en/resource.html`). The frontmatter `permalink` is the source of truth; a few pages deviate (`database.media.md` → `database_media.html`, `server.md` → `swoole.html`). Link to the permalink, never derive it from the filename.
- **Navigation**: page order is defined in `_includes/manuals/1.0/{language}/contents.html`, not in config. Not every page is in the nav or in `llms.txt` — both are curated. When adding a new page, add it to `contents.html` and `llms.txt` deliberately.
- **Bilingual parity**: every `manuals/1.0/en/*.md` has a counterpart with the same filename under `ja/`, and both `contents.html` files carry the same set of links. Change both languages in the same PR, or say in the PR that the translation is deferred.
- **Beta features**: use `<sup style="font-size:0.5em; color:#666; font-weight:normal;">Beta</sup>` in headings.

## Testing Guidelines

No automated test suite, and no CI runs on pull requests (`pages.yml` triggers on push to `master` only). Before committing:

1. Run `node scripts/validate-frontmatter.js` to ensure all manual pages have valid frontmatter.
2. Run `./bin/serve_local.sh` and spot-check the rendered pages locally.
3. Verify `llms-full.txt` is not committed — it is a build artifact. `llms.txt` itself is a hand-maintained index and is committed.

## Commit & Pull Request Guidelines

- **Commit messages**: use imperative mood, short summary line. Conventional commit prefixes (`fix:`, `docs:`) are used occasionally but not strictly enforced. Example: `Add Input DTO guidance to resource parameter docs`, `Fix PDO pool module references`.
- **Merge commits**: standard GitHub merge style — `Merge pull request #XXX from {branch}`.
- **Pull requests**: target the `master` branch. Link related issues. For documentation changes, include a brief description of what was changed and why.
- **Deployment**: CI runs on push to `master` via `.github/workflows/pages.yml`. Do not create a second Pages workflow — they share a concurrency group (`pages`) and will race.

## Architecture Notes

- **Jekyll**: Kramdown with Rouge syntax highlighting. `_plugins/copy_markdown.rb` copies frontmatter-stripped markdown into `_site/` for llms.txt compliance, driven by the `include` / `keep_files` settings in `_config.yml`.
- **Search**: Pagefind, indexed in CI from `_site/`.
- **CI build order**: gen `llms-full.txt` → Jekyll build → Pagefind index → build Learn site (static snapshot via wget) → upload Pages artifact.
- **Deploy source**: production deploys from `bearsunday/bearsunday.github.io` (`upstream`) `master`. A personal fork (often the local `origin`) does not trigger a deploy — push there and nothing redeploys.

## Learn Site (`/learn/`)

The marketing site lives in a separate repo (`bearsunday/site-bear-sunday`, a Vite + RSC app; fully static, no `"use client"`) and is bundled under `/learn/` by the same `pages.yml` workflow. The canonical copy of that workflow is `site-bear-sunday/deploy/bearsunday.github.io/pages.yml` — keep both in sync when editing the Learn build step. The build crawls the running app with `wget` into a static snapshot, copies `public/` assets, then `sed`-rewrites absolute root paths to resolve under `/learn/`.

### Pitfall: `wget -k` mangles inline-style `url()`

**Symptom**: an asset 200s at `/learn/<file>` but does not render. The hero `bear-logo.png` background was invisible while `/learn/bear-logo.png` returned 200.

**Cause**: the source uses an inline style — `style={{ backgroundImage: "url('/bear-logo.png')" }}`. React serializes the quotes as HTML entities, so SSR emits `url(&#x27;/bear-logo.png&#x27;)`. `wget -k` (`--convert-links`) cannot parse that as a CSS `url()` and rewrites the whole token to the crawl base, producing `url(http://localhost:4399/&)` with the filename dropped. The later `sed s#/bear-logo.png#…#` then never matches the rendered `<div>`; the path only survives inside the JSON RSC payload. This is a snapshot-time corruption, not a hydration or cache issue.

**Guardrails**:

- `pages.yml` carries `s#url(http://localhost:4399/[^)]*)#url('bear-logo.png')#g` to restore that one mangled token. Do not remove it.
- Any new inline-style `url(...)` asset in the Learn app hits the same bug. Fix it at the source (unquoted `url(/path)`, which `wget -k` relativizes cleanly) or extend the `sed`, and confirm in a real browser that the asset paints — not just that it returns 200.
