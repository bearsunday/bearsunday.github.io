---
layout: docs-en
title: Resource (v2)
category: Manual
permalink: /manuals/1.0/en/resource.html
---

# Resource

A BEAR.Sunday application is a collection of RESTful resources. Each piece of information and each function the application handles is represented as a resource with a URI, just like a web page.

Every resource is accessed through a **uniform interface** shared with HTTP, such as GET and POST. Because the interface is uniform, the same resource can be used in the same way from the Web, from the console, and through in-process calls from other resources. There is no need to prepare a separate controller or entry point for each access method, and the meaning of a resource can be understood with knowledge of HTTP alone.

## Object as a service

A resource expressed in PHP is a `ResourceObject`. It is an **object for a service** (Object-as-a-service) in which HTTP methods are mapped to PHP methods.

```php
class Index extends ResourceObject
{
    public function onGet(int $a, int $b): static
    {
        $this->body = [
            'sum' => $a + $b  // $_GET['a'] + $_GET['b']
        ];

        return $this;
    }
}
```

A resource class has a URI such as `page://self/index`, similar to a web URI, and `on` methods such as `onGet` and `onPost` that correspond to HTTP methods. An `on` method determines its own resource state (`code`, `headers`, `body`) from the given parameters and returns `$this`. From a stateless request, the resource state is generated as a resource representation and transferred to the client. ([Representational State Transfer](http://en.wikipedia.org/wiki/Representational_state_transfer))

`code` and `headers` have defaults (`200` and empty headers), so the example above determines only `body`. Override them only when returning a response other than the defaults.

```php
class Todo extends ResourceObject
{
    public function onPost(string $id, string $todo): static
    {
        $this->code = 201; // override the default 200
        $this->headers = [
            'Location' => '/todo/new_id'
        ];

        return $this;
    }
}
```

## Page and app resources

Resources are divided into two schemes according to their role.

| Scheme | Role |
|--------|------|
| page | Public resources exposed to the outside |
| app | Private resources that cannot be accessed directly from outside |

A page resource receives requests from the outside, such as from the Web or console, and requests app resources to determine its own resource state. App resources own the domain information and functions that describe what the application does, while page resources own how to present it, separating these concerns.

The benefit of this separation appears when the context changes. In an HTML application, for example, page resources output HTML while app resources remain private. When the same application becomes the backend of a mobile application, changing the context exposes the app resources as an API that outputs JSON. The resource code stays the same; only the exposure and representation change.

## URI

URIs are mapped to PHP classes. Applications use the URI instead of the class name to access resources.

| URI | Class |
|-----+-------|
| page://self/ | Koriym\Todo\Resource\Page\Index |
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts?id=3 | Koriym\Todo\Resource\App\Blog\Posts |

* `index` can be omitted.

## Method

Resources can be accessed through six methods corresponding to HTTP methods. These methods are not a mapping to CRUD; they are distinguished by whether they are **safe** (do not change the resource state) and whether they are **idempotent** (repeating the same request produces the same result).

| Method | Meaning | [Safe](https://developer.mozilla.org/en-US/docs/Glossary/safe) | [Idempotent](https://developer.mozilla.org/en-US/docs/Glossary/Idempotent) | [Cacheable](https://developer.mozilla.org/en-US/docs/Glossary/cacheable) |
|-|-|-|-|-|
| GET | Retrieve a representation of the resource | Yes | Yes | Yes |
| POST | Process the representation contained in the request (for example, add a new resource) | No | No | No |
| PUT | Replace the resource with the payload, or create it if absent | No | Yes | No |
| PATCH | Partially modify the resource [^patch] | No | No | No |
| DELETE | Delete the resource | No | Yes | No |
| OPTIONS | Retrieve information about the parameters and responses required for a request [^json-schema] | Yes | Yes | No |

[^patch]: [https://www.rfc-editor.org/rfc/rfc5789](https://www.rfc-editor.org/rfc/rfc5789)

[^json-schema]: Retrieving response information requires a JsonSchema declaration.

## Parameters

The request value corresponding to the variable name is passed to the argument of the response method.

```php
class Index extends ResourceObject
{
    // $_GET['id'] is passed to $id
    public function onGet(int $id): static
    {
        return $this;
    }

    // $_POST['name'] is passed to $name
    public function onPost(string $name): static
    {
        return $this;
    }
}
```

See [Resource Parameters](resource_param.html) for other methods and for how to pass external variables such as cookies as parameters.

## Rendering and transfer

The request method of a ResourceObject is not concerned with the representation of the resource. The injected renderer generates the resource representation and the responder outputs it. See [Rendering and Transfer](resource_renderer.html) for details.

## Client

Use the injected resource client to request other resources. The following request executes a request to the `app://self/blog/posts` resource with the query `?id=1`.

```php
use BEAR\Resource\ResourceInterface;

class Index extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    ){}

    public function onGet(): static
    {
        $this->body = [
            'posts' => $this->resource->get('app://self/blog/posts', ['id' => 1])
        ];

        return $this;
    }
}
```

This is an `eager` request that executes immediately.[^history]

[^history]: Historical notations such as `$this->resource->get->uri('app://self/posts')->withQuery(['id' => 1])->eager->request();` (PHP 5.x and later) and `$this->resource->get->uri('app://self/posts')(['id' => 1]);` (PHP 7.x and later) remain valid.

## Lazy evaluation

It is also possible to generate the request itself rather than its result and delay execution.

```php
$request = $this->resource->get->uri('app://self/posts')->withQuery(['id' => 1]); // callable
$posts = $request();
```

When this request is embedded in a template or resource, it is evaluated lazily. That is, when it is not evaluated, the request is not made and incurs no execution cost.

```php
$this->body = [
    'lazy' => $this->resource->get->uri('app://self/posts')->withQuery(['id' => 3])
];
```

The `#[Embed]` attribute provides a declarative way to express the same lazy embedding. See [Resource Link](resource_link.html) for details.

## Cache

In addition to regular TTL caching, BEAR.Sunday supports REST client caching and advanced partial caching (donut caching), including CDN. See [Cache](cache.html) for details. For the traditional `@Cacheable` annotation, see the previous [Resource (v1)](resourcev1.html#resource-cache) document.

## Link

One important REST constraint is resource linking. ResourceObject supports both internal and external links. See [Resource Link](resource_link.html) for details.

## BEAR.Resource

The functionality of the BEAR.Sunday resource object is also available as a standalone package. See the BEAR.Resource [README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.md).

---
