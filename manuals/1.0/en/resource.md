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
  
It has methods which corresponds to HTTP verbs `onGet`, `onPost`, `onPut`, `onPatch`, or `onDelete`.
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

The format defined by `content-type` header will be passed to for `onPut`,`onPatch` or `onDelete`.

{% highlight php %}
<?php
    class User
    {
        public function onPut($id, $todo)
        {
            // `x-www-form-urlencoded` or `application/json`
            $id
{% endhighlight %}

The resource status (`code`,`headers` or`body`) is changed by method with given parameters. Then the resource class return itself(`$this`). 

### Syntax sugar

The body access has a syntax sugar. 
{% highlight php %}
<?php

$this['price'] = 10;
// is same as
$this->body['price'] = 10;
{% endhighlight %}

## Scheme

The equivalent to a MVC model is an `app` resource. A resource functions as an internal API, but as it is designed using REST it also works as an external API transport.
The `page` resource carries out the page controller role is also a resource, according to its URL calls an application resource and builds itself.

| URI | Class |
|-----+-------|
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts | Koriym\Todo\Resource\App\Blog\Posts |

## Method

Resources have 6 interfaces conforming to the HTTP methods.

| **method** | **description**|
|--------|------------|
| GET | Resource retrieval |
| PUT | Resource update and creation |
| PATCH | Resource update |
| POST | Resource creation |
| DELETE | Resource delete |
| OPTIONS | Resource access method query |

#### GET 
Resource reading. This method does not provide any changing of the resource state. A safe method with no side affects.

#### PUT 
Performs resource updates and also creation. This method has the benefit that even if you run it once, running it many more times will have no more effect. See [Idempotence](http://en.wikipedia.org/wiki/Idempotence).

#### PATCH

Performs resource updatese, but unlike PUT, it applies a delta rather than replacing the entire resource. 

#### POST 
Performs resource creation. If you run the request multiple times the resource will be created that many times. A method with no idempotence.

#### DELETE 
Resource deletion. Has idempotence just as PUT.

#### OPTIONS 
Inspects which methods and parameters can be used on the resource. Just like `GET` there is no effect on the resource.


## Client

You need **Resource client** to request resource. See the following example. `ResourceInject` trait is useful for injection.

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

This code invoke `GET` request to `app://self/blog/posts` App resource with `?id=1` query.
If you have no `eager` options with the request, It is just a request. Request invocation will be made at representation.

{% highlight php %}
<?php
$posts = $this->resource->get->uri('app://self/posts')->request(); //lazy
$posts = $this->resource->get->uri('app://self/posts')->eager->request(); // eager
{% endhighlight %}

A `request()` method without `eager` returns invokable request object, or you can invoke by `$posts()`.
You can assign this value to template engine or embed to another resource. It will be lazy evaluated.

## Link request

Resource can be linked in various way.

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

 * `linkSelf($rel)` Replace with linked resource.
 * `linkNew($rel)`  Add linked resource to source resource.
 * `linkCrawl($rel)` Crawl the link to build resource tree.

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

 * NOTE: In `hal` context, `@Link` is used for HAL link.

{% highlight php %}
<?php
use BEAR\Resource\Annotation\Link;

/**
 * @Link(crawl="post-tree", rel="post", href="app://self/post?author_id={id}")
 */
public function onGet($id = null)
{% endhighlight %}

A `crawl` tagged link will be [crawled](https://github.com/koriym/BEAR.Resource#crawl) with `linkCrawl`.

Found more info about the `@Link` at  BEAR.Resource [README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.md).

### @Embed
{% highlight php %}
<?php
use BEAR\Resource\Annotation\Embed;

    /**
     * @Embed(rel="website", src="/website{?id}")
     */
    public function onGet($id)
{% endhighlight %}

You can embed another resource in `src`. Imagine  `<img src="...">` in HTML. It embedded image resource into its own html. It's just like that.

 * NOTE: In HAL renderer, used as `__embed`.

## Bind parameter

You can bind method parameter to "external value". The external value might be web context or other resource status.

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

The above example is in the case a key name and parameter name is same.
Specify `key` and `param` when it isn't matched.

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

full list

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

This `bind parameter` is very useful for testing.

### Resource Parameter

We can bind the status of other resource to parameter with `@ResourceParam` annotation.

{% highlight php %}
<?php
/**
 * @ResourceParam(param=“name”, uri="app://self//login#nickname")
 */
public function onGet($name)
{
{% endhighlight %}

In this example, `nickname` of `app://self//login` is bound to `$name`.

## Resource cache

### @Cacheable

{% highlight php %}
<?php
/**
 * @Cacheable
 */
class User extends ResourceObject
{% endhighlight %}

`@Cacheable` annotated resource object works as cache without time limit.
Cache is updated not by expiry time (unless you specify) but any no GET request in same class. (Parameter is looked to determine identify)

`@Cacheable` annotated resource object will have `Last-Modified` and `ETag` headers.

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

For instance, when `->post(10, 'shopping')` is given at a request, `?id=10` cache will be updated.
Set `update` false if you don't want to have this auto update.

{% highlight php %}
/**
 * @Cacheable(update=false)
 */
{% endhighlight %}

You can specify `expiry` as cache life time. `short`, `medium` or `long` are valid for `expiry`.

{% highlight php %}
/**
 * @Cacheable(expiry="short")
 */
{% endhighlight %}


### @Purge @Refresh

You can also update cache with `@Purge` and `@Refresh`annotation.

{% highlight php %}
<?php
/**
 * @Purge(uri="app://self/user/friend?user_id={id}")
 * @Refresh(uri="app://self/user/profile?user_id={id}")
 */
public function onPut($id, $name, $age)
{% endhighlight %}

You can update another resource class and multiple resource at once. `@Purge` delete cache. `@Refresh` recreate cache data.
