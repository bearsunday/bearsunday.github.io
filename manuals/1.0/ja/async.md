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
| Swoole HTTPサーバー | `bin/swoole.php` | `AsyncSwooleModule`を`AppModule`にインストール |

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

#### ext-parallelの制約

ワーカーは別スレッドで動作し、それぞれ独立したZendメモリ空間を持ちます。並列実行する埋め込みリソースは、順序に依存しない読み取り専用（冪等なGET）リソースにしてください。各ワーカーは独自のDIコンテナを持つため、リクエストローカルな可変状態や「同一インスタンスである」という前提はスレッド境界を越えて引き継がれません。

スレッド境界をまたぐ引数と戻り値はコピー可能でなければなりません。具体的にはスカラー値・`null`・それらをネストした配列です。オブジェクトやクロージャ、リソースを渡した場合は即座にエラーになります。並列実行される埋め込みリソースに適用するインターセプターは冪等に保ち、リクエストローカルな共有状態を書き換えないでください。

### Swoole実行（ext-swoole）

すでにSwoole HTTPサーバー上で稼働しており、高い並行性能を求めるアプリケーション向けのランタイム環境です。

ext-parallelはワーカー（別スレッド）で動作するため別エントリポイントから選択しますが、ext-swooleは同一サーバープロセス内で動作するため、アプリケーションモジュールとしてインストールします。

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

Swooleではコルーチン同士がメモリを共有するため、`PdoPoolEnvModule`による接続プールが必要です。読み取り中心で埋め込みリソースを多用する構成では、外部から到達するHTTPリクエスト数だけでなく、1リクエスト内で同時に実行される埋め込みの数も加味してプールサイズを見積もります。キュー待ちを避けたい場合は `PDO_POOL_SIZE >= embed_count * request_concurrency` を目安にし、DBへの同時接続数を抑えたい場合はあえて小さめに設定します。

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

BEAR.Sundayでは、情報がリソースとして URI で**構造化**されています。`#[Embed]`はそのリソースの実行結果ではなく、リソースリクエストそのものを埋め込み、リソース間の関係を宣言します。実行戦略 — 逐次・ext-parallelワーカー・Swooleコルーチン — を選ぶのは Linker の役割で、リソースクラスは自分が同期で呼ばれたか並列で呼ばれたかを知る必要がありません。

通常モードではレンダリング時にこれらのリクエストが1つずつ逐次解決されますが、並列実行モードでは、最初の埋め込みリクエストが解決される時点で残りの埋め込みリクエストもまとめて並列に実行されます。BEAR.Asyncの非同期リクエストはBEAR.Resourceの通常リクエストと同じ型として扱えるため、HALレンダラなど周辺の仕組みはこの差を意識せずシリアライズに統合できます。

非同期プログラミングでしばしば言われる「関数の色」問題 — 非同期関数を呼ぶ関数は自身も非同期でなければならず、コード全体が非同期に汚染される問題 — も、リソースという境界がこれを遮断します。同期と並列でコードは同じ、変わるのは実行戦略だけです。

これはBEAR.Async固有ではなく、BEAR.Sunday全体の性質です。MVCフレームワークが「どう実行するか」を手続きで書く箇所を、BEAR.Sundayはリソース間の関係を宣言として表します。宣言は実行戦略から独立しているため、戦略の差し替えはコードに影響しません。

## デモとベンチマーク

BEAR.AsyncリポジトリにはSync・ext-parallel・Swooleの動作を比較できる、Dockerベースのデモとベンチマークスクリプトが含まれています。詳細は[デモガイド](https://github.com/bearsunday/BEAR.Async/tree/1.x/demo)と[ベンチマーク結果](https://github.com/bearsunday/BEAR.Async/blob/1.x/docs/benchmark-results.md)を参照してください。

## 動作要件

各ランタイム環境は対応するPHP拡張を必要とします。

| ランタイム環境 | 必要なもの | アプリケーション側の変更 |
|-----|-----|-----|
| ext-parallel | ZTS PHP + ext-parallel | `bin/async.php`を追加 |
| ext-swoole | ext-swoole | `AsyncSwooleModule`をインストール、`bin/swoole.php`を使用 |

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
- [BEAR.Async デモガイド](https://github.com/bearsunday/BEAR.Async/tree/1.x/demo)
- [BEAR.Async ベンチマーク結果](https://github.com/bearsunday/BEAR.Async/blob/1.x/docs/benchmark-results.md)
- [BEAR.Projection](https://github.com/bearsunday/BEAR.Projection)
- [並列実行アーキテクチャ](https://bearsunday.github.io/BEAR.Async/parallel-execution-architecture.html)
