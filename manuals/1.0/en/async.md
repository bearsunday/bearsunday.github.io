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
| Swoole HTTP Server | `bin/swoole.php` | a `swoole` context module installs `AsyncSwooleModule`; `AppModule` is unchanged |

| | ext-parallel | ext-swoole / ext-openswoole |
|---|---|---|
| Concurrency | Thread pool (CPU cores) | Coroutines (thousands) |
| Memory | Separate per worker | Shared (process-level) |
| PDO handling | Isolated per thread | Connection pool required |
| Server | PHP-FPM / Apache | Swoole HTTP Server |
| Setup | Add `bin/async.php` | Add `bin/swoole.php` |

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

Do not install the parallel runtime in `AppModule` directly. The bootstrap is the only supported install path, so the same `AppModule` runs unchanged under `bin/app.php` (sequential) and `bin/async.php` (parallel).

Under classic PHP-FPM / Apache the `parallel\Runtime` pool lives in process state, so a request that needs it rebuilds the pool: the thread spawn, autoload, and DI container cost is paid per request. `ParallelAsync` warms one worker synchronously before dispatching, so the container is built once rather than once per thread, but steady-state low latency needs a resident process that keeps the pool warm across requests. See [`demo/bin/parallel-server.php`](https://github.com/bearsunday/BEAR.Async/blob/1.x/demo/bin/parallel-server.php) and the [benchmark results](https://github.com/bearsunday/BEAR.Async/blob/1.x/docs/benchmark-results.md).

#### ext-parallel constraints

Workers run on separate threads, each with an independent Zend memory space. Embedded resources executed in parallel should be read-only (idempotent GET) resources with no ordering dependency. Because each worker holds its own DI container, request-local mutable state and "same instance" assumptions do not carry across thread boundaries.

Arguments and return values that cross the thread boundary must be copyable: scalar values, `null`, and nested arrays of those. Objects, closures, and resources fail fast with `NonCopyablePayloadException`. Keep any interceptors applied to embedded resources executed in parallel idempotent, and do not mutate request-local shared state.

### Swoole execution (ext-swoole)

A runtime for applications already running on a Swoole HTTP server and aiming for high concurrency.

ext-parallel runs in worker threads, so it is selected by a separate entrypoint. ext-swoole runs inside the server process, so it is selected by the context string: add a `swoole` context module and boot with a context such as `prod-swoole-hal-api-app`. `AppModule` stays as it is, and `bin/app.php` with `hal-api-app` still runs sequentially for development.

```php
namespace MyVendor\MyApp\Module;

use BEAR\Async\Module\AsyncSwooleModule;
use BEAR\Async\Module\PdoPoolEnvModule;
use Ray\Di\AbstractModule;

final class SwooleModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new AsyncSwooleModule());
        $this->install(new PdoPoolEnvModule('PDO_DSN', 'PDO_USER', 'PDO_PASSWORD'));
    }
}
```

A context module wraps `AppModule`, so its bindings take precedence over the framework's sequential `LinkCrawler` and `EmbedInterceptor`. Do not install `AsyncSwooleModule` inside `AppModule` instead: Ray.Di keeps the first binding, and installed after `PackageModule` the async bindings are silently dropped.

Run the Swoole server on the compiled injector. The reflective `Injector` shares its resolution state across the whole process; when a coroutine suspends inside a provider (waiting for a pooled connection, say), another coroutine enters that state and fails with a `CircularDependency` whose chain is not circular. Boot through `BEAR\Package\Injector` so production contexts get Ray.Compiler's `CompiledInjector`, and call its `warmup()` before the server accepts requests so every singleton is built while there is still a single coroutine. [`demo/bin/swoole.php`](https://github.com/bearsunday/BEAR.Async/blob/1.x/demo/bin/swoole.php) shows the sequence; see [Ray.Di: Coroutine servers](https://ray-di.github.io/manuals/1.0/en/performance_boost.html) for the contract.

In Swoole, coroutines share memory, so a connection pool via `PdoPoolEnvModule` is required. In read-heavy setups that make heavy use of embedded resources, the pool size should account not only for the number of incoming HTTP requests but also for the number of embeds executed concurrently within one request. Each coroutine holds one connection until it ends, and the parent request keeps its own while its embeds run, so start from `PDO_POOL_SIZE >= (1 + embed_count) * request_concurrency`; intentionally use a smaller pool when you want to cap concurrent connections to the database.

`PdoPoolModule` / `RedisPoolModule` and their env-driven counterparts take a `borrowTimeout` (default 5.0 s). Waiting on an exhausted pool fails with `PoolTimeoutException` instead of blocking forever. Every checkout is pinged first (`SELECT 1` for PDO, `PING` for Redis); a dead connection is discarded and retried once, and if the retry is also dead `StalePooledConnectionException` is thrown with the driver error as the previous exception. Redis connections are cached per coroutine the same way PDO connections are, so repeated injections within one coroutine reuse one checkout.

Never inject `ExtendedPdoInterface`, `PDO`, or `Redis` into a singleton. The provider hands out a connection borrowed for one coroutine; a singleton would hold that connection for the life of the process, defeating the pool and mixing coroutines. Keep DB-using dependencies prototype-scoped (the default), or inject `ProviderInterface<ExtendedPdoInterface>` and call `get()` per use.

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

## How it works

BEAR.Async replaces two bear/resource bindings:

1. `LinkCrawlerInterface` → `AsyncLinkCrawler`: crawls `#[Link(crawl:)]` graphs level by level. Requests at each level are batched, deduplicated by URI+query hash, and executed together; results are distributed to every requester.
2. `EmbedInterceptorInterface` → `AsyncEmbedInterceptor`: wraps each `#[Embed]` in an `AsyncRequest` over a `DeferredRequest` and registers it with `PendingRequests`, which dispatches every pending embed as one batch the first time any of them is rendered.

Both hand the batch to the configured `AsyncInterface` adapter: `ParallelAsync`, `SwooleAsync`, or `SyncAsync` (the default when no runtime module is installed).

```text
Level 1: Users → all user requests execute in parallel
Level 2: Posts for each user → all post requests execute in parallel
Level 3: Comments for each post → all comment requests execute in parallel
```

### Failure semantics

One failing embed or crawl task does not kill the Swoole worker or abort its siblings: every task runs to completion, then the first exception is rethrown to the caller, producing a 500 for that request alone. `ParallelAsync` follows the same rule: every dispatched `Future` is joined before the first `Throwable` is rethrown.

There is no silent fallback. If the required extension is not loaded, the owning module throws `ExtensionNotLoadedException` from `configure()` rather than degrading to `SyncAsync`.

## Demo and benchmarks

The BEAR.Async repository includes a Docker-based demo and benchmark scripts that compare Sync, ext-parallel, and Swoole behavior. See the [demo guide](https://github.com/bearsunday/BEAR.Async/tree/1.x/demo) and [benchmark results](https://github.com/bearsunday/BEAR.Async/blob/1.x/docs/benchmark-results.md) for details.

## Requirements

Each runtime requires the corresponding PHP extension.

| Runtime | Requires | Application-side change |
|---|---|---|
| ext-parallel | ZTS PHP + ext-parallel | add `bin/async.php` |
| ext-swoole | ext-swoole or ext-openswoole | add a `swoole` context module, use `bin/swoole.php` |

## SQL resources with BDR + `#[Embed]`

To run multiple SQL queries for one page, split each query into its own `ResourceObject` and let `#[Embed]` parallelize them. The call site just composes resources — the runtime decides how to execute the embeds in parallel.

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

// Aggregate — the embeds run in parallel under BEAR.Async
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
- BEAR.Async runs the three embeds in parallel via ext-parallel (PHP-FPM / Apache) or Swoole coroutines
- Without ext-parallel and without Swoole the same code runs synchronously per request, which is fine for PHP-FPM (each request is its own process)
- For Swoole, install `PdoPoolEnvModule` so each coroutine borrows a pooled PDO connection

## References

- [BEAR.Async](https://github.com/bearsunday/BEAR.Async)
- [BEAR.Async Demo Guide](https://github.com/bearsunday/BEAR.Async/tree/1.x/demo)
- [BEAR.Async Benchmark Results](https://github.com/bearsunday/BEAR.Async/blob/1.x/docs/benchmark-results.md)
- [Ray.MediaQuery BDR Pattern](https://github.com/ray-di/Ray.MediaQuery/blob/1.x/BDR_PATTERN.md)
- [Parallel Execution Architecture](https://bearsunday.github.io/BEAR.Async/parallel-execution-architecture.html)
