---
layout: docs-en
title: BEAR.Sunday Complete Manual
category: Manual
permalink: /manuals/1.0/en/1page.html
---

# BEAR.Sunday Complete Manual

This comprehensive manual contains all BEAR.Sunday documentation in a single page for easy reference, printing, or offline viewing.

***
# Technology

The distinctive technologies and features of BEAR.Sunday are explained in the following chapters. 

* [Architecture and Design Principles](#architecture-and-design-principles)
* [Performance and Scalability](#performance-and-scalability)
* [Developer Experience](#developer-experience)
* [Extensibility and Integration](#extensibility-and-integration)
* [Design Philosophy and Quality](#design-philosophy-and-quality)
* [The Value BEAR.Sunday Brings](#the-value-bearsunday-brings)

## Architecture and Design Principles

### Resource Oriented Architecture (ROA)

BEAR.Sunday's ROA is an architecture that realizes RESTful API within a web application. It is the core of BEAR.Sunday's design principles, functioning as both a hypermedia framework and a service-oriented architecture. Similar to the Web, all data and functions are considered resources and are operated through standardized interfaces such as GET, POST, PUT, and DELETE.

#### URI

URI (Uniform Resource Identifier) is a key element to the success of the Web and is also at the heart of BEAR.Sunday's ROA. By assigning URIs to all resources handled by the application, resources can be easily identified and accessed. URIs not only function as identifiers for resources but also express links between resources.

#### Uniform Interface

Access to resources is done using HTTP methods such as GET, POST, PUT, and DELETE. These methods specify the operations that can be performed on resources and provide a common interface regardless of the type of resource.

#### Hypermedia

In BEAR.Sunday's Resource Oriented Architecture (ROA), each resource provides affordances (available operations and functions for the client) through hyperlinks. These links represent the operations that clients can perform and guide navigation within the application.

#### Separation of State and Representation

In BEAR.Sunday's ROA, the state of a resource and its representation are clearly separated. The state of the resource is managed by the resource class, and the renderer injected into the resource converts the state of the resource into a resource state representation in various formats (JSON, HTML, etc.). Domain logic and presentation logic are loosely coupled, and even with the same code, changing the binding of the state representation based on the context will also change the representation.

#### Differences from MVC

BEAR.Sunday's ROA (Resource Oriented Architecture) takes a different approach from the traditional MVC architecture. MVC composes an application with three components: model, view, and controller. The controller receives a request object, controls a series of processes, and returns a response. In contrast, a resource in BEAR.Sunday, following the Single Responsibility Principle (SRP), only specifies the state of the resource in the request method and is not involved in the representation.

While there are no constraints on the relationship between controllers and models in MVC, resources have explicit constraints on including other resources using hyperlinks and URIs. This allows for declarative definition of content inclusion relationships and tree structures while maintaining information hiding of the called resources.

MVC controllers manually retrieve values from the request object, while resources declaratively define the required variables as arguments to the request method. Therefore, input validation is also performed declaratively using JsonSchema, and the arguments and their constraints are documented.

### Dependency Injection (DI)

Dependency Injection (DI) is an important technique for enhancing the design and structure of applications in object-oriented programming. The central purpose of DI is to divide an application's responsibilities into multiple components with independent domains or roles and manage the dependencies between them.

DI helps to horizontally divide one responsibility into multiple functions. The divided functions can be developed and tested independently as "dependencies". By injecting those dependencies with clear responsibilities and roles based on the single responsibility principle from the outside, the reusability and testability of objects are improved. Dependencies can also be vertically divided into other dependencies, forming a tree of dependencies.

BEAR.Sunday's DI uses a separate package called [Ray.Di](https://github.com/ray-di/Ray.Di), which adopts the design philosophy of Google's DI framework Guice and covers almost all of its features.

It also has the following characteristics:

* Bindings can be changed by context, allowing different implementations to be injected during testing.
* Attribute-based configuration enhances the self-descriptiveness of the code.
* Ray.Di performs dependency resolution at compile-time, improving runtime performance. This is different from other DI containers that resolve dependencies at runtime.
* Object dependencies can be visualized as a graph. Example: [Root Object](/images/app.svg).

<img src="https://ray-di.github.io/images/logo.svg" width="180" alt="Ray.Di logo">

### Aspect Oriented Programming (AOP)

Aspect-Oriented Programming (AOP) is a pattern that realizes flexible applications by separating essential concerns such as business logic from cross-cutting concerns such as logging and caching. Cross-cutting concerns refer to functions or processes that span across multiple modules or layers. It is possible to bind cross-cutting processes based on search conditions and flexibly configure them based on context.

BEAR.Sunday's AOP uses a separate package called Ray.Aop, which declaratively binds cross-cutting processes by attaching PHP attributes to classes and methods. Ray.Aop conforms to Java's [AOP Alliance](https://aopalliance.sourceforge.net/).

AOP is often misunderstood as a technology that "has the strong power to break the existing order". However, its raison d'être is not to exercise power beyond constraints but to complement areas where object-orientation is not well-suited, such as exploratory assignment of functions using matchers and separation of cross-cutting processes. AOP is a paradigm that can create cross-cutting constraints for applications, in other words, it functions as an application framework.

## Performance and Scalability

### ROA-based Event-Driven Content Strategy with Modern CDN Integration

BEAR.Sunday realizes an advanced event-driven caching strategy by integrating with instant purge-capable CDNs such as Fastly, with Resource Oriented Architecture (ROA) at its core. Instead of invalidating caches based on the conventional TTL (Time to Live), this strategy immediately invalidates the CDN and server-side caches, as well as ETags (entity tags), in response to resource state change events.

By taking this approach of creating non-volatile and persistent content on CDNs, it not only avoids SPOF (Single Point of Failure) and achieves high availability and fault tolerance but also maximizes user experience and cost efficiency. It realizes the same distributed caching as static content for dynamic content, which is the original principle of the Web. It re-realizes the scalable and network cost-reducing distributed caching principle that the Web has had since the 1990s with modern technology.

#### Cache Invalidation by Semantic Methods and Dependencies

In BEAR.Sunday's ROA, each resource operation is given a semantic role. For example, the GET method retrieves a resource, and the PUT method updates a resource. These methods collaborate in an event-driven manner and efficiently invalidate related caches. For instance, when a specific resource is updated, the cache of resources that require that resource is invalidated. This ensures data consistency and freshness, providing users with the latest information.

#### Identity Confirmation and Fast Response with ETag

By setting ETags before the system boots, content identity can be quickly confirmed, and if there are no changes, a 304 Not Modified response is returned to minimize network load.

#### Partial Updates with Donut Caching and ESI

BEAR.Sunday adopts a donut caching strategy and uses ESI (Edge Side Includes) to enable partial content updates at the CDN edge. This technology allows for dynamic updates of only the necessary parts without re-caching the entire page, improving caching efficiency.

In this way, BEAR.Sunday and Fastly's integration of ROA-based caching strategy not only realizes advanced distributed caching but also enhances application performance and fault tolerance.

### Accelerated Startup

In the original world of DI, users avoid dealing directly with the injector (DI container) as much as possible. Instead, they generate a single root object at the application's entry point to start the application. In BEAR.Sunday's DI, there is virtually no DI container manipulation even at configuration time. The root object is huge but is a single variable, so it is reused beyond requests, realizing an optimized bootstrap to the limit.

## Developer Experience

### Ease of Testing

BEAR.Sunday allows for easy and effective testing due to the following design features:

* Each resource is independent, and testing is easy due to the stateless nature of REST requests.
  Since the state and representation of resources are clearly separated, it is possible to test the state of resources even when they are in HTML representation.
* API testing can be performed while following hypermedia links, and tests can be written in the same code for PHP and HTTP.
* Different implementations are bound during testing through context-based binding.

### API Documentation Generation

API documentation is automatically generated from the code. It maintains consistency between code and documentation and improves maintainability.

### Visualization and Debugging

Utilizing the technical feature of resources rendering themselves, during development, the scope of resources can be indicated on HTML, resource states can be monitored, and PHP code and HTML templates can be edited in an online editor and reflected in real-time.

## Extensibility and Integration

### Integration of PHP Interfaces and SQL Execution

In BEAR.Sunday, the execution of SQL statements for interacting with databases can be easily managed through PHP interfaces. It is possible to directly bind SQL execution objects to PHP interfaces without implementing classes. The boundary between the domain and infrastructure is connected by PHP interfaces.

In that case, types can also be specified for arguments, and any missing parts are dependency-resolved by DI and used as strings. Even when the current time is needed for SQL execution, there is no need to pass it; it is automatically bound. This helps keep the code concise as the client is not responsible for passing all arguments.

Moreover, direct management of SQL makes debugging easier when errors occur. The behavior of SQL queries can be directly observed, allowing for quick identification and correction of problems.

### Integration with Other Systems

BEAR.Sunday resources can be accessed through various interfaces. In addition to web interfaces, resources can be accessed directly from the console, allowing the same resources to be used from both web and command-line interfaces without changing the source code. Furthermore, using BEAR.CLI, resources can be distributed as standalone UNIX commands. Multiple BEAR.Sunday applications can also run concurrently within the same PHP runtime, enabling collaboration between independent applications without building microservices.

### Stream Output

By assigning streams such as file pointers to the body of a resource, large-scale content that cannot be handled in memory can be output. In that case, streams can also be mixed with ordinary variables, allowing flexible output of large-scale responses.

### Gradual Migration from Other Systems

BEAR.Sunday provides a gradual migration path and enables seamless integration with other frameworks and systems such as Laravel and Symfony. This framework can be implemented as a Composer package, allowing developers to gradually introduce BEAR.Sunday's features into their existing codebase.

### Flexibility in Technology Migration

BEAR.Sunday protects investments in preparation for future technological changes and evolving requirements. Even if there is a need to migrate from this framework to another framework or language, the constructed resources will not go to waste. In a PHP environment, BEAR.Sunday applications can be integrated as Composer packages and continuously utilized, and BEAR.Thrift allows efficient access to BEAR.Sunday resources from other languages. When not using Thrift, access via HTTP is also possible. SQL code can also be easily reused.

Even if the library being used is strongly dependent on a specific PHP version, different versions of PHP can coexist using BEAR.Thrift.

## Design Philosophy and Quality

### Adoption of Standard Technologies and Elimination of Proprietary Standards

BEAR.Sunday has a design philosophy of adopting standard technologies as much as possible and eliminating framework-specific standards and rules. For example, it supports content negotiation for JSON format and www-form format HTTP requests by default and uses the [vnd.error+json](https://github.com/blongden/vnd.error) media type format for error responses. It actively incorporates standard technologies and specifications such as adopting [HAL](https://datatracker.ietf.org/doc/html/draft-kelly-json-hal) (Hypertext Application Language) for links between resources and using [JsonSchema](https://json-schema.org/) for validation.

On the other hand, it eliminates proprietary validation rules and framework-specific standards and rules as much as possible.

### Object-Oriented Principles

BEAR.Sunday emphasizes object-oriented principles to make applications maintainable in the long term.

#### Composition over Inheritance

Composition is recommended over inheritance classes. Generally, directly calling a parent class's method from a child class can potentially increase the coupling between classes. The only abstract class that requires inheritance at runtime by design is the resource class `BEAR\Resource\ResourceObject`, but the methods of ResourceObject exist solely for other classes to use. There is no case in BEAR.Sunday where a user calls a method of a framework's parent class that they have inherited at runtime.

#### Everything is Injected

Framework classes do not refer to "configuration files" or "debug constants" during execution to determine their behavior. Dependencies corresponding to the behavior are injected. This means that to change the application's behavior, there is no need to change the code; only the binding of the implementation of the dependency to the interface needs to be changed. Constants like APP_DEBUG or APP_MODE do not exist. There is no way to know in what mode the software is currently running after it has started, and there is no need to know.

### Permanent Assurance of Backward Compatibility

BEAR.Sunday is designed with an emphasis on maintaining backward compatibility in the evolution of software and has continued to evolve without breaking backward compatibility since its release. In modern software development, frequent breaking of backward compatibility and the associated burden of modification and testing have become a challenge, but BEAR.Sunday has avoided this problem.

BEAR.Sunday not only adopts semantic versioning but also does not perform major version upgrades that involve breaking changes. It prevents new feature additions or changes to existing features from affecting existing code. Code that has become old and unused is given the attribute "deprecated" but is never deleted and does not affect the behavior of existing code. Instead, new features are added, and evolution continues.

Here's the English translation of the revised text:

### Acyclic Dependencies Principle (ADP)

The Acyclic Dependencies Principle states that dependencies should be unidirectional and non-circular. The BEAR.Sunday framework adheres to this principle and is composed of a series of packages with a hierarchical structure where larger framework packages depend on smaller framework packages. Each level does not need to be aware of the existence of other levels that encompass it, and the dependencies are unidirectional and do not form cycles. For example, Ray.Aop is not even aware of the existence of Ray.Di, and Ray.Di is not aware of the existence of BEAR.Sunday.

<img src="/images/screen/package_adp.png" width="360px" alt="Framework structure following the Acyclic Dependencies Principle">

As backward compatibility is maintained, each package can be updated independently. Moreover, there is no version number that locks the entire system, as seen in other frameworks, and there is no mechanism for object proxies that hold cross-cutting dependencies between objects.

The Acyclic Dependencies Principle is in harmony with the DI (Dependency Injection) principle, and the root object generated during the bootstrapping process of BEAR.Sunday is also constructed following the structure of this Acyclic Dependencies Principle.

[<img src="/images/screen/clean-architecture.png" width="40%">](/images/screen/clean-architecture.png)

The same applies to the runtime. When accessing a resource, first, the cross-cutting processing of the AOP aspects bound to the method is executed, and then the method determines the state of the resource. At this point, the method is not aware of the existence of the aspects bound to it. The same goes for resources embedded in the resource's state. They do not have knowledge of the outer layers or elements. The separation of concerns is clearly defined.

### Code Quality

To provide applications with high code quality, the BEAR.Sunday framework also strives to maintain a high standard of code quality.

* The framework code is applied at the strictest level by both static analysis tools, Psalm and PHPStan.
* It maintains 100% test coverage and nearly 100% type coverage.
* It is fundamentally an immutable system and is so clean that initialization is not required every time, even in tests. It unleashes the power of PHP's asynchronous communication engines like Swoole.

## The Value BEAR.Sunday Brings

### Value for Developers

* Improved productivity: Based on robust design patterns and principles with constraints that don't change over time, developers can focus on core business logic.
* Collaboration in teams: By providing development teams with consistent guidelines and structure, it keeps the code of different engineers loosely coupled and unified, improving code readability and maintainability.
* Flexibility and extensibility: BEAR.Sunday's policy of not including libraries brings developers flexibility and freedom in component selection.
* Ease of testing: BEAR.Sunday's DI (Dependency Injection) and ROA (Resource Oriented Architecture) increase the ease of testing.

### Value for Users

* High performance: BEAR.Sunday's optimized fast startup and CDN-centric caching strategy brings users a fast and responsive experience.
* Reliability and availability: BEAR.Sunday's CDN-centric caching strategy minimizes single points of failure (SPOF), allowing users to enjoy stable services.
* Ease of use: BEAR.Sunday's excellent connectivity makes it easy to collaborate with other languages and systems.

### Value for Business

* Reduced development costs: The consistent guidelines and structure provided by BEAR.Sunday promote a sustainable and efficient development process, reducing development costs.
* Reduced maintenance costs: BEAR.Sunday's approach to maintaining backward compatibility increases technical continuity and minimizes the time and cost of change response.
* High extensibility: With technologies like DI (Dependency Injection) and AOP (Aspect Oriented Programming) that change behavior while minimizing code changes, BEAR.Sunday allows applications to be easily extended in line with business growth and changes.
* Excellent User Experience (UX): BEAR.Sunday provides high performance and high availability, increasing user satisfaction, enhancing customer loyalty, expanding the customer base, and contributing to business success.

Excellent constraints do not change. The constraints brought by BEAR.Sunday provide specific value to developers, users, and businesses respectively.

BEAR.Sunday is a framework designed based on the principles and spirit of the Web, providing developers with clear constraints to empower them to build flexible and robust applications.

***

# Version

## Supported PHP

[![Continuous Integration](https://github.com/bearsunday/BEAR.SupportedVersions/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.SupportedVersions/actions/workflows/continuous-integration.yml)

BEAR.Sunday supports the following supported PHP versions

* `8.1` (Old stable 25 Nov 2021 - 31 Dec 2025)
* `8.2` (Old stable 8 Dec 2022 - 31 Dec 2026)
* `8.3` (Old stable 23 Nov 2023 - 31 Dec 2027)
* `8.4` (Current stable 21 Nov 2024 - 31 Dec 2028)

* End of life ([EOL](http://php.net/eol.php))

* `5.5` (21 Jul 2016)
* `5.6` (31 Dec 2018)
* `7.0` (3 Dec 2018)
* `7.1` (1 Dec 2019)
* `7.2` (30 Nov 2020)
* `7.3` (6 Dec 2021)
* `7.4` (28 Nov 2022)
* `8.0` (26 Nov 2023)

The new optional package will be developed based on the current stable PHP. We encourage you to use the current stable PHP for quality, performance and security.

[BEAR.SupportedVersions](https://github.com/bearsunday/BEAR.SupportedVersions/), you can check the tests for each version in CI.

## Semver

BEAR.Sunday follows [Semantic Versioning](http://
semper.org/lang/en/). It is not necessary to modify the application code on minor version upgrades.

`composer update` can be done at any time for packages.

## Version Policy

 * The core package of the framework does not make a breaking change which requires change of user code.
 * Since it does not do destructive change, it handles unnecessary old ones as `deprecetad` but does not delete and new functions are always "added".
 * When PHP comes to an EOL and upgraded to a major version (ex. `5.6` →` 7.0`), BEAR.Sunday will not break the BC of the application code. Even though the version number of PHP that is necessary to use the new module becomes higher, changes to the application codes are not needed.

BEAR.Sunday emphasizes clean code and **longevity**.

## Package version

The version of the framework does not lock the version of the library. The library can be updated regardless of the version of the framework.

***

# Tutorial

In this tutorial, we introduce the basic features of BEAR.Sunday, including **DI** (Dependency Injection), **AOP** (Aspect-Oriented Programming), and **REST API**. Follow along with the commits from [tutorial1](https://github.com/bearsunday/tutorial1/commits/v3).

## Project Creation

Let's create a web service that returns the day of the week when a date (year, month, day) is entered. Start by creating a project.

```bash
composer create-project bear/skeleton MyVendor.Weekday
```

Enter `MyVendor` for the **vendor** name and `Weekday` for the **project** name. [^2]

## Resources

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
        $dateTime = (new DateTimeImmutable)->createFromFormat('Y-m-d', "$year-$month-$day");
        $weekday = $dateTime->format('D');
        $this->body = ['weekday' => $weekday];

        return $this;
    }
}
```

This resource class `MyVendor\Weekday\Resource\App\Weekday` can be accessed via the path `/weekday`. The query parameters of the `GET` method are passed to the `onGet` method.

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

The result is correctly returned in the [application/hal+json](https://tools.ietf.org/html/draft-kelly-json-hal-06) media type.

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
        $dateTime = (new DateTimeImmutable)->createFromFormat('Y-m-d', "$year-$month-$day");
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
        $weekday = (new DateTimeImmutable)->createFromFormat('Y-m-d', "$year-$month-$day")->format('D');
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

***

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

***

# BEAR.Sunday CLI Tutorial

## Prerequisites

- PHP 8.2 or higher
- Composer
- Git

## Step 1: Project Creation

### 1.1 Create a New Project

```bash
composer create-project -n bear/skeleton MyVendor.Greet
cd MyVendor.Greet
```

### 1.2 Verify Development Server

```bash
php -S 127.0.0.1:8080 -t public
```

Access [http://127.0.0.1:8080](http://127.0.0.1:8080) in your browser and confirm that "Hello BEAR.Sunday" is displayed.

```php
{
    "greeting": "Hello BEAR.Sunday",
    "_links": {
        "self": {
            "href": "/index"
        }
    }
}
```

## Step 2: Install BEAR.Cli

```bash
composer require bear/cli
```

## Step 3: Create Greeting Resource

Create `src/Resource/Page/Greeting.php`:

```php
<?php

namespace MyVendor\Greet\Resource\Page;

use BEAR\Cli\Attribute\Cli;
use BEAR\Cli\Attribute\Option;
use BEAR\Resource\ResourceObject;

class Greeting extends ResourceObject
{
    #[Cli(
        name: 'greet',
        description: 'Generate a greeting message',
        output: 'message'
    )]
    public function onGet(
        #[Option(shortName: 'n', description: 'Name to greet')]
        string $name = 'World',
        #[Option(shortName: 'l', description: 'Language (en, ja, fr, es)')]
        string $lang = 'en'
    ): static {
        $greeting = match ($lang) {
            'ja' => 'こんにちは',
            'fr' => 'Bonjour',
            'es' => '¡Hola',
            default => 'Hello',
        };
        
        $this->body = [
            'message' => "{$greeting}, {$name}!",
            'language' => $lang
        ];

        return $this;
    }
}
```

## Step 4: Generate CLI Command

Generate the CLI command using your application namespace:

```bash
$ vendor/bin/bear-cli-gen 'MyVendor\Greet'
# Generated files:
#   bin/cli/greet         # CLI command
#   var/homebrew/greet.rb # Homebrew formula (if Git repository is configured)
```

## Step 5: Test the CLI Command

### 5.1 Basic Usage

```bash
$ bin/cli/greet --help
Generate a greeting message

Usage: greet [options]

Options:
  --name, -n     Name to greet (default: World)
  --lang, -l     Language (en, ja, fr, es) (default: en)
  --help, -h     Show this help message

$ bin/cli/greet
Hello, World!

$ bin/cli/greet -n "Alice" -l ja
こんにちは, Alice!
```

### 5.2 Advanced Examples

```bash
# French greeting
$ bin/cli/greet --name "Pierre" --lang fr
Bonjour, Pierre!

# Spanish greeting
$ bin/cli/greet -n "Carlos" -l es
¡Hola, Carlos!
```

## Step 6: Add More Complex Features

### 6.1 Add Time-Based Greetings

Update the `Greeting` resource to include time-based greetings:

```php
<?php

namespace MyVendor\Greet\Resource\Page;

use BEAR\Cli\Attribute\Cli;
use BEAR\Cli\Attribute\Option;
use BEAR\Resource\ResourceObject;
use DateTimeImmutable;

class Greeting extends ResourceObject
{
    #[Cli(
        name: 'greet',
        description: 'Generate a time-aware greeting message',
        output: 'message'
    )]
    public function onGet(
        #[Option(shortName: 'n', description: 'Name to greet')]
        string $name = 'World',
        #[Option(shortName: 'l', description: 'Language (en, ja, fr, es)')]
        string $lang = 'en',
        #[Option(shortName: 't', description: 'Include time-based greeting')]
        bool $timeGreeting = false
    ): static {
        $greeting = $this->getGreeting($lang, $timeGreeting);
        
        $this->body = [
            'message' => "{$greeting}, {$name}!",
            'language' => $lang,
            'time' => (new DateTimeImmutable())->format('Y-m-d H:i:s')
        ];

        return $this;
    }
    
    private function getGreeting(string $lang, bool $timeGreeting): string
    {
        $baseGreeting = match ($lang) {
            'ja' => 'こんにちは',
            'fr' => 'Bonjour',
            'es' => '¡Hola',
            default => 'Hello',
        };
        
        if (!$timeGreeting) {
            return $baseGreeting;
        }
        
        $hour = (int) (new DateTimeImmutable())->format('H');
        
        return match ($lang) {
            'ja' => match (true) {
                $hour < 12 => 'おはようございます',
                $hour < 18 => 'こんにちは',
                default => 'こんばんは'
            },
            'fr' => match (true) {
                $hour < 12 => 'Bonjour',
                $hour < 18 => 'Bon après-midi',
                default => 'Bonsoir'
            },
            'es' => match (true) {
                $hour < 12 => 'Buenos días',
                $hour < 18 => 'Buenas tardes',
                default => 'Buenas noches'
            },
            default => match (true) {
                $hour < 12 => 'Good morning',
                $hour < 18 => 'Good afternoon',
                default => 'Good evening'
            }
        };
    }
}
```

### 6.2 Test Enhanced Features

```bash
# Regenerate CLI command after changes
$ vendor/bin/bear-cli-gen 'MyVendor\Greet'

# Test time-based greetings
$ bin/cli/greet -n "Alice" -l en -t
Good morning, Alice!  # (if run in the morning)

$ bin/cli/greet -n "田中" -l ja -t
おはようございます, 田中!  # (if run in the morning)
```

## Step 7: Testing

### 7.1 Create Unit Tests

Create `tests/Resource/Page/GreetingTest.php`:

```php
<?php

namespace MyVendor\Greet\Resource\Page;

use BEAR\Resource\ResourceInterface;
use MyVendor\Greet\Injector;
use PHPUnit\Framework\TestCase;

class GreetingTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $this->resource = Injector::getInstance('test-cli-app')
            ->getInstance(ResourceInterface::class);
    }

    public function testDefaultGreeting(): void
    {
        $response = $this->resource->get('page://self/greeting');
        
        $this->assertSame(200, $response->code);
        $this->assertSame('Hello, World!', $response->body['message']);
        $this->assertSame('en', $response->body['language']);
    }

    public function testJapaneseGreeting(): void
    {
        $response = $this->resource->get('page://self/greeting', [
            'name' => '太郎',
            'lang' => 'ja'
        ]);
        
        $this->assertSame('こんにちは, 太郎!', $response->body['message']);
        $this->assertSame('ja', $response->body['language']);
    }

    public function testTimeBasedGreeting(): void
    {
        $response = $this->resource->get('page://self/greeting', [
            'name' => 'Alice',
            'lang' => 'en',
            'timeGreeting' => true
        ]);
        
        $this->assertStringContains('Alice!', $response->body['message']);
        $this->assertArrayHasKey('time', $response->body);
    }
}
```

### 7.2 Run Tests

```bash
$ composer test
```

## Step 8: Deployment and Distribution

### 8.1 GitHub Repository Setup

If you have a GitHub repository configured, a Homebrew formula will be generated automatically. You can distribute your CLI tool via Homebrew:

```bash
# Create a tap repository
$ git clone https://github.com/yourusername/homebrew-tap.git
$ cp var/homebrew/greet.rb homebrew-tap/greet.rb
$ cd homebrew-tap
$ git add greet.rb
$ git commit -m "Add greet formula"
$ git push
```

### 8.2 Install via Homebrew

Users can then install your CLI tool:

```bash
$ brew tap yourusername/tap
$ brew install greet
$ greet -n "User" -l en
Hello, User!
```

## Conclusion

This tutorial has demonstrated more than just CLI tool creation—it has revealed the essential value of BEAR.Sunday:

### The True Value of Resource-Oriented Architecture

**One Resource, Multiple Boundaries**
- The `Greeting` resource functions as Web API, CLI, and Homebrew package with a single implementation
- No duplication of business logic, maintenance in one place

### Boundary-Crossing Framework

BEAR.Sunday functions as a **boundary framework**, transparently handling:

- **Protocol boundaries**: HTTP ↔ Command line
- **Interface boundaries**: Web ↔ CLI ↔ Package distribution  
- **Environment boundaries**: Development ↔ Production ↔ User environments

### Design Philosophy in Action

```php
// One resource
class Greeting extends ResourceObject {
    public function onGet(string $name, string $lang = 'en'): static
    {
        // Business logic in one place
    }
}
```

↓

```bash
# As Web API
curl "http://localhost/greeting?name=World&lang=ja"

# As CLI  
./bin/cli/greet -n "World" -l ja

# As Homebrew package
brew install your-vendor/greet && greet -n "World" -l ja
```

### Long-term Maintainability and Productivity

- **DRY Principle**: Domain logic is not coupled with interfaces
- **Unified Testing**: Testing one resource covers all boundaries
- **Consistent API Design**: Same parameter structure for Web API and CLI
- **Future Extensibility**: New boundaries (gRPC, GraphQL, etc.) can use the same resource
- **PHP Version Independence**: Freedom to continue using what works

### Integration with Modern Distribution Systems

BEAR.Sunday resources integrate naturally with modern package systems. By leveraging package managers like Homebrew and the Composer ecosystem, users can utilize tools through unified interfaces without being aware of the execution environment.

BEAR.Sunday's "Because Everything is a Resource" is not just a slogan, but a design philosophy that realizes consistency and maintainability across boundaries. As experienced in this tutorial, resource-oriented architecture creates boundary-free software and brings new horizons to both development and user experiences.

## Next Steps

- Explore more complex CLI patterns
- Add configuration file support
- Implement subcommands
- Add logging and error handling
- Create interactive CLI interfaces

For more information, see the [CLI documentation](cli.html) and [BEAR.Cli repository](https://github.com/bearsunday/BEAR.Cli).

***

# Package

BEAR.Sunday application is a composer package taking BEAR.Sunday framework as dependency package.
You can also install another BEAR.Sunday application package as dependency.

## Application organization

The file layout of the BEAR.Sunday application conforms to [php-pds/skeleton](https://github.com/php-pds/skeleton) standard.

### Invoke sequence

 1. Console input(`bin/app.php`, `bin/page.php`) or web entry file (`public/index.php`) excute `bootstrap.php` function.
 3. `$app` application object is created by `$context` in `boostrap.php`.
 4. A router in `$app` convert external resource request to internal resource request.
 4. A resource request is invoked. The representation of the result transfered to a client.


### bootstrap/

You can access same resource through console input or web access with same boot file.

```bash
php bin/app.php options /todos // console API access　(app resource)
```

```bash
php bin/page.php get '/todos?id=1' // console Web access (page resource)
```

```bash
php -S 127.0.0.1bin/app.php // PHP server
```

You can create your own boot file for different context.

### bin/

Plavce command-line executable files.

### src/

Place application class file.

### publc/

Web public folder.

### var/

`log` and `tmp` folder need write permission.

## Framework Package

### ray/aop
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/)
[![codecov](https://codecov.io/gh/ray-di/Ray.Aop/branch/2.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/ray-di/Ray.Aop)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Aop/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Aop)
[![Continuous Integration](https://github.com/ray-di/Ray.Aop/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ray-di/Ray.Aop/actions/workflows/continuous-integration.yml)

An aspect oriented framework based on Java AOP Alliance API.

### ray/di
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/)
[![codecov](https://codecov.io/gh/ray-di/Ray.Di/branch/2.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/ray-di/Ray.Di)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Di/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Di)
[![Continuous Integration](https://github.com/ray-di/Ray.Di/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ray-di/Ray.Di/actions/workflows/continuous-integration.yml)

A Google Guice style DI framework. It contains `ray/aop`.

### bear/resource
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Resource/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/bearsunday/BEAR.Resource)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Resource/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Resource)
[![Continuous Integration](https://github.com/bearsunday/BEAR.Resource/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.Resource/actions/workflows/continuous-integration.yml)

A REST framework for PHP object as a service. It contains `ray/di`.

### bear/sunday
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Sunday/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/bearsunday/BEAR.Sunday)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Sunday/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Sunday)
[![Continuous Integration](https://github.com/bearsunday/BEAR.Sunday/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.Sunday/actions/workflows/continuous-integration.yml)

A web application interface package. It contains `bear/resource`.

### bear/package
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Package/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/bearsunday/BEAR.Pacakge)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Package/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Package)
[![Continuous Integration](https://github.com/bearsunday/BEAR.Package/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.Package/actions/workflows/continuous-integration.yml)

A web application implmentation package. It contains `bear/sunday`.

## Library Package

Optional library package can be installed with `composer require` command.

| **Category** | **Composer package** | **Library**
| Router |
| |[bear/aura-router-module](https://github.com/bearsunday/BEAR.AuraRouterModule) | [Aura.Router v2](https://github.com/auraphp/Aura.Router/tree/2.x) |
| Database |
|| [ray/media-query](https://github.com/ray-di/Ray.MediaQuery) |
|| [ray/aura-sql-module](https://github.com/ray-di/Ray.AuraSqlModule) | [Aura.Sql v2](https://github.com/auraphp/Aura.Sql/tree/2.x)
|| [ray/dbal-module](https://github.com/ray-di/Ray.DbalModule) | [Doctrine DBAL](https://github.com/doctrine/dbal)
|| [ray/cake-database-module](https://github.com/ray-di/Ray.CakeDbModule) | [CakePHP v3 database](https://github.com/cakephp/database)
|| [ray/doctrine-orm-module](https://github.com/kawanamiyuu/Ray.DoctrineOrmModule) | [Doctrine ORM](https://github.com/doctrine/doctrine2)
| Storage |
||[bear/query-repository](https://github.com/bearsunday/BEAR.QueryRepository) | CQRS inspired repository
||[bear/query-module](https://github.com/ray-di/Ray.QueryModule) | Separation of external access such as DB or Web API
| Web
| |[madapaja/twig-module](http://bearsunday.github.io/manuals/1.0/ja/html.html) | [Twig](http://twig.sensiolabs.org/)
| |[ray/web-form-module](http://bearsunday.github.io/manuals/1.0/ja/form.html) | Web form
| |[ray/aura-web-module](https://github.com/Ray-Di/Ray.AuraWebModule) | [Aura.Web](https://github.com/auraphp/Aura.Web)
| |[ray/aura-session-module](https://github.com/ray-di/Ray.AuraSessionModule) | [Aura.Session](https://github.com/auraphp/Aura.Session)
| |[ray/symfony-session-module](https://github.com/kawanamiyuu/Ray.SymfonySessionModule) | [Symfony Session](https://github.com/symfony/http-foundation/tree/master/Session)
| Validation |
| |[ray/validate-module](https://github.com/ray-di/Ray.ValidateModule) | [Aura.Filter](https://github.com/auraphp/Aura.Filter)
| |[satomif/extra-aura-filter-module](https://github.com/satomif/ExtraAuraFilterModule)| [Aura.Filter](https://github.com/auraphp/Aura.Filter)
| Authorization and Authentication
| |[ray/oauth-module](https://github.com/Ray-Di/Ray.OAuthModule) | OAuth
| |[kuma-guy/jwt-auth-module](https://github.com/kuma-guy/BEAR.JwtAuthModule) | JSON Web Token
| |[ray/role-module](https://github.com/ray-di/Ray.RoleModule) | Zend Acl
| |[bear/acl-resource](https://github.com/bearsunday/BEAR.AclResource) | ACL based embedded resource
| Hypermedia
| |[kuma-guy/siren-module](https://github.com/kuma-guy/BEAR.SirenModule) | Siren
|  Development
| |[ray/test-double](https://github.com/ray-di/Ray.TestDouble) | Test Double
|  Asynchronous high performance |
| |[MyVendor.Swoole](https://github.com/bearsunday/MyVendor.Swoole) | [Swoole](https://github.com/swoole/swoole-src)

## Vendor Package

You can reuse common packages and tool combinations as modules with only modules and share modules of similar projects.[^1]

## Semver

All packages adhere to [Semantic Versioning](http://semver.org/).

---

[^1]: See [Koriym.DbAppPackage](https://github.com/koriym/Koriym.DbAppPackage)

***

# <a name="app"></a>Application

## Sequence

A BEAR.Sunday app has a run order of `compile`, `request` and `response`.

### 0. Compile

An `$app` application object is created through `DI` and `AOP` configuration depending on a specified context.
An `$app` is made up of service objects as it's properties that are needed to run the application such as a `router` or `transfer` etc.
`$app` then connects these object together depending on whether it is owned by another or contains other objects.
This is called an [Object Graph](http://en.wikipedia.org/wiki/Object_graph).
`$app` is then serialized and reused in each request and response.

* router - Converting external input to resource requests
* resource - Resource client
* transfer - Output

### 1. Request

An application resource request and resource object is created based on the HTTP request.

A resource object which has methods that respond to `onGet`, `onPost` etc upon request sets the `code` or `body` property of it's own resource state.

The resource object can then `#[Embed]` or `#[Link]` other resource objects.

Methods on the resource object are only for changing the resources state and have no interest in the representation itself (HTML, JSON etc).

Before and after the method, application logic bound to the method, such as logging and authentication, is executed in AOP.

### 2. Response

A `Renderer` is injected into the resource object, then the state of resource is represented as HTML, JSON etc or however it has been configured, it is then transfered to the client.

 <img src="/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>


## Boot File

To run an application, we need just two lines of code.
An entry point for a web server or console application access is usually set to `public/index.php` or `bin/app.php`.
As you can see below, we need to pass an application context to `bootstrap.php` the application script.


```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')('prod-html-app'));
```

Depending on your context choose a boot file.

```bash
// fire php server
php -S 127.0.0.1:8080 public/index.php
```

```
// console access
php bin/app.php get /user/1
```

## Context

The composition of the application object `$app` changes in response to the defined context, so that application behavior changes.

Depending on the defined context the building of the application object `$app` changes, altering the overall behavior.


For example, `WebRouter` is bound to `RouterInterface` by default.
However, if `Cli` mode is set (instead of HTTP) the `CliRouter` is bound to the `RouterInterface` and it will then take console input.

There are built-in and custom contexts that can be used in an application.

### Built-in Contexts

 * `api`  API Application
 * `cli`  Console Application
 * `hal`  HAL Application
 * `prod` Production

For `app`, resources are rendered in JSON.
`api` changes the default resource schema from page to app; web root access (GET /) is from page://self/ to app://self/.
Set `cli` to be a console application.
prod` makes it a production application with cache settings, etc.

You can also use a combination of these built-in contexts and add your own custom contexts.
If you set the context to `prod-hal-api-app` your application will run as an API application in production mode using the [HAL](http://stateless.co/hal_specification.html) media type.

### Custom Context

Place it in `src/Module`/ of the application; if it has the same name as the builtin context, the custom context will take precedence. You can override some of the constraints by calling the built-in context from the custom context.

Each application context (cli, app etc) represents a module.
For example the `cli` context relates to a `CliModule`, then binds all of the DI and AOP bindings that is needed for a console application.

### Context Agnostic

The context value is used only to create the root object and then disappears. There is no global "mode" that can be referenced by the application, and the application can not know what context it is currently running in. The behavior should only change through **code that is dependent on an interface**[^dip] and changes of dependencies by context.

---

[^dip]: [Dependency inversion principle](https://en.wikipedia.org/wiki/Dependency_inversion_principle)

***

# Modules

A Module is a collection of DI & AOP bindings that sets up your application.

BEAR.Sunday doesn't have a *global* config file or a config class to set default values for components such as a database or a template engine.
Instead for each peice of functionality we set up DI and AOP by injecting configuration values into a stand alone module.

`AppModule` (src/Module/AppModule.php) is the root module. We use an `install()` method in here to load each module that we would like to invoke.

You can also override existing bindings by using `override()`.

```php?start_inline
class AppModule extends AbstractAppModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // ...
        // install additional modules
        $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', 'username', 'password');
        $this->install(new TwigModule));
        // install basic module
        $this->install(new PackageModule));
    }
}
```

## DI bindings

`Ray.Di` is the core DI framework used in BEAR.Sunday. It binds interfaces to a class or factory to create an object graph.

```php?start_inline
// Class binding
$this->bind($interface)->to($class);
// Provider (factory) binding
$this->bind($interface)->toProvider($provider);
// Instance binding
$this->bind($interface)->toInstance($instance);
// Named binding
$this->bind($interface)->annotatedWith($annotation)->to($class);
// Singleton
$this->bind($interface)->to($class)->in(Scope::SINGLETON);
// Constructor binding
$this->bind($interface)->toConstructor($class, $named);
```

Bindings declared first take priority
More info can be found at Ray.Di [README](https://github.com/ray-di/Ray.Di/blob/2.x/README.md)

## AOP Bindings

We can "search" for classes and methods with a built-in `Matcher`, then interceptors can be bound to any found methods.

```php?start_inline
$this->bindInterceptor(
    // In any class
    $this->matcher->any(),
    // Method(s) names that start with "delete"
    $this->matcher->startWith('delete'),
    // Bind a Logger interceptor
    [LoggerInterceptor::class]
);

$this->bindInterceptor(
    // The AdminPage class or a class inherited from it.
    $this->matcher->SubclassesOf(AdminPage::class),
    // Annotated with the @Auth annotation
    $this->matcher->annotatedWith(Auth::class),
    // Bind the AdminAuthenticationInterceptor
    [AdminAuthenticationInterceptor::class]
);
```

`Matcher` has various binding methods.

 * [Matcher::any](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L16) - Any
 * [Matcher::annotatedWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L23) - Annotation
 * [Matcher::subclassesOf](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php#L30) - Sub class
 * [Matcher::startsWith](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php#L37) - start with name (class or method)
 * [Matcher::logicalOr](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php#L44) - OR
 * [Matcher::logicalAnd](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php#L51) - AND
 * [Matcher::logicalNot](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php#L58) - NOT

## Interceptor

In an interceptor a `MethodInvocation` object gets passed to the `invoke` method. We can the decorate the targetted instances so that you run computations before or after any methods on the target are invoked.

```php?start_inline
class MyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        // Before invocation
        // ...

        //  Method invocation
        $result = $invocation->proceed();

        //  After invocation
        // ...

        return $result;
    }
}
```

With the `MethodInvocation` object, you can access the target method's invocation object, method's and parameters.

 * [MethodInvocation::proceed](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Joinpoint.php#L39) - Invoke method
 * [MethodInvocation::getMethod](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MethodInvocation.php) -  Get method reflection
 * [MethodInvocation::getThis](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Joinpoint.php#L48) - Get object
 * [MethodInvocation::getArguments](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Invocation.php) - Pet parameters

Annotations can be obtained using the reflection API.

```php?start_inline
$method = $invocation->getMethod();
$class = $invocation->getMethod()->getDeclaringClass();
```

 * `$method->getAnnotations()`
 * `$method->getAnnotation($name)`
 * `$class->getAnnotations()`
 * `$class->getAnnotation($name)`

## Environment Settings

BEAR.Sunday does not have any special environment mode except `prod`.
A Module and the application itself are unaware of the current environment.

There is no way to get the current "mode", this is intentional to keep the code clean.

***

# DI

Dependency injection is basically providing the objects that an object needs (its dependencies) instead of having it construct them itself.

With dependency injection, objects accept dependencies in their constructors. To construct an object, you first build its dependencies. But to build each dependency, you need its dependencies, and so on. So when you build an object, you really need to build an object graph.

Building object graphs by hand is labour intensive, error prone, and makes testing difficult. Instead, **Dependency Injector** ([Ray.Di](https://github.com/ray-di/Ray.Di)) can build the object graph for you. 

| What is object graph ?
| Object-oriented applications contain complex webs of interrelated objects. Objects are linked to each other by one object either owning or containing another object or holding a reference to another object. This web of objects is called an object graph and it is the more abstract structure that can be used in discussing an application's state. - [Wikipedia](http://en.wikipedia.org/wiki/Object_graph)

Ray.Di is the core DI framework used in BEAR.Sunday, which is heavily inspired by Google [Guice](http://code.google.com/p/google-guice/wiki/Motivation?tm=6) DI framework.See more detail at [Ray.Di Manual](https://ray-di.github.io/manuals/1.0/en/index.html).

***

# AOP

BEAR.Sunday **AOP** enables you to write code that is executed each time a matching method is invoked. It's suited for cross cutting concerns ("aspects"), such as transactions, security and logging. Because interceptors divide a problem into aspects rather than objects, their use is called Aspect Oriented Programming (AOP).

The method interceptor API implemented is a part of a public specification called [AOP Alliance](http://aopalliance.sourceforge.net/).

## Interceptor

[MethodInterceptors](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInterceptor.php) are executed whenever a matching method is invoked.
They have the opportunity to inspect the call: the method, its arguments, and the receiving instance.
They can perform their cross-cutting logic and then delegate to the underlying method.
Finally, they may inspect the return value or the exception and return. Since interceptors may be applied to many methods and will receive many calls, their implementation should be efficient and unintrusive.


```php?start_inline
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class MyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        // Process before method invocation
        // ...

        // Original method invocation
        $result = $invocation->proceed();

        // Process after method invocation
        // ...

        return $result;
    }
}
```

## Bindings

"Find" the target class and method with `Matcher` and bind the interceptor to the matching method in [Module](module.html).

```php?start_inline
$this->bindInterceptor(
    $this->matcher->any(),                   // In any class,
    $this->matcher->startsWith('delete'),    // Method(s) names that start with "delete",
    [Logger::class]                          // Bind a Logger interceptor
);

$this->bindInterceptor(
    $this->matcher->subclassesOf(AdminPage::class),  // Of the AdminPage class or a class inherited from it
    $this->matcher->annotatedWith(Auth::class),      // Annotated method with the @Auth annotation
    [AdminAuthentication::class]                     //Bind the AdminAuthenticationInterceptor
);
```

There are various matchers.

 * [Matcher::any](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L16) 
 * [Matcher::annotatedWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L23) 
 * [Matcher::subclassesOf](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L30)
 * [Matcher::startsWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L37)
 * [Matcher::logicalOr](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L44)
 * [Matcher::logicalAnd](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L51)
 * [Matcher::logicalNot](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L58) 
```

With the `MethodInvocation` object, you can access the target method's invocation object, method's and parameters.

 * [MethodInvocation::proceed](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Joinpoint.php#L39) - Invoke method
 * [MethodInvocation::getMethod](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MethodInvocation.php) -  Get method reflection
 * [MethodInvocation::getThis](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Joinpoint.php#L48) - Get object
 * [MethodInvocation::getArguments](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Invocation.php) - Pet parameters

Annotations can be obtained using the reflection API.

```php?start_inline
$method = $invocation->getMethod();
$class = $invocation->getMethod()->getDeclaringClass();
```

 * `$method->getAnnotations()`    // get method annotations
 * `$method->getAnnotation($name)`
 * `$class->getAnnotations()`     // get class annotations
 * `$class->getAnnotation($name)`

## Own matcher
   
You can have your own matcher.
To create `contains` matcher, You need to provide a class which has two methods. One is `matchesClass` for a class match.
The other one is `matchesMethod` method match. Both return the boolean result of match.

```php?start_inline
use Ray\Aop\AbstractMatcher;

class ContainsMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        list($contains) = $arguments;

        return (strpos($class->name, $contains) !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        list($contains) = $arguments;

        return (strpos($method->name, $contains) !== false);
    }
}
```

Module

```php?start_inline
class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->bindInterceptor(
            $this->matcher->any(),       // In any class,
            new ContainsMatcher('user'), // When 'user' contained in method name
            [UserLogger::class]          // Bind UserLogger class
        );
    }
};
```

***

# Resource

A BEAR.Sunday application is [RESTful](http://en.wikipedia.org/wiki/Representational_state_transfer) and is made up of a collection of resources connected by links.

## Object as a service

An HTTP method is mapped to a PHP method in the `ResourceObject` class.
It transfers its resource state as a resource representation from stateless request.
([Representational State Transfer)](http://en.wikipedia.org/wiki/REST)

Here are some examples of a resource object:

```php?start_inline
class Index extends ResourceObject
{
    public $code = 200;
    public $headers = [];

    public function onGet(int $a, int $b): static
    {
        $this->body = [
            'sum' => $a + $b // $_GET['a'] + $_GET['b']
        ] ;

        return $this;
    }
}
```

```php?start_inline
class Todo extends ResourceObject
{
    public function onPost(string $id, string $todo): static
    {
        $this->code = 201; // status code
        $this->headers = [ // header
            'Location' => '/todo/new_id'
        ];

        return $this;
    }
}
```

The PHP resource class has URIs such as  `page://self/index` similar to the URI of the web, and conforms to the HTTP method `onGet`,` onPost`, `onPut`,` onPatch`, `onDelete` interface.

$_GET for `onGet` and $_POST for `onPost` are passed to the arguments of the method depending on the variable name, and the methods of `onPut`,` onPatch`, `onDelete` are content. The value that can be handled according to `content-type`(`x-www-form-urlencoded` or `application/json`) is an argument.

The resource state (`code`,`headers` or`body`) is handled by these method using the given parameters. Then the resource class returns itself(`$this`).

## URI

URIs are mapped to PHP classes. Applications use the URI instead of the class name to access resources.

| URI | Class |
|-----+-------|
| page://self/ | Koriym\Todo\Resource\Page\Index |
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts?id=3 | Koriym\Todo\Resource\App\Blog\Posts |

## Scheme

The equivalent to a MVC model is an `app` resource. A resource functions as an internal API, but as it is designed using REST it also works as an external API transport.
The `page` resource carries out a similar role as a page controller which is also a resource. Unlike `app` resources, it receives external requests and generates representations for output.

| URI | Class |
|-----+-------|
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts | Koriym\Todo\Resource\App\Blog\Posts |

## Method

Resources have 6 interfaces conforming to HTTP methods.[^method]

[^method]: REST methods are not a mapping to CRUD. They are divided into two categories: safe ones that do not change the resource state, or idempotent ones.

### GET
Reads resources. This method does not provide any changing of the resource state. A safe method with no possible side affects.

### POST
The POST method requests processing of the representation contained in the request. For example, adding a new resource to a target URI or adding a representation to an existing resource. Unlike PUT, requests do not have [idempotence](https://ja.wikipedia.org/wiki/%E5%86%AA%E7%AD%89), and multiple consecutive executions will not produce the same result.

### PUT
Replaces the resource with the payload of the request at the requested URI. If the target resource does not exist, it is created. Unlike POST, there is not idempotent.

### PATCH

Performs resource updates, but unlike PUT, it applies a delta rather than replacing the entire resource.


### DELETE
Resource deletion. Has idempotence just like PUT.

### OPTIONS
Get information on parameters and responses required for resource request. It is as secure as GET method.

#### List of method properties

| Methods | [Safe](https://developer.mozilla.org/en-US/docs/Glossary/Safe/HTTP) | [Idempotent](https://developer.mozilla.org/en-US/docs/Glossary/Idempotent) | [Cacheable](https://developer.mozilla.org/en-US/docs/Glossary/cacheable) 
|-|-|-|-|-
| GET | Yes | Yes | Yes
| POST | No | No | No
| PUT | No | Yes | No
| PATCH | No | Yes | No
| DELETE | No | Yes | No
| OPTIONS | Yes | Yes | No

## Parameters

The response method argument is passed the request value corresponding to the variable name.

```php?start_inline
class Index extends ResourceObject
{
    // $_GET['id'] to $id
    public function onGet(int $id): static
    {
    }

    // $_POST['name'] to $name
    public function onPost(string $name): static
    {
    }
```

See [Resource Parameters](resource_param.html) for other methods and how to pass external variables such as cookies as parameters.

## Rendering and transfer

The request method of a ResourceObject is not concerned with the representation of the resource. The injected renderer generates the representation of the resource and the responder outputs it. See [Rendering and Transferring](resource_renderer.html) for details.

## Client

Use the resource client to request other resources. This request executes a request to the `app://self/blog/posts` resource with the query `?id=1`.

```php?start_inline
use BEAR\Sunday\Inject\ResourceInject;

class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(): static
    {
        $this->body = [
            'posts' => $this->resource->get('app://self/blog/posts', ['id' => 1])
        ];
    }
}
```

Other historical notations include the following

```php?start_inline
// PHP 5.x and up
$posts = $this->resource->get->uri('app://self/posts')->withQuery(['id' => 1])->eager->request();
// PHP 7.x and up
$posts = $this->resource->get->uri('app://self/posts')(['id' => 1]);
// you can omit `get`
$posts = $this->resource->uri('app://self/posts')(['id' => 1]);
// bear/resource 1.11 and up
$posts = $this->resource->get('app://self/posts', ['id' => 1]);
```

## Lazy evaluation

The above is an `eager` request that makes the request immediately, but it is also possible to generate a request and delay execution instead of the request result.

```php
$request = $this->resource->get('app://self/posts'); // callable
$posts = $request(['id' => 1]);
```

When this request is embedded in a template or resource, it is evaluated lazily. That is, when it is not evaluated, the request is not made and has no execution cost.

```php
$this->body = [
    'lazy' => $this->resource->get('app://self/posts')->withQuery(['id' => 3])->request();
];
```

## Cache

Along with regular TTL caching, we support REST client caching and advanced partial caching (doughnut caching), including CDN. See [cache](cache.html) for details. Also see the previous [resource(v1)](resourcev1.html#Resource Cache) document for the previous `@Cacheable` annotation.

## Link

One important REST constraint is resource linking; ResourceObject supports both internal and external linking. See [Resource Linking](resource_link.html) for details.

## BEAR.Resource

The functionality of the BEAR.Sunday resource object is also available in a stand-alone package for stand-alone use: BEAR.Resource [README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.ja.md).

---

***

# Router

The router converts resource requests for external contexts such as Web and console into resource requests inside BEAR.Sunday.

```php?start_inline
$request = $app->router->match($GLOBALS, $_SERVER);
echo (string) $request;
// get page://self/user?name=bear
```

## Web Router

The default web router accesses the resource class corresponding to the HTTP request path (`$_SERVER['REQUEST_URI']`).
For example, a request of `/index` is accessed by a PHP method corresponding to the HTTP method of the `{Vendor name}\{Project name}\Resource\Page\Index` class.

The Web Router is a convention-based router. No configuration or scripting is required.

```php?start_inline
namespace MyVendor\MyProject\Resource\Page;

// page://self/index
class Index extends ResourceObject
{
    public function onGet(): static // GET request
    {
    }
}
```

## CLI Router

In the `cli` context, the argument from the console is "input of external context".

```bash
php bin/page.php get /
```

The BEAR.Sunday application works on both the Web and the CLI.

## Multiple words URI

The path of the URI using hyphens and using multiple words uses the class name of Camel Case.
For example `/wild-animal` requests are accessed to the `WildAnimal` class.

## Parameters

The name of the PHP method executed corresponding to the HTTP method and the value passed are as follows.

| HTTP method | PHP method | Parameters |
|---|---|---|
| GET | onGet | $_GET |
| POST | onPost | $_POST or ※ standard input |
| PUT | onPut | ※ standard input |
| PATCH | onPatch | ※ standard input |
| DELETE | onDelete | ※ standard input　|

There are two media types available for request:

 * `application/x-www-form-urlencoded` // param1=one&param2=two
 * `application/json` // {"param1": "one", "param2": "one"}

Please also see the [PUT method support](http://php.net/manual/en/features.file-upload.put-method.php) of the PHP manual.

## Method Override

There are firewalls that do not allow HTTP PUT traffic or HTTP DELETE traffic.
To deal with this constraint, you can send these requests in the following two ways.

 * `X-HTTP-Method-Override` Send a PUT request or DELETE request using the header field of the POST request.
 * `_method` Use the URI parameter. ex) POST /users?...&_method=PUT

## Aura Router

To receive the request path as a parameter, use Aura Router.

```bash
composer require bear/aura-router-module ^2.0
```

Install `AuraRouterModule` with the path of the router script.

```php?start_inline
use BEAR\Package\AbstractAppModule;
use BEAR\Package\Provide\Router\AuraRouterModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new AuraRouterModule($appDir . '/var/conf/aura.route.php'));
    }
}
```

Delete cached DI files to activate new router.

```
rm -rf var/tmp/*
```

### Router Script

Router scripts set routes for `Map` objects passed globally.
You do not need to specify a method for routing.
The first argument specifies the path as the root name and the second argument specifies the path containing the place folder of the named token.

`var/conf/aura.route.php`

```php
<?php
/* @var \Aura\Router\Map $map */
$map->route('/blog', '/blog/{id}');
$map->route('/user', '/user/{name}')->tokens(['name' => '[a-z]+']);
$map->route('/blog/comment', '/blog/{id}/comment');
```

 * In the first line, accessing `/blog/bear` will be accessed as `page://self/blog?id=bear`.
(= `Blog` class's` onGet($id)` method with the value `$id`=`bear`.)
 * `token` is used to restrict parameters with regular expressions.
 * `/blog/{id}/comment` to route `Blog\Comment` class.
  
### Preferred router

If it is not routed by the Aura router, a web router will be used.
In other words, it is OK to prepare the router script only for the URI that passes the parameters in the path.

### Parameter

`Aura router` have various methods to obtain parameters from the path.

### Custom Placeholder Token Matching

The script below routes only when `{date}` is in the proper format.

```php?start_inline
$map->route('/calendar/from', '/calendar/from/{date}')
    ->tokens([
        'date' => function ($date, $route, $request) {
            try {
                new \DateTime($date);
                return true;
            } catch(\Exception $e) {
                return false;
            }
        }
    ]);
```

### Optional Placeholder Tokens

Sometimes it is useful to have a route with optional placeholder tokens for attributes. None, some, or all of the optional values may be present, and the route will still match.

To specify optional attributes, use the notation {/attribute1,attribute2,attribute3} in the path. For example:

ex）
```php?start_inline
$map->route('archive', '/archive{/year,month,day}')
    ->tokens([
        'year' => '\d{4}',
        'month' => '\d{2}',
        'day' => '\d{2}',
    ]);
```

Please note that there is the first slash **inside** of the place holder.
Then all the paths below are routed to 'archive' and the value of the parameter is appended.


- `/archive            : ['year' => null,   'month' => null, 'day' => null]`
- `/archive/1979       : ['year' => '1979', 'month' => null, 'day' => null]`
- `/archive/1979/11    : ['year' => '1979', 'month' => '11', 'day' => null]`
- `/archive/1979/11/07 : ['year' => '1979', 'month' => '11', 'day' => '07']`

Optional parameters are **options in the order of**. In other words, you can not specify "day" without "month".

### Wildcard Attributes

Sometimes it is useful to allow the trailing part of the path be anything at all. To allow arbitrary trailing path segments on a route, call the wildcard() method. This will let you specify the attribute name under which the arbitrary trailing values will be stored.

```php?start_inline
$map->route('wild', '/wild')
    ->wildcard('card');
```

All slash-separated path segments after the {id} will be captured as an array in the in wildcard attribute. For example:

- `/wild             : ['card' => []]`
- `/wild/foo         : ['card' => ['foo']]`
- `/wild/foo/bar     : ['card' => ['foo', 'bar']]`
- `/wild/foo/bar/baz : ['card' => ['foo', 'bar', 'baz']]`

For other advanced routes, please refer to Aura Router's [defining-routes](https://github.com/auraphp/Aura.Router/blob/3.x/docs/defining-routes.md).

## Generating Paths From Routes

You can generate a URI from the name of the route and the value of the parameter.

```php?start_inline
use BEAR\Sunday\Extension\Router\RouterInterface;

class Index extends ResourceObject
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onGet(): static
    {
        $userLink = $this->router->generate('/user', ['name' => 'bear']);
        // '/user/bear'
```

### Request Method

It is not necessary to specify a request method.

### Request Header

Normally request headers are not passed to Aura.Router, but installing `RequestHeaderModule` allows Aura.Router to match using headers.

```php
$this->install(new RequestHeaderModule());
```

## Custom Router Component

Implement [RouterInterface](https://github.com/bearsunday/BEAR.Sunday/blob/1.x/src/Extension/Router/RouterInterface.php) with by referring to [BEAR.AuraRouterModule](https://github.com/bearsunday/BEAR.AuraRouterModule).

---
*[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/router.md) needs to be proofread by native speaker. *

***

# Production

For BEAR.Sunday's default `prod` binding, the application customizes the module according to each [deployment environment](https://en.wikipedia.org/wiki/Deployment_environment) and performs the binding.

## Default ProdModule

The default `prod` binding binds the following interfaces:

* Error page generation factory
* PSR logger interface
* Local cache
* Distributed cache

See [ProdModule.php](https://github.com/bearsunday/BEAR.Package/blob/1.x/src/Context/ProdModule.php) in BEAR.Package for details.

## Application's ProdModule

Customize the application's `ProdModule` in `src/Module/ProdModule.php` against the default ProdModule. Error pages and distributed caches are particularly important.

```php
<?php
namespace MyVendor\Todo\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\QueryRepository\CacheVersionModule;
use BEAR\Resource\Module\OptionsMethodModule;
use BEAR\Package\AbstractAppModule;

class ProdModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new PackageProdModule);       // Default prod settings
        $this->override(new OptionsMethodModule);    // Enable OPTIONS method in production as well
        $this->install(new CacheVersionModule('1')); // Specify resource cache version

        // Custom error page
        $this->bind(ErrorPageFactoryInterface::class)->to(MyErrorPageFactory::class);
    }
}
```

## Cache

There are two types of caches: a local cache and a distributed cache that is shared between multiple web servers.
Both caches default to [PhpFileCache](https://www.doctrine-project.org/projects/doctrine-cache/en/1.10/index.html#phpfilecache).

### Local Cache

The local cache is used for caches that do not change after deployment, such as annotations, while the distributed cache is used to store resource states.

### Distributed Cache

To provide services with two or more web servers, a distributed cache configuration is required.
Modules for each of the popular [memcached](http://php.net/manual/en/book.memcached.php) and [Redis](https://redis.io) cache engines are provided.


### Memcached

```php
<?php
namespace BEAR\HelloWorld\Module;

use BEAR\QueryRepository\StorageMemcachedModule;
use BEAR\Resource\Module\ProdLoggerModule;
use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\Package\AbstractAppModule;
use Ray\Di\Scope;

class ProdModule extends AbstractModule
{
    protected function configure()
    {
        // memcache
        // {host}:{port}:{weight},...
        $memcachedServers = 'mem1.domain.com:11211:33,mem2.domain.com:11211:67';
        $this->install(new StorageMemcachedModule($memcachedServers));

        // Install Prod logger
        $this->install(new ProdLoggerModule);
        // Install default ProdModule
        $this->install(new PackageProdModule);
    }
}
```

### Redis


```php?start_inline
// redis
$redisServer = 'localhost:6379'; // {host}:{port}
$this->install(new StorageRedisModule($redisServer));
```

In addition to simply updating the cache by TTL for storing resource states, it is also possible to operate (CQRS) as a persistent storage that does not disappear after the TTL time.
In that case, you need to perform persistent processing with `Redis` or prepare your own storage adapter for other KVS such as Cassandra.

### Specifying Cache Time

To change the default TTL, install `StorageExpiryModule`.

```php?start_inline
// Cache time
$short = 60;
$medium = 3600;
$long = 24 * 3600;
$this->install(new StorageExpiryModule($short, $medium, $long));
```
### Specifying Cache Version

Change the cache version when the resource schema changes and compatibility is lost. This is especially important for CQRS operation that does not disappear over TTL time.

```
$this->install(new CacheVersionModule($cacheVersion));
```

To discard the resource cache every time you deploy, it is convenient to assign a time or random value to `$cacheVersion` so that no change is required.

## Logging

`ProdLoggerModule` is a resource execution log module for production. When installed, it logs requests other than GET to the logger bound to `Psr\Log\LoggerInterface`.
If you want to log on a specific resource or specific state, bind a custom log to [BEAR\Resource\LoggerInterface](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/LoggerInterface.php).

```php
use BEAR\Resource\LoggerInterface;
use Ray\Di\AbstractModule;

final class MyProdLoggerModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(LoggerInterface::class)->to(MyProdLogger::class);
    }
}
```

The `__invoke` method of [LoggerInterface](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/LoggerInterface.php) passes the resource URI and resource state as a `ResourceObject` object, so log the necessary parts based on its contents.
Refer to the [existing implementation ProdLogger](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/ProdLogger.php) for creation.

## Deployment

### ⚠️ Avoid Overwriting Updates

#### When deploying to a server

* Overwriting a running project folder with `rsync` or similar poses a risk of inconsistency with caches and on-demand generated files, and can exceed capacity on high-load sites.
  Set up in a separate directory for safety and switch if the setup is successful.
* You can use the [BEAR.Sunday recipe](https://github.com/bearsunday/deploy) of [Deployer](http://deployer.org/).

#### When deploying to the cloud

* It is recommended to incorporate compilation into CI as the compiler outputs exit code 1 when it finds dependency issues and 0 when compilation succeeds.

### Compilation Recommended

When setting up, you can **warm up** the project using the `vendor/bin/bear.compile` script.
The compile script creates all static cache files such as dynamically created files for DI/AOP and annotations in advance, and outputs an optimized autoload.php file and preload.php.

* If you compile, the possibility of DI errors at runtime is extremely low because injection is performed in all classes.
* The contents included in `.env` are incorporated into the PHP file, so `.env` can be deleted after compilation.

When compiling multiple contexts (ex. api-app, html-app) in one application, such as when performing content negotiation, it is necessary to evacuate the files.

```
mv autoload.php api.autoload.php  
```

Edit `composer.json` to change the content of `composer compile`.

### autoload.php

An optimized autoload.php file is output to `{project_path}/autoload.php`.
It is much faster than `vendor/autoload.php` output by `composer dumpa-autoload --optimize`.

Note: If you use `preload.php`, most of the classes used are loaded at startup, so the compiled `autoload.php` is not necessary. Please use `vendor/autload.php` generated by Composer.

### preload.php

An optimized preload.php file is output to `{project_path}/preload.php`.
To enable preloading, you need to specify [opcache.preload](https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.preload) and [opcache.preload](https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.preload-user) in php.ini. It is a feature supported in PHP 7.4, but it is unstable in the initial versions of `7.4`. Let's use the latest version of `7.4.4` or higher.

Example)

```
opcache.preload=/path/to/project/preload.php
opcache.preload_user=www-data
```

Note: Please refer to the [benchmark](https://github.com/bearsunday/BEAR.HelloworldBenchmark/wiki/Intel-Core-i5-3.8-GHz-iMac-(Retina-5K,-27-inch,-2017)---PHP-7.4.4) for performance benchmarks.


### .compile.php

When there are classes that cannot be generated in a non-production environment (for example, a ResourceObject that requires successful authentication to complete injection), you can compile them by describing dummy class loading in the root `.compile.php` file, which is only loaded during compilation.

.compile.php

Example) If there is an AuthProvider that throws an exception when authentication cannot be obtained in the constructor, you can create an empty class as follows and load it in .compile.php:

/tests/Null/AuthProvider.php
```php
<?php
class AuthProvider 
{  // Only for instantiation, so implementation is not required
}
```

.compile.php
```php
<?php
require __DIR__ . '/tests/Null/AuthProvider.php'; // Always-generatable Null object
$_SERVER[__REQUIRED_KEY__] = 'fake'; // For cases where errors occur without specific environment variables
```

This allows you to avoid exceptions and perform compilation. Additionally, since Symfony's cache component connects to the cache engine in the constructor, it's good to load a dummy adapter during compilation like this:

tests/Null/RedisAdapter.php
```php
namespace Ray\PsrCacheModule;

use Ray\Di\ProviderInterface;
use Serializable;
use Symfony\Component\Cache\Adapter\RedisAdapter as OriginAdapter;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

class RedisAdapter extends OriginAdapter implements Serializable
{
    use SerializableTrait;
    
    public function __construct(ProviderInterface $redisProvider, string $namespace = '', int $defaultLifetime = 0, ?MarshallerInterface $marshaller = null)
    {
    　　// do nothing
    }
}
```
### module.dot

When you compile, a "dot file" is output, so you can convert it to an image file with [graphviz](https://graphviz.org/) or use [GraphvizOnline](https://dreampuf.github.io/GraphvizOnline/) to display the object graph.
Also, please see the [object graph](/images/screen/skeleton.svg) of the skeleton.

```php
dot -T svn module.dot > module.svg
```

## Bootstrap Performance Tuning

[immutable_cache](https://pecl.php.net/package/immutable_cache) is a PECL package for caching immutable values in shared memory. It is based on APCu but is faster than APCu because it stores immutable values such as PHP objects and arrays in shared memory. Additionally, installing PECL's [Igbinary](https://www.php.net/manual/en/book.igbinary.php) with either APCu or immutable_cache can reduce memory usage and further improve performance.

Currently, there are no dedicated cache adapters available. Please refer to [ImmutableBootstrap](https://github.com/koriym/BEAR.Hello/commit/507d1ee3ed514686be2d786cdaae1ba8bed63cc4) to create and call a dedicated Bootstrap. This allows you to minimize initialization costs and achieve maximum performance.

### php.ini

```
// Extensions
extension="apcu.so"
extension="immutable_cache.so" 
extension="igbinary.so"

// Specifying serializer
apc.serializer=igbinary
immutable_cache.serializer=igbinary
```
`````

----

***

# Import

BEAR applications can cooperate with multiple BEAR applications into a single system without having to be microservices. It is also easy to use BEAR resources from other applications.

## Composer Install

Install the BEAR application you want to use as a composer package.

composer.json
```json
{
  "require": {
    "bear/package": "^1.13",
    "my-vendor/weekday": "dev-master"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/bearsunday/tutorial1.git"
    }
  ]
}
```

Requires `bear/package ^1.13`.

## Module Install

Install other applications with `ImportAppModule`, specifying the hostname, application name (namespace) and context to import.

```diff
+use BEAR\Package\Module\ImportAppModule;
+use BEAR\Package\Module\Import\ImportApp;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        // ...
+        $this->install(new ImportAppModule([
+            new ImportApp('foo', 'MyVendor\Weekday', 'prod-app')
+        ]));
        $this->install(new PackageModule());
    }
}
```

## Request

The imported resource will be used with the specified host name.

```php
class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(string $name = 'BEAR.Sunday'): static
    {
        $weekday = $this->resource->get('app://foo/weekday?year=2022&month=1&day=1');
        $this->body = [
            'greeting' => 'Hello ' . $name,
            'weekday' => $weekday
        ];

        return $this;
    }
}
````

You can also use `#[Embed]` and `#[Link]` in the same way.

## Requests from other systems

It is easy to use BEAR resources from other frameworks or CMS.

Install it as a package in the same way, and use `Injector::getInstance` to get the resource client of the application you require and request it.

```php

use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;

$resource = Injector::getInstance(
    'MyVendor\Weekday',
    'prod-api-app',
    dirname(__DIR__) . '/vendor/my-vendor/weekday'
)->getInstance(ResourceInterface::class);
$weekdday = $resource->get('/weekday', ['year' => '2022', 'month' => '1', 'day' => 1]);

echo $weekdday->body['weekday'] . PHP_EOL;
```
## Environment variables

Environment variables are global. Care should be taken to prefix them to avoid conflicts between applications. Instead of using `.env` files, the application to be imported will get the shell environment variables just like in production.

## System Boundary

It is similar to microservices in that a large application can be built as a collection of multiple smaller applications, but without the disadvantages of microservices such as increased infrastructure overhead. It also has clearer component independence and boundaries than modular monoliths.

The code for this page can be found at [bearsunday/example-app-import](https://github.com/bearsunday/example-import-app/commits/master).

## Multilingual Framework

Using [BEAR.Thrift](https://github.com/bearsunday/BEAR.Thrift), you can access resources from other languages, different versions of PHP, or BEAR applications using Apache Thrift. [Apache Thrift](https://thrift.apache.org/) is a framework that enables efficient communication between different languages.

***

# Database

The following modules are available for database use, with different problem solving methods. They are all independent libraries for SQL based on [PDO](https://www.php.net/manual/ja/intro.pdo.php).

* ExtendedPdo with PDO extended ([Aura.sql](https://github.com/auraphp/Aura.Sql))
* Query Builder ([Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery))
* Binding PHP interface and SQL execution ([Ray.MediaQuery](database_media.html))

Having static SQL in a file[^locator] makes it easier to use and tune with other SQL tools. SqlQuery can dynamically assemble queries, but the rest of the library is for basic static SQL execution. Ray.MediaQuery can also replace parts of the SQL with those assembled by the builder.

[^locator]: [query-locater](https://github.com/koriym/Koriym.QueryLocator) is a library for handling SQL as files, which is useful with Aura.Sql.

## Module

Modules are provided for using the database. They are all independent libraries for SQL.

* [Ray.AuraSqlModule](database_aura.html)
* [Ray.MediaQuery](database_media.html)

`Ray.AuraSqlModule` is a PDO extension [Aura.Sql](https://github.com/auraphp/Aura.Sql) and a query builder [Aura.SqlQuery](https://github.com/auraphp/) SqlQuery, plus a low-level module that provides pagination functionality.
`Ray.MediaQuery` is a high-performance DB access framework that generates and injects SQL execution objects from user-provided interfaces and SQL [^doma] .

[^doma]: The mechanism is similar to Java's DB access framework [Doma](https://doma.readthedocs.io/en/latest/basic/#examples).

## Other

* [DBAL](database_dbal.html)
* [CakeDb](database_cake.html)
* [Ray.QueryModule](https://github.com/ray-di/Ray.QueryModule/blob/1.x/README.md)

`DBAL` is Doctrine and `CakeDB` is CakePHP's DB library. `Ray.QueryModule` is an earlier library of Ray.MediaQuery that converts SQL to anonymous functions.

----

***

# Validation

 * You can define resource APIs in the JSON schema.
 * You can separate the validation code with `@Valid`, `@OnValidate` annotation.
 * Please see the form for validation by web form.

# JSON Schema

The [JSON Schema](http://json-schema.org/) is the standard for describing and validating JSON objects. `@JsonSchema` and the resource body returned by the method of annotated resource class are validated by JSON schema.


### Install

If you want to validate in all contexts including production, create `AppModule`, if validation is done only during development, create `DevModule` and install within it


```php?start_inline
use BEAR\Resource\Module\JsonSchemaModule; // Add this line
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(
            new JsonSchemaModule(
                $appDir . '/var/json_schema',
                $appDir . '/var/json_validate'
            )
        );  // Add this line
    }
}
```
Create directories for the JSON schema files

```bash
mkdir var/json_schema
mkdir var/json_validate
```

In the `var/json_schema/`, store the JSON schema file which is the specification of the body of the resource, and the `var/json_validate/` stores the JSON schema file for input validation.

### @JsonSchema annotation

Annotate the method of the resource class by adding `@JsonSchema`, then add the `schema` property by specifying the JSON schema file name, which is `user.json` for this purpose.

### schema

src/Resource/App/User.php

```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class User extends ResourceObject
{
    #[JsonSchema('user.json')]
    public function onGet(): static
    {
        $this->body = [
            'firstName' => 'mucha',
            'lastName' => 'alfons',
            'age' => 12
        ];

        return $this;
    }
}
```

We will create a JSON schema named `/var/json_schema/user.json`

```json
{
  "type": "object",
  "properties": {
    "firstName": {
      "type": "string",
      "maxLength": 30,
      "pattern": "[a-z\\d~+-]+"
    },
    "lastName": {
      "type": "string",
      "maxLength": 30,
      "pattern": "[a-z\\d~+-]+"
    }
  },
  "required": ["firstName", "lastName"]
}
```

### key

If the body has an index key, specify it with the key property of the annotation

```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class User extends ResourceObject
{
    #[JsonSchema(key:'user', schema:'user.json')]
    public function onGet()
    {
        $this->body = [
            'user' => [
                'firstName' => 'mucha',
                'lastName' => 'alfons',
                'age' => 12
            ]
        ];        

        return $this;
    }
}
```

### params

The `params` property specifies the JSON schema file name for the argument validation


```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class Todo extends ResourceObject
{
    #[JsonSchema(key:'user', schema:'user.json', params:'todo.post.json')]
    public function onPost(string $title)
```

We place the JSON schema file

**/var/json_validate/todo.post.json**

```json
{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "title": "/todo POST request validation",
  "properties": {
    "title": {
      "type": "string",
      "minLength": 1,
      "maxLength": 40
    }
}

```

By constantly verifying in a standardized way instead of proprietary documentation, the specification is **reliable and understandable** to both humans and machines.

### target

To apply schema validation to the representation of the resource object (the rendered result) rather than to the body of the ResourceObject, specify the option `target='view'`.

```php
#[JsonSchema(schema: 'user.json', target: 'view')]
```

### Related Links

 * [Example](http://json-schema.org/examples.html)
 * [Understanding JSON Schema](https://spacetelescope.github.io/understanding-json-schema/)
 * [JSON Schema Generator](https://jsonschema.net/#/editor)

## @Valid annotation

The `@Valid` annotation is a validation for input.
You can set up validation as AOP for your method.
By separating validation logic from the method, the code will be readable and testable.

Validation libraries are available such as [Aura.Filter](https://github.com/auraphp/Aura.Filter), [Respect\Validation](https://github.com/Respect/Validation), and [PHP Standard Filter](http://php.net/manual/en/book.filter.php)

### Install

Install `Ray.ValidateModule` via composer.

```bash
composer require ray/validate-module
```

Installing `ValidateModule` in your application module `src/Module/AppModule.php`.

```php?start_inline
use Ray\Validation\ValidateModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new ValidateModule);
    }
}
```

### Annotation

There are three annotations `@Valid`, `@OnValidate`, `@OnFailure` for validation.

First of all, annotate the method that you want to validate with `@Valid`

```php?start_inline
use Ray\Validation\Annotation\Valid;

class News
{
    /**
     * @Valid
     */
    public function createUser($name)
    {
```

Validation will be conducted in the method annotated with `@OnValidate`.

The arguments of the method should be the same as the original method. The method name can be anything.

```php?start_inline
use Ray\Validation\Annotation\OnValidate;

class News
{
    /**
     * @OnValidate
     */
    public function onValidate($name)
    {
        $validation = new Validation;
        if (! is_string($name)) {
            $validation->addError('name', 'name should be string');
        }

        return $validation;
    }
```

Add validations to your elements by `addError()` with the `element name` and` error message` as parameters, then return the validation object.

When validation fails, the exception `Ray\Validation\Exception\InvalidArgumentException` will be thrown,
but if you have a method annotated with the `@OnFailure`, it will be called, instead of throwing an exception

```php?start_inline
use Ray\Validation\Annotation\OnFailure;

class News
{
    /**
     * @OnFailure
     */
    public function onFailure(FailureInterface $failure)
    {
        // original parameters
        list($this->defaultName) = $failure->getInvocation()->getArguments();

        // errors
        foreach ($failure->getMessages() as $name => $messages) {
            foreach ($messages as $message) {
                echo "Input '{$name}': {$message}" . PHP_EOL;
            }
        }
    }
```

In the method annotated with `@OnFailure`, you can access the validated messages with `$failure->getMessages()`
and also you can get the object of the original method with `$failure->getInvocation()`.

### Various validation

If you want to have different validations for a class, you can specify the name of the validation like below

```php?start_inline
use Ray\Validation\Annotation\Valid;
use Ray\Validation\Annotation\OnValidate;
use Ray\Validation\Annotation\OnFailure;

class News
{
    /**
     * @Valid("foo")
     */
    public function fooAction($name, $address, $zip)
    {

    /**
     * @OnValidate("foo")
     */
    public function onValidateFoo($name, $address, $zip)
    {

    /**
     * @OnFailure("foo")
     */
    public function onFailureFoo(FailureInterface $failure)
    {
```

### Other validation

If you need to implement complex validation, you can have another class for validation and inject it.
And then call in the method annotated with the `onValidate`.
You can also change your validation behavior by context with DI.

***

# Command Line Interface (CLI)

BEAR.Sunday's Resource Oriented Architecture (ROA) represents all application functionality as URI-addressable resources. This approach allows resources to be accessed through various means, not just through the web.

```bash
$ php bin/page.php '/greeting?name=World&lang=fr'
{
    "greeting": "Bonjour, World",
    "lang": "fr"
}
```

BEAR.Cli is a tool that converts these resources into native CLI commands and makes them distributable via Homebrew, which uses formula scripts to define installation procedures.

```bash
$ greet -n "World" -l fr
Bonjour, World
```

You can reuse existing application resources as standard CLI tools without writing additional code. Through Homebrew distribution, users can utilize these tools like any other command-line tool, without needing to know they're powered by PHP or BEAR.Sunday.

## Installation

Install using Composer:

```bash
composer require bear/cli
```

## Basic Usage

### Adding CLI Attributes to Resources

Add CLI attributes to your resource class to define the command-line interface:

```php
use BEAR\Cli\Attribute\Cli;
use BEAR\Cli\Attribute\Option;

class Greeting extends ResourceObject
{
    #[Cli(
        name: 'greet',
        description: 'Say hello in multiple languages',
        output: 'greeting'
    )]
    public function onGet(
        #[Option(shortName: 'n', description: 'Name to greet')]
        string $name,
        #[Option(shortName: 'l', description: 'Language (en, ja, fr, es)')]
        string $lang = 'en'
    ): static {
        $greeting = match ($lang) {
            'ja' => 'こんにちは',
            'fr' => 'Bonjour',
            'es' => '¡Hola',
            default => 'Hello',
        };
        $this->body = [
            'greeting' => "{$greeting}, {$name}",
            'lang' => $lang
        ];

        return $this;
    }
}
```

### Generating CLI Commands and Formula

To convert a resource into a command, run the following command with your application name (vendor name and project name):

```bash
$ vendor/bin/bear-cli-gen 'MyVendor\MyProject'
# Generated files:
#   bin/cli/greet         # CLI command
#   var/homebrew/greet.rb # Homebrew formula
```

Note: Homebrew formula is generated only when a GitHub repository is configured.

## Command Usage

The generated command provides standard CLI features such as:

### Displaying Help

```bash
$ greet --help
Say hello in multiple languages

Usage: greet [options]

Options:
  --name, -n     Name to greet (required)
  --lang, -l     Language (en, ja, fr, es) (default: en)
  --help, -h     Show this help message
  --version, -v  Show version information
  --format       Output format (text|json) (default: text)
```

### Showing Version Information

```bash
$ greet --version
greet version 0.1.0
```

### Basic Usage Examples

```bash
# Basic greeting
$ greet -n "World"
Hello, World

# Specify language
$ greet -n "World" -l ja
こんにちは, World

# Short options
$ greet -n "World" -l fr
Bonjour, World

# Long options
$ greet --name "World" --lang es
¡Hola, World
```

### JSON Output

```bash
$ greet -n "World" -l ja --format json
{
    "greeting": "こんにちは, World",
    "lang": "ja"
}
```

### Output Behavior

CLI command output follows these specifications:

- **Default output**: Displays only the specified field value
- **`--format=json` option**: Displays full JSON response similar to API endpoint
- **Error messages**: Output to standard error (stderr)
- **HTTP status code mapping**: Maps to exit codes (0: success, 1: client error, 2: server error)

## Distribution

Commands created with BEAR.Cli can be distributed via Homebrew.
Formula generation requires the application to be published on GitHub:

### 1. Local Formula Distribution

For testing development versions:

```bash
$ brew install --formula ./var/homebrew/greet.rb
```

### 2. Homebrew Tap Distribution

Method for wide distribution using a public repository:

Note: The file name of the formula and the class name inside it are based on the name of the repository. For example, if the GH repository is `koriym/greet`, then `var/homebrew/greet.rb` will be generated, which contains the `Greet` class. In this case, `greet` will be the name of the tap that is published, but if you want to change it, please change the class name and file name of fomula script.

```bash
$ brew tap your-vendor/greet
$ brew install your-vendor/greet
```

This method is particularly suitable for:

- Open source projects
- Continuous updates provision

#### Testing Development Version

```bash
$ brew install --HEAD ./var/homebrew/greet.rb
```
```bash
$ greet --version
greet version 0.1.0
```

#### Stable Release

1. Create a tag:
```bash
$ git tag -a v0.1.0 -m "Initial stable release"
$ git push origin v0.1.0
```

2. Update formula:
```diff
 class Greet < Formula
+  desc "Your CLI tool description"
+  homepage "https://github.com/your-vendor/greet"
+  url "https://github.com/your-vendor/greet/archive/refs/tags/v0.1.0.tar.gz"
+  sha256 "..." # Add hash value obtained from the command below
+  version "0.1.0"
   head "https://github.com/your-vendor/greet.git", branch: "main"
   
   depends_on "php@8.1"
   depends_on "composer"
 end
```

You can add dependencies like databases to the formula as needed. However, it's recommended to handle database setup and other environment configuration in the `bin/setup` script.

3. Get SHA256 hash:
```bash
# Download tarball from GitHub and calculate hash
$ curl -sL https://github.com/your-vendor/greet/archive/refs/tags/v0.1.0.tar.gz | shasum -a 256
```

4. Create Homebrew tap:
   Create a repository using [GitHub CLI(gh)](https://cli.github.com/) or [github.com/new](https://github.com/new). The public repository name must start with `homebrew-`, for example `homebrew-greet`:
```bash
$ gh auth login
$ gh repo create your-vendor/homebrew-greet --public --clone
# Or create and clone repository using the web interface
$ cd homebrew-greet
```

5. Place and publish formula:
```bash
$ cp /path/to/project/var/homebrew/greet.rb .
$ git add greet.rb
$ git commit -m "Add formula for greet command"
$ git push
```

6. Installation and distribution:
   End users can start using the tool with just these commands. PHP environment and dependency package installation are handled automatically, so users don't need to worry about environment setup:
```bash
$ brew tap your-vendor/greet    # homebrew- prefix can be omitted
$ brew install your-vendor/greet
# Ready to use immediately
$ greet --version
greet version 0.1.0
```

## Formula Customization

You can edit the formula using the `brew edit` command as needed:

```bash
$ brew edit your-vendor/greet
```

```ruby
class Greet < Formula
  desc "Your CLI tool description"
  homepage "https://github.com/your-vendor/greet"
  url "https://github.com/your-vendor/greet/archive/refs/tags/v0.1.0.tar.gz"
  sha256 "..." # tgz SHA256
  version "0.1.0"
  
  depends_on "php@8.4"  # Specify PHP version
  depends_on "composer"

  # Add if required by the application
  # depends_on "mysql"
  # depends_on "redis"
end
```

## Clean Architecture

BEAR.Cli demonstrates the strengths of both Resource Oriented Architecture (ROA) and Clean Architecture. Following Clean Architecture's principle that "UI is a detail," you can add CLI as a new adapter alongside the web interface for the same resource.

Furthermore, BEAR.Cli supports not only command creation but also distribution and updates through Homebrew. This allows end users to start using tools with a single command, treating them as native UNIX commands without awareness of PHP or BEAR.Sunday.

Additionally, CLI tools can be version-controlled and updated independently from the application repository. This means they can maintain stability and continuous updates as command-line tools without being affected by API evolution. This represents a new form of API delivery, realized through the combination of Resource Oriented Architecture and Clean Architecture.

***

# HTML

The following template engines are available for HTML representation.

* [Twig v1](html-twig-v1.html)
* [Twig v2](html-twig-v2.html)
* [Qiq](html-qiq.html)

## Twig vs Qiq

[Twig](https://twig.symfony.com) was first released in 2009 and has a large user base. [Qiq](https://qiqphp.github.io) is a new template engine released in 2021.

Twig uses implicit escaping by default and has custom syntax for control structures. In contrast, Qiq requires explicit escaping and uses PHP syntax as the base template language. Twig has a large codebase and rich features, while Qiq is compact and simple. (Using pure PHP syntax in Qiq makes it IDE and static analysis-friendly, although it may be redundant.)

### Syntax Comparison

PHP
```php
<?= htmlspecialchars($var, ENT_QUOTES|ENT_DISALLOWED, 'utf-8') ?>
<?= htmlspecialchars(helper($var, ENT_QUOTES|ENT_DISALLOWED, 'utf-8')) ?>
<?php foreach ($users => $user): ?>
 * <?= $user->name; ?>
<?php endforeach; ?>
```

Twig

```
{% raw %}{{ var | raw }}
{{ var }}
{{ var | helper }}
{% for user in users %}
  * {{ user.name }}
{% endfor %}{% endraw %}
```

Qiq

```
{% raw %}{{% var }}
{{h $var }}
{{h helper($var) }}
{{ foreach($users => $user) }}
  * {{h $user->name }}
{{ endforeach }}

{{ var }} // Not displayed {% endraw %}
```
```php
<?php /** @var Template $this */ ?>
<?= $this->h($var) ?>
```

## Renderer

The renderer, bound to `RenderInterface` and injected into the ResourceObject, generates the representation of the resource. The resource itself is agnostic about its representation.

Since the renderer is injected per resource, it is possible to use multiple template engines simultaneously.

## Halo UI for Development

During development, you can render a UI element called Halo [^halo] around the rendered resource. Halo provides information about the resource's state, representation, and applied interceptors. It also provides links to open the corresponding resource class or resource template in PHPStorm.

[^halo]: The name is derived from a similar feature in the [Seaside](https://github.com/seasidest/seaside) framework for Smalltalk.

<img src="https://user-images.githubusercontent.com/529021/211504531-37cd4a8d-80b3-4d77-903f-c8f5baf5dc37.png" alt="Halo displays resource state" width="50%">

<link href="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css" rel="stylesheet">

* <span class="glyphicon glyphicon-home" rel="tooltip" title="Home"></span> Halo Home (Border and Tools Display)
* <span class="glyphicon glyphicon-zoom-in" rel="tooltip" title="Status"></span> Resource State
* <span class="glyphicon glyphicon-font" rel="tooltip" title="View"></span> Resource Representation
* <span class="glyphicon glyphicon-info-sign" rel="tooltip" title="Info"></span> Profile

You can try a demo of Halo in the [demo](/docs/demo/halo/).

### Performance Monitoring

Halo also displays performance information about the resource, including execution time, memory usage, and a link to the profiler.

<img src="https://user-images.githubusercontent.com/529021/212373901-fce7b2fd-41b0-478f-9d36-5e2eb3b97d9c.png" alt="Halo displays performance"  width="50%">

### Installation

To enable profiling, you need to install [xhprof](https://www.php.net/manual/en/intro.xhprof.php), which helps identify performance bottlenecks.

```
pecl install xhprof
// Also add 'extension=xhprof.so' to your php.ini file
```

To visualize and graphically display call graphs, you need to install [graphviz](https://graphviz.org/download/).
Example: [Call Graph Demo](/docs/demo/halo/callgraph.svg)

```
// macOS
brew install graphviz

// Windows
// Download and install the installer from the graphviz website

// Linux (Ubuntu)
sudo apt-get install graphviz
```

In your application, create a Dev context module and install the `HaloModule`.

```php
class DevModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new HaloModule($this));
    }
}
```

---

***

# Form

Each related function of Web Forms using [Aura.Input](https://github.com/auraphp/Aura.Input) and [Aura.Filter](https://github.com/auraphp/Aura.Filter) is aggregated to a single class so that it is easy to test and change.
We can use a corresponding class for the use of Web Forms and validation.

## Install

Install `ray/web-form-module` via composer to add form using Aura.Input

```bash
composer require ray/web-form-module
```

Install `AuraInputModule` in our application module `src/Module/AppModule.php`

```php?start_inline
use BEAR\Package\AbstractAppModule;
use Ray\WebFormModule\WebFormModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new AuraInputModule);
    }
}
```

##  Web Form

Create **a form class** that defines the registration and the rules of form elements, then bind it to a method using `@FormValidation` annotation.
The method runs only when the sent data is validated.

```php?start_inline
use Ray\WebFormModule\AbstractForm;
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractForm
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // set input fields
        $this->setField('name', 'text')
             ->setAttribs([
                 'id' => 'name'
             ]);
        // set rules and user defined error message
        $this->filter->validate('name')->is('alnum');
        $this->filter->useFieldMessage('name', 'Name must be alphabetic only.');
    }
}
```

We can register the input elements in the `init()` method of the form class and apply the rules of validation and sanitation.
Please refer to [Rules To Validate Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/validate.md) of Aura.Filter with respect to validation rules, and [Rules To Sanitize Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/sanitize.md) with respect to sanitize rules.

We validate an associative array of the argument of the method.
If we want to change the input, we can set the values by implementing `submit()` method of `SubmitInterface` interface.

## @FormValidation Annotation

Annotate the method that we want to validate with the `@FormValidation`, so that the validation is done in the form object specified by the `form` property before execution.
When validation fails, the method with the `ValidationFailed` suffix is called.

```php?start_inline
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\WebFormModule\Annotation\FormValidation;
use Ray\WebFormModule\FormInterface;

class MyController
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @Inject
     * @Named("contact_form")
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @FormValidation
     * // or
     * @FormValidation(form="form", onFailure="onPostValidationFailed")
     */
    public function onPost($name, $age)
    {
        // validation success
    }

    public function onPostValidationFailed($name, $age)
    {
        // validation failed
    }
}
```

We can explicitly specify the name and the method by changing the `form` property of `@FormValidation` annotation or the `onValidationFailed` property.

The submit parameters will be passed to the `onPostValidationFailed` method.

## View

Specify the element name to get the `input` elements and error messages

```php?start_inline
  $form->input('name'); // <input id="name" type="text" name="name" size="20" maxlength="20" />
  $form->error('name'); // "Please enter a double-byte characters or letters in the name." or blank
```

The same applies to Twig template

```php?start_inline
{% raw %}{{ form.input('name') }}
{{ form.error('name') }}{% endraw %}
```

## CSRF Protections

We can add a CSRF(Cross site request forgeries) object to the form to apply CSRF protections.

```php?start_inline
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractAuraForm
{
    use SetAntiCsrfTrait;
```

In order to increase the security level, add a custom CSRF class that contains the user authentication to the form class.
Please refer to the [Applying CSRF Protections](https://github.com/auraphp/Aura.Input#applying-csrf-protections) of Aura.Input for more information.

## @InputValidation annotation

If we annotate the method with `@InputValidation` instead of `@FormValidation`, the exception `Ray\WebFormModule\Exception\ValidationException` is thrown when validation fails.
For convenience, HTML representation is not used in this case.

When we `echo` the `error` property of the caught exception, we can see the representation of the media type [application/vnd.error+json](https://github.com/blongden/vnd.error).

```php?start_inline
http_response_code(400);
echo $e->error;

// {
//     "message": "Validation failed",
//     "path": "/path/to/error",
//     "validation_messages": {
//         "name": [
//             "Please enter a double-byte characters or letters in the name."
//         ]
//     }
// }
```

We can add the necessary information to `vnd.error+json` using `@VndError` annotation.

```php?start_inline
/**
 * @FormValidation(form="contactForm")
 * @VndError(
 *   message="foo validation failed",
 *   logref="a1000", path="/path/to/error",
 *   href={"_self"="/path/to/error", "help"="/path/to/help"}
 * )
 */
 public function onPost()
```

## FormVndErrorModule

If we install `Ray\WebFormModule\FormVndErrorModule`, the method annotated with `@FormValidation`
will throw an exception in the same way as the method annotated with `@InputValidation`.
We can use the page resources as API.

```php?start_inline
class FooModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new AuraInputModule);
        $this->override(new FormVndErrorModule);
    }
}
```

## Demo

Try the demo app [MyVendor.ContactForm](https://github.com/bearsunday/MyVendor.ContactForm) to get an idea on how forms such as
a confirmation form and multiple forms in a single page work.

***

# PSR-7

You can get server side request information using [PSR7 HTTP message interface](https://www.php-fig.org/psr/psr-7/). Also, you can run BEAR.Sunday application as PSR 7 middleware.


## HTTP Request

PHP has `Superglobals` such as `$_SERVER` and `$_COOKIE`, but instead of using them it receives server side request information using the PSR-7 HTTP message interface.


### ServerRequest （general）

````php
class Index extends ResourceObject
{
    public function __construct(ServerRequestInterface $serverRequest)
    {
        // retrieve cookies
        $cookie = $serverRequest->getCookieParams(); // $_COOKIE
    }
}
````

### Upload Files

````php

use Psr\Http\Message\UploadedFileInterface;
use Ray\HttpMessage\Annotation\UploadFiles;

class Index extends ResourceObject
{
    /**
     * @UploadFiles
     */
    public function __construct(array $files)
    {
        // retrieve file name
        $file = $files['my-form']['details']['avatar'][0]
        /* @var UploadedFileInterface $file */
        $name = $file->getClientFilename(); // my-avatar3.png
    }
}
````

### URI

````php

use Psr\Http\Message\UriInterface;

class Index extends ResourceObject
{
    public function __construct(UriInterface $uri)
    {
        // retrieve host name
        $host = $uri->getHost();
    }
}
````

## PSR-7

An existing BEAR.Sunday application can work as
a [PSR-7](http://www.php-fig.org/psr/psr-7/) middleware with these easy steps:

1) Add `bear/middleware` package then replace [bootstrap.php](https://github.com/bearsunday/BEAR.Middleware/blob/1.x/bootstrap/bootstrap.php) script.

```bash
composer require bear/middleware
```
```bash
cp vendor/bear/middleware/bootstrap/bootstrap.php bootstrap/bootstrap.php
```

2) Replace `__PACKAGE__\__VENDOR__` in bootstrap.php to application namespace.

Stat the server.

```bash
php -S 127.0.0.1:8080 -t public
```

### Stream

BEAR.Sunday supports HTTP body of a message output in a [stream](http://php.net/manual/ja/intro.stream.php).

In `ResourceObject`, you can mix stream with a normal string. The output is converted to a single stream.
`StreamTransfer` is the default HTTP transfer. Seem more at [Stream Response](http://bearsunday.github.io/manuals/1.0/en/stream.html).

### New Project

You can also create a BEAR.Sunday PSR-7 project with `bear/project` from scratch.

```
composer create-project bear/project my-psr7-project
cd my-psr7-project/
php -S 127.0.0.1:8080 -t public
```

### PSR-7 middleware

 * [oscarotero/psr7-middlewares](https://github.com/oscarotero/psr7-middlewares)

***

# Stream Response

Normally, resources are rendered by renderers into one string and finally `echo`ed out, but then you cannot output content whose size exceeds the memory limit of PHP. With `StreamRenderer` you can stream HTTP output and you can output large size content while keeping memory consumption low. Stream output can also be used in coexistence with existing renderers.

## Change Transferer and Renderer

Use the [StreamTransferInject](https://github.com/bearsunday/BEAR.Streamer/blob/1.x/src/StreamTransferInject.php) trait on the page to render and respond to the stream output. In the example of this download page, since `$body` is made to be a resource variable of the stream, the injected renderer is ignored and the resource is streamed.

```php?start_inline
use BEAR\Streamer\StreamTransferInject;

class Download extends ResourceObject
{
    use StreamTransferInject;

    public $headers = [
        'Content-Type' => 'image/jpeg',
        'Content-Disposition' => 'attachment; filename="image.jpg"'
    ];

    public function onGet(): static
    {
        $fp = fopen(__DIR__ . '/BEAR.jpg', 'r');
        $this->body = $fp;

        return $this;
    }
}
```

## With Renderers

Stream output can coexist with conventional renderers. Normally, Twig renderers and JSON renderers generate character strings, but when a stream is assigned to a part of it, the whole is output as a stream.

This is an example of assigning a `string` and a `resource` variable to the Twig template and generating a page of inline image.

Template

```twig
<!DOCTYPE html>
<html lang="en">
<body>
<p>Hello, {% raw  %}{{ name }}{% endraw %}</p>
<img src="data:image/jpg;base64,{% raw  %}{{ image }}{% endraw %}">
</body>
</html>
```

`name` assigns the string as usual, but assigns the resource variable of the image file's pointer resource to` image` with the `base64-encode` filter.

```php?start_inline
class Image extends ResourceObject
{
    use StreamTransferInject;

    public function onGet(string $name = 'inline image'): static
    {
        $fp = fopen(__DIR__ . '/image.jpg', 'r');
        stream_filter_append($fp, 'convert.base64-encode'); // image base64 format
        $this->body = [
            'name' => $name,
            'image' => $fp
        ];

        return $this;
    }
}
```

If you want to further control streaming such as streaming bandwidth and timing control, uploading to the cloud, etc use [StreamResponder](https://github.com/bearsunday/BEAR.Streamer/blob/1.x/src /StreamResponder.php ) which is build for it.

The demo is available at [MyVendor.Stream](https://github.com/bearsunday/MyVendor.Stream).


---
*[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/stream.md) needs to be proofread by native speaker.*

***

# Cache

> There are only two hard things in Computer Science: cache invalidation and naming things.
>
> -- Phil Karlton

## Overview

A good caching system fundamentally improves the quality of user experience and reduces resource utilization costs and environmental impact. BEAR.Sunday supports the following caching features in addition to traditional simple TTL-based caching:

* Event-driven cache invalidation
* Cache dependency resolution
* Donut cache and donut hole cache
* CDN control
* Conditional requests

### Distributed Cache Framework

A distributed caching system that follows REST constraints saves not only computational resources but also network resources. BEAR.Sunday provides a caching framework that integrates **server-side caches** (such as Redis and APC handled directly by PHP), **shared caches** (known as content delivery networks - CDNs), and **client-side caches** (cached by web browsers and API clients) with modern CDNs.

<img src="https://user-images.githubusercontent.com/529021/137062427-c733c832-0631-4a43-a6ee-4204e6be007c.png" alt="distributed cache">

## Tag-based Cache Invalidation

<img width="369" alt="dependency graph 2021-10-19 21 38 02" src="https://user-images.githubusercontent.com/529021/137910748-b6e95839-eeb7-4ade-a564-3cdcd5fdc09e.png">

Content caching has dependency issues. If content A depends on content B, and B depends on C, then when C is updated, not only must C's cache and ETag be updated, but also B's cache and ETag (which depends on C), and A's cache and ETag (which depends on B).

BEAR.Sunday solves this problem by having each resource hold the URI of dependent resources as tags. When a resource embedded with `#[Embed]` is modified, the cache and ETag of all related resources are invalidated, and cache regeneration occurs for the next request.

## Donut Cache

<img width="200" alt="donut caching" src="https://user-images.githubusercontent.com/529021/137097856-f9428918-5b76-4c0e-8cea-2472c15d82e9.png">

Donut caching is a partial caching technique for cache optimization. It separates content into cacheable and non-cacheable parts and combines them for output.

For example, consider content containing a non-cacheable resource like "`Welcome to $name`". The non-cacheable (do-not-cache) part is combined with other cacheable parts for output.

<img width="557" alt="image" src="https://user-images.githubusercontent.com/529021/139617102-1f7f436c-a1f4-4c6c-b90b-de24491e4c8c.png">

In this case, since the entire content is dynamic, the whole donut is not cached. Therefore, no ETag is output either.

## Donut Hole Cache

<img width="544" alt="image" src="https://user-images.githubusercontent.com/529021/139617571-31aea99a-533f-4b95-b3f3-6c613407d377.png">

When the donut hole part is cacheable, it can be handled the same way as donut cache. In the example above, a weather forecast resource that changes once per hour is cached and included in the news resource.

In this case, since the donut content as a whole (news) is static, the entire content is also cached and given an ETag. This creates cache dependency. When the donut hole content is updated, the entire cached donut needs to be regenerated.

This dependency resolution happens automatically. To minimize computational resources, the donut part computation is reused. When the hole part (weather resource) is updated, the cache and ETag of the entire content are also automatically updated.

### Recursive Donut

<img width="191" alt="recursive donut 2021-10-19 21 27 06" src="https://user-images.githubusercontent.com/529021/137909083-2c5176f7-edb7-422b-bccc-1db90460fc15.png">

The donut structure is applied recursively. For example, if A contains B and B contains C, when C is modified, A's cache and B's cache are reused except for the modified C part. A's and B's caches and ETags are regenerated, but database access for A and B content retrieval and view rendering are not performed.

The optimized partial cache structure performs content regeneration with minimal cost. Clients don't need to know about the content cache structure.

## Event-Driven Content

Traditionally, CDNs considered content requiring application logic as "dynamic" and therefore not cacheable by CDNs. However, some CDNs like Fastly and Akamai now support immediate or tag-based cache invalidation within seconds, making [this thinking obsolete](https://www.fastly.com/blog/leveraging-your-cdn-cache-uncacheable-content).

BEAR.Sunday dependency resolution works not only server-side but also on shared caches. When AOP detects changes and makes PURGE requests to shared caches, related cache invalidation occurs on shared caches just like server-side.

## Conditional Requests

<img width="468" alt="conditional request" src="https://user-images.githubusercontent.com/529021/137151061-8d7a5605-3aa3-494c-91c5-c1deddd987dd.png">

Content changes are managed by AOP, and content entity tags (ETags) are automatically updated. HTTP conditional requests using ETags not only minimize computational resource usage, but responses returning only `304 Not Modified` also minimize network resource usage.

# Usage

For classes to be cached, use the `#[DonutCache]` attribute for donut cache (when embedded content is not cacheable), and `#[CacheableResponse]` for other cases:

```php
use BEAR\RepositoryModule\Annotation\CacheableResponse;

#[CacheableResponse]
class BlogPosting extends ResourceObject
{
    public $headers = [
        RequestHeader::CACHE_CONTROL => CacheControl::NO_CACHE
    ];

    #[Embed(rel: "comment", src: "page://self/html/comment")]
    public function onGet(int $id = 0): static
    {
        $this->body['article'] = 'hello world';

        return $this;
    }

    public function onDelete(int $id = 0): static
    {
        return $this;
    }
}
```

### recursive donut

<img width="191" alt="recursive donut 2021-10-19 21 27 06" src="https://user-images.githubusercontent.com/529021/137909083-2c5176f7-edb7-422b-bccc-1db90460fc15.png">

The donut structure will be recursively applied.
For example, if A contains B and B contains C and C is modified, A's cache and B's cache will be reused except for the modified C. A's and B's caches and ETags will be regenerated, but DB access to retrieve A's and B's content and rendering of views will not be done.

The optimized structure of the partial cache performs content regeneration with minimal cost. The client does not need to know about the content cache structure.

## Event-driven content

Traditionally, CDNs have believed that content that requires application logic is "dynamic" and therefore cannot be cached by a CDN. However, some CDNs, such as Fastly and Akamai, allow immediate or tag-based cache invalidation within seconds, [this idea is a thing of the past](https://www.fastly.com/blog/leveraging-your-cdn-cache- uncacheable-content).

Sunday dependency resolution is done not only on the server side, but also on the shared cache; when AOP detects a change and makes a PURGE request to the shared cache, the related cache on the shared cache will be invalidated, just like on the server side.

## Conditional request

<img width="468" alt="conditional request" src="https://user-images.githubusercontent.com/529021/137151061-8d7a5605-3aa3-494c-91c5-c1 deddd987dd.png">

Content changes are managed by AOP, and the entity tag (ETag) of the content is automatically updated. conditional requests for HTTP using ETag not only minimize the use of computational resources, but responses that only return `304 Not Modified` also minimize the use of network resources. Conditional HTTP requests using ETag not only minimize the use of computational resources, but also minimize the use of network resources by simply returning `304 Not Modified`.


# Usage

Give the class to be cached the attribute `#[DonutCache]` if it is a donut cache (embedded content is not cacheable) and `#[CacheableResponse]` otherwise.


```php
class Todo extends ResourceObject
{
    #[CacheableResponse]
    public function onPut(int $id = 0, string $todo): static
    {
    }

    #[RefreshCache]
    public function onDelete(int $id = 0): static
    {
    }	
}
```

If you give attributes in either way, all the features introduced in the overview will apply.
Caching is not disabled by time (TTL) by default, assuming event-driven content

Note that with `#[DonutCache]` the whole content will not be cached, but with `#[CacheableResponse]` it will be.

## TTL

TTL is specified with `DonutRepositoryInterface::put()`.
`ttl` is the cache time for non-donut holes, `sMaxAge` is the cache time for CDNs.

```php
use BEAR\RepositoryModule\Annotation\CacheableResponse;

#[CacheableResponse]
class BlogPosting extends ResourceObject
{
    public function __construct(private DonutRepositoryInterface $repository)
    {}

    #[Embed(rel: "comment", src: "page://self/html/comment")]
    public function onGet(): static
    {
        // process ...
        $this->repository->put($this, ttl:10, sMaxAge:100);　

        return $this;
    }
}
```
### Default TTL value

For event-driven content, changes to the content must be reflected immediately in the cache, so the default TTL varies depending on the CDN module installed. Therefore, the default TTL will vary depending on the CDN module installed: indefinitely (1 year) if the CDN supports tag-based disabling of caching, or 10 seconds if it does not.

The expected cache reflection time is immediate for Fastly, a few seconds for Akamai, and 10 seconds for others.

To customize it, bind it by implementing `CdnCacheControlHeaderSetterInterface` with reference to `CdnCacheControlHeader`.

## Cache invalidation

Use the methods of `DonutRepositoryInterface` to manually invalidate the cache.
This will invalidate not only the specified cache, but also the cache of the ETag, any other resources it depends on, and the cache of the ETag on the server side and, if possible, on the CDN.

```php
interface DonutRepositoryInterface
{
    public function purge(AbstractUri $uri): void;
    public function invalidateTags(array $tags): void;
}
```

### Invalidate by URI

```php
// example
$this->repository->purge(new Uri('app://self/blog/comment'));
```

### Disable by tag

```php
$this->repository->invalidateTags(['template_a', 'campaign_b']);
```
### Tag Invalidation in CDN

In order to enable tag-based cache invalidation in CDN, you need to implement and bind `PurgerInterface`.

```php
use BEAR\QueryRepository\PurgerInterface;

interface PurgerInterface
{
    public function __invoke(string $tag): void;
}
```

### Specify dependent tags.

Use the `SURROGATE_KEY` header to specify the key for PURGE. Use a space as a separator for multiple strings.

```php
use BEAR\QueryRepository\Header;

class Foo
{
    public $headers = [
        Header::SURROGATE_KEY => 'template_a campaign_b'
    ];
```

If the cache is invalidated by `template_a` or `campaign_b` tags, Foo's cache and Foo's ETag will be invalidated both server-side and CDN.

### Resource Dependencies.

Use `UriTagInterface` to convert a URI into a dependency tag string.

```php
public function __construct(private UriTagInterface $uriTag)
{}
```
```php
$this->headers[Header::SURROGATE_KEY] = ($this->uriTag)(new Uri('app://self/foo'));
```

This cache will be invalidated both server-side and CDN when `app://self/foo` is modified.

### Make associative array a resource dependency.

```php
// bodyの内容
[
    ['id' => '1', 'name' => 'a'],
    ['id' => '2', 'name' => 'b'],
]
```
If you want to generate a list of dependent URI tags from a `body` associative array like the above, you can specify the URI template with the `fromAssoc()` method.

```php
$this->headers[Header::SURROGATE_KEY] = $this->uriTag->fromAssoc(
    uriTemplate: 'app://self/item{?id}',
    assoc: $this->body
);
```

In the above case, this cache will be invalidated for both server-side and CDN when `app://self/item?id=1` and `app://self/item?id=2` are changed.

## CDN

If you install a module that supports a specific CDN, vendor-specific headers will be output.

```php
$this->install(new FastlyModule())
$this->install(new AkamaiModule())
```

## Multi-CDN

You can also configure a multi-tier CDN and set the TTL according to the role. For example, in this diagram, a multi-functional CDN is placed upstream, and a conventional CDN is placed downstream. Content invalidation is done for the upstream CDN, and the downstream CDN uses it.

<img width="344" alt="multi cdn diagram" src="https://user-images.githubusercontent.com/529021/137098809-ec949a15-8efb-4d03-9808-3be15523ade7.png">


# Response headers

Sunday will automatically do the cache control for the CDN and output the header for the CDN. Client cache control is described in `$header` of ResourceObject depending on the content.

This section is important for security and maintenance purposes.
Make sure to specify the `Cache-Control` in all ResourceObjects.

### Cannot cache

Always specify content that cannot be cached.

```php
ResponseHeader::CACHE_CONTROL => CacheControl::NO_STORE
```

### Conditional requests

Check the server for content changes before using the cache. Server-side content changes will be detected and reflected.

```php
ResponseHeader::CACHE_CONTROL => CacheControl::NO_CACHE
```

### Specify client cache time.

The client is cached on the client. This is the most efficient cache, but server-side content changes will not be reflected at the specified time.

Also, this cache is not used when the browser reloads. The cache is used when a transition is made with the `<a>` tag or when a URL is entered.

```php
ResponseHeader::CACHE_CONTROL => 'max-age=60'
```

If response time is important to you, consider specifying SWR.

```php
ResponseHeader::CACHE_CONTROL => 'max-age=30 stale-while-revalidate=10'
```

In this case, when the max-age of 30 seconds is exceeded, the old cached (stale) response will be returned for up to 10 seconds, as specified in the SWR, until a fresh response is obtained from the origin server. This means that the cache will be updated sometime between 30 and 40 seconds after the last cache update, but every request will be a response from the cache and will be fast.

#### RFC7234 compliant clients

To use the client cache with APIs, use an RFC7234 compliant API client.

* iOS [NSURLCache](https://nshipster.com/nsurlcache/)
* Android [HttpResponseCache](https://developer.android.com/reference/android/net/http/HttpResponseCache)
* PHP [guzzle-cache-middleware](https://github.com/Kevinrob/guzzle-cache-middleware)
* JavaScript(Node) [cacheable-request](https://www.npmjs.com/package/cacheable-request)
* Go [lox/httpcache](https://github.com/lox/httpcache)
* Ruby [faraday-http-cache](https://github.com/plataformatec/faraday-http-cache)
* Python [requests-cache](https://pypi.org/project/requests-cache/)

### private

Specify `private` if you do not want to share the cache with other clients. The cache will be saved only on the client side. In this case, do not specify the cache on the server side.

````php
ResponseHeader::CACHE_CONTROL => 'private, max-age=30'
````

> Even if you use shared cache, you don't need to specify `public` in most cases.

## Cache design

APIs (or content) can be divided into two categories: **Information APIs** (Information APIs) and **Computation APIs** (Computation APIs). The **Computation API** is content that is difficult to reproduce and is truly dynamic, making it unsuitable for caching. The Information API, on the other hand, is an API for content that is essentially static, even if it is read from a DB and processed by PHP.

It analyzes the content in order to apply the appropriate cache.

* Information API or Computation API?
* Dependencies are
* Are the comprehension relationships
* Is the invalidation triggered by an event or TTL?
* Is the event detectable by the application or does it need to be monitored?
* Is the TTL predictable or unpredictable?

Consider making cache design a part of the application design process and make it a specification. It should also contribute to the safety of your project throughout its lifecycle.

### Adaptive TTL

Adaptive TTL is the ability to predict the lifetime of content and correctly tell the client or CDN when it will not be updated by an event during that time. For example, when dealing with a stock API, if it is Friday night, we know that the information will not be updated until the start of trading on Monday. We calculate the number of seconds until that time, specify it as the TTL, and then specify the appropriate TTL when it is time to trade.

The client does not need to request a resource that it knows will not be updated.

## #[Cacheable].

The traditional ##[Cacheable] TTL caching is also supported.

Example: 30 seconds cache on the server side, 30 seconds cache on the client.

The same number of seconds will be cached on the client side since it is specified on the server side.

The same number of seconds will be cached on the client side.
use BEAR\RepositoryModule\Annotation\Cacheable;

#[Cacheable(expirySecond: 30)]]
class CachedResource extends ResourceObject
{
````

Example: Cache the resource on the server and client until the specified expiration date (the date in `$body['expiry_at']`)

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

#[Cacheable(expiryAt: 'expiry_at')]]
class CachedResource extends ResourceObject
{
```.

See the [HTTP Cache](https://bearsunday.github.io/manuals/1.0/ja/http-cache.html) page for more information.

## Conclusion

Web content can be of the information (data) type or the computation (process) type. Although the former is essentially static, it is difficult to treat it as completely static content due to the problems of managing content changes and dependencies, so the cache was invalidated by TTL even though no content changes occurred. Sunday's caching framework treats information type content as static as possible, maximizing the power of the cache.


## Terminology

* [条件付きリクエスト](https://developer.mozilla.org/ja/docs/Web/HTTP/Conditional_requests)
* [ETag (バージョン識別子)](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/ETag)
* [イベントドリブン型コンテンツ](https://www.fastly.com/blog/rise-event-driven-content-or-how-cache-more-edge)
* [ドーナッツキャッシュ / 部分キャッシュ](https://www.infoq.com/jp/news/2011/12/MvcDonutCaching/)
* [サロゲートキー / タグベースの無効化](https://docs.fastly.com/ja/guides/getting-started-with-surrogate-keys)
* ヘッダー
  * [Cache-Control](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/Cache-Control)
  * [CDN-Cache-Control](https://blog.cloudflare.com/cdn-cache-control/)
  * [Vary](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/Vary)
  * [Stale-While-Revalidate (SWR)](https://www.infoq.com/jp/news/2020/12/ux-stale-while-revalidate/)

***

# Swoole

You can execute your BEAR.Sunday application using Swoole directly from the command line. It dramatically improves performance.

## Install

### Swoole Install

See [https://github.com/swoole/swoole-src#%EF%B8%8F-installation](https://github.com/swoole/swoole-src#%EF%B8%8F-installation)

### BEAR.Swoole Install

```bash
composer require bear/swoole ^0.4
```
Place the bootstrap script at `bin/swoole.php`

```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/vendor/bear/swoole/bootstrap.php')(
    'prod-hal-app',       // context
    'MyVendor\MyProject', // application name
    '127.0.0.1',          // IP
    8080                  // port
));
```

## Excute

```
php bin/swoole.php
```
```
Swoole http server is started at http://127.0.0.1:8088
```

## Benchmarking site

See [BEAR.HelloworldBenchmark](https://github.com/bearsunday/BEAR.HelloworldBenchmark)
You can expect x2 to x10 times bootstrap performance boost.

 * [The benchmarking result](https://github.com/bearsunday/BEAR.HelloworldBenchmark/wiki)

[<img src="https://github.com/swoole/swoole-src/raw/master/mascot.png">](https://github.com/swoole/swoole-src)

***

# Types

This page is a placeholder for types documentation.

***

# Test

Proper testing makes software better with continuity. A clean application of BEAR.Sunday is test friendly, with all dependencies injected and crosscutting interests provided in the AOP.

## Run test

Run `vendor/bin/phpunit` or `composer test`.　Other commands are as follows.

```
composer test    // phpunit test
composer tests   // test + sa + cs
composer coverage // test coverage
composer pcov    // test coverage (pcov)
composer sa      // static analysis
composer cs      // coding standards check
composer cs-fix  // coding standards fix
```

## Resource test

**Everything is a resource** - BEAR.Sunday application can be tested with resoure access.

This is a test that tests that `201 (Created)` will be returned by POSTing `['title' => 'test']` to URI `page://self/todo` of `Myvendor\MyProject` application in `html-app` context.

```php
<?php

use BEAR\Resource\ResourceInterface;

class TodoTest extends TestCase
{
    private ResourceInterface $resource;
    
    protected function setUp(): void
    {
        $injector = Injector::getInstance('test-html-app');
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testOnPost(): void
    {
        $page = $this->resource->post('page://self/todo', ['title' => 'test']);
        $this->assertSame(StatusCode::CREATED, $page->code);
    }
}
```

## Test Double

A Test Double is a substitute that replaces a component on which the software test object depends. Test doubles can have the following patterns

* Stub (provides "indirect input" to the test target)
* Mock ( validate "indirect output" from the test target inside a test double)
* Spy (records "indirect output" from the target to be tested)
* Fake (simpler implementation that works closer to the actual object)
* _Dummy_ (necessary to generate the test target but no call is made)

### Test Double Binding

There are two ways to change the bundling for a test. One is to change the bundling across all tests in the context module, and the other is to temporarily change the bundling only for a specific purpose within one test only.

#### Context Module

Create a ``TestModule`` to make the `test` context available in bootstrap.

```php
class TestModule extends AbstractModule
{
    public function configure(): void
    {
        $this->bind(DateTimeInterface::class)->toInstance(new DateTimeImmutable('1970-01-01 00:00:00'));
        $this->bind(Auth::class)->to(FakeAuth::class);    
    }
}
```

Injector with test context.

```php
$injector = Injector::getInstance('test-hal-app', $module);
```

#### Temporary binding change

Temporary bundle changes for a single test specify the bundle to override with `Injector::getOverrideInstance`.

```php
public function testBindFake(): void
{
    $module = new class extends AbstractModule {
        protected function configure(): void
        {
            $this->bind(FooInterface::class)->to(FakeFoo::class);
        }
    }
    $injector = Injector::getOverrideInstance('hal-app', $module);
}
```

### Mock

```php
public function testBindMock(): void
{ 
    $mock = $this->createMock(FooInterface::class);
    // expect that update() will be called once and the parameter will be the string 'something'.
    mock->expects($this->once())
             ->method('update')
             ->with($this->equalTo('something'));
    $module = new class($mock) extends AbstractModule {
        public function __construct(
            private FooInterface $foo
        ){}
        protected function configure(): void
        {
            $this->bind(FooInterface::class)->toInstance($this->foo);
        }
    };
    $injector = Injector::getOverrideInstance('hal-app', $module);
}
```
### spy

Installs a `SpyModule` by specifying the interface or class name of the spy target. [^spy-module] After running the SUT containing the spy target, verify the number of calls and the value of the calls in the spy log.

[^spy-module]: [ray/test-double](https://github.com/ray-di/Ray.TestDouble) must be installed to use SpyModule.

```php
public function testBindSpy(): void
{
    $module = new class extends AbstractModule {
        protected function configure(): void
        {
            $this->install(new SpyModule([FooInterface::class]));
        }
    };
    $injector = Injector::getOverrideInstance('hal-app', $module);
    $resource = $injector->getInstance(ResourceInterface::class);
    // Spy logs of FooInterface objects are logged, whether directly or indirectly.
    $resource->get('/');
    // Spyログの取り出し
    $spyLog = $injector->getInstance(\Ray\TestDouble\LoggerInterface::class);
    // @var array<int, Log> $addLog
    $addLog = $spyLog->getLogs(FooInterface, 'add');   
    $this->assertSame(1, count($addLog), 'Should have received once');
    // Argument validation from SUT
    $this->assertSame([1, 2], $addLog[0]->arguments);
    $this->assertSame(1, $addLog[0]->namedArguments['a']);
}
```

### Dummy

Use [Null Binding](https://ray-di.github.io/manuals/1.0/ja/null_object_binding.html) to bind a null object to an interface.

## Hypermedia Test

Resource testing is an input/output test for each endpoint. Hypermedia tests, on the other hand, test the workflow behavior of how the endpoints are connected.

Workflow tests are inherited from HTTP tests and are tested at both the PHP and HTTP levels in a single code. HTTP testing is done with `curl` and the request/response is logged in a log file.

## Best Practice

 * Test the interface, not the implementation.
 * Create a actual fake class rather than using a mock library.
 * Testing is a specification. Ease of reading rather than ease of coding.

Reference

* [Stop mocking, start testing](https://nedbatchelder.com/blog/201206/tldw_stop_mocking_start_testing.html)
* [Mockists Are Dead](https://www.thoughtworks.com/insights/blog/mockists-are-dead-long-live-classicists)

***

# Examples

This example application is built on the principles described in the [Coding Guide](http://bearsunday.github.io/manuals/1.0/en/coding-guide.html).

## Polidog.Todo

[https://github.com/koriym/Polidog.Todo](https://github.com/koriym/Polidog.Todo)


`Todos` is a basic CRUD application. The DB is accessed using the static　SQL file in the `var/sql` directory. Includes REST API using hyperlinks and testing, as well as form validation tests.

  * [ray/aura-sql-module](https://github.com/ray-di/Ray.AuraSqlModule) - Extended PDO ([Aura.Sql](https://github.com/auraphp/Aura.Sql))
  * [ray/web-form-module](https://github.com/ray-di/Ray.WebFormModule) - Web form ([Aura.Input](https://github.com/auraphp/Aura.Input))
  * [madapaja/twig-module](https://github.com/madapaja/Madapaja.TwigModule) - Twig template engine
  * [koriym/now](https://github.com/koriym/Koriym.Now) - Current datetime
  * [koriym/query-locator](https://github.com/koriym/Koriym.QueryLocator) - SQL locator
  * [koriym/http-constants](https://github.com/koriym/Koriym.HttpConstants) - Contains the values HTTP

## MyVendor.ContactForm

[https://github.com/bearsunday/MyVendor.ContactForm](https://github.com/bearsunday/MyVendor.ContactForm)


It is a sample of various form pages.

  * Minimal form page
  * Multiple forms page
  * Looped input form page
  * Preview form page including checkbox and radio button

***

# Attributes

BEAR.Sunday supports PHP8's [attributes](https://www.php.net/manual/en/language.attributes.overview.php) in addition to the annotations.

**Annotation**
```php?start_inline
/**
 * @Inject
 * @Named('admin')
 */
public function setLogger(LoggerInterface $logger)
```
**Attribute**
```php?start_inline
#[Inject, Named('admin')]
public function setLogger(LoggerInterface $logger)
```

```php?start_inline
#[Embed(rel: 'weather', src: 'app://self/weather{?date}')]
#[Link(rel: 'event', href: 'app://self/event{?news_date}')]
public function onGet(string $date): self
```

## Apply to parameters

While some annotations can only be applied to methods and require the argument names to be specified by name, the
Attributes can be used to decorate arguments directly.

```php?start_inline
public __construct(#[Named('payment')] LoggerInterface $paymentLogger, #[Named('debug')] LoggerInterface $debugLogger)
```

```php?start_inline
public function onGet($id, #[Assisted] DbInterface $db = null)
```

```php?start_inline
public function onGet(#[CookieParam('id')]string $tokenId): void
```

```php?start_inline
public function onGet(#[ResourceParam(uri: 'app://self/login#nickname')] string $nickname = null): static
```
## Compatibility

Attributes and annotations can be mixed in a single project. [^1]
All annotations described in this manual will work when converted to attributes.

## Performance

Although the cost of loading annotations/attributes for production is minimal due to optimization, you can speed up development by declaring that you will only use attribute readers, as follows

```php?start_inline
// tests/bootstap.php 

use Ray\ServiceLocator\ServiceLocator;

ServiceLocator::setReader(new AttributeReader());
```

```php?start_inline
// DevModule
 
$this->install(new AttributeModule());
```

---

[^1]:Attributes take precedence when mixed in a single method.

***

# API Doc

ApiDoc generates API documentation from your application.

The auto-generated documentation from your code and JSON schema will reduce your effort and keep your API documentation accurate.

## Usage

Install BEAR.ApiDoc.

    composer require bear/api-doc --dev

Copy the configuration file.

    cp ./vendor/bear/api-doc/apidoc.xml.dist ./apidoc.xml

## Source

ApiDoc generates documentation by retrieving information from phpdoc, method signatures, and JSON schema.

#### PHPDOC

In phpdoc, the following parts are retrieved.
For information that applies across resources, such as authentication, prepare a separate documentation page and link it with `@link`.

```php
/**
 * {title}
 *
 * {description}
 *
 * {@link htttp;//example.com/docs/auth 認証}
 */
 class Foo extends ResourceObject
 {
 }
```

```php
/**
 * {title}
 *
 * {description}
 *
 * @param string $id ユーザーID
 */
 public function onGet(string $id ='kuma'): static
 {
 }
```

* If there is no `@param` description in the phpdoc of the method, get the information of the argument from the method signature.
* The order of priority for information acquisition is phpdoc, JSON schema, and profile.

## Configuration

The configuration is written in XML.
The minimum specification is as follows.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<apidoc
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://bearsunday.github.io/BEAR.ApiDoc/apidoc.xsd">
    <appName>MyVendor\MyProject</appName>
    <scheme>app</scheme>
    <docDir>docs</docDir>
    <format>html</format>
</apidoc>
```

### Required Attributes

#### appName

Application namespaces

#### scheme

The name of the schema to use for API documentation. `page` or `app`.

#### docDir

Output directory name.

#### format

The output format, HTML or MD (Mark down).

### Optional attributes

#### title

API title

```xml
<title>MyBlog API</title>
```

#### description

API description

```xml
<description>MyBlog API description</description
```

#### links

Links. The `href` is the URL of the link, and the `rel` is its content.

```xml
<links>
    <link href="https://www.example.com/issue" rel="issue" />
    <link href="https://www.example.com/help" rel="help" />
</links>
```

#### alps

Specifies an "ALPS profile" that defines the terms used by the API.

```xml
<alps>alps/profile.json</alps>.
```

## Profile

ApiDoc supports the [ALPS](http://alps.io/) format of the [RFC 6906 Profile](https://tools.ietf.org/html/rfc6906) which gives additional information to the application.

Words used in API request and response keys are called semantic descriptors, and if you create a dictionary of profiles, you don't need to describe the words for each request.
Centralized definitions of words and phrases prevent notational errors and aid in shared understanding.

The words used in API request and response keys are called semantic descriptors, and creating a dictionary of profiles eliminates the need to explain the words for each request.
Centralized definitions of words and phrases prevent shaky notation and aid in shared understanding.

The following is an example of defining descriptors `firstName` and `familyName` with `title` and `def` respectively.
While `title` describes a word and clarifies its meaning, `def` links standard words defined in vocabulary sites such as [Schema.org](https://schema.org/).

ALPS profiles can be written in XML or JSON.

profile.xml
```xml
<?xml version="1.0" encoding="UTF-8"?>
<alps
     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     xsi:noNamespaceSchemaLocation="https://alps-io.github.io/schemas/alps.xsd">
    <!-- Ontology -->
    <descriptor id="firstName" title="The person's first name."/>
    <descriptor id="familyName" def="https://schema.org/familyName"/>
</alps>
```

profile.json

```json
{
  "$schema": "https://alps-io.github.io/schemas/alps.json",
  "alps": {
    "descriptor": [
      {"id": "firstName", "title": "The person's first name."}
      {"id": "familyName", "def": "https://schema.org/familyName"},
    ]
  }
}
```

Descriptions of words appearing in ApiDoc take precedence over phpdoc > JsonSchema > ALPS in that order.

## Reference

* [Demo](https://bearsunday.github.io/BEAR.ApiDoc/)
* [ALPS](http://alps.io/)
* [ALPS-ASD](https://github.com/koriym/app-state-diagram)

***

# Reference

## Attributes

| Attribute | Description |
| --- | --- |
| `#[CacheableResponse]` | An attribute to indicate a cacheable response. |
| `#[Cacheable(int $expirySecond = 0)]` | An attribute to indicate the cacheability of a resource. `$expirySecond` is the cache expiration time in seconds. |
| `#[CookieParam(string $name)]` | An attribute to receive parameters from cookies. `$name` is the name of the cookie. |
| `#[DonutCache]` | An attribute to indicate Donut cache. |
| `#[Embed(src: string $src, rel: string $rel)]` | An attribute to indicate embedding other resources. `$src` is the URI of the embedded resource, `$rel` is the relation name. |
| `#[EnvParam(string $name)]` | An attribute to receive parameters from environment variables. `$name` is the name of the environment variable. |
| `#[FormParam(string $name)]` | An attribute to receive parameters from form data. `$name` is the name of the form field. |
| `#[Inject]` | An attribute to indicate setter injection. |
| `#[InputValidation]` | An attribute to indicate input validation. |
| `#[JsonSchema(key: string $key = null, schema: string $schema = null, params: string $params = null)]` | An attribute to specify the JSON schema for input/output of a resource. `$key` is the schema key, `$schema` is the schema file name, `$params` is the schema file name for parameters. |
| `#[Link(rel: string $rel, href: string $href, method: string $method = null)]` | An attribute to indicate links between resources. `$rel` is the relation name, `$href` is the URI of the linked resource, `$method` is the HTTP method. |
| `#[Named(string $name)]` | An attribute to indicate named binding. `$name` is the binding name. |
| `#[OnFailure(string $name = null)]` | An attribute to specify a method for validation failure. `$name` is the name of the validation. |
| `#[OnValidate(string $name = null)]` | An attribute to specify a validation method. `$name` is the name of the validation. |
| `#[Produces(array $mediaTypes)]` | An attribute to specify the output media types of a resource. `$mediaTypes` is an array of producible media types. |
| `#[QueryParam(string $name)]` | An attribute to receive query parameters. `$name` is the name of the query parameter. |
| `#[RefreshCache]` | An attribute to indicate cache refresh. |
| `#[ResourceParam(uri: string $uri, param: string $param)]` | An attribute to receive the result of another resource as a parameter. `$uri` is the URI of the resource, `$param` is the parameter name. |
| `#[ReturnCreatedResource]` | An attribute to indicate that the created resource will be returned. |
| `#[ServerParam(string $name)]` | An attribute to receive parameters from server variables. `$name` is the name of the server variable. |
| `#[Ssr(app: string $appName, state: array $state = [], metas: array $metas = [])]` | An attribute to indicate server-side rendering. `$appName` is the name of the JS application, `$state` is the state of the application, `$metas` is an array of meta information. |
| `#[Transactional(array $props = ['pdo'])]` | An attribute to indicate that a method will be executed within a transaction. `$props` is an array of properties to which the transaction will be applied. |
| `#[UploadFiles]` | An attribute to receive uploaded files. |
| `#[Valid(form: string $form = null, onFailure: string $onFailure = null)]` | An attribute to indicate request validation. `$form` is the form class name, `$onFailure` is the method name for validation failure. |

## Modules

| Module Name | Description |
| --- | --- |
| `ApcSsrModule` | A module for server-side rendering using APCu. |
| `ApiDoc` | A module for generating API documentation. |
| `AppModule` | The main module of the application. It installs and configures other modules. |
| `AuraSqlModule` | A module for database connection using Aura.Sql. |
| `AuraSqlQueryModule` | A module for query builder using Aura.SqlQuery. |
| `CacheVersionModule` | A module for cache version management. |
| `CliModule` | A module for command-line interface. |
| `DoctrineOrmModule` | A module for database connection using Doctrine ORM. |
| `FakeModule` | A fake module for testing purposes. |
| `HalModule` | A module for HAL (Hypertext Application Language). |
| `HtmlModule` | A module for HTML rendering. |
| `ImportAppModule` | A module for loading other applications. |
| `JsonSchemaModule` | A module for input/output validation of resources using JSON schema. |
| `JwtAuthModule` | A module for authentication using JSON Web Token (JWT). |
| `NamedPdoModule` | A module that provides named PDO instances. |
| `PackageModule` | A module that installs the basic modules provided by BEAR.Package together. |
| `ProdModule` | A module for production environment settings. |
| `QiqModule` | A module for the Qiq template engine. |
| `ResourceModule` | A module for settings related to resource classes. |
| `AuraRouterModule` | A module for routing using Aura.Router. |
| `SirenModule` | A module for Siren (Hypermedia Specification). |
| `SpyModule` | A module for recording method calls. |
| `SsrModule` | A module for server-side rendering. |
| `TwigModule` | A module for the Twig template engine. |
| `ValidationModule` | A module for validation. |
