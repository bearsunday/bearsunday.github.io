---
layout: docs-en
title: 1 Page Manual
category: Manual
permalink: /manuals/1.0/en/1page.html
---
This page collects all BEAR.Sunday manuals in one place.



# What is BEAR.Sunday?

BEAR.Sunday is a PHP application framework that combines clean object-oriented design with a resource-oriented architecture aligned with the fundamental principles of the web. This framework emphasizes compliance with standards, a long-term perspective, high efficiency, flexibility, self-description, and importantly, simplicity.

## Framework

BEAR.Sunday consists of three frameworks.

`Ray.Di` interfaces object dependencies based on the [Principle of Dependency Inversion](http://en.wikipedia.org/wiki/Dependency_inversion_principle).

`Ray.Aop` connects core concerns and cross-cutting concerns with [aspect-oriented programming](http://en.wikipedia.org/wiki/Aspect-oriented_programming).

`BEAR.Resource` connects application data and functionality with resources with [REST constraints](https://en.wikipedia.org/wiki/Representational_state_transfer).

The framework provides constraints and design principles that guide the entire application, promoting consistent design and implementation, and resulting in high-quality, clean code.

## Libraries

Unlike full-stack frameworks, BEAR.Sunday does not include its own libraries for specific tasks like authentication or database management. Instead, it favors the use of high-quality third-party libraries.

This approach is based on two key design philosophies: firstly, the belief that "frameworks remain, libraries change," acknowledging that while the framework provides a stable foundation, libraries evolve to meet changing needs over time. Secondly, it empowers "application architects with the right and responsibility to choose libraries" that best fit their application's requirements, constraints, and goals.

BEAR.Sunday draws a clear distinction between frameworks and libraries, emphasizing the role of the framework as an application constraint.

## Architecture

BEAR.Sunday departs from the traditional MVC (Model-View-Controller) architecture, embracing a resource-oriented architecture (ROA). In this paradigm, data and business logic are unified as resources, and the design revolves around links and operations on those resources. While ROA is commonly used for REST API design, BEAR.Sunday extends it to the entire web application.

## Long-term perspective

BEAR.Sunday is designed with a long-term view, focusing on application maintainability:

- **Constraints**: The consistent application constraints imposed by DI, AOP, and REST remain unchanged over time.

- **Eternal 1.x**:The System That Never Breaks Backward Compatibility. Since its initial release in 2015, BEAR.Sunday has continuously evolved without introducing any backward-incompatible changes. This steadfast approach eliminates the need for compatibility fixes and their associated testing, thereby preventing future technical debt. The system remains cutting-edge, ensuring easy upgrades and access to the latest features without compatibility concerns.

- **Standards Compliance**: BEAR.Sunday adheres to various standards, including HTTP, JsonSchema, and others. For DI, it follows Google Guice, and for AOP, it aligns with the Java Aop Alliance.

## Connectivity

BEAR.Sunday transcends traditional web applications, offering seamless integration with a diverse range of clients:

- **HTTP Client**: All resources are directly accessible via HTTP, unlike models or controllers in MVC.

- **composer package**: Resources from applications installed under the vendor directory via Composer can be invoked directly, enabling coordination between multiple applications without resorting to microservices.

- **Multilingual framework**: BEAR.Thrift facilitates seamless and efficient interoperability with other languages and PHP versions.

## Web Cache

By integrating resource-oriented architecture with modern CDN technology, we achieve distributed caching that surpasses traditional server-side TTL caching. BEAR.Sunday's design philosophy adheres to the fundamental principles of the Web, utilizing a CDN-centered distributed caching system to ensure high performance and availability.

- **Distributed Caching**: By caching on the client, CDN, and server-side, both CPU and network costs are minimized.

- **Identification**: ETag-based verification ensures that only modified content is retrieved, enhancing network efficiency.

- **Fault tolerance**: Event-based cache invalidation allows all content to be stored in CDN caches without TTL limitations. This improves fault tolerance to the point where the system remains available even if the PHP or database servers go down.


## Performance

BEAR.Sunday is designed with a focus on performance and efficiency while maintaining maximum flexibility. This approach enables a highly optimized bootstrap, positively impacting both user experience and system resources. Performance is always one of the primary concerns for BEAR.Sunday, playing a central role in our design and development decisions.

## Because Everything is a Resource

BEAR.Sunday embraces the essence of the Web, where "Everything is a Resource." As a PHP web application framework, it excels by providing superior constraints based on object-oriented and REST principles, applicable to the entire application.

These constraints encourage developers to design and implement consistently and improve the quality of the application in the long run. At the same time, the constraints provide developers with freedom and enhance creativity in building the application.


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



# Resource

A BEAR.Sunday application is [RESTful](http://en.wikipedia.org/wiki/Representational_state_transfer) and is made up of a collection of resources connected by links.

## Object as a service

An HTTP method is mapped to a PHP method in the `ResourceObject` class.
It transfers its resource state as a resource representation from stateless request.
([Representational State Transfer)](http://en.wikipedia.org/wiki/REST)

Here are some examples of a resource object:

```php?start_inline
class Index extends ResourceObject
{
    public $code = 200;
    public $headers = [];

    public function onGet(int $a, int $b): static
    {
        $this->body = [
            'sum' => $a + $b // $_GET['a'] + $_GET['b']
        ] ;

        return $this;
    }
}
```

```php?start_inline
class Todo extends ResourceObject
{
    public function onPost(string $id, string $todo): static
    {
        $this->code = 201; // status code
        $this->headers = [ // header
            'Location' => '/todo/new_id'
        ];

        return $this;
    }
}
```

The PHP resource class has URIs such as  `page://self/index` similar to the URI of the web, and conforms to the HTTP method `onGet`,` onPost`, `onPut`,` onPatch`, `onDelete` interface.

$_GET for `onGet` and $_POST for `onPost` are passed to the arguments of the method depending on the variable name, and the methods of `onPut`,` onPatch`, `onDelete` are content. The value that can be handled according to `content-type`(`x-www-form-urlencoded` or `application/json`) is an argument.

The resource state (`code`,`headers` or`body`) is handled by these method using the given parameters. Then the resource class returns itself(`$this`).

## URI

URIs are mapped to PHP classes. Applications use the URI instead of the class name to access resources.

| URI | Class |
|--+-|
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts | Koriym\Todo\Resource\App\Blog\Posts |

## Method

Resources have 6 interfaces conforming to HTTP methods.[^method]

[^method]: REST methods are not a mapping to CRUD. They are divided into two categories: safe ones that do not change the resource state, or idempotent ones.

### GET
Reads resources. This method does not provide any changing of the resource state. A safe method with no possible side affects.

### POST
The POST method requests processing of the representation contained in the request. For example, adding a new resource to a target URI or adding a representation to an existing resource. Unlike PUT, requests do not have [idempotence](https://ja.wikipedia.org/wiki/%E5%86%AA%E7%AD%89), and multiple consecutive executions will not produce the same result.

### PUT
Replaces the resource with the payload of the request at the requested URI. If the target resource does not exist, it is created. Unlike POST, there is not idempotent.

### PATCH

Performs resource updates, but unlike PUT, it applies a delta rather than replacing the entire resource.


### DELETE
Resource deletion. Has idempotence just like PUT.

### OPTIONS
Get information on parameters and responses required for resource request. It is as secure as GET method.

#### List of method properties

| Methods | [Safe](https://developer.mozilla.org/en-US/docs/Glossary/Safe/HTTP) | [Idempotent](https://developer.mozilla.org/en-US/docs/Glossary/Idempotent) | [Cacheable](https://developer.mozilla.org/en-US/docs/Glossary/cacheable) 
|-|-|-|-|-
| GET | Yes | Yes | Yes
| POST | No | No | No
| PUT | No | Yes | No
| PATCH | No | Yes | No
| DELETE | No | Yes | No
| OPTIONS | Yes | Yes | No

## Parameters

The response method argument is passed the request value corresponding to the variable name.

```php?start_inline
class Index extends ResourceObject
{
    // $_GET['id'] to $id
    public function onGet(int $id): static
    {
    }

    // $_POST['name'] to $name
    public function onPost(string $name): static
    {
    }
```

See [Resource Parameters](resource_param.html) for other methods and how to pass external variables such as cookies as parameters.

## Rendering and transfer

The request method of a ResourceObject is not concerned with the representation of the resource. The injected renderer generates the representation of the resource and the responder outputs it. See [Rendering and Transferring](resource_renderer.html) for details.

## Client

Use the resource client to request other resources. This request executes a request to the `app://self/blog/posts` resource with the query `?id=1`.

```php?start_inline
use BEAR\Sunday\Inject\ResourceInject;

class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(): static
    {
        $this->body = [
            'posts' => $this->resource->get('app://self/blog/posts', ['id' => 1])
        ];
    }
}
```

Other historical notations include the following

```php?start_inline
// PHP 5.x and up
$posts = $this->resource->get->uri('app://self/posts')->withQuery(['id' => 1])->eager->request();
// PHP 7.x and up
$posts = $this->resource->get->uri('app://self/posts')(['id' => 1]);
// you can omit `get`
$posts = $this->resource->uri('app://self/posts')(['id' => 1]);
// bear/resource 1.11 and up
$posts = $this->resource->get('app://self/posts', ['id' => 1]);
```

## Lazy evaluation

The above is an `eager` request that makes the request immediately, but it is also possible to generate a request and delay execution instead of the request result.

```php
$request = $this->resource->get('app://self/posts'); // callable
$posts = $request(['id' => 1]);
```

When this request is embedded in a template or resource, it is evaluated lazily. That is, when it is not evaluated, the request is not made and has no execution cost.

```php
$this->body = [
    'lazy' => $this->resource->get('app://self/posts')->withQuery(['id' => 3])->requrest();
];
```

## Cache

Along with regular TTL caching, we support REST client caching and advanced partial caching (doughnut caching), including CDN. See [cache](cache.html) for details. Also see the previous [resource(v1)](resourcev1.html#Resource Cache) document for the previous `@Cacheable` annotation.

## Link

One important REST constraint is resource linking; ResourceObject supports both internal and external linking. See [Resource Linking](resource_link.html) for details.

## BEAR.Resource

The functionality of the BEAR.Sunday resource object is also available in a stand-alone package for stand-alone use: BEAR.Resource [README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.ja.md).

---



# Resource Parameters

## Basics

Web runtime values such as HTTP requests and cookies that require ResourceObjects are passed directly to the method arguments.

For requests from HTTP, the arguments of the `onGet` and `onPost` methods are passed `$_GET` and `$_POST`, respectively, depending on the variable name. For example, `$id` in the following is passed `$_GET['id']`. Arguments passed as strings when the input is HTTP will be casted to the specified type.


```php?start_inline
class Index extends ResourceObject
{
    public function onGet(int $id): static
    {
        // ....
```

## Parameter type

### Scalar parameters

All parameters passed via HTTP are strings, but if you specify a non-string type such as `int`, it will be cast.

### Array parameters

Parameters can be nested data [^2]; data sent as JSON or nested query strings can be received as arrays.

[^2]:[parse_str](https://www.php.net/manual/ja/function.parse-str.php)参照

```php?start_inline
class Index extends ResourceObject
{
    public function onPost(array $user):static
    {
        $name = $user['name']; // bear
```

### Class Parameters

Parameters can also be received in a dedicated Input class.

```php?start_inline
class Index extends ResourceObject
{
    public function onPost(User $user): static
    {
        $name = $user->name; // bear
```

The Input class is defined in advance with parameters as public properties.

```php?start_inline
<?php

namespace Vendor\App\Input;

final class User
{
    public int $id;
    public string $name;
}
```

At this time, if there is a constructor, it will be called. [^php8]

[^php8]: This is called with named arguments in PHP8.x, but with ordinal arguments in PHP7.x.

```php?start_inline
<?php

namespace Vendor\App\Input;

final class User
{
    public function __constrcut(
        public readonly int $id,
        public readonly string $name
    } {}
}
```

The Input class can implement methods to summarize and validate input data.

### Enum parameters

You can specify an [enumerated type](https://www.php.net/manual/en/language.types.enumerations.php) in PHP8.1 to limit the possible values.

```php
enum IceCreamId: int
{
    case VANILLA = 1;
    case PISTACHIO = 2;
}
```

```php
class Index extends ResourceObject
{
    public function onGet(IceCreamId $iceCreamId): static
    {
        $id = $iceCreamId->value // 1 or 2
```

In the above case, if anything other than 1 or 2 is passed, a `ParameterInvalidEnumException` will be raised.

## Web context binding

PHP superglobals such as `$_GET` and `$_COOKIE` can be bound to method arguments instead of being retrieved in the method.

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;

class News extends ResourceObject
{
    public function foo(
    	  #[QueryParam('id')] string $id
    ): static {
       // $id = $_GET['id'];
```

Others can be done by binding the values of `$_ENV`, `$_POST`, and `$_SERVER`.

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

class News extends ResourceObject
{
    public function onGet(
        #[QueryParam('id')] string $userId,            // $_GET['id'];
        #[CookieParam('id')] string $tokenId = "0000", // $_COOKIE['id'] or "0000" when unset;
        #[EnvParam('app_mode')] string $app_mode,      // $_ENV['app_mode'];
        #[FormParam('token')] string $token,           // $_POST['token'];
        #[ServerParam('SERVER_NAME') string $server    // $_SERVER['SERVER_NAME'];
    ): static {
```

When the client specifies a value, the specified value takes precedence and the bound value is invalid. This is useful for testing.

## Resource Binding

The `#[ResourceParam]` annotation can be used to bind the results of other resource requests to the method argument.

```php?start_inline
use BEAR\Resource\Annotation\ResourceParam;

class News extends ResourceObject
{
    public function onGet(
        #[ResourceParam('app://self//login#nickname') string $name
    ): static {
```

In this example, when the method is called, it makes a `get` request to the `login` resource and receives `$body['nickname']` with `$name`.

## Content negotiation

The `content-type` header of HTTP requests is supported. The `application/json` and `x-www-form-urlencoded` media types are determined and values are passed to the parameters. [^json].

[^json]:Set the `content-type` header to `application/json` if you are sending API requests in JSON.




## Best Practices

In REST, resources are connected to other resources. Good use of links makes code concise, easy to read, test and change.

### #[Embed].

Embed a resource with `#[Embed]` instead of `get` the state of another resource.

```php?start_inline
// OK but not the best
class Index extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )

    public function onGet(string $status): static
    {
        $this->body = [
            'todos' => $this->resource->uri('app://self/todos')(['status' => $status]) // lazy request
        ];

        return $this;
    }
}

// Better
class Index extends ResourceObject
{
    #[@Embed(rel: 'todos', src: 'app://self/todos{?status}')]
    public function onGet(string $status): static
    {
        return $this;
    }
}
```

### #[Link]

The next action indicated by `#[Link]` when changing the state of another resource is traced using `href()` (hyper reference).

```php?start_inline
// OK but not the best
class Todo extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )

    public function onPost(string $title): static
    {
        $this->resource->post('app://self/todo', ['title' => $title]);
        $this->code = 301;
        $this->headers[ResponseHeader::LOCATION] = '/';

        return $this;
    }
}

// Better
class Todo extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )

    #[Link(rel: 'create', href: 'app://self/todo', method: 'post')]
    public function onPost(string $title): static
    {
        $this->resource->href('create', ['title' => $title]);
        $this->code = 301;
        $this->headers[ResponseHeader::LOCATION] = '/';

        return $this;
    }
}
```

### #[ResourceParam]

Use `#[ResourceParam]` if you need other resource results to request other resources.

```php?start_inline
// OK but not the best
class User extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )

    public function onGet(string $id): static
    {
        $nickname = $this->resource->get('app://self/login-user', ['id' => $id])->body['nickname'];
        $this->body = [
            'profile'=> $this->resource->get('app://self/profile', ['name' => $nickname])->body
        ];

        return $this;
    }
}

// Better
class User extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )

    #[ResourceParam(param: 'name', uri: 'app://self//login-user#nickname')]
    public function onGet(string $id, string $name): static
    {
        $this->body = [
            'profile' => $this->resource->get('app://self/profile', ['name' => $name])->body
        ];

        return $this;
    }
}

// Best
class User extends ResourceObject
{
    #[ResourceParam(param: 'name', uri: 'app://self//login-user#nickname')]
    #[Embed(rel: 'profile', src: 'app://self/profile')]
    public function onGet(string $id, string $name): static
    {
        $this->body['profile']->addQuery(['name'=>$name]);

        return $this;
    }
}
```



# Reousrce link

Resources can be linked to other resources. There are two types of links: external links [^LO], which link external resources, and internal links [^LE], which embed other resources in the resource itself.

[^LE]: [embedded links](http://amundsen.com/hypermedia/hfactor/#le) Example: html can embed independent image resources.
[^LO]: [out-bound links](http://amundsen.com/hypermedia/hfactor/#le) e.g.) html can link to other related html.

## Out-bound links

Specify links by `rel` (relation) and `href` of the link name. The `href` can be a regular URI or [RFC6570 URI template](https://github.com/ioseb/uri-template).

```php?start_inline
    #[Link rel: 'profile', href: '/profile{?id}']
    public function onGet($id): static
    {
        $this->body = [
            'id' => 10
        ];

        return $this;
    }
```

In the above example, `href` is represented by and `$body['id']` is assigned to `{?id}`. The output in [HAL](https://stateless.group/hal_specification.html) format is as follows

```json
{
    "id": 10,
    "_links": {
        "self": {
            "href": "/test"
        },
        "profile": {
            "href": "/profile?id=10"
        }
    }
}
```


## Internal links

A resource can embed another resource. Specify the resource in the `src` of `#[Embed]`.

Internally linked resources may also internally link other resources. In that case, another internally linked resource is needed, and the process is repeated recursively to obtain a **resource graph**. The client can retrieve the desired set of resources at once without having to fetch the resources multiple times. [^di] For example, instead of calling a customer resource and a product resource respectively, embed them both in an order resource.

[^di]:This is similar to an object graph where the dependency tree is a graph in DI.

```php?start_inline
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    #[Embed(rel: 'sports', src: '/news/sports')]
    #[Embed(rel: 'weather', src: '/news/weather')]
    public function onGet(): static
```

It is the resource **request** that is embedded. It is executed at rendering time, but before that you can add arguments with the `addQuery()` method or replace them with `withQuery()`.

A URI template can be used for the `src`, and **request method arguments** will be bound to it. (Unlike external links, it is not `$body`)

```php?start_inline
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    #[Embed(rel: 'website', src: '/website{?id}']
    public function onGet(string $id): static
    {
        // ...
        $this->body['website']->addQuery(['title' => $title]); // 引数追加
```

### Self linking

Linking a relation as `_self` in ``#[Embed]`` copies the linked resource state to its own resource state.

```php
namespace MyVendor\Weekday\ResourcePage;.

class Weekday extends ResourceObject
{
#[Embed(rel: '_self', src: 'app://self/weekday{?year,month,day}'])
public function onGet(string $id): static
{
```

In this example, the Page resource copies the state of the `weekday` resource of the App resource to itself.

### Internal links in HAL

Handled as `_embedded ` in the [HAL](https://github.com/blongden/hal) renderer.

## Link request

Clients can link resources connected by hyperlinks.

```php?start_inline
$blog = $this
    ->resource
    ->get
    ->uri('app://self/user')
    ->withQuery(['id' => 1])
    ->linkSelf("blog")
    ->eager
    ->request()
    ->body;
```

There are three types of links. The `body` linked resource of the original resource is embedded using `$rel` as the key.

* `linkSelf($rel)` which will be replaced with the link destination.
* `linkNew($rel)` the linked resource is added to the original resource
* `linkCrawl($rel)` crawl the link and create a resource graph.

## crawl

Crawls are lists (arrays) of resources, and links can be traversed in sequence to compose complex resource graphs.
Just as a crawler crawls a web page, the resource client crawls hyperlinks and generates a source graph.

#### Crawl Example

Consider a resource graph with author, post, meta, tag, and tag/name associated with each.
Name this resource graph **post-tree** and specify a hyperreference **href** in the `#[Link]' attribute of each resource.



The first starting point, the author resource, has a hyperlink to the post resource. 1:n relationship.

```php
#[Link(crawl: "post-tree", rel: "post", href: "app://self/post?author_id={id}")]
public function onGet($id = null)
```

The post resource has hyperlinks to the meta and tag resources. 1:n relationship.

```php
#[Link(crawl: "post-tree", rel: "meta", href: "app://self/meta?post_id={id}")]
#[Link(crawl: "post-tree", rel: "tag", href: "app://self/tag?post_id={id}")]
public function onGet($author_id)
{
```

A tag resource is just an ID with a hyperlink to the corresponding tag/name resource. 1:1 relationship.

```php
#[Link(crawl:"post-tree", rel:"tag_name", href:"app://self/tag/name?tag_id={tag_id}")]
public function onGet($post_id)
```

Each is now connected. Request with a crawl name.

```php
$graph = $resource
  ->get
  ->uri('app://self/marshal/author')
  ->linkCrawl('post-tree')
  ->eager
  ->request();
```

When a resource client finds a crawl name specified in the #[Link] attribute, it creates a resource graph by connecting resources by their **rel** names.

```
var_export($graph->body);

array (
    0 =>
    array (
        'name' => 'Athos',
        'post' =>
        array (
            0 =>
            array (
                'author_id' => '1',
                'body' => 'Anna post #1',
                'meta' =>
                array (
                    0 =>
                    array (
                        'data' => 'meta 1',
                    ),
                ),
                'tag' =>
                array (
                    0 =>
                    array (
                        'tag_name' =>
                        array (
                            0 =>
                            array (
                                'name' => 'zim',
                            ),
                        ),
                    ),
 ...
```



# Rendering and transfer

The request method of a ResourceObject is not concerned with the representation of the resource. The context-sensitive injected renderer generates the representation of the resource. The same application can be output in HTML or JSON and benefit by simply changing the context.

## Lazy evaluation

Rendering occurs when the resource is string-evaluated.

```php?start_inline

$weekday = $api->resource->get('app://self/weekday', ['year' => 2000, 'month'=> 1, 'day'=> 1]);
var_dump($weekday->body);
//array(1) {
//    ["weekday"]=>
//  string(3) "Sat"
//}

echo $weekday;
//{
//    "weekday": "Sat",
//    "_links": {
//    "self": {
//        "href": "/weekday/2000/1/1"
//        }
//    }
//}
```

## Renderer

Each ResourceObject is injected with a renderer for its representation as specified by its context. When performing resource-specific rendering, inject or set the `renderer` property.

Example: If you write a renderer for the default JSON representation from scratch

```php?start_inline
class Index extends ResourceObject
{
    #[Inject]
    public function setRenderer(RenderInterface $renderer)
    {
        $this->renderer = new class implements RenderInterface {
            public function render(ResourceObject $ro)
            {
                $ro->headers['content-type'] = 'application/json;';
                $ro->view = json_encode($ro->body);

                return $ro->view;
            }
        };
    }
}
```

## Transfer

Transfers the resource representation injected into the root object `$app` to the client (console or web client). Normally, output is done with the `header` function or `echo`, but for large data, etc., [stream transfer](stream.html) is useful.

Override the `transfer` method to perform resource-specific transfers.

```php
public function transfer(TransferInterface $responder, array $server)
{
    $responder($this, $server);
}
```

## Resource autonomy

Each resource class has the ability to change its own resource state upon request and transfer it as an expression.


# Technology

The distinctive technologies and features of BEAR.Sunday are explained in the following chapters. 

* [Architecture and Design Principles](#architecture-and-design-principles)
* [Performance and Scalability](#performance-and-scalability)
* [Developer Experience](#developer-experience)
* [Extensibility and Integration](#extensibility-and-integration)
* [Design Philosophy and Quality](#design-philosophy-and-quality)
* [The Value BEAR.Sunday Brings](#the-value-bearsunday-brings)

## Architecture and Design Principles

### Resource Oriented Architecture (ROA)

BEAR.Sunday's ROA is an architecture that realizes RESTful API within a web application. It is the core of BEAR.Sunday's design principles, functioning as both a hypermedia framework and a service-oriented architecture. Similar to the Web, all data and functions are considered resources and are operated through standardized interfaces such as GET, POST, PUT, and DELETE.

#### URI

URI (Uniform Resource Identifier) is a key element to the success of the Web and is also at the heart of BEAR.Sunday's ROA. By assigning URIs to all resources handled by the application, resources can be easily identified and accessed. URIs not only function as identifiers for resources but also express links between resources.

#### Uniform Interface

Access to resources is done using HTTP methods such as GET, POST, PUT, and DELETE. These methods specify the operations that can be performed on resources and provide a common interface regardless of the type of resource.

#### Hypermedia

In BEAR.Sunday's Resource Oriented Architecture (ROA), each resource provides affordances (available operations and functions for the client) through hyperlinks. These links represent the operations that clients can perform and guide navigation within the application.

#### Separation of State and Representation

In BEAR.Sunday's ROA, the state of a resource and its representation are clearly separated. The state of the resource is managed by the resource class, and the renderer injected into the resource converts the state of the resource into a resource state representation in various formats (JSON, HTML, etc.). Domain logic and presentation logic are loosely coupled, and even with the same code, changing the binding of the state representation based on the context will also change the representation.

#### Differences from MVC

BEAR.Sunday's ROA (Resource Oriented Architecture) takes a different approach from the traditional MVC architecture. MVC composes an application with three components: model, view, and controller. The controller receives a request object, controls a series of processes, and returns a response. In contrast, a resource in BEAR.Sunday, following the Single Responsibility Principle (SRP), only specifies the state of the resource in the request method and is not involved in the representation.

While there are no constraints on the relationship between controllers and models in MVC, resources have explicit constraints on including other resources using hyperlinks and URIs. This allows for declarative definition of content inclusion relationships and tree structures while maintaining information hiding of the called resources.

MVC controllers manually retrieve values from the request object, while resources declaratively define the required variables as arguments to the request method. Therefore, input validation is also performed declaratively using JsonSchema, and the arguments and their constraints are documented.

### Dependency Injection (DI)

Dependency Injection (DI) is an important technique for enhancing the design and structure of applications in object-oriented programming. The central purpose of DI is to divide an application's responsibilities into multiple components with independent domains or roles and manage the dependencies between them.

DI helps to horizontally divide one responsibility into multiple functions. The divided functions can be developed and tested independently as "dependencies". By injecting those dependencies with clear responsibilities and roles based on the single responsibility principle from the outside, the reusability and testability of objects are improved. Dependencies can also be vertically divided into other dependencies, forming a tree of dependencies.

BEAR.Sunday's DI uses a separate package called [Ray.Di](https://github.com/ray-di/Ray.Di), which adopts the design philosophy of Google's DI framework Guice and covers almost all of its features.

It also has the following characteristics:

* Bindings can be changed by context, allowing different implementations to be injected during testing.
* Attribute-based configuration enhances the self-descriptiveness of the code.
* Ray.Di performs dependency resolution at compile-time, improving runtime performance. This is different from other DI containers that resolve dependencies at runtime.
* Object dependencies can be visualized as a graph. Example: [Root Object](/images/app.svg).

<img src="https://ray-di.github.io/images/logo.svg" width="180" alt="Ray.Di logo">

### Aspect Oriented Programming (AOP)

Aspect-Oriented Programming (AOP) is a pattern that realizes flexible applications by separating essential concerns such as business logic from cross-cutting concerns such as logging and caching. Cross-cutting concerns refer to functions or processes that span across multiple modules or layers. It is possible to bind cross-cutting processes based on search conditions and flexibly configure them based on context.

BEAR.Sunday's AOP uses a separate package called Ray.Aop, which declaratively binds cross-cutting processes by attaching PHP attributes to classes and methods. Ray.Aop conforms to Java's [AOP Alliance](https://aopalliance.sourceforge.net/).

AOP is often misunderstood as a technology that "has the strong power to break the existing order". However, its raison d'être is not to exercise power beyond constraints but to complement areas where object-orientation is not well-suited, such as exploratory assignment of functions using matchers and separation of cross-cutting processes. AOP is a paradigm that can create cross-cutting constraints for applications, in other words, it functions as an application framework.

## Performance and Scalability

### ROA-based Event-Driven Content Strategy with Modern CDN Integration

BEAR.Sunday realizes an advanced event-driven caching strategy by integrating with instant purge-capable CDNs such as Fastly, with Resource Oriented Architecture (ROA) at its core. Instead of invalidating caches based on the conventional TTL (Time to Live), this strategy immediately invalidates the CDN and server-side caches, as well as ETags (entity tags), in response to resource state change events.

By taking this approach of creating non-volatile and persistent content on CDNs, it not only avoids SPOF (Single Point of Failure) and achieves high availability and fault tolerance but also maximizes user experience and cost efficiency. It realizes the same distributed caching as static content for dynamic content, which is the original principle of the Web. It re-realizes the scalable and network cost-reducing distributed caching principle that the Web has had since the 1990s with modern technology.

#### Cache Invalidation by Semantic Methods and Dependencies

In BEAR.Sunday's ROA, each resource operation is given a semantic role. For example, the GET method retrieves a resource, and the PUT method updates a resource. These methods collaborate in an event-driven manner and efficiently invalidate related caches. For instance, when a specific resource is updated, the cache of resources that require that resource is invalidated. This ensures data consistency and freshness, providing users with the latest information.

#### Identity Confirmation and Fast Response with ETag

By setting ETags before the system boots, content identity can be quickly confirmed, and if there are no changes, a 304 Not Modified response is returned to minimize network load.

#### Partial Updates with Donut Caching and ESI

BEAR.Sunday adopts a donut caching strategy and uses ESI (Edge Side Includes) to enable partial content updates at the CDN edge. This technology allows for dynamic updates of only the necessary parts without re-caching the entire page, improving caching efficiency.

In this way, BEAR.Sunday and Fastly's integration of ROA-based caching strategy not only realizes advanced distributed caching but also enhances application performance and fault tolerance.

### Accelerated Startup

In the original world of DI, users avoid dealing directly with the injector (DI container) as much as possible. Instead, they generate a single root object at the application's entry point to start the application. In BEAR.Sunday's DI, there is virtually no DI container manipulation even at configuration time. The root object is huge but is a single variable, so it is reused beyond requests, realizing an optimized bootstrap to the limit.

## Developer Experience

### Ease of Testing

BEAR.Sunday allows for easy and effective testing due to the following design features:

* Each resource is independent, and testing is easy due to the stateless nature of REST requests.
  Since the state and representation of resources are clearly separated, it is possible to test the state of resources even when they are in HTML representation.
* API testing can be performed while following hypermedia links, and tests can be written in the same code for PHP and HTTP.
* Different implementations are bound during testing through context-based binding.

### API Documentation Generation

API documentation is automatically generated from the code. It maintains consistency between code and documentation and improves maintainability.

### Visualization and Debugging

Utilizing the technical feature of resources rendering themselves, during development, the scope of resources can be indicated on HTML, resource states can be monitored, and PHP code and HTML templates can be edited in an online editor and reflected in real-time.

## Extensibility and Integration

### Integration of PHP Interfaces and SQL Execution

In BEAR.Sunday, the execution of SQL statements for interacting with databases can be easily managed through PHP interfaces. It is possible to directly bind SQL execution objects to PHP interfaces without implementing classes. The boundary between the domain and infrastructure is connected by PHP interfaces.

In that case, types can also be specified for arguments, and any missing parts are dependency-resolved by DI and used as strings. Even when the current time is needed for SQL execution, there is no need to pass it; it is automatically bound. This helps keep the code concise as the client is not responsible for passing all arguments.

Moreover, direct management of SQL makes debugging easier when errors occur. The behavior of SQL queries can be directly observed, allowing for quick identification and correction of problems.

### Integration with Other Systems

Integration with console applications allows access from both the web and the command line without changing the source code. Also, by being able to concurrently execute different BEAR.Sunday applications within the same PHP runtime, multiple independent applications can be coordinated without building microservices.

### Stream Output

By assigning streams such as file pointers to the body of a resource, large-scale content that cannot be handled in memory can be output. In that case, streams can also be mixed with ordinary variables, allowing flexible output of large-scale responses.

### Gradual Migration from Other Systems

BEAR.Sunday provides a gradual migration path and enables seamless integration with other frameworks and systems such as Laravel and Symfony. This framework can be implemented as a Composer package, allowing developers to gradually introduce BEAR.Sunday's features into their existing codebase.

### Flexibility in Technology Migration

BEAR.Sunday protects investments in preparation for future technological changes and evolving requirements. Even if there is a need to migrate from this framework to another framework or language, the constructed resources will not go to waste. In a PHP environment, BEAR.Sunday applications can be integrated as Composer packages and continuously utilized, and BEAR.Thrift allows efficient access to BEAR.Sunday resources from other languages. When not using Thrift, access via HTTP is also possible. SQL code can also be easily reused.

Even if the library being used is strongly dependent on a specific PHP version, different versions of PHP can coexist using BEAR.Thrift.

## Design Philosophy and Quality

### Adoption of Standard Technologies and Elimination of Proprietary Standards

BEAR.Sunday has a design philosophy of adopting standard technologies as much as possible and eliminating framework-specific standards and rules. For example, it supports content negotiation for JSON format and www-form format HTTP requests by default and uses the [vnd.error+json](https://github.com/blongden/vnd.error) media type format for error responses. It actively incorporates standard technologies and specifications such as adopting [HAL](https://datatracker.ietf.org/doc/html/draft-kelly-json-hal) (Hypertext Application Language) for links between resources and using [JsonSchema](https://json-schema.org/) for validation.

On the other hand, it eliminates proprietary validation rules and framework-specific standards and rules as much as possible.

### Object-Oriented Principles

BEAR.Sunday emphasizes object-oriented principles to make applications maintainable in the long term.

#### Composition over Inheritance

Composition is recommended over inheritance classes. Generally, directly calling a parent class's method from a child class can potentially increase the coupling between classes. The only abstract class that requires inheritance at runtime by design is the resource class `BEAR\Resource\ResourceObject`, but the methods of ResourceObject exist solely for other classes to use. There is no case in BEAR.Sunday where a user calls a method of a framework's parent class that they have inherited at runtime.

#### Everything is Injected

Framework classes do not refer to "configuration files" or "debug constants" during execution to determine their behavior. Dependencies corresponding to the behavior are injected. This means that to change the application's behavior, there is no need to change the code; only the binding of the implementation of the dependency to the interface needs to be changed. Constants like APP_DEBUG or APP_MODE do not exist. There is no way to know in what mode the software is currently running after it has started, and there is no need to know.

### Permanent Assurance of Backward Compatibility

BEAR.Sunday is designed with an emphasis on maintaining backward compatibility in the evolution of software and has continued to evolve without breaking backward compatibility since its release. In modern software development, frequent breaking of backward compatibility and the associated burden of modification and testing have become a challenge, but BEAR.Sunday has avoided this problem.

BEAR.Sunday not only adopts semantic versioning but also does not perform major version upgrades that involve breaking changes. It prevents new feature additions or changes to existing features from affecting existing code. Code that has become old and unused is given the attribute "deprecated" but is never deleted and does not affect the behavior of existing code. Instead, new features are added, and evolution continues.

Here's the English translation of the revised text:

### Acyclic Dependencies Principle (ADP)

The Acyclic Dependencies Principle states that dependencies should be unidirectional and non-circular. The BEAR.Sunday framework adheres to this principle and is composed of a series of packages with a hierarchical structure where larger framework packages depend on smaller framework packages. Each level does not need to be aware of the existence of other levels that encompass it, and the dependencies are unidirectional and do not form cycles. For example, Ray.Aop is not even aware of the existence of Ray.Di, and Ray.Di is not aware of the existence of BEAR.Sunday.

<img src="/images/screen/package_adp.png" width="360px" alt="Framework structure following the Acyclic Dependencies Principle">

As backward compatibility is maintained, each package can be updated independently. Moreover, there is no version number that locks the entire system, as seen in other frameworks, and there is no mechanism for object proxies that hold cross-cutting dependencies between objects.

The Acyclic Dependencies Principle is in harmony with the DI (Dependency Injection) principle, and the root object generated during the bootstrapping process of BEAR.Sunday is also constructed following the structure of this Acyclic Dependencies Principle.

[<img src="/images/screen/clean-architecture.png" width="40%">](/images/screen/clean-architecture.png)

The same applies to the runtime. When accessing a resource, first, the cross-cutting processing of the AOP aspects bound to the method is executed, and then the method determines the state of the resource. At this point, the method is not aware of the existence of the aspects bound to it. The same goes for resources embedded in the resource's state. They do not have knowledge of the outer layers or elements. The separation of concerns is clearly defined.

### Code Quality

To provide applications with high code quality, the BEAR.Sunday framework also strives to maintain a high standard of code quality.

* The framework code is applied at the strictest level by both static analysis tools, Psalm and PHPStan.
* It maintains 100% test coverage and nearly 100% type coverage.
* It is fundamentally an immutable system and is so clean that initialization is not required every time, even in tests. It unleashes the power of PHP's asynchronous communication engines like Swoole.

## The Value BEAR.Sunday Brings

### Value for Developers

* Improved productivity: Based on robust design patterns and principles with constraints that don't change over time, developers can focus on core business logic.
* Collaboration in teams: By providing development teams with consistent guidelines and structure, it keeps the code of different engineers loosely coupled and unified, improving code readability and maintainability.
* Flexibility and extensibility: BEAR.Sunday's policy of not including libraries brings developers flexibility and freedom in component selection.
* Ease of testing: BEAR.Sunday's DI (Dependency Injection) and ROA (Resource Oriented Architecture) increase the ease of testing.

### Value for Users

* High performance: BEAR.Sunday's optimized fast startup and CDN-centric caching strategy brings users a fast and responsive experience.
* Reliability and availability: BEAR.Sunday's CDN-centric caching strategy minimizes single points of failure (SPOF), allowing users to enjoy stable services.
* Ease of use: BEAR.Sunday's excellent connectivity makes it easy to collaborate with other languages and systems.

### Value for Business

* Reduced development costs: The consistent guidelines and structure provided by BEAR.Sunday promote a sustainable and efficient development process, reducing development costs.
* Reduced maintenance costs: BEAR.Sunday's approach to maintaining backward compatibility increases technical continuity and minimizes the time and cost of change response.
* High extensibility: With technologies like DI (Dependency Injection) and AOP (Aspect Oriented Programming) that change behavior while minimizing code changes, BEAR.Sunday allows applications to be easily extended in line with business growth and changes.
* Excellent User Experience (UX): BEAR.Sunday provides high performance and high availability, increasing user satisfaction, enhancing customer loyalty, expanding the customer base, and contributing to business success.

Excellent constraints do not change. The constraints brought by BEAR.Sunday provide specific value to developers, users, and businesses respectively.

BEAR.Sunday is a framework designed based on the principles and spirit of the Web, providing developers with clear constraints to empower them to build flexible and robust applications.



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

The Web Router is a convention-based router. No configuration or scripting is required.

```php?start_inline
namespace MyVendor\MyProject\Resource\Page;

// page://self/index
class Index extends ResourceObject
{
    public function onGet(): static // GET request
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

## Parameters

The name of the PHP method executed corresponding to the HTTP method and the value passed are as follows.

| HTTP method | PHP method | Parameters |
||
*[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/router.md) needs to be proofread by native speaker. *


# Production

For BEAR.Sunday's default `prod` binding, the application customizes the module according to each [deployment environment](https://en.wikipedia.org/wiki/Deployment_environment) and performs the binding.

## Default ProdModule

The default `prod` binding binds the following interfaces:

* Error page generation factory
* PSR logger interface
* Local cache
* Distributed cache

See [ProdModule.php](https://github.com/bearsunday/BEAR.Package/blob/1.x/src/Context/ProdModule.php) in BEAR.Package for details.

## Application's ProdModule

Customize the application's `ProdModule` in `src/Module/ProdModule.php` against the default ProdModule. Error pages and distributed caches are particularly important.

```php
<?php
namespace MyVendor\Todo\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\QueryRepository\CacheVersionModule;
use BEAR\Resource\Module\OptionsMethodModule;
use BEAR\Package\AbstractAppModule;

class ProdModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new PackageProdModule);       // Default prod settings
        $this->override(new OptionsMethodModule);    // Enable OPTIONS method in production as well
        $this->install(new CacheVersionModule('1')); // Specify resource cache version

        // Custom error page
        $this->bind(ErrorPageFactoryInterface::class)->to(MyErrorPageFactory::class);
    }
}
```

## Cache

There are two types of caches: a local cache and a distributed cache that is shared between multiple web servers.
Both caches default to [PhpFileCache](https://www.doctrine-project.org/projects/doctrine-cache/en/1.10/index.html#phpfilecache).

### Local Cache

The local cache is used for caches that do not change after deployment, such as annotations, while the distributed cache is used to store resource states.

### Distributed Cache

To provide services with two or more web servers, a distributed cache configuration is required.
Modules for each of the popular [memcached](http://php.net/manual/en/book.memcached.php) and [Redis](https://redis.io) cache engines are provided.


### Memcached

```php
<?php
namespace BEAR\HelloWorld\Module;

use BEAR\QueryRepository\StorageMemcachedModule;
use BEAR\Resource\Module\ProdLoggerModule;
use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\Package\AbstractAppModule;
use Ray\Di\Scope;

class ProdModule extends AbstractModule
{
    protected function configure()
    {
        // memcache
        // {host}:{port}:{weight},...
        $memcachedServers = 'mem1.domain.com:11211:33,mem2.domain.com:11211:67';
        $this->install(new StorageMemcachedModule($memcachedServers));

        // Install Prod logger
        $this->install(new ProdLoggerModule);
        // Install default ProdModule
        $this->install(new PackageProdModule);
    }
}
```

### Redis


```php?start_inline
// redis
$redisServer = 'localhost:6379'; // {host}:{port}
$this->install(new StorageRedisModule($redisServer));
```

In addition to simply updating the cache by TTL for storing resource states, it is also possible to operate (CQRS) as a persistent storage that does not disappear after the TTL time.
In that case, you need to perform persistent processing with `Redis` or prepare your own storage adapter for other KVS such as Cassandra.

### Specifying Cache Time

To change the default TTL, install `StorageExpiryModule`.

```php?start_inline
// Cache time
$short = 60;
$medium = 3600;
$long = 24 * 3600;
$this->install(new StorageExpiryModule($short, $medium, $long));
```
### Specifying Cache Version

Change the cache version when the resource schema changes and compatibility is lost. This is especially important for CQRS operation that does not disappear over TTL time.

```
$this->install(new CacheVersionModule($cacheVersion));
```

To discard the resource cache every time you deploy, it is convenient to assign a time or random value to `$cacheVersion` so that no change is required.

## Logging

`ProdLoggerModule` is a resource execution log module for production. When installed, it logs requests other than GET to the logger bound to `Psr\Log\LoggerInterface`.
If you want to log on a specific resource or specific state, bind a custom log to [BEAR\Resource\LoggerInterface](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/LoggerInterface.php).

```php
use BEAR\Resource\LoggerInterface;
use Ray\Di\AbstractModule;

final class MyProdLoggerModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(LoggerInterface::class)->to(MyProdLogger::class);
    }
}
```

The `__invoke` method of [LoggerInterface](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/LoggerInterface.php) passes the resource URI and resource state as a `ResourceObject` object, so log the necessary parts based on its contents.
Refer to the [existing implementation ProdLogger](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/ProdLogger.php) for creation.

## Deployment

### ⚠️ Avoid Overwriting Updates

#### When deploying to a server

* Overwriting a running project folder with `rsync` or similar poses a risk of inconsistency with caches and on-demand generated files, and can exceed capacity on high-load sites.
  Set up in a separate directory for safety and switch if the setup is successful.
* You can use the [BEAR.Sunday recipe](https://github.com/bearsunday/deploy) of [Deployer](http://deployer.org/).

#### When deploying to the cloud

* It is recommended to incorporate compilation into CI as the compiler outputs exit code 1 when it finds dependency issues and 0 when compilation succeeds.

### Compilation Recommended

When setting up, you can **warm up** the project using the `vendor/bin/bear.compile` script.
The compile script creates all static cache files such as dynamically created files for DI/AOP and annotations in advance, and outputs an optimized autoload.php file and preload.php.

* If you compile, the possibility of DI errors at runtime is extremely low because injection is performed in all classes.
* The contents included in `.env` are incorporated into the PHP file, so `.env` can be deleted after compilation.

When compiling multiple contexts (ex. api-app, html-app) in one application, such as when performing content negotiation, it is necessary to evacuate the files.

```
mv autoload.php api.autoload.php  
```

Edit `composer.json` to change the content of `composer compile`.

### autoload.php

An optimized autoload.php file is output to `{project_path}/autoload.php`.
It is much faster than `vendor/autoload.php` output by `composer dumpa-autoload --optimize`.

Note: If you use `preload.php`, most of the classes used are loaded at startup, so the compiled `autoload.php` is not necessary. Please use `vendor/autload.php` generated by Composer.

### preload.php

An optimized preload.php file is output to `{project_path}/preload.php`.
To enable preloading, you need to specify [opcache.preload](https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.preload) and [opcache.preload](https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.preload-user) in php.ini. It is a feature supported in PHP 7.4, but it is unstable in the initial versions of `7.4`. Let's use the latest version of `7.4.4` or higher.

Example)

```
opcache.preload=/path/to/project/preload.php
opcache.preload_user=www-data
```

Note: Please refer to the [benchmark](https://github.com/bearsunday/BEAR.HelloworldBenchmark/wiki/Intel-Core-i5-3.8-GHz-iMac-(Retina-5K,-27-inch,-2017)-



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



## Application Import

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


# Database

The following modules are available for database use, with different problem solving methods. They are all independent libraries for SQL based on [PDO](https://www.php.net/manual/ja/intro.pdo.php).

* ExtendedPdo with PDO extended ([Aura.sql](https://github.com/auraphp/Aura.Sql))
* Query Builder ([Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery))
* Binding PHP interface and SQL execution ([Ray.MediaQuery](database_media.html))

Having static SQL in a file[^locator] makes it easier to use and tune with other SQL tools. SqlQuery can dynamically assemble queries, but the rest of the library is for basic static SQL execution. Ray.MediaQuery can also replace parts of the SQL with those assembled by the builder.

[^locator]: [query-locater](https://github.com/koriym/Koriym.QueryLocator) is a library for handling SQL as files, which is useful with Aura.Sql.

## Module

Modules are provided for using the database. They are all independent libraries for SQL.

* [Ray.AuraSqlModule](database_aura.html)
* [Ray.MediaQuery](database_media.html)

`Ray.AuraSqlModule` is a PDO extension [Aura.Sql](https://github.com/auraphp/Aura.Sql) and a query builder [Aura.SqlQuery](https://github.com/auraphp/) SqlQuery, plus a low-level module that provides pagination functionality.
`Ray.MediaQuery` is a high-performance DB access framework that generates and injects SQL execution objects from user-provided interfaces and SQL [^doma] .

[^doma]: The mechanism is similar to Java's DB access framework [Doma](https://doma.readthedocs.io/en/latest/basic/#examples).

## Other

* [DBAL](database_dbal.html)
* [CakeDb](database_cake.html)
* [Ray.QueryModule](https://github.com/ray-di/Ray.QueryModule/blob/1.x/README.md)

`DBAL` is Doctrine and `CakeDB` is CakePHP's DB library. `Ray.QueryModule` is an earlier library of Ray.MediaQuery that converts SQL to anonymous functions.

----



# Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql) is an Aura database library that extends from `PDO` .

### Installation

Install `Ray.AuraSqlModule` via composer.

```bash
composer require ray/aura-sql-module
```

Installing `AuraSqlModule` in your application module`src/Module/AppModule.php`.

```php?start_inline
use BEAR\Package\AbstractAppModule;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\AuraSqlModule\AuraSqlModule; // add this line

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        // Add the below install method call and contents
        $this->install(
            new AuraSqlModule(
                'mysql:host=localhost;dbname=test',
                'username',
                'password',
                // $options,
                // $attributes
            )
        );
        $this->install(new PackageModule));
    }
}
```

Now the `DI` bindings are ready. The db object will be injected via a constructor or the `AuraSqlInject` setter trait.

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;

class Index
{
    public function __construct(ExtendedPdoInterface $pdo)
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;

class Index
{
    use AuraSqlInject;

    public function onGet()
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```

`Ray.AuraSqlModule` contains [Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) to help you build sql queries.
[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) also have other useful methods like [Array Quoting](https://github.com/auraphp/Aura.Sql/tree/2.x#array-quoting), [fetch*()](https://github.com/auraphp/Aura.Sql/tree/2.x#new-fetch-methods), [perform()](https://github.com/auraphp/Aura.Sql/tree/2.x#the-perform-method) and [yield*()](https://github.com/auraphp/Aura.Sql/tree/2.x#new-yield-methods) that you can use for your needs, please check their documentation.

## Replication

To automatically perform master / slave connection, specify the IP of the slave DB as the fourth argument.

```php?start_inline
$this->install(
  new AuraSqlModule(
    'mysql:host=localhost;dbname=test',
    'username',
    'password',
    'slave1,slave2' // specify slave IP as a comma separated value
  )
);
```

You will now have a slave db connection when using HTTP GET, or a master db connection in other HTTP methods.

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;
use PDO;

class User extends ResourceObject
{
    public $pdo;

    public function __construct(ExtendedPdoInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    public function onGet()
    {
         $this->pdo; // slave db
    }

    public function onPost($todo)
    {
         $this->pdo; // master db
    }
}
```

`$this->pdo` is overwritten if the method is annotated with`@ReadOnlyConnection` or`@WriteConnection`. The master / slave db connection corresponds to the annotation.

```php?start_inline
use Ray\AuraSqlModule\Annotation\ReadOnlyConnection;  // important
use Ray\AuraSqlModule\Annotation\WriteConnection;     // important

class User
{
    public $pdo; // override when @ReadOnlyConnection or @WriteConnection annotated method called

    public function onPost($todo)
    {
         $this->read();
    }

    /**
     * @ReadOnlyConnection
     */
    public function read()
    {
         $this->pdo; // slave db
    }

    /**
     * @WriteConnection
     */
    public function write()
    {
         $this->pdo; // master db
    }
}
```

## Connect to multiple databases

To receive multiple `PdoExtendedInterface` objects with different connection destinations, use `@Named` annotation.

```php?start_inline
/**
 * @Inject
 * @Named("log_db")
 */
public function setLoggerDb(ExtendedPdoInterface $pdo)
{
    // ...
}
```

Specify an identifier with `NamedPdoModule` and bind it.

```php?start_inline
$this->install(new NamedPdoModule('log_db', 'mysql:host=localhost;dbname=log', 'username',
$this->install(new NamedPdoModule('job_db', 'mysql:host=localhost;dbname=job', 'username',
```

In the module, you specify an identifier in `NamedPdoModule` and bind it.

```php?start_inline
$this->install(
  new NamedPdoModule(
    'log_db', // Type of database specified by @Named
    'mysql:host=localhost;dbname=log',
    'username',
    'pass',
    'slave1,slave2' // specify slave IP as a comma separated value
  )
);
```

## Transactions

Using the `@Transactional` annotation wraps methods with a transaction.

```php?start_inline
use Ray\AuraSqlModule\Annotation\Transactional;

// ....
    /**
     * @Transactional
     */
    public function write()
    {
         // \Ray\AuraSqlModule\Exception\RollbackException thrown if it failed.
    }
```

To do transactions on multiple connected databases, specify properties in the `@Transactional` annotation.
If not specified, it becomes `{"pdo"}`.

```php?start_inline
/**
 * @Transactional({"pdo", "userDb"})
 */
public function write()
```

It is run as follows.

```php?start_inline
$this->pdo->beginTransaction()
$this->userDb->beginTransaction()

// ...

$this->pdo->commit();
$this->userDb->commit();
```

## Aura.SqlQuery

[Aura.Sql](https://github.com/auraphp/Aura.Sql) is an extension of PDO. [Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) provides database-specific SQL builder for MySQL, Postgres, SQLite or Microsoft SQL Server.

Specify the database and install it with the application module `src/Module/AppModule.php`.

```php?start_inline
// ...
$this->install(new AuraSqlQueryModule('mysql')); // pgsql, sqlite, or sqlsrv
```

### SELECT

The resource receives the DB Query Builder object and constructs a SELECT query using the following methods.
You can also call the method multiple times in any order.

```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlSelectInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlSelectInject;

    public function onGet()
    {
        $this->select
            ->distinct()                    // SELECT DISTINCT
            ->cols([                        // select these columns
                'id',                       // column name
                'name AS namecol',          // one way of aliasing
                'col_name' => 'col_alias',  // another way of aliasing
                'COUNT(foo) AS foo_count'   // embed calculations directly
            ])
            ->from('foo AS f')              // FROM these tables
            ->fromSubselect(                // FROM sub-select AS my_sub
                'SELECT ...',
                'my_sub'
            )
            ->join(                         // JOIN ...
                'LEFT',                     // left/inner/natural/etc
                'doom AS d'                 // this table name
                'foo.id = d.foo_id'         // ON these conditions
            )
            ->joinSubSelect(                // JOIN to a sub-select
                'INNER',                    // left/inner/natural/etc
                'SELECT ...',               // the subselect to join on
                'subjoin'                   // AS this name
                'sub.id = foo.id'           // ON these conditions
            )
            ->where('bar > :bar')           // AND WHERE these conditions
            ->where('zim = ?', 'zim_val')   // bind 'zim_val' to the ? placeholder
            ->orWhere('baz < :baz')         // OR WHERE these conditions
            ->groupBy(['dib'])              // GROUP BY these columns
            ->having('foo = :foo')          // AND HAVING these conditions
            ->having('bar > ?', 'bar_val')  // bind 'bar_val' to the ? placeholder
            ->orHaving('baz < :baz')        // OR HAVING these conditions
            ->orderBy(['baz'])              // ORDER BY these columns
            ->limit(10)                     // LIMIT 10
            ->offset(40)                    // OFFSET 40
            ->forUpdate()                   // FOR UPDATE
            ->union()                       // UNION with a followup SELECT
            ->unionAll()                    // UNION ALL with a followup SELECT
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to named placeholders
                'bar' => 'bar_val',
                'baz' => 'baz_val',
            ]);

        $sth = $this->pdo->prepare($this->select->getStatement());

        // bind the values and execute
        $sth->execute($this->select->getBindValues());
        $result = $sth->fetch(\PDO::FETCH_ASSOC);
        // or
        // $result = $this->pdo->fetchAssoc($stm, $bind);
```

The created queries are queried as strings with the `getStatement()`.

### INSERT

### Single row INSERT


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlInsertInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlInsertInject;

    public function onPost()
    {
        $this->insert
            ->into('foo')                   // INTO this table
            ->cols([                        // bind values as "(col) VALUES (:col)"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values
                'bar' => 'foo',
                'baz' => 'zim',
            ]);

        $sth = $this->pdo->prepare($this->insert->getStatement());
        $sth->execute($this->insert->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->insert->getStatement(), this->insert->getBindValues());

        // get the last insert ID
        $name = $insert->getLastInsertIdName('id');
        $id = $pdo->lastInsertId($name);
```

The `cols()` method allows you to pass an array of key-value pairs where the key is the column name and the value is a bind value (not a raw value).

```php?start_inline
        $this->insert
            ->into('foo')                   // insert into this table
            ->cols([                        // insert these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
```

### Multi-line INSERT

To do a multiple row INSERT, use the `addRow ()` method at the end of the first line. Then build the following query.

```php?start_inline
        // insert into this table
        $this->insert->into('foo');

        // set up the first row
        $this->insert->cols([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $this->insert->set('ts', 'NOW()');

        // set up the second row. the columns here are in a different order
        // than in the first row, but it doesn't matter; the INSERT object
        // keeps track and builds them the same order as the first row.
        $this->insert->addRow();
        $this->insert->set('ts', 'NOW()');
        $this->insert->cols([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);

        // set up further rows ...
        $this->insert->addRow();
        // ...

        // execute a bulk insert of all rows
        $sth = $this->pdo->prepare($insert->getStatement());
        $sth->execute($insert->getBindValues());

```

> Note: If you try to add a row without specifying the value of the first column in the first row, an exception will be thrown.
> Passing an associative array of columns to `addRow()` will be used on the next line. That is, you can not specify `col()` or `cols()` on the first line.

```php?start_inline
        // set up the first row
        $insert->addRow([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $insert->set('ts', 'NOW()');

        // set up the second row
        $insert->addRow([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);
        $insert->set('ts', 'NOW()');

        // etc.
```

You can also set the database at once using `addRows()`.

```php?start_inline
        $rows = [
            [
                'bar' => 'bar-0',
                'baz' => 'baz-0'
            ],
            [
                'bar' => 'bar-1',
                'baz' => 'baz-1'
            ],
        ];
        $this->insert->addRows($rows);
```

### UPDATE
Use the following methods to construct an UPDATE query. You can also call the method multiple times in any order.

```php?start_inline
        $this->update
            ->table('foo')                  // update this table
            ->cols([                        // bind values as "SET bar = :bar"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->update->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->update->getStatement(), $this->update->getBindValues());
```

You can also pass an associative array to `cols()` with the key as the column name and the value as the bound value (not the RAW value).

```php?start_inline

        $this-update->table('foo')          // update this table
            ->cols([                        // update these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
?>
```

### DELETE
Use the following methods to construct a DELETE query. You can also call the method multiple times in any order.
```php?start_inline
        $this->delete
            ->from('foo')                   // FROM this table
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->delete->getBindValues());
```

### Pagination

[ray/aura-sql-module](https://packagist.org/packages/ray/aura-sql-module) supports pagination (page splitting) in both Ray.Sql raw SQL and Ray.AuraSqlQuery query builder.
We create a pager using the `newInstance()` with a uri_template, binding values and the number of items per page. You can access the page by $page[$number].

### Aura.Sql
AuraSqlPagerFactoryInterface

```php?start_inline
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $sql, $params, 10, '/?page={page}&category=sports'); // 10 items per page
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
// $page->data // sliced data (array|\Traversable)
// $page->current; (int)
// $page->total (int)
// $page->hasNext (bool)
// $page->hasPrevious (bool)
// $page->maxPerPage; (int)
// (string) $page // pager html (string)
```

## Aura.SqlQuery
AuraSqlQueryPagerFactoryInterface

```php?start_inline
// for Select
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlQueryPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $select, 10, '/?page={page}&category=sports');
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
```
> Note: Although the Aura.Sql edits the raw SQL directly, it currently only supports the MySQL LIMIT clause format.

`$page` is iterable.

```php?start_inline
foreach ($page as $row) {
 // Process each row
}
```
To change the pager HTML template, change the binding of `TemplateInterface`.
For details about templates, please see [Pagerfanta](https://github.com/whiteoctober/Pagerfanta#views).

```php?start_inline
use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap3Template;
use Ray\AuraSqlModule\Annotation\PagerViewOption;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->bind(TemplateInterface::class)->to(TwitterBootstrap3Template::class);
        $this->bind()->annotatedWith(PagerViewOption::class)->toInstance($pagerViewOption);
    }
}
```


# CakeDb

**CakeDb** is an ORM using the active record and data mapper pattern idea. It is the same as the one provided in CakePHP3.

Install `Ray.CakeDbModule` with composer.

```bash
composer require ray/cake-database-module ~1.0
```

Please refer to [Ray.CakeDbModule](https://github.com/ray-di/Ray.CakeDbModule) for installation and refer to [CakePHP3 Database Access & ORM](http://book.cakephp.org/3.0/en/orm.html) for the ORM usage.

Ray.CakeDbModule is provided by Jose ([@lorenzo](https://github.com/lorenzo)) who developed the ORM of CakePHP3.

## Connection settings

Use the [phpdotenv](https://github.com/vlucas/phpdotenv) library etc. to set the connection according to the environment destination. Please see the [Ex.Package](https://github.com/BEARSunday/Ex.Package) for implementation.


# Doctrine DBAL

[Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html) is also abstraction layer for database.

Install `Ray.DbalModule` with composer.

```bash
composer require ray/dbal-module
```

Install `DbalModule` in application module.

```php?start_inline
use BEAR\Package\AbstractAppModule;
use Ray\DbalModule\DbalModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new DbalModule('driver=pdo_sqlite&memory=true'));
    }
}
```

New DI bindings are now ready and `$this->db` can be injected with the `DbalInject` trait.

```php?start_inline
use Ray\DbalModule\DbalInject;

class Index
{
    use DbalInject;

    public function onGet()
    {
        return $this->db; // \Doctrine\DBAL\Driver\Connection
    }
}
```

### Connect to multiple databases

To connect to multiple databases, specify the identifier as the second argument.

```php?start_inline
$this->install(new DbalModule($logDsn, 'log_db');
$this->install(new DbalModule($jobDsn, 'job_db');
```

```php?start_inline
/**
 * @Inject
 * @Named("log_db")
 */
public function setLogDb(Connection $logDb)
```

[MasterSlaveConnection](http://www.doctrine-project.org/api/dbal/2.0/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html) is provided for master/slave connections.


# Ray.MediaQuery

`Ray.QueryModule` makes a query to an external media such as a database or Web API with a function object to be injected.

## Motivation

* You can have a clear boundary between domain layer (usage code) and infrastructure layer (injected function) in code.
* Execution objects are generated automatically so you do not need to write procedural code for execution.
* Since usage codes are indifferent to the actual state of external media, storage can be changed later. Easy parallel development and stabbing.

## Composer install

    $ composer require ray/media-query

## Getting Started

Define the interface for media access.

### DB

Specify the SQL ID with the attribute `DbQuery`.

```php
interface TodoAddInterface
{
    #[DbQuery('user_add')]
    public function add(string $id, string $title): void;
}
```

### Web API

Specify the Web request ID with the attribute `WebQuery`.

```php
interface PostItemInterface
{
    #[WebQuery('user_item')]
    public function get(string $id): array;
}
```

Create the web api path list file as `web_query.json`.

```json
{
    "$schema": "https://ray-di.github.io/Ray.MediaQuery/schema/web_query.json",
    "webQuery": [
        {"id": "user_item", "method": "GET", "path": "https://{domain}/users/{id}"}
    ]
}
```

### Module

MediaQueryModule binds the execution of SQL and Web API requests to an interface by setting `DbQueryConfig` or `WebQueryConfig` or both.

```php
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\MediaQuery\ApiDomainModule;
use Ray\MediaQuery\DbQueryConfig;
use Ray\MediaQuery\MediaQueryModule;
use Ray\MediaQuery\Queries;
use Ray\MediaQuery\WebQueryConfig;

protected function configure(): void
{
    $this->install(
        new MediaQueryModule(
            Queries::fromDir('/path/to/queryInterface'),[
                new DbQueryConfig('/path/to/sql'),
                new WebQueryConfig('/path/to/web_query.json', ['domain' => 'api.exmaple.com'])
            ],
        ),
    );
    $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', 'username', 'password'));
}
```

MediaQueryModule requires AuraSqlModule to be installed.

### Request object injection

You don't need to provide any implementation classes. It will be generated and injected.

```php
class Todo
{
    public function __construct(
        private TodoAddInterface $todoAdd
    ) {}

    public function add(string $id, string $title): void
    {
        $this->todoAdd->add($id, $title);
    }
}
```

### Notes

#### DbQuery

SQL execution is mapped to a method, and the SQL specified by ID is bound and executed by the method argument.
For example, if the ID is `todo_item`, `todo_item.sql` SQL statement will be executed with `['id => $id]` bound.

* Prepare the SQL file in the `$sqlDir` directory.

#### Entity

* The SQL execution result can be hydrated to the entity class with `entity` parameter

```php
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class, type:'row')]
    public function getItem(string $id): Todo;
}
```
```php
final class Todo
{
    public string $id;
    public string $title;
}
```

Use `CameCaseTrait` to convert a property to camelCase.

```php
use Ray\MediaQuery\CamelCaseTrait;

class Invoice
{
    use CamelCaseTrait;

    public $userName;
}
```

If the entity has a constructor, the constructor will be called with the fetched data.

```php
final class Todo
{
    public function __construct(
        public string $id,
        public string $title
    ) {}
}
```

#### type: 'row'

If the return value of SQL execution is a single row, specify the attribute `type: 'row'`. However, if the return value of the interface is an entity class, it can be omitted. [^v0dot5].

[^v0dot5]: Until the previous version `0.5`, the SQL file was identified by its name as follows:" If the return value of the SQL execution is a single row, add a postfix of `item`; if it is multiple rows, add a postfix of `list`."

```php
/** If the return value is Entity */
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class)]
    public function getItem(string $id): Todo;
}
```

```php
/** If the return value is array */
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class, type: 'row')]
    public function getItem(string $id): array;
}
```

#### Web API

* Customization such as header for authentication is done by binding Guzzle's `ClinetInterface`.

```php
$this->bind(ClientInterface::class)->toProvider(YourGuzzleClientProvicer::class);
```

## Parameters

### DateTime

You can pass a value object as a parameter.
For example, you can specify a `DateTimeInterface` object like this.

```php
interface TaskAddInterface
{
    public function __invoke(string $title, DateTimeInterface $cratedAt = null): void;
}
```

The value will be converted to a date formatted string at SQL execution time or Web API request time.

```sql
INSERT INTO task (title, created_at) VALUES (:title, :createdAt); # 2021-2-14 00:00:00
```

If no value is passed, the bound current time will be injected.
This eliminates the need to hard-code `NOW()` inside SQL and pass the current time every time.

### Test clock

When testing, you can also use a single time binding for the `DateTimeInterface`, as shown below.

```php
$this->bind(DateTimeInterface::class)->to(UnixEpochTime::class);
```

## VO

If a value object other than `DateTime` is passed, the return value of the `ToScalar()` method that implements the `toScalar` interface or the `__toString()` method will be the argument.

```php
interface MemoAddInterface
{
    public function __invoke(string $memo, UserId $userId = null): void;
}
```

```php
class UserId implements ToScalarInterface
{
    public function __construct(
        private readonly LoginUser $user;
    ){}
    
    public function toScalar(): int
    {
        return $this->user->id;
    }
}
```

```sql
INSERT INTO memo (user_id, memo) VALUES (:user_id, :memo);
```

### Parameter Injection

Note that the default value of `null` for the value object argument is never used in SQL. If no value is passed, the scalar value of the value object injected with the parameter type will be used instead of null.

```php
public function __invoke(Uuid $uuid = null): void; // UUID is generated and passed.
````

## Pagination

The `#[Pager]` annotation allows paging of SELECT queries.

```php
use Ray\MediaQuery\PagesInterface;

interface TodoList
{
    #[DbQuery, Pager(perPage: 10, template: '/{?page}')]
    public function __invoke(): PagesInterface;
}
```

You can get the number of pages with `count()`, and you can get the page object with array access by page number.
`Pages` is a SQL lazy execution object.

```php
$pages = ($todoList)();
$cnt = count($pages); // When count() is called, the count SQL is generated and queried.
$page = $pages[2]; // A page query is executed when an array access is made.

// $page->data // sliced data
// $page->current;
// $page->total
// $page->hasNext
// $page->hasPrevious
// $page->maxPerPage;
// (string) $page // pager html
```

# SqlQuery

If you pass a `DateTimeIntetface` object, it will be converted to a date formatted string and queried.

```php
$sqlQuery->exec('memo_add', ['memo' => 'run', 'created_at' => new DateTime()]);
```

When an object is passed, it is converted to a value of `toScalar()` or `__toString()` as in Parameter Injection.

## Get* Method

To get the SELECT result, use `get*` method depending on the result you want to get.

```php
$sqlQuery->getRow($queryId, $params); // Result is a single row
$sqlQuery->getRowList($queryId, $params); // result is multiple rows
$statement = $sqlQuery->getStatement(); // Retrieve the PDO Statement
$pages = $sqlQuery->getPages(); // Get the pager
```

Ray.MediaQuery contains the [Ray.AuraSqlModule](https://github.com/ray-di/Ray.AuraSqlModule).
If you need more lower layer operations, you can use Aura.Sql's [Query Builder](https://github.com/ray-di/Ray.AuraSqlModule#query-builder) or [Aura.Sql](https://github.com/auraphp/Aura.Sql) which extends PDO.
[doctrine/dbal](https://github.com/ray-di/Ray.DbalModule) is also available.

## Profiler

Media accesses are logged by a logger. By default, a memory logger is bound to be used for testing.

```php
public function testAdd(): void
{
    $this->sqlQuery->exec('todo_add', $todoRun);
    $this->assertStringContainsString('query: todo_add({"id": "1", "title": "run"})', (string) $this->log);
}
```

Implement your own [MediaQueryLoggerInterface](src/MediaQueryLoggerInterface.php) and run
You can also implement your own [MediaQueryLoggerInterface](src/MediaQueryLoggerInterface.php) to benchmark each media query and log it with the injected PSR logger.

## Annotations / Attributes

You can use either [doctrine annotations](https://github.com/doctrine/annotations/) or [PHP8 attributes](https://www.php.net/manual/en/language.attributes.overview.php) can both be used.
The next two are the same.

```php
use Ray\MediaQuery\Annotation\DbQuery;

#[DbQuery('user_add')]
public function add1(string $id, string $title): void;

/** @DbQuery("user_add") */
public function add2(string $id, string $title): void;
```



# Database

`Aura.Sql`、`Doctrine DBAL`, `CakeDB` modules are available for database connections.

## Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql) is an Aura database library that extends from `PDO` .

### Installation

Install `Ray.AuraSqlModule` via composer.

```bash
composer require ray/aura-sql-module
```

Installing `AuraSqlModule` in your application module`src/Module/AppModule.php`.

```php?start_inline
use BEAR\Package\AbstractAppModule;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\AuraSqlModule\AuraSqlModule; // add this line

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        // Add the below install method call and contents
        $this->install(
            new AuraSqlModule(
                'mysql:host=localhost;dbname=test',
                'username',
                'password',
                // $options,
                // $attributes
            )
        );
        $this->install(new PackageModule));
    }
}
```

Now the `DI` bindings are ready. The db object will be injected via a constructor or the `AuraSqlInject` setter trait.

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;

class Index
{
    public function __construct(ExtendedPdoInterface $pdo)
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;

class Index
{
    use AuraSqlInject;

    public function onGet()
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```

`Ray.AuraSqlModule` contains [Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) to help you build sql queries.
[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) also have other useful methods like [Array Quoting](https://github.com/auraphp/Aura.Sql/tree/2.x#array-quoting), [fetch*()](https://github.com/auraphp/Aura.Sql/tree/2.x#new-fetch-methods), [perform()](https://github.com/auraphp/Aura.Sql/tree/2.x#the-perform-method) and [yield*()](https://github.com/auraphp/Aura.Sql/tree/2.x#new-yield-methods) that you can use for your needs, please check their documentation.

## Replication

To automatically perform master / slave connection, specify the IP of the slave DB as the fourth argument.

```php?start_inline
$this->install(
  new AuraSqlModule(
    'mysql:host=localhost;dbname=test',
    'username',
    'password',
    'slave1,slave2' // specify slave IP as a comma separated value
  )
);
```

You will now have a slave db connection when using HTTP GET, or a master db connection in other HTTP methods.

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;
use PDO;

class User extends ResourceObject
{
    public $pdo;

    public function __construct(ExtendedPdoInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    public function onGet()
    {
         $this->pdo; // slave db
    }

    public function onPost($todo)
    {
         $this->pdo; // master db
    }
}
```

`$this->pdo` is overwritten if the method is annotated with`@ReadOnlyConnection` or`@WriteConnection`. The master / slave db connection corresponds to the annotation.

```php?start_inline
use Ray\AuraSqlModule\Annotation\ReadOnlyConnection;  // important
use Ray\AuraSqlModule\Annotation\WriteConnection;     // important

class User
{
    public $pdo; // override when @ReadOnlyConnection or @WriteConnection annotated method called

    public function onPost($todo)
    {
         $this->read();
    }

    /**
     * @ReadOnlyConnection
     */
    public function read()
    {
         $this->pdo; // slave db
    }

    /**
     * @WriteConnection
     */
    public function write()
    {
         $this->pdo; // master db
    }
}
```

## Connect to multiple databases

To receive multiple `PdoExtendedInterface` objects with different connection destinations, use `@Named` annotation.

```php?start_inline
/**
 * @Inject
 * @Named("log_db")
 */
public function setLoggerDb(ExtendedPdoInterface $pdo)
{
    // ...
}
```

Specify an identifier with `NamedPdoModule` and bind it.

```php?start_inline
$this->install(new NamedPdoModule('log_db', 'mysql:host=localhost;dbname=log', 'username',
$this->install(new NamedPdoModule('job_db', 'mysql:host=localhost;dbname=job', 'username',
```

In the module, you specify an identifier in `NamedPdoModule` and bind it.

```php?start_inline
$this->install(
  new NamedPdoModule(
    'log_db', // Type of database specified by @Named
    'mysql:host=localhost;dbname=log',
    'username',
    'pass',
    'slave1,slave2' // specify slave IP as a comma separated value
  )
);
```

## Transactions

Using the `@Transactional` annotation wraps methods with a transaction.

```php?start_inline
use Ray\AuraSqlModule\Annotation\Transactional;

// ....
    /**
     * @Transactional
     */
    public function write()
    {
         // \Ray\AuraSqlModule\Exception\RollbackException thrown if it failed.
    }
```

To do transactions on multiple connected databases, specify properties in the `@Transactional` annotation.
If not specified, it becomes `{"pdo"}`.

```php?start_inline
/**
 * @Transactional({"pdo", "userDb"})
 */
public function write()
```

It is run as follows.

```php?start_inline
$this->pdo->beginTransaction()
$this->userDb->beginTransaction()

// ...

$this->pdo->commit();
$this->userDb->commit();
```

## Aura.SqlQuery

[Aura.Sql](https://github.com/auraphp/Aura.Sql) is an extension of PDO. [Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) provides database-specific SQL builder for MySQL, Postgres, SQLite or Microsoft SQL Server.

Specify the database and install it with the application module `src/Module/AppModule.php`.

```php?start_inline
// ...
$this->install(new AuraSqlQueryModule('mysql')); // pgsql, sqlite, or sqlsrv
```

### SELECT

The resource receives the DB Query Builder object and constructs a SELECT query using the following methods.
You can also call the method multiple times in any order.

```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlSelectInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlSelectInject;

    public function onGet()
    {
        $this->select
            ->distinct()                    // SELECT DISTINCT
            ->cols([                        // select these columns
                'id',                       // column name
                'name AS namecol',          // one way of aliasing
                'col_name' => 'col_alias',  // another way of aliasing
                'COUNT(foo) AS foo_count'   // embed calculations directly
            ])
            ->from('foo AS f')              // FROM these tables
            ->fromSubselect(                // FROM sub-select AS my_sub
                'SELECT ...',
                'my_sub'
            )
            ->join(                         // JOIN ...
                'LEFT',                     // left/inner/natural/etc
                'doom AS d'                 // this table name
                'foo.id = d.foo_id'         // ON these conditions
            )
            ->joinSubSelect(                // JOIN to a sub-select
                'INNER',                    // left/inner/natural/etc
                'SELECT ...',               // the subselect to join on
                'subjoin'                   // AS this name
                'sub.id = foo.id'           // ON these conditions
            )
            ->where('bar > :bar')           // AND WHERE these conditions
            ->where('zim = ?', 'zim_val')   // bind 'zim_val' to the ? placeholder
            ->orWhere('baz < :baz')         // OR WHERE these conditions
            ->groupBy(['dib'])              // GROUP BY these columns
            ->having('foo = :foo')          // AND HAVING these conditions
            ->having('bar > ?', 'bar_val')  // bind 'bar_val' to the ? placeholder
            ->orHaving('baz < :baz')        // OR HAVING these conditions
            ->orderBy(['baz'])              // ORDER BY these columns
            ->limit(10)                     // LIMIT 10
            ->offset(40)                    // OFFSET 40
            ->forUpdate()                   // FOR UPDATE
            ->union()                       // UNION with a followup SELECT
            ->unionAll()                    // UNION ALL with a followup SELECT
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to named placeholders
                'bar' => 'bar_val',
                'baz' => 'baz_val',
            ]);

        $sth = $this->pdo->prepare($this->select->getStatement());

        // bind the values and execute
        $sth->execute($this->select->getBindValues());
        $result = $sth->fetch(\PDO::FETCH_ASSOC);
        // or
        // $result = $this->pdo->fetchAssoc($stm, $bind);
```

The created queries are queried as strings with the `getStatement()`.

### INSERT

### Single row INSERT


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlInsertInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlInsertInject;

    public function onPost()
    {
        $this->insert
            ->into('foo')                   // INTO this table
            ->cols([                        // bind values as "(col) VALUES (:col)"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values
                'bar' => 'foo',
                'baz' => 'zim',
            ]);

        $sth = $this->pdo->prepare($this->insert->getStatement());
        $sth->execute($this->insert->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->insert->getStatement(), this->insert->getBindValues());

        // get the last insert ID
        $name = $insert->getLastInsertIdName('id');
        $id = $pdo->lastInsertId($name);
```

The `cols()` method allows you to pass an array of key-value pairs where the key is the column name and the value is a bind value (not a raw value).

```php?start_inline
        $this->insert
            ->into('foo')                   // insert into this table
            ->cols([                        // insert these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
```

### Multi-line INSERT

To do a multiple row INSERT, use the `addRow ()` method at the end of the first line. Then build the following query.

```php?start_inline
        // insert into this table
        $this->insert->into('foo');

        // set up the first row
        $this->insert->cols([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $this->insert->set('ts', 'NOW()');

        // set up the second row. the columns here are in a different order
        // than in the first row, but it doesn't matter; the INSERT object
        // keeps track and builds them the same order as the first row.
        $this->insert->addRow();
        $this->insert->set('ts', 'NOW()');
        $this->insert->cols([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);

        // set up further rows ...
        $this->insert->addRow();
        // ...

        // execute a bulk insert of all rows
        $sth = $this->pdo->prepare($insert->getStatement());
        $sth->execute($insert->getBindValues());

```

> Note: If you try to add a row without specifying the value of the first column in the first row, an exception will be thrown.
> Passing an associative array of columns to `addRow()` will be used on the next line. That is, you can not specify `col()` or `cols()` on the first line.

```php?start_inline
        // set up the first row
        $insert->addRow([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $insert->set('ts', 'NOW()');

        // set up the second row
        $insert->addRow([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);
        $insert->set('ts', 'NOW()');

        // etc.
```

You can also set the database at once using `addRows()`.

```php?start_inline
        $rows = [
            [
                'bar' => 'bar-0',
                'baz' => 'baz-0'
            ],
            [
                'bar' => 'bar-1',
                'baz' => 'baz-1'
            ],
        ];
        $this->insert->addRows($rows);
```

### UPDATE
Use the following methods to construct an UPDATE query. You can also call the method multiple times in any order.

```php?start_inline
        $this->update
            ->table('foo')                  // update this table
            ->cols([                        // bind values as "SET bar = :bar"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->update->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->update->getStatement(), $this->update->getBindValues());
```

You can also pass an associative array to `cols()` with the key as the column name and the value as the bound value (not the RAW value).

```php?start_inline

        $this-update->table('foo')          // update this table
            ->cols([                        // update these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
?>
```

### DELETE
Use the following methods to construct a DELETE query. You can also call the method multiple times in any order.
```php?start_inline
        $this->delete
            ->from('foo')                   // FROM this table
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->delete->getBindValues());
```

### Pagination

[ray/aura-sql-module](https://packagist.org/packages/ray/aura-sql-module) supports pagination (page splitting) in both Ray.Sql raw SQL and Ray.AuraSqlQuery query builder.
We create a pager using the `newInstance()` with a uri_template, binding values and the number of items per page. You can access the page by $page[$number].

### Aura.Sql
AuraSqlPagerFactoryInterface

```php?start_inline
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $sql, $params, 10, '/?page={page}&category=sports'); // 10 items per page
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
// $page->data // sliced data (array|\Traversable)
// $page->current; (int)
// $page->total (int)
// $page->hasNext (bool)
// $page->hasPrevious (bool)
// $page->maxPerPage; (int)
// (string) $page // pager html (string)
```

## Aura.SqlQuery
AuraSqlQueryPagerFactoryInterface

```php?start_inline
// for Select
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlQueryPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $select, 10, '/?page={page}&category=sports');
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
```
> Note: Although the Aura.Sql edits the raw SQL directly, it currently only supports the MySQL LIMIT clause format.

`$page` is iterable.

```php?start_inline
foreach ($page as $row) {
 // Process each row
}
```
To change the pager HTML template, change the binding of `TemplateInterface`.
For details about templates, please see [Pagerfanta](https://github.com/whiteoctober/Pagerfanta#views).

```php?start_inline
use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap3Template;
use Ray\AuraSqlModule\Annotation\PagerViewOption;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->bind(TemplateInterface::class)->to(TwitterBootstrap3Template::class);
        $this->bind()->annotatedWith(PagerViewOption::class)->toInstance($pagerViewOption);
    }
}
```

## Doctrine DBAL

[Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html) is also abstraction layer for database.

Install `Ray.DbalModule` with composer.

```bash
composer require ray/dbal-module
```

Install `DbalModule` in application module.

```php?start_inline
use BEAR\Package\AbstractAppModule;
use Ray\DbalModule\DbalModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new DbalModule('driver=pdo_sqlite&memory=true');
    }
}
```

New DI bindings are now ready and `$this->db` can be injected with the `DbalInject` trait.

```php?start_inline
use Ray\DbalModule\DbalInject;

class Index
{
    use DbalInject;

    public function onGet()
    {
        return $this->db; // \Doctrine\DBAL\Driver\Connection
    }
}
```

### Connect to multiple databases

To connect to multiple databases, specify the identifier as the second argument.

```php?start_inline
$this->install(new DbalModule($logDsn, 'log_db');
$this->install(new DbalModule($jobDsn, 'job_db');
```

```php?start_inline
/**
 * @Inject
 * @Named("log_db")
 */
public function setLogDb(Connection $logDb)
```

[MasterSlaveConnection](http://www.doctrine-project.org/api/dbal/2.0/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html) is provided for master/slave connections.

## CakeDb

**CakeDb** is an ORM using the active record and data mapper pattern idea. It is the same as the one provided in CakePHP3.

Install `Ray.CakeDbModule` with composer.

```bash
composer require ray/cake-database-module ~1.0
```

Please refer to [Ray.CakeDbModule](https://github.com/ray-di/Ray.CakeDbModule) for installation and refer to [CakePHP3 Database Access & ORM](http://book.cakephp.org/3.0/en/orm.html) for the ORM usage.

Ray.CakeDbModule is provided by Jose ([@lorenzo](https://github.com/lorenzo)) who developed the ORM of CakePHP3.

## Connection settings

Use the [phpdotenv](https://github.com/vlucas/phpdotenv) library etc. to set the connection according to the environment destination. Please see the [Ex.Package](https://github.com/BEARSunday/Ex.Package) for implementation.

Redis Cache Adapter
===================

    This article explains how to configure the Redis adapter when using the
    Cache as an independent component in any PHP application. Read the
    :ref:`Symfony Cache configuration <cache-configuration-with-frameworkbundle>`
    article if you are using it in a Symfony application.

This adapter stores the values in-memory using one (or more) `Redis server`_ instances.

Unlike the :doc:`APCu adapter </components/cache/adapters/apcu_adapter>`, and similarly to the
:doc:`Memcached adapter </components/cache/adapters/memcached_adapter>`, it is not limited to the current server's
shared memory; you can store contents independent of your PHP environment. The ability
to utilize a cluster of servers to provide redundancy and/or fail-over is also available.

.. caution::

    **Requirements:** At least one `Redis server`_ must be installed and running to use this
    adapter. Additionally, this adapter requires a compatible extension or library that implements
    ``\Redis``, ``\RedisArray``, ``RedisCluster``, ``\Relay\Relay`` or ``\Predis``.

This adapter expects a `Redis`_, `RedisArray`_, `RedisCluster`_, `Relay`_ or `Predis`_ instance to be
passed as the first parameter. A namespace and default cache lifetime can optionally be passed
as the second and third parameters::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    $cache = new RedisAdapter(

        // the object that stores a valid connection to your Redis system
        \Redis $redisConnection,

        // the string prefixed to the keys of the items stored in this cache
        $namespace = '',

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until RedisAdapter::clear() is invoked or the server(s) are purged)
        $defaultLifetime = 0
    );

Configure the Connection


The :method:`Symfony\\Component\\Cache\\Traits\\RedisTrait::createConnection`
helper method allows creating and configuring the Redis client class instance using a
`Data Source Name (DSN)`_::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    // pass a single DSN string to register a single server with the client
    $client = RedisAdapter::createConnection(
        'redis://localhost'
    );

The DSN can specify either an IP/host (and an optional port) or a socket path, as well as a
password and a database index. To enable TLS for connections, the scheme ``redis`` must be
replaced by ``rediss`` (the second ``s`` means "secure").

.. note::

    A `Data Source Name (DSN)`_ for this adapter must use either one of the following formats.

    .. code-block:: text

        redis[s]://[pass@][ip|host|socket[:port]][/db-index]

    .. code-block:: text

        redis[s]:[[user]:pass@]?[ip|host|socket[:port]][&params]

    Values for placeholders ``[user]``, ``[:port]``, ``[/db-index]`` and ``[&params]`` are optional.

Below are common examples of valid DSNs showing a combination of available values::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    // host "my.server.com" and port "6379"
    RedisAdapter::createConnection('redis://my.server.com:6379');

    // host "my.server.com" and port "6379" and database index "20"
    RedisAdapter::createConnection('redis://my.server.com:6379/20');

    // host "localhost", auth "abcdef" and timeout 5 seconds
    RedisAdapter::createConnection('redis://abcdef@localhost?timeout=5');

    // socket "/var/run/redis.sock" and auth "bad-pass"
    RedisAdapter::createConnection('redis://bad-pass@/var/run/redis.sock');

    // host "redis1" (docker container) with alternate DSN syntax and selecting database index "3"
    RedisAdapter::createConnection('redis:?host[redis1:6379]&dbindex=3');

    // providing credentials with alternate DSN syntax
    RedisAdapter::createConnection('redis:default:verysecurepassword@?host[redis1:6379]&dbindex=3');

    // a single DSN can also define multiple servers
    RedisAdapter::createConnection(
        'redis:?host[localhost]&host[localhost:6379]&host[/var/run/redis.sock:]&auth=my-password&redis_cluster=1'
    );

`Redis Sentinel`_, which provides high availability for Redis, is also supported
when using the PHP Redis Extension v5.2+ or the Predis library. Use the ``redis_sentinel``
parameter to set the name of your service group::

    RedisAdapter::createConnection(
        'redis:?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&redis_sentinel=mymaster'
    );

    // providing credentials
    RedisAdapter::createConnection(
        'redis:default:verysecurepassword@?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&redis_sentinel=mymaster'
    );

    // providing credentials and selecting database index "3"
    RedisAdapter::createConnection(
        'redis:default:verysecurepassword@?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&redis_sentinel=mymaster&dbindex=3'
    );

.. note::

    See the :class:`Symfony\\Component\\Cache\\Traits\\RedisTrait` for more options
    you can pass as DSN parameters.

Configure the Options
--

When using Redis as cache, you should configure the ``maxmemory`` and ``maxmemory-policy``
settings. By setting ``maxmemory``, you limit how much memory Redis is allowed to consume.
If the amount is too low, Redis will drop entries that would still be useful and you benefit
less from your cache. Setting the ``maxmemory-policy`` to ``allkeys-lru`` tells Redis that
it is ok to drop data when it runs out of memory, and to first drop the oldest entries (least
recently used). If you do not allow Redis to drop entries, it will return an error when you
try to add data when no memory is available. An example setting could look as follows:

.. code-block:: ini

    maxmemory 100mb
    maxmemory-policy allkeys-lru

Working with Tags
-----

In order to use tag-based invalidation, you can wrap your adapter in
:class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter`. However, when Redis
is used as backend, it's often more interesting to use the dedicated
:class:`Symfony\\Component\\Cache\\Adapter\\RedisTagAwareAdapter`. Since tag
invalidation logic is implemented in Redis itself, this adapter offers better
performance when using tag-based invalidation::

    use Symfony\Component\Cache\Adapter\RedisAdapter;
    use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;

    $client = RedisAdapter::createConnection('redis://localhost');
    $cache = new RedisTagAwareAdapter($client);

.. note::

    When using RedisTagAwareAdapter, in order to maintain relationships between
    tags and cache items, you have to use either ``noeviction`` or ``volatile-*``
    in the Redis ``maxmemory-policy`` eviction policy.

Read more about this topic in the official `Redis LRU Cache Documentation`_.

.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
.. _`Redis server`: https://redis.io/
.. _`Redis`: https://github.com/phpredis/phpredis
.. _`RedisArray`: https://github.com/phpredis/phpredis/blob/develop/arrays.md
.. _`RedisCluster`: https://github.com/phpredis/phpredis/blob/develop/cluster.md
.. _`Relay`: https://relay.so/
.. _`Predis`: https://packagist.org/packages/predis/predis
.. _`Predis Connection Parameters`: https://github.com/nrk/predis/wiki/Connection-Parameters#list-of-connection-parameters
.. _`TCP-keepalive`: https://redis.io/topics/clients#tcp-keepalive
.. _`Redis Sentinel`: https://redis.io/topics/sentinel
.. _`Redis LRU Cache Documentation`: https://redis.io/topics/lru-cache
.. _`php.net/context.ssl`: https://php.net/context.ssl



# Validation

 * You can define resource APIs in the JSON schema.
 * You can separate the validation code with `@Valid`, `@OnValidate` annotation.
 * Please see the form for validation by web form.

# JSON Schema

The [JSON Schema](http://json-schema.org/) is the standard for describing and validating JSON objects. `@JsonSchema` and the resource body returned by the method of annotated resource class are validated by JSON schema.


### Install

If you want to validate in all contexts including production, create `AppModule`, if validation is done only during development, create `DevModule` and install within it


```php?start_inline
use BEAR\Resource\Module\JsonSchemaModule; // Add this line
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(
            new JsonSchemaModule(
                $appDir . '/var/json_schema',
                $appDir . '/var/json_validate'
            )
        );  // Add this line
    }
}
```
Create directories for the JSON schema files

```bash
mkdir var/json_schema
mkdir var/json_validate
```

In the `var/json_schema/`, store the JSON schema file which is the specification of the body of the resource, and the `var/json_validate/` stores the JSON schema file for input validation.

### @JsonSchema annotation

Annotate the method of the resource class by adding `@JsonSchema`, then add the `schema` property by specifying the JSON schema file name, which is `user.json` for this purpose.

### schema

src/Resource/App/User.php

```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class User extends ResourceObject
{
    #[JsonSchema('user.json')]
    public function onGet(): static
    {
        $this->body = [
            'firstName' => 'mucha',
            'lastName' => 'alfons',
            'age' => 12
        ];

        return $this;
    }
}
```

We will create a JSON schema named `/var/json_schema/user.json`

```json
{
  "type": "object",
  "properties": {
    "firstName": {
      "type": "string",
      "maxLength": 30,
      "pattern": "[a-z\\d~+-]+"
    },
    "lastName": {
      "type": "string",
      "maxLength": 30,
      "pattern": "[a-z\\d~+-]+"
    }
  },
  "required": ["firstName", "lastName"]
}
```

### key

If the body has an index key, specify it with the key property of the annotation

```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class User extends ResourceObject
{
    #[JsonSchema(key:'user', schema:'user.json')]
    public function onGet()
    {
        $this->body = [
            'user' => [
                'firstName' => 'mucha',
                'lastName' => 'alfons',
                'age' => 12
            ]
        ];        

        return $this;
    }
}
```

### params

The `params` property specifies the JSON schema file name for the argument validation


```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class Todo extends ResourceObject
{
    #[JsonSchema(key:'user', schema:'user.json', params:'todo.post.json')]
    public function onPost(string $title)
```

We place the JSON schema file

**/var/json_validate/todo.post.json**

```json
{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "title": "/todo POST request validation",
  "properties": {
    "title": {
      "type": "string",
      "minLength": 1,
      "maxLength": 40
    }
}

```

By constantly verifying in a standardized way instead of proprietary documentation, the specification is **reliable and understandable** to both humans and machines.

### target

To apply schema validation to the representation of the resource object (the rendered result) rather than to the body of the ResourceObject, specify the option `target='view'`.

```php
#[JsonSchema(schema: 'user.json', target: 'view')]
```

### Related Links

 * [Example](http://json-schema.org/examples.html)
 * [Understanding JSON Schema](https://spacetelescope.github.io/understanding-json-schema/)
 * [JSON Schema Generator](https://jsonschema.net/#/editor)

## @Valid annotation

The `@Valid` annotation is a validation for input.
You can set up validation as AOP for your method.
By separating validation logic from the method, the code will be readable and testable.

Validation libraries are available such as [Aura.Filter](https://github.com/auraphp/Aura.Filter), [Respect\Validation](https://github.com/Respect/Validation), and [PHP Standard Filter](http://php.net/manual/en/book.filter.php)

### Install

Install `Ray.ValidateModule` via composer.

```bash
composer require ray/validate-module
```

Installing `ValidateModule` in your application module `src/Module/AppModule.php`.

```php?start_inline
use Ray\Validation\ValidateModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new ValidateModule);
    }
}
```

### Annotation

There are three annotations `@Valid`, `@OnValidate`, `@OnFailure` for validation.

First of all, annotate the method that you want to validate with `@Valid`

```php?start_inline
use Ray\Validation\Annotation\Valid;

class News
{
    /**
     * @Valid
     */
    public function createUser($name)
    {
```

Validation will be conducted in the method annotated with `@OnValidate`.

The arguments of the method should be the same as the original method. The method name can be anything.

```php?start_inline
use Ray\Validation\Annotation\OnValidate;

class News
{
    /**
     * @OnValidate
     */
    public function onValidate($name)
    {
        $validation = new Validation;
        if (! is_string($name)) {
            $validation->addError('name', 'name should be string');
        }

        return $validation;
    }
```

Add validations to your elements by `addError()` with the `element name` and` error message` as parameters, then return the validation object.

When validation fails, the exception `Ray\Validation\Exception\InvalidArgumentException` will be thrown,
but if you have a method annotated with the `@OnFailure`, it will be called, instead of throwing an exception

```php?start_inline
use Ray\Validation\Annotation\OnFailure;

class News
{
    /**
     * @OnFailure
     */
    public function onFailure(FailureInterface $failure)
    {
        // original parameters
        list($this->defaultName) = $failure->getInvocation()->getArguments();

        // errors
        foreach ($failure->getMessages() as $name => $messages) {
            foreach ($messages as $message) {
                echo "Input '{$name}': {$message}" . PHP_EOL;
            }
        }
    }
```

In the method annotated with `@OnFailure`, you can access the validated messages with `$failure->getMessages()`
and also you can get the object of the original method with `$failure->getInvocation()`.

### Various validation

If you want to have different validations for a class, you can specify the name of the validation like below

```php?start_inline
use Ray\Validation\Annotation\Valid;
use Ray\Validation\Annotation\OnValidate;
use Ray\Validation\Annotation\OnFailure;

class News
{
    /**
     * @Valid("foo")
     */
    public function fooAction($name, $address, $zip)
    {

    /**
     * @OnValidate("foo")
     */
    public function onValidateFoo($name, $address, $zip)
    {

    /**
     * @OnFailure("foo")
     */
    public function onFailureFoo(FailureInterface $failure)
    {
```

### Other validation

If you need to implement complex validation, you can have another class for validation and inject it.
And then call in the method annotated with the `onValidate`.
You can also change your validation behavior by context with DI.


This page collects all BEAR.Sunday manuals in one place.



# What is BEAR.Sunday?

BEAR.Sunday is a PHP application framework that combines clean object-oriented design with a resource-oriented architecture aligned with the fundamental principles of the web. This framework emphasizes compliance with standards, a long-term perspective, high efficiency, flexibility, self-description, and importantly, simplicity.

## Framework

BEAR.Sunday consists of three frameworks.

`Ray.Di` interfaces object dependencies based on the [Principle of Dependency Inversion](http://en.wikipedia.org/wiki/Dependency_inversion_principle).

`Ray.Aop` connects core concerns and cross-cutting concerns with [aspect-oriented programming](http://en.wikipedia.org/wiki/Aspect-oriented_programming).

`BEAR.Resource` connects application data and functionality with resources with [REST constraints](https://en.wikipedia.org/wiki/Representational_state_transfer).

The framework provides constraints and design principles that guide the entire application, promoting consistent design and implementation, and resulting in high-quality, clean code.

## Libraries

Unlike full-stack frameworks, BEAR.Sunday does not include its own libraries for specific tasks like authentication or database management. Instead, it favors the use of high-quality third-party libraries.

This approach is based on two key design philosophies: firstly, the belief that "frameworks remain, libraries change," acknowledging that while the framework provides a stable foundation, libraries evolve to meet changing needs over time. Secondly, it empowers "application architects with the right and responsibility to choose libraries" that best fit their application's requirements, constraints, and goals.

BEAR.Sunday draws a clear distinction between frameworks and libraries, emphasizing the role of the framework as an application constraint.

## Architecture

BEAR.Sunday departs from the traditional MVC (Model-View-Controller) architecture, embracing a resource-oriented architecture (ROA). In this paradigm, data and business logic are unified as resources, and the design revolves around links and operations on those resources. While ROA is commonly used for REST API design, BEAR.Sunday extends it to the entire web application.

## Long-term perspective

BEAR.Sunday is designed with a long-term view, focusing on application maintainability:

- **Constraints**: The consistent application constraints imposed by DI, AOP, and REST remain unchanged over time.

- **Eternal 1.x**:The System That Never Breaks Backward Compatibility. Since its initial release in 2015, BEAR.Sunday has continuously evolved without introducing any backward-incompatible changes. This steadfast approach eliminates the need for compatibility fixes and their associated testing, thereby preventing future technical debt. The system remains cutting-edge, ensuring easy upgrades and access to the latest features without compatibility concerns.

- **Standards Compliance**: BEAR.Sunday adheres to various standards, including HTTP, JsonSchema, and others. For DI, it follows Google Guice, and for AOP, it aligns with the Java Aop Alliance.

## Connectivity

BEAR.Sunday transcends traditional web applications, offering seamless integration with a diverse range of clients:

- **HTTP Client**: All resources are directly accessible via HTTP, unlike models or controllers in MVC.

- **composer package**: Resources from applications installed under the vendor directory via Composer can be invoked directly, enabling coordination between multiple applications without resorting to microservices.

- **Multilingual framework**: BEAR.Thrift facilitates seamless and efficient interoperability with other languages and PHP versions.

## Web Cache

By integrating resource-oriented architecture with modern CDN technology, we achieve distributed caching that surpasses traditional server-side TTL caching. BEAR.Sunday's design philosophy adheres to the fundamental principles of the Web, utilizing a CDN-centered distributed caching system to ensure high performance and availability.

- **Distributed Caching**: By caching on the client, CDN, and server-side, both CPU and network costs are minimized.

- **Identification**: ETag-based verification ensures that only modified content is retrieved, enhancing network efficiency.

- **Fault tolerance**: Event-based cache invalidation allows all content to be stored in CDN caches without TTL limitations. This improves fault tolerance to the point where the system remains available even if the PHP or database servers go down.


## Performance

BEAR.Sunday is designed with a focus on performance and efficiency while maintaining maximum flexibility. This approach enables a highly optimized bootstrap, positively impacting both user experience and system resources. Performance is always one of the primary concerns for BEAR.Sunday, playing a central role in our design and development decisions.

## Because Everything is a Resource

BEAR.Sunday embraces the essence of the Web, where "Everything is a Resource." As a PHP web application framework, it excels by providing superior constraints based on object-oriented and REST principles, applicable to the entire application.

These constraints encourage developers to design and implement consistently and improve the quality of the application in the long run. At the same time, the constraints provide developers with freedom and enhance creativity in building the application.


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



# Resource

A BEAR.Sunday application is [RESTful](http://en.wikipedia.org/wiki/Representational_state_transfer) and is made up of a collection of resources connected by links.

## Object as a service

An HTTP method is mapped to a PHP method in the `ResourceObject` class.
It transfers its resource state as a resource representation from stateless request.
([Representational State Transfer)](http://en.wikipedia.org/wiki/REST)

Here are some examples of a resource object:

```php?start_inline
class Index extends ResourceObject
{
    public $code = 200;
    public $headers = [];

    public function onGet(int $a, int $b): static
    {
        $this->body = [
            'sum' => $a + $b // $_GET['a'] + $_GET['b']
        ] ;

        return $this;
    }
}
```

```php?start_inline
class Todo extends ResourceObject
{
    public function onPost(string $id, string $todo): static
    {
        $this->code = 201; // status code
        $this->headers = [ // header
            'Location' => '/todo/new_id'
        ];

        return $this;
    }
}
```

The PHP resource class has URIs such as  `page://self/index` similar to the URI of the web, and conforms to the HTTP method `onGet`,` onPost`, `onPut`,` onPatch`, `onDelete` interface.

$_GET for `onGet` and $_POST for `onPost` are passed to the arguments of the method depending on the variable name, and the methods of `onPut`,` onPatch`, `onDelete` are content. The value that can be handled according to `content-type`(`x-www-form-urlencoded` or `application/json`) is an argument.

The resource state (`code`,`headers` or`body`) is handled by these method using the given parameters. Then the resource class returns itself(`$this`).

## URI

URIs are mapped to PHP classes. Applications use the URI instead of the class name to access resources.

| URI | Class |
|--+-|
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts | Koriym\Todo\Resource\App\Blog\Posts |

## Method

Resources have 6 interfaces conforming to HTTP methods.[^method]

[^method]: REST methods are not a mapping to CRUD. They are divided into two categories: safe ones that do not change the resource state, or idempotent ones.

### GET
Reads resources. This method does not provide any changing of the resource state. A safe method with no possible side affects.

### POST
The POST method requests processing of the representation contained in the request. For example, adding a new resource to a target URI or adding a representation to an existing resource. Unlike PUT, requests do not have [idempotence](https://ja.wikipedia.org/wiki/%E5%86%AA%E7%AD%89), and multiple consecutive executions will not produce the same result.

### PUT
Replaces the resource with the payload of the request at the requested URI. If the target resource does not exist, it is created. Unlike POST, there is not idempotent.

### PATCH

Performs resource updates, but unlike PUT, it applies a delta rather than replacing the entire resource.


### DELETE
Resource deletion. Has idempotence just like PUT.

### OPTIONS
Get information on parameters and responses required for resource request. It is as secure as GET method.

#### List of method properties

| Methods | [Safe](https://developer.mozilla.org/en-US/docs/Glossary/Safe/HTTP) | [Idempotent](https://developer.mozilla.org/en-US/docs/Glossary/Idempotent) | [Cacheable](https://developer.mozilla.org/en-US/docs/Glossary/cacheable) 
|-|-|-|-|-
| GET | Yes | Yes | Yes
| POST | No | No | No
| PUT | No | Yes | No
| PATCH | No | Yes | No
| DELETE | No | Yes | No
| OPTIONS | Yes | Yes | No

## Parameters

The response method argument is passed the request value corresponding to the variable name.

```php?start_inline
class Index extends ResourceObject
{
    // $_GET['id'] to $id
    public function onGet(int $id): static
    {
    }

    // $_POST['name'] to $name
    public function onPost(string $name): static
    {
    }
```

See [Resource Parameters](resource_param.html) for other methods and how to pass external variables such as cookies as parameters.

## Rendering and transfer

The request method of a ResourceObject is not concerned with the representation of the resource. The injected renderer generates the representation of the resource and the responder outputs it. See [Rendering and Transferring](resource_renderer.html) for details.

## Client

Use the resource client to request other resources. This request executes a request to the `app://self/blog/posts` resource with the query `?id=1`.

```php?start_inline
use BEAR\Sunday\Inject\ResourceInject;

class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(): static
    {
        $this->body = [
            'posts' => $this->resource->get('app://self/blog/posts', ['id' => 1])
        ];
    }
}
```

Other historical notations include the following

```php?start_inline
// PHP 5.x and up
$posts = $this->resource->get->uri('app://self/posts')->withQuery(['id' => 1])->eager->request();
// PHP 7.x and up
$posts = $this->resource->get->uri('app://self/posts')(['id' => 1]);
// you can omit `get`
$posts = $this->resource->uri('app://self/posts')(['id' => 1]);
// bear/resource 1.11 and up
$posts = $this->resource->get('app://self/posts', ['id' => 1]);
```

## Lazy evaluation

The above is an `eager` request that makes the request immediately, but it is also possible to generate a request and delay execution instead of the request result.

```php
$request = $this->resource->get('app://self/posts'); // callable
$posts = $request(['id' => 1]);
```

When this request is embedded in a template or resource, it is evaluated lazily. That is, when it is not evaluated, the request is not made and has no execution cost.

```php
$this->body = [
    'lazy' => $this->resource->get('app://self/posts')->withQuery(['id' => 3])->requrest();
];
```

## Cache

Along with regular TTL caching, we support REST client caching and advanced partial caching (doughnut caching), including CDN. See [cache](cache.html) for details. Also see the previous [resource(v1)](resourcev1.html#Resource Cache) document for the previous `@Cacheable` annotation.

## Link

One important REST constraint is resource linking; ResourceObject supports both internal and external linking. See [Resource Linking](resource_link.html) for details.

## BEAR.Resource

The functionality of the BEAR.Sunday resource object is also available in a stand-alone package for stand-alone use: BEAR.Resource [README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.ja.md).

-



# Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql) is an Aura database library that extends from `PDO` .

### Installation

Install `Ray.AuraSqlModule` via composer.

```bash
composer require ray/aura-sql-module
```

Installing `AuraSqlModule` in your application module`src/Module/AppModule.php`.

```php?start_inline
use BEAR\Package\AbstractAppModule;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\AuraSqlModule\AuraSqlModule; // add this line

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        // Add the below install method call and contents
        $this->install(
            new AuraSqlModule(
                'mysql:host=localhost;dbname=test',
                'username',
                'password',
                // $options,
                // $attributes
            )
        );
        $this->install(new PackageModule));
    }
}
```

Now the `DI` bindings are ready. The db object will be injected via a constructor or the `AuraSqlInject` setter trait.

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;

class Index
{
    public function __construct(ExtendedPdoInterface $pdo)
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;

class Index
{
    use AuraSqlInject;

    public function onGet()
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```

`Ray.AuraSqlModule` contains [Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) to help you build sql queries.
[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) also have other useful methods like [Array Quoting](https://github.com/auraphp/Aura.Sql/tree/2.x#array-quoting), [fetch*()](https://github.com/auraphp/Aura.Sql/tree/2.x#new-fetch-methods), [perform()](https://github.com/auraphp/Aura.Sql/tree/2.x#the-perform-method) and [yield*()](https://github.com/auraphp/Aura.Sql/tree/2.x#new-yield-methods) that you can use for your needs, please check their documentation.

## Replication

To automatically perform master / slave connection, specify the IP of the slave DB as the fourth argument.

```php?start_inline
$this->install(
  new AuraSqlModule(
    'mysql:host=localhost;dbname=test',
    'username',
    'password',
    'slave1,slave2' // specify slave IP as a comma separated value
  )
);
```

You will now have a slave db connection when using HTTP GET, or a master db connection in other HTTP methods.

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;
use PDO;

class User extends ResourceObject
{
    public $pdo;

    public function __construct(ExtendedPdoInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    public function onGet()
    {
         $this->pdo; // slave db
    }

    public function onPost($todo)
    {
         $this->pdo; // master db
    }
}
```

`$this->pdo` is overwritten if the method is annotated with`@ReadOnlyConnection` or`@WriteConnection`. The master / slave db connection corresponds to the annotation.

```php?start_inline
use Ray\AuraSqlModule\Annotation\ReadOnlyConnection;  // important
use Ray\AuraSqlModule\Annotation\WriteConnection;     // important

class User
{
    public $pdo; // override when @ReadOnlyConnection or @WriteConnection annotated method called

    public function onPost($todo)
    {
         $this->read();
    }

    /**
     * @ReadOnlyConnection
     */
    public function read()
    {
         $this->pdo; // slave db
    }

    /**
     * @WriteConnection
     */
    public function write()
    {
         $this->pdo; // master db
    }
}
```

## Connect to multiple databases

To receive multiple `PdoExtendedInterface` objects with different connection destinations, use `@Named` annotation.

```php?start_inline
/**
 * @Inject
 * @Named("log_db")
 */
public function setLoggerDb(ExtendedPdoInterface $pdo)
{
    // ...
}
```

Specify an identifier with `NamedPdoModule` and bind it.

```php?start_inline
$this->install(new NamedPdoModule('log_db', 'mysql:host=localhost;dbname=log', 'username',
$this->install(new NamedPdoModule('job_db', 'mysql:host=localhost;dbname=job', 'username',
```

In the module, you specify an identifier in `NamedPdoModule` and bind it.

```php?start_inline
$this->install(
  new NamedPdoModule(
    'log_db', // Type of database specified by @Named
    'mysql:host=localhost;dbname=log',
    'username',
    'pass',
    'slave1,slave2' // specify slave IP as a comma separated value
  )
);
```

## Transactions

Using the `@Transactional` annotation wraps methods with a transaction.

```php?start_inline
use Ray\AuraSqlModule\Annotation\Transactional;

// ....
    /**
     * @Transactional
     */
    public function write()
    {
         // \Ray\AuraSqlModule\Exception\RollbackException thrown if it failed.
    }
```

To do transactions on multiple connected databases, specify properties in the `@Transactional` annotation.
If not specified, it becomes `{"pdo"}`.

```php?start_inline
/**
 * @Transactional({"pdo", "userDb"})
 */
public function write()
```

It is run as follows.

```php?start_inline
$this->pdo->beginTransaction()
$this->userDb->beginTransaction()

// ...

$this->pdo->commit();
$this->userDb->commit();
```

## Aura.SqlQuery

[Aura.Sql](https://github.com/auraphp/Aura.Sql) is an extension of PDO. [Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) provides database-specific SQL builder for MySQL, Postgres, SQLite or Microsoft SQL Server.

Specify the database and install it with the application module `src/Module/AppModule.php`.

```php?start_inline
// ...
$this->install(new AuraSqlQueryModule('mysql')); // pgsql, sqlite, or sqlsrv
```

### SELECT

The resource receives the DB Query Builder object and constructs a SELECT query using the following methods.
You can also call the method multiple times in any order.

```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlSelectInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlSelectInject;

    public function onGet()
    {
        $this->select
            ->distinct()                    // SELECT DISTINCT
            ->cols([                        // select these columns
                'id',                       // column name
                'name AS namecol',          // one way of aliasing
                'col_name' => 'col_alias',  // another way of aliasing
                'COUNT(foo) AS foo_count'   // embed calculations directly
            ])
            ->from('foo AS f')              // FROM these tables
            ->fromSubselect(                // FROM sub-select AS my_sub
                'SELECT ...',
                'my_sub'
            )
            ->join(                         // JOIN ...
                'LEFT',                     // left/inner/natural/etc
                'doom AS d'                 // this table name
                'foo.id = d.foo_id'         // ON these conditions
            )
            ->joinSubSelect(                // JOIN to a sub-select
                'INNER',                    // left/inner/natural/etc
                'SELECT ...',               // the subselect to join on
                'subjoin'                   // AS this name
                'sub.id = foo.id'           // ON these conditions
            )
            ->where('bar > :bar')           // AND WHERE these conditions
            ->where('zim = ?', 'zim_val')   // bind 'zim_val' to the ? placeholder
            ->orWhere('baz < :baz')         // OR WHERE these conditions
            ->groupBy(['dib'])              // GROUP BY these columns
            ->having('foo = :foo')          // AND HAVING these conditions
            ->having('bar > ?', 'bar_val')  // bind 'bar_val' to the ? placeholder
            ->orHaving('baz < :baz')        // OR HAVING these conditions
            ->orderBy(['baz'])              // ORDER BY these columns
            ->limit(10)                     // LIMIT 10
            ->offset(40)                    // OFFSET 40
            ->forUpdate()                   // FOR UPDATE
            ->union()                       // UNION with a followup SELECT
            ->unionAll()                    // UNION ALL with a followup SELECT
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to named placeholders
                'bar' => 'bar_val',
                'baz' => 'baz_val',
            ]);

        $sth = $this->pdo->prepare($this->select->getStatement());

        // bind the values and execute
        $sth->execute($this->select->getBindValues());
        $result = $sth->fetch(\PDO::FETCH_ASSOC);
        // or
        // $result = $this->pdo->fetchAssoc($stm, $bind);
```

The created queries are queried as strings with the `getStatement()`.

### INSERT

### Single row INSERT


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlInsertInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlInsertInject;

    public function onPost()
    {
        $this->insert
            ->into('foo')                   // INTO this table
            ->cols([                        // bind values as "(col) VALUES (:col)"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values
                'bar' => 'foo',
                'baz' => 'zim',
            ]);

        $sth = $this->pdo->prepare($this->insert->getStatement());
        $sth->execute($this->insert->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->insert->getStatement(), this->insert->getBindValues());

        // get the last insert ID
        $name = $insert->getLastInsertIdName('id');
        $id = $pdo->lastInsertId($name);
```

The `cols()` method allows you to pass an array of key-value pairs where the key is the column name and the value is a bind value (not a raw value).

```php?start_inline
        $this->insert
            ->into('foo')                   // insert into this table
            ->cols([                        // insert these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
```

### Multi-line INSERT

To do a multiple row INSERT, use the `addRow ()` method at the end of the first line. Then build the following query.

```php?start_inline
        // insert into this table
        $this->insert->into('foo');

        // set up the first row
        $this->insert->cols([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $this->insert->set('ts', 'NOW()');

        // set up the second row. the columns here are in a different order
        // than in the first row, but it doesn't matter; the INSERT object
        // keeps track and builds them the same order as the first row.
        $this->insert->addRow();
        $this->insert->set('ts', 'NOW()');
        $this->insert->cols([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);

        // set up further rows ...
        $this->insert->addRow();
        // ...

        // execute a bulk insert of all rows
        $sth = $this->pdo->prepare($insert->getStatement());
        $sth->execute($insert->getBindValues());

```

> Note: If you try to add a row without specifying the value of the first column in the first row, an exception will be thrown.
> Passing an associative array of columns to `addRow()` will be used on the next line. That is, you can not specify `col()` or `cols()` on the first line.

```php?start_inline
        // set up the first row
        $insert->addRow([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $insert->set('ts', 'NOW()');

        // set up the second row
        $insert->addRow([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);
        $insert->set('ts', 'NOW()');

        // etc.
```

You can also set the database at once using `addRows()`.

```php?start_inline
        $rows = [
            [
                'bar' => 'bar-0',
                'baz' => 'baz-0'
            ],
            [
                'bar' => 'bar-1',
                'baz' => 'baz-1'
            ],
        ];
        $this->insert->addRows($rows);
```

### UPDATE
Use the following methods to construct an UPDATE query. You can also call the method multiple times in any order.

```php?start_inline
        $this->update
            ->table('foo')                  // update this table
            ->cols([                        // bind values as "SET bar = :bar"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->update->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->update->getStatement(), $this->update->getBindValues());
```

You can also pass an associative array to `cols()` with the key as the column name and the value as the bound value (not the RAW value).

```php?start_inline

        $this-update->table('foo')          // update this table
            ->cols([                        // update these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
?>
```

### DELETE
Use the following methods to construct a DELETE query. You can also call the method multiple times in any order.
```php?start_inline
        $this->delete
            ->from('foo')                   // FROM this table
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->delete->getBindValues());
```

### Pagination

[ray/aura-sql-module](https://packagist.org/packages/ray/aura-sql-module) supports pagination (page splitting) in both Ray.Sql raw SQL and Ray.AuraSqlQuery query builder.
We create a pager using the `newInstance()` with a uri_template, binding values and the number of items per page. You can access the page by $page[$number].

### Aura.Sql
AuraSqlPagerFactoryInterface

```php?start_inline
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $sql, $params, 10, '/?page={page}&category=sports'); // 10 items per page
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
// $page->data // sliced data (array|\Traversable)
// $page->current; (int)
// $page->total (int)
// $page->hasNext (bool)
// $page->hasPrevious (bool)
// $page->maxPerPage; (int)
// (string) $page // pager html (string)
```

## Aura.SqlQuery
AuraSqlQueryPagerFactoryInterface

```php?start_inline
// for Select
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlQueryPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $select, 10, '/?page={page}&category=sports');
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
```
> Note: Although the Aura.Sql edits the raw SQL directly, it currently only supports the MySQL LIMIT clause format.

`$page` is iterable.

```php?start_inline
foreach ($page as $row) {
 // Process each row
}
```
To change the pager HTML template, change the binding of `TemplateInterface`.
For details about templates, please see [Pagerfanta](https://github.com/whiteoctober/Pagerfanta#views).

```php?start_inline
use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap3Template;
use Ray\AuraSqlModule\Annotation\PagerViewOption;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->bind(TemplateInterface::class)->to(TwitterBootstrap3Template::class);
        $this->bind()->annotatedWith(PagerViewOption::class)->toInstance($pagerViewOption);
    }
}
```


# CakeDb

**CakeDb** is an ORM using the active record and data mapper pattern idea. It is the same as the one provided in CakePHP3.

Install `Ray.CakeDbModule` with composer.

```bash
composer require ray/cake-database-module ~1.0
```

Please refer to [Ray.CakeDbModule](https://github.com/ray-di/Ray.CakeDbModule) for installation and refer to [CakePHP3 Database Access & ORM](http://book.cakephp.org/3.0/en/orm.html) for the ORM usage.

Ray.CakeDbModule is provided by Jose ([@lorenzo](https://github.com/lorenzo)) who developed the ORM of CakePHP3.

## Connection settings

Use the [phpdotenv](https://github.com/vlucas/phpdotenv) library etc. to set the connection according to the environment destination. Please see the [Ex.Package](https://github.com/BEARSunday/Ex.Package) for implementation.


# Doctrine DBAL

[Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html) is also abstraction layer for database.

Install `Ray.DbalModule` with composer.

```bash
composer require ray/dbal-module
```

Install `DbalModule` in application module.

```php?start_inline
use BEAR\Package\AbstractAppModule;
use Ray\DbalModule\DbalModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new DbalModule('driver=pdo_sqlite&memory=true'));
    }
}
```

New DI bindings are now ready and `$this->db` can be injected with the `DbalInject` trait.

```php?start_inline
use Ray\DbalModule\DbalInject;

class Index
{
    use DbalInject;

    public function onGet()
    {
        return $this->db; // \Doctrine\DBAL\Driver\Connection
    }
}
```

### Connect to multiple databases

To connect to multiple databases, specify the identifier as the second argument.

```php?start_inline
$this->install(new DbalModule($logDsn, 'log_db');
$this->install(new DbalModule($jobDsn, 'job_db');
```

```php?start_inline
/**
 * @Inject
 * @Named("log_db")
 */
public function setLogDb(Connection $logDb)
```

[MasterSlaveConnection](http://www.doctrine-project.org/api/dbal/2.0/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html) is provided for master/slave connections.


# Ray.MediaQuery

`Ray.QueryModule` makes a query to an external media such as a database or Web API with a function object to be injected.

## Motivation

* You can have a clear boundary between domain layer (usage code) and infrastructure layer (injected function) in code.
* Execution objects are generated automatically so you do not need to write procedural code for execution.
* Since usage codes are indifferent to the actual state of external media, storage can be changed later. Easy parallel development and stabbing.

## Composer install

    $ composer require ray/media-query

## Getting Started

Define the interface for media access.

### DB

Specify the SQL ID with the attribute `DbQuery`.

```php
interface TodoAddInterface
{
    #[DbQuery('user_add')]
    public function add(string $id, string $title): void;
}
```

### Web API

Specify the Web request ID with the attribute `WebQuery`.

```php
interface PostItemInterface
{
    #[WebQuery('user_item')]
    public function get(string $id): array;
}
```

Create the web api path list file as `web_query.json`.

```json
{
    "$schema": "https://ray-di.github.io/Ray.MediaQuery/schema/web_query.json",
    "webQuery": [
        {"id": "user_item", "method": "GET", "path": "https://{domain}/users/{id}"}
    ]
}
```

### Module

MediaQueryModule binds the execution of SQL and Web API requests to an interface by setting `DbQueryConfig` or `WebQueryConfig` or both.

```php
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\MediaQuery\ApiDomainModule;
use Ray\MediaQuery\DbQueryConfig;
use Ray\MediaQuery\MediaQueryModule;
use Ray\MediaQuery\Queries;
use Ray\MediaQuery\WebQueryConfig;

protected function configure(): void
{
    $this->install(
        new MediaQueryModule(
            Queries::fromDir('/path/to/queryInterface'),[
                new DbQueryConfig('/path/to/sql'),
                new WebQueryConfig('/path/to/web_query.json', ['domain' => 'api.exmaple.com'])
            ],
        ),
    );
    $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', 'username', 'password'));
}
```

MediaQueryModule requires AuraSqlModule to be installed.

### Request object injection

You don't need to provide any implementation classes. It will be generated and injected.

```php
class Todo
{
    public function __construct(
        private TodoAddInterface $todoAdd
    ) {}

    public function add(string $id, string $title): void
    {
        $this->todoAdd->add($id, $title);
    }
}
```

### Notes

#### DbQuery

SQL execution is mapped to a method, and the SQL specified by ID is bound and executed by the method argument.
For example, if the ID is `todo_item`, `todo_item.sql` SQL statement will be executed with `['id => $id]` bound.

* Prepare the SQL file in the `$sqlDir` directory.

#### Entity

* The SQL execution result can be hydrated to the entity class with `entity` parameter

```php
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class, type:'row')]
    public function getItem(string $id): Todo;
}
```
```php
final class Todo
{
    public string $id;
    public string $title;
}
```

Use `CameCaseTrait` to convert a property to camelCase.

```php
use Ray\MediaQuery\CamelCaseTrait;

class Invoice
{
    use CamelCaseTrait;

    public $userName;
}
```

If the entity has a constructor, the constructor will be called with the fetched data.

```php
final class Todo
{
    public function __construct(
        public string $id,
        public string $title
    ) {}
}
```

#### type: 'row'

If the return value of SQL execution is a single row, specify the attribute `type: 'row'`. However, if the return value of the interface is an entity class, it can be omitted. [^v0dot5].

[^v0dot5]: Until the previous version `0.5`, the SQL file was identified by its name as follows:" If the return value of the SQL execution is a single row, add a postfix of `item`; if it is multiple rows, add a postfix of `list`."

```php
/** If the return value is Entity */
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class)]
    public function getItem(string $id): Todo;
}
```

```php
/** If the return value is array */
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class, type: 'row')]
    public function getItem(string $id): array;
}
```

#### Web API

* Customization such as header for authentication is done by binding Guzzle's `ClinetInterface`.

```php
$this->bind(ClientInterface::class)->toProvider(YourGuzzleClientProvicer::class);
```

## Parameters

### DateTime

You can pass a value object as a parameter.
For example, you can specify a `DateTimeInterface` object like this.

```php
interface TaskAddInterface
{
    public function __invoke(string $title, DateTimeInterface $cratedAt = null): void;
}
```

The value will be converted to a date formatted string at SQL execution time or Web API request time.

```sql
INSERT INTO task (title, created_at) VALUES (:title, :createdAt); # 2021-2-14 00:00:00
```

If no value is passed, the bound current time will be injected.
This eliminates the need to hard-code `NOW()` inside SQL and pass the current time every time.

### Test clock

When testing, you can also use a single time binding for the `DateTimeInterface`, as shown below.

```php
$this->bind(DateTimeInterface::class)->to(UnixEpochTime::class);
```

## VO

If a value object other than `DateTime` is passed, the return value of the `ToScalar()` method that implements the `toScalar` interface or the `__toString()` method will be the argument.

```php
interface MemoAddInterface
{
    public function __invoke(string $memo, UserId $userId = null): void;
}
```

```php
class UserId implements ToScalarInterface
{
    public function __construct(
        private readonly LoginUser $user;
    ){}
    
    public function toScalar(): int
    {
        return $this->user->id;
    }
}
```

```sql
INSERT INTO memo (user_id, memo) VALUES (:user_id, :memo);
```

### Parameter Injection

Note that the default value of `null` for the value object argument is never used in SQL. If no value is passed, the scalar value of the value object injected with the parameter type will be used instead of null.

```php
public function __invoke(Uuid $uuid = null): void; // UUID is generated and passed.
````

## Pagination

The `#[Pager]` annotation allows paging of SELECT queries.

```php
use Ray\MediaQuery\PagesInterface;

interface TodoList
{
    #[DbQuery, Pager(perPage: 10, template: '/{?page}')]
    public function __invoke(): PagesInterface;
}
```

You can get the number of pages with `count()`, and you can get the page object with array access by page number.
`Pages` is a SQL lazy execution object.

```php
$pages = ($todoList)();
$cnt = count($pages); // When count() is called, the count SQL is generated and queried.
$page = $pages[2]; // A page query is executed when an array access is made.

// $page->data // sliced data
// $page->current;
// $page->total
// $page->hasNext
// $page->hasPrevious
// $page->maxPerPage;
// (string) $page // pager html
```

# SqlQuery

If you pass a `DateTimeIntetface` object, it will be converted to a date formatted string and queried.

```php
$sqlQuery->exec('memo_add', ['memo' => 'run', 'created_at' => new DateTime()]);
```

When an object is passed, it is converted to a value of `toScalar()` or `__toString()` as in Parameter Injection.

## Get* Method

To get the SELECT result, use `get*` method depending on the result you want to get.

```php
$sqlQuery->getRow($queryId, $params); // Result is a single row
$sqlQuery->getRowList($queryId, $params); // result is multiple rows
$statement = $sqlQuery->getStatement(); // Retrieve the PDO Statement
$pages = $sqlQuery->getPages(); // Get the pager
```

Ray.MediaQuery contains the [Ray.AuraSqlModule](https://github.com/ray-di/Ray.AuraSqlModule).
If you need more lower layer operations, you can use Aura.Sql's [Query Builder](https://github.com/ray-di/Ray.AuraSqlModule#query-builder) or [Aura.Sql](https://github.com/auraphp/Aura.Sql) which extends PDO.
[doctrine/dbal](https://github.com/ray-di/Ray.DbalModule) is also available.

## Profiler

Media accesses are logged by a logger. By default, a memory logger is bound to be used for testing.

```php
public function testAdd(): void
{
    $this->sqlQuery->exec('todo_add', $todoRun);
    $this->assertStringContainsString('query: todo_add({"id": "1", "title": "run"})', (string) $this->log);
}
```

Implement your own [MediaQueryLoggerInterface](src/MediaQueryLoggerInterface.php) and run
You can also implement your own [MediaQueryLoggerInterface](src/MediaQueryLoggerInterface.php) to benchmark each media query and log it with the injected PSR logger.

## Annotations / Attributes

You can use either [doctrine annotations](https://github.com/doctrine/annotations/) or [PHP8 attributes](https://www.php.net/manual/en/language.attributes.overview.php) can both be used.
The next two are the same.

```php
use Ray\MediaQuery\Annotation\DbQuery;

#[DbQuery('user_add')]
public function add1(string $id, string $title): void;

/** @DbQuery("user_add") */
public function add2(string $id, string $title): void;
```



# Database

`Aura.Sql`、`Doctrine DBAL`, `CakeDB` modules are available for database connections.

## Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql) is an Aura database library that extends from `PDO` .

### Installation

Install `Ray.AuraSqlModule` via composer.

```bash
composer require ray/aura-sql-module
```

Installing `AuraSqlModule` in your application module`src/Module/AppModule.php`.

```php?start_inline
use BEAR\Package\AbstractAppModule;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\AuraSqlModule\AuraSqlModule; // add this line

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        // Add the below install method call and contents
        $this->install(
            new AuraSqlModule(
                'mysql:host=localhost;dbname=test',
                'username',
                'password',
                // $options,
                // $attributes
            )
        );
        $this->install(new PackageModule));
    }
}
```

Now the `DI` bindings are ready. The db object will be injected via a constructor or the `AuraSqlInject` setter trait.

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;

class Index
{
    public function __construct(ExtendedPdoInterface $pdo)
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;

class Index
{
    use AuraSqlInject;

    public function onGet()
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```

`Ray.AuraSqlModule` contains [Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) to help you build sql queries.
[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) also have other useful methods like [Array Quoting](https://github.com/auraphp/Aura.Sql/tree/2.x#array-quoting), [fetch*()](https://github.com/auraphp/Aura.Sql/tree/2.x#new-fetch-methods), [perform()](https://github.com/auraphp/Aura.Sql/tree/2.x#the-perform-method) and [yield*()](https://github.com/auraphp/Aura.Sql/tree/2.x#new-yield-methods) that you can use for your needs, please check their documentation.

## Replication

To automatically perform master / slave connection, specify the IP of the slave DB as the fourth argument.

```php?start_inline
$this->install(
  new AuraSqlModule(
    'mysql:host=localhost;dbname=test',
    'username',
    'password',
    'slave1,slave2' // specify slave IP as a comma separated value
  )
);
```

You will now have a slave db connection when using HTTP GET, or a master db connection in other HTTP methods.

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;
use PDO;

class User extends ResourceObject
{
    public $pdo;

    public function __construct(ExtendedPdoInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    public function onGet()
    {
         $this->pdo; // slave db
    }

    public function onPost($todo)
    {
         $this->pdo; // master db
    }
}
```

`$this->pdo` is overwritten if the method is annotated with`@ReadOnlyConnection` or`@WriteConnection`. The master / slave db connection corresponds to the annotation.

```php?start_inline
use Ray\AuraSqlModule\Annotation\ReadOnlyConnection;  // important
use Ray\AuraSqlModule\Annotation\WriteConnection;     // important

class User
{
    public $pdo; // override when @ReadOnlyConnection or @WriteConnection annotated method called

    public function onPost($todo)
    {
         $this->read();
    }

    /**
     * @ReadOnlyConnection
     */
    public function read()
    {
         $this->pdo; // slave db
    }

    /**
     * @WriteConnection
     */
    public function write()
    {
         $this->pdo; // master db
    }
}
```

## Connect to multiple databases

To receive multiple `PdoExtendedInterface` objects with different connection destinations, use `@Named` annotation.

```php?start_inline
/**
 * @Inject
 * @Named("log_db")
 */
public function setLoggerDb(ExtendedPdoInterface $pdo)
{
    // ...
}
```

Specify an identifier with `NamedPdoModule` and bind it.

```php?start_inline
$this->install(new NamedPdoModule('log_db', 'mysql:host=localhost;dbname=log', 'username',
$this->install(new NamedPdoModule('job_db', 'mysql:host=localhost;dbname=job', 'username',
```

In the module, you specify an identifier in `NamedPdoModule` and bind it.

```php?start_inline
$this->install(
  new NamedPdoModule(
    'log_db', // Type of database specified by @Named
    'mysql:host=localhost;dbname=log',
    'username',
    'pass',
    'slave1,slave2' // specify slave IP as a comma separated value
  )
);
```

## Transactions

Using the `@Transactional` annotation wraps methods with a transaction.

```php?start_inline
use Ray\AuraSqlModule\Annotation\Transactional;

// ....
    /**
     * @Transactional
     */
    public function write()
    {
         // \Ray\AuraSqlModule\Exception\RollbackException thrown if it failed.
    }
```

To do transactions on multiple connected databases, specify properties in the `@Transactional` annotation.
If not specified, it becomes `{"pdo"}`.

```php?start_inline
/**
 * @Transactional({"pdo", "userDb"})
 */
public function write()
```

It is run as follows.

```php?start_inline
$this->pdo->beginTransaction()
$this->userDb->beginTransaction()

// ...

$this->pdo->commit();
$this->userDb->commit();
```

## Aura.SqlQuery

[Aura.Sql](https://github.com/auraphp/Aura.Sql) is an extension of PDO. [Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) provides database-specific SQL builder for MySQL, Postgres, SQLite or Microsoft SQL Server.

Specify the database and install it with the application module `src/Module/AppModule.php`.

```php?start_inline
// ...
$this->install(new AuraSqlQueryModule('mysql')); // pgsql, sqlite, or sqlsrv
```

### SELECT

The resource receives the DB Query Builder object and constructs a SELECT query using the following methods.
You can also call the method multiple times in any order.

```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlSelectInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlSelectInject;

    public function onGet()
    {
        $this->select
            ->distinct()                    // SELECT DISTINCT
            ->cols([                        // select these columns
                'id',                       // column name
                'name AS namecol',          // one way of aliasing
                'col_name' => 'col_alias',  // another way of aliasing
                'COUNT(foo) AS foo_count'   // embed calculations directly
            ])
            ->from('foo AS f')              // FROM these tables
            ->fromSubselect(                // FROM sub-select AS my_sub
                'SELECT ...',
                'my_sub'
            )
            ->join(                         // JOIN ...
                'LEFT',                     // left/inner/natural/etc
                'doom AS d'                 // this table name
                'foo.id = d.foo_id'         // ON these conditions
            )
            ->joinSubSelect(                // JOIN to a sub-select
                'INNER',                    // left/inner/natural/etc
                'SELECT ...',               // the subselect to join on
                'subjoin'                   // AS this name
                'sub.id = foo.id'           // ON these conditions
            )
            ->where('bar > :bar')           // AND WHERE these conditions
            ->where('zim = ?', 'zim_val')   // bind 'zim_val' to the ? placeholder
            ->orWhere('baz < :baz')         // OR WHERE these conditions
            ->groupBy(['dib'])              // GROUP BY these columns
            ->having('foo = :foo')          // AND HAVING these conditions
            ->having('bar > ?', 'bar_val')  // bind 'bar_val' to the ? placeholder
            ->orHaving('baz < :baz')        // OR HAVING these conditions
            ->orderBy(['baz'])              // ORDER BY these columns
            ->limit(10)                     // LIMIT 10
            ->offset(40)                    // OFFSET 40
            ->forUpdate()                   // FOR UPDATE
            ->union()                       // UNION with a followup SELECT
            ->unionAll()                    // UNION ALL with a followup SELECT
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to named placeholders
                'bar' => 'bar_val',
                'baz' => 'baz_val',
            ]);

        $sth = $this->pdo->prepare($this->select->getStatement());

        // bind the values and execute
        $sth->execute($this->select->getBindValues());
        $result = $sth->fetch(\PDO::FETCH_ASSOC);
        // or
        // $result = $this->pdo->fetchAssoc($stm, $bind);
```

The created queries are queried as strings with the `getStatement()`.

### INSERT

### Single row INSERT


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlInsertInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlInsertInject;

    public function onPost()
    {
        $this->insert
            ->into('foo')                   // INTO this table
            ->cols([                        // bind values as "(col) VALUES (:col)"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values
                'bar' => 'foo',
                'baz' => 'zim',
            ]);

        $sth = $this->pdo->prepare($this->insert->getStatement());
        $sth->execute($this->insert->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->insert->getStatement(), this->insert->getBindValues());

        // get the last insert ID
        $name = $insert->getLastInsertIdName('id');
        $id = $pdo->lastInsertId($name);
```

The `cols()` method allows you to pass an array of key-value pairs where the key is the column name and the value is a bind value (not a raw value).

```php?start_inline
        $this->insert
            ->into('foo')                   // insert into this table
            ->cols([                        // insert these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
```

### Multi-line INSERT

To do a multiple row INSERT, use the `addRow ()` method at the end of the first line. Then build the following query.

```php?start_inline
        // insert into this table
        $this->insert->into('foo');

        // set up the first row
        $this->insert->cols([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $this->insert->set('ts', 'NOW()');

        // set up the second row. the columns here are in a different order
        // than in the first row, but it doesn't matter; the INSERT object
        // keeps track and builds them the same order as the first row.
        $this->insert->addRow();
        $this->insert->set('ts', 'NOW()');
        $this->insert->cols([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);

        // set up further rows ...
        $this->insert->addRow();
        // ...

        // execute a bulk insert of all rows
        $sth = $this->pdo->prepare($insert->getStatement());
        $sth->execute($insert->getBindValues());

```

> Note: If you try to add a row without specifying the value of the first column in the first row, an exception will be thrown.
> Passing an associative array of columns to `addRow()` will be used on the next line. That is, you can not specify `col()` or `cols()` on the first line.

```php?start_inline
        // set up the first row
        $insert->addRow([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $insert->set('ts', 'NOW()');

        // set up the second row
        $insert->addRow([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);
        $insert->set('ts', 'NOW()');

        // etc.
```

You can also set the database at once using `addRows()`.

```php?start_inline
        $rows = [
            [
                'bar' => 'bar-0',
                'baz' => 'baz-0'
            ],
            [
                'bar' => 'bar-1',
                'baz' => 'baz-1'
            ],
        ];
        $this->insert->addRows($rows);
```

### UPDATE
Use the following methods to construct an UPDATE query. You can also call the method multiple times in any order.

```php?start_inline
        $this->update
            ->table('foo')                  // update this table
            ->cols([                        // bind values as "SET bar = :bar"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->update->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->update->getStatement(), $this->update->getBindValues());
```

You can also pass an associative array to `cols()` with the key as the column name and the value as the bound value (not the RAW value).

```php?start_inline

        $this-update->table('foo')          // update this table
            ->cols([                        // update these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
?>
```

### DELETE
Use the following methods to construct a DELETE query. You can also call the method multiple times in any order.
```php?start_inline
        $this->delete
            ->from('foo')                   // FROM this table
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->delete->getBindValues());
```

### Pagination

[ray/aura-sql-module](https://packagist.org/packages/ray/aura-sql-module) supports pagination (page splitting) in both Ray.Sql raw SQL and Ray.AuraSqlQuery query builder.
We create a pager using the `newInstance()` with a uri_template, binding values and the number of items per page. You can access the page by $page[$number].

### Aura.Sql
AuraSqlPagerFactoryInterface

```php?start_inline
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $sql, $params, 10, '/?page={page}&category=sports'); // 10 items per page
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
// $page->data // sliced data (array|\Traversable)
// $page->current; (int)
// $page->total (int)
// $page->hasNext (bool)
// $page->hasPrevious (bool)
// $page->maxPerPage; (int)
// (string) $page // pager html (string)
```

## Aura.SqlQuery
AuraSqlQueryPagerFactoryInterface

```php?start_inline
// for Select
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlQueryPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $select, 10, '/?page={page}&category=sports');
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
```
> Note: Although the Aura.Sql edits the raw SQL directly, it currently only supports the MySQL LIMIT clause format.

`$page` is iterable.

```php?start_inline
foreach ($page as $row) {
 // Process each row
}
```
To change the pager HTML template, change the binding of `TemplateInterface`.
For details about templates, please see [Pagerfanta](https://github.com/whiteoctober/Pagerfanta#views).

```php?start_inline
use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap3Template;
use Ray\AuraSqlModule\Annotation\PagerViewOption;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->bind(TemplateInterface::class)->to(TwitterBootstrap3Template::class);
        $this->bind()->annotatedWith(PagerViewOption::class)->toInstance($pagerViewOption);
    }
}
```

## Doctrine DBAL

[Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html) is also abstraction layer for database.

Install `Ray.DbalModule` with composer.

```bash
composer require ray/dbal-module
```

Install `DbalModule` in application module.

```php?start_inline
use BEAR\Package\AbstractAppModule;
use Ray\DbalModule\DbalModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new DbalModule('driver=pdo_sqlite&memory=true');
    }
}
```

New DI bindings are now ready and `$this->db` can be injected with the `DbalInject` trait.

```php?start_inline
use Ray\DbalModule\DbalInject;

class Index
{
    use DbalInject;

    public function onGet()
    {
        return $this->db; // \Doctrine\DBAL\Driver\Connection
    }
}
```

### Connect to multiple databases

To connect to multiple databases, specify the identifier as the second argument.

```php?start_inline
$this->install(new DbalModule($logDsn, 'log_db');
$this->install(new DbalModule($jobDsn, 'job_db');
```

```php?start_inline
/**
 * @Inject
 * @Named("log_db")
 */
public function setLogDb(Connection $logDb)
```

[MasterSlaveConnection](http://www.doctrine-project.org/api/dbal/2.0/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html) is provided for master/slave connections.

## CakeDb

**CakeDb** is an ORM using the active record and data mapper pattern idea. It is the same as the one provided in CakePHP3.

Install `Ray.CakeDbModule` with composer.

```bash
composer require ray/cake-database-module ~1.0
```

Please refer to [Ray.CakeDbModule](https://github.com/ray-di/Ray.CakeDbModule) for installation and refer to [CakePHP3 Database Access & ORM](http://book.cakephp.org/3.0/en/orm.html) for the ORM usage.

Ray.CakeDbModule is provided by Jose ([@lorenzo](https://github.com/lorenzo)) who developed the ORM of CakePHP3.

## Connection settings

Use the [phpdotenv](https://github.com/vlucas/phpdotenv) library etc. to set the connection according to the environment destination. Please see the [Ex.Package](https://github.com/BEARSunday/Ex.Package) for implementation.

Redis Cache Adapter
===================

    This article explains how to configure the Redis adapter when using the
    Cache as an independent component in any PHP application. Read the
    :ref:`Symfony Cache configuration <cache-configuration-with-frameworkbundle>`
    article if you are using it in a Symfony application.

This adapter stores the values in-memory using one (or more) `Redis server`_ instances.

Unlike the :doc:`APCu adapter </components/cache/adapters/apcu_adapter>`, and similarly to the
:doc:`Memcached adapter </components/cache/adapters/memcached_adapter>`, it is not limited to the current server's
shared memory; you can store contents independent of your PHP environment. The ability
to utilize a cluster of servers to provide redundancy and/or fail-over is also available.

.. caution::

    **Requirements:** At least one `Redis server`_ must be installed and running to use this
    adapter. Additionally, this adapter requires a compatible extension or library that implements
    ``\Redis``, ``\RedisArray``, ``RedisCluster``, ``\Relay\Relay`` or ``\Predis``.

This adapter expects a `Redis`_, `RedisArray`_, `RedisCluster`_, `Relay`_ or `Predis`_ instance to be
passed as the first parameter. A namespace and default cache lifetime can optionally be passed
as the second and third parameters::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    $cache = new RedisAdapter(

        // the object that stores a valid connection to your Redis system
        \Redis $redisConnection,

        // the string prefixed to the keys of the items stored in this cache
        $namespace = '',

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until RedisAdapter::clear() is invoked or the server(s) are purged)
        $defaultLifetime = 0
    );

Configure the Connection


The :method:`Symfony\\Component\\Cache\\Traits\\RedisTrait::createConnection`
helper method allows creating and configuring the Redis client class instance using a
`Data Source Name (DSN)`_::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    // pass a single DSN string to register a single server with the client
    $client = RedisAdapter::createConnection(
        'redis://localhost'
    );

The DSN can specify either an IP/host (and an optional port) or a socket path, as well as a
password and a database index. To enable TLS for connections, the scheme ``redis`` must be
replaced by ``rediss`` (the second ``s`` means "secure").

.. note::

    A `Data Source Name (DSN)`_ for this adapter must use either one of the following formats.

    .. code-block:: text

        redis[s]://[pass@][ip|host|socket[:port]][/db-index]

    .. code-block:: text

        redis[s]:[[user]:pass@]?[ip|host|socket[:port]][&params]

    Values for placeholders ``[user]``, ``[:port]``, ``[/db-index]`` and ``[&params]`` are optional.

Below are common examples of valid DSNs showing a combination of available values::

    use Symfony\Component\Cache\Adapter\RedisAdapter;

    // host "my.server.com" and port "6379"
    RedisAdapter::createConnection('redis://my.server.com:6379');

    // host "my.server.com" and port "6379" and database index "20"
    RedisAdapter::createConnection('redis://my.server.com:6379/20');

    // host "localhost", auth "abcdef" and timeout 5 seconds
    RedisAdapter::createConnection('redis://abcdef@localhost?timeout=5');

    // socket "/var/run/redis.sock" and auth "bad-pass"
    RedisAdapter::createConnection('redis://bad-pass@/var/run/redis.sock');

    // host "redis1" (docker container) with alternate DSN syntax and selecting database index "3"
    RedisAdapter::createConnection('redis:?host[redis1:6379]&dbindex=3');

    // providing credentials with alternate DSN syntax
    RedisAdapter::createConnection('redis:default:verysecurepassword@?host[redis1:6379]&dbindex=3');

    // a single DSN can also define multiple servers
    RedisAdapter::createConnection(
        'redis:?host[localhost]&host[localhost:6379]&host[/var/run/redis.sock:]&auth=my-password&redis_cluster=1'
    );

`Redis Sentinel`_, which provides high availability for Redis, is also supported
when using the PHP Redis Extension v5.2+ or the Predis library. Use the ``redis_sentinel``
parameter to set the name of your service group::

    RedisAdapter::createConnection(
        'redis:?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&redis_sentinel=mymaster'
    );

    // providing credentials
    RedisAdapter::createConnection(
        'redis:default:verysecurepassword@?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&redis_sentinel=mymaster'
    );

    // providing credentials and selecting database index "3"
    RedisAdapter::createConnection(
        'redis:default:verysecurepassword@?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&redis_sentinel=mymaster&dbindex=3'
    );

.. note::

    See the :class:`Symfony\\Component\\Cache\\Traits\\RedisTrait` for more options
    you can pass as DSN parameters.

Configure the Options
--

When using Redis as cache, you should configure the ``maxmemory`` and ``maxmemory-policy``
settings. By setting ``maxmemory``, you limit how much memory Redis is allowed to consume.
If the amount is too low, Redis will drop entries that would still be useful and you benefit
less from your cache. Setting the ``maxmemory-policy`` to ``allkeys-lru`` tells Redis that
it is ok to drop data when it runs out of memory, and to first drop the oldest entries (least
recently used). If you do not allow Redis to drop entries, it will return an error when you
try to add data when no memory is available. An example setting could look as follows:

.. code-block:: ini

    maxmemory 100mb
    maxmemory-policy allkeys-lru

Working with Tags
-----

In order to use tag-based invalidation, you can wrap your adapter in
:class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter`. However, when Redis
is used as backend, it's often more interesting to use the dedicated
:class:`Symfony\\Component\\Cache\\Adapter\\RedisTagAwareAdapter`. Since tag
invalidation logic is implemented in Redis itself, this adapter offers better
performance when using tag-based invalidation::

    use Symfony\Component\Cache\Adapter\RedisAdapter;
    use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;

    $client = RedisAdapter::createConnection('redis://localhost');
    $cache = new RedisTagAwareAdapter($client);

.. note::

    When using RedisTagAwareAdapter, in order to maintain relationships between
    tags and cache items, you have to use either ``noeviction`` or ``volatile-*``
    in the Redis ``maxmemory-policy`` eviction policy.

Read more about this topic in the official `Redis LRU Cache Documentation`_.

.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
.. _`Redis server`: https://redis.io/
.. _`Redis`: https://github.com/phpredis/phpredis
.. _`RedisArray`: https://github.com/phpredis/phpredis/blob/develop/arrays.md
.. _`RedisCluster`: https://github.com/phpredis/phpredis/blob/develop/cluster.md
.. _`Relay`: https://relay.so/
.. _`Predis`: https://packagist.org/packages/predis/predis
.. _`Predis Connection Parameters`: https://github.com/nrk/predis/wiki/Connection-Parameters#list-of-connection-parameters
.. _`TCP-keepalive`: https://redis.io/topics/clients#tcp-keepalive
.. _`Redis Sentinel`: https://redis.io/topics/sentinel
.. _`Redis LRU Cache Documentation`: https://redis.io/topics/lru-cache
.. _`php.net/context.ssl`: https://php.net/context.ssl



# Validation

 * You can define resource APIs in the JSON schema.
 * You can separate the validation code with `@Valid`, `@OnValidate` annotation.
 * Please see the form for validation by web form.

# JSON Schema

The [JSON Schema](http://json-schema.org/) is the standard for describing and validating JSON objects. `@JsonSchema` and the resource body returned by the method of annotated resource class are validated by JSON schema.


### Install

If you want to validate in all contexts including production, create `AppModule`, if validation is done only during development, create `DevModule` and install within it


```php?start_inline
use BEAR\Resource\Module\JsonSchemaModule; // Add this line
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(
            new JsonSchemaModule(
                $appDir . '/var/json_schema',
                $appDir . '/var/json_validate'
            )
        );  // Add this line
    }
}
```
Create directories for the JSON schema files

```bash
mkdir var/json_schema
mkdir var/json_validate
```

In the `var/json_schema/`, store the JSON schema file which is the specification of the body of the resource, and the `var/json_validate/` stores the JSON schema file for input validation.

### @JsonSchema annotation

Annotate the method of the resource class by adding `@JsonSchema`, then add the `schema` property by specifying the JSON schema file name, which is `user.json` for this purpose.

### schema

src/Resource/App/User.php

```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class User extends ResourceObject
{
    #[JsonSchema('user.json')]
    public function onGet(): static
    {
        $this->body = [
            'firstName' => 'mucha',
            'lastName' => 'alfons',
            'age' => 12
        ];

        return $this;
    }
}
```

We will create a JSON schema named `/var/json_schema/user.json`

```json
{
  "type": "object",
  "properties": {
    "firstName": {
      "type": "string",
      "maxLength": 30,
      "pattern": "[a-z\\d~+-]+"
    },
    "lastName": {
      "type": "string",
      "maxLength": 30,
      "pattern": "[a-z\\d~+-]+"
    }
  },
  "required": ["firstName", "lastName"]
}
```

### key

If the body has an index key, specify it with the key property of the annotation

```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class User extends ResourceObject
{
    #[JsonSchema(key:'user', schema:'user.json')]
    public function onGet()
    {
        $this->body = [
            'user' => [
                'firstName' => 'mucha',
                'lastName' => 'alfons',
                'age' => 12
            ]
        ];        

        return $this;
    }
}
```

### params

The `params` property specifies the JSON schema file name for the argument validation


```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class Todo extends ResourceObject
{
    #[JsonSchema(key:'user', schema:'user.json', params:'todo.post.json')]
    public function onPost(string $title)
```

We place the JSON schema file

**/var/json_validate/todo.post.json**

```json
{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "title": "/todo POST request validation",
  "properties": {
    "title": {
      "type": "string",
      "minLength": 1,
      "maxLength": 40
    }
}

```

By constantly verifying in a standardized way instead of proprietary documentation, the specification is **reliable and understandable** to both humans and machines.

### target

To apply schema validation to the representation of the resource object (the rendered result) rather than to the body of the ResourceObject, specify the option `target='view'`.

```php
#[JsonSchema(schema: 'user.json', target: 'view')]
```

### Related Links

 * [Example](http://json-schema.org/examples.html)
 * [Understanding JSON Schema](https://spacetelescope.github.io/understanding-json-schema/)
 * [JSON Schema Generator](https://jsonschema.net/#/editor)

## @Valid annotation

The `@Valid` annotation is a validation for input.
You can set up validation as AOP for your method.
By separating validation logic from the method, the code will be readable and testable.

Validation libraries are available such as [Aura.Filter](https://github.com/auraphp/Aura.Filter), [Respect\Validation](https://github.com/Respect/Validation), and [PHP Standard Filter](http://php.net/manual/en/book.filter.php)

### Install

Install `Ray.ValidateModule` via composer.

```bash
composer require ray/validate-module
```

Installing `ValidateModule` in your application module `src/Module/AppModule.php`.

```php?start_inline
use Ray\Validation\ValidateModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new ValidateModule);
    }
}
```

### Annotation

There are three annotations `@Valid`, `@OnValidate`, `@OnFailure` for validation.

First of all, annotate the method that you want to validate with `@Valid`

```php?start_inline
use Ray\Validation\Annotation\Valid;

class News
{
    /**
     * @Valid
     */
    public function createUser($name)
    {
```

Validation will be conducted in the method annotated with `@OnValidate`.

The arguments of the method should be the same as the original method. The method name can be anything.

```php?start_inline
use Ray\Validation\Annotation\OnValidate;

class News
{
    /**
     * @OnValidate
     */
    public function onValidate($name)
    {
        $validation = new Validation;
        if (! is_string($name)) {
            $validation->addError('name', 'name should be string');
        }

        return $validation;
    }
```

Add validations to your elements by `addError()` with the `element name` and` error message` as parameters, then return the validation object.

When validation fails, the exception `Ray\Validation\Exception\InvalidArgumentException` will be thrown,
but if you have a method annotated with the `@OnFailure`, it will be called, instead of throwing an exception

```php?start_inline
use Ray\Validation\Annotation\OnFailure;

class News
{
    /**
     * @OnFailure
     */
    public function onFailure(FailureInterface $failure)
    {
        // original parameters
        list($this->defaultName) = $failure->getInvocation()->getArguments();

        // errors
        foreach ($failure->getMessages() as $name => $messages) {
            foreach ($messages as $message) {
                echo "Input '{$name}': {$message}" . PHP_EOL;
            }
        }
    }
```

In the method annotated with `@OnFailure`, you can access the validated messages with `$failure->getMessages()`
and also you can get the object of the original method with `$failure->getInvocation()`.

### Various validation

If you want to have different validations for a class, you can specify the name of the validation like below

```php?start_inline
use Ray\Validation\Annotation\Valid;
use Ray\Validation\Annotation\OnValidate;
use Ray\Validation\Annotation\OnFailure;

class News
{
    /**
     * @Valid("foo")
     */
    public function fooAction($name, $address, $zip)
    {

    /**
     * @OnValidate("foo")
     */
    public function onValidateFoo($name, $address, $zip)
    {

    /**
     * @OnFailure("foo")
     */
    public function onFailureFoo(FailureInterface $failure)
    {
```

### Other validation

If you need to implement complex validation, you can have another class for validation and inject it.
And then call in the method annotated with the `onValidate`.
You can also change your validation behavior by context with DI.




# Version

## Supported PHP

[![Continuous Integration](https://github.com/bearsunday/BEAR.SupportedVersions/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.SupportedVersions/actions/workflows/continuous-integration.yml)

BEAR.Sunday supports the following supported PHP versions

* `8.1` (Old stable 25 Nov 2021 - 25 Nov 2024)
* `8.2` (Old stable 8 Dec 2022 - 8 Dec 2025)
* `8.3` (Current stable 23 Nov 2022 - 26 Nov 2026)

* End of life ([EOL](http://php.net/eol.php))

* `5.5` (21 Jul 2016)
* `5.6` (31 Dec 2018)
* `7.0` (3 Dec 2018)
* `7.1` (1 Dec 2019)
* `7.2` (30 Nov 2020)
* `7.3` (6 Dec 2021)
* `7.4` (28 Nov 2022)
* `8.0` (26 Nov 2023)

The new optional package will be developed based on the current stable PHP. We encourage you to use the current stable PHP for quality, performance and security.

[BEAR.SupportedVersions](https://github.com/bearsunday/BEAR.SupportedVersions/), you can check the tests for each version in CI.

## Semver

BEAR.Sunday follows [Semantic Versioning](http://
semper.org/lang/en/). It is not necessary to modify the application code on minor version upgrades.

`composer update` can be done at any time for packages.

## Version Policy

 * The core package of the framework does not make a breaking change which requires change of user code.
 * Since it does not do destructive change, it handles unnecessary old ones as `deprecetad` but does not delete and new functions are always "added".
 * When PHP comes to an EOL and upgraded to a major version (ex. `5.6` →` 7.0`), BEAR.Sunday will not break the BC of the application code. Even though the version number of PHP that is necessary to use the new module becomes higher, changes to the application codes are not needed.

BEAR.Sunday emphasizes clean code and **longevity**.

## Package version

The version of the framework does not lock the version of the library. The library can be updated regardless of the version of the framework.



# HTML

The following template engines are available for HTML representation.

* [Twig v1](html-twig-v1.html)
* [Twig v2](html-twig-v2.html)
* [Qiq](html-qiq.html)

## Twig vs Qiq

[Twig](https://twig.symfony.com) was first released in 2009 and has a large user base. [Qiq](https://qiqphp.github.io) is a new template engine released in 2021.

Twig uses implicit escaping by default and has custom syntax for control structures. In contrast, Qiq requires explicit escaping and uses PHP syntax as the base template language. Twig has a large codebase and rich features, while Qiq is compact and simple. (Using pure PHP syntax in Qiq makes it IDE and static analysis-friendly, although it may be redundant.)

### Syntax Comparison

PHP
```php
<?= htmlspecialchars($var, ENT_QUOTES|ENT_DISALLOWED, 'utf-8') ?>
<?= htmlspecialchars(helper($var, ENT_QUOTES|ENT_DISALLOWED, 'utf-8')) ?>
<?php foreach ($users => $user): ?>
 * <?= $user->name; ?>
<?php endforeach; ?>
```

Twig

```
{% raw %}{{ var | raw }}
{{ var }}
{{ var | helper }}
{% for user in users %}
  * {{ user.name }}
{% endfor %}{% endraw %}
```

Qiq

```
{% raw %}{{% var }}
{{h $var }}
{{h helper($var) }}
{{ foreach($users => $user) }}
  * {{h $user->name }}
{{ endforeach }}

{{ var }} // Not displayed {% endraw %}
```
```php
<?php /** @var Template $this */ ?>
<?= $this->h($var) ?>
```

## Renderer

The renderer, bound to `RenderInterface` and injected into the ResourceObject, generates the representation of the resource. The resource itself is agnostic about its representation.

Since the renderer is injected per resource, it is possible to use multiple template engines simultaneously.

## Halo UI for Development

During development, you can render a UI element called Halo [^halo] around the rendered resource. Halo provides information about the resource's state, representation, and applied interceptors. It also provides links to open the corresponding resource class or resource template in PHPStorm.

[^halo]: The name is derived from a similar feature in the [Seaside](https://github.com/seasidest/seaside) framework for Smalltalk.

<img src="https://user-images.githubusercontent.com/529021/211504531-37cd4a8d-80b3-4d77-903f-c8f5baf5dc37.png" alt="Halo displays resource state" width="50%">

<link href="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css" rel="stylesheet">

* <span class="glyphicon glyphicon-home" rel="tooltip" title="Home"></span> Halo Home (Border and Tools Display)
* <span class="glyphicon glyphicon-zoom-in" rel="tooltip" title="Status"></span> Resource State
* <span class="glyphicon glyphicon-font" rel="tooltip" title="View"></span> Resource Representation
* <span class="glyphicon glyphicon-info-sign" rel="tooltip" title="Info"></span> Profile

You can try a demo of Halo in the [demo](/docs/demo/halo/).

### Performance Monitoring

Halo also displays performance information about the resource, including execution time, memory usage, and a link to the profiler.

<img src="https://user-images.githubusercontent.com/529021/212373901-fce7b2fd-41b0-478f-9d36-5e2eb3b97d9c.png" alt="Halo displays performance"  width="50%">

### Installation

To enable profiling, you need to install [xhprof](https://www.php.net/manual/en/intro.xhprof.php), which helps identify performance bottlenecks.

```
pecl install xhprof
// Also add 'extension=xhprof.so' to your php.ini file
```

To visualize and graphically display call graphs, you need to install [graphviz](https://graphviz.org/download/).
Example: [Call Graph Demo](/docs/demo/halo/callgraph.svg)

```
// macOS
brew install graphviz

// Windows
// Download and install the installer from the graphviz website

// Linux (Ubuntu)
sudo apt-get install graphviz
```

In your application, create a Dev context module and install the `HaloModule`.

```php
class DevModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new HaloModule($this));
    }
}
```

---



# HTML (Qiq)

## Setup

Install `bear/qiq-module` in composer to get HTML view in Qiq.

```bash
composer require bear/qiq-module
```

Next, Provide a directory to store templates and helpers.

```
cd /path/to/project
cp -r vendor/bear/qiq-module/var/qiq var
```

Provide the `html` context file `src/Module/HtmlModule.php` and install `QiqModule`.

```php?start_inline
namespace MyVendor\MyPackage\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\QiqModule\QiqModule;


class HtmlModule extends AbstractAppModule
{
    protected function configure()
    {
        $this->install(new QiqModule($this->appMeta->appDir . '/var/qiq/template'));
    }
}```

## Change context

Change the context of `bin/page.php` to enable `html`.

```bash
$context = 'cli-html-app';
```

## Template 

Prepare the template for the Index resource in `var/qiq/template/Page/Index.php`.

```
{% raw %}<h1>{{h $this->greeting }}</h1>{% endraw %}
```

The `$body` of the ResourceObject will be assigned to the template as `$this`.

```bash
php bin/page.php get /
200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
```

## Custom helper

[Custom Helpers](https://qiqphp-ja.github.io/1.x/helpers/custom.html#1-8-4) will be created in the `Qiq\Helper\` namespace. Example: `Qiq\Helper\Foo`.

Specify the `Qiq\Helper` in the `autoload` of composer.json (e.g: [composer.json](https://github.com/bearsunday/BEAR.QiqModule/blob/1.x/demo/composer.json#L26)) and run `composr dump-autoload` to enable to autoload the custom helper class. Custom helpers placed in the specified directory will be made available.


## ProdModule

Install a module in ProdModule to make the error page HTML for production and to enable compiler cache.

```php
class ProdModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new QiqErrorModule);
        $this->install(new QiqProdModule($this->appDir . '/var/tmp');
    }
}
```




# HTML (Twig v1)

In order to have an HTML reprensentation lets install `madapaja/twig-module` with composer.

```bash
composer require madapaja/twig-module ^1.0
```

Next create the context file `src/Module/HtmlModule.php` and install the `TwigModule`.

```php?start_inline
namespace MyVendor\MyPackage\Module;

use BEAR\AppMeta\AppMeta;
use Madapaja\TwigModule\TwigModule;
use BEAR\Package\AbstractAppModule;

class HtmlModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TwigModule);
    }
}
```

Update the context in `bin/page.php` and enable `html`.

```bash
$context = 'cli-html-app';
```
We prepare twig templates by placing them in the same directory as the `page resource` that you want to bind it to. Replace the `.php` suffix with `.html.twig`. So a template for the `Page/Index.php` resource would be `Page/Index.html.twig`.

```hml
{% raw %}<h1>{{ greeting }}</h1>{% endraw %}
```

The `$body` in a resource is assigned to the template and then rendered.

```bash
php bin/page.php get /
200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
```

By default partials and template files are found in `var/lib/twig`.

## Custom Settings

If you would like to change options depending on the context or add a template path, configuration values are bound to `@TwigPaths`and `@TwigOptions` annotations.

```php?start_inline
namespace MyVendor\MyPackage\Module;

use Madapaja\TwigModule\Annotation\TwigOptions;
use Madapaja\TwigModule\Annotation\TwigPaths;
use Madapaja\TwigModule\TwigModule;
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new TwigModule());

        // You can add twig template paths by the following
        $appDir = dirname(dirname(__DIR__));
        $paths = [
            $appDir . '/src/Resource',
            $appDir . '/var/lib/twig'
        ];
        $this->bind()->annotatedWith(TwigPaths::class)->toInstance($paths);

        // Also you can set environment options
        // @see http://twig.sensiolabs.org/doc/api.html#environment-options
        $options = [
            'debug' => false,
            'cache' => $appDir . '/tmp'
        ];
        $this->bind()->annotatedWith(TwigOptions::class)->toInstance($options);
    }
}
```

## Other template engines

You can not only select a template engine, but you can also provide multiple template engines and assign them to different resources.



# HTML (Twig v2)

## Install

In order to have an HTML reprensentation, Let's install [Twig v2](https://twig.symfony.com/doc/2.x/) module with composer.

```bash
composer require madapaja/twig-module ^2.0
```

Next create the context file `src/Module/HtmlModule.php` and install the `TwigModule`.

```php?start_inline
namespace MyVendor\MyPackage\Module;

use BEAR\AppMeta\AppMeta;
use Madapaja\TwigModule\TwigErrorPageModule;
use Madapaja\TwigModule\TwigModule;
use Ray\Di\AbstractModule;

class HtmlModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TwigModule);
        $this->install(new TwigErrorPageModule);
    }
}
```

Update the context in `bin/page.php` or `public/index.php` and enable `html`.

```bash
$context = 'cli-html-app'; // or 'html-app'
```
## Template

One template file is required for one resource object class in `var/templates` directory to represent in HTML.
For example, for `src/Page/Index.php` resource class, a template file is required in `var/templates/Page/Index.html.twig`.

The body of the resource is assigned to the template.

example）

`src/Page/Index.php`

```php
class Index extends ResourceObject
{
    public $body = [
        'greeting' => 'Hello BEAR.Sunday'
    ];
}
```

`var/templates/Page/Index.twig.php`

```twig
{% raw %}<h1>{{ greeting }}</h1>{% endraw %}
```

Output:

```bash
php bin/page.php get /
```

```bash
200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
```
## Select template file

Resource does not select the template file. It `includes` depending on the state of the resource.

```twig{% raw %}
{% if user.is_login %}
    {{ include('member.html.twig') }}
{% else %}
    {{ include('guest.html.twig') }}
{% endif %}{% endraw %}
```

In the resource class, you should only concern resource state. Then template should concern the resource representation.
See [Separation of concerns (SoC)](https://en.wikipedia.org/wiki/Separation_of_concerns).

## Error Page

Edit `var/templates/error.html.twig`. Following values are assigned to the error page.

| Variable | Title | Key |
|||
| status | HTTP status | code, message |
| e | Exception | code, message, class |
| logref | Log ID | n/a |

例

```twig
{% raw %}{% extends 'layout/base.html.twig' %}
{% block title %}{{ status.code }} {{ status.message }}{% endblock %}
{% block content %}
    <h1>{{ status.code }} {{ status.message }}</h1>
    {% if status.code == 404 %}
        <p>The requested URL was not found on this server.</p>
    {% else %}
        <p>The server is temporarily unable to service your request.</p>
        <p>refference number: {{ logref }}</p>
    {% endif %}
{% endblock %}{% endraw %}
```


## Assign resource

To refer to the properties of the resource object class, Use `_ro` (resource object) to which the entire resource object is assigned

exmaple）

`Todos.php`

```php
class Todos extend ResourceObject
{
    public $code = 200;

    public $text = [
        'name' => 'BEAR'
    ];

    public $body = [
        ['title' => 'run']
    ];
}
```

`Todos.html.twig`

```twig
{% raw %}{{ _ro.code }} // 200
{{ _ro.text.name }} // 'BEAR'
{% for todo in _ro.body %}
  {{ todo.title }} // 'run'
{% endfor %}{% endraw %}
```

## Hierarchical view structure

You can have a view on a resource class basis. It represents the structure well. Also, the cache is also hierarchically done on a resource basis, so it is efficient.

example) `page://self/index` which embeds `app://self/todos`

### app://self/todos

```php
class Todos extends ResourceObject
{
    use AuraSqlInject;
    use QueryLocatorInject;

    public function onGet(): static
    {
        $this->body = $this->pdo->fetchAll($this->query['todos_list']);
        return $this;
    }
}
```

```twig
{% raw %}{% for todo in _ro.body %}
  {{ todo.title }}</td>
{% endfor %}{% endraw %}
```

### page://self/index

```php
class Index extends ResourceObject
{
    /**
     * @Embed(rel="todos", src="app://self/todos")
     */
    public function onGet(): static
    {
        return $this;
    }
}
```

```twig
{% raw %}{% extends 'layout/base.html.twig' %}
{% block content %}
  {{ todos|raw }}
{% endblock %}{% endraw %}
```

## Extending Twig

When you extend Twig with the `addExtension()` method, prepare Twig's Provider class which performs extension and bind `Provider` to `Twig_Environment` class.

```php
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

class MyTwigProvider implements ProviderInterface
{
    private $twig;

    /**
     * @Named("original")
     */
    public function __construct(\Twig_Environment $twig)
    {
        // $twig is an original \Twig_Environment instance
        $this->twig = $twig;
    }

    public function get()
    {
        // Extending Twig
        $this->twig->addExtension(new MyTwigExtension());

        return $this->twig;
    }
}
```

```php
class HtmlModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TwigModule);
        $this->bind(\Twig_Environment::class)->toProvider(MyTwigProvider::class)->in(Scope::SINGLETON);
    }
}
```

## Template for mobile device

To use the template for mobile devices, install `MobileTwigModule`.

```php
class HtmlModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TwigModule);
        $this->install(new MobileTwigModule);
    }
}
```

If **there is** a mobile site template `Index.mobile.twig` that will replace `Index.html.twig`, it will be used in preference.

## Custom Settings

If you would like to change options depending on the context or add a template path, configuration values are bound to `@TwigPaths`and `@TwigOptions` annotations.

Note: Since caches are always created in the `var/tmp` folder, there is no particular need for special settings for production.

```php
namespace MyVendor\MyPackage\Module;

use Madapaja\TwigModule\Annotation\TwigDebug;
use Madapaja\TwigModule\Annotation\TwigOptions;
use Madapaja\TwigModule\Annotation\TwigPaths;
use Madapaja\TwigModule\TwigModule;
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new TwigModule);

        // You can add twig template paths by the following
        $appDir = $this->appMeta->appDir;
        $paths = [
            $appDir . '/src/Resource',
            $appDir . '/var/templates'
        ];
        $this->bind()->annotatedWith(TwigPaths::class)->toInstance($paths);

        // Also you can set environment options
        // @see http://twig.sensiolabs.org/doc/api.html#environment-options
        $options = [
            'debug' => false,
            'cache' => $appDir . '/tmp'
        ];
        $this->bind()->annotatedWith(TwigOptions::class)->toInstance($options);
        
        // Only for debug option
        $this->bind()->annotatedWith(TwigDebug::class)->toInstance(true);
    }
}
```



# Form

Each related function of Web Forms using [Aura.Input](https://github.com/auraphp/Aura.Input) and [Aura.Filter](https://github.com/auraphp/Aura.Filter) is aggregated to a single class so that it is easy to test and change.
We can use a corresponding class for the use of Web Forms and validation.

## Install

Install `ray/web-form-module` via composer to add form using Aura.Input

```bash
composer require ray/web-form-module
```

Install `AuraInputModule` in our application module `src/Module/AppModule.php`

```php?start_inline
use BEAR\Package\AbstractAppModule;
use Ray\WebFormModule\WebFormModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new AuraInputModule);
    }
}
```

##  Web Form

Create **a form class** that defines the registration and the rules of form elements, then bind it to a method using `@FormValidation` annotation.
The method runs only when the sent data is validated.

```php?start_inline
use Ray\WebFormModule\AbstractForm;
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractForm
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // set input fields
        $this->setField('name', 'text')
             ->setAttribs([
                 'id' => 'name'
             ]);
        // set rules and user defined error message
        $this->filter->validate('name')->is('alnum');
        $this->filter->useFieldMessage('name', 'Name must be alphabetic only.');
    }
}
```

We can register the input elements in the `init()` method of the form class and apply the rules of validation and sanitation.
Please refer to [Rules To Validate Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/validate.md) of Aura.Filter with respect to validation rules, and [Rules To Sanitize Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/sanitize.md) with respect to sanitize rules.

We validate an associative array of the argument of the method.
If we want to change the input, we can set the values by implementing `submit()` method of `SubmitInterface` interface.

## @FormValidation Annotation

Annotate the method that we want to validate with the `@FormValidation`, so that the validation is done in the form object specified by the `form` property before execution.
When validation fails, the method with the `ValidationFailed` suffix is called.

```php?start_inline
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\WebFormModule\Annotation\FormValidation;
use Ray\WebFormModule\FormInterface;

class MyController
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @Inject
     * @Named("contact_form")
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @FormValidation
     * // or
     * @FormValidation(form="form", onFailure="onPostValidationFailed")
     */
    public function onPost($name, $age)
    {
        // validation success
    }

    public function onPostValidationFailed($name, $age)
    {
        // validation failed
    }
}
```

We can explicitly specify the name and the method by changing the `form` property of `@FormValidation` annotation or the `onValidationFailed` property.

The submit parameters will be passed to the `onPostValidationFailed` method.

## View

Specify the element name to get the `input` elements and error messages

```php?start_inline
  $form->input('name'); // <input id="name" type="text" name="name" size="20" maxlength="20" />
  $form->error('name'); // "Please enter a double-byte characters or letters in the name." or blank
```

The same applies to Twig template

```php?start_inline
{% raw %}{{ form.input('name') }}
{{ form.error('name') }}{% endraw %}
```

## CSRF Protections

We can add a CSRF(Cross site request forgeries) object to the form to apply CSRF protections.

```php?start_inline
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractAuraForm
{
    use SetAntiCsrfTrait;
```

In order to increase the security level, add a custom CSRF class that contains the user authentication to the form class.
Please refer to the [Applying CSRF Protections](https://github.com/auraphp/Aura.Input#applying-csrf-protections) of Aura.Input for more information.

## @InputValidation annotation

If we annotate the method with `@InputValidation` instead of `@FormValidation`, the exception `Ray\WebFormModule\Exception\ValidationException` is thrown when validation fails.
For convenience, HTML representation is not used in this case.

When we `echo` the `error` property of the caught exception, we can see the representation of the media type [application/vnd.error+json](https://github.com/blongden/vnd.error).

```php?start_inline
http_response_code(400);
echo $e->error;

// {
//     "message": "Validation failed",
//     "path": "/path/to/error",
//     "validation_messages": {
//         "name": [
//             "Please enter a double-byte characters or letters in the name."
//         ]
//     }
// }
```

We can add the necessary information to `vnd.error+json` using `@VndError` annotation.

```php?start_inline
/**
 * @FormValidation(form="contactForm")
 * @VndError(
 *   message="foo validation failed",
 *   logref="a1000", path="/path/to/error",
 *   href={"_self"="/path/to/error", "help"="/path/to/help"}
 * )
 */
 public function onPost()
```

## FormVndErrorModule

If we install `Ray\WebFormModule\FormVndErrorModule`, the method annotated with `@FormValidation`
will throw an exception in the same way as the method annotated with `@InputValidation`.
We can use the page resources as API.

```php?start_inline
class FooModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new AuraInputModule);
        $this->override(new FormVndErrorModule);
    }
}
```

## Demo

Try the demo app [MyVendor.ContactForm](https://github.com/bearsunday/MyVendor.ContactForm) to get an idea on how forms such as
a confirmation form and multiple forms in a single page work.



# Content Negotiation

In HTTP, [Content Negotiation](https://en.wikipedia.org/wiki/Content_negotiation) is a mechanism used to provide various versions of resources for the same URL. BEAR.Sunday supports server-side content negotiation of media type 'Accept' and 'Accept-Language' of language. It can be specified on an application basis or resource basis.

## Install

Install [BEAR.Accept](https://github.com/bearsunday/BEAR.Accept) with composer

```bash
composer require bear/accept ^0.1
```

Next, save the context corresponding to the `Accept *` request header in `/var/locale/available.php`

```php?
<?php
return [
    'Accept' => [
        'text/hal+json' => 'hal-app',
        'application/json' => 'app',
        'cli' => 'cli-hal-app'
    ],
    'Accept-Language' => [ // lower case for key
        'ja-jp' => 'ja',
        'ja' => 'ja',
        'en-us' => 'en',
        'en' => 'en'
    ]
];
```

The `Accept` key array specifies an array whose context is a value with the media type as a key. `cli` is not used in web access in the context of console access.

The `Accept-Language` key array specifies an array with the context key as the key for the language.

## Enable by Application

Change `public/index.php` to enable content negotiation **throughout the application**.

```php
<?php
use BEAR\Accept\Accept;

require dirname(__DIR__) . '/vendor/autoload.php';

$accept = new Accept(require dirname(__DIR__) . '/var/locale/available.php');
list($context, $vary) = $accept($_SERVER);

require dirname(__DIR__) . '/bootstrap/bootstrap.php';
```

For example, in the above setting, the access context of the following `Accept*` header will be `prod-hal-ja-app`.

```
Accept: application/hal+json
Accept-Language: ja-JP
```

At this time `JaModule` requires binding for Japanese text. For details, refer to the demo application [MyVendor.Locale](https://github.com/koriym/MyVendor.Locale).

## Enable by Resource

To do content negotiation on a resource basis, install the `AcceptModule` module and use the `@Produces` annotation.

### Module Install

```php?start_inline
protected function configure()
{
    // ...
    $available = $appDir . '/var/locale/available.php';
    $this->install(new AcceptModule($available));
}
```

## @Produces annotation

```php?start_inline
use BEAR\Accept\Annotation\Produces;

/**
 * @Produces({"application/hal+json", "text/csv"})
 */
public function onGet()
```

Annotate available media type from left by priority. The representation (JSON or HTML) is changed by the contextual renderer. You do not need to add `Vary` header manually unlike application level content-negotiation.

## Access using curl

Specify the `Accept*` header with the `-H` option.

```
curl -H 'Accept-Language: en' http://127.0.0.1:8080/
```

```
curl -i -H 'Accept-Language: en' -H 'Accept: application/hal+json' http://127.0.0.1:8080/
```

```
HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Date: Fri, 11 Aug 2017 08:32:33 +0200
Connection: close
X-Powered-By: PHP/7.1.4
Vary: Accept, Accept-Language
content-type: application/hal+json

{
    "greeting": "Hello BEAR.Sunday",
    "_links": {
        "self": {
            "href": "/index"
        }
    }
}
```



# Hypermedia API

## HAL

BEAR.Sunday supports the [HAL](https://en.wikipedia.org/wiki/Hypertext_Application_Language) hypermedia (`application/hal+json`) API.


The HAL resource model consists of the following elements:

 * Link
 * Embedded resources
 * State

HAL is the JSON which represents only the state of the conventional resource plus the link `_links` plus `_embedded` to embed other resources. HAL makes API searchable and can find its API document from the API itself.


### Links


Resources should have a `self` URI

```
{
    "_links": {
        "self": { "href": "/user" }
    }
}
```

### Link Relations

Link rels are the main way of distinguishing between a resource's links.

There is a `rel` (relation) on the link, and it shows how the relationship is linked. It is similar to the `rel` used in the HTML `<link>` and `<a>` tag.

```
{
    "_links": {
        "next": { "href": "/page=2" }
    }
}
```

For more information about HAL please visit [http://stateless.co/hal_specification.html](http://stateless.co/hal_specification.html).

## Resource Class

You can annotate links and embed other resources.

### #[Link]

You can declaratively describe the `@Link` annotation, or dynamic ones are assigned to `body['_links']`.

```php?start_inline
#[Link(rel="user", href="/user")]
#[Link(rel="latest-post", href="/latest-post", title="latest post entrty")]
public function onGet()
```

or

```php?start_inline
public function onGet() {
    if ($hasCommentPrivilege) {
        $this->body += [
            '_links' => [
                'comment' => [
                    'href' => '/comments/{post-id}',
                    'templated' => true
                ]
            ]
        ];
    }
}

```

### #[Embed]

To embed other resources statically, use the `@Embed` annotation, and to embed it dynamically, assign the "request" to` body`.

```php?start_inline
#[Embed(rel="todos", src="/todos{?status}")]
#[Embed(rel="me", src="/me")]
public function onGet(string $status): static

```

or

```php?start_inline
$this->body['_embedded']['todos'] = $this->resource->uri('app://self/todos');
```

## API document service

The API server can also be an API document server. It solves problems such as the time required to create the API document, deviation from actual API, verification, maintenance.

In order for it to be on service, install `bear/api-doc` and install it by inheriting the `BEAR\ApiDoc\ApiDoc` page class

```
composer require bear/api-doc
```

```php
<?php
namespace MyVendor\MyPorject\Resource\Page\Rels;

use BEAR\ApiDoc\ApiDoc;

class Index extends ApiDoc
{
}
```

Publish the folder of JSON Schema to the web

```
ln -s var/json_schema public/schemas
```

API documents are automatically generated using Docblock comments and JSON Schema. The page class has its own renderer and is not affected by `$context`, it serves a document (`text/html`) for people. Since it is not affected by `$context`, you can install either` App` or `Page`.

If CURIEs is installed at the root, the API itself can be used even for raw JSON which is not hypermedia. Documents generated in real time always accurately reflect property information and validation constraints.

### Run demo

```
git clone https://github.com/koriym/Polidog.Todo.git
cd Polidog.Todo/
composer install
composer setup
composer doc
```

Open [docs/index.md](https://github.com/koriym/Polidog.Todo/blob/master/docs/index.md) to see API doc page.

## Browsable

The API set written in HAL functions as **headless REST application**.

You can access all the resources by following the link from the root like the website with the Web-based HAL Browser or the CURL command of the console.

 * [HAL Browser](https://github.com/mikekelly/hal-browser) - [example](http://haltalk.herokuapp.com/explorer/browser.html#/)
 * [hyperagent.js](https://weluse.github.io/hyperagent/)


## Siren

[Siren Module](https://github.com/kuma-guy/BEAR.SirenModule) is also available for [Siren](https://github.com/kevinswiber/siren) hypermedia (`application/vnd.siren+json`) type.



# PSR-7

You can get server side request information using [PSR7 HTTP message interface](https://www.php-fig.org/psr/psr-7/). Also, you can run BEAR.Sunday application as PSR 7 middleware.


## HTTP Request

PHP has `Superglobals` such as `$_SERVER` and `$_COOKIE`, but instead of using them it receives server side request information using the PSR-7 HTTP message interface.


### ServerRequest （general）

````php
class Index extends ResourceObject
{
    public function __construct(ServerRequestInterface $serverRequest)
    {
        // retrieve cookies
        $cookie = $serverRequest->getCookieParams(); // $_COOKIE
    }
}
````

### Upload Files

````php

use Psr\Http\Message\UploadedFileInterface;
use Ray\HttpMessage\Annotation\UploadFiles;

class Index extends ResourceObject
{
    /**
     * @UploadFiles
     */
    public function __construct(array $files)
    {
        // retrieve file name
        $file = $files['my-form']['details']['avatar'][0]
        /* @var UploadedFileInterface $file */
        $name = $file->getClientFilename(); // my-avatar3.png
    }
}
````

### URI

````php

use Psr\Http\Message\UriInterface;

class Index extends ResourceObject
{
    public function __construct(UriInterface $uri)
    {
        // retrieve host name
        $host = $uri->getHost();
    }
}
````

## PSR-7

An existing BEAR.Sunday application can work as
a [PSR-7](http://www.php-fig.org/psr/psr-7/) middleware with these easy steps:

1) Add `bear/middleware` package then replace [bootstrap.php](https://github.com/bearsunday/BEAR.Middleware/blob/1.x/bootstrap/bootstrap.php) script.

```bash
composer require bear/middleware
```
```bash
cp vendor/bear/middleware/bootstrap/bootstrap.php bootstrap/bootstrap.php
```

2) Replace `__PACKAGE__\__VENDOR__` in bootstrap.php to application namespace.

Stat the server.

```bash
php -S 127.0.0.1:8080 -t public
```

### Stream

BEAR.Sunday supports HTTP body of a message output in a [stream](http://php.net/manual/ja/intro.stream.php).

In `ResourceObject`, you can mix stream with a normal string. The output is converted to a single stream.
`StreamTransfer` is the default HTTP transfer. Seem more at [Stream Response](http://bearsunday.github.io/manuals/1.0/en/stream.html).

### New Project

You can also create a BEAR.Sunday PSR-7 project with `bear/project` from scratch.

```
composer create-project bear/project my-psr7-project
cd my-psr7-project/
php -S 127.0.0.1:8080 -t public
```

### PSR-7 middleware

 * [oscarotero/psr7-middlewares](https://github.com/oscarotero/psr7-middlewares)



# Javascript UI

Instead of rendering views with PHP template engines such Twig etc, we will be doing so using server-side JavaScript. On the PHP side we will be carrying out the authorisation, authentication, initialization and API delivery then we will do the rendering of the UI using JS.

Currently within our project architecture, we will only be making changes to annotated resources so should be simple.

## Prerequisites

 * PHP 7.1
 * [Node.js](https://nodejs.org/)
 * [yarn](https://yarnpkg.com/)
 * [V8Js](http://php.net/manual/en/book.v8js.php) (Development option)

Note: If you do not install V8Js then JS will be run using Node.js.

## Terminology

 * **CSR** Client Side Rendering (via Web Browser)
 * **SSR** Server Side Rendering (via V8 or Node.js)

## JavaScript

### Installation

Install `koriym/ssr-module` into the project.

```bash
// composer create-project bear/skeleton MyVendor.MyProject && cd MyVendor.MyProject;
composer require bear/ssr-module
```

Install the UI skeleton app `koriym/js-ui-skeleton`.

```bash
composer require koriym/js-ui-skeleton 1.x-dev
cp -r vendor/koriym/js-ui-skeleton/ui .
cp -r vendor/koriym/js-ui-skeleton/package.json .
yarn install
```

### Running the UI application

Lets start by running the demo application.
From the displayed web page lets select the rendering engine and run the JS application.

```
yarn run ui
```
This applications inputs can be set using the `ui/dev/config/` config files.

```php?
<?php
$app = 'index';                   // =index.bundle.js
$state = [                        // Application state
    'hello' =>['name' => 'World']
];
$metas = [                        // value used in SSR only
    'title' =>'page-title'
];

return [$app, $state, $metas];
```
Lets copy the configuration file and try changing the input values.

```
cp ui/dev/config/index.php ui/dev/config/myapp.php
```

Reload the browser and try out the new settings.

In this way without changing the JavaScript or core PHP application we can alter the UI data and check that it is working.

The PHP configuration files that have been edited in this section are only used when executing `yarn run ui`.
All the PHP side needs is the output bundled JS file.


### Creating the UI application.

Using the variables that have been passed in from PHP, create a **render** function that returns a rendered string.


```
const render = (state, metas) => (
  __AWESOME_UI__ // Using a SSR compatible library or JS template engine return an output string.
)
```

The `state` value is needed in the document root, `metas` contains other variables, such as those needed in <head>. The `render` function name cannot be changed.

Here we can grab the name and create a greeting string to be returned.

```
const render = state => (
  `Hello ${state.name}`
)
```

Save this as `ui/src/page/index/hello/server.js` and register this as a Webpack entry point in`ui/entry.js`.

```javascript?start_inline
module.exports = {
  hello: 'src/page/hello/server',
};
```

Having done this a `hello.bundle.js` bundled file is created for us.

Create a file at `ui/dev/config/myapp.php` to test run this application.

```php?
<?php
$app = 'hello';
$state = [
    ['name' => 'World']
];
$metas = [];

return [$app, $state, $metas];
```

Thats it! Reload the browser to try it out.

Inside the render function you can use any UI framework such as React or Vue.js to create a rich UI.
In a regular application in order to limit the number of dependencies in the `server.js` entry file import the render module as below.

```javascript
import render from './render';
global.render = render;
```

Thus far there has been nothing happening on the PHP side. Development on the SSR application and PHP development can done independently.

## PHP

### Module Installation

Install `SsrModule` in AppModule.

```php
<?php
use BEAR\SsrModule\SsrModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $build = dirname(__DIR__, 2) . '/var/www/build';
        $this->install(new SsrModule($build));
    }
}
```

The `$build` directory is where the JS files live.(The Webpack output location set in `ui/ui.config.js`)


### @Ssr Annotation

Annotate the resource function to be SSR'd with `@Ssr`. The JS application name is required in `app`.

```php?start_inline
<?php

namespace MyVendor\MyRedux\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\SsrModule\Annotation\Ssr;

class Index extends ResourceObject
{
    /**
     * @Ssr(app="index_ssr")
     */
    public function onGet($name = 'BEAR.Sunday')
    {
        $this->body = [
            'hello' => ['name' => $name]
        ];

        return $this;
    }
}
```

When you want to pass in distinct values for SSR and CSR set a key in `state` and `metas`.

```php?start_inline
/**
 * @Ssr(
 *   app="index_ssr",
 *   state={"name", "age"},
 *   metas={"title"}
 * )
 */
public function onGet()
{
    $this->body = [
        'name' => 'World',
        'age' => 4.6E8;
        'title' => 'Age of the World'
    ];

    return $this;
}
```

To see exactly how you pass in `state` and `metas` to achieve SSR see the sample application `ui/src/page/index/server`. The only influence is from the annotated method, the rest comes straight from the API or HTML rendering configuration.


### Runtime PHP Application Settings

Edit `ui/ui.config.js`, set the Webpack build location in `build` and web directory in `public`. The `build` directory is the same that you set in the SsrModule installation.

```javascript
const path = require('path');

module.exports = {
  public: path.join(__dirname, '../var/www'),
  build: path.join(__dirname, '../var/www/build')
};
```

### Running the PHP application

```
yarn run dev
```

Run using live updating.
When the PHP file is changed it will be automatically reloaded, if there is a change in a React component without hitting refresh the component will update. If you want to run the app without live updating you can by running `yarn run start`.

For other commands such `lint` or `test` etc. please see [commands](https://github.com/koriym/Koriym.JsUiSkeleton/blob/1.x/README.md#command).

## Performance

The ability to save the V8 Snapshot into APC means we can see dramatic performance benefits. In `ProdModule` install `ApcSsrModule`.
ReactJs or your application snapshot is saved in `APCu` and can be reused. V8 is required.

```php?start_inline
$this->install(new ApcSsrModule);
```
To use caches other than APC look at  the code in `ApcSsrModule` as a reference to make your own module. It is possible to use a cache compatible with PSR16.

In order to tune performance at compile time pulling in your JS code (and ReactJs etc) into the V8 snapshot can give you further performance improvements.
For more info please see the following.

 * [20x performance boost with V8Js snapshots](http://stesie.github.io/2016/02/snapshot-performance)
 * [v8js - Possibility to Improve Performance with Precompiled Templates/Classes ?](https://github.com/phpv8/v8js/issues/205)

## Debugging

 * Chrome Plugin [React developer tools](https://chrome.google.com/webstore/detail/react-developer-tools/fmkadmapgofadopljbjfkapdkoienihi) or [Redux devTools]( https://chrome.google.com/webstore/detail/redux-devtools/lmhkpmbekcpmknklioeibfkpmmfibljd) can be used.
 * When a 500 error is returned look at the response details by using `var/log` or `curl` etc.


## References

 * [ECMAScript 6](http://postd.cc/es6-cheatsheet/)
 * [Airbnb JavaScript Styleguide](https://github.com/airbnb/javascript)
 * [React](https://facebook.github.io/react/)
 * [Redux](http://redux.js.org/)
 * [Redux github](https://github.com/reactjs/redux)
 * [Redux devtools](https://github.com/gaearon/redux-devtools)
 * [Karma test runner](http://karma-runner.github.io/1.0/index.html)
 * [Mocha test framework](https://mochajs.org/)
 * [Chai assertion library](http://chaijs.com/)
 * [Yarn package manager](https://yarnpkg.com/)
 * [Webpack module bundler](https://webpack.js.org/)

## Other view libraries

  * [Vue.js](https://vuejs.org/)
  * [Handlesbar.js](http://handlebarsjs.com/)
  * [doT.js](http://olado.github.io/doT/index.html)
  * [pug](https://pugjs.org/api/getting-started.html)
  * [Hogan](http://twitter.github.io/hogan.js/) (Twitter)
  * [Nunjucks](https://mozilla.github.io/nunjucks/)(Mozilla)
  * [dust.js](http://www.dustjs.com/) (LinkedIn)
  * [marko](http://markojs.com/) (Ebay)



# Stream Response

Normally, resources are rendered by renderers into one string and finally `echo`ed out, but then you cannot output content whose size exceeds the memory limit of PHP. With `StreamRenderer` you can stream HTTP output and you can output large size content while keeping memory consumption low. Stream output can also be used in coexistence with existing renderers.

## Change Transferer and Renderer

Use the [StreamTransferInject](https://github.com/bearsunday/BEAR.Streamer/blob/1.x/src/StreamTransferInject.php) trait on the page to render and respond to the stream output. In the example of this download page, since `$body` is made to be a resource variable of the stream, the injected renderer is ignored and the resource is streamed.

```php?start_inline
use BEAR\Streamer\StreamTransferInject;

class Download extends ResourceObject
{
    use StreamTransferInject;

    public $headers = [
        'Content-Type' => 'image/jpeg',
        'Content-Disposition' => 'attachment; filename="image.jpg"'
    ];

    public function onGet(): static
    {
        $fp = fopen(__DIR__ . '/BEAR.jpg', 'r');
        $this->body = $fp;

        return $this;
    }
}
```

## With Renderers

Stream output can coexist with conventional renderers. Normally, Twig renderers and JSON renderers generate character strings, but when a stream is assigned to a part of it, the whole is output as a stream.

This is an example of assigning a `string` and a `resource` variable to the Twig template and generating a page of inline image.

Template

```twig
<!DOCTYPE html>
<html lang="en">
<body>
<p>Hello, {% raw  %}{{ name }}{% endraw %}</p>
<img src="data:image/jpg;base64,{% raw  %}{{ image }}{% endraw %}">
</body>
</html>
```

`name` assigns the string as usual, but assigns the resource variable of the image file's pointer resource to` image` with the `base64-encode` filter.

```php?start_inline
class Image extends ResourceObject
{
    use StreamTransferInject;

    public function onGet(string $name = 'inline image'): static
    {
        $fp = fopen(__DIR__ . '/image.jpg', 'r');
        stream_filter_append($fp, 'convert.base64-encode'); // image base64 format
        $this->body = [
            'name' => $name,
            'image' => $fp
        ];

        return $this;
    }
}
```

If you want to further control streaming such as streaming bandwidth and timing control, uploading to the cloud, etc use [StreamResponder](https://github.com/bearsunday/BEAR.Streamer/blob/1.x/src /StreamResponder.php ) which is build for it.

The demo is available at [MyVendor.Stream](https://github.com/bearsunday/MyVendor.Stream).


---
*[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/stream.md) needs to be proofread by native speaker.*



# Cache

> There are only two hard things in Computer Science: cache invalidation and naming things.
>
> -- Phil Karlton

## Overview

A good caching system improves the intrinsic quality of the user experience and reduces the cost of resource usage and environmental impact.
Sunday supports the following caching features in addition to the traditional simple TTL-based caching

* Event-driven cache invalidation
* Cache dependency resolution
* Donut cache and donut hole cache
* CDN control
* Conditional requests.


### Distributed Cache Framework


A distributed caching system that obeys REST constraints saves not only computational resources but also network resources.

PHP directly handles **server-side caches** such as Redis and APC, **shared caches** known as content delivery networks (CDNs), **client-side caches** cached by web browsers and API clients, BEAR.Sunday provides a caching framework that integrates these caches with modern CDNs.

<img src="https://user-images.githubusercontent.com/529021/137062427-c733c832-0631-4a43-a6ee-4204e6be007c.png" alt="distributed cache ">

## Tag-based cache invalidation

<img width="369" alt="dependency graph 2021-10-19 21 38 02" src="https://user-images.githubusercontent.com/529021/137910748-b6e95839-eeb7-4ade-a564-3cdcd5fdc09e.png">

There is a dependency problem in the content cache. If content A depends on content B, and B depends on C, then when C is updated, not only C's cache and ETag must be updated, but also B's cache and ETag which depend on C, and A's cache and ETag which depend on B.

Sunday solves this problem by having each resource hold the URI of the dependent resource as a tag. When a resource embedded with `#[Embed]` is modified, the cache and ETag of all the resources involved will be invalidated and the cache will be regenerated for the next request.

## Donut cache

<img width="200" alt="donut caching" src="https://user-images.githubusercontent.com/529021/137097856-f9428918-5b76-4c0e-8cea-2472c15d82e9.png">

Donut caching is one of the partial caching techniques for cache optimization. It composes the content into cacheable and non-cacheable parts.

For example, consider the content "`Welcome to $name`", which contains a non-cacheable resource. The do-not-cache part will be combined with the other cacheable parts and output.

<img width="557" alt="image" src="https://user-images.githubusercontent.com/529021/139617102-1f7f436c-a1f4-4c6c-b90b-de24491e4c8c.png ">



In this case, the entire content is dynamic, so the entire donut will not be cached. Therefore, no ETag will be output either.

## Donut hole cache

<img width="544" alt="image" src="https://user-images.githubusercontent.com/529021/139617571-31aea99a-533f-4b95-b3f3-6c613407d377.png ">

When the hole part of the doughnut is cacheable, it can be treated in the same way as the doughnut cache.

In the example above, the resource for the weather forecast, which changes once an hour, is cached and included in the news resource. In this case, since the content of the donut as a whole (news) is static, the whole thing is also cached and given an ETag.

This is where the cache dependency comes in. When the content of the hole part of the donut is updated, the entire cached donut needs to be regenerated.

The nice thing is that this dependency resolution is done automatically. The computation of the donut part is then reused to minimize the computational resources. When the hole part (weather resource) is updated, the cache and ETag of the entire content is also automatically updated.
Translated with www.DeepL.com/Translator (free version)

### recursive donut

<img width="191" alt="recursive donut 2021-10-19 21 27 06" src="https://user-images.githubusercontent.com/529021/137909083-2c5176f7-edb7-422b-bccc-1db90460fc15.png">

The donut structure will be recursively applied.
For example, if A contains B and B contains C and C is modified, A's cache and B's cache will be reused except for the modified C. A's and B's caches and ETags will be regenerated, but DB access to retrieve A's and B's content and rendering of views will not be done.
For example, if A contains B and B contains C, and C is changed, A's cache and B's cache will be reused except for the part of C that is changed. The new partially-composed A and B caches and ETags will be regenerated.

The optimized structure of the partial cache performs content regeneration with minimal cost. The client does not need to know about the content cache structure.

## Event-driven content

Traditionally, CDNs have believed that content that requires application logic is "dynamic" and therefore cannot be cached by a CDN. However, some CDNs, such as Fastly and Akamai, allow immediate or tag-based cache invalidation within seconds, [this idea is a thing of the past](https://www.fastly.com/blog/leveraging-your-cdn-cache- uncacheable-content).

Sunday dependency resolution is done not only on the server side, but also on the shared cache; when AOP detects a change and makes a PURGE request to the shared cache, the related cache on the shared cache will be invalidated, just like on the server side.

## Conditional request

<img width="468" alt="conditional request" src="https://user-images.githubusercontent.com/529021/137151061-8d7a5605-3aa3-494c-91c5-c1 deddd987dd.png">

Content changes are managed by AOP, and the entity tag (ETag) of the content is automatically updated. conditional requests for HTTP using ETag not only minimize the use of computational resources, but responses that only return `304 Not Modified` also minimize the use of network resources. Conditional HTTP requests using ETag not only minimize the use of computational resources, but also minimize the use of network resources by simply returning `304 Not Modified`.


# Usage

Give the class to be cached the attribute `#[DonutCache]` if it is a donut cache (embedded content is not cacheable) and `#[CacheableResponse]` otherwise.

```php
use BEAR\RepositoryModule\Annotation\CacheableResponse;

#[CacheableResponse]
class BlogPosting extends ResourceObject
{
    public $headers = [
        RequestHeader::CACHE_CONTROL => CacheControl::NO_CACHE
    ];

    #[Embed(rel: "comment", src: "page://self/html/comment")]
    public function onGet(int $id = 0): static
    {
        $this->body['article'] = 'hello world';

        return $this;
    }

    public function onDelete(int $id = 0): static
    {
        return $this;
    }
}
```

### recursive donut

<img width="191" alt="recursive donut 2021-10-19 21 27 06" src="https://user-images.githubusercontent.com/529021/137909083-2c5176f7-edb7- 422b-bccc-1db90460fc15.png">

The donut structure will be recursively applied.
For example, if A contains B and B contains C and C is modified, A's cache and B's cache will be reused except for the modified C. A's and B's caches and ETags will be regenerated, but DB access to retrieve A's and B's content and rendering of views will not be done.
For example, if A contains B and B contains C, and C is changed, A's cache and B's cache will be reused except for the part of C that is changed. The new partially-composed A and B caches and ETags will be regenerated.

The optimized structure of the partial cache performs content regeneration with minimal cost. The client does not need to know about the content cache structure.

## Event-driven content

Traditionally, CDNs have believed that content that requires application logic is "dynamic" and therefore cannot be cached by a CDN. However, some CDNs, such as Fastly and Akamai, allow immediate or tag-based cache invalidation within seconds, [this idea is a thing of the past](https://www.fastly.com/blog/leveraging-your-cdn-cache- uncacheable-content).

Sunday dependency resolution is done not only on the server side, but also on the shared cache; when AOP detects a change and makes a PURGE request to the shared cache, the related cache on the shared cache will be invalidated, just like on the server side.

## Conditional request

<img width="468" alt="conditional request" src="https://user-images.githubusercontent.com/529021/137151061-8d7a5605-3aa3-494c-91c5-c1 deddd987dd.png">

Content changes are managed by AOP, and the entity tag (ETag) of the content is automatically updated. conditional requests for HTTP using ETag not only minimize the use of computational resources, but responses that only return `304 Not Modified` also minimize the use of network resources. Conditional HTTP requests using ETag not only minimize the use of computational resources, but also minimize the use of network resources by simply returning `304 Not Modified`.


# Usage

Give the class to be cached the attribute `#[DonutCache]` if it is a donut cache (embedded content is not cacheable) and `#[CacheableResponse]` otherwise.


```php
class Todo extends ResourceObject
{
    #[CacheableResponse]
    public function onPut(int $id = 0, string $todo): static
    {
    }

    #[RefreshCache]
    public function onDelete(int $id = 0): static
    {
    }	
}
```

If you give attributes in either way, all the features introduced in the overview will apply.
Caching is not disabled by time (TTL) by default, assuming event-driven content

Note that with `#[DonutCache]` the whole content will not be cached, but with `#[CacheableResponse]` it will be.

## TTL

TTL is specified with `DonutRepositoryInterface::put()`.
`ttl` is the cache time for non-donut holes, `sMaxAge` is the cache time for CDNs.

```php
use BEAR\RepositoryModule\Annotation\CacheableResponse;

#[CacheableResponse]
class BlogPosting extends ResourceObject
{
    public function __construct(private DonutRepositoryInterface $repository)
    {}

    #[Embed(rel: "comment", src: "page://self/html/comment")]
    public function onGet(): static
    {
        // process ...
        $this->repository->put($this, ttl:10, sMaxAge:100);　

        return $this;
    }
}
```
### Default TTL value

For event-driven content, changes to the content must be reflected immediately in the cache, so the default TTL varies depending on the CDN module installed. Therefore, the default TTL will vary depending on the CDN module installed: indefinitely (1 year) if the CDN supports tag-based disabling of caching, or 10 seconds if it does not.

The expected cache reflection time is immediate for Fastly, a few seconds for Akamai, and 10 seconds for others.

To customize it, bind it by implementing `CdnCacheControlHeaderSetterInterface` with reference to `CdnCacheControlHeader`.

## Cache invalidation

Use the methods of `DonutRepositoryInterface` to manually invalidate the cache.
This will invalidate not only the specified cache, but also the cache of the ETag, any other resources it depends on, and the cache of the ETag on the server side and, if possible, on the CDN.

```php
interface DonutRepositoryInterface
{
    public function purge(AbstractUri $uri): void;
    public function invalidateTags(array $tags): void;
}
```

### Invalidate by URI

```php
// example
$this->repository->purge(new Uri('app://self/blog/comment'));
```

### Disable by tag

```php
$this->repository->invalidateTags(['template_a', 'campaign_b']);
```
### Tag Invalidation in CDN

In order to enable tag-based cache invalidation in CDN, you need to implement and bind `PurgerInterface`.

```php
use BEAR\QueryRepository\PurgerInterface;

interface PurgerInterface
{
    public function __invoke(string $tag): void;
}
```

### Specify dependent tags.

Use the `SURROGATE_KEY` header to specify the key for PURGE. Use a space as a separator for multiple strings.

```php
use BEAR\QueryRepository\Header;

class Foo
{
    public $headers = [
        Header::SURROGATE_KEY => 'template_a campaign_b'
    ];
```

If the cache is invalidated by `template_a` or `campaign_b` tags, Foo's cache and Foo's ETag will be invalidated both server-side and CDN.

### Resource Dependencies.

Use `UriTagInterface` to convert a URI into a dependency tag string.

```php
public function __construct(private UriTagInterface $uriTag)
{}
```
```php
$this->headers[Header::SURROGATE_KEY] = ($this->uriTag)(new Uri('app://self/foo'));
```

This cache will be invalidated both server-side and CDN when `app://self/foo` is modified.

### Make associative array a resource dependency.

```php
// bodyの内容
[
    ['id' => '1', 'name' => 'a'],
    ['id' => '2', 'name' => 'b'],
]
```
If you want to generate a list of dependent URI tags from a `body` associative array like the above, you can specify the URI template with the `fromAssoc()` method.

```php
$this->headers[Header::SURROGATE_KEY] = $this->uriTag->fromAssoc(
    uriTemplate: 'app://self/item{?id}',
    assoc: $this->body
);
```

In the above case, this cache will be invalidated for both server-side and CDN when `app://self/item?id=1` and `app://self/item?id=2` are changed.

## CDN

If you install a module that supports a specific CDN, vendor-specific headers will be output.

```php
$this->install(new FastlyModule())
$this->install(new AkamaiModule())
```

## Multi-CDN

You can also configure a multi-tier CDN and set the TTL according to the role. For example, in this diagram, a multi-functional CDN is placed upstream, and a conventional CDN is placed downstream. Content invalidation is done for the upstream CDN, and the downstream CDN uses it.

<img width="344" alt="multi cdn diagram" src="https://user-images.githubusercontent.com/529021/137098809-ec949a15-8efb-4d03-9808-3be15523ade7.png">


# Response headers

Sunday will automatically do the cache control for the CDN and output the header for the CDN. Client cache control is described in `$header` of ResourceObject depending on the content.

This section is important for security and maintenance purposes.
Make sure to specify the `Cache-Control` in all ResourceObjects.

### Cannot cache

Always specify content that cannot be cached.

```php
ResponseHeader::CACHE_CONTROL => CacheControl::NO_STORE
```

### Conditional requests

Check the server for content changes before using the cache. Server-side content changes will be detected and reflected.

```php
ResponseHeader::CACHE_CONTROL => CacheControl::NO_CACHE
```

### Specify client cache time.

The client is cached on the client. This is the most efficient cache, but server-side content changes will not be reflected at the specified time.

Also, this cache is not used when the browser reloads. The cache is used when a transition is made with the `<a>` tag or when a URL is entered.

```php
ResponseHeader::CACHE_CONTROL => 'max-age=60'
```

If response time is important to you, consider specifying SWR.

```php
ResponseHeader::CACHE_CONTROL => 'max-age=30 stale-while-revalidate=10'
```

In this case, when the max-age of 30 seconds is exceeded, the old cached (stale) response will be returned for up to 10 seconds, as specified in the SWR, until a fresh response is obtained from the origin server. This means that the cache will be updated sometime between 30 and 40 seconds after the last cache update, but every request will be a response from the cache and will be fast.

#### RFC7234 compliant clients

To use the client cache with APIs, use an RFC7234 compliant API client.

* iOS [NSURLCache](https://nshipster.com/nsurlcache/)
* Android [HttpResponseCache](https://developer.android.com/reference/android/net/http/HttpResponseCache)
* PHP [guzzle-cache-middleware](https://github.com/Kevinrob/guzzle-cache-middleware)
* JavaScript(Node) [cacheable-request](https://www.npmjs.com/package/cacheable-request)
* Go [lox/httpcache](https://github.com/lox/httpcache)
* Ruby [faraday-http-cache](https://github.com/plataformatec/faraday-http-cache)
* Python [requests-cache](https://pypi.org/project/requests-cache/)

### private

Specify `private` if you do not want to share the cache with other clients. The cache will be saved only on the client side. In this case, do not specify the cache on the server side.

````php
ResponseHeader::CACHE_CONTROL => 'private, max-age=30'
````

> Even if you use shared cache, you don't need to specify `public` in most cases.

## Cache design

APIs (or content) can be divided into two categories: **Information APIs** (Information APIs) and **Computation APIs** (Computation APIs). The **Computation API** is content that is difficult to reproduce and is truly dynamic, making it unsuitable for caching. The Information API, on the other hand, is an API for content that is essentially static, even if it is read from a DB and processed by PHP.

It analyzes the content in order to apply the appropriate cache.

* Information API or Computation API?
* Dependencies are
* Are the comprehension relationships
* Is the invalidation triggered by an event or TTL?
* Is the event detectable by the application or does it need to be monitored?
* Is the TTL predictable or unpredictable?

Consider making cache design a part of the application design process and make it a specification. It should also contribute to the safety of your project throughout its lifecycle.

### Adaptive TTL

Adaptive TTL is the ability to predict the lifetime of content and correctly tell the client or CDN when it will not be updated by an event during that time. For example, when dealing with a stock API, if it is Friday night, we know that the information will not be updated until the start of trading on Monday. We calculate the number of seconds until that time, specify it as the TTL, and then specify the appropriate TTL when it is time to trade.

The client does not need to request a resource that it knows will not be updated.

## #[Cacheable].

The traditional ##[Cacheable] TTL caching is also supported.

Example: 30 seconds cache on the server side, 30 seconds cache on the client.

The same number of seconds will be cached on the client side since it is specified on the server side.

The same number of seconds will be cached on the client side.
use BEAR\RepositoryModule\Annotation\Cacheable;

#[Cacheable(expirySecond: 30)]]
class CachedResource extends ResourceObject
{
````

Example: Cache the resource on the server and client until the specified expiration date (the date in `$body['expiry_at']`)

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

#[Cacheable(expiryAt: 'expiry_at')]]
class CachedResource extends ResourceObject
{
```.

See the [HTTP Cache](https://bearsunday.github.io/manuals/1.0/ja/http-cache.html) page for more information.

## Conclusion

Web content can be of the information (data) type or the computation (process) type. Although the former is essentially static, it is difficult to treat it as completely static content due to the problems of managing content changes and dependencies, so the cache was invalidated by TTL even though no content changes occurred. Sunday's caching framework treats information type content as static as possible, maximizing the power of the cache.


## Terminology

* [条件付きリクエスト](https://developer.mozilla.org/ja/docs/Web/HTTP/Conditional_requests)
* [ETag (バージョン識別子)](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/ETag)
* [イベントドリブン型コンテンツ](https://www.fastly.com/blog/rise-event-driven-content-or-how-cache-more-edge)
* [ドーナッツキャッシュ / 部分キャッシュ](https://www.infoq.com/jp/news/2011/12/MvcDonutCaching/)
* [サロゲートキー / タグベースの無効化](https://docs.fastly.com/ja/guides/getting-started-with-surrogate-keys)
* ヘッダー
  * [Cache-Control](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/Cache-Control)
  * [CDN-Cache-Control](https://blog.cloudflare.com/cdn-cache-control/)
  * [Vary](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/Vary)
  * [Stale-While-Revalidate (SWR)](https://www.infoq.com/jp/news/2020/12/ux-stale-while-revalidate/)



# Swoole

You can execute your BEAR.Sunday application using Swoole directly from the command line. It dramatically improves performance.

## Install

### Swoole Install

See [https://github.com/swoole/swoole-src#%EF%B8%8F-installation](https://github.com/swoole/swoole-src#%EF%B8%8F-installation)

### BEAR.Swoole Install

```bash
composer require bear/swoole ^0.4
```
Place the bootstrap script at `bin/swoole.php`

```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/vendor/bear/swoole/bootstrap.php')(
    'prod-hal-app',       // context
    'MyVendor\MyProject', // application name
    '127.0.0.1',          // IP
    8080                  // port
));
```

## Excute

```
php bin/swoole.php
```
```
Swoole http server is started at http://127.0.0.1:8088
```

## Benchmarking site

See [BEAR.HelloworldBenchmark](https://github.com/bearsunday/BEAR.HelloworldBenchmark)
You can expect x2 to x10 times bootstrap performance boost.

 * [The benchmarking result](https://github.com/bearsunday/BEAR.HelloworldBenchmark/wiki)

[<img src="https://github.com/swoole/swoole-src/raw/master/mascot.png">](https://github.com/swoole/swoole-src)



# Coding Guide

## Project

`Vendor` should be the company name, team name or the owner (`excite`, `koriym` etc.).
`Package` is the name of the application or service (`blog`, `news` etc.).
Projects must be created on a per application basis. Even when you create a Web API and an HTML from different hosts, they are considered one project.


## Style

BEAR.Sunday follows the PSR style.

  * [PSR1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
  * [PSR2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
  * [PSR4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)


Here is ResourceObject code example.

```php
<?php
namespace Koriym\Blog\Resource\App;

use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\AuraSqlModule\AuraSqlInject;

/**
 * @Cacheable
 */
class Entry extends ResourceObject
{
    use AuraSqlInject;
    use ResourceInject;

    /**
     * @Embed(rel="author", src="/author{?author_id}")
     */
    public function onGet(string $author_id, string $slug): static
    {
        // ...

        return $this;
    }

    /**
     * @Link(rel="next_act", href="/act1")
     * @Link(rel="next_act2", href="/act2")
     */
    public function onPost (
        string $tile,
        string $body,
        string $uid,
        string $slug
    ): static {
        // ...
        $this->code = Code::CREATED;

        return $this;
    }
}
```

A [DocBlock comment]([https://phpdoc.org/docs/latest/getting-started/your-first-set-of-documentation.html]) is optional. A DocBlock contains the method summary in one line.
Then followed by the description, which can be a multiple lines.
We should also put @params and @Link after description if possible.



```php?start_inline
/**
 * A summary informing the user what the associated element does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 *
 * @param string $arg1 *description*
 * @param string $arg2 *description*
 * @param string $arg3 *description*
 *
 * @Link(rel="next_act", href="/next_act_uri")
 * @Link(rel="next_act2", href="/next_act_uri2")
*/
```

## Resources

See [Resource Best Practices](resource_bp.html) for best practices for resources.

## Globals

We do not recommend referencing global values in resources or application classes. It is only used with Modules.

* Do not refer to the value of [Superglobal](http://php.net/manual/ja/language.variables.superglobals.php)
* Do not use [define](http://php.net/manual/en/function.define.php)
* Do not create `Config` class to hold set values.
* Do not use global object container (service locator) [[1]](http://koriym.github.io/adv10/), [[2]](http://blog.ploeh.dk/2010/02/03/ServiceLocatorisanAnti-Pattern/)
* Use [Date](http://php.net/manual/en/function.date.php) function and [DateTime](http://php.net/manual/en/class.datetime.php) class. It is not recommended to get the time directly. Inject the time from outside using [koriym/now](https://github.com/koriym/Koriym.Now).


Global method calls such as static methods are also not recommended.

The values required by the application code are all injected. The setting files are used for injecting. When you need an external value such as Web API, make a special gateway class for all requests. Then you can mock that special class with DI or AOP.

## Classes and object

* * [Traits](http://php.net/manual/en/language.oop5.traits.php) are not recommended. Traits for injection such as `ResourceInject` that reduce boilerplate code for injection were added in PHP8 [constructor property promotion (declaring properties in the constructor signature)](https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion). Use constructor injection.
* It is not recommended for the child classes to use the parent class methods. Common functions are not shared by inheritance and trait, they are dedicated classes and they are injected and used. [Composite from inheritance](https://en.wikipedia.org/wiki/Composition_over_inheritance).
* A class with only one method should reflect the function to the class name and should set the name of the method to `__invoke ()` so that function access can be made.

## Script command

* It is recommended to end the setup by using the `composer setup` command. This script includes the necessary database initialization and library checking. If manual operation such as `.env` setting is required, it is recommended that the procedure be displayed on the screen.
* It is recommended that all application caches and logs are cleared with `composer cleanup` command.
* It is recommended that all executable test (phpinit/phpcs/phpmd ..) are invoked with `composer test` command.
* It is recommended an application is deployed with `composer deploy` command.

## Code check

It is recommended to check the codes for each commit with the following commands. These commands can be installed with [bear/qatools](https://github.com/bearsunday/BEAR.QATools).

```
phpcs src tests
phpmd src text ./phpmd.xml
php-cs-fixer fix --config-file=./.php_cs
phpcbf src
```

## Resources

Please also refer to [Resouce best practice](/manuals/1.0/en/resource.html#best-practice).

### Code

Returns the appropriate status code. Testing is easier, and the correct information can be conveyed to bots and crawlers.

* `100` Continue Continuation of multiple requests
* `200` OK
* `201` Created Resource Creation
* `202` Accepted queue / batch acceptance
* `204` If there is no content body
* `304` Not Modified Not Updated
* `400` Bad request
* `401` Unauthorized Authentication required
* `403` Forbidden ban
* `404` Not Found
* `405` Method Not Allowed
* `503` Service Unavailable Temporary error on server side

In `OnPut` method, you deal with the resource state with idempotence. For example, resource creation with UUID or update resource state.

`OnPatch` is implemented when changing the state of a part of a resource.

### HTML Form Method

BEAR.Sunday can overwrite methods using the `X-HTTP-Method-Override` header or` _method` query at the `POST` request in the HTML web form, but it is not necessarily recommended . For the Page resource, it is OK to implement policies other than `onGet` and` onPost`. [[1]](http://programmers.stacxchange.com/questions/114156/why-are-there-are-no-put-and-delete-methods-on-html-forms), [[2]](Http://roy.gbiv.com/untangled/2009/it-is-okay-to-use-post)

### Hyperlink

* It is recommended that resources with links be indicated by `#[Link]`.
* It is recommended that resources be embedded as a graph of semantic coherence with `#[Embed]`.

## DI

 * Do not inject the value itself of the execution context (prod, dev etc). Instead, we inject instances according to the context. The application does not know in which context it is running.
 * Setter injection is not recommended for library code.
 * It is recommended that you override the `toConstructor` bindings instead and avoid the `Provider` bindings as much as possible.
 * Avoid binding by `Module` according to conditions. [AvoidConditionalLogicInModules](https://github.com/google/guice/wiki/AvoidConditionalLogicInModules)
 * It is not recommended to reference environmental variables since there is no module. Pass it in the constructor.

## AOP

 * Do not make interceptor mandatory. We will make the program work even without an interceptor. (For example, if you remove `@Transactional` interceptor, the function of transaction will be lost, but "core concers" will work without issue.)
 * Prevent the interceptor from injecting dependencies in methods. Values that can only be determined at implementation time are injected into arguments via `@Assisted` injection.
 * If there are multiple interceptors, do not depend on the execution order.
 * If it is an interceptor unconditionally applied to all methods, consider the description in `bootstrap.php`.

## Environment

To make applications testable, it should also work on the console, and not only on the Web.

It is recommended not to include the `.env` file in the project repository.

## Testing

* Focus on resource testing using resource clients, adding resource representation testing (e.g. HTML) if needed.
* Hypermedia tests can leave use cases as tests.
* `prod` is the context for production. Use of the `prod` context in tests should be minimal, preferably none.

## HTML templates

* Avoid large loop statements. Consider replacing if statements in loops with [Generator](https://www.php.net/manual/en/language.generators.overview.php).

---


# Quick Start

Installation is done via [composer](http://getcomposer.org)

```bash
composer create-project -n bear/skeleton MyVendor.MyProject
cd MyVendor.MyProject
```

Next, let's create a new resource. A resource is a class which corresponds, for instance, to a JSON payload (if working with an API-first driven model) 
or a web page.
Create your own basic page resource in `src/Resource/Page/Hello.php`

```php
<?php
namespace MyVendor\MyProject\Resource\Page;

use BEAR\Resource\ResourceObject;

class Hello extends ResourceObject
{
    public function onGet(string $name = 'BEAR.Sunday'): static
    {
        $this->body = [
            'greeting' => 'Hello ' . $name
        ];

        return $this;
    }
}
```

In the above example, when the page is requested using a GET method, `Hello` and `$_GET['name']` strings are joined, and assigned to a variable `greeting`.
The BEAR.Sunday application that you have created will work on a web server, but also in the console.

```bash
php bin/page.php get /hello
php bin/page.php get '/hello?name=World'
```

```bash
200 OK
Content-Type: application/hal+json

{
    "greeting": "Hello World",
    "_links": {
        "self": {
            "href": "/hello?name=World"
        }
    }
}
```

Let us fire up the php server and access our page at [http://127.0.0.1:8080/hello](http://127.0.0.1:8080/hello).

```bash
php -S 127.0.0.1:8080 -t public
```

```bash
curl -i 127.0.0.1:8080/hello
```



# PHPDoc Types

PHP is a dynamically typed language, but by using static analysis tools like psalm or phpstan along with PHPDoc, we can express advanced type concepts and benefit from type checking during static analysis. This reference explains the types available in PHPDoc and other related concepts.

## Table of Contents

1. [Atomic Types](#atomic-types)
    - [Scalar Types](#scalar-types)
    - [Object Types](#object-types)
    - [Array Types](#array-types)
    - [Callable Types](#callable-types)
    - [Value Types](#value-types)
    - [Special Types](#special-types)
2. [Compound Types](#compound-types)
    - [Union Types](#union-types)
    - [Intersection Types](#intersection-types)
3. [Advanced Type System](#advanced-type-system)
    - [Generic Types](#generic-types)
    - [Template Types](#template-types)
    - [Conditional Types](#conditional-types)
    - [Type Aliases](#type-aliases)
    - [Type Constraints](#type-constraints)
    - [Covariance and Contravariance](#covariance-and-contravariance)
4. [Type Operators (Utility Types)](#type-operators)
    - [Key-of and Value-of Types](#key-of-and-value-of-types)
    - [Properties-of Type](#properties-of-type)
    - [Class Name Mapping Type](#class-name-mapping-type)
    - [Index Access Type](#index-access-type)
5. [Functional Programming Concepts](#functional-programming-concepts)
    - [Pure Functions](#pure-functions)
    - [Immutable Objects](#immutable-objects)
    - [Side Effect Annotations](#side-effect-annotations)
    - [Higher-Order Functions](#higher-order-functions)
6. [Assert Annotations](#assert-annotations)
7. [Security Annotations](#security-annotations)
8. [Example: Using Types in Design Patterns](#example-using-types-in-design-patterns)

---

## Atomic Types

These are the basic types that cannot be further divided.

### Scalar Types

```php
/** @param int $i */
/** @param float $f */
/** @param string $str */
/** @param lowercase-string $lowercaseStr */
/** @param non-empty-string $nonEmptyStr */
/** @param non-empty-lowercase-string $nonEmptyLowercaseStr */
/** @param class-string $class */
/** @param class-string<AbstractFoo> $fooClass */
/** @param callable-string $callable */
/** @param numeric-string $num */ 
/** @param bool $isSet */
/** @param array-key $key */
/** @param numeric $num */
/** @param scalar $a */
/** @param positive-int $positiveInt */
/** @param negative-int $negativeInt */
/** @param int-range<0, 100> $percentage */
/** @param int-mask<1, 2, 4> $flags */
/** @param int-mask-of<MyClass::CLASS_CONSTANT_*> $classFlags */
/** @param trait-string $trait */
/** @param enum-string $enum */
/** @param literal-string $literalStr */
/** @param literal-int $literalInt */
```

These types can be combined using [Compound Types](#compound-types) and [Advanced Type System](#advanced-type-system).

### Object Types

```php
/** @param object $obj */
/** @param stdClass $std */
/** @param Foo\Bar $fooBar */
/** @param object{foo: string, bar?: int} $objWithProperties */
/** @return ArrayObject<int, string> */
/** @param Collection<User> $users */
/** @return Generator<int, string, mixed, void> */
```

Object types can be combined with [Generic Types](#generic-types).

### Array Types

#### Generic Arrays

```php
/** @return array<TKey, TValue> */
/** @return array<int, Foo> */
/** @return array<string, int|string> */
/** @return non-empty-array<string, int> */
```

Generic arrays use the concept of [Generic Types](#generic-types).

#### Object-like Arrays

```php
/** @return array{0: string, 1: string, foo: stdClass, 28: false} */
/** @return array{foo: string, bar: int} */
/** @return array{optional?: string, bar: int} */
```

#### Lists

```php
/** @param list<string> $stringList */
/** @param non-empty-list<int> $nonEmptyIntList */
```

#### PHPDoc Arrays (Legacy Notation)

```php
/** @param string[] $strings */
/** @param int[][] $nestedInts */
```

### Callable Types

```php
/** @return callable(Type1, OptionalType2=, SpreadType3...): ReturnType */
/** @return Closure(bool):int */
/** @param callable(int): string $callback */
```

Callable types are especially important in [Higher-Order Functions](#higher-order-functions).

### Value Types

```php
/** @return null */
/** @return true */
/** @return false */
/** @return 42 */
/** @return 3.14 */
/** @return "specific string" */
/** @param Foo\Bar::MY_SCALAR_CONST $const */
/** @param A::class|B::class $classNames */
```

### Special Types

```php
/** @return void */
/** @return never */
/** @return empty */
/** @return mixed */
/** @return resource */
/** @return closed-resource */
/** @return iterable<TKey, TValue> */
```

## Compound Types

These are types created by combining multiple [Atomic Types](#atomic-types).

### Union Types

```php
/** @param int|string $id */
/** @return string|null */
/** @var array<string|int> $mixedArray */
/** @return 'success'|'error'|'pending' */
```

### Intersection Types

```php
/** @param Countable&Traversable $collection */
/** @param Renderable&Serializable $object */
```

Intersection types can be useful in implementing [Design Patterns](#example-using-types-in-design-patterns).

## Advanced Type System

These are advanced features that allow for more complex and flexible type expressions.

### Generic Types

```php
/**
 * @template T
 * @param array<T> $items
 * @param callable(T): bool $predicate
 * @return array<T>
 */
function filter(array $items, callable $predicate): array {
    return array_filter($items, $predicate);
}
```

Generic types are often used in combination with [Higher-Order Functions](#higher-order-functions).

### Template Types

```php
/**
 * @template T of object
 * @param class-string<T> $className
 * @return T
 */
function create(string $className)
{
    return new $className();
}
```

Template types can be used in combination with [Type Constraints](#type-constraints).

### Conditional Types

```php
/**
 * @template T
 * @param T $value
 * @return (T is string ? int : string)
 */
function processValue($value) {
    return is_string($value) ? strlen($value) : strval($value);
}
```

Conditional types may be used in combination with [Union Types](#union-types).

### Type Aliases

```php
/**
 * @psalm-type UserId = positive-int
 * @psalm-type UserData = array{id: UserId, name: string, email: string}
 */

/**
 * @param UserData $userData
 * @return UserId
 */
function createUser(array $userData): int {
    // User creation logic
    return $userData['id'];
}
```

Type aliases are helpful for simplifying complex type definitions.

### Type Constraints

Type constraints allow you to specify more concrete type requirements for type parameters.

```php
/**
 * @template T of \DateTimeInterface
 * @param T $date
 * @return T
 */
function cloneDate($date) {
    return clone $date;
}

// Usage example
$dateTime = new DateTime();
$clonedDateTime = cloneDate($dateTime);
```

In this example, `T` is constrained to classes that implement `\DateTimeInterface`.

### Covariance and Contravariance

When dealing with generic types, the concepts of [covariance and contravariance](https://www.php.net/manual/en/language.oop5.variance.php) become important.

```php
/**
 * @template-covariant T
 */
interface Producer {
    /** @return T */
    public function produce();
}

/**
 * @template-contravariant T
 */
interface Consumer {
    /** @param T $item */
    public function consume($item);
}

// Usage example
/** @var Producer<Dog> $dogProducer */
/** @var Consumer<Animal> $animalConsumer */
```

Covariance allows you to use a more specific type (subtype), while contravariance means you can use a more basic type (supertype).

## Type Operators

Type operators allow you to generate new types from existing ones. Psalm refers to these as utility types.

### Key-of and Value-of Types

- `key-of` retrieves the type of all keys in a specified array or object, while `value-of` retrieves the type of its values.

```php
/**
 * @param key-of<UserData> $key
 * @return value-of<UserData>
 */
function getUserData(string $key) {
    $userData = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];
    return $userData[$key] ?? null;
}

/**
 * @return ArrayIterator<key-of<UserData>, value-of<UserData>>
 */
function getUserDataIterator() {
    $userData = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];
    return new ArrayIterator($userData);
}
```

### Properties-of Type

`properties-of` represents the type of all properties of a class. This is useful when dealing with class properties dynamically.

```php
class User {
    public int $id;
    public string $name;
    public ?string $email;
}

/**
 * @param User $user
 * @param key-of<properties-of<User>> $property
 * @return value-of<properties-of<User>>
 */
function getUserProperty(User $user, string $property) {
    return $user->$property;
}

// Usage example
$user = new User();
$propertyValue = getUserProperty($user, 'name'); // $propertyValue is of type string
```

`properties-of` has the following variants:

- `public-properties-of<T>`: Targets only public properties.
- `protected-properties-of<T>`: Targets only protected properties.
- `private-properties-of<T>`: Targets only private properties.

Using these variants allows you to deal with properties of specific access modifiers.

### Class Name Mapping Type

`class-string-map` represents an array with class names as keys and their instances as values. This is useful for implementing dependency injection containers or factory patterns.

```php
/**
 * @template T of object
 * @param class-string-map<T, T> $map
 * @param class-string<T> $className
 * @return T
 */
function getInstance(array $map, string $className) {
    return $map[$className] ?? new $className();
}

// Usage example
$container = [
    UserRepository::class => new UserRepository(),
    ProductRepository::class => new ProductRepository(),
];

$userRepo = getInstance($container, UserRepository::class);
```

### Index Access Type

The index access type (`T[K]`) represents the element of type `T` at index `K`. This is useful for accurately representing types when accessing array or object properties.

```php
/**
 * @template T of array
 * @template K of key-of<T>
 * @param T $data
 * @param K $key
 * @return T[K]
 */
function getArrayValue(array $data, $key) {
    return $data[$key];
}

// Usage example
$config = ['debug' => true, 'version' => '1.0.0'];
$debugMode = getArrayValue($config, 'debug'); // $debugMode is of type bool
```

These utility types are specific to psalm. They can be considered part of the [Advanced Type System](#advanced-type-system).

## Functional Programming Concepts

PHPDoc supports important concepts influenced by functional programming. Using these concepts can improve the predictability and reliability of your code.

### Pure Functions

Pure functions are functions without side effects that always return the same output for the same input.

```php
/**
 * @pure
 */
function add(int $a, int $b): int 
{
    return $a + $b;
}
```

This annotation indicates that the function has no side effects and always produces the same output for the same input.

### Immutable Objects

Immutable objects are objects whose state cannot be altered once they are created.

```php
/**
 * @immutable
 *
 * - All properties are considered readonly.
 * - All methods are implicitly treated as `@psalm-mutation-free`.
 */
class Point {
    public function __construct(
        private float $x, 
        private float $y
    ) {}

    public function withX(float $x): static 
    {
        return new self($x, $this->y);
    }

    public function withY(float $y): static
    {
        return new self($this->x, $y);
    }
}
```

#### @psalm-mutation-free

This annotation indicates that a method does not change the internal state of the class or any external state. Methods of `@immutable` classes implicitly have this property, but it can also be used for specific methods of non-immutable classes.

```php
class Calculator {
    private float $lastResult = 0;

    /**
     * @psalm-mutation-free
     */
    public function add(float $a, float $b): float {
        return $a + $b;
    }

    public function addAndStore(float $a, float $b): float {
        $this->lastResult = $a + $b; // This is not allowed with @psalm-mutation-free
        return $this->lastResult;
    }
}
```

#### @psalm-external-mutation-free

This annotation indicates that a method does not change any external state. Changes to the internal state of the class are allowed.

```php
class Logger {
    private array $logs = [];

    /**
     * @psalm-external-mutation-free
     */
    public function log(string $message): void {
        $this->logs[] = $message; // Internal state change is allowed
    }

    public function writeToFile(string $filename): void {
        file_put_contents($filename, implode("\n", $this->logs)); // This changes external state, so it can't be @psalm-external-mutation-free
    }
}
```

#### Guidelines for Using Immutability Annotations

1. Use `@immutable` when the entire class is immutable.
2. Use `@psalm-mutation-free` for specific methods that don't change any state.
3. Use `@psalm-external-mutation-free` for methods that don't change external state but may change internal state.

Properly expressing immutability can lead to many benefits, including improved safety in concurrent processing, reduced side effects, and easier-to-understand code.

### Side Effect Annotations

When a function has side effects, it can be explicitly annotated to caution its usage.

```php
/**
 * @side-effect This function writes to the database
 */
function logMessage(string $message): void {
    // Logic to write message to database
}
```

### Higher-Order Functions

Higher-order functions are functions that take functions as arguments or return functions. PHPDoc can be used to accurately express the types of higher-order functions.

```php
/**
 * @param callable(int): bool $predicate
 * @param list<int>           $numbers
 * @return list<int>
 */
function filter(callable $predicate, array $numbers): array {
    return array_filter($numbers, $predicate);
}
```

Higher-order functions are closely related to [Callable Types](#callable-types).

## Assert Annotations

Assert annotations are used to inform static analysis tools that certain conditions are met.

```php
/**
 * @psalm-assert string $value
 * @psalm-assert-if-true string $value
 * @psalm-assert-if-false null $value
 */
function isString($value): bool {
    return is_string($value);
}

/**
 * @psalm-assert !null $value
 */
function assertNotNull($value): void {
    if ($value === null) {
        throw new \InvalidArgumentException('Value must not be null');
    }
}

/**
 * @psalm-assert-if-true positive-int $number
 */
function isPositiveInteger($number): bool {
    return is_int($number) && $number > 0;
}
```

These assert annotations are used as follows:

- `@psalm-assert`: Indicates that the assertion is true if the function terminates normally (without throwing an exception).
- `@psalm-assert-if-true`: Indicates that the assertion is true if the function returns `true`.
- `@psalm-assert-if-false`: Indicates that the assertion is true if the function returns `false`.

Assert annotations may be used in combination with [Type Constraints](#type-constraints).

## Security Annotations

Security annotations are used to highlight security-critical parts of the code and track potential vulnerabilities. There are mainly three annotations:

1. `@psalm-taint-source`: Indicates an untrusted input source.
2. `@psalm-taint-sink`: Indicates where security-critical operations are performed.
3. `@psalm-taint-escape`: Indicates where data has been safely escaped or sanitized.

Here's an example of using these annotations:

```php
/**
 * @psalm-taint-source input
 */
function getUserInput(): string {
    return $_GET['user_input'] ?? '';
}

/**
 * @psalm-taint-sink sql
 */
function executeQuery(string $query): void {
    // Execute SQL query
}

/**
 * @psalm-taint-escape sql
 */
function escapeForSql(string $input): string {
    return addslashes($input);
}

// Usage example
$userInput = getUserInput();
$safeSqlInput = escapeForSql($userInput);
executeQuery("SELECT * FROM users WHERE name = '$safeSqlInput'");
```

By using these annotations, static analysis tools can track the flow of untrusted input and detect potential security issues (such as SQL injection).

## Example: Using Types in Design Patterns

You can use the type system to implement common design patterns in a more type-safe manner.

#### Builder Pattern

```php
/**
 * @template T
 */
interface BuilderInterface {
    /**
     * @return T
     */
    public function build();
}

/**
 * @template T
 * @template-implements BuilderInterface<T>
 */
abstract class AbstractBuilder implements BuilderInterface {
    /** @var array<string, mixed> */
    protected $data = [];

    /** @param mixed $value */
    public function set(string $name, $value): static {
        $this->data[$name] = $value;
        return $this;
    }
}

/**
 * @extends AbstractBuilder<User>
 */
class UserBuilder extends AbstractBuilder {
    public function build(): User {
        return new User($this->data);
    }
}

// Usage example
$user = (new UserBuilder())
    ->set('name', 'John Doe')
    ->set('email', 'john@example.com')
    ->build();
```

#### Repository Pattern

```php
/**
 * @template T
 */
interface RepositoryInterface {
    /**
     * @param int $id
     * @return T|null
     */
    public function find(int $id);

    /**
     * @param T $entity
     */
    public function save($entity): void;
}

/**
 * @implements RepositoryInterface<User>
 */
class UserRepository implements RepositoryInterface {
    public function find(int $id): ?User {
        // Logic to retrieve user from database
    }

    public function save(User $user): void {
        // Logic to save user to database
    }
}
```

#### Type Collection Demo

A comprehensive demonstration showcasing PHP's rich type system capabilities through practical code examples.

```php
<?php

namespace App\Final\Types;

/**
 * @psalm-type SqlQuery = string
 * @psalm-type HtmlContent = string
 * @psalm-type RegexPattern = regex-string
 * @psalm-type ClassConstant = class-constant-string
 * @psalm-type InterfaceClass = interface-string
 */
class SecurityContext {
    /**
     * @param non-empty-string $userInput
     * @return html-escaped-string
     * 
     * @psalm-taint-source input $userInput
     * @psalm-taint-escape html
     */
    public function escapeHtml(string $userInput): string {
        return htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param array<string, mixed> $params
     * @return SqlQuery
     * 
     * @psalm-taint-sink sql $query
     * @psalm-taint-escape sql
     */
    public function prepareSqlQuery(array $params): string {
        $query = "SELECT * FROM users WHERE id = :id";
        // prepared statements here
        return $query;
    }

    /**
     * @param RegexPattern $pattern
     * @param non-empty-string $subject
     * @return array<array-key, string>
     *
     * @psalm-taint-specialize
     */
    public function secureMatch(string $pattern, string $subject): array {
        if (@preg_match($pattern, '') === false) {
            throw new \InvalidArgumentException('Invalid regex pattern');
        }
        preg_match_all($pattern, $subject, $matches);
        return $matches[0];
    }
}

/**
 * @template T
 * @psalm-require-extends \Exception
 */
class TypedException extends \Exception {
    /** @var T */
    private mixed $context;

    /**
     * @param T $context
     */
    public function __construct(mixed $context) {
        $this->context = $context;
        parent::__construct();
    }

    /**
     * @return T
     * @psalm-mutation-free
     */
    public function getContext(): mixed {
        return $this->context;
    }
}

/**
 * @template T
 * @psalm-require-implements \Stringable
 */
class StringWrapper {
    /**
     * @param T $value
     */
    public function __construct(
        private readonly mixed $value
    ) {}

    /**
     * @return non-empty-string
     * @psalm-trace
     */
    public function toString(): string {
        return (string) $this->value;
    }
}

/**
 * @psalm-type MemoryTrace = array{
 *   allocation: positive-int,
 *   deallocated: bool,
 *   stack_trace: list<non-empty-string>
 * }
 */
class MemoryManager {
    /** @var array<string, MemoryTrace> */
    private array $traces = [];

    /**
     * @param object $object
     * @return positive-int
     * @psalm-flows-into $this->traces
     */
    public function track(object $object): int {
        $id = spl_object_id($object);
        $this->traces[spl_object_hash($object)] = [
            'allocation' => memory_get_usage(true),
            'deallocated' => false,
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ];
        return $id;
    }
}

/**
 * @template T of object
 * @psalm-type Middleware = callable(T, callable(T): void): void
 */
class MiddlewareChain {
    /** @var list<Middleware<T>> */
    private array $middlewares = [];

    /**
     * @param Middleware<T> $middleware
     */
    public function append(callable $middleware): void {
        $this->middlewares[] = $middleware;
    }

    /**
     * @param T $context
     * @psalm-taint-specialize $context
     */
    public function execute(object $context): void {
        $next = function($ctx) use (&$next): void {};
        
        foreach (array_reverse($this->middlewares) as $middleware) {
            $next = function($ctx) use ($middleware, $next): void {
                $middleware($ctx, $next);
            };
        }

        $next($context);
    }
}

/**
 * @template T
 */
interface Cache {
    /**
     * @param non-empty-string $key
     * @param T $value
     * @param positive-int|null $ttl
     * @psalm-taint-sink system $key
     */
    public function set(string $key, mixed $value, ?int $ttl = null): void;

    /**
     * @param non-empty-string $key
     * @return T|null
     * @psalm-taint-sink system $key
     */
    public function get(string $key): mixed;
}

/**
 * @template T
 * @implements Cache<T>
 */
class FileCache implements Cache {
    /**
     * @param non-empty-string $directory
     * @throws \RuntimeException
     */
    public function __construct(
        private readonly string $directory
    ) {
        if (!is_dir($directory) && !mkdir($directory, 0777, true)) {
            throw new \RuntimeException("Cannot create directory: {$directory}");
        }
    }

    /**
     * @param non-empty-string $key
     * @param T $value
     * @param positive-int|null $ttl
     * @psalm-taint-sink system $key
     * @psalm-taint-sink file $value
     */
    public function set(string $key, mixed $value, ?int $ttl = null): void {
        $path = $this->getPath($key);
        file_put_contents($path, serialize([
            'value' => $value,
            'expires_at' => $ttl ? time() + $ttl : null
        ]));
    }

    /**
     * @param non-empty-string $key
     * @return T|null
     * @psalm-taint-sink system $key
     * @psalm-taint-source file
     */
    public function get(string $key): mixed {
        $path = $this->getPath($key);
        if (!file_exists($path)) {
            return null;
        }

        $data = unserialize(file_get_contents($path));
        if ($data['expires_at'] !== null && $data['expires_at'] < time()) {
            unlink($path);
            return null;
        }

        return $data['value'];
    }

    /**
     * @param non-empty-string $key
     * @return non-empty-string
     * @psalm-taint-escape file
     */
    private function getPath(string $key): string {
        return $this->directory . '/' . hash('sha256', $key);
    }
}
```

## Summary

By deeply understanding and appropriately using the PHPDoc type system, you can benefit from self-documenting code, early bug detection through static analysis, powerful code completion and assistance from IDEs, clarification of code intentions and structure, and mitigation of security risks. This allows you to write more robust and maintainable PHP code.

The following is an example that covers all available types.

```php
<?php

namespace App\Comprehensive\Types;

/**
 * Example class covering atomic, scalar, union, intersection, and generic types
 *
 * @psalm-type UserId = int
 * @psalm-type HtmlContent = string
 * @psalm-type PositiveFloat = float&positive
 * @psalm-type Numeric = int|float
 * @psalm-type QueryResult = array<string, mixed>
 */
class TypeExamples {
    /**
     * Retrieves user content based on ID
     *
     * @param UserId|non-empty-string $id
     * @return HtmlContent
     */
    public function getUserContent(int|string $id): string {
        return "<p>User ID: {$id}</p>";
    }

    /**
     * Processes a positive float amount
     *
     * @param PositiveFloat $amount
     * @return bool
     */
    public function processPositiveAmount(float $amount): bool {
        return $amount > 0;
    }
}

/**
 * Immutable class, functional programming, pure function example
 *
 * @immutable
 */
class ImmutableUser {
    /** @var non-empty-string */
    private string $name;

    /** @var positive-int */
    private int $age;

    /**
     * Constructor for an immutable user
     *
     * @param non-empty-string $name
     * @param positive-int $age
     */
    public function __construct(string $name, int $age) {
        $this->name = $name;
        $this->age = $age;
    }

    /**
     * Returns a new user with additional years added
     *
     * @psalm-pure
     * @return ImmutableUser
     */
    public function withAdditionalYears(int $additionalYears): self {
        return new self($this->name, $this->age + $additionalYears);
    }
}

/**
 * Template type, generic type, conditional type, covariance and contravariance example
 *
 * @template T
 * @template-covariant U
 */
class StorageContainer {
    /** @var array<T, U> */
    private array $items = [];

    /**
     * Adds a new item to the container
     *
     * @param T $key
     * @param U $value
     */
    public function add(mixed $key, mixed $value): void {
        $this->items[$key] = $value;
    }

    /**
     * Retrieves an item by its key
     *
     * @param T $key
     * @return U|null
     * @psalm-assert-if-true string $key
     */
    public function get(mixed $key): mixed {
        return $this->items[$key] ?? null;
    }

    /**
     * @template V
     * @param T $key
     * @return (T is string ? string : U|null)
     */
    public function conditinalGet(mixed $key): mixed {
        return is_string($key) ? "default_string_value" : ($this->items[$key] ?? null);
    }
}

/**
 * Example of type constraints, utility types, functional programming, and assertion annotations
 *
 * @template T of array-key
 */
class UtilityExamples {
    /**
     * Returns the keys of an associative array
     *
     * @template T of array-key
     * @psalm-param array<T, mixed> $array
     * @psalm-return list<T>
     * @psalm-assert array<string, mixed> $array
     */
    public function getKeys(array $array): array {
        return array_keys($array);
    }

    /**
     * Maps classes to their instances
     *
     * @template T of object
     * @psalm-param class-string-map<T, array-key> $classes
     * @psalm-return list<T>
     */
    public function mapClasses(array $classes): array {
        return array_map(fn(string $className): object => new $className(), array_keys($classes));
    }
}

/**
 * High-order function, type alias, index access type example
 *
 * @template T
 * @psalm-type Predicate = callable(T): bool
 */
class FunctionalExamples {
    /**
     * Filters items based on a predicate
     *
     * @param list<T> $items
     * @param Predicate<T> $predicate
     * @return list<T>
     */
    public function filter(array $items, callable $predicate): array {
        return array_filter($items, $predicate);
    }

    /**
     * Retrieves a value from a map by key
     *
     * @param array<string, T> $map
     * @param key-of $map $key
     * @return T|null
     */
    public function getValue(array $map, string $key): mixed {
        return $map[$key] ?? null;
    }
}

/**
 * Security annotation, type constraint, index access type, property access type, key and value access type example
 *
 * @template T
 */
class SecureAccess {
    /**
     * Retrieves a property from a user profile
     *
     * @psalm-type UserProfile = array{
     *   id: int,
     *   name: non-empty-string,
     *   email: non-empty-string,
     *   roles: list<non-empty-string>
     * }
     * @psalm-param UserProfile $profile
     * @psalm-param key-of<UserProfile> $property
     * @return value-of<UserProfile>
     * @psalm-taint-escape system
     */
    public function getUserProperty(array $profile, string $property): mixed {
        return $profile[$property];
    }
}

/**
 * Complex structure type, security annotations, pure function example
 *
 * @template T of object
 * @template-covariant U of array-key
 * @psalm-type ErrorResponse = array{error: non-empty-string, code: positive-int}
 */
class ComplexExample {
    /** @var array<U, T> */
    private array $registry = [];

    /**
     * Registers an object by key
     *
     * @param U $key
     * @param T $value
     */
    public function register(mixed $key, object $value): void {
        $this->registry[$key] = $value;
    }

    /**
     * Retrieves a registered object by key
     *
     * @param U $key
     * @return T|null
     * @psalm-pure
     * @psalm-assert-if-true ErrorResponse $this->registry[$key]
     */
    public function getRegistered(mixed $key): ?object {
        return $this->registry[$key] ?? null;
    }
}

```


## References

To make the most of PHPDoc types, static analysis tools like Psalm or PHPStan are necessary. For more details, refer to the following resources:

- [Psalm - Typing in Psalm](https://psalm.dev/docs/annotating_code/typing_in_psalm/)
   - [Atomic Types](https://psalm.dev/docs/annotating_code/type_syntax/atomic_types/)
   - [Templating](https://psalm.dev/docs/annotating_code/templated_annotations/)
   - [Assertions](https://psalm.dev/docs/annotating_code/adding_assertions/)
   - [Security Analysis](https://psalm.dev/docs/security_analysis/)
- [PHPStan - PHPDoc Types](https://phpstan.org/writing-php-code/phpdoc-types)



# Test

Proper testing makes software better with continuity. A clean application of BEAR.Sunday is test friendly, with all dependencies injected and crosscutting interests provided in the AOP.

## Run test

Run `vendor/bin/phpunit` or `composer test`.　Other commands are as follows.

```
composer test    // phpunit test
composer tests   // test + sa + cs
composer coverge // test coverage
composer pcov    // test coverage (pcov)
composer sa      // static analysis
composer cs      // coding standards check
composer cs-fix  // coding standards fix
```

## Resource test

**Everything is a resource** - BEAR.Sunday application can be tested with resoure access.

This is a test that tests that `201 (Created)` will be returned by POSTing `['title' => 'test']` to URI `page://self/todo` of `Myvendor\MyProject` application in `html-app` context.

```php
<?php

use BEAR\Resource\ResourceInterface;

class TodoTest extends TestCase
{
    private ResourceInterface $resource;
    
    protected function setUp(): void
    {
        $injector = Injector::getInstance('test-html-app');
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testOnPost(): void
    {
        $page = $this->resource->post('page://self/todo', ['title' => 'test']);
        $this->assertSame(StatusCode::CREATED, $page->code);
    }
}
```

## Test Double

A Test Double is a substitute that replaces a component on which the software test object depends. Test doubles can have the following patterns

* Stub (provides "indirect input" to the test target)
* Mock ( validate "indirect output" from the test target inside a test double)
* Spy (records "indirect output" from the target to be tested)
* Fake (simpler implementation that works closer to the actual object)
* _Dummy_ (necessary to generate the test target but no call is made)

### Test Double Binding

There are two ways to change the bundling for a test. One is to change the bundling across all tests in the context module, and the other is to temporarily change the bundling only for a specific purpose within one test only.

#### Context Module

Create a ``TestModule`` to make the `test` context available in bootstrap.

```php
class TestModule extends AbstractModule
{
    public function configure(): void
    {
        $this->bind(DateTimeInterface::class)->toInstance(new DateTimeImmutable('1970-01-01 00:00:00'));
        $this->bind(Auth::class)->to(FakeAuth::class);    
    }
}
```

Injector with test context.

```php
$injector = Injector::getInstance('test-hal-app', $module);
```

#### Temporary binding change

Temporary bundle changes for a single test specify the bundle to override with `Injector::getOverrideInstance`.

```php
public function testBindFake(): void
{
    $module = new class extends AbstractModule {
        protected function configure(): void
        {
            $this->bind(FooInterface::class)->to(FakeFoo::class);
        }
    }
    $injector = Injector::getOverrideInstance('hal-app', $module);
}
```

### Mock

```php
public function testBindMock(): void
{ 
    $mock = $this->createMock(FooInterface::class);
    // expect that update() will be called once and the parameter will be the string 'something'.
    mock->expects($this->once())
             ->method('update')
             ->with($this->equalTo('something'));
    $module = new class($mock) extends AbstractModule {
        public function __constcuct(
            private FooInterface $foo
        ){}
        protected function configure(): void
        {
            $this->bind(FooInterface::class)->toInstance($this->foo);
        }
    };
    $injector = Injector::getOverrideInstance('hal-app', $module);
}
```
### spy

Installs a `SpyModule` by specifying the interface or class name of the spy target. [^spy-module] After running the SUT containing the spy target, verify the number of calls and the value of the calls in the spy log.

[^spy-module]: [ray/test-double](https://github.com/ray-di/Ray.TestDouble) must be installed to use SpyModule.

```php
public function testBindSpy(): void
{
    $module = new class extends AbstractModule {
        protected function configure(): void
        {
            $this->install(new SpyModule([FooInterface::class]));
        }
    };
    $injector = Injector::getOverrideInstance('hal-app', $module);
    $resource = $injector->getInstance(ResourceInterface::class);
    // Spy logs of FooInterface objects are logged, whether directly or indirectly.
    $resource->get('/');
    // Spyログの取り出し
    $spyLog = $injector->getInstance(\Ray\TestDouble\LoggerInterface::class);
    // @var array<int, Log> $addLog
    $addLog = $spyLog->getLogs(FooInterface, 'add');   
    $this->assertSame(1, count($addLog), 'Should have received once');
    // Argument validation from SUT
    $this->assertSame([1, 2], $addLog[0]->arguments);
    $this->assertSame(1, $addLog[0]->namedArguments['a']);
}
```

### Dummy

Use [Null Binding](https://ray-di.github.io/manuals/1.0/ja/null_object_binding.html) to bind a null object to an interface.

## Hypermedia Test

Resource testing is an input/output test for each endpoint. Hypermedia tests, on the other hand, test the workflow behavior of how the endpoints are connected.

Workflow tests are inherited from HTTP tests and are tested at both the PHP and HTTP levels in a single code. HTTP testing is done with `curl` and the request/response is logged in a log file.

## Best Practice

 * Test the interface, not the implementation.
 * Create a actual fake class rather than using a mock library.
 * Testing is a specification. Ease of reading rather than ease of coding.

Reference

* [Stop mocking, start testing](https://nedbatchelder.com/blog/201206/tldw_stop_mocking_start_testing.html)
* [Mockists Are Dead](https://www.thoughtworks.com/insights/blog/mockists-are-dead-long-live-classicists)



# Examples

This example application is built on the principles described in the [Coding Guide](http://bearsunday.github.io/manuals/1.0/en/coding-guide.html).

## Polidog.Todo

[https://github.com/koriym/Polidog.Todo](https://github.com/koriym/Polidog.Todo)


`Todos` is a basic CRUD application. The DB is accessed using the static　SQL file in the `var/sql` directory. Includes REST API using hyperlinks and testing, as well as form validation tests.

  * [ray/aura-sql-module](https://github.com/ray-di/Ray.AuraSqlModule) - Extended PDO ([Aura.Sql](https://github.com/auraphp/Aura.Sql))
  * [ray/web-form-module](https://github.com/ray-di/Ray.WebFormModule) - Web form ([Aura.Input](https://github.com/auraphp/Aura.Input))
  * [madapaja/twig-module](https://github.com/madapaja/Madapaja.TwigModule) - Twig template engine
  * [koriym/now](https://github.com/koriym/Koriym.Now) - Current datetime
  * [koriym/query-locator](https://github.com/koriym/Koriym.QueryLocator) - SQL locator
  * [koriym/http-constants](https://github.com/koriym/Koriym.HttpConstants) - Contains the values HTTP

## MyVendor.ContactForm

[https://github.com/bearsunday/MyVendor.ContactForm](https://github.com/bearsunday/MyVendor.ContactForm)


It is a sample of various form pages.

  * Minimal form page
  * Multiple forms page
  * Looped input form page
  * Preview form page including checkbox and radio button

# Attributes

BEAR.Sunday supports PHP8's [attributes](https://www.php.net/manual/en/language.attributes.overview.php) in addition to the annotations.

**Annotation**
```php?start_inline
/**
 * @Inject
 * @Named('admin')
 */
public function setLogger(LoggerInterface $logger)
```
**Attribute**
```php?start_inline
#[Inject, Named('admin')]
public function setLogger(LoggerInterface $logger)
```

```php?start_inline
#[Embed(rel: 'weather', src: 'app://self/weather{?date}')]
#[Link(rel: 'event', href: 'app://self/event{?news_date}')]
public function onGet(string $date): self
```

## Apply to parameters

While some annotations can only be applied to methods and require the argument names to be specified by name, the
Attributes can be used to decorate arguments directly.

```php?start_inline
public __construct(#[Named('payment')] LoggerInterface $paymentLogger, #[Named('debug')] LoggerInterface $debugLogger)
```

```php?start_inline
public function onGet($id, #[Assisted] DbInterface $db = null)
```

```php?start_inline
public function onGet(#[CookieParam('id')]string $tokenId): void
```

```php?start_inline
public function onGet(#[ResourceParam(uri: 'app://self/login#nickname')] string $nickname = null): static
```
## Compatibility

Attributes and annotations can be mixed in a single project. [^1]
All annotations described in this manual will work when converted to attributes.

## Performance

Although the cost of loading annotations/attributes for production is minimal due to optimization, you can speed up development by declaring that you will only use attribute readers, as follows

```php?start_inline
// tests/bootstap.php 

use Ray\ServiceLocator\ServiceLocator;

ServiceLocator::setReader(new AttributeReader());
```

```php?start_inline
// DevModule
 
$this->install(new AttributeModule());
```

---

[^1]:Attributes take precedence when mixed in a single method.

# API Doc

ApiDoc generates API documentation from your application.

The auto-generated documentation from your code and JSON schema will reduce your effort and keep your API documentation accurate.

## Usage

Install BEAR.ApiDoc.

    composer require bear/api-doc --dev

Copy the configuration file.

    cp ./vendor/bear/api-doc/apidoc.xml.dist ./apidoc.xml

## Source

ApiDoc generates documentation by retrieving information from phpdoc, method signatures, and JSON schema.

#### PHPDOC

In phpdoc, the following parts are retrieved.
For information that applies across resources, such as authentication, prepare a separate documentation page and link it with `@link`.

```php
/**
 * {title}
 *
 * {description}
 *
 * {@link htttp;//example.com/docs/auth 認証}
 */
 class Foo extends ResourceObject
 {
 }
```

```php
/**
 * {title}
 *
 * {description}
 *
 * @param string $id ユーザーID
 */
 public function onGet(string $id ='kuma'): static
 {
 }
```

* If there is no `@param` description in the phpdoc of the method, get the information of the argument from the method signature.
* The order of priority for information acquisition is phpdoc, JSON schema, and profile.

## Configuration

The configuration is written in XML.
The minimum specification is as follows.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<apidoc
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://bearsunday.github.io/BEAR.ApiDoc/apidoc.xsd">
    <appName>MyVendor\MyProject</appName>
    <scheme>app</scheme>
    <docDir>docs</docDir>
    <format>html</format>
</apidoc>
```

### Required Attributes

#### appName

Application namespaces

#### scheme

The name of the schema to use for API documentation. `page` or `app`.

#### docDir

Output directory name.

#### format

The output format, HTML or MD (Mark down).

### Optional attributes

#### title

API title

```xml
<title>MyBlog API</title>
```

#### description

API description

```xml
<description>MyBlog API description</description
```

#### links

Links. The `href` is the URL of the link, and the `rel` is its content.

```xml
<links>
    <link href="https://www.example.com/issue" rel="issue" />
    <link href="https://www.example.com/help" rel="help" />
</links>
```

#### alps

Specifies an "ALPS profile" that defines the terms used by the API.

```xml
<alps>alps/profile.json</alps>.
```

## Profile

ApiDoc supports the [ALPS](http://alps.io/) format of the [RFC 6906 Profile](https://tools.ietf.org/html/rfc6906) which gives additional information to the application.

Words used in API request and response keys are called semantic descriptors, and if you create a dictionary of profiles, you don't need to describe the words for each request.
Centralized definitions of words and phrases prevent notational errors and aid in shared understanding.

The words used in API request and response keys are called semantic descriptors, and creating a dictionary of profiles eliminates the need to explain the words for each request.
Centralized definitions of words and phrases prevent shaky notation and aid in shared understanding.

The following is an example of defining descriptors `firstName` and `familyName` with `title` and `def` respectively.
While `title` describes a word and clarifies its meaning, `def` links standard words defined in vocabulary sites such as [Schema.org](https://schema.org/).

ALPS profiles can be written in XML or JSON.

profile.xml
```xml
<?xml version="1.0" encoding="UTF-8"?>
<alps
     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     xsi:noNamespaceSchemaLocation="https://alps-io.github.io/schemas/alps.xsd">
    <!-- Ontology -->
    <descriptor id="firstName" title="The person's first name."/>
    <descriptor id="familyName" def="https://schema.org/familyName"/>
</alps>
```

profile.json

```json
{
  "$schema": "https://alps-io.github.io/schemas/alps.json",
  "alps": {
    "descriptor": [
      {"id": "firstName", "title": "The person's first name."}
      {"id": "familyName", "def": "https://schema.org/familyName"},
    ]
  }
}
```

Descriptions of words appearing in ApiDoc take precedence over phpdoc > JsonSchema > ALPS in that order.

## Reference

* [Demo](https://bearsunday.github.io/BEAR.ApiDoc/)
* [ALPS](http://alps.io/)
* [ALPS-ASD](https://github.com/koriym/app-state-diagram)



# Reference

## Attributes

| Attribute | Description |
|  |
| `#[CacheableResponse]` | An attribute to indicate a cacheable response. |
| `#[Cacheable(int $expirySecond = 0)]` | An attribute to indicate the cacheability of a resource. `$expirySecond` is the cache expiration time in seconds. |
| `#[CookieParam(string $name)]` | An attribute to receive parameters from cookies. `$name` is the name of the cookie. |
| `#[DonutCache]` | An attribute to indicate Donut cache. |
| `#[Embed(src: string $src, rel: string $rel)]` | An attribute to indicate embedding other resources. `$src` is the URI of the embedded resource, `$rel` is the relation name. |
| `#[EnvParam(string $name)]` | An attribute to receive parameters from environment variables. `$name` is the name of the environment variable. |
| `#[FormParam(string $name)]` | An attribute to receive parameters from form data. `$name` is the name of the form field. |
| `#[Inject]` | An attribute to indicate setter injection. |
| `#[InputValidation]` | An attribute to indicate input validation. |
| `#[JsonSchema(key: string $key = null, schema: string $schema = null, params: string $params = null)]` | An attribute to specify the JSON schema for input/output of a resource. `$key` is the schema key, `$schema` is the schema file name, `$params` is the schema file name for parameters. |
| `#[Link(rel: string $rel, href: string $href, method: string $method = null)]` | An attribute to indicate links between resources. `$rel` is the relation name, `$href` is the URI of the linked resource, `$method` is the HTTP method. |
| `#[Named(string $name)]` | An attribute to indicate named binding. `$name` is the binding name. |
| `#[OnFailure(string $name = null)]` | An attribute to specify a method for validation failure. `$name` is the name of the validation. |
| `#[OnValidate(string $name = null)]` | An attribute to specify a validation method. `$name` is the name of the validation. |
| `#[Produces(array $mediaTypes)]` | An attribute to specify the output media types of a resource. `$mediaTypes` is an array of producible media types. |
| `#[QueryParam(string $name)]` | An attribute to receive query parameters. `$name` is the name of the query parameter. |
| `#[RefreshCache]` | An attribute to indicate cache refresh. |
| `#[ResourceParam(uri: string $uri, param: string $param)]` | An attribute to receive the result of another resource as a parameter. `$uri` is the URI of the resource, `$param` is the parameter name. |
| `#[ReturnCreatedResource]` | An attribute to indicate that the created resource will be returned. |
| `#[ServerParam(string $name)]` | An attribute to receive parameters from server variables. `$name` is the name of the server variable. |
| `#[Ssr(app: string $appName, state: array $state = [], metas: array $metas = [])]` | An attribute to indicate server-side rendering. `$appName` is the name of the JS application, `$state` is the state of the application, `$metas` is an array of meta information. |
| `#[Transactional(array $props = ['pdo'])]` | An attribute to indicate that a method will be executed within a transaction. `$props` is an array of properties to which the transaction will be applied. |
| `#[UploadFiles]` | An attribute to receive uploaded files. |
| `#[Valid(form: string $form = null, onFailure: string $onFailure = null)]` | An attribute to indicate request validation. `$form` is the form class name, `$onFailure` is the method name for validation failure. |

## Modules

| Module Name | Description |
|  |
| `ApcSsrModule` | A module for server-side rendering using APCu. |
| `ApiDoc` | A module for generating API documentation. |
| `AppModule` | The main module of the application. It installs and configures other modules. |
| `AuraSqlModule` | A module for database connection using Aura.Sql. |
| `AuraSqlQueryModule` | A module for query builder using Aura.SqlQuery. |
| `CacheVersionModule` | A module for cache version management. |
| `CliModule` | A module for command-line interface. |
| `DoctrineOrmModule` | A module for database connection using Doctrine ORM. |
| `FakeModule` | A fake module for testing purposes. |
| `HalModule` | A module for HAL (Hypertext Application Language). |
| `HtmlModule` | A module for HTML rendering. |
| `ImportAppModule` | A module for loading other applications. |
| `JsonSchemaModule` | A module for input/output validation of resources using JSON schema. |
| `JwtAuthModule` | A module for authentication using JSON Web Token (JWT). |
| `NamedPdoModule` | A module that provides named PDO instances. |
| `PackageModule` | A module that installs the basic modules provided by BEAR.Package together. |
| `ProdModule` | A module for production environment settings. |
| `QiqModule` | A module for the Qiq template engine. |
| `ResourceModule` | A module for settings related to resource classes. |
| `AuraRouterModule` | A module for routing using Aura.Router. |
| `SirenModule` | A module for Siren (Hypermedia Specification). |
| `SpyModule` | A module for recording method calls. |
| `SsrModule` | A module for server-side rendering. |
| `TwigModule` | A module for the Twig template engine. |
| `ValidationModule` | A module for validation. |


# Tutorial

In this tutorial, we introduce the basic features of BEAR.Sunday, including **DI** (Dependency Injection), **AOP** (Aspect-Oriented Programming), and **REST API**. Follow along with the commits from [tutorial1](https://github.com/bearsunday/tutorial1/commits/v3).

## Project Creation

Let's create a web service that returns the day of the week when a date (year, month, day) is entered. Start by creating a project.

```bash
composer create-project bear/skeleton MyVendor.Weekday
```

Enter `MyVendor` for the **vendor** name and `Weekday` for the **project** name. [^2]

## Resources

First, create an application resource file at `src/Resource/App/Weekday.php`.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use DateTimeImmutable;

class Weekday extends ResourceObject
{
    public function onGet(int $year, int $month, int $day): static
    {
        $dateTime = (new DateTimeImmutable)->createFromFormat('Y-m-d', "$year-$month-$day");
        $weekday = $dateTime->format('D');
        $this->body = ['weekday' => $weekday];

        return $this;
    }
}
```

This resource class `MyVendor\Weekday\Resource\App\Weekday` can be accessed via the path `/weekday`. The query parameters of the `GET` method are passed to the `onGet` method.

Try accessing it via the console. First, test with an error.

```bash
php bin/app.php get /weekday
```

```
400 Bad Request
content-type: application/vnd.error+json

{
    "message": "Bad Request",
    "logref": "e29567cd",
```

Errors are returned in the [application/vnd.error+json](https://github.com/blongden/vnd.error) media type. The 400 error code indicates a problem with the request. Each error is assigned a `logref` ID, and the details of the error can be found in `var/log/`.

Next, try a correct request with parameters.

```bash
php bin/app.php get '/weekday?year=2001&month=1&day=1'
```

```bash
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
```

The result is correctly returned in the [application/hal+json](https://tools.ietf.org/html/draft-kelly-json-hal-06) media type.

Let's turn this into a Web API service. Start the built-in server.

```bash
php -S 127.0.0.1:8080 bin/app.php
```

Test it with an HTTP `GET` request using `curl`.

Modify `public/index.php` as shown below:

```diff
<?php

declare(strict_types=1);

use MyVendor\Weekday\Bootstrap;

require dirname(__DIR__) . '/autoload.php';
- exit((new Bootstrap())(PHP_SAPI === 'cli-server' ? 'hal-app' : 'prod-hal-app', $GLOBALS, $_SERVER));
+ exit((new Bootstrap())(PHP_SAPI === 'cli-server' ? 'hal-api-app' : 'prod-hal-api-app', $GLOBALS, $_SERVER));
```

```
curl -i 'http://127.0.0.1:8080/weekday?year=2001&month=1&day=1'
```

```
HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Date: Tue, 04 May 2021 01:55:59 GMT
Connection: close
X-Powered-By: PHP/8.0.3
Content-Type: application/hal+json

{
    "weekday": "Mon",
    "_links": {
        "self": {
            "href": "/weekday/2001/1/1"
        }
    }
}
```

This resource class does not have methods other than GET, so trying other methods will return `405 Method Not Allowed`. Let's test this as well.

```
curl -i -X POST 'http://127.0.0.1:8080/weekday?year=2001&month=1&day=1'
```

```
HTTP/1.1 405 Method Not Allowed
...
```

The HTTP `OPTIONS` method request can be used to determine the available HTTP methods and required parameters ([RFC7231](https://tools.ietf.org/html/rfc7231#section-4.3.7)).

```
curl -i -X OPTIONS http://127.0.0.1:8080/weekday
```

```
HTTP/1.1 200 OK
...
Content-Type: application/json
Allow: GET

{
    "GET": {
        "parameters": {
            "year": {
                "type": "integer"
            },
            "month": {
                "type": "integer"
            },
            "day": {
                "type": "integer"
            }
        },
        "required": [
            "year",
            "month",
            "day"
        ]
    }
}
```

## Testing

Let's create a test for the resource using [PHPUnit](https://phpunit.readthedocs.io/ja/latest/).

`tests/Resource/App/WeekdayTest.php` with the following test code:

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceInterface;
use MyVendor\Weekday\Injector;
use PHPUnit\Framework\TestCase;

class WeekdayTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $injector = Injector::getInstance('app');
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testOnGet(): void
    {
        $ro = $this->resource->get('app://self/weekday', ['year' => '2001', 'month' => '1', 'day' => '1']);
        $this->assertSame(200, $ro->code);
        $this->assertSame('Mon', $ro->body['weekday']);
    }
}
```

The `setUp()` method specifies the context (app) and uses the application's injector `Injector` to obtain a resource client (`ResourceInterface`), and the `testOnGet` method requests the resource for testing.

Let's run it.

```
./vendor/bin/phpunit
```
```
PHPUnit 9.5.4 by Sebastian Bergmann and contributors.

....                                                                4 / 4 (100%)

Time: 00:00.281, Memory: 14.00 MB
```

The installed project also includes commands for running tests and code inspections. To obtain test coverage, run `composer coverage`.

```
composer coverage
```

[pcov](https://pecl.php.net/package/pcov) can measure coverage more quickly.

```
composer pcov
```

You can view the details of the coverage by opening `build/coverage/index.html` in a web browser.

To check if the coding standards are being followed, use the `composer cs` command.
Automatic corrections can be done with the `composer cs-fix` command.

```
composer cs
```
```
composer cs-fix
```

## Static Analysis

Static analysis of the code is performed using the `composer sa` command.

```
composer sa
```

When running the code up to this point, the following error was detected by phpstan.

```
   
  15     Cannot call method format() on DateTimeImmutable|false.  
  

[^1]:The source code for this project is committed to [bearsunday/Tutorial](https://github.com/bearsunday/tutorial1/commits/v3) section by section. Please refer to it as needed.
[^2]:Normally, the **vendor** name is the name of an individual or team (organization). A GitHub account name or team name would be suitable. Enter the application name for **project**.
```



# Tutorial 2


In this tutorial, you will learn how to develop high quality standards-based REST (Hypermedia) applications using the following tools.

* Define a JSON schema and use it for validation and documentation [Json Schema](https://json-schema.org/)
* Hypermedia types [HAL (Hypertext Application Language)](https://stateless.group/hal_specification.html)
* A DB migration tool developed by CakePHP [Phinx](https://book.cakephp.org/phinx/0/en/index.html)
* Binding PHP interfaces to SQL statement execution [Ray. MediaQuery](https://github.com/ray-di/Ray.MediaQuery)

Let's proceed with the commits found in [tutorial2](https://github.com/bearsunday/tutorial2/commits/v2-php8.2).

## Create the project

Create the project skeleton.

```
composer create-project bear/skeleton MyVendor.Ticket
```

Enter the **vendor** name as `MyVendor` and the **project** name as `Ticket`.

## Migration

Install Phinx.

```
composer require --dev robmorgan/phinx
```

Configure the DB connection information in the `.env.dist` file in the project root folder.

```
TKT_DB_HOST=127.0.0.1:3306
TKT_DB_NAME=ticket
TKT_DB_USER=root
TKT_DB_PASS=''
TKT_DB_SLAVE=''
TKT_DB_DSN=mysql:host=${TKT_DB_HOST}
```

The `.env.dist` file should look like this, and the actual connection information should be written in `.env`. ^1]

Next, create a folder to be used by Phinx.

```bash
mkdir -p var/phinx/migrations
mkdir var/phinx/seeds
```

Set up `var/phinx/phinx.php` to use the `.env` connection information we have set up earlier.

```php
<?php
use BEAR\Dotenv\Dotenv;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

(new Dotenv())->load(dirname(__DIR__, 2));

$development = new PDO(getenv('TKT_DB_DSN'), getenv('TKT_DB_USER'), getenv('TKT_DB_PASS'));
$test = new PDO(getenv('TKT_DB_DSN') . '_test', getenv('TKT_DB_USER'), getenv('TKT_DB_PASS'));
return [
    'paths' => [
        'migrations' => __DIR__ . '/migrations',
    ],
    'environments' => [
        'development' => [
            'name' => $development->query("SELECT DATABASE()")->fetchColumn(),
            'connection' => $development
        ],
        'test' => [
            'name' => $test->query("SELECT DATABASE()")->fetchColumn(),
            'connection' => $test
        ]
    ]
];
```

### setup script

Edit `bin/setup.php` for easy database creation and migration.

```php
<?php
use BEAR\Dotenv\Dotenv;

require_once dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv())->load(dirname(__DIR__));

chdir(dirname(__DIR__));
passthru('rm -rf var/tmp/*');
passthru('chmod 775 var/tmp');
passthru('chmod 775 var/log');
// db
$pdo = new \PDO('mysql:host=' . getenv('TKT_DB_HOST'), getenv('TKT_DB_USER'), getenv('TKT_DB_PASS'));
$pdo->exec('CREATE DATABASE IF NOT EXISTS ' . getenv('TKT_DB_NAME'));
$pdo->exec('DROP DATABASE IF EXISTS ' . getenv('TKT_DB_NAME') . '_test');
$pdo->exec('CREATE DATABASE ' . getenv('TKT_DB_NAME') . '_test');
passthru('./vendor/bin/phinx migrate -c var/phinx/phinx.php -e development');
passthru('./vendor/bin/phinx migrate -c var/phinx/phinx.php -e test');
```

Next, we will create a migration class to create the `ticket` table.

```
./vendor/bin/phinx create Ticket -c var/phinx/phinx.php
```
```
Phinx by CakePHP - https://phinx.org.

...
created var/phinx/migrations/20210520124501_ticket.php
```

Edit `var/phinx/migrations/{current_date}_ticket.php` to implement the `change()` method.

```php
<?php
use Phinx\Migration\AbstractMigration;

final class Ticket extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('ticket', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('title', 'string')
            ->addColumn('date_created', 'datetime')
            ->create();
    }
}
```

In addition, edit `.env.dist` like the following.

```diff
 TKT_DB_USER=root
 TKT_DB_PASS=
 TKT_DB_SLAVE=
-TKT_DB_DSN=mysql:host=${TKT_DB_HOST}
+TKT_DB_DSN=mysql:host=${TKT_DB_HOST};dbname=${TKT_DB_NAME}
```

Now that we are done with the setup, run the setup command to create the table.

```
composer setup
```
```
> php bin/setup.php
...
All Done. Took 0.0248s
```

The table has been created. The next time you want to set up a database environment for this project, just run `composer setup`.

For more information about writing migration classes, see [Phinx Manual: Writing Migrations](https://book.cakephp.org/3.0/ja/phinx/migrations.html).

## Module

Install the module as a composer.

```
composer require ray/identity-value-module ray/media-query -w
```

Install the package with AppModule.

`src/Module/AppModule.php`

```php
<?php
namespace MyVendor\Ticket\Module;

use BEAR\Dotenv\Dotenv;
use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;

use BEAR\Resource\Module\JsonSchemaModule;
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\IdentityValueModule\IdentityValueModule;
use Ray\MediaQuery\DbQueryConfig;
use Ray\MediaQuery\MediaQueryModule;
use Ray\MediaQuery\Queries;
use function dirname;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        (new Dotenv())->load(dirname(__DIR__, 2));
        $this->install(
            new AuraSqlModule(
                (string) getenv('TKT_DB_DSN'),
                (string) getenv('TKT_DB_USER'),
                (string) getenv('TKT_DB_PASS'),
                (string) getenv('TKT_DB_SLAVE')
            )
        );
        $this->install(
            new MediaQueryModule(
                Queries::fromDir($this->appMeta->appDir . '/src/Query'), [
                   new DbQueryConfig($this->appMeta->appDir . '/var/sql'),
                ]
            )
        );
        $this->install(new IdentityValueModule());
        $this->install(
            new JsonSchemaModule(
                $this->appMeta->appDir . '/var/schema/response',
                $this->appMeta->appDir . '/var/schema/request'
            )
        );
        $this->install(new PackageModule());
    }
}
```

## SQL

Save the three SQLs for the ticket in `var/sql`.[^13]

`var/sql/ticket_add.sql`

```sql
/* ticket add */
INSERT INTO ticket (id, title, date_created)
VALUES (:id, :title, :dateCreated);
```

`var/sql/ticket_list.sql`

```sql
/* ticket list */
SELECT id, title, date_created
  FROM ticket
 LIMIT 3;
```

`var/sql/ticket_item.sql`

```sql
/* ticket item */
SELECT id, title, date_created
  FROM ticket
 WHERE id = :id
```

Make sure that the SQL will work on its own when you create it.

> PHPStorm includes a database tool, [DataGrip](https://www.jetbrains.com/datagrip/), which has all the necessary features for SQL development such as code completion and SQL refactoring.
Once the DB connection and other setups are made, SQL files can be executed directly in the IDE. [^3][^4]

## JsonSchema.

Create new files that will represent the resource `Ticket` (ticket item) and `Tickets` (ticket item list) with [JsonSchema](http://json-schema.org/):

`var/schema/response/ticket.json`

```json
{
  "$id": "ticket.json",
  "$schema": "http://json-schema.org/draft-07/schema#",
  "title": "Ticket",
  "type": "object",
  "required": ["id", "title", "date_created"],
  "properties": {
    "id": {
      "description": "The unique identifier for a ticket.",
      "type": "string",
      "maxLength": 64
    },
    "title": {
      "description": "The unique identifier for a ticket.",
      "type": "string",
      "maxLength": 255
    },
    "date_created": {
      "description": "The date and time that the ticket was created",
      "type": "string",
      "format": "datetime"
    }
  }
}
```

`var/schema/response/tickets.json`

Tickets is a `Ticket` array.

```json
{
  "$id": "tickets.json",
  "$schema": "http://json-schema.org/draft-07/schema#",
  "title": "Tickets",
  "type": "object",
  "required": ["tickets"],
  "properties": {
    "tickets": {
      "type": "array",
      "items":{"$ref": "./ticket.json"}
    }
  }
}

```

* **$id** - specifies the file name, but if it is to be published, it should be a URL.
* **title** - This will be treated in the API documentation as an object name.
* **examples** - specify examples as appropriate. You can also specify the entire object.

In PHPStorm, you will see a green check in the upper right corner of the editor to indicate that everything is OK. You should also validate the schema itself when you create it.

## Query Interface

We will create a PHP interface that abstracts access to the infrastructure.

* Read Ticket resources **TicketQueryInterface**.
* Create a Ticket resource **TicketCommandInterface**.

`src/Query/TicketQueryInterface.php`

```php
<?php

namespace MyVendor\Ticket\Query;

use Ray\MediaQuery\Annotation\DbQuery;

interface TicketQueryInterface
{
    #[DbQuery('ticket_item')]
    public function item(string $id): Ticket|null;

    /** @return array<Ticket> */
    #[DbQuery('ticket_list')]
    public function list(): array;
}
```

`src/Query/TicketCommandInterface.php`

```php
<?php

namespace MyVendor\Ticket\Query;

use DateTimeInterface;
use Ray\MediaQuery\Annotation\DbQuery;

interface TicketCommandInterface
{
    #[DbQuery('ticket_add')]
    public function add(string $id, string $title, DateTimeInterface $dateCreated = null): void;
}
```

Specify an SQL statement with the `#[DbQuery]` attribute. You do not need to write any implementation for this interface. An object that performs the specified SQL query will be created automatically.

The interface is divided into two concerns: **command** which has side effects, and **query** which returns a value.
It can be one interface and one method as in [ADR pattern](https://github.com/pmjones/adr). The application designer decides the policy.

## Entity

If you specify `array` for the return value of a method, you will get the database result as it is, an associative array, but if you specify an entity type for the return value of the method, it will be hydrated to that type.

``php
#[DbQuery('ticket_item')
public function item(string $id): array // you get an array.
```

```php
#[DbQuery('ticket_item')].
public function item(string $id): ticket|null; // yields a Ticket entity.
```

For multiple rows (row_list), use `/** @return array<Ticket>*/` and phpdoc to specify that ``Ticket`` is returned as an array.

```
/** @return array<Ticket> */
#[DbQuery('ticket_list')].
public function list(): array; // yields an array of Ticket entities.
```
The value of each row is passed to the constructor by name argument. [^named]

[^named]: [PHP 8.0+ named arguments ¶](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments), column order for PHP 7.x.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Entity;

class Ticket
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $dateCreated
    ) {}
}
```

## Resources

The resource class depends on the query interface.

## Ticket resource

Create a `ticket` resource in `src/Resource/App/Ticket.php`.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;
use MyVendor\Ticket\Query\TicketQueryInterface;

class Ticket extends ResourceObject
{
    public function __construct(
        private TicketQueryInterface $query
    ){}
    
   #[JsonSchema("ticket.json")]
   public function onGet(string $id = ''): static
    {
        $this->body = (array) $this->query->item($id);

        return $this;
    }
}
```

The attribute `#[JsonSchema]` indicates that the value output by `onGet()` is defined in the `ticket.json` schema.
It is validated for each request by AOP.

Let's try to request a resource by entering a seed. [^8]

```bash 
% mysql -u root -e "INSERT INTO ticket (id, title, date_created) VALUES ('1', 'foo', '1970-01-01 00:00:00')" ticket
```

```bash
% php bin/app.php get '/ticket?id=1'
```
```bash
200 OK
Content-Type: application/hal+json

{
    "id": "1",
    "title": "foo",
    "date_created": "1970-01-01 00:00:01",
    "_links": {
        "self": {
            "href": "/ticket?id=1"
        }
    }
}
```

### MediaQuery

With Ray.MediaQuery, an auto-generated SQL execution object is injected from the interface without the need to code boilerplate implementation classes. [^5]

A SQL statement can contain multiple SQLs separated by `;`, and multiple SQLs are bound to the same parameter by name, and transactions are executed for queries other than SELECT.

If you want to generate SQL dynamically, you can use an SQL execution class that injects the query builder instead of Ray.
For more details, please see [Database](database.html) in the manual.

## Embedded links

Usually, a website page contains multiple resources. For example, a blog post page might contain recommendations, advertisements, category links, etc. in addition to the post.
Instead of the client getting them separately, they can be bundled into one resource with embedded links as independent resources.

Think of HTML and the `<img>` tag that is written in it. Both have independent URLs, but the image resource is embedded in the HTML resource, and when the HTML is retrieved, the image is displayed in the HTML.
These are called hypermedia types [Embedding links(LE)](http://amundsen.com/hypermedia/hfactor/#le), and the resource to be embedded is linked.

Let's embed the project resource into the ticket resource, and prepare the Project class.

`src/Resource/App/Project.php`

```php
<?php

namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\ResourceObject;

class Project extends ResourceObject
{
    public function onGet(): static
    {
        $this->body = ['title' => 'Project A'];

        return $this;
    }
}
```

Add the attribute `#[Embed]` to the Ticket resource.

```diff
+use BEAR\Resource\Annotation\Embed;
+use BEAR\Resource\Request;
+
+   #[Embed(src: '/project', rel: 'project')]
    #[JsonSchema("ticket.json")]
    public function onGet(string $id = ''): static
    {
+        assert($this->body['project'] instanceof Request);
-        $this->body = (array) $this->query->item($id);
+        $this->body += (array) $this->query->item($id);
```

The request for the resource specified by the `#[Embed]` attribute `src` will be injected into the `rel` key of the body property, and will be lazily evaluated into a string representation when rendered.

For the sake of simplicity, no parameters are passed in this example, but you can pass the values received by the method arguments using the URI template, or you can modify or add parameters to the injected request.
See [resource](resource.html) for details.

If you make the request again, you will see that the status of the project resource has been added to the property `_embedded`.

```
% php bin/app.php get '/ticket?id=1'
```
```diff

{
    "id": "1",
    "title": "2",
    "date_created": "1970-01-01 00:00:01",
+    "_embedded": {
+        "project": {
+            "title": "Project A",
+        }
    },
```

Embedded resources are an important feature of the REST API. It gives a tree structure to the content and reduces the HTTP request cost. Instead of letting the client fetching it as a separate resource each time, the relationship can be represented in server-side. [^6]

## tickets resource

Create a `tickets` resource in `src/resource/App/Tickets.php` that can be created with `POST` and retrieved with `GET` for a list of tickets.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;
use Koriym\HttpConstants\ResponseHeader;
use Koriym\HttpConstants\StatusCode;
use MyVendor\Ticket\Query\TicketCommandInterface;
use MyVendor\Ticket\Query\TicketQueryInterface;
use Ray\IdentityValueModule\UuidInterface;
use function uri_template;

class Tickets extends ResourceObject
{
    public function __construct(
        private TicketQueryInterface $query,
        private TicketCommandInterface $command,
        private UuidInterface $uuid
    ){}

    #[Link(rel: "doPost", href: '/tickets')]
    #[Link(rel: "goTicket", href: '/ticket{?id}')]
    #[JsonSchema("tickets.json")]
    public function onGet(): static
    {
        $this->body = [
            'tickets' => $this->query->list()
        ];
        
        return $this;
    }

    #[Link(rel: "goTickets", href: '/tickets')]
    public function onPost(string $title): static
    {
        $id = (string) $this->uuid;
        $this->command->add($id, $title);

        $this->code = StatusCode::CREATED;
        $this->headers[ResponseHeader::LOCATION] = uri_template('/ticket{?id}', ['id' => $id]);

        return $this;
    }
}
```

The injected `$uuid` can be cast to a string to get the UUID. Also, `#Link[]` represents a link to another resource (application state).

Notice that we don't pass the current time in the `add()` method.
If no value is passed, it will not be null, but the MySQL current time string will be bound to the SQL.
This is because the string representation of the current time DateTime object bound to the `DateTimeInterface` (current time string) is bound to SQL.

```php
public function add(string $id, string $title, DateTimeInterface $dateCreated = null): void;
```
It saves you the trouble of hard-coding NOW() inside SQL and passing the current time to the method every time.
You can pass a `DateTime object`, or in the context of a test, you can bind a fixed test time.

In this way, if you specify an interface as an argument to a query, you get that object using DI, and its string representation is bound to SQL.
For example, login user IDs can be bound and used across applications. [^7]

## Hypermedia API test

> The term REST (representational state transfer) was introduced and defined by Roy Fielding in his doctoral dissertation in 2000, and is intended to give an idea of "the behavior of a properly designed web application".
> It is a network of web resources (a virtual state machine) where the user selects a resource identifier (URL) and a resource operation (application state transition) such as GET or POST to proceed with the application, resulting in the next representation of the resource (the next application state) being forwarded to the end user. application state) is transferred to the end user for use.
>
> -- [Wikipedia (REST)](https://en.wikipedia.org/wiki/Representational_state_transfer)

In a REST application, the following actions are provided by the service as URLs, and the client selects them.

HTML web applications are completely RESTful. The only operations are "**Go to the provided URL** (with a tag, etc.)" or "**Fill the provided form and submit**".

The REST API tests are written in the same way.

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Hypermedia;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use Koriym\HttpConstants\ResponseHeader;
use MyVendor\Ticket\Injector;
use MyVendor\Ticket\Query\TicketQueryInterface;
use PHPUnit\Framework\TestCase;
use Ray\Di\InjectorInterface;
use function json_decode;

class WorkflowTest extends TestCase
{
    protected ResourceInterface $resource;
    protected InjectorInterface $injector;

    protected function setUp(): void
    {
        $this->injector = Injector::getInstance('hal-api-app');
        $this->resource = $this->injector->getInstance(ResourceInterface::class);
        $a = $this->injector->getInstance(TicketQueryInterface::class);
    }

    public function testIndex(): static
    {
        $index = $this->resource->get('/');
        $this->assertSame(200, $index->code);

        return $index;
    }

    /**
     * @depends testIndex
     */
    public function testGoTickets(ResourceObject $response): static
    {

        $json = (string) $response;
        $href = json_decode($json)->_links->{'goTickets'}->href;
        $ro = $this->resource->get($href);
        $this->assertSame(200, $ro->code);

        return $ro;
    }

    /**
     * @depends testGoTickets
     */
    public function testDoPost(ResourceObject $response): static
    {
        $json = (string) $response;
        $href = json_decode($json)->_links->{'doPost'}->href;
        $ro = $this->resource->post($href, ['title' => 'title1']);
        $this->assertSame(201, $ro->code);

        return $ro;
    }

    /**
     * @depends testDoPost
     */
    public function testGoTicket(ResourceObject $response): static
    {
        $href = $response->headers[ResponseHeader::LOCATION];
        $ro = $this->resource->get($href);
        $this->assertSame(200, $ro->code);

        return $ro;
    }
}
```

You will also need a route page as a starting point.

`src/Resource/App/Index.php`

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    #[Link(rel: 'goTickets', href: '/tickets')]
    public function onGet(): static
    {
        return $this;
    }
}
```

* `setUp` creates a resource client, and `testIndex()` accesses the root page.
* The `testGoTickets()` method, which receives the response, makes a JSON representation of the response object and gets the link `goTickets` to get the next list of tickets.
* There is no need to write a test for the resource body. * No need to write tests for the resource body, just check the status code, since it is guaranteed that the JsonSchema validation of the response has passed.
* Following the uniform interface of REST, the next request URL to be accessed is always included in the response. Inspect them one after another.

> **Uniform Interface**
>
> REST is defined by four interface constraints: identification of resources; manipulation of resources through representations; self-descriptive messages; and, hypermedia as the engine of application state.[^11]

Let's run it.

```bash
./vendor/bin/phpunit --testsuite hypermedia
```

Hypermedia API tests (REST application tests) are a good representation of the fact that REST applications are state machines, and workflows can be described as use cases.
Ideally, REST API tests should cover how the application will be used.

### HTTP Testing

To test the REST API over HTTP, inherit the whole test and set the client to the HTTP test client with `setUp`.

```php
class WorkflowTest extends Workflow
{
    protected function setUp(): void
    {
        $this->resource = new HttpResource('127.0.0.1:8080', __DIR__ . '/index.php', __DIR__ . '/log/workflow.log');
    }
}
```

This client has the same interface as the resource client, but the actual request is made as an HTTP request to the built-in server and receives the response from the server.
The first argument is the URL of the built-in server. When `new` is executed, the built-in server will be started with the bootstrap script specified in the second argument.

The bootstrap script for the test server will also be changed to the API context.

`tests/Http/index.php`

```diff
-exit((new Bootstrap())('hal-app', $GLOBALS, $_SERVER));
+exit((new Bootstrap())('hal-api-app', $GLOBALS, $_SERVER));
```

Let's run it.

```
./vendor/bin/phpunit --testsuite http
```

#### HTTP Access Log

The actual HTTP request/response log made by curl will be recorded in the resource log of the third argument.

```
curl -s -i 'http://127.0.0.1:8080/'

HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Date: Fri, 21 May 2021 22:41:02 GMT
Connection: close
X-Powered-By: PHP/8.0.6
Content-Type: application/hal+json

{
    "_links": {
        "self": {
            "href": "/index"
        },
        "goTickets": {
            "href": "/tickets"
        }
    }
}
```

```
curl -s -i -H 'Content-Type:application/json' -X POST -d '{"title":"title1"}' http://127.0.0.1:8080/tickets

HTTP/1.1 201 Created
Host: 127.0.0.1:8080
Date: Fri, 21 May 2021 22:41:02 GMT
Connection: close
X-Powered-By: PHP/8.0.6
Location: /ticket?id=421d997c-9a0e-4018-a6c2-9b8758cac6d6
```


The actual recorded JSON is useful for checking, especially if it has a complex structure, and is also good to check along with the API documentation.
The HTTP client can also be used for E2E testing.

## API documentation

In ResourceObjects, method signatures are the input parameters to the API, and responses are schema-defined.
Because of its self-descriptiveness, API documentation can be generated automatically.

Let's create it. The documentation will be output to the [docs](https://bearsunday.github.io/tutorial2/) folder.

```
composer doc
```

It reduces the effort of writing IDL (Interface Definition Language), but more valuable is that the documentation follows the latest PHP code and is always accurate.
It is a good idea to include it in your CI so that your code and API documentation are always in sync.

You can also link to related documentation. See [ApiDoc](apidoc.html) for more details on configuration.

## Code examples

The following code example is also available.

* TestModulethat adds a `Test` context and clears the DB for each test.  [4e9704d](https://github.com/bearsunday/tutorial2/commit/4e9704d3bc65b9c7e7a8c13164dfe7cc3d6929b2)
* `entity` option for `#[DbQuery]` that returns a hydrated entity class instead of an associative array in DB queries [29f0a1f](https://github.com/bearsunday/tutorial2/commit/29f0a1f4d4bf51e6c0a722fd6b2f44cb78de999e)
* Query builder synthesizing static and dynamic SQL [9d095ac](https://github.com/bearsunday/tutorial2/commit/9d095acfed6150fb99f36d502ae13f03bdf2916d)

## REST framework

There are three styles of Web APIs.

* Tunnels (SOAP, GraphQL)
* URI (Object, CRUD)
* Hypermedia (REST)

In contrast to the URI style, where resources are treated as just RPCs [^9], what we learned in this tutorial is REST, where resources are linked. [^10]
Resources are connected by LOs (outbound links) in `#Link` to represent workflows, and LEs (embedded links) in `#[Embed]` to represent tree structures.

BEAR.Sunday emphasizes clean, standards-based code.

JsonSchema over framework-specific validators, standard SQL over proprietary ORM, IANA registered standard[^12] media type JSON over proprietary structure JSON.

Application design is not about "free implementation", but about "free choice of constraints".
Applications should aim for evolvability without breaking development efficiency, performance, and backward compatibility based on the constraints.

(This manual has been prepared through deepL automated translation.)

----

[^1]:.env should not be git committed.
[^2]:For example, if it is an e-commerce site, the test will represent the transition of each application state, such as product list, add to cart, order, payment, etc.
[^3]:[PHPStorm Database Tools and SQL](https://pleiades.io/help/phpstorm/relational-databases.html)
[^4]:[Database Diagrams](https://pleiades.io/help/phpstorm/creating-diagrams.html), etc. to check the query plan and execution plan to improve the quality of the SQL you create.
[^5]: Ray.MediaQuery also supports HTTP API requests.
[^6]: MediaQuery also supports HTTP API requests. This hierarchical structure of content is called **Taxonomy** in IA (Information Architecture). See [Understanding Information Architecture](https://understandinggroup.com/ia-theory/understanding-information-architecture).
[^7]: Ray.MediaQuery [README](https://github.com/ray-di/Ray.MediaQuery/blob/1.x/README.ja.md#%E6%97%A5%E4%BB%98%E6%99%82%E5%88%BB)
[^8]: MediaQuery [README]() Here we run it directly from mysql as an example, but you should also learn how to enter seed in the migration tool and use the IDE's DB tools.
[^9]: The so-called "Restish API"; many APIs introduced as REST APIs have this URI/object style, and REST is misused.
[^10]: If you remove the links from the tutorial, you get the URI style.
[^11]: It is a widespread misconception that the Uniform Interface is not an HTTP method. See [Uniform Interface](https://www.ics.uci.edu/~fielding/pubs/dissertation/rest_arch_style.htm).
[^12]: [https://www.iana.org/assignments/media-types/media-types.xhtml](https://www.iana.org/assignments/media-types/media-types.xhtml)
[^13]: This SQL conforms to the [SQL Style Guide](https://www.sqlstyle.guide/). It can be configured from PhpStorm as [Joe Celko](https://twitter.com/koriym/status/1410996122412150786).
The comment is not only descriptive, but also makes it easier to identify the SQL in the slow query log, etc.



# Package

BEAR.Sunday application is a composer package taking BEAR.Sunday framework as dependency package.
You can also install another BEAR.Sunday application package as dependency.

## Application organization

The file layout of the BEAR.Sunday application conforms to [php-pds/skeleton](https://github.com/php-pds/skeleton) standard.

### Invoke sequence

 1. Console input(`bin/app.php`, `bin/page.php`) or web entry file (`public/index.php`) excute `bootstrap.php` function.
 3. `$app` application object is created by `$context` in `boostrap.php`.
 4. A router in `$app` convert external resource request to internal resource request.
 4. A resource request is invoked. The representation of the result transfered to a client.


### bootstrap/

You can access same resource through console input or web access with same boot file.

```bash
php bin/app.php options /todos // console API access　(app resource)
```

```bash
php bin/page.php get '/todos?id=1' // console Web access (page resource)
```

```bash
php -S 127.0.0.1bin/app.php // PHP server
```

You can create your own boot file for different context.

### bin/

Plavce command-line executable files.

### src/

Place application class file.

### publc/

Web public folder.

### var/

`log` and `tmp` folder need write permission.

## Framework Package

### ray/aop
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/)
[![codecov](https://codecov.io/gh/ray-di/Ray.Aop/branch/2.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/ray-di/Ray.Aop)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Aop/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Aop)
[![Continuous Integration](https://github.com/ray-di/Ray.Aop/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ray-di/Ray.Aop/actions/workflows/continuous-integration.yml)

An aspect oriented framework based on Java AOP Alliance API.

### ray/di
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/)
[![codecov](https://codecov.io/gh/ray-di/Ray.Di/branch/2.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/ray-di/Ray.Di)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Di/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Di)
[![Continuous Integration](https://github.com/ray-di/Ray.Di/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ray-di/Ray.Di/actions/workflows/continuous-integration.yml)

A Google Guice style DI framework. It contains `ray/aop`.

### bear/resource
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Resource/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/bearsunday/BEAR.Resource)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Resource/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Resource)
[![Continuous Integration](https://github.com/bearsunday/BEAR.Resource/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.Resource/actions/workflows/continuous-integration.yml)

A REST framework for PHP object as a service. It contains `ray/di`.

### bear/sunday
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Sunday/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/bearsunday/BEAR.Sunday)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Sunday/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Sunday)
[![Continuous Integration](https://github.com/bearsunday/BEAR.Sunday/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.Sunday/actions/workflows/continuous-integration.yml)

A web application interface package. It contains `bear/resource`.

### bear/package
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Package/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/bearsunday/BEAR.Pacakge)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Package/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Package)
[![Continuous Integration](https://github.com/bearsunday/BEAR.Package/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.Package/actions/workflows/continuous-integration.yml)

A web application implmentation package. It contains `bear/sunday`.

## Library Package

Optional library package can be installed with `composer require` command.

| **Category** | **Composer package** | **Library**
| Router |
| |[bear/aura-router-module](https://github.com/bearsunday/BEAR.AuraRouterModule) | [Aura.Router v2](https://github.com/auraphp/Aura.Router/tree/2.x) |
| Database |
|| [ray/media-query](https://github.com/ray-di/Ray.MediaQuery) |
|| [ray/aura-sql-module](https://github.com/ray-di/Ray.AuraSqlModule) | [Aura.Sql v2](https://github.com/auraphp/Aura.Sql/tree/2.x)
|| [ray/dbal-module](https://github.com/ray-di/Ray.DbalModule) | [Doctrine DBAL](https://github.com/doctrine/dbal)
|| [ray/cake-database-module](https://github.com/ray-di/Ray.CakeDbModule) | [CakePHP v3 database](https://github.com/cakephp/database)
|| [ray/doctrine-orm-module](https://github.com/kawanamiyuu/Ray.DoctrineOrmModule) | [Doctrine ORM](https://github.com/doctrine/doctrine2)
| Storage |
||[bear/query-repository](https://github.com/bearsunday/BEAR.QueryRepository) | CQRS inspired repository
||[bear/query-module](https://github.com/ray-di/Ray.QueryModule) | Separation of external access such as DB or Web API
| Web
| |[madapaja/twig-module](http://bearsunday.github.io/manuals/1.0/ja/html.html) | [Twig](http://twig.sensiolabs.org/)
| |[ray/web-form-module](http://bearsunday.github.io/manuals/1.0/ja/form.html) | Web form
| |[ray/aura-web-module](https://github.com/Ray-Di/Ray.AuraWebModule) | [Aura.Web](https://github.com/auraphp/Aura.Web)
| |[ray/aura-session-module](https://github.com/ray-di/Ray.AuraSessionModule) | [Aura.Session](https://github.com/auraphp/Aura.Session)
| |[ray/symfony-session-module](https://github.com/kawanamiyuu/Ray.SymfonySessionModule) | [Symfony Session](https://github.com/symfony/http-foundation/tree/master/Session)
| Validation |
| |[ray/validate-module](https://github.com/ray-di/Ray.ValidateModule) | [Aura.Filter](https://github.com/auraphp/Aura.Filter)
| |[satomif/extra-aura-filter-module](https://github.com/satomif/ExtraAuraFilterModule)| [Aura.Filter](https://github.com/auraphp/Aura.Filter)
| Authorization and Authentication
| |[ray/oauth-module](https://github.com/Ray-Di/Ray.OAuthModule) | OAuth
| |[kuma-guy/jwt-auth-module](https://github.com/kuma-guy/BEAR.JwtAuthModule) | JSON Web Token
| |[ray/role-module](https://github.com/ray-di/Ray.RoleModule) | Zend Acl
| |[bear/acl-resource](https://github.com/bearsunday/BEAR.AclResource) | ACL based embedded resource
| Hypermedia
| |[kuma-guy/siren-module](https://github.com/kuma-guy/BEAR.SirenModule) | Siren
|  Development
| |[ray/test-double](https://github.com/ray-di/Ray.TestDouble) | Test Double
|  Asynchronous high performance |
| |[MyVendor.Swoole](https://github.com/bearsunday/MyVendor.Swoole) | [Swoole](https://github.com/swoole/swoole-src)

## Vendor Package

You can reuse common packages and tool combinations as modules with only modules and share modules of similar projects.[^1]

## Semver

All packages adhere to [Semantic Versioning](http://semver.org/).

---

[^1]: See [Koriym.DbAppPackage](https://github.com/koriym/Koriym.DbAppPackage)



# <a name="app"></a>Application

## Sequence

A BEAR.Sunday app has a run order of `compile`, `request` and `response`.

### 0. Compile

An `$app` application object is created through `DI` and `AOP` configuration depending on a specified context.
An `$app` is made up of service objects as it's properties that are needed to run the application such as a `router` or `transfer` etc.
`$app` then connects these object together depending on whether it is owned by another or contains other objects.
This is called an [Object Graph](http://en.wikipedia.org/wiki/Object_graph).
`$app` is then serialized and reused in each request and response.

* router - Converting external input to resource requests
* resource - Resource client
* transfer - Output

### 1. Request

An application resource request and resource object is created based on the HTTP request.

A resource object which has methods that respond to `onGet`, `onPost` etc upon request sets the `code` or `body` property of it's own resource state.

The resource object can then `#[Embed]` or `#[Link]` other resource objects.

Methods on the resource object are only for changing the resources state and have no interest in the representation itself (HTML, JSON etc).

Before and after the method, application logic bound to the method, such as logging and authentication, is executed in AOP.

### 2. Response

A `Renderer` is injected into the resource object, then the state of resource is represented as HTML, JSON etc or however it has been configured, it is then transfered to the client.

 <img src="/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>


## Boot File

To run an application, we need just two lines of code.
An entry point for a web server or console application access is usually set to `public/index.php` or `bin/app.php`.
As you can see below, we need to pass an application context to `bootstrap.php` the application script.


```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')('prod-html-app'));
```

Depending on your context choose a boot file.

```bash
// fire php server
php -S 127.0.0.1:8080 public/index.php
```

```
// console access
php bin/app.php get /user/1
```

## Context

The composition of the application object `$app` changes in response to the defined context, so that application behavior changes.

Depending on the defined context the building of the application object `$app` changes, altering the overall behavior.


For example, `WebRouter` is bound to `RouterInterface` by default.
However, if `Cli` mode is set (instead of HTTP) the `CliRouter` is bound to the `RouterInterface` and it will then take console input.

There are built-in and custom contexts that can be used in an application.

### Built-in Contexts

 * `api`  API Application
 * `cli`  Console Application
 * `hal`  HAL Application
 * `prod` Production

For `app`, resources are rendered in JSON.
`api` changes the default resource schema from page to app; web root access (GET /) is from page://self/ to app://self/.
Set `cli` to be a console application.
prod` makes it a production application with cache settings, etc.

You can also use a combination of these built-in contexts and add your own custom contexts.
If you set the context to `prod-hal-api-app` your application will run as an API application in production mode using the [HAL](http://stateless.co/hal_specification.html) media type.

### Custom Context

Place it in `src/Module`/ of the application; if it has the same name as the builtin context, the custom context will take precedence. You can override some of the constraints by calling the built-in context from the custom context.

Each application context (cli, app etc) represents a module.
For example the `cli` context relates to a `CliModule`, then binds all of the DI and AOP bindings that is needed for a console application.

### Context Agnostic

The context value is used only to create the root object and then disappears. There is no global "mode" that can be referenced by the application, and the application can not know what context it is currently running in. The behavior should only change through **code that is dependent on an interface**[^dip] and changes of dependencies by context.

---

[^dip]: [Dependency inversion principle](https://en.wikipedia.org/wiki/Dependency_inversion_principle)



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


# DI

Dependency injection is basically providing the objects that an object needs (its dependencies) instead of having it construct them itself.

With dependency injection, objects accept dependencies in their constructors. To construct an object, you first build its dependencies. But to build each dependency, you need its dependencies, and so on. So when you build an object, you really need to build an object graph.

Building object graphs by hand is labour intensive, error prone, and makes testing difficult. Instead, **Dependency Injector** ([Ray.Di](https://github.com/ray-di/Ray.Di)) can build the object graph for you. 

| What is object graph ?
| Object-oriented applications contain complex webs of interrelated objects. Objects are linked to each other by one object either owning or containing another object or holding a reference to another object. This web of objects is called an object graph and it is the more abstract structure that can be used in discussing an application's state. - [Wikipedia](http://en.wikipedia.org/wiki/Object_graph)

Ray.Di is the core DI framework used in BEAR.Sunday, which is heavily inspired by Google [Guice](http://code.google.com/p/google-guice/wiki/Motivation?tm=6) DI framework.See more detail at [Ray.Di Manual](https://ray-di.github.io/manuals/1.0/en/index.html).
