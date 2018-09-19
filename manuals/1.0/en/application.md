---
layout: docs-en
title: Application
category: Manual
permalink: /manuals/1.0/en/application.html
---

# <a name="app"></a>Application

A BEAR.Sunday application transfers the state of a resource that can be represented (REST)
using a script found in `bootstrap/bootstrap.php`.

A BEAR.Sunday app has a run order of `compile`, `request` and `response`.

## 0. Compile

An `$app` application object is created through `DI` and `AOP` configuration depending on a specified `$context`.
An `$app` is made up of service objects as it's properties that are needed to run the application such as a `router` or `transfer` etc.
`$app` then connects these object together depending on whether it is owned by another or contains other objects.
This is called an [Object Graph](http://en.wikipedia.org/wiki/Object_graph).
`$app` is then serialized and reused in each request and response.

## 1. Request

An application resource request and resource object is created based on the HTTP request.

A resource object which has methods that respond to `onGet`, `onPost` etc upon request sets the `code` or `body` property of it's own resource state.

The resource object can then `@Embed` or `@Link` other resource objects.

Methods on the resource object are only for changing the resources state and have no interest in the representation itself (HTML, JSON etc).

## 2. Response

A `Renderer` is injected into the resource object, then the state of resource is represented as HTML, JSON etc or however it has been configured, it is then transfered to the client.

 <img src="/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>


# <a name="boot"></a>Boot File

To run an application, we need just two lines of code.
An entry point for a web server or console application access is usually set to `var/www/index.php` or `bin/app.php`.
As you can see below, we need to assign an application context to a global variable `$context` then require `bootstrap.php` to run the application.


```php?start_inline
$context = 'prod-api-hal-app'
require 'pat/to/bootstrap.php';
```

Depending on your context choose a boot file.

```bash
// Fire up built in php server
php -S 127.0.0.1:8080 var/www/index.php

// Console access
php bin/app.php get /user/1

// Web access for the api
php -S 127.0.0.1:8080 bin/app.php
```

## <a name="context"></a>Application Context

The composition of the application object `$app` changes in response to the defined context, so that application behavior changes.

Depending on the defined context the building of the application object `$app` changes, altering the overall behavior.


For example, `WebRouter` is bound to `RouterInterface` by default.
However, if `Cli` mode is set (instead of HTTP) the `CliRouter` is bound to the `RouterInterface` and it will then take console input.

There are built-in and custom contexts that can be used in an application.

**Built-in contexts**

 * `api`  API Application
 * `cli`  Console Application
 * `hal`  HAL Application
 * `prod` Production

 You can also use a combination of these built-in contexts and add your own custom contexts.

 * `app` is the default application context. It will be rendered in JSON.
 * `api` modifies page resources to an **app resource** by default. Also any web root access (`GET /`) that is usually mapped to `page://self/` will is re-mapped to `app://self/`.
 * `cli-app` represents a console application. If you set the context to `prod-hal-api-app` your application will run as an API application in production mode using the [HAL](http://stateless.co/hal_specification.html) media type.


Each application context (cli, app etc) represents a module.
For example the `cli` context relates to a `CliModule`, then binds all of the DI and AOP bindings that is needed for a console application.

The values of each context will be only used when generating an object graph.
It is not recommended for your application code and libraries to change their behaviour based on the context.
Instead, the behavior should only change through **code that is dependent on an interface** and **changes of dependencies by context**.
