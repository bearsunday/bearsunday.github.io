---
layout: docs-en
title: Tutorial 2
category: Manual
permalink: /manuals/1.0/en/tutorial2.html
---

# Tutorial 2


In this tutorial, you will learn how to develop high quality standards-based REST (Hypermedia) applications using the following tools.

* Define a JSON schema and use it for validation and documentation [Json Schema](https://json-schema.org/)
* Hypermedia types [HAL (Hypertext Application Language)](https://stateless.group/hal_specification.html)
* A DB migration tool developed by CakePHP [Phinx](https://book.cakephp.org/phinx/0/en/index.html)
* Binding PHP interfaces to SQL statement execution [Ray. MediaQuery](https://github.com/ray-di/Ray.MediaQuery)

Let's proceed with the commits found in [tutorial2](https://github.com/bearsunday/tutorial2/commits/v2-php8.2).

## Create the project

Create the project skeleton.

```
composer create-project bear/skeleton MyVendor.Ticket
```

Enter the **vendor** name as `MyVendor` and the **project** name as `Ticket`.

## Migration

Install Phinx.

```
composer require --dev robmorgan/phinx
```

Configure the DB connection information in the `.env.dist` file in the project root folder.

```
TKT_DB_HOST=127.0.0.1:3306
TKT_DB_NAME=ticket
TKT_DB_USER=root
TKT_DB_PASS=''
TKT_DB_SLAVE=''
TKT_DB_DSN=mysql:host=${TKT_DB_HOST}
```

The `.env.dist` file should look like this, and the actual connection information should be written in `.env`. ^1]

Next, create a folder to be used by Phinx.

```bash
mkdir -p var/phinx/migrations
mkdir var/phinx/seeds
```

Set up `var/phinx/phinx.php` to use the `.env` connection information we have set up earlier.

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

### setup script

Edit `bin/setup.php` for easy database creation and migration.

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

Next, we will create a migration class to create the `ticket` table.

```
./vendor/bin/phinx create Ticket -c var/phinx/phinx.php
```
```
Phinx by CakePHP - https://phinx.org.

...
created var/phinx/migrations/20210520124501_ticket.php
```

Edit `var/phinx/migrations/{current_date}_ticket.php` to implement the `change()` method.

```php
<?php
use Phinx\Migration\AbstractMigration;

final class Ticket extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('ticket', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('title', 'string')
            ->addColumn('date_created', 'datetime')
            ->create();
    }
}
```

In addition, edit `.env.dist` like the following.

```diff
 TKT_DB_USER=root
 TKT_DB_PASS=
 TKT_DB_SLAVE=
-TKT_DB_DSN=mysql:host=${TKT_DB_HOST}
+TKT_DB_DSN=mysql:host=${TKT_DB_HOST};dbname=${TKT_DB_NAME}
```

Now that we are done with the setup, run the setup command to create the table.

```
composer setup
```
```
> php bin/setup.php
...
All Done. Took 0.0248s
```

The table has been created. The next time you want to set up a database environment for this project, just run `composer setup`.

For more information about writing migration classes, see [Phinx Manual: Writing Migrations](https://book.cakephp.org/3.0/ja/phinx/migrations.html).

## Module

Install the module as a composer.

```
composer require ray/identity-value-module ray/media-query -w
```

Install the package with AppModule.

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

Save the three SQLs for the ticket in `var/sql`.[^13]

`var/sql/ticket_add.sql`

```sql
/* ticket add */
INSERT INTO ticket (id, title, date_created)
VALUES (:id, :title, :dateCreated);
```

`var/sql/ticket_list.sql`

```sql
/* ticket list */
SELECT id, title, date_created
  FROM ticket
 LIMIT 3;
```

`var/sql/ticket_item.sql`

```sql
/* ticket item */
SELECT id, title, date_created
  FROM ticket
 WHERE id = :id
```

Make sure that the SQL will work on its own when you create it.

> PHPStorm includes a database tool, [DataGrip](https://www.jetbrains.com/datagrip/), which has all the necessary features for SQL development such as code completion and SQL refactoring.
Once the DB connection and other setups are made, SQL files can be executed directly in the IDE. [^3][^4]

## JsonSchema.

Create new files that will represent the resource `Ticket` (ticket item) and `Tickets` (ticket item list) with [JsonSchema](http://json-schema.org/):

`var/schema/response/ticket.json`

```json
{
  "$id": "ticket.json",
  "$schema": "http://json-schema.org/draft-07/schema#",
  "title": "Ticket",
  "type": "object",
  "required": ["id", "title", "date_created"],
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
    "date_created": {
      "description": "The date and time that the ticket was created",
      "type": "string",
      "format": "datetime"
    }
  }
}
```

`var/schema/response/tickets.json`

Tickets is a `Ticket` array.

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

* **$id** - specifies the file name, but if it is to be published, it should be a URL.
* **title** - This will be treated in the API documentation as an object name.
* **examples** - specify examples as appropriate. You can also specify the entire object.

In PHPStorm, you will see a green check in the upper right corner of the editor to indicate that everything is OK. You should also validate the schema itself when you create it.

## Query Interface

We will create a PHP interface that abstracts access to the infrastructure.

* Read Ticket resources **TicketQueryInterface**.
* Create a Ticket resource **TicketCommandInterface**.

`src/Query/TicketQueryInterface.php`

```php
<?php

namespace MyVendor\Ticket\Query;

use Ray\MediaQuery\Annotation\DbQuery;

interface TicketQueryInterface
{
    #[DbQuery('ticket_item')]
    public function item(string $id): Ticket|null;

    /** @return array<Ticket> */
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

Specify an SQL statement with the `#[DbQuery]` attribute. You do not need to write any implementation for this interface. An object that performs the specified SQL query will be created automatically.

The interface is divided into two concerns: **command** which has side effects, and **query** which returns a value.
It can be one interface and one method as in [ADR pattern](https://github.com/pmjones/adr). The application designer decides the policy.

## Entity

If you specify `array` for the return value of a method, you will get the database result as it is, an associative array, but if you specify an entity type for the return value of the method, it will be hydrated to that type.

``php
#[DbQuery('ticket_item')
public function item(string $id): array // you get an array.
```

```php
#[DbQuery('ticket_item')].
public function item(string $id): ticket|null; // yields a Ticket entity.
```

For multiple rows (row_list), use `/** @return array<Ticket>*/` and phpdoc to specify that ``Ticket`` is returned as an array.

```
/** @return array<Ticket> */
#[DbQuery('ticket_list')].
public function list(): array; // yields an array of Ticket entities.
```
The value of each row is passed to the constructor by name argument. [^named]

[^named]: [PHP 8.0+ named arguments ¶](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments), column order for PHP 7.x.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Entity;

class Ticket
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $dateCreated
    ) {}
}
```

## Resources

The resource class depends on the query interface.

## Ticket resource

Create a `ticket` resource in `src/Resource/App/Ticket.php`.

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
        $this->body = (array) $this->query->item($id);

        return $this;
    }
}
```

The attribute `#[JsonSchema]` indicates that the value output by `onGet()` is defined in the `ticket.json` schema.
It is validated for each request by AOP.

Let's try to request a resource by entering a seed. [^8]

```bash 
% mysql -u root -e "INSERT INTO ticket (id, title, date_created) VALUES ('1', 'foo', '1970-01-01 00:00:00')" ticket
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
    "date_created": "1970-01-01 00:00:01",
    "_links": {
        "self": {
            "href": "/ticket?id=1"
        }
    }
}
```

### MediaQuery

With Ray.MediaQuery, an auto-generated SQL execution object is injected from the interface without the need to code boilerplate implementation classes. [^5]

A SQL statement can contain multiple SQLs separated by `;`, and multiple SQLs are bound to the same parameter by name, and transactions are executed for queries other than SELECT.

If you want to generate SQL dynamically, you can use an SQL execution class that injects the query builder instead of Ray.
For more details, please see [Database](database.html) in the manual.

## Embedded links

Usually, a website page contains multiple resources. For example, a blog post page might contain recommendations, advertisements, category links, etc. in addition to the post.
Instead of the client getting them separately, they can be bundled into one resource with embedded links as independent resources.

Think of HTML and the `<img>` tag that is written in it. Both have independent URLs, but the image resource is embedded in the HTML resource, and when the HTML is retrieved, the image is displayed in the HTML.
These are called hypermedia types [Embedding links(LE)](http://amundsen.com/hypermedia/hfactor/#le), and the resource to be embedded is linked.

Let's embed the project resource into the ticket resource, and prepare the Project class.

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

Add the attribute `#[Embed]` to the Ticket resource.

```diff
+use BEAR\Resource\Annotation\Embed;
+use BEAR\Resource\Request;
+
+   #[Embed(src: '/project', rel: 'project')]
    #[JsonSchema("ticket.json")]
    public function onGet(string $id = ''): static
    {
+        assert($this->body['project'] instanceof Request);
-        $this->body = (array) $this->query->item($id);
+        $this->body += (array) $this->query->item($id);
```

The request for the resource specified by the `#[Embed]` attribute `src` will be injected into the `rel` key of the body property, and will be lazily evaluated into a string representation when rendered.

For the sake of simplicity, no parameters are passed in this example, but you can pass the values received by the method arguments using the URI template, or you can modify or add parameters to the injected request.
See [resource](resource.html) for details.

If you make the request again, you will see that the status of the project resource has been added to the property `_embedded`.

```
% php bin/app.php get '/ticket?id=1'
```
```diff

{
    "id": "1",
    "title": "2",
    "date_created": "1970-01-01 00:00:01",
+    "_embedded": {
+        "project": {
+            "title": "Project A",
+        }
    },
```

Embedded resources are an important feature of the REST API. It gives a tree structure to the content and reduces the HTTP request cost. Instead of letting the client fetching it as a separate resource each time, the relationship can be represented in server-side. [^6]

## tickets resource

Create a `tickets` resource in `src/resource/App/Tickets.php` that can be created with `POST` and retrieved with `GET` for a list of tickets.

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

The injected `$uuid` can be cast to a string to get the UUID. Also, `#Link[]` represents a link to another resource (application state).

Notice that we don't pass the current time in the `add()` method.
If no value is passed, it will not be null, but the MySQL current time string will be bound to the SQL.
This is because the string representation of the current time DateTime object bound to the `DateTimeInterface` (current time string) is bound to SQL.

```php
public function add(string $id, string $title, DateTimeInterface $dateCreated = null): void;
```
It saves you the trouble of hard-coding NOW() inside SQL and passing the current time to the method every time.
You can pass a `DateTime object`, or in the context of a test, you can bind a fixed test time.

In this way, if you specify an interface as an argument to a query, you get that object using DI, and its string representation is bound to SQL.
For example, login user IDs can be bound and used across applications. [^7]

## Hypermedia API test

> The term REST (representational state transfer) was introduced and defined by Roy Fielding in his doctoral dissertation in 2000, and is intended to give an idea of "the behavior of a properly designed web application".
> It is a network of web resources (a virtual state machine) where the user selects a resource identifier (URL) and a resource operation (application state transition) such as GET or POST to proceed with the application, resulting in the next representation of the resource (the next application state) being forwarded to the end user. application state) is transferred to the end user for use.
>
> -- [Wikipedia (REST)](https://en.wikipedia.org/wiki/Representational_state_transfer)

In a REST application, the following actions are provided by the service as URLs, and the client selects them.

HTML web applications are completely RESTful. The only operations are "**Go to the provided URL** (with a tag, etc.)" or "**Fill the provided form and submit**".

The REST API tests are written in the same way.

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

    public function testIndex(): static
    {
        $index = $this->resource->get('/');
        $this->assertSame(200, $index->code);

        return $index;
    }

    /**
     * @depends testIndex
     */
    public function testGoTickets(ResourceObject $response): static
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
    public function testDoPost(ResourceObject $response): static
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
    public function testGoTicket(ResourceObject $response): static
    {
        $href = $response->headers[ResponseHeader::LOCATION];
        $ro = $this->resource->get($href);
        $this->assertSame(200, $ro->code);

        return $ro;
    }
}
```

You will also need a route page as a starting point.

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

* `setUp` creates a resource client, and `testIndex()` accesses the root page.
* The `testGoTickets()` method, which receives the response, makes a JSON representation of the response object and gets the link `goTickets` to get the next list of tickets.
* There is no need to write a test for the resource body. * No need to write tests for the resource body, just check the status code, since it is guaranteed that the JsonSchema validation of the response has passed.
* Following the uniform interface of REST, the next request URL to be accessed is always included in the response. Inspect them one after another.

> **Uniform Interface**
>
> REST is defined by four interface constraints: identification of resources; manipulation of resources through representations; self-descriptive messages; and, hypermedia as the engine of application state.[^11]

Let's run it.

```bash
./vendor/bin/phpunit --testsuite hypermedia
```

Hypermedia API tests (REST application tests) are a good representation of the fact that REST applications are state machines, and workflows can be described as use cases.
Ideally, REST API tests should cover how the application will be used.

### HTTP Testing

To test the REST API over HTTP, inherit the whole test and set the client to the HTTP test client with `setUp`.

```php
class WorkflowTest extends Workflow
{
    protected function setUp(): void
    {
        $this->resource = new HttpResource('127.0.0.1:8080', __DIR__ . '/index.php', __DIR__ . '/log/workflow.log');
    }
}
```

This client has the same interface as the resource client, but the actual request is made as an HTTP request to the built-in server and receives the response from the server.
The first argument is the URL of the built-in server. When `new` is executed, the built-in server will be started with the bootstrap script specified in the second argument.

The bootstrap script for the test server will also be changed to the API context.

`tests/Http/index.php`

```diff
-exit((new Bootstrap())('hal-app', $GLOBALS, $_SERVER));
+exit((new Bootstrap())('hal-api-app', $GLOBALS, $_SERVER));
```

Let's run it.

```
./vendor/bin/phpunit --testsuite http
```

#### HTTP Access Log

The actual HTTP request/response log made by curl will be recorded in the resource log of the third argument.

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


The actual recorded JSON is useful for checking, especially if it has a complex structure, and is also good to check along with the API documentation.
The HTTP client can also be used for E2E testing.

## API documentation

In ResourceObjects, method signatures are the input parameters to the API, and responses are schema-defined.
Because of its self-descriptiveness, API documentation can be generated automatically.

Let's create it. The documentation will be output to the [docs](https://bearsunday.github.io/tutorial2/) folder.

```
composer doc
```

It reduces the effort of writing IDL (Interface Definition Language), but more valuable is that the documentation follows the latest PHP code and is always accurate.
It is a good idea to include it in your CI so that your code and API documentation are always in sync.

You can also link to related documentation. See [ApiDoc](apidoc.html) for more details on configuration.

## Code examples

The following code example is also available.

* TestModulethat adds a `Test` context and clears the DB for each test.  [4e9704d](https://github.com/bearsunday/tutorial2/commit/4e9704d3bc65b9c7e7a8c13164dfe7cc3d6929b2)
* `entity` option for `#[DbQuery]` that returns a hydrated entity class instead of an associative array in DB queries [29f0a1f](https://github.com/bearsunday/tutorial2/commit/29f0a1f4d4bf51e6c0a722fd6b2f44cb78de999e)
* Query builder synthesizing static and dynamic SQL [9d095ac](https://github.com/bearsunday/tutorial2/commit/9d095acfed6150fb99f36d502ae13f03bdf2916d)

## REST framework

There are three styles of Web APIs.

* Tunnels (SOAP, GraphQL)
* URI (Object, CRUD)
* Hypermedia (REST)

In contrast to the URI style, where resources are treated as just RPCs [^9], what we learned in this tutorial is REST, where resources are linked. [^10]
Resources are connected by LOs (outbound links) in `#Link` to represent workflows, and LEs (embedded links) in `#[Embed]` to represent tree structures.

BEAR.Sunday emphasizes clean, standards-based code.

JsonSchema over framework-specific validators, standard SQL over proprietary ORM, IANA registered standard[^12] media type JSON over proprietary structure JSON.

Application design is not about "free implementation", but about "free choice of constraints".
Applications should aim for evolvability without breaking development efficiency, performance, and backward compatibility based on the constraints.

(This manual has been prepared through deepL automated translation.)

----

[^1]:.env should not be git committed.
[^2]:For example, if it is an e-commerce site, the test will represent the transition of each application state, such as product list, add to cart, order, payment, etc.
[^3]:[PHPStorm Database Tools and SQL](https://pleiades.io/help/phpstorm/relational-databases.html)
[^4]:[Database Diagrams](https://pleiades.io/help/phpstorm/creating-diagrams.html), etc. to check the query plan and execution plan to improve the quality of the SQL you create.
[^5]: Ray.MediaQuery also supports HTTP API requests.
[^6]: MediaQuery also supports HTTP API requests. This hierarchical structure of content is called **Taxonomy** in IA (Information Architecture). See [Understanding Information Architecture](https://understandinggroup.com/ia-theory/understanding-information-architecture).
[^7]: Ray.MediaQuery [README](https://github.com/ray-di/Ray.MediaQuery/blob/1.x/README.ja.md#%E6%97%A5%E4%BB%98%E6%99%82%E5%88%BB)
[^8]: MediaQuery [README]() Here we run it directly from mysql as an example, but you should also learn how to enter seed in the migration tool and use the IDE's DB tools.
[^9]: The so-called "Restish API"; many APIs introduced as REST APIs have this URI/object style, and REST is misused.
[^10]: If you remove the links from the tutorial, you get the URI style.
[^11]: It is a widespread misconception that the Uniform Interface is not an HTTP method. See [Uniform Interface](https://www.ics.uci.edu/~fielding/pubs/dissertation/rest_arch_style.htm).
[^12]: [https://www.iana.org/assignments/media-types/media-types.xhtml](https://www.iana.org/assignments/media-types/media-types.xhtml)
[^13]: This SQL conforms to the [SQL Style Guide](https://www.sqlstyle.guide/). It can be configured from PhpStorm as [Joe Celko](https://twitter.com/koriym/status/1410996122412150786).
The comment is not only descriptive, but also makes it easier to identify the SQL in the slow query log, etc.
