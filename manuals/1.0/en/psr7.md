---
layout: docs-en
title: PSR7
category: Manual
permalink: /manuals/1.0/en/psr7.html
---

*[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/psr7) needs to be proofread by an English speaker. If interested please send me a pull request. Thank you.*

## Middleware

An existing BEAR.Sunday application can work as 
[PSR7](http://www.php-fig.org/psr/psr-7/) middleware with easy step.

1) Add `bear/middleware` package then replace [bootstrap.php](https://github.com/bearsunday/BEAR.Middleware/blob/1.x/bootstrap/bootstrap.php) script.

{% highlight bash %}
composer require bear/middleware
{% endhighlight %}
{% highlight bash %}
cp vendor/bear/middleware/bootstrap/bootstrap.php bootstrap/bootstrap.php
{% endhighlight %}

2) Replace `__PACKAGE__\__VENDOR__` in bootstrap.php to application namespace. 

Stat the server.

{% highlight bash %}
php -S 127.0.0.1:8080 -t var/www
{% endhighlight %}

## Stream

BEAR.Sunday supports http body of a message output in a [stream](http://php.net/manual/ja/intro.stream.php).

In `ResourceObject`, you can mix with stream and normal string. The output is conveted to single stream.

{% highlight bash %}
<?php
    public function onGet($name = 'BEAR.Sunday')
    {
        $fp = fopen(__DIR__ . '/image.jpg', 'r');
        stream_filter_append($fp, 'convert.base64-encode');
        $this['greeting'] = 'Hello ' . $name;
        $this['image'] = $fp; // image in base64 format

        return $this;
    }
{% endhighlight %}

## New Project

You can create BEAR.Sunday PSR7 project with `bear/project`.

{% highlight bash %}
composer create-project bear/project my-awesome-project
cd my-awesome-project/
php -S 127.0.0.1:8080 -t var/www/
{% endhighlight %}

Add other middleware or Ray.Di modules upon your request.

 * [oscarotero/psr7-middlewares](https://github.com/oscarotero/psr7-middlewares)
 * [Ray packages](https://packagist.org/packages/ray/)
 * [BEAR packages](https://packagist.org/packages/bear/)
