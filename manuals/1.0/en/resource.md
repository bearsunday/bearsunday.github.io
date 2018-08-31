---
layout: docs-en
title: Resource
category: Manual
permalink: /manuals/1.0/en/resource.html
---

# Resource

A BEAR.Sunday application is [RESTful](http://en.wikipedia.org/wiki/Representational_state_transfer) and is made up of a collection of resources connected by links.

# Object as a service

An HTTP method is mapped to a PHP method in the `ResourceObject` class.
It transfers its resource state as a resource representation from stateless request.
([Representational State Transfer)](http://en.wikipedia.org/wiki/REST)

Here are some examples of a resource object:

```php?start_inline
class Index extends ResourceObject
{
    public $code = 200;
    public $headers = [];

    public function onGet(int $a, int $b) : ResourceObject
    {
        $this->body = [
            'result' => $a + $b // $_GET['a'] + $_GET['b']
        ] ;

        return $this;
    }
}
```

```php?start_inline
class Todo extends ResourceObject
{
    public function onPost(string $id, string $todo) : ResourceOjbect
    {
        $this->code = 201; // status code
        $this->headers = [ // header
            'Location' => '/todo/new_id';
        ];

        return $this;
    }
}
```

The PHP resource class has URIs such as `app://self/blog/posts/?id=3`, `page://self/index` similar to the URI of the web, and conforms to the HTTP method `onGet`,` onPost`, `onPut`,` onPatch`, `onDelete` interface.

$_GET for `onGet` and $_POST for `onPost` are passed to the arguments of the method depending on the variable name, and the methods of `onPut`,` onPatch`, `onDelete` are content The value that can be handled according to `content-type`(`x-www-form-urlencoded` or `application/json`) is an argument.

The resource state (`code`,`headers` or`body`) is handled by these method using the given parameters. Then the resource class returns itself(`$this`).

### Syntax sugar

Access to the body property has some syntactic sugar.

```php?start_inline
$this['price'] = 10;
// is same as
$this->body['price'] = 10;
```

## Scheme

The equivalent to a MVC model is an `app` resource. A resource functions as an internal API, but as it is designed using REST it also works as an external API transport.
The `page` resource carries out a similar role as a page controller which is also a resource. A page resource then can consume application resources and builds itself based on the called URI.

| URI | Class |
|-----+-------|
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts | Koriym\Todo\Resource\App\Blog\Posts |

## Method

Resources have 6 interfaces conforming to HTTP methods.

### GET
Reads resources. This method does not provide any changing of the resource state. A safe method with no possible side affects.

### PUT
Performs creation and updates of a resource. This method has the benefit that running it once or many more times will have no more effect. This is referred to as [Idempotence](http://en.wikipedia.org/wiki/Idempotence).

### PATCH

Performs resource updates, but unlike PUT, it applies a delta rather than replacing the entire resource.

### POST
Performs resource creation. If you run a request multiple times the resource will be created as many times. A method with no idempotence.

### DELETE
Resource deletion. Has idempotence just like PUT.

### OPTIONS
Get information on parameters and responses required for resource request. It is as secure as GET method.

# Rendering

The request method of the `ResourceObject` class (such as `onGet`) has no interest in expressions such as whether the resource is represented in HTML or JSON.
Depending on the context, the resource renderer injected into `ResourceObject` renders it to JSON or HTML and makes it a resource representation (view).

Rendering is done when a resource is string evaluated.

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

It is injected according to the context so you do not usually need to be aware.
When a resource specific expression is required, we inject our own renderer as follows.

```php?start_inline
class Index
{
    // ...
    /**
     * @Inject
     * @Named("my_renderer")
     */
    public function setRenderer(RenderInterface $renderer)
    {
        parent::setRenderer($renderer);
    }
}
```

or

```php?start_inline
class Index
{
    /**
     * @Inject
     */
    public function setRenderer(RenderInterface $renderer)
    {
        unset($renderer);
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

# Transfer

The transponder forwards the representation (view) to the client (console or web client).
Transfer is mostly done simply by simple `header()` function or `echo`, but it can be transferred with [stream output](stream.html).

Like a renderer, you do not have to be aware of it normally.

When doing a resource specific transfer, override the following method.

```php?start_inline
class Index
{
    // ...
    public function transfer(TransferInterface $responder, array $server)
    {
        $responder($this, $server);
    }
}
```

In this way, each class has a function to **change its own resource state** by request, **transfer** it by **rendering** it.

## Client

You need a **Resource Client** to request resource. In the following example a `ResourceInject` trait is used to inject a `Resource Client`.

```php?start_inline
use BEAR\Sunday\Inject\ResourceInject;

class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet() : ResourceOjbect
    {
        $this->body = [
            'posts' => $this->resource->get('app://self/blog/posts', ['id' => 1])
        ];
    }
}
```
This code invokes a `GET` request to `app://self/blog/posts` `app` resource with the query `?id=1` .

この他にも以下の表記があります。

```php?start_inline
// PHP 5.x >== (deprecated)
$posts = $this->resource->get->uri('app://self/posts')->withQuery(['id' => 1])->eager->request();
// PHP 7.x >==
$posts = $this->resource->get->uri('app://self/posts')(['id' => 1]);
// getは省略化
$posts = $this->resource->uri('app://self/posts')(['id' => 1]);
```

The above is an `eager` request to make a request immediately, but it gets the request itself rather than the request result,
Request invocation will then be made when values are lazily output in the representation.
You can assign this value to a template engine or embed it in another resource. It will then be lazily evaluated.


```php?start_inline
$request = $this->resource->get->uri('app://self/posts'); // callable
$posts = $request(['id' => 1]);
```

## Link request

Resources can be linked in various way.

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

Three type of links are provided.

 * `linkSelf($rel)` Replace with the linked resource.
 * `linkNew($rel)`  Add linked resources to the base resource.
 * `linkCrawl($rel)` Crawl the link to build a resource tree.

## Link Annotation

### @Link
```php?start_inline
/**
 * @Link(rel="profile", href="/profile{?id}")
 */
public function onGet($id) : ResourceOjbect
```

Set the link with `rel` key name and `href` resource URI.

 * NOTE: When using the `hal` context, `@Link` is used for a HAL link.

```php?start_inline
use BEAR\Resource\Annotation\Link;

/**
 * @Link(crawl="post-tree", rel="post", href="app://self/post?author_id={id}")
 */
public function onGet($id = null)
```

A `crawl` tagged link will then be [crawled](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.md#crawl) with `linkCrawl`.

Find out more about the `@Link` annotation at  BEAR.Resource [README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.md).

## Embed Resource

You can embed another resource by using `src`.

```php?start_inline
use BEAR\Resource\Annotation\Embed;

class News
{
    /**
     * @Embed(rel="sports", src="/news/sports")
     * @Embed(rel="weater", src="/news/weather")
     */
    public function onGet() : ResourceOjbect
```

The resource **request** is embeded. The request is invoked when rendering. You can add parameters or replace with `addQuery()` or `withQuery()`

```php?start_inline
use BEAR\Resource\Annotation\Embed;

class News
{
    /**
     * @Embed(rel="website", src="/website{?id}")
     */
    public function onGet(string $id) : ResourceOjbect
    {
        // ...
        $this['website']->addQuery(['title' => $title]); // add parameters
```

 * NOTE: In the HAL renderer, this is used as `__embed`.

## Bind parameters

You can bind method parameters to an "external value". The external value might be a web context or any other resource state.

### Web context parameter

For instance, Instead you "pull" `$_GET` or any global the web context values, You can bind PHP super global values to method parameters.

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;

class News
{
    /**
     * @QueryParam("id")
     */
    public function foo(strin $id) : ResoureObject
    {
      // $id = $_GET['id'];
```

The above example is a case where a key name and the parameter name are the same.
You can specify `key` and `param` values when they don't match.

```php?start_inline
use Ray\WebContextParam\Annotation\CookieParam;

class News
{
    /**
     * @CookieParam(key="id", param="tokenId")
     */
    public function foo(string $tokenId) : ResoureObject
    {
      // $tokenId = $_COOKIE['id'];
```

Full List

```php?start_inline

use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

class News
{
    /**
     * @QueryParam(key="id", param="userId")
     * @CookieParam(key="id", param="tokenId")
     * @EnvParam("app_mode")
     * @FormParam("token")
     * @ServerParam(key="SERVER_NAME", param="server")
     */
    public function foo(
        string $userId,           // $_GET['id'];
        string $tokenId = "0000", // $_COOKIE['id'] or "0000" when unset;
        string $app_mode,         // $_ENV['app_mode'];
        string $token,            // $_POST['token'];
        string $server            // $_SERVER['SERVER_NAME'];
    ) : ResourceOjbect {
```

This `bind parameter` is also very useful for testing.

### Resource Parameter

We can bind the status of another resource to a parameter with the `@ResourceParam` annotation.

```php?start_inline
use BEAR\Resource\Annotation\ResourceParam;

class News
{
    /**
     * @ResourceParam(param=“name”, uri="app://self//login#nickname")
     */
    public function onGet(string $name) : ResourceOjbect
    {
```

In this example, the `nickname` property of `app://self//login` is bound to `$name`.

## Resource cache

### @Cacheable

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

/**
 * @Cacheable
 */
class User extends ResourceObject
```

`@Cacheable` annotated resource objects are cached without a time limit.
The cache will be updated by any non-GET request on the same class  with no expiry time (unless you specify one). A parameter is inspected to determine identity of the resource.

`@Cacheable` annotated resource objects will have `Last-Modified` and `ETag` headers added automatically.

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

/**
 * @Cacheable
 */
class Todo
{
    public function onGet(string $id) : ResoureObject
    {
        // read
    }

    public function onPost(string $id, string $name) : ResoureObject
    {
        // update
    }
}
```

For example, when a request is made to `->post(10, 'shopping')`, the `?id=10` cache will be updated.

Set `update` to false if you don't want to have this auto update.

```php?start_inline
/**
 * @Cacheable(update=false)
 */
```

You can specify a cache life time as `short`, `medium` or `long` on the  `expiry` property.

```php?start_inline
/**
 * @Cacheable(expiry="short")
 */
```


### @Purge @Refresh

You can also update caches with the `@Purge` and `@Refresh` annotation.

```php?start_inline
use BEAR\RepositoryModule\Annotation\Purge;
use BEAR\RepositoryModule\Annotation\Refresh;

class News
{
  /**
   * @Purge(uri="app://self/user/friend?user_id={id}")
   * @Refresh(uri="app://self/user/profile?user_id={id}")
   */
   public function onPut(string $id, string $name, int $age) : ResoureObject
```

You can update the cache for another resource class or even multiple resources at once. `@Purge` deletes a cache where `@Refresh` will recreate cache data.

## Best Practice<a name="best-practice"></a>

In the real world of REST, resources are connected with other resources.
The use of the link makes the code simpler and makes it easier to read and test and change.

### @Embed

Embed resources with `@Embed` instead of `get` the state of other resources.

```php?start_inline
// OK but not the best
class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(string $status) : ResoureObject
    {
        $this->body = [
            'todos' => $this->resource->uri('app://self/todos')(['status' => $status]); // lazy request
        ];

        return $this;
    }
}

// Better
class Index extends ResourceObject
{
    /**
     * @Embed(rel="todos", src="app://self/todos{?status}")
     */
    public function onGet(string $status) : ResourceObject
    {
        return $this;
    }
}
```

### @Link

When changing the state of another resource we will follow the next action indicated with `@Link` using `href()` (href = hyper reference).

```php?start_inline
// OK but not the best
class Todo extends ResourceObject
{
    use ResourceInject;

    public function onPost(string $title) : ResourceObject
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
    use ResourceInject;

    /**
     * @Link(rel="create", href="app://self/todo", method="post")
     */
    public function onPost(string $title) : ResourceObject
    {
        $this->resource->href('create', ['title' => $title]);
        $this->code = 301;
        $this->headers[ResponseHeader::LOCATION] = '/';

        return $this;
    }
}
```

### ＠ResourceParam

If you need other resource results to request other resources, use `@ResourceParam`.

```php?start_inline
// OK but not the best
class User extends ResourceObject
{
    use ResourceInject;

    public function onGet(string $id) : ResoureObject
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
    use ResourceInject;

    /**
     * @ResourceParam(param=“name”, uri="app://self//login-user#nickname")
     */
    public function onGet(string $id, string $name) : ResoureObject
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
    /**
     * @ResourceParam(param=“name”, uri="app://self//login-user#nickname")
     * @Embed(rel="profile", src="app://self/profile")
     */
    public function onGet(string $id, string $name) : ResoureObject
    {
        $this->body['profile']->addQuery(['name'=>$name]);

        return $this;
    }
}
```
