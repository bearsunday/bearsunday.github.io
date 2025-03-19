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
cd MyVendor.MyProject
```

次にPageリソースを作成します。PageリソースはWebページに対応したクラスです。`src/Resource/Page/Hello.php`に作成します。

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

GETメソッドでリクエストされると`$name`に`$_GET['name']`が渡されるので、挨拶を`greeting`にセットし`$this`を返します。

作成したアプリケーションはコンソールでもWebサーバーでも動作します。

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

ビルトインウェブサーバーを起動し

```bash
php -S 127.0.0.1:8080 -t public
```

webブラウザまたはcurlコマンドで[http://127.0.0.1:8080/hello](http://127.0.0.1:8080/hello)をリクエストします。

```bash
curl -i 127.0.0.1:8080/hello
```
