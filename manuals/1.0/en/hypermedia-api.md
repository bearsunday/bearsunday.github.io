---
layout: docs-en
title: Hypermedia API
category: Manual
permalink: /manuals/1.0/en/hypermedia-api.html
---

# Hypermedia API

## HAL

BEAR.Sunday supports the [HAL](https://en.wikipedia.org/wiki/Hypertext_Application_Language) hypermedia (`application/hal+json`) API.


The HAL resource model consists of the following elements:

 * Link
 * Embedded resources
 * State

HAL is the JSON which represents only the state of the conventional resource plus the link `_links` plus `_embedded` to embed other resources. HAL makes API searchable and can find its API document from the API itself.


### Links


Resources should have a `self` URI

```
{
    "_links": {
        "self": { "href": "/user" }
    }
}
```

### Link Relations

Link rels are the main way of distinguishing between a resource's links.

There is a `rel` (relation) on the link, and it shows how the relationship is linked. It is similar to the `rel` used in the HTML `<link>` and `<a>` tag.

```
{
    "_links": {
        "next": { "href": "/page=2" }
    }
}
```

For more information about HAL please visit [http://stateless.co/hal_specification.html](http://stateless.co/hal_specification.html).

## Resource Class

You can annotate links and embed other resources.

### #[Link]

You can declaratively describe the `@Link` annotation, or dynamic ones are assigned to `body['_links']`.

```php?start_inline
#[Link(rel="user", href="/user")]
#[Link(rel="latest-post", href="/latest-post", title="latest post entrty")]
public function onGet()
```

or

```php?start_inline
public function onGet() {
    if ($hasCommentPrivilege) {
        $this->body += [
            '_links' => [
                'comment' => [
                    'href' => '/comments/{post-id}',
                    'templated' => true
                ]
            ]
        ];
    }
}

```

### #[Embed]

To embed other resources statically, use the `@Embed` annotation, and to embed it dynamically, assign the "request" to` body`.

```php?start_inline
#[Embed(rel="todos", src="/todos{?status}")]
#[Embed(rel="me", src="/me")]
public function onGet(string $status): static

```

or

```php?start_inline
$this->body['_embedded']['todos'] = $this->resource->uri('app://self/todos');
```

## API document service

The API server can also be an API document server. It solves problems such as the time required to create the API document, deviation from actual API, verification, maintenance.

In order for it to be on service, install `bear/api-doc` and install it by inheriting the `BEAR\ApiDoc\ApiDoc` page class

```
composer require bear/api-doc
```

```php
<?php
namespace MyVendor\MyPorject\Resource\Page\Rels;

use BEAR\ApiDoc\ApiDoc;

class Index extends ApiDoc
{
}
```

Publish the folder of JSON Schema to the web

```
ln -s var/json_schema public/schemas
```

API documents are automatically generated using Docblock comments and JSON Schema. The page class has its own renderer and is not affected by `$context`, it serves a document (`text/html`) for people. Since it is not affected by `$context`, you can install either` App` or `Page`.

If CURIEs is installed at the root, the API itself can be used even for raw JSON which is not hypermedia. Documents generated in real time always accurately reflect property information and validation constraints.

### Run demo

```
git clone https://github.com/koriym/Polidog.Todo.git
cd Polidog.Todo/
composer install
composer setup
composer doc
```

Open [docs/index.md](https://github.com/koriym/Polidog.Todo/blob/master/docs/index.md) to see API doc page.

## Browsable

The API set written in HAL functions as **headless REST application**.

You can access all the resources by following the link from the root like the website with the Web-based HAL Browser or the CURL command of the console.

 * [HAL Browser](https://github.com/mikekelly/hal-browser) - [example](http://haltalk.herokuapp.com/explorer/browser.html#/)
 * [hyperagent.js](https://weluse.github.io/hyperagent/)

### HAL Layout <sup style="font-size:0.5em; color:#666; font-weight:normal;">Beta</sup>

Libraries for rendering HAL resources as React/Vue components:

* [hal-layout](https://github.com/koriym/hal-layout) - React
* [hal-layout-vue](https://github.com/koriym/hal-layout-vue) - Vue 3

## Siren

[Siren Module](https://github.com/kuma-guy/BEAR.SirenModule) is also available for [Siren](https://github.com/kevinswiber/siren) hypermedia (`application/vnd.siren+json`) type.
