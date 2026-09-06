---
layout: docs-ja
title: 並列リソース実行
category: Manual
permalink: /manuals/1.0/ja/async.html
---

# 並列リソース実行 <sup style="font-size:0.5em; color:#666; font-weight:normal;">Alpha</sup>

BEAR.Asyncはこれまで逐次取得されていた`#[Embed]`埋め込みリソースを透過的に並列実行します。リソースのコードに手を入れることなく、並列実行用の起動スクリプトを用意するだけで、埋め込みリソースは自動的に並列取得に切り替わります。

## 概要

標準のBEAR.Sundayでは`#[Embed]`リソースは順次取得されますが、BEAR.Asyncでランタイム環境を選択すると並列に取得されます。

```text
[順次実行]                       [並列実行]
Request                          Request
    │                                │
    ├── Embed 1 ──── 50ms            ├── Embed 1 ──┬── 50ms
    ├── Embed 2 ──── 50ms            ├── Embed 2 ──┤
    ├── Embed 3 ──── 50ms            ├── Embed 3 ──┤
    └── Embed 4 ──── 50ms            └── Embed 4 ──┘
    │                                │
Response (200ms)                 Response (50ms)
```

## インストール

```bash
composer require bear/async
```

## ランタイム環境

サーバー構成に応じて適切なランタイム環境を選択します。

| 用途 | エントリポイント | ランタイム設定 |
|-----|-----|-----|
| PHP-FPM / Apache（埋め込みリソースあり） | `bin/async.php` | ライブラリの`bootstrap.php`が`AppModule`に並列ランタイムを重ねる |
| Swoole HTTPサーバー | `bin/swoole.php` | `swoole`コンテキストモジュールが`AsyncSwooleModule`をインストールする。`AppModule`は変えない |

| | ext-parallel | ext-swoole / ext-openswoole |
|---|---|---|
| 並行の単位 | スレッドプール（CPUコア数） | コルーチン（数千） |
| メモリ | ワーカーごとに独立 | プロセス内で共有 |
| PDO | スレッドごとに独立 | 接続プールが要る |
| サーバー | PHP-FPM / Apache | Swoole HTTPサーバー |
| 導入 | `bin/async.php`を追加 | `bin/swoole.php`を追加 |

### 並列実行（ext-parallel）

PHP-FPM / Apache上で動作する一般的なWebアプリケーション向けのランタイム環境です。ext-parallelのスレッドプールを使って`#[Embed]`を並列実行します。

`bin/app.php`の隣に`bin/async.php`を追加します。このエントリポイントはライブラリの`bootstrap.php`に処理を委譲し、通常の`AppModule`の上にext-parallelランタイムを重ねます。

```text
bin/async.php → vendor/bear/async/bootstrap.php → AppModule + 並列ランタイム
```

```php
<?php // bin/async.php

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';

$bootstrap = dirname(__DIR__) . '/vendor/bear/async/bootstrap.php';
if (! file_exists($bootstrap)) {
    throw new LogicException('"bear/async" is not installed.');
}

$defaultContext = PHP_SAPI === 'cli' ? 'cli-hal-api-app' : 'hal-api-app';
$context = getenv('APP_CONTEXT') ?: $defaultContext;

exit((require $bootstrap)(
    $context,
    'MyVendor\MyApp',
    dirname(__DIR__),
    $GLOBALS,
    $_SERVER,
));
```

ワーカープールのサイズ（デフォルトはCPUコア数）を変更したい場合は、第6引数として明示的に指定します。

```php
exit((require $bootstrap)($context, 'MyVendor\MyApp', dirname(__DIR__), $GLOBALS, $_SERVER, 8));
```

並列ランタイムを`AppModule`に直接インストールしないでください。bootstrap経由だけがサポートされる導入経路で、だからこそ同じ`AppModule`が`bin/app.php`（逐次）でも`bin/async.php`（並列）でも変更なしで動きます。

PHP-FPM / Apacheでは`parallel\Runtime`のプールはプロセスの状態として持たれるため、プールを必要とするリクエストごとに作り直されます。スレッド生成、オートロード、DIコンテナ構築のコストはリクエストごとにかかります。`ParallelAsync`は最初のディスパッチ前にワーカーを1つ同期的に温めるので、コンテナ構築はスレッドごとではなく1回で済みますが、定常状態の低レイテンシにはプールをリクエスト間で保持する常駐プロセスが要ります。[`demo/bin/parallel-server.php`](https://github.com/bearsunday/BEAR.Async/blob/1.x/demo/bin/parallel-server.php)と[ベンチマーク結果](https://github.com/bearsunday/BEAR.Async/blob/1.x/docs/benchmark-results.md)を参照してください。

#### ext-parallelの制約

ワーカーは別スレッドで動作し、それぞれ独立したZendメモリ空間を持ちます。並列実行する埋め込みリソースは、順序に依存しない読み取り専用（冪等なGET）リソースにしてください。各ワーカーは独自のDIコンテナを持つため、リクエストローカルな可変状態や「同一インスタンスである」という前提はスレッド境界を越えて引き継がれません。

スレッド境界をまたぐ引数と戻り値はコピー可能でなければなりません。具体的にはスカラー値、`null`、それらをネストした配列です。オブジェクト、クロージャ、リソースを渡すと`NonCopyablePayloadException`で即座に失敗します。並列実行される埋め込みリソースに適用するインターセプターは冪等に保ち、リクエストローカルな共有状態を書き換えないでください。

### Swoole実行（ext-swoole）

すでにSwoole HTTPサーバー上で稼働しており、高い並行性能を求めるアプリケーション向けのランタイム環境です。

ext-parallelはワーカースレッドで動くので別エントリポイントで選びます。ext-swooleはサーバープロセスの中で動くので、コンテキスト文字列で選びます。`swoole`コンテキストモジュールを追加し、`prod-swoole-hal-api-app`のようなコンテキストで起動します。`AppModule`はそのままで、開発時は`bin/app.php`と`hal-api-app`で今までどおり逐次実行できます。

```php
namespace MyVendor\MyApp\Module;

use BEAR\Async\Module\AsyncSwooleModule;
use BEAR\Async\Module\PdoPoolEnvModule;
use Ray\Di\AbstractModule;

final class SwooleModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new AsyncSwooleModule());
        $this->install(new PdoPoolEnvModule('PDO_DSN', 'PDO_USER', 'PDO_PASSWORD'));
    }
}
```

コンテキストモジュールは`AppModule`を外側から包むので、その束縛はフレームワーク側の逐次実行の`LinkCrawler`と`EmbedInterceptor`より優先されます。代わりに`AsyncSwooleModule`を`AppModule`の中にインストールしないでください。Ray.Diは最初に登録された束縛を採用するため、`PackageModule`の後にインストールすると非同期の束縛は何も告げずに捨てられます。

Swooleサーバーはコンパイル済みインジェクターで動かします。リフレクションで解決する`Injector`は解決中の状態をプロセス全体で共有しているため、provider内でコルーチンが待ちに入る（プールの接続待ちなど）と別のコルーチンが同じ状態に入り込み、循環していない連なりで`CircularDependency`が投げられます。`BEAR\Package\Injector`から起動して本番コンテキストではRay.Compilerの`CompiledInjector`を受け取り、サーバーがリクエストを受け付ける前に`warmup()`を呼んでコルーチンが1つしかないうちにsingletonを作り切ります。手順は[`demo/bin/swoole.php`](https://github.com/bearsunday/BEAR.Async/blob/1.x/demo/bin/swoole.php)に、契約は[Ray.Di: コルーチンサーバー](https://ray-di.github.io/manuals/1.0/ja/performance_boost.html)にあります。

Swooleではコルーチン同士がメモリを共有するため、`PdoPoolEnvModule`による接続プールが必要です。読み取り中心で埋め込みリソースを多用する構成では、外部から到達するHTTPリクエスト数だけでなく、1リクエスト内で同時に実行される埋め込みの数も加味してプールサイズを見積もります。各コルーチンは終了まで接続を1本持ち、親リクエストも埋め込みの実行中は自分の接続を持ったままなので、`PDO_POOL_SIZE >= (1 + embed_count) * request_concurrency` を出発点にし、DBへの同時接続数を抑えたい場合はあえて小さめに設定します。

`PdoPoolModule` / `RedisPoolModule`とそのenv版は`borrowTimeout`（既定5.0秒）を受け取ります。空きのないプールを待つと、いつまでもブロックする代わりに`PoolTimeoutException`で失敗します。貸し出しのたびにまず疎通を確認し（PDOは`SELECT 1`、Redisは`PING`）、死んだ接続は捨てて1回だけ取り直します。取り直した接続も死んでいれば、ドライバのエラーをpreviousに付けた`StalePooledConnectionException`が投げられます。Redis接続もPDOと同じくコルーチンごとにキャッシュされるので、同じコルーチン内で何度注入しても貸し出しは1回です。

`ExtendedPdoInterface`、`PDO`、`Redis`をsingletonに注入しないでください。providerが渡すのは1つのコルーチンのために借りた接続で、singletonがそれを掴むとプロセスの寿命の間ずっと握り続け、プールが意味をなさずコルーチン間で接続が混ざります。DBを使う依存はprototypeスコープ（既定）のままにするか、`ProviderInterface<ExtendedPdoInterface>`を注入して使うたびに`get()`を呼びます。

> **技術ノート（プール接続の取得方式）:** プールからの接続取得はコルーチン単位で管理されます。同じコルーチン内で`PDO`と`ExtendedPdo`の両方が注入された場合でも、両者は同一の接続を共有し、コルーチン終了時に`Coroutine::defer()`で一度だけプールへ返却されます。これにより、1つの処理が意図せず2本の接続を握ることを防ぎます。さらに`#[Embed]`で埋め込まれたリクエストは遅延評価されるため、埋め込みリソースを`#[Embed]`で宣言した時点ではプールから接続を確保せず、各リクエストが実際に実行される時点まで取得を遅らせます。
>
> **技術ノート（PDOProxyの扱い）:** Swooleはコルーチン対応のために`PDO`を独自に`PDOProxy`でラップしますが、BEAR.Asyncはこのラップを内部で吸収して通常の`PDO`として扱えるようにします。何らかの理由で元の`PDO`を取り出せない場合は、リフレクション失敗をそのまま伝播させず、PDOプロキシ抽出専用のドメイン例外として扱います。

Swooleのコルーチンと有効化されたXdebugを併用すると安全に動作しません。Swoole用のエントリポイントはXdebugを読み込まないPHPで実行するか、ローカル確認時には`XDEBUG_MODE=off`を設定してください。

## 使用方法

ランタイム環境を選択すると、既存の`#[Embed]`リソースは自動的に並列実行されます。

```php
class Dashboard extends ResourceObject
{
    #[Embed(rel: 'user', src: '/user{?id}')]
    #[Embed(rel: 'notifications', src: '/notifications{?user_id}')]
    #[Embed(rel: 'stats', src: '/stats{?user_id}')]
    public function onGet(string $id): static
    {
        $this->body['id'] = $id;
        return $this;
    }
}
```

開発環境では`bin/app.php`で同期実行してデバッグし、本番環境では`bin/async.php`から起動して並列実行に切り替えます。

## なぜコード変更なしで動くのか

BEAR.Sundayでは、情報がリソースとして URI で**構造化**されています。`#[Embed]`はそのリソースの実行結果ではなく、リソースリクエストそのものを埋め込み、リソース間の関係を宣言します。どの実行戦略（逐次、ext-parallelワーカー、Swooleコルーチン）を使うかを決めるのは Linker で、リソースクラスは自分が同期で呼ばれたか並列で呼ばれたかを知る必要がありません。

通常モードではレンダリング時にこれらのリクエストが1つずつ逐次解決されますが、並列実行モードでは、最初の埋め込みリクエストが解決される時点で残りの埋め込みリクエストもまとめて並列に実行されます。BEAR.Asyncの非同期リクエストはBEAR.Resourceの通常リクエストと同じ型として扱えるため、HALレンダラなど周辺の仕組みはこの差を意識せずシリアライズに統合できます。

非同期プログラミングには「関数の色」問題があります。非同期関数を呼ぶ関数は自身も非同期でなければならず、非同期がコード全体に広がっていくというものです。BEAR.Sundayではリソースが境界になるため、これが起きません。同期と並列でコードは同じで、変わるのは実行戦略だけです。

これはBEAR.Async固有ではなく、BEAR.Sunday全体の性質です。MVCフレームワークが「どう実行するか」を手続きで書く箇所を、BEAR.Sundayはリソース間の関係を宣言として表します。宣言は実行戦略から独立しているため、戦略の差し替えはコードに影響しません。

## 仕組み

BEAR.Asyncはbear/resourceの束縛を2つ差し替えます。

1. `LinkCrawlerInterface` → `AsyncLinkCrawler`: `#[Link(crawl:)]`のグラフを階層ごとに辿ります。各階層のリクエストをまとめ、URI+クエリのハッシュで重複を除き、一括で実行して、結果を要求元すべてに配ります。
2. `EmbedInterceptorInterface` → `AsyncEmbedInterceptor`: 各`#[Embed]`を`DeferredRequest`の上の`AsyncRequest`で包んで`PendingRequests`に登録します。どれか1つが最初にレンダリングされた時点で、溜まっている埋め込み全部を1バッチで実行します。

どちらも設定された`AsyncInterface`アダプター（`ParallelAsync`、`SwooleAsync`、ランタイムモジュール未導入時の既定である`SyncAsync`）にバッチを渡します。

```text
階層1: Users → 全ユーザーのリクエストを並列実行
階層2: 各ユーザーのPosts → 全投稿のリクエストを並列実行
階層3: 各投稿のComments → 全コメントのリクエストを並列実行
```

### 失敗時の挙動

埋め込みやクロールのタスクが1つ失敗しても、Swooleワーカーは落ちず、兄弟タスクも中断されません。全タスクが完了してから最初の例外が呼び出し元に投げ直され、そのリクエストだけが500になります。`ParallelAsync`も同じ規則で、ディスパッチした全`Future`をjoinしてから最初の`Throwable`を投げ直します。

黙って代替に落ちることはありません。必要な拡張がロードされていなければ、該当モジュールが`configure()`の時点で`ExtensionNotLoadedException`を投げます。`SyncAsync`への降格はしません。

## デモとベンチマーク

BEAR.AsyncリポジトリにはSync・ext-parallel・Swooleの動作を比較できる、Dockerベースのデモとベンチマークスクリプトが含まれています。詳細は[デモガイド](https://github.com/bearsunday/BEAR.Async/tree/1.x/demo)と[ベンチマーク結果](https://github.com/bearsunday/BEAR.Async/blob/1.x/docs/benchmark-results.md)を参照してください。

## 動作要件

各ランタイム環境は対応するPHP拡張を必要とします。

| ランタイム環境 | 必要なもの | アプリケーション側の変更 |
|-----|-----|-----|
| ext-parallel | ZTS PHP + ext-parallel | `bin/async.php`を追加 |
| ext-swoole | ext-swoole または ext-openswoole | `swoole`コンテキストモジュールを追加、`bin/swoole.php`を使用 |

## SQLリソースの並列化（BDR + `#[Embed]`）

1ページで複数のSQLを発行したい場合、SQLごとにResourceObjectを分け、上位リソースから`#[Embed]`で束ねます。`#[Embed]`はランタイムに応じて並列実行されるので、利用側はリソースを組み合わせるだけで並列化されます。

Ray.MediaQueryの[BDRパターン](https://github.com/ray-di/Ray.MediaQuery/blob/1.x/BDR_PATTERN.md)（`#[DbQuery]`インターフェース + ファクトリ + 不変ドメインオブジェクト）と組み合わせると、SQLは`var/sql/*.sql`にまとまり、リソース側はドメインオブジェクトを扱うだけになります。

レシピ依存（BEAR.Asyncには同梱されません）:

```bash
composer require ray/media-query
```

```php
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;
use Ray\MediaQuery\Annotation\DbQuery;

// ドメインオブジェクト: 不変スナップショット
final class UserAccount
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {
    }
}

// リポジトリ: SQLは var/sql/user.sql に置く
// UserFactoryで行をUserAccountにハイドレートする（ファクトリの詳細はBDR_PATTERN.md参照）
interface UserRepositoryInterface
{
    #[DbQuery('user', factory: UserFactory::class)]
    public function getUser(int $id): UserAccount;
}

// リソース: SQL 1つにつき1リソース
class User extends ResourceObject
{
    public function __construct(private UserRepositoryInterface $repo)
    {
    }

    public function onGet(int $id): static
    {
        $this->body = ['user' => $this->repo->getUser($id)];

        return $this;
    }
}

// 集約リソース: `#[Embed]` がBEAR.Asyncで自動的に並列化される
class UserDashboard extends ResourceObject
{
    #[Embed(rel: 'user',     src: 'app://self/user{?id}')]
    #[Embed(rel: 'posts',    src: 'app://self/user/posts{?id}')]
    #[Embed(rel: 'comments', src: 'app://self/user/comments{?id}')]
    public function onGet(int $id): static
    {
        return $this;
    }
}
```

- SQLは`var/sql/*.sql`に置く（Ray.MediaQueryの規約）
- ドメインオブジェクトは不変スナップショット。呼び出し側で `$results['user'][0] ?? null` のような配列のお作法は不要
- BEAR.Asyncが3つのEmbedをext-parallel（PHP-FPM / Apache）またはSwooleコルーチンで並列実行
- ext-parallel/Swooleなしでも、同じコードがリクエストごとに同期実行される（PHP-FPMはリクエスト＝プロセスなので機能としては問題なし）
- Swooleで動かす場合は`PdoPoolEnvModule`をインストールし、各コルーチンがプールからPDO接続を借りるようにする

## 参考リンク

- [BEAR.Async](https://github.com/bearsunday/BEAR.Async)
- [BEAR.Async デモガイド](https://github.com/bearsunday/BEAR.Async/tree/1.x/demo)
- [BEAR.Async ベンチマーク結果](https://github.com/bearsunday/BEAR.Async/blob/1.x/docs/benchmark-results.md)
- [Ray.MediaQuery BDRパターン](https://github.com/ray-di/Ray.MediaQuery/blob/1.x/BDR_PATTERN.md)
- [並列実行アーキテクチャ](https://bearsunday.github.io/BEAR.Async/parallel-execution-architecture.html)
