---
layout: docs-en
title: Production
category: Manual
permalink: /manuals/1.0/en/production.html
---
# Production

For BEAR.Sunday's default `prod` binding, the application customizes the module according to each [deployment environment](https://en.wikipedia.org/wiki/Deployment_environment) and performs the binding.

## Default ProdModule

The default `prod` binding binds the following interfaces:

* Error page generation factory
* PSR logger interface
* Local cache
* Distributed cache

See [ProdModule.php](https://github.com/bearsunday/BEAR.Package/blob/1.x/src/Context/ProdModule.php) in BEAR.Package for details.

## Application's ProdModule

Customize the application's `ProdModule` in `src/Module/ProdModule.php` against the default ProdModule. Error pages and distributed caches are particularly important.

```php
<?php
namespace MyVendor\Todo\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\QueryRepository\CacheVersionModule;
use BEAR\Resource\Module\OptionsMethodModule;
use BEAR\Package\AbstractAppModule;

class ProdModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new PackageProdModule);       // Default prod settings
        $this->override(new OptionsMethodModule);    // Enable OPTIONS method in production as well
        $this->install(new CacheVersionModule('1')); // Specify resource cache version

        // Custom error page
        $this->bind(ErrorPageFactoryInterface::class)->to(MyErrorPageFactory::class);
    }
}
```

## Cache

There are two types of caches: a local cache and a distributed cache that is shared between multiple web servers.
Both caches default to [PhpFileCache](https://www.doctrine-project.org/projects/doctrine-cache/en/1.10/index.html#phpfilecache).

### Local Cache

The local cache is used for caches that do not change after deployment, such as annotations, while the distributed cache is used to store resource states.

### Distributed Cache

To provide services with two or more web servers, a distributed cache configuration is required.
Modules for each of the popular [memcached](http://php.net/manual/en/book.memcached.php) and [Redis](https://redis.io) cache engines are provided.


### Memcached

```php
<?php
namespace BEAR\HelloWorld\Module;

use BEAR\QueryRepository\StorageMemcachedModule;
use BEAR\Resource\Module\ProdLoggerModule;
use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\Package\AbstractAppModule;
use Ray\Di\Scope;

class ProdModule extends AbstractModule
{
    protected function configure()
    {
        // memcache
        // {host}:{port}:{weight},...
        $memcachedServers = 'mem1.domain.com:11211:33,mem2.domain.com:11211:67';
        $this->install(new StorageMemcachedModule($memcachedServers));

        // Install Prod logger
        $this->install(new ProdLoggerModule);
        // Install default ProdModule
        $this->install(new PackageProdModule);
    }
}
```

### Redis


```php?start_inline
// redis
$redisServer = 'localhost:6379'; // {host}:{port}
$this->install(new StorageRedisModule($redisServer));
```

In addition to simply updating the cache by TTL for storing resource states, it is also possible to operate (CQRS) as a persistent storage that does not disappear after the TTL time.
In that case, you need to perform persistent processing with `Redis` or prepare your own storage adapter for other KVS such as Cassandra.

### Specifying Cache Time

To change the default TTL, install `StorageExpiryModule`.

```php?start_inline
// Cache time
$short = 60;
$medium = 3600;
$long = 24 * 3600;
$this->install(new StorageExpiryModule($short, $medium, $long));
```
### Specifying Cache Version

Change the cache version when the resource schema changes and compatibility is lost. This is especially important for CQRS operation that does not disappear over TTL time.

```
$this->install(new CacheVersionModule($cacheVersion));
```

To discard the resource cache every time you deploy, it is convenient to assign a time or random value to `$cacheVersion` so that no change is required.

## Logging

`ProdLoggerModule` is a resource execution log module for production. When installed, it logs requests other than GET to the logger bound to `Psr\Log\LoggerInterface`.
If you want to log on a specific resource or specific state, bind a custom log to [BEAR\Resource\LoggerInterface](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/LoggerInterface.php).

```php
use BEAR\Resource\LoggerInterface;
use Ray\Di\AbstractModule;

final class MyProdLoggerModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(LoggerInterface::class)->to(MyProdLogger::class);
    }
}
```

The `__invoke` method of [LoggerInterface](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/LoggerInterface.php) passes the resource URI and resource state as a `ResourceObject` object, so log the necessary parts based on its contents.
Refer to the [existing implementation ProdLogger](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/ProdLogger.php) for creation.

## Deployment

### ⚠️ Avoid Overwriting Updates

#### When deploying to a server

* Overwriting a running project folder with `rsync` or similar poses a risk of inconsistency with caches and on-demand generated files, and can exceed capacity on high-load sites.
  Set up in a separate directory for safety and switch if the setup is successful.
* You can use the [BEAR.Sunday recipe](https://github.com/bearsunday/deploy) of [Deployer](http://deployer.org/).

#### When deploying to the cloud

* It is recommended to incorporate compilation into CI as the compiler outputs exit code 1 when it finds dependency issues and 0 when compilation succeeds.

### Compilation Recommended

When setting up, you can **warm up** the project using the `vendor/bin/bear.compile` script.
The compile script creates all static cache files such as dynamically created files for DI/AOP and annotations in advance, and outputs an optimized autoload.php file and preload.php.

* If you compile, the possibility of DI errors at runtime is extremely low because injection is performed in all classes.
* The contents included in `.env` are incorporated into the PHP file, so `.env` can be deleted after compilation.

When compiling multiple contexts (ex. api-app, html-app) in one application, such as when performing content negotiation, it is necessary to evacuate the files.

```
mv autoload.php api.autoload.php  
```

Edit `composer.json` to change the content of `composer compile`.

### autoload.php

An optimized autoload.php file is output to `{project_path}/autoload.php`.
It is much faster than `vendor/autoload.php` output by `composer dumpa-autoload --optimize`.

Note: If you use `preload.php`, most of the classes used are loaded at startup, so the compiled `autoload.php` is not necessary. Please use `vendor/autload.php` generated by Composer.

### preload.php

An optimized preload.php file is output to `{project_path}/preload.php`.
To enable preloading, you need to specify [opcache.preload](https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.preload) and [opcache.preload](https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.preload-user) in php.ini. It is a feature supported in PHP 7.4, but it is unstable in the initial versions of `7.4`. Let's use the latest version of `7.4.4` or higher.

Example)

```
opcache.preload=/path/to/project/preload.php
opcache.preload_user=www-data
```

Note: Please refer to the [benchmark](https://github.com/bearsunday/BEAR.HelloworldBenchmark/wiki/Intel-Core-i5-3.8-GHz-iMac-(Retina-5K,-27-inch,-2017)---PHP-7.4.4) for performance benchmarks.


### .compile.php

When there are classes that cannot be generated in a non-production environment (for example, a ResourceObject that requires successful authentication to complete injection), you can compile them by describing dummy class loading in the root `.compile.php` file, which is only loaded during compilation.

.compile.php

Example) If there is an AuthProvider that throws an exception when authentication cannot be obtained in the constructor, you can create an empty class as follows and load it in .compile.php:

/tests/Null/AuthProvider.php
```php
<?php
class AuthProvider 
{  // Only for instantiation, so implementation is not required
}
```

.compile.php
```php
<?php
require __DIR__ . '/tests/Null/AuthProvider.php'; // Always-generatable Null object
$_SERVER[__REQUIRED_KEY__] = 'fake'; // For cases where errors occur without specific environment variables
```

This allows you to avoid exceptions and perform compilation. Additionally, since Symfony's cache component connects to the cache engine in the constructor, it's good to load a dummy adapter during compilation like this:

tests/Null/RedisAdapter.php
```php
namespace Ray\PsrCacheModule;

use Ray\Di\ProviderInterface;
use Serializable;
use Symfony\Component\Cache\Adapter\RedisAdapter as OriginAdapter;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

class RedisAdapter extends OriginAdapter implements Serializable
{
    use SerializableTrait;
    
    public function __construct(ProviderInterface $redisProvider, string $namespace = '', int $defaultLifetime = 0, ?MarshallerInterface $marshaller = null)
    {
    　　// do nothing
    }
}
```
### module.dot

When you compile, a "dot file" is output, so you can convert it to an image file with [graphviz](https://graphviz.org/) or use [GraphvizOnline](https://dreampuf.github.io/GraphvizOnline/) to display the object graph.
Also, please see the [object graph](/images/screen/skeleton.svg) of the skeleton.

```php
dot -T svn module.dot > module.svg
```

## Bootstrap Performance Tuning

[immutable_cache](https://pecl.php.net/package/immutable_cache) is a PECL package for caching immutable values in shared memory. It is based on APCu but is faster than APCu because it stores immutable values such as PHP objects and arrays in shared memory. Additionally, installing PECL's [Igbinary](https://www.php.net/manual/en/book.igbinary.php) with either APCu or immutable_cache can reduce memory usage and further improve performance.

Currently, there are no dedicated cache adapters available. Please refer to [ImmutableBootstrap](https://github.com/koriym/BEAR.Hello/commit/507d1ee3ed514686be2d786cdaae1ba8bed63cc4) to create and call a dedicated Bootstrap. This allows you to minimize initialization costs and achieve maximum performance.

### php.ini

```
// Extensions
extension="apcu.so"
extension="immutable_cache.so" 
extension="igbinary.so"

// Specifying serializer
apc.serializer=igbinary
immutable_cache.serializer=igbinary
```
`````

----
