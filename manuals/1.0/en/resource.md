---
layout: docs-en
title: Resource
category: Manual
permalink: /manuals/1.0/en/resource.html
---

# Object as a service

A BEAR.Sunday application is [RESTful](http://en.wikipedia.org/wiki/Representational_state_transfer) and is made up of a collection of resources.

An HTTP method is mapped to a PHP method in the `ResourceObject` class.

Here are some examples of a resource object:

{% highlight php %}
<?php
class Index extends ResourceObject
{
    public function onGet($a, $b)
    {
        $this->code = 200; // 省略可
        // $_GET['a'] + $_GET['b']
        $this['result'] = $a + $b;

        return $this;
    }
}
{% endhighlight %}

{% highlight php %}
<?php
class Todo extends ResourceObject
{
    public function onPost($id, $todo)
    {
        // status code
        $this->code = 201;
        // location header for created resource
        $this->headers['Location'] = '/todo/new_id'; 
        
        return $this;
    }
}
{% endhighlight %}

A resource has a URI just like a web URL. 
{% highlight bash %}
app://self/blog/posts/?id=3
{% endhighlight %}

{% highlight bash %}
page://self/index
{% endhighlight %}
  
It has methods that correspond to HTTP verbs `onGet`, `onPost`, `onPut`, `onPatch`, or `onDelete`.
`$_GET` parameters are passed to the parameters of `onGet` method, as are `$_POST` parameters sent to the `onPost` method. 

{% highlight php %}
<?php
    class User
    {
        public function onGet($id, $todo)
        {
            // $id   <= $_GET['id']
            // $todo <= $_GET['todo']
{% endhighlight %}

The format defined by `content-type` header will handle the passing of parameters to be sent to `onPut`,`onPatch` or `onDelete`.

{% highlight php %}
<?php
    class User
    {
        public function onPut($id, $todo)
        {
            // `x-www-form-urlencoded` or `application/json`
            $id
{% endhighlight %}

The resource state (`code`,`headers` or`body`) is handled by these method using the given parameters. Then the resource class returns itself(`$this`). 

### Syntax sugar

Access to the body property has some syntactic sugar. 
{% highlight php %}
<?php

$this['price'] = 10;
// is same as
$this->body['price'] = 10;
{% endhighlight %}

## Scheme

The equivalent to a MVC model is an `app` resource. A resource functions as an internal API, but as it is designed using REST it also works as an external API transport.
The `page` resource carries out a similar role as a page controller which is also a resource. A page resource then can consume application resources and builds itself based on the called URI.

| URI | Class |
|-----+-------|
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts | Koriym\Todo\Resource\App\Blog\Posts |

## Method

Resources have 6 interfaces conforming to HTTP methods.

| **method** | **description**|
|--------|------------|
| GET | Resource retrieval |
| PUT | Resource update and creation |
| PATCH | Resource update |
| POST | Resource creation |
| DELETE | Resource delete |
| OPTIONS | Resource access method query |

#### GET 
Reads resources. This method does not provide any changing of the resource state. A safe method with no possible side affects.

#### PUT 
Performs creation and updates of a resource. This method has the benefit that running it once or many more times will have no more effect. This is referred to as [Idempotence](http://en.wikipedia.org/wiki/Idempotence).

#### PATCH

Performs resource updates, but unlike PUT, it applies a delta rather than replacing the entire resource. 

#### POST 
Performs resource creation. If you run a request multiple times the resource will be created as many times. A method with no idempotence.

#### DELETE 
Resource deletion. Has idempotence just like PUT.

#### OPTIONS 
Inspects which methods and parameters can be used on the resource. Just like `GET` there is no effect on the resource.


## Client

You need a **Resource Client** to request resource. In the following example a `ResourceInject` trait is used to inject a `Resource Client`.

{% highlight php %}
<?php

use BEAR\Sunday\Inject\ResourceInject;

class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet($a, $b)
    {
        $this['post'] = $this
            ->resource
            ->get
            ->uri('app://self/blog/posts')
            ->withQuery(['id' => 1])
            ->eager
            ->request();
    }
}
{% endhighlight %}

This code invokes a `GET` request to `app://self/blog/posts` App resource with the query `?id=1` .
If you do not specify an `eager` option with the request, it is just hold the request. Request invocation will then be made when values are lazily output in the representation.

{% highlight php %}
<?php
$posts = $this->resource->get->uri('app://self/posts')->request(); //lazy
$posts = $this->resource->get->uri('app://self/posts')->eager->request(); // eager
{% endhighlight %}

A `request()` method without `eager` returns an invokable request object, which can be invoked by calling `$posts()`.
You can assign this value to a template engine or embed it in another resource. It will then be lazily evaluated.

## Link request

Resources can be linked in various way.

{% highlight php %}
<?php
$blog = $this
    ->resource
    ->get
    ->uri('app://self/User')
    ->withQuery(['id' => 1])
    ->linkSelf("blog")
    ->eager
    ->request()->body;
{% endhighlight %}

Three type of links are provided. 

 * `linkSelf($rel)` Replace with the linked resource.
 * `linkNew($rel)`  Add linked resources to the base resource.
 * `linkCrawl($rel)` Crawl the link to build a resource tree.

## Link Annotation

### @Link
{% highlight php %}
<?php
    /**
     * @Link(rel="profile", href="/profile{?id}")
     */
    public function onGet($id)
{% endhighlight %}

Set the link with `rel` key name and `href` resource URI. 

 * NOTE: When using the `hal` context, `@Link` is used for a HAL link.

{% highlight php %}
<?php
use BEAR\Resource\Annotation\Link;

/**
 * @Link(crawl="post-tree", rel="post", href="app://self/post?author_id={id}")
 */
public function onGet($id = null)
{% endhighlight %}

A `crawl` tagged link will then be [crawled](https://github.com/koriym/BEAR.Resource#crawl) with `linkCrawl`.

Find out more about the `@Link` annotation at  BEAR.Resource [README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.md).

### @Embed
{% highlight php %}
<?php
use BEAR\Resource\Annotation\Embed;

    /**
     * @Embed(rel="website", src="/website{?id}")
     */
    public function onGet($id)
{% endhighlight %}

You can embed another resource by using `src`. Just think of a regular image tag (`<img src="...">`) in HTML. It embeds an image resource into its own element. It's exactly the same.

 * NOTE: In the HAL renderer, this is used as `__embed`.

## Bind parameters

You can bind method parameters to an "external value". The external value might be a web context or any other resource state.

### Web context parameter

For instance, Instead you "pull" `$_GET` or any global the web context values, You can bind PHP super global values to method parameters.

{% highlight php %}
<?php
use Ray\WebContextParam\Annotation\QueryParam;

    /**
     * @QueryParam("id")
     */
    public function foo($id = null)
    {
      // $id = $_GET['id'];
{% endhighlight %}

The above example is a case where a key name and the parameter name are the same.
You can specify `key` and `param` values when they don't match.

{% highlight php %}
<?php
use Ray\WebContextParam\Annotation\CookieParam;

    /**
     * @CookieParam(key="id", param="tokenId")
     */
    public function foo($tokenId = null)
    {
      // $tokenId = $_COOKIE['id'];
{% endhighlight %}

Full List

{% highlight php %}
<?php

use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

    /**
     * @QueryParam(key="id", param="userId")
     * @CookieParam(key="id", param="tokenId")
     * @EnvParam("app_mode")
     * @FormParam("token")
     * @ServerParam(key="SERVER_NAME", param="server")
     */
    public function foo($userId = null, $tokenId = "0000", $app_mode = null, $token = null, $server = null)
    {
       // $userId   = $_GET['id'];
       // $tokenId  = $_COOKIE['id'] or "0000" when unset;
       // $app_mode = $_ENV['app_mode'];
       // $token    = $_POST['token'];
       // $server   = $_SERVER['SERVER_NAME'];
{% endhighlight %}

This `bind parameter` is also very useful for testing.

### Resource Parameter

We can bind the status of another resource to a parameter with the `@ResourceParam` annotation.

{% highlight php %}
<?php
/**
 * @ResourceParam(param=“name”, uri="app://self//login#nickname")
 */
public function onGet($name)
{
{% endhighlight %}

In this example the `nickname` property of `app://self//login` is bound to `$name`.

## Resource cache

### @Cacheable

{% highlight php %}
<?php
/**
 * @Cacheable
 */
class User extends ResourceObject
{% endhighlight %}

`@Cacheable` annotated resource objects are cached without a time limit.
The cache will be updated by any non-GET request on the same class  with no expiry time (unless you specify one). A parameter is inspected to determine identity of the resource.

`@Cacheable` annotated resource objects will have `Last-Modified` and `ETag` headers added automatically.

{% highlight php %}
<?php

/**
 * @Cacheable
 */
class Todo
{
    public function onGet($id)
    {
        // read
    }

    public function onPost($id, $name)
    {
        // update
    }
}
{% endhighlight %}

For example, when a request is made to `->post(10, 'shopping')`, the `?id=10` cache will be updated.

Set `update` to false if you don't want to have this auto update.

{% highlight php %}
/**
 * @Cacheable(update=false)
 */
{% endhighlight %}

You can specify a cache life time as `short`, `medium` or `long` on the  `expiry` property.

{% highlight php %}
/**
 * @Cacheable(expiry="short")
 */
{% endhighlight %}


### @Purge @Refresh

You can also update caches with the `@Purge` and `@Refresh` annotation.

{% highlight php %}
<?php
/**
 * @Purge(uri="app://self/user/friend?user_id={id}")
 * @Refresh(uri="app://self/user/profile?user_id={id}")
 */
public function onPut($id, $name, $age)
{% endhighlight %}

You can update the cache for another resource class or even multiple resources at once. `@Purge` deletes a cache where `@Refresh` will recreate cache data.
