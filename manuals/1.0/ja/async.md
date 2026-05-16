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

## 設計思想

### URLは意図である

BEAR.Sundayにおいて、URIは単なる場所ではなく**意図**を表現します。

```php
#[Embed(rel: 'profile', src: 'query://self/user_profile{?id}')]
```

この`query://self/user_profile`は「ユーザーのプロファイル情報が欲しい」という意図だけを示しています。この「What（何を）」と「How（どう）」の分離により、同じコードが同期実行でも並列実行でも動作します。開発時は通常のPHPとしてXdebugでデバッグし、本番では`bin/async.php`から起動するだけで並列実行を有効化できます。

### 関数の色問題の解決

非同期プログラミングには「関数の色」問題があります。非同期関数を呼ぶ関数は自身も非同期でなければならず、コード全体が「非同期に汚染」されていきます。

BEAR.Sundayでは「リソース」という境界がこの問題を断ち切ります。非同期のためのコード記述は一切不要で、リソースクラスは自分がどう呼び出されたかを知る必要がありません。

## インストール

```bash
composer require bear/async
```

BEAR.Async 0.3.0 以降を推奨します。HAL/JSONシリアライズ時にも
async embed が正しく解決されるように、`bear/resource` 1.32+ に依存します。

## ランタイム環境

サーバー構成に応じて適切なランタイム環境を選択します。

| 用途 | エントリポイント | ランタイム設定 |
|-----|-----|-----|
| PHP-FPM / Apache（埋め込みリソースあり） | `bin/async.php` | ライブラリの`bootstrap.php`によるオーバーレイ |
| Swoole HTTPサーバー | `bin/swoole.php` | `AsyncSwooleModule`を`AppModule`にインストール |

### 並列実行（ext-parallel）

PHP-FPM / Apache 上で動作する典型的な Web アプリケーション向けのランタイム環境です。ext-parallel のスレッドプールで`#[Embed]`を並列実行します。

`bin/app.php`の隣に`bin/async.php`を追加します。このエントリポイントはライブラリの`bootstrap.php`に処理を委譲し、通常の`AppModule`の上に ext-parallel ランタイムをオーバーレイします。

```text
bin/async.php → vendor/bear/async/bootstrap.php → AppModule + ランタイムオーバーレイ
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

`AppModule`に並列実行用のモジュールを直接インストールしないでください。ライブラリの`bootstrap.php`経由が唯一サポートされる導入方法です。これにより、同じ`AppModule`が`bin/app.php`（同期）と`bin/async.php`（並列）の両方でそのまま動作します。

ワーカープールサイズ（デフォルトはCPUコア数）を上書きするには、
オプションの第6引数に渡します。

```php
exit((require $bootstrap)($context, 'MyVendor\MyApp', dirname(__DIR__), $GLOBALS, $_SERVER, 8));
```

#### ext-parallel の制約

ワーカーランタイムは別スレッドで、それぞれ独立した Zend メモリを持ちます。
並列実行される埋め込みリソースは、順序依存のない read-only / idempotent な
GET リソースにしてください。各ワーカーは独自の DI コンテナを持つため、
リクエストローカルな可変状態や「同一インスタンスである」という前提は
スレッド境界を越えません。

スレッド境界を越える引数と戻り値はコピー可能である必要があります。
具体的には scalar、`null`、またはそれらのネスト配列です。オブジェクト、
クロージャ、リソースは fail fast します。並列 embed グラフ内で使う
インターセプターは冪等にし、リクエストローカルな共有状態の変更を避けてください。

### Swoole実行（ext-swoole）

すでに Swoole HTTP Server で稼働しており、高い並行性能が求められるアプリケーション向けのランタイム環境です。

ext-parallel はワーカーランタイム（別スレッド）で動作するため別エントリポイントで選択しますが、ext-swoole は同一サーバプロセス内で動作するためアプリケーションモジュールとしてインストールします。

```php
use BEAR\Async\Module\AsyncSwooleModule;
use BEAR\Async\Module\PdoPoolEnvModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new AsyncSwooleModule());
        $this->install(new PdoPoolEnvModule('PDO_DSN', 'PDO_USER', 'PDO_PASSWORD'));
    }
}
```

Swooleではコルーチンがメモリを共有するため、`PdoPoolEnvModule`による接続プールが必要です。
read-heavy な embed グラフでは、HTTP の並行数だけでなく内部の embed 並列度も
考慮して pool を設定します。キューイングを避けたい場合は
`PDO_POOL_SIZE >= embed_count * request_concurrency` を出発点にし、
DB へのバックプレッシャーを意図する場合は小さめの pool を選びます。

Swoole のコルーチンと有効な Xdebug の組み合わせは安全ではありません。
Swoole エントリポイントは Xdebug をロードしない PHP で実行するか、
ローカル確認では `XDEBUG_MODE=off` を設定してください。

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

開発環境では`bin/app.php`を使用して同期実行でデバッグし、本番環境では`bin/async.php`から起動することで並列実行を有効にできます。`AppModule`は実行形態に依存しないため、同じコードがそのまま両環境で動作します。

## いつ parallel を選ぶか

複数の独立した GET リソースを `#[Embed]` で合成する read-only な
リソースグラフでは、ランタイム拡張が利用でき、下流 DB / API が追加の
並列度を吸収できる限り、parallel adapter を第一候補にできます。
これが BEAR.Async の中心的な価値です。アプリケーションコードは
`#[Embed]` でリソースグラフを宣言し、Linker の実装がそのグラフを
逐次、ext-parallel worker、Swoole coroutine のどれで解決するかを決めます。

### 前提条件

- 埋め込みリソースは read-only GET で、順序依存がない。
- 対象ランタイムで `ext-parallel` または `ext-swoole` が利用できる。
- 下流 DB / API の容量を、HTTP リクエスト並行数だけでなく内部 embed 並列度にも合わせている。
- ext-parallel の steady-state 性能は、`parallel\Runtime` pool を warm に保つ常駐プロセスで評価する。one-shot CLI は runtime 起動コストを含む cold-start 挙動として読む。

### Adapter の選び方

| 状況 | 推奨 adapter |
|---|---|
| Swoole HTTP server に乗せられ、高 throughput が必要 | Swoole adapter |
| PHP-FPM / Apache のプロセスモデルを維持し、worker が warm に保たれる | ext-parallel adapter |
| 拡張に頼れない、または portable fallback が必要 | Sync adapter |

### 利得が小さい / 出ないケース

- 下流 DB / API が pool 制限、飽和、rate limit で追加並列度を吸収できない。
- 各 embed がすでに極端に速く、固定オーバーヘッドが支配的になる。
- embed 間に実際の順序依存がある、または可変のリクエストローカル状態を共有している。
- one-shot CLI / cron-style job でも BEAR.Async は使えますが、そこで測っているのは warmed per-request latency ではなく cold-start 挙動です。

## Demo と Benchmark

BEAR.Async リポジトリには Docker ベースの demo があります。MySQL を起動し、
8つの独立した SQL-backed GET embed を持つ dashboard リソースグラフを seed し、
Sync、ext-parallel、Swoole の各 entrypoint を確認できます。

```bash
cd demo
docker compose up -d --wait parallel
docker compose exec parallel composer app -- get 'app://self/dashboard?user_id=1'
docker compose exec parallel composer async -- get 'app://self/dashboard?user_id=1'
```

demo は cold one-shot CLI ベンチと、`wrk` による steady-state HTTP ベンチを
分けています。

```bash
docker compose exec parallel composer parallel-benchmark
docker compose exec parallel composer steady-state-parallel
docker compose up -d --wait swoole
docker compose exec swoole composer swoole-benchmark
docker compose exec swoole composer steady-state-swoole
```

cold one-shot CLI は DI lookup や ext-parallel の一度きりの
`parallel\Runtime` spawn を含みます。warmed per-request 性能を評価する場合は
steady-state HTTP ベンチを使ってください。

## 動作要件

ライブラリ自体はPHP 8.2+で動作します。各ランタイム環境は対応するPHP拡張を必要とします。

| ランタイム環境 | 必要なもの | アプリケーション側の変更 |
|-----|-----|-----|
| ext-parallel | ZTS PHP + ext-parallel | `bin/async.php`を追加 |
| ext-swoole | ext-swoole | `AsyncSwooleModule`をインストール、`bin/swoole.php`を使用 |

## BEAR.Projectionとの連携

[BEAR.Projection](https://github.com/bearsunday/BEAR.Projection)は、SQLクエリ結果を型付きのProjectionオブジェクトに変換し、`query://`スキームでリソースとして公開します。`#[Embed]`と組み合わせることで、複数のSQLクエリが並列実行されます。

Projectionクラスはイミュータブルな値オブジェクトとして定義します。

```php
final class UserProfile
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $age,
        public readonly string $avatarUrl,
    ) {}
}
```

Factoryクラスでは、SQLの生データをProjectionに変換します。DIで依存を注入できるため、年齢計算やURL解決などのビジネスロジックを適用できます。

```php
final class UserProfileFactory
{
    public function __construct(
        private readonly AgeCalculator $ageCalculator,
        private readonly ImageUrlResolver $imageResolver,
    ) {}

    public function __invoke(
        string $id,
        string $name,
        string $birthDate,
        string $avatarPath,
    ): UserProfile {
        return new UserProfile(
            id: $id,
            name: $name,
            age: $this->ageCalculator->fromBirthDate($birthDate),
            avatarUrl: $this->imageResolver->resolve($avatarPath),
        );
    }
}
```

SQLファイルはFactoryのパラメータ名に対応するカラムを返します。

```sql
-- var/sql/query/user_profile.sql
SELECT id, name, birth_date, avatar_path FROM users WHERE id = :id
```

これらを`#[Embed]`で利用すると、複数のProjectionが並列実行されます。

```php
class User extends ResourceObject
{
    #[Embed(rel: 'profile', src: 'query://self/user_profile{?id}')]
    #[Embed(rel: 'orders', src: 'query://self/user_orders{?id}')]
    public function onGet(string $id): static
    {
        return $this;
    }
}
```

## SQLバッチ実行

mysqliのネイティブ非同期サポートを使用した並列SQLクエリ実行も提供します。

```php
use BEAR\Async\Module\MysqliEnvModule;

$this->install(new MysqliEnvModule(
    'MYSQLI_HOST',
    'MYSQLI_USER',
    'MYSQLI_PASSWORD',
    'MYSQLI_DATABASE',
));
```

```php
use BEAR\Async\SqlBatch;
use BEAR\Async\SqlBatchExecutorInterface;

class MyService
{
    public function __construct(
        private SqlBatchExecutorInterface $executor,
    ) {}

    public function getData(int $userId): array
    {
        $results = (new SqlBatch($this->executor, [
            'user' => ['SELECT * FROM users WHERE id = :id', ['id' => $userId]],
            'posts' => ['SELECT * FROM posts WHERE user_id = :user_id', ['user_id' => $userId]],
            'comments' => ['SELECT * FROM comments WHERE user_id = :user_id', ['user_id' => $userId]],
        ]))();

        return [
            'user' => $results['user'][0] ?? null,
            'posts' => $results['posts'],
            'comments' => $results['comments'],
        ];
    }
}
```

## 参考リンク

- [BEAR.Async](https://github.com/bearsunday/BEAR.Async)
- [BEAR.Async Demo Guide](https://github.com/bearsunday/BEAR.Async/tree/1.x/demo)
- [BEAR.Async Benchmark Results](https://github.com/bearsunday/BEAR.Async/blob/1.x/docs/benchmark-results.md)
- [BEAR.Projection](https://github.com/bearsunday/BEAR.Projection)
- [並列実行アーキテクチャ](https://bearsunday.github.io/BEAR.Async/parallel-execution-architecture.html)
