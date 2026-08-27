---
layout: docs-ja
title: プロダクション
category: Manual
permalink: /manuals/1.0/ja/production.html
---

# プロダクション

BEAR.Sundayの既定の`prod`束縛をベースに、アプリケーション側で各[デプロイ環境](https://en.wikipedia.org/wiki/Deployment_environment)に応じたモジュールを追加・上書きしてカスタマイズします。

## 既定のProdModule

既定の`prod`束縛では、以下のインターフェイスが束縛されています。

* エラーページ生成ファクトリー
* PSRロガーインターフェース
* ローカルキャッシュ
* リソースキャッシュ（クエリーリポジトリー）

また、OPTIONSメソッドは無効化されます。詳細はBEAR.Packageの[ProdModule.php](https://github.com/bearsunday/BEAR.Package/blob/1.x/src/Context/ProdModule.php)を参照してください。

## アプリケーションのProdModule

既定のProdModuleに対してアプリケーションの`ProdModule`を`src/Module/ProdModule.php`に設置してカスタマイズします。

```php
<?php

namespace MyVendor\MyProject\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\Package\Provide\Error\ErrorPageFactoryInterface;
use BEAR\QueryRepository\CacheVersionModule;
use BEAR\Resource\Module\OptionsMethodModule;
use Ray\Di\AbstractModule;

class ProdModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new PackageProdModule);       // 既定のprod束縛
        $this->override(new OptionsMethodModule);    // OPTIONSメソッドをプロダクションでも有効に
        $this->install(new CacheVersionModule('1')); // リソースキャッシュのバージョン指定

        // 独自のエラーページ
        $this->bind(ErrorPageFactoryInterface::class)->to(MyErrorPageFactory::class);
    }
}
```

デプロイ環境に応じてこのモジュールに足すものが、このページの残りです。

* [分散キャッシュ](#cache) — 2台以上のWebサーバーで必要
* [キャッシュバージョン](#cache-version) — リソースのスキーマ変更時にキャッシュを破棄
* [リソース実行ログ](#logging)
* [エラーログのファイル出力](#error-log)
* [書き込み先の宣言](#writable-paths) — サーバーレスや読み取り専用コンテナで必要

## キャッシュ {#cache}

キャッシュには、ローカルキャッシュと複数のWebサーバー間で共有する分散キャッシュの2種類があります。

### ローカルキャッシュ

ローカルキャッシュはアノテーションなど、デプロイ後に変更されないもののキャッシュに使われます。[APCu](https://www.php.net/manual/ja/book.apcu.php)が有効ならAPCuに、なければファイルに書かれます。

### 分散キャッシュ

リソース状態の保存に使われます。2つ以上のWebサーバーでサービスを提供するには分散キャッシュの構成が必要です。代表的なキャッシュエンジンである[memcached](https://www.php.net/manual/ja/book.memcached.php)や[Redis](https://redis.io)向けのモジュールが用意されています。

#### Memcached

```php
// {host}:{port}:{weight},...
$memcachedServers = 'mem1.domain.com:11211:33,mem2.domain.com:11211:67';
$this->install(new StorageMemcachedModule($memcachedServers));
```

#### Redis

```php
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

### キャッシュバージョンの指定 {#cache-version}

リソースのスキーマが変わり、互換性が失われる時にはキャッシュバージョンを変更します。特にTTL時間で消えないCQRS運用の場合に重要です。

```php
$this->install(new CacheVersionModule($cacheVersion));
```

デプロイの度にリソースキャッシュを破棄するためには`$cacheVersion`に時刻や乱数の値を割り当てると変更が不要で便利です。

## ログ {#logging}

`ProdLoggerModule`はプロダクション用のリソース実行ログモジュールです。インストールするとGET以外のリクエストを`Psr\Log\LoggerInterface`にバインドされているロガーでログします。

```php
$this->install(new ProdLoggerModule);
```

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

### エラーログ {#error-log}

捕捉されなかったエラーはレスポンスに`logref` IDを付与し、整形された例外を`Psr\Log\LoggerInterface`にバインドされたロガーへ送ります（独自のロガーをバインドしていなければPHPの`error_log`）。既定では`var/log/{context}`に`logref.{id}.log`ファイルも書かれ、`last.logref.log`が最新のものを指します。`ProdModule`は代わりに`NullLogRefWriter`をバインドするため、プロダクションではファイルを書きません。エラーごとのファイルを残すには、アプリケーションの`ProdModule`で`LogRefWriterInterface`を`FileLogRefWriter`にバインドします。

## デプロイ

デプロイの戦略は2つです。

### 完全な事前コンパイル（推奨） {#aot-compile}

ビルドで[コンパイル](#compilation-recommended)し、コンパイル済みの成果物を配布します。特徴はコールドスタートの速さ（起動は成果物を読むだけ）、スケール（増えるインスタンスはすべて同じ成果物から起動）、安全性（DIの構成エラーはビルドのexitコード1で止まり、本番に届きません）です。

成果物の代表的な形は2つです。コンテナパイプラインがあるなら[Dockerマルチステージビルド](#docker-multi-stage)で、コンパイルをイメージのビルドに固定します。パイプラインのない環境では[Phar](phar.html)がこの形を1ファイルで実現します。デプロイは1ファイルのコピーで、ロールバックは1つ前のファイルです。成果物の同一性と安全性を、CIの規律ではなくパックが強制します。

* CIではコンパイルの終了コードを合否判定に使います
* ランタイムで変わる値（接続先・トークン等）は焼き込まず、ランタイム解決にします
* ビルド環境から触れないサービスは[`.compile.php`](#compile-php)でスタブします
* 読み取り専用の実行環境では[書き込み先の宣言](#writable-paths)を併用します

### ヘルスチェック時のオンデマンドコンパイル {#health-check-compile}

コンパイルせずに配布し、デプロイ先でコンパイルします。特徴は、パスや環境変数など実環境の値がそのまま使われることです。ヘルスチェック（warmup）のタイミングで`composer compile`を実行するか、最初の起動に任せます。書き込めるツリーなら未コンパイルの成果物はその場でコンパイルされ、`Compiled DI scripts on demand`のnoticeが出ます。リリースは別のディレクトリに展開し、ヘルスチェックが通ってからトラフィックを向けます。インスタンスごとにコンパイルが走るため、成果物の同一性は事前コンパイルに劣ります。

<a id="compilation"></a>
### コンパイル {#compilation-recommended}

コンパイルはDI/AOP用の動的ファイルやアノテーションなどの静的キャッシュを事前に作成し、最適化された`autoload.php`と`preload.php`を出力します。

ビルドスクリプトはアプリケーションを**名乗るだけ**で、起動はしません（BEAR.Package 1.22以降。スケルトンは`bin/compile.php`）。

```php
<?php
// bin/compile.php
use BEAR\Package\Compiler;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('memory_limit', '-1');

$context = $argv[1] ?? 'prod-app';

exit((new Compiler('MyVendor\MyProject', $context, dirname(__DIR__)))());
```

スクリプトが名乗るのはアプリケーション名、context、アプリケーションのディレクトリで、アプリケーションは起動しません。`.compile.php`のビルド用スタブはCompiler自身が読み込みます。`Compiler::phar()`はコンパイル結果を1つのアーカイブにします（BEAR.Package 1.24以降）→ [Phar](phar.html)

```json
"scripts": {
    "compile": "php bin/compile.php"
}
```

* コンパイルをすれば全てのクラスでインジェクションを行うのでランタイムでDIのエラーが出る可能性が極めて低くなります。
* `.env`に含まれた内容はPHPファイルに取り込まれるのでコンパイル後に`.env`を消去可能です。

DIスクリプトの出力先は`{appDir}/var/build/{context}/di`です。ビルドディレクトリにはコンパイルが作ったものだけが入り、リクエストが書くものは入りません。だから読み取り専用で配れます。成果物に同梱されていれば実行時はコンパイルせず読むだけです。

`vendor/bin/bear.compile` は BEAR.Package 1.24 で削除されました。移行手順は [BEAR.Package#482](https://github.com/bearsunday/BEAR.Package/issues/482) を参照してください。

#### 複数コンテキストのコンパイル {#multiple-contexts}

コンテントネゴシエーションを行う場合など（例：api-app, html-app）1つのアプリケーションで複数コンテキストをコンパイルするときは、スクリプト内のループにします。`autoload.php`と`preload.php`は固定パスに書かれ、次のコンパイルで消えるので、その都度 rename します。

```php
// bin/compile.php
$appDir = dirname(__DIR__);

foreach (['prod-hal-api-app', 'prod-html-app'] as $context) {
    $code = (new Compiler('MyVendor\MyProject', $context, $appDir))();
    if ($code !== 0) {
        exit($code);
    }

    foreach (['preload.php', 'autoload.php'] as $written) {
        if (! rename($appDir . '/' . $written, $appDir . '/' . $context . '.' . $written)) {
            exit(1);
        }
    }
}

exit(0);
```

[`opcache.preload`](https://www.php.net/manual/ja/opcache.preloading.php) は PHP プロセス単位の設定です。複数コンテキストを preload する場合は**それぞれ別プロセス（php-fpm プール等）**になり、プロセスごとに退避した preload を指します（例：api 用プールは `opcache.preload=/path/to/prod-hal-api-app.preload.php`、html 用プールは `/path/to/prod-html-app.preload.php`）。

context ごとにアーカイブにする場合はループが別で、preloadのrenameもしません → [Phar](phar.html)

#### compile step {#compile-steps}

モジュールは compile step（`BEAR\Sunday\Compile\CompileStepInterface`）をバインドでき、コンパイルがそれを実行します。step にはビルドディレクトリの下に自分専用の空のディレクトリが渡されます。名前はバインディングのキーで、書いたものは成果物に同梱されます。

```text
{appDir}/var/build/{context}/di        コンパイル済みDIスクリプト
{appDir}/var/build/{context}/qiq       Qiqがコンパイルしたテンプレート
{appDir}/var/build/{context}/twig      Twigのキャッシュ
```

テンプレートエンジンがこれを使います。最初のリクエストでコンパイルするものは残っておらず、そのためにアプリケーションルート配下を書き込み可能にする必要もありません。step が失敗するとコンパイルマーカーが残らないので、テンプレートの無いビルドを配信するのではなく、次の起動が再びコンパイルします。

bear/sunday 1.9以降が必要です。背景: [BEAR.Package#501](https://github.com/bearsunday/BEAR.Package/pull/501)

#### .compile.php {#compile-php}

実環境ではないと生成ができないクラス（例えば認証が成功しないとインジェクトが完了しないResourceObject）がある場合には、コンパイル時にのみ読み込まれるダミークラス読み込みをルートの`.compile.php`に記述することによってコンパイルをすることができます。**目的は「コンパイル時に構築を通す」ことなので、中身は Null オブジェクト（何もしない実装）が基本**です。これは事前コンパイル（実サービスに触れない）だけでなく、**認証などリクエスト時の状態が要るために、デプロイ先でコンパイルしても構築できない**リソースにも当てはまります。値の偽装（`$_SERVER['X'] = 'fake'` など）は最小限にとどめ、ランタイムで本物が要る値には使わないでください（焼き込まれます）。

**注意**: `.compile.php` はCompiler自身が、コンテナを組む前に読み込みます。削除された `vendor/bin/bear.compile` も読み込んでいました。アプリ側で何かする必要はありません。

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
$_SERVER['YOUR_REQUIRED_ENV'] = 'fake'; // 特定の環境変数がないとエラーになる場合
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

#### autoload.php {#autoloadphp}

`{project_path}/autoload.php`に最適化されたautoloadファイルが出力されます。中身は、コンパイルで実際に使われたクラスファイルを並べた`require`のリストで、残りはcomposerのオートローダーに委ねます。アプリケーションが使うクラスにはオートローダーの解決そのものが起きません。`composer dump-autoload --optimize`はクラスマップの検索を速くしますが、クラスごとのオートローダー呼び出しは残ります。この`autoload.php`はそれを消すので、**preloadを使わない構成**でリクエストごとのautoloadコストを下げます。

注意：`preload.php` を使う場合は利用クラスの大半が起動時に読み込まれるので、この `autoload.php` はほぼ不要です（composer の `vendor/autoload.php` で十分）。つまり `autoload.php` は **preload を使えない環境向けのフォールバック**という位置づけです。

#### preload.php {#preloadphp}

`{project_path}/preload.php`に最適化されたpreload.phpファイルが出力されます。preloadを有効にするためにはphp.iniで[opcache.preload](https://www.php.net/manual/ja/opcache.configuration.php#ini.opcache.preload)、[opcache.preload_user](https://www.php.net/manual/ja/opcache.configuration.php#ini.opcache.preload-user)を指定する必要があります。

例）
```ini
opcache.preload=/path/to/project/preload.php
opcache.preload_user=www-data
```

#### module.dot {#module-dot}

コンパイルをすると"dotファイル"が出力されるので[graphviz](https://graphviz.org/)で画像ファイルに変換するか、[GraphvizOnline](https://dreampuf.github.io/GraphvizOnline/)を利用すればオブジェクトグラフを表示することができます。スケルトンの[オブジェクトグラフ](/images/screen/skeleton.svg)もご覧ください。

```bash
dot -T svg module.dot > module.svg
```

### 読み取り専用デプロイ（サーバーレス、イミュータブルコンテナ） {#writable-paths}

サーバーレスやイミュータブルコンテナでは書き込めるディレクトリが制限されることがあります。VercelやAWS Lambda、`docker run --read-only`や`readOnlyRootFilesystem: true`で起動したコンテナでは、プロジェクトのディレクトリは読み取り専用で、書き込めるのは`/tmp`など1つのディレクトリだけです。通常のVPSや共有ホストでは不要です。

アプリケーションは自身の`ProdModule`で書き込み先を宣言します。

```php
<?php
// src/Module/ProdModule.php
namespace MyVendor\MyProject\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\Package\Module\ReadOnlyAppModule;
use Ray\Di\AbstractModule;

class ProdModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new ReadOnlyAppModule());
        $this->install(new PackageProdModule());
    }
}
```

省略したときは、起動したマシンの一時ディレクトリ（[`sys_get_temp_dir()`](https://www.php.net/sys_get_temp_dir)）の下になります。アプリケーション名・アプリケーションディレクトリ・contextで分かれます。

```text
{appDir}/var/build/{context}/di                                        コンパイル済みDIスクリプト（成果物内）
{一時ディレクトリ}/MyVendor/MyProject/{appDirハッシュ}/var/tmp/{context}   クエリリポジトリのキャッシュ
{一時ディレクトリ}/MyVendor/MyProject/{appDirハッシュ}/var/log/{context}
```

一時ディレクトリの場所はphp.iniの[`sys_temp_dir`](https://www.php.net/manual/ja/ini.core.php#ini.sys-temp-dir)で決まります。起動時に渡すこともできます。

```bash
php -d sys_temp_dir=/mnt/tmp public/index.php
```

パスにアプリケーション名とcontextが入るのは、ローカルキャッシュのキーがリソースURIだからです。区切りがないまま2つのアプリケーションや2つのcontextが同じディレクトリを共有すると、互いのキャッシュエントリで応答してしまいます。appDirハッシュも同じ理由で、同じアプリケーションの別チェックアウトを分けます。

エントリポイントに変更はありません。環境変数も要りません。ビルドと起動で合わせるものがないので、同じ成果物がどのマシンでもそのマシンの答えで起動します。

書き込み先の決め方は2つです。既定のままシステム依存にして、その依存（`sys_temp_dir`）を環境側で操作するのが基本です。php.iniも起動オプションも操作できない環境でだけ、絶対パスを渡して固定値を焼き込みます。

パスを渡すと、その通りに使われます。

```php
$this->install(new ReadOnlyAppModule('/tmp/myapp/tmp', '/tmp/myapp/log'));
```

渡した値はコンパイル時に、そのままコンテナに入ります。このビルドを起動するマシンで使える絶対パスを渡してください。書けるかどうかは、何かが書く時点でファイルシステムが答えます。

片方だけ渡すこともできます。渡さなかった方は起動時に決まります。

```php
$this->install(new ReadOnlyAppModule(logDir: '/var/log/myapp'));
```

コンパイル済みDIスクリプトは`appDir`配下に残り、デプロイ成果物に同梱されます。新しいインスタンスの一時ディレクトリは空なので、DIスクリプトまでそこに移すとコールドスタートのたびに再コンパイルになります（リソース5個のアプリケーションで、再コンパイルが0.38秒、成果物からの読み込みが0.018秒）。

コンパイルされていない成果物を起動すると`NotCompiledException`で止まります。書き込めるツリーなら、その場でコンパイルして`Compiled DI scripts on demand`のnoticeが出ます。

自身に書き込まない1ファイルの成果物にするには[Phar](phar.html)を参照してください。

BEAR.Package 1.24以降が必要です。背景: [BEAR.Package#491](https://github.com/bearsunday/BEAR.Package/pull/491)

### Dockerマルチステージビルド {#docker-multi-stage}

コンパイルはビルドの仕事です。Dockerの[マルチステージビルド](https://docs.docker.com/build/building/multi-stage/)を使うと、コンパイルをイメージのビルドに固定できます。

```dockerfile
FROM php:8.3-cli AS build
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
RUN composer install --no-dev --prefer-dist --no-progress
RUN php bin/compile.php prod-app

FROM php:8.3-apache
COPY --from=build /app /var/www/app
```

起動するイメージにはコンパイル済みのDIスクリプトが入っていて、起動時にすることが残っていません。コールドスタートは成果物を読むだけになり（上記の実測で0.38秒→0.018秒）、スケールアウトで増えるインスタンスはすべて同じ成果物から起動するので、どのコンテナも同じ答えを返します。実行ステージを`docker run --read-only`で起動するなら[書き込み先の宣言](#writable-paths)と合わせます。

これは[完全な事前コンパイル](#aot-compile)の形です。接続先やトークンなどランタイムで変わる値は焼き込まず、ランタイム解決にします。
