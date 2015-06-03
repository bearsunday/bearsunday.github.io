---
layout: docs-ja
title: プロダクション
category: Manual
permalink: /manuals/1.0/ja/production.html
---
プロダクション環境用の構成のためにキャッシュの設定やスクリプトの変更を行います。

## bootファイル

コンテキストが`prod-`で始まるとアプリケーションオブジェクト`$app`がキャッシュされます。
キャッシュドライバーは環境に応じて`ApcCache`か`FilesystemCache`が自動選択されます。

{% highlight php %}
<?php
$context = 'prod-app';
require dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';
{% endhighlight %}

## キャッシュの設定

## ProdModule

`BEAR.Package`のプロダクション用のモジュール`ProdModule`はwebサーバー1台を前提にしている`ApcCache`になっています。
webサーバー1台でキャッシュを全て`Apc`で使う場合にはそのまましようできます。

複数のWebサーバーの構成のためには共有のキャッシュストレージの設定が必要です。
その場合アプリケーション固有の`ProdModule`を`src/Module/ProdModule.php`に用意します。

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
        // configure shared storage for query repository
        $cache = ApcCache::class;
        $this->bind(CacheProvider::class)->annotatedWith(Storage::class)->to($cache)->in(Scope::SINGLETON);

        $this->install(new PackageProdModule);
    }
}
{% endhighlight %}
`@Storage`とアノテートされた`Cache`インターフェイスは、クエリーリポジトリーのためのものでWebサーバーで共有されるストレージです。

複数のWebサーバーで`ApcCache`を指定することはできないので、
[Redis](http://doctrine-orm.readthedocs.org/en/latest/reference/caching.html#redis)を指定するか永続化可能な他のストレージを使ったアダプターを作成して束縛します。
([memcached](http://doctrine-orm.readthedocs.org/en/latest/reference/caching.html#memcached)も指定できますが、メモリなので容量と揮発性に注意）

## HTTP Cache

キャッシュ可能(`@Cacheable`)とアノテートしたリソースはエンティティタグ`ETag`を出力します。

この`ETag`を使ってリソースに変更が無い時は自動で適切な`304` (Not Modified)のレスポンスコードを返すことができます。
（この時、ネットワークの転送コストだけでなく、CPUコストも最小限のものにします。）

### App

`HttpCache`をスクリプトで使うために`App`クラスで`HttpCacheInject`のtraitを使って`HttpCache`をインジェクトします。

{% highlight php %}
<?php

namespace MyVendor\MyApi\Module;

use BEAR\QueryRepository\HttpCacheInject; // この行を追加
use BEAR\Sunday\Extension\Application\AbstractApp;
use Ray\Di\Di\Inject;

class App extends AbstractApp
{
    use HttpCacheInject; // この行を追加
}
{% endhighlight %}

### bootstrap

次に`bootstrap/bootstrap.php`の`route`のセクションで以下のように`if`文を追加して、
与えらた`ETag`のコンテンツに変更がなければ`304`を返して終了するようにします。

{% highlight php %}
<?php
route: {
    $app = (new Bootstrap)->getApp(__NAMESPACE__, $context);
    if ($app->httpCache->isNotModified($_SERVER)) {
        http_response_code(304);
        exit(0);
    }

{% endhighlight %}

`ETag`の更新は自動で行われますが、`@Refresh`や`@Purge`アノテーションを使ってリソースキャッシュの破棄の関係性を適切に指定しておかなければなりません。

## エクステンション

以下のPECLエクステンションをインストールするとパフォーマンスが最適化されます。

 * [PECL/uri_template](http://pecl.php.net/package/uri_template) URI Template
 * [PECL/igbinary](https://pecl.php.net/package/igbinary) シリアライズ最適化

```
pecl install uri_template
pecl install igbinary
```

確認

```
composer show --platform
ext-uri_template    1.0      The uri_template PHP extension
```
