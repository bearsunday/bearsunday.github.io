---
layout: docs-ja
title: チュートリアル2
category: Manual
permalink: /manuals/1.0/ja/tutorial2.html
---
# チュートリアル2

このチュートリアルでは以下のツールを用いてタスク管理のチケット作成・取得用REST APIを作成し、疎結合で高品質なREST APIアプリケーションの開発をテスト駆動で学びます。[^1]

* CakePHPが開発してるフレームワーク非依存の[Phinx](https://book.cakephp.org/3.0/ja/phinx.html) DBマイグレーションツール
* JSONのデータ構造を定義しバリデーションやドキュメンテーションに利用する [Json Schema](https://qiita.com/kyoh86/items/e7de290e9a0e989fcc14)
* SQL文をSQL実行オブジェクトに変換しアプリケーションレイヤーとデータアクセスレイヤーを疎にする [ray/query-module](https://github.com/ray-di/Ray.QueryModule)
* UUIDや現在時刻をインジェクトする [IdentityValueModule](https://github.com/ray-di/Ray.IdentityValueModule)

作成するAPIはスキーマ定義され、自己記述（self-descriptive)性に優れた高品質なものです。

[チュートリアル](/manuals/1.0/ja/tutorial.html)と被る箇所もありますがおさらいのつもりでトライしてみましょう。
レポジトリは [bearsunday/tutorial2](https://github.com/bearsunday/tutorial2) にあります。うまくいかないときは見比べてみましょう。

## プロジェクト作成

プロジェクトスケルトンを作成します。

```
composer create-project bear/skeleton MyVendor.Ticket
```
**vendor**名を`MyVendor`に**project**名を`Ticket`として入力します。[^2]

## composerインストール

次に依存するパッケージを一度にインストールします。

```
composer require bear/aura-router-module ray/identity-value-module ray/query-module
composer require --dev robmorgan/phinx bear/api-doc
```

## モジュールインストール

`src/Module/AppModule.php`を編集してcomposerでインストールしたパッケージをモジュールインストールします。

```php
<?php
namespace MyVendor\Ticket\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;
use BEAR\Package\Provide\Router\AuraRouterModule;
use BEAR\Resource\Module\JsonSchemaLinkHeaderModule;
use BEAR\Resource\Module\JsonSchemaModule;
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\IdentityValueModule\IdentityValueModule;
use Ray\Query\SqlQueryModule;

class AppModule extends AbstractAppModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $appDir = $this->appMeta->appDir;
        require_once $appDir . '/env.php';
        $this->install(
            new AuraSqlModule(
                getenv('TKT_DB_DSN'),
                getenv('TKT_DB_USER'),
                getenv('TKT_DB_PASS'),
                getenv('TKT_DB_SLAVE')
            )
        );
        $this->install(new SqlQueryModule($appDir . '/var/sql'));
        $this->install(new IdentityValueModule);
        $this->install(
            new JsonSchemaModule(
                $appDir . '/var/json_schema',
                $appDir . '/var/json_validate'
            )
        );
        $this->install(new JsonSchemaLinkHeaderModule('http://www.example.com/'));
        $this->install(new AuraRouterModule($appDir . '/var/conf/aura.route.php'));
        $this->install(new PackageModule);
    }
}
```

テスト用のデータベースのために`src/Module/TestModule.php`も作成します。

```php
<?php
namespace MyVendor\Ticket\Module;

use BEAR\Package\AbstractAppModule;
use Ray\AuraSqlModule\AuraSqlModule;

class TestModule extends AbstractAppModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(
            new AuraSqlModule(
                getenv('TKT_DB_DSN') . '_test',
                getenv('TKT_DB_USER'),
                getenv('TKT_DB_PASS'),
                getenv('TKT_DB_SLAVE')
            )
        );
    }
}
```


モジュールが必要とするフォルダを作成します。

```bash
mkdir var/sql
mkdir var/json_schema
mkdir var/json_validate
```

## ルーターファイル

`tickets/{id}`のアクセスを`Ticket`クラスにルートするためにルーターファイルを`var/conf/aura.route.php`に設置します。

```php
<?php
/* @var \Aura\Router\Map $map */
$map->route('/ticket', '/tickets/{id}');
```

## データベース

プロジェクトルートフォルダの`.env`ファイルに接続情報を記述します。[^6]

```
TKT_DB_HOST=127.0.0.1
TKT_DB_NAME=ticket
TKT_DB_USER=root
TKT_DB_PASS=''
TKT_DB_SLAVE=''
TKT_DB_DSN=mysql:host=${TKT_DB_HOST};dbname=${TKT_DB_NAME}
```

`.env`はリポジトリにはコミットされません。`env.dist`に記述例を残して置きましょう。

```
cp .env .env.dist
// remove password, etc..
git add .env.dist
```

## マイグレーション

phinxの実行環境を整えます。

まずはphinxが利用するフォルダを作成します。

```bash
mkdir -p var/phinx/migrations
mkdir var/phinx/seeds
```

次に`.env`の接続情報をphinxで利用するために`var/phinx/phinx.php`を設置します。

```php
<?php
require_once dirname(__DIR__, 2) . '/env.php';
$devlopment = new \PDO(getenv('TKT_DB_DSN'), getenv('TKT_DB_USER'), getenv('TKT_DB_PASS'));
$test = new \PDO(getenv('TKT_DB_DSN') . '_test', getenv('TKT_DB_USER'), getenv('TKT_DB_PASS'));
return [
    'paths' => [
        'migrations' => __DIR__ . '/migrations',
    ],
    'environments' => [
        'development' => [
            'name' => $devlopment->query("SELECT DATABASE()")->fetchColumn(),
            'connection' => $devlopment
        ],
        'test' => [
            'name' => $test->query("SELECT DATABASE()")->fetchColumn(),
            'connection' => $test
        ]
    ]
];
```
## setupスクリプト

データベース作成やマイグレーションを簡単に実行できるように`bin/setup.php`を編集します。

```php
<?php
require dirname(__DIR__) . '/autoload.php';
require_once dirname(__DIR__) . '/env.php';
// dir
chdir(dirname(__DIR__));
passthru('rm -rf var/tmp/*');
passthru('chmod 775 var/tmp');
passthru('chmod 775 var/log');
// db
$pdo = new \PDO('mysql:host=' . getenv('TKT_DB_HOST'), getenv('TKT_DB_USER'), getenv('TKT_DB_PASS'));
$pdo->exec('CREATE DATABASE IF NOT EXISTS ' . getenv('TKT_DB_NAME'));
$pdo->exec('CREATE DATABASE IF NOT EXISTS ' . getenv('TKT_DB_NAME') . '_test');
passthru('./vendor/bin/phinx migrate -c var/phinx/phinx.php -e development');
passthru('./vendor/bin/phinx migrate -c var/phinx/phinx.php -e test');
```

実行してデータベースを作成します。

```
composer setup
```
```
Phinx by CakePHP - https://phinx.org. 0.10.6

...
using database ticket_test
```

次に`ticket`テーブルを作成するためにマイグレーションクラスを作成します。

```
./vendor/bin/phinx create Ticket -c var/phinx/phinx.php
```
```
Phinx by CakePHP - https://phinx.org. 0.10.6

...
created var/phinx/migrations/20180920054037_ticket.php
```

`var/phinx/migrations/{current_date}_ticket.php`を編集して`change()`メソッドを実装します。

```php
<?php
use Phinx\Migration\AbstractMigration;

class Ticket extends AbstractMigration
{
    public function change()
    {
         $table = $this->table('ticket', ['id' => false, 'primary_key' => ['id']]);
         $table->addColumn('id', 'uuid')
            ->addColumn('title', 'string')
            ->addColumn('description', 'string')
            ->addColumn('status', 'string')
            ->addColumn('assignee', 'string')
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime')
            ->create();
    }
}
```

もう一度セットアップコマンドを実行してテーブルを作成します。

```
composer setup
```
```
> php bin/setup.php
Phinx by CakePHP - https://phinx.org. 0.10.6

...
All Done. Took 0.0248s
```

これでテーブルが作成されました。次回からこのプロジェクトのデータベース環境を整えるには`composer setup`を実行するだけで行えます。[^7]
マイグレーションクラスの記述について詳しくは[Phixのマニュアル：マイグレーションを書く](https://book.cakephp.org/3.0/ja/phinx/migrations.html)をご覧ください。

## SQL

チケットをデータベースに保存、読み込むために次の３つのSQLを`var/sql`に保存します。

`var/sql/ticket_insert.sql`

```sql
/* create ticket */
INSERT INTO ticket (id, title, description, status, assignee, created_at, updated_at)
VALUES (:id, :title, :description, :status, :assignee, :created_at, :updated_at)
```

`var/sql/ticket_list.sql`

```sql
SELECT id, title, description, status, assignee, created_at, updated_at
  FROM ticket
```

`var/sql/ticket_item_by_id.sql`

```sql
SELECT id, title, description, status, assignee, created_at, updated_at
  FROM ticket
 WHERE id = :id
```

上記のSQLの記述は[SQLスタイルガイド](https://www.sqlstyle.guide/ja/)に従ったものです。以下の事柄が推奨されています。

 * スペースとインデントを慎重に使用しコードを読みやすくする。
 * ISO-8601に準拠した日付時間フォーマット（YYYY-MM-DD HH:MM:SS.SSSSS）で格納する。
 * 移植性のためベンダー固有の関数の代わりに標準のSQL関数のみを使用する。
 * 必要に応じてSQLコードにコメントを挿入する。可能なら /* で始まり */ で終わるC言語スタイルのコメントを使用し、その他の場合、-- で始まり改行で終わる行コメントを使用する。
 * スペースを活用し、基底のキーワードがすべて同じ位置で終わるようにコードを整列させる。これは途中で「リバー」を形作り、コードの見通しを良くし、実装の詳細からキーワードを分離することを容易にする。


PHPStormで[Database Navigator](https://confluence.jetbrains.com/display/CONTEST/Database+Navigator)を使うと、SQLのコード補完や実行が行えます。[](https://www.youtube.com/watch?v=P3C0iO1yqhk)
PHPでSQLを実行する前に、データベースツールでSQLを単体で実行して正しく記述できているかを確かめると開発も容易で確実です。

[JetBrain DataGrip](https://www.jetbrains.com/datagrip/)、[Sequel Pro](https://www.sequelpro.com/)、[MySQL Workbench](https://www.mysql.com/jp/products/workbench/)などの単体のデータベースツールがあります。

## JsonSchema

`Ticket`（`チケットアイテム`）、`Tickets`（`チケットアイテムの集合`）の２つのリソースを作成するために、まずこれらのリソースの定義を[JsonSchema](http://json-schema.org/)で定義します。JsonSchemaについて[日本語での解説](https://qiita.com/kyoh86/items/e7de290e9a0e989fcc14)もご覧ください。

それぞれのスキーマファイルを`var/json_schema`フォルダに保存します。

`var/json_schema/ticket.json`

```json

{
  "$id": "ticket.json",
  "$schema": "http://json-schema.org/draft-07/schema#",
  "title": "Ticket",
  "type": "object",
  "properties": {
    "id": {
      "type": "string",
      "description": "The unique identifier for a ticket."
    },
    "title": {
      "type": "string",
      "description": "The title of the ticket",
      "minLength": 3,
      "maxLength": 255
    },
    "description": {
      "type": "string",
      "description": "The description of the ticket",
      "maxLength": 255
    },
    "assignee": {
      "type": "string",
      "description": "The assignee of the ticket",
      "maxLength": 255
    },
    "status": {
      "description": "The name of the status",
      "type": "string",
      "maxLength": 255
    },
    "created_at": {
      "description": "The date and time that the ticket was created",
      "type": "string",
      "format": "datetime"
    },
    "updated_at": {
      "description": "The date and time that the ticket was last modified",
      "type": "string",
      "format": "datetime"
    }
  },
  "required": ["title", "description", "status", "created_at", "updated_at"]
}
```
`var/json_schema/tickets.json`

```json
{
  "$id": "tickets.json",
  "$schema": "http://json-schema.org/draft-07/schema#",
  "title": "Collection of Tickets",
  "type": "array",
  "items": {
    "$ref": "ticket.json"
  }
}
```

作成したJSONを`validate-json`を使ってバリデートすることができます。[^13]

```
./vendor/bin/validate-json var/json_schema/ticket.json
./vendor/bin/validate-json var/json_schema/tickets.json
```

これでリソースを定義をすることが出来ました。このスキーマは実際にバリデーションで使うことが出来ます。また独立したJSONファイルはフロントエンドのバリデーションでも使うことができるでしょう。

# テスト

次に今から作ろうとする`/ticket`リソースのテストを`tests/Resource/App/TicketsTest.php`に用意します。

```php
<?php
namespace MyVendor\Ticket\Resource\App;

use BEAR\Package\AppInjector;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use Koriym\HttpConstants\ResponseHeader;
use PHPUnit\Framework\TestCase;

class TicketsTest extends TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    protected function setUp() : void
    {
        $this->resource = (new AppInjector('MyVendor\Ticket', 'test-app'))->getInstance(ResourceInterface::class);
    }

    public function testOnPost()
    {
        $ro = $this->resource->post('app://self/tickets', [
            'title' => 'title1',
            'status' => 'status1',
            'description' => 'description1',
            'assignee' => 'assignee1'
        ]);
        $this->assertSame(201, $ro->code);
        $this->assertContains('/ticket?id=', $ro->headers['Location']);

        return $ro;
    }

    /**
     * @depends testOnPost
     */
    public function testOnGet(ResourceObject $ro)
    {
        $location = $ro->headers[ResponseHeader::LOCATION];
        $ro = $this->resource->get('app://self' . $location);
        $this->assertSame('title1', $ro->body['title']);
        $this->assertSame('description1', $ro->body['description']);
        $this->assertSame('assignee1', $ro->body['assignee']);
    }
}
```

`$this->resource`は`MyVendor\Ticket`アプリケーションを`test-app`コンテキストで動作させた時のリソースクライアントです。
`AppModule`、`TestModule`の順のモジュールで上書きされるのでデータベースはテスト用の`ticket_test`データベースが使われます。

`testOnPost`でリソースをPOSTリクエストで作成して、`testOnGet`ではそのレスポンスのLocationヘッダーに表されているリソースのURIをGETリクエストして、作成したリソースが正しいものかをテストしています。

まだ実装してないのでエラーが出ますが、テスト実行を試してみましょう。

```
composer test
```

当面の目標はこのテストがパスするようになる事です。テストを先に用意する事で、作ったリソースの実行やデバックトレースが簡単になり、着手した作業のゴールが明確になります。

# リソース


リソースのロジックはSQLとして、そのバリデーションはJSONファイルで表すことが出来ています。リソースクラスではそれらのファイルを利用します。

## tikcetリソース

`ticket`リソースを`src/Resource/App/Ticket.php`に作成します。

```php
<?php
namespace MyVendor\Ticket\Resource\App;

use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;
use Ray\Query\Annotation\Query;

/**
 * @Cacheable
 */
class Ticket extends ResourceObject
{
    /**
     * @JsonSchema(key="ticket", schema="ticket.json")
     * @Query("ticket_item_by_id", type="row")
     */
    public function onGet(string $id) : ResourceObject
    {
        unset($id);

        return $this;
    }
}
```

## ticket - GETリクエスト

`GET`のための`onGet`メソッドを見てみましょう。メソッドシグネチャーを見れば、リクエストに必要な入力は`$_GET['id']`のみで、それは省略ができないという事が分かります。

`@JsonSchema`アノテーションはこのクラスの`body`プロパティの`ticket`キー配列が`ticket.json`で定義されたスキーマであるということを宣言しつつリアルタイムのバリデーションをAOPで毎回行う事で保証しています。

`@Query("ticket_item_by_id", type="row")`と指定されているので、このメソッドはSQL実行と置き換わります。`var/sql/ticket_item_by_id.sql`ファイルのSQLが実行され、その結果が単一行（type="row"）で返ります。このようにロジックが単純にSQLで置換えられる場合は`@Query`を使ってPHPの記述を省略することができます。


## tikcetsリソース

次は`tikcet`リソースの集合の`tikcets`リソースを`src/resource/App/Tickets.php`に作成します。

```php
<?php
namespace MyVendor\Ticket\Resource\App;

use BEAR\Package\Annotation\ReturnCreatedResource;
use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\RepositoryModule\Annotation\Purge;
use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;
use Koriym\HttpConstants\ResponseHeader;
use Koriym\HttpConstants\StatusCode;
use Ray\AuraSqlModule\Annotation\Transactional;
use Ray\Di\Di\Named;
use Ray\IdentityValueModule\NowInterface;
use Ray\IdentityValueModule\UuidInterface;
use Ray\Query\Annotation\Query;

/**
 * @Cacheable
 */
class Tickets extends ResourceObject
{
    /**
     * @var callable
     */
    private $createTicket;

    /**
     * @var NowInterface
     */
    private $now;

    /**
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @Named("createTicket=ticket_insert")
     */
    public function __construct(callable $createTicket, NowInterface $now, UuidInterface $uuid)
    {
        $this->createTicket = $createTicket;
        $this->now = $now;
        $this->uuid = $uuid;
    }

    /**
     * @JsonSchema(schema="tickets.json")
     * @Query("ticket_list")
     */
    public function onGet() : ResourceObject
    {
        return $this;
    }

    /**
     * @ReturnCreatedResource
     * @Transactional
     * @Purge(uri="app://self/tickets")
     */
    public function onPost(
        string $title,
        string $description = '',
        string $assignee = ''
    ) : ResourceObject {
        $id = (string) $this->uuid;
        $time = (string) $this->now;
        ($this->createTicket)([
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'assignee' => $assignee,
            'status' => '',
            'created_at' => $time,
            'updated_at' => $time,
        ]);
        $this->code = StatusCode::CREATED;
        $this->headers[ResponseHeader::LOCATION] = "/ticket?id={$id}";

        return $this;
    }
}
```

## tickets - GETリクエスト

`var/json_schema/tickets.json`JSONスキーマをご覧になってください。`ticket.json`スキーマの集合(array)と定義されています。
このようにJSONスキーマはスキーマの構造を表すことができます。メソッドは`/ticket`リソース同様に`ticket_list.sql`のSQL実行を結果として返します。

## tickets - POSTリクエスト

コンストラクタでインジェクトされた`$this->createTicket`は`ticket_insert.sql`の実行オブジェクトです。受け取った連想配列をバインドしてSQL実行します。
リソースを作成する時は必ず`Location`ヘッダーでリソースのURLを保存するようにします。作成した内容をボディに含みたい時は`@ReturnCreatedResource`とアノテートします。

## indexリソース

`index`リソースは作成したリソース(API)へのリンク集です。`src/Resource/App/Index.php`に作成します。

```php
<?php
namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    public $body = [
        'overview' => 'This is the Tutorial2 REST API',
        'issue' => 'https://github.com/bearsunday/tutorial2/issues',
        '_links' => [
            'self' => [
                'href' => '/',
            ],
            'curies' => [
                'href' => 'rels/{rel}.html',
                'name' => 'tk',
                'templated' => true
            ],
            'tk:ticket' => [
                'href' => '/tickets/{id}',
                'title' => 'Ticket',
                'templated' => true
            ],
            'tk:tickets' => [
                'href' => '/tickets',
                'title' => 'The collection of ticket'
            ]
        ]
    ];

    public function onGet() : ResourceObject
    {
        return $this;
    }
}
```

[CURIE](https://en.wikipedia.org/wiki/CURIE)(compact URI)というフォーマットを使って、このプロジェクトにはどのようなリソースがあるか、またそれらのドキュメンテーションはどこにあるかという情報をAPI自身がサービスする事ができます。

Webサイトを利用するのに事前に全てのURIを知る必要がないように、APIサービスも同様に一覧のリンクを持つことでAPIの"発見容易性(Discoverability)"を高めます。

早速リクエストしてみましょう。

```
php bin/app.php get /
```
```
200 OK
content-type: application/hal+json

{
    "overview": "This is the Tutorial2 REST API",
    "issue": "https://github.com/bearsunday/tutorial2/issues",
    "_links": {
        "self": {
            "href": "/"
        },
        "curies": [
            {
                "href": "rels/{rel}.html",
                "name": "tk",
                "templated": true
            }
        ],
        "tk:ticket": {
            "href": "/tickets/{id}",
            "title": "Ticket",
            "templated": true
        },
        "tk:tickets": {
            "href": "/tickets",
            "title": "The collection of ticket"
        }
    }
}
```

[`curies`](http://stateless.co/hal_specification.html)はヒューマンリーダブルなドキュメントのためのリンクです。
このAPIは`/ticket`と`/tickets`という２つのリソースがある事が分かります。
`curies`によってそれらのドキュメントはそれぞれ`rels/ticket.html`,`rels/tickets.html`にあると示されてます。

まだ作成していないので見る事は今はできませんが、`OPTIONS`コマンドで調べることができます。

```
php bin/app.php options /tickets
```
```
200 OK
Content-Type: application/json
Allow: GET, POST

{
    "GET": {
        "schema": {
            "$id": "tickets.json",
            "$schema": "http://json-schema.org/draft-07/schema#",
            "title": "Collection of Tickets",
            "type": "array",
            "items": {
                "$ref": "ticket.json"
            }
        }
    },
    "POST": {
        "request": {
            "parameters": {
                "title": {
                    "type": "string"
                },
                "description": {
                    "type": "string",
                    "default": ""
                },
                "assignee": {
                    "type": "string",
                    "default": ""
                }
            },
            "required": [
                "title"
            ]
        }
    }
}
```

マシンリーダブルなAPIドキュメントとして表示されます。

では実際に`/tickets`にアクセスしてみましょう。


POSTリクエストでチケット作成します。

```
php bin/app.php post '/tickets?title=run'
```
```
201 Created
Location: /tickets/b0f9c395-3a3d-48ee-921b-ce45a06eee11
content-type: application/hal+json

{
    "id": "b0f9c395-3a3d-48ee-921b-ce45a06eee11",
    "title": "run",
    "description": "",
    "status": "",
    "assignee": "",
    "created": "2018-09-11 13:15:33",
    "updated": "2018-09-11 13:15:33",
    "_links": {
        "self": {
            "href": "/tickets/b0f9c395-3a3d-48ee-921b-ce45a06eee11"
        }
    }
}
```

レスポンスにあるLocationヘッダーのURIをGETリクエストします。

```
php bin/app.php get '/tickets/b0f9c395-3a3d-48ee-921b-ce45a06eee11'
```
```
200 OK
Link: <http://www.example.com/ticket.json>; rel="describedby"
content-type: application/hal+json
ETag: 3794765489
Last-Modified: Tue, 11 Sep 2018 11:16:05 GMT

{
    "id": "b0f9c395-3a3d-48ee-921b-ce45a06eee11",
    "title": "run",
    "description": "",
    "status": "",
    "assignee": "",
    "created": "2018-09-11 13:15:33",
    "updated": "2018-09-11 13:15:33",
    "_links": {
        "self": {
            "href": "/tickets/b0f9c395-3a3d-48ee-921b-ce45a06eee11"
        }
    }
}
```

レスポンスは200 OKで帰ってきましたか？
このレスポンスの定義は`ticket.json`で定義されたものであることが`Link`ヘッダーの`describedby`で分かります。[^9]
`@Cacheable`と宣言されたリソースは`ETag`と`Last-Modified`ヘッダーが付加されより効率の良いHTTPレベルのキャッシュが有効になります。
`@Purge`はキャッシュの破壊です。[^10]

最初に作ったテストも今はうまくパスするはずです。試してみましょう。

```
composer test
```

コーディング規約通りに書けているか、または`phpdoc`がコードと同じように正しく書けているかはツールで調べることができます。
エラーが出れば`cs-fix`で直すことができます。

```
composer cs-fix
```

ユニットテストとコーディング規約、静的解析ツールを同時に行うこともできます。コミットする前に実行しましょう。[^3]

```
composer tests
```

`compile`コマンドで最適化された`autoload.php`を生成してDI/AOPスクリプトを生成することができます。ディプロイ前は実行しましょう。[^4][^5]
全てをDIするBEAR.Sundayアプリケーションはアプリケーション実行前に依存の問題を見つけることができます。ログでDIの束縛の情報を見る事もできるので開発時でも役に立ちます。

```
composer compile
```

テストはパスしてコンパイルもうまくできましたか？ REST APIの完成です！

## APIドキュメント

APIドキュメントを出力するために`composer.json`の`scrpits`に以下の`doc`コマンドを追加します。

```
"doc": "bear.apidoc 'MyVendor\\Ticket' ./ docs",
```

ドキュメントのためのディレクトリを作成します。

```
mkdir docs
```

`composer doc`コマンドでAPIサイトのHTMLとJSONが出力されます。

```
composer doc
```
```
API Doc is created at /path/to/docs
```

このサイトをGitHub Pages[^11]などで公開して、APIドキュメントにします。
公開APIサイトのドメインが決まれば`JsonSchemaLinkHeaderModule()`モジュールで公開ドメインを指定します。

```php?start_inline
 $this->install(new JsonSchemaLinkHeaderModule('https://{your-domain}/schema'));
```

このようなAPIドキュメントサイトができるはずです。

[https://bearsunday.github.io/tutorial2/](https://bearsunday.github.io/tutorial2/)

ドキュメントサイトをコードと同じGithub管理するとでコードとどの時点で作成されたドキュメントなのか記録が残ります。

## 終わりに



 * phinxマイグレーションツールを使ってアプリケーションのバージョンに従ったデータベースの環境構築ができるようになりました。
 
 * `composer setup`コマンドで環境構築ができれば、データベースコマンドを操作する必要がなくディプロイやCIでも便利です。

 * SQLファイルを`var/sql`フォルダに置くことでGUIやCLIのSQLツールで単体実行することができ、開発や運用にも便利でテストも容易になります。スタティックなSQLはPhpStormで補完も効くし、GUIでモデリングできるツールもあります。

 * リソースの引数と出力はメソッドやスキーマで宣言されていて明瞭です。AOPでバリデーションが行わることでドキュメントの正当性が保証され、ドキュメントメンテナンスのの労力を最小化できます。

チュートリアルはうまく言ったでしょうか？ もしうまく行ったらなチュートリアル[bearsunday/tutorial2](https://github.com/bearsunday/tutorial2)にスターをして記念に残しましょう。
うまくいかない時は[gitter](https://gitter.im/bearsunday/BEAR.Sunday)で相談すると解決できるかもしれません。提案や間違いがあれば[PR](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/ja/tutorial2.md)をお願いします！

---

[^1]:[チュートリアル](/manuals/1.0/ja/tutorial.html)を終えた方を対象としています。被る箇所もありますがおさらいのつもりでトライしてみましょう。レポジトリは[bearsaunday/Tutorial2](https://github.com/bearsunday/Tutorial2)にあります。うまくいかないときは見比べてみましょう。
[^2]:通常は**vendor**名は個人またはチーム（組織）の名前を入力します。githubのアカウント名やチーム名が適当でしょう。**project**にはアプリケーション名を入力します。
[^3]:コミットフックを設定するのも良い方法です。
[^4]:キャッシュを"温める"ために２度行うと確実です。
[^5]:コンテキストの変更は`composer.json`の`compile`スクリプトコマンドを編集します。
[^6]:BEAR.Sundayフレームワークが依存する環境変数は１つもありません。
[^7]:mysqlコマンドの操作などをREADMEで説明する必要もないので便利です。
[^9]:http://json-schema.org/latest/json-schema-core.html#rfc.section.10.1
[^10]:`/ticket`でPOSTされると`/tickets`リソースのキャッシュを破壊しています。`@Refresh`とすると破壊のタイミングでキャッシュを再生成します。
[^11]: [Publishing your GitHub Pages site from a /docs folder on your master branch](https://help.github.com/articles/configuring-a-publishing-source-for-github-pages/#publishing-your-github-pages-site-from-a-docs-folder-on-your-master-branch)
[^13]: 2018年9月現在php7.3だと実行できますが`PHP Deprecated`が表示されます。
