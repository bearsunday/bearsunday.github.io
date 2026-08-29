---
layout: docs-en
title: PhpStorm + Xdebug
category: Manual
permalink: /manuals/1.0/en/phpstorm-xdebug.html
---

# PhpStorm + Xdebug

This guide shows how to debug a BEAR.Sunday application created from BEAR.Skeleton with Docker, PhpStorm, and Xdebug.

The Docker side provides PHP and Xdebug. PhpStorm still needs one-time IDE settings: a Docker Compose interpreter, a PHP server name, path mapping, and a PHP Script debug configuration.

## Required values

| Setting | Value |
| ------- | ----- |
| Debug port | `9003` |
| Server name | `BEAR.Skeleton` |
| Server path | `/app` |
| Path mapping | project root → `/app` |
| `PHP_IDE_CONFIG` | `serverName=BEAR.Skeleton` |
| Xdebug client host | `host.docker.internal` |

The server name is important. It must be the same in Docker and PhpStorm.

## Start Docker

Start the application container:

```bash
docker compose up -d
```

Check normal CLI execution before debugging:

```bash
docker compose exec -T app php bin/page.php get /
```

Expected output:

```text
200 OK
Content-Type: application/hal+json
```

## Configure the Docker Compose interpreter

Open **Settings | PHP | CLI Interpreters** and add a Docker Compose interpreter.

Use the project `compose.yaml` and the `app` service.

<img src="/images/screen/phpstorm-xdebug/docker-compose-interpreter.svg" alt="Docker Compose interpreter screenshot" style="max-width: 100%; height: auto;" />

## Configure the PHP server

Open **Settings | PHP | Servers** and add a server.

Use these values:

- Name: `BEAR.Skeleton`
- Host: `localhost`
- Port: `8080`
- Debugger: `Xdebug`
- Use path mappings: enabled
- Project root on the host: your local project directory
- Absolute path on the server: `/app`

<img src="/images/screen/phpstorm-xdebug/php-server.svg" alt="PHP server path mapping screenshot" style="max-width: 100%; height: auto;" />

## Create a PHP Script debug configuration

Create a **PHP Script** run configuration.

Use these values:

- Name: `page get /`
- File: `bin/page.php`
- Arguments: `get /`
- Interpreter: the Docker Compose interpreter
- Server: `BEAR.Skeleton`

<img src="/images/screen/phpstorm-xdebug/run-configuration.svg" alt="PHP Script debug configuration screenshot" style="max-width: 100%; height: auto;" />

## Debug with the bug button

Set a breakpoint in `bin/page.php`, then click the bug button for the `page get /` configuration.

A successful session stops at the breakpoint.

<img src="/images/screen/phpstorm-xdebug/breakpoint.svg" alt="PhpStorm breakpoint screenshot" style="max-width: 100%; height: auto;" />

Resume execution. The console should finish with exit code `0`.

<img src="/images/screen/phpstorm-xdebug/console-success.svg" alt="PhpStorm debug console success screenshot" style="max-width: 100%; height: auto;" />

## Xdebug mode policy

Do not fix `XDEBUG_MODE=off` in `Dockerfile` or `compose.yaml`.

Use this default container-side policy:

```ini
xdebug.mode=develop
xdebug.start_with_request=trigger
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
```

With this setup, normal execution does not start a debug session. When you click PhpStorm's bug button, PhpStorm starts the script with options such as:

```text
-dxdebug.mode=debug -dxdebug.client_port=9003 -dxdebug.client_host=host.docker.internal
```

This keeps normal CLI execution light while allowing the IDE to enable debugging for the current session.

## Troubleshooting

### PhpStorm says `xdebug.remote_host` may be wrong

This warning can be misleading. With Xdebug 3, first check these items:

- `XDEBUG_MODE=off` is not fixed in Docker.
- The PhpStorm server name is exactly `BEAR.Skeleton`.
- `PHP_IDE_CONFIG=serverName=BEAR.Skeleton` is set in Docker.
- The project root is mapped to `/app`.
- PhpStorm is listening on port `9003`.

### The breakpoint is not hit

Run this command and check that the application works without the debugger:

```bash
docker compose exec -T app php bin/page.php get /
```

Then check the debug command shown in PhpStorm's console. It should include `-dxdebug.mode=debug`.

### Normal execution is slow

Xdebug affects performance when debug mode is active. Keep the container default at `xdebug.mode=develop` and let PhpStorm enable `debug` only for a debug session.
