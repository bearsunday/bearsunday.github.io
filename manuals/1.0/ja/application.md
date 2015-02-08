---
layout: docs-ja
title: アプリケーション
category: Manual
permalink: /manuals/1.0/ja/application.html
---

## アプリケーションオブジェクト ##

`$app`という１つの変数にアプリケーション全体のオブジェクトが格納されます。

`$app`は`router`や`transfer`などアプリケーションの実行に必要なサービスオブジェクトをプロパティに持ちます。
アプリケーションスクリプト`bootstrap.php`はルーターを使いリソースリクエストを作成、実行して結果をクライアントに転送します。

## 実行シーケンス ##

 0. アプリケーションコンテキスト`$context`が与えられ、それに応じたDIとAOPの束縛の集合で`$app`を生成
 1. 外部のパラメーターを元にリソースリクエストを作成してリクエスト
 2. リクエスト結果を表現にしてクライアントに転送

 <img src="/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>

 この実行シーケンスを表したアプリケーションスクリプト`bootstrap/bootstrap.php`は変更可能です。
 
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
     $page = $app->resource
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

## 実行ファイル ##
 
グローバル変数`$context`にコンテキストを指定して`bootstrap.php`ファイルを読み込むと実行されます。

{% highlight php %}
<?php
$context = 'prod-api-hal-app'
require 'pat/to/bootstrap.php'; 
{% endhighlight %}

コンテキストによるアプリケーション変更は実行ファイル選択で事で行います。例えばAPIは`bootstrap/api.php`をHTTPのゲートウエイファイルに指定しますが、
コンソールアプリケーションの場合は`bootstrap/cli.php`を呼び出します。

{% highlight bash %}
// web app
php -S 127.0.0.1:8080 var/www/index.php

// console app
php bootstrap/api.php get /user/1
{% endhighlight %}

## コンテキスト ##

`$app`に含まれるオブジェクトは、あるオブジェクトから所有されているか、他のオブジェクト（またはそのリファレンス）を含んでいるか、そのどちらかでお互いに接続されている**オブジェクトグラフ**です。
接続を変える事でアプリケーションは振る舞いを変えます。（例えば`Cli`コンテキストでは`RouterInterface`に`CliRouter`を束縛してコンソールの入力値がルーターの入力値になります。）

そのコンテキストに応じた束縛の集合が**アプリケーションコンテキスト**です。

フレームワークが用意しているbuilt-inコンテキストとアプリケーションが作成するカスタムコンテキストがあります。

**built-inコンテキスト**

 * `api`  APIアプリケーション
 * `cli`  コンソールアプリケーション
 * `hal`  HALアプリケーション
 * `prod` プロダクション

 コンテキストは組み合わせて使う事ができます。
 
`app`は素のアプリケーションです。
`cli-app`にするとコンソールアプリケーションになり、`prod-hal-api-app`だとHALフォーマットを使ったプロダクション用のAPIアプリケーションになります。
 
アリケーションコンテキストはそれぞれのモジュールに対応します。例えば`cli`コンテキストは`CliModule`でコンソールアプリケーションのためのDIとAOPの束縛が行われます。

コンテキストの値はオブジェクトの作成のみに使われ、意図的に保持されません。
アプリケーションやライブラリのコードでコンテキストを参照して振る舞いを変える事は推奨されず、実現できないようになっています。

代わりにインターフェイスのみに依存したコードを記述し、コンテキストによって依存を変える事で振る舞いを変えます。
