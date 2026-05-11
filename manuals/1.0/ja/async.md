---
layout: docs-ja
title: 並列リソース実行
category: Manual
permalink: /manuals/1.0/ja/async.html
---

# 並列リソース実行 <sup style="font-size:0.5em; color:#666; font-weight:normal;">Alpha</sup>

BEAR.Asyncはこれまで逐次取得されていた`#[Embed]`埋め込みリソースを透過的に並列実行します。リソースのコードに手を入れることなく、並列実行用の起動スクリプトを用意するだけで、埋め込みリソースは自動的に並列取得に切り替わります。

## 概要

標準のBEAR.Sundayでは`#[Embed]`リソースは順次取得されますが、BEAR.Asyncで実行モードを選択すると並列に取得されます。

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

## 実行モード

サーバー環境に応じて適切な実行モードを選択します。

| 用途 | エントリポイント | ランタイム設定 |
|-----|-----|-----|
| PHP-FPM / Apache（埋め込みリソースあり） | `bin/async.php` | ライブラリの`bootstrap.php`によるオーバーレイ |
| Swoole HTTPサーバー | `bin/swoole.php` | `AsyncSwooleModule`を`AppModule`にインストール |

### 並列実行（ext-parallel）

PHP-FPM / Apache 上で動作する典型的な Web アプリケーション向けの実行モードです。ext-parallel のスレッドプールで`#[Embed]`を並列実行します。

`bin/app.php`の隣に`bin/async.php`を追加します。このエントリポイントはライブラリの`bootstrap.php`に処理を委譲し、通常の`AppModule`の上に ext-parallel ランタイムをオーバーレイします。

```text
bin/async.php → vendor/bear/async/bootstrap.php → AppModule + ランタイムオーバーレイ
```

```php
<?php // bin/async.php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$bootstrap = dirname(__DIR__) . '/vendor/bear/async/bootstrap.php';

exit((require $bootstrap)(
    name: 'MyVendor\MyApp',
    context: $_GET['_context'] ?? 'prod-hal-app',
    appDir: dirname(__DIR__),
    globals: [
        'GET'    => $_GET,
        'POST'   => $_POST,
        'COOKIE' => $_COOKIE,
    ],
    server: $_SERVER,
));
```

`AppModule`に並列実行用のモジュールを直接インストールしないでください。ライブラリの`bootstrap.php`経由が唯一サポートされる導入方法です。これにより、同じ`AppModule`が`bin/app.php`（同期）と`bin/async.php`（並列）の両方でそのまま動作します。

ワーカープールサイズ（デフォルトはCPUコア数）を上書きするには、オプションの第6引数に渡します。

### Swoole実行（ext-swoole）

すでに Swoole HTTP Server で稼働しており、高い並行性能が求められるアプリケーション向けの実行モードです。

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

## 使用方法

実行モードを選択すると、既存の`#[Embed]`リソースは自動的に並列実行されます。

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

開発環境では`bin/app.php`を使用して同期モードでデバッグし、本番環境では`bin/async.php`から起動することで並列実行を有効にできます。`AppModule`は実行形態に依存しないため、同じコードがそのまま両モードで動作します。

## 動作要件

ライブラリ自体はPHP 8.2+で動作します。各実行モードはそれぞれ別のランタイムを必要とします。

| モード | 必要なもの | アプリケーション側の変更 |
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
use BEAR\Async\Module\MysqliBatchEnvModule;

$this->install(new MysqliBatchEnvModule('MYSQL_HOST', 'MYSQL_USER', 'MYSQL_PASS', 'MYSQL_DB'));
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
        return (new SqlBatch($this->executor, [
            'user' => ['SELECT * FROM users WHERE id = ?', [$userId]],
            'posts' => ['SELECT * FROM posts WHERE user_id = ?', [$userId]],
        ]))();
    }
}
```

## 参考リンク

- [BEAR.Async](https://github.com/bearsunday/BEAR.Async)
- [BEAR.Projection](https://github.com/bearsunday/BEAR.Projection)
- [並列実行アーキテクチャ](https://bearsunday.github.io/BEAR.Async/parallel-execution-architecture.html)
