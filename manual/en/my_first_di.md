---
layout: default
title: BEAR.Sunday | My First DI
category: My First - Tutorial
--- 

# My First DI

## Dependencies on Greeting that Enables a Whole Range of Greetings 

In the greeting resource in [my_first_resource My First Resource] the word "Hello" is fixed.
Here using [Dependency Injection](http://en.wikipedia.org/wiki/Dependency_injection)
we can make all sorts of greeting respond to this. 

## Dependency

In order to show a greeting in [my_first_resource Greeting Resource] we need a string. 
This string is the dependency. In which way can we prepare this string? 
There are predominantly 3 ways.


### 1. Dependency Inside

The descriptor is inside the dependency code.
In [my_first_resource My First Resource] 'Hello' was hard coded inside a method.
If you make this a class constant the readability and maintainability is increased.
However the existence of this within the dependency code means that to some degree it is the same as hard coding.
In order to alter it you will need to alter the class itself still.

### 2. Dependency Pull

Some time ago configuration value were often defined in global variables.
From there we went on to using a configuration object.
In both cases we are pulling in dependencies from an external scope, and so are not really any different.
Even if you use a service locater it is the same. It is pulling in dependencies from external sources.

You still need to change a configuration file or define a configuration in order to test it.

### 3. Dependency Injection

You are not retrieving the greeting string yourself, externally upon construction the greeting string is injected into the class.
As for retrieving the dependency the class is only concerned with receiving the dependency, no request is necessary.
When testing the you simply use the constructor or specified setter to pass in the dependency.

## Take Hold of the Constructor

In [My First Resource Request](my_first_resource_request.html) we used a trait for injection, but here we use a constructor to receive the dependency.

The constructor looks like this.

In the constructor wanting external assignment(injection) we add the `@Inject` annotation.
In which case in order to set the specified injection, we add the annotation `@Named` to the injection point.

*apps/Demo.Sandbox/src/Resource/App/First/Greeting/Di.php*

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\Resource\App\First\Greeting;

use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Greeting resource
 */
class Di extends ResourceObject
{
    /**
     * @param string $message
     *
     * @Inject
     * @Named("greeting_msg")
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * @param string $name
     *
     * @return string
     *
     */
    public function onGet($name = 'anonymous')
    {
        return "{$this->message}, {$name}";
    }
}
{% endhighlight %}

### Let's try a bad injection execution

We have saved the created file so we can make a URI request to `app://self/first/greeting/di`.

We have added the annotation needed for injection, but we haven't done any configuration for what to inject.
So the injector cannot carry out the injection. Let's try this bad injection execution.

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get 'app://self/first/greeting/di'

500 Internal Server Error
x-exception-class: ["Ray\\Di\\Exception\\NotBound"]
x-exception-message: ["typehint='', annotate='greeting_msg' for $message in class 'Demo\\Sandbox\\Resource\\App\\First\\Greeting\\Di'"]
...
```

`Ray\Di\Exception\NotBound` exception is raised.

The exception message shows that there is no DI settings have been bound for the named `greeting_msg` with no typehint. 
A DI configuration is needed to be bound to the named `greeting_msg`.

### Injector DSL 

The DI configuration (Injector Config) takes place in the `configure()` method of the module. 
We add the following to the `configure()` method in class `Demo\Sandbox\Module\App\Dependency`.

*apps/Demo.Sandbox/src/Module/App/Dependency.php*

{% highlight php startinline %}
protected function configure()
{
    // ...
    $this->bind()->annotatedWith('greeting_msg')->toInstance('Hola');
}
{% endhighlight %}

Here we pass 'Hola' into the method we annotated with `@Inject`and`@Named("greeting_msg")` (or even a contstructor).

Here we are directing an object (instance), but we can also can also set a class or factory class name.
Using a factory is is possible to generate a more complex instance. 

Note: Even if you change the generation method, the retrieval descriptor method will not change.

{% highlight php startinline %}
    /**
     * @Inject
     * @Named("greeting_msg")
     */
    public function __construct($message)
    {
        $this->message = $message;
    }
{% endhighlight %}

## Let's Check

```
$ php api.php get 'app://self/first/greeting/di?name=BEAR'
```

```
200 OK
...
[BODY]
"Hola, BEAR"
```

We pass in the instance (string) that we setup in the module configuration.
No matter if you retrieve the greeting text from a DB or from a file, the class will not change.
The only change is in the preparation module.

All we need to do is add an annotation to the class that we are using for DI to use.
There is nothing special about the constructor, no specific method names or use of an object container, it is just a normal clean PHP class.
You can easily unit test by passing a dependency manually.

Your class has become more modular, reusable and testable.
