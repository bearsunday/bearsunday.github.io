---
layout: docs-ja
title: クイックスタート
category: Manual
permalink: /manuals/1.0/ja/quick-start.html
---


# クイックスタート

インストールは [composer](http://getcomposer.org) で行います。

```bash
composer create-project -n bear/skeleton MyVendor.MyProject
```

次にPageリソースを作成します。PageリソースはWebページに対応したクラスです。 `MyVendor.MyProject/src/Resource/Page/Hello.php`に作成します。

```php?start_inline
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
```

GETメソッドでリクエストされると`$name`に`$_GET['name']`が渡されるので、挨拶を`greeting`にセットし`$this`を返します。

作成したアプリケーションはコンソールでもWebサーバーでも動作します。

```bash
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
```

ビルトインウェブサーバーを起動し、 `http://127.0.0.1:8080/hello` にアクセスします。

```bash
php -S 127.0.0.1:8080 var/www/index.php
```

# クイックAPI

API用パッケージのスケルトンを使った[クイックAPI](quick-api.html)チュートリアルもお試しください。
