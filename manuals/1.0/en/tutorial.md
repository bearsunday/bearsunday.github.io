---
layout: docs-en
title: Tutorial (v3)
category: Manual
permalink: /manuals/1.0/en/tutorial.html
---
# Tutorial

In this tutorial, we introduce the basic features of BEAR.Sunday, including **DI** (Dependency Injection), **AOP** (Aspect-Oriented Programming), and **REST API**. Follow along with the commits from [tutorial1](https://github.com/bearsunday/tutorial1/commits/v3).

## Project Creation

Let's create a web service that returns the day of the week when a date (year, month, day) is entered. Start by creating a project.

```bash
composer create-project bear/skeleton MyVendor.Weekday
```

Enter `MyVendor` for the **vendor** name and `Weekday` for the **project** name. [^2]

> **Note**: For automated setup, you can use:
> ```bash
> expect << 'EOF'
> spawn composer create-project bear/skeleton MyVendor.Weekday
> expect "What is the vendor name ?"
> send "MyVendor\r"
> expect "What is the project name ?"
> send "Weekday\r"
> expect eof
> EOF
> ```
> 
> Or if expect is not available: `printf "MyVendor\nWeekday\n" | composer create-project bear/skeleton MyVendor.Weekday`

## Resources

BEAR.Sunday applications are made up of resources. A **ResourceObject** is an object that represents a web resource itself. It receives HTTP requests and transforms itself into the current state of that resource.

First, create an application resource file at `src/Resource/App/Weekday.php`.

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

This resource class `MyVendor\Weekday\Resource\App\Weekday` can be accessed via the path `/weekday`. The query parameters of the `GET` method are passed to the `onGet` method.

The job of a ResourceObject is to receive requests and determine its own state.

Try accessing it via the console. First, test with an error.

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

Errors are returned in the [application/vnd.error+json](https://github.com/blongden/vnd.error) media type. The 400 error code indicates a problem with the request. Each error is assigned a `logref` ID, and the details of the error can be found in `var/log/`.

Next, try a correct request with parameters.

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

The result is correctly returned in the [application/hal+json](https://tools.ietf.org/html/draft-kelly-json-hal-06) media type. HAL+JSON is a JSON format that uses the `_links` section to link related resources. For more details about HAL+JSON, see [here](https://en.wikipedia.org/wiki/Hypertext_Application_Language).

Let's turn this into a Web API service. Start the built-in server.

```bash
php -S 127.0.0.1:8080 bin/app.php
```

Test it with an HTTP `GET` request using `curl`.

Modify `public/index.php` as shown below:

```diff
<?php

declare(strict_types=1);

use MyVendor\Weekday\Bootstrap;

require dirname(__DIR__) . '/autoload.php';
- exit((new Bootstrap())(PHP_SAPI === 'cli-server' ? 'hal-app' : 'prod-hal-app', $GLOBALS, $_SERVER));
+ exit((new Bootstrap())(PHP_SAPI === 'cli-server' ? 'hal-api-app' : 'prod-hal-api-app', $GLOBALS, $_SERVER));
```

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

This resource class does not have methods other than GET, so trying other methods will return `405 Method Not Allowed`. Let's test this as well.

```
curl -i -X POST 'http://127.0.0.1:8080/weekday?year=2001&month=1&day=1'
```

```
HTTP/1.1 405 Method Not Allowed
...
```

The HTTP `OPTIONS` method request can be used to determine the available HTTP methods and required parameters ([RFC7231](https://tools.ietf.org/html/rfc7231#section-4.3.7)).

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

## Testing

Let's create a test for the resource using [PHPUnit](https://phpunit.readthedocs.io/ja/latest/).

`tests/Resource/App/WeekdayTest.php` with the following test code:

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

The `setUp()` method specifies the context (app) and uses the application's injector `Injector` to obtain a resource client (`ResourceInterface`), and the `testOnGet` method requests the resource for testing.

Let's run it.

```
./vendor/bin/phpunit
```
```
PHPUnit 9.5.4 by Sebastian Bergmann and contributors.

....                                                                4 / 4 (100%)

Time: 00:00.281, Memory: 14.00 MB
```

The installed project also includes commands for running tests and code inspections. To obtain test coverage, run `composer coverage`.

```
composer coverage
```

[pcov](https://pecl.php.net/package/pcov) can measure coverage more quickly.

```
composer pcov
```

You can view the details of the coverage by opening `build/coverage/index.html` in a web browser.

To check if the coding standards are being followed, use the `composer cs` command.
Automatic corrections can be done with the `composer cs-fix` command.

```
composer cs
```
```
composer cs-fix
```

## Static Analysis

Static analysis of the code is performed using the `composer sa` command.

```
composer sa
```

When running the code up to this point, the following error was detected by phpstan.

```
 ------ --------------------------------------------------------- 
  Line   src/Resource/App/Weekday.php                             
 ------ --------------------------------------------------------- 
  15     Cannot call method format() on DateTimeImmutable|false.  
 ------ --------------------------------------------------------- 
```

The earlier code did not consider that `DateTimeImmutable::createFromFormat` might return false when invalid values (such as the year being -1) are passed.

Let's try it.

```
php bin/app.php get '/weekday?year=-1&month=1&day=1'
```

Even if a PHP error occurs, the error handler catches it and displays the error message in the correct `application/vnd.error+json` media type, but to pass static analysis inspection, you need to add code to `assert` the result of `DateTimeImmutable` or check the type and throw an exception.

### Using assert

```php
$dateTime =(new DateTimeImmutable)->createFromFormat('Y-m-d', "$year-$month-$day");
assert($dateTime instanceof DateTimeImmutable);
```

### Throwing an exception

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

Modify the code to check values.

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

Add a test as well.

```diff
+    public function testInvalidDateTime(): void
+    {
+        $this->expectException(InvalidDateTimeException::class);
+        $this->resource->get('app://self/weekday', ['year' => '-1', 'month' => '1', 'day' => '1']);
+    }
```

#### Best Practices for Exception Creation
>
> Since the exception occurred due to an input mistake, there is no problem with the code itself. Such exceptions that become apparent at runtime are `RuntimeExceptions`. We have extended this to create a dedicated exception.
> Conversely, if the occurrence of an exception is due to a bug requiring code correction, you would extend `LogicException` to create the exception. Instead of explaining the type of exception in the message, create dedicated exceptions for each type.


#### Defensive Programming

> This modification eliminates the possibility of `false` being in `$dateTime` when executing `$dateTime->format('D');`.
> This type of programming, which avoids problems before they occur, is called defensive programming, and static analysis is helpful for these inspections.

#### Pre-Commit Testing

`composer tests` not only performs `composer test` but also checks coding standards (cs) and static analysis (sa).

```
composer tests
```

## Routing

The default router is `WebRouter`, which maps URLs to directories.
Here, we use the Aura router to accept dynamic parameters in the path.

First, install it with composer.
```bash
composer require bear/aura-router-module ^2.0
```

Next, install `AuraRouterModule` in `src/Module/AppModule.php` before `PackageModule`.

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

Place the router script file in `var/conf/aura.route.php`.

```php
<?php
/** 
 * @see http://bearsunday.github.io/manuals/1.0/ja/router.html
 * @var \Aura\Router\Map $map 
 */

$map->route('/weekday', '/weekday/{year}/{month}/{day}');
```

Let's try it.

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

Let's add a feature to log the requested weekday.

First, create `src/MyLoggerInterface.php` to log the weekday.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday;

interface MyLoggerInterface
{
    public function log(string $message): void;
}
```

The resource will be modified to use this logging feature.

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
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");
        $weekday = $dateTime->format('D');
        $this->body = [
            'weekday' => $weekday
        ];
+        $this->logger->log("$year-$month-$day {$weekday}");

        return $this;
    }
}
```
The `Weekday` class receives the logger service via the constructor.
This mechanism, where the necessary objects (dependencies) are not created with `new` or obtained from a container but are instead injected from outside, is called [DI](http://ja.wikipedia.org/wiki/%E4%BE%9D%E5%AD%98%E6%80%A7%E3%81%AE%E6%B3%A8%E5%85%A5).

Next, implement `MyLoggerInterface` in `MyLogger`.

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

Implementing `MyLogger` requires information about the application's log directory (`AbstractAppMeta`), which is also received as a `dependency` in the constructor.
Thus, while the `Weekday` resource depends on `MyLogger`, `MyLogger` also depends on the log directory information. In this way, objects constructed with DI are recursively injected with their dependencies.

This dependency resolution is performed by the DI tool (dependency injector).

To bind `MyLoggerInterface` and `MyLogger` using the DI tool, edit the `configure` method in `src/Module/AppModule.php`.

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

This allows any class to receive a logger via the constructor using `MyLoggerInterface`.

Run it and check that the results are output to `var/log/cli-hal-api-app/weekday.log`.

```bash
php bin/app.php get /weekday/2011/05/23
```

```bash
cat var/log/cli-hal-api-app/weekday.log
```

## AOP

Let's consider a benchmarking process to measure the execution time of methods.

```php?start_inline
$start = microtime(true);
// method invokation
$time = microtime(true) - $start;
```

Adding this code every time you perform a benchmark and removing it when it's no longer needed is cumbersome.
**Aspect-Oriented Programming (AOP)** allows you to nicely synthesize such specific pre- and post-method processes.

First, to achieve AOP, create an **interceptor** that hijacks the method execution and performs the benchmark in `src/Interceptor/BenchMarker.php`.

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
        $result = $invocation->proceed(); // Execute the original method
        $time = microtime(true) - $start;
        $message = sprintf('%s: %0.5f(µs)', $invocation->getMethod()->getName(), $time);
        $this->logger->log($message);

        return $result;
    }
}
```

In the interceptor's `invoke` method, the original method's execution can be performed using `$invocation->proceed();`, and the timer reset and recording process are performed before and after this. (The original method's object and method name are obtained from the method execution object [MethodInvocation](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php) `$invocation`.)

Next, create an [attribute](https://www.php.net/manual/ja/language.attributes.overview.php) to mark the method you want to benchmark in `src/Annotation/BenchMark.php`.

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

In `AppModule`, use **Matcher** to bind (bind) the interceptor to the method to which you want to apply it.

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

Apply the `#[BenchMark]` attribute to the method you want to benchmark.

```diff
+use MyVendor\Weekday\Annotation\BenchMark;

class Weekday extends ResourceObject
{

+   #[BenchMark]
    public function onGet(int $year, int $month, int $day): static
    {
```

Now you can benchmark any method by adding the `#[BenchMark]` attribute.

Adding functionality with attributes and interceptors is flexible. There are no changes to the target methods or the methods that call them.
The attribute remains as is, but you can remove the binding if you don't want to benchmark. For example, you can bind only during development and issue a warning if it exceeds a certain number of seconds.

Run it and check that the execution time logs are output to `var/log/weekday.log`.

```bash
php bin/app.php get '/weekday/2015/05/28'
```

```bash
cat var/log/cli-hal-api-app/weekday.log
```

## HTML

Next, let's turn this API application into an HTML application.
In addition to the current `app` resource, add a `page` resource in `src/Resource/Page/Index.php`.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Embed;

class Index extends ResourceObject
{
    #[Embed(rel:"_self", src: "app://self/weekday{?year,month,day}")]
    public function onGet(int $year, int $month, int $day): static
    {
        $this->body += [
            'year' => $year,
            'month' => $month,
            'day' => $day,
        ];

        return $this;
    }
}
```

The page resource class is essentially the same as the app resource class, just with different locations and roles.
Linking with `_self` copies the `app://self/weekday` resource onto itself.

The app resource's `weekday` is assigned to `$body['weekday']`, and the arguments year, month, day are added to the body.

Let's see what representation this resource has.

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

The resource is output as the `application/hal+json` media type, but to output it as HTML (text/html), install the HTML module. See [HTML Manual](/manuals/1.0/ja/html.html).

composer install

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

Copy the `templates` folder.

```bash
cp -r vendor/madapaja/twig-module/var/templates var
```

Change `bin/page.php` to use the `html-app` context.

```php
<?php
use MyVendor\Weekday\Bootstrap;

require dirname(__DIR__) . '/autoload.php';
exit((new Bootstrap())(PHP_SAPI === 'cli' ? 'cli-html-app' : 'html-app', $GLOBALS, $_SERVER));
```

This prepares you for `text/html` output.
Finally, edit the `var/templates/Page/Index.html.twig` file.

```bash
{% raw %}{% extends 'layout/base.html.twig' %}
{% block title %}Weekday{% endblock %}
{% block content %}
The weekday of {{ year }}/{{ month }}/{{ day }} is {{ weekday }}.
{% endblock %}{% endraw %}
```

Preparations are complete. First, check that this HTML is output in the console.

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

If html is not displayed at this time, there may be an error in the template engine.
In that case, check the error in the log file (`var/log/cli-html-app/last.log ref.log`).

Next, to provide web services, also change `public/index.php`.


```php
<?php

use MyVendor\Weekday\Bootstrap;

require dirname(__DIR__) . '/autoload.php';
exit((new Bootstrap())(PHP_SAPI === 'cli-server' ? 'html-app' : 'prod-html-app', $GLOBALS, $_SERVER));
```

Start the PHP server and check by accessing [http://127.0.0.1:8080/?year=2001&month=1&day=1](http://127.0.0.1:8080/?year=2001&month=1&day=1) in a web browser.

```bash
php -S 127.0.0.1:8080 public/index.php
```

[Context](/manuals/1.0/ja/application.html#context) is something like the application's execution mode, and multiple can be specified. Let's try it.

```php?start_inline
<?php

use MyVendor\Weekday\Bootstrap;

// JSON Application
require dirname(__DIR__) . '/autoload.php';
exit((new Bootstrap())('prod-app', $GLOBALS, $_SERVER));
```

```php?start_inline
<?php

use MyVendor\Weekday\Bootstrap;

// Production HAL Application
require dirname(__DIR__) . '/autoload.php';
exit((new Bootstrap())('prod-hal-app', $GLOBALS, $_SERVER));
```

PHP code that generates instances according to the context is created. Check the `var/tmp/{context}/di` folder of the application.
You don't usually need to see these files, but you can check how the objects are created.

## REST API

Let's create an application resource using sqlite3.
First, create a DB in `var/db/todo.sqlite3` in the console.

```bash
mkdir var/db
sqlite3 var/db/todo.sqlite3

sqlite> create table todo(id integer primary key, todo, created_at);
sqlite> .exit
```

You can choose database access from [AuraSql](https://github.com/ray-di/Ray.AuraSqlModule), [Doctrine Dbal](https://github.com/ray-di/Ray.DbalModule), [CakeDB](https://github.com/ray-di/Ray.CakeDbModule), etc., but here we will install Ray.AuraSqlModule.

```bash
composer require ray/aura-sql-module
```

Install the module in `src/Module/AppModule::configure()`.
At that time, bind `DateTimeImmutable` so that you can receive the current time in the constructor.

```diff
<?php
+use Ray\AuraSqlModule\AuraSqlModule;
+use DateTimeImmutable;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        // ...
+        $this->bind(DateTimeImmutable::class);        
+        $this->install(new AuraSqlModule(sprintf('sqlite:%s/var/db/todo.sqlite3', $this->appMeta->appDir)));
        $this->install(new PackageModule());
    }
}
```

Place the Todo resource in `src/Resource/App/Todos.php`.

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
    public function __construct(
        private readonly ExtendedPdoInterface $pdo,
        private readonly DateTimeImmutable $date,
    ) {
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

Pay attention to the attributes.

#### #[Cacheable]

The class attribute `#[Cacheable]` indicates that the GET method of this resource is cacheable.

#### #[Transactional]

`#[Transactional]` on `onPost` and `onPut` indicates database access transactions.

#### #[ReturnCreatedResource]

`#[ReturnCreatedResource]` on `onPost` creates and includes a resource indicated by the `Location` URL in the body. At this time, `onGet` is actually called using the `Location` header URI, ensuring the content of the `Location` header is correct while also creating a cache.

### POST Request

Let's try a `POST`.

First, to perform a cache-enabled test, create a test context boot file `bin/test.php`.

```php
<?php

declare(strict_types=1);

use MyVendor\Weekday\Bootstrap;

require dirname(__DIR__) . '/autoload.php';
exit((new Bootstrap())('prod-cli-hal-api-app', $GLOBALS, $_SERVER));
```

Make a request with a console command. It's a `POST`, but in BEAR.Sunday, parameters are passed in the form of a query.

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

The status code is `201 Created`. The `Location` header indicates that a new resource has been created at `/todos/?id=1`.
[RFC7231 Section-6.3.2](https://tools.ietf.org/html/rfc7231#section-6.3.2)

Next, get this resource.

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

The Hypermedia API is complete! Let's start the API server.

```bash
php -S 127.0.0.1:8081 bin/app.php
```

Use the `curl` command to GET.

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

Make multiple requests and confirm that the `Last-Modified` date does not change. (Try adding `echo` or similar in the method to check.)

The `Cacheable` attribute, if not set with `expiry`, does not invalidate the cache over time.
The cache is regenerated when resources are changed with `onPut($id, $todo)` or `onDelete($id)`.

Next, change this resource with the `PUT` method.

```bash
curl -i http://127.0.0.1:8081/todos -X PUT -d "id=1&todo=think"
```
A `204 No Content` response is returned, indicating there is no body.

```
HTTP/1.1 204 No Content
...
```

You can specify the media type with the `Content-Type` header. Try it with `application/json` as well.

```bash
curl -i http://127.0.0.1:8081/todos -X PUT -H 'Content-Type: application/json' -d '{"id": 1, "todo":"think" }'
```

GET again to see that the `Etag` and `Last-Modified` have changed.

```bash
curl -i 'http://127.0.0.1:8081/todos?id=1'
```

This `Last-Modified` date is provided by `#[Cacheable]`.
There is no need for the application to manage this or provide a database column.

Using `#[Cacheable]`, the resource content is managed in a "query repository" dedicated to resource storage, separate from the write database, and headers such as `Etag` and `Last-Modified` are automatically added.

## Because Everything is A Resource.

In BEAR, everything is a resource.

Resource identifiers (URI), a unified interface, stateless access, powerful caching systems, hyperlinks, layered systems, and self-descriptiveness.
BEAR.Sunday applications have these characteristics of REST, adhering to HTTP standards and excelling in reusability.

BEAR.Sunday is a connecting layer framework that ties dependencies with **DI**, cross-cutting concerns with **AOP**, and application information as resources with the power of **REST**.

---

[^1]:The source code for this project is committed to [bearsunday/Tutorial](https://github.com/bearsunday/tutorial1/commits/v3) section by section. Please refer to it as needed.
[^2]:Normally, the **vendor** name is the name of an individual or team (organization). A GitHub account name or team name would be suitable. Enter the application name for **project**.
```
