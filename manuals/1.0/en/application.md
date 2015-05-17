---
layout: docs-en
title: Application
category: Manual
permalink: /manuals/1.0/en/application.html
---

A BEAR.Sunday application transfers the state of a resource that can be represented (REST)
using a script found in `bootstrap/bootstrap.php`.

A BEAR.Sunday app has a run order of `compile`, `request` and `response`.

### 0. Compile

An `$app` application object is created through `DI` and `AOP` settings depending on the `$context` configuration.
`$app` is made up of service objects as it's properties that are needed to run the application such as `router` or `transfer` etc.
`$app` is then connects these object together depending on whether it is owned by another or contains other objects.
This is called an [Object Graph](http://en.wikipedia.org/wiki/Object_graph).
`$app` is then serialized and reused for each request and response.


リソースオブジェクトはリクエストに対応する`onGet`や`onPost`などのメソッドで自身のリソース状態を`code`や`body`にセットします。
リソースオブジェクトは他のリソースオブジェクトを`@Embed`したり`@Link`することができます。メソッド内ではリソース状態の変更をするだけでその表現（HTMLやJSONなど）に関心を持つことはありません。

### 1. Request

An application resource request and and resource object is created based on the HTTP request.

A resource object which has methods that respond to `onGet`, `onPost` etc upon request sets the `code` or `body` property of it's own resource state.

The resource object can then `@Embed` or `@Link` other resource objects. 

Methods on the resource object are only for changing the resources state and have no interest in the representation (HTML, JSON etc).


### 2. Response

A `Renderer` is injected into the resource object, then the state of resource is represented as HTML, JSON etc or however it has been configured, it is then transfered to the client.

 <img src="/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>


# Boot File

To run an application, we need just two lines of code.
An entry point for a web server or console application access is usually set to `var/www/index.php` or `bootstrap/api.php`.
As you can see below, we need to assign an application context to a global variable `$context` then require `bootstrap.php` to run the application.


{% highlight php %}
<?php
$context = 'prod-api-hal-app'
require 'pat/to/bootstrap.php';
{% endhighlight %}

Depending on your context choose a boot file.

{% highlight bash %}
// Fire up built in php server
php -S 127.0.0.1:8080 var/www/index.php

// Console access
php bootstrap/api.php get /user/1

// Web access for the api
php -S 127.0.0.1:8080 bootstrap/api.php
{% endhighlight %}

## Application Context

The composition of application object `$app` changes in response to your defined context, so that application behavior changes.
For example, `WebRouter` is bound to `RouterInterface` by default settings.
However, if you set `Cli`, which is defined for console application, as your context, 
then `CliRouter` is bound to `RouterInterface` and it will take console input instead.

There are built-in and custom context's created by application.

**Built-in contexts**

 * `api`  API Application
 * `cli`  Console Application
 * `hal`  HAL Application
 * `prod` Production

 You can also use a combination of the built-in context and your own custom contexts.

 * `app` is the default application context.
 * `api` modifies page resources to an **app resource** by default. Also any web root access (`GET /`) that is usually mapped to `page://self/` will is re-mapped to `app://self/`.
 * `cli-app` represents a console application. If you set the context to `prod-hal-api-app` your application will run as an API application in production mode using the [HAL](http://stateless.co/hal_specification.html) media type.


Each application context (cli, app etc) corresponds to a module.
For example the `cli` context relates to `CliModule`, then binds all of the DI and AOP bindings that is needed for a console application.

The value of context wil be only used when generating an object graph.
It is not recommend for your application code and library to change its behavior by referring to the context.
Instead, it should be changed by **the code depend on interface** and **the changes of dependency by the context**.
