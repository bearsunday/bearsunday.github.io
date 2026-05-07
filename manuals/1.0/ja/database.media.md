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

#### 戻り値型の一覧

インターフェイスで宣言する戻り値の型が、Ray.MediaQueryの返却内容と _hydrate_（結果セットの各行をエンティティクラスのインスタンスに変換すること、詳細は後述の[Entity](#entity)節）の方法を決定します。サポートされる全てのパターン:

| 用途              | 戻り値型の宣言                                    | 受け取れるもの                              |
|------------------|--------------------------------------------------|-------------------------------------------|
| 単行 (assoc)     | `array` + `#[DbQuery(type: 'row')]`              | 1行を連想配列で                            |
| 単行 (object)    | `Entity` / `Entity\|null`                        | 1行をhydrate済みエンティティで              |
| 複数 (assoc)     | `array`                                          | 複数行を連想配列の配列で                    |
| 複数 (object)    | `array` + `@return list<Entity>`                 | 複数行をhydrate済みエンティティで            |
| 独自コレクション | `MyColl` (`PostQueryInterface` 実装)             | 自前の型付きラッパー（`IteratorAggregate` 等） |
| ページネーション | `PagesInterface` + `#[Pager]`                    | Pagerfanta ベースのページャ                |
| DML 影響行数     | `AffectedRows`                                   | `UPDATE` / `DELETE` の影響行数             |
| DML 採番ID       | `InsertedRow`                                    | `INSERT` の解決済み値 + `lastInsertId`     |
| DML 結果のみ     | `void`                                           | 実行のみ（戻り値不要）                      |

以下の各節は、上の表の各行の詳細です。

#### Entity

メソッドの戻り値の型を指定すると、SQL実行結果が自動的にそのエンティティクラスに変換（hydrate）されます。

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

#### type: 'row'

単一行の結果を連想配列で取得する場合は`type: 'row'`を指定します。

```php
interface TodoItemInterface
{
    #[DbQuery('todo_stats', type: 'row')]
    public function getStats(string $id): array;  // ['total' => 10, 'active' => 5]
}
```

#### PostQueryInterface（型付き結果クラス）

クエリ実行後に結果を組み立てる戻り値型は、`PostQueryInterface`を実装したクラスとして表現します。インターセプターは戻り値型がこのインターフェースを実装していれば、静的ファクトリ`fromContext()`に実行コンテキストを表す`PostQueryContext`を渡して結果を構築します。フレームワークはDML（Data Manipulation Language — `INSERT` / `UPDATE` / `DELETE`）とSELECTを同じ仕組みでルーティングします。

```php
interface PostQueryInterface
{
    public static function fromContext(PostQueryContext $context): static;
}
```

`PostQueryContext`は実行内容を4つの readonly プロパティとして公開します:

| プロパティ   | 型                          | 用途                                                                |
|------------|----------------------------|---------------------------------------------------------------------|
| `$statement` | `PDOStatement`             | 実行済みステートメント。`rowCount()`やカラムメタデータ等を参照可能。       |
| `$pdo`       | `ExtendedPdoInterface`     | 接続。`lastInsertId()` や追加読み取りに使う。                          |
| `$values`    | `array<string, mixed>`     | `ParamConverter` / `ParamInjector` 解決後の値（UUID、タイムスタンプ、[バリューオブジェクト](#バリューオブジェクトvo)のスカラー化等）。 |
| `$rows`      | `array<mixed>`             | SELECT時はhydrate済みの行（エンティティまたは連想配列）。DML時は `[]`。  |

##### AffectedRows（UPDATE / DELETE の影響行数）

`UPDATE` / `DELETE`の影響行数を受け取るには、戻り値型に`AffectedRows`を指定します。

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

##### InsertedRow（INSERT の解決済み値とID）

`INSERT`で、フレームワークが注入した値（UUID・タイムスタンプなど）と採番された`lastInsertId`を受け取るには、戻り値型に`InsertedRow`を指定します。

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

##### 独自の型付きコレクションラッパー（SELECT）

SELECTの結果を独自のコレクションでラップしたい場合も、同じ`PostQueryInterface`で表現します。`PostQueryContext::$rows`にはhydrate済みのエンティティ配列が入ります。

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

エンティティのhydrationは従来どおり`factory:`または`@return Articles<Article>`のdocblockで指示します。継承ではなく**コンポジション**で表現することで、Laravel `Collection`、Doctrine `ArrayCollection`、独自実装などを自由に内部に保持できます。

実プロジェクトでの応用例として、BEAR.Sunday リファレンス実装 [MyVendor.Cms](https://github.com/bearsunday/MyVendor.Cms) では以下のパターンを通しで使っています:

- [`ArticleSelection`](https://github.com/bearsunday/MyVendor.Cms/blob/1.x/src/Result/ArticleSelection.php) — `PostQueryInterface` のコレクションラッパーに `published()` / `titles()` / `first()` のドメインメソッドを実装
- [`ArticleSelectionQueryInterface`](https://github.com/bearsunday/MyVendor.Cms/blob/1.x/src/Query/ArticleSelectionQueryInterface.php) — `factory: ArticleFactory::class` でラッパーを戻り値型に宣言
- [`ArticleAffectedRowsCommandInterface`](https://github.com/bearsunday/MyVendor.Cms/blob/1.x/src/Query/Samples/ArticleAffectedRowsCommandInterface.php) — `UPDATE` / `DELETE` で `AffectedRows` を受け取る例

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
