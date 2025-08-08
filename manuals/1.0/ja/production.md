---
layout: docs-ja
title: プロダクション
category: Manual
permalink: /manuals/1.0/ja/production.html
---

# プロダクション

BEAR.Sunday既定の`prod`束縛に対して、アプリケーションがそれぞれの[デプロイ環境](https://en.wikipedia.org/wiki/Deployment_environment)に応じたモジュールをカスタマイズして束縛を行います。

## 既定のProdModule

既定の`prod`束縛では以下のインターフェイスの束縛がされています。

* エラーページ生成ファクトリー
* PSRロガーインターフェース
* ローカルキャッシュ
* 分散キャッシュ

詳細はBEAR.Packageの[ProdModule.php](https://github.com/bearsunday/BEAR.Package/blob/1.x/src/Context/ProdModule.php)参照。

## アプリケーションのProdModule

既定のProdModuleに対してアプリケーションの`ProdModule`を`src/Module/ProdModule.php`に設置してカスタマイズします。特にエラーページと分散キャッシュは重要です。

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
        $this->override(new OptionsMethodModule);    // OPTIONSメソッドをプロダクションでも有効に
        $this->install(new CacheVersionModule('1')); // リソースキャッシュのバージョン指定
        
        // 独自のエラーページ
        $this->bind(ErrorPageFactoryInterface::class)->to(MyErrorPageFactory::class);
    }
}
```

## キャッシュ

キャッシュはローカルキャッシュと、複数のWebサーバー間でシェアをする分散キャッシュの2種類があります。どちらのキャッシュもデフォルトは[PhpFileCache](https://www.doctrine-project.org/projects/doctrine-cache/en/1.10/index.html#phpfilecache)です。

### ローカルキャッシュ

ローカルキャッシュはdeploy後に変更のないアノテーション等のキャッシュ例に使われ、分散キャッシュはリソース状態の保存に使われます。

### 分散キャッシュ

2つ以上のWebサーバーでサービスを行うためには分散キャッシュの構成が必要です。代表的な[memcached](http://php.net/manual/ja/book.memcached.php)、[Redis](https://redis.io)のキャッシュエンジンのそれぞれのモジュールが用意されています。

### Memcached
```php
<?php
namespace BEAR\HelloWorld\Module;

use BEAR\QueryRepository\StorageMemcachedModule;
use BEAR\Resource\Module\ProdLoggerModule;
use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\Package\AbstractAppModule;
use Ray\Di\Scope;

class ProdModule extends AbstractModule
{
    protected function configure()
    {
        // memcache
        // {host}:{port}:{weight},...
        $memcachedServers = 'mem1.domain.com:11211:33,mem2.domain.com:11211:67';
        $this->install(new StorageMemcachedModule($memcachedServers));
        
        // Prodロガーのインストール
        $this->install(new ProdLoggerModule);
        
        // デフォルトのProdModuleのインストール
        $this->install(new PackageProdModule);
    }
}
```

### Redis

```php
// redis
$redisServer = 'localhost:6379'; // {host}:{port}
$this->install(new StorageRedisModule($redisServer));
```

リソースの状態保存は単にTTLによる時間更新のキャッシュとの他に、TTL時間では消えない永続的なストレージとして（CQRS）の運用も可能です。その場合には`Redis`で永続処理を行うか、Cassandraなどの他KVSのストレージアダプターを独自で用意する必要があります。

### キャッシュ時間の指定

デフォルトのTTLを変更する場合`StorageExpiryModule`をインストールします。

```php
// Cache time
$short = 60;
$medium = 3600;
$long = 24 * 3600;
$this->install(new StorageExpiryModule($short, $medium, $long));
```

### キャッシュバージョンの指定

リソースのスキーマが変わり、互換性が失われる時にはキャッシュバージョンを変更します。特にTTL時間で消えないCQRS運用の場合に重要です。

```php
$this->install(new CacheVersionModule($cacheVersion));
```

ディプロイの度にリソースキャッシュを破棄するためには`$cacheVersion`に時刻や乱数の値を割り当てると変更が不要で便利です。

## ログ

`ProdLoggerModule`はプロダクション用のリソース実行ログモジュールです。インストールするとGET以外のリクエストを`Psr\Log\LoggerInterface`にバインドされているロガーでログします。

特定のリソースや特定の状態でログしたい場合は、カスタムのログを[BEAR\Resource\LoggerInterface](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/LoggerInterface.php)にバインドします。

```php
use BEAR\Resource\LoggerInterface;
use Ray\Di\AbstractModule;

final class MyProdLoggerModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(LoggerInterface::class)->to(MyProdLogger::class);
    }
}
```

[LoggerInterface](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/LoggerInterface.php)の`__invoke`メソッドでリソースのURIとリソース状態が`ResourceObject`オブジェクトとして渡されるのでその内容で必要な部分をログします。作成には[既存の実装 ProdLogger](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/ProdLogger.php)を参考にしてください。

## デプロイ

### ⚠️ 上書き更新を避ける

#### サーバーにディプロイする場合

* 駆動中のプロジェクトフォルダを`rsync`などで上書きするのはキャッシュやオンデマンドで生成されるファイルの不一致や、高負荷のサイトではキャパシティを超えるリスクがあります。安全のために別のディレクトリでセットアップを行い、そのセットアップが成功すれば切り替えるようにします。
* [Deployer](http://deployer.org/)の[BEAR.Sundayレシピ](https://github.com/bearsunday/deploy)を利用することができます。

#### クラウドにディプロイする時には
* コンパイルが成功すると0、依存関係の問題を見つけるとコンパイラはexitコード1を出力します。それを利用してCIにコンパイルを組み込むことを推奨します。

### コンパイル

推奨セットアップを行う際に`vendor/bin/bear.compile`スクリプトを使ってプロジェクトを**ウォームアップ**することができます。コンパイルスクリプトはDI/AOP用の動的に作成されるファイルやアノテーションなどの静的なキャッシュファイルを全て事前に作成し、最適化されたautoload.phpファイルとpreload.phpを出力します。

* コンパイルをすれば全てのクラスでインジェクションを行うのでランタイムでDIのエラーが出る可能性が極めて低くなります。
* `.env`には含まれた内容はPHPファイルに取り込まれるのでコンパイル後に`.env`を消去可能です。コンテントネゴシエーションを行う場合など（例：api-app, html-app）1つのアプリケーションで複数コンテキストのコンパイルを行うときにはファイルの退避が必要です。

```bash
mv autoload.php api.autoload.php
```

`composer.json`を編集して`composer compile`の内容を変更します。

### autoload.php

`{project_path}/autoload.php`に最適化されたautoload.phpファイルが出力されます。`composer dump-autoload --optimize`で出力される`vendor/autoload.php`よりずっと高速です。

注意：`preload.php`を利用する場合、ほとんどの利用クラスが読み込まれた状態で起動するのでコンパイルされた`autoload.php`は不要です。composerが生成する`vendor/autoload.php`をご利用ください。

### preload.php

`{project_path}/preload.php`に最適化されたpreload.phpファイルが出力されます。preloadを有効にするためにはphp.iniで[opcache.preload](https://www.php.net/manual/ja/opcache.configuration.php#ini.opcache.preload)、[opcache.preload_user](https://www.php.net/manual/ja/opcache.configuration.php#ini.opcache.preload-user)を指定する必要があります。

PHP 7.4でサポートされた機能ですが、`7.4`初期のバージョンでは不安定です。`7.4.4`以上の最新版を使いましょう。

例）
```ini
opcache.preload=/path/to/project/preload.php
opcache.preload_user=www-data
```

Note: パフォーマンスベンチマークは[benchmark](https://github.com/bearsunday/BEAR.HelloWorldBenchmark/wiki/Intel-Core-i5-3.8-GHz-iMac-(Retina-5K,-27-inch,-2017)---PHP-7.4.4)を参考にしてください。(2020年）

### .compile.php

実環境ではないと生成ができないクラス（例えば認証が成功しないとインジェクトが完了しないResourceObject）がある場合には、コンパイル時にのみ読み込まれるダミークラス読み込みをルートの`.compile.php`に記述することによってコンパイルをすることができます。

.compile.php

例) 例えばコンストラクタで認証が得られない場合に例外を出してしまうAuthProviderがあったとしたら以下のように空のクラスを作っておいて、.compile.phpに読み込ませます。

/tests/Null/AuthProvider.php
```php
<?php
class AuthProvider 
{  // newをするだけのdummyなので実装は不要
}
```

.compile.php
```php
<?php
require __DIR__ . '/tests/Null/AuthProvider.php'; // 常に生成可能なNullオブジェクト
$_SERVER[__REQUIRED_KEY__] = 'fake'; // 特定の環境変数がないとエラーになる場合
```

こうする事で例外を避けてコンパイルを行うことができます。他にもSymfonyのキャッシュコンポーネントはコンストラクタでキャッシュエンジンに接続を行うので、コンパイル時にはこのようにダミーのアダプターを読み込むようにしておくと良いでしょう。

tests/Null/RedisAdapter.php
```php
namespace Ray\PsrCacheModule;

use Ray\Di\ProviderInterface;
use Serializable;
use Symfony\Component\Cache\Adapter\RedisAdapter as OriginAdapter;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

class RedisAdapter extends OriginAdapter implements Serializable
{
    use SerializableTrait;

    public function __construct(ProviderInterface $redisProvider, string $namespace = '', int $defaultLifetime = 0, ?MarshallerInterface $marshaller = null)
    {
        // do nothing
    }
}
```

### module.dot

コンパイルをすると"dotファイル"が出力されるので[graphviz](https://graphviz.org/)で画像ファイルに変換するか、[GraphvizOnline](https://dreampuf.github.io/GraphvizOnline/)を利用すればオブジェクトグラフを表示することができます。スケルトンの[オブジェクトグラフ](/images/screen/skeleton.svg)もご覧ください。

```bash
dot -T svg module.dot > module.svg
```

## ブートストラップのパフォーマンスチューニング

[immutable_cache](https://pecl.php.net/package/immutable_cache)は、不変の値を共有メモリにキャッシュするためのPECLパッケージです。APCuをベースにしていますが、PHPのオブジェクトや配列などの不変の値を共有メモリに保存するため、APCuよりも高速です。また、APCuでもimmutable_cacheでも、PECLの[Igbinary](https://www.php.net/manual/ja/book.igbinary.php)をインストールすることでメモリ使用量が減り、さらなる高速化が期待できます。

現在、専用のキャッシュアダプターなどは用意されていません。[ImmutableBootstrap](https://github.com/koriym/BEAR.Hello/commit/507d1ee3ed514686be2d786cdaae1ba8bed63cc4)を参考に、専用のBootstrapを作成し呼び出してください。初期化コストを最小限に抑え、最大のパフォーマンスを得ることができます。

### php.ini
```ini
// エクステンション
extension="apcu.so"
extension="immutable_cache.so"
extension="igbinary.so"

// シリアライザーの指定
apc.serializer=igbinary
immutable_cache.serializer=igbinary
```
