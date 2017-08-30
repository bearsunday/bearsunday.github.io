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

* Trait(http://php.net/manual/ja/language.oop5.traits.php) is not recommended except for injection.
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

In `OnPut` method, you deal with the resource state with idempotence. For example, resource creation with UUID or update resource state.

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

If the resource refers to other resources, it is advisable to include it with `@Embed`.

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

You may need to add queries for a resource request. Even if you still don't know the specific parameters, you can already add it by `@Embed`.

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

Then use `addQuery ()` to append to a query in a `@ Embed` URI.

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

To use the value of `$_GET` on a different method other than onGet, use `@QueryParam`. Superglobal variables like $_POST and $_SERVER are [Web Context Parameters](https://github.com/ray-di/Ray.WebParamModule) that you should bind to an argument.

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

Resource clients should embed them with `@Embed` and use `@Link` as much as possible. Embedded resources become request strings with `toUri()` and `toUriWithMethod ()`, which makes testing easier.

## Resource

Please also refer to [Resouce best practice](/manuals/1.0/en/resource.html#best-practice).

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

## Test

Basically you test resources with resource client. You request a resource then examine the returned response value.
If you need to test the representation part, such as HTML or JSON, you may add an additional test for it.

## Development tools

We recommend the following PHPStorm plugin. Set it with `PHPStorm > Preference > Plugins`.

* [BEAR Sunday](https://github.com/kuma-guy/idea-php-bear-sunday-plugin)
* [PHP Annotations](https://github.com/Haehnchen/idea-php-annotation-plugin)
* PHP Advanced AutoComplete
* Database Navigator
