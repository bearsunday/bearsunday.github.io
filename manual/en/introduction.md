---
layout: default
title: BEAR.Sunday | Introduction
category: Manual
---

# Introduction

## What is BEAR.Sunday ?

This resource orientated framework has both externally and internally a REST centric architecture, implementing **Dependency Injection** and **Aspect Orientated Programming** heavily to offer you surprising simplicity, order and flexibility in your application.

With very few components of its own, it is a fantastic example of how a framework can be built using existing components and libraries from other frameworks, yet offer even further benefit and beauty.

## Resource Orientated Framework

In BEAR.Sunday everything is a REST resource which leads to far simpler design and extensibility. Interactions with your database, services and even pages and sections of your app all sit comfortably in a resource which can be consumed or rendered at will.

### Everything is a resource

<img src="/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>

The above diagram illustrates how resource object works.

TBD.

# Packages

BEAR.Sunday application framework consists packages of three type. **Independent packages** which are available in [Packagist](https://packagist.org/)
. **Ray packages** are DI and AOP object framework. **BEAR.* packages** organize as a application framework with former two packages.

## BEAR.* packages

### BEAR.Resource ⊂ BEAR.Sunday ⊂ BEAR.Package

[BEAR.Resource](https://github.com/koriym/BEAR.Sunday) is a **hypermedia framework** that allows resources to behave as objects.
It allows objects to have RESTful web service benefits such as client-server, uniform interface, statelessness, resource expression with mutual connectivity and layered components.

[BEAR.Sunday](https://github.com/koriym/BEAR.Sunday) is a collections of abstractions to form BEAR.Resource as an application framework.
Such as annotations, exceptions or interfaces. It has almost no actual implementations.

[BEAR.Package](https://github.com/koriym/BEAR.Package) supply bindings for BEAR.Sunday abstractions to actual implementations, such as Aura libraries or Symfony components.
It organizes a web application framework with application scripts and development tools.

## Ray DI/AOP packages

All dependencies for application and framework are resolved  [Ray.Di](https://github.com/koriym/Ray.Di) framework.
Ray.Di is google guice clone. It creates one root object in bootstrap.

[Ray.Aop](https://github.com/koriym/Ray.Aop) framework enable to separate domain logic and application logic, increased testability and reusability

Once all dependencies are injected and also all cross cutting concerns are bound to specified method **by context**, Its object graph are cached and re-used beyond request.

## Framework Organization

The framework package (BEAR.Package) directory structure looks like this:

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
