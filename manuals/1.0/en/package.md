---
layout: docs-en
title: Package
category: Manual
permalink: /manuals/1.0/en/package.html
---

# Package

BEAR.Sunday application is a composer package taking BEAR.Sunday framework as dependency package.
You can also install another BEAR.Sunday application package as dependency.

## Application organization

The file layout of the BEAR.Sunday application conforms to [php-pds/skeleton] (https://github.com/php-pds/skeleton).

```
├── (bin)
├── bootstrap
│   ├── api.php
│   ├── bootstrap.php
│   └── web.php
├── composer.json
├── composer.lock
├── phpunit.xml.dist
├── public
│   └── index.php
├── src
│   ├── (Annotation)
│   ├── (Interceptor)
│   ├── Module
│   └── Resource
├── tests
│   ├── (Fake)
│   ├── bootstrap.php
│   └── tmp
├── var
│   ├── (conf)
│   ├── log
│   └── tmp
└── vendor

```

### Invoke sequence

 1. Console input or web router file call `boot file` such as `api.php` or `web.php`.
 3. `$app` application object is created by `$context` in `boostrap.php`.
 4. A router in `$app` convert external resource request to internal resource request.
 4. A resource request is invoked. The representation of the result transfered to a client.


### bootstrap/

You can access same resource through console input or web access with same boot file.

```bash
php bootstrap/api.php options '/self/todo' // console API access
```

```bash
php bootstrap/web.php get '/todo?id=1' // console Web access
```

```bash
php -S 127.0.0.1bootstrap/api.php // PHP server
```

You can create your own boot file for different context.

### bin/

Plavce command-line executable files.

### src/

Place application class file.

### publc/

Web public folder.

### var/

`log` and `tmp` folder need write permission.

## Framework Package


## bear/sunday
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Sunday.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Sunday?branch=1.x)

An interface package for BEAR.Sunday framework.

## bear/package
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Package.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Package)

A basic implmentation package for `bear/sunday`.

## bear/resource
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Resource.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Resource)

A REST framework for PHP object as a service.

## ray/di
 [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/)
 [![Code Coverage](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/)
 [![Build Status](https://secure.travis-ci.org/ray-di/Ray.Di.png?b=2.x)](http://travis-ci.org/ray-di/Ray.Di)

A Google Guice style DI framework.

## ray/aop
 [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/)
 [![Code Coverage](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/)
 [![Build Status](https://secure.travis-ci.org/ray-di/Ray.Aop.png?b=2.x)](http://travis-ci.org/ray-di/Ray.Aop)

An aspect oriented framework based on Java AOP Alliance API.

## bear/middleware
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Middleware/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Middleware/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Middleware/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Middleware/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Middleware.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Middleware)

An optional PSR-7 middleware package.

## Library Package

Optional library package can be installed with `composer require` command.

| **Category** | **Composer package** | **Library**
| Router |
| |[bear/aura-router-module](https://github.com/bearsunday/BEAR.AuraRouterModule) | [Aura.Router v2](https://github.com/auraphp/Aura.Router/tree/2.x) |
| Database |
|| [ray/aura-sql-module](https://github.com/ray-di/Ray.AuraSqlModule) | [Aura.Sql v2](https://github.com/auraphp/Aura.Sql/tree/2.x)
|| [ray/dbal-module](https://github.com/ray-di/Ray.DbalModule) | [Doctrine DBAL](https://github.com/doctrine/dbal)
|| [ray/cake-database-module](https://github.com/ray-di/Ray.CakeDbModule) | [CakePHP v3 database](https://github.com/cakephp/database)
|| [ray/doctrine-orm-module](https://github.com/kawanamiyuu/Ray.DoctrineOrmModule) | [Doctrine ORM](https://github.com/doctrine/doctrine2)
| Storage |
||[bear/query-repository](https://github.com/bearsunday/BEAR.QueryRepository) | CQRS inspired repository
||[bear/query-module](https://github.com/ray-di/Ray.QueryModule) | Separation of external access such as DB or Web API
| Web
| |[madapaja/twig-module](http://bearsunday.github.io/manuals/1.0/ja/html.html) | [Twig](http://twig.sensiolabs.org/)
| |[ray/web-form-module](http://bearsunday.github.io/manuals/1.0/ja/form.html) | Web form
| |[ray/aura-web-module](https://github.com/Ray-Di/Ray.AuraWebModule) | [Aura.Web](https://github.com/auraphp/Aura.Web)
| |[ray/aura-session-module](https://github.com/ray-di/Ray.AuraSessionModule) | [Aura.Session](https://github.com/auraphp/Aura.Session)
| |[ray/symfony-session-module](https://github.com/kawanamiyuu/Ray.SymfonySessionModule) | [Symfony Session](https://github.com/symfony/http-foundation/tree/master/Session)
| Validation |
| |[ray/validate-module](https://github.com/ray-di/Ray.ValidateModule) | [Aura.Filter](https://github.com/auraphp/Aura.Filter)
| |[satomif/extra-aura-filter-module](https://github.com/satomif/ExtraAuraFilterModule)| [Aura.Filter](https://github.com/auraphp/Aura.Filter)
| Authorization and Authentication
| |[ray/oauth-module](https://github.com/Ray-Di/Ray.OAuthModule) | OAuth
| |[kuma-guy/jwt-auth-module](https://github.com/kuma-guy/BEAR.JwtAuthModule) | JSON Web Token
| |[ray/role-module](https://github.com/ray-di/Ray.RoleModule) | Zend Acl
| |[bear/acl-resource](https://github.com/bearsunday/BEAR.AclResource) | ACL based embedded resource
| Hypermedia
| |[kuma-guy/siren-module](https://github.com/kuma-guy/BEAR.SirenModule) | Siren
|  Development
| |[ray/test-double](https://github.com/ray-di/Ray.TestDouble) | Test Double

## Semver

All package adhere to [Semantic Versioning](http://semver.org/).
