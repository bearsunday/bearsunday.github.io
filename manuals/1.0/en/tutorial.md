---
layout: docs-en
title: Tutorial
category: Manual
permalink: /manuals/1.0/en/tutorial.html
---

# Tutorial

This tutorial introduces the basic features of BEAR.Sunday that use resources, DI, AOP, REST API etc.
Each section of the source code of this project is committed at [bearsunday/Tutorial](https://github.com/bearsunday/Tutorial/commits/master).

## Get started

Let's make a web service that returns the weekday for a given year-month-day.

First, create a new project with [composer](https://getcomposer.org/).

```bash
composer create-project bear/skeleton MyVendor.Weekday
```
This will prompt you to choose vendor name and project name. Type `MyVendor` and `Weekday` here. [^1]

## Resource

Add the first application resource file at `src/Resource/App/Weekday.php`

```php
<?php
namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;

class Weekday extends ResourceObject
{
    public function onGet(int $year, int $month, int $day) : ResourceObject
    {
        $weekday = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day")->format('D');
        $this->body = [
            'weekday' => $weekday
        ];

        return $this;
    }
}
```

This `MyVendor\Weekday\Resource\App\Weekday` resource class is mapped to the `/weekday` path (by default, Bear.Sunday automatically creates route based on the filename - this point will be explained later).
The request query is automatically converted to PHP method parameters (internally, Bear.Sunday introspect the method parameters).

The route can be accessed via the console by typing this command:

```bash
php bin/app.php get '/weekday'
```

The following error should display:

```
400 Bad Request
content-type: application/vnd.error+json

{
    "message": "Bad Request",
    "logref": "e29567cd",
```

A `400` means that you have sent a bad request (in this example, required parameters are missing).

Send a new request with the expected parameters by typing the following command:

```bash
php bin/app.php get '/weekday?year=2001&month=1&day=1'
```

```bash
200 OK
Content-Type: application/hal+json

{
    "weekday": "Mon",
    "_links": {
        "self": {
            "href": "/weekday?year=2001&month=1&day=1"
        }
    }
}
```

The result is returned successfully with the `application/hal+json` media type.

The previous example can be executed as a webservice as well. To do this, fire the built-in PHP server:

```bash
php -S 127.0.0.1:8080 bin/app.php
```

Send a HTTP `GET` request with `curl` (or type the URL in your browser):

```
curl -i 'http://127.0.0.1:8080/weekday?year=2001&month=1&day=1'
```
```
HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Date: Sun, 04 Jun 2017 19:48:09 +0200
Connection: close
X-Powered-By: PHP/7.1.4
content-type: application/hal+json

{
    "weekday": "Mon",
    "_links": {
        "self": {
            "href": "/weekday/2001/1/1"
        }
    }
}
```

This resource class only has a GET method, therefore `405 Method Not Allowed` will be returned with any other HTTP method. Try it out!

```
curl -i -X POST 'http://127.0.0.1:8080/weekday?year=2001&month=1&day=1'
```


```
HTTP/1.1 405 Method Not Allowed
...
```


You can use the OPTIONS method to retrieve the supported HTTP methods and the required parameters in the request. ([RFC7231](https://tools.ietf.org/html/rfc7231#section-4.3.7))
```
curl -i -X OPTIONS http://127.0.0.1:8080/weekday
```

```
HTTP/1.1 200 OK
...
Content-Type: application/json
Allow: GET

{
    "GET": {
        "parameters": {
            "year": {
                "type": "integer"
            },
            "month": {
                "type": "integer"
            },
            "day": {
                "type": "integer"
            }
        },
        "required": [
            "year",
            "month",
            "day"
        ]
    }
}
```

## Test

Let's create a resource test using [PHPUnit](https://phpunit.readthedocs.io/ja/latest/).

Create test file at `tests/Resource/App/WeekdayTest.php`.

```php
<?php
namespace MyVendor\Weekday\Resource\App;

use BEAR\Package\AppInjector;
use BEAR\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;

class WeekdayTest extends TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    protected function setUp() : void
    {
        $this->resource = (new AppInjector('MyVendor\Weekday', 'app'))->getInstance(ResourceInterface::class);
    }

    public function testOnGet()
    {
        $ro = $this->resource->get('app://self/weekday', ['year' => '2001', 'month' => '1', 'day' => '1']);
        $this->assertSame(200, $ro->code);
        $this->assertSame('Mon', $ro->body['weekday']);
    }
}
```

Any object of the application can be instanciated by `AppInjector` with given application name (MyVendor\Weekday) and the context (app).
Use it to request testing resource in the test method.

Let's run it.

```
./vendor/bin/phpunit
```
```
PHPUnit 7.1.5 by Sebastian Bergmann and contributors.

..                                                                  2 / 2 (100%)

Time: 159 ms, Memory: 10.00MB
```

There are other commands to perform test and code checking.
To get test coverage, run `composer coverage`.

```
composer coverage
```

You can see the details of the coverage by opening `build/coverage/index.html` with a web browser.

You can inspect whether you are following coding standard with `composer cs` command.
Fix it with `composer cs-fix` command.

```
composer cs
```
```
composer cs-fix
```

`composer tests` will also check [phpmd](https://phpmd.org/) and [phpstan](https://github.com/phpstan/phpstan) in addition to` phpunit`, `phpcs`. It's better to do it before committing.

```
composer tests
```

## Routing

A default router is set to `WebRouter` which simply maps URL's to the resource class directory.
To receive a dynamic parameter in URI path, we can use `AuraRouter`. This can be done with an override install of the `AuraRouterModule` in `src/Module/AppModule.php`.
Get it with [composer](http://getcomposer.org) first.

```bash
composer require bear/aura-router-module ^2.0
```

```php
<?php
namespace MyVendor\Weekday\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;
use BEAR\Package\Provide\Router\AuraRouterModule; // add this line

class AppModule extends AbstractAppModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $appDir = $this->appMeta->appDir;
        require_once $appDir . '/env.php';
        $this->install(new AuraRouterModule($appDir . '/var/conf/aura.route.php')); // add this line
        $this->install(new PackageModule);
    }
}
```

This module looks for a router script file at `var/conf/aura.route.php`.

```php
<?php
/* @var \Aura\Router\Map $map */

$map->route('/weekday', '/weekday/{year}/{month}/{day}');
```

Let's try it out.

```bash
php bin/app.php get /weekday/1981/09/08
```
```
200 OK
Content-Type: application/hal+json

{
    "weekday": "Tue",
    "_links": {
        "self": {
            "href": "/weekday/1981/09/08"
        }
    }
}
```

Congratulations! You’ve just developed a hypermedia-driven RESTful web service with BEAR.Sunday.

## DI

To demonstrate the power of DI, let's log a result !

First create `src/MyLoggerInterface.php` which logs the days of the week.

```php
<?php
namespace MyVendor\Weekday;

interface MyLoggerInterface
{
    public function log(string $message) : void;
}
```

Change the resource to use this logger.

```php
<?php
namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use MyVendor\Weekday\MyLoggerInterface;

class Weekday extends ResourceObject
{
    /**
     * @var MyLoggerInterface
     */
    private $logger;

    public function __construct(MyLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onGet(int $year, int $month, int $day) : ResourceObject
    {
        $weekday = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day")->format('D');
        $this->body = [
            'weekday' => $weekday
        ];
        $this->logger->log("$year-$month-$day {$weekday}");

        return $this;
    }
}
```

A naive approach is to instantiate a logger object with the new operator whenever you need it.
However this approach is strongly discouraged (and make testing much harder). Instead, your objects should receive a created instance as a constructor dependency.
This is called the [DI pattern](https://en.wikipedia.org/wiki/Dependency_injection).


Next we will implement `MyLoggerInterface` in` MyLogger`.

```php
<?php
namespace MyVendor\Weekday;

use BEAR\AppMeta\AbstractAppMeta;

class MyLogger implements MyLoggerInterface
{
    private $logFile;

    public function __construct(AbstractAppMeta $meta)
    {
        $this->logFile = $meta->logDir . '/weekday.log';
    }

    public function log(string $message) : void
    {
        error_log($message . PHP_EOL, 3, $this->logFile);
    }
}
```

In order to implement `MyLogger` you need the application's log directory information (`AbstractAppMeta`), but this is also accepted as `dependency` in the constructor.
In other words, the `Weekday` resource depends on` MyLogger`, but `MyLogger` also depends on the log directory information. Objects built with DI in this way are dependencies depend on .. and dependency assignments are made.

It is the DI tool (dependency injector) that makes this dependency solution.

Edit the `configure` method of` src/Module/AppModule.php` to bind `MyLoggerInterface` and` MyLogger` with the DI tool.

```php
<?php
namespace MyVendor\Weekday\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;
use BEAR\Package\Provide\Router\AuraRouterModule;
use MyVendor\Weekday\MyLogger; // add this line
use MyVendor\Weekday\MyLoggerInterface;  // add this line

class AppModule extends AbstractAppModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $appDir = $this->appMeta->appDir;
        require_once $appDir . '/env.php';
        $this->install(new AuraRouterModule($appDir . '/var/conf/aura.route.php'));
        $this->bind(MyLoggerInterface::class)->to(MyLogger::class); // add this line
        $this->install(new PackageModule);
    }
}
```

Now all classes can now accept loggers with `MyLoggerInterface` in the constructor.
Let's make sure that the result is output to `var/log/cli-hal-api-app/weekday.log`.

```bash
php bin/app.php get /weekday/2011/05/23
```

```bash
cat var/log/cli-hal-api-app/weekday.log
```

## AOP

We can benchmarking method invocation like is often done like this.

```php?start_inline
$start = microtime(true);
// Method invocation
$time = microtime(true) - $start;
```

Changing code to benchmark each different method can be tedious.
For such problems [Aspect Oriented Programming](https://github.com/google/guice/wiki/AOP) works great. Using this concept you can compose a clean separation of a `cross cutting concern` and `core concern`.

First, make a **interceptor** which intercepts the target method for benchmarking which we will save in `src/Interceptor/BenchMarker.php`.

```php
<?php
namespace MyVendor\Weekday\Interceptor;

use Psr\Log\LoggerInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class BenchMarker implements MethodInterceptor
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function invoke(MethodInvocation $invocation)
    {
        $start = microtime(true);
        $result = $invocation->proceed(); // invoke original method
        $time = microtime(true) - $start;
        $msg = sprintf("%s: %s", $invocation->getMethod()->getName(), $time);
        $this->logger->info($msg);

        return $result;
    }
}
```

You can invoke the original method with `$invocation->proceed();` inside an `invoke` method.
You can then reset and stop the timer on before and after this is invoked. The target method object and method name is taken in the form of a [MethodInvocation](http://www.bear-project.net/Ray.Aop/build/apigen/class-Ray.Aop.MethodInvocation.html) object sent to the invoke method.

Next, provide an [annotation](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html) class at `src/Annotation/BenchMark.php`.

```php
<?php
namespace MyVendor\Weekday\Annotation;

/**
 * @Annotation
 */
final class BenchMark
{
}
```

We then need to bind the target method to the benchmarking interceptor in `AppModule` with a matcher.

```php
<?php
// ...
use MyVendor\Weekday\Annotation\BenchMark; // Add this line
use MyVendor\Weekday\Interceptor\BenchMarker; // Add this line

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->bindInterceptor(
            $this->matcher->any(),                           // in any class
            $this->matcher->annotatedWith(BenchMark::class), // which annotated as @BenchMark
            [BenchMarker::class]                             // apply BenchMarker interceptor
        );
    }
}
```

Annotate the target method with `@BenchMark`.

```php?start_inline
use MyVendor\Weekday\Annotation\BenchMark;

/**
 * @BenchMark
 */
public function onGet($year, $month, $day)
{
```

Now, you can benchmark any method that has the `@BenchMark` annotation.

There is no need to modify the method caller or the target method itself. Benchmarking is only invoked with the interceptor binding, so even by leaving the annotation in place you can turn benchmarking on and off by adding and removing the binding from the application.

Now check out the logging for the method invocation speed in `var/log/weekday.log`.

```bash
php bin/app.php get '/weekday/2015/05/28'
```
```
cat var/log/cli-hal-api-app/weekday.log
```

## HTML

While modern applications will likely be API-first, you can turn this API application into an HTML application. Go ahead and create a new `page` resource at `src/Resource/Page/Index.php`. Even though `page` resource and `app` resource are effectively the same class, their role and location differs.

```php
<?php
namespace MyVendor\Weekday\Resource\Page;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    /**
     * @Embed(rel="weekday", src="app://self/weekday{?year,month,day}")
     */
    public function onGet(int $year, int $month, int $day) : ResourceObject
    {
        $this->body += [
            'year' => $year,
            'month' => $month,
            'day' => $day
        ];

        return $this;
    }
}
```

Using the `@Embed` annotation you can refer to the `app://self/weekday` resource in the `weekday` slot.

If parameters are needed to be passed, parameters that have been recieved in a resource method can then be passed by using the [RFC6570 URI template](https://github.com/ioseb/uri-template) standard such as `{?year,month,day}`.

The above page class is the same as the below page class. Here instead of using `@Embed` to include the linked resource resource, through implementing ` use ResourceInject;` a resource client is injected and another resource can be embedded.

Both methods are equally valid, however the `@Embed` declaration is concise and you can see very clearly which resources are embedded in other resources.

```php
<?php
namespace MyVendor\Weekday\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(int $year, int $month, int $day) : ResourceObject
    {
        $params = get_defined_vars(); // ['year' => $year, 'month' => $month, 'day' => $day]
        $this->body = $params + [
            'weekday' => $this->resource->uri('app://self/weekday')($params)
        ];

        return $this;
    }
}
```

At this stage let's check how this resource is rendered.

```bash
php bin/page.php get '/?year=2000&month=1&day=1'
```

```
200 OK
content-type: application/hal+json

{
    "year": 2000,
    "month": 1,
    "day": 1,
    "_embedded": {
        "weekday": {
            "weekday": "Sat"
        }
    },
    "_links": {
        "self": {
            "href": "/index?year=2000&month=1&day=1"
        }
    }
}
```

We can see that the other resource has been included in the `_embedded` node.  Because there is no change to the resource renderer, an `application/hal+json` media type is output. In order to output the HTML(text/html) media, we need to install an HTML Module.

Composer Install

```bash
composer require madapaja/twig-module ^2.0
```

Create `src/Module/HtmlModule.php`.

```php
<?php
namespace MyVendor\Weekday\Module;

use Madapaja\TwigModule\TwigErrorPageModule;
use Madapaja\TwigModule\TwigModule;
use BEAR\Package\AbstractAppModule;

class HtmlModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TwigModule);
        $this->install(new TwigErrorPageModule);
    }
}
```

Copy `templates` directory.

```bash
cp -r vendor/madapaja/twig-module/var/templates var
```


Change `bin/page.php`

```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')(PHP_SAPI === 'cli' ? 'cli-html-app' : 'html-app'));
```

In this way `text/html` media output can be set. Lastly, save your Twig template `var/templates/Page/Index.html.twig`.

```bash
{% raw %}{% extends 'layout/base.html.twig' %}
{% block title %}Weekday{% endblock %}
{% block content %}
The weekday of {{ year }}/{{ month }}/{{ day }} is {{ weekday.weekday }}.
{% endblock %}{% endraw %}
```

Set up is now complete. Check in the console that this kind of HTML is output.

```bash
php bin/page.php get '/?year=1991&month=8&day=1'
```

```bash
200 OK
content-type: text/html; charset=utf-8

<!DOCTYPE html>
<html>
<body>
The weekday of 1991/8/1 is Thu.
</body>
</html>
```

In order to run the web service, we need to make a change to `public/index.php`.

```php
<?php
$context = PHP_SAPI === 'cli-server' ? 'html-app' : 'prod-html-app';
require dirname(__DIR__) . '/bootstrap/bootstrap.php';
```

Boot up the PHP web server and check it out by accessing [http://127.0.0.1:8080/?year=2001&month=1&day=1](http://127.0.0.1:8080/?year=2001&month=1&day=1).

```bash
php -S 127.0.0.1:8080 var/www/index.php
```

As the [context](/manuals/1.0/en/application.html#context) changes, so does the behaviour of the application. Let's try it.

```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')(PHP_SAPI === 'cli-server' ? 'html-app' : 'prod-html-app'));
```

For each context PHP code that builds up the application is produced and saved in `var/tmp/`. These files are not normally needed, but you can use it to check how your application object is created. Using the `diff` command you can check which dependencies have changed across contexts.

```bash
diff -q var/tmp/app/ var/tmp/prod-hal-app/
```

## A Hypermedia API using a Database

Let's make an application resource that uses SQLite3.
First, using the console, create a database `var/db/todo.sqlite3`.

```bash
mkdir var/db
sqlite3 var/db/todo.sqlite3

sqlite> create table todo(id integer primary key, todo, created_at);
sqlite> .exit
```

Various DB systems can be used, such as [AuraSql](https://github.com/ray-di/Ray.AuraSqlModule), [Doctrine Dbal](https://github.com/ray-di/Ray.DbalModule) or [CakeDB](https://github.com/ray-di/Ray.CakeDbModule).
Let's install CakeDB that the Cake PHP framework uses.

```bash
composer require ray/cake-database-module ^1.0
```

In `src/Module/AppModule::configure()` we install the module.

```php
```php
<?php
// ...
use Ray\CakeDbModule\CakeDbModule; // add this line

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $dbConfig = [
            'driver' => 'Cake\Database\Driver\Sqlite',
            'database' => $appDir . '/var/db/todo.sqlite3'
        ];
        $this->install(new CakeDbModule($dbConfig));
    }
}
```

Now if we `use` the setter method trait `DatabaseInject` we have the CakeDB object available to us in `$this->db`.

Build up the `src/Resource/App/Todos.php` resource.

```php
<?php
namespace MyVendor\Weekday\Resource\App;

use BEAR\Package\Annotation\ReturnCreatedResource;
use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\ResourceObject;
use Ray\CakeDbModule\Annotation\Transactional;
use Ray\CakeDbModule\DatabaseInject;

/**
 * @Cacheable
 */
class Todos extends ResourceObject
{
    use DatabaseInject;

    public function onGet(int $id) : ResourceObject
    {
        $this->body = $this
            ->db
            ->newQuery()
            ->select('*')
            ->from('todo')
            ->where(['id' => $id])
            ->execute()
            ->fetch('assoc');

        return $this;
    }

    /**
     * @Transactional
     * @ReturnCreatedResource
     */
    public function onPost(string $todo) : ResourceObject
    {
        $statement = $this->db->insert(
            'todo',
            ['todo' => $todo, 'created_at' => new \DateTime('now')],
            ['created_at' => 'datetime']
        );
        // created
        $this->code = 201;
        // hyperlink
        $id = $statement->lastInsertId();
        $this->headers['Location'] = '/todos?id=' . $id;

        return $this;
    }

    /**
     * @Transactional
     */
    public function onPut(int $id, string $todo) : ResourceObject
    {
        $this->db->update(
            'todo',
            ['todo' => $todo],
            ['id' => $id]
        );
        // no content
        $this->code = 204;

        return $this;
    }
}
```

Some annotations used in this code are worth mentioning:

* `@cacheable`: added at the class level, it indicates that the GET method of this resource can be cached.
`@Transactional` on `onPost` and `onPut` shows database access transactions.
* `@ReturnCreatedResource`: added on the `onPost` method, it indicates that it contains the created resource in body.

At this time, since the `onGet` is actually called with the URI in the `Location` header, we guarantee that the URI of the `Location` header is correct, and at the same time we call `onGet` to create a cache.

Let's try a `POST`.

In order to enable caching , create the context of `bin/app.php` `test` for caching.

```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')('cli-prod-hal-api-app'));
```

Request with console command. `POST`, but for convenience we pass parameters in the form of a query.

```bash
php bin/test.php post '/todos?todo=shopping'
```

```bash
201 Created
Location: /todos?id=1

{
    "id": "1",
    "todo": "shopping",
    "created": "2017-06-04 15:58:03",
    "_links": {
        "self": {
            "href": "/todos?id=1"
        }
    }
}
```

Our response returned a `201` status code, and a new resource `/todo/?id=1` has been created.　[RFC7231 Section-6.3.2](https://tools.ietf.org/html/rfc7231#section-6.3.2)

Since it has been annotated with `@ReturnCreatedResource`, the resource is automatically returned as the body.

Next we will do a `GET`.

```bash
php bin/app.php get '/todos?id=1'
```

```
200 OK
ETag: 2527085682
Last-Modified: Sun, 04 Jun 2017 15:23:39 GMT
content-type: application/hal+json

{
    "id": "1",
    "todo": "shopping",
    "created": "2017-06-04 15:58:03",
    "_links": {
        "self": {
            "href": "/todos?id=1"
        }
    }
}
```

The HyperMedia API is now complete. Let's start up the API server.

```bash
php -S 127.0.0.1:8081 bin/app.php
```

Let's do a GET `curl` request:

```bash
curl -i http://127.0.0.1:8081/todos?id=1
```

```bash
HTTP/1.1 200 OK
Host: 127.0.0.1:8081
Date: Sun, 04 Jun 2017 18:02:55 +0200
Connection: close
X-Powered-By: PHP/7.1.4
ETag: 2527085682
Last-Modified: Sun, 04 Jun 2017 16:02:55 GMT
content-type: application/hal+json

{
    "id": "1",
    "todo": "shopping",
    "created": "2017-06-04 15:58:03",
    "_links": {
        "self": {
            "href": "/todos?id=1"
        }
    }
}
```

If you run the request several times, you will notice that the `Last-Modified` timestamp does not change. This is because the class is annotated with `@Cacheable`.

On the `@Cacheable` annotation, if no `expiry` is set then it will be cached forever. However when updates `onPut($id, $todo)` or deletes `onDelete($id)` occur on the resource, the cached resource will automatically be flushed and refreshed for the given ID.

Next we update the resource with a `PUT`.

```bash
curl -i http://127.0.0.1:8080/todo -X PUT -d "id=2&todo=think"
```

If you would rather send a JSON body with the PUT request you can run the following.

```bash
curl -i http://127.0.0.1:8080/todo -X PUT -H 'Content-Type: application/json' -d '{"id": "2", "todo":"think" }'
```

This time, when you perform a `GET` you can see that the `Last-Modified` has been updated.

```bash
curl -i 'http://127.0.0.1:8080/todos?id=2'
```

This `Last-Modified` time stamp has been provided by `@Cacheable`. No need to provide any special application admin or database columns.

When you use `@Cacheable`, the resource content is also saved in a separate `query repository` where along with the resources changes are managed along with `Etag` or `Last-Modified` headers being automatically appended.


## Because Everything is A Resource

Uniform resource identifier(URI), a consistent interface, stateless access, powerful caching system, hyperlinks, layered system, and self-descriptive messages. A resource built with BEAR.Sunday implements all of these REST features.

You can connect to data from other applications using hyperlinks, creating an API to be consumed from another CMS or framework is easy. The resource object is completely decoupled from any rendering !

---

[^1]:Normally you enter the name of an individual or team (organization) in **vendor**. Github's account name or team name would be appropriate. For **project**, enter the application name.
