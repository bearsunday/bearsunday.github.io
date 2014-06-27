---
layout: default
title: BEAR.Sunday | Blog Tutorial Adding a Post
category: Blog Tutorial
---

# Adding a Post

## POST Method

In steps up until now we have been able to show posts that have been saved in our database. Next we will go ahead and make a form, however before we do that, let's make it possible to add a post through a console resource operation.

### Create a Posts Resource POST Interface 

Adding a POST interface to allow you to add posts to a posts resource that only has a GET interface method.

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

{% highlight php startinline %}
public function onPost($title, $body)
{
    return $this;
}
{% endhighlight %}

First let's try a POST even in this bare state.

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php post 'app://self/blog/posts'

x-exception-class: ["BEAR\\Resource\\Exception\\SignalParameter"]
x-exception-message: ["$title in Demo_Sandbox_Resource_App_Blog_Posts_cc436baec58dbc6a06237a589e2e39d8RayAop::onPost"]
x-exception-code-file-line: ["(0) ...\/vendor\/bear\/resource\/src\/SignalParameter.php:62"]
x-exception-previous: ["BEAR\\Resource\\Exception\\Parameter: $title in Demo_Sandbox_Resource_App_Blog_Posts_cc436baec58dbc6a06237a589e2e39d8RayAop::onPost"]
x-exception-id: ["e500-efa6b"]
x-exception-id-file: ["...\/vendor\/bear\/demo-apps\/Demo.Sandbox\/var\/log\/e500-efa6b.log"]
cache-control: ["no-cache"]
date: ["Tue, 24 Jun 2014 00:45:57 GMT"]
[BODY]
```

You have not specified the required parameters, a *400 Bad Request* response is returned. 

We can check what the required parameters are by using the `options` method. (@TODO options method does not show all method params now)

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php options 'app://self/blog/posts'

200 OK
allow: ["get","post"]
param-post: ["title,body"]
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Tue, 24 Jun 2014 00:47:25 GMT"]
[BODY]
*NULL
[VIEW]
{
    "_links": {
        "self": {
            "href": "http://localhost/app/blog/posts/"
        }
    }
}
```

The possible methods are shown in the `allow` header, then each of the possible parameters are shown. Parameters shown in parenthesis are optional.

For example a GET method can be requested with either no parameters or by using the `id` parameter. The POST method however *must* contain a `title` and `body` parameter.

The required fields have now become clear. We can now add a query to make our request.

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php post 'app://self/blog/posts?title=hello&body=this%20is%20first%20post'

200 OK
...
[BODY]
*NULL
...
```

A status 200 OK with contents NULL has been returned.
There is no problem, however lets change this to use the *more* accurate 204(No Content) status code.

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

{% highlight php startinline %}
public function onPost($title, $body)
{
    $this->code = 204;
    return $this;
}
{% endhighlight %}

In order to change the status code we set the `code` property.

We have now made it so that we are more accurately informed of the correct status code. This will also help us in our unit tests.

```
204 No Content
...
[BODY]
*NULL
...
```

Implementing the POST interface.

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

{% highlight php startinline %}
public function onPost($title, $body)
{
    $values = [
        'title' => $title,
        'body' => $body,
        'created' => $this->time,
    ];
    $this->db->insert($this->table, $values);
    $this->code = 204;

    return $this;
}
{% endhighlight %}

The interface method which picks up the POST request inserts the post into the database. In this way we are able to add posts.

Just like the GET interface when we use this method the injected DB object is available for us to use. The difference to the GET interface is that it is not the _slave_ DB object that is injected but the _master_.

Please remember that the DB object is bound by the injecting interceptor to all method names that begin with `on`. The bound DB injector depending on the resource request injects the DB object just before the method is requested. Please take notice that the resource request *pays no attention to the preparation or retrieval of the required dependencies, it just uses the object*. In this way BEAR.Sunday adhears to the *separation of concerns* principle in a consistent and unified manner.

### Post Resource Test 

The post has been added, let's make a test to check the added content. When a resource unit test contains a DB test you write code like the following.

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\tests\Resource\App\Blog;

use BEAR\Resource\Code;
use BEAR\Resource\Header;

class PostsTest extends \PHPUnit_Extensions_Database_TestCase
{
    private $resource;

    public function getConnection()
    {
        $pdo = require $_ENV['APP_DIR'] . '/tests/scripts/db.php';

        return $this->createDefaultDBConnection($pdo, 'sqlite');
    }

    public function getDataSet()
    {
        $seed = $this->createFlatXmlDataSet($_ENV['APP_DIR'] . '/tests/mock/seed.xml');
        return $seed;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->resource = $GLOBALS['RESOURCE'];
    }

    public function testOnPost()
    {
        // inc 1
        $before = $this->getConnection()->getRowCount('posts');
        $resourceObject = $this->resource
            ->post
            ->uri('app://self/blog/posts')
            ->withQuery(['title' => 'test_title', 'body' => 'test_body'])
            ->eager
            ->request();

        $this->assertEquals($before + 1, $this->getConnection()->getRowCount('posts'), "failed to add");
    }

    /**
     * @depends testOnPost
     */
    public function testOnPostNewRow()
    {
        $this->resource
            ->post
            ->uri('app://self/blog/posts')
            ->withQuery(['title' => 'test_title', 'body' => 'test_body'])
            ->eager
            ->request();

        // new post
        $entries = $this->resource->get->uri('app://self/blog/posts')->withQuery([])->eager->request()->body;
        $body = array_pop($entries);

        $this->assertEquals('test_title', $body['title']);
        $this->assertEquals('test_body', $body['body']);
    }
}
{% endhighlight %}

We test that the post has been created by the `testOnPost` method, and we check contents by the `testOnPostNewRow` method.

### Creating the Add Post Page 

We have created the app resource that adds a post, we will now create a page resource that grabs input from the web and requests the app resource.

Add a template.

*Demo.Sandbox/src/Resource/Page/Blog/Posts/Newpost.tpl*

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="container">
        <h1>New Post</h1>
        <form action="/blog/posts/newpost" method="POST">
            <input name="X-HTTP-Method-Override" type="hidden" value="POST" />
            <div class="control-group {if $errors.title}error{/if}">
                <label class="control-label" for="title">Title</label>
                <div class="controls">
                    <input type="text" id="title" name="title" value="{$submit.title}">
                    <p class="help-inline">{$errors.title}</p>
                </div>
            </div>
            <div class="control-group {if $errors.body}error{/if}">
                <label>Body</label>
                <textarea name="body" rows="10" cols="40">{$submit.body}</textarea>
                <p class="help-inline">{$errors.body}</p>
            </div>
            <input type="submit" value="Send">
        </form>
    </div>
</body>
</html>
```

Add Newpost page resource and implement the GET and POST interfaces.

*Demo.Sandbox/src/Resource/Page/Blog/Posts/Newpost.php*

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\Resource\Page\Blog\Posts;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Newpost extends ResourceObject
{
    use ResourceInject;

    public function onGet()
    {
        return $this;
    }

    /**
     * @param string $title
     * @param string $body
     */
    public function onPost($title, $body)
    {
        // create post
        $this->resource
            ->post
            ->uri('app://self/blog/posts')
            ->withQuery(['title' => $title, 'body' => $body])
            ->eager->request();

        // redirect
        $this->code = 303;
        $this->headers = ['Location' => '/blog/posts'];
        return $this;
    }
}
{% endhighlight %}

Unlike with the GET interface with the `withQuery()` the parameters for the resource request are set. Note that unlike a regular PHP method there is no order values are set using named parameters. Like a web request it has been set up for the method request to be made with a `key=value` style query. (The key is the parameter name.)

An **eager->request()** shows that the resource request will be made *immediately*.

From the console let's try a `POST` via a newpost page resource request.

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php post 'page://self/blog/posts/newpost?title=hello%20again&body=how%20have%20you%20been%20?'
```

Now when you make a POST request to the newpost page a post resource is added.
