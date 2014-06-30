---
layout: default
title: BEAR.Sunday | My First Resource Request
category: My First - Tutorial
--- 

# My First Resource Request

## Using the Application Resource 

Until not in the tutorial we have been using the console or web browser to request resources, in this tutorial we will make a resource request from another resource.

We will change the page we made in <a href="my_first_web_page.html">My First Web Page</a> to use the resource we made in [my_first_resource My First Resource].

The page resource will request an application resource.
The page resource is a resource primarily concerned with a page.
Normally a page resource will request all of the necessary application resources in order to compose them together within itself.

Note: For example a page that the controller returns 'Hello World' to, changes into a page that the model returns 'Hello World'.

## Resource Client Preparation 

In BEAR.Sunday the necessary services (object) fundamentally expects everything to be injected externally.
In a resource request we need a resource client.

Using the type hinted Resource client interface (`BEAR\Resource\ResourceInterface`) and mark using the`@Inject` annotation we can have it injected for us (external assignment).

{% highlight php startinline %}
<?php

use BEAR\Resource\ResourceInterface;
use Ray\Di\Di\Inject;

class User
{
    /**
     * @Inject
     */
    public function setResource(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }
{% endhighlight %}

## Using the Trait Setter 

This setter can be used via a trait as in the example below.

{% highlight php startinline %}
<?php

use BEAR\Sunday\Inject\ResourceInject;

class User
{
    use ResourceInject;
}
{% endhighlight %}

## GET Request

A method that makes a resource request to an application resource with a URI `app://self/first/greeting` and appended query string `?name=$name` looks like this.

*apps/Demo.Sandbox/src/Resource/Page/First/Greeting.php*

{% highlight php startinline %}
    /**
     * @param  string $name
     */
    public function onGet($name = 'anonymous')
    {
        $this['greeting'] = $this->resource
            ->get
            ->uri('app://self/first/greeting')
            ->withQuery(['name' => $name])
            ->request();
        
        return $this;
    }
{% endhighlight %}

Here we assign request to `greeting` slot, not a value. If we add `eager` clause,
Then that request is evaluated and assign as value.

## **$_GET** Query

The contents of `$_GET['name']` is passed to the variable `$name`.
When `$_GET['name']` doesn't exist 'anonymous' is passed in.

## Checking the Page with the Command Line 

The resource request `app://self/first/greeting` is stored in the `greeting` slot.

## Check as an API 

Let's check the page resource as an API.

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get 'page://self/first/greeting?name=BEAR'

200 OK
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Mon, 30 Jun 2014 01:22:01 GMT"]
[BODY]
greeting => Demo\Sandbox\Resource\App\First\Greeting(
  uri app://self/first/greeting?name=BEAR,
  code 200,
  headers => array(
  ),
  view ,
  links => array(
  ),
  body Hello, BEAR,
),
...
```

**Hello, BEAR** is passed into the `greeting` slot. If we remove the query what happens ?

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get 'page://self/first/greeting'

200 OK
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Mon, 30 Jun 2014 01:25:24 GMT"]
[BODY]
greeting => Demo\Sandbox\Resource\App\First\Greeting(
  uri app://self/first/greeting?name=anonymous,
  code 200,
  headers => array(
  ),
  view ,
  links => array(
  ),
  body Hello, anonymous,
),
...
```

We have been able to check that the default value has been injected.

## Page Template Preparation 

The template for the page resource is also the same.

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<h1>{$greeting|escape}</h1>
</body>
</html>
```

## Check the HTML through the Command Line 

```
$ php apps/Demo.Sandbox/bootstrap/contexts/dev.php get '/first/greeting?name=Sunday'

200 OK
cache-control: ["no-cache"]
date: ["Mon, 30 Jun 2014 01:30:37 GMT"]
[BODY]
greeting app://self/first/greeting?name=Sunday,

[VIEW]
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<h1>Hello, Sunday</h1>
</body>
</html>
```

We can see the resource view as well as resource value. We can confirm `greeting` slot has request with underlined URI.
This request was evaluated on appearance in view. In this case both has no difference,
but you may want to have a lazy request because the resource appearance will be changed in condition.

## Testing The Page

The page is also a resource. The method of testing these is the same as testing page resources covered in <a href="my_first_test.html">My First Test</a>
