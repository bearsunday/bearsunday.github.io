---
layout: docs-en
title: Database
category: Manual
permalink: /manuals/1.0/en/databasev1.html
---

# Database

`Aura.Sql`、`Doctrine DBAL`, `CakeDB` modules are available for database connections.

## Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql) is an Aura database library that extends from `PDO` .

### Installation

Install `Ray.AuraSqlModule` via composer.

```bash
composer require ray/aura-sql-module
```

Installing `AuraSqlModule` in your application module`src/Module/AppModule.php`.

```php?start_inline
use BEAR\Package\AbstractAppModule;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\AuraSqlModule\AuraSqlModule; // add this line

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        // Add the below install method call and contents
        $this->install(
            new AuraSqlModule(
                'mysql:host=localhost;dbname=test',
                'username',
                'password',
                // $options,
                // $attributes
            )
        );
        $this->install(new PackageModule));
    }
}
```

Now the `DI` bindings are ready. The db object will be injected via a constructor or the `AuraSqlInject` setter trait.

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;

class Index
{
    public function __construct(ExtendedPdoInterface $pdo)
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;

class Index
{
    use AuraSqlInject;

    public function onGet()
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```

`Ray.AuraSqlModule` contains [Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) to help you build SQL for MySQL, Postgres, and other databases.

### perform() method

The `perform()` method binds an array of values to a SQL statement that has a single placeholder.

```php
$stm = 'SELECT * FROM test WHERE foo IN (:foo)';
$array = ['foo', 'bar', 'baz'];
```

With native PDO:

```php
// the native PDO way does not work (PHP Notice: Array to string conversion)
// Native PDO cannot bind an array to :foo
$sth = $pdo->prepare($stm);
$sth->bindValue('foo', $array);
```

With Aura.Sql's ExtendedPDO:

```php
$stm = 'SELECT * FROM test WHERE foo IN (:foo)';
$values = ['foo' => ['foo', 'bar', 'baz']];
$sth = $pdo->perform($stm, $values);
```

`['foo', 'bar', 'baz']` is bound to `:foo`. You can inspect the actual query with `queryString`.

```php
echo $sth->queryString;
// the query string has been modified by ExtendedPdo to become
// "SELECT * FROM test WHERE foo IN ('foo', 'bar', 'baz')"
```

### fetch*() methods

Instead of repeating `prepare()`, `bindValue()`, and `execute()` to retrieve values from the database, the `fetch*()` methods reduce boilerplate code.
(They run `perform()` internally, so array placeholders are also supported.)

```php
$stm  = 'SELECT * FROM test WHERE foo = :foo AND bar = :bar';
$bind = ['foo' => 'baz', 'bar' => 'dib'];
// "fetch all" with native PDO
$pdo = new PDO(...);
$sth = $pdo->prepare($stm);
$sth->execute($bind);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

// "fetch all" with ExtendedPdo
$pdo = new ExtendedPdo(...);
$result = $pdo->fetchAll($stm, $bind);

// fetchAssoc() returns an associative array of all rows where the key is the
// first column value, and each row is keyed by column name.
$result = $pdo->fetchAssoc($stm, $bind);

// fetchGroup() is like fetchAssoc() except that the values aren't wrapped in
// arrays. Instead, single column values are returned as a single dimensional
// array and multiple columns are returned as an array of arrays.
// Set style to PDO::FETCH_NAMED when values are an array
// (i.e. there are more than two columns in the select)
$result = $pdo->fetchGroup($stm, $bind, $style = PDO::FETCH_COLUMN);

// fetchOne() returns the first row as an associative array keyed by column name.
$result = $pdo->fetchOne($stm, $bind);

// fetchPairs() returns an associative array mapping values from the first column
// to values from the second column.
$result = $pdo->fetchPairs($stm, $bind);

// fetchValue() returns the value of the first column.
$result = $pdo->fetchValue($stm, $bind);

// fetchAffected() returns the number of affected rows.
$stm = 'UPDATE test SET incr = incr + 1 WHERE foo = :foo AND bar = :bar';
$row_count = $pdo->fetchAffected($stm, $bind);
```

`fetchAll()`, `fetchAssoc()`, `fetchCol()`, and `fetchPairs()` accept an optional third argument: a callback applied to each row.

```php
$result = $pdo->fetchAssoc($stm, $bind, function (&$row) {
    // add a column to the row
    $row['my_new_col'] = 'Added this column from the callable.';
});
```

### yield*() methods

Use the `yield*()` methods to save memory. The `fetch*()` methods retrieve all rows at once, while the `yield*()` methods return an iterator.

```php
$stm  = 'SELECT * FROM test WHERE foo = :foo AND bar = :bar';
$bind = ['foo' => 'baz', 'bar' => 'dib'];

// Rows are associative arrays, like fetchAll()
foreach ($pdo->yieldAll($stm, $bind) as $row) {
    // ...
}

// The key is the first column value and each row is an associative array, like fetchAssoc()
foreach ($pdo->yieldAssoc($stm, $bind) as $key => $row) {
    // ...
}

// Returns the values of the first column, like fetchCol()
foreach ($pdo->yieldCol($stm, $bind) as $val) {
    // ...
}

// Returns key/value pairs from the first and second columns, like fetchPairs()
foreach ($pdo->yieldPairs($stm, $bind) as $key => $val) {
    // ...
}
```

### Replication

To automatically perform master / slave connection, specify the IP of the slave DB as the fourth argument.

```php?start_inline
$this->install(
  new AuraSqlModule(
    'mysql:host=localhost;dbname=test',
    'username',
    'password',
    'slave1,slave2' // specify slave IP as a comma separated value
  )
);
```

You will now have a slave db connection when using HTTP GET, or a master db connection in other HTTP methods.

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;
use PDO;

class User extends ResourceObject
{
    public $pdo;

    public function __construct(ExtendedPdoInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    public function onGet()
    {
         $this->pdo; // slave db
    }

    public function onPost($todo)
    {
         $this->pdo; // master db
    }
}
```

`$this->pdo` is overwritten if the method is annotated with`#[ReadOnlyConnection]` or`#[WriteConnection]`. The master / slave db connection corresponds to the attribute.

```php?start_inline
use Ray\AuraSqlModule\Annotation\ReadOnlyConnection;  // important
use Ray\AuraSqlModule\Annotation\WriteConnection;     // important

class User
{
    public $pdo; // override when #[ReadOnlyConnection] or #[WriteConnection] annotated method called

    public function onPost($todo)
    {
         $this->read();
    }

    #[ReadOnlyConnection]
    public function read()
    {
         $this->pdo; // slave db
    }

    #[WriteConnection]
    public function write()
    {
         $this->pdo; // master db
    }
}
```

### Connect to multiple databases

To receive multiple `PdoExtendedInterface` objects with different connection destinations, use `#[Named]` attribute.

```php?start_inline
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

#[Inject]
#[Named('log_db')]
public function setLoggerDb(ExtendedPdoInterface $pdo)
{
    // ...
}
```

In the module, you specify an identifier in `NamedPdoModule` and bind it.

```php?start_inline
$this->install(
  new NamedPdoModule(
    'log_db', // Type of database specified by #[Named]
    'mysql:host=localhost;dbname=log',
    'username',
    'pass',
    'slave1,slave2' // specify slave IP as a comma separated value
  )
);
```

### Transactions

Using the `#[Transactional]` attribute wraps methods with a transaction.

```php?start_inline
use Ray\AuraSqlModule\Annotation\Transactional;

// ....
    #[Transactional]
    public function write()
    {
         // \Ray\AuraSqlModule\Exception\RollbackException thrown if it failed.
    }
```

To do transactions on multiple connected databases, specify properties in the `#[Transactional]` attribute.
If not specified, it becomes `['pdo']`.

```php?start_inline
#[Transactional(['pdo', 'userDb'])]
public function write()
```

It is run as follows.

```php?start_inline
$this->pdo->beginTransaction()
$this->userDb->beginTransaction()

// ...

$this->pdo->commit();
$this->userDb->commit();
```

## Aura.SqlQuery

[Aura.Sql](https://github.com/auraphp/Aura.Sql) is an extension of PDO. [Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) provides database-specific SQL builder for MySQL, Postgres, SQLite or Microsoft SQL Server.

Specify the database and install it with the application module `src/Module/AppModule.php`.

```php?start_inline
// ...
$this->install(new AuraSqlQueryModule('mysql')); // pgsql, sqlite, or sqlsrv
```

### SELECT

The resource receives the DB Query Builder object and constructs a SELECT query using the following methods.
You can also call the method multiple times in any order.

```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlSelectInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlSelectInject;

    public function onGet()
    {
        $this->select
            ->distinct()                    // SELECT DISTINCT
            ->cols([                        // select these columns
                'id',                       // column name
                'name AS namecol',          // one way of aliasing
                'col_name' => 'col_alias',  // another way of aliasing
                'COUNT(foo) AS foo_count'   // embed calculations directly
            ])
            ->from('foo AS f')              // FROM these tables
            ->fromSubselect(                // FROM sub-select AS my_sub
                'SELECT ...',
                'my_sub'
            )
            ->join(                         // JOIN ...
                'LEFT',                     // left/inner/natural/etc
                'doom AS d'                 // this table name
                'foo.id = d.foo_id'         // ON these conditions
            )
            ->joinSubSelect(                // JOIN to a sub-select
                'INNER',                    // left/inner/natural/etc
                'SELECT ...',               // the subselect to join on
                'subjoin'                   // AS this name
                'sub.id = foo.id'           // ON these conditions
            )
            ->where('bar > :bar')           // AND WHERE these conditions
            ->where('zim = ?', 'zim_val')   // bind 'zim_val' to the ? placeholder
            ->orWhere('baz < :baz')         // OR WHERE these conditions
            ->groupBy(['dib'])              // GROUP BY these columns
            ->having('foo = :foo')          // AND HAVING these conditions
            ->having('bar > ?', 'bar_val')  // bind 'bar_val' to the ? placeholder
            ->orHaving('baz < :baz')        // OR HAVING these conditions
            ->orderBy(['baz'])              // ORDER BY these columns
            ->limit(10)                     // LIMIT 10
            ->offset(40)                    // OFFSET 40
            ->forUpdate()                   // FOR UPDATE
            ->union()                       // UNION with a followup SELECT
            ->unionAll()                    // UNION ALL with a followup SELECT
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to named placeholders
                'bar' => 'bar_val',
                'baz' => 'baz_val',
            ]);

        $sth = $this->pdo->prepare($this->select->getStatement());

        // bind the values and execute
        $sth->execute($this->select->getBindValues());
        $result = $sth->fetch(\PDO::FETCH_ASSOC);
        // or
        // $result = $this->pdo->fetchAssoc($stm, $bind);
```

The created queries are queried as strings with the `getStatement()`.

### INSERT

#### Single row INSERT


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlInsertInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlInsertInject;

    public function onPost()
    {
        $this->insert
            ->into('foo')                   // INTO this table
            ->cols([                        // bind values as "(col) VALUES (:col)"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values
                'bar' => 'foo',
                'baz' => 'zim',
            ]);

        $sth = $this->pdo->prepare($this->insert->getStatement());
        $sth->execute($this->insert->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->insert->getStatement(), this->insert->getBindValues());

        // get the last insert ID
        $name = $insert->getLastInsertIdName('id');
        $id = $pdo->lastInsertId($name);
```

The `cols()` method allows you to pass an array of key-value pairs where the key is the column name and the value is a bind value (not a raw value).

```php?start_inline
        $this->insert
            ->into('foo')                   // insert into this table
            ->cols([                        // insert these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
```

#### Multi-line INSERT

To do a multiple row INSERT, use the `addRow ()` method at the end of the first line. Then build the following query.

```php?start_inline
        // insert into this table
        $this->insert->into('foo');

        // set up the first row
        $this->insert->cols([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $this->insert->set('ts', 'NOW()');

        // set up the second row. the columns here are in a different order
        // than in the first row, but it doesn't matter; the INSERT object
        // keeps track and builds them the same order as the first row.
        $this->insert->addRow();
        $this->insert->set('ts', 'NOW()');
        $this->insert->cols([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);

        // set up further rows ...
        $this->insert->addRow();
        // ...

        // execute a bulk insert of all rows
        $sth = $this->pdo->prepare($insert->getStatement());
        $sth->execute($insert->getBindValues());

```

> Note: If you try to add a row without specifying the value of the first column in the first row, an exception will be thrown.
> Passing an associative array of columns to `addRow()` will be used on the next line. That is, you can not specify `col()` or `cols()` on the first line.

```php?start_inline
        // set up the first row
        $insert->addRow([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $insert->set('ts', 'NOW()');

        // set up the second row
        $insert->addRow([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);
        $insert->set('ts', 'NOW()');

        // etc.
```

You can also set the database at once using `addRows()`.

```php?start_inline
        $rows = [
            [
                'bar' => 'bar-0',
                'baz' => 'baz-0'
            ],
            [
                'bar' => 'bar-1',
                'baz' => 'baz-1'
            ],
        ];
        $this->insert->addRows($rows);
```

### UPDATE
Use the following methods to construct an UPDATE query. You can also call the method multiple times in any order.

```php?start_inline
        $this->update
            ->table('foo')                  // update this table
            ->cols([                        // bind values as "SET bar = :bar"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->update->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->update->getStatement(), $this->update->getBindValues());
```

You can also pass an associative array to `cols()` with the key as the column name and the value as the bound value (not the RAW value).

```php?start_inline

        $this-update->table('foo')          // update this table
            ->cols([                        // update these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
?>
```

### DELETE
Use the following methods to construct a DELETE query. You can also call the method multiple times in any order.
```php?start_inline
        $this->delete
            ->from('foo')                   // FROM this table
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->delete->getBindValues());
```

### Pagination

[ray/aura-sql-module](https://packagist.org/packages/ray/aura-sql-module) supports pagination (page splitting) in both Aura.Sql raw SQL and Ray.AuraSqlQuery query builder.
We create a pager using the `newInstance()` with a uri_template, binding values and the number of items per page. You can access the page by $page[$number].

#### Aura.Sql
AuraSqlPagerFactoryInterface

```php?start_inline
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $sql, $params, 10, '/?page={page}&category=sports'); // 10 items per page
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
// $page->data // sliced data (array|\Traversable)
// $page->current; (int)
// $page->total (int)
// $page->hasNext (bool)
// $page->hasPrevious (bool)
// $page->maxPerPage; (int)
// (string) $page // pager html (string)
```

#### Aura.SqlQuery
AuraSqlQueryPagerFactoryInterface

```php?start_inline
// for Select
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlQueryPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $select, 10, '/?page={page}&category=sports');
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
```
> Note: Although the Aura.Sql edits the raw SQL directly, it currently only supports the MySQL LIMIT clause format.

`$page` is iterable.

```php?start_inline
foreach ($page as $row) {
 // Process each row
}
```
To change the pager HTML template, change the binding of `TemplateInterface`.
For details about templates, please see [Pagerfanta](https://github.com/whiteoctober/Pagerfanta#views).

```php?start_inline
use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap3Template;
use Ray\AuraSqlModule\Annotation\PagerViewOption;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->bind(TemplateInterface::class)->to(TwitterBootstrap3Template::class);
        $this->bind()->annotatedWith(PagerViewOption::class)->toInstance($pagerViewOption);
    }
}
```
