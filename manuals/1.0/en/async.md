---
layout: docs-en
title: Parallel Resource Execution
category: Manual
permalink: /manuals/1.0/en/async.html
---

# Parallel Resource Execution <sup style="font-size:0.5em; color:#666; font-weight:normal;">Alpha</sup>

BEAR.Async turns the previously sequential fetch of `#[Embed]` resources into transparent parallel execution. Without touching your resource code, just add a dedicated entrypoint script for parallel execution and embedded resources automatically switch to parallel fetching.

## Overview

In standard BEAR.Sunday, `#[Embed]` resources are fetched sequentially. With BEAR.Async and a runtime selected, they are fetched in parallel.

```text
[Sequential]                     [Parallel]
Request                          Request
    │                                │
    ├── Embed 1 ──── 50ms            ├── Embed 1 ──┬── 50ms
    ├── Embed 2 ──── 50ms            ├── Embed 2 ──┤
    ├── Embed 3 ──── 50ms            ├── Embed 3 ──┤
    └── Embed 4 ──── 50ms            └── Embed 4 ──┘
    │                                │
Response (200ms)                 Response (50ms)
```

## Installation

```bash
composer require bear/async
```

## Runtime environments

Choose a runtime that matches your server setup.

| Use case | Entrypoint | Runtime setup |
|---|---|---|
| PHP-FPM / Apache (with embedded resources) | `bin/async.php` | the library `bootstrap.php` overlays the parallel runtime on `AppModule` |
| Swoole HTTP Server | `bin/swoole.php` | install `AsyncSwooleModule` in `AppModule` |

### Parallel execution (ext-parallel)

A runtime for typical web applications running on PHP-FPM / Apache. It executes `#[Embed]` in parallel using an ext-parallel thread pool.

Add `bin/async.php` next to `bin/app.php`. This entrypoint delegates to the library `bootstrap.php`, which overlays the ext-parallel runtime on top of the normal `AppModule`.

```text
bin/async.php → vendor/bear/async/bootstrap.php → AppModule + parallel runtime
```

```php
<?php // bin/async.php

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';

$bootstrap = dirname(__DIR__) . '/vendor/bear/async/bootstrap.php';
if (! file_exists($bootstrap)) {
    throw new LogicException('"bear/async" is not installed.');
}

$defaultContext = PHP_SAPI === 'cli' ? 'cli-hal-api-app' : 'hal-api-app';
$context = getenv('APP_CONTEXT') ?: $defaultContext;

exit((require $bootstrap)(
    $context,
    'MyVendor\MyApp',
    dirname(__DIR__),
    $GLOBALS,
    $_SERVER,
));
```

To change the worker pool size (defaults to the number of CPU cores), pass it explicitly as the 6th argument.

```php
exit((require $bootstrap)($context, 'MyVendor\MyApp', dirname(__DIR__), $GLOBALS, $_SERVER, 8));
```

#### ext-parallel constraints

Workers run on separate threads, each with an independent Zend memory space. Embedded resources executed in parallel should be read-only (idempotent GET) resources with no ordering dependency. Because each worker holds its own DI container, request-local mutable state and "same instance" assumptions do not carry across thread boundaries.

Arguments and return values that cross the thread boundary must be copyable: scalar values, `null`, and nested arrays of those. Passing objects, closures, or resources fails immediately. Keep any interceptors applied to embedded resources executed in parallel idempotent, and do not mutate request-local shared state.

### Swoole execution (ext-swoole)

A runtime for applications already running on a Swoole HTTP server and aiming for high concurrency.

Because ext-parallel runs in workers (separate threads), it is selected via a separate entrypoint. ext-swoole, on the other hand, runs inside the same server process, so it is installed as an application module.

```php
use BEAR\Async\Module\AsyncSwooleModule;
use BEAR\Async\Module\PdoPoolEnvModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new AsyncSwooleModule());
        $this->install(new PdoPoolEnvModule('PDO_DSN', 'PDO_USER', 'PDO_PASSWORD'));
    }
}
```

In Swoole, coroutines share memory, so a connection pool via `PdoPoolEnvModule` is required. In read-heavy setups that make heavy use of embedded resources, the pool size should account not only for the number of incoming HTTP requests but also for the number of embeds executed concurrently within one request. To avoid queueing, use `PDO_POOL_SIZE >= embed_count * request_concurrency` as a starting point; intentionally use a smaller pool when you want to cap concurrent connections to the database.

> **Technical note (pool connection acquisition):** Connection acquisition from the pool is managed per coroutine. Even when both `PDO` and `ExtendedPdo` are injected within the same coroutine, they share a single connection and that connection is returned to the pool exactly once via `Coroutine::defer()` when the coroutine ends. This prevents a single piece of work from unintentionally holding two connections. Furthermore, requests embedded via `#[Embed]` are lazily evaluated, so the pool is not touched at the point the embed is declared with `#[Embed]`; connection acquisition is deferred until each request is actually executed.
>
> **Technical note (PDOProxy handling):** Swoole wraps `PDO` in its own `PDOProxy` for coroutine support, but BEAR.Async absorbs this wrapping internally so the value can be treated as a regular `PDO`. If the original `PDO` cannot be extracted for some reason, the reflection failure is not propagated as-is; instead it is surfaced as a domain-specific PDO proxy extraction exception.

Swoole coroutines and an active Xdebug do not run safely together. Run Swoole entrypoints with a PHP that does not load Xdebug, or set `XDEBUG_MODE=off` for local verification.

## Usage

Once a runtime is selected, existing `#[Embed]` resources are automatically executed in parallel.

```php
class Dashboard extends ResourceObject
{
    #[Embed(rel: 'user', src: '/user{?id}')]
    #[Embed(rel: 'notifications', src: '/notifications{?user_id}')]
    #[Embed(rel: 'stats', src: '/stats{?user_id}')]
    public function onGet(string $id): static
    {
        $this->body['id'] = $id;
        return $this;
    }
}
```

In development, run synchronously via `bin/app.php` for debugging; in production, switch to parallel execution by starting from `bin/async.php`.

## Why no code change is needed

In BEAR.Sunday, information is **structured** as resources identified by URIs. `#[Embed]` does not embed the result of a resource — it embeds the resource request itself and declares a relationship between resources. Choosing the execution strategy — sequential, ext-parallel workers, or Swoole coroutines — is the Linker's job; resource classes do not need to know whether they were called synchronously or in parallel.

In the default mode these requests are resolved one by one at rendering time. In parallel mode, the moment the first embedded request is resolved, the remaining embedded requests are executed together in parallel. BEAR.Async asynchronous requests share the same type as ordinary BEAR.Resource requests, so the HAL renderer and other surrounding machinery can integrate them into serialization without being aware of the difference.

The "function coloring" problem often raised in async programming — a function calling an async function must itself be async, contaminating the whole codebase — is cut off at the resource boundary. The code is the same under sync and parallel execution; only the execution strategy changes.

This is not specific to BEAR.Async; it is a property of BEAR.Sunday as a whole. Where MVC frameworks write *how to execute* procedurally, BEAR.Sunday expresses *relationships between resources* declaratively. Because the declaration is independent of the execution strategy, swapping strategies has no effect on the code.

## Demo and benchmarks

The BEAR.Async repository includes a Docker-based demo and benchmark scripts that compare Sync, ext-parallel, and Swoole behavior. See the [demo guide](https://github.com/bearsunday/BEAR.Async/tree/1.x/demo) and [benchmark results](https://github.com/bearsunday/BEAR.Async/blob/1.x/docs/benchmark-results.md) for details.

## Requirements

Each runtime requires the corresponding PHP extension.

| Runtime | Requires | Application-side change |
|---|---|---|
| ext-parallel | ZTS PHP + ext-parallel | add `bin/async.php` |
| ext-swoole | ext-swoole | install `AsyncSwooleModule`, use `bin/swoole.php` |

## SQL resources with BDR + `#[Embed]`

To run multiple SQL queries for one page, split each query into its own `ResourceObject` and let `#[Embed]` parallelize them via AsyncLinker. The call site just composes resources — the runtime decides how to execute the embeds in parallel.

Combined with Ray.MediaQuery's [BDR pattern](https://github.com/ray-di/Ray.MediaQuery/blob/1.x/BDR_PATTERN.md) (`#[DbQuery]` interface + factory + immutable domain object), SQL stays in `var/sql/*.sql`, the call site reads as plain objects, and the resource graph itself is what gets parallelized.

Recipe dependency (not bundled with BEAR.Async):

```bash
composer require ray/media-query
```

```php
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;
use Ray\MediaQuery\Annotation\DbQuery;

// Domain object — immutable snapshot
final class UserAccount
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {
    }
}

// Repository — SQL lives in var/sql/user.sql.
// UserFactory hydrates the row into UserAccount; see BDR_PATTERN.md for factory details.
interface UserRepositoryInterface
{
    #[DbQuery('user', factory: UserFactory::class)]
    public function getUser(int $id): UserAccount;
}

// Resource — one resource per SQL
class User extends ResourceObject
{
    public function __construct(private UserRepositoryInterface $repo)
    {
    }

    public function onGet(int $id): static
    {
        $this->body = ['user' => $this->repo->getUser($id)];

        return $this;
    }
}

// Aggregate — Embeds parallelize automatically under AsyncLinker
class UserDashboard extends ResourceObject
{
    #[Embed(rel: 'user',     src: 'app://self/user{?id}')]
    #[Embed(rel: 'posts',    src: 'app://self/user/posts{?id}')]
    #[Embed(rel: 'comments', src: 'app://self/user/comments{?id}')]
    public function onGet(int $id): static
    {
        return $this;
    }
}
```

- SQL stays in `var/sql/*.sql` (Ray.MediaQuery convention)
- Domain objects are immutable snapshots; no `$results['user'][0] ?? null` plumbing at the call site
- AsyncLinker runs the three embeds in parallel via ext-parallel (PHP-FPM / Apache) or Swoole coroutines
- Without ext-parallel and without Swoole the same code runs synchronously per request, which is fine for PHP-FPM (each request is its own process)
- For Swoole, install `PdoPoolModule` so each coroutine borrows a pooled PDO connection

## References

- [BEAR.Async](https://github.com/bearsunday/BEAR.Async)
- [BEAR.Async Demo Guide](https://github.com/bearsunday/BEAR.Async/tree/1.x/demo)
- [BEAR.Async Benchmark Results](https://github.com/bearsunday/BEAR.Async/blob/1.x/docs/benchmark-results.md)
- [Ray.MediaQuery BDR Pattern](https://github.com/ray-di/Ray.MediaQuery/blob/1.x/BDR_PATTERN.md)
- [Parallel Execution Architecture](https://bearsunday.github.io/BEAR.Async/parallel-execution-architecture.html)
