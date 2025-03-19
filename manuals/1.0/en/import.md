---
layout: docs-en
title: Import
category: Manual
permalink: /manuals/1.0/en/import.html
---

# Import

BEAR applications can cooperate with multiple BEAR applications into a single system without having to be microservices. It is also easy to use BEAR resources from other applications.

## Composer Install

Install the BEAR application you want to use as a composer package.

composer.json
```json
{
  "require": {
    "bear/package": "^1.13",
    "my-vendor/weekday": "dev-master"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/bearsunday/tutorial1.git"
    }
  ]
}
```

Requires `bear/package ^1.13`.

## Module Install

Install other applications with `ImportAppModule`, specifying the hostname, application name (namespace) and context to import.

```diff
+use BEAR\Package\Module\ImportAppModule;
+use BEAR\Package\Module\Import\ImportApp;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        // ...
+        $this->install(new ImportAppModule([
+            new ImportApp('foo', 'MyVendor\Weekday', 'prod-app')
+        ]));
        $this->install(new PackageModule());
    }
}
```

## Request

The imported resource will be used with the specified host name.

```php
class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(string $name = 'BEAR.Sunday'): static
    {
        $weekday = $this->resource->get('app://foo/weekday?year=2022&month=1&day=1');
        $this->body = [
            'greeting' => 'Hello ' . $name,
            'weekday' => $weekday
        ];

        return $this;
    }
}
````

You can also use `#[Embed]` and `#[Link]` in the same way.

## Requests from other systems

It is easy to use BEAR resources from other frameworks or CMS.

Install it as a package in the same way, and use `Injector::getInstance` to get the resource client of the application you require and request it.

```php

use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;

$resource = Injector::getInstance(
    'MyVendor\Weekday',
    'prod-api-app',
    dirname(__DIR__) . '/vendor/my-vendor/weekday'
)->getInstance(ResourceInterface::class);
$weekdday = $resource->get('/weekday', ['year' => '2022', 'month' => '1', 'day' => 1]);

echo $weekdday->body['weekday'] . PHP_EOL;
```
## Environment variables

Environment variables are global. Care should be taken to prefix them to avoid conflicts between applications. Instead of using `.env` files, the application to be imported will get the shell environment variables just like in production.

## System Boundary

It is similar to microservices in that a large application can be built as a collection of multiple smaller applications, but without the disadvantages of microservices such as increased infrastructure overhead. It also has clearer component independence and boundaries than modular monoliths.

The code for this page can be found at [bearsunday/example-app-import](https://github.com/bearsunday/example-import-app/commits/master).

## Multilingual Framework

Using [BEAR.Thrift](https://github.com/bearsunday/BEAR.Thrift), you can access resources from other languages, different versions of PHP, or BEAR applications using Apache Thrift. [Apache Thrift](https://thrift.apache.org/) is a framework that enables efficient communication between different languages.
