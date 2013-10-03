---
layout: default
title: BEAR.Sunday | Aspect Orientated Programming 
category: Getting Started
subcategory: AOP
---

Aspect Oriented Framework for PHP
=======

[![Latest Stable Version](https://poser.pugx.org/ray/aop/v/stable.png)](https://packagist.org/packages/ray/aop)
[![Build Status](https://secure.travis-ci.org/koriym/Ray.Aop.png)](http://travis-ci.org/koriym/Ray.Aop)

**Ray.Aop** package provides method interception. This feature enables you to write code that is executed each time a matching method is invoked. It's suited for cross cutting concerns ("aspects"), such as transactions, security and logging. Because interceptors divide a problem into aspects rather than objects, their use is called Aspect Oriented Programming (AOP).

A [Matcher](http://koriym.github.io/Ray.Aop/api/interfaces/Ray_Aop_Matchable.html) is a simple interface that either accepts or rejects a value. For Ray.AOP, you need two matchers: one that defines which classes participate, and another for the methods of those classes. To make this easy, there's factory class to satisfy the common scenarios.

[MethodInterceptors](http://koriym.github.io/Ray.Aop/api/interfaces/Ray_Aop_MethodInterceptor.html) are executed whenever a matching method is invoked. They have the opportunity to inspect the call: the method, its arguments, and the receiving instance. They can perform their cross-cutting logic and then delegate to the underlying method. Finally, they may inspect the return value or exception and return. Since interceptors may be applied to many methods and will receive many calls, their implementation should be efficient and unintrusive.



Example: Forbidding method calls on weekends
--------------------------------------------

To illustrate how method interceptors work with Ray.Aop, we'll forbid calls to our pizza billing system on weekends. The delivery guys only work Monday thru Friday so we'll prevent pizza from being ordered when it can't be delivered! This example is structurally similar to use of AOP for authorization.

To mark select methods as weekdays-only, we define an annotation.
(Ray.Aop uses Doctrine Annotations)


```php
<?php
/**
 * NotOnWeekends
 *
 * @Annotation
 * @Target("METHOD")
 */
final class NotOnWeekends
{
}
```

...and apply it to the methods that need to be intercepted:

```php
<?php
class RealBillingService
{
    /**
     * @NotOnWeekends
     */
    chargeOrder(PizzaOrder $order, CreditCard $creditCard)
    {
```

Next, we define the interceptor by implementing the org.aopalliance.intercept.MethodInterceptor interface. When we need to call through to the underlying method, we do so by calling $invocation->proceed():

```php
<?php
class WeekendBlocker implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $today = getdate();
        if ($today['weekday'][0] === 'S') {
            throw new \RuntimeException(
                $invocation->getMethod()->getName() . " not allowed on weekends!"
            );
        }
        return $invocation->proceed();
    }
}
```
Finally, we configure everything. In this case we match any class, but only the methods with our @NotOnWeekends annotation:

```php
<?php
$bind = new Bind;
$matcher = new Matcher(new Reader);
$interceptors = [new WeekendBlocker];
$pointcut = new Pointcut(
        $matcher->any(),
        $matcher->annotatedWith('Ray\Aop\Sample\Annotation\NotOnWeekends'),
        $interceptors
);
$bind->bind('Ray\Aop\Sample\AnnotationRealBillingService', [$pointcut]);

$compiler = require dirname(__DIR__) . '/scripts/instance.php';
$billing = $compiler->newInstance('RealBillingService', [], $bind);
try {
    echo $billing->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
```
Putting it all together, (and waiting until Saturday), we see the method is intercepted and our order is rejected:

```php
<?php
RuntimeException: chargeOrder not allowed on weekends! in /apps/pizza/WeekendBlocker.php on line 14

Call Stack:
    0.0022     228296   1. {main}() /apps/pizza/main.php:0
    0.0054     317424   2. Ray\Aop\Weaver->chargeOrder() /apps/pizza/main.php:14
    0.0054     317608   3. Ray\Aop\Weaver->__call() /libs/Ray.Aop/src/Weaver.php:14
    0.0055     318384   4. Ray\Aop\ReflectiveMethodInvocation->proceed() /libs/Ray.Aop/src/Weaver.php:68
    0.0056     318784   5. Ray\Aop\Sample\WeekendBlocker->invoke() /libs/Ray.Aop/src/ReflectiveMethodInvocation.php:65
```

Explicit method name match
---------------------------

```php
<?php
    $bind = new Bind;
    $bind->bindInterceptors('chargeOrder', [new WeekendBlocker]);

    $compiler = require dirname(__DIR__) . '/scripts/instance.php';
    $billing = $compiler->newInstance('RealBillingService', [], $bind);
    try {
       echo $billing->chargeOrder();
    } catch (\RuntimeException $e) {
       echo $e->getMessage() . "\n";
       exit(1);
    }
```

Limitations
-----------

Behind the scenes, method interception is implemented by generating code at runtime. Ray.Aop dynamically creates a subclass that applies interceptors by overriding methods.

This approach imposes limits on what classes and methods can be intercepted:

 * Classes must be *non-final*
 * Methods must be *public*
 * Methods must be *non-final*

AOP Alliance
------------
The method interceptor API implemented by Ray.Aop is a part of a public specification called [AOP Alliance](http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/MethodInterceptor.html). 

Testing Ray.Aop Stand-Alone
===============

Here's how to install Ray.Aop from source to run the unit tests and sample:

```
$ git clone git://github.com/koriym/Ray.Aop.git
$ cd Ray.Aop
$ wget http://getcomposer.org/composer.phar
$ php composer.phar install
$ php doc/sample-01-quick-weave/main.php
// Charged. | chargeOrder not allowed on weekends!
```

Requirements
-------------

 * PHP 5.4+

### ini_set

You may want to set the `xdebug.max_nesting_level` ini option to a higher value:

```php
ini_set('xdebug.max_nesting_level', 2000);
```

* This documentation for the most part is taken from [Guice/AOP](https://code.google.com/p/google-guice/wiki/AOP).
=======
title: BEAR.Sunday | Aspect Orientated Programming (AOP)
category: DI & AOP
---
# Aspect Orientated Programming (AOP)

With BEAR.Sunday's [http://en.wikipedia.org/wiki/Aspect-oriented_programming Aspect Orientated Programming(AOP)], you can for example with a `method annotated with *@Log* log the result` and you can do that *without changing the original method being called*.

AOP class does not directly affect the class, it is a way of each module to independently separate process logic that is shared between modules. For processing where many classes in which normally code duplication may creep in, we can use a technique where as an aspect (cross-cutting concerns) we can add this to a different module.

In the BEAR.Sunday framework there are many functions which the thinking is to gather aspects and implement cross-cutting functionality in AOP. When the object is created the `Injector` depending on conditions set in the module weaves the aspect to the respective method.

BEAR.Sunday uses an AOP framework called Ray.Aop which implements the [http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/MethodInvocation.html#getMethod%28%29 MethodInterceptor Interface] set out in the AOP Alliance which is similar to Google Guice or Springs implementation of AOP.

## Interceptor 

The interceptor takes hold of the method being called and performs cross-cutting processing on it. The interceptor implements the ```invoke``` method, inside that method the original method is called and the cross-cutting operations are performed.

```
public function invoke(MethodInvocation $invocation);
```

Below is a logger interceptor which logs the the parameters from the operation output.

```
class Logger implements MethodInterceptor
{
    use LogInject;

    /**
     * (non-PHPdoc)
     * @see Ray\Aop.MethodInterceptor::invoke()
     */
    public function invoke(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();
        $class = get_class($invocation->getThis());
        $args = $invocation->getArguments();
        $input = json_encode($args);
        $output = json_encode($result);
        $log # "target = [{$class}], input = [{$input}], result  [{$output}]";
        $this->log->log($log);
        return $result;
    }
}
```

In this interceptor it uses the injected Log object, the called parameters and the result are logged in JSON format. This interceptor is bound to all resources in the sandbox application's DEV mode allowing you to easily debug your app.

This method the logger is bound to has no changes at all, yet logging functionality has been added.
The original method doesn't care if the logger changes or if it is added or removed.

The primary concern of the original method is its *(core concern)*, this is completely separated from the method that takes the logs the *(cross-cutting concern)*.

## Matcher Binding 

The interceptor you made operates by being bound to the method. You use the *matcher* to decides what method it will be bound to. The object below binds all methods that begin with `on` in classes that inherit from `BEAR\Resource\Object` to the injected log object.

```
$logger = $this->requestInjection('BEAR\Framework\Interceptor\Logger');
$this->bindInterceptor(
    $this->matcher->subclassesOf('BEAR\Resource\Object'),
    $this->matcher->startWith('on'),
    [$logger]
);
```

`bindInterceptor` takes 3 parameters, the first is a class match, the second is a method match and the 3rd in an interceptor.

|| Method Signature ||　Function ||
|| bool subclassesOf($class) || Specifies the subclass. Cannot be specified in multi-dimensional arrays.  ||
|| bool any() || Matches anything||
|| bool annotatedWith($annotation) || $annotation is the annotations full path. Matches whatever is marked with this annotation. ||
|| bool startWith($prefix) || Matches whatever class/method begins with this string||

For example when you specify the following method matching, methods that are named setXX are matched.
```
$this->matcher->startWith('set')
```

## `MethodInvocation` 

The interceptor receives the MethodInvocation model variables, wraps the method runtime before and after processing and uses the variables to invoke the original method.
The `MethodInvocation` main methods are as below.

|| Method Signature ||　Function ||
|| void proceed() || Run the target method ||
|| Reflectionmethod getMethod() || Retrieve the target method reflection ||
|| Object getThis() || Retrieve the target object ||
|| array getArguments() (|| Retrieve the argument array  || 
|| array getAnnotations() || Retrieve the target methods annotations ||

## Ray.Aop 
Please see the [Ray.Aop](http://code.google.com/p/rayphp/wiki/AOP) manual for more info on the framework BEAR.Sunday uses.