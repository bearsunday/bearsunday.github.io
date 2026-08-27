---
layout: docs-ja
title: Wasm
category: Manual
permalink: /manuals/1.0/ja/wasm.html
---

# Wasm

[Phar](phar.html)にしたアプリケーションを、ブラウザの中で動かします。サーバーもPHPランタイムも要りません。1つの`app.phar`がWebAssemblyの中で起動し、service workerがHTTPを処理します。

```text
ブラウザ ──fetch──> service worker ──> php-cgi-wasm ──> app.phar (BEAR.Sunday)
                     (wasm/sw.js)                        └─ pdo_sqlite
```

デモ: [koriym/wasm-todo](https://github.com/koriym/wasm-todo) / [公開ページ](https://koriym.github.io/wasm-todo/)

## 何ができるか

- コンパイル済みの`app.phar`がブラウザ内で起動し、HTTPを返す
- リソースがHTMLを返し、`_links`が`<a>`と`<form>`になる
- SQLite（`pdo_sqlite`）で状態を永続化する。ブラウザのIndexedDBが裏にある

価値はtodoアプリではありません。PHP開発者が、JavaScriptを書かずに、サーバーを立てずに、動くWebアプリを1ファイルで配れることです。

## 必要なもの

- BEAR.Package 1.24以降（[Phar](phar.html)と同じく`ReadOnlyAppModule`を使う）
- PHP 8.5（pharのビルド用）
- Node.js（service workerのバンドル用）

## ビルド

```bash
composer install
composer compile          # bin/compile.php が app.phar を作る
```

`bin/compile.php`は`prod-html-app` contextでコンパイルし、`app.phar`にパックします。HTMLを返すための`HtmlModule`が`RenderInterface`を`HtmlRenderer`に束縛します。

## 動かす

```bash
cd wasm
npm install
npm run build             # sw.js をバンドルし、dist/ にアセットを集める
npx serve dist            # 任意の静的サーバー
```

`index.html`がservice workerを登録し、workerが`app.phar`をwasm仮想ファイルシステムの`/index.php`にマウントして、全リクエストをPHPに流します。

## 仕組み

### service worker

`wasm/sw.js`が`PhpCgiWorker`（php-cgi-wasm）を起動します。`handleFetchEvent`がリクエストをPHPに渡し、レスポンスを返します。Nodeサーバーは要りません。

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

### サブパス

GitHub Pagesは`https://koriym.github.io/wasm-todo/`のようにサブパスで配信します。workerは自身のURLから`basePath`を導出し、エントリポイントが`BASE_PATH`を剥がしてルーティングします。リソースは相対リンクを返すので、サブパスでもリンクが正しく維持されます。

### 永続化

`TodoRepository`は`/persist/todo.db`に書きます。`/persist`はwasm仮想ファイルシステムのうちIndexedDBに同期されるマウントで、リロードしても状態が残ります。

## 制約

- **MySQLは動かない**。ブラウザのwasmにはraw TCPソケットがなく、`mysqli`/`pdo_mysql`は使えません。SQLite、PGlite、Cloudflare D1が選択肢です。
- **パッキングはネイティブのみ**。`Compiler::phar()`は`phar.readonly=0`（INI_SYSTEM）の子プロセスをspawnするため、wasmではブートのみ可能です。パッキングはビルドマシンで行います。
- **`phar`と`sqlite`拡張は別パッケージ**。php-cgi-wasmのデフォルトビルドには入っていません。`php-wasm-phar`と`php-wasm-sqlite`を`sharedLibs`でロードします。

## デモ

[koriym/wasm-todo](https://github.com/koriym/wasm-todo)が最小の実装です。リソース2つ（`Todos`/`Todo`）、`HtmlRenderer`、`TodoRepository`、service worker、ビルドスクリプトで構成されます。GitHub Actionsがpharをビルドし、esbuildでバンドルしてGitHub Pagesにデプロイします。
