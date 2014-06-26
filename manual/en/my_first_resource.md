---
layout: default
title: BEAR.Sunday | My First Resource
category: My First - Tutorial
---

# My First Resource Object

## Application Resource

Here we will pass in a `name` and create a `greeting` resource which the greeting will return.
In terms of MVC the `Model` in BEAR.Sunday is called an `Application Resource`. 
An application resource is used as an internal API within the application.

## Resource Architecture 

A resource is a bundle of information. 
He we have a greeting (`greeting`) which is used as a `greeting resource`.
In order to create the resource object class the following is needed.

 * URI
 * Request Interface 

The pattern is as follows.

| Method | URI                         | Query      |
|--------|-----------------------------|------------|
| get    | app://self/first/greeting   |?name=Name  |

The expected greeting resource is as below.

Request

```
get app://self/first/greeting?name=BEAR
```

Response

```
Hello, BEAR.
```

## Resource Object 

Let's implement the Sandbox application. The URI, PHP class and file layout are as follows. 

| URI | Class | File |
|-----|--------|-----|
| app://self/first/greeting | Sandbox\Resource\App\First\Greeting | apps/Demo.Sandbox/src/Sandbox/Resource/App/First/Greeting.php |

Implementing the request interface (method).

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\Resource\App\First;

use BEAR\Resource\ResourceObject;

/**
 * Greeting resource
 */
class Greeting extends ResourceObject
{
    /**
     * @param string $name
     *
     * @return string
     */
    public function onGet($name)
    {
        return "Hello, {$name}";
    }
}
{% endhighlight %}

## Command Line Testing 

Let's try this out using the Command Line Interface (CLI). 
In the console we will enter some commands, starting with a *failure*.

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get app://self/first/greeting
```

400 Bad Request is returned in the response.

```
400 Bad Request
...
[BODY]
```

As you can see in the header information that an exception has been raised, 
you can decipher that in the query a `name` is required. 
Using the *`OPTIONS` Method* you can more accurately examine this.

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php options app://self/first/greeting

200 OK
allow: ["get"]
param-get: ["name"]
...
```

This tells us that the resource has the `GET` method enabled and requires 1 parameter `name`.
If this `name` parameter was to be optional you would wrap it in parenthesis `(name)`.
Now we know about the required parameters via the options method, let's try again.

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get app://self/first/greeting?name=BEAR

200 OK
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Thu, 26 Jun 2014 11:25:07 GMT"]
[BODY]
Hello, BEAR
```

Now the correct response is returned. Success!

## The resource object is returned 

This greeting resource returns a string when run, 
but if you alter it as below it will be handled in the same way.
Which ever method is used the request made by the client will return a resource object.

{% highlight php startinline %}
public function onGet($name)
{
    $this->body = "Hello, {$name}";
    return $this;
}
{% endhighlight %}

Let's change the `onGet` method like this and check that the response returned has not changed.
