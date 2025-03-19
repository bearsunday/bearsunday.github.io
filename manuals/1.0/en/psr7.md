---
layout: docs-en
title: PSR-7
category: Manual
permalink: /manuals/1.0/en/psr7.html
---

# PSR-7

You can get server side request information using [PSR7 HTTP message interface](https://www.php-fig.org/psr/psr-7/). Also, you can run BEAR.Sunday application as PSR 7 middleware.


## HTTP Request

PHP has `Superglobals` such as `$_SERVER` and `$_COOKIE`, but instead of using them it receives server side request information using the PSR-7 HTTP message interface.


### ServerRequest （general）

````php
class Index extends ResourceObject
{
    public function __construct(ServerRequestInterface $serverRequest)
    {
        // retrieve cookies
        $cookie = $serverRequest->getCookieParams(); // $_COOKIE
    }
}
````

### Upload Files

````php

use Psr\Http\Message\UploadedFileInterface;
use Ray\HttpMessage\Annotation\UploadFiles;

class Index extends ResourceObject
{
    /**
     * @UploadFiles
     */
    public function __construct(array $files)
    {
        // retrieve file name
        $file = $files['my-form']['details']['avatar'][0]
        /* @var UploadedFileInterface $file */
        $name = $file->getClientFilename(); // my-avatar3.png
    }
}
````

### URI

````php

use Psr\Http\Message\UriInterface;

class Index extends ResourceObject
{
    public function __construct(UriInterface $uri)
    {
        // retrieve host name
        $host = $uri->getHost();
    }
}
````

## PSR-7

An existing BEAR.Sunday application can work as
a [PSR-7](http://www.php-fig.org/psr/psr-7/) middleware with these easy steps:

1) Add `bear/middleware` package then replace [bootstrap.php](https://github.com/bearsunday/BEAR.Middleware/blob/1.x/bootstrap/bootstrap.php) script.

```bash
composer require bear/middleware
```
```bash
cp vendor/bear/middleware/bootstrap/bootstrap.php bootstrap/bootstrap.php
```

2) Replace `__PACKAGE__\__VENDOR__` in bootstrap.php to application namespace.

Stat the server.

```bash
php -S 127.0.0.1:8080 -t public
```

### Stream

BEAR.Sunday supports HTTP body of a message output in a [stream](http://php.net/manual/ja/intro.stream.php).

In `ResourceObject`, you can mix stream with a normal string. The output is converted to a single stream.
`StreamTransfer` is the default HTTP transfer. Seem more at [Stream Response](http://bearsunday.github.io/manuals/1.0/en/stream.html).

### New Project

You can also create a BEAR.Sunday PSR-7 project with `bear/project` from scratch.

```
composer create-project bear/project my-psr7-project
cd my-psr7-project/
php -S 127.0.0.1:8080 -t public
```

### PSR-7 middleware

 * [oscarotero/psr7-middlewares](https://github.com/oscarotero/psr7-middlewares)
