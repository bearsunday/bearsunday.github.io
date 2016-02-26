---
layout: docs-ja
title: クイックAPI
category: Manual
permalink: /manuals/1.0/ja/quick-api.html
---


# クイックAPI


[API用のパッケージ](https://github.com/koriym/Koriym.DbAppPackage)と以下の４つのSQLファイルを使ってWeb APIを作成します。

{% highlight sql %}
SELECT id, title, completed FROM task;
SELECT id, title, completed FROM task WHERE id = :id;
INSERT task (title, completed, created) VALUES (:title, :completed, :created);
UPDATE task SET completed = 1 WHERE id = :id;
{% endhighlight %}

# インストール

API用プロジェクトのスケルトン`dev-api`をcomposerインストールします。　

```
composer create-project bear/skeleton MyVendor.Task dev-api
```

ベンダー名とパッケージ名を入力します。

```
What is the vendor name ?

(MyVendor):

What is the project name ?

(MyProject):Task
```

# データベース

`.env`ファイルでデータベース接続を設定します。`DB_DSN`のフォーマットは[PDO](http://php.net/manual/ja/pdo.connections.php)です。環境に合わせて適宜変更します。

```
DB_DSN=mysql:host=localhost;dbname=task
DB_USER=root
DB_PASS=
DB_READ=
```

スレイブDBを利用する場合は複数のサーバーリストをカンマ区切りで`DB_READ`に設定します。

```
DB_READ=slave1.example.com,slave2.example.com
```

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


{% highlight php %}
<?php
/** @var $router \BEAR\Package\Provide\Router\AuraRoute */
$router->route('/task', '/task/{id}');
{% endhighlight %}

`POST`や`PATCH`もそれぞ対応するメソッドにルートされます。

# SQL

SQLフィイルを設置します。

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
INSERT task (title, completed, created) VALUES (:title, :completed, :created);
```

`var/db/sql/task_update.sql`

```sql
UPDATE task SET completed = 1 WHERE id = :id;
```

# リソース

SQLを実行するリソースクラスを作成します。

`src/Resource/App/Task.php`

{% highlight php %}
<?php
class Task extends ResourceObject
{
    use AuraSqlInject;
    use NowInject;
    use QueryLocatorInject;

    public function onGet($id = null)
    {
        $this->body = $id ?
            $this->pdo->fetchOne($this->query['task_item'], ['id' => $id]) :
            $this->pdo->fetchAssoc($this->query['task_list']);

        return $this;
    }

    public function onPost($title)
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

    public function onPatch($id)
    {
        $params = [
            'id' => $id,
            'completed' => true
        ];
        $this->pdo->perform($this->query['task_update'], $params);

        return $this;
    }
}
{% endhighlight %}

# 実行

まずはリソースをコンソールで実行します。

```
$ php bootstrap/api.php options /task
$ php bootstrap/api.php post '/task?title=run'
$ php bootstrap/api.php patch /task/1
$ php bootstrap/api.php get /task/1
```

次に同じリソースをWebでアクセスするためにWebサーバーをスタートさせます。

```
php -S 127.0.0.1:8080 bootstrap/api.php 
```

`curl`コマンドでアクセスします。

```
curl -i -X OPTIONS http://127.0.0.1:8080/task
curl -i -X POST --form "title=mail" http://127.0.0.1:8080/task
curl -i -X PATCH http://127.0.0.1:8080/task/1
curl -i -X GET http://127.0.0.1:8080/task/1
```

## スクリプト

再度環境構築をするために`composer.json`の`scripts`に以下のコードを追加します。

    "scripts": {
        "setup": [
            "php bin/create_db.php",
            "php vendor/bin/phinx migrate -c var/db/phinx.php"
        ],

他の環境でもsetupコマンドで同じ環境が構築できます。

```
composer setup
```

## まとめ

DBの接続情報を`.env`で設定して、SQLのファイルをHTTPのURLにマップされたリソースから呼び出してWeb APIを作成しました。

`sql`フォルダに集められたSQLは一覧やテストも簡単ですがSQL文を直接リソースクラスに記述することもできます。条件によって動的に変わるSQLは[Aura.SqlQuery](http://bearsunday.github.io/manuals/1.0/ja/database.html#aurasqlquery)クエリービルダーを利用します。

