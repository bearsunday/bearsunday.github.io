---
layout: docs-en
title: Import
category: Manual
permalink: /manuals/1.0/en/import.html
---

# Import

Every BEAR application is a collection of resources, each with a URI. Because of this, you can install a whole application as a "part" of another BEAR application and use it through the same interface as your own resources. It splits a system into independent applications the way microservices do, while calls stay in-process—no network involved.

## Composer install

Install the BEAR application you want to use as a composer package. [^1]

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

## Module install

Register the application to import with `ImportAppModule`. `ImportApp` takes three arguments: a hostname, the application name (namespace), and a context.

AppModule.php
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

* `foo` — the hostname that points to the import target, corresponding to `app://foo/` in a URI.
* `MyVendor\Weekday` — the application name (namespace) to import.
* `prod-app` — the context the imported application runs under.

Notice that the context can be specified independently of the host application's own context. Even while your own application runs in a development context, the imported application can run under `prod-app` bindings. This kind of composition is possible precisely because each BEAR application has a self-contained DI and AOP configuration. [^2]

## Request

Request an imported resource by specifying the hostname you registered.

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
```

The only difference from your own resources (`app://self/`) is the hostname—`#[Embed]` and `#[Link]` work the same way. Calling code doesn't need to know whether a resource is its own or imported, and although this request looks like remote access, it carries none of the latency or connection-failure cost of a distributed system.

## Requests from other systems

It's just as easy to use BEAR resources from other frameworks or CMSs. Install the application as a package the same way, then get an injector by passing `Injector::getInstance` the application name, context, and application path, and get a resource client from it. No bootstrap script is needed—the BEAR application functions as a single library configured for the context you specify.

```php
use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;

$resource = Injector::getInstance(
    'MyVendor\Weekday',
    'prod-api-app',
    dirname(__DIR__) . '/vendor/my-vendor/weekday'
)->getInstance(ResourceInterface::class);

$weekday = $resource->get('/weekday', ['year' => '2022', 'month' => '1', 'day' => 1]);
echo $weekday->body['weekday'] . PHP_EOL;
```

## Environment variables

Environment variables are global and shared across applications, so prefix them to avoid conflicts. Rather than a `.env` file, the imported application picks up shell environment variables just as it would in production.

## System boundary

Building a large application as a collection of smaller ones is similar to microservices, but without microservices' downsides, such as increased infrastructure overhead. And because the boundary is the application itself rather than a namespace convention, component independence and boundaries are clearer than with a modular monolith.

The code for this page can be found at [bearsunday/example-import-app](https://github.com/bearsunday/example-import-app/commits/master).

## Multilingual framework

Using [BEAR.Thrift](https://github.com/bearsunday/BEAR.Thrift), you can access resources from other languages, different PHP versions, or other BEAR applications via [Apache Thrift](https://thrift.apache.org/).

[^1]: Import requires `bear/package ^1.13`.
[^2]: `ImportAppModule` comes from `BEAR\Package`, not `BEAR\Resource`.
