---
layout: default
title: BEAR.Sunday | Dependency Injection
category: Manual
---


Dependency Injection framework
==============================

**Ray.Di** was created in order to get Guice style dependency injection in PHP projects. It tries to mirror Guice's behavior and style. [Guice]((http://code.google.com/p/google-guice/wiki/Motivation?tm=6) is a Java dependency injection framework developed by Google.

 * Supports some of the [JSR-250](http://en.wikipedia.org/wiki/JSR_250) object lifecycle annotations (`@PostConstruct`, `@PreDestroy`)
 * Provides an [AOP Alliance](http://aopalliance.sourceforge.net/)-compliant aspect-oriented programming implementation.
 * Extends [Aura.Di](http://auraphp.github.com/Aura.Di).
 * [Doctrine.Common](http://www.doctrine-project.org/projects/common) annotations.

_Not all features of Guice have been implemented._


Overview
--------

Here is a basic example of dependency injection using Ray.Di.

{% highlight php startinline %}
<?php
use Ray\Di\Injector;
use Ray\Di\AbstractModule;

interface FinderInterface
{
}

class Finder implements FinderInterface
{
}

class Lister
{
    public $finder;

    /**
     * @Inject
     */
    public function setFinder(FinderInterface $finder)
    {
        $this->finder = $finder;
    }
}

class Module extends \Ray\Di\AbstractModule
{
    public function configure()
    {
        $this->bind('MovieApp\FinderInterface')->to('MovieApp\Finder');
    }
}

$injector = Injector::create([new Module]);
$lister = $injector->getInstance('MovieApp\Lister');
$works = ($lister->finder instanceof MovieApp\Finder);
echo(($works) ? 'It works!' : 'It DOES NOT work!');

// It works!
{% endhighlight %}

This is an example of **Linked Bindings**. Linked bindings map a type to its implementation.

### Provider Bindings

[Provider bindings](http://code.google.com/p/rayphp/wiki/ProviderBindings) map a type to its provider.

{% highlight php startinline %}
<?php
$this->bind('TransactionLogInterface')->toProvider('DatabaseTransactionLogProvider');
{% endhighlight %}

The provider class implements Ray's Provider interface, which is a simple, general interface for supplying values:

{% highlight php startinline %}
<?php
use Ray\Di\ProviderInterface;

interface ProviderInterface
{
    public function get();
}
{% endhighlight %}

Our provider implementation class has dependencies of its own, which it receives via a contructor annotated with `@Inject`.
It implements the Provider interface to define what's returned with complete type safety:

{% highlight php startinline %}
<?php
class DatabaseTransactionLogProvider implements Provider
{
    private ConnectionInterface connection;

    /**
     * @Inject
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function get()
    {
        $transactionLog = new DatabaseTransactionLog;
        $transactionLog->setConnection($this->connection);

        return $transactionLog;
    }
}
{% endhighlight %}

Finally we bind to the provider using the `toProvider()` method:

{% highlight php startinline %}
<?php
$this->bind('TransactionLogInterface')->toProvider('DatabaseTransactionLogProvider');
{% endhighlight %}

### Named Bindings

Ray comes with a built-in binding annotation `@Named` that takes a string.

{% highlight php startinline %}
<?php
/**
 *  @Inject
 *  @Named("processor=Checkout")
 */
public RealBillingService(CreditCardProcessor $processor)
{
{% endhighlight %}

To bind a specific name, pass that string using the `annotatedWith()` method.

{% highlight php startinline %}
<?php
protected function configure()
{
    $this->bind('CreditCardProcessorInterface')
        ->annotatedWith('Checkout')
        ->to('CheckoutCreditCardProcessor');
}
{% endhighlight %}

### Instance Bindings

{% highlight php startinline %}
<?php
protected function configure()
{
    $this->bind('UserIntetrface')->toInstance(new User);
}
{% endhighlight %}

You can bind a type to an instance of that type. This is usually only useful for objects that don't have dependencies of their own, such as value objects:

{% highlight php startinline %}
<?php
protected function configure()
{
    $this->bind()->annotatedWith("login_id")->toInstance('bear');
}
{% endhighlight %}

### Constructor Bindings

Occasionally it's necessary to bind a type to an arbitrary constructor. This arises when the `@Inject` annotation cannot be applied to the target constructor. eg. when it is a third party class.

{% highlight php startinline %}
<?php
class TransactionLog
{
    public function __construct($db)
    {
     // ....
{% endhighlight %}

{% highlight php startinline %}
<?php
protected function configure()
{
    $this->bind('TransactionLog')->toConstructor(['db' => new Database]);
}
{% endhighlight %}

## Scopes

By default, Ray returns a new instance each time it supplies a value. This behaviour is configurable via scopes.

{% highlight php startinline %}
<?php
protected function configure()
{
    $this->bind('TransactionLog')->to('InMemoryTransactionLog')->in(Scope::SINGLETON);
}
{% endhighlight %}

## Object life cycle

`@PostConstruct` is used on methods that need to get executed after dependency injection has finalized to perform any extra initialization.

{% highlight php startinline %}
<?php
/**
 * @PostConstruct
 */
public function onInit()
{
    //....
}
{% endhighlight %}

`@PreDestroy` is used on methods that are called after script execution finishes or exit() is called.
This method is registered by using **register_shutdown_function**.

{% highlight php startinline %}
<?php
/**
 * @PreDestroy
 */
public function onShutdown()
{
    //....
}
{% endhighlight %}

## Automatic Injection

Ray.Di automatically injects all of the following:

 * instances passed to `toInstance()` in a bind statement
 * provider instances passed to `toProvider()` in a bind statement

The objects will be injected while the injector itself is being created. If they're needed to satisfy other startup injections, Ray.Di will inject them before they're used.

## Installation

A module can install other modules to configure more bindings.

 * Earlier bindings have priority even if the same binding is made later.
 * The module can use an existing bindings by passing in `$this`. The bindings in that module have priority.

{% highlight php startinline %}
<?php
protected function configure()
{
    $this->install(new OtherModule);
    $this->install(new CustomiseModule($this);
}
{% endhighlight %}

## Injection in the module

You can use a built-in injector in the module which uses existing bindings.

{% highlight php startinline %}
<?php
protected function configure()
{
    $this->bind('DbInterface')->to('Db');
    $dbLogger = $this->requestInjection('DbLogger');
}
{% endhighlight %}

## Aspect Oriented Programing

You can use Aspect Oriented Programing of Ray.Aop. It makes binding interceptors easier, and also resolves aspect dependency.

{% highlight php startinline %}
<?php
class TaxModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Ray\Di\Aop\RealBillingService'),
            $this->matcher->annotatedWith('Tax'),
            [new TaxCharger]
        );
    }
}
{% endhighlight %}

{% highlight php startinline %}
<?php
class AopMatcherModule extends AbstractModule
{
    pro
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),                 // In any class and
            $this->matcher->startWith('delete'), // ..the method start with "delete"
            [new Logger]
        );
    }
}
{% endhighlight %}
