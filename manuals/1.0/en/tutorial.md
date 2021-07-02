---
layout: docs-en
title: Tutorial
category: Manual
permalink: /manuals/1.0/en/tutorial.html
---
# Tutorial

This tutorial introduces the basic features of BEAR.Sunday that use resources, DI, AOP, REST API etc.
Each section of the source code of this project is committed at [bearsunday/Tutorial](https://github.com/bearsunday/Tutorial/commits/v2).


## Get started

Let's make a web service that returns the weekday for a given year-month-day.

First, create a new project with [composer](https://getcomposer.org/).

```bash
composer create-project bear/skeleton MyVendor.Weekday
```

Add the first application resource file at `src/Resource/App/Weekday.php`

## Resource

First, create an application resource file in `src/Resource/App/Weekday.php`.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use DateTimeImmutable;

class Weekday extends ResourceObject
{
    public function onGet(int $year, int $month, int $day): static
    {
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");
        $weekday = $dateTime->format('D');
        $this->body = ['weekday' => $weekday];

        return $this;
    }
}
```

This `MyVendor\Weekday\Resource\App\Weekday` resource class is mapped to the `/weekday` path (by default, Bear.Sunday automatically creates route based on the filename - this point will be explained later).
The request query is automatically converted to PHP method parameters (internally, Bear.Sunday introspect the method parameters).

Let's try to access it in the console. Let's try the error first.

```bash
php bin/app.php get /weekday
```

```
400 Bad Request
content-type: application/vnd.error+json

{
    "message": "Bad Request",
    "logref": "e29567cd",
```

Errors are returned with the [application/vnd.error+json](https://github.com/blongden/vnd.error) media type.
`400` is the error code for a problem with the request. Errors are marked with a `logref` ID and can be found in `var/log/` for a detailed description of the error.

Next, we will try a correct request with an argument.

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
Date: Tue, 04 May 2021 01:55:59 GMT
Connection: close
X-Powered-By: PHP/8.0.3
Content-Type: application/hal+json

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

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceInterface;
use MyVendor\Weekday\Injector;
use PHPUnit\Framework\TestCase;

class WeekdayTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $injector = Injector::getInstance('app');
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testOnGet(): void
    {
        $ro = $this->resource->get('app://self/weekday', ['year' => '2001', 'month' => '1', 'day' => '1']);
        $this->assertSame(200, $ro->code);
        $this->assertSame('Mon', $ro->body['weekday']);
    }
}
```

In `setUp()`, an application injector that can be created by any object of the application given a context (app).
The `Injector` is used to get the resource client (`ResourceInterface`), and the test method `testOnGet` is used to request and test the resource.

Let's run it.

```
./vendor/bin/phpunit
```
```
PHPUnit 9.5.4 by Sebastian Bergmann and contributors.

....                                                                4 / 4 (100%)

Time: 00:00.281, Memory: 14.00 MB
```

There are other commands to perform test and code checking.
To get test coverage, run `composer coverage`.

```
composer coverage
```

[pcov](https://pecl.php.net/package/pcov) provides a faster coverage measurement.

```
composer pcov
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
## Static Analysis

Static analysis of the code is done with the `composer sa` command.

```
composer sa
```

When I ran the code so far, the following error was detected by phpstan.

```
 ------ --------------------------------------------------------- 
  Line   src/Resource/App/Weekday.php                             
 ------ --------------------------------------------------------- 
  15     Cannot call method format() on DateTimeImmutable|false.  
 ------ --------------------------------------------------------- 
```

The previous code did not take into account the fact that `DateTimeImmutable::createFromFormat` will return false if an invalid value (such as -1 for the year) is passed.

Let's try it.

```
php bin/app.php get '/weekday?year=-1&month=1&day=1'
```

PHP errors are still caught by the error handler and error messages are displayed with the correct `application/vnd.error+json` media type, but
To pass the static parsing check, you can either `assert` the result of `DateTimeImmutable` or add code to check the type and throw an exception.
### assert

```php
$dateTime =DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");
assert($dateTime instanceof DateTimeImmutable);
```

### Exception

First, create a dedicated exception `src/Exception/InvalidDateTimeException.php`.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Exception;

use RuntimeException;

class InvalidDateTimeException extends RuntimeException
{
}
```

Modify the code to inspect the value.

```diff
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use DateTimeImmutable;
+use MyVendor\Weekday\Exception\InvalidDateTimeException;

class Weekday extends ResourceObject
{
    public function onGet(int $year, int $month, int $day): static
    {
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");
+        if (! $dateTime instanceof DateTimeImmutable) {
+            throw new InvalidDateTimeException("$year-$month-$day");
+        }

        $weekday = $dateTime->format('D');
        $this->body = ['weekday' => $weekday];

        return $this;
    }
}
```

We'll also add a unit test.

```diff
+    public function tesInvalidDateTime(): void
+    {
+        $this->expectException(InvalidDateTimeException::class);
+        $this->resource->get('app://self/weekday', ['year' => '-1', 'month' => '1', 'day' => '1']);
+    }
```

#### Best Practices for Exception Creation
> There is nothing wrong with the code itself, since the exception was caused by a mistake in the input. Such an exception that turns up at runtime is a `RuntimeException`. We have extended it to create a dedicated exception.
On the other hand, if the exception is caused by a bug and you need to fix the code, you can extend `LogicException` to create an exception. Instead of using the message of the exception to describe the type, create a dedicated exception for each.


#### Defensive programming

> This fix eliminates the possibility of false values in `$dateTime` when executing `$dateTime->format('D');`. This kind of programming that avoids problems before they occur is called defensive programming, and static analysis is useful for checking it.

#### Testing before committing

`composer tests` performs coding convention (cs) and static analysis (sa) tests in addition to `composer test`.

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

Next, install the `AuraRouterModule` in `src/Module/AppModule.php`

```diff
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Module;

use BEAR\Dotenv\Dotenv;
use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;
+use BEAR\Package\Provide\Router\AuraRouterModule;
use function dirname;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        (new Dotenv())->load(dirname(__DIR__, 2));
+        $appDir = $this->appMeta->appDir;
+        $this->install(new AuraRouterModule($appDir . '/var/conf/aura.route.php'));
        $this->install(new PackageModule());
    }
}

```

This module looks for a router script file at `var/conf/aura.route.php`.

```php
<?php
/** 
 * @see http://bearsunday.github.io/manuals/1.0/ja/router.html
 * @var \Aura\Router\Map $map 
 */

$map->route('/weekday', '/weekday/{year}/{month}/{day}');
```

Let's try it out.

```bash
php bin/app.php get /weekday/1981/09/08
```

```bash
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

## DI

To demonstrate the power of DI, let's log a result !

First create `src/MyLoggerInterface.php` which logs the days of the week.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday;

interface MyLoggerInterface
{
    public function log(string $message): void;
}
```

Change the resource to use this logger.

```diff
<?php
namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use MyVendor\Weekday\MyLoggerInterface;

class Weekday extends ResourceObject
{
+    public function __construct(public MyLoggerInterface $logger)
+    {
+    }

    public function onGet(int $year, int $month, int $day): static
    {
        $weekday = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day")->format('D');
        $this->body = [
            'weekday' => $weekday
        ];
+        $this->logger->log("$year-$month-$day {$weekday}");

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

declare(strict_types=1);

namespace MyVendor\Weekday;

use BEAR\AppMeta\AbstractAppMeta;

use function error_log;

use const PHP_EOL;

class MyLogger implements MyLoggerInterface
{
    private string $logFile;

    public function __construct(AbstractAppMeta $meta)
    {
        $this->logFile = $meta->logDir . '/weekday.log';
    }

    public function log(string $message): void
    {
        error_log($message . PHP_EOL, 3, $this->logFile);
    }
}
```

In order to implement `MyLogger` you need the application's log directory information (`AbstractAppMeta`), but this is also accepted as `dependency` in the constructor.
In other words, the `Weekday` resource depends on` MyLogger`, but `MyLogger` also depends on the log directory information. Objects built with DI in this way are dependencies depend on .. and dependency assignments are made.

It is the DI tool (dependency injector) that makes this dependency solution.

Edit the `configure` method of` src/Module/AppModule.php` to bind `MyLoggerInterface` and` MyLogger` with the DI tool.

```diff
class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        (new Dotenv())->load(dirname(__DIR__, 2));
        $appDir = $this->appMeta->appDir;
        $this->install(new AuraRouterModule($appDir . '/var/conf/aura.route.php'));
+        $this->bind(MyLoggerInterface::class)->to(MyLogger::class);
        $this->install(new PackageModule());
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
// method invokation
$time = microtime(true) - $start;
```

Changing code to benchmark each different method can be tedious.
For such problems [Aspect Oriented Programming](https://github.com/google/guice/wiki/AOP) works great. Using this concept you can compose a clean separation of a `cross cutting concern` and `core concern`.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Interceptor;

use MyVendor\Weekday\MyLoggerInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

use function microtime;
use function sprintf;

class BenchMarker implements MethodInterceptor
{
    public function __construct(private MyLoggerInterface $logger)
    {
    }

    public function invoke(MethodInvocation $invocation): mixed
    {
        $start = microtime(true);
        $result = $invocation->proceed(); // 元のメソッドの実行
        $time = microtime(true) - $start;
        $message = sprintf('%s: %0.5f(µs)', $invocation->getMethod()->getName(), $time);
        $this->logger->log($message);

        return $result;
    }
}
```

ou can invoke the original method with `$invocation->proceed();` inside an `invoke` method.
You can then reset and stop the timer on before and after this is invoked. The target method object and method name is taken in the form of a [MethodInvocation](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php) object sent to the invoke method.

Next, create an [attribute](https://www.php.net/manual/en/language.attributes.overview.php) in `src/Annotation/BenchMark.php ` to mark the methods you want to benchmark. to create it.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class BenchMark
{
}
```

In `AppModule`, bind the methods that apply the interceptor using **Matcher**.

```diff
+use MyVendor\Weekday\Annotation\BenchMark;
+use MyVendor\Weekday\Interceptor\BenchMarker;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        (new Dotenv())->load(dirname(__DIR__, 2));
        $appDir = $this->appMeta->appDir;
        $this->install(new AuraRouterModule($appDir . '/var/conf/aura.route.php'));
        $this->bind(MyLoggerInterface::class)->to(MyLogger::class);
+        $this->bindInterceptor(
+            $this->matcher->any(),                           // In any class,
+            $this->matcher->annotatedWith(BenchMark::class), // To #[BenchMark] attributed method
+            [BenchMarker::class]                             // Apply BenchMarker interceptor interception
+        );
        $this->install(new PackageModule());
    }
}
```

Give the method you want to benchmark an attribute of `#[BenchMark]`.

```diff
+use MyVendor\Weekday\Annotation\BenchMark;

class Weekday extends ResourceObject
{

+   #[BenchMark]
    public function onGet(int $year, int $month, int $day): static
    {
```

Now, you can benchmark any method you want by adding the attribute `#[BenchMark]` to it.

Adding functionality through attributes and interceptors is flexible. There is no change to the target method or the caller of the method.
Annotations can be left as is or unbound to avoid benchmarking. For example, you can bind them only during development and warn the user if the number of seconds exceeds a certain value.

Run it and make sure that the log of execution time is output to `var/log/weekday.log`.

```bash
php bin/app.php get '/weekday/2015/05/28'
```

```bash
cat var/log/cli-hal-api-app/weekday.log
```

## HTML

While modern applications will likely be API-first, you can turn this API application into an HTML application. Go ahead and create a new `page` resource at `src/Resource/Page/Index.php`. Even though `page` resource and `app` resource are effectively the same class, their role and location differs.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\Page;

use BEAR\Resource\ResourceObject;
use MyVendor\Weekday\Resource\App\Weekday;

class Index extends ResourceObject
{
    public function __construct(private Weekday $weekday)
    {
    }

    public function onGet(int $year, int $month, int $day): static
    {
        $weekday = $this->weekday->onGet($year, $month, $day);
        $this->body = [
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'weekday' => $weekday->body['weekday']
        ];

        return $this;
    }
}
```

The `page` resource class is essentially the same class as the `app` resource, except for its location and role.

In a typical scenario, the `page` is a publicly available HTML page, and the `app` is a private resource when used with the `page`, close to the infrastructure layer such as a DB.
Which one is made public is determined by the runtime context; in the MVC analogy, the app resource plays the role of the model and the page resource plays the role of the controller.
The app resource is the model and the page resource is the controller.

At this stage let's check how this resource is rendered.

```bash
php bin/page.php get '/?year=2000&month=1&day=1'
```

```
200 OK
Content-Type: application/hal+json

{
    "year": 2000,
    "month": 1,
    "day": 1,
    "weekday": "Sat",
    "_links": {
        "self": {
            "href": "/index?year=2000&month=1&day=1"
        }
    }
}

```

The resource is output as `application/hal+json` media type, but to output it as HTML (text/html), install the HTML module. See [Manual for HTML](/manuals/1.0/en/html.html).

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
use Ray\Di\AbstractModule;

class HtmlModule extends AbstractModule
{
    protected function configure(): void
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

`bin/page.php`を変更してコンテキストを`html-app`にします。

```php
<?php
use MyVendor\Weekday\Bootstrap;

require dirname(__DIR__) . '/autoload.php';
exit((new Bootstrap())(PHP_SAPI === 'cli' ? 'cli-html-app' : 'html-app', $GLOBALS, $_SERVER));
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

```html
200 OK
Content-Type: text/html; charset=utf-8

<!DOCTYPE html>
<html>
...
```

In order to run the web service, we need to make a change to `public/index.php`.

```php
<?php

use MyVendor\Weekday\Bootstrap;

require dirname(__DIR__) . '/autoload.php';
exit((new Bootstrap())(PHP_SAPI === 'cli-server' ? 'html-app' : 'prod-html-app', $GLOBALS, $_SERVER));
```

Boot up the PHP web server and check it out by accessing [http://127.0.0.1:8080/?year=2001&month=1&day=1](http://127.0.0.1:8080/?year=2001&month=1&day=1).

```bash
php -S 127.0.0.1:8080 public/index.php
```

As the [context](/manuals/1.0/en/application.html#context) changes, so does the behaviour of the application. Let's try it.

```php?start_inline
<?php
// JSONアプリケーション （最小）
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')('app'));
```

```php?start_inline
<?php
// プロダクション用HALアプリケーション
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')('prod-hal-app'));
```

For each context PHP code that builds up the application is produced and saved in `var/tmp/`. These files are not normally needed, but you can use it to check how your application object is created. Using the `diff` command you can check which dependencies have changed across contexts.

## REST API

Let's make an application resource that uses SQLite3.
First, using the console, create a database `var/db/todo.sqlite3`.

```bash
mkdir var/db
sqlite3 var/db/todo.sqlite3

sqlite> create table todo(id integer primary key, todo, created_at);
sqlite> .exit
```

For database access you can choose from [AuraSql](https://github.com/ray-di/Ray.AuraSqlModule), [Doctrine Dbal](https://github.com/ray-di/Ray.DbalModule), [ CakeDB](https://github.com/ray-di/Ray.CakeDbModule).
AuraSqlModule. Let's install it here.

```bash
composer require ray/aura-sql-module
```

Install the module with `src/Module/AppModule::configure()`.

```diff
<?php
+use Ray\AuraSqlModule\AuraSqlModule;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        // ...
+        $this->install(new AuraSqlModule(sprintf('sqlite:%s/var/db/todo.sqlite3', $this->appMeta->appDir)));
        $this->install(new PackageModule());
    }
}
```

Build up the `src/Resource/App/Todos.php` resource.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\App;

use Aura\Sql\ExtendedPdoInterface;
use BEAR\Package\Annotation\ReturnCreatedResource;
use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\ResourceObject;
use DateTimeImmutable;
use Ray\AuraSqlModule\Annotation\Transactional;

use function sprintf;

#[Cacheable]
class Todos extends ResourceObject
{
    public function __construct(private ExtendedPdoInterface $pdo, private DateTimeImmutable $date)
    {
    }

    public function onGet(string $id = ''): static
    {
        $sql = $id ? /** @lang SQL */'SELECT * FROM todo WHERE id=:id' : /** @lang SQL */'SELECT * FROM todo';
        $this->body = $this->pdo->fetchAssoc($sql, ['id' => $id]);

        return $this;
    }

    #[Transactional, ReturnCreatedResource]
    public function onPost(string $todo): static
    {
        $this->pdo->perform(/** @lang SQL */'INSERT INTO todo (todo, created_at) VALUES (:todo, :created_at)', [
            'todo' => $todo,
            'created_at' => $this->date->format('Y-m-d H:i:s')
        ]);
        $this->code = 201; // Created
        $this->headers['Location'] = sprintf('/todos?id=%s', $this->pdo->lastInsertId()); // new URL

        return $this;
    }

    #[Transactional]
    public function onPut(int $id, string $todo): static
    {
        $this->pdo->perform(/** @lang SQL */'UPDATE todo SET todo = :todo WHERE id = :id', [
            'id' => $id,
            'todo' => $todo
        ]);
        $this->code = 204; // No content

        return $this;
    }
}
```
See the attributes. The class attribute `#[Cacheable]` indicates that the GET method of this resource is cacheable.
The `#[Transactional]` of `onPost` or `onPut` indicates a transaction of database access.


Creates an `onPost` `#[ReturnCreatedResource]` and returns the resource whose URL is given in the `Location`, including the body.
At this time, `onGet` is actually called with the URI in the `Location` header, so the content of the `Location` header is guaranteed to be correct, and calling `onGet` will also create a cache.


Let's try a `POST`.

In order to enable caching , create the context of `bin/app.php` `test` for caching.

```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')('prod-cli-hal-api-app'));
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
Next we will do a `GET`.

```bash
php bin/test.php get '/todos?id=1'
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

The HTTP API is now complete. Let's start up the API server.

```bash
php -S 127.0.0.1:8081 bin/app.php
```

Let's do a GET `curl` request:

```bash
curl -i 'http://127.0.0.1:8081/todos?id=1'
```

```bash
HTTP/1.1 200 OK
Host: 127.0.0.1:8081
Date: Sun, 02 May 2021 17:10:55 GMT
Connection: close
X-Powered-By: PHP/8.0.3
Content-Type: application/hal+json
ETag: 197839553
Last-Modified: Sun, 02 May 2021 17:10:55 GMT
Cache-Control: max-age=31536000

{
    "id": "1",
```

If you run the request several times, you will notice that the `Last-Modified` timestamp does not change. This is because the class is annotated with `#[Cacheable]`.

On the `#[Cacheable]` attribute, if no `expiry` is set then it will be cached forever. However when updates `onPut($id, $todo)` or deletes `onDelete($id)` occur on the resource, the cached resource will automatically be flushed and refreshed for the given ID.
Next we update the resource with a `PUT`.

```bash
curl -i http://127.0.0.1:8081/todos -X PUT -d "id=1&todo=think"
```
You will get a response of `204 No Content` indicating that there is no body.

```
HTTP/1.1 204 No Content
...
```

If you would rather send a JSON body with the PUT request you can run the following.

```bash
curl -i http://127.0.0.1:8081/todos -X PUT -H 'Content-Type: application/json' -d '{"id": 1, "todo":"think" }'
```

This time, when you perform a `GET` you can see that the `Last-Modified` has been updated.

```bash
curl -i 'http://127.0.0.1:8081/todos?id=1'
```

This `Last-Modified` time stamp has been provided by `#[Cacheable]`. No need to provide any special application admin or database columns.

With `#[Cacheable]`, resource contents are managed in a "query repository" dedicated for storing resources, which is different from the database for writing, and `Etag` and `Last-Modified` headers are added automatically.

## Because Everything is A Resource.

Uniform resource identifier(URI), a consistent interface, stateless access, powerful caching system, hyperlinks, layered system, and self-descriptive messages. A resource built with BEAR.Sunday implements all of these REST features.

You can connect to data from other applications using hyperlinks, creating an API to be consumed from another CMS or framework is easy. The resource object is completely decoupled from any rendering.


BEAR.Sunday is a **connecting layer framework** that connects dependencies with **DI**, cross-cutting interests with **AOP**, and application information as resources with the power of **REST**.
