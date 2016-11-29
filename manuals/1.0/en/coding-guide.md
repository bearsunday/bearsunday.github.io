---
layout: docs-en
title: コーディングガイド
category: Manual
permalink: /manuals/1.0/en/coding-guide.html
---

*[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/psr7.md) needs to be proofread by an English speaker. If interested please send me a pull request. Thank you.*

# Coding Guide

## Project

`Vendor` specifies the company name, team name or personal name (` excite`, `koriym` etc.),` package` specifies the name of the application (service) ( `blog`,` news` etc. ) Is specified.
Projects are created on a per application basis, and even when Web API and HTML are served on different hosts, they are made into one project.

## Style

Follow PSR style.

  *  [PSR1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
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

A [DocBlock comment]([https://phpdoc.org/docs/latest/getting-started/your-first-set-of-documentation.html]) is optionable. Append a method summary (one line), a description (multiple lines allowed), `@params` when it is insufficient to explain with just the resource URI or argument name. After `@params`, empty blank lines and write custom annotations after that.

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

## Globals

We do not recommend referencing global values in resource or application classes. Only used with Modules.

* Do not refer to the value of [Superglobal](http://php.net/manual/ja/language.variables.superglobals.php)
* Do not use [define](http://php.net/manual/en/function.define.php)
* Do not create `Config` class to hold set values.
* Do not use global object container (service locator) [[1]](http://koriym.github.io/adv10/), [[2]](http://blog.ploeh.dk/2010/02/03/ServiceLocatorisanAnti-Pattern/)
* [Date] (http://php.net/manual/en/function.date.php) function and [DateTime](http://php.net/manual/en/class.datetime.php) class now It is not recommended to get the time directly. Inject the time from outside using [koriym/now](https://github.com/koriym/Koriym.Now).

Global method calls such as static methods are also not recommended.

The values required by the application code are basically all injected, not from the configuration file etc. (The setting file is used for injecting) When using external system values such as Web API, concentrate it on one place such as client class and Web API access resource and make it easier for DI and AOP to mock I will do.

## Classe and object

* Trait(http://php.net/manual/ja/language.oop5.traits.php) is not recommended except for injection.
* It is not recommended that child classes use parent class methods. Common functions are not shared by inheritance and trait, they are dedicated classes and they are injected and used. [Composite from inheritance](https://en.wikipedia.org/wiki/Composition_over_inheritance).
* A class with only one method reflects the function to the class name and sets the name of the method to `__invoke ()` so that function access can be made.

## Script command

* It is recommended that setup of the application is completed with `composer setup` command. This script includes database initialization and necessary library checking. If manual operation such as `.env` setting is required, it is recommended that the procedure be displayed on the screen.
* It is recommended that all application caches and logs are cleared with `composer cleanup` command.

## Code check

It is recommended to check the code with the following command for each commit. Commands can be installed with [bear/qatools](https://github.com/bearsunday/BEAR.QATools).

```
phpcs src tests
phpmd src text ./phpmd.xml
php-cs-fixer fix --config-file=./.php_cs
phpcbf src
```

## Resources

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

`OnPut` implements idempotent resource operations. For example, it is change of resource contents, creation of specified resource such as UID.

`OnPatch` is implemented when changing the state of a part of a resource.

### HTML Form Method

BEAR.Sunday can overwrite methods using the `X-HTTP-Method-Override` header or` _method` query at the `POST` request in the HTML web form, but it is not necessarily recommended . For the Page resource, it is OK to implement policies other than `onGet` and` onPost`. [[1]](http://programmers.stacxchange.com/questions/114156/why-are-there-are-no-put-and-delete-methods-on-html-forms), [[2]](Http://roy.gbiv.com/untangled/2009/it-is-okay-to-use-post)

### Hyperlink

Resources with links should be indicated with `@Link`.

```php?start_inline
class User
{
    /**
     * @Link(rel="profile", href="/profile{?id})
     * @Link(rel="blog", href="/blog{?id})
     */
    public function onGet($id)
```

Resource requests with the following actions are recommended to be traversed with `href ()` (hyperreference).

```php?start_inline
public function onPost(string $title) : ResourceObject
{
    // ...
    $this->code = 201;
    $this->headers['Location'] = "/task?id={$id}";

    return $this;
}
```

`OnPut` implements idempotent resource operations. For example, it is change of resource contents, creation of specified resource such as UID.

`OnPatch` is implemented when changing the state of a part of a resource.


Resource requests with the following actions are recommended to be traversed with `href()`(hyperreference).

```php?start_inline
class Order
{
    /**
     * @Link(rel="payment", href="/payment{?order_id, credit_card_number}", method="put")
     */
    public function onPost($drink)
```
```php?start_inline
// 上記の注文リソースを作成して支払いリソースにリクエストします
$order = $this->resource
    ->post
    ->uri('app://self/order')
    ->withQuery(['drink' => 'latte'])
    ->eager
    ->request();
$payment = ['credit_card_number' => '123456789'];
$response = $resource->href('payment', $payment);
```

### Embedded Resources

If the resource contains resources, it is recommended to embed it with `@Embed`.

```php?start_inline
/**
 * @Embed(rel="user", src="/user{?user_id}")
 */
public function onGet(string $userId) : ResourceObject
{
```

```php?start_inline
/**
 * @Embed(rel="uid", src="/uid")
 */
public function onPost(string $userId, string $title) : ResourceObject
{
    $uid = $this['uid']()->body;
```

When determining the required query for a request for a resource to be `@Embed` without a method, specify the query after` @Embed` an incomplete resource without parameters.

```php?start_inline
/**
 * @Embed(rel="user", src="/user")
 */
public function onGet() : ResourceObject
{
    ...
    $query = ['userId' => $userId];
    $user = $this['user']->withQuery($query)()->body; // /user?user={$userId}
```

Use `addQuery ()` to append to a query in a `@ Embed` URI.

```php?start_inline
/**
 * @Embed(rel="user", src="/user&category=1")
 */
public function onGet() : ResourceObject
{
    ...
    $query = ['userId' => $userId];
    $user = $this['user']->addQuery($query)()->body; // /user?category=1&user=$userId
```

### Argument binding

To use the value of `$_GET` in a method other than `onGet` use `@QueryParam`. Other values stored in superglobal variables of PHP are [Web Context Parameters
](https://github.com/ray-di/Ray.WebParamModule) to bind to the argument.

```php?start_inline
/**
 * @QueryParam(key="id", param="userId")
 */
public function foo($userId = null) : ResourceObject
{
   // $userId = $_GET['id'];
```

To use the value of another resource as an argument, use `@ ResourceParam`.

```php?start_inline
/**
 * @ResourceParam(param=“name”, uri="/login#nickname")
 */
public function onGet($name) : ResourceObject
{
```  

Resource clients should try to embed them with `@Embed` and use`@Link` links as much as possible. Embedded resources become request strings with `toUri()` and `toUriWithMethod ()`, which makes testing easier.

## DI

 * Setter injection is not recommended for library code.
 * It is recommended that you override `toConstructor` bindings as much as possible by avoiding` Provider` bindings.
 * Avoid binding by `Module` according to conditions. ([AvoidConditionalLogicInModules] (https://github.com/google/guice/wiki/AvoidConditionalLogicInModules))
 * It is not recommended to reference environmental variables since there is no module. Pass it in the constructor.

## Router

When routing is changed in multiple contexts such as API and HTML, the router file `route.aura.conf` requires for each context by `$schemeHost`.

```php?start_inline
<?php
/* @var $router \BEAR\Package\Provide\Router\AuraRoute */
/* @var $schemeHost string */

require ($schemeHost === 'app://self') ? __DIR__ . '/app.route.conf' : __DIR__ . '/page.route.conf';
```

## Environment

Applications that only work on the Web are not recommended. We will also work on the console to make it testable.

It is recommended not to include the `.env` file in the project repository.

## Test

It is based on resource test using resource client. Check the value of the resource and test it if necessary (HTML or JSON).

## Development tools

We recommend the following PHPStorm plugin. Set it with `PHPStorm > Preference > Plugins`.

* [BEAR Sunday](https://github.com/kuma-guy/idea-php-bear-sunday-plugin)
* [PHP Annotations](https://github.com/Haehnchen/idea-php-annotation-plugin)
* PHP Advanced AutoComplete
* Database Navigator
