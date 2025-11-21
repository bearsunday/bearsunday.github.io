---
layout: docs-ja
title: MediaQuery
category: Manual
permalink: /manuals/1.0/ja/database_media.html
---

# Ray.MediaQuery

`Ray.MediaQuery`はデータベースクエリーのインターフェイスから、クエリー実行オブジェクトを生成しインジェクトします。

* ドメイン層とインフラ層の境界を明確にします。
* ボイラープレートコードを削減します。
* 外部メディアの実体には無関係なので、後からストレージを変更することができます。並列開発やスタブ作成が容易です。

## インストール

```bash
composer require ray/media-query
```

> **Note**: Web API機能は別パッケージ [ray/web-query](https://github.com/ray-di/Ray.WebQuery) に移動しました。

## 利用方法

データベースアクセスするインターフェイスを定義します。

### インターフェイス定義

`#[DbQuery]`アトリビュートでSQLのIDを指定します。

```php
use Ray\MediaQuery\Annotation\DbQuery;

interface TodoAddInterface
{
    #[DbQuery('todo_add')]
    public function add(string $id, string $title): void;
}
```

### モジュール設定

`MediaQuerySqlModule`でSQLディレクトリとインターフェイスディレクトリを指定します。

```php
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\MediaQuery\MediaQuerySqlModule;

protected function configure(): void
{
    $this->install(
        new MediaQuerySqlModule(
            interfaceDir: '/path/to/query/interfaces',
            sqlDir: '/path/to/sql'
        )
    );
    $this->install(new AuraSqlModule(
        'mysql:host=localhost;dbname=test',
        'username',
        'password'
    ));
}
```

MediaQuerySqlModuleはAuraSqlModuleのインストールが必要です。

### 注入

インターフェイスからオブジェクトが直接生成され、インジェクトされます。実装クラスのコーディングが不要です。

```php
class Todo
{
    public function __construct(
        private TodoAddInterface $todoAdd
    ) {}

    public function add(string $id, string $title): void
    {
        $this->todoAdd->add($id, $title);
    }
}
```

### DbQuery

SQL実行がメソッドにマップされ、IDで指定されたSQLをメソッドの引数でバインドして実行します。例えばIDが`todo_item`の指定では`todo_item.sql`SQL文に`['id => $id]`をバインドして実行します。

* `$sqlDir`ディレクトリにSQLファイルを用意します。
* SQLファイルには複数のSQL文が記述できます。最後の行のSELECTが返り値になります。

#### Entity

メソッドの戻り値の型を指定すると、SQL実行結果が自動的にそのエンティティクラスに変換（hydrate）されます。

```php
interface TodoItemInterface
{
    #[DbQuery('todo_item')]
    public function getItem(string $id): Todo;
}
```

### Constructor Property Promotion（推奨）

コンストラクタプロパティプロモーションを使うと型安全でイミュータブルなエンティティを作成できます。

```php
final class Todo
{
    public function __construct(
        public readonly string $id,
        public readonly string $title
    ) {}
}
```

### snake_case → camelCase 自動変換

データベースのカラム名（snake_case）とプロパティ名（camelCase）は自動的に変換されます。

```php
final class Invoice
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $userName,      // user_name → userName
        public readonly string $emailAddress,  // email_address → emailAddress
    ) {}
}
```

```sql
-- invoice.sql
SELECT id, title, user_name, email_address FROM invoices WHERE id = :id
```

#### type: 'row'

単一行の結果を連想配列で取得する場合は`type: 'row'`を指定します。

```php
interface TodoItemInterface
{
    #[DbQuery('todo_stats', type: 'row')]
    public function getStats(string $id): array;  // ['total' => 10, 'active' => 5]
}
```

## パラメーター

### 日付時刻

パラメーターにバリューオブジェクトを渡すことができます。例えば、`DateTimeInterface`オブジェクトをこのように指定できます。

```php
interface TaskAddInterface
{
    #[DbQuery('task_add')]
    public function __invoke(string $title, DateTimeInterface $createdAt = null): void;
}
```

値はSQL実行時やWeb APIリクエスト時に日付フォーマットされた文字列に変換されます。

```sql
INSERT INTO task (title, created_at) VALUES (:title, :createdAt); # 2021-2-14 00:00:00
```

値を渡さないとバインドされている現在時刻がインジェクションされます。SQL内部で`NOW()`とハードコーディングする事や、毎回現在時刻を渡す手間を省きます。

### テスト時刻

テストの時には以下のように`DateTimeInterface`の束縛を1つの時刻にすることもできます。

```php
$this->bind(DateTimeInterface::class)->to(UnixEpochTime::class);
```

### バリューオブジェクト（VO）

`DateTime`以外のバリューオブジェクトが渡されると`ToScalarInterface`を実装した`toScalar()`メソッド、もしくは`__toString()`メソッドの返り値が引数になります。

```php
interface MemoAddInterface
{
    public function __invoke(string $memo, UserId $userId = null): void;
}
```

```php
class UserId implements ToScalarInterface
{
    public function __construct(
        private readonly LoginUser $user
    ) {}
    
    public function toScalar(): int
    {
        return $this->user->id;
    }
}
```

```sql
INSERT INTO memo (user_id, memo) VALUES (:user_id, :memo);
```

### パラメーターインジェクション

バリューオブジェクトの引数のデフォルトの値の`null`がSQLやWebリクエストで使われることはないことに注意してください。値が渡されないと、nullの代わりにパラメーターの型でインジェクトされたバリューオブジェクトのスカラー値が使われます。

```php
public function __invoke(Uuid $uuid = null): void; // UUIDが生成され渡される
```

## ページネーション

`#[Pager]`アトリビュートでSELECTクエリーをページングできます。

```php
use Ray\MediaQuery\Annotation\DbQuery;
use Ray\MediaQuery\Annotation\Pager;
use Ray\MediaQuery\Pages;

interface TodoList
{
    #[DbQuery('todo_list'), Pager(perPage: 10, template: '/{?page}')]
    public function __invoke(): Pages;
}
```

`count()`で件数が取得でき、ページ番号で配列アクセスをするとページオブジェクトが取得できます。`Pages`はSQL遅延実行オブジェクトです。

```php
$pages = ($todoList)();
$cnt = count($pages);    // count()をした時にカウントSQLが生成されクエリーが行われます。
$page = $pages[2];       // 配列アクセスをした時にそのページのDBクエリーが行われます。

// $page->data           // sliced data
// $page->current;       // 現在のページ番号
// $page->total          // 総件数
// $page->hasNext        // 次ページの有無
// $page->hasPrevious    // 前ページの有無
// $page->maxPerPage;    // 1ページあたりの最大件数
// (string) $page        // ページャーHTML
```

## SqlQuery

`SqlQuery`はSQLファイルのIDを指定してSQLを実行します。実装クラスを用意して詳細な実装を行う時に使用します。

```php
class TodoItem implements TodoItemInterface
{
    public function __construct(
        private SqlQueryInterface $sqlQuery
    ) {}

    public function __invoke(string $id): array
    {
        return $this->sqlQuery->getRow('todo_item', ['id' => $id]);
    }
}
```

## get* メソッド

SELECT結果を取得するためには取得する結果に応じた`get*`を使います。

```php
$sqlQuery->getRow($queryId, $params);        // 結果が単数行
$sqlQuery->getRowList($queryId, $params);    // 結果が複数行
$statement = $sqlQuery->getStatement();       // PDO Statementを取得
$pages = $sqlQuery->getPages();              // ページャーを取得
```

Ray.MediaQueryは[Ray.AuraSqlModule](https://github.com/ray-di/Ray.AuraSqlModule)を含んでいます。さらに低レイヤーの操作が必要な時はAura.Sqlの[Query Builder](https://github.com/ray-di/Ray.AuraSqlModule#query-builder)やPDOを拡張した[Aura.Sql](https://github.com/auraphp/Aura.Sql)のExtended PDOをお使いください。[doctrine/dbal](https://github.com/ray-di/Ray.DbalModule)も利用できます。

パラメーターインジェクションと同様、`DateTimeInterface`オブジェクトを渡すと日付フォーマットされた文字列に変換されます。

```php
$sqlQuery->exec('memo_add', [
    'memo' => 'run',
    'created_at' => new DateTime()
]);
```

他のオブジェクトが渡されると`toScalar()`または`__toString()`の値に変換されます。

### Ray.InputQueryとの連携

BEAR.ResourceでRay.InputQueryを利用している場合、InputクラスをMediaQueryのパラメーターとして直接渡すことができます。

```php
use Ray\InputQuery\Attribute\Input;

final class UserCreateInput
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $email,
        #[Input] public readonly int $age
    ) {}
}
```

```php
interface UserCreateInterface
{
    #[DbQuery('user_create')]
    public function add(UserCreateInput $input): void;
}
```

InputオブジェクトのプロパティがSQLパラメータに自動展開されます。

```sql
-- user_create.sql
INSERT INTO users (name, email, age) VALUES (:name, :email, :age);
```

この連携により、ResourceObjectからMediaQueryまで一貫して型安全なデータフローを実現できます。

## プロファイラー

メディアアクセスはロガーで記録されます。標準ではテストに使うメモリロガーがバインドされています。

```php
public function testAdd(): void
{
    $this->sqlQuery->exec('todo_add', $todoRun);
    $this->assertStringContainsString(
        'query: todo_add({"id":"1","title":"run"})',
        (string) $this->log
    );
}
```

独自の[MediaQueryLoggerInterface](src/MediaQueryLoggerInterface.php)を実装して、各メディアクエリーのベンチマークを行ったり、インジェクトしたPSRロガーでログをすることもできます。

## PerformSqlInterface

`PerformSqlInterface`を実装することで、SQL実行部分を完全にカスタマイズできます。デフォルトの実行処理を独自の実装に入れ替えることで、より高度なログ機能、パフォーマンス監視、セキュリティ制御などを実現できます。

```php
use Ray\MediaQuery\PerformSqlInterface;

final class CustomPerformSql implements PerformSqlInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    #[Override]
    public function perform(ExtendedPdoInterface $pdo, string $sqlId, string $sql, array $values): PDOStatement
    {
        $startTime = microtime(true);
        
        // カスタムログ出力
        $this->logger->info("Executing SQL: {$sqlId}", [
            'sql' => $sql,
            'params' => $values
        ]);
        
        try {
            /** @var array<string, mixed> $values */
            $statement = $pdo->perform($sql, $values);
            
            // 実行時間のログ
            $executionTime = microtime(true) - $startTime;
            $this->logger->info("SQL executed successfully", [
                'sqlId' => $sqlId,
                'execution_time' => $executionTime
            ]);
            
            return $statement;
        } catch (Exception $e) {
            $this->logger->error("SQL execution failed: {$sqlId}", [
                'error' => $e->getMessage(),
                'sql' => $sql
            ]);
            throw $e;
        }
    }
}
```

カスタム実装を使用するには、DIコンテナで束縛します：

```php
use Ray\MediaQuery\PerformSqlInterface;

protected function configure(): void
{
    $this->bind(PerformSqlInterface::class)->to(CustomPerformSql::class);
}
```

## SQLテンプレート

SQLの実行時にクエリーIDを含むカスタムログを出力して、スローログ分析時にどのクエリーが実行されたかを特定しやすくすることができます。

`MediaQuerySqlTemplateModule`を使用して、SQLログのフォーマットをカスタマイズできます。

```php
use Ray\MediaQuery\MediaQuerySqlTemplateModule;

protected function configure(): void
{
    $this->install(new MediaQuerySqlTemplateModule("-- App: {{ id }}.sql\n{{ sql }}"));
}
```

利用可能なテンプレート変数：

- `{% raw %}{{ id }}{% endraw %}`: クエリーID
- `{% raw %}{{ sql }}{% endraw %}`: 実際のSQL文

デフォルトテンプレート：`-- {% raw %}{{ id }}.sql\n{{ sql }}{% endraw %}`

この機能により、実行されるSQLにクエリーIDがコメントとして含まれ、データベースのスローログを分析する際に、どのアプリケーションのどのクエリーが実行されたかを容易に特定できます。

```sql
-- App: todo_item.sql
SELECT * FROM todo WHERE id = :id
```

## PHP 8 アトリビュート

Ray.MediaQuery 1.0以降は、PHP 8の[アトリビュート](https://www.php.net/manual/ja/language.attributes.overview.php)を使用します。

```php
use Ray\MediaQuery\Annotation\DbQuery;
use Ray\MediaQuery\Annotation\Pager;

interface TodoRepository
{
    #[DbQuery('todo_add')]
    public function add(string $id, string $title): void;

    #[DbQuery('todo_list'), Pager(perPage: 20)]
    public function list(): Pages;
}
```

> **Note**: Doctrineアノテーション（`@DbQuery`）のサポートは終了しました。マイグレーション方法は[Ray.MediaQuery MIGRATION.md](https://github.com/ray-di/Ray.MediaQuery/blob/1.x/MIGRATION.md)を参照してください。
