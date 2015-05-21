---
layout: docs-ja
title: クイックスタート
category: Manual
permalink: /manuals/1.0/ja/quick-start.html
---

## プロジェクトの作成

インストールは [composer](http://getcomposer.org) で行います。

{% highlight bash %}
composer create-project -n bear/skeleton MyVendor.MyPackage ~1.0@dev
cd MyVendor.MyPackage
composer install
{% endhighlight %}

次にPageリソースを作成します。PageリソースはWebページに対応したクラスです。 `MyVendor.MyPackage/src/Resource/Page/Hello.php`に作成します。

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

GETメソッドでリクエストされると`$name`に`$_GET['name']`が渡されるので、挨拶を`greeting`にセットし`$this`を返します。

作成したアプリケーションはコンソールでもWebサーバーでも動作します。

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

ビルトインウェブサーバーを起動し、 http://127.0.0.1:8080/hello にアクセスします。

{% highlight bash %}
php -S 127.0.0.1:8080 var/www/index.php
{% endhighlight %}
