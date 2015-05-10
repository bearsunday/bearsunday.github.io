---
layout: docs-en
title: Resource
category: Manual
permalink: /manuals/1.0/en/resource.html
---

 * *[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/resource.md) needs to be proofread by an English speaker. If interested please send me a pull request. Thank you.*

# Object as a service

The application of BEAR.Sunday is the the collection of resources. It is truly [RESTful](http://en.wikipedia.org/wiki/Representational_state_transfer).
A HTTP method is mapped to PHP method. The `resource object` works as a service.

Here are examples of resource object:

{% highlight php %}
<?php
class Index extends ResourceObject
{
    public function onGet($a, $b)
    {
        $this->code = 200; // 省略可
        $this['result'] = $a + $b; // $a = $_GET['a']; $b = $_GET['b'];

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
        $this->code = 201; // status code
        $this->headers['Location'] = '/todo/new_id'; // location header for new resource
        
        return $this;
    }
}
{% endhighlight %}

The PHP resource class has the URI like `app://self/blog/posts/?id=3` or `page://self/index`. Also it has  method which correspond to HTTP method `onGet`, `onPost`, `onPut`, `onPatch`, or `onDelete`.
`$_GET` is passed to the parameters of `onGet` method, `$_POST` is same to `onPost`. `content-type`ed value will be passed to PHP method for `onPut`,`onPatch` or `onDelete`, 

With given parameters, The resource status (`code`,`headers` or`body`) should be changed by method and return `$this`. 


 * NOTE: `$this->body['price'] = 10;` can be written as `$this['price'] = 10;` with syntax sugar.

## Resource scheme


Resource scheme has two types. The one is `App` resource. It is an **API**.
The other one is `Page` resource. It is Web Page.
`Page` resource crate web page using `App` resource. It is like `Controller` use `Model` for output.

The following is the example how `URI` and `class` are mapped when `koriym\todo` application name is given.

| URI | Class |
|-----+-------|
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts | Koriym\Todo\Resource\App\Blog\Posts |


# Resource client

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
If you have no `eager` options with the request, It is just a request. Request invocation is made at representation.

{% highlight php %}
<?php
$posts = $this->resource->get->uri('app://self/posts')->request(); //lazy
$posts = $this->resource->get->uri('app://self/posts')->eager->request(); // eager
{% endhighlight %}

A `request()` method without `eager` returns invokable request object, Or you can invoke by `$posts()`.
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

For instance, Instead you "pull" `$_GET` or any global web context values, You can bind PHP super global values to method parameters.

When key name and parameter name is same:

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


Specify `key` and `param` if it is not matched.

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
 * @Etag
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
 * @Etag
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


### @Etag

When HTTP request contains `Etag` and contents is not modified, `304 Not Modified` will be responded.


## BEAR.Resource

You can find more detail at BEAR.Resource [README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.md).
