---
layout: docs-ja
title: アプリケーション
category: Manual
permalink: /manuals/1.0/ja/application.html
---

## アプリケーションオブジェクト

BEAR.Sundayのオブジェクトは、あるオブジェクトから所有されているか、他のオブジェクトを含んでいるか、そのどちらかでお互いに接続されています。
これを[オブジェクトグラフ](http://en.wikipedia.org/wiki/Object_graph)と呼びますが`$app`はそのオブジェクトグラフのルートのオブジェクトです。１つの変数にアプリケーション全体のオブジェクトが格納されています。

`$app`は`router`や`transfer`などアプリケーションの実行に必要なサービスオブジェクトをプロパティに持ちます。
アプリケーションスクリプト`bootstrap.php`はそのサービスを使ってリソースを作成しその表現をクライントに転送します。

以下の手順でスクリプトが実行されます。

### 0. コンパイル

アプリケーションスクリプト`bootstrap.php`にアプリケーションコンテキスト`$context`が与えられ、対応するDIとAOPの設定でアプリケーションオブジェクト`$app`が作られます。

### 1. リクエスト

HTTP等の外部のパラメーターを元にリソースリクエストが作成されます。リソースオブジェクトはHTTPのリクエストに対応する`onGet`や`onPost`などのメソッドで自身の状態を`code`や`body`にセットして変更します。

### 2. レスポンス

リソースオブジェクトにインジェクトされているレンダラーが、リクエスト結果による**リソースの状態**をJSONやHTMLなどの**表現**にしてクライアントに**転送**します。

 <img src="/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>

### REST

アプリケーションスクリプト`bootstrap/bootstrap.php`はREST=表現可能なリソース状態(**Representational State**)の転送(**Transfer**)の動作手順を表しています。
依存解決はコンパイルの時点で完了していてランタイム（初期化が終わってアプリケーションの実行中）に依存解決のエラーは発生しません。またこのアプリケーションスクリプト`bootstrap/bootstrap.php`はユーザー領域にあり変更可能です。

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

アプリケーションオブジェクト`$app`内のオブジェクト間の接続を変える事でアプリケーションのオブジェクトの構成が変わり、違う振る舞いをするようになります。
例えば`Cli`（コンソールコンテキスト）では`RouterInterface`に`WebRouter`の代わりに`CliRouter`を束縛すると、HTTPリクエストの代わりにコンソールの入力値がルーターの入力値になるようになります。


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

コンテキストの値はオブジェクトの作成のみに使われ、アプリケーションやライブラリのコードでコンテキストを参照して振る舞いを変える事は推奨されません。
代わりに**インターフェイスのみに依存したコード**と**コンテキストによる依存の変更**で振る舞いを変えます。
