---
layout: docs-en
title: Parallel Resource Execution
category: Manual
permalink: /manuals/1.0/en/async.html
---

# Parallel Resource Execution <sup style="font-size:0.5em; color:#666; font-weight:normal;">Alpha</sup>

BEAR.Async turns the previously sequential fetch of `#[Embed]` resources into transparent parallel execution. Without touching your resource code, just add a dedicated entrypoint script for parallel execution and embedded resources automatically switch to parallel fetching.

## Overview

In standard BEAR.Sunday, `#[Embed]` resources are fetched sequentially. With BEAR.Async and an execution mode selected, they are fetched in parallel.

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

## Design Philosophy

### URL as Intent

In BEAR.Sunday, a URI expresses **intent**, not just a location.

```php
#[Embed(rel: 'profile', src: 'query://self/user_profile{?id}')]
```

The `query://self/user_profile` expresses only the intent: "I want the user's profile information." This separation of "What" from "How" allows the same code to work in both sync and parallel execution. Debug with Xdebug in development, then start from `bin/async.php` in production to enable parallel execution.

### Solving the Function Coloring Problem

Async programming has the "Function Coloring" problem—functions calling async functions must themselves be async, causing "async contamination" throughout the codebase.

In BEAR.Sunday, the "resource" boundary cuts through this problem. No async-specific code is required—resource classes don't need to know how they were invoked.

## Installation

```bash
composer require bear/async
```

BEAR.Async 0.3.0 or later is recommended. It depends on `bear/resource`
1.32+ so async embeds are also resolved correctly during HAL/JSON
serialization.

## Execution Modes

Choose the appropriate execution mode based on your server environment.

| Use Case | Entrypoint | Runtime setup |
|---|---|---|
| PHP-FPM / Apache with embedded resources | `bin/async.php` | library `bootstrap.php` overlay |
| Swoole HTTP Server | `bin/swoole.php` | install `AsyncSwooleModule` in `AppModule` |

### Parallel execution (ext-parallel)

The recommended mode for typical PHP-FPM / Apache web applications with embedded resources. It runs `#[Embed]` in parallel using an ext-parallel thread pool.

Add `bin/async.php` next to `bin/app.php`. The entrypoint hands off to the library `bootstrap.php`, which overlays the ext-parallel runtime on the normal `AppModule`:

```text
bin/async.php → vendor/bear/async/bootstrap.php → AppModule + runtime overlay
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

Do not install the parallel runtime in `AppModule` directly — the bootstrap is the only supported install path. The same `AppModule` works under `bin/app.php` (sync) and `bin/async.php` (parallel) unchanged.

To override the worker pool size (default = CPU cores), pass it as the
optional 6th argument:

```php
exit((require $bootstrap)($context, 'MyVendor\MyApp', dirname(__DIR__), $GLOBALS, $_SERVER, 8));
```

#### ext-parallel constraints

Worker runtimes are separate threads with their own Zend memory. Embedded
resources executed in parallel should be read-only, idempotent GET resources
with no ordering dependency. Each worker has its own DI container, so
request-local mutable state and "same instance" assumptions do not cross the
thread boundary.

Arguments and return values crossing the thread boundary must be copyable:
scalar values, `null`, or nested arrays of those values. Objects, closures,
and resources fail fast. Keep interceptors used inside parallel embed graphs
idempotent, and avoid mutating request-local shared state there.

### Swoole execution (ext-swoole)

For applications already running on Swoole HTTP Server with high concurrency requirements.

ext-parallel uses worker runtimes (separate threads), so it is selected by a separate entrypoint. ext-swoole runs inside one server process, so it is installed as an application module.

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

Swoole coroutines share memory, so `PdoPoolEnvModule` is required for connection pooling.
For read-heavy embed graphs, size the pool for the internal parallelism as
well as HTTP concurrency. A practical starting point is
`PDO_POOL_SIZE >= embed_count * request_concurrency` when you want to avoid
queueing; use a smaller pool intentionally when you want database backpressure.

Swoole coroutines and active Xdebug are not a safe combination. Run Swoole
entrypoints without Xdebug loaded, or set `XDEBUG_MODE=off` for local
verification.

## Usage

Once an execution mode is selected, existing `#[Embed]` resources are automatically executed in parallel.

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

By using `bin/app.php` in development and `bin/async.php` in production, you can debug in sync mode and run parallel in production. `AppModule` is unaware of the execution form, so the same code runs unchanged in both modes.

## When to Choose Parallel

For a read-only resource graph that embeds multiple independent GET resources,
parallel execution should be the first candidate when the runtime extension is
available and the downstream database or API capacity is sized for the extra
concurrency. This is where BEAR.Async is strongest: application code declares
the resource graph with `#[Embed]`; the Linker implementation decides whether
the graph is resolved sequentially, with ext-parallel workers, or with Swoole
coroutines.

### Preconditions

- Embedded resources are read-only GET resources with no ordering dependency.
- `ext-parallel` or `ext-swoole` is available in the target runtime.
- Downstream capacity is sized for internal embed parallelism, not only for
  incoming HTTP request concurrency.
- ext-parallel steady-state performance requires a resident process that keeps
  the `parallel\Runtime` pool warm, such as PHP-FPM workers or a benchmark HTTP
  harness. One-shot CLI runs include runtime startup cost and should be read as
  cold-start behavior.

### Adapter guide

| Situation | Recommended adapter |
|---|---|
| Swoole HTTP server is acceptable and high throughput is needed | Swoole adapter |
| PHP-FPM / Apache process model should remain and workers stay warm | ext-parallel adapter |
| Extension support is unavailable or portability is the priority | Sync adapter |

### Cases with little or no gain

- The downstream database or API cannot absorb the added concurrency because
  of pool limits, saturation, or rate limits.
- Each embedded resource is already extremely fast; fixed runtime overhead can
  dominate in that case.
- Embedded resources have real ordering dependencies or share mutable
  request-local state.
- One-shot CLI and cron-style jobs can still use BEAR.Async, but they measure
  cold-start behavior rather than warmed per-request latency.

## Demo and Benchmarks

The BEAR.Async repository includes a Docker-based demo that starts MySQL,
seeds a dashboard resource graph with 8 independent SQL-backed GET embeds,
and provides Sync, ext-parallel, and Swoole entrypoints.

```bash
cd demo
docker compose up -d --wait parallel
docker compose exec parallel composer app -- get 'app://self/dashboard?user_id=1'
docker compose exec parallel composer async -- get 'app://self/dashboard?user_id=1'
```

The demo separates cold one-shot CLI measurements from steady-state HTTP
measurements with `wrk`:

```bash
docker compose exec parallel composer parallel-benchmark
docker compose exec parallel composer steady-state-parallel
docker compose up -d --wait swoole
docker compose exec swoole composer swoole-benchmark
docker compose exec swoole composer steady-state-swoole
```

Cold one-shot CLI numbers include startup work such as DI lookup and, for
ext-parallel, one-time `parallel\Runtime` spawn. Use the steady-state HTTP
benchmark when evaluating warmed per-request performance.

## Requirements

PHP 8.2+ for the library itself. Each execution mode adds its own runtime requirement:

| Mode | Requires | Application change |
|---|---|---|
| ext-parallel | ZTS PHP + ext-parallel | add `bin/async.php` |
| ext-swoole | ext-swoole | install `AsyncSwooleModule`, use `bin/swoole.php` |

## BEAR.Projection Integration

[BEAR.Projection](https://github.com/bearsunday/BEAR.Projection) transforms SQL query results into typed Projection objects and exposes them as resources via the `query://` scheme. Combined with `#[Embed]`, multiple SQL queries execute in parallel.

Projection classes are defined as immutable value objects.

```php
final class UserProfile
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $age,
        public readonly string $avatarUrl,
    ) {}
}
```

Factory classes transform raw SQL data into Projections. Dependencies can be injected via DI, enabling business logic like age calculation or URL resolution.

```php
final class UserProfileFactory
{
    public function __construct(
        private readonly AgeCalculator $ageCalculator,
        private readonly ImageUrlResolver $imageResolver,
    ) {}

    public function __invoke(
        string $id,
        string $name,
        string $birthDate,
        string $avatarPath,
    ): UserProfile {
        return new UserProfile(
            id: $id,
            name: $name,
            age: $this->ageCalculator->fromBirthDate($birthDate),
            avatarUrl: $this->imageResolver->resolve($avatarPath),
        );
    }
}
```

SQL files return columns corresponding to Factory parameter names.

```sql
-- var/sql/query/user_profile.sql
SELECT id, name, birth_date, avatar_path FROM users WHERE id = :id
```

When used with `#[Embed]`, multiple Projections execute in parallel.

```php
class User extends ResourceObject
{
    #[Embed(rel: 'profile', src: 'query://self/user_profile{?id}')]
    #[Embed(rel: 'orders', src: 'query://self/user_orders{?id}')]
    public function onGet(string $id): static
    {
        return $this;
    }
}
```

## SQL Batch Execution

Parallel SQL query execution using mysqli's native async support is also provided.

```php
use BEAR\Async\Module\MysqliEnvModule;

$this->install(new MysqliEnvModule(
    'MYSQLI_HOST',
    'MYSQLI_USER',
    'MYSQLI_PASSWORD',
    'MYSQLI_DATABASE',
));
```

```php
use BEAR\Async\SqlBatch;
use BEAR\Async\SqlBatchExecutorInterface;

class MyService
{
    public function __construct(
        private SqlBatchExecutorInterface $executor,
    ) {}

    public function getData(int $userId): array
    {
        $results = (new SqlBatch($this->executor, [
            'user' => ['SELECT * FROM users WHERE id = :id', ['id' => $userId]],
            'posts' => ['SELECT * FROM posts WHERE user_id = :user_id', ['user_id' => $userId]],
            'comments' => ['SELECT * FROM comments WHERE user_id = :user_id', ['user_id' => $userId]],
        ]))();

        return [
            'user' => $results['user'][0] ?? null,
            'posts' => $results['posts'],
            'comments' => $results['comments'],
        ];
    }
}
```

## References

- [BEAR.Async](https://github.com/bearsunday/BEAR.Async)
- [BEAR.Async Demo Guide](https://github.com/bearsunday/BEAR.Async/tree/1.x/demo)
- [BEAR.Async Benchmark Results](https://github.com/bearsunday/BEAR.Async/blob/1.x/docs/benchmark-results.md)
- [BEAR.Projection](https://github.com/bearsunday/BEAR.Projection)
- [Parallel Execution Architecture](https://bearsunday.github.io/BEAR.Async/parallel-execution-architecture.html)
