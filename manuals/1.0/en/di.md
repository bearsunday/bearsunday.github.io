---
layout: docs-en
title: DI
category: Manual
permalink: /manuals/1.0/en/di.html
---
# DI

Dependency injection means an object is given the objects it needs (its dependencies) from the outside, rather than constructing them itself with `new`. Building a dependency requires its own dependencies in turn, so an application ends up as a web of interconnected objects—an object graph. Building this graph by hand is labor-intensive, error-prone, and makes testing difficult. In BEAR.Sunday, the DI framework [Ray.Di](https://ray-di.github.io/manuals/1.0/en/index.html) builds the object graph instead.

## The code you write

A class just declares what it needs in its constructor.

```php
class Index extends ResourceObject
{
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    public function onGet(string $id): static
    {
        $this->body = $this->repository->get($id);

        return $this;
    }
}
```

`Index` doesn't know what implements `UserRepositoryInterface`, or how it's built. That decision belongs not to the class that uses it, but to a binding declared in a [module](module.html).

```php
$this->bind(UserRepositoryInterface::class)->to(UserRepository::class);
```

The injector follows this binding and recursively assembles the whole graph, including the dependencies of dependencies. Because "using" an object is separate from "constructing" it, swapping an implementation never ripples into the code that uses it, and tests only need to bind a mock.

## Bindings decide

Bindings come in a few kinds, depending on what you need:

* **Linked binding** `to()` — the most basic binding: maps an interface to an implementation class
* **Provider binding** `toProvider()` — binds a factory when construction needs logic
* **Instance binding** `toInstance()` — binds an existing value or instance directly (a connection string, a config value, an already-built object)
* **Named binding** `annotatedWith()` — distinguishes dependencies of the same type by name

See [Module](module.html) for the full binding syntax, and the [Ray.Di manual](https://ray-di.github.io/manuals/1.0/en/index.html) for details on each. Cross-cutting concerns (logging, authentication, transactions) are handled separately from dependency binding, through [AOP](aop.html) (method interception via `bindInterceptor()`).

## The graph changes with context

Switch the set of bindings, and the application's behavior changes while the code stays the same. BEAR.Sunday calls this set of bindings a **context**, specified as a combination like `prod-hal-api-app`. Development, production, and API each build a different object graph, so there's no need for conditional branches driven by config files or a runtime "mode." See [Application](application.html) for details.

Ray.Di is heavily inspired by [Google Guice](https://github.com/google/guice).