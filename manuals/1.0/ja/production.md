---
layout: docs-ja
title: プロダクション
category: Manual
permalink: /manuals/1.0/ja/production.html
---

# プロダクション

アプリケーションの[ディプロイ環境](https://en.wikipedia.org/wiki/Deployment_environment)に応じてキャッシュの設定や束縛の変更を行います。

## コンテキスト

`prod`はプロダクションのためのコンテキストです。
ルートオブジェクト`$app`やアノテーションリーダーなどでキャッシュが使われます。

```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')('prod-api-app'));
```
**重要:**

**プロダクションではディプロイ毎に`$app`を再生成する必要があります。**

`$app`を再生成するには`src/`ディレクトリのタイムスタンプを変更します。
BEAR.Sundayはそれを検知して`$app`と`tmp/{context}/di`のDI/AOPファイルの再生成を行います。

## ProdModule

アプリケーション用の`ProdModule`を`src/Module/ProdModule.php`に設置して、プロダクション用の束縛をカスタマイズやHTTP OPTIONSメソッドの許可を行う事ができます。

```php
<?php
namespace Polidog\Todo\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\QueryRepository\CacheVersionModule;
use BEAR\Resource\Module\OptionsMethodModule;
use BEAR\Package\AbstractAppModule;

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

### Memcached

```php
<?php
namespace BEAR\HelloWorld\Module;

use BEAR\QueryRepository\StorageMemcachedModule;
use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\Package\AbstractAppModule;
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


## デプロイ

### ⚠️ 上書き更新を避ける

駆動中のプロジェクトフォルダを`rsync`などで上書きするのはリソースキャッシュの不一致や`tmp/`に作成される自動生成のクラスファイルと実際のクラスとの不一致になるリスクがあります。高負荷のサイトではキャッシュ作成やopcode作成などの大量のジョブが同時に複数実行されサイトのパフォーマンスのキャパシティを超える可能性もあります。

別のディレクトリでセットアップを行いそのセットアップが問題なければ切り替えるようにします。

### コンパイル推奨

セットアップを行う際に`vendor/bin/bear.compile`スクリプトを使ってプロジェクトを**ウオームアップ**することができます。コンパイルスクリプトはDI/AOP用の動的に作成されるファイルやアノテーションなどの静的なキャッシュファイルを全て事前に作成し、最適化されたautoload.phpファイルとpreload.phpが出力されます。

全てのクラスでインジェクションを行うのでランタイムでDIのエラーが出ることもありません。また`.env`には一般にAPIキーやパスワードなどのクレデンシャル情報が含まれますが、内容は全てPHPファイルに取り込まれるのでコンパイル後に消去可能です。コンパイルはdeployをより高速で安全にします。

例）コンソールで実行

```
vendor/bin/bear.compile 'Polidog\Todo' prod-html-app /path/to/prject
```

### autoload.php

`{project_path}/autoload.php`に最適化されたautoload.phpファイルが出力されます。`composer dumpa-autoload --optimize`でダンプされる`vendor/autoload.php`よりずっと高速です。

### preload.php

`{project_path}/preload.php`に最適化されたpreload.phpファイルが出力されます。preloadを有効にするためにはphp.iniで[opcache.preload](https://www.php.net/manual/ja/opcache.configuration.php#ini.opcache.preload)、[opcache.preload](https://www.php.net/manual/ja/opcache.configuration.php#ini.opcache.preload-user)を指定する必要があります。PHP 7.4でサポートされた機能ですが、`7.4.0`-`7.4.2`では不安定で推奨できません。`7.4.4`以上の最新版を使いましょう。

例）
```
opcache.preload=/path/to/project/preload.php
opcache.preload_user=www-data
```

Note: パフォーマンスについては[bechmark](https://github.com/bearsunday/BEAR.HelloworldBenchmark/wiki/Intel-Core-i5-3.8-GHz-iMac-(Retina-5K,-27-inch,-2017)---PHP-7.4.4)を参考にしてください。


### Deployerサポート

[Deployer](http://deployer.org/)の[BEAR.Sundayレシピ](https://github.com/bearsunday/deploy)を利用が便利で安全です。他のサーバー構成ツールを利用する場合でも参考にしたりDeployerスクリプトを実行することを検討してください。またDeployerをプロジェクトのディレクトリを毎回生成するので`$app`の再生成を気にする必要がありません。
