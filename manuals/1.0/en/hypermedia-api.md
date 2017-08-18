---
layout: docs-en
title: Hypermedia API
category: Manual
permalink: /manuals/1.0/en/hypermedia-api.html
---

# Hypermedia API

## HAL

BEAR.Sunday supports the [HAL](https://en.wikipedia.org/wiki/Hypertext_Application_Language) hypermedia (`application/hal+json`) API.


The HAL resource model consists of the following elements.

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

The link has a `rel` (relationship), which indicates the meaning of the link. `Rel` distinguishes resource links. As with Web pages, link operations to other resources and resources related to resources.

```
{
    "_links": {
        "next": { "href": "/page=2" }
    }
}
```

For more information about HAL please visit [http://stateless.co/hal_specification.html](http://stateless.co/hal_specification.html).

## Resoure Class

You can annotate links and embed other resources.

### @Link

You can declaratively describe the `@Link` annotation, or dynamic ones are assigned to `body['_links']`. 

```
/**
 * @Link(rel="user", href="/user")
 * @Link(rel="latest-post", href="/latest-post", title="latest post entrty")
 */
public function onGet()
```

or

```
public function onGet() {
    if ($hasCommentPrivilege) {
        $this->body += [
            '_links' => [
                'rel' => 'comment',
                'href' => '/comments/{post-id}',
                'templated' => true
            ]
        ];
    }
}

```
### @Embeded

To embed other resources statically, use the `@ Embeded` annotation, and to embed it dynamically, assign the "request" to` body`.

```
/**
 * @Embed(rel="todos", src="/todos{?status}")
 * @Embed(rel="me", src="/me")
 */
public function onGet() : ResourceObject

```

or

```
$this->body['_embedded']['todos'] = $this->resource->uri('app://self/todos');
```

## CURIEs

"CURIE"s help providing links to resource documentation.　Place `index.json` with a link to each API documentation, or such a resource class at the root.



```php
<?php

use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    public $body = [
        'message' => 'Welcome to the Polidog.Todo API ! Our hope is to be as self-documenting and RESTful as possible.',
        '_links' => [
            'self' => [
                'href' => '/',
            ],
            'curies' => [
                'name' => 'doc',
                'href' => 'http://apidoc.example.com/rels/{?rel}',
                'templated' => true
            ],
            'doc:todo' => [
                'href' => '/todo/{id}',
                'title' => 'todo item',
                'templated' => true
            ]
        ]
    ];

    public function onGet()
    {
        return $this;
    }
}
```

In `_links`, specify a special token that defines the document` curies`. In `curies`, specify` href` which indicates the document URI of the resource and its name with `name`.

In this example you will find that you can access the `http: // quickoc.example.com/rels/? rel = todo` URL to get documentation on the` todo` resource.

## API document service

The API server can also be an API document server. It solves problems such as the time required to create API document, deviation from actual API, verification, maintenance.

In order to service it install `bear/api-doc` and install it by inheriting the `BEAR\ApiDoc\ApiDoc` page class.

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

Publish the folder of Json Schema to the web.

```
ln -s var/json_schema public/schemas
```

API Documents are automatically generated using Docblock comments and Json Shcema. The page class has its own renderer and is not affected by `$context`, it serves a document (`text/html`) for people. Since it is not affected by `$context`, you can install either` App` or `Page`.

If CURIEs is installed at the root, the API itself can be used even for raw JSON which is not hypermedia. Documents generated in real time always accurately reflect property information and validation constraints.


## Browsable

The API set written in HAL functions as **headless REST application**.

You can access all the resources by following the link from the root like the web site with the Web based HAL Browser or the CURL command of the console.

 * [HAL Browser](https://github.com/mikekelly/hal-browser) - [example](http://haltalk.herokuapp.com/explorer/browser.html#/)
 * [hyperagent.js](https://weluse.github.io/hyperagent/)


# Siren

[Siren Module](https://github.com/kuma-guy/BEAR.SirenModule) is also available for [Siren](https://github.com/kevinswiber/siren) hypermedia (`application/vnd.siren+json`) type.