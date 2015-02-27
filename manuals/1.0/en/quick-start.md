---
layout: docs-en
title: Quick Start
category: Manual
permalink: /manuals/1.0/en/quick-start.html
---

## Creating your project

Installation is done via [composer](http://getcomposer.org)

{% highlight bash %}
composer create-project bear/skeleton MyVendor.MyPackage ~1.0@dev
cd MyVendor.MyPackage
composer install
{% endhighlight %}

Next, let's create a Page resource. Page resource is a class which is corresponding to a Web page.
Create your basic page resource `MyVendor.MyPackage/src/Resource/Page/Hello.php`

{% highlight php %}
<?php

namespace MyVendor\MyPackage\Resource\Page;

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

In the above example, when the page is requested using GET method, `Hello` and `$_GET['name']` strings are joined, and assigned to a variable `greeting`.
BEAR.Sunday application that you create will work on Web server, but also in the console.

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

Let us fire the php server

{% highlight bash %}
php -S 127.0.0.1:8080 var/www/index.php
{% endhighlight %}
