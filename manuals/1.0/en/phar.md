---
layout: docs-en
title: Phar
category: Manual
permalink: /manuals/1.0/en/phar.html
---

# Phar

A [phar](https://www.php.net/manual/en/intro.phar.php) is the application as one file: the code, `vendor/`, and the compiled DI scripts in a single archive. The boot reads the archive and writes nothing into it — a deploy is a copy of one file, a rollback is the file before it.

```text
app.phar                                  the application, vendor/, compiled DI scripts
/tmp/MyVendor/MyProject/prod-hal-app      everything the runtime writes
```

Requires BEAR.Package 1.24+.

## Make your application a phar

The build script compiles, then packs. Both steps are on the compiler:

```php
<?php
// bin/compile.php
use BEAR\Package\Compiler;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('memory_limit', '-1');

$context = 'prod-hal-app';
$writeDir = getenv('APP_WRITE_DIR') ?: null;

$compiler = new Compiler('MyVendor\MyProject', $context, dirname(__DIR__), $writeDir);
$code = $compiler();

exit($code === 0 ? $compiler->phar() : $code);
```

The script names the application, the context it boots — the same one `public/index.php` uses — and reads the write directory from the environment. The rest it does not have to say, because packing is the framework's business: the archive carries named top-level directories only — `src`, `public`, `bin`, `vendor`, `var`, and wherever an imported application sits — and of `var/` only this build: `var/build/{context}`, which holds the DI scripts with their compile marker and whatever [compile steps](production.html#compile-steps) wrote. `var/log` and `var/tmp` stay out, as do `.env`, `autoload.php`, `preload.php` and `tests/`; the directories left behind are printed as `Not packed:`. The marker is `.bear-compile.json`, and it is what `phar()` reads to decide: `app`, `context`, `tmpDir`, `time`. The `.env` file stays out, but the values it held are compiled into the DI scripts, and those ship: treat the archive as a secret. `phar.readonly` is handled in a child process, so there is no ini flag to remember.

```bash
APP_WRITE_DIR=/tmp php bin/compile.php
```

```text
Compiled: 16 resource classes
Phar: /app/app.phar (7.5MB, 2100 files)
Not packed: tests
```

`__invoke()` and `phar()` are separate steps, so a build pipeline can compile in one job and pack in another. `phar()` packs what is on disk and refuses a context that was never compiled, or one compiled to write inside the tree. It writes `{appDir}/app.phar`, beside the `autoload.php` and `preload.php` the compile wrote; another entry is its one argument. Compiling several contexts is a loop in your script, and all three outputs are fixed paths, so move them between contexts as [Production](production.html#compilation-recommended) shows.

## Run

```bash
APP_WRITE_DIR=/tmp php app.phar get '/index?name=BEAR'
```

The stub runs `public/index.php` from inside the archive, so `dirname(__DIR__)` in `src/Injector.php` is `phar:///path/app.phar`. The entry points are the ones [read-only deployments](production.html#writable-paths) shows; nothing else changes.

php-fpm runs a file, not an archive, so the entry point sits next to it and loads the autoloader from inside:

```php
<?php
// index.php, in the same directory as app.phar
require 'phar://' . __DIR__ . '/app.phar/vendor/autoload.php';

exit((new MyVendor\MyProject\Bootstrap())('prod-hal-app', $GLOBALS, $_SERVER, getenv('APP_WRITE_DIR') ?: null));
```

## It moves

The archive is the build. Copy it anywhere — another directory, another machine — and boot it there: nothing outside it is read, so the tree it was packed from can be deleted.

```bash
cp app.phar /srv/releases/2026-08-23.phar
APP_WRITE_DIR=/tmp php /srv/releases/2026-08-23.phar get '/index?name=BEAR'
```

What it holds to is the write directory, not a location. That is the one thing a boot has to match, and an archive says so when it does not: [When the build stops](#when-the-build-stops).

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
| `PharImportsUnreadableException` | The compiled container declares its imports in a form this version cannot read: recompile with the version that packs |
| `PharWritesInsideArchiveException` | An application — the host or an import — was compiled to write into the tree. Compile with `APP_WRITE_DIR` set |
| `PharImportOutsideTreeException` | An imported application lies outside the tree being packed and cannot ship in it |
| `PharEntryNotFoundException` | No `public/index.php`; pass another entry to `Compiler::phar()` |
| `PharEntryNotPackedException` | The entry exists but does not ship: nothing loose at the application root does |
| `PharStaleOutputException` | An archive of a previous build survived at the output path and could not be removed |
| `PharSymlinkedDirectoryException` | A directory in the tree is a symlink, which `Phar` cannot pack |

At boot, an archive started without `APP_WRITE_DIR` stops with `WriteDirRequiredException`, and one started with a different `APP_WRITE_DIR` than the build stops with `CompiledForAnotherWriteDirException`, naming both directories.

Background: [BEAR.Package#426](https://github.com/bearsunday/BEAR.Package/issues/426).
