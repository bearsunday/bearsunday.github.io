---
layout: docs-ja
title: Aura.Sql
category: Manual
permalink: /manuals/1.0/ja/database_aura.html
---
# Ray.AuraSqlModule

`Ray.AuraSqlModule`はPDO拡張のAura.SqlとクエリビルダーAura.SqlQuery、その他にデータベースクエリー結果のページネーションのためのライブラリを提供します。

## インストール

composerで`ray/aura-sql-module`をインストールします。

```bash
composer require ray/aura-sql-module
```

アプリケーションモジュール`src/Module/AppModule.php`で`AuraSqlModule`をインストールします。

```php
use BEAR\Package\AbstractAppModule;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\AuraSqlModule\AuraSqlModule; // この行を追加

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(
          new AuraSqlModule(
            'mysql:host=localhost;dbname=test' // またはgetenv('PDO_DSN')
            'username',
            'password',
          )
        );  // この行を追加
        $this->install(new PackageModule));
    }
}
```

設定時に直接値を指定するのではなく、実行時に毎回環境変数から取得するためには`AuraSqlEnvModule`を使います。
接続先と認証情報の値を直接指定する代わりに、該当する環境変数のキーを渡します。

```php
        $this->install(
            new AuraSqlEnvModule(
                'PDO_DSN',             // getenv('PDO_DSN')
                'PDO_USER',            // getenv('PDO_USER')
                'PDO_PASSWORD',        // getenv('PDO_PASSWORD')
                'PDO_SLAVE'            // getenv('PDO_SLAVE')
                $options,              // optional key=>value array of driver-specific connection options
                $queris                // Queries to execute after the connection.
        );
```

## Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPHPのPDOを拡張したデータベースライブラリです。
コンストラクタインジェクションや`AuraSqlInject`トレイトを利用して`PDO`を拡張したDBオブジェクト`ExtendedPDO`を受け取ります。

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;

class Index
{
    public function __construct(
        private readonly ExtendedPdoInterface $pdo
    ) {}
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

### perform() メソッド

`perform()`メソッドは、1つのプレイスホルダーしかないSQLに配列の値をバインドすることが出来ます。

```php?start_inline
$stm = 'SELECT * FROM test WHERE foo IN (:foo)'
$array = ['foo', 'bar', 'baz'];
```

既存のPDOの場合

```php?start_inline
// the native PDO way does not work (PHP Notice:  Array to string conversion)
// ネイティブのPDOでは`:foo`に配列を指定することは出来ません
$sth = $pdo->prepare($stm);
$sth->bindValue('foo', $array);
```

Aura.SqlのExtendedPDOの場合

```php?start_inline
$stm = 'SELECT * FROM test WHERE foo IN (:foo)'
$values = ['foo' => ['foo', 'bar', 'baz']];
$sth = $pdo->perform($stm, $values);
```

`:foo`に`['foo', 'bar', 'baz']`がバインドがされます。`queryString`で実際のクエリーを調べることが出来ます。

```php?start_inline
echo $sth->queryString;
// the query string has been modified by ExtendedPdo to become
// "SELECT * FROM test WHERE foo IN ('foo', 'bar', 'baz')"
```

### fetch*() メソッド

`prepare()`、`bindValue()`、 `execute()`を繰り返してデータベースから値を取得する代わりに`fetch*()`メソッドを使うとボイラープレートコードを減らすことができます。
（内部では`perform()`メソッドを実行しているので配列のプレイスホルダーもサポートしています）

```php?start_inline
$stm  = 'SELECT * FROM test WHERE foo = :foo AND bar = :bar';
$bind = array('foo' => 'baz', 'bar' => 'dib');
// ネイティブのPDOで"fetch all"を行う場合
$pdo = new PDO(...);
$sth = $pdo->prepare($stm);
$sth->execute($bind);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

// ExtendedPdoで"fetch all"を行う場合
$pdo = new ExtendedPdo(...);
$result = $pdo->fetchAll($stm, $bind);

// fetchAssoc()は全ての行がコラム名のキーを持つ連想配列が返ります。
$result = $pdo->fetchAssoc($stm, $bind);

// fetchGroup() is like fetchAssoc() except that the values aren't wrapped in
// arrays. Instead, single column values are returned as a single dimensional
// array and multiple columns are returned as an array of arrays
// Set style to PDO::FETCH_NAMED when values are an array
// (i.e. there are more than two columns in the select)
$result = $pdo->fetchGroup($stm, $bind, $style = PDO::FETCH_COLUMN)

// fetchOne()は最初の行をキーをコラム名にした連想配列で返します。
$result = $pdo->fetchOne($stm, $bind);

// fetchPairs()は最初の列の値をキーに二番目の列の値を値にした連想配列を返します  
$result = $pdo->fetchPairs($stm, $bind);

// fetchValue()は最初の列の値を返します。
$result = $pdo->fetchValue($stm, $bind);

// fetchAffected()は影響を受けた行数を返します。
$stm = "UPDATE test SET incr = incr + 1 WHERE foo = :foo AND bar = :bar";
$row_count = $pdo->fetchAffected($stm, $bind);
?>
```

`fetchAll()`, `fetchAssoc()`, `fetchCol()`, 及び `fetchPairs()`のメソッドは三番目のオプションの引数に、それぞれの列に適用されるコールバックを指定することができます。

```php?start_inline
$result = $pdo->fetchAssoc($stm, $bind, function (&$row) {
    // add a column to the row
    $row['my_new_col'] = 'Added this column from the callable.';
});
?>
```
### yield*() メソッド

メモリを節約するために`yield*()`メソッドを使うことができます。 `fetch*()`メソッドは全ての行を一度に取得しますが、
`yield*()`メソッドはイテレーターが返ります。

```php
$stm  = 'SELECT * FROM test WHERE foo = :foo AND bar = :bar';
$bind = array('foo' => 'baz', 'bar' => 'dib');

// fetchAll()のように行は連想配列です
foreach ($pdo->yieldAll($stm, $bind) as $row) {
    // ...
}

// fetchAssoc()のようにキーが最初の列名で行が連想配列です。
foreach ($pdo->yieldAssoc($stm, $bind) as $key => $row) {
    // ...
}

// fetchCol()のように最初の列が値になった値を返します。
foreach ($pdo->yieldCol($stm, $bind) as $val) {
    // ...
}

// fetchPairs()と同様に最初の列からキー/バリューのペアの値を返します。
foreach ($pdo->yieldPairs($stm, $bind) as $key => $val) {
    // ...
}
```

## リプリケーション

マスター／スレーブ構成のデータベース接続を行うためには4つ目の引数にスレーブDBのホストを指定します。

```php?start_inline
$this->install(
  new AuraSqlModule(
    'mysql:host=localhost;dbname=test',
    'username',
    'password',
    'slave1,slave2' // スレーブのホストをカンマ区切りで指定
  )
);
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

## 複数データベースの接続

接続先の異なるデータベースのPDOインスタンスをインジェクトするには識別子[^qualifier]をつけます。

```php
    public function __constrcut(
        private readonly #[Log] ExtendedPdoInterface $logDb,
        private readonly #[Mail] ExtendedPdoInterface $mailDb,
    ){}
```

[^qualifier]: 識別子（クオリファイアー）についてはRay.Diのマニュアルの[束縛アトリビュート](https://ray-di.github.io/manuals/1.0/ja/binding_attributes.html)をご覧ください。

`NamedPdoModule`でその識別子と接続情報を指定してインストールします。

```php
class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new NamedPdoModule(Log::class, 'mysql:host=localhost;dbname=log', 'username', 
        $this->install(new NamedPdoModule(Mail::class, 'mysql:host=localhost;dbname=mail', 'username', 
    }
}
```

接続情報を環境変数から都度取得するときはNamedPdoEnvModuleを使います。

```php
class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new NamedPdoEnvModule(Log::class, 'LOG_DSN', 'LOG_USERNAME',  
        $this->install(new NamedPdoEnvModule(Mail::class, 'MAIL_DSN', 'MAIL_USERNAME', 
    }
}
```

## トランザクション

`#[Transactional]`アトリビュートを追加したメソッドはトランザクション管理されます。

```php?start_inline
use Ray\AuraSqlModule\Annotation\Transactional;

// ....
    #[Transactional]
    public function write()
    {
         // 例外発生したら\Ray\AuraSqlModule\Exception\RollbackExceptionに
    }
```

複数接続したデータベースのトランザクションを行うためには`@Transactional`アノテーションにプロパティを指定します。
指定しない場合は`{"pdo"}`になります。

```php?start_inline
#[Transactional({"pdo", "userDb"})]
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

## Aura.SqlQuery

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPDOを拡張したライブラリですが、[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)は MySQL、Postgres,、SQLiteあるいは Microsoft SQL Serverといったデータベース固有のSQLのビルダーを提供します。

データベースを指定してアプリケーションモジュール`src/Module/AppModule.php`でインストールします。

```php?start_inline
// ...
$this->install(new AuraSqlQueryModule('mysql')); // pgsql, sqlite, or sqlsrv
```

### SELECT

リソースではDBクエリービルダオブジェクトを受け取り、下記のメソッドを使ってSELECTクエリーを組み立てます。
メソッドに特定の順番はなく複数回呼ぶことこともできます。

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;
use Aura\SqlQuery\Common\SelectInterface;

class User extend ResourceObject
{
    public function __construct(
        private readonly ExtendedPdoInterface $pdo,
        private readonly SelectInterface $select
    ) {}

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

### INSERT

#### 単一行のINSERT


```php?start_inline
class User extend ResourceObject
{
    public function __construct(
        private readonly ExtendedPdoInterface $pdo,
        private readonly SelectInterface $select
    ) {}

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

#### 複数行のINSERT

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

### UPDATE
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

### DELETE
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

### パジネーション

[ray/aura-sql-module](https://packagist.org/packages/ray/aura-sql-module)はRay.Sqlの生SQL、Ray.AuraSqlQueryのクエリービルダー双方でパジネーション（ページ分割）をサポートしています。
バインドする値と１ページあたりのアイテム数、それに{page}をページ番号にしたuri_templateでページャーファクトリーを`newInstance()`で生成して、ページ番号で配列アクセスします。

#### Aura.Sql用
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

#### Aura.SqlQuery用
AuraSqlQueryPagerFactoryInterface

```php?start_inline
// for Select
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlQueryPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $select, 10, '/?page={page}&category=sports');
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
```
> 注：Aura.Sqlは生SQLを直接編集していますが現在MySql形式のLIMIT句しか対応していません。

`$page`はイテレータブルです。

```php?start_inline
foreach ($page as $row) {
 // 各行の処理
}
```
ページャーのリンクHTMLのテンプレートを変更するには`TemplateInterface`の束縛を変更します。
テンプレート詳細に関しては[Pagerfanta](https://github.com/whiteoctober/Pagerfanta#views)をご覧ください。

```php?start_inline
use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap3Template;
use Ray\AuraSqlModule\Annotation\PagerViewOption;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ..
        $this->bind(TemplateInterface::class)->to(TwitterBootstrap3Template::class);
        $this->bind()->annotatedWith(PagerViewOption::class)->toInstance($pagerViewOption);
    }
}
```

---
