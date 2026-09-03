---
layout: docs-en
title: Phar
category: Manual
permalink: /manuals/1.0/en/phar.html
---

# Phar

A [phar](https://www.php.net/manual/en/intro.phar.php) is the application as one file. The code, `vendor/`, and the compiled DI scripts fit in a single archive. The application writes nothing into the archive. A deploy is a copy of one file. Requires BEAR.Package 1.24 or later.

```text
app.phar                                            the application, vendor/, compiled DI scripts
{temp directory}/MyVendor/MyProject/{appDir hash}/var    what the runtime writes: tmp and log
```

## ReadOnlyAppModule

Installing `ReadOnlyAppModule` moves the runtime writes (`var/tmp` and `var/log`) out of the application directory, so the application directory is only read.

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

With no arguments, `var/tmp` and `var/log` are created under the temp directory of the machine that boots ([`sys_get_temp_dir()`](https://www.php.net/sys_get_temp_dir)). The path includes a hash of the application directory, so two checkouts of the same application do not share a cache.

```text
{temp directory}/MyVendor/MyProject/{appDir hash}/var/tmp/prod-hal-app
{temp directory}/MyVendor/MyProject/{appDir hash}/var/log/prod-hal-app
```

Normally `sys_get_temp_dir()` returns a writable temp directory and no configuration is needed.
To change it, set `sys_temp_dir` in php.ini or the `TMPDIR` environment variable.
To fix the location in the project, pass the paths and they are used as given.

```php
$this->install(new ReadOnlyAppModule('/var/tmp/myapp', '/var/log/myapp'));
```

Either one can be passed on its own. The other is decided at boot by `sys_get_temp_dir()`.

```php
$this->install(new ReadOnlyAppModule(logDir: '/var/log/myapp'));
```

## Building the phar

In the build script, call `phar()` after the compile to create the phar file.

```php
<?php
// bin/compile.php
use BEAR\Package\Compiler;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('memory_limit', '-1');

$context = 'prod-hal-app';

$compiler = new Compiler('MyVendor\MyProject', $context, dirname(__DIR__));
$code = $compiler();

exit($code === 0 ? $compiler->phar() : $code); // exit($code) to compile only
```

The script needs the application name, the context to boot (the same one `public/index.php` uses), and the application directory. Nothing else has to be written.
What goes into the archive is decided by the framework: only named top-level directories are included, namely `src`, `public`, `bin`, `vendor`, `var`, and wherever an imported application sits. Of `var/`, only this build's `var/build/{context}` is included, which holds the DI scripts with their compile marker and whatever [compile steps](production.html#compile-steps) wrote. `var/log` and `var/tmp` are not included, nor are `.env`, `autoload.php`, and `tests/`. Of the files at the application root, only `preload.php` is included. The directories left out are printed as `Not packed:` after the compile. The marker is `.bear-compile.json` (`app`, `context`, `time`), and `phar()` reads it to check that the build was compiled. The `.env` file itself is not included, but if its values are bound as instances they are recorded in the DI scripts and included in the archive. In that case, treat the archive as a secret too. `phar.readonly` is handled in a child process, so there is no ini flag to remember.

```bash
php bin/compile.php
```

```text
Compiled: 16 resource classes
Phar: /app/app.phar (7.5MB, 2100 files)
Not packed: tests
```

`phar()` creates the archive from the compile result on disk. Its one argument is the entry point path, `public/index.php` by default. A context that was not compiled, or a build compiled without `ReadOnlyAppModule`, stops with an error before the archive is made ([Build errors](#when-the-build-stops)).

`app.phar` is written to a fixed path and overwritten by the next `phar()`. To build a Phar archive for each of several contexts, rename the file after each one.

## Running

```bash
php app.phar get '/index?name=BEAR'
```

The phar stub runs `public/index.php` inside the archive, so `dirname(__DIR__)` in `src/Injector.php` is `phar:///path/app.phar`. The entry point code is the same as in [Read-only deployment](production.html#writable-paths) and needs no change.

php-fpm cannot run the archive directly, so put the entry point in the same directory as the archive and load the autoloader from inside it.

```php
<?php
// index.php, in the same directory as app.phar
require 'phar://' . __DIR__ . '/app.phar/vendor/autoload.php';

exit((new MyVendor\MyProject\Bootstrap())('prod-hal-app', $GLOBALS, $_SERVER));
```

`preload.php` is included in the archive, so `opcache.preload` points to the path inside it.

```ini
opcache.preload=phar:///path/to/app.phar/preload.php
```

`autoload.php` is not included in the archive. With preload, [autoload.php is no longer needed](production.html#autoloadphp). The `preload.php` the compile wrote cannot be used from beside the archive. Its requires are written relative to its own directory, and there is no `vendor/` outside the archive, so startup fails with `Failed opening required '…/vendor/autoload.php'`. Also, `preload.php` is overwritten at `{appDir}/preload.php` on every compile, so run `phar()` for the context you compiled last. If another context's `preload.php` remains, `PharPreloadForAnotherBuildException` is thrown.

## One binary

[static-php-cli](https://static-php.dev) builds a single PHP binary with no shared-library dependencies. Placing the `php` and `php-fpm` binaries is enough to run. There is no need to install PHP on the host or to match the distribution's PHP version. Include the `phar` extension and the extensions the application uses in the build.

```bash
./spc download --for-extensions=phar,opcache -P
./spc build phar,opcache --build-cli --build-fpm
```

Running is the same. Use this binary instead of the host's `php`.

```bash
./buildroot/bin/php app.phar get '/index?name=BEAR'
```

For php-fpm, start `./buildroot/bin/php-fpm` with the `php-fpm.conf` the host's php-fpm uses, passed with `-y`. The entry point placement and the opcache and `opcache.preload` settings are the same as with the host's PHP.

## Copying to another location

The archive contains everything needed to run, so it can be copied to another directory or another machine and started as is. It never refers to the original project directory, so that directory can be deleted after the build.

```bash
cp app.phar /srv/releases/2026-08-23.phar
php /srv/releases/2026-08-23.phar get '/index?name=BEAR'
```

Renaming the file or running it from another directory works the same.

## Caveat

**Do not build runtime file paths in a module's `configure()`.** In a compiled application, `configure()` runs once at compile time and is not called at runtime. Values computed in `configure()` are recorded in the DI scripts as strings. It makes no difference whether the path came from `$this->appMeta->appDir` or `__DIR__`. Paths of files read at runtime, such as templates and SQL files, are built from `__DIR__` in the class that reads them, or decided at request time by a Provider. `tmpDir` and `logDir` are the write directories `ReadOnlyAppModule` sets, so they can be used as they are.

## Imported applications

[Imported applications](import.html) are included in the archive too. An imported application is an independent application with its own `Meta` and its own compiled scripts. No code change is needed to import it.

```php
$this->install(new ImportAppModule([
    new ImportApp('greeting', 'ImportVendor\Greeting', 'prod-app')
]));
```

Compiling the host application also compiles each imported application into its own directory (`Compiled DI scripts on demand` in the build log). The generated DI scripts are included in the archive automatically.

An imported application does not inherit the host's write directories, so install `ReadOnlyAppModule` in its own `ProdModule`.

```php
<?php
// imports/greeting/src/Module/ProdModule.php
$this->install(new ReadOnlyAppModule());
```

```text
{temp directory}/ImportVendor/Greeting/{appDir hash}/var/tmp/prod-app
{temp directory}/ImportVendor/Greeting/{appDir hash}/var/log/prod-app
```

Without it, the imported application is configured to write into its own directory, which is inside the archive. In that case `phar()` stops with `PharWritesInsideArchiveException` naming the application.

## Build errors {#when-the-build-stops}

These errors are detected at build time. The message includes the path concerned.

| Error | Meaning |
|---|---|
| `PharNotCompiledException` | The context is not compiled. Run `$compiler()` first |
| `PharPreloadForAnotherBuildException` | The `preload.php` at the application root belongs to another context. Run `phar()` for the context you compiled last |
| `PharImportsUnreadableException` | The compiled container declares its imports in a form this version of BEAR.Package cannot read. Recompile with the same version |
| `PharWritesInsideArchiveException` | The host or an imported application was compiled to write inside the application directory. Install `ReadOnlyAppModule` and recompile |
| `PharImportOutsideTreeException` | An imported application is outside the application directory |
| `PharEntryNotFoundException` | No `public/index.php`. To use another file as the entry, pass it to `phar()` |
| `PharEntryNotPackedException` | The file given as the entry is not included in the archive. Of the files at the application root, only `preload.php` is |
| `PharStaleOutputException` | A previous archive remained at the output path and could not be removed |
| `PharSymlinkedDirectoryException` | A directory inside the application directory is a symlink, which `Phar` cannot add |

Starting an archive that is not compiled throws `NotCompiledException`. The archive cannot be written to, so it cannot be compiled at boot.

To run this archive in the browser, see [Wasm](wasm.html). A working demo is [koriym/wasm-todo](https://github.com/koriym/wasm-todo) ([live page](https://koriym.github.io/wasm-todo/)).

Background: [BEAR.Package#426](https://github.com/bearsunday/BEAR.Package/issues/426)
