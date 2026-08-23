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

<a id="compilation"></a>
### Compilation Recommended

When setting up, you can **warm up** the project: create static cache files for DI/AOP and annotations in advance, and write optimized `autoload.php` and `preload.php`.

**Principle: compile on the deploy target when you can** (at warm-up / health-check time). Real environment values — paths, environment variables — are reflected as-is, so values bake in correctly and no writable-path override is needed.

**Exception: environments that need ahead-of-time compilation** (serverless, a read-only app root, or a fixed path such as `/tmp`). Because you compile where the real services are unreachable, combine [changing writable paths](#writable-paths), AOT script reuse, and `.compile.php` (to stub the unreachable services), and **do not bake values that vary at runtime (hosts, tokens) — resolve them at runtime.**

A build script names the application; it does not boot it (BEAR.Package 1.22+; the skeleton ships `bin/compile.php`).

```php
<?php
// bin/compile.php
use BEAR\Package\Compiler;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('memory_limit', '-1');

// Load build-time-only stubs (null objects / fake env) if present.
$dotCompile = dirname(__DIR__) . '/.compile.php';
is_file($dotCompile) && require $dotCompile;

$context = $argv[1] ?? 'prod-app';
$writeDir = getenv('APP_WRITE_DIR') ?: null;

exit((new Compiler('MyVendor\MyProject', $context, dirname(__DIR__), $writeDir))());
```

The script names the application, the context and the write directory, and it does not boot the application. `.compile.php` build stubs are loaded by the Compiler itself. `Compiler::phar()` packs the compiled result into one archive (BEAR.Package 1.23+): [Phar](phar.html).

```json
"scripts": {
    "compile": "php bin/compile.php"
}
```

* If you compile, the possibility of DI errors at runtime is extremely low because injection is performed in all classes.
* The contents included in `.env` are incorporated into the PHP file, so `.env` can be deleted after compilation.

Compiling multiple contexts (e.g. api-app and html-app for content negotiation) is a loop in the script. `autoload.php` and `preload.php` are written to fixed paths and the next compile removes them, so rename them as you go:

```php
// bin/compile.php
$appDir = dirname(__DIR__);
$writeDir = getenv('APP_WRITE_DIR') ?: null;

foreach (['prod-hal-api-app', 'prod-html-app'] as $context) {
    $code = (new Compiler('MyVendor\MyProject', $context, $appDir, $writeDir))();
    if ($code !== 0) {
        exit($code);
    }

    foreach (['preload.php', 'autoload.php'] as $written) {
        if (! rename($appDir . '/' . $written, $appDir . '/' . $context . '.' . $written)) {
            exit(1);
        }
    }
}

exit(0);
```

[`opcache.preload`](https://www.php.net/manual/en/opcache.preloading.php) is a per-process setting, so preloading multiple contexts means **separate PHP processes** (e.g. php-fpm pools), each pointing at its evacuated preload (e.g. the api pool: `opcache.preload=/path/to/api.preload.php`). In the example the html side keeps the default name because its process points at the default `preload.php`.

DI scripts are written under `{appDir}/var/build/{context}/di`. The build directory holds what a compile produced and nothing a request writes, so it can ship read-only: when the artifact carries it, runtime reads the scripts instead of compiling.

`vendor/bin/bear.compile` is deprecated. Migration: [BEAR.Package#482](https://github.com/bearsunday/BEAR.Package/issues/482).

#### Compile steps {#compile-steps}

A module can bind a compile step — `BEAR\Sunday\Compile\CompileStepInterface` — and the compile runs it. Each step is handed an empty directory of its own under the build directory, named after its binding key, and what it writes ships with the artifact:

```text
{appDir}/var/build/{context}/di        compiled DI scripts
{appDir}/var/build/{context}/qiq       the templates Qiq compiled
{appDir}/var/build/{context}/twig      Twig's cache
```

Template engines use this: the first request has nothing left to compile, and nothing under the application root has to be writable for them. A step that fails leaves no compile marker, so the next boot compiles again rather than serve a build whose templates never arrived.

Requires bear/sunday 1.9+. Background: [BEAR.Package#501](https://github.com/bearsunday/BEAR.Package/pull/501).

#### Read-only deployments (serverless, immutable containers) {#writable-paths}

Serverless platforms and immutable containers restrict where an application may write. On Vercel or AWS Lambda, or in a container started with `docker run --read-only` / `readOnlyRootFilesystem: true`, the project directory is read-only and one directory - `/tmp`, typically - is the only writable location. Ordinary VPS and shared hosting are unaffected.

Tell the application which directory it may write to. Pass the same directory to both the build and the boot, and keep to two rules:

* Pass an absolute path. A relative path throws `WriteDirNotAbsoluteException` where the `Meta` is built.
* Pass the same path to the build and the boot. The paths are compiled into the DI scripts, so a boot given another one compiles again where it can write, and stops with `CompiledForAnotherWriteDirException` where it cannot.

`$writeDir` is the optional last argument on `Bootstrap::__invoke()`, `Injector::getInstance()`, `Injector::getOverrideInstance()` and `new Compiler()`. Change the entry points like this:

```diff
 // public/index.php
-exit((new Bootstrap())('prod-app', $GLOBALS, $_SERVER));
+exit((new Bootstrap())('prod-app', $GLOBALS, $_SERVER, getenv('APP_WRITE_DIR') ?: null));

 // bin/compile.php               APP_WRITE_DIR=/tmp php bin/compile.php
-exit((new Compiler('MyVendor\MyProject', $context, dirname(__DIR__)))());
+$writeDir = getenv('APP_WRITE_DIR') ?: null;
+
+exit((new Compiler('MyVendor\MyProject', $context, dirname(__DIR__), $writeDir))());

 // src/Bootstrap.php
-    public function __invoke(string $context, array $globals, array $server): int
+    public function __invoke(
+        string $context, array $globals, array $server, string|null $writeDir = null
+    ): int
     {
-        $app = Injector::getInstance($context)->getInstance(AppInterface::class);
+        $app = Injector::getInstance($context, $writeDir)->getInstance(AppInterface::class);

 // src/Injector.php
-use BEAR\Package\Injector\PackageInjector;
+use BEAR\Package\Injector as PackageInjector;

-    public static function getInstance(
-        string $context, string|null $tmpDir = null, string|null $logDir = null
-    ): InjectorInterface
-    {
-        $meta = new Meta(__NAMESPACE__, $context, dirname(__DIR__), $tmpDir, $logDir);
-        $cacheNamespace = str_replace('/', '_', $meta->appDir) . $context;
-        $cache = (new LocalCacheProvider($meta->tmpDir . '/injector', $cacheNamespace))->get();
-
-        return PackageInjector::getInstance($meta, $context, $cache);
-    }
+    public static function getInstance(string $context, string|null $writeDir = null): InjectorInterface
+    {
+        return PackageInjector::getInstance(__NAMESPACE__, $context, dirname(__DIR__), writeDir: $writeDir);
+    }
```

`BEAR\Package\Injector` builds the `Meta` and the injector cache pool from the write directory, so the skeleton's own `Meta` / `LocalCacheProvider` lines go away. Development entry points pass nothing and keep the default paths.

`APP_WRITE_DIR` is the one source, for the build and for the runtime — `AppModule` runs during the compile, so what it reads must be what the build uses:

```text
build     APP_WRITE_DIR=/tmp php bin/compile.php
runtime   APP_WRITE_DIR=/tmp
          php-fpm   env[APP_WRITE_DIR] = /tmp
          docker    --env APP_WRITE_DIR=/tmp
```

With `/tmp` as the write directory, the layout is:

```text
{appDir}/var/build/{context}/di                 compiled DI scripts, in the artifact
/tmp/MyVendor/MyProject/{context}/tmp           query repository cache, serialized injector
/tmp/MyVendor/MyProject/{context}/log
```

The application and the context are in the path because local cache keys are resource URIs: two applications or two contexts sharing one directory would answer with each other's entries.

Compiled DI scripts stay under `appDir` and ship inside the artifact. A new instance starts with an empty `/tmp`, so following the write directory would compile again on every cold start - 0.38s against 0.018s on a five-resource application.

If the boot is given a different write directory than the build used, the DI scripts are compiled again instead of read from the old paths: a read-only artifact stops with `CompiledForAnotherWriteDirException`, naming both directories, and a writable one emits a `Compiled DI scripts on demand` notice.

A single-file artifact that never writes into itself is [Phar](phar.html).

Requires BEAR.Package 1.22+. Background: [BEAR.Package#491](https://github.com/bearsunday/BEAR.Package/pull/491).

### autoload.php

An optimized autoload file is output to `{project_path}/autoload.php`. It is lighter than the `vendor/autoload.php` produced by `composer dump-autoload --optimize` and reduces per-request autoload cost **when you do not use preload**.

Note: If you use `preload.php`, most classes are already loaded at startup, so this `autoload.php` is essentially unnecessary — use the `vendor/autoload.php` generated by Composer. In other words, `autoload.php` is a fallback for environments that cannot use preload.

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

When there are classes that cannot be generated in a non-production environment (for example, a ResourceObject that requires successful authentication to complete injection), you can compile them by describing dummy class loading in the root `.compile.php` file, which is only loaded during compilation. **Its purpose is to let construction succeed at compile time, so its contents should be null objects (do-nothing implementations).** This applies not only to ahead-of-time builds (real services unreachable) but also to resources that need per-request state such as authentication, which is absent during compilation even when you compile on the deploy target. Keep value fakes (`$_SERVER['X'] = 'fake'`, etc.) to the minimum needed to pass construction, and never use them for values that must be real at runtime (they get baked in).

**Note:** the compiler loads `.compile.php` itself, before it builds the container. The deprecated `vendor/bin/bear.compile` did too; nothing else has to.

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
$_SERVER['YOUR_REQUIRED_ENV'] = 'fake'; // For cases where errors occur without specific environment variables
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
