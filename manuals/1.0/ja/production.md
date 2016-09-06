---
layout: docs-ja
title: プロダクション
category: Manual
permalink: /manuals/1.0/ja/production.html
---

# プロダクション

プロダクション環境用の構成のためにキャッシュの設定やスクリプトの変更を行います。

## bootファイル

コンテキストが`prod-`で始まるとアプリケーションオブジェクト`$app`がキャッシュされます。
キャッシュドライバーは環境に応じて`ApcCache`か`FilesystemCache`が自動選択されます。

```php?start_inline
$context = 'prod-app';
require dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';
```

## キャッシュの設定

## ProdModule

`BEAR.Package`のプロダクション用のモジュール`ProdModule`はwebサーバー1台を前提にしている`ApcCache`になっています。
webサーバー1台でキャッシュを全て`Apc`で使う場合にはそのまま使用できます。

複数のWebサーバーを構成するためには共有のキャッシュストレージを設定する必要があります。
この場合、アプリケーション固有の`ProdModule`を`src/Module/ProdModule.php`に用意して、
サーバー間で共有するコンテンツ用キャッシュ`Doctrine\Common\Cache\CacheProvider:@BEAR\RepositoryModule\Annotation\Storage`インターフェイスとサーバー単位のキャッシュ`Doctrine\Common\Cache\Cache`インターフェイスを束縛します。

```php?start_inline
namespace BEAR\HelloWorld\Module;

use BEAR\RepositoryModule\Annotation\Storage;
use BEAR\Package\Context\ProdModule as PackageProdModule;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
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
        $cache = ApcCache::class;
        // 共有キャッシュ
        $this->bind(CacheProvider::class)->annotatedWith(Storage::class)->to($cache)->in(Scope::SINGLETON);
        // サーバー単位のキャッシュ
        $this->bind(Cache::class)->to($cache)->in(Scope::SINGLETON);

        // ProdModule パッケージのインストール
        $this->install(new PackageProdModule);
    }
}
```
`@Storage`とアノテートされた`Cache`インターフェイスは、クエリーリポジトリーのためのものでWebサーバーで共有されるストレージです。

複数のWebサーバーで`ApcCache`を指定することはできないので、
[Redis](http://doctrine-orm.readthedocs.org/en/latest/reference/caching.html#redis)を指定するか、永続化可能な他のストレージを使ったアダプターを作成して束縛します。
([memcached](http://doctrine-orm.readthedocs.org/en/latest/reference/caching.html#memcached)も指定できますが、メモリなので容量と揮発性に注意する必要があります。）

## HTTP Cache

キャッシュ可能(`@Cacheable`)とアノテートしたリソースはエンティティタグ`ETag`を出力します。

この`ETag`を使ってリソースに変更が無い時は自動で適切な`304` (Not Modified)のレスポンスコードを返すことができます。
（この時、ネットワークの転送コストだけでなく、CPUコストも最小限のものにします。）

### App

`HttpCache`をスクリプトで使うために`App`クラスで`HttpCacheInject`のtraitを使って`HttpCache`をインジェクトします。

```php?start_inline
namespace MyVendor\MyApi\Module;

use BEAR\QueryRepository\HttpCacheInject; // この行を追加
use BEAR\Sunday\Extension\Application\AbstractApp;
use Ray\Di\Di\Inject;

class App extends AbstractApp
{
    use HttpCacheInject; // この行を追加
}
```

### bootstrap

次に`bootstrap/bootstrap.php`の`route`のセクションで以下のように`if`文を追加して、
与えらた`ETag`のコンテンツに変更がなければ`304`を返して終了するようにします。

```php?start_inline
route: {
    $app = (new Bootstrap)->getApp(__NAMESPACE__, $context);
    if ($app->httpCache->isNotModified($_SERVER)) {
        http_response_code(304);
        exit(0);
    }

```

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

## ディプロイ

[Deployer](http://deployer.org/)のサポート[BEAR.Sunday Deployer.php support](https://github.com/bearsunday/deploy)をご覧ください。
