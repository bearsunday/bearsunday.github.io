---
layout: docs-en
title: AOP
category: Manual
permalink: /manuals/1.0/en/aop.html
---
# AOP

BEAR.Sunday **AOP** enables you to write code that is executed each time a matching method is invoked. It's suited for cross cutting concerns ("aspects"), such as transactions, security and logging. Because interceptors divide a problem into aspects rather than objects, their use is called Aspect Oriented Programming (AOP).

The method interceptor API implemented is a part of a public specification called [AOP Alliance](http://aopalliance.sourceforge.net/).

## Interceptor

[MethodInterceptors](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInterceptor.php) are executed whenever a matching method is invoked.
They have the opportunity to inspect the call: the method, its arguments, and the receiving instance.
They can perform their cross-cutting logic and then delegate to the underlying method.
Finally, they may inspect the return value or the exception and return. Since interceptors may be applied to many methods and will receive many calls, their implementation should be efficient and unintrusive.


```php?start_inline
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class MyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        // Process before method invocation
        // ...

        // Original method invocation
        $result = $invocation->proceed();

        // Process after method invocation
        // ...

        return $result;
    }
}
```

## Bindings

"Find" the target class and method with `Matcher` and bind the interceptor to the matching method in [Module](module.html).

```php?start_inline
$this->bindInterceptor(
    $this->matcher->any(),                   // In any class,
    $this->matcher->startsWith('delete'),    // Method(s) names that start with "delete",
    [Logger::class]                          // Bind a Logger interceptor
);

$this->bindInterceptor(
    $this->matcher->subclassesOf(AdminPage::class),  // Of the AdminPage class or a class inherited from it
    $this->matcher->annotatedWith(Auth::class),      // Annotated method with the @Auth annotation
    [AdminAuthentication::class]                     //Bind the AdminAuthenticationInterceptor
);
```

There are various matchers.

 * [Matcher::any](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L16) 
 * [Matcher::annotatedWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L23) 
 * [Matcher::subclassesOf](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L30)
 * [Matcher::startsWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L37)
 * [Matcher::logicalOr](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L44)
 * [Matcher::logicalAnd](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L51)
 * [Matcher::logicalNot](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L58) 
```

With the `MethodInvocation` object, you can access the target method's invocation object, method's and parameters.

 * [MethodInvocation::proceed](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Joinpoint.php#L39) - Invoke method
 * [MethodInvocation::getMethod](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MethodInvocation.php) -  Get method reflection
 * [MethodInvocation::getThis](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Joinpoint.php#L48) - Get object
 * [MethodInvocation::getArguments](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Invocation.php) - Pet parameters

Annotations can be obtained using the reflection API.

```php?start_inline
$method = $invocation->getMethod();
$class = $invocation->getMethod()->getDeclaringClass();
```

 * `$method->getAnnotations()`    // get method annotations
 * `$method->getAnnotation($name)`
 * `$class->getAnnotations()`     // get class annotations
 * `$class->getAnnotation($name)`

## Own matcher
   
You can have your own matcher.
To create `contains` matcher, You need to provide a class which has two methods. One is `matchesClass` for a class match.
The other one is `matchesMethod` method match. Both return the boolean result of match.

```php?start_inline
use Ray\Aop\AbstractMatcher;

class ContainsMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        list($contains) = $arguments;

        return (strpos($class->name, $contains) !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        list($contains) = $arguments;

        return (strpos($method->name, $contains) !== false);
    }
}
```

Module

```php?start_inline
class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->bindInterceptor(
            $this->matcher->any(),       // In any class,
            new ContainsMatcher('user'), // When 'user' contained in method name
            [UserLogger::class]          // Bind UserLogger class
        );
    }
};
```
