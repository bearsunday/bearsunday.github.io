---
layout: docs-ja
title: クイックAPI
category: Manual
permalink: /manuals/1.0/ja/quick-api.html
---


# クイックAPI


このチュートリアルではデータベースを用いた[API用のパッケージ](https://github.com/koriym/Koriym.DbAppPackage)と以下の４つのSQLファイルを使ってWeb APIを作成します。

```sql
SELECT id, title, completed FROM task;
SELECT id, title, completed FROM task WHERE id = :id;
INSERT INTO task (title, completed, created) VALUES (:title, :completed, :created);
UPDATE task SET completed = 1 WHERE id = :id;
```

# インストール

API用プロジェクトのスケルトンをcomposerインストールします。　

```
composer create-project bear/skeleton MyVendor.Task
```

ベンダー名とパッケージ名をそれぞれ`MyVendor`、`Task`と入力します。

```
What is the vendor name ?

(MyVendor):

What is the project name ?

(MyProject):Task
```

# データベース

データベースパッケージをcomposerインストールします。

```
cd MyVendor.Task
composer require koriym/db-app-package
php vendor/koriym/db-app-package/bin/install.php
```

`AppModule::configure()`でインストールしている`PackageModule`を`DbAppPackage`に変更します。


```php?start_inline
class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        Dotenv::load([
            'filepath' => dirname(dirname(__DIR__)) . '/.env',
            'toEnv' => true
        ]);
        $this->install(new DbAppPackage($_ENV['DB_DSN'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_READ']));
    }
}
```

[`DbAppPackage`](https://github.com/koriym/Koriym.DbAppPackage)は`PackageModule`に以下の特定のパッケージを追加したものです。


* [Aura.Router v2](https://github.com/auraphp/Aura.Router/tree/2.x) Webルーター
* [Aura.Sql v2](https://github.com/auraphp/Aura.Sql) PDOを拡張したSQLアダプター
* [Aura.SqlQuery v2](https://github.com/auraphp/Aura.SqlQuery) クエリービルダー
* [Phinx](https://phinx.org/) データベースマイグレーション
* [Koriym.QueryLocator](https://github.com/koriym/Koriym.QueryLocator) SQLロケーター
* [Koriym.DevPdoDtatement](https://github.com/koriym/Koriym.DevPdoStatement) SQLクエリー調査のための開発用PDOStatement
* [Koriym.Now](https://github.com/koriym/Koriym.Now) 現在時刻

## 接続設定

`.env`ファイルでデータベース接続を設定します。`DB_DSN`のフォーマットは[PDO](http://php.net/manual/ja/pdo.connections.php)です。環境に合わせて適宜変更します。


MySQL

```
DB_DSN=mysql:host=localhost;dbname=task
DB_USER=root
DB_PASS=
DB_READ=
```

sqlite

```
DB_DSN=sqlite:/tmp/task.sq3
DB_USER=
DB_PASS=
DB_READ=
```

スレイブDBを利用する場合は複数のサーバーリストをカンマ区切りで`DB_READ`に設定します。

```
DB_READ=slave1.example.com,slave2.example.com
```

## 作成

データベースを作成します。（`sqlite`の場合は必要ありません）


```
php bin/create_db.php
```

[Phinx](http://docs.phinx.org/en/latest/)でマイグレーションファイルの生成を行います。

```
php vendor/bin/phinx create -c var/db/phinx.php MyNewMigration  
```

作成されたマイグレーション編集します。[[?]](http://docs.phinx.org/en/latest/migrations.html "Phinx マニュアル: Writing Migrations")

`var/db/20160222042911_my_new_migration.php`

```php
<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class MyNewMigration extends AbstractMigration
{
    public function change()
    {
        // create the table
        $table = $this->table('task');
        $table->addColumn('title', 'string', ['limit' => 100])
            ->addColumn('completed', 'text', ['limit' => MysqlAdapter::INT_TINY])
            ->addColumn('created', 'datetime')
            ->create();
    }
}
```

マイグレーションを実行します。

```
php vendor/bin/phinx migrate -c var/db/phinx.php
php vendor/bin/phinx migrate -c var/db/phinx.php -e test
```

# ルーティング

`GET /task/1`Webリクエストを`Task::onGet($id)`メソッドにルートするために`var/conf/aura.route.php`
を編集します。


```php?start_inline
/** @var $router \BEAR\Package\Provide\Router\AuraRoute */
$router->route('/task', '/task/{id}');
```

`POST`や`PATCH`もそれぞ対応するメソッドにルートされます。

# SQL

SQLファイルを設置します。

`var/db/sql/task_list.sql`

```sql
SELECT id, title, completed FROM task;
```
`var/db/sql/task_item.sql`

```sql
SELECT id, title, completed FROM task WHERE id = :id;
```

`var/db/sql/task_insert.sql`

```sql
INSERT INTO task (title, completed, created) VALUES (:title, :completed, :created);
```

`var/db/sql/task_update.sql`

```sql
UPDATE task SET completed = 1 WHERE id = :id;
```

# リソース

SQLを実行するリソースクラスを`src/Resource/App/Task.php`に作成します。

```php?start_inline
declare(strict_types=1);

namespace MyVendor\Task\Resource\App;

use BEAR\Resource\ResourceObject;
use Koriym\Now\NowInject;
use Koriym\QueryLocator\QueryLocatorInject;
use Ray\AuraSqlModule\AuraSqlInject;

class Task extends ResourceObject
{
    use AuraSqlInject;
    use NowInject;
    use QueryLocatorInject;

    public function onGet(string $id = null) : ResourceObject
    {
        $this->body = $id ?
            $this->pdo->fetchOne($this->query['task_item'], ['id' => $id]) :
            $this->pdo->fetchAssoc($this->query['task_list']);

        return $this;
    }

    public function onPost(string $title) : ResourceObject
    {
        $params = [
            'title' => $title,
            'created' => $this->now,
            'completed' => false
        ];
        $this->pdo->perform($this->query['task_insert'], $params);
        $id = $this->pdo->lastInsertId('id');
        $this->code = 201;
        $this->headers['Location'] = "/task?id={$id}";

        return $this;
    }

    public function onPatch(string $id) : ResourceObject
    {
        $params = [
            'id' => $id,
            'completed' => true
        ];
        $this->pdo->perform($this->query['task_update'], $params);

        return $this;
    }
}
```

# 実行

まずはリソースをコンソールで実行します。

```
php bin/app.php options /task
php bin/app.php post '/task?title=run'
php bin/app.php patch /task/1
php bin/app.php get /task/1
```

次に同じリソースをWebでアクセスするためにWebサーバーをスタートさせます。

```
php -S 127.0.0.1:8080 bin/app.php
```

`curl`コマンドでアクセスします。

```
curl -i -X OPTIONS http://127.0.0.1:8080/task
curl -i -X POST --form "title=mail" http://127.0.0.1:8080/task
curl -i -X PATCH http://127.0.0.1:8080/task/1
curl -i -X GET http://127.0.0.1:8080/task/1
```

## テスト

リソースの操作をテストするためにTaskリソースのテストコードを`/tests/Resource/App/TaskTest.php`に追加します。[[?]](https://phpunit.de/manual/current/ja/writing-tests-for-phpunit.html "PHPUnit 用のテストの書き方")

```php?start_inline

namespace MyVendor\Task\Resource\Page;

use BEAR\Resource\ResourceObject;
use Koriym\DbAppPackage\AbstractDatabaseTestCase;

class TaskTest extends AbstractDatabaseTestCase
{
    const URI = 'app://self/task';

    public function testOnPost()
    {
        $query = ['title' => 'shopping'];
        $page = $this->resource->post->uri(self::URI)->withQuery($query)->eager->request();
        $this->assertSame(201, $page->code);
        $this->assertArrayHasKey('Location', $page->headers);

        return $page;
    }

    /**
     * @depends testOnPost
     */
    public function testPatch(ResourceObject $page)
    {
        $uri = sprintf('app://self%s', $page->headers['Location']);
        $page = $this->resource->patch->uri($uri)->eager->request();
        $this->assertSame(200, $page->code);

        return $page;
    }

    /**
     * @depends testOnPost
     */
    public function testGet(ResourceObject $page)
    {
        $uri = sprintf('app://self%s', $page->headers['Location']);
        $page = $this->resource->get->uri($uri)->eager->request();
        $this->assertSame('shopping', $page->body['title']);
        $this->assertSame('1', $page->body['completed']);
    }
}
```

`phpunit`を実行します。

```
phpunit

...
OK (5 tests, 8 assertions)
```

`composer test`を実行するとコーディングスタイルをチェックする[phpcs](https://github.com/squizlabs/PHP_CodeSniffer/wiki), [phpmd](https://phpmd.org/about.html)も合わせて実行されます。

```
composer test
```

### フィクスチャ

[フィクスチャ](https://phpunit.de/manual/current/ja/database.html#database.set-up-fixture)とは、アプリケーションやデータベースの初期状態のことです。テストによっては特定のデータベースの状態を前提にしたいことがあります。MySql専用ですがフィクスチャの用意はを簡単です。

まずDBUnitのための接続情報を`tests/phpunit.xml`に追加します

```xml
<phpunit bootstrap="tests/bootstrap.php">
    <php>
        <var name="DB_DSN" value="mysql:host=localhost" />
        <var name="DB_USER" value="root" />
        <var name="DB_PASSWD" value="" />
        <var name="DB_DBNAME" value="task_test" />
    </php>
    ```
```

DBを保存したい状態にしておいて、テストクラスと同じ階層の`fixtures `ディレクトリに`mysqldump `コマンドで既存のデータベースの状態をdumpします。

```bash
mysqldump --xml -t -u [username] --password=[password] [database] > /path/to/file.xml
```

```bash
├── TaskTest.php
└── fixtures
    ├── tag.xml
    └── task.xml
```

複数のxmlは合成され１つのフィクスチャになり、テスト実行前のデータベース状態を再現できます。

## スクリプト

再度環境構築をするために`composer.json`の`scripts`に以下のコードを追加します。

    "scripts": {
        "setup": [
            "php bin/create_db.php",
            "php vendor/bin/phinx migrate -c var/db/phinx.php"
        ],

setupコマンドで環境構築できます。

```
composer setup
```

## その他の方法

依存をコンストラクタではなく、メソッドのパラメーターで受け取る事もできます。（アシスティッドインジェクション）

```php?start_inline
declare(strict_types=1);

namespace MyVendor\Task\Resource\App;

use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;
use Koriym\Now\NowInterface;
use Koriym\QueryLocator\QueryLocatorInterface;
use Ray\Di\Di\Assisted;

class Task extends ResourceObject
{
    /**
     * @Assisted({"pdo", "query"})
     */
    public function onGet(string $id = null, ExtendedPdoInterface $pdo = null, QueryLocatorInterface $query = null) : ResourceObject
    {
        $this->body = $id ?
            $pdo->fetchOne($query['task_item'], ['id' => $id]) :
            $pdo->fetchAssoc($query['task_list']);

        return $this;
    }

    /**
     * @Assisted({"pdo", "query", "now"})
     */
    public function onPost(string $title, ExtendedPdoInterface $pdo = null, QueryLocatorInterface $query = null, NowInterface $now = null) : ResourceObject
    {
        $params = [
            'title' => $title,
            'created' => (string) $now,
            'completed' => false
        ];
        $pdo->perform($query['task_insert'], $params);
        $id = $pdo->lastInsertId('id');
        $this->code = 201;
        $this->headers['Location'] = "/task?id={$id}";

        return $this;
    }

    /**
     * @Assisted({"pdo", "query"})
     */
    public function onPatch(string $id, ExtendedPdoInterface $pdo = null, QueryLocatorInterface $query = null) : ResourceObject
    {
        $params = [
            'id' => $id,
            'completed' => true
        ];
        $pdo->perform($query['task_update'], $params);

        return $this;
    }
}
```


条件によって動的に変わるSQLは[Aura.SqlQuery](http://bearsunday.github.io/manuals/1.0/ja/database.html#aurasqlquery)クエリービルダーを利用するのが良いでしょう。

```php?start_inline
declare(strict_types=1);

namespace MyVendor\Task\Resource\App;

use Aura\Sql\ExtendedPdoInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use BEAR\Resource\ResourceObject;
use Koriym\Now\NowInterface;
use Ray\Di\Di\Assisted;

/**
 * with assisted injection + query builder
 */
class TaskQb extends ResourceObject
{
    /**
     * @Assisted({"pdo", "select"})
     */
    public function onGet(string $id = null, ExtendedPdoInterface $pdo = null, SelectInterface $select = null) : ResourceObject
    {
        $select->cols(['id', 'title', 'completed'])->from('task');
        if ($id) {
            return $this->onGetItem($id, $pdo, $select);
        }
        $sql = $select->getStatement();
        $this->body = $pdo->fetchAssoc($sql);

        return $this;
    }

    /**
     * @Assisted({"pdo", "insert", "now"})
     */
    public function onPost(string $title, ExtendedPdoInterface $pdo = null, InsertInterface $insert = null, NowInterface $now = null) : ResourceObject
    {
        $params = [
            'title' => $title,
            'created' => (string) $now,
            'completed' => false
        ];
        $insert
            ->into('task')
            ->cols(['title', 'completed', 'created'])
            ->bindValues($params);
        $pdo->perform($insert->getStatement(), $insert->getBindValues());
        $name = $insert->getLastInsertIdName('id');
        $id = $pdo->lastInsertId($name);
        $this->code = 201;
        $this->headers['Location'] = "/task?id={$id}";

        return $this;
    }

    /**
     * @Assisted({"pdo", "query"})
     */
    public function onPatch(string $id, ExtendedPdoInterface $pdo = null, UpdateInterface $update = null) : ResourceObject
    {
        $values = [
            'id' => $id,
            'completed' => true
        ];
        $update
            ->table('task')
            ->cols(['title', 'completed', 'created'])
            ->where('id = :id')
            ->bindValues($values);
        $pdo->perform($update->getStatement(), $update->getBindValues());

        return $this;
    }

    private function onGetItem(string $id, ExtendedPdoInterface $pdo, SelectInterface $select) : ResourceObject
    {
        $select->where('id = :id')->bindValue('id', $id);
        $this->body = $pdo->fetchOne($select->getStatement(), $select->getBindValues());

        return $this;
    }
}

```

# まとめ

DBの接続情報を`.env`で設定して、SQLのファイルをHTTPのURLにマップされたリソースから呼び出してWeb APIを作成しました。テストでDB状態を復元するにはフィクスチャを使います。フィクスチャには`phpunit.xml`に接続情報が必要です。


`sql`フォルダに集められたSQLは一覧性に優れ、SQL単体テストも容易ですがSQL文を直接リソースクラスに記述することもできます。動的に生成するSQLクエリーはクエリービルダーが向いています。依存はコンストラクタだけではなく、実行時のメソッドでも受け取れます。


このチュートリアルで作成したプロジェクトの作成履歴はMyVendor.Taskの[commitログ](https://github.com/bearsunday/MyVendor.Task/commits/master)で見ることができます。

*(この[マニュアル](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/ja/quick-api.md)でわかりにくいところや、間違えているところがあれば[issue](https://github.com/bearsunday/bearsunday.github.io/issues)で教えてください。)*
