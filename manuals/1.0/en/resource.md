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

    public function onGet() : ResourceObject
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
    'lazy' => $this->resource->get('app://self/posts')->withQuery(['id' => 3])->requrest();
];
```

## Cache

Along with regular TTL caching, we support REST client caching and advanced partial caching (doughnut caching), including CDN. See [cache](cache.html) for details. Also see the previous [resource(v1)](resourcev1.html#Resource Cache) document for the previous `@Cacheable` annotation.

## Link

One important REST constraint is resource linking; ResourceObject supports both internal and external linking. See [Resource Linking](resource_link.html) for details.

## BEAR.Resource

The functionality of the BEAR.Sunday resource object is also available in a stand-alone package for stand-alone use: BEAR.Resource [README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.ja.md).

---
