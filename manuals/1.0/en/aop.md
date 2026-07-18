---
layout: docs-en
title: AOP
category: Manual
permalink: /manuals/1.0/en/aop.html
---
# AOP

Logging, authentication, transactions, caching. This kind of processing doesn't fit into a single class—it cuts across the whole application. Aspect-oriented programming (AOP) separates these **cross-cutting concerns** from the method body. The target method contains only its essential concern, like business logic, while the cross-cutting processing is woven in before and after it as an **interceptor**. The calling code never changes.

BEAR.Sunday's AOP is provided by [Ray.Aop](https://github.com/ray-di/Ray.Aop), which conforms to Java's [AOP Alliance](http://aopalliance.sourceforge.net/) specification.

## Interceptor

An interceptor receives a `MethodInvocation` (the method-invocation object) in its `invoke` method. `proceed()` runs the original method, so you write cross-cutting logic before and after that call.

```php
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class MyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        // Before method invocation
        // ...

        // Method invocation
        $result = $invocation->proceed();

        // After method invocation
        // ...

        return $result;
    }
}
```

The key point is that you have full control over the original method's invocation. You can skip the method entirely by returning a value without calling `proceed()` (on a cache hit, for instance), handle exceptions across the board by wrapping the call in `try/catch`, or transform the return value. Because the injector builds the interceptor itself, it can also receive its own dependencies through its constructor.

## Bindings

Which methods get an interceptor woven in isn't declared on the target method's side. In a [module](module.html), you "search" for the target class and method with a `Matcher`, then bind the interceptor to the methods that match.

```php
$this->bindInterceptor(
    $this->matcher->any(),                   // In any class,
    $this->matcher->startsWith('delete'),    // methods whose name starts with "delete",
    [Logger::class]                          // bind the Logger interceptor
);

$this->bindInterceptor(
    $this->matcher->subclassesOf(AdminPage::class),  // A subclass or implementation of AdminPage,
    $this->matcher->annotatedWith(Auth::class),      // on methods carrying the #[Auth] attribute,
    [AdminAuthentication::class]                     // bind the AdminAuthentication interceptor
);
```

In the second example, all that remains on the target method's side is the `#[Auth]` declaration. The intent—"this needs authentication"—lives on the method; its implementation and scope live in the module (see [Attribute](attribute.html) for more on attributes).

Centralizing bindings in the module has its own benefits. First, where cross-cutting processing applies is visible as a list of binding declarations, so there's no need to hunt through code to find it. Second, bindings can be switched per context: weaving in caching only in production, or execution-time logging only in development, without changing any application code. Weaving happens through code generation rather than reflection on every call, so the runtime overhead is minimal.

`Matcher` supports the following:

* [Matcher::any](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) — unrestricted
* [Matcher::annotatedWith](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) — by attribute/annotation
* [Matcher::subclassesOf](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) — by inherited or implemented class
* [Matcher::startsWith](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) — by leading name string
* [Matcher::logicalOr](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) — OR condition
* [Matcher::logicalAnd](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) — AND condition
* [Matcher::logicalNot](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) — NOT condition

## MethodInvocation

The `MethodInvocation` passed to the interceptor gives you access to the target method's object, method, and arguments.

* [MethodInvocation::proceed](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php) — invoke the target method
* [MethodInvocation::getMethod](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php) — get the target method's reflection
* [MethodInvocation::getThis](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php) — get the target object
* [MethodInvocation::getArguments](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Invocation.php) — get the array of call arguments

From the reflection you get, you can read the attributes/annotations attached to the target. Values passed to an attribute, like `#[Auth('admin')]`, can be used as parameters that drive the interceptor's behavior.

```php
$method = $invocation->getMethod();
$class = $invocation->getMethod()->getDeclaringClass();
```

* `$method->getAnnotations()`    — get method attributes
* `$method->getAnnotation($name)`
* `$class->getAnnotations()`     — get class attributes
* `$class->getAnnotation($name)`

## Custom matcher

If the built-in matchers aren't enough, extend `AbstractMatcher` and implement `matchesClass` and `matchesMethod`. Both return a bool indicating whether they matched. Here's a `contains` matcher that checks whether a name contains a given string.

```php
use Ray\Aop\AbstractMatcher;

/**
 * Whether a given string is contained
 */
class ContainsMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        [$contains] = $arguments;

        return str_contains($class->name, $contains);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        [$contains] = $arguments;

        return str_contains($method->name, $contains);
    }
}
```

Pass it to `bindInterceptor()` just like a built-in matcher.

```php
class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            new ContainsMatcher('user'), // whether the method name contains 'user'
            [UserLogger::class]
        );
    }
};
```
