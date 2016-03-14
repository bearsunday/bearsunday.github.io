---
layout: docs-en
title: Production
category: Manual
permalink: /manuals/1.0/en/production.html
---

# Production

In this section, we'll cover how to set cache and script for production environment.

## Boot file

In the context starting with `prod-`, an `$app` application object will be cached.

Cache drivers like `ApcCache` or `FilesystemCache` will be selected in response to the environment automatically.

{% highlight php %}
<?php
$context = 'prod-app';
require dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';
{% endhighlight %}

## Cache settings

## ProdModule

In `ProdModule` production module in `BEAR.Package`, `ApcCache` cache is designed for one single web server.

As for multiple servers, we need to set shared cache storage.
In that case, you can implement by creating application specific `ProdModule` to `src/Module/ProdModule.php`.

{% highlight php %}
<?php
namespace BEAR\HelloWorld\Module;

use BEAR\RepositoryModule\Annotation\Storage;
use BEAR\Package\Context\ProdModule as PackageProdModule;
use Doctrine\Common\Cache\Cache;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

use Doctrine\Common\Cache\ApcCache;

class ProdModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $cache = ApcCache::class;
        // shared cache
        $this->bind(CacheProvider::class)->annotatedWith(Storage::class)->to($cache)->in(Scope::SINGLETON);
        // cache per server
        $this->bind(Cache::class)->to($cache)->in(Scope::SINGLETON);
        // install package ProdModule
        $this->install(new PackageProdModule);
    }
}
{% endhighlight %}
`Cache` interface annotated with `@Storage` is defined for query repository and it is shared storage for web server.

We cannot use `ApcCache` on multiple servers, however, we have options to use 
[Redis](http://doctrine-orm.readthedocs.org/en/latest/reference/caching.html#redis) or other storage by creating adapter.
([memcached](http://doctrine-orm.readthedocs.org/en/latest/reference/caching.html#memcached) is also available, but be careful about the capacity and volatile because it is a memory storage.）

## HTTP Cache

The resource annotated with `@Cacheable` cacheable, outputs `ETag` entity tag.

By using this `ETag`, we can return `304` (Not Modified) appropriate response when the resource is not modified.

（In this time, we can save not only cpu cost but also transfer cost of network.）

### App

To use `HttpCache` in script, we are going to inject `HttpCache` using `HttpCacheInject` trait in `App` class.

{% highlight php %}
<?php

namespace MyVendor\MyApi\Module;

use BEAR\QueryRepository\HttpCacheInject; // Add this line
use BEAR\Sunday\Extension\Application\AbstractApp;
use Ray\Di\Di\Inject;

class App extends AbstractApp
{
    use HttpCacheInject; // Add this line
}
{% endhighlight %}

### bootstrap

Next, modify `route` section in `bootstrap/bootstrap.php` for returning `304` when the contents are not modified by adding
`if` conditional statement.

{% highlight php %}
<?php
route: {
    $app = (new Bootstrap)->getApp(__NAMESPACE__, $context);
    if ($app->httpCache->isNotModified($_SERVER)) {
        http_response_code(304);
        exit(0);
    }

{% endhighlight %}

`ETag` is also updated automatically,
but you need to specify the relation of resource caches using `@Refresh` and `@Purge` annotations.

## Extension

Optimize performances by installing pecl extensions.

 * [PECL/uri_template](http://pecl.php.net/package/uri_template) URI Template
 * [PECL/igbinary](https://pecl.php.net/package/igbinary) Optimization for serializing

```
pecl install uri_template
pecl install igbinary
```

Confirmation

```
composer show --platform
ext-uri_template    1.0      The uri_template PHP extension
```

## Deploy

Please referer [BEAR.Sunday Deployer.php support](https://github.com/bearsunday/deploy) for deploy with [Deployer](http://deployer.org/).