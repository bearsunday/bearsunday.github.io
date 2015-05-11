---
layout: docs-en
title: Module
category: Manual
permalink: /manuals/1.0/en/module.html
---

 * *[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/module.md) needs to be proofread by an English speaker. If interested please send me a pull request. Thank you.*


# Module

Module is the collection of DI & AOP bindings. It forms application.

BEAR.Sunday doesn't have an *global* config file nor Config class for the components such as database or template engine.
Inject component config values into each Module in `AppModule` in constructor instead of pulling them from factory class. 

`AppModule` (src/Module/AppModule.php) is a root application module. We `install()` all required module here.
Also you can override existing bindings `override()`.

{% highlight php %}
<?php
class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // install basic module
        $this->install(new PackageModule));
        // install additional module
        $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', 'username', 'password');
        $this->install(new TwigModule));
    }
}
{% endhighlight %}

## DI bindings

`Ray.Di` is the DI framework in BEAR.Sunday. It binds interface to the class or factory to create an object graph.

{% highlight php %}
<?php
// Class binding
$this->bind($interface)->to($class);
// Provider (factory) binding
$this->bind($interface)->toProvider($provider);
// Instance binding
$this->bind($interface)->toInstance($instance);
// Named binding
$this->bind($interface)->annotatedWith($annotation)->to($class);
// Singleton
$this->bind($interface)->to($class)->in(Scope::SINGLETON);
// Constructor binding
$this->bind($interface)->toConstructor($class, $named);
{% endhighlight %}

Earlier binding has a priority. You can override bindings with `override()`.
More detail information are available at Ray.Di [README](https://github.com/ray-di/Ray.Di/blob/2.x/README.md)

## Aop bindings

We can "search" class and method with builtin `Matcher`, then bound interceptors on found method.

{% highlight php %}
<?php
$this->bindInterceptor(
    $this->matcher->any(),                   // in any class
    $this->matcher->annotatedWith('delete'), // method(s) starts with "delete"
    [LoggerInterceptor::class]               // bind Logger interceptor
);

$this->bindInterceptor(
    $this->matcher->SubclassesOf(AdminPage::class),  // inherited or implemented of AdminPage
    $this->matcher->annotatedWith(Auth::class),      // annotated with @Auth annotation
    [AdminAuthenticationInterceptor::class]          // bind AdminAuthenticationInterceptor
);
{% endhighlight %}

`Matcher` has various bind methods.

 * [Matcher::any](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L16) - Any
 * [Matcher::annotatedWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L23) - Annotation
 * [Matcher::subclassesOf](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L30) - Sub class
 * [Matcher::startsWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L37) - start with name (class or method)
 * [Matcher::logicalOr](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L44) - OR
 * [Matcher::logicalAnd](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L51) - AND
 * [Matcher::logicalNot](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L58) - NOT

We can receive a `MethodInvocation` object in`invoke` method. Decorating the configured instances so some logic can be run before or after any of their methods.

{% highlight php %}
<?php
class MyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        // before invocation
        // ...
        
        //  method invocation
        $result = $invocation->proceed();
        
        //  after invocation
        // ...
        
        return $result; 
    }
}
{% endhighlight %}

With `MethodInvocation`, You can access method invocation object, method, or parameters.

 * [MethodInvocation::proceed](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Joinpoint.php#L39) - Invoke method
 * [MethodInvocation::getMethod](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MethodInvocation.php) -  Get method reflection
 * [MethodInvocation::getThis](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Joinpoint.php#L48) - Get object
 * [MethodInvocation::getArguments](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Invocation.php) - Pet parameters
 
## Mode

BEAR.Sunday does not have mode expect `prod`.
An Module and application are agnostic about its own environment.
