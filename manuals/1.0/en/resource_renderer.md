---
layout: docs-en
title: Rendering and Transfer
category: Manual
permalink: /manuals/1.0/en/resource_renderer.html
---

# Rendering and transfer

The request method of a ResourceObject is not concerned with the representation of the resource. The context-sensitive injected renderer generates the representation of the resource. The same application can be output in HTML or JSON and benefit by simply changing the context.

## Lazy evaluation

Rendering occurs when the resource is string-evaluated.

```php?start_inline

$weekday = $api->resource->get('app://self/weekday', ['year' => 2000, 'month'=> 1, 'day'=> 1]);
var_dump($weekday->body);
//array(1) {
//    ["weekday"]=>
//  string(3) "Sat"
//}

echo $weekday;
//{
//    "weekday": "Sat",
//    "_links": {
//    "self": {
//        "href": "/weekday/2000/1/1"
//        }
//    }
//}
```

## Renderer

Each ResourceObject is injected with a renderer for its representation as specified by its context. When performing resource-specific rendering, inject or set the `renderer` property.

Example: If you write a renderer for the default JSON representation from scratch

```php?start_inline
class Index extends ResourceObject
{
    #[Inject]
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

Transfers the resource representation injected into the root object `$app` to the client (console or web client). Normally, output is done with the `header` function or `echo`, but for large data, etc., [stream transfer](stream.html) is useful.

Override the `transfer` method to perform resource-specific transfers.

```php
public function transfer(TransferInterface $responder, array $server)
{
    $responder($this, $server);
}
```

## Resource autonomy

Each resource class has the ability to change its own resource state upon request and transfer it as an expression.
