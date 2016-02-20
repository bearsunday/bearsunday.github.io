---
layout: docs-en
title: Quick Start
category: Manual
permalink: /manuals/1.0/en/quick-start.html
---
# Quick Satrt

Installation is done via [composer](http://getcomposer.org)

{% highlight bash %}
composer create-project -n bear/skeleton MyVendor.MyProject
{% endhighlight %}

Next, let's create a `page resource`. A page resource is a class which is corresponds to a web page.
Create your own basic page resource in `MyVendor.MyPackage/src/Resource/Page/Hello.php`

{% highlight php %}
<?php

namespace MyVendor\MyProject\Resource\Page;

use BEAR\Resource\ResourceObject;

class Hello extends ResourceObject
{
    public function onGet($name = 'BEAR.Sunday')
    {
        $this['greeting'] = 'Hello ' . $name;

        return $this;
    }
}
{% endhighlight %}

In the above example, when the page is requested using a GET method, `Hello` and `$_GET['name']` strings are joined, and assigned to a variable `greeting`.
The BEAR.Sunday application that you have created will work on a web server, but also in the console.

{% highlight bash %}
php bootstrap/web.php get /hello
php bootstrap/web.php get '/hello?name=World'

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
{% endhighlight %}

Let us fire up the php server and access our page at `http://127.0.0.1:8080/hello`.

{% highlight bash %}
php -S 127.0.0.1:8080 var/www/index.php
{% endhighlight %}
