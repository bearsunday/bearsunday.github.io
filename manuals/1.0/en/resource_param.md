---
layout: docs-en
title: Resource Parameter
category: Manual
permalink: /manuals/1.0/en/resource_param.html
---

# Resource Parameters

## Basic

Web runtime values such as HTTP requests and cookies that require ResourceObjects are passed directly to method arguments.

For requests from HTTP, the arguments of the `onGet` and `onPost` methods are passed `$_GET` and `$_POST`, respectively, depending on the variable name. For example, $id in the following is passed as $_GET['id'].

```php?start_inline
class Index extends ResourceObject
{
    public function onGet(int $id): static
    {
```

## Array parameters

Parameters can be nested data [^2]; data sent in JSON or nested query strings can be received in arrays.

[^2]:[parse_str](https://www.php.net/manual/ja/function.parse-str.php)参照 

```php?start_inline
class Index extends ResourceObject
{
    public function onPost(array $user):static
    {
        $name = $user['name']; // bear
```

## Input class parameters

It can also be received in a parameter-only Input object.

```php?start_inline
class Index extends ResourceObject
{
    public function onPost(User $user): static
    {
        $name = $user->name; // bear
```

Input classes are predefined with parameters as public properties。

```php?start_inline
<?php

namespace Vendor\App\Input;

final class User
{
    public $id;
    public $name;
}
```
At this time, if there is a constructor, it will be called. [^php8]
[^php8]: called with named arguments in PHP8.x at this time, but with ordinal arguments in PHP7.x.

```php?start_inline
<?php

namespace Vendor\App\Input;

final class User
{
    public function __constrcut(
        public readonly int $id,
        public readonly string $name
    } {}
}
```

The namespace is optional; the Input class can implement methods to summarize and validate input data.

## Web context binding

PHP superglobals such as `$_GET` and `$_COOKIE` can be bound to method arguments instead of being retrieved in the method.

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;

class News extends ResourceObject
{
    public function foo(
    	  #[QueryParam('id')] string $id
    ): static {
       // $id = $_GET['id'];
```

Others can be done by binding the values of `$_ENV`, `$_POST`, and `$_SERVER`.

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

class News extends ResourceObject
{
    public function onGet(
        #[QueryParam('id')] string $userId,            // $_GET['id'];
        #[CookieParam('id')] string $tokenId = "0000", // $_COOKIE['id'] or "0000" when unset;
        #[EnvParam('app_mode')] string $app_mode,      // $_ENV['app_mode'];
        #[FormParam('token')] string $token,           // $_POST['token'];
        #[ServerParam('SERVER_NAME') string $server    // $_SERVER['SERVER_NAME'];
    ): static {
```

When the client specifies a value, the specified value takes precedence and the bound value is invalid. This is useful for testing.

## Resource Binding

The `#[ResourceParam]` annotation can be used to bind the results of other resource requests to the method argument.

```php?start_inline
use BEAR\Resource\Annotation\ResourceParam;

class News extends ResourceObject
{
    public function onGet(
        #[ResourceParam('app://self//login#nickname') string $name
    ): static {
```

In this example, when the method is called, it makes a `get` request to the `login` resource and receives `$body['nickname']` with `$name`.

## Content negotiation

The `content-type` header of HTTP requests is supported. The `application/json` and `x-www-form-urlencoded` media types are determined and values are passed to the parameters. [^json].

[^json]:Set the `content-type` header to `application/json` if you are sending API requests in JSON.

