---
layout: docs-ja
title: Wasm
category: Manual
permalink: /manuals/1.0/ja/wasm.html
---

# Wasm

[Phar](phar.html)にしたアプリケーションは、ブラウザの中でも動きます。service workerがWebAssemblyにコンパイルされたPHP（php-cgi-wasm）を起動し、fetchを`app.phar`に渡します。PHPランタイムもアプリケーションサーバーも要りません。要るのは静的配信だけです。

```text
ブラウザ ──fetch──> service worker ──> php-cgi-wasm ──> app.phar (BEAR.Sunday)
                     (wasm/sw.js)       └─ pdo_sqlite
```

リソースはHTMLを返し、`_links`は`<a>`と`<form>`になります。状態はSQLite（`pdo_sqlite`）に書かれ、ブラウザのIndexedDBが保持します。書くのはPHPだけで、アプリケーションのJavaScriptはありません。

動くデモは[koriym/wasm-todo](https://github.com/koriym/wasm-todo)（[公開ページ](https://koriym.github.io/wasm-todo/)）です。

BEAR.Package 1.24以降が必要です。`ProdModule`に書く`ReadOnlyAppModule`は[Phar](phar.html)と同じです。

## ビルド

アーカイブはビルドマシンで作ります。[Phar](phar.html)と同じです。

```bash
composer compile          # bin/compile.php が prod-html-app をコンパイルして app.phar にパック
```

HTMLで答えるために、`HtmlModule`が`RenderInterface`を`HtmlRenderer`に束縛します。

wasmの中でパックはできません。`Compiler::phar()`は`phar.readonly=0`の子プロセスを使い、wasmにプロセスはないからです。wasmがするのは、出来上がったアーカイブの起動だけです。

## 動かす

`index.html`がservice workerを登録します。workerは`app.phar`をwasmの仮想ファイルシステムに置き、すべてのリクエストをPHPに渡します。

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

`phar`と`sqlite`の拡張はphp-cgi-wasmの既定ビルドに入っていません。`php-wasm-phar`と`php-wasm-sqlite`を`sharedLibs`で渡します。

バンドルはNode.jsで行い、出来たものを静的サーバーに置きます。

```bash
cd wasm
npm install
npm run build             # sw.js をバンドルし、dist/ にアセットを集める
npx serve dist            # 任意の静的サーバー
```

## サブパス

GitHub Pagesは`https://{user}.github.io/{repo}/`のようにサブパスで配信します。workerは自身のURLから`basePath`を導出し、エントリポイントが`BASE_PATH`を剥がしてからルーティングします。リソースが返すリンクは相対なので、どのサブパスでも正しいままです。

## 永続化

wasmの仮想ファイルシステムのうち、`/persist`はIndexedDBに同期されるマウントです。ここに置いたSQLiteはリロードしても残ります。デモでは`TodoRepository`が`/persist/todo.db`に書きます。

## 動かないもの

MySQLは動きません。ブラウザのwasmにはraw TCPソケットがなく、`mysqli`も`pdo_mysql`も接続できません。ストレージはSQLiteか、PGlite、Cloudflare D1です。

## デモ

[koriym/wasm-todo](https://github.com/koriym/wasm-todo)は、リソース2つ（`Todos`/`Todo`）、`HtmlRenderer`、`TodoRepository`、service worker、ビルドスクリプトの構成です。GitHub Actionsがpharをビルドし、esbuildでバンドルし、GitHub Pagesが配信します。
