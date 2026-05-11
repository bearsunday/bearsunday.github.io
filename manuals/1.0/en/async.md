---
layout: docs-en
title: Parallel Resource Execution
category: Manual
permalink: /manuals/1.0/en/async.html
---

# Parallel Resource Execution <sup style="font-size:0.5em; color:#666; font-weight:normal;">Alpha</sup>

BEAR.Async enables transparent parallel execution of `#[Embed]` resources. Embedded resources are fetched in parallel without changing any application code. Resource classes written 10 years ago can benefit from parallel execution just by switching the execution mode.

## Overview

In standard BEAR.Sunday, `#[Embed]` resources are fetched sequentially. With BEAR.Async, they are fetched in parallel.

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

The `query://self/user_profile` expresses only the intent: "I want the user's profile information." This separation of "What" from "How" allows the same code to work in both sync and parallel execution. Debug with Xdebug in development, then switch the entrypoint or module in production to enable parallel execution.

### Solving the Function Coloring Problem

Async programming has the "Function Coloring" problem—functions calling async functions must themselves be async, causing "async contamination" throughout the codebase.

In BEAR.Sunday, the "resource" boundary cuts through this problem. No async-specific code is required—resource classes don't need to know how they were invoked.

## Installation

```bash
composer require bear/async
```

## Execution Modes

BEAR.Async chooses an execution mode at the application boundary. Your resource code (the `#[Embed]` graph) does not change.

| Mode | Entrypoint | Runtime requirement |
|-----|----------|------|
| ext-parallel | add `bin/async.php` | ZTS PHP + ext-parallel |
| ext-swoole | `bin/swoole.php` + install `AsyncSwooleModule` | ext-swoole |

ext-parallel uses worker runtimes (separate threads with separate memory), so it is selected by a separate entrypoint. ext-swoole runs inside one server process as coroutines, so it is installed as an application module. The asymmetry follows the runtime model.

### ext-parallel (PHP-FPM / Apache)

Add `bin/async.php` next to `bin/app.php`. It hands off to the library bootstrap, which overlays the ext-parallel runtime on the normal AppModule:

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

Do not install the parallel runtime in `AppModule` directly. The bootstrap is the only supported install path so the same `AppModule` works under `bin/app.php` (sync) and `bin/async.php` (parallel) unchanged.

To override the worker pool size (default = CPU cores), pass it as the optional 6th argument:

```php
exit((require $bootstrap)($context, 'MyVendor\MyApp', dirname(__DIR__), $GLOBALS, $_SERVER, 8));
```

#### Constraints

Worker runtimes are separate threads with their own Zend memory. Embedded resources executed in parallel must satisfy:

- **Pure / idempotent** — same input must yield same output. Workers do not share request-scoped state (no shared session, no shared logger context).
- **Each worker holds its own DI container** — singletons in `AppModule` are not the same instance across threads. Avoid relying on "same-instance" guarantees inside parallelizable embeds.
- **Payload copyability** — arguments passed across the thread boundary (currently the `query` array) and return values (`$ro->body` or the rendered string) must be scalar / null / nested arrays of those. Objects, closures, and resources will fail fast via `NonCopyablePayloadException`.
- **Interceptors that mutate request-local state will misbehave** across worker boundaries. Keep cross-cutting concerns idempotent or scope them outside the parallelized embed graph.

### ext-swoole (Swoole HTTP Server)

For applications already running on Swoole HTTP Server with high concurrency requirements. Install `AsyncSwooleModule` in `AppModule` and start with `bin/swoole.php`.

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

Swoole coroutines share memory, so `PdoPoolEnvModule` is required for connection pooling. The cross-thread copyability constraint does not apply on Swoole.

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

Debug in sync mode with `bin/app.php` and Xdebug during development, then switch to `bin/async.php` for parallel execution in production.

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
- [BEAR.Projection](https://github.com/bearsunday/BEAR.Projection)
- [Parallel Execution Architecture](https://bearsunday.github.io/BEAR.Async/parallel-execution-architecture.html)
