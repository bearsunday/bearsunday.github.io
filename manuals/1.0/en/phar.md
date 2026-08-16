---
layout: docs-en
title: Phar
category: Manual
permalink: /manuals/1.0/en/phar.html
---

# Phar

A [phar](https://www.php.net/manual/en/intro.phar.php) is the application as one file: the code, `vendor/`, and the compiled DI scripts in a single archive. The boot reads the archive and writes nothing into it — a deploy is a copy of one file, a rollback is the file before it.

```text
prod-app.phar                             the application, vendor/, compiled DI scripts
/tmp/MyVendor/MyProject/prod-app          everything the runtime writes
```

Requires BEAR.Package 1.23+.

## Make your application a phar

Your application compiles with its own `bin/compile.php`. Copy it once if you do not have it (BEAR.Skeleton ships it):

```bash
cp vendor/bear/package/bin/compile.php bin/
```

```php
<?php
// bin/compile.php
use BEAR\Package\Console;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('memory_limit', '-1');

$console = new Console(dirname(__DIR__), getenv('APP_WRITE_DIR') ?: null);
$context = 'prod-hal-app';

$code = $console->compile($context);
exit($code === 0 ? $console->phar($context) : $code);
```

The script is yours: it names the context your application boots — the same one `public/index.php` uses — and reads the environment. The rest it does not have to say. The application name comes from the autoloader (the namespace whose `Module/AppModule.php` sits under your application root), and packing is the framework's business: `src/`, `public/`, `vendor/` and the DI scripts with their compile markers go in; logs, caches, `.env`, `autoload.php`, `preload.php` and `tests/` stay out. `phar.readonly` is handled in a child process, so there is no ini flag to remember.

```bash
APP_WRITE_DIR=/tmp php bin/compile.php
```

```text
Compiled: 16 resource classes
Phar: var/build/prod-hal-app.phar (7.5MB, 2100 files)
Boot: APP_WRITE_DIR=/tmp php var/build/prod-hal-app.phar
```

`compile()` and `phar()` are separate steps, so a build pipeline can compile in one job and pack in another. `phar()` packs what is on disk and refuses a context that was never compiled, or one compiled for another write directory. Compiling several contexts is a loop in your script; `preload.php` and `autoload.php` are written to fixed paths, so rename them between contexts as [Production](production.html#compilation) shows.

## Run

```bash
APP_WRITE_DIR=/tmp php var/build/prod-hal-app.phar get '/index?name=BEAR'
```

The stub runs `public/index.php` from inside the archive, so `dirname(__DIR__)` in `src/Injector.php` is `phar:///path/prod-hal-app.phar`. The entry points are the ones [read-only deployments](production.html#writable-paths) shows; nothing else changes.

php-fpm runs a file, not an archive, so the entry point sits next to it and loads the autoloader from inside:

```php
<?php
// index.php, in the same directory as prod-hal-app.phar
require 'phar://' . __DIR__ . '/prod-hal-app.phar/vendor/autoload.php';

exit((new MyVendor\MyProject\Bootstrap())('prod-hal-api-app', $GLOBALS, $_SERVER, getenv('APP_WRITE_DIR') ?: null));
```

## Two rules

**One write directory, the same absolute path at the build and at the boot.** The compiled scripts hold the paths they were compiled with, so an archive is built for one write directory. `APP_WRITE_DIR` is that one place: the build reads it and the boot reads it. Imported applications receive it from the container, so nothing else names it.

**No binding that derives a runtime path from `$appMeta->appDir`.** The compiled scripts carry the `Meta` of the build, so the injected `appDir` is the build directory, not `phar://…`; `tmpDir` and `logDir` are the write directory and are correct. Anything that reads a file at runtime — a template directory, a data file — takes its path from `__DIR__`, which resolves inside the archive.

## Imported applications

An [imported application](import.html) in the archive is a second application: its own `Meta`, its own compiled scripts, its own write directory. It needs no change at all - the container hands it the write directory the host was given:

```php
$this->install(new ImportAppModule([
    new ImportApp('greeting', 'ImportVendor\Greeting', 'prod-app')
]));
```

The compile boots the application, that boot compiles each imported application into its own tree (`Compiled DI scripts on demand` in the build log is that), and the pack ships their DI scripts automatically. An imported application resolves its own directory at boot, so it follows the archive.

## When the build stops

Everything that used to fail at the deploy fails at the build, with the path in the message:

| Error | Meaning |
|---|---|
| `PharNotCompiledException` | The context was never compiled: `phar()` packs what is on disk |
| `PharWritesInsideArchiveException` | An application — the host or an import — was compiled to write into the tree. Compile with `APP_WRITE_DIR` set |
| `PharWriteDirMismatchException` | An import writes somewhere other than under the host's write directory: it was compiled before the host was given this one |
| `PharImportOutsideTreeException` | An imported application lies outside the tree being packed and cannot ship in it |
| `PharEntryNotFoundException` | No `public/index.php`; pass another entry to `Compiler::phar()` |
| `PharEntryNotPackedException` | The entry exists but does not ship: nothing loose at the application root does |
| `PharStaleOutputException` | An archive of a previous build survived at the output path and could not be removed |
| `PharSymlinkedDirectoryException` | A directory in the tree is a symlink, which `Phar` cannot pack |

At boot, an archive started without `APP_WRITE_DIR` stops with `WriteDirRequiredException`, and one started with a different `APP_WRITE_DIR` than the build stops with `CompiledForAnotherWriteDirException`, naming both directories.

Background: [BEAR.Package#426](https://github.com/bearsunday/BEAR.Package/issues/426).
