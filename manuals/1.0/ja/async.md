---
layout: docs-ja
title: 並列リソース実行
category: Manual
permalink: /manuals/1.0/ja/async.html
---

# 並列リソース実行 <sup style="font-size:0.5em; color:#666; font-weight:normal;">Alpha</sup>

BEAR.Asyncは`#[Embed]`リソースの透過的な並列実行を可能にします。アプリケーションコードを変更することなく、埋め込みリソースを並列に取得します。10年前に書かれたリソースクラスも、Moduleを追加するだけで並列実行の恩恵を受けられます。

## 概要

標準のBEAR.Sundayでは`#[Embed]`リソースは順次取得されますが、BEAR.Asyncを使用すると並列に取得されます。

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

この`query://self/user_profile`は「ユーザーのプロファイル情報が欲しい」という意図だけを示しています。この「What（何を）」と「How（どう）」の分離により、同じコードが同期実行でも並列実行でも動作します。開発時は通常のPHPとしてXdebugでデバッグし、本番ではModuleを切り替えるだけで並列実行を有効化できます。

### 関数の色問題の解決

非同期プログラミングには「関数の色」問題があります。非同期関数を呼ぶ関数は自身も非同期でなければならず、コード全体が「非同期に汚染」されていきます。

BEAR.Sundayでは「リソース」という境界がこの問題を断ち切ります。非同期のためのコード記述は一切不要で、リソースクラスは自分がどう呼び出されたかを知る必要がありません。

## インストール

```bash
composer require bear/async
```

## 設定

サーバー環境に応じて適切なモジュールを選択します。

| 環境 | モジュール | 特徴 |
|-----|----------|------|
| PHP-FPM / Apache | `AsyncParallelModule` | ext-parallel使用、ZTS PHP必要 |
| Swoole HTTPサーバー | `AsyncSwooleModule` | コルーチン使用、接続プール必要 |

### AsyncParallelModule

```php
use BEAR\Async\Module\AsyncParallelModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new AsyncParallelModule(
            namespace: 'MyVendor\MyApp',
            context: 'prod-app',
            appDir: dirname(__DIR__),
        ));
    }
}
```

### AsyncSwooleModule

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

モジュールをインストールすると、既存の`#[Embed]`リソースは自動的に並列実行されます。

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

開発環境では非同期モジュールをインストールせず、本番環境でのみ有効にすることで、同期モードでのデバッグと本番での並列実行を使い分けられます。

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
- [BEAR.Projection](https://github.com/bearsunday/BEAR.Projection)
- [並列実行アーキテクチャ](https://bearsunday.github.io/BEAR.Async/parallel-execution-architecture.html)
