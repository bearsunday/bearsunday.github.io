---
layout: docs-en
title: Production
category: Manual
permalink: /manuals/1.0/en/production.html
---

# Production

In this section, we will cover how to setup the cache and the system for production environment.

## Boot file

If the context is prefixed with `prod-`, the `$app` application object will be cached.

Cache drivers like `ApcCache` or `FilesystemCache` will be used in response to the environment automatically.

```php?start_inline
$context = 'prod-app';
require dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';
```

## Cache settings

## ProdModule

In the default `ProdModule` of `BEAR.Package`, `ApcCache` is designed for one single web server.

For multiple servers, you need to set the shared cache storage. You can implement application specific src/Module/ProdModule.php.

```php?start_inline
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
```
`Cache` interface annotated with `@Storage` is defined for query repository and it is a shared storage for web servers.

We cannot use `ApcCache` on multiple servers, however, we have the options to use
[Redis](http://doctrine-orm.readthedocs.org/en/latest/reference/caching.html#redis) or other storage by creating an adapter.
([memcached](http://doctrine-orm.readthedocs.org/en/latest/reference/caching.html#memcached) is also available, but be careful about the capacity and volatility because it is stored in memory.）

## HTTP Cache

The resource annotated with the `@Cacheable`, outputs an `ETag` entity tag.

By using this `ETag`, we can return a `304` (Not Modified) appropriate response when the resource is not modified.

（Therefore, we can save not only the cpu cost but also the network transfer cost.）

### App

To use `HttpCache` in script, we are going to inject `HttpCache` using `HttpCacheInject` trait in `App` class.

```php?start_inline
namespace MyVendor\MyApi\Module;

use BEAR\QueryRepository\HttpCacheInject; // Add this line
use BEAR\Sunday\Extension\Application\AbstractApp;
use Ray\Di\Di\Inject;

class App extends AbstractApp
{
    use HttpCacheInject; // Add this line
}
```

### bootstrap

Next, modify the `route` section in `bootstrap/bootstrap.php` to return a `304` when the contents are not modified by adding an `if` conditional statement.

```php?start_inline
route: {
    $app = (new Bootstrap)->getApp(__NAMESPACE__, $context);
    if ($app->httpCache->isNotModified($_SERVER)) {
        http_response_code(304);
        exit(0);
}

```

`ETag` is also updated automatically, but you need to specify the relation of the resource caches using `@Refresh` and `@Purge` annotations.

## Extension

Optimize performances by installing pecl extensions.

 * [PECL/uri_template](http://pecl.php.net/package/uri_template) URI Template

```
pecl install uri_template
```

Confirmation

```
composer show --platform
ext-uri_template    1.0      The uri_template PHP extension
```

## Deploy

Please refer to [BEAR.Sunday Deployer.php support](https://github.com/bearsunday/deploy) for deploying with [Deployer](http://deployer.org/).
