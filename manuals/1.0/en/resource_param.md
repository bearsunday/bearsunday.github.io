---
layout: docs-en
title: Resource Parameters
category: Manual
permalink: /manuals/1.0/en/resource_param.html
---

# Resource Parameters

## Basics

Web runtime values such as HTTP requests and cookies that ResourceObjects require are passed directly to method arguments. For HTTP requests, the `onGet` and `onPost` method arguments receive `$_GET` and `$_POST` respectively, according to variable names.

For example, the following `$id` receives `$_GET['id']`. When input is from HTTP, string arguments are cast to the specified type.

```php
class Index extends ResourceObject
{
    public function onGet(int $id): static
    {
        // ....
```

## Parameter Types

### Scalar Parameters

All parameters passed via HTTP are strings, but specifying non-string types like `int` will cast them.

### Array Parameters

Parameters can be nested data [^2]. Data sent as JSON or nested query strings can be received as arrays.

[^2]: See [parse_str](https://www.php.net/manual/en/function.parse-str.php)

```php
class Index extends ResourceObject
{
    public function onPost(array $user): static
    {
        $name = $user['name']; // bear
```

### Class Parameters

Parameters can also be received as dedicated Input classes.

```php
class Index extends ResourceObject
{
    public function onPost(User $user): static
    {
        $name = $user->name; // bear
```

Input classes are pre-defined with parameters as public properties.

```php
<?php
namespace Vendor\App\Input;

final class User
{
    public int $id;
    public string $name;
}
```

If a constructor exists, it will be called. [^php8]

[^php8]: Called with named arguments in PHP8.x, but with positional arguments in PHP7.x.

```php
<?php
namespace Vendor\App\Input;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {
    }
}
```

Namespaces are arbitrary. Input classes can implement methods to aggregate or validate input data.

### Ray.InputQuery Integration

You can use type-safe input object generation from the `Ray.InputQuery` library using the `#[Input]` attribute.

```php
use Ray\InputQuery\Attribute\Input;

class Index extends ResourceObject
{
    public function onPost(#[Input] ArticleInput $article): static
    {
        $this->body = [
            'title' => $article->title,
            'author' => $article->author->name
        ];
        return $this;
    }
}
```

Parameters with the `#[Input]` attribute automatically receive structured objects generated from flat query data.

```php
use Ray\InputQuery\Attribute\Input;

final class ArticleInput
{
    public function __construct(
        #[Input] public readonly string $title,
        #[Input] public readonly AuthorInput $author
    ) {}
}

final class AuthorInput  
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $email
    ) {}
}
```

In this case, nested object structures are automatically generated from flat data like `title=Hello&authorName=John&authorEmail=john@example.com`.

Array data can also be handled.

#### Simple Arrays

```php
final class TagsInput
{
    public function __construct(
        #[Input] public readonly string $title,
        #[Input] public readonly array $tags
    ) {}
}
```

```php
class Index extends ResourceObject
{
    public function onPost(#[Input] TagsInput $input): static
    {
        // For tags[]=php&tags[]=web&title=Hello
        // $input->tags = ['php', 'web']
        // $input->title = 'Hello'
    }
}
```

#### Object Arrays

Using the `item` parameter, each array element can be generated as an object of the specified Input class.

```php
use Ray\InputQuery\Attribute\Input;

final class UserInput
{
    public function __construct(
        #[Input] public readonly string $id,
        #[Input] public readonly string $name
    ) {}
}

class Index extends ResourceObject
{
    public function onPost(
        #[Input(item: UserInput::class)] array $users
    ): static {
        foreach ($users as $user) {
            echo $user->name; // Each element is a UserInput instance
        }
    }
}
```

This generates arrays from data in the following format:

```php
// users[0][id]=1&users[0][name]=John&users[1][id]=2&users[1][name]=Jane
$data = [
    'users' => [
        ['id' => '1', 'name' => 'John'],
        ['id' => '2', 'name' => 'Jane']
    ]
];
```

* When parameters have the `#[Input]` attribute: Object generation with Ray.InputQuery
* When parameters don't have the `#[Input]` attribute: Traditional dependency injection

For details, see the [Ray.InputQuery](https://github.com/ray-di/Ray.InputQuery) documentation.

### Enum Parameters

You can specify PHP8.1 [enumerations](https://www.php.net/manual/en/language.types.enumerations.php) to restrict possible values.

```php
enum IceCreamId: int
{
    case VANILLA = 1;
    case PISTACHIO = 2;
}
```

```php
class Index extends ResourceObject
{
    public function onGet(IceCreamId $iceCreamId): static
    {
        $id = $iceCreamId->value // 1 or 2
```

In the above case, passing anything other than 1 or 2 will raise a `ParameterInvalidEnumException`.

## Web Context Binding

Values from PHP superglobals like `$_GET` and `$_COOKIE` can be bound to method arguments instead of retrieving them within methods.

```php
use Ray\WebContextParam\Annotation\QueryParam;

class News extends ResourceObject
{
    public function foo(
        #[QueryParam('id')] string $id
    ): static {
        // $id = $_GET['id'];
```

You can also bind values from `$_ENV`, `$_POST`, and `$_SERVER`.

```php
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

class News extends ResourceObject
{
    public function onGet(
        #[QueryParam('id')] string $userId,            // $_GET['id']
        #[CookieParam('id')] string $tokenId = "0000", // $_COOKIE['id'] or "0000" when unset
        #[EnvParam('app_mode')] string $app_mode,      // $_ENV['app_mode']
        #[FormParam('token')] string $token,           // $_POST['token']
        #[ServerParam('SERVER_NAME')] string $server   // $_SERVER['SERVER_NAME']
    ): static {
```

When clients specify values, those values take precedence and bound values become invalid. This is useful for testing.

## Resource Binding

The `#[ResourceParam]` annotation can bind results from other resource requests to method arguments.

```php
use BEAR\Resource\Annotation\ResourceParam;

class News extends ResourceObject
{
    public function onGet(
        #[ResourceParam('app://self//login#nickname')] string $name
    ): static {
```

In this example, when the method is called, it makes a `get` request to the `login` resource and receives `$body['nickname']` as `$name`.

## Content Negotiation

HTTP request `content-type` headers are supported. `application/json` and `x-www-form-urlencoded` media types are distinguished and values are passed to parameters. [^json]

[^json]: When sending API requests as JSON, set the `content-type` header to `application/json`.

