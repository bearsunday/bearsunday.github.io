---
layout: docs-en
title: Content Negotiation
category: Manual
permalink: /manuals/1.0/en/content-negotiation.html
---

# Content Negotiation

In HTTP, [Content Negotiation](https://en.wikipedia.org/wiki/Content_negotiation) is a mechanism used to provide various versions of resources for the same URL. BEAR.Sunday supports server-side content negotiation of media type 'Accept' and 'Accept-Language' of language. It can be specified on an application basis or resource basis.

## Install

Install [BEAR.Accept](https://github.com/bearsunday/BEAR.Accept) with composer

```bash
composer require bear/accept ^0.1
```

Next, save the context corresponding to the `Accept *` request header in `/var/locale/available.php`

```php?
<?php
return [
    'Accept' => [
        'text/hal+json' => 'hal-app',
        'application/json' => 'app',
        'cli' => 'cli-hal-app'
    ],
    'Accept-Language' => [ // lower case for key
        'ja-jp' => 'ja',
        'ja' => 'ja',
        'en-us' => 'en',
        'en' => 'en'
    ]
];
```

The `Accept` key array specifies an array whose context is a value with the media type as a key. `cli` is not used in web access in the context of console access.

The `Accept-Language` key array specifies an array with the context key as the key for the language.

## Enable by Application

Change `public/index.php` to enable content negotiation **throughout the application**.

```php
<?php
use BEAR\Accept\Accept;

require dirname(__DIR__) . '/vendor/autoload.php';

$accept = new Accept(require dirname(__DIR__) . '/var/locale/available.php');
list($context, $vary) = $accept($_SERVER);

require dirname(__DIR__) . '/bootstrap/bootstrap.php';
```

For example, in the above setting, the access context of the following `Accept*` header will be `prod-hal-ja-app`.

```
Accept: application/hal+json
Accept-Language: ja-JP
```

At this time `JaModule` requires binding for Japanese text. For details, refer to the demo application [MyVendor.Locale](https://github.com/koriym/MyVendor.Locale).

## Enable by Resource

To do content negotiation on a resource basis, install the `AcceptModule` module and use the `@Produces` annotation.

### Module Install

```php?start_inline
protected function configure()
{
    // ...
    $available = $appDir . '/var/locale/available.php';
    $this->install(new AcceptModule(available));
}
```

## @Produces annotation

```php?start_inline
use BEAR\Accept\Annotation\Produces;

/**
 * @Produces({"application/hal+json", "text/csv"})
 */
public function onGet()
```

Annotate available media type from left by priority. The representation (JSON or HTML) is changed by the contextual renderer. You do not need to add `Vary` header manually unlike application level content-negotiation.

## Access using curl

Specify the `Accept*` header with the `-H` option.

```
curl -H 'Accept-Language: en' http://127.0.0.1:8080/
```

```
curl -i -H 'Accept-Language: en' -H 'Accept: application/hal+json' http://127.0.0.1:8080/
```

```
HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Date: Fri, 11 Aug 2017 08:32:33 +0200
Connection: close
X-Powered-By: PHP/7.1.4
Vary: Accept, Accept-Language
content-type: application/hal+json

{
    "greeting": "Hello BEAR.Sunday",
    "_links": {
        "self": {
            "href": "/index"
        }
    }
}
```
