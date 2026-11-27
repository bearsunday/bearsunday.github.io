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
* Resource cache (query repository)

The OPTIONS method is also disabled. See [ProdModule.php](https://github.com/bearsunday/BEAR.Package/blob/1.x/src/Context/ProdModule.php) in BEAR.Package for details.

## Application's ProdModule

Customize the application's `ProdModule` in `src/Module/ProdModule.php` against the default ProdModule.

```php
<?php

namespace MyVendor\MyProject\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\Package\Provide\Error\ErrorPageFactoryInterface;
use BEAR\QueryRepository\CacheVersionModule;
use BEAR\Resource\Module\OptionsMethodModule;
use Ray\Di\AbstractModule;

class ProdModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new PackageProdModule);       // Default prod binding
        $this->override(new OptionsMethodModule);    // Enable OPTIONS method in production as well
        $this->install(new CacheVersionModule('1')); // Specify resource cache version

        // Custom error page
        $this->bind(ErrorPageFactoryInterface::class)->to(MyErrorPageFactory::class);
    }
}
```

What you add to this module, depending on the deployment environment, is the rest of this page:

* [Distributed cache](#cache) — required with two or more web servers
* [Cache version](#cache-version) — discard the cache when the resource schema changes
* [Resource execution log](#logging)
* [Per-error log files](#error-log)
* [Declaring where the application writes](#writable-paths) — required on serverless and read-only containers

## Cache {#cache}

There are two types of caches: a local cache and a distributed cache that is shared between multiple web servers.

### Local Cache

The local cache is used for what does not change after deployment, such as annotations. It is written to [APCu](https://www.php.net/manual/en/book.apcu.php) when available, and to files otherwise.

### Distributed Cache

Resource states are stored here. To provide services with two or more web servers, a distributed cache configuration is required. Modules for each of the popular [memcached](https://www.php.net/manual/en/book.memcached.php) and [Redis](https://redis.io) cache engines are provided.

#### Memcached

```php
// {host}:{port}:{weight},...
$memcachedServers = 'mem1.domain.com:11211:33,mem2.domain.com:11211:67';
$this->install(new StorageMemcachedModule($memcachedServers));
```

#### Redis

```php
$redisServer = 'localhost:6379'; // {host}:{port}
$this->install(new StorageRedisModule($redisServer));
```

To use authentication, TLS, Sentinel, Cluster, and so on, use `StorageRedisDsnModule`, which takes the connection as a DSN. See [Redis DSN](redis-dsn.html) for the format.

```php
$this->install(new StorageRedisDsnModule('redis://localhost:6379'));
```

In addition to simply updating the cache by TTL for storing resource states, it is also possible to operate (CQRS) as a persistent storage that does not disappear after the TTL time. In that case, you need to perform persistent processing with `Redis` or prepare your own storage adapter for other KVS such as Cassandra.

### Specifying Cache Time

To change the default TTL, install `StorageExpiryModule`.

```php
// Cache time
$short = 60;
$medium = 3600;
$long = 24 * 3600;
$this->install(new StorageExpiryModule($short, $medium, $long));
```

### Specifying Cache Version {#cache-version}

Change the cache version when the resource schema changes and compatibility is lost. This is especially important for CQRS operation that does not disappear over TTL time.

```php
$this->install(new CacheVersionModule($cacheVersion));
```

To discard the resource cache every time you deploy, it is convenient to assign a time or random value to `$cacheVersion` so that no change is required.

## Logging {#logging}

`ProdLoggerModule` is a resource execution log module for production. When installed, it logs requests other than GET to the logger bound to `Psr\Log\LoggerInterface`.

```php
$this->install(new ProdLoggerModule);
```

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

The `__invoke` method of [LoggerInterface](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/LoggerInterface.php) passes the resource URI and resource state as a `ResourceObject` object, so log the necessary parts based on its contents. Refer to the [existing implementation ProdLogger](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/ProdLogger.php) for creation.

### Error Logs {#error-log}

An uncaught error responds with a `logref` ID, and the rendered exception goes to the logger bound to `Psr\Log\LoggerInterface` — with no logger of your own bound, that is PHP's `error_log`. By default a `logref.{id}.log` file is also written under `var/log/{context}`, with `last.logref.log` linking to the newest one. `ProdModule` binds `NullLogRefWriter` instead, so production writes no file; to keep the per-error files, bind `LogRefWriterInterface` to `FileLogRefWriter` in your `ProdModule`.

## Deployment

There are two deployment strategies.

### Full ahead-of-time compilation (recommended) {#aot-compile}

[Compile](#compilation-recommended) at build time and ship the compiled artifact. Its traits: cold-start speed (boot only reads the artifact), scale (every added instance boots from the same artifact), and safety (a DI configuration error stops the build with exit code 1 and never reaches production). Boot writes nothing into the artifact, so the tree ships read-only ([Read-only deployment](#writable-paths)).

The artifact has two representative shapes. With a container pipeline, a [Docker multi-stage build](#docker-multi-stage) pins the compile to the image build. Without one, [Phar](phar.html) is this shape as a single file: a deploy is a copy of one file, and the pack — not CI discipline — enforces the artifact's identity and safety.

* Gate CI with the compiler's exit code
* Values that change at runtime (hosts, tokens) are not baked in; resolve them at runtime
* The object graph built at boot must build without the build environment's services: see [`.compile.php`](#compile-php)
* On a read-only runtime, combine with [declared write locations](#writable-paths)

### On-demand compilation at health check {#health-check-compile}

Ship uncompiled; the first request on the deploy target compiles. Its trait: real environment values — paths, environment variables — are used as they are. When an uncompiled artifact boots on a writable tree, it compiles in place with a `Compiled DI scripts on demand` notice. Unpack each release into a fresh directory and route traffic only after the health check passes: that first request is the health check, so a failed compile shows up before any traffic does. Every instance compiles for itself, so artifact identity is weaker than with ahead-of-time compilation.

<a id="compilation"></a>
### Compilation {#compilation-recommended}

Compilation creates the static caches — DI/AOP dynamic files, annotations — in advance, and writes optimized `autoload.php` and `preload.php`.

A build script names the application; it does not boot it (BEAR.Package 1.22+; the skeleton ships `bin/compile.php`).

```php
<?php
// bin/compile.php
use BEAR\Package\Compiler;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('memory_limit', '-1');

$context = $argv[1] ?? 'prod-app';

exit((new Compiler('MyVendor\MyProject', $context, dirname(__DIR__)))());
```

The script names the application, the context and the application directory, and it does not boot the application. `Compiler::phar()` packs the compiled result into one archive (BEAR.Package 1.24+): [Phar](phar.html).

```json
"scripts": {
    "compile": "php bin/compile.php"
}
```

* If you compile, the possibility of DI errors at runtime is extremely low because injection is performed in all classes.
* The contents included in `.env` are incorporated into the PHP file, so `.env` can be deleted after compilation.

DI scripts are written under `{appDir}/var/build/{context}/di`. The build directory holds what a compile produced and nothing a request writes, so it can ship read-only: when the artifact carries it, runtime reads the scripts instead of compiling.

`vendor/bin/bear.compile` was removed in BEAR.Package 1.24. Migration: [BEAR.Package#482](https://github.com/bearsunday/BEAR.Package/issues/482).

#### Compiling multiple contexts {#multiple-contexts}

Compiling multiple contexts (e.g. api-app and html-app for content negotiation) is a loop in the script. `autoload.php` and `preload.php` are written to fixed paths and the next compile removes them, so rename them as you go:

```php
// bin/compile.php
$appDir = dirname(__DIR__);

foreach (['prod-hal-api-app', 'prod-html-app'] as $context) {
    $code = (new Compiler('MyVendor\MyProject', $context, $appDir))();
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

[`opcache.preload`](https://www.php.net/manual/en/opcache.preloading.php) is a per-process setting, so preloading multiple contexts means **separate PHP processes** (e.g. php-fpm pools), each pointing at its evacuated preload (e.g. the api pool: `opcache.preload=/path/to/prod-hal-api-app.preload.php`, the html pool: `/path/to/prod-html-app.preload.php`).

When each context is packed into a [Phar](phar.html), `preload.php` goes into the archive and is not renamed.

#### Compile steps {#compile-steps}

A module can bind a compile step — `BEAR\Sunday\Compile\CompileStepInterface` — and the compile runs it. Each step is handed an empty directory of its own under the build directory, named after its binding key, and what it writes ships with the artifact:

```text
{appDir}/var/build/{context}/di        compiled DI scripts
{appDir}/var/build/{context}/qiq       the templates Qiq compiled
{appDir}/var/build/{context}/twig      Twig's cache
```

Template engines use this: the first request has nothing left to compile, and nothing under the application root has to be writable for them. A step that fails leaves no compile marker, so the next boot compiles again rather than serve a build whose templates never arrived.

Requires bear/sunday 1.9+. Background: [BEAR.Package#501](https://github.com/bearsunday/BEAR.Package/pull/501).

#### .compile.php {#compile-php}

BEAR.Package 1.27 and later do not load `.compile.php`. A leftover one raises `E_USER_DEPRECATED` and is ignored: delete it, together with the stub classes it loads.

The stubs in `.compile.php` replaced the real classes for the whole compile process, so the compiled DI scripts took the stubs' constructors and interceptor bindings. Since 1.22 production boots from those scripts when the compile marker matches, so a stub with another constructor compiled cleanly and failed in production with `ArgumentCountError`, and a stub without a method attribute ran without its interceptor, a transaction for example. Background: [BEAR.Package#530](https://github.com/bearsunday/BEAR.Package/pull/530).

What the compile needs instead:

* **The object graph built at boot must build without stubs.** The compile builds `AppInterface` and its dependencies, as every boot does. A constructor or provider in that graph must not connect to a database, a cache or an external service, nor read request state such as authentication: connect on first use instead. For `StorageRedisDsnModule`, either compile where Redis is reachable, or pass `['lazy' => true]`; with `lazy` a wrong Redis host is no longer noticed at compile.
* **Request-time code needs no stubs.** To record `preload.php` and `autoload.php` the compile runs `GET /` twice against the real services, so whatever that page writes or sends happens at compile, twice. A failure while the request runs does not stop the compile; it is reported on STDERR as `get <uri> failed while recording preload.php: <exception>: <first line of its message>`, and `preload.php` lacks what the rest of that request loads. A missing page at `/` is not reported. Compile where the page can run to get a full `preload.php`.
* **Values a module reads in `configure()` are baked in** with the compile machine's values. They can no longer be faked in `.compile.php`: compile with the real values, or read them at runtime through a provider binding.

Up to 1.26 the compiler loads `.compile.php`. A stub there must carry the real class's constructor, and the methods and attributes an interceptor matches; from 1.22 a mismatch breaks production when the compiled scripts ship with their compile marker.

#### autoload.php {#autoloadphp}

An optimized autoload file is output to `{project_path}/autoload.php`. It is a flat `require` list of the class files the compile observed in use, with Composer's autoloader behind it for the rest. For the classes the application uses, no autoloader resolution happens at all: `composer dump-autoload --optimize` speeds up the classmap lookup, but the per-class autoloader call remains — this file removes it. That is why it reduces per-request autoload cost **when you do not use preload**.

Note: If you use `preload.php`, most classes are already loaded at startup, so this `autoload.php` is essentially unnecessary — use the `vendor/autoload.php` generated by Composer. In other words, `autoload.php` is a fallback for environments that cannot use preload.

#### preload.php {#preloadphp}

An optimized preload.php file is output to `{project_path}/preload.php`. To enable preloading, you need to specify [opcache.preload](https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.preload) and [opcache.preload_user](https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.preload-user) in php.ini.

Example)

```ini
opcache.preload=/path/to/project/preload.php
opcache.preload_user=www-data
```

#### module.dot {#module-dot}

When you compile, a "dot file" is output, so you can convert it to an image file with [graphviz](https://graphviz.org/) or use [GraphvizOnline](https://dreampuf.github.io/GraphvizOnline/) to display the object graph. Also, please see the [object graph](/images/screen/skeleton.svg) of the skeleton.

```bash
dot -T svg module.dot > module.svg
```

### Read-only deployment (serverless, immutable containers) {#writable-paths}

Serverless platforms and immutable containers restrict where an application may write. On Vercel or AWS Lambda, or in a container started with `docker run --read-only` / `readOnlyRootFilesystem: true`, the project directory is read-only and one directory - `/tmp`, typically - is the only writable location. Ordinary VPS and shared hosting are unaffected.

An application declares where it writes in its own `ProdModule`.

```php
<?php
// src/Module/ProdModule.php
namespace MyVendor\MyProject\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\Package\Module\ReadOnlyAppModule;
use Ray\Di\AbstractModule;

class ProdModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new ReadOnlyAppModule());
        $this->install(new PackageProdModule());
    }
}
```

Omitted, the directories are under the temp directory of the machine that boots ([`sys_get_temp_dir()`](https://www.php.net/sys_get_temp_dir)), keyed by application name, application directory and context.

```text
{appDir}/var/build/{context}/di                                        compiled DI scripts, in the artifact
{temp directory}/MyVendor/MyProject/{appDir hash}/var/tmp/{context}    query repository cache
{temp directory}/MyVendor/MyProject/{appDir hash}/var/log/{context}
```

The temp directory is set by the [`sys_temp_dir`](https://www.php.net/manual/en/ini.core.php#ini.sys-temp-dir) php.ini directive, and can be passed at boot:

```bash
php -d sys_temp_dir=/mnt/tmp public/index.php
```

The application and the context are in the path because local cache keys are resource URIs: two applications or two contexts sharing one directory would answer with each other's entries. The application directory hash keeps two checkouts of one application apart for the same reason.

The entry points do not change, and no environment variable is read. Nothing has to match between the build and the boot, so one artifact boots on any machine with that machine's answer.

There are two ways to decide where the application writes. The default is to stay system-dependent and operate that dependency — `sys_temp_dir` — from the environment. Only where neither php.ini nor a boot option is yours to set, pass an absolute path and bake the fixed value in.

A named path is used as given.

```php
$this->install(new ReadOnlyAppModule('/tmp/myapp/tmp', '/tmp/myapp/log'));
```

A value passed here enters the container when it is compiled, as given. Name an absolute path that the machines booting this build can use: whether one can be written to is the filesystem's answer, at the moment something writes.

Either can be named on its own; the other is answered at boot.

```php
$this->install(new ReadOnlyAppModule(logDir: '/var/log/myapp'));
```

Compiled DI scripts stay under `appDir` and ship inside the artifact. A new instance starts with an empty temp directory, so following it would compile again on every cold start - 0.38s against 0.018s on a five-resource application.

An artifact that was never compiled stops with `NotCompiledException`. Where the tree is writable, it compiles there instead and emits a `Compiled DI scripts on demand` notice.

A single-file artifact that never writes into itself is [Phar](phar.html).

Requires BEAR.Package 1.24+. Background: [BEAR.Package#491](https://github.com/bearsunday/BEAR.Package/pull/491).

### Docker multi-stage build {#docker-multi-stage}

Compilation is build work. A Docker [multi-stage build](https://docs.docker.com/build/building/multi-stage/) pins it to the image build.

```dockerfile
FROM php:8.3-cli AS build
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
RUN composer install --no-dev --prefer-dist --no-progress
RUN php bin/compile.php prod-app

FROM php:8.3-apache
COPY --from=build /app /var/www/app
```

The image that boots carries the compiled DI scripts, with nothing left to do at boot. A cold start only reads the artifact — 0.38s down to 0.018s in the measurement above — and every instance that scale-out adds boots from the same artifact, so every container gives the same answer. To start the runtime stage with `docker run --read-only`, combine with [declared write locations](#writable-paths).

This is the shape of [full ahead-of-time compilation](#aot-compile): values that change at runtime — endpoints, tokens — are not baked in, but resolved at runtime.
