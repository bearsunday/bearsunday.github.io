---
layout: docs-en
title: Tutorial 2
category: Manual
permalink: /manuals/1.0/en/tutorial2.html
---

**[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/tutorial2.md) was created by machine translation and it needs to be proofread by native speaker.**

---

# Tutorial 2

In this tutorial, we will create a REST API for task management ticket creation / acquisition using the following tools and learn the development of loosely coupled high quality test driven REST API application.[^1]

* A framework-agnostic [Phinx](https://book.cakephp.org/3.0/ja/phinx.html) DB migration tool by CakePHP
* Define the data structure of JSON and use it for validation and documentation [Json Schema](https://qiita.com/kyoh86/items/e7de290e9a0e989fcc14)
* Convert SQL statement to SQL execution object and make application layer and data access layer loosely coupled [ray/query-module](https://github.com/ray-di/Ray.QueryModule)
* Inject UUID and current time [IdentityValueModule](https://github.com/ray-di/Ray.IdentityValueModule)

The aim is to create a schema-defined, high quality and self-descriptive API.

The repository is [bearsunday/tutorial2](https://github.com/bearsunday/tutorial2).

## New Project

Create a project skeleton.

```
composer create-project bear/skeleton MyVendor.Ticket
```

Enter `MyVendor` for **vendor** and enter `Ticket` for **project**.[^2]

## Composer install

Next, type the following commands to install all dependencies.

```
composer require bear/aura-router-module ray/identity-value-module ray/query-module
composer require --dev robmorgan/phinx bear/api-doc
```

## Module install

Edit `src/Module/AppModule.php` to install the package you installed with composer.

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

We also create `src/Module/TestModule.php` for testing the database.

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

Create the required folders by typing those commands:

```bash
mkdir var/sql
mkdir var/json_schema
mkdir var/json_validate
```

## Router file

To define the routes, create a router file in `var/conf/aura.route.php`. The following route will map the `tickets/{id}` route to the `Ticket` class.

```php
<?php
/* @var\Aura\Router\Map $map */
$map->route('/ticket', '/tickets/{id}');
```

## DB

Write the connection information in the `.env` file in the project root folder. [^6]

```
TKT_DB_HOST=127.0.0.1
TKT_DB_NAME=ticket
TKT_DB_USER=root
TKT_DB_PASS=''
TKT_DB_SLAVE=''
TKT_DB_DSN=mysql:host=${TKT_DB_HOST};dbname=${TKT_DB_NAME}
```

Because `.env` file is not comitted to Git, it can contains sensitive information such as password. You can however create a copy called `env.dist` that will be committed, and that contains an example of the file structure.

```
cp .env .env.dist
// remove password, etc..
git add .env.dist
```

## Migration

It prepares the execution environment of phinx.

First, create a folder that phinx will use.

```bash
mkdir -p var/phinx/migrations
mkdir var/phinx/seeds
```

Next, set `var/phinx/phinx.php` to use the connection information of `.env` in phinx.

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
## setup script

Edit `bin/setup.php` to make database creation and migration easy.

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

Run it and create the database.

```
composer setup
```
```
Phinx by CakePHP - https://phinx.org. 0.10.6

...
using database ticket_test
```

Next create a migration class to create a `ticket` table.

```
./vendor/bin/phinx create Ticket -c var/phinx/phinx.php
```
```
Phinx by CakePHP - https://phinx.org. 0.10.6

...
created var/phinx/migrations/20180920054037_ticket.php
```

Edit `var/phinx/migrations/{current_date}_ticket.php` and implement the `change()` method.

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

Run the setup command again to create the table.

```
composer setup
```
```
> php bin/setup.php
Phinx by CakePHP - https://phinx.org. 0.10.6

...
All Done. Took 0.0248s
```

The table has been created. You can do this just by running `composer setup` to prepare the database environment of this project from next time. [^7]
For details on the description of migration classes, please refer to [Phix Manual: Write Migration](https://book.cakephp.org/3.0/en/phinx/migrations.html).

## SQL

Save the following three SQL files in `var/sql`. Each file contains a specific SQL query that create a ticket, get all tickets and get a specific ticket by id, respectively.

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

The above description of SQL conforms to [SQL style guide](https://www.sqlstyle.guide/en/).

  Using [Database Navigator](https://confluence.jetbrains.com/display/CONTEST/Database+Navigator) on PHPStorm you can take advantage of SQL code completion and execution. [youtube](https://www.youtube.com/watch?v=P3C0iO1yqhk)
  We would recommend you to use a tool such as [JetBrain DataGrip](https://www.jetbrains.com/datagrip/), [Sequel Pro](https://www.sequelpro.com/), or [MySQL Workbench](https://www.mysql.com/jp/products/workbench/) to easily write SQL queries and make sure they are valid, before executing them with PHP.

## JsonSchema

In order to create two resources, `Ticket` (`ticket item`),` Tickets` (`collection of ticket items`), we first define the definition of these resources as [JsonSchema](http://json-schema.org/).

Save each schema file in the `var/json_schema` folder.

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

You can validate the created JSON using `validate-json`. [^13]
```
./vendor/bin/validate-json var/json_schema/ticket.json
./vendor/bin/validate-json var/json_schema/tickets.json
```

Now that the schema have been defined, the framework will be able to use them to validate the data. Please note that you can use different schema files for the front-end, if validation requirements differ.

## Test

Next, create the `tests/Resource/App/TicketsTest.php` file to test the `/ticket` resource.

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
        $this->assertStringContainsString('/ticket?id=', $ro->headers['Location']);

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

`$this->resource` is a resource client when you run the `MyVendor\Ticket` application in the `test-app` context.
`AppModule`,` TestModule`, so the database uses the `ticket_test` database for testing.

`testOnPost` creates a resource by simulating a POST request, while `testOnGet` validates that the resource exists by inspecting the Location response header.

We can run the tests by typing the following command:

```
composer test
```

Since the code has not been implemented yet, an error should be raised. However, writing the tests before the implementation allows for a cleaner and simpler implementation as we know beforehand the goal of the method.

## Resource

The logic of the resource can be expressed as SQL and its validation can be represented by JSON file. Resource classes use those files.

### tikcet resource

Create `ticket` resource in `src/Resource/App/Ticket.php`.

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
     * @Schema(schema="ticket.json")
     * @Query("ticket_item_by_id", type="row")
     */
    public function onGet(string $id) : ResourceObject
    {
        unset($id);

        return $this;
    }
}
```

### ticket - GET request

Let's see the `onGet` method for` GET`. If you look at the method signature, you can see that the input required for the request is only `$_GET['id']` and can not be omitted.

The `@JsonSchema` annotation ensures real-time validation by AOP every time declaring that the　`ticket` key array of this class's `body` property is a schema defined with　`ticket.json` It is.

This method replaces SQL execution as `@Query("ticket_item_by_id", type ="row")` is specified. The SQL in the `var/sql/ticket_item_by_id.sql` file is executed and the result is returned as a single line (type="row"). If you can simply replace the logic with SQL like this you can omit PHP description using `@Query`.

### tikcets　resource

Next we will create a `tikcets` resource in the set of` tikcet` resources in `src/resource/App/Tickets.php`.

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

### tickets - GET request

Please see the `var/json_schema/tickets.json` JSON schema. It is defined as a collection of `ticket.json` schemas (array).
In this way the JSON schema can represent the structure of the schema. As with the `/ticket` resource, the method returns the SQL execution of `ticket_list.sql` as the result.

### tickets - POST request

The `$this->createTicket` injected in the constructor is the execution object of `ticket_insert.sql`. Bind the received associative array and execute SQL.
When creating a resource make sure to save the URL of the resource in the `Location` header. If you want to include the created content in the body, annotate it as `@ReturnCreatedResource`.

### index Resource

The `index` resource is a collection of links to the created resource (API). Create it in `src/Resource/App/Index.php`.

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
                'title' => 'The ticket item',
                'templated' => true
            ],
            'tk:tickets' => [
                'href' => '/tickets',
                'title' => 'The ticket list'
            ]
        ]
    ];

    public function onGet() : ResourceObject
    {
        return $this;
    }
}
```

Using the format [CURIE](https://en.wikipedia.org/wiki/CURIE) (compact URI), information on what resources this project has and information on where those documents are located The API itself can service it.

To make it unnecessary to know all the URIs before using the Web site, the API service likewise increases the "Discoverability" of the API by having a list link.

Let's request it immediately.

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
            "title": "The ticket item",
            "templated": true
        },
        "tk:tickets": {
            "href": "/tickets",
            "title": "The ticket list"
        }
    }
}
```

`[curies](http://stateless.co/hal_specification.html)` is a link for human-readable documents.
You can see that this API has two resources, `/ticket` and` / ickets`.
`curies` indicates that these documents are located in` rels/ticket.html`, `rels/tickets.html` respectively.

Since I have not created it yet, I can not see it, but I can check it with `OPTIONS` command.

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

It is displayed as a machine-readable API document.

Let's access `/tickets`.


Create a ticket with POST request.

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

GET request the URI of the Location header in the response.

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

Did you get the response with 200 OK?
You can see in the `describedby` in the` Link` header that the definition of this response is defined by `ticket.json`. [^9]
Resources declared `@Cacheable` are appended with` ETag` and `Last-Modified` headers to enable more efficient HTTP level caching.
`@Purge` is the destruction of the cache. [^10]

The first test I have made should pass well now. Let's try it.

```
composer test
```
You can check with the tool whether you are writing according to coding conventions or if `phpdoc` is written just like code.

If you get an error you can fix it with `cs-fix`.

```
composer cs-fix
```

You can also do unit testing, coding conventions, static analysis tools at the same time. Let's do it before committing. [^3]

```
composer tests
```

You can generate an optimized `autoload.php` with the` compile` command to generate a DI / AOP script. Let's run it before deploying. [^4] [^5]
The BEAR.Sunday application that DIs everything can find dependency problems before running the application. You can also see the DI bondage information in the log, so it is useful even during development.

```
composer compile
```

Did you pass the test and compile well? Completion of the REST API!

## API Document

Add the following `doc` command to` scrpits` of `composer.json` to output the API document.

```
"doc": "bear.apidoc 'MyVendor\\Ticket' ./ docs",
```

Create a directory for the document.

```
mkdir docs
```

HTML compositing API site and JSON will be output with `composer doc` command.

```
composer doc
```
```
API Doc is created at /path/to/docs
```

Publish this site on GitHub Pages [^11] etc., and make it API document.
Once the domain of the public API site is determined, specify the public domain with the `JsonSchemaLinkHeaderModule()` module.


```php?start_inline
 $this->install(new JsonSchemaLinkHeaderModule('https://{your-domain}/schema'));
```

You should be able to create such an API document site.

[https://bearsunday.github.io/tutorial2/](https://bearsunday.github.io/tutorial2/)

We keep track of the code site and the document created at which point by managing the document site the same Github as the code.

## Recap

* You can now create a database environment according to the application version using the phinx migration tool.

  * If you can build the environment with the `composer setup` command, you do not need to manipulate database commands and it is also useful for deployment and CI.

  * By placing the SQL file in the `var/sql` folder, it can be executed as standalone by GUI or CLI SQL tool, it is convenient for development and operation and easy to test. Static SQL is complemented by PhpStorm, and some tools can be modeled with GUI.

  * Resource arguments and output are declared in methods and schemas and are clear. Validation at AOP ensures the validity of the document, minimizing the document maintenance effort.

Was the tutorial done well? Let's star on a well-done tutorial [bearsunday/tutorial2] (https://github.com/bearsunday/tutorial2) to commemorate.
When it does not work it may be resolved by consulting with [gitter](https://gitter.im/bearsunday/BEAR.Sunday). [PR](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/tutorial2.md) if you have suggestions or mistakes please!

---

[^1]: This is for those who have finished [Tutorial](/manuals/1.0/en/tutorial.html). There are also places to suffer, but let's try trying out for a while. The repository is [bearsaunday/Tutorial2](https://github.com/bearsunday/Tutorial2). Let's compare when it does not go well.
[^2]: Normally **vendor** Name is the name of individual or team (organization). Github's account name and team name would be appropriate. Enter the application name in project.
[^3]: Setting a commit hook is also a good method.
[^4]: It is certain to do twice to "warm up" the cache.
[^5]: To change the context edit the `compile` script command of` composer.json`.
[^6]: There is no environment variable on which the BEAR.Sunday framework depends.
[^7]: This is handy as there is no need to explain the operation of the mysql command with the README.
[^9]: [http://json-schema.org/latest/json-schema-core.html#rfc.section.10.1](http://json-schema.org/latest/json-schema-core.html#rfc.section.10.1)
[^10]: POST with `/ticket` destroys the cache of` / tickets` resource. `@ Refresh` will regenerate the cache at the time of corruption.
[^11]: [Publishing your GitHub Pages site from a /docs folder on your master branch](https://help.github.com/articles/configuring-a-publishing-source-for-github-pages/#publishing -your-github-pages-site-from-a-docs-folder-on-your-master-branch)
[^13]: As of September 2018 php 7.3 can be executed but `PHP Deprecated` is displayed.
