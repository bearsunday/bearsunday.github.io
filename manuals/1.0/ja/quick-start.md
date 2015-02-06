---
layout: docs-ja
title: クイックスタート
category: Manual
permalink: /manuals/1.0/ja/quick-start.html
---

## プロジェクトの作成

インストールは [composer](http://getcomposer.org) で行います。

{% highlight bash %}
composer create-project bear/skeleton MyVendor.MyPackage ~1.0@dev
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

このページはGETメソッドでリクエストされると`Hello`と`$_GET['name']`文字列を連結して`greeting`にセットします。
作成したアプリケーションはコンソールでもWebサーバーでも動作します。

{% highlight bash %}
php bootstrap/web.php get /hello
php bootstrap/web.php get '/hello?name=World'

code: 200
header:
body:
{
    "greeting": "Hello World",
    "_links": {
        "self": {
            "href": "/hello?name=World"
        }
    }
}
{% endhighlight %}

次にPHPサーバを起動してみましょう。

{% highlight bash %}
php -S 0.0.0.0:8080 var/www/index.php
{% endhighlight %}
