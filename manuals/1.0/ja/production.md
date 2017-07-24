---
layout: docs-ja
title: プロダクション
category: Manual
permalink: /manuals/1.0/ja/production.html
---

# プロダクション

アプリケーションの[ディプロイ環境](https://en.wikipedia.org/wiki/Deployment_environment)に応じてキャッシュの設定や束縛の変更を行います。

## コンテキスト

bootファイルで指定するコンテキストが`prod-`または`stage-`で始まるとアプリケーションオブジェクト`$app`がキャッシュされます。（`prod`は実際に運用するプロダクションサイトで`stage`は`prod`のミラーサイトです）

```php?start_inline
$context = 'prod-app';
require dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';
```

deploy後にキャッシュを再生成するにはwebサーバーを再起動するか、`src/`ディレクトリのタイムスタンプを変更します。

## ProdModule

アプリケーション用の`ProdModule`を`src/Module/ProdModule.php`に用意してプロダクション用の束縛をカスタマイズします。

```php
<?php
namespace Polidog\Todo\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\QueryRepository\CacheVersionModule;
use BEAR\Resource\Module\OptionsMethodModule;
use Ray\Di\AbstractModule;

class ProdModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new PackageProdModule);       // デフォルトのprod設定
        $this->install(new OptionsMethodModule);     // OPTIONSメソッドをプロダクションでも有効に
        $this->install(new CacheVersionModule('1')); // リソースキャッシュのバージョン指定
    }
}
```

## キャッシュ

キャッシュは複数のWebサーバー間でシェアをしないローカルキャッシュと、シェアをする共有キャッシュの２種類あります。ローカルキャッシュはdeploy後に変更のないキャシュ例えばアノテーション等に使われ、共有キャッシュはリソース状態の保存に使われます。

どちらのキャッシュもデフォルトでは「APCuキャッシュ+ファイルキャッシュ」のチェーンキャッシュです。リードはAPCuが優先して使用されライトは両方のストレージに行われます。

### リソースキャッシュ

複数のWebサーバーを構成するためには共有のキャッシュストレージを設定する必要があり、（[memcached](http://php.net/manual/ja/book.memcached.php)または[Redis](https://redis.io)）のモジュールをインストールします。

### memcached

```php
<?php
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

上記以外のストレージを利用する場合には、それぞれのモジュールを参考に新たにモジュールを作成します。

### キャッシュ時間の指定

デフォルトのTTLを変更する場合`StorageExpiryModule`をインストールします。

```php?start_inline
// Cache time
$short = 60;
$medium = 3600;
$long = 24 * 3600;
$this->install(new StorageExpiryModule($short, $medium, $long);
```
### キャッシュバージョンの指定

リソースキャッシュの互換性が失われるdeployの時にはキャッシュバージョンを変更します。

```
$this->install(new CacheVersionModule($cacheVersion));
```

deployの度にリソースキャッシュを破棄したい場合は`$cacheVersion`に時刻や乱数の値を割り当てます。

## HTTP キャッシュ

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

### ⚠️ 上書き更新を避ける

駆動中のプロジェクトフォルダを`rsync`などで上書きするのはリソースキャッシュの不一致や`tmp/`に作成される自動生成のクラスファイルと実際のクラスとの不一致になるリスクがあります。高負荷のサイトではキャッシュ作成やopcode作成などの大量のジョブが同時に複数実行されサイトのパフォーマンスのキャパシティを超える可能性もあります。

別のディレクトリでセットアップを行いそのセットアップが問題なければ切り替えるようにします。

### コンパイルを推奨

セットアップを行う際に`vendor/bin/bear.compile`スクリプトを使ってプロジェクトを**ウオームアップ**することができます。コンパイルスクリプトはDI/AOP用の動的に作成されるファイルやアノテーションなどの静的なキャッシュファイルを全て事前に作成します。

全てのクラスでインジェクションを行うのでランタイムでDIのエラーが出ることもありません。また`.env`には一般にAPIキーやパスワードなどのクレデンシャル情報が含まれますが、内容は全てPHPファイルに取り込まれるのでコンパイル後に消去可能です。コンパイルはdeployをより高速で安全にします。

例）コンソールで実行

```
vendor/bin/bear.compile 'Polidog\Todo' prod-html-app /path/to/prject
```
例) PHPスクリプトで実行

```php
<?php
use BEAR\Package\Compiler;
use Doctrine\Common\Annotations\AnnotationRegistry;

$appName = 'Polidog\Todo';
$context = 'prod-html-app';
$appDir = '/path/to/project';

$loader = require $appDir . '/vendor/autoload.php';
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
(new Compiler)->__invoke($appName, $context, $appDir);
```


### Deployerサポート

[Deployer](http://deployer.org/)の[BEAR.Sundayレシピ](https://github.com/bearsunday/deploy)を利用が便利で安全です。他のサーバー構成ツールを利用する場合でも参考にしたりDeployerスクリプトを実行することを検討してください。
