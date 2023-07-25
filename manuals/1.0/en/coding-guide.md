---
layout: docs-en
title: Coding Guide
category: Manual
permalink: /manuals/1.0/en/coding-guide.html
---

# Coding Guide

## Project

`Vendor` should be the company name, team name or the owner (`excite`, `koriym` etc.).
`Package` is the name of the application or service (`blog`, `news` etc.).
Projects must be created on a per application basis. Even when you create a Web API and an HTML from different hosts, they are considered one project.


## Style

BEAR.Sunday follows the PSR style.

  * [PSR1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
  * [PSR2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
  * [PSR4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)


Here is ResourceObject code example.

```php
<?php
namespace Koriym\Blog\Resource\App;

use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\AuraSqlModule\AuraSqlInject;

/**
 * @Cacheable
 */
class Entry extends ResourceObject
{
    use AuraSqlInject;
    use ResourceInject;

    /**
     * @Embed(rel="author", src="/author{?author_id}")
     */
    public function onGet(string $author_id, string $slug) : ResourceObject
    {
        // ...

        return $this;
    }

    /**
     * @Link(rel="next_act", href="/act1")
     * @Link(rel="next_act2", href="/act2")
     */
    public function onPost (
        string $tile,
        string $body,
        string $uid,
        string $slug
    ) : ResourceObject {
        // ...
        $this->code = Code::CREATED;

        return $this;
    }
}
```

A [DocBlock comment]([https://phpdoc.org/docs/latest/getting-started/your-first-set-of-documentation.html]) is optional. A DocBlock contains the method summary in one line.
Then followed by the description, which can be a multiple lines.
We should also put @params and @Link after description if possible.



```php?start_inline
/**
 * A summary informing the user what the associated element does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 *
 * @param string $arg1 *description*
 * @param string $arg2 *description*
 * @param string $arg3 *description*
 *
 * @Link(rel="next_act", href="/next_act_uri")
 * @Link(rel="next_act2", href="/next_act_uri2")
*/
```

## Resources

See [Resource Best Practices](resource_bp.html) for best practices for resources.

## Globals

We do not recommend referencing global values in resources or application classes. It is only used with Modules.

* Do not refer to the value of [Superglobal](http://php.net/manual/ja/language.variables.superglobals.php)
* Do not use [define](http://php.net/manual/en/function.define.php)
* Do not create `Config` class to hold set values.
* Do not use global object container (service locator) [[1]](http://koriym.github.io/adv10/), [[2]](http://blog.ploeh.dk/2010/02/03/ServiceLocatorisanAnti-Pattern/)
* Use [Date](http://php.net/manual/en/function.date.php) function and [DateTime](http://php.net/manual/en/class.datetime.php) class. It is not recommended to get the time directly. Inject the time from outside using [koriym/now](https://github.com/koriym/Koriym.Now).


Global method calls such as static methods are also not recommended.

The values required by the application code are all injected. The setting files are used for injecting. When you need an external value such as Web API, make a special gateway class for all requests. Then you can mock that special class with DI or AOP.

## Classes and object

* * [Traits](http://php.net/manual/en/language.oop5.traits.php) are not recommended. Traits for injection such as `ResourceInject` that reduce boilerplate code for injection were added in PHP8 [constructor property promotion (declaring properties in the constructor signature)](https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion). Use constructor injection.
* It is not recommended for the child classes to use the parent class methods. Common functions are not shared by inheritance and trait, they are dedicated classes and they are injected and used. [Composite from inheritance](https://en.wikipedia.org/wiki/Composition_over_inheritance).
* A class with only one method should reflect the function to the class name and should set the name of the method to `__invoke ()` so that function access can be made.

## Script command

* It is recommended to end the setup by using the `composer setup` command. This script includes the necessary database initialization and library checking. If manual operation such as `.env` setting is required, it is recommended that the procedure be displayed on the screen.
* It is recommended that all application caches and logs are cleared with `composer cleanup` command.
* It is recommended that all executable test (phpinit/phpcs/phpmd ..) are invoked with `composer test` command.
* It is recommended an application is deployed with `composer deploy` command.

## Code check

It is recommended to check the codes for each commit with the following commands. These commands can be installed with [bear/qatools](https://github.com/bearsunday/BEAR.QATools).

```
phpcs src tests
phpmd src text ./phpmd.xml
php-cs-fixer fix --config-file=./.php_cs
phpcbf src
```

## Resources

Please also refer to [Resouce best practice](/manuals/1.0/en/resource.html#best-practice).

### Code

Returns the appropriate status code. Testing is easier, and the correct information can be conveyed to bots and crawlers.

* `100` Continue Continuation of multiple requests
* `200` OK
* `201` Created Resource Creation
* `202` Accepted queue / batch acceptance
* `204` If there is no content body
* `304` Not Modified Not Updated
* `400` Bad request
* `401` Unauthorized Authentication required
* `403` Forbidden ban
* `404` Not Found
* `405` Method Not Allowed
* `503` Service Unavailable Temporary error on server side

In `OnPut` method, you deal with the resource state with idempotence. For example, resource creation with UUID or update resource state.

`OnPatch` is implemented when changing the state of a part of a resource.

### HTML Form Method

BEAR.Sunday can overwrite methods using the `X-HTTP-Method-Override` header or` _method` query at the `POST` request in the HTML web form, but it is not necessarily recommended . For the Page resource, it is OK to implement policies other than `onGet` and` onPost`. [[1]](http://programmers.stacxchange.com/questions/114156/why-are-there-are-no-put-and-delete-methods-on-html-forms), [[2]](Http://roy.gbiv.com/untangled/2009/it-is-okay-to-use-post)

### Hyperlink

* It is recommended that resources with links be indicated by `#[Link]`.
* It is recommended that resources be embedded as a graph of semantic coherence with `#[Embed]`.

## DI

 * Do not inject the value itself of the execution context (prod, dev etc). Instead, we inject instances according to the context. The application does not know in which context it is running.
 * Setter injection is not recommended for library code.
 * It is recommended that you override the `toConstructor` bindings instead and avoid the `Provider` bindings as much as possible.
 * Avoid binding by `Module` according to conditions. [AvoidConditionalLogicInModules](https://github.com/google/guice/wiki/AvoidConditionalLogicInModules)
 * It is not recommended to reference environmental variables since there is no module. Pass it in the constructor.

## AOP

 * Do not make interceptor mandatory. We will make the program work even without an interceptor. (For example, if you remove `@Transactional` interceptor, the function of transaction will be lost, but "core concers" will work without issue.)
 * Prevent the interceptor from injecting dependencies in methods. Values that can only be determined at implementation time are injected into arguments via `@Assisted` injection.
 * If there are multiple interceptors, do not depend on the execution order.
 * If it is an interceptor unconditionally applied to all methods, consider the description in `bootstrap.php`.

## Environment

To make applications testable, it should also work on the console, and not only on the Web.

It is recommended not to include the `.env` file in the project repository.

## Testing

* Focus on resource testing using resource clients, adding resource representation testing (e.g. HTML) if needed.
* Hypermedia tests can leave use cases as tests.
* `prod` is the context for production. Use of the `prod` context in tests should be minimal, preferably none.

## HTML templates

* Avoid large loop statements. Consider replacing if statements in loops with [Generator](https://www.php.net/manual/en/language.generators.overview.php).

---
