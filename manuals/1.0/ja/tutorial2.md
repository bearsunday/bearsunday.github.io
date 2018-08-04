---
layout: docs-ja
title: チュートリアル2
category: Manual
permalink: /manuals/1.0/ja/tutorial2.html
---
# チュートリアル2

このチュートリアルは以下のライブラリやツールを使ってREST APIを作成します。

 * CakePHPが開発してる[Phinx](https://book.cakephp.org/3.0/ja/phinx.html) DBマイグレーションツール
 * APIレスポンスのスキーマを定義する [Json Schema](https://qiita.com/kyoh86/items/e7de290e9a0e989fcc14)
 * SQL文をSQL実行オブジェクトに変換する [ray/query-module](https://github.com/ray-di/Ray.QueryModule)

[チュートリアル](/manuals/1.0/ja/tutorial.html)と被る箇所もありますがおさらいのつもりでトライして見ましょう。
レポジトリは[MyVendor.Ticket](https://github.com/bearsunday/MyVendor.Ticket)にあります。うまくいかないときは見比べて見ましょう。

## プロジェクト作成

まずプロジェクトを作成します。

```
composer create-project bear/skeleton MyVendor.Ticket
```
**vendor**名を`MyVendor`に**project**名を`Ticket`として入力します。

## composerインストール

次に依存するパッケージを一度にインストールします。

```
composer require  \
koriym/now  \
bear/api-doc  \
ray/query-module  \
ramsey/uuid  \
robmorgan/phinx
```

## モジュールインストール

`src/Module/AppModule.php`を編集してcomposerでインストールしたパッケージをモジュールインストールします。

```php
<?php
namespace MyVendor\Ticket\Module;

use BEAR\Package\PackageModule;
use BEAR\Resource\Module\JsonSchemaModule;
use josegonzalez\Dotenv\Loader;
use Koriym\Now\NowModule;
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\Di\AbstractModule;
use Ray\Query\SqlQueryModule;

class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $appDir = dirname(__DIR__, 2);
        (new Loader($appDir . '/.env'))->parse()->toEnv(true);
        $this->install(new AuraSqlModule($_ENV['DB_DSN'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_SLAVE']));
        $this->install(new SqlQueryModule($appDir . '/var/sql'));
        $this->install(new NowModule);
        $this->install(new JsonSchemaModule($appDir . '/var/json_schema', $appDir . '/var/json_validate'));
        $this->install(new PackageModule);
    }
}
```

モジュールが必要とするフォルダを作成します。

```bash
mkdir var/sql
mkdir var/json_schema
mkdir var/json_validate
```

## データベース

プロジェクトルートフォルダの`.env`ファイルに接続情報を記述します。

```
DB_DSN=mysql:host=localhost;dbname=ticket
DB_USER=root
DB_PASS=''
DB_SLAVE=''
DB_NAME=ticket
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
## setupスクリプト

データベース作成やマイグレーションを簡単に実行できるように`bin/setup.php`を編集します。

```php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';
chdir(dirname(__DIR__));

db: {
    (new josegonzalez\Dotenv\Loader(dirname(__DIR__) . '/.env'))->parse()->toEnv();
    $db = new PDO('mysql:', $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $db->exec('CREATE DATABASE IF NOT EXISTS ' . $_ENV['DB_NAME']);
    $db->exec('CREATE DATABASE IF NOT EXISTS ' . $_ENV['DB_NAME'] . '_test');
    passthru('./vendor/bin/phinx migrate -c var/phinx/phinx.php -e development');
}
```

実行してデータベースを作成します。

```
composer setup
```
```
> php bin/setup.php
Phinx by CakePHP - https://phinx.org. 0.10.5

using config file ./var/phinx/phinx.php
using config parser php
using migration paths 
using environment development
using database ticket

All Done. Took 0.0462s
```

次に`ticket`テーブルを作成するためにマイグレーションクラスを作成します。

```
./vendor/bin/phinx create Ticket -c var/phinx/phinx.php
```
```
Phinx by CakePHP - https://phinx.org. 0.10.5

using config file ./var/phinx/phinx.php
using config parser php
using migration paths 
using migration base class Phinx\Migration\AbstractMigration
using default template
created var/phinx/migrations/20180719040628_ticket.php
```

`var/phinx/migrations/{current_date}_ticket.php`を編集して`change()`メソッドと`down()`メソッドを実装します。

```php
<?php
use Phinx\Migration\AbstractMigration;

class Ticket extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('ticket', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'uuid')
            ->addColumn('title', 'string')
            ->addColumn('description', 'string')
            ->addColumn('status', 'string')
            ->addColumn('assignee', 'string')
            ->addColumn('created', 'datetime')
            ->addColumn('updated', 'datetime')
            ->create();
    }

    public function down()
    {
        $this->table('ticket')->drop()->save();
    }
}
```

もう一度セットアップコマンドを実行してテーブルを作成します。

```
composer setup
```
```
> php bin/setup.php
Phinx by CakePHP - https://phinx.org. 0.10.5

using config file ./var/phinx/phinx.php
using config parser php
using migration paths 
 - /Users/akihito/git/MyVendor.Ticket/var/phinx/migrations
using environment development
using database ticket

 == 20180719040628 Ticket: migrating
 == 20180719040628 Ticket: migrated 0.0756s

All Done. Took 0.0900s
```

これでテーブルが作成されました。次回からこのプロジェクトのデータベース環境を整えるには`composer setup`を実行するだけで行えます。

## SQL

チケットをデータベースに保存、読み込むために次の３つのSQLを`var/sql`に保存します。

`var/sql/ticket_insert.sql`

```sql
INSERT INTO ticket (id, title, description, status, assignee, created, updated) VALUES (:id, :title, :description, :status, :assignee, :created, :updated)
```

`var/sql/ticket_item_by_id.sql`

```sql
SELECT * FROM ticket WHERE id = :id
```

`var/sql/ticket_list.sql`

```sql
SELECT * FROM ticket
```

*Note:* PHPStormを使用しているならPreference > Plugin で [Database Navigator](https://plugins.jetbrains.com/plugin/1800-database-navigator)をインストールするとSQLファイルを右クリックすると単体で実行することが出来ます。

PHPでSQLを実行する前に、このように事前に単体で実行してSQLが正しく記述できているかを確かめると確実で開発も容易です。[Sequel Pro](https://www.sequelpro.com/)や[MySQL Workbench](https://www.mysql.com/jp/products/workbench/)などのデータベースブラウザを使うのも良いでしょう。

## JsonSchema

`Ticket`（`チケットアイテム`）、`Tickets`（`チケットアイテムの集合`）の２つのリソースを作成するために、まずこれらのリソースの定義を[JsonSchema](http://json-schema.org/)で定義します。JsonSchemaについて[日本語での解説](https://qiita.com/kyoh86/items/e7de290e9a0e989fcc14)もご覧ください。

それぞれのスキーマファイルを`var/json_schema`フォルダに保存します。

`var/json_schema/ticket.json`

```json
{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "type": "object",
  "id": "ticket.json",
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
    "created": {
      "description": "The date and time that the ticket was created",
      "type": "string",
      "format": "datetime"
    },
    "updated": {
      "description": "The date and time that the ticket was last modified",
      "type": "string",
      "format": "datetime"
    }
  },
  "required": ["title", "description", "status", "created", "updated"],
  "additionalProperties": false
}
```
`var/json_schema/tickets.json`

```json
{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "id": "tickets.json",
  "title": "Collection of Tickets",
  "type": "array",
  "items": {
    "$ref": "ticket.json"
  }
}
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

    protected function setUp()
    {
        parent::setUp();
        $this->resource = (new AppInjector('MyVendor\Ticket', 'app'))->getInstance(ResourceInterface::class);
    }

    public function testOnPost()
    {
        $params = [
            'title' => 'title1',
            'status' => 'status1',
            'description' => 'description1',
            'assignee' => 'assignee1'
        ];
        $ro = $this->resource->post->uri('app://self/ticket')($params);
        /* @var ResourceObject $ro */
        $this->assertSame(201, $ro->code);
        $this->assertContains('/ticket', $ro->headers['Location']);

        return $ro;
    }

    /**
     * @depends testOnPost
     */
    public function testOnGet(ResourceObject $ro)
    {
        $location = $ro->headers[ResponseHeader::LOCATION];
        $ro = $this->resource->uri('app://self' . $location)();
        /* @var ResourceObject $ro */
        $this->assertSame('title1', $ro->body['title']);
        $this->assertSame('description1', $ro->body['description']);
        $this->assertSame('assignee1', $ro->body['assignee']);
    }
}
```

`$this->resource`は`MyVendor\Ticket`アプリケーションを`app`コンテキストで動作させた時のリソースクライアントです。`testOnPost`でリソースをPOSTリクエストで作成して、`testOnGet`ではそのレスポンスのLocationヘッダーに表されているリソースのURIをGETリクエストして、作成したリソースが正しいものかをテストしています。

まだ実装してないのでエラーが出ますが、テスト実行を試してみましょう。

```
composer test
```

当面の目標はこのテストがパスするようになる事です。テストを先に用意する事で、作ったリソースの実行やデバックトレースが簡単になり、着手した作業のゴールが明確になります。

# リソース


リソースのロジックはSQLとして、そのバリデーションはJSONファイルで表すことが出来ています。リソースクラスではそれらのファイルを利用します。

## tikcetリソース

まずは基本となる`ticket`リソースを`src/Resource/App/Ticket.php`に作成します。

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
use Koriym\Now\NowInterface;
use Ramsey\Uuid\Uuid;
use Ray\AuraSqlModule\Annotation\Transactional;
use Ray\Di\Di\Named;
use Ray\Query\Annotation\AliasQuery;

/**
 * @Cacheable
 */
class Ticket extends ResourceObject
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
     * @Named("createTicket=ticket_insert")
     */
    public function __construct(callable $createTicket, NowInterface $now)
    {
        $this->createTicket = $createTicket;
        $this->now = $now;
    }

    /**
     * @JsonSchema(key="ticket", schema="ticket.json")
     * @AliasQuery("ticket_item_by_id", type="row")
     */
    public function onGet(string $id) : ResourceObject
    {
        unset($id);
    }

    /**
     * @ReturnCreatedResource
     * @Transactional
     * @Purge(uri="app://self/tickets")
     * @Purge(uri="page://self/tickets")
     */
    public function onPost(
        string $title,
        string $description = '',
        string $assignee = ''
    ) : ResourceObject {
        $id = Uuid::uuid4()->toString();
        ($this->createTicket)([
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'assignee' => $assignee,
            'status' => '',
            'created' => (string) $this->now,
            'updated' => (string) $this->now,
        ]);
        $this->code = StatusCode::CREATED;
        $this->headers[ResponseHeader::LOCATION] = "/ticket?id={$id}";

        return $this;
    }
}
```

## ticket - GETリクエスト

`GET`のための`onGet`メソッドを見てみましょう。メソッドシグネチャーを見れば、リクエストに必要な入力は`$_GET['id']`のみで、それは省略ができないという事が分かります。

`@JsonSchema`アノテーションはこのクラスの`body`プロパティの`ticket`キー配列が`ticket.json`で定義されたスキーマであるということを宣言しつつリアルタイムのバリデーションをAOPで毎回行う事で保証しています。

`@AliasQuery("ticket_item_by_id", type="row")`と指定されているので、このメソッドはSQL実行と置き換わります。`var/sql/ticket_item_by_id.sql`ファイルのSQLが実行され、その結果が単一行（type="row"）で返ります。このようにロジックが単純にSQLで置換えられる場合は`@AliasQuery`を使ってPHPの記述を省略することができます。

## ticket - POSTリクエスト

コンストラクタでインジェクトされた`$this->createTicket`は`ticket_insert.sql`の実行オブジェクトです。受け取った連想配列をバインドしてSQL実行します。

注意：リソースを作成する時は必ずロケーションヘッダーでリソースのURLを保存するようにします。作成した内容をボディに含みたい時は`@ReturnCreatedResource`とアノテートします。

## tikcetsリソース

次は`tikcet`リソースの集合の`tikcets`リソースを`src/resource/App/Tickets.php`に作成します。JSONスキーマでは単純に`var/json_schema/tickets.json`は`ticket.json`スキーマの集合(array)と定義されています。
このようにJSONスキーマはスキーマの構造を表すことができます。

```php
<?php
namespace MyVendor\Ticket\Resource\App;

use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;
use Ray\Query\Annotation\AliasQuery;

/**
 * @Cacheable
 */
class Tickets extends ResourceObject
{
    /**
     * @JsonSchema(schema="tickets.json")
     * @AliasQuery("ticket_list")
     */
    public function onGet() : ResourceObject
    {
        return $this;
    }
}
```

GETリクエストは`Ticket.php`の時とほぼ同様です。

## indexリソース

`index`リソースは作成したリソース(API)へのリンク集です。`src/resource/App/Index.php`に作成します。

```php
<?php
namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    public $body = [
        'message' => 'Welcome to the MyVendor.Ticket API !',
        '_links' => [
            'self' => [
                'href' => '/',
            ],
            'curies' => [
                'href' => 'http://localhost:8081/rels/{?rel}',
                'name' => 'kt',
                'templated' => true
            ],
            'kt:ticket' => [
                'href' => '/ticket',
                'title' => 'tickets item',
                'templated' => true
            ],
            'kt:tickets' => [
                'href' => '/tickets',
                'title' => 'ticket list'
            ]
        ]
    ];

    public function onGet()
    {
        return $this;
    }
}
```

[CURIE](https://en.wikipedia.org/wiki/CURIE)(compact URI)というフォーマットを使って、このプロジェクトにはどのようなリソースがあるか、またそれらのドキュメンテーションはどこにあるかという情報をAPI自身がサービスする事ができます。

Webサイトを利用するのに事前に全てのURIを知る必要がないように、APIサービスも同様に一欄のリンクを持つことでAPIの"発見容易性(Discoverability)"を高めます。

早速リクエストして見ましょう。

```
php bootstrap/api.php get /
```
```
200 OK
content-type: application/hal+json

{
    "message": "Welcome to the Koriym.TicketSan API !",
    "_links": {
        "self": {
            "href": "/"
        },
        "curies": [
            {
                "href": "http://localhost:8081/rels/{?rel}",
                "name": "kt",
                "templated": true
            }
        ],
        "kt:ticket": {
            "href": "/ticket",
            "title": "tickets item",
            "templated": true
        },
        "kt:tickets": {
            "href": "/tickets",
            "title": "ticket list"
        }
    }
}
```

`curies`はヒューマンリーダブルなドキュメントのためのリンクです。詳しくは[APIドキュメンとサービス](hypermedia-api.html)をご覧ください。

他のセクションを見るとこのAPIは`/ticket`と`/tickets`という２つのリソースがある事が分かります。
それぞれの詳細を調べるには`OPTIONS`コマンドでリクエストします。

```
php bootstrap/api.php options /ticket
```
```
200 OK
Content-Type: application/json
Allow: GET, POST

{
    "GET": {
        "request": {
            "parameters": {
                "id": {
                    "type": "string"
                }
            },
            "required": [
                "id"
            ]
        },
        "schema": {
            "$schema": "http://json-schema.org/draft-04/schema#",
            "type": "object",
            "id": "ticket.json",
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
                "created": {
                    "description": "The date and time that the ticket was created",
                    "type": "string",
                    "format": "datetime"
                },
                "updated": {
                    "description": "The date and time that the ticket was last modified",
                    "type": "string",
                    "format": "datetime"
                }
            },
            "required": [
                "title",
                "description",
                "status",
                "created",
                "updated"
            ],
            "additionalProperties": false
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

`request`に入力が、`schema `にスキーマがマシンリーダブルなAPIドキュメントとして表示されます。

では実際に`/ticket`にアクセスして見ましょう。


POSTリクエストでチケット作成します。

```
php bootstrap/api.php post '/ticket?title=run'
```
```
201 Created
Location: /ticket?id=ed3f9f53-d5ef-4d7c-843e-e2d81361f62a
content-type: application/hal+json
```

レスポンスにあるLocationヘッダーのURIをGETリクエストします。

```
php bootstrap/api.php get '/ticket?id=ed3f9f53-d5ef-4d7c-843e-e2d81361f62a'
```
```
200 OK
content-type: application/hal+json
ETag: 4274077199
Last-Modified: Sat, 21 Jul 2018 03:02:04 GMT

{
    "id": "ed3f9f53-d5ef-4d7c-843e-e2d81361f62a",
    "title": "run",
    "description": "",
    "status": "",
    "assignee": "",
    "created": "2018-07-21 04:58:46",
    "updated": "2018-07-21 04:58:46",
    "_links": {
        "self": {
            "href": "/ticket?id=ed3f9f53-d5ef-4d7c-843e-e2d81361f62a"
        }
    }
}
```

それぞれのリソースには`@Cacheable`がアノテートされているのでGETレスポンスは引数をキーにしてキャッシュされます。`@Purge`はキャッシュの破壊です。`/ticket`でPOSTされると`/tickets`リソースのキャッシュを破壊しています。`@Refresh`とすると破壊のタイミングでキャッシュを再生成します。
最初に作ったテストも今はうまくパスするはずです。試してみましょう。

```
composer test
```

コーディング規約通りに書けているか、または`phpdoc`がコードと同じように正しく書けているかは静的解析ツールで調べることができます。コミットする前に必ず実行しましょう。コミットフックを設定するのも良い方法です。
コーディング規約のエラーは`composer cs-fix`で直すことができます。

```
composer tests
```

テストはパスしましたか？　REST APIの完成です！
