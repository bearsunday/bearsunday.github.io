---
layout: docs-en
title: Package
category: Manual
permalink: /manuals/1.0/en/package.html
---

*[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/psr7.md) needs to be proofread by an English speaker. If interested please send me a pull request. Thank you.*

# Package

BEAR.Sunday application is an composer package taking BEAR.Sunday framework as dependency package.
You can also install another BEAR.Sunday application package as dependency.

## Application organization


```
├── bootstrap
│   ├── api.php
│   ├── bootstrap.php
│   └── web.php
├── composer.json
├── composer.lock
├── phpunit.xml.dist
├── src
│   ├── (Annotation)
│   ├── (Interceptor)
│   ├── Module
│   └── Resource
├── tests
│   ├── bootstrap.php
│   └── tmp
├── var
│   ├── (conf)
│   ├── log
│   ├── tmp
│   └── www
└── vendor

```

### Invoke sequence

 1. Console input or web router file call `boot file` such as `api.php` or `web.php`.
 3. `$app` application object is created by `$context` in `boostrap.php`.
 4. A rounter in `$app` convert external resource request to internal resource request.
 4. A resource request is invoked. The representation of the result transfered to a client.


### bootstrap/

You can access same resource through console input or web access with same boot file.

{% highlight php %}
php bootstrap/api.php options '/self/todo' // console API access
{% endhighlight %}

{% highlight php %}
php bootstrap/web.php get '/todo?id=1' // console Web access
{% endhighlight %}

{% highlight php %}
php -S 127.0.0.1bootstrap/api.php // PHP sercer
{% endhighlight %}

You can create your own boot file for different context.

### src/

Place application class file.

### var/

`log` and `tmp` folder need write permission.
`var/www`is web public folder.

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

An optional PSR7 middleware package.

## Library Package



### Router

 *  [bear/aura-router-module](https://github.com/bearsunday/BEAR.AuraRouterModule) Aura.Router v2

### Database

 * [ray/aura-sql-module](https://github.com/ray-di/Ray.AuraSqlModule) Aura.Sql v2
 * [ray/dbal-module](https://github.com/ray-di/Ray.DbalModule) Doctrine DBAL
 * [ray/cake-database-module](https://github.com/ray-di/Ray.CakeDbModule) CakePHP v3 database

### Storage

 * [bear/query-repository](https://github.com/bearsunday/BEAR.QueryRepository) r/w repository separation

### Web

 * [madapaja/twig-module](http://bearsunday.github.io/manuals/1.0/ja/html.html) Twig
 * [ray/web-form-module](http://bearsunday.github.io/manuals/1.0/ja/form.html) Web form and validation
 * [ray/aura-web-module](https://github.com/Ray-Di/Ray.AuraWebModule) Aura.Web

### Authorization and Authentication

 * [ray/oauth-module](https://github.com/Ray-Di/Ray.OAuthModule) OAuth
 * [ray/role-module](https://github.com/ray-di/Ray.RoleModule) Zend Acl

### Hypermedia

 * [kuma-guy/siren-module](https://github.com/kuma-guy/BEAR.SirenModule) Siren

### 開発

 * [ray/fake-module](https://github.com/shingo-kumagai/Ray.FakeModule) Mocking

## Semver

All package adhere to [Semantic Versioning](http://semver.org/).
