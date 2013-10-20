---
layout: default
title: BEAR.Sunday | BEAR.Sunday Installation
category: Getting Started
subcategory: Installation
---

# Installation 

```
composer create-project bear/package {$PROJECT_PATH}
```

## Prerequisites 
 * PHP 5.4

## Optional 
 * [APC](http://php.net/manual/ja/book.apc.php)
 * [curl](http://php.net/manual/ja/book.curl.php)
 * Profiler　[xhprof](http://jp.php.net/manual/en/book.xhprof.php)
 * Graph Visualization [graphviz](http://www.graphviz.org/)

## Environment Check 
```
$ php bin/env.php
```

BEAR.Sunday application can be accessed via the web or CLI.

### Helloworld application
```
cd {$PROJECT_PATH}/apps/Helloworld/var/www/
php -S 0.0.0.0:8080 index.php
```

### Sandbox application
```
cd {$PROJECT_PATH}/apps/Sandbox/var/www/
php -S 0.0.0.0:8080 dev.php
```

Please see https://github.com/koriym/BEAR.package#buil-in-web-server-for-development.

