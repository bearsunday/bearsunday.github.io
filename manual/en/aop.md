---
layout: default
title: BEAR.Sunday | Aspect Orientated Programming 
category: Manual
---

Aspect Oriented Framework
=========================

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
$this->bindInterceptor(
    $this->matcher->any(),                          // any class
    $this->matcher->annotatedWith('NotOnWeekends'), // @NotOnWeekends method
    [$logger]
);


```
Putting it all together, (and waiting until Saturday), we see the method is intercepted and our order is rejected:

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

## Interceptor

The interceptor takes hold of the method being called and performs cross-cutting processing on it. The interceptor implements the ```invoke``` method, inside that method the original method is called and the cross-cutting operations are performed.

```php
<?php
public function invoke(MethodInvocation $invocation);
```

Below is a logger interceptor which logs the the parameters from the operation output.

```php
<?php
class Logger implements MethodInterceptor
{
    use LogInject;

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

The interceptor you made operates by being bound to the method. You use the *matcher* to decides what method it will be bound to. The object below binds all methods that begin with `on` in classes that inherit from `BEAR\Resource\ResourceObject` to the injected log object.

```php
<?php
$logger = $this->requestInjection('BEAR\Framework\Interceptor\Logger');
$this->bindInterceptor(
    $this->matcher->subclassesOf('BEAR\Resource\ResourceObject'),
    $this->matcher->startWith('on'),
    [$logger]
);
```

`bindInterceptor` takes 3 parameters, the first is a class match, the second is a method match and the 3rd in an interceptor.

| Method Signature |　Function |
|----------------- | ----------|
| bool subclassesOf($class) | Specifies the subclass. Cannot be specified in multi-dimensional arrays.  |
| bool any() | Matches anything|
| bool annotatedWith($annotation) | $annotation is the annotations full path. Matches whatever is marked with this annotation. |
| bool startWith($prefix) | Matches whatever class/method begins with this string|

For example when you specify the following method matching, methods that are named setXX are matched.

```php
<?php
$this->matcher->startWith('set')
```

## MethodInvocation

The interceptor receives the MethodInvocation model variables, wraps the method runtime before and after processing and uses the variables to invoke the original method.
The `MethodInvocation` main methods are as below.

| Method Signature |　Function |
|----------------- | ----------|
| void proceed() | Run the target method |
| Reflectionmethod getMethod() | Retrieve the target method reflection |
| Object getThis() | Retrieve the target object |
| array getArguments() | Retrieve the argument array  |
| array getAnnotations() | Retrieve the target methods annotations |

## NamedArgs

Arguments of method interceptors are ordered varibles like normal PHP function calls.
You can change them to associative array which has variable names as keys and variable values as values.

```php
 public function onGet($userId)
```

You can use the variable `$userId` like this.

```php
use NamedArgsInject;

public function invoke(MethodInvocation $invocation)
{
    $args = $this->namedArgs->get($invocation);
    $userId = $args['userId'] // value of argument $userId
    ...
```
