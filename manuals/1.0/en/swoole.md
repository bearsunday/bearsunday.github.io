---
layout: docs-en
title: Swoole
category: Manual
permalink: /manuals/1.0/en/swoole.html
---

# Swoole

You can execute your BEAR.Sunday application using Swoole directly from the command line. It dramatically improves performance.

## Install

### Swoole Install

See [https://github.com/swoole/swoole-src#%EF%B8%8F-installation](https://github.com/swoole/swoole-src#%EF%B8%8F-installation)

### BEAR.Swoole Install

```bash
composer require bear/swoole ^0.4
```
Place the bootstrap script at `bin/swoole.php`

```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/vendor/bear/swoole/bootstrap.php')(
    'prod-hal-app',       // context
    'MyVendor\MyProject', // application name
    '127.0.0.1',          // IP
    8080                  // port
));
```

## Excute

```
php tests/bin/swoole.php
```
```
Swoole http server is started at http://127.0.0.1:8088
```

## Benchmarking site

See [BEAR.HelloworldBenchmark](https://github.com/bearsunday/BEAR.HelloworldBenchmark)
You can expect x2 to x10 times bootstrap performance boost.

 * [The benchmarking result](https://github.com/bearsunday/BEAR.HelloworldBenchmark/wiki)

[<img src="https://github.com/swoole/swoole-src/raw/master/mascot.png">](https://github.com/swoole/swoole-src)
