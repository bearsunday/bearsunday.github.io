---
layout: docs-en
title: Application as a Service
category: Manual
permalink: /manuals/1.0/en/aaas.html
---

##
Application Import

Resources created with BEAR.Sunday have unrivaled re-usability.
You can run multiple applications at the same time and use resources of other applications. You do not need to set up separate web servers.

Let's try using a resource in another application.

Normally you would set up the new application as a package, For this tutorial let's create a new `my-vendor` and manually add it to the auto loader. .

```bash
mkdir my-vendor
cd my-vendor
composer create-project bear/skeleton Acme.Blog
```

In the `composer.json` in the `autoload` section add `Acme\\Blog`.

```json
"autoload": {
    "psr-4": {
        "MyVendor\\Weekday\\": "src/",
        "Acme\\Blog\\": "my-vendor/Acme.Blog/src/"
    }
},
```

Dump the `autoload`.

```bash
composer dump-autoload
```

With this the configuration for the `Acme\Blog` application is complete.

Next in order to import the application in `src/Module/AppModule.php` we use the `ImportAppModule` in `src/Module/AppModule.php` to install as an override.

```php
<?php
// ...
use BEAR\Resource\Module\ImportAppModule; // add this line
use BEAR\Resource\ImportApp; // add this line
use BEAR\Package\Context; // add this line

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $importConfig = [
            new ImportApp('blog', 'Acme\Blog', 'prod-hal-app') // host, name, context
        ];
        $this->override(new ImportAppModule($importConfig , Context::class));
    }
}
```

With this a `Acme\Blog` application using a `prod-hal-app` context can create resources that will be available to the `blog` host.

Let's check it works by creating an Import resource in `src/Resource/App/Import.php`.

```php
<?php
namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Import extends ResourceObject
{
    use ResourceInject;

    public function onGet()
    {
        $this->body =[
            'blog' => $this->resource->uri('page://blog/index')['greeting']
        ];

        return $this;
    }
}
```

The `page://blog/index` resource should now be assigned to `blog`. `@Embed` can be used in the same way.

```bash
php bin/app.php get /import
```

```bash
200 OK
content-type: application/hal+json

{
    "blog": "Hello BEAR.Sunday",
    "_links": {
        "self": {
            "href": "/import"
        }
    }
}
```

Great, we could now use another application's resource. We do not even need to use HTTP to fetch this data.

The combined application is now seen as 1 layer of a single application. A
[Layered System](http://en.wikipedia.org/wiki/Representational_state_transfer#Layered_system) is another feature of REST.

Next lets look at how we use a resource in a system that is not BEAR.Sunday based. We create an app.php. You can place this anywhere but be careful that it picks up `autoload.php` path correctly.

```php?start_inline
use BEAR\Package\Bootstrap;

require __DIR__ . '/autoload.php';

$api = (new Bootstrap)->getApp('MyVendor\Weekday', 'prod-hal-app');

$blog = $api->resource->uri('app://self/import')['blog'];
var_dump($blog);
```

Let's try it..

```bash
php bin/import.php
```

```
string(17) "Hello BEAR.Sunday"
```

Other examples..

```php?start_inline
$weekday = $api->resource->uri('app://self/weekday')(['year' => 2000, 'month'=>1, 'day'=>1]);
var_dump($weekday->body); // as array
//array(1) {
//    ["weekday"]=>
//  string(3) "Sat"
//}

echo $weekday; // as string
//{
//    "weekday": "Sat",
//    "_links": {
//    "self": {
//        "href": "/weekday/2000/1/1"
//        }
//    }
//}
```

```php?start_inline
$html = (new Bootstrap)->getApp('MyVendor\Weekday', 'prod-html-app');
$index = $html->resource->uri('page://self/index')(['year' => 2000, 'month'=>1, 'day'=>1]);
var_dump($index->code);
//int(200)

echo $index;
//<!DOCTYPE html>
//<html>
//<body>
//The weekday of 2000/1/1 is Sat.
//</body>
//</html>
```

Response is returned with a stateless request REST's resource is like a PHP function. You can get the value in `body` or you can express it like JSON or HTML with `(string)`. You can operate on any resource of the application with two lines except autoload, one line script if you concatenate it.

In this way, resources created with BEAR.Sunday can be easily used from other CMS and framework. You can handle the values of multiple applications at once.