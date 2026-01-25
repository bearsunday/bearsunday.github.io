---
layout: docs-en
title: Parallel Resource Execution
category: Manual
permalink: /manuals/1.0/en/async.html
---

# Parallel Resource Execution

BEAR.Async enables transparent parallel execution of BEAR.Sunday's `#[Embed]` resources. By replacing the standard `LinkerInterface` implementation, embedded resources are fetched concurrently without changing any application code.

## Overview

In standard BEAR.Sunday, `#[Embed]` resources are fetched sequentially:

```text
Request
    │
    ├── Embed 1 ──── 50ms
    ├── Embed 2 ──── 50ms
    ├── Embed 3 ──── 50ms
    └── Embed 4 ──── 50ms
    │
Response (200ms total)
```

With BEAR.Async, embedded resources are fetched in parallel:

```text
Request
    │
    ├── Embed 1 ──┬── 50ms
    ├── Embed 2 ──┤
    ├── Embed 3 ──┤
    └── Embed 4 ──┘
    │
Response (50ms total)
```

## How It Works

### Architecture

```text
LinkerInterface (bear/resource)
       ↓ replaced by
AsyncLinker ──uses──→ AsyncInterface
                           ↓ implemented by
              ┌────────────┼────────────┐
        ParallelAsync  SwooleAsync  SyncAsync
        (ext-parallel)  (ext-swoole) (fallback)
```

### Key Components

| Component | Responsibility |
|-----------|----------------|
| AsyncLinker | Replaces standard Linker, executes crawl requests level-by-level in parallel |
| AsyncInterface | Adapter interface for different async runtimes |
| ParallelAsync | Thread pool executor using ext-parallel |
| SwooleAsync | Coroutine executor using ext-swoole |
| SyncAsync | Sequential fallback when no async extension is available |

### Execution Flow

1. `AsyncLinker.linkCrawl()` collects all embed requests at each level
2. `RequestBatch` deduplicates requests by URI+query hash
3. `AsyncInterface` executes all tasks in parallel
4. Results are cached and distributed to all requesters

```text
Level 1: Users → all user requests execute in parallel
Level 2: Posts for each user → all post requests execute in parallel
Level 3: Comments for each post → all comment requests execute in parallel
```

## Installation

```bash
composer require bear/async
```

### Requirements

- PHP 8.2+
- bear/resource ^1.17
- ray/di ^2.18

### Optional Extensions

- **ext-parallel**: For thread-based parallel execution (requires ZTS PHP)
- **ext-swoole**: For coroutine-based parallel execution

## Configuration

### PHP-FPM + ext-parallel

Recommended for typical web applications using PHP-FPM or Apache.

```php
use BEAR\Async\Module\AsyncParallelModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new PackageModule());
        $this->install(new AsyncParallelModule(
            namespace: 'MyVendor\MyApp',
            context: 'prod-app',
            appDir: dirname(__DIR__),
        ));
    }
}
```

Pool size defaults to CPU core count. To override:

```php
$this->install(new AsyncParallelModule(
    namespace: 'MyVendor\MyApp',
    context: 'prod-app',
    appDir: dirname(__DIR__),
    poolSize: 8,
));
```

### Swoole + Coroutines

For applications running on Swoole HTTP Server with high concurrency requirements.

```php
use BEAR\Async\Module\AsyncSwooleModule;
use BEAR\Async\Module\PdoPoolModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new PackageModule());
        $this->install(new AsyncSwooleModule());
        // Connection pool required for Swoole coroutines
        $this->install(new PdoPoolModule($dsn, $user, $password));
    }
}
```

#### Why PdoPoolModule?

In Swoole, coroutines share memory within the same process. Database connections created in one coroutine cannot be safely used by another. `PdoPoolModule` provides a connection pool that manages PDO instances across coroutines.

## Module Selection

| Use Case | Recommended Module |
|----------|-------------------|
| PHP-FPM / Apache | `AsyncParallelModule` |
| Swoole HTTP Server | `AsyncSwooleModule` |

### Comparison

| | AsyncParallelModule | AsyncSwooleModule |
|---|---|---|
| Concurrency | Thread pool (CPU cores) | Coroutines (thousands) |
| PDO handling | Isolated per thread | Connection pool required |
| Server | PHP-FPM / Apache | Swoole HTTP Server |
| Setup | Simple | Requires Swoole server |

## Usage

Once the module is installed, no code changes are required. Existing `#[Embed]` resources will automatically be executed in parallel.

```php
use BEAR\Resource\Annotation\Embed;

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

All three embedded resources (`user`, `notifications`, `stats`) will be fetched in parallel.

### Context-Based Configuration

Use different contexts to enable async in production while using sync in development:

```php
// src/Module/ProdModule.php
class ProdModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new AsyncParallelModule(/* ... */));
    }
}

// src/Module/DevModule.php
class DevModule extends AbstractModule
{
    protected function configure(): void
    {
        // No async module - uses standard sequential execution
    }
}
```

## Performance

### Benchmark Results

| Scenario | Sync Time | Parallel Time | Speedup |
|----------|-----------|---------------|---------|
| 3 embeds (50ms each) | 150ms | ~52ms | 2.9x |
| 5 embeds (50ms each) | 250ms | ~54ms | 4.6x |
| 11 embeds (50ms each) | 550ms | ~59ms | 9.4x |

### When Parallel Execution Helps

- I/O-bound embed operations (database queries, API calls)
- Multiple independent embeds
- Adequate CPU cores available

### When It May Not Help

- CPU-bound operations (complex calculations)
- Single embed or sequential dependencies
- Very fast queries (< 5ms) where overhead dominates

## References

- [BEAR.Async Repository](https://github.com/bearsunday/BEAR.Async)
- [Parallel Execution Architecture](https://bearsunday.github.io/BEAR.Async/parallel-execution-architecture.html)
- [Resource Link](resource_link.html) - Documentation for `#[Embed]` and `#[Link]`
- [High-Performance Servers](swoole.html) - Running BEAR.Sunday on Swoole
