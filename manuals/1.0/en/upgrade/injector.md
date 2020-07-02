---
layout: docs-en
title: Injector Upgrade Guide
category: Manual
permalink: /manuals/1.0/en/upgrade/injector.html
---

# Injector Upgrade Guide

## Changes

[BEAR.Package 1.10](https://github.com/bearsunday/BEAR.Package/releases/tag/1.10.0), `Bootstrap` and `AppInjector` has been replaced by `Injector`.

```diff
-AppInjector
-Bootstrap
+Injector.
```

In the case of `BEAR\Package\Injector::getInstance()`, the Depending on the context, different injectors are passed for production and development.
Production writes out a  DI script file with `ScriptInjector`, or no writing out DI files for development `Ray\Di\Injector`.

The interface and the usage is the same.

```php
$injector = Injector::getInstance($context);
$instance = $injector->getInsntance($interface, name);
```

## Advantages

 * The `ScriptInjector` doesn't erase files on the first request and is more secure.
 
 * The `RayRay\DiDi\Injector` does not output a DI file to `var\tmp`. Development injection is faster (especially with Docker).

 * Optimized for container environments.

 * The `AppInjector` has been optimized for different compile and runtime environments.

In the old `AppInjector`, the injector instance is retrieved every time. However, with the new `Injector`, a singleton will be shared between tests.
Speeds are dramatically improved and you won't run out of connections on a DB connection per test.

The more application- and context-spanning accesses are possible in the same memory space, the more difficult it is to implement. Clean and improved.
Swoole and other runtime environments (not PHP's shared-nothing architecture) But it runs safer and faster.

## How to upgrade

### Step 1

Put the `Injector` of the application in `src/Injector.php` and add the following lines of code Change the `Vendor\Package` to your project name.

```php
<?php
namespace Vendor\Package;

use BEAR\Package\Injector as PackageInjector;
use Ray\Di\InjectorInterface;

final class Injector
{
    private function __construct()
    {
    }

    public static function getInstance(string $context) : InjectorInterface
    {
        return PackageInjector::getInstance(__NAMESPACE__, $context, dirname(__DIR__));
    }
}
```

### Step 2

Change the `bootstrap.php`.

```diff
-$app = (new Bootstrap)->getApp($name, $context, __DIR__);
+$app = \Vendor\Package\Injector::getInstance($context)->getInstance(\BEAR\Sunday\Extension\Application\AppInterface::class);
```

### Step 3

Change the AppInjector used in `tests/`.

```diff
-new AppInjector('Vendor\Package', 'test-hal-api-app');
+\Vendor\Package\Injector::getInstance('test-hal-api-app');
```

Getting an injector for another application in a multi-application project Use the Inner Inner Inner Designer of BEARPackage in the case of

```diff
-new AppInjector('Vendor\Package', 'test-hal-api-app');
+\BEAR\Package\Injector::getInstance('Vendor\Package', 'test-hal-api-app', $appDir);
```

That's it.

## Compatibility

Backward compatibility is preserved. The class `@deprecate` is still available and will not be deprecated.
BEAR.Sunday project complies with semver seriously.