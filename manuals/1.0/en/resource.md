---
layout: docs-en
title: Resource
category: Manual
permalink: /manuals/1.0/en/resource.html
---

# Resource

A BEAR.Sunday application is a collection of RESTful resources. Every piece of information or functionality the application handles is represented as a "resource" with its own URI, just like a web page.

Every resource is accessed through a **uniform interface** shared with HTTP—GET, POST, and so on. Because the interface is uniform, the same resource can be used the same way from the web, from the console, or from an in-process call from another resource. There's no need for a separate controller or entry point per access method, and the resource's meaning can be understood with nothing more than knowledge of HTTP.

## Object as a service

`ResourceObject` is a resource expressed in PHP. It's an **object-as-a-service**: HTTP methods are mapped to PHP methods.

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

A resource class has a URI such as `page://self/index`, similar to a web URI, and exposes `on` methods—`onGet`, `onPost`, and so on—that conform to the HTTP methods. An `on` method determines its own resource state (`code`, `headers`, `body`) from the given parameters and returns `$this`. From a stateless request, the resource's state is generated as a resource representation and transferred to the client. ([Representational State Transfer](http://en.wikipedia.org/wiki/REST))

`code` and `headers` have default values (`200` and empty headers), so the example above only sets `body`. Override the defaults only when the response needs to differ from them.

```php
class Todo extends ResourceObject
{
    public function onPost(string $id, string $todo): static
    {
        $this->code = 201; // overrides the default 200
        $this->headers = [
            'Location' => '/todo/new_id'
        ];

        return $this;
    }
}
```

## page resources and app resources

Resources fall into two schemes, by role.

| Scheme | Role |
|--------|------|
| page | A public resource exposed externally |
| app | A private resource, not directly accessible from outside |

A `page` resource, which receives requests from the web, the console, or other external sources, requests `app` resources to determine its own state. The `app` resources own the domain's "what"—information and functionality—while `page` resources own the "how it's presented," and the two concerns stay separate.

The benefit of this separation shows up when you change context. In an HTML application, `page` resources output HTML while `app` resources stay private. To turn the same application into a mobile app's backend, a context change publishes the `app` resources as an API that outputs JSON. The resource code doesn't change—only its visibility and representation do.

## URI

URIs are mapped to PHP classes. Applications use the URI instead of the class name to access resources.

| URI | Class |
|-----+-------|
| page://self/ | Koriym\Todo\Resource\Page\Index |
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts?id=3 | Koriym\Todo\Resource\App\Blog\Posts |

* `index` can be omitted.

## Methods

Resources are accessed through six methods that correspond to HTTP methods. Rather than mapping to CRUD, they're distinguished by whether they're **safe** (don't change resource state) or **idempotent** (repeating the same request produces the same result).

| Method | Meaning | [Safe](https://developer.mozilla.org/en-US/docs/Glossary/Safe/HTTP) | [Idempotent](https://developer.mozilla.org/en-US/docs/Glossary/Idempotent) | [Cacheable](https://developer.mozilla.org/en-US/docs/Glossary/cacheable) |
|-|-|-|-|-|
| GET | Retrieve a representation of the resource | Yes | Yes | Yes |
| POST | Process the representation in the request (e.g., add a new resource) | No | No | No |
| PUT | Replace the resource with the payload, or create it if absent | No | Yes | No |
| PATCH | Partially modify the resource [^patch] | No | No | No |
| DELETE | Delete the resource | No | Yes | No |
| OPTIONS | Get information about the parameters and response required for the request [^json-schema] | Yes | Yes | No |

[^patch]: [https://www.rfc-editor.org/rfc/rfc5789](https://www.rfc-editor.org/rfc/rfc5789)

[^json-schema]: Retrieving response information requires a JsonSchema declaration.

## Parameters

The request method's arguments are passed the request values that correspond to their variable names.

```php
class Index extends ResourceObject
{
    // $_GET['id'] becomes $id
    public function onGet(int $id): static
    {
        return $this;
    }

    // $_POST['name'] becomes $name
    public function onPost(string $name): static
    {
        return $this;
    }
}
```

See [Resource Parameters](resource_param.html) for other methods and how to pass external variables such as cookies as parameters.

## Rendering and transfer

A ResourceObject's request methods aren't concerned with the resource's representation. An injected renderer generates the representation, and a responder outputs it. See [Rendering and Transfer](resource_renderer.html) for details.

## Client

Use the injected resource client to request other resources. The following request calls the `app://self/blog/posts` resource with the query `?id=1`.

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

This is an `eager` request—it executes immediately. [^history]

[^history]: Historical notations from earlier versions remain valid, including `$this->resource->get->uri('app://self/posts')->withQuery(['id' => 1])->eager->request();` (PHP 5.x and up) and `$this->resource->get->uri('app://self/posts')(['id' => 1]);` (PHP 7.x and up).

## Lazy evaluation

Rather than the request result, you can generate the request itself and defer execution.

```php
$request = $this->resource->get->uri('app://self/posts')->withQuery(['id' => 1]); // callable
$posts = $request();
```

Embed this request in a template or resource, and it's evaluated lazily—when it's never evaluated, the request is never made and costs nothing.

```php
$this->body = [
    'lazy' => $this->resource->get->uri('app://self/posts')->withQuery(['id' => 3])
];
```

The `#[Embed]` attribute lets you declare the same lazy embedding declaratively. See [Resource Link](resource_link.html) for details.

## Cache

Along with regular TTL caching, BEAR.Sunday supports REST client caching and advanced partial caching (doughnut caching) that includes CDNs. See [Cache](cache.html) for details.

## Link

Resource linking is one of the important REST constraints. ResourceObject supports both internal and external linking. See [Resource Link](resource_link.html) for details.

## BEAR.Resource

BEAR.Sunday's resource object functionality is also available as a stand-alone package. See the BEAR.Resource [README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.md).

---
