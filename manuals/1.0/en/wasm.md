---
layout: docs-en
title: Wasm
category: Manual
permalink: /manuals/1.0/en/wasm.html
---

# Wasm

Run a [Phar](phar.html) application in the browser. No server, no PHP runtime installed — a single `app.phar` boots inside WebAssembly, served by a service worker.

```text
browser ──fetch──> service worker ──> php-cgi-wasm ──> app.phar (BEAR.Sunday)
                    (wasm/sw.js)                        └─ pdo_sqlite
```

Demo: [koriym/wasm-todo](https://github.com/koriym/wasm-todo) / [live page](https://koriym.github.io/wasm-todo/)

## What it does

- A compiled `app.phar` boots in the browser and serves HTTP.
- Resources render HTML; `_links` become `<a>` and `<form>`.
- State persists in SQLite (`pdo_sqlite`), backed by the browser's IndexedDB.

The point is not the todo app. It is that a PHP developer can ship a working web app as one file, with no JavaScript to write and no server to run.

## Requirements

- BEAR.Package 1.24+ (uses `ReadOnlyAppModule`, as [Phar](phar.html) does)
- PHP 8.5 (to build the phar)
- Node.js (to bundle the service worker)

## Build

```bash
composer install
composer compile          # bin/compile.php produces app.phar
```

`bin/compile.php` compiles for the `prod-html-app` context and packs `app.phar`. The `HtmlModule` binds `RenderInterface` to `HtmlRenderer` to return HTML.

## Run

```bash
cd wasm
npm install
npm run build             # bundles sw.js and collects assets into dist/
npx serve dist            # any static server
```

`index.html` registers the service worker, which mounts `app.phar` into the wasm virtual filesystem as `/index.php` and routes every request through PHP.

## How it works

### Service worker

`wasm/sw.js` starts `PhpCgiWorker` (php-cgi-wasm). `handleFetchEvent` passes requests to PHP and returns the response. No Node server is needed.

```javascript
import { PhpCgiWebBase } from 'php-cgi-wasm/PhpCgiWebBase.mjs';
import Php85CgiWorker from 'php-cgi-wasm/php8.5-cgi-worker.mjs';
import phar from 'php-wasm-phar';
import sqlite from 'php-wasm-sqlite';

const php = new PhpCgiWebBase(
    Promise.resolve({ default: Php85CgiWorker }),
    { version: '8.5', sharedLibs: [phar, sqlite], env: { CONTEXT: 'prod-html-app' } }
);

self.addEventListener('fetch', event => php.handleFetchEvent(event));
```

### Subpath

GitHub Pages serves under a subpath such as `https://koriym.github.io/wasm-todo/`. The worker derives its `basePath` from its own URL, and the entry point strips `BASE_PATH` before routing. Resources return relative links, so links stay correct under any subpath.

### Persistence

`TodoRepository` writes to `/persist/todo.db`. `/persist` is the wasm filesystem mount synced to IndexedDB, so state survives reloads.

## Constraints

- **MySQL does not run.** Browser wasm has no raw TCP sockets, so `mysqli`/`pdo_mysql` are unavailable. SQLite, PGlite, and Cloudflare D1 are the options.
- **Packing is native-only.** `Compiler::phar()` spawns a subprocess with `phar.readonly=0` (INI_SYSTEM), so wasm can only boot the already-built phar. Packing happens on the build machine.
- **The `phar` and `sqlite` extensions are separate packages.** They are not in php-cgi-wasm's default build; load them via `php-wasm-phar` and `php-wasm-sqlite` in `sharedLibs`.

## Demo

[koriym/wasm-todo](https://github.com/koriym/wasm-todo) is the minimal implementation: two resources (`Todos`/`Todo`), an `HtmlRenderer`, a `TodoRepository`, a service worker, and a build script. GitHub Actions builds the phar, bundles it with esbuild, and deploys to GitHub Pages.
