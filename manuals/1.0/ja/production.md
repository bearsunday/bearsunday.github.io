---
layout: docs-ja
title: プロダクション
category: Manual
permalink: /manuals/1.0/ja/production.html
---
プロダクション環境用の構成のためにキャッシュの設定やスクリプトの変更を行います。

## bootファイル

コンテクストが`prod-`で始まるbootファイルを`var/www/index.php`などに用意します。

{% highlight php %}
<?php
$context = 'prod-app';
require dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';
{% endhighlight %}

## キャッシュの設定

## bootstrap.php

{% highlight php %}
<?php
$app = (new Bootstrap)->newApp(new AppMeta(__NAMESPACE__), $context, new ApcCache);
{% endhighlight %}

`newApp()`の３つ目の引数はアプリケーションオブジェクトをキャッシュします。通常`ApcCache`を指定します。
無指定の場合`ApcCache`か`FilesystemCache`を自動選択します。

## ProdModule

`BEAR.Package`のプロダクション用のモジュール`ProdModule`はwebサーバー1台で`ApcCache`を前提したものです。

複数のWebサーバーの構成のためには共有のキャッシュストレージの設定が必要です。
設定のためアプリケーション固有の`ProdModule`を`src/Module/ProdModule.php`に用意します。

{% highlight php %}
<?php
namespace BEAR\HelloWorld\Module;

use BEAR\RepositoryModule\Annotation\Storage;
use BEAR\Package\Context\ProdModule as PackageProdModule;
use Doctrine\Common\Cache\Cache;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

use Doctrine\Common\Cache\ApcCache;

class ProdModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $cache = ApcCache::class; // <= configure shared storage for query repository
        $this->bind(Cache::class)->annotatedWith(Storage::class)->to($cache)->in(Scope::SINGLETON);
        
        $this->install(new PackageProdModule);
    }
}
{% endhighlight %}
`@Storage`とアノテートされた`Cache`インターフェイスは、クエリーリポジトリーのためのものでWebサーバーで共有されるストレージです。

複数のWebサーバーで`ApcCache`を指定することはできないので、
[Redis](http://doctrine-orm.readthedocs.org/en/latest/reference/caching.html#redis)を指定するか永続化可能な他のストレージを使ったアダプターを作成して束縛します。
([memcached](http://doctrine-orm.readthedocs.org/en/latest/reference/caching.html#memcached)も指定できますが、メモリなので容量と揮発性に注意）

## HTTP Cache

`Etag`やリソースURIがクエリーレポジトリーに保存されていると`$httpCache($_SERVER)`を行った時最小のCPUコストでレスポンスを完了します。
`Etag`の場合は`304 (Not Modified)`のコードを返し転送コストも最小です。

`HttpCache`を有効にするためには`bootstrap.php`の`load`のセクションの後に`http_cache`セクションを追加します。

{% highlight php %}
<?php
use BEAR\QueryRepository\HttpCache;

http_cache: {
    $httpCache = new HttpCache(__NAMESPACE__);
    list($code) = $httpCache($_SERVER);
    if ($code) {
        exit(0);
    }
}
{% endhighlight %}

次にレスポンスの転送に`$httpCache->saver`を加えます。

{% highlight php %}
<?php
    $page = $app->resource
        ->{$request->method}
        ->uri($request->path)
        ->withQuery($request->query)
        ->eager
        ->request();
    $page->transfer($app->responder, $_SERVER);
    $page->transfer($httpCache->saver, $_SERVER);
{% endhighlight %}


