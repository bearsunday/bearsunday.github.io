---
layout: docs-en
title: Coding Guide
category: Manual
permalink: /manuals/1.0/en/coding-guide.html
---

# Coding Guide

## Project

`vendor` is the company, team, or owner name (`excite`, `koriym`, etc.), and `package` is the name of the application or service (`blog`, `news`, etc.).
Create one project per application. Even when a Web API and HTML are served from different hosts, they are still one project.

## Style

Follow [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md), [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), and [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md).

```php
<?php
namespace Koriym\Blog\Resource\App;

use Aura\Sql\ExtendedPdoInterface;
use BEAR\RepositoryModule\Annotation\CacheableResponse;
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Code;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

#[CacheableResponse]
class Entry extends ResourceObject
{
    public function __construct(
        private readonly ExtendedPdoInterface $pdo,
        private readonly ResourceInterface $resource
    ) {}

    #[Embed(rel: "author", src: "/author{?author_id}")]
    public function onGet(string $author_id, string $slug): static
    {
        // ...
        return $this;
    }

    #[Link(rel: "next_action1", href: "/next_action1")]
    public function onPost(
        string $title,
        string $body,
        string $uid,
        string $slug
    ): static {
        // ...
        $this->code = Code::CREATED;
        return $this;
    }
}
```

A resource [DocBlock comment](https://phpdoc.org/docs/latest/getting-started/your-first-set-of-documentation.html) is optional. Add a one-line method summary, a description (which may span multiple lines), and `@param` tags when the resource URI and argument names do not explain the method sufficiently.

```php
/**
 * A summary informing the user what the associated element does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 *
 * @param string $arg1 *description*
 * @param string $arg2 *description*
*/
```

## Resources

See [Resource Best Practices](resource_bp.html) for best practices for resources.

### Code

Return the appropriate status code. This makes testing easier and conveys the correct information to bots and crawlers.

* `100` Continue Continuation of multiple requests
* `200` OK
* `201` Created Resource created
* `202` Accepted Queued or batched for processing
* `204` No Content No body
* `304` Not Modified Not updated
* `400` Bad Request Malformed request
* `401` Unauthorized Authentication required
* `403` Forbidden
* `404` Not Found
* `405` Method Not Allowed
* `503` Service Unavailable Temporary server-side error

`304` is set automatically when the `#[Cacheable]` attribute is used. `404` is set automatically when the resource class does not exist, and `405` when the resource method does not exist. Always return `503` for database connection errors and similar failures so crawlers are informed.

### HTML Form Method

In HTML web forms, BEAR.Sunday can override the method of a `POST` request with the `X-HTTP-Method-Override` header or the `_method` query parameter, but this is not recommended. A Page resource may simply implement only `onGet` and `onPost`.

### Hyperlink

* Resources with links should indicate them with `#[Link]`.
* Resources should form a graph of meaning and embed related resources with `#[Embed]`.

## Globals

Referencing global values in resource or application classes is not recommended. (Use them only in modules.)

* Do not reference [superglobal](https://www.php.net/manual/en/language.variables.superglobals.php) values.
* Do not use [define](https://www.php.net/manual/en/function.define.php).
* Do not create a `Config` class to hold configuration values.
* Do not use a global object container (service locator).
* Do not fetch the current time directly with the [date](https://www.php.net/manual/en/function.date.php) function or the [DateTime](https://www.php.net/manual/en/class.datetime.php) class[^now]. Inject the time from outside.
* Global method calls such as static methods are also not recommended.
* All values that application code needs should be injected, not read from configuration files or elsewhere.[^inject-all]

[^now]: [koriym/now](https://github.com/koriym/Koriym.Now)
[^inject-all]: When using values from external systems such as a Web API, concentrate them in one place, such as a client class or a Web API access resource, so they can be mocked easily with DI or AOP.

## Classes and objects

* [Traits](https://www.php.net/manual/en/language.oop5.traits.php) are not recommended.[^no-trait]
* A child class should not use its parent's methods. Share common functionality by injecting a class rather than through inheritance or traits. [Prefer composition over inheritance](https://en.wikipedia.org/wiki/Composition_over_inheritance).

[^no-trait]: Injection traits such as `ResourceInject` existed to reduce boilerplate injection code, but PHP 8's [constructor property promotion](https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion) made them unnecessary. Use constructor injection.

## DI

* Do not inject the execution context value itself (prod, dev, etc.). Inject an instance that corresponds to the context instead. The application should not know which context it is running in.
* Setter injection is not recommended in library code.
* Prefer `toConstructor` bindings over `Provider` bindings wherever possible.
* Avoid conditional bindings in a `Module`. ([AvoidConditionalLogicInModules](https://github.com/google/guice/wiki/AvoidConditionalLogicInModules))
* Do not read environment variables from a module's `configure()`; pass them through constructor injection instead.

## AOP

* An interceptor must not be mandatory. Logging, database transactions, and similar concerns do not change the essential behavior of the program whether the interceptor is present or not.
* An interceptor should not inject dependencies that are internal to a method. Inject values that can only be determined when the method is implemented into its arguments with `@Assisted` injection.
* With multiple interceptors, avoid depending on their execution order as much as possible.
* For an interceptor that unconditionally applies to all methods, consider declaring it in `bootstrap.php`.
* AOP separates cross-cutting concerns from core concerns. Intercepting a specific method as a hack is not recommended.

## Script commands

* Prefer completing application setup with the `composer setup` command. This script should include database initialization and dependency checks. When manual steps such as configuring `.env` are required, display the procedure on screen.

## Environment

* Applications that run only on the Web are not recommended. Make them run on the console as well so they are testable.
* Do not include the `.env` file in the project repository.
* Consider [Koriym.EnvJson](https://github.com/koriym/Koriym.EnvJson), which describes the schema, instead of `.env`.

## Testing

* Focus on resource tests using the resource client, and add tests for resource representations (HTML, etc.) when necessary.
* Hypermedia tests preserve use cases as tests.
* `prod` is the context for production. Use the `prod` context in tests as little as possible, preferably not at all.

## HTML templates

* Avoid large loops. Consider replacing `if` statements inside a loop with a [generator](https://www.php.net/manual/en/language.generators.overview.php).
