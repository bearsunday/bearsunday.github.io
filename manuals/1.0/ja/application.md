---
layout: docs-ja
title: アプリケーション
category: Manual
permalink: /manuals/1.0/ja/application.html
---

# <a name="app"></a>アプリケーション

アプリケーションは以下のような順番で実行されます。

```php?start_inline
boot -> compile -> request(route, method, interceptor) -> response(representation) -> exit
```


## 0. コンパイル

与えられた`$context`に対応するDIとAOPの設定で、`router`や`transfer`などアプリケーションの実行に必要なサービスを保持する`$app`オブジェクトが作られます。

## 1. リクエスト

HTTPリクエストは`router`でアプリケーションのリソースへのリクエストに変換され実行されます。
リソースの`method`では**リソース状態**を`code`,`header`, `body`にセットします。

## 2. レスポンス

リソースのレンダラーが**リソースの状態**を**表現**(JSONやHTML)にして、クライアントに**転送**します。
(**RE**presentational **S**tate **T**ransfer)

 <img src="/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>


# <a name="boot"></a>bootファイル

アプリケーションを実行するわずか２行のPHPスクリプトです。`var/www/index.php`や`bin/app.php`等に設置してWebサーバーやコンソールアプリケーションのエントリーポイントにします。
スクリプトではグローバル変数`$context`にコンテキストを指定して`bootstrap.php`ファイルを読み込むとアプリケーションが実行されます。

```php?start_inline
$context = 'prod-api-hal-app'
require 'pat/to/bootstrap.php';
```

コンテキストに応じてbootファイルを選択します。

```bash
// fire php server
php -S 127.0.0.1:8080 var/www/index.php

// console access
php bin/app.php get /user/1

// web access
php -S 127.0.0.1:8080 bin/app.php
```

## <a name="context"></a>アプリケーションコンテキスト

コンテキストに応じてアプリケーションオブジェクト`$app`の構成が変わり、振る舞いが変更されます。
例えばデフォルトの設定では`RouterInterface`に`WebRouter`が束縛されていますが、`Cli`では`RouterInterface`に`CliRouter`が束縛され(HTTPリクエストの代わりに)コンソールの入力値が入力値になります。
また、キャッシュや認証、ログなど`method`の前後に実行されるインターセプターもコンテキストに応じて変わります。

フレームワークが用意しているbuilt-inコンテキストとアプリケーションが作成するカスタムコンテキストがあります。

**built-inコンテキスト**

 * `api`  APIアプリケーション
 * `cli`  コンソールアプリケーション
 * `hal`  HALアプリケーション
 * `prod` プロダクション

 コンテキストは組み合わせて使う事ができます。

 * `app`は基本のアプリケーションです。リソースはJSONでレンダリングされます。
 * `api`はデフォルトのリソースをpageリソースから**appリソース**に変更します。`page://self/`にマップされているWebのルートアクセス(`GET /`)は`app://self/`へのアクセスになります。
 * `cli-app`にするとコンソールアプリケーションになり、`prod-hal-api-app`だと[HAL](http://stateless.co/hal_specification.html)メディアタイプを使ったプロダクション用のAPIアプリケーションになります。


 アプリケーションコンテキスト(cli, app..)はそれぞれのモジュールに対応します。例えば`cli`は`CliModule`に対応しており、コンソールアプリケーションのためのDIとAOPの束縛が行われます。

コンテキストの値はオブジェクトグラフの作成のみに使われます。コンテキストを参照してアプリケーションやライブラリが振る舞いを変える事は推奨されません。その代わりに**インターフェイスのみに依存したコード**と**コンテキストによる依存の変更**で振る舞いを変えます。
