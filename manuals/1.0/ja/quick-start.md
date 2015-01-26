---
layout: docs-ja
title: クイックスタート
category: Manual
permalink: /manuals/1.0/ja/quick-start.html
---

## プロジェクトの作成

インストールは [composer](http://getcomposer.org) で行います。

{% highlight bash %}
create-project bear/skeleton MyVendor.MyPackage ~1.0@dev
cd MyVendor.MyPackage
composer install
{% endhighlight %}

Pageリソースファイルを`MyVendor.MyPackage/Resource/Page/Hello.php`に作成します。

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

Pageリソースは対応するWebページを表します。このページはGETメソッドでリクエストされると`Hello`と`$_GET['name']`文字列を連結して`greeting`にセットします。
作成したアプリケーションはコンソールでもWebサーバーでも動作します。

{% highlight bash %}
php bootstrap/web.php get /hello

code: 200
header:
body:
{
    "greeting": "Hello BEAR.Sunday",
    "_links": {
        "self": {
            "href": "/hello"
        }
    }
}
{% endhighlight %}

次にPHPサーバを起動してみましょう。

{% highlight bash %}
php -S 0.0.0.0:8080 var/www/index.php
{% endhighlight %}
