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
BEAR.Sunday既定のprod束縛に対して、アプリケーションがカスタマイズした束縛を行ます。

## 既定のProdModule

既定の`prod`束縛では以下のインターフェイスの束縛がされています。

 * エラーページ生成ファクトリー
 * PSRロガーインターフェイス
 * ローカルキャッシュ
 * 分散キャッシュ
 
詳しくはBEAR.Packageの[ProdModule.php](https://github.com/bearsunday/BEAR.Package/blob/1.x/src/Context/ProdModule.php)をご覧ください。

## アプリケーションのProdModule

既定のProdModuleに対してアプリケーション用の`ProdModule`を`src/Module/ProdModule.php`に設置してカスタマイズします。特にエラーページと分散キャッシュは重要です。

```php
<?php
namespace MyVendor\Todo\Module;

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

        // 独自のエラーページ
        $this->bind(ErrorPageFactoryInterface::class)->to(MyErrorPageFactory::class);
    }
}
```

## キャッシュ

キャッシュはローカルキャッシュと、複数のWebサーバー間でシェアをする分散キャッシュの２種類があります。

ローカルキャッシュはdeploy後に変更のないアノテーション等のキャシュ例に使われ、分散キャッシュはリソース状態の保存に使われます。
どちらのキャッシュもデフォルトでは[PhpFileCache](https://www.doctrine-project.org/projects/doctrine-cache/en/1.10/index.html#phpfilecache)です。

### 分散キャッシュ

2つ以上のWebサーバーでサービスを行うためには分散キャッシュの構成が必要です。
代表的なキャッシュストレージに[memcached](http://php.net/manual/ja/book.memcached.php)または[Redis](https://redis.io)があり、それぞれのモジュールが用意されています。

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

リソースの状態保存は単にTTLによる時間更新のキャッシュとしてではなく、TTL時間では消えない永続的なストレージとして(CQRS）の運用も可能です。
その場合には`Redis`で永続処理を行うか、Cassandraなどの他KVSのストレージアダプターを記述する必要があります。

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

リソースのスキーマが代わり、互換性が失われる時にはキャッシュバージョンを変更します。特にTTL時間で消えないCQRS運用の場合に重要です。

```
$this->install(new CacheVersionModule($cacheVersion));
```
ディプロイの度にリソースキャッシュを破棄したい場合は`$cacheVersion`に時刻や乱数の値を割り当てます。

## デプロイ

### ⚠️ 上書き更新を避ける

#### サーバーにディプロイする場合

 * 駆動中のプロジェクトフォルダを`rsync`などで上書きするのはキャッシュやオンデマンドで生成されるファイルのが不一致や、高負荷のサイトではキャパシティを超えるリスクがあります。
安全のために別のディレクトリでセットアップを行いそのセットアップが成功すれば切り替えるようにします。
 * [Deployer](http://deployer.org/)の[BEAR.Sundayレシピ](https://github.com/bearsunday/deploy)を利用する事ができます。

#### クラウドにディプロイする時には

 * 依存関係の問題を見つけるとコンパイラはexitコード1を出力します。それを利用してCIにコンパイルを組み込む事を推奨します。

### コンパイル推奨

セットアップを行う際に`vendor/bin/bear.compile`スクリプトを使ってプロジェクトを**ウオームアップ**することができます。
コンパイルスクリプトはDI/AOP用の動的に作成されるファイルやアノテーションなどの静的なキャッシュファイルを全て事前に作成し、最適化されたautoload.phpファイルとpreload.phpを出力します。

 * 複数のコンテキストを扱うアプリケーションではそのコンテキストに応じた`autoload.php`の利用が効果的です。
 * コンパイルをすれば全てのクラスでインジェクションを行うのでランタイムでDIのエラーが出る可能性が極めて低くなります。
 * `.env`には含まれた内容はPHPファイルに取り込まれるのでコンパイル後に`.env`を消去可能です。

`composer.json`を編集して`composer compile`の内容を変更します。

### autoload.php

`{project_path}/autoload.php`に最適化されたautoload.phpファイルが出力されます。
`composer dumpa-autoload --optimize`で出力される`vendor/autoload.php`よりずっと高速です。

### preload.php

`{project_path}/preload.php`に最適化されたpreload.phpファイルが出力されます。
preloadを有効にするためにはphp.iniで[opcache.preload](https://www.php.net/manual/ja/opcache.configuration.php#ini.opcache.preload)、[opcache.preload](https://www.php.net/manual/ja/opcache.configuration.php#ini.opcache.preload-user)を指定する必要があります。PHP 7.4でサポートされた機能ですが、`7.4`初期のバージョンでは不安定です。`7.4.4`以上の最新版を使いましょう。

例）

```
opcache.preload=/path/to/project/preload.php
opcache.preload_user=www-data
```

Note: パフォーマンスについては[bechmark](https://github.com/bearsunday/BEAR.HelloworldBenchmark/wiki/Intel-Core-i5-3.8-GHz-iMac-(Retina-5K,-27-inch,-2017)---PHP-7.4.4)を参考にしてください。


### .compile.php

実環境ではないと生成ができないクラス、（例えば認証が成功しないとインジェクトが完了しないResourceObject）がある場合にはコンパイル時にのみ読み込まれるダミークラス読み込みをルートの`.compile.php`に記述する事によってコンパイルをする事ができます。


.compile.php

```php
<?php

require __DIR__ . '/tests/Null/AuthProvider.php'; // 常に生成可能なNullオブジェクト
$_SERVER[__REQUIRED_KEY__] = 'fake';
```

--

* このマニュアルはBEAR.Package 0.10に対応したマニュアルです。これ以前のものは[/manuals/1.0/ja/archive/production1.html](/manuals/1.0/ja/archive/production1.html)をご覧ください。
