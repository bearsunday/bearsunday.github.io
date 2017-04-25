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

デフォルトの`ProdModule`はwebサーバー1台を前提にしている`ApcCache`になっています。
複数のWebサーバーを構成するためには共有のキャッシュストレージを設定する必要があります。

アプリケーション用の`ProdModule`を`src/Module/ProdModule.php`に用意して、サーバー間で共有するコンテンツ用キャッシュのストレージのモジュール（[memcached](http://php.net/manual/ja/book.memcached.php)または[Redis](https://redis.io)）をインストールします。

### memcached

```php?start_inline
namespace BEAR\HelloWorld\Module;

use BEAR\QueryRepository\StorageMemcachedModule;
use BEAR\Package\Context\ProdModule as PackageProdModule;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class ProdModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // memcache
        // {host}:{port}:{weight},...
        $memcachedServers = 'mem1.domain.com:11211:33,mem2.domain.com:11211:67';
        $this->install(new StorageMemcachedModule(memcachedServers);

        // デフォルトのProdModuleのインストール
        $this->install(new PackageProdModule);
    }
}
```

### Redis

```php?start_inline
// redis
$redisServer = 'localhost:6379'; // {host}:{port}
$this->install(new StorageRedisModule($redisServer);
```

### キャッシュ時間の指定

デフォルトのTTLを変更する場合

```php?start_inline
// Cache time
$short = 60;
$medium = 3600;
$long = 24 * 3600;
$this->install(new StorageExpiryModule($short, $medium, $long);
```

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

`bootstrap/bootstrap.php`で以下のように`if`文を追加して、`ETag`のコンテンツに変更がなければ`304 (NotModified)`を出力するようにします。

```php?start_inline

$app = (new Bootstrap)->getApp('BEAR\HelloWorld', $context, dirname(__DIR__));
if ($app->httpCache->isNotModified($_SERVER)) {
    http_response_code(304);
    exit(0);
}

```

`ETag`の更新は自動で行われますが、`@Refresh`や`@Purge`アノテーションを使ってリソースキャッシュの破棄の関係性を適切に指定しておかなければなりません。

## デプロイ

[Deployer](http://deployer.org/)のサポート[BEAR.Sunday Deployer.php support](https://github.com/bearsunday/deploy)をご覧ください。
