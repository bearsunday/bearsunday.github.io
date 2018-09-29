---
layout: docs-en
title: Module
category: Manual
permalink: /manuals/1.0/en/module.html
---

# Modules

A Module is a collection of DI & AOP bindings that sets up your application.

BEAR.Sunday doesn't have a *global* config file or a config class to set default values for components such as a database or a template engine.
Instead for each peice of functionality we set up DI and AOP by injecting configuration values into a stand alone module.

`AppModule` (src/Module/AppModule.php) is the root module. We use an `install()` method in here to load each module that we would like to invoke.

You can also override existing bindings by using `override()`.

```php?start_inline
class AppModule extends AbstractAppModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // ...
        // install additional modules
        $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', 'username', 'password');
        $this->install(new TwigModule));
        // install basic module
        $this->install(new PackageModule));
    }
}
```

## DI bindings

`Ray.Di` is the core DI framework used in BEAR.Sunday. It binds interfaces to a class or factory to create an object graph.

```php?start_inline
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
```

Bindings declared first take priority
More info can be found at Ray.Di [README](https://github.com/ray-di/Ray.Di/blob/2.x/README.md)

## AOP Bindings

We can "search" for classes and methods with a built-in `Matcher`, then interceptors can be bound to any found methods.

```php?start_inline
$this->bindInterceptor(
    // In any class
    $this->matcher->any(),
    // Method(s) names that start with "delete"
    $this->matcher->startWith('delete'),
    // Bind a Logger interceptor
    [LoggerInterceptor::class]
);

$this->bindInterceptor(
    // The AdminPage class or a class inherited from it.
    $this->matcher->SubclassesOf(AdminPage::class),
    // Annotated with the @Auth annotation
    $this->matcher->annotatedWith(Auth::class),
    // Bind the AdminAuthenticationInterceptor
    [AdminAuthenticationInterceptor::class]
);
```

`Matcher` has various binding methods.

 * [Matcher::any](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L16) - Any
 * [Matcher::annotatedWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L23) - Annotation
 * [Matcher::subclassesOf](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php#L30) - Sub class
 * [Matcher::startsWith](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php#L37) - start with name (class or method)
 * [Matcher::logicalOr](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php#L44) - OR
 * [Matcher::logicalAnd](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php#L51) - AND
 * [Matcher::logicalNot](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php#L58) - NOT

## Interceptor

In an interceptor a `MethodInvocation` object gets passed to the `invoke` method. We can the decorate the targetted instances so that you run computations before or after any methods on the target are invoked.

```php?start_inline
class MyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        // Before invocation
        // ...

        //  Method invocation
        $result = $invocation->proceed();

        //  After invocation
        // ...

        return $result;
    }
}
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

 * `$method->getAnnotations()`
 * `$method->getAnnotation($name)`
 * `$class->->getAnnotations()`
 * `$class->->getAnnotation($name)`

## Environment Settings

BEAR.Sunday does not have any special environment mode except `prod`.
A Module and the application itself are unaware of the current environment.

There is no way to get the current "mode", this is intentional to keep the code clean.
