---
layout: docs-ja
title: データベース
category: Manual
permalink: /manuals/1.0/ja/database.html
---
# データベース

データベースライブラリの利用のため`Aura.Sql`、`Doctrine DBAL`, `CakeDB`などのモジュールが用意されています。

# Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPHPのPDOを拡張したデータベースライブラリです。

### インストール

composerで`Ray.AuraSqlModule`をインストールします。

```bash
composer require ray/aura-sql-module
```

アプリケーションモジュール`src/Module/AppModule.php`で`AuraSqlModule`をインストールします。

```php?start_inline
use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\AuraSqlModule\AuraSqlModule; // この行を追加
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new PackageModule));
        $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', 'username', 'password'));  // この行を追加
    }
}
```

これでDIの設定が整いました。コンストラクタや`AuraSqlInject`トレイトを利用してDBオブジェクトを受け取ります。

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

`Ray.AuraSqlModule`は[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)を含んでいてMySQLやPostgresなどのSQLを組み立てるのに利用できます。

## リプリケーションのための接続

マスター／スレーブの接続を自動で行うためには接続オブジェクト`$locator`を作成して`AuraSqlReplicationModule`をインストールします。

```php?start_inline
use Ray\Di\AbstractModule;
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\AuraSqlModule\Annotation\AuraSqlConfig;
use Aura\Sql\ConnectionLocator;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $locator = new ConnectionLocator;
        $locator->setWrite('master', new Connection('mysql:host=localhost;dbname=master', 'id', 'pass'));
        $locator->setRead('slave1',  new Connection('mysql:host=localhost;dbname=slave1', 'id', 'pass'));
        $locator->setRead('slave2',  new Connection('mysql:host=localhost;dbname=slave2', 'id', 'pass'));
        $this->install(new AuraSqlReplicationModule($locator));
    }
}

```

これでHTTPリクエストがGETの時がスレーブDB、その他のメソッドの時はマスターDBのDBオブジェクトがコンスタラクタに渡されます。

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

`@ReadOnlyConnection`、`@WriteConnection`でアノテートされたメソッドはメソッド名に関わらず、呼ばれた時にアノテーションに応じたDBオブジェクトが`$this->pdo`に上書きされます。

```php?start_inline
use Ray\AuraSqlModule\Annotation\ReadOnlyConnection;  // important
use Ray\AuraSqlModule\Annotation\WriteConnection;     // important

class User
{
    public $pdo; // @ReadOnlyConnectionや@WriteConnectionのメソッドが呼ばれた時に上書きされる

    public function onPost($todo)
    {
         $this->read();
    }

    /**
     * @ReadOnlyConnection
     */
    public function read()
    {
         $this->pdo; // slave db
    }

    /**
     * @WriteConnection
     */
    public function write()
    {
         $this->pdo; // master db
    }
}
```

## 複数のデータベースに接続

接続先の違う複数の`PdoExtendedInterface`オブジェクトを受け取るためには
`@Named`アノテーションで指定します。

```php?start_inline
/**
 * @Inject
 * @Named("log_db")
 */
public function setLoggerDb(ExtendedPdoInterface $pdo)
{
    // ...
}
```

`NamedPdoModule`で識別子を指定して束縛します。

```php?start_inline
$this->install(new NamedPdoModule('log_db', 'mysql:host=localhost;dbname=log', 'username',
$this->install(new NamedPdoModule('job_db', 'mysql:host=localhost;dbname=job', 'username',
```

リプリケーションの場合には二番目の引数に識別子を指定します。

```php?start_inline
$logDblocator = new ConnectionLocator;
$logDblocator->setWrite('master', new Connection('mysql:host=localhost;dbname=master', 'id', 'pass'));
$logDblocator->setRead('slave1',  new Connection('mysql:host=localhost;dbname=slave1', 'id', 'pass'));
$logDblocator->setRead('slave2',  new Connection('mysql:host=localhost;dbname=slave2', 'id', 'pass'));
$this->install(new AuraSqlReplicationModule($logDblocator, 'log_db'));
```

## トランザクション

`@Transactional`とアノテートしたメソッドはトランザクション管理されます。

```php?start_inline
use Ray\AuraSqlModule\Annotation\Transactional;

// ....
    /**
     * @Transactional
     */
    public function write()
    {
         // 例外発生したら\Ray\AuraSqlModule\Exception\RollbackExceptionに
    }
```

複数接続したデータベースのトランザクションを行うためには`@Transactional`アノテーションにプロパティを指定します。
指定しない場合は`{"pdo"}`になります。

```php?start_inline
/**
 * @Transactional({"pdo", "userDb"})
 */
public function write()
```

以下のように実行されます。

```php?start_inline
$this->pdo->beginTransaction()
$this->userDb->beginTransaction()

// ...

$this->pdo->commit();
$this->userDb->commit();
```

# Aura.SqlQuery

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPDOを拡張したライブラリですが、[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)は MySQL、Postgres,、SQLiteあるいは Microsoft SQL Serverといったデータベース固有のSQLのビルダーを提供します。

データベースを指定してアプリケーションモジュール`src/Module/AppModule.php`でインストールします。

```php?start_inline
// ...
$this->install(new AuraSqlQueryModule('mysql')); // pgsql, sqlite, or sqlsrv
```

## SELECT

リソースではDBクエリービルダオブジェクトを受け取り、下記のメソッドを使ってSELECTクエリーを組み立てます。
メソッドに特定の順番はなく複数回呼ぶことこともできます。

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

組み立てたクエリーは`getStatement()`で文字列にしてクエリーを行います。

## INSERT

### 単一行のINSERT


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

`cols()`メソッドはキーがコラム名、値をバインドする値にした連想配列を渡すこともできます。

```php?start_inline
        $this->insert
            ->into('foo')                   // insert into this table
            ->cols([                        // insert these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
```

### 複数行のINSERT

複数の行のINSERTを行うためには、最初の行の最後で`addRow()`メソッドを使います。その後に次のクエリーを組み立てます。

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

> 注:最初の行で始めて現れた列の値を指定しないで、行を追加しようとすると例外が投げられます。
> `addRow()`に列の連想配列を渡すと次の行で使われます。つまり最初の行で`col()`や`cols()`を指定しないこともできます。

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

`addRows()`を使ってデータベースを一度にセットすることもできます。

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

## UPDATE
下記のメソッドを使ってUPDATEクエリーを組み立てます。 メソッドに特定の順番はなく複数回呼ぶことこともできます。

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

キーを列名、値をバインドされた値（RAW値ではなりません）にした連想配列を`cols()`に渡すこともできます。

```php?start_inline

        $this-update->table('foo')          // update this table
            ->cols([                        // update these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
?>
```

## DELETE
下記のメソッドを使ってDELETEクエリーを組み立てます。 メソッドに特定の順番はなく複数回呼ぶことこともできます。
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

## パジネーション

[ray/aura-sql-module](https://packagist.org/packages/ray/aura-sql-module)はRay.Sqlの生SQL、Ray.AuraSqlQueryのクエリービルダー双方でパジネーション（ページ分割）をサポートしています。
バインドする値と１ページあたりのアイテム数、それに{page}をページ番号にしたuri_templateでページャーファクトリーを`newInstance()`で生成して、ページ番号で配列アクセスします。

### Aura.Sql用
AuraSqlPagerFactoryInterface
```php?start_inline
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $sql, $params, 10, '/?page={page}&category=sports'); // 10 items per page
$page = $pager[2]; // page 2
```

### Aura.SqlQuery用
AuraSqlQueryPagerFactoryInterface
```php?start_inline
// for Select
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlQueryPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $select, 10, '/?page={page}&category=sports');
$page = $pager[2]; // page 2
```

> 注：Aura.Sqlは生SQLを直接編集していますが現在MySql形式のLIMIT句しか対応していません。

# Doctrine DBAL

[Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html)はDoctrineが提供しているデータベースの抽象化レイヤーです。

composerで`Ray.DbalModule`をインストールします。

```bash
composer require ray/dbal-module
```

アプリケーションモジュールで`DbalModule`をインストールします。

```php?start_inline
use Ray\DbalModule\DbalModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new DbalModule('driver=pdo_sqlite&memory=true');
    }
}
```

これでDIの設定が整いました。
`DbalInject`トレイトを利用すると`$this->db`にDBオブジェクトがインジェクトされます。

```php?start_inline
use Ray\DbalModule\DbalInject;

class Index
{
    use DbalInject;

    public function onGet()
    {
        return $this->db; // \Doctrine\DBAL\Driver\Connection
    }
}
```

## 複数のデータベースに接続

複数のデータベースの接続には二番目の引数に識別子を指定します。

```php?start_inline
$this->install(new DbalModule($logDsn, 'log_db');
$this->install(new DbalModule($jobDsn, 'job_db');
```

```php?start_inline
/**
 * @Inject
 * @Named("log_db")
 */
public function setLogDb(Connection $logDb)
```

[MasterSlaveConnection](http://www.doctrine-project.org/api/dbal/2.0/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html)というリプリケーションのためのマスター／スレーブ接続が標準で用意されています。

# CakeDb

**CakeDb**はアクティブレコードとデータマッパーパターンのアイデアを使ったORMで、素早くシンプルにORMを使うことができます。CakePHP3で提供されているORMと同じものです。

composerで`Ray.CakeDbModule`をインストールします。

```bash
composer require ray/cake-database-module ~1.0
```

インストールの方法については[Ray.CakeDbModule](https://github.com/ray-di/Ray.CakeDbModule)を、ORMの利用には[CakePHP3 Database Access & ORM](http://book.cakephp.org/3.0/en/orm.html)をご覧ください。

Ray.CakeDbModuleはCakePHP3のORMを開発したJose([@lorenzo](https://github.com/lorenzo))さんにより提供されています。

# 環境による接続先の変更

[phpdotenv](https://github.com/vlucas/phpdotenv)ライブラリなどを利用して環境先に応じた接続先を設定します。実装例の[Ex.Package](https://github.com/BEARSunday/Ex.Package)をご覧ください。
