---
layout: docs-en
title: Redis DSN
category: Manual
permalink: /manuals/1.0/en/redis-dsn.html
---
# Redis DSN

`StorageRedisDsnModule`, which stores resource caches in Redis, takes the Redis connection as a DSN string. This page is the reference for that DSN format and its connection options.

```php
use BEAR\QueryRepository\StorageRedisDsnModule;

$this->install(new StorageRedisDsnModule(
    dsn: 'redis://localhost:6379',
    options: ['timeout' => 5],
));
```

`dsn` and `options` are passed as-is to Symfony's `RedisAdapter::createConnection()`, so every DSN format and option Symfony supports is available. For the overall cache setup, see [Cache](cache.html).

Note: The rest of this page extracts and adapts only the DSN configuration part of the [symfony/cache documentation](https://symfony.com/doc/current/components/cache/adapters/redis_adapter.html#configure-the-connection).

> Note:
> Requirements: Besides a Redis server, you need an extension or library that implements `\Redis`, `\RedisArray`, `RedisCluster`, `\Relay\Relay`, `\Relay\Cluster`, or `\Predis`.

## Configure the Connection

Create and configure the Redis client class instance using a Data Source Name (DSN):

A DSN can specify either an IP/host (and an optional port) or a socket path, as well as a password and a database index. To enable TLS for connections, the scheme `redis` must be replaced by `rediss` (the second `s` means "secure").

> Note:
> A Data Source Name (DSN) for this adapter must use one of the following formats.
>
> ```
> redis[s]://[pass@][ip|host|socket[:port]][/db-index]
> ```
>
> ```
> redis[s]:[[user]:pass@]?[ip|host|socket[:port]][&params]
> ```
>
> Values for the placeholders `[user]`, `[:port]`, `[/db-index]`, and `[&params]` are optional.

Below are common examples of valid DSNs showing a combination of available values:

```php
use Symfony\Component\Cache\Adapter\RedisAdapter;

// host "my.server.com" and port "6379"
RedisAdapter::createConnection('redis://my.server.com:6379');

// host "my.server.com", port "6379", and database index "20"
RedisAdapter::createConnection('redis://my.server.com:6379/20');

// host "localhost", auth "abcdef", and a timeout of 5 seconds
RedisAdapter::createConnection('redis://abcdef@localhost?timeout=5');

// socket "/var/run/redis.sock" and auth "bad-pass"
RedisAdapter::createConnection('redis://bad-pass@/var/run/redis.sock');

// host "redis1" (Docker container), using the alternate DSN syntax and selecting database index "3"
RedisAdapter::createConnection('redis:?host[redis1:6379]&dbindex=3');

// providing credentials with the alternate DSN syntax
RedisAdapter::createConnection('redis:default:verysecurepassword@?host[redis1:6379]&dbindex=3');

// a single DSN can also define multiple servers
RedisAdapter::createConnection('redis:?host[localhost]&host[localhost:6379]&host[/var/run/redis.sock:]&auth=my-password&redis_cluster=1');
```

Redis Sentinel, which provides high availability for Redis, is supported when using the PHP Redis extension v5.2+ or the Predis library. Use the `redis_sentinel` parameter to set the name of your service group:

```php
RedisAdapter::createConnection('redis:?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&redis_sentinel=mymaster');

// providing credentials
RedisAdapter::createConnection('redis:default:verysecurepassword@?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&redis_sentinel=mymaster');

// providing credentials and selecting database index "3"
RedisAdapter::createConnection('redis:default:verysecurepassword@?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&redis_sentinel=mymaster&dbindex=3');
```

> Note:
> See `Symfony\Component\Cache\Traits\RedisTrait` for more options you can pass as DSN parameters.


### Available Options

`class` (type: `string`, default: `null`)
Specifies the connection library to return, either `\Redis`, `\Relay\Relay`, or `\Predis\Client`.
If none is specified, the fallback value is used in the following order, depending on which one is available first:
`\Redis`, `\Relay\Relay`, `\Predis\Client`. Explicitly set this to `\Predis\Client` for Sentinel if you run into issues when retrieving master information.

`persistent` (type: `int`, default: `0`)
Enables or disables the use of persistent connections. A value of `0` disables persistent connections, and a value of `1` enables them.

`persistent_id` (type: `string|null`, default: `null`)
Specifies the persistent id string to use for a persistent connection.

`timeout` (type: `int`, default: `30`)
Specifies the time (in seconds) used to connect to a Redis server before the connection attempt times out.

`read_timeout` (type: `int`, default: `0`)
Specifies the time (in seconds) used when performing read operations on the underlying network resource before the operation times out.

`retry_interval` (type: `int`, default: `0`)
Specifies the delay (in milliseconds) between reconnection attempts in case the client loses connection with the server.

`tcp_keepalive` (type: `int`, default: `0`)
Specifies the TCP keepalive timeout (in seconds) of the connection. This requires phpredis v4 or higher and a TCP-keepalive enabled server.

`lazy` (type: `bool`, default: `null`)
Enables or disables lazy connections to the backend. It is `false` by default when used as a stand-alone component, and `true` by default when used inside a Symfony application.

`redis_cluster` (type: `bool`, default: `false`)
Enables or disables Redis cluster. The actual value passed is irrelevant as long as it passes loose comparison checks: `redis_cluster=1` will suffice.

`redis_sentinel` (type: `string`, default: `null`)
Specifies the master name connected to the sentinels.

`sentinel_master` (type: `string`, default: `null`)
An alias of the `redis_sentinel` option.

`dbindex` (type: `int`, default: `0`)
Specifies the database index to select.

`failover` (type: `string`, default: `none`)
Specifies failover for cluster implementations. For `\RedisCluster`, valid options are `none` (default), `error`, `distribute`, or `slaves`. For `\Predis\ClientInterface`, valid options are `slaves` or `distribute`.

`ssl` (type: `array`, default: `null`)
SSL context options. See `php.net/context.ssl` for more information.

> Added in version 7.1:
> The `sentinel_master` option as an alias for `redis_sentinel` was introduced in Symfony 7.1.

> Note:
> When using the `Predis` library, some additional Predis-specific options are available. See the `Predis Connection Parameters` documentation for more information.

## Configuring Redis

When using Redis as a cache, you should configure the `maxmemory` and `maxmemory-policy` settings. By setting `maxmemory`, you limit how much memory Redis is allowed to consume. If the amount is too low, Redis will drop entries that would still be useful and you will benefit less from your cache. Setting `maxmemory-policy` to `allkeys-lru` tells Redis that it is OK to drop data when it runs out of memory, and to drop the oldest entries (least recently used) first. If you do not allow Redis to drop entries, it will return an error when you try to add data when no memory is available. An example setting could look as follows:

```ini
maxmemory 100mb
maxmemory-policy allkeys-lru
```
