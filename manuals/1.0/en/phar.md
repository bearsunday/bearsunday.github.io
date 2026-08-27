---
layout: docs-en
title: Phar
category: Manual
permalink: /manuals/1.0/en/phar.html
---

# Phar

A [phar](https://www.php.net/manual/en/intro.phar.php) is the application as one file: the code, `vendor/`, and the compiled DI scripts in a single archive. The boot reads the archive and writes nothing into it — a deploy is a copy of one file, a rollback is the file before it.

```text
app.phar                                            the application, vendor/, compiled DI scripts
{temp directory}/MyVendor/MyProject/{appDir hash}/var    what the runtime writes: tmp and log
```

Requires BEAR.Package 1.24+.

## Where the application writes

An application declares it in its own `ProdModule`.

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

Omitted, the directories are under the temp directory of the machine that boots ([`sys_get_temp_dir()`](https://www.php.net/sys_get_temp_dir)): the same shape the application would have used inside its own tree, moved there. The path carries a hash of the application directory, so two checkouts of one application never share a cache.

```text
{temp directory}/MyVendor/MyProject/{appDir hash}/var/tmp/prod-hal-app
{temp directory}/MyVendor/MyProject/{appDir hash}/var/log/prod-hal-app
```

The archive carries no write directory, so each machine boots with its own answer. There is nothing to match against the build.

A named path is used as given.

```php
$this->install(new ReadOnlyAppModule('/var/tmp/myapp', '/var/log/myapp'));
```

A value passed here enters the container when it is compiled, as given. Name an absolute path that the machines booting this build can use: whether one can be written to is the filesystem's answer, at the moment something writes.

Either can be named on its own; the other is answered at boot.

```php
$this->install(new ReadOnlyAppModule(logDir: '/var/log/myapp'));
```

## Make your application a phar

The build script compiles, then packs. Both steps are on the compiler:

```php
<?php
// bin/compile.php
use BEAR\Package\Compiler;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('memory_limit', '-1');

$context = 'prod-hal-app';

$compiler = new Compiler('MyVendor\MyProject', $context, dirname(__DIR__));
$code = $compiler();

exit($code === 0 ? $compiler->phar() : $code);
```

The script names the application, the context it boots — the same one `public/index.php` uses — and the application directory. The rest it does not have to say, because packing is the framework's business: the archive carries named top-level directories only — `src`, `public`, `bin`, `vendor`, `var`, and wherever an imported application sits — and of `var/` only this build: `var/build/{context}`, which holds the DI scripts with their compile marker and whatever [compile steps](production.html#compile-steps) wrote. `var/log` and `var/tmp` stay out, as do `.env`, `autoload.php` and `tests/`; of the files at the root only `preload.php` ships; the directories left behind are printed as `Not packed:`. The marker is `.bear-compile.json` (`app`, `context`, `time`), and it is what `phar()` reads to tell a compiled build from an uncompiled tree. The `.env` file stays out, but the values it held are compiled into the DI scripts, and those ship: treat the archive as a secret. `phar.readonly` is handled in a child process, so there is no ini flag to remember.

```bash
php bin/compile.php
```

```text
Compiled: 16 resource classes
Phar: /app/app.phar (7.5MB, 2100 files)
Not packed: tests
```

`__invoke()` and `phar()` are separate steps, so a build pipeline can compile in one job and pack in another. `phar()` packs what is on disk and refuses a context that was never compiled, or one compiled to write inside the tree. It writes `{appDir}/app.phar`, beside the `autoload.php` and `preload.php` the compile wrote; another entry is its one argument.

All three are fixed paths, so several contexts are a loop that packs each one before it compiles the next, and moves the archive aside:

```php
// bin/compile.php
$appDir = dirname(__DIR__);

foreach (['prod-hal-api-app', 'prod-html-app'] as $context) {
    $compiler = new Compiler('MyVendor\MyProject', $context, $appDir);
    $code = $compiler();
    if ($code !== 0) {
        exit($code);
    }

    $code = $compiler->phar();
    if ($code !== 0) {
        exit($code);
    }

    if (! rename($appDir . '/app.phar', $appDir . '/' . $context . '.phar')) {
        exit(1);
    }
}

exit(0);
```

The `preload.php` rename that [production](production.html#compilation-recommended) shows has no place here: it serves a deploy that is not an archive, where the preload has to sit on disk beside the code. Each archive carries its own at `phar://…/{context}.phar/preload.php`. Renaming before the pack leaves the archive without one, silently — a build that uses no preload is legitimate, so nothing stops it.

## Run

```bash
php app.phar get '/index?name=BEAR'
```

The stub runs `public/index.php` from inside the archive, so `dirname(__DIR__)` in `src/Injector.php` is `phar:///path/app.phar`. The entry points are the ones [Read-only deployment](production.html#writable-paths) shows; nothing else changes.

php-fpm runs a file, not an archive, so the entry point sits next to it and loads the autoloader from inside:

```php
<?php
// index.php, in the same directory as app.phar
require 'phar://' . __DIR__ . '/app.phar/vendor/autoload.php';

exit((new MyVendor\MyProject\Bootstrap())('prod-hal-app', $GLOBALS, $_SERVER));
```

`preload.php` ships in the archive, so `opcache.preload` names it there:

```ini
opcache.preload=phar:///path/to/app.phar/preload.php
```

`autoload.php` does not ship: a preload leaves it [nothing to do](production.html#autoloadphp). The same preload placed beside the archive instead stops the server at startup with `Failed opening required '…/vendor/autoload.php'` — its requires are written relative to the directory it sits in, and outside the archive that directory holds no `vendor/`. One preload is written per compile, at a fixed path, so pack the context you compiled last; the pack refuses one another context left behind.

## It moves

The archive is the build. Copy it anywhere — another directory, another machine — and boot it there: nothing outside it is read, so the tree it was packed from can be deleted.

```bash
cp app.phar /srv/releases/2026-08-23.phar
php /srv/releases/2026-08-23.phar get '/index?name=BEAR'
```

Renaming it, or running it from another directory, is the same.

## One rule

**No binding that derives a runtime path from `$appMeta->appDir`.** The compiled scripts carry the `Meta` of the build, so the injected `appDir` is the build directory, not `phar://…`; `tmpDir` and `logDir` are the write directories and are correct. Anything that reads a file at runtime — a template directory, a data file — takes its path from `__DIR__`, which resolves inside the archive.

## Imported applications

An [imported application](import.html) in the archive is a second application: its own `Meta`, its own compiled scripts. It needs no change at all:

```php
$this->install(new ImportAppModule([
    new ImportApp('greeting', 'ImportVendor\Greeting', 'prod-app')
]));
```

The compile boots the application, that boot compiles each imported application into its own tree (`Compiled DI scripts on demand` in the build log is that), and the pack ships their DI scripts automatically.

It declares where it writes itself; it does not inherit the host's.

```php
<?php
// imports/greeting/src/Module/ProdModule.php
$this->install(new ReadOnlyAppModule());
```

```text
{temp directory}/ImportVendor/Greeting/{appDir hash}/var/tmp/prod-app
{temp directory}/ImportVendor/Greeting/{appDir hash}/var/log/prod-app
```

Declaring nothing leaves it writing in its own tree, and that tree is inside the archive, so the pack stops with `PharWritesInsideArchiveException` naming the application.

## When the build stops

Everything that used to fail at the deploy fails at the build, with the path in the message:

| Error | Meaning |
|---|---|
| `PharNotCompiledException` | The context was never compiled: `phar()` packs what is on disk |
| `PharPreloadForAnotherBuildException` | The `preload.php` at the application root was written by another context: pack the context you compiled last |
| `PharImportsUnreadableException` | The compiled container declares its imports in a form this version cannot read: recompile with the version that packs |
| `PharWritesInsideArchiveException` | An application — the host or an import — was compiled to write into the tree. Install `ReadOnlyAppModule` and compile again |
| `PharImportOutsideTreeException` | An imported application lies outside the tree being packed and cannot ship in it |
| `PharEntryNotFoundException` | No `public/index.php`; pass another entry to `Compiler::phar()` |
| `PharEntryNotPackedException` | The entry exists but does not ship: of the files loose at the application root only `preload.php` does |
| `PharStaleOutputException` | An archive of a previous build survived at the output path and could not be removed |
| `PharSymlinkedDirectoryException` | A directory in the tree is a symlink, which `Phar` cannot pack |

An archive that was never compiled stops at boot with `NotCompiledException`: an archive cannot be written to, so the build it lacks cannot be made there.

To run this archive in the browser, see [Wasm](wasm.html). A working demo is [koriym/wasm-todo](https://github.com/koriym/wasm-todo) ([live page](https://koriym.github.io/wasm-todo/)).

Background: [BEAR.Package#426](https://github.com/bearsunday/BEAR.Package/issues/426).
