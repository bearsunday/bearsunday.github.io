---
layout: docs-en
title: Application
category: Manual
permalink: /manuals/1.0/en/application.html
---

 * *[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/application.md) needs to be proofread by an English speaker. If interested please send me a pull request. Thank you.*

BEAR.Sunday application transfer (REST) the state of resource that is capable of representation
using script `bootstrap/bootstrap.php`.

### 0. Compile

An application Object `$app`, which is settings of DI and AOP, is created based on `$context`.
`$app` consists of service objects such as `router` and `transfer` as property to run an application.
`$app` are linked to these objects either owned by others or containing them so that it is capable of communicating with each other.
This is called as [Object Graph](http://en.wikipedia.org/wiki/Object_graph).
Also, `$app` is serialized and reused.

### 1. Request

The resource request and object of application are created based on HTTP request.
The resource object is a method such as `onGet` and `onPost` that corresponds to a request and set its own state of the resource to `code` and `body`.
Also, it is used to embed `@Embed` or to link `@Link` other resource objects. 
As a method, it just handle the state of resource or modify it and not to involved in the representation of resource such as HTML and JSON. 


### 2. Response

A Renderer, which is injected into resource object, make the state of resource into representation such as HTML and JSON and transfer to a client.

 <img src="/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>


# boot file

To run an application, we need just two line of codes.
It is usually set to `var/www/index.php` or `bootstrap/api.php` as entry point for web server and console application.
As shown below, we need to set an application context to a global variable `$context` and require `bootstrap.php` to run an application.


{% highlight php %}
<?php
$context = 'prod-api-hal-app'
require 'pat/to/bootstrap.php';
{% endhighlight %}

Your boot file will be selected by defined context.

{% highlight bash %}
// fire php server
php -S 127.0.0.1:8080 var/www/index.php

// console access
php bootstrap/api.php get /user/1

// web access
php -S 127.0.0.1:8080 bootstrap/api.php
{% endhighlight %}

## Application Context

The composition of application object `$app` changes in response to your defined context, so that application behavior changes.
For example, `WebRouter` is bound to `RouterInterface` by default settings.
However, if you set `Cli`, which is defined for console application, as your context, 
then `CliRouter` is bound to `RouterInterface` and it will take console input instead.

There are built-in and custom context created by application.

**built-in context**

 * `api`  API Application
 * `cli`  Console Application
 * `hal`  HAL Application
 * `prod` Production

 You can also use a combination of the context and others.

 * `app` is a default application context.
 * `api` modify page resource to **app resource** by default. Also, web root access (`GET /`) that is mapped to `page://self/` will be changed to `app://self/`.
 * `cli-app` is a console application. If you set `prod-hal-api-app` as context, your application will be an API application for production using [HAL](http://stateless.co/hal_specification.html) media type.


Application Context (cli, app..) corresponds to each modules.
For example, `cli` context corresponds to a module `CliModule`, and conduct binding of DI and AOP for console application.

The value of context wil be only used when generating an object graph.
It is not recommend for your application code and library to change its behavior by referring to the context.
Instead, it should be changed by **the code depend on interface** and **the changes of dependency by the context**.
