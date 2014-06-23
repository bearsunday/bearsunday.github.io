---
layout: default
title: BEAR.Sunday | Blog Tutorial(5) Creating templates
category: Blog Tutorial
---

# Resource Rendering 

## Resource Renderer Displaying the Resource State 

In the steps up until last time in the posts resource the posts data was set in the page resource by a request to the posts resource. In order to *display* this *resource state* we need to use some HTML rendering.

A resource each internally contain their own renderer. In the sandbox application in order to output HTML a Smarty 3 template engine is injected into all of the resources.

Note: A controller does not retrieve data from the model and pass strings for output to a template engine. In BEAR.Sunday all resources include an view renderer. The responsibility for the output of the model is held by each of the resources themselves.

## Posts Resource Template

*Sandbox/Resource/App/Posts.tpl*

```html
<table class="table table-bordered table-striped">
    <tr>
        <th class="span1">Id</th>
        <th>Title</th>
        <th>Body</th>
        <th>CreatedAt</th>
    </tr>
    {foreach from=$resource->body item=post}
    <tr>
        <td>{$post.id}</td>
        <td><a href="posts/post?id{$post.id}">{$post.title}</a></td>
        <td>{$post.body|truncate:60}</td>
        <td>{$post.created}</td>
    </tr>
    {/foreach}
</table>
```

We are unfolding the contents (body property) of the posts resource ($posts).

## Posts Display Page Template 

*Sandbox/Resource/Resource/Posts.tpl*

```html
<html>
    <body>
    <h1>Posts</h1>
    {$posts}
    </body>
</html>
```

Please notice that the data details held by the posts resource is not shown in the page template. There is only a posts resource place holder, the page, page resource and page template take no notice that posts contain a 'title' or 'body' property etc.

What decides how the post resource should be displayed is the template that the post resource itself contains.

Note: The class that contains the needed information for accomplishment of the 'general principle for assignment of object responsibility' follows the [http://en.wikipedia.org/wiki/GRASP_(object-oriented_design)#Information_Expert Information Expert Pattern]. In this case only the posts resource has anything to do with the template, the posts index page is not concerned posts resource template or the construction of the posts resource. 

## Resource Display = Resource State + Resource Template 

Just like we have seen up to here the resource state is combined with the resource template, the rendered result is sent to the client as a resource representation.

Let's check this through the command line. This time we won't use api.php, we will use dev.php and request the web view.

```
$ php apps/Demo.Sandbox/bootstrap/contexts/dev.php get page://self/blog/posts

200 OK
tag: [1850711642]
x-cache: ["{\"mode\":\"W\",\"date\":\"Mon, 23 Jun 2014 13:44:09 +0200\",\"life\":0}"]
cache-control: ["no-cache"]
date: ["Mon, 23 Jun 2014 11:44:09 GMT"]
[BODY]
posts app://self/blog/posts,

[VIEW]
<html>
    <body>
    <h1>Posts</h1>
    <table class="table table-bordered table-striped">
    <tr>
        <th class="span1">Id</th>
        <th>Title</th>
        <th>Body</th>
        <th>CreatedAt</th>
    </tr>
```

Header information that is helpful in development is output, in the [VIEW] we can check the HTML of the final output.

The request to the posts resource is made the resource request result is assigned to the posts slot.

## Lazy Request 

In a page resource the posts resource request are set in {$posts}. This request is made at the point that the {$posts} placeholder is called inside the template.

So if there is no call this request will never be made, it is up to template to decide whether or not this request is actually made.

## Resource Object 

Not only are you able to handle the set resource representation you can also directly use the item component itself.

```
{$posts.0.title}
```

If you need it a method or property can be used an in an object.

```
{$posts->owner}
{$posts->isPublic()}
```

Note: Having a custom method in this way is not considered resource orientated design. 

The behavior of the resource assigned to the template changes depending on how it is handled. Not Whether pages need to be accessed or not, how these are assigned, are both decided in the view according to the context and not in the controller.

This is one of the many features of BEAR.Sunday.
