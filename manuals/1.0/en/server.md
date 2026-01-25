---
layout: docs-en
title: High-Performance Servers
category: Manual
permalink: /manuals/1.0/en/swoole.html
---

# High-Performance Servers

BEAR.Sunday applications can run on high-performance PHP servers that eliminate per-request bootstrap overhead. This guide covers three server options: Swoole, RoadRunner, and FrankenPHP.

## Overview

In traditional PHP-FPM, each request bootstraps the entire application:

```text
Request -> Boot Framework -> Route -> Execute -> Response -> Shutdown
Request -> Boot Framework -> Route -> Execute -> Response -> Shutdown
Request -> Boot Framework -> Route -> Execute -> Response -> Shutdown
```

With persistent worker mode, the application boots once:

```text
Boot Framework (once)
    |
Request -> Route -> Execute -> Response
Request -> Route -> Execute -> Response
Request -> Route -> Execute -> Response
```

This eliminates boot overhead, resulting in significantly lower latency and higher throughput.

## Server Comparison

| Feature | Swoole | RoadRunner | FrankenPHP |
|---------|--------|------------|------------|
| Language | C + PHP | Go + PHP | Go + PHP |
| Worker Mode | Yes | Yes | Yes |
| HTTP/2 | Yes | Yes | Yes |
| HTTP/3 | No | No | Yes |
| WebSocket | Native | Native | Via Caddy |
| Coroutines | Yes | No | No |
| Hot Reload | Manual | Yes | Yes |
| Memory Limit | Shared | Per worker | Per worker |

## Quick Start with Docker

The [bear-sunday-servers](https://github.com/bearsunday/bear-sunday-servers) repository provides ready-to-use Docker configurations for all three servers.

```bash
git clone https://github.com/bearsunday/bear-sunday-servers.git
cd bear-sunday-servers

# Swoole (port 8081)
cd swoole && docker compose up -d && curl http://localhost:8081/

# RoadRunner (port 8082)
cd roadrunner && docker compose up -d && curl http://localhost:8082/

# FrankenPHP (port 8080)
cd frankenphp && docker compose up -d && curl http://localhost:8080/
```

---

## Swoole

[Swoole](https://www.swoole.com/) is a coroutine-based PHP extension providing event-driven asynchronous I/O.

### Features

- **Event-Driven**: Asynchronous I/O handling
- **Coroutines**: Concurrent request processing without threads
- **High Performance**: Eliminates per-request boot overhead
- **Memory Efficient**: Shared memory between workers

### Install

#### Swoole Extension

```bash
pecl install swoole
```

Or compile from source:

```bash
git clone https://github.com/swoole/swoole-src.git && \
cd swoole-src && \
phpize && \
./configure && \
make && make install
```

Add `extension=swoole.so` to your `php.ini`.

#### BEAR.Swoole Package

```bash
composer require bear/swoole
```

### Bootstrap Script

Create `bin/swoole.php`:

```php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';

$bootstrap = dirname(__DIR__) . '/vendor/bear/swoole/bootstrap.php';

$context = getenv('BEAR_CONTEXT') ?: 'prod-hal-app';
$ip = getenv('SWOOLE_IP') ?: '0.0.0.0';
$port = (int) (getenv('SWOOLE_PORT') ?: 8080);

exit((require $bootstrap)(
    $context,
    'MyVendor\MyProject',
    $ip,
    $port
));
```

### Run

```bash
php bin/swoole.php
```

```text
Swoole http server is started at http://127.0.0.1:8080
```

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `BEAR_CONTEXT` | prod-hal-app | BEAR.Sunday context |
| `SWOOLE_IP` | 0.0.0.0 | Server bind address |
| `SWOOLE_PORT` | 8080 | Server port |

### Architecture

```text
Master Process
    |
    +-- Manager Process
           |
           +-- Worker 1 (coroutines)
           +-- Worker 2 (coroutines)
           +-- Worker N (coroutines)
```

Each worker can handle multiple concurrent requests using coroutines.

### Development Notes

Xdebug is not fully compatible with Swoole's coroutines. For debugging:
- Use `var_dump()` / `error_log()` for simple debugging
- Or disable Swoole and use PHP's built-in server with Xdebug

Swoole does not support automatic hot reload. Restart after code changes:

```bash
# With Docker
docker compose restart

# Without Docker
pkill -f swoole.php && php bin/swoole.php
```

---

## RoadRunner

[RoadRunner](https://roadrunner.dev/) is a high-performance Go application server with PSR-7 PHP workers.

### Features

- **Go Application Server**: High-performance process manager
- **PSR-7 Workers**: Standard HTTP message interface
- **Built-in Metrics**: Prometheus-compatible endpoint
- **Hot Reload**: Automatic worker restart on file changes

### Install

#### RoadRunner Binary

Download from [releases](https://github.com/roadrunner-server/roadrunner/releases) or use Docker.

#### PHP Dependencies

```bash
composer require spiral/roadrunner-http nyholm/psr7
```

### Configuration

Create `.rr.yaml`:

```yaml
version: "3"

server:
  command: "php bin/worker.php"
  relay: pipes

http:
  address: "0.0.0.0:8082"
  pool:
    num_workers: 4
    max_jobs: 1000
    allocate_timeout: 60s
    destroy_timeout: 60s

logs:
  mode: production
  level: info
  output: stdout

status:
  address: "0.0.0.0:2112"
```

### Worker Script

Create `bin/worker.php`:

```php
<?php

declare(strict_types=1);

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Application\AppInterface;
use MyVendor\MyProject\Injector;
use MyVendor\MyProject\Module\App;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker;

require dirname(__DIR__) . '/autoload.php';

// Get configuration from environment
$context = getenv('BEAR_CONTEXT') ?: 'prod-hal-app';
$maxRequests = (int) (getenv('MAX_REQUESTS') ?: 1000);

// Boot application once (outside the request loop)
$app = Injector::getInstance($context)->getInstance(AppInterface::class);
assert($app instanceof App);

// Create RoadRunner worker
$worker = Worker::create();
$factory = new Psr17Factory();
$psr7Worker = new PSR7Worker($worker, $factory, $factory, $factory);

$requestCount = 0;

while ($psrRequest = $psr7Worker->waitRequest()) {
    try {
        if (! $psrRequest instanceof ServerRequestInterface) {
            break;
        }

        // Convert PSR-7 request to $_SERVER format
        $server = createServerVars($psrRequest);
        $globals = createGlobals($psrRequest);

        // Route and execute request
        $request = $app->router->match($globals, $server);
        $response = $app->resource->{$request->method}->uri($request->path)($request->query);
        assert($response instanceof ResourceObject);

        // Convert ResourceObject to PSR-7 Response
        $psrResponse = $factory->createResponse($response->code);
        foreach ($response->headers as $name => $value) {
            $psrResponse = $psrResponse->withHeader($name, $value);
        }
        $psrResponse = $psrResponse->withBody($factory->createStream((string) $response));

        $psr7Worker->respond($psrResponse);
    } catch (Throwable $e) {
        $psr7Worker->respond($factory->createResponse(500)->withBody(
            $factory->createStream($e->getMessage())
        ));
    }

    gc_collect_cycles();
    $requestCount++;

    if ($maxRequests > 0 && $requestCount >= $maxRequests) {
        break;
    }
}

function createServerVars(ServerRequestInterface $request): array
{
    $uri = $request->getUri();
    $server = $request->getServerParams();

    $server['REQUEST_METHOD'] = $request->getMethod();
    $server['REQUEST_URI'] = $uri->getPath() . ($uri->getQuery() ? '?' . $uri->getQuery() : '');
    $server['QUERY_STRING'] = $uri->getQuery();
    $server['HTTP_HOST'] = $uri->getHost();

    foreach ($request->getHeaders() as $name => $values) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $server[$key] = implode(', ', $values);
    }

    return $server;
}

function createGlobals(ServerRequestInterface $request): array
{
    return [
        '_GET' => $request->getQueryParams(),
        '_POST' => (array) $request->getParsedBody(),
        '_COOKIE' => $request->getCookieParams(),
        '_FILES' => $request->getUploadedFiles(),
        '_SERVER' => createServerVars($request),
    ];
}
```

### Run

```bash
./rr serve -c .rr.yaml
```

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `BEAR_CONTEXT` | prod-hal-app | BEAR.Sunday context |
| `MAX_REQUESTS` | 1000 | Requests before worker restart |

### Architecture

```text
RoadRunner (Go)
    |
    +-- PHP Worker 1 (persistent)
    +-- PHP Worker 2 (persistent)
    +-- PHP Worker N (persistent)
```

Each worker boots BEAR.Sunday once and handles requests via pipes.

### Metrics

Prometheus metrics available at `http://localhost:2112/metrics`.

---

## FrankenPHP

[FrankenPHP](https://frankenphp.dev/) is a modern PHP application server built on Caddy with worker mode support.

### Features

- **Worker Mode**: Eliminates application boot cost per request
- **HTTP/2 & HTTP/3**: Automatic HTTPS with Caddy
- **Production Ready**: OPcache JIT, multi-stage builds
- **Development Ready**: Xdebug, hot reload

### Install

FrankenPHP is typically used via Docker. For standalone installation, see [FrankenPHP documentation](https://frankenphp.dev/docs/).

### Worker Script

Create `bin/worker.php`:

```php
<?php

declare(strict_types=1);

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Application\AppInterface;
use MyVendor\MyProject\Injector;
use MyVendor\MyProject\Module\App;

require dirname(__DIR__) . '/autoload.php';

// Get configuration from environment
$context = getenv('BEAR_CONTEXT') ?: 'prod-hal-app';
$maxRequests = (int) (getenv('MAX_REQUESTS') ?: 1000);

// Boot application once (outside the request loop)
$app = Injector::getInstance($context)->getInstance(AppInterface::class);
assert($app instanceof App);

$requestCount = 0;

// FrankenPHP worker loop
// Superglobals ($_GET, $_POST, $_SERVER) are automatically reset
do {
    $running = frankenphp_handle_request(static function () use ($app): void {
        try {
            // Check HTTP cache
            if ($app->httpCache->isNotModified($_SERVER)) {
                $app->httpCache->transfer();
                return;
            }

            // Route and execute request
            $request = $app->router->match($GLOBALS, $_SERVER);
            $response = $app->resource->{$request->method}->uri($request->path)($request->query);
            assert($response instanceof ResourceObject);
            $response->transfer($app->responder, $_SERVER);
        } catch (Throwable $e) {
            $app->throwableHandler->handle($e, $request ?? null)->transfer();
        }

        gc_collect_cycles();
    });

    $requestCount++;

    if ($maxRequests > 0 && $requestCount >= $maxRequests) {
        break;
    }
} while ($running);
```

### Caddyfile Configuration

```caddyfile
{
    admin off

    frankenphp {
        worker /app/bin/worker.php {
            num {$FRANKENPHP_NUM_WORKERS:4}
        }
    }
}

{$SERVER_NAME::8080} {
    root * /app/public

    encode zstd br gzip

    respond /health 200

    php_server

    log {
        output stdout
        format console
    }
}
```

### Run with Docker

```bash
docker run -v $PWD:/app -p 8080:8080 dunglas/frankenphp
```

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `BEAR_CONTEXT` | prod-hal-app | BEAR.Sunday context |
| `MAX_REQUESTS` | 1000 | Requests before worker restart |
| `SERVER_NAME` | :8080 | Listen address |
| `FRANKENPHP_NUM_WORKERS` | 4 | Number of worker processes |

### Memory Management

- Workers automatically restart after `MAX_REQUESTS` to prevent memory leaks
- `gc_collect_cycles()` runs after each request
- Set `MAX_REQUESTS=0` for unlimited requests (development only)

---

## Production Deployment

For production deployments, each server directory in [bear-sunday-servers](https://github.com/bearsunday/bear-sunday-servers) includes:

- `Dockerfile` - Optimized production build
- `docker-compose.prod.yml` - Production configuration
- Health check endpoints
- OPcache optimization

Example production deployment:

```bash
cd swoole  # or roadrunner, frankenphp
docker compose -f docker-compose.prod.yml up -d
```

## Benchmarking

See [BEAR.HelloworldBenchmark](https://github.com/bearsunday/BEAR.HelloworldBenchmark) for benchmark comparisons.

## Related

- [Parallel Resource Execution](async.html) - Parallel execution of `#[Embed]` resources with BEAR.Async

## References

- [Swoole](https://www.swoole.com/) - [Documentation](https://wiki.swoole.com/)
- [RoadRunner](https://roadrunner.dev/) - [Documentation](https://roadrunner.dev/docs)
- [FrankenPHP](https://frankenphp.dev/) - [Documentation](https://frankenphp.dev/docs/)
- [BEAR.Swoole](https://github.com/bearsunday/BEAR.Swoole)
- [bear-sunday-servers](https://github.com/bearsunday/bear-sunday-servers)
