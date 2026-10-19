---
layout: docs-en
title: Module
category: Manual
permalink: /manuals/1.0/en/module.html
---
# Modules

A module is a set of DI and AOP bindings. BEAR.Sunday has no general configuration file, Config class, or execution mode. Each component receives the values it needs through dependency injection, and the module is responsible for binding those dependencies.

The root module of an application is `AppModule` (src/Module/AppModule.php).
Inside `AppModule`, install the other modules the application needs.

Values required by a module (values needed at compile time rather than runtime) are bound through manual constructor injection.

```php
class AppModule extends AbstractAppModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // Additional modules
        $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', getenv('db_username'), getenv('db_password')));
        $this->install(new TwigModule());
        // Package standard modules
        $this->install(new PackageModule());
    }
}
```

## DI bindings

Here are the most common binding patterns:

```php
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

See [DI](di.html) for details.

## AOP bindings

AOP "searches" classes and methods with a `Matcher` and binds interceptors to the matching methods.

```php
// Example 1: binding by method name
$this->bindInterceptor(
    $this->matcher->any(),                   // in any class
    $this->matcher->startsWith('delete'),    // for methods whose names start with "delete"
    [Logger::class]                          // bind the Logger interceptor
);

// Example 2: binding by class and annotation
$this->bindInterceptor(
    $this->matcher->SubclassesOf(AdminPage::class),  // in classes that extend or implement AdminPage
    $this->matcher->annotatedWith(Auth::class),      // for methods annotated with #[Auth]
    [AdminAuthentication::class]                     // bind the AdminAuthentication interceptor
);
```

See [AOP](aop.html) for details.

## Binding priority

See also: [Ray.Di bindings](https://ray-di.github.io/manuals/1.0/en/bindings.html)

### Within a single module

Within the same module, the first binding wins. In the following example, `Foo1` takes priority:

```php
$this->bind(FooInterface::class)->to(Foo1::class);
$this->bind(FooInterface::class)->to(Foo2::class);
```

### Module installation order

The first installed module wins. In the following example, `Foo1Module` takes priority:

```php
$this->install(new Foo1Module);
$this->install(new Foo2Module);
```

To give a later module priority, use `override`. In the following example, `Foo2Module` takes priority:

```php
$this->install(new Foo1Module);
$this->override(new Foo2Module);
```

### Context string order

Context modules are processed in **reverse order** (right to left). For example, with `prod-hal-api-app`:

```text
Installation order: AppModule → ApiModule → HalModule → ProdModule
```

A later installed module can override the bindings of an earlier one. That is:

- `HalModule` takes priority over `AppModule`
- `ProdModule` takes priority over `HalModule`

When creating a custom context module that overrides a binding from a built-in context (such as `HalModule`), place it to the **left** of that context in the context string. For example, to override `HalModule`'s `RenderInterface` binding:

```php
// Context: "prod-mycontext-hal-api-app"
// Installation order: AppModule → ApiModule → HalModule → MycontextModule → ProdModule
```
