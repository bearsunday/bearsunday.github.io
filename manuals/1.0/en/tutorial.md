---
layout: docs-en
title: Tutorial
category: Manual
permalink: /manuals/1.0/en/tutorial.html
---

**"[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/tutorial.md) needs to be proofread by a English speaker. If interested please send me a pull request."**

## Tutorial

Let's make a web service that returns the weekday for a given year-month-day.

Make a project with [composer](https://getcomposer.org/) first.

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

This `MyVendor\Weekday\Resource\App\Weekday` resource class is mapped `/weekday` path.
The request query is passed to php method parameters.

Let's access in console. Try the error first.

{% highlight bash %}
php bootstrap/api.php get '/weekday'

400 Bad Request
Content-Type: application/vnd.error+json

{"message":"Bad Request"}
...
{% endhighlight %}

A `400` means that you send the bad request.

Next you request with expected parameters.

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

The result is returned successfully with `application/hal+json` media type.

Let us fire the php server to make a web service for this

{% highlight bash %}
php -S 127.0.0.1:8080 bootstrap/api.php
{% endhighlight %}

Send a `GET` request as `http://127.0.0.1:8080/weekday?year=2001&month=1&day=1` with a rest client like a Chrome [Advanced REST client](https://chrome.google.com/webstore/detail/advanced-rest-client/hgmloofddffdnphfgcellkdfbfbjeloo/).

This resource class only have a GET method, therefore  `405 Method Not Allowed` will returned with another request. You may try it.

## Routing

A default router was set to `WebRouter` which simply maps URL to resource class directory.
To receive a dynamic parameter in URI path, Use `AuraRouter`. Override install with `AuraRouterModule` in `src/Modules/AppModule.php`.

{% highlight php %}

use BEAR\Package\Provide\Router\AuraRouterModule; // add this line

class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new PackageModule(new AppMeta('MyVendor\Weekday')));
        $this->override(new AuraRouterModule()); // add this line
    }
}
{% endhighlight %}

Place router script file at `var/conf/aura.route.php`.

{% highlight php %}
<?php

/** @var $router \Aura\Router\RouteCollection */

$router->add('/weekday', '/weekday/{year}/{month}/{day}')->addValues(['path' => '/weekday']);
{% endhighlight %}

Let's try it.

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

You may *not* instantiate `monolog` object with `new` operator directory, 
You "receive" a created instance as a dependency instead. This is so called [DI pattern](http://en.wikipedia.org/wiki/Dependency_injection).

Make a `MonologLoggerProvider` dependency provider in `src/Module/MonologLoggerProvider.php`

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
Add following code in `configure` method in `src/Modules/AppModule.php`.

{% highlight php %}
<?php
$this->bind(LoggerInterface::class)->toProvider(MonologLoggerProvider::class)->in(Scope::SINGLETON);
{% endhighlight %}

You may need to following code to resolve full class name.
{% highlight php %}
<?php
use Psr\Log\LoggerInterface;
use Ray\Di\Scope;
{% endhighlight %}

Now we can expect to have a `monolog` object in any constructor.
Add logging code in `src/Resource/App/Weekday.php`.

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

Let's verify `var/log/weekday.log` to confirm logger worked.

## AOP

Typical benchmarking for method invocation time is like this.

{% highlight php %}
<?php
$start = microtime(true);
// Method invocation
$time = microtime(true) - $start;
{% endhighlight %}

Changing code on each benchmarking is tedious.
A [Aspect Oriented Programming](https://github.com/google/guice/wiki/AOP) works in such a case, It can compose a `cross cutting concern` and `core concern` nicely.

First, Make a **interceptor** which intercept method for benchmarking at `src/Interceptor/BenchMarker.php`.

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
        $msg = sprintf("%s: %s", $invocation->getMethod()->getName(),$time);
        $this->logger->info($msg);

        return $result;
    }
}

{% endhighlight %}

You can invoke original method with `$invocation->proceed();` in `Invoke` method.
You may reset and stop the timer on before and after. An original method object and method name is taken [MethodInvocation](http://www.bear-project.net/Ray.Aop/build/apigen/class-Ray.Aop.MethodInvocation.html) object.

Next, Provide a [annotate](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html) class at `src/Annotation/BenchMark.php`.

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

Bind method to benchmarking interceptor in `AppModule` with the matcher. 

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

Annotate `@BenchMark` in target method.

{% highlight php %}
<?php
use MyVendor\Weekday\Annotation\BenchMark;

/**
 * @BenchMark
 */
public function onGet($year, $month, $day)
{
{% endhighlight %}

Now, you can benchmark any method with `@BenchMark` annotation.

No need to modify method caller or the method. Benchmarking is only invoked with interceptor binding even the annotation stay same.

Confirm `var/log/weekday.log` for invocation time is logged.
