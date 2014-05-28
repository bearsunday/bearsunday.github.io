---
layout: default_ja
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

### Sandbox application
```
$ bin/bear.server apps/Demo.Sandbox
```

Please see https://github.com/koriym/BEAR.package#buil-in-web-server-for-development.


## Create New Application

```
$ composer create-project bear/skeleton {$PACKAGE_DIR}/apps/{$Vendor.Application}
$ composer create-project bear/skeleton {$PACKAGE_DIR}/apps/{$Vendor.Application} {$SKELETON_VERSION}
```

## Create New Resource

```
$ bin/bear.create-resource apps/Demo.{$Vendor.Application} {$URI}
$ bin/bear.create-resource apps/Demo.Sandbox/ page://self/greeting
```
## Try it out

Let's create new application 'My.Hello' and see how does it works.

```
// create BEAR.Package framework files
$ composer create-project bear/package ./bear
$ cd bear/apps

// create 'My.Hello' application files
$ composer create-project bear/skeleton My.Hello
$ cd ..

// run built-in web server
$ bin/bear.server apps/My.Hello
```

Browse `http://0.0.0.0:8080` URL and see the message from BEAR.Sunday.