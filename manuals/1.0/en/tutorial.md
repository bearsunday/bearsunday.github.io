---
layout: docs-en
title: Tutorial
category: Manual
permalink: /manuals/1.0/en/tutorial.html
---

## Tutorial

Let's make a web service that returns the weekday for a given year-month-day.

First make a project with [composer](https://getcomposer.org/).

{% highlight bash %}
composer create-project bear/skeleton MyVendor.Weekday ~1.0@dev
...
cd MyVendor.Weekday
composer install
...
{% endhighlight %}

Add the first application resource file at `src/Resource/App/Weekday.php`

{% highlight php %}
<?php

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;

class Weekday extends ResourceObject
{
    public function onGet($year, $month, $day)
    {
        $date = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        $this['weekday'] = $date->format("D");

        return $this;
    }
}
{% endhighlight %}

This `MyVendor\Weekday\Resource\App\Weekday` resource class is mapped to the `/weekday` path.
The request query is then passed to php method parameters.

Let's access it in console and check the error code first.

{% highlight bash %}
php bootstrap/api.php get '/weekday'

400 Bad Request
Content-Type: application/vnd.error+json

{"message":"Bad Request"}
...
{% endhighlight %}

A `400` means that you sent a bad request.

Next make request with the expected parameters.

{% highlight bash %}
php bootstrap/api.php get '/weekday?year=2001&month=1&day=1'

200 OK
Content-Type: application/hal+json

{
    "weekday": "Mon",
    "_links": {
        "self": {
            "href": "/weekday?year=2001&month=1&day=1"
        }
    }
}
{% endhighlight %}

The result is returned successfully with the `application/hal+json` media type.

Let us fire the php server to make a web service for this

{% highlight bash %}
php -S 127.0.0.1:8080 bootstrap/api.php
{% endhighlight %}

Send a `GET` request as `http://127.0.0.1:8080/weekday?year=2001&month=1&day=1` with a rest client like a Chrome [Advanced REST client](https://chrome.google.com/webstore/detail/advanced-rest-client/hgmloofddffdnphfgcellkdfbfbjeloo/).

This resource class only has a GET method, therefore `405 Method Not Allowed` will be returned with any other request. Try it out!.

## Routing

A default router is set to `WebRouter` which simply maps URL's to the resource class directory.
To receive a dynamic parameter in URI path, we can use `AuraRouter`. This can be done with an override install of the `AuraRouterModule` in `src/Modules/AppModule.php`.

{% highlight php %}

use BEAR\Package\Provide\Router\AuraRouterModule; // add this line

class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new PackageModule));
        $this->override(new AuraRouterModule); // add this line
    }
}
{% endhighlight %}

This module looks for a router script file at `var/conf/aura.route.php`.

{% highlight php %}
<?php

/** @var $router \Aura\Router\RouteCollection */

$router->add('/weekday', '/weekday/{year}/{month}/{day}')->addValues(['path' => '/weekday']);
{% endhighlight %}

Let's try it out.

{% highlight bash %}
php bootstrap/api.php get '/weekday/1981/09/08'
200 OK
Content-Type: application/hal+json

{
    "weekday": "Tue",
    "_links": {
        "self": {
            "href": "/weekday/1981/09/08"
        }
    }
}
{% endhighlight %}

Congratulations! You’ve just developed a hypermedia-driven RESTful web service with BEAR.Sunday.

## DI

Let's log a result with [monolog](https://github.com/Seldaek/monolog) logger.
Get it with [composer](http://getcomposer.org) first.

{% highlight bash %}
composer require monolog/monolog "~1.0"
{% endhighlight %}

You instantiating `monolog` object with the `new` operator is *strongly discouraged*, 
you "receive" a created instance as a dependency instead. This is called the [DI pattern](http://en.wikipedia.org/wiki/Dependency_injection).

To do this make a `MonologLoggerProvider` dependency provider in `src/Module/MonologLoggerProvider.php`

{% highlight php %}
<?php

namespace MyVendor\Weekday\Module;

use BEAR\AppMeta\AbstractAppMeta;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ray\Di\ProviderInterface;

class MonologLoggerProvider implements ProviderInterface
{
    /**
     * @var AbstractAppMeta
     */
    private $appMeta;

    public function __construct(AbstractAppMeta $appMeta)
    {
        $this->appMeta = $appMeta;
    }

    public function get()
    {
        $log = new Logger('weekday');
        $log->pushHandler(
            new StreamHandler($this->appMeta->logDir . '/weekday.log')
        );

        return $log;
    }
}
{% endhighlight %}

We need a log directory path to log, It can be get via the application meta information object which passed in constructor.
Dependency is provided via `get` method.

To bind the [logger interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) to the factory class,
Add the following code to the `configure` method in `src/Modules/AppModule.php`.

{% highlight php %}
<?php
$this->bind(LoggerInterface::class)->toProvider(MonologLoggerProvider::class)->in(Scope::SINGLETON);
{% endhighlight %}

You may need the following code to resolve the full class name.
{% highlight php %}
<?php
use Psr\Log\LoggerInterface;
use Ray\Di\Scope;
{% endhighlight %}

Now we can expect to have a `monolog` object injected into any constructor.
Add some code in `src/Resource/App/Weekday.php` to be able to start logging.

{% highlight php %}
<?php

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use Psr\Log\LoggerInterface;

class Weekday extends ResourceObject
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onGet($year, $month, $day)
    {
        $date = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        $this['weekday'] = $date->format("D");
        $this->logger->info("$year-$month-$day {$this['weekday']}");

        return $this;
    }
}

{% endhighlight %}

Let's check `var/log/weekday.log` to see if our logger worked.

## AOP

We can benchmarking method invocation like is often done like this.

{% highlight php %}
<?php
$start = microtime(true);
// Method invocation
$time = microtime(true) - $start;
{% endhighlight %}

Changing code to benchmark each different method can be tedious.
For such problems [Aspect Oriented Programming](https://github.com/google/guice/wiki/AOP) works great. Using this concept you can compose a clean separation of a `cross cutting concern` and `core concern.

First, make a **interceptor** which intercepts the target method for benchmarking which we will save in `src/Interceptor/BenchMarker.php`.

{% highlight php %}
<?php

namespace MyVendor\Weekday\Interceptor;

use Psr\Log\LoggerInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class BenchMarker implements MethodInterceptor
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function invoke(MethodInvocation $invocation)
    {
        $start = microtime(true);
        $result = $invocation->proceed(); // original method invocation
        $time = microtime(true) - $start;
        $msg = sprintf("%s: %s", $invocation->getMethod()->getName(), $time);
        $this->logger->info($msg);

        return $result;
    }
}

{% endhighlight %}

You can invoke the original method with `$invocation->proceed();` inside an `invoke` method.
You can then reset and stop the timer on before and after this is invoked. The target method object and method name is taken in the form of a [MethodInvocation](http://www.bear-project.net/Ray.Aop/build/apigen/class-Ray.Aop.MethodInvocation.html) object sent to the invoke method.

Next, provide an [annotation](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html) class at `src/Annotation/BenchMark.php`.

{% highlight php %}
<?php

namespace MyVendor\Weekday\Annotation;

/**
 * @Annotation
 */
final class BenchMark
{
}
{% endhighlight %}

We then need to bind the target method to the benchmarking interceptor in `AppModule` with a matcher. 

{% highlight php %}
<?php
use MyVendor\Weekday\Annotation\BenchMark;
use MyVendor\Weekday\Interceptor\BenchMarker;

// add the code to configure() method.
$this->bindInterceptor(
    $this->matcher->any(),                           // in any class
    $this->matcher->annotatedWith(BenchMark::class), // which annotated as @BenchMark
    [BenchMarker::class]                             // apply BenchMarkerInterceptor
);
{% endhighlight %}

Annotate the target method with `@BenchMark`.

{% highlight php %}
<?php
use MyVendor\Weekday\Annotation\BenchMark;

/**
 * @BenchMark
 */
public function onGet($year, $month, $day)
{
{% endhighlight %}

Now, you can benchmark any method that has the `@BenchMark` annotation.

There is no need to modify the method caller or the target method itself. Benchmarking is only invoked with the interceptor binding, so even by leaving the annotation in place you can turn benchmarking on and off by adding and removing the binding from the application.

Now check out the logging for the method invocation speed in `var/log/weekday.log`.
