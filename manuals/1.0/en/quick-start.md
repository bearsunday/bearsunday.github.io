---
layout: docs-en
title: Quick Start
category: Manual
permalink: /manuals/1.0/en/quick-start.html
---
# Quick Start

Installation is done via [composer](http://getcomposer.org)

```bash
composer create-project -n bear/skeleton MyVendor.MyProject
cd MyVendor.MyProject
```

Next, let's create a new resource. A resource is a class which corresponds, for instance, to a JSON payload (if working with an API-first driven model) 
or a web page.
Create your own basic page resource in `src/Resource/Page/Hello.php`

```php
<?php
namespace MyVendor\MyProject\Resource\Page;

use BEAR\Resource\ResourceObject;

class Hello extends ResourceObject
{
    public function onGet(string $name = 'BEAR.Sunday'): static
    {
        $this->body = [
            'greeting' => 'Hello ' . $name
        ];

        return $this;
    }
}
```

In the above example, when the page is requested using a GET method, `Hello` and the `$name` parameter (which corresponds to `$_GET['name']`) are joined, and assigned to a variable `greeting`.
The BEAR.Sunday application that you have created will work on a web server, but also in the console.

```bash
php bin/page.php get /hello
php bin/page.php get '/hello?name=World'
```

```bash
200 OK
Content-Type: application/hal+json

{
    "greeting": "Hello World",
    "_links": {
        "self": {
            "href": "/hello?name=World"
        }
    }
}
```

Let us fire up the php server and access our page at [http://127.0.0.1:8080/hello](http://127.0.0.1:8080/hello).

```bash
php -S 127.0.0.1:8080 -t public
```

```bash
curl -i 127.0.0.1:8080/hello
```
