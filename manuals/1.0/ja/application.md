---
layout: docs-ja
title: アプリケーション
category: Manual
permalink: /manuals/1.0/ja/application.html
---

## アプリケーションスクリプト

アプリケーションはアプリケーションスクリプト`bootstrap/bootstrap.php`で実行されます。

スクリプトではリソースリクエストを行い、そのリソース表現(HTMLやJSON)をクライアントに転送します。
その時アプリケーションオブジェクト`$app`は`router`や`transfer`などアプリケーションの実行に必要な保持しているサービスオブジェクトを使います。

`$app`のオブジェクトは他から所有されているか、他のオブジェクトを含んでいるかの関係でお互いに接続されている１つの大きなオブジェクトです。
これを[オブジェクトグラフ](http://en.wikipedia.org/wiki/Object_graph)と呼びますが`$app`はそのルートのオブジェクトです。

### 0. コンパイル

与えられた`$context`に対応するDIとAOPの設定でアプリケーションオブジェクト`$app`が作られます。

### 1. リクエスト

HTTPリクエストを元にアプリケーションのリソースリクエストが作成されます。リソースオブジェクトはリクエストに対応する`onGet`や`onPost`などのメソッドで自身のリソース状態を`code`や`body`にセットします。

### 2. レスポンス

リソースオブジェクトにインジェクトされているレンダラーが、リクエスト結果による**リソースの状態**をJSONやHTMLなどの**表現**にしてクライアントに**転送**します。

 <img src="/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>

### REST

アプリケーションスクリプト`bootstrap/bootstrap.php`はユーザー領域にあり変更可能です。

{% highlight php %}
<?php

 /**
  * @global string $context
  */
 namespace MyVendor\Weekday;

 use BEAR\Package\Bootstrap;
 use BEAR\Package\AppMeta;
 use Doctrine\Common\Cache\ApcCache;
 use Doctrine\Common\Annotations\AnnotationRegistry;

 load: {
     $dir = dirname(__DIR__);
     $loader = require $dir . '/vendor/autoload.php';
     AnnotationRegistry::registerLoader([$loader, 'loadClass']);
 }

 route: {
     $context = isset($context) ? $context : 'app';
     $app = (new Bootstrap)->newApp(new AppMeta(__NAMESPACE__), $context, new ApcCache);
     /** @var $app \BEAR\Sunday\Extension\Application\AbstractApp */
     $request = $app->router->match($GLOBALS, $_SERVER);
 }

 try {
     // resource request
     $page = $app
         ->resource
         ->{$request->method}
         ->uri($request->path)
         ->withQuery($request->query)
         ->request();
     /** @var $page \BEAR\Resource\Request */

     // representation transfer
     $page()->transfer($app->responder, $_SERVER);
     exit(0);
 } catch (\Exception $e) {
     $app->error->handle($e, $request)->transfer();
     exit(1);
 }
{% endhighlight %}

## bootファイル

アプリケーションを実行するわずか２行のPHPスクリプトです。`var/www/index.php`や`bootstrap/api.php`等に設置してWebサーバーやコンソールアプリケーションのエントリーポイントにします。
スクリプトではグローバル変数`$context`にコンテキストを指定して`bootstrap.php`ファイルを読み込むとアプリケーションが実行されます。

{% highlight php %}
<?php
$context = 'prod-api-hal-app'
require 'pat/to/bootstrap.php';
{% endhighlight %}

コンテキストに応じてをbootファイルを選択します。

{% highlight bash %}
// fire php server
php -S 127.0.0.1:8080 var/www/index.php

// console access
php bootstrap/api.php get /user/1

// web access
php -S 127.0.0.1:8080 bootstrap/api.php
{% endhighlight %}

## アプリケーションコンテキスト

コンテキストに応じてアプリケーションオブジェクト`$app`の構成が変わり、振る舞いが変更されます。
例えばデフォルトの設定では`RouterInterface`に`WebRouter`が束縛されていますが、`Cli`では`RouterInterface`に`CliRouter`が束縛され(HTTPリクエストの代わりに)コンソールの入力値が入力値になります。

フレームワークが用意しているbuilt-inコンテキストとアプリケーションが作成するカスタムコンテキストがあります。

**built-inコンテキスト**

 * `api`  APIアプリケーション
 * `cli`  コンソールアプリケーション
 * `hal`  HALアプリケーション
 * `prod` プロダクション

 コンテキストは組み合わせて使う事ができます。

 * `app`は素のアプリケーションです。
 * `api`はデフォルトのリソースをpageリソースから**appリソース**に変更します。`page://self/`にマップされているWebのルートアクセス(`GET /`)は`app://self/`へのアクセスになります。
 * `cli-app`にするとコンソールアプリケーションになり、`prod-hal-api-app`だと[HAL](http://stateless.co/hal_specification.html)メディタイプを使ったプロダクション用のAPIアプリケーションになります。


 アプリケーションコンテキスト(cli, app..)はそれぞれのモジュールに対応します。例えば`cli`は`CliModule`に対応しており、コンソールアプリケーションのためのDIとAOPの束縛が行われます。

コンテキストの値はオブジェクトグラフの作成のみに使われます。コンテキストを参照してアプリケーションやライブラリがて振る舞いを変える事は推奨されません。その代わりに**インターフェイスのみに依存したコード**と**コンテキストによる依存の変更**で振る舞いを変えます。
