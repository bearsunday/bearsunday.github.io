---
layout: docs-en
title: Router
category: Manual
permalink: /manuals/1.0/en/router.html
---

# Router

The router converts resource requests for external contexts such as Web and console into resource requests inside BEAR.Sunday.

```php?start_inline
$request = $app->router->match($GLOBALS, $_SERVER);
echo (string) $request;
// get page://self/user?name=bear
```

## Web Router


The default web router accesses the resource class corresponding to the HTTP request path (`$_SERVER['REQUEST_URI']`).
For example, a request of `/index` is accessed by a PHP method corresponding to the HTTP method of the `{Vendor name}\{Project name}\Resource\Page\Index` class.

Router settings and scripts are not necessary.

```php?start_inline
namespace MyVendor\MyProject\Resource\Page;

// page://self/index
class Index extends ResourceObject
{
    public function onGet() : ResourceObject // GETリクエスト
    {
    }
}
```

## CLI Router

In the `cli` context, the argument from the console is "input of external context".

```bash
php bin/page.php get /
```

The BEAR.Sunday application works on both the Web and the CLI.

## Multiple words URI

The path of the URI using hyphens and using multiple words uses the class name of Camel Case.
For example `/wild-animal` requests are accessed to the `WildAnimal` class.

## Prameters

The name of the PHP method executed corresponding to the HTTP method and the value passed are as follows.

| HTTP method | PHP method | Parameters |
|---|---|---|
| GET | onGet | $_GET |
| POST | onPost | $_POST or ※ standard input |
| PUT | onPut | ※ standard input |
| PATCH | onPatch | ※ standard input |
| DELETE | onDelete | ※ standard input　|

There are two media types available for request:

 * `application/x-www-form-urlencoded` // param1=one&param2=two
 * `application/json` // {"param1": "one", "param2": "one"}

Please also see the [PUT method support](http://php.net/manual/en/features.file-upload.put-method.php) of the PHP manual.

## Method Override

There are firewalls that do not allow HTTP PUT traffic or HTTP DELETE traffic.
To deal with this constraint, you can send these requests in the following two ways.

 * `X-HTTP-Method-Override` Send a PUT request or DELETE request using the header field of the POST request.
 * `_method` Use the URI parameter. ex) POST /users?...&_method=PUT

## Aura Router

To receive the request path as a parameter, use Aura Router.

```bash
composer require bear/aura-router-module ^2.0
```

Install `AuraRouterModule` with the path of the router script.

```php?start_inline
use BEAR\Package\AbstractAppModule;
use BEAR\Package\Provide\Router\AuraRouterModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new AuraRouterModule($appDir . '/var/conf/aura.route.php');
    }
}
```

### Router Script

Router scripts set routes for `Map` objects passed globally.
You do not need to specify a method for routing.
The first argument specifies the path as the root name and the second argument specifies the path containing the place folder of the named token.

`var/conf/aura.route.php`

```php
<?php
/* @var \Aura\Router\Map $map */
$map->route('/blog', '/blog/{id}');
$map->route('/user', '/user/{name}')->tokens(['name' => '[a-z]+']);
```

In the first line, accessing `/blog/bear` will be accessed as `page://self/blog?id=bear`.
(= `Blog` class's` onGet($id)` method with the value `$id`=`bear`.) Also `token` is used to restrict parameters with regular expressions.

### Preferred router

If it is not routed by the Aura router, a web router will be used.
In other words, it is OK to prepare the router script only for the URI that passes the parameters in the path.

### Parameter

`Aura router` have various methods to obtain parameters from the path.

### Custom Placeholder Token Matching

The script below routes only when `{date}` is in the proper format.

```php?start_inline
$map->route('/calendar/from', '/calendar/from/{date}')
    ->tokens([
        'date' => function ($date, $route, $request) {
            try {
                new \DateTime($date);
                return true;
            } catch(\Exception $e) {
                return false;
            }
        }
    ]);
```

### Optional Placeholder Tokens

Sometimes it is useful to have a route with optional placeholder tokens for attributes. None, some, or all of the optional values may be present, and the route will still match.

To specify optional attributes, use the notation {/attribute1,attribute2,attribute3} in the path. For example:

ex）
```php?start_inline
$map->route('archive', '/archive{/year,month,day}')
    ->tokens([
        'year' => '\d{4}',
        'month' => '\d{2}',
        'day' => '\d{2}',
    ]);
```

Please note that there is the first slash **inside** of the place holder.
Then all the paths below are routed to 'archive' and the value of the parameter is appended.


- `/archive            : ['year' => null,   'month' => null, 'day' = null]`
- `/archive/1979       : ['year' => '1979', 'month' => null, 'day' = null]`
- `/archive/1979/11    : ['year' => '1979', 'month' => '11', 'day' = null]`
- `/archive/1979/11/07 : ['year' => '1979', 'month' => '11', 'day' = '07']`

Optional parameters are **options in the order of**. In other words, you can not specify "day" without "month".

### Wildcard Attributes

Sometimes it is useful to allow the trailing part of the path be anything at all. To allow arbitrary trailing path segments on a route, call the wildcard() method. This will let you specify the attribute name under which the arbitrary trailing values will be stored.

```php?start_inline
$map->route('wild', '/wild')
    ->wildcard('card');
```

All slash-separated path segments after the {id} will be captured as an array in the in wildcard attribute. For example:

- `/wild             : ['card' => []]`
- `/wild/foo         : ['card' => ['foo']]`
- `/wild/foo/bar     : ['card' => ['foo', 'bar']]`
- `/wild/foo/bar/baz : ['card' => ['foo', 'bar', 'baz']]`

For other advanced routes, please refer to Aura Router's [defining-routes](https://github.com/auraphp/Aura.Router/blob/3.x/docs/defining-routes.md).

## Generating Paths From Routes

You can generate a URI from the name of the route and the value of the parameter.

```php?start_inline
use BEAR\Sunday\Extension\Router\RouterInterface;

class Index extends ResourceObject
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onGet() : ResourceObject
    {
        $userLink = $this->router->generate('/user', ['name' => 'bear']);
        // '/user/bear'
```

## Custom Router Component

Implement [RouterInterface](https://github.com/bearsunday/BEAR.Sunday/blob/1.x/src/Extension/Router/RouterInterface.php) with by referring to [BEAR.AuraRouterModule](https://github.com/bearsunday/BEAR.AuraRouterModule).

---
*[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/router.md) needs to be proofread by native speaker. *
