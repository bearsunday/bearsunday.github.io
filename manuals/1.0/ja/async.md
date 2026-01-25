---
layout: docs-ja
title: 並列リソース実行
category: Manual
permalink: /manuals/1.0/ja/async.html
---

# 並列リソース実行

BEAR.AsyncはBEAR.Sundayの`#[Embed]`リソースの透過的な並列実行を可能にします。標準の`LinkerInterface`実装を置き換えることで、アプリケーションコードを変更することなく、埋め込みリソースを並行して取得します。

## 概要

標準のBEAR.Sundayでは、`#[Embed]`リソースは順次取得されます：

```text
Request
    │
    ├── Embed 1 ──── 50ms
    ├── Embed 2 ──── 50ms
    ├── Embed 3 ──── 50ms
    └── Embed 4 ──── 50ms
    │
Response (合計200ms)
```

BEAR.Asyncを使用すると、埋め込みリソースは並列に取得されます：

```text
Request
    │
    ├── Embed 1 ──┬── 50ms
    ├── Embed 2 ──┤
    ├── Embed 3 ──┤
    └── Embed 4 ──┘
    │
Response (合計50ms)
```

## 動作原理

### アーキテクチャ

```text
LinkerInterface (bear/resource)
       ↓ 置換
AsyncLinker ──使用──→ AsyncInterface
                           ↓ 実装
              ┌────────────┼────────────┐
        ParallelAsync  SwooleAsync  SyncAsync
        (ext-parallel)  (ext-swoole) (フォールバック)
```

### 主要コンポーネント

| コンポーネント | 責務 |
|--------------|------|
| AsyncLinker | 標準Linkerを置換し、crawlリクエストをレベルごとに並列実行 |
| AsyncInterface | 異なる非同期ランタイム用のアダプターインターフェース |
| ParallelAsync | ext-parallelを使用したスレッドプールエグゼキュータ |
| SwooleAsync | ext-swooleを使用したコルーチンエグゼキュータ |
| SyncAsync | 非同期拡張が利用できない場合の順次フォールバック |

### 実行フロー

1. `AsyncLinker.linkCrawl()`が各レベルのすべてのembedリクエストを収集
2. `RequestBatch`がURI+クエリハッシュでリクエストを重複排除
3. `AsyncInterface`がすべてのタスクを並列実行
4. 結果がキャッシュされ、すべてのリクエスタに配布

```text
Level 1: Users → すべてのユーザーリクエストを並列実行
Level 2: 各ユーザーのPosts → すべての投稿リクエストを並列実行
Level 3: 各投稿のComments → すべてのコメントリクエストを並列実行
```

## インストール

```bash
composer require bear/async
```

### 要件

- PHP 8.2+
- bear/resource ^1.17
- ray/di ^2.18

### オプション拡張

- **ext-parallel**: スレッドベースの並列実行用（ZTS PHPが必要）
- **ext-swoole**: コルーチンベースの並列実行用
- **ext-mysqli**: mysqliバッチ実行用

## 設定

### PHP-FPM + ext-parallel

PHP-FPMまたはApacheを使用する一般的なWebアプリケーションに推奨。

```php
use BEAR\Async\Module\AsyncParallelModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new PackageModule());
        $this->install(new AsyncParallelModule(
            namespace: 'MyVendor\MyApp',
            context: 'prod-app',
            appDir: dirname(__DIR__),
        ));
    }
}
```

プールサイズはデフォルトでCPUコア数になります。オーバーライドするには：

```php
$this->install(new AsyncParallelModule(
    namespace: 'MyVendor\MyApp',
    context: 'prod-app',
    appDir: dirname(__DIR__),
    poolSize: 8,
));
```

### Swoole + コルーチン

高い並行性が必要なSwoole HTTPサーバー上で実行するアプリケーション用。

```php
use BEAR\Async\Module\AsyncSwooleModule;
use BEAR\Async\Module\PdoPoolModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new PackageModule());
        $this->install(new AsyncSwooleModule());
        // Swooleコルーチンには接続プールが必要
        $this->install(new PdoPoolModule($dsn, $user, $password));
    }
}
```

#### PdoPoolModuleが必要な理由

Swooleでは、コルーチンは同じプロセス内でメモリを共有します。あるコルーチンで作成されたデータベース接続は、別のコルーチンで安全に使用できません。`PdoPoolModule`は、コルーチン間でPDOインスタンスを管理する接続プールを提供します。

## モジュール選択

| ユースケース | 推奨モジュール |
|------------|--------------|
| PHP-FPM / Apache | `AsyncParallelModule` |
| Swoole HTTPサーバー | `AsyncSwooleModule` |

### 比較

| | AsyncParallelModule | AsyncSwooleModule |
|---|---|---|
| 並行性 | スレッドプール（CPUコア数） | コルーチン（数千） |
| PDO処理 | スレッドごとに分離 | 接続プールが必要 |
| サーバー | PHP-FPM / Apache | Swoole HTTPサーバー |
| セットアップ | シンプル | Swooleサーバーが必要 |

## 使用方法

モジュールをインストールすると、コードの変更は不要です。既存の`#[Embed]`リソースは自動的に並列実行されます。

```php
use BEAR\Resource\Annotation\Embed;

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

3つの埋め込みリソース（`user`、`notifications`、`stats`）はすべて並列に取得されます。

### コンテキストベースの設定

本番環境では非同期を有効にし、開発環境では同期を使用するために異なるコンテキストを使用：

```php
// src/Module/ProdModule.php
class ProdModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new AsyncParallelModule(/* ... */));
    }
}

// src/Module/DevModule.php
class DevModule extends AbstractModule
{
    protected function configure(): void
    {
        // asyncモジュールなし - 標準の順次実行を使用
    }
}
```

## SQLバッチ実行

BEAR.Asyncは、mysqliのネイティブ非同期サポートを使用した並列SQLクエリ実行も提供します。

### 設定

```php
use BEAR\Async\Module\MysqliBatchModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new MysqliBatchModule(
            host: 'localhost',
            user: 'root',
            pass: 'password',
            database: 'mydb',
        ));
    }
}
```

または環境変数を使用：

```php
use BEAR\Async\Module\MysqliEnvModule;

$this->install(new MysqliEnvModule(
    'MYSQLI_HOST',
    'MYSQLI_USER',
    'MYSQLI_PASSWORD',
    'MYSQLI_DATABASE',
));
```

### 使用方法

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

3つのクエリが`mysqli_poll`を使用して並列実行され、合計実行時間は最も遅いクエリの時間に短縮されます。

## パフォーマンス

### ベンチマーク結果

| シナリオ | 同期時間 | 並列時間 | 高速化 |
|---------|---------|---------|-------|
| 3 embeds（各50ms） | 150ms | ~52ms | 2.9倍 |
| 5 embeds（各50ms） | 250ms | ~54ms | 4.6倍 |
| 11 embeds（各50ms） | 550ms | ~59ms | 9.4倍 |

### 並列実行が効果的な場合

- I/Oバウンドのembed操作（データベースクエリ、API呼び出し）
- 複数の独立したembed
- 十分なCPUコアが利用可能

### 効果が薄い場合

- CPUバウンドの操作（複雑な計算）
- 単一のembedまたは順次依存関係
- 非常に高速なクエリ（< 5ms）でオーバーヘッドが支配的

## 参考リンク

- [BEAR.Asyncリポジトリ](https://github.com/bearsunday/BEAR.Async)
- [BEAR.Projection](https://github.com/bearsunday/BEAR.Projection) - SQLベースのプロジェクションを使用したCQRSリードモデル
- [並列実行アーキテクチャ](https://bearsunday.github.io/BEAR.Async/parallel-execution-architecture.html)
- [リソースリンク](resource_link.html) - `#[Embed]`と`#[Link]`のドキュメント
- [高性能サーバー](swoole.html) - BEAR.SundayをSwooleで実行
