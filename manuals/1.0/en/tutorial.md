---
layout: docs-en
title: Tutorial
category: Manual
permalink: /manuals/1.0/en/tutorial.html
---

## Tutorial

Let's make a web service that returns the weekday for a given year-month-day.

First make a project with [composer](https://getcomposer.org/).

{% highlight bash %}
composer create-project -n bear/skeleton MyVendor.Weekday
...
cd MyVendor.Weekday
composer install
...
{% endhighlight %}

Add the first application resource file at `src/Resource/App/Weekday.php`

{% highlight php %}
<?php

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;

class Weekday extends ResourceObject
{
    public function onGet($year, $month, $day)
    {
        $date = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        $this['weekday'] = $date->format("D");

        return $this;
    }
}
{% endhighlight %}

This `MyVendor\Weekday\Resource\App\Weekday` resource class is mapped to the `/weekday` path.
The request query is then passed to php method parameters.

Let's access it in console and check the error code first.

{% highlight bash %}
php bootstrap/api.php get '/weekday'

400 Bad Request
Content-Type: application/vnd.error+json

{"message":"Bad Request"}
...
{% endhighlight %}

A `400` means that you sent a bad request.

Next make request with the expected parameters.

{% highlight bash %}
php bootstrap/api.php get '/weekday?year=2001&month=1&day=1'

200 OK
Content-Type: application/hal+json

{
    "weekday": "Mon",
    "_links": {
        "self": {
            "href": "/weekday?year=2001&month=1&day=1"
        }
    }
}
{% endhighlight %}

The result is returned successfully with the `application/hal+json` media type.

Let us fire the php server to make a web service for this

{% highlight bash %}
php -S 127.0.0.1:8080 bootstrap/api.php
{% endhighlight %}

Send a `GET` request as `http://127.0.0.1:8080/weekday?year=2001&month=1&day=1` with a rest client like a Chrome [Advanced REST client](https://chrome.google.com/webstore/detail/advanced-rest-client/hgmloofddffdnphfgcellkdfbfbjeloo/).

This resource class only has a GET method, therefore `405 Method Not Allowed` will be returned with any other request. Try it out!.

## Routing

A default router is set to `WebRouter` which simply maps URL's to the resource class directory.
To receive a dynamic parameter in URI path, we can use `AuraRouter`. This can be done with an override install of the `AuraRouterModule` in `src/Module/AppModule.php`.
Get it with [composer](http://getcomposer.org) first.

{% highlight bash %}
composer require bear/aura-router-module ~1.0
{% endhighlight %}

{% highlight php %}
<?php

namespace MyVendor\Weekday\Module;

use BEAR\Package\PackageModule;
use Ray\Di\AbstractModule;
use BEAR\Package\Provide\Router\AuraRouterModule; // add this line

class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new PackageModule);
        $this->override(new AuraRouterModule); // add this line
    }
}
{% endhighlight %}

This module looks for a router script file at `var/conf/aura.route.php`.

{% highlight php %}
<?php

/** @var $router \Aura\Router\RouteCollection */

$router->add('/weekday', '/weekday/{year}/{month}/{day}')->addValues(['path' => '/weekday']);
{% endhighlight %}

Let's try it out.

{% highlight bash %}
php bootstrap/api.php get '/weekday/1981/09/08'
200 OK
Content-Type: application/hal+json

{
    "weekday": "Tue",
    "_links": {
        "self": {
            "href": "/weekday/1981/09/08"
        }
    }
}
{% endhighlight %}

Congratulations! You’ve just developed a hypermedia-driven RESTful web service with BEAR.Sunday.

## DI

Let's log a result with [monolog](https://github.com/Seldaek/monolog) logger.
Get it with [composer](http://getcomposer.org) first.

{% highlight bash %}
composer require monolog/monolog ~1.0
{% endhighlight %}

You instantiating `monolog` object with the `new` operator is *strongly discouraged*, 
you "receive" a created instance as a dependency instead. This is called the [DI pattern](http://en.wikipedia.org/wiki/Dependency_injection).

To do this make a `MonologLoggerProvider` dependency provider in `src/Module/MonologLoggerProvider.php`

{% highlight php %}
<?php

namespace MyVendor\Weekday\Module;

use BEAR\AppMeta\AbstractAppMeta;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ray\Di\ProviderInterface;

class MonologLoggerProvider implements ProviderInterface
{
    /**
     * @var AbstractAppMeta
     */
    private $appMeta;

    public function __construct(AbstractAppMeta $appMeta)
    {
        $this->appMeta = $appMeta;
    }

    public function get()
    {
        $log = new Logger('weekday');
        $log->pushHandler(
            new StreamHandler($this->appMeta->logDir . '/weekday.log')
        );

        return $log;
    }
}
{% endhighlight %}

We need a log directory path to log, It can be get via the application meta information object which passed in constructor.
Dependency is provided via `get` method.

To bind the [logger interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) to the factory class,
Add the following code to the `configure` method in `src/Modules/AppModule.php`.

{% highlight php %}
<?php
$this->bind(LoggerInterface::class)->toProvider(MonologLoggerProvider::class)->in(Scope::SINGLETON);
{% endhighlight %}

You may need the following code to resolve the full class name.
{% highlight php %}
<?php
use Psr\Log\LoggerInterface;
use Ray\Di\Scope;
{% endhighlight %}

Now we can expect to have a `monolog` object injected into any constructor.
Add some code in `src/Resource/App/Weekday.php` to be able to start logging.

{% highlight php %}
<?php

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use Psr\Log\LoggerInterface;

class Weekday extends ResourceObject
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onGet($year, $month, $day)
    {
        $date = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        $this['weekday'] = $date->format("D");
        $this->logger->info("$year-$month-$day {$this['weekday']}");

        return $this;
    }
}

{% endhighlight %}

Let's check `var/log/weekday.log` to see if our logger worked.

{% highlight bash %}
php bootstrap/api.php get '/weekday/2011/05/23'
cat var/log/weekday.log
{% endhighlight %}

## AOP

We can benchmarking method invocation like is often done like this.

{% highlight php %}
<?php
$start = microtime(true);
// Method invocation
$time = microtime(true) - $start;
{% endhighlight %}

Changing code to benchmark each different method can be tedious.
For such problems [Aspect Oriented Programming](https://github.com/google/guice/wiki/AOP) works great. Using this concept you can compose a clean separation of a `cross cutting concern` and `core concern.

First, make a **interceptor** which intercepts the target method for benchmarking which we will save in `src/Interceptor/BenchMarker.php`.

{% highlight php %}
<?php

namespace MyVendor\Weekday\Interceptor;

use Psr\Log\LoggerInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class BenchMarker implements MethodInterceptor
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function invoke(MethodInvocation $invocation)
    {
        $start = microtime(true);
        $result = $invocation->proceed(); // original method invocation
        $time = microtime(true) - $start;
        $msg = sprintf("%s: %s", $invocation->getMethod()->getName(), $time);
        $this->logger->info($msg);

        return $result;
    }
}

{% endhighlight %}

You can invoke the original method with `$invocation->proceed();` inside an `invoke` method.
You can then reset and stop the timer on before and after this is invoked. The target method object and method name is taken in the form of a [MethodInvocation](http://www.bear-project.net/Ray.Aop/build/apigen/class-Ray.Aop.MethodInvocation.html) object sent to the invoke method.

Next, provide an [annotation](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html) class at `src/Annotation/BenchMark.php`.

{% highlight php %}
<?php

namespace MyVendor\Weekday\Annotation;

/**
 * @Annotation
 */
final class BenchMark
{
}
{% endhighlight %}

We then need to bind the target method to the benchmarking interceptor in `AppModule` with a matcher. 

{% highlight php %}
<?php
use MyVendor\Weekday\Annotation\BenchMark;
use MyVendor\Weekday\Interceptor\BenchMarker;

// add the code to configure() method.
$this->bindInterceptor(
    $this->matcher->any(),                           // in any class
    $this->matcher->annotatedWith(BenchMark::class), // which annotated as @BenchMark
    [BenchMarker::class]                             // apply BenchMarker interceptor
);
{% endhighlight %}

Annotate the target method with `@BenchMark`.

{% highlight php %}
<?php
use MyVendor\Weekday\Annotation\BenchMark;

/**
 * @BenchMark
 */
public function onGet($year, $month, $day)
{
{% endhighlight %}

Now, you can benchmark any method that has the `@BenchMark` annotation.

There is no need to modify the method caller or the target method itself. Benchmarking is only invoked with the interceptor binding, so even by leaving the annotation in place you can turn benchmarking on and off by adding and removing the binding from the application.

Now check out the logging for the method invocation speed in `var/log/weekday.log`.

{% highlight bash %}
php bootstrap/api.php get '/weekday/2015/05/28'
cat var/log/weekday.log
{% endhighlight %}

## HTML

Next let's turn this API application into an HTML application. Go ahead and create a new `page` resource at `src/Resource/Page/Index.php`. Even though `page` resources and `app` resources are effectively the same class their role and location differs. 

{% highlight php %}
<?php

namespace MyVendor\Weekday\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Embed;

class Index extends ResourceObject
{
    /**
     * @Embed(rel="weekday", src="app://self/weekday{?year,month,day}")
     */
    public function onGet($year, $month, $day)
    {
        $this['year'] = $year;
        $this['month'] = $month;
        $this['day'] = $day;

        return $this;
    }
}
{% endhighlight %}

Using the `@Embed` annotation you can refer to the `app://self/weekday` resource in the `weekday` slot.

If parameters are needed to be passed, parameters that have been recieved in a resource method can then be passed by using the [RFC6570 URI template](https://github.com/ioseb/uri-template) standard such as `{?year,month,day}`.

The above page class is the same as the below page class. Here instead of using `@Embed` to include the linked resource resource, through implementing ` use ResourceInject;` a resource client is injected and another resource can be embedded.

Both methods are equally valid, however the `@Embed` declaration is concise and you can see very clearly which resources are embedded in other resources.

{% highlight php %}
<?php

namespace MyVendor\Weekday\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet($year, $month, $day)
    {
        $this['year'] = $year;
        $this['month'] = $month;
        $this['day'] = $day;
        $this['weekday'] = $this->resource
            ->get
            ->uri('app://self/weekday')
            ->withQuery(['year' => $year, 'month' => $month, 'day' => $day])
            ->request();

        return $this;
    }
}
{% endhighlight %}

At this stage let's check how this resource is rendered.

{% highlight bash %}
php bootstrap/web.php get '/?year=1991&month=8&day=1'

200 OK
Content-Type: application/hal+json

{
    "_embedded": {
        "weekday": {
            "weekday": "Thu",
            "_links": {
                "self": {
                    "href": "/weekday/1991/8/1"
                }
            }
        }
    },
    "_links": {
        "self": {
            "href": "/?year=1991&month=8&day=1"
        }
    }
}
{% endhighlight %}

We can see that the other resource has been included in the `_embedded` node.  Because there is no change to the resource renderer an `application/hal+json` media type is output. In order to output the HTML(text/html) media we need to install an HTML Module.

Composer Install
{% highlight bash %}
composer require madapaja/twig-module ~1.0
{% endhighlight %}

Create `src/Module/HtmlModule.php`.
{% highlight php %}
<?php

namespace MyVendor\Weekday\Module;

use Madapaja\TwigModule\TwigModule;
use Ray\Di\AbstractModule;

class HtmlModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TwigModule);
    }
}
{% endhighlight %}

Change `bootstrap/web.php`
{% highlight php %}
<?php

$context = 'cli-html-app';
require __DIR__ . '/bootstrap.php';
{% endhighlight %}

In this way `text/html` media output can be set. Lastly save your twig template `src/Resource/Page/Index.html.twig`.

{% highlight bash %}
<!DOCTYPE html>
<html>
<body>
{% raw %}The weekday of {{ year }}/{{ month }}/{{ day }} is {{ weekday.weekday }}.{% endraw %}
</body> 
</html>
{% endhighlight %}

Set up is now complete. Check in the console that this kind of HTML is output.

{% highlight bash %}

php bootstrap/web.php get '/?year=1991&month=8&day=1'
200 OK
content-type: text/html; charset=utf-8

<!DOCTYPE html>
<html>
<body>
The weekday of 1991/8/1 is <b>Thu</b>.
</body>
</html>
{% endhighlight %}

In order to run the web service we need make a change to `var/www/index.php`.

{% highlight php %}
<?php

$context = 'prod-html-app';
require dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';
{% endhighlight %}

Boot up the PHP web server and check it out by accessing [http://127.0.0.1:8080/?year=2001&month=1&day=1](http://127.0.0.1:8080/?year=2001&month=1&day=1).

{% highlight bash %}
php -S 127.0.0.1:8080 var/www/index.php 
{% endhighlight %}

As the context changes so does the behaviour of the application. Let's try it.

{% highlight php %}
<?php
$context = 'app';           // JSON Application
$context = 'prod-hal-app';  // HAL application for production
{% endhighlight %}

For each context php code that builds up the application is produced and saved in `var/tmp/`. These files are not normally needed, but you can use it to check how your application object is created. Using the `diff` command you can check which dependencies have changed across contexts.

{% highlight bash %}
diff -q var/tmp/app/ var/tmp/prod-hal-app/
{% endhighlight %}

## A Hypermedia API using a Database

Let's make an application resource that uses SQLite3.
First in the console we can create our database `var/db/todo.sqlite3`.

{% highlight bash %}
mkdir var/db
sqlite3 var/db/todo.sqlite3

sqlite> create table todo(id integer primary key, todo, created);
sqlite> .exit
{% endhighlight %}

For the DB there are various option that we have including [AuraSql](https://github.com/ray-di/Ray.AuraSqlModule), [Doctrine Dbal](https://github.com/ray-di/Ray.DbalModule) or [CakeDB](https://github.com/ray-di/Ray.CakeDbModule).
Let's install CakeDB that the Cake PHP framework uses.

{% highlight bash %}
composer require ray/cake-database-module ~1.0
{% endhighlight %}

In `src/Module/AppModule::configure()` we install the module.

{% highlight bash %}

use Ray\CakeDbModule\CakeDbModule;

...
$dbConfig = [
    'driver' => 'Cake\Database\Driver\Sqlite',
    'database' => dirname(dirname(__DIR__)) . '/var/db/todo.sqlite3'
];
$this->install(new CakeDbModule($dbConfig));
{% endhighlight %}

Now if we `use` the setter method trait `DatabaseInject` we have the CakeDB object available to us in `$this->db`.

Build up the `src/Resource/App/Todo.php` resource.

{% highlight php %}
<?php

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use Ray\CakeDbModule\DatabaseInject;

class Todo extends ResourceObject
{
    use DatabaseInject;

    public function onGet($id)
    {
        $this['todo'] = $this
            ->db
            ->newQuery()
            ->select('*')
            ->from('todo')
            ->where(['id' => $id])
            ->execute()
            ->fetchAll('assoc');

        return $this;
    }

    public function onPost($todo)
    {
        $statement = $this->db->insert(
            'todo',
            ['todo' => $todo, 'created' => new \DateTime('now')],
            ['created' => 'datetime']
        );
        // hyper link
        $this->headers['Location'] = '/todo/?id=' . $statement->lastInsertId();
        // status code
        $this->code = 201;

        return $this;
    }
}
{% endhighlight %}

Let's try a `POST`.

{% highlight bash %}

php bootstrap/api.php post '/todo?todo=shopping'

201 Created
Location: /todo/?id=1
{% endhighlight %}

We can see that with a `201` response, a new resource `/todo/?id=6` has been created.

Next we will do a `GET`.

{% highlight bash %}
php bootstrap/api.php get '/todo?id=1'

200 OK
content-type: application/hal+json

{
    "todo": [
        {
            "id": "1",
            "todo": "shopping",
            "created": "2015-05-03 01:58:17"
        }
    ],
    "_links": {
        "self": {
            "href": "/todo?id=1"
        }
    }
}

{% endhighlight %}

The HyperMedia API is now complete.

## Transactions

In order to implement transactional behavior on the `POST` action we use the `@Transactional` annotation. 

{% highlight bash %}

<?php

use Ray\CakeDbModule\Annotation\Transactional;
// ...

    /**
     * @Transactional
     */
    public function onPost($todo="shopping")
{% endhighlight %}

## Query Repository

A resource cache is created by annotating a resource class with `@cachable`. This cache data is created when the `onPost` action has been invoked, not only the resource properties but the HTML and JSON is also cached.

{% highlight bash %}

<?php
use BEAR\RepositoryModule\Annotation\Cacheable;
// ...

/**
 * @Cacheable
 */
class Todo extends ResourceObject
{% endhighlight %}

Let's try it. Unlike last time an `Etag` and `Last-Modified` header has been added to the response.

{% highlight bash %}
php bootstrap/api.php get '/todo?id=1'

200 OK
content-type: application/hal+json
Etag: 2105959211
Last-Modified: Sat, 02 May 2015 17:26:42 GMT


{
    "todo": [
        {
            "id": "1",
            "todo": "shopping",
            "created": "2015-05-03 01:58:17"
// ...
{% endhighlight %}

`Last-Modified` changes upon each request, but this is because currently cache settings have been disabled. When `prod` is added to the context it becomes enabled. 

On the `@Cacheable` annotation if no `expiry` is set then it will be cached forever. However when updates `onPut($id, $todo)` or deletes `onDelete($id)` occur on the resource then the cache will be updated on the corresponding id.

 So a GET request just uses the saved cache data, logic contained in the `onGet` method is not invoked.
 
 Just like this todo resource the timing of update or deletion of the cache is effective as it is completely contained within the resource itself. Invoke an `onPut` or `onDelete` method to give it a try.

## Auto-Updating the Cache via the resource method

Let's implement the `onPut` method in the `todo` resource.

{% highlight php %}
<?php

namespace MyVendor\Weekday\Resource\App;

use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\ResourceObject;
use Ray\CakeDbModule\Annotation\Transactional;
use Ray\CakeDbModule\DatabaseInject;

/**
 * @Cacheable
 */
class Todo extends ResourceObject
{
    use DatabaseInject;

    public function onGet($id)
    {
        $this['todo'] = $this
            ->db
            ->newQuery()
            ->select('*')
            ->from('todo')
            ->where(['id' => $id])
            ->execute()
            ->fetchAll('assoc');

        return $this;
    }

    /**
     * @Transactional
     */
    public function onPost($todo)
    {
        $statement = $this->db->insert(
            'todo',
            ['todo' => $todo, 'created' => new \DateTime('now')],
            ['created' => 'datetime']
        );
        // hyper link
        $this->headers['Location'] = '/todo/?id=' . $statement->lastInsertId();
        // status code
        $this->code = 201;

        return $this;
    }


    /**
     * @Transactional
     */
    public function onPut($id, $todo)
    {
        $this->db->update(
            'todo',
            ['todo' => $todo],
            ['id' => (int) $id]
        );
        $this->headers['location'] = '/todo/?id=' . $id;

        return $this;
    }
}
{% endhighlight %}

First create some data by doing a POST in the console.

{% highlight bash %}
php bootstrap/api.php post '/todo?todo=run'


201 Created
location: /todo/?id=2
content-type: application/hal+json

{% endhighlight %}

Next we start up the API server.
{% highlight bash %}
php -S 127.0.0.1:8081 bootstrap/api.php 
{% endhighlight %}

This time do a get with a `curl` command.
{% highlight bash %}
curl -i 'http://127.0.0.1:8081/todo?id=2'

HTTP/1.1 200 OK
Host: 127.0.0.1:8081
Connection: close
X-Powered-By: PHP/5.6.6
content-type: application/hal+json
ETag: 3134272297
Last-Modified: Tue, 26 May 2015 04:08:59 GMT

{
    "todo": [
        {
            "id": "2",
            "todo": "run",
            "created": "2015-05-04 03:51:50"
        }
    ],
    "_links": {
        "self": {
            "href": "/todo?id=2"
        }
    }
}
{% endhighlight %}

Make a request several times and check that the `Last-Modified` time stamp doesn't change. In this case the `onGet` method has not been run. As a test add an `echo` into the method and try again.

Next we update the resource with a `PUT`.

{% highlight bash %}
curl http://127.0.0.1:8081/todo -X PUT -d "id=2&todo=think"
{% endhighlight %}

If you would rather send a JSON body with the PUT request you can run the following.

{% highlight bash %}
curl http://127.0.0.1:8081/todo -X PUT -H 'Content-Type: application/json' -d '{"id": "2", "todo":"think" }'
{% endhighlight %} 

This time when you perform a `GET` you can see that the `Last-Modified` has been updated.

{% highlight bash %}
curl -i 'http://127.0.0.1:8081/todo?id=2'
{% endhighlight %}

This `Last-Modified` time stamp has been provided by `@Cacheable`. No need to provide any special application admin or database columns.

When you use `@Cacheable` the resource content is also saved in a separate `query repository` where along with the resources changes are managed along with `Etag` or `Last-Modified` headers beinig automatically appended.

## Application Import

Resources created with BEAR.Sunday have unrivaled re-usability. Let's try using a resource in another application.

So for this tutorial let's create a new `my-vendor` and manually add it to the auto loader. (Normally you would set up the new application as a package). 

{% highlight bash %}
mkdir my-vendor
cd my-vendor
composer create-project bear/skeleton Acme.Blog ~1.0@dev
{% endhighlight %}

In the `composer.json` in the `autoload` section add `Acme\\Blog`.
{% highlight bash %}

"autoload": {
    "psr-4": {
        "MyVendor\\Weekday\\": "src/",
        "Acme\\Blog\\": "my-vendor/Acme.Blog/src/"
    }
},
{% endhighlight %}

Dump the `autoload`.

{% highlight bash %}
composer dump-autoload
{% endhighlight %}

With this the configuration for the `Acme\Blog` application is complete.

Next in order to import the application in `src/Module/AppModule.php` we use the `ImportAppModule` in `src/Module/AppModule.php` to install as an override. 

{% highlight php %}
<?php
use BEAR\Resource\Module\ImportAppModule;
use BEAR\Resource\ImportApp;
use BEAR\Package\Context;

$importConfig = [
    new ImportApp('blog', 'Acme\Blog', 'prod-hal-app') // host, name, context 
];
$this->override(new ImportAppModule($importConfig , Context::class));
{% endhighlight %}
With this a Acme\Blog` application using a `prod-hal-app` context can create resources that will be available to the `blog` host.

Let's check it works by creating an Import resource in `src/Resource/App/Import.php`.

{% highlight php %}
<?php

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Import extends ResourceObject
{
    use ResourceInject;

    public function onGet()
    {
        $this['blog'] = $this->resource->get->uri('page://blog/index')->eager->request()->body['greeting'];
        
        return $this;
    }
}
{% endhighlight %}

The `page://blog/index` resource should now be assigned to `blog`. `@Embed` can be used in the same way.


{% highlight bash %}
php bootstrap/api.php get /import
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
{% endhighlight %}

Great, we could now use another application's resource. We do not even need to use HTTP to fetch this data.

The combined application is now seen as 1 layer of a single application. A
[Layered System](http://en.wikipedia.org/wiki/Representational_state_transfer#Layered_system) is another feature of REST.

Next lets look at how we use a resource in a system that is not BEAR.Sunday based. We create an app.php. You can place this anywhere but be careful that it picks up `autoload.php` path correctly.

{% highlight php %}
<?php

use BEAR\Package\Bootstrap;
use Doctrine\Common\Annotations\AnnotationRegistry;

/** @var $loader \Composer\Autoload\ClassLoader */
$loader = require __DIR__ . '/vendor/autoload.php';
AnnotationRegistry::registerLoader([$loader, 'loadClass']); // Annotation load

$app = (new Bootstrap)->getApp('MyVendor\Weekday', 'prod-hal-app'); // Fetch the application
$import = $app->resource->get->uri('app://self/import')->request(); 

echo $import['blog'] . PHP_EOL;

{% endhighlight %}

Let's try it..
{% highlight bash %}
php app.php
{% endhighlight %}
Does it display `Hello BEAR.Sunday`?

## Because everything is a resource

Unique data identifier URIs, a consistent interface, stateless access, powerful caching system, hyperlinks, layered system, and self-descriptive messages. A resource built with BEAR.Sunday implements all of these REST features.

You can connect to data from other applications using hyperlinks, creating an API to be consumed from another CMS or framework is easy. The resource object is completely decoupled from any rendering! 

