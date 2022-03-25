---
layout: docs-en
title: Resource
category: Manual
permalink: /manuals/1.0/en/resource.html
---

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

    public function onGet(int $a, int $b) : ResourceObject
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
    public function onPost(string $id, string $todo) : ResourceObject
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

## Scheme

The equivalent to a MVC model is an `app` resource. A resource functions as an internal API, but as it is designed using REST it also works as an external API transport.
The `page` resource carries out a similar role as a page controller which is also a resource. A page resource then can consume application resources and builds itself based on the called URI.

| URI | Class |
|-----+-------|
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts | Koriym\Todo\Resource\App\Blog\Posts |

## Method

Resources have 6 interfaces conforming to HTTP methods.

### GET
Reads resources. This method does not provide any changing of the resource state. A safe method with no possible side affects.

### PUT
Performs creation and updates of a resource. This method has the benefit that running it once or many more times will have no more effect. This is referred to as [Idempotence](http://en.wikipedia.org/wiki/Idempotence).

### PATCH

Performs resource updates, but unlike PUT, it applies a delta rather than replacing the entire resource.

### POST
Performs resource creation. If you run a request multiple times the resource will be created as many times. A method with no idempotence.

### DELETE
Resource deletion. Has idempotence just like PUT.

### OPTIONS
Get information on parameters and responses required for resource request. It is as secure as GET method.

## Client

You need a **Resource Client** to request resource. In the following example a `ResourceInject` trait is used to inject a `Resource Client`.

```php?start_inline
use BEAR\Sunday\Inject\ResourceInject;

class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet() : ResourceObject
    {
        $this->body = [
            'posts' => $this->resource->get('app://self/blog/posts', ['id' => 1])
        ];
    }
}
```
This code invokes a `GET` request to `app://self/blog/posts` `app` resource with the query `?id=1` .

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

The above is an `eager` request to make a request immediately, but it gets the request itself rather than the request result,
Request invocation will then be made when values are lazily output in the representation.
You can assign this value to a template engine or embed it in another resource. It will then be lazily evaluated.


```php?start_inline
$request = $this->resource->uri('app://self/posts'); // callable
$posts = $request(['id' => 1]);
```

## Best Practice<a name="best-practice"></a>

In the real world of REST, resources are connected with other resources.
The use of the link makes the code simpler and makes it easier to read and test and change.

### @Embed

Embed resources with `@Embed` instead of `get` the state of other resources.

```php?start_inline
// OK but not the best
class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(string $status) : ResourceObject
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
    public function onGet(string $status) : ResourceObject
    {
        return $this;
    }
}
```

### @Link

When changing the state of another resource we will follow the next action indicated with `@Link` using `href()` (href = hyper reference).

```php?start_inline
// OK but not the best
class Todo extends ResourceObject
{
    use ResourceInject;

    public function onPost(string $title) : ResourceObject
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
    public function onPost(string $title) : ResourceObject
    {
        $this->resource->href('create', ['title' => $title]);
        $this->code = 301;
        $this->headers[ResponseHeader::LOCATION] = '/';

        return $this;
    }
}
```

### ＠ResourceParam

If you need other resource results to request other resources, use `@ResourceParam`.

```php?start_inline
// OK but not the best
class User extends ResourceObject
{
    use ResourceInject;

    public function onGet(string $id) : ResourceObject
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
     * @ResourceParam(param=“name”, uri="app://self//login-user#nickname")
     */
    public function onGet(string $id, string $name) : ResourceObject
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
     * @ResourceParam(param=“name”, uri="app://self//login-user#nickname")
     * @Embed(rel="profile", src="app://self/profile")
     */
    public function onGet(string $id, string $name) : ResourceObject
    {
        $this->body['profile']->addQuery(['name'=>$name]);

        return $this;
    }
}
```
