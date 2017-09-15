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


## Deploy

### ⚠️ Avoid overwriting updates

Overwriting a running project folder with `rsync` or the like has a risk of inconsistency between the resource cache and automatic generation class file created in　`tmp/` and the actual class. On heavily loaded sites, it is possible that multiple jobs such as cache creation and opcode creation are executed at the same time, exceeding the performance capacity of the site.

Set up in a different directory and switch (by symlink) it if the setup is OK.

### 👍🏻 Compilation recommended

When setting up, you can warm up the project using the `vendor/bin/bear.compile` script. The compilation script prepares all static cache files such as dynamically created files and annotations for DI / AOP in advance.

Since injection is done in all classes, there is no problem of DI error at runtime. In addition, although `.env` generally contains credential information such as API key and password, all contents are imported into PHP file and can be deleted after compilation. Compilation makes deploy faster and safer.

**Execution at the console**

```
vendor/bin/bear.compile 'Polidog\Todo' prod-html-app /path/to/prject
```

Deployer's [BEAR.Sunday recipe]((https://github.com/bearsunday/deploy)) is convenient and safe to use. Consider using the other server configuration tool as well as referring or running the Deployer script.
