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

> **Note**: Web APIを同様にインターフェイスから扱うには [ray/web-query](https://github.com/ray-di/Ray.WebQuery) を参照してください。

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

SQL実行がメソッドにマップされ、IDで指定されたSQLをメソッドの引数でバインドして実行します。例えばIDが`todo_item`の指定では`todo_item.sql`SQL文に`['id' => $id]`をバインドして実行します。

* `$sqlDir`ディレクトリにSQLファイルを用意します。
* SQLファイルには複数のSQL文が記述できます。最後の行のSELECTが返り値になります。

基本形は **Entity**（1行をhydrate済みエンティティで受け取る）と **Entity リスト**（複数行をエンティティの配列で受け取る）です。連想配列・独自コレクション・ページネーション・DML 系の戻り値型はこれらの応用として段階的に紹介します。

#### Entity（1行）

メソッドの戻り値の型としてエンティティクラスを指定すると、SQL実行結果が自動的にそのインスタンスに変換（_hydrate_）されます。

```php
interface TodoItemInterface
{
    #[DbQuery('todo_item')]
    public function getItem(string $id): Todo;
}
```

##### Constructor Property Promotion（推奨）

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

##### snake_case → camelCase 自動変換

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

行が見つからない可能性があるときは戻り値型に `Entity|null` を指定します。該当行がなければ `null` が返ります。

#### Entity リスト（複数行）

戻り値の型を `array` に宣言すると複数行を受け取れます。各行をエンティティに hydrate するには、`@return list<Entity>` の docblock を付けるか、`#[DbQuery]` の `factory:` パラメーターでファクトリを指定します。

```php
interface TodoListInterface
{
    /** @return list<Todo> */
    #[DbQuery('todo_list')]
    public function list(): array;

    #[DbQuery('todo_list', factory: TodoFactory::class)]
    public function listByFactory(): array;
}
```

`@return list<Entity>` も `factory:` も付けない場合、各行は連想配列のまま返ります（単行版は次の[type: 'row'](#type-row連想配列)を参照）。

#### type: 'row'（連想配列）

戻り値の型が `array` のとき、デフォルトでは複数行（`[['total' => 10, ...], ...]`）として返ります。単一行を**そのまま**連想配列で受け取りたい場合は `type: 'row'` を指定します。指定しないと結果は `$result[0]` に入ります。

```php
interface TodoItemInterface
{
    #[DbQuery('todo_stats', type: 'row')]
    public function getStats(string $id): array;  // ['total' => 10, 'active' => 5]
}
```

#### AffectedRows（UPDATE / DELETE の影響行数）

`UPDATE` / `DELETE`の影響行数を、ただの `int` ではなく型付きの値で受け取るには、戻り値型に`AffectedRows`を指定します。

```php
use Ray\MediaQuery\Result\AffectedRows;

interface TodoRepositoryInterface
{
    #[DbQuery('todo_delete')]
    public function delete(string $id): AffectedRows;
}

$affected = $todoRepo->delete($id);
$affected->count;        // int — 影響を受けた行数
$affected->isAffected(); // bool — count > 0 のときtrue
```

SQLファイルに複数のステートメントが含まれる場合、`AffectedRows`は**最後に実行されたステートメントの結果**を表します。

参考実装: [`ArticleAffectedRowsCommandInterface`](https://github.com/bearsunday/MyVendor.Cms/blob/1.x/src/Query/Samples/ArticleAffectedRowsCommandInterface.php)（[MyVendor.Cms](https://github.com/bearsunday/MyVendor.Cms)）

#### InsertedRow（INSERT の解決済み値とID）

`INSERT`でフレームワークが解決した値（UUID・タイムスタンプなど）と、採番された`lastInsertId`をまとめて受け取るには、戻り値型に`InsertedRow`を指定します。

```php
use Ray\MediaQuery\Result\InsertedRow;

interface TodoRepositoryInterface
{
    #[DbQuery('todo_add')]
    public function add(string $title): InsertedRow;
}

$inserted = $todoRepo->add('ドキュメント作成');
$inserted->values;  // array<string, mixed> — ドライバーにバインドされた解決済み値
$inserted->id;      // ?string — auto-increment ID（採番されない場合はnull）
```

`$inserted->id`はドライバーが`false` / `''` / `'0'`を返した場合、`null`に正規化されます。

#### PostQueryInterface（独自の型付き結果）

SELECTの結果を `array<Article>` ではなく、`published()` / `titles()` のようなドメインメソッドを持つ独自のコレクションでラップしたいことがあります。`PostQueryInterface`を実装したクラスを戻り値型に指定すると、フレームワークはクエリ実行後の状態を `PostQueryContext` にまとめて静的ファクトリ `fromContext()` に渡し、インスタンスの組み立てはクラス側で自由に決められます。

```php
interface PostQueryInterface
{
    public static function fromContext(PostQueryContext $context): static;
}
```

`PostQueryContext`は次の4つの readonly プロパティを持ちます:

| プロパティ   | 型                          | 用途                                                                |
|------------|----------------------------|---------------------------------------------------------------------|
| `$statement` | `PDOStatement`             | 実行済みステートメント。`rowCount()`やカラムメタデータ等を参照可能。       |
| `$pdo`       | `ExtendedPdoInterface`     | 接続。`lastInsertId()` や追加読み取りに使う。                          |
| `$values`    | `array<string, mixed>`     | `ParamConverter` / `ParamInjector` 解決後の値（UUID、タイムスタンプ、[バリューオブジェクト](#バリューオブジェクトvo)のスカラー化等）。 |
| `$rows`      | `array<mixed>`             | hydrate済みの行（エンティティまたは連想配列）。                          |

```php
use Ray\MediaQuery\Result\PostQueryContext;
use Ray\MediaQuery\Result\PostQueryInterface;

/** @implements IteratorAggregate<int, Article> */
final class Articles implements PostQueryInterface, IteratorAggregate, Countable
{
    /** @param list<Article> $items */
    public function __construct(public readonly array $items) {}

    public static function fromContext(PostQueryContext $context): static
    {
        /** @var list<Article> $rows */
        $rows = $context->rows;
        return new static($rows);
    }

    public function getIterator(): ArrayIterator { return new ArrayIterator($this->items); }
    public function count(): int { return count($this->items); }
}

interface ArticleRepositoryInterface
{
    #[DbQuery('article_list', factory: ArticleFactory::class)]
    public function list(): Articles;
}
```

各行のhydrationは Entity リストと同じく `factory:` で指示します。継承ではなく**コンポジション**で表現することで、Laravel `Collection`、Doctrine `ArrayCollection`、独自実装などを自由に内部に保持できます。

参考実装（[MyVendor.Cms](https://github.com/bearsunday/MyVendor.Cms)）:

- [`ArticleSelection`](https://github.com/bearsunday/MyVendor.Cms/blob/1.x/src/Result/ArticleSelection.php) — `published()` / `titles()` / `first()` のドメインメソッドを持つコレクション
- [`ArticleSelectionQueryInterface`](https://github.com/bearsunday/MyVendor.Cms/blob/1.x/src/Query/ArticleSelectionQueryInterface.php) — `factory: ArticleFactory::class` でラッパーを戻り値型に宣言

> なお、`AffectedRows` / `InsertedRow` も同じ `PostQueryInterface` の実装です。DML 後に独自の集計や監査ログを伴う結果型が欲しい場合は、同じ仕組みで自作できます。

#### 戻り値型 早見表

|              | 1行                                  | 複数行（rowlist）                                    |
|--------------|--------------------------------------|----------------------------------------------------|
| エンティティ | `Entity` / `Entity\|null`            | `array` + `@return list<Entity>` または `factory:`  |
| 連想配列     | `array` + `#[DbQuery(type: 'row')]`  | `array`（docblock / `factory:` なし）              |

応用的な戻り値型:

- `MyColl`（`PostQueryInterface` 実装）— 独自の型付きコレクションラッパー
- `PagesInterface` + `#[Pager]` — ページネーション
- `AffectedRows` — DML の影響行数
- `InsertedRow` — DML の採番ID + 解決済み値
- `void` — DML の実行のみ

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

