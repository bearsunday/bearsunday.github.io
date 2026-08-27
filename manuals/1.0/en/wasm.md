---
layout: docs-en
title: Wasm
category: Manual
permalink: /manuals/1.0/en/wasm.html
---

# Wasm

A [Phar](phar.html) application also runs in the browser. A service worker boots PHP compiled to WebAssembly (php-cgi-wasm) and hands every request to `app.phar`. No PHP runtime, no application server — static file hosting is all it takes.

```text
browser ──fetch──> service worker ──> php-cgi-wasm ──> app.phar (BEAR.Sunday)
                    (wasm/sw.js)       └─ pdo_sqlite
```

Resources return HTML, and `_links` become `<a>` and `<form>`. State goes to SQLite (`pdo_sqlite`), held by the browser's IndexedDB.

A working demo is [koriym/wasm-todo](https://github.com/koriym/wasm-todo) ([live page](https://koriym.github.io/wasm-todo/)).

Requires BEAR.Package 1.24+. The `ReadOnlyAppModule` in `ProdModule` is the one [Phar](phar.html) shows.

## Build

The archive is built on the build machine, as with [Phar](phar.html).

```bash
composer compile          # bin/compile.php compiles prod-html-app and packs app.phar
```

To answer in HTML, `HtmlModule` binds `RenderInterface` to `HtmlRenderer`.

Packing cannot happen inside wasm: `Compiler::phar()` uses a subprocess with `phar.readonly=0`, and wasm has no processes. What wasm does is boot the finished archive.

## Run

`index.html` registers the service worker. The worker places `app.phar` on the wasm virtual filesystem and routes every request through PHP.

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

This is all the JavaScript there is.

The `phar` and `sqlite` extensions are not in php-cgi-wasm's default build; `php-wasm-phar` and `php-wasm-sqlite` hand them to `sharedLibs`.

Node.js does the bundling, and the result goes on any static server.

```bash
cd wasm
npm install
npm run build             # bundles sw.js and collects assets into dist/
npx serve dist            # any static server
```

## Subpath

GitHub Pages serves under a subpath such as `https://{user}.github.io/{repo}/`. The worker derives its `basePath` from its own URL, and the entry point strips `BASE_PATH` before routing. Resources return relative links, so links stay correct under any subpath.

## Persistence

On the wasm virtual filesystem, `/persist` is the mount synced to IndexedDB: a SQLite database placed there survives reloads. In the demo, `TodoRepository` writes `/persist/todo.db`.

## Wasm limits

MySQL does not run. Browser wasm has no raw TCP sockets, so neither `mysqli` nor `pdo_mysql` can connect. Storage is SQLite or PGlite, both running in the browser, or a remote Cloudflare D1. The limit is TCP, not HTTP.

## Demo

[koriym/wasm-todo](https://github.com/koriym/wasm-todo) is two resources (`Todos`/`Todo`), an `HtmlRenderer`, a `TodoRepository`, a service worker and a build script. GitHub Actions builds the phar, esbuild bundles it, and GitHub Pages serves it.
