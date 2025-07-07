---
layout: docs-en
title: MediaQuery
category: Manual
permalink: /manuals/1.0/en/database_media.html
---

# Ray.MediaQuery

`Ray.MediaQuery` generates and injects query execution objects from interfaces for querying external media such as databases and Web APIs.

* Clarifies the boundary between domain layer and infrastructure layer.
* Reduces boilerplate code.
* Since it's independent of the actual external media, storage can be changed later. Enables easy parallel development and stub creation.

## Installation

```bash
$ composer require ray/media-query
```

## Usage

Define an interface for media access.

### Database

Specify the SQL ID with the `DbQuery` attribute.

```php
interface TodoAddInterface
{
    #[DbQuery('user_add')]
    public function add(string $id, string $title): void;
}
```

### Web API

Specify the Web API ID with the `WebQuery` attribute.

```php
interface PostItemInterface
{
    #[WebQuery('user_item')]
    public function get(string $id): array;
}
```

Create an API path list file as `media_query.json`.

```json
{
    "$schema": "https://ray-di.github.io/Ray.MediaQuery/schema/web_query.json",
    "webQuery": [
        {
            "id": "user_item",
            "method": "GET",
            "path": "https://{domain}/users/{id}"
        }
    ]
}
```

MediaQueryModule binds SQL and Web API request execution to interfaces with `DbQueryConfig` and/or `WebQueryConfig` settings.

```php
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\MediaQuery\ApiDomainModule;
use Ray\MediaQuery\DbQueryConfig;
use Ray\MediaQuery\MediaQueryModule;
use Ray\MediaQuery\Queries;
use Ray\MediaQuery\WebQueryConfig;

protected function configure(): void
{
    $this->install(
        new MediaQueryModule(
            Queries::fromDir('/path/to/queryInterface'),
            [
                new DbQueryConfig('/path/to/sql'),
                new WebQueryConfig('/path/to/web_query.json', ['domain' => 'api.example.com'])
            ],
        ),
    );
    $this->install(new AuraSqlModule(
        'mysql:host=localhost;dbname=test',
        'username',
        'password'
    ));
}
```

MediaQueryModule requires AuraSqlModule to be installed.

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

SQL execution is mapped to methods, binding the SQL specified by ID with method arguments for execution. For example, with ID `todo_item`, it executes `todo_item.sql` bound with `['id' => $id]`.

* Prepare SQL files in the `$sqlDir` directory.
* SQL files can contain multiple SQL statements. The last SELECT statement becomes the return value.

#### Entity

SQL execution results can be converted (hydrated) to prepared entity classes by specifying `entity`.

```php
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class)]
    public function getItem(string $id): Todo;
}
```

```php
final class Todo
{
    public string $id;
    public string $title;
}
```

Use `CamelCaseTrait` to convert properties to camelCase.

```php
use Ray\MediaQuery\CamelCaseTrait;

class Invoice
{
    use CamelCaseTrait;
    public $userName;
}
```

If a constructor exists, it will be called with the fetched data.

```php
final class Todo
{
    public function __construct(
        public string $id,
        public string $title
    ) {}
}
```

#### type: 'row'

If SQL execution returns a single row, specify the `type: 'row'` attribute. However, this can be omitted if the interface return value is an entity class.

```php
/** When return value is Entity */
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class)]
    public function getItem(string $id): Todo;
}
```

```php
/** When return value is array */
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class, type: 'row')]
    public function getItem(string $id): array;
}
```

### Web API

* Method arguments are bound to the URI template specified in `uri` to generate Web API request objects.
* Customization for authentication headers, etc. is done by binding Guzzle's `ClientInterface`.

```php
$this->bind(ClientInterface::class)->toProvider(YourGuzzleClientProvider::class);
```

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

For databases, you can paginate SELECT queries with the `#[Pager]` attribute.

```php
use Ray\MediaQuery\PagesInterface;

interface TodoList
{
    #[DbQuery, Pager(perPage: 10, template: '/{?page}')]
    public function __invoke(): PagesInterface;
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

Default template: `-- {% raw %}{{{ id }}.sql\n{{ sql }}{% endraw %}`

This feature includes the query ID as a comment in the executed SQL, making it easy to identify which application query was executed when analyzing database slow logs.

```sql
-- App: todo_item.sql
SELECT * FROM todo WHERE id = :id
```

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
- `{% raw %}{{ sql }}{% endraw %}`: The ac
- tual SQL statement

Default template: `-- {% raw %}{{{ id }}.sql\n{{ sql }}{% endraw %}`

This feature includes the query ID as a comment in the executed SQL, making it easy to identify which application query was executed when analyzing database slow logs.

```sql
-- App: todo_item.sql
SELECT * FROM todo WHERE id = :id
```

## Annotations / Attributes

Both [doctrine annotations](https://github.com/doctrine/annotations/) and [attributes](https://www.php.net/manual/en/language.attributes.overview.php) can be used to represent properties. The following two are equivalent:

```php
use Ray\MediaQuery\Annotation\DbQuery;

#[DbQuery('user_add')]
public function add1(string $id, string $title): void;

/** @DbQuery("user_add") */
public function add2(string $id, string $title): void;
```
