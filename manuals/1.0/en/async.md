---
layout: docs-en
title: Parallel Resource Execution
category: Manual
permalink: /manuals/1.0/en/async.html
---

# Parallel Resource Execution <sup style="font-size:0.5em; color:#666; font-weight:normal;">Alpha</sup>

BEAR.Async enables transparent parallel execution of `#[Embed]` resources. It preserves your resource code and lets you choose an async execution mode at the application boundary — no async/await, no Promise, no yield, no rewrites. Resource classes written 10 years ago can benefit from parallel execution unchanged.

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

use BEAR\AppMeta\Meta;

require dirname(__DIR__) . '/vendor/autoload.php';

$bootstrap = dirname(__DIR__) . '/vendor/bear/async/bootstrap.php';

exit((require $bootstrap)(
    name: 'MyVendor\MyApp',
    context: $_GET['_context'] ?? 'prod-hal-app',
    appDir: dirname(__DIR__),
    globals: [
        'GET'    => $_GET,
        'POST'   => $_POST,
        'COOKIE' => $_COOKIE,
    ],
    server: $_SERVER,
));
```

Do not install the parallel runtime in `AppModule` directly — the bootstrap is the only supported install path. The same `AppModule` works under `bin/app.php` (sync) and `bin/async.php` (parallel) unchanged.

To override the worker pool size (default = CPU cores), pass it as the optional 6th argument.

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
use BEAR\Async\Module\MysqliBatchEnvModule;

$this->install(new MysqliBatchEnvModule('MYSQL_HOST', 'MYSQL_USER', 'MYSQL_PASS', 'MYSQL_DB'));
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
        return (new SqlBatch($this->executor, [
            'user' => ['SELECT * FROM users WHERE id = ?', [$userId]],
            'posts' => ['SELECT * FROM posts WHERE user_id = ?', [$userId]],
        ]))();
    }
}
```

## References

- [BEAR.Async](https://github.com/bearsunday/BEAR.Async)
- [BEAR.Projection](https://github.com/bearsunday/BEAR.Projection)
- [Parallel Execution Architecture](https://bearsunday.github.io/BEAR.Async/parallel-execution-architecture.html)
