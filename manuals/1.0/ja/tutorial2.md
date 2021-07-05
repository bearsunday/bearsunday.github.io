---
layout: docs-ja
title: チュートリアル2
category: Manual
permalink: /manuals/1.0/ja/tutorial2.html
---
# チュートリアル2

このチュートリアルでは以下のツールを用いて標準に基づいた高品質なREST(Hypermedia)アプリケーション開発を学びます。

* JSONのスキーマを定義しバリデーションやドキュメンテーションに利用する [Json Schema](https://json-schema.org/)
* ハイパーメディアタイプ [HAL (Hypertext Application Language)](https://stateless.group/hal_specification.html)  
* CakePHPが開発してるDBマイグレーションツール [Phinx](https://book.cakephp.org/3.0/ja/phinx.html) 
* PHPのインターフェイスとSQL文実行を束縛する [Ray.MediaQuery](https://github.com/ray-di/Ray.MediaQuery)

[tutorial2](https://github.com/bearsunday/tutorial2/commits/v2)のコミットを参考にして進めましょう。

## プロジェクト作成

プロジェクトスケルトンを作成します。

```
composer create-project bear/skeleton MyVendor.Ticket
```

**vendor**名を`MyVendor`に**project**名を`Ticket`として入力します。

## マイグレーション

Phinxをインストールします。

```
composer require --dev robmorgan/phinx
```

プロジェクトルートフォルダの`.env.dist`ファイルにDB接続情報を記述します。

```
TKT_DB_HOST=127.0.0.1:3306
TKT_DB_NAME=ticket
TKT_DB_USER=root
TKT_DB_PASS=''
TKT_DB_SLAVE=''
TKT_DB_DSN=mysql:host=${TKT_DB_HOST}
```

`.env.dist`ファイルはこのようにして、実際の接続情報は`.env`に記述しましょう。[^1]

次にphinxが利用するフォルダを作成します。

```bash
mkdir -p var/phinx/migrations
mkdir var/phinx/seeds
```

`.env`の接続情報をphinxで利用するために`var/phinx/phinx.php`を設置します。

```php
<?php
use BEAR\Dotenv\Dotenv;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

(new Dotenv())->load(dirname(__DIR__, 2));

$development = new PDO(getenv('TKT_DB_DSN'), getenv('TKT_DB_USER'), getenv('TKT_DB_PASS'));
$test = new PDO(getenv('TKT_DB_DSN') . '_test', getenv('TKT_DB_USER'), getenv('TKT_DB_PASS'));
return [
    'paths' => [
        'migrations' => __DIR__ . '/migrations',
    ],
    'environments' => [
        'development' => [
            'name' => $development->query("SELECT DATABASE()")->fetchColumn(),
            'connection' => $development
        ],
        'test' => [
            'name' => $test->query("SELECT DATABASE()")->fetchColumn(),
            'connection' => $test
        ]
    ]
];
```

### setupスクリプト

データベース作成やマイグレーションを簡単に実行できるように`bin/setup.php`を編集します。

```php
<?php
use BEAR\Dotenv\Dotenv;

require_once dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv())->load(dirname(__DIR__));

chdir(dirname(__DIR__));
passthru('rm -rf var/tmp/*');
passthru('chmod 775 var/tmp');
passthru('chmod 775 var/log');
// db
$pdo = new \PDO('mysql:host=' . getenv('TKT_DB_HOST'), getenv('TKT_DB_USER'), getenv('TKT_DB_PASS'));
$pdo->exec('CREATE DATABASE IF NOT EXISTS ' . getenv('TKT_DB_NAME'));
$pdo->exec('DROP DATABASE IF EXISTS ' . getenv('TKT_DB_NAME') . '_test');
$pdo->exec('CREATE DATABASE ' . getenv('TKT_DB_NAME') . '_test');
passthru('./vendor/bin/phinx migrate -c var/phinx/phinx.php -e development');
passthru('./vendor/bin/phinx migrate -c var/phinx/phinx.php -e test');
```

次に`ticket`テーブルを作成するためにマイグレーションクラスを作成します。

```
./vendor/bin/phinx create Ticket -c var/phinx/phinx.php
```
```
Phinx by CakePHP - https://phinx.org.

...
created var/phinx/migrations/20210520124501_ticket.php
```

`var/phinx/migrations/{current_date}_ticket.php`を編集して`change()`メソッドを実装します。

```php
<?php
use Phinx\Migration\AbstractMigration;

final class Ticket extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('ticket', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'uuid')
            ->addColumn('title', 'string')
            ->addColumn('dateCreated', 'datetime')
            ->create();
    }
}
```

`.env.dist`ファイルを以下のように変更します。

```diff
 TKT_DB_USER=root
 TKT_DB_PASS=
 TKT_DB_SLAVE=
-TKT_DB_DSN=mysql:host=${TKT_DB_HOST}
+TKT_DB_DSN=mysql:host=${TKT_DB_HOST};dbname=${TKT_DB_NAME}
```

準備が完了したので、セットアップコマンドを実行してテーブルを作成します。

```
composer setup
```
```
> php bin/setup.php
...
All Done. Took 0.0248s
```

テーブルが作成されました。次回からこのプロジェクトのデータベース環境を整えるには`composer setup`を実行するだけで行えます。

マイグレーションクラスの記述について詳しくは[Phinxのマニュアル：マイグレーションを書く](https://book.cakephp.org/3.0/ja/phinx/migrations.html)をご覧ください。

## モジュール

モジュールをcomposerインストールします。

```
composer require ray/identity-value-module ray/media-query
```

AppModuleでパッケージをインストールします。

`src/Module/AppModule.php`

```php
<?php
namespace MyVendor\Ticket\Module;

use BEAR\Dotenv\Dotenv;
use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;

use BEAR\Resource\Module\JsonSchemaModule;
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\IdentityValueModule\IdentityValueModule;
use Ray\MediaQuery\DbQueryConfig;
use Ray\MediaQuery\MediaQueryModule;
use Ray\MediaQuery\Queries;
use function dirname;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        (new Dotenv())->load(dirname(__DIR__, 2));
        $this->install(
            new AuraSqlModule(
                (string) getenv('TKT_DB_DSN'),
                (string) getenv('TKT_DB_USER'),
                (string) getenv('TKT_DB_PASS'),
                (string) getenv('TKT_DB_SLAVE')
            )
        );
        $this->install(
            new MediaQueryModule(
                Queries::fromDir($this->appMeta->appDir . '/src/Query'), [
                   new DbQueryConfig($this->appMeta->appDir . '/var/sql'),
                ]
            )
        );
        $this->install(new IdentityValueModule());
        $this->install(
            new JsonSchemaModule(
                $this->appMeta->appDir . '/var/schema/response',
                $this->appMeta->appDir . '/var/schema/request'
            )
        );
        $this->install(new PackageModule());
    }
}
```


## SQL

チケット用の３つのSQLを`var/sql`に保存します。

`var/sql/ticket_add.sql`

```sql
INSERT INTO ticket (id, title, dateCreated) VALUES (:id, :title, :dateCreated);
```

`var/sql/ticket_list.sql`

```sql
SELECT id, title, dateCreated FROM ticket LIMIT 3;
```

`var/sql/ticket_item.sql`

```sql
SELECT id, title, dateCreated FROM ticket WHERE id = :id
```

作成時に単体でそのSQLが動作するか確認しましょう。

例えば、PHPStormにはデータベースツールの[DataGrip](https://www.jetbrains.com/ja-jp/datagrip/)が含まれていて、コード補完やSQLのリファクタリングなどSQL開発に必要な機能が揃っています。
DB接続などのセットアップを行えば、SQLファイルをIDEで直接実行できます。[^3][^4]

## JsonSchema

`Ticket`（チケットアイテム）、`Tickets`（チケットアイテムリスト）のリソース表現を[JsonSchema](http://json-schema.org/)で定義し、それぞれ保存します。

`var/schema/response/ticket.json`

```json
{
  "$id": "ticket.json",
  "$schema": "http://json-schema.org/draft-07/schema#",
  "title": "Ticket",
  "type": "object",
  "required": ["id", "title", "dateCreated"],
  "properties": {
    "id": {
      "description": "The unique identifier for a ticket.",
      "type": "string",
      "maxLength": 64
    },
    "title": {
      "description": "The unique identifier for a ticket.",
      "type": "string",
      "maxLength": 255
    },
    "dateCreated": {
      "description": "The date and time that the ticket was created",
      "type": "string",
      "format": "datetime"
    }
  }
}
```

`var/schema/response/tickets.json`

Ticketsはticketの配列です。

```json
{
  "$id": "tickets.json",
  "$schema": "http://json-schema.org/draft-07/schema#",
  "title": "Tickets",
  "type": "object",
  "required": ["tickets"],
  "properties": {
    "tickets": {
      "type": "array",
      "items":{"$ref": "./ticket.json"}
    }
  }
}
```

* **$id** - ファイル名を指定しますが、公開する場合はURLを記述します。
* **title** - オブジェクト名としてAPIドキュメントで扱われます。
* **examples** - 適宜、例を指定しましょう。オブジェクト全体のも指定できます。

PHPStormではエディタの右上に緑色のチェックが出ていて問題がない事が分かります。スキーマ作成時にスキーマ自身もバリデートしましょう。

## クエリーインターフェイス

インフラストラクチャへのアクセスを抽象化したPHPのインターフェイスを作成します。

 * Ticketリソースを読み出す **TicketQueryInterface**
 * Ticketリソースを作成する **TicketCommandInterface**

`src/Query/TicketQueryInterface.php`

```php
<?php

namespace MyVendor\Ticket\Query;

use Ray\MediaQuery\Annotation\DbQuery;

interface TicketQueryInterface
{
    #[DbQuery('ticket_item')]
    public function item(string $id): array;

    #[DbQuery('ticket_list')]
    public function list(): array;
}
```

`src/Query/TicketCommandInterface.php`

```php
<?php

namespace MyVendor\Ticket\Query;

use DateTimeInterface;
use Ray\MediaQuery\Annotation\DbQuery;

interface TicketCommandInterface
{
    #[DbQuery('ticket_add')]
    public function add(string $id, string $title, DateTimeInterface $dateCreated = null): void;
}
```

`#[DbQuery]`アトリビュートでSQL文を指定します。

このインターフェイスに対する実装を用意する必要はありません。 指定されたSQLのクエリーを行うオブジェクトが自動生成されます。

インターフェイスを**副作用が発生するcommand**または**値を返すquery**という2つの関心に分けていますが、リポジトリパターンのように1つにまとめたり
[ADRパターン](https://github.com/pmjones/adr)のように1インターフェイス1メソッドにしても構いません。アプリケーション設計者が方針を決定します。

## リソース

リソースクラスはクエリーインターフェイスに依存します。

## tikcetリソース

`ticket`リソースを`src/Resource/App/Ticket.php`に作成します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;
use MyVendor\Ticket\Query\TicketQueryInterface;

class Ticket extends ResourceObject
{
    public function __construct(
        private TicketQueryInterface $query
    ){}
    
   #[JsonSchema("ticket.json")]
   public function onGet(string $id = ''): static
    {
        $this->body = $this->query->item($id);

        return $this;
    }
}
```

アトリビュート`#[JsonSchema]`は`onGet()`で出力される値が`ticket.json`のスキーマで定義されている事を表します。
AOPによってリクエスト毎にバリデートされます。

シードを入力してリソースをリクエストしてみましょう。[^8]

```bash 
% mysql -u root -e "INSERT INTO ticket (id, title, dateCreated) VALUES ('1', 'foo', '1970-01-01 00:00:00')" ticket
```

```bash
% php bin/app.php get '/ticket?id=1'
```
```bash
200 OK
Content-Type: application/hal+json

{
    "id": "1",
    "title": "foo",
    "dateCreated": "1970-01-01 00:00:01",
    "_links": {
        "self": {
            "href": "/ticket?id=1"
        }
    }
}
```

### Ray.MediaQuery

Ray.MediaQueryを使えば、ボイラープレートとなりやすい実装クラスをコーディングする事なく、インターフェイスから自動生成されたSQL実行オブジェクトがインジェクトされます。[^5]

SQL文には`;`で区切った複数のSQL分を記述する事ができ、複数のSQLに同じパラメーターが名前でバインドされます。SELECT以外のクエリーではトランザクションも実行されます。

利用クラスはインターフェイスにしか依存していないので、動的にSQLを生成したい場合にはRay.MediaQueryの代わりにクエリービルダーをインジェクトしたSQL実行クラスで組み立てたSQLを実行すれば良いでしょう。
詳しくはマニュアルの[データベース](database.html)をご覧ください。

## 埋め込みリンク

通常Webサイトのページは複数のリソースを内包します。例えばブログの記事ページであれば、記事以外にもおすすめや広告、カテゴリーリンクなどが含まれるかもしれません。
それらをクライアントがバラバラに取得する代わりに、独立したリソースとして埋め込みリンクで1つのリソースに束ねる事ができます。

HTMLとそこに記述される`<img>`タグをイメージしてください。どちらも独立したURLを持ちますが、画像リソースがHTMLリソースに埋めこ込まれていてHTMLを取得するとHTML内に画像が表示されます。
これらはハイパーメディアタイプの[Embedding links(LE)](http://amundsen.com/hypermedia/hfactor/#le)と呼ばれるもので、埋め込まれるリソースがリンクされています。

ticketリソースにprojectリソースを埋め込んでみましょう。Projectクラスを用意します。

`src/Resource/App/Project.php`

```php
<?php

namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\ResourceObject;

class Project extends ResourceObject
{
    public function onGet(): static
    {
        $this->body = ['title' => 'Project A'];

        return $this;
    }
}
```

Ticketリソースにアトリビュート`#[Embed]`を追加します。

```diff
+use BEAR\Resource\Annotation\Embed;
+use BEAR\Resource\Request;
+
+   #[Embed(src: '/project', rel: 'project')]
    #[JsonSchema("ticket.json")]
    public function onGet(string $id = ''): static
    {
+        assert($this->body['project'] instanceof Request);
-        $this->body = $this->query->item($id);
+        $this->body += $this->query->item($id);
```

`#[Embed]`アトリビュートの`src`で指定されたリソースのリクエストがbodyプロパティの`rel`キーにインジェクトされ、レンダリング時に遅延評価され文字列表現になります。

例を簡単にするためにこの例ではパラメーターを渡していませんが、メソッド引数が受け取った値をURI templateを使って渡す事もできますし、インジェクトされたリクエストのパラメーターを修正、追加する事ができます。
詳しくは[リソース](resource.html)をご覧ください。

もう一度リクエストすると`_embedded`というプロパティにprojectリソースの状態が追加されているのが分かります。

```
% php bin/app.php get '/ticket?id=1'
```
```diff

{
    "id": "1",
    "title": "2",
    "dateCreated": "1970-01-01 00:00:01",
+    "_embedded": {
+        "project": {
+            "title": "Project A",
+        }
    },
```

埋め込みリソースはREST APIの重要な機能です。 コンテンツにツリー構造を与えHTTPリクエストコストを削減します。
情報が他の何の情報を含んでいるかはドメインの関心事です。クライアントで都度取得するのではなく、その関心事はサーバーサイドのLE(埋め込みリンク)でうまく表す事ができます。[^6]

## ticketsリソース

`POST`で作成、`GET`でチケットリストが取得できる`tikcets`リソースを`src/resource/App/Tickets.php`に作成します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;
use Koriym\HttpConstants\ResponseHeader;
use Koriym\HttpConstants\StatusCode;
use MyVendor\Ticket\Query\TicketCommandInterface;
use MyVendor\Ticket\Query\TicketQueryInterface;
use Ray\IdentityValueModule\UuidInterface;
use function uri_template;

class Tickets extends ResourceObject
{
    public function __construct(
        private TicketQueryInterface $query,
        private TicketCommandInterface $command,
        private UuidInterface $uuid
    ){}

    #[Link(rel: "doPost", href: '/tickets')]
    #[Link(rel: "goTicket", href: '/ticket{?id}')]
    #[JsonSchema("tickets.json")]
    public function onGet(): static
    {
        $this->body = [
            'tickets' => $this->query->list()
        ];
        
        return $this;
    }

    #[Link(rel: "goTickets", href: '/tickets')]
    public function onPost(string $title): static
    {
        $id = (string) $this->uuid;
        $this->command->add($id, $title);

        $this->code = StatusCode::CREATED;
        $this->headers[ResponseHeader::LOCATION] = uri_template('/ticket{?id}', ['id' => $id]);

        return $this;
    }
}
```

インジェクトされた`$uuid`は文字列にキャストする事でUUIDが得られます。また`#Link[]`は他のリソース（アプリケーション状態）へのリンクを表します。

`add()`メソッドで現在時刻を渡してない事に注目してください。
値が渡されない場合nullではなく、MySQLの現在時刻文字列がSQLにバインドされます。
なぜなら`DateTimeInterface`に束縛された現在時刻DateTimeオブジェクトの文字列表現（現在時刻文字列）がSQLに束縛されているからです。

```php
public function add(string $id, string $title, DateTimeInterface $dateCreated = null): void;
```
SQL内部でNOW()とハードコーディングする事や、メソッドに毎回現在時刻を渡す手間を省きます。
`DateTimeオブジェクト`を渡す事もできるし、テストのコンテキストでは固定のテスト用時刻を束縛することもできます。

このようにクエリーの引数にインターフェイスを指定するとそのオブジェクトをDIを使って取得、その文字列表現がSQLに束縛されます。
例えばログインユーザーIDなどを束縛してアプリケーションで横断的に利用できます。[^7]

## ハイパーメディアAPIテスト

> REST(representational state transfer)という用語は、2000年にRoy Fieldingが博士論文の中で紹介、定義したもので「適切に設計されたWebアプリケーションの動作」をイメージさせることを目的としていてます。
> それはWebリソースのネットワーク（仮想ステートマシン）であり、ユーザーはリソース識別子(URL)と、 GETやPOSTなどのリソース操作（アプリケーションステートの遷移）を選択することで、アプリケーションを進行させ、その結果、次のリソースの表現（次のアプリケーションステート）がエンドユーザーに転送されて使用されるというものです。
>
> -- [Wikipedia (REST)](https://en.wikipedia.org/wiki/Representational_state_transfer)

RESTアプリケーションでは次のアクションがURLとしてサービスから提供され、クライアントはそれを選択します。

HTML Webアプリケーションは完全にRESTfulです。その操作は「（aタグなどで）**提供されたURLに遷移する**」 または 「**提供されたフォームを埋めて送信する**」この何れかでしかありません。

REST APIのテストも同様に記述します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Hypermedia;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use Koriym\HttpConstants\ResponseHeader;
use MyVendor\Ticket\Injector;
use MyVendor\Ticket\Query\TicketQueryInterface;
use PHPUnit\Framework\TestCase;
use Ray\Di\InjectorInterface;
use function json_decode;

class WorkflowTest extends TestCase
{
    protected ResourceInterface $resource;
    protected InjectorInterface $injector;

    protected function setUp(): void
    {
        $this->injector = Injector::getInstance('hal-api-app');
        $this->resource = $this->injector->getInstance(ResourceInterface::class);
        $a = $this->injector->getInstance(TicketQueryInterface::class);
    }

    public function testIndex(): ResourceObject
    {
        $index = $this->resource->get('/');
        $this->assertSame(200, $index->code);

        return $index;
    }

    /**
     * @depends testIndex
     */
    public function testGoTickets(ResourceObject $response): ResourceObject
    {

        $json = (string) $response;
        $href = json_decode($json)->_links->{'goTickets'}->href;
        $ro = $this->resource->get($href);
        $this->assertSame(200, $ro->code);

        return $ro;
    }

    /**
     * @depends testGoTickets
     */
    public function testDoPost(ResourceObject $response): ResourceObject
    {
        $json = (string) $response;
        $href = json_decode($json)->_links->{'doPost'}->href;
        $ro = $this->resource->post($href, ['title' => 'title1']);
        $this->assertSame(201, $ro->code);

        return $ro;
    }

    /**
     * @depends testDoPost
     */
    public function testGoTicket(ResourceObject $response): ResourceObject
    {
        $href = $response->headers[ResponseHeader::LOCATION];
        $ro = $this->resource->get($href);
        $this->assertSame(200, $ro->code);

        return $ro;
    }
}
```

起点となるルートページも必要です。

`src/Resource/App/Index.php`

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    #[Link(rel: 'goTickets', href: '/tickets')]
    public function onGet(): static
    {
        return $this;
    }
}
```

* `setUp`ではリソースクライアントを生成、`testIndex()`でルートページをアクセスしています。
* レスポンスを受け取った`testGoTickets()`メソッドではそのレスポンスオブジェクトをJSON表現にして、次のチケット一覧を取得するリンク`goTickets`を取得しています。
* リソースボディのテストを記述する必要はありません。レスポンスのJsonSchemaバリデーションが通ったというが保証されているので、ステータスコードの確認だけでOKです。
* RESTの統一インターフェイスに従い、次にアクセスするリクエストURLは常にレスポンスに含まれます。それを次々に検査します。

> **RESTの統一インターフェイス**
> 
> 1)リソースの識別、2)表現によるリソースの操作、3)自己記述メッセージ、
> 4)アプリケーション状態のエンジンとしてのハイパーメディア(HATEOAS)の4つのインターフェイス制約です。[^11]

実行してみましょう

```bash
./vendor/bin/phpunit --testsuite hypermedia
```

ハイパーメディアAPIテスト（RESTアプリケーションテスト）はRESTアプリケーションがステートマシンであるという事をよく表し、ワークフローをユースケースとして記述する事ができます。
REST APIテストを見ればそのアプリケーションがどのように使われるか網羅されているのが理想です。

### HTTPテスト

HTTPでREST APIのテストを行うためにはテスト全体を継承して、`setUp`でクライアントをHTTPテストクライアントにします。

```php
class WorkflowTest extends Workflow
{
    protected function setUp(): void
    {
        $this->resource = new HttpResource('127.0.0.1:8080', __DIR__ . '/index.php', __DIR__ . '/log/workflow.log');
    }
}
```

このクライアントはリソースクライアントと同じインターフェイスを持ちますが、実際のリクエストはビルトインサーバーに対してHTTPリクエストで行われサーバーからのレスポンスを受け取ります。
1つ目の引数はビルトインサーバーのURLです。`new`されると二番目の引数で指定されたbootstrapスクリプトでビルトインサーバーが起動します。

テストサーバー用のbootstrapスクリプトもAPIコンテキストに変更します。

`tests/Http/index.php`

```diff
-exit((new Bootstrap())('hal-app', $GLOBALS, $_SERVER));
+exit((new Bootstrap())('hal-api-app', $GLOBALS, $_SERVER));
```

実行してみましょう。

```
./vendor/bin/phpunit --testsuite http
```
#### HTTPアクセスログ

curlで行われた実際のHTTPリクエスト/レスポンスログが三番目の引数のリソースログに記録されます。

```
curl -s -i 'http://127.0.0.1:8080/'

HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Date: Fri, 21 May 2021 22:41:02 GMT
Connection: close
X-Powered-By: PHP/8.0.6
Content-Type: application/hal+json

{
    "_links": {
        "self": {
            "href": "/index"
        },
        "goTickets": {
            "href": "/tickets"
        }
    }
}
```

```
curl -s -i -H 'Content-Type:application/json' -X POST -d '{"title":"title1"}' http://127.0.0.1:8080/tickets

HTTP/1.1 201 Created
Host: 127.0.0.1:8080
Date: Fri, 21 May 2021 22:41:02 GMT
Connection: close
X-Powered-By: PHP/8.0.6
Location: /ticket?id=421d997c-9a0e-4018-a6c2-9b8758cac6d6
```


実際に記録されたJSONは、特に複雑な構造を持つ場合に確認するのに役に立ちます。APIドキュメントと併せて確認するのにもいいでしょう。
HTTPクライアントはE2Eテストにも利用する事ができます。

## APIドキュメント

ResourceObjectではメソッドシグネチャーがAPIの入力パラメーターになっていて、レスポンスがスキーマ定義されています。
その自己記述性の高さからAPIドキュメントが自動生成する事ができます。

作成してみましょう。[docs](https://bearsunday.github.io/tutorial2/)フォルダにドキュメントが出力されます。

```
composer doc
```

    

IDL(インターフェイス定義言語）を記述する労力を削減しますが、より価値があるのはドキュメントが最新のPHPコードに追従し常に正確な事です。
CIに組み込み常にコードとAPIドキュメントが同期している状態にするのがいいでしょう。

関連ドキュメントをリンクする事もできます。設定について詳しくは[ApiDoc](apidoc.html)をご覧ください。

## コード例

以下のコード例も用意しています。

* `Test`コンテキストを追加してテスト毎にDBをクリアするTestModule [4e9704d](https://github.com/bearsunday/tutorial2/commit/4e9704d3bc65b9c7e7a8c13164dfe7cc3d6929b2)
* DBクエリーで連想配列を返す代わりにハイドレートされたエンティティクラスを返す[Ray.MediaQuery]()の`entity`オプション [29f0a1f](https://github.com/bearsunday/tutorial2/commit/29f0a1f4d4bf51e6c0a722fd6b2f44cb78de999e)
* 静的なSQLと動的なSQLを合成したクエリービルダー [9d095ac](https://github.com/bearsunday/tutorial2/commit/9d095acfed6150fb99f36d502ae13f03bdf2916d)

## REST framework

Web APIには以下の3つのスタイルがあります。

* トンネル (SOAP, GraphQL）
* URI (オブジェクト、CRUD)
* ハイパーメディア (REST)

リソースを単なるRPCとして扱うURIスタイル[^9]に対して、 このチュートリアルで学んだのはリソースがリンクされているRESTです。[^10]
リソースは`#Link`のLO(アウトバウンドリンク)で結ばれワークフローを表し、`#[Embed]`のLE(埋め込みリンクで)ツリー構造を表しています。

BEAR.Sundayは標準に基づいたクリーンなコードである事を重視します。 

フレームワーク固有のバリデータよりJsonSchema。独自ORMより標準SQL。独自構造JSONよりIANA標準メディアタイプ[^12]JSON。

アプリケーション設計は「実装が自由である」事ではなく「制約の選択が自由である」という事が重要です。
アプリケーションはその制約に基づき開発効率やパフォーマンス、後方互換性を壊さない進化可能性を目指すと良いでしょう。

----

[^1]:.envはgit commitされないようにしておきます。
[^2]:例えばECサイトであれば、商品一覧、カートに入れる、注文、支払い、などそれぞれのアプリケーションステートの遷移をテストで表します。
[^3]:[PHPStorm データベースツールおよび SQL](https://pleiades.io/help/phpstorm/relational-databases.html))
[^4]:[データベース図](https://pleiades.io/help/phpstorm/creating-diagrams.html)などでクエリプランや実行計画を確認し、作成するSQLの質を高めます。
[^5]: Ray.MediaQueryはHTTP APIリクエストにも対応しています。
[^6]: このようなコンテンツの階層構造の事を、IA(インフォメーションアーキテクチャ)では**タクソノミー**と呼びます。[Understanding Information Architecture](https://understandinggroup.com/ia-theory/understanding-information-architecture)参照
[^7]: Ray.MediaQuery [README](https://github.com/ray-di/Ray.MediaQuery/blob/1.x/README.ja.md#%E6%97%A5%E4%BB%98%E6%99%82%E5%88%BB)
[^8]: ここでは例としてmysqlから直接実行していますが、マイグレーションツールでseedを入力したりIDEのDBツールの利用方法も学びましょう。
[^9]: いわゆる"Restish API"。REST APIと紹介されている多くのAPIはこのURI/オブジェクトスタイルで、RESTが誤用されています。
[^10]: チュートリアルからリンクを取り除けばURIスタイルになります。
[^11]: 広く誤解されていますが統一インターフェイスはHTTPメソッドの事ではありません。[Uniform Interface](https://www.ics.uci.edu/~fielding/pubs/dissertation/rest_arch_style.htm)参照
[^12]: [https://www.iana.org/assignments/media-types/media-types.xhtml](https://www.iana.org/assignments/media-types/media-types.xhtml)

※ 以前のPHP7対応のチュートリアルは[tutorial2_v1](tutorial2_v1.html)にあります。

