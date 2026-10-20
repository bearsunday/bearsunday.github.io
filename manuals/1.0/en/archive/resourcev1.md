---
layout: docs-en
title: Resource (v1)
category: Manual
permalink: /manuals/1.0/en/resourcev1.html
---

# Resource

A BEAR.Sunday application is a collection of resources connected by links.

## Object as a Service

`ResourceObject` is an object as a service (Object as a service) in which HTTP methods are mapped to PHP methods. It represents its own resource state and transfers it in a stateless request.
([Representational State Transfer](https://en.wikipedia.org/wiki/Representational_state_transfer))

```php?start_inline
class Index extends ResourceObject
{
    public $code = 200;
    public $headers = [];

    public function onGet(int $a, int $b): static
    {
        $this->body = [
            'sum' => $a + $b  // $_GET['a'] + $_GET['b']
        ];

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
        $this->headers = [ // headers
            'Location' => '/todo/new_id'
        ];

        return $this;
    }
}
```

A PHP resource class has a URI such as `app://self/blog/posts/?id=3` or `page://self/index`, just like a web URI, and has `onGet`, `onPost`, `onPut`, `onPatch`, and `onDelete` interfaces corresponding to HTTP methods.

The method changes its own resource state `code`, `headers`, and `body`, and returns `$this`.

## URI

PHP classes are mapped to URIs, and an application accesses resources by URI instead of by class name.
For an application named `koriym\todo`, URIs and classes correspond as follows.

| URI | Class |
|---|---|
| `page://self/index` | `Koriym\Todo\Resource\Page\Index` |
| `app://self/blog/posts` | `Koriym\Todo\Resource\App\Blog\Posts` |


* `index` in `page://self/index` can be omitted. `page://self/index` and `page://self/` are the same.
* Queries can be used just like HTTP URIs. (app://self/blog/posts/?id=3)


Two kinds of resources are provided by default. One is the `App` application resource, the API of the application.
The other is the `Page` resource. A `Page` resource expresses a web page using `App` resources.

## Methods

Resources can be accessed with six methods corresponding to HTTP methods.

### GET
Gets the resource state. This is a safe method. It must not change the resource state.

### PUT
Replaces the resource state at the requested URI. This method is not safe and changes the resource state.
It is [idempotent](https://en.wikipedia.org/wiki/Idempotence), so executing it any number of times produces the same result.

### PATCH
Partially modifies the resource.

### POST
Adds a new resource at the requested URI. This method is not safe and changes the resource state. It is not idempotent, so a resource is added for each request.

### DELETE
Deletes the resource. It is idempotent.

### OPTIONS
Gets the parameters required to request the resource and information about responses. This is a safe method.

## Parameters

On an HTTP request, `$_GET` is passed to the arguments of `onGet` and `$_POST` to `onPost`, according to the variable names. For example, `$_GET['id']` is passed to `$id` below.


```php?start_inline
class Index extends ResourceObject
{
    public function onGet(int $id): static
    {
```

For methods other than GET and POST, namely `onPut`, `onPatch`, and `onDelete`, the request body is given to the arguments in the format specified by the `content-type` header. [^1]
For example, `application/json` is treated as JSON format, and `x-www-form-urlencoded` is treated as the same format as a query string passed via URL, key1=val1&key2=vale2&...

Parameters may be nested data [^2].
Data sent as JSON or a nested query string can be received as an array or a class.

```php?start_inline
class Index extends ResourceObject
{
    public function onPost(array $user): static
    {
        $name = $user['name']; // bear
```

```php?start_inline
class Index extends ResourceObject
{
    public function onPost(User $user): static
    {
        $name = $user->name; // bear
```

Define the receiving class (Input class) beforehand with its parameters as public properties.

```php?start_inline
<?php

namespace Vendor\App\Input;

final class User
{
    public $id;
    public $name;
}
```

The namespace is optional. An Input class can implement methods that group or validate the input data.[^3]

```php?start_inline
final class User
{
    public $givenName;
    public $familyName;

    public function getFullName() : string
    {
        return "{$this->givenName} {$this->familyName}";
    }
}
```

Receiving an array is also useful when accepting input as a collection of Input classes.

### Bound Parameters

The arguments of a resource class method can be bound to the web context or to the state of another resource.

#### Web Context Parameters

Values of PHP superglobals such as `$_GET` and `$_COOKIE` can be bound to method arguments instead of being retrieved inside the method.

When the key name and the argument name are the same

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;

class News extends ResourceObject
{
    /**
     * @QueryParam("id")
     */
    public function foo(string $id): static
    {
      // $id = $_GET['id'];
```

When the key name and the argument name differ, specify `key` and `param`.

```php?start_inline
use Ray\WebContextParam\Annotation\CookieParam;

class News extends ResourceObject
{
    /**
     * @CookieParam(key="id", param="tokenId")
     */
    public function foo(string $tokenId): static
    {
      // $tokenId = $_COOKIE['id'];
```

Full list

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

class News extends ResourceObject
{
    /**
     * @QueryParam(key="id", param="userId")
     * @CookieParam(key="id", param="tokenId")
     * @EnvParam("app_mode")
     * @FormParam("token")
     * @ServerParam(key="SERVER_NAME", param="server")
     */
    public function foo(
        string $userId,           // $_GET['id'];
        string $tokenId = "0000", // $_COOKIE['id'] or "0000" when unset;
        string $app_mode,         // $_ENV['app_mode'];
        string $token,            // $_POST['token'];
        string $server            // $_SERVER['SERVER_NAME'];
    ): static {
```

This feature requires `null` as the argument default.
Also, when the client specifies a value, that value takes precedence and the bound value is ignored.

#### Resource Parameters

The `@ResourceParam` annotation binds the result of another resource request to a method argument.

```php?start_inline
use BEAR\Resource\Annotation\ResourceParam;

class News extends ResourceObject
{
    /**
     * @ResourceParam(param="name", uri="app://self/login#nickname")
     */
    public function onGet(string $name): static
    {
```

In this example, when the method is called, a `get` request is made to the `login` resource and `$body['nickname']` is received as `$name`.


## Rendering

Request methods of the `ResourceObject` class (such as `onGet`) have no concern for whether the resource is represented as HTML or JSON.
The resource renderer injected into `ResourceObject` according to the context renders the resource into JSON or HTML as the resource representation (view).

Rendering occurs when the resource is evaluated as a string.

```php?start_inline

$weekday = $api->resource->get('app://self/weekday', ['year' => 2000, 'month'=> 1, 'day'=> 1]);
var_dump($weekday->body); // as array
//array(1) {
//    ["weekday"]=>
//  string(3) "Sat"
//}

echo $weekday; // as string
//{
//    "weekday": "Sat",
//    "_links": {
//    "self": {
//        "href": "/weekday/2000/1/1"
//        }
//    }
//}
```

Because the renderer is injected according to the context, you normally do not need to be aware of it.

When a resource-specific representation is needed, inject a custom renderer as follows.

```php?start_inline
class Index extends ResourceObject
{
    // ...
    /**
     * @Inject
     * @Named("my_renderer")
     */
    public function setRenderer(RenderInterface $renderer)
    {
        parent::setRenderer($renderer);
    }
}
```

or

```php?start_inline
class Index extends ResourceObject
{
    /**
     * @Inject
     */
    public function setRenderer(RenderInterface $renderer)
    {
        $this->renderer = new class implements RenderInterface {
            public function render(ResourceObject $ro)
            {
                $ro->headers['content-type'] = 'application/json;';
                $ro->view = json_encode($ro->body);

                return $ro->view;
            }
        };
    }
}
```

## Transfer

A transponder transfers the representation (view) to the client (console or web client).
Transfer is usually done simply with the `header` function or `echo`, but it can also be done with [stream output](stream.html).

As with the renderer, you normally do not need to be aware of it.

When resource-specific transfer is needed, override the following method.

```php?start_inline
class Index extends ResourceObject
{
    // ...
    public function transfer(TransferInterface $responder, array $server)
    {
        $responder($this, $server);
    }
}
```

In this way, each resource class has the ability to change its own resource state according to the request and to transfer it as a representation.

## Client

Use the injected resource client to request other resources.

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
This request executes a request to the `app://self/blog/posts` resource with the query `?id=1`.
The following notations are also available.

```php?start_inline
// PHP 5.x and up
$posts = $this->resource->get->uri('app://self/posts')->withQuery(['id' => 1])->eager->request();
// PHP 7.x and up
$posts = $this->resource->get->uri('app://self/posts')(['id' => 1]);
// get can be omitted
$posts = $this->resource->uri('app://self/posts')(['id' => 1]);
// bear/resource 1.11 and up
$posts = $this->resource->get('app://self/posts', ['id' => 1]);
```

The above are `eager` requests that perform the request immediately. You can also obtain the request itself rather than the result and defer execution.

```php?start_inline
$request = $this->resource->uri('app://self/posts'); // callable
$posts = $request(['id' => 1]);
```

When this request is embedded in a template or resource, it is evaluated and the request is executed when that element is used. In other words, when it is not evaluated, no request is made and no execution cost is incurred.

## Link Requests

The client can link resources connected by hyperlinks.

```php?start_inline
$blog = $this
    ->resource
    ->get
    ->uri('app://self/user')
    ->withQuery(['id' => 1])
    ->linkSelf("blog")
    ->eager
    ->request()
    ->body;
```

There are three kinds of links. The linked resource is embedded in the `body` of the original resource under the key `$rel`.

 * `linkSelf($rel)` Replaces the resource with the linked resource.
 * `linkNew($rel)` Adds the linked resource to the linking resource
 * `linkCrawl($rel)` Crawls links to create a "resource tree".

### Crawl Links

Resources with a tree structure — for example, a user linked to a blog, a blog to posts, a post to comments, and a comment to ratings — can be retrieved well with `linkCrawl()`.
See [Crawl](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.md#crawl) for details.

## Link Annotations

The `@Link` annotation hyperlinks another resource, and the `@Embed` annotation embeds another resource.

### @Link

Specify a link with `rel` and `href`. In the `hal` context it is treated as the HAL link format.

```php?start_inline
    /**
     * @Link(rel="profile", href="/profile{?id}")
     */
    public function onGet($id): static
    {
        $this->body = [
            'id' => 10
        ];

        return $this;
    }
```

The `{?id}` in the `href` URI of `@Link` is the value of `$body` (not the method's `$id`). The URI is generated with the [RFC6570 URI template](https://github.com/ioseb/uri-template), and the HAL output is as follows.
```json
{
    "id": 10,
    "_links": {
        "self": {
            "href": "/test"
        },
        "profile": {
            "href": "/profile?id=10"
        }
    }
}
```

In BEAR resource requests, it is used as a resource link for `linkSelf()`, `linkNew`, and `linkCrawl`.

```php?start_inline
use BEAR\Resource\Annotation\Link;

/**
 * @Link(crawl="post-tree", rel="post", href="app://self/post?author_id={id}")
 */
public function onGet($id = null): static
```

`linkCrawl` [crawls](https://github.com/bearsunday/BEAR.Resource#crawl) links with `crawl` and collects resources.

## Embedded Resources

You can embed another resource specified by `src` of `@Embed` inside a resource.

```php?start_inline
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    /**
     * @Embed(rel="sports", src="/news/sports")
     * @Embed(rel="weather", src="/news/weather")
     */
    public function onGet(): static
```

What is embedded is the resource **request**. It is executed at rendering time, but before that you can add arguments with the `addQuery()` method or replace arguments with `withQuery()`.

`src` can use a URI template, and **the arguments of the request method** are bound (not the resource's `$body`).

```php?start_inline
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    /**
     * @Embed(rel="website", src="/website{?id}")
     */
    public function onGet(string $id): static
    {
        // ...
        $this->body['website']->addQuery(['title' => $title]); // add argument
```

In the [HAL](https://github.com/blongden/hal) renderer it is treated as `_embedded`.


## Resource Cache

### @Cacheable

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

/**
 * @Cacheable
 */
class User extends ResourceObject
```

When annotated with `@Cacheable`, `get` requests use the read repository `QueryRepository` and function as a cache with no expiration.
When there is a request other than `get`, the corresponding `QueryRepository` resource is updated.

Resource objects read from `@Cacheable` are given HTTP-compliant `Last-Modified` and `ETag` headers.

When a request method other than `onGet` of the same class is requested and the resource is judged to have changed by looking at the arguments, the contents of `QueryRepository` are also updated.


```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

/**
 * @Cacheable
 */
class Todo extends ResourceObject
{
    public function onGet(string $id): static
    {
        // read
    }

    public function onPost(string $id, string $name): static
    {
        // update
    }
}
```

For example, in this class, a request `->post(10, 'shopping')` updates the contents of `QueryRepository` for `id=10`.
To disable this automatic update, set `update` to false.

```php?start_inline
/**
 * @Cacheable(update=false)
 */
```

To specify a duration, use `expiry` with one of `short`, `medium`, or `long`.
```php?start_inline
/**
 * @Cacheable(expiry="short")
 */
```


## @Purge @Refresh

Another way is to specify the URIs to be updated with the `@Purge` or `@Refresh` annotation.


```php?start_inline
use BEAR\RepositoryModule\Annotation\Purge;
use BEAR\RepositoryModule\Annotation\Refresh;

class News extends ResourceObject
{
  /**
   * @Purge(uri="app://self/user/friend?user_id={id}")
   * @Refresh(uri="app://self/user/profile?user_id={id}")
   */
   public function onPut(string $id, string $name, int $age): static
```

You can update the contents of the `QueryRepository` of resources in another class or of multiple related resources.
`@Purge` purges the resource cache, and `@Refresh` regenerates the cache immediately after the method is executed.

As elsewhere, the values given to the URI template are the values assigned to `$body`, which take precedence over the actual arguments.

```php?start_inline
use BEAR\RepositoryModule\Annotation\Purge;
use BEAR\RepositoryModule\Annotation\Refresh;

class News extends ResourceObject
{
  /**
   * @Purge(uri="app://self/user/friend?user_id={id}")
   * @Refresh(uri="app://self/user/profile?user_id={id}")
   */
   public function onPut($id, $name, $age): static
```

## Direct Query Repository Access

Data stored in the query repository can be directly `put` (saved) or `get` with a client obtained via `QueryRepositoryInterface`.

```php?start_inline
use BEAR\QueryRepository\QueryRepositoryInterface;

class Foo
{
    public function __construct(QueryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function foo()
    {
        // save
        $this->repository->put($this);
        $this->repository->put($resourceObject);

        // purge
        $this->repository->purge($resourceObject->uri);
        $this->repository->purge(new Uri('app://self/user'));
        $this->repository->purge(new Uri('app://self/ad/?id={id}', ['id' => 1]));

        // read
        list($code, $headers, $body, $view) = $this->repository->get(new Uri('app://self/user'));
     }
```

## Best Practices<a name="best-practice"></a>

In REST, resources are connected to other resources. Using links well makes code concise, readable, and easier to test and change.

### @Embed

Instead of `get`ting the state of another resource, embed the resource with `@Embed`.

```php?start_inline
// OK but not the best
class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(string $status): static
    {
        $this->body = [
            'todos' => $this->resource->uri('app://self/todos')(['status' => $status]) // lazy request
        ];

        return $this;
    }
}

// Better
class Index extends ResourceObject
{
    /**
     * @Embed(rel="todos", src="app://self/todos{?status}")
     */
    public function onGet(string $status): static
    {
        return $this;
    }
}
```

### @Link

When changing the state of another resource, follow the next action indicated by `@Link` using `href()` (hyperreference).

```php?start_inline
// OK but not the best
class Todo extends ResourceObject
{
    use ResourceInject;

    public function onPost(string $title): static
    {
        $this->resource->post('app://self/todo', ['title' => $title]);
        $this->code = 301;
        $this->headers[ResponseHeader::LOCATION] = '/';

        return $this;
    }
}

// Better
class Todo extends ResourceObject
{
    use ResourceInject;

    /**
     * @Link(rel="create", href="app://self/todo", method="post")
     */
    public function onPost(string $title): static
    {
        $this->resource->href('create', ['title' => $title]);
        $this->code = 301;
        $this->headers[ResponseHeader::LOCATION] = '/';

        return $this;
    }
}
```

### @ResourceParam

Use `@ResourceParam` when you need the result of another resource in order to request another resource.

```php?start_inline
// OK but not the best
class User extends ResourceObject
{
    use ResourceInject;

    public function onGet(string $id): static
    {
        $nickname = $this->resource->get('app://self/login-user', ['id' => $id])->body['nickname'];
        $this->body = [
            'profile'=> $this->resource->get('app://self/profile', ['name' => $nickname])->body
        ];

        return $this;
    }
}

// Better
class User extends ResourceObject
{
    use ResourceInject;

    /**
     * @ResourceParam(param="name", uri="app://self/login-user#nickname")
     */
    public function onGet(string $id, string $name): static
    {
        $this->body = [
            'profile' => $this->resource->get('app://self/profile', ['name' => $name])->body
        ];

        return $this;
    }
}

// Best
class User extends ResourceObject
{
    /**
     * @ResourceParam(param="name", uri="app://self/login-user#nickname")
     * @Embed(rel="profile", src="app://self/profile")
     */
    public function onGet(string $id, string $name): static
    {
        $this->body['profile']->addQuery(['name'=>$name]);

        return $this;
    }
}
```


### body Syntax Sugar

Array access on `$this` is access to `$this->body`.

```php?start_inline
$this['price'] = 10;
// is same as
$this->body['price'] = 10;
```

## BEAR.Resource

See the BEAR.Resource [README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.md) for more information about resource classes.

---
[^1]:See [PUT method support](https://www.php.net/manual/en/features.file-upload.put-method.php)
[^2]:See [parse_str](https://www.php.net/manual/en/function.parse-str.php)
[^3]:You can also validate with the `__set()` magic method instead of defining public properties.
