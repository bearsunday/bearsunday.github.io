---
layout: default
title: BEAR.Sunday | Installation
category: Manual
---

# Installation 

```
$ composer create-project bear/package {$PROJECT_PATH}
```

## Prerequisites 
 * PHP 5.4+

## Optional 
 * [APC](http://php.net/manual/ja/book.apc.php)
 * [APCu](http://pecl.php.net/package/APCu) (PHP5.5+)
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
$ cd {$PROJECT_PATH}/apps/Helloworld/var/www/
$ php -S 0.0.0.0:8080 index.php
```

### Sandbox application
```
cd {$PROJECT_PATH}/apps/Sandbox/var/www/
php -S 0.0.0.0:8080 dev.php
```

Please see https://github.com/koriym/BEAR.package#buil-in-web-server-for-development.


## Create New Application

```
$ composer create-project bear/skeleton {$PACKAGE_DIR}/apps/{$APPLICATION_NAME} {$SKELETON_VERSION}
```

## Create New Resource

```
$ php {$PACKAGE_DIR}/bin/new_res.php {$APP_NAME} {$NEW_RESOURCE_URI}
```
## Try it out

Let's create new application 'Hello' and see how does it works.

```
// create BEAR.Package framework files
$ composer create-project bear/package ./bear
$ cd bear/apps
// create 'Hello' application files
$ composer create-project bear/skeleton Hello
$ cd Hello/var/www/
// run built-in web server
$ php -S 0.0.0.0:8088 dev.php
```

Browse `http://0.0.0.0:8088` URL and see the message from BEAR.Sunday.