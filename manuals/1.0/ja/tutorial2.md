---
layout: docs-ja
title: チュートリアル2
category: Manual
permalink: /manuals/1.0/ja/tutorial2.html
---
# チュートリアル２

このチュートリアルは以下の３つのSQLファイルをREST APIにして、そのAPIを使ったフォームを含むHTMLアプリケーションを作成します。

```sql
INSERT INTO ticket (id, title, description, status, assignee, created, updated) VALUES (:id, :title, :description, :status, :assignee, :created, :updated)
SELECT * FROM ticket
SELECT * FROM ticket WHERE id = :id
```

DBマイグレーションツールの[phinx](https://phinx.org/)を使い、プロダクション用にキャッシュを設定する[チュートリアル](/manuals/1.0/ja/tutorial.html)より本格的なものです。
被る箇所もありますが、おさらいのつもりでトライして見ましょう。

## プロジェクト作成

まずプロジェクトを作成します。

```bash
composer create-project bear/skeleton MyVendor.Ticket
```
**vendor**名を`MyVendor`に**project**名を`Ticket`として入力します。

次に依存するパッケージを一度にインストールします。

```bash
composer require  \
madapaja/twig-module \
koriym/now  \
bear/aura-router-module \
bear/api-doc  \
ray/query-module  \
ramsey/uuid  \
robmorgan/phinx
```

## データベース準備

### .env

.envにデータベース接続情報を用意します。

```bash
DB_DSN=mysql:host=localhost;dbname=ticket
DB_NAME=ticket
DB_USER=root
DB_PASS=''
DB_SLAVE=''
```

### DB作成

`bin/create_db.php`を作成して実行します。

```php
<?php
require dirname(__DIR__) . '/vendor/autoload.php';
(new josegonzalez\Dotenv\Loader( dirname(__DIR__) . '/.env'))->parse()->toEnv();
$db = new PDO('mysql:', $_ENV['DB_USER'], $_ENV['DB_PASS']);
$db->exec('CREATE DATABASE IF NOT EXISTS ' . $_ENV['DB_NAME']);
$db->exec('CREATE DATABASE IF NOT EXISTS ' . $_ENV['DB_NAME'] . '_test');
```

```php
php bin/create_db.php
```

`src/Module/AppModule.php`を編集してPackageModuleの前に`AuraSqlModule`をインストールします。

```php
class AppModule extends AbstractModule
{
    protected function configure()
    {
        $appDir = dirname(__DIR__, 2);
        (new Loader($appDir . '/.env'))->parse()->toEnv();
        $this->install(new AuraSqlModule($_ENV['DB_DSN'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_SLAVE'])); // <-- this
        $this->install(new PackageModule);
    }
}
```

### マイグレーション

設定情報ファイル`/var/db/phinx.php`を作成します。

```php
<?php
use Aura\Sql\ExtendedPdoInterface;
use MyVendor\Ticket\Module\AppModule;
use Ray\Di\Injector;

$pdo = (new Injector(new AppModule))->getInstance(ExtendedPdoInterface::class);
$name = $pdo->query("SELECT DATABASE()")->fetchColumn();
return [
    'paths' => [
        'migrations' => __DIR__ . '/migrations',
    ],
    'environments' => [
        'development' => [
            'name' => $name,
            'connection' => $pdo
        ],
        'test' => [
            'name' => $name . '_test',
            'connection' => $pdo
        ]
    ]
];

```

phinxで初期化して`Ticket`マイグレーションファイルを作成します。

```bash
mkdir -p var/db/migrations
vendor/bin/phinx create -c var/db/phinx.php Ticket
```

```bash
// ...
created var/db/migrations/20180602071503_ticket.php
```
作成された`var/db/migrations/{タイムスタンプ}_ticket.php`マイグレーションファイルを編集します。

```php
    public function change()
    {
        $table = $this->table('ticket');
        $table->addColumn('uuid', 'uuid')
            ->addColumn('title', 'string')
            ->addColumn('description', 'string')
            ->addColumn('status', 'string')
            ->addColumn('assignee', 'datetime')
            ->addColumn('modified', 'datetime')
            ->addColumn('deleted', 'boolean')
            ->create();
    }

    public function down()
    {
        $this->dropTable('ticket');
    }
```

マイグレーション実行します。

```bash
vendor/bin/phinx migrate -c var/db/phinx.php -e development
```

以上でデータベースにテーブルが作成できました。
新しい環境でこの作業を繰り返せるように`bin/setup.php`を編集します。

```php
<?php
passthru('php ./create_db.php');
passthru('vendor/bin/phinx migrate -c var/db/phinx.php -e development');
```

### setupコマンド

以下のコマンドで環境のセットアップが完了するようになりました。

```bash
composer setup
```

`composer install`と`composer setup`だけで環境構築が完了するようにすれば、セットアップのための詳しいドキュメンテーションは不要で、ディプロイにも便利です。
必要に応じてフォルダの書き込み権限のチェックやクリーニング、DIのcompileなどのウオームアップスクリプトも追加しましょう。

