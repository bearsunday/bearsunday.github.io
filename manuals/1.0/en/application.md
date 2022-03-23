---
layout: docs-en
title: Application
category: Manual
permalink: /manuals/1.0/en/application.html
---

# <a name="app"></a>Application

## Sequence

A BEAR.Sunday app has a run order of `compile`, `request` and `response`.

### 0. Compile

An `$app` application object is created through `DI` and `AOP` configuration depending on a specified context.
An `$app` is made up of service objects as it's properties that are needed to run the application such as a `router` or `transfer` etc.
`$app` then connects these object together depending on whether it is owned by another or contains other objects.
This is called an [Object Graph](http://en.wikipedia.org/wiki/Object_graph).
`$app` is then serialized and reused in each request and response.

* router - Converting external input to resource requests
* resource - Resource client
* transfer - Output

### 1. Request

An application resource request and resource object is created based on the HTTP request.

A resource object which has methods that respond to `onGet`, `onPost` etc upon request sets the `code` or `body` property of it's own resource state.

The resource object can then `#[Embed]` or `#[Link]` other resource objects.

Methods on the resource object are only for changing the resources state and have no interest in the representation itself (HTML, JSON etc).

Before and after the method, application logic bound to the method, such as logging and authentication, is executed in AOP.

### 2. Response

A `Renderer` is injected into the resource object, then the state of resource is represented as HTML, JSON etc or however it has been configured, it is then transfered to the client.

 <img src="/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>


## Boot File

To run an application, we need just two lines of code.
An entry point for a web server or console application access is usually set to `public/index.php` or `bin/app.php`.
As you can see below, we need to pass an application context to `bootstrap.php` the application script.


```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')('prod-html-app'));
```

Depending on your context choose a boot file.

```bash
// fire php server
php -S 127.0.0.1:8080 public/index.php
```

```
// console access
php bin/app.php get /user/1
```

## Context

The composition of the application object `$app` changes in response to the defined context, so that application behavior changes.

Depending on the defined context the building of the application object `$app` changes, altering the overall behavior.


For example, `WebRouter` is bound to `RouterInterface` by default.
However, if `Cli` mode is set (instead of HTTP) the `CliRouter` is bound to the `RouterInterface` and it will then take console input.

There are built-in and custom contexts that can be used in an application.

### Built-in Contexts

 * `api`  API Application
 * `cli`  Console Application
 * `hal`  HAL Application
 * `prod` Production

 * `app` is the default application context. It will be rendered in JSON.
 * `api` modifies page resources to an **app resource** by default. Also any web root access (`GET /`) that is usually mapped to `page://self/` will is re-mapped to `app://self/`.
 * `cli-app` represents a console application. 

You can also use a combination of these built-in contexts and add your own custom contexts.
If you set the context to `prod-hal-api-app` your application will run as an API application in production mode using the [HAL](http://stateless.co/hal_specification.html) media type.

### Custom Context

Place it in `src/Module`/ of the application; if it has the same name as the builtin context, the custom context will take precedence. You can override some of the constraints by calling the built-in context from the custom context.

Each application context (cli, app etc) represents a module.
For example the `cli` context relates to a `CliModule`, then binds all of the DI and AOP bindings that is needed for a console application.

### Context Agnostic

The values of each context will be only used when generating an object graph.
It is not recommended for your application code and libraries to change their behaviour based on the context.
Instead, the behavior should only change through **code that is dependent on an interface**[^dip] and **changes of dependencies by context**.

---

[^dip]: [Dependency inversion principle](https://en.wikipedia.org/wiki/Dependency_inversion_principle)
