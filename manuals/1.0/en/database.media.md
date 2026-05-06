---
layout: docs-en
title: MediaQuery
category: Manual
permalink: /manuals/1.0/en/database_media.html
---

# Ray.MediaQuery

`Ray.MediaQuery` generates and injects query execution objects from database query interfaces.

* Clarifies the boundary between domain layer and infrastructure layer.
* Reduces boilerplate code.
* Since it's independent of the actual external media, storage can be changed later. Enables easy parallel development and stub creation.

## Installation

```bash
composer require ray/media-query
```

> **Note**: Web API functionality has been moved to a separate package [ray/web-query](https://github.com/ray-di/Ray.WebQuery).

## Usage

Define an interface for database access.

### Interface Definition

Specify the SQL ID with the `#[DbQuery]` attribute.

```php
use Ray\MediaQuery\Annotation\DbQuery;

interface TodoAddInterface
{
    #[DbQuery('todo_add')]
    public function add(string $id, string $title): void;
}
```

### Module Configuration

Specify SQL directory and interface directory with `MediaQuerySqlModule`.

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

MediaQuerySqlModule requires AuraSqlModule to be installed.

### Injection

Objects are generated directly from interfaces and injected. No implementation class coding is required.

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

SQL execution is mapped to methods, binding the SQL specified by ID with method arguments for execution. For example, with ID `todo_item`, it executes `todo_item.sql` SQL statement bound with `['id => $id]`.

* Prepare SQL files in the `$sqlDir` directory.
* SQL files can contain multiple SQL statements. The last SELECT statement becomes the return value.

#### Return type at a glance

The return type you declare on the interface drives what Ray.MediaQuery returns and how it hydrates results. The full set of supported shapes:

| Use case            | Declared return type                          | What you get                                |
|---------------------|-----------------------------------------------|---------------------------------------------|
| Single row (assoc)  | `array` + `#[DbQuery(type: 'row')]`           | One row as an associative array             |
| Single row (object) | `Entity` / `Entity\|null`                     | One row hydrated to an entity               |
| Row list (assoc)    | `array`                                       | Rows as associative arrays                  |
| Row list (object)   | `array` + `@return list<Entity>`              | Rows as hydrated entities                   |
| Custom collection   | `MyColl` implementing `PostQueryInterface`    | Your own typed wrapper (`IteratorAggregate`, etc.) |
| Pagination          | `PagesInterface` + `#[Pager]`                 | Pagerfanta-backed pages                     |
| DML affected rows   | `AffectedRows`                                | `UPDATE` / `DELETE` affected row count      |
| DML inserted row    | `InsertedRow`                                 | `INSERT` resolved values + `lastInsertId`   |
| DML execute only    | `void`                                        | Run the statement without a result          |

The sections below describe each row in more detail.

#### Entity

When you specify a return type for a method, SQL execution results are automatically converted (hydrated) to that entity class.

```php
interface TodoItemInterface
{
    #[DbQuery('todo_item')]
    public function getItem(string $id): Todo;
}
```

##### Constructor Property Promotion (Recommended)

Using constructor property promotion creates type-safe and immutable entities.

```php
final class Todo
{
    public function __construct(
        public readonly string $id,
        public readonly string $title
    ) {}
}
```

##### Automatic snake_case → camelCase conversion

Database column names (snake_case) and property names (camelCase) are automatically converted.

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

Specify `type: 'row'` to retrieve a single row result as an associative array.

```php
interface TodoItemInterface
{
    #[DbQuery('todo_stats', type: 'row')]
    public function getStats(string $id): array;  // ['total' => 10, 'active' => 5]
}
```

#### PostQueryInterface (typed result types)

Return types that build a result after the query executes are expressed as classes implementing `PostQueryInterface`. The interceptor detects the interface on the return type and calls the static `fromContext()` factory with a `PostQueryContext` describing the execution. The same dispatch covers DML (Data Manipulation Language — `INSERT` / `UPDATE` / `DELETE`) and SELECT.

```php
interface PostQueryInterface
{
    public static function fromContext(PostQueryContext $context): static;
}
```

`PostQueryContext` exposes the execution as four readonly properties:

| Property     | Type                       | Purpose                                                       |
|--------------|----------------------------|---------------------------------------------------------------|
| `$statement` | `PDOStatement`             | The executed statement; inspect `rowCount()`, column metadata, etc. |
| `$pdo`       | `ExtendedPdoInterface`     | The connection; useful for `lastInsertId()` and follow-up reads.    |
| `$values`    | `array<string, mixed>`     | Parameter values resolved by `ParamConverter` / `ParamInjector` (UUIDs, timestamps, value-object scalars). |
| `$rows`      | `array<mixed>`             | SELECT: pre-hydrated rows (entities or assoc arrays). DML: `[]`.    |

##### AffectedRows (UPDATE / DELETE row count)

Declare `AffectedRows` as the return type to receive the number of rows affected by an `UPDATE` / `DELETE`.

```php
use Ray\MediaQuery\Result\AffectedRows;

interface TodoRepositoryInterface
{
    #[DbQuery('todo_delete')]
    public function delete(string $id): AffectedRows;
}

$affected = $todoRepo->delete($id);
$affected->count;        // int — number of affected rows
$affected->isAffected(); // bool — true when count > 0
```

When a SQL file contains multiple statements, `AffectedRows` reflects the **last executed statement only**.

##### InsertedRow (INSERT resolved values and id)

Use `InsertedRow` to recover the values the framework injected on the caller's behalf (UUIDs, timestamps, `DateTime` → SQL strings, `ToScalarInterface` reductions) together with the auto-increment id reported by the driver.

```php
use Ray\MediaQuery\Result\InsertedRow;

interface TodoRepositoryInterface
{
    #[DbQuery('todo_add')]
    public function add(string $title): InsertedRow;
}

$inserted = $todoRepo->add('Write docs');
$inserted->values;  // array<string, mixed> — resolved values bound to the driver
$inserted->id;      // ?string — auto-increment id, null when none was assigned
```

`$inserted->id` is normalised to `null` when the driver returns `false` / `''` / `'0'`.

##### Custom typed collection wrappers (SELECT)

To wrap SELECT results in your own collection type, implement `PostQueryInterface` on the wrapper. `PostQueryContext::$rows` carries the hydrated entities.

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

Hydration is configured as before via `factory:` or a `@return Articles<Article>` docblock. The wrapper uses **composition** rather than inheritance, so it can hold any internal collection — Laravel `Collection`, Doctrine `ArrayCollection`, or a custom one — without coupling to any specific library.

For a real-world reference, the BEAR.Sunday sample app [MyVendor.Cms](https://github.com/bearsunday/MyVendor.Cms) ships these patterns end-to-end:

- [`ArticleSelection`](https://github.com/bearsunday/MyVendor.Cms/blob/1.x/src/Result/ArticleSelection.php) — `PostQueryInterface` collection with domain methods `published()` / `titles()` / `first()`
- [`ArticleSelectionQueryInterface`](https://github.com/bearsunday/MyVendor.Cms/blob/1.x/src/Query/ArticleSelectionQueryInterface.php) — declaring the wrapper as the return type with `factory: ArticleFactory::class`
- [`ArticleAffectedRowsCommandInterface`](https://github.com/bearsunday/MyVendor.Cms/blob/1.x/src/Query/Samples/ArticleAffectedRowsCommandInterface.php) — `AffectedRows` for `UPDATE` / `DELETE`

## Parameters

### DateTime

You can pass value objects as parameters. For example, `DateTimeInterface` objects can be specified like this:

```php
interface TaskAddInterface
{
    #[DbQuery('task_add')]
    public function __invoke(string $title, DateTimeInterface $createdAt = null): void;
}
```

Values are converted to date-formatted strings during SQL execution or Web API requests.

```sql
INSERT INTO task (title, created_at) VALUES (:title, :createdAt); # 2021-2-14 00:00:00
```

If no value is passed, the bound current time is injected. This eliminates the need to hard-code `NOW()` in SQL or pass current time every time.

### Test Time

For testing, you can bind `DateTimeInterface` to a single time like this:

```php
$this->bind(DateTimeInterface::class)->to(UnixEpochTime::class);
```

### Value Objects (VO)

When value objects other than `DateTime` are passed, the return value of the `toScalar()` method implementing `ToScalarInterface`, or the `__toString()` method becomes the argument.

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

### Parameter Injection

Note that the default value `null` for value object arguments is never used in SQL or Web requests. When no value is passed, the scalar value of the value object injected by parameter type is used instead of null.

```php
public function __invoke(Uuid $uuid = null): void; // UUID is generated and passed
```

## Pagination

You can paginate SELECT queries with the `#[Pager]` attribute.

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

You can get the count with `count()`, and get page objects with array access by page number. `Pages` is a SQL lazy execution object.

```php
$pages = ($todoList)();
$cnt = count($pages);    // Count SQL is generated and queried when count() is called
$page = $pages[2];       // DB query for that page is executed when array access is made

// $page->data           // sliced data
// $page->current;       // current page number
// $page->total          // total count
// $page->hasNext        // whether next page exists
// $page->hasPrevious    // whether previous page exists
// $page->maxPerPage;    // maximum items per page
// (string) $page        // pager HTML
```

## SqlQuery

`SqlQuery` executes SQL by specifying the SQL file ID. Used when preparing implementation classes for detailed implementation.

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

## get* Methods

Use appropriate `get*` methods to retrieve SELECT results based on the expected result type.

```php
$sqlQuery->getRow($queryId, $params);        // Result is single row
$sqlQuery->getRowList($queryId, $params);    // Result is multiple rows
$statement = $sqlQuery->getStatement();       // Get PDO Statement
$pages = $sqlQuery->getPages();              // Get pager
```

Ray.MediaQuery includes [Ray.AuraSqlModule](https://github.com/ray-di/Ray.AuraSqlModule). For lower-level operations, use Aura.Sql's [Query Builder](https://github.com/ray-di/Ray.AuraSqlModule#query-builder) or PDO-extended [Aura.Sql](https://github.com/auraphp/Aura.Sql). [doctrine/dbal](https://github.com/ray-di/Ray.DbalModule) is also available.

Like parameter injection, passing `DateTimeInterface` objects converts them to date-formatted strings.

```php
$sqlQuery->exec('memo_add', [
    'memo' => 'run',
    'created_at' => new DateTime()
]);
```

Other objects are converted to `toScalar()` or `__toString()` values.

### Integration with Ray.InputQuery

When using Ray.InputQuery with BEAR.Resource, Input classes can be passed directly as MediaQuery parameters.

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

Input object properties are automatically expanded to SQL parameters.

```sql
-- user_create.sql
INSERT INTO users (name, email, age) VALUES (:name, :email, :age);
```

This integration enables consistent type-safe data flow from ResourceObject to MediaQuery.

## Profiler

Media access is logged by loggers. By default, a memory logger for testing is bound.

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

You can implement your own [MediaQueryLoggerInterface](src/MediaQueryLoggerInterface.php) to benchmark each media query or log with injected PSR loggers.

## PerformSqlInterface

By implementing `PerformSqlInterface`, you can completely customize the SQL execution layer. Replace the default execution process with your own implementation to achieve advanced logging, performance monitoring, security controls, and more.

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

        // Custom logging
        $this->logger->info("Executing SQL: {$sqlId}", [
            'sql' => $sql,
            'params' => $values
        ]);

        try {
            /** @var array<string, mixed> $values */
            $statement = $pdo->perform($sql, $values);

            // Execution time logging
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

To use your custom implementation, bind it in the DI container:

```php
use Ray\MediaQuery\PerformSqlInterface;

protected function configure(): void
{
    $this->bind(PerformSqlInterface::class)->to(CustomPerformSql::class);
}
```

## SQL Template

You can customize SQL log formatting to include query IDs in the executed SQL, making it easier to identify which queries are running when analyzing slow logs.

Use `MediaQuerySqlTemplateModule` to customize the SQL log format.

```php
use Ray\MediaQuery\MediaQuerySqlTemplateModule;

protected function configure(): void
{
    $this->install(new MediaQuerySqlTemplateModule("-- App: {{ id }}.sql\n{{ sql }}"));
}
```

Available template variables:

- `{% raw %}{{ id }}{% endraw %}`: Query ID
- `{% raw %}{{ sql }}{% endraw %}`: The actual SQL statement

Default template: `-- {% raw %}{{ id }}.sql\n{{ sql }}{% endraw %}`

This feature includes the query ID as a comment in the executed SQL, making it easy to identify which application query was executed when analyzing database slow logs.

```sql
-- App: todo_item.sql
SELECT * FROM todo WHERE id = :id
```

## PHP 8 Attributes

Ray.MediaQuery 1.0 and later uses PHP 8 [attributes](https://www.php.net/manual/en/language.attributes.overview.php).

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

> **Note**: Doctrine annotation (`@DbQuery`) support has been removed. For migration instructions, see [Ray.MediaQuery MIGRATION.md](https://github.com/ray-di/Ray.MediaQuery/blob/1.x/MIGRATION.md).
