---
layout: default
title: BEAR.Sunday | Overview
category: Manual
---

# BEAR.Package

## What is BEAR.Package ?

BEAR.Sunday is a collection of ...

BEAR.Package is a collection of ...

TBD

## Package Organization

The framework package directory structure looks like this:

```
{$PACKAGE_DIR}
├── apps                 # applications
│   ├── Helloworld
│   └── Sandbox
├── bin                  # command-line script invokers
│   ├── env.php
│   └── new_res.php
├── src                  # BEAR.Package source code organized for PSR-0
│   └── BEAR
│       └── Package
├── tests                # test files for phpunit
├── var
│   ├── lib              # system script
│   ├── log              # system log
│   └── www
│       ├── admin        # public web folder for system
│       └── dev          # public web folder for application development (/dev)
└── vendor
```

## Application Organization

The application directory structure looks like this:

```
├── bin                  # command-line script invokers
│   ├── api.php
│   ├── clear.php
│   ├── compiler.php
│   └── web.php
├── bootstrap
│   ├── autoload.php     # autoloader
│   ├── contexts         # contextual application scripts
│   │   ├── api.php
│   │   ├── dev.php
│   │   └── prod.php
│   ├── develop
│   │   └── instance.php # application script for development
│   └── instance.php     # application script for production
├── src
│   └── {Skeleton}
│       ├── Annotation   # application annotation
│       ├── App.php      # application class
│       ├── Interceptor  # AOP interceptors
│       ├── Module       # DI binding modules
│       └── Params       # signal parameter providers
├── var                  # application variable directories
│   ├── db               # application data base file
│   ├── lib              # vendor(packagist) related files
│   ├── log              # application log directory
│   ├── tmp              # application tmp files
│   └── www              # public web folder
└── vendor
```

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