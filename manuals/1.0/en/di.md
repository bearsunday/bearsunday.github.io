---
layout: docs-en
title: DI
category: Manual
permalink: /manuals/1.0/en/di.html
---
# DI

Dependency injection is basically providing the objects that an object needs (its dependencies) instead of having it construct them itself.

With dependency injection, objects accept dependencies in their constructors. To construct an object, you first build its dependencies. But to build each dependency, you need its dependencies, and so on. So when you build an object, you really need to build an object graph.

Building object graphs by hand is labour intensive, error prone, and makes testing difficult. Instead, **Dependency Injector** ([Ray.Di](https://github.com/ray-di/Ray.Di)) can build the object graph for you. 

| What is object graph ?
| Object-oriented applications contain complex webs of interrelated objects. Objects are linked to each other by one object either owning or containing another object or holding a reference to another object. This web of objects is called an object graph and it is the more abstract structure that can be used in discussing an application's state. - [Wikipedia](http://en.wikipedia.org/wiki/Object_graph)


Ray.Di is the core DI framework used in BEAR.Sunday, which is heavily inspired by Google [Guice](http://code.google.com/p/google-guice/wiki/Motivation?tm=6) DI framework.

## Overview

The Ray.Di package provides a dependency injector with the following features:

- constructor and setter injection

- automatic injection 

- post-construct initialization

- raw PHP factory code compiler

- dependency naming

- injection point meta data

- instance factories

- Optional Annotation ([Doctrine Annotation](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html))

## Injection

There are three types of dependency classes, constructors, setter methods, and execution methods, which are called injection points.
Implantation in the constructor is mandatory, but the setter method requires the `@Inject` annotation mark to distinguish it from regular methods.

Constructor Injection

```php?start_inline
use Ray\Di\Di\Inject;

class Index
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
```

Setter Injection

```php?start_inline
use Ray\Di\Di\Inject;

class Index
{
    private $logger;

    /**
     * @Inject
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
```

Assisted Injection (Method Injection)

```php?start_inline
use Ray\Di\Di\Assisted;

class Index
{
    /**
     * @Assisted({"logger"})
     */
    public function doSomething(LoggerInterface $logger = null)
    {
        $logger-log('log message..');
    }
```

## Bindings

To create bindings, extend AbstractModule and override its configure method. In the method body, call bind() to specify each binding. These methods are type checked in compile time and will report errors if you use the wrong types. Once you've created your modules, pass them as arguments to Injector to build an injector.

Use modules to create linked bindings, instance bindings, provider bindings, constructor bindings and untargeted bindings.


```php?start_inline
class Tweet
extends AbstractModule
{
    protected function configure()
    {
        $this->bind(TweetClient::class);
        $this->bind(TweeterInterface::class)->to(SmsTweeter::class)->in(Scope::SINGLETON);
        $this->bind(UrlShortenerInterface)->toProvider(TinyUrlShortener::class)
        $this->bind('')->annotatedWith(Username::class)->toInstance("koriym")
    }
}
```

There are different types of bonds.

 * Linked Bindings

```php?start_inline
 $this->bind($interface)->to($class);
```

* Named Bindings

```php?start_inline
$this->bind($interface)->annotatedWith($name)->to($class);
```

 * Constructor Bindings

```php?start_inline
$this->bind($interface)->toConstructor($class, [$varName => $name]);
```

 * Untarget Bindings

```php?start_inline
$this->bind($class);
```

 * Provider Bindings

```php?start_inline
$this->bind($interface)->toProvider($provider);
```

 * Instance Bindings

```php?start_inline
$this->bind($interface)->toInstance($instance);
```

## Linked Bindings

Linked bindings map a type to its implementation.

```php?start_inline
class ListerModule extends AbstractModule
{
    public function configure()
    {
        $this->bind(LoggerInterface::class)->to(Logger::class);
    }
}
```

## Named Bindings

If there is more than one implementation class on an interface, or in the case of a scalar type dependency that does not have an interface, we **name** the dependency  in order to select the right class to be instantiated.

```php?start_inline
class ListerModule extends AbstractModule
{
   public function configure()
   {
       $this->bind(LoggerInterface::class)->annotatedWith('prod')->to(Logger::class);
       $this->bind(LoggerInterface::class)->annotatedWith('dev')->to(Logger::class);
   }
}
```

Dependencies bound by named bindings are received with the `@Named` annotation.

```php?start_inline
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class Index
{
    private $logger;

    /**
     * @Inject
     * @Named("prod")
     */
    public function setLogger(LoggerInterface $foo)
    {
        $this->logger = $logger;
    }
```

The `Qualifier` annotation can be used instead of a constant with `@Named` annotation.

```php?start_inline
/**
 * @Annotation
 * @Target("METHOD")
 */
final class Prod
{
}
```

```php?start_inline
$this->bind(LoggerInterface::class)->annotatedWith(Prod::class)->to(Logger::class);
```

```php?start_inline
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class Index
{
    private $logger;

    /**
     * @Inject
     * @Prod
     */
    public function setLogger(LoggerInterface $foo)
    {
        $this->logger = $logger;
    }
```

If there are multiple arguments, specify a comma separated string with '{variable name}={dependency name}' pair.

```php?start_inline
/**
 * @Inject
 * @Named("paymentLogger=payment_logger,debugLogger=debug_logger")
 */
public __construct(LoggerInterface $paymentLogger, LoggerInterface $debugLogger)
{
```

## Untargeted Bindings
   

You may create bindings without specifying a target. This is most useful for concrete classes. An untargeted binding informs the injector about a type, so it may prepare dependencies eagerly. Untargeted bindings have no to a clause, like so:

```php

protected function configure()
{
    $this->bind(MyConcreteClass::class);
    $this->bind(AnotherConcreteClass::class)->in(Scope::SINGLETON);
}
```

Note: All BEAR.Sunday resource classes are bound with "Untargeted Bindings". So if there is a problem with dependency resolution on even an unused resource, an Unbound exception is raised.


## Constructor Bindings

When @Inject annotation cannot be applied to the target constructor or setter method because it is a third party class, Or you simply don't like to use annotations. Provider Binding provides the solution to this problem. By calling your target constructor explicitly, you don't need reflection and its associated pitfalls. But there are limitations of that approach: manually constructed instances do not participate in AOP.

To address this, Ray.Di has toConstructor bindings.

The first argument is the class name, the second argument `{variable name}=>{dependency name}` name binding, and the third argument is setter injection.

```php
<?php
class WebApi implements WebApiInterface
{
    private $id;
    private $password;
    private $client;
    private $token;

    /**
     * @Named("id=user_id,password=user_password")
     */
    public function __construct(string $id, string $password)
    {
        $this->id = $id;
        $this->password = $password;
    }
    
    /**
     * @Inject
     */
    public function setGuzzle(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Inect(optional=true)
     * @Named("token")
     */
    public function setOptionalToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * @PostConstruct
     */
    public function initialize()
    {
    }
```

All annotation in dependent above can be removed by following `toConstructor` binding.

```php
<?php
protected function configure()
{
    $this
        ->bind(WebApiInterface::class)
        ->toConstructor(
            WebApi::class,                              // string $class_name
            [
                ['id' => 'user_id'],                    // array $name
                ['passowrd' => 'user_password']
            ],
            (new InjectionPoints)                       // InjectionPoints　$setter_injection
                ->addMethod('setGuzzle', 'token')
                ->addOptionalMethod('setOptionalToken'),
            'initialize'                                // string $postCostruct
        );
    $this->bind()->annotated('user_id')->toInstance($_ENV['user_id']);
    $this->bind()->annotated('user_password')->toInstance($_ENV['user_password']);
}
```

## PDO Example

Here is the example for the native [PDO](http://php.net/manual/ja/pdo.construct.php) class. 

```php?start_inline
public PDO::__construct ( string $dsn [, string $username [, string $password [, array $options ]]] )
```

```php?start_inline
protected function configure()
{
    $this->bind(\PDO::class)->toConstructor(
        \PDO::class,
        [
            ['pdo' => 'pdo_dsn'],
            ['username' => 'pdo_username'],
            ['password' => 'pdo_password']
        ]
    )->in(Scope::SINGLETON);
    $this->bind()->annotatedWith('pdo_dsn')->toInstance($dsn);
    $this->bind()->annotatedWith('pdo_username')->toInstance($username);
    $this->bind()->annotatedWith('pdo_password')->toInstance($password);
}
```

## Provier Bindings

Provider bindings map a type to its provider(factory).

```php?start_inline
$this->bind(TransactionLogInterface::class)->toProvider(DatabaseTransactionLogProvider::class);
```

The provider class implements `ProviderInterface` interface, which is a simple, general interface for supplying values:


```php?start_inline
use Ray\Di\ProviderInterface;

interface ProviderInterface
{
    public function get();
}
```

Provider can take dependency.


```php?start_inline
use Ray\Di\ProviderInterface;

class DatabaseTransactionLogProvider implements Provider
{
    private $pdo;

    /**
     * @Named("original")
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get()
    {
        $this->pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);

        return $pdo;
    }
}
```

AOP is not possible with Provider Bindings. You should only do this binding when you can not do with other bindings.

### Context Provider Bindings

You may want to create an object using the context when binding with Provider. For example, you want to inject different connection destinations on the same DB interface. In such a case, we bind it by specifying the context (string) with toProvider().

```php?start_inline
$dbConfig = ['user' => $userDsn, 'job' => $jobDsn, 'log' => $logDsn];
$this->bind()->annotatedWith('db_config')->toInstance(dbConfig);
$this->bind(Connection::class)->annotatedWith('usr_db')->toProvider(DbalProvider::class, 'user');
$this->bind(Connection::class)->annotatedWith('job_db')->toProvider(DbalProvider::class, 'job');
$this->bind(Connection::class)->annotatedWith('log_db')->toProvider(DbalProvider::class, 'log');
```

Providers are created for each context.

```php?start_inline
class DbalProvider implements ProviderInterface, SetContextInterface
{
    private $dbConfigs;

    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @Named("db_config")
     */
    public function __construct(array $dbConfigs)
    {
        $this->dbConfigs = $dbConfigs;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $config = $this->dbConfigs[$this->context];
        $conn = DriverManager::getConnection(config);

        return $conn;
    }
}
```
It is the same interface, but you can receive different connections made by Provider.

```php?start_inline
/**
 * @Named("userDb=user_db,jobDb=job_db,logDb=log_db")
 */
public function __construct(Connection $userDb, Connection $jobDb, Connection $logDb)
{
  //...
}
```

### Injection Point
    

An InjectionPoint is a class that has information about an injection point. It provides access to metadata via \ReflectionParameter or an annotation in Provider.

For example, the following get() method of Psr3LoggerProvider class creates injectable Loggers. The log category of a Logger depends upon the class of the object into which it is injected.


```php?start_inline
class Psr3LoggerProvider implements ProviderInterface
{
    /**
     * @var InjectionPoint
     */
    private $ip;

    public function __construct(InjectionPointInterface $ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return Logger
     */
    public function get()
    {
        $targetClass = $this->ip->getClass()->getName();
        $logger = new \Monolog\Logger(targetClass);
        $logger->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));

        return $logger;
    }
}
```

InjectionPointInterface provides the following methods:

```php?start_inline
$ip->getClass();      // \ReflectionClass
$ip->getMethod();     // \ReflectionMethod
$ip->getParameter();  // \ReflectionParameter
$ip->getQualifiers(); // (array) $qualifierAnnotations[]
```

## Instance Bindings
   

You can bind a type to an instance of that type. This is usually only useful for objects that don't have dependencies of their own, such as value objects:

```php?start_inline
protected function configure()
{
    $this->bind()->annotatedWith("message")->toInstance('Hello');
}
```

Use `NamedModule` in order to bind multiple constants at once,

```php?start_inline
protected function configure()
{
    $names = [
        'lang' => 'en',
        'message' => 'Hello'
    ];
    $this->install(new NamedModule($names));
}
```

```php?start_inline
/**
 * @Named("message")
 */
public function setMessage(string $message) // Hello
{
```

Objects can also be bound, but should only be **value objects**.

```php?start_inline
protected function configure()
{
    $this->bind(UserInterface::class)->toInstance(new User); // Serialized to save
}
```

## Object life cycle

`@PostConstruct` is used on methods that need to get executed after dependency injection has finalized to perform any extra initialization.


```php?start_inline
use Ray\Di\Di\PostConstruct;

/**
 * @PostConstruct
 */
public function onInit()
{
    //....
}
```

Methods are called in the following order:

 * Constructor
 * Setter methods (randam order)
 * `@PostConstruct` method

## Scopes

By default, Ray returns a new instance each time it supplies a value. This behavior is configurable via scopes.

```php?start_inline
use Ray\Di\Scope;

protected function configure()
{
    $this->bind(TransactionLog::class)->to(InMemoryTransactionLog::class)->in(Scope::SINGLETON);
}
```

## Assisted Injection
   
It is also possible to inject dependencies directly in the invoke method parameter(s). When doing this, add the dependency to the end of the arguments and annotate the method with @Assisted with having assisted parameter(s). You need the null default for that parameter.

```php?start_inline
use Ray\Di\Di\Assisted;

class Index
{
    /**
     * @Assisted({"db"})
     */
    public function doSomething($id, DbInterface $db = null)
    {
        $this->db = $db;
    }
```

You can also provide dependency which depends on other dynamic parameters in the method invocation. `MethodInvocationProvider` provides [MethodInvocation](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php) object.

```php?start_inline
class HorizontalScaleDbProvider implements ProviderInterface
{
    /**
     * @var MethodInvocationProvider
     */
    private $invocationProvider;

    public function __construct(MethodInvocationProvider $invocationProvider)
    {
        $this->invocationProvider = $invocationProvider;
    }

    public function get()
    {
        $methodInvocation = $this->invocationProvider->get();
        list($id) = methodInvocation->getArguments()->getArrayCopy();

        return new UserDb($id); // $id for database choice.
    }
}
```

## Debug

Complex bindings are eventually compiled into simple PHP factory code and outputted to the `var/tmp/{context}` folder.
By looking at the generated file, you can see which setter method is effective and which dependency and how (Singleton?) it was injected.

The file name is `{interface} - {name}` and its contents are such code.

```
<?php

$instance = new \MyVendor_Todo_Resource_App_Todos_c0kmGJA();
$instance->setRenderer($singleton('BEAR\\Resource\\RenderInterface-'));
$instance->setAuraSql($singleton('Aura\\Sql\\ExtendedPdoInterface-'));
$instance->setQueryLocator($prototype('Koriym\\QueryLocator\\QueryLocatorInterface-'));
$instance->bindings = array('onGet' => array($singleton('BEAR\\Resource\\Interceptor\\JsonSchemaInterceptor-')));
return $instance;
```

 * `MyVendor_Todo_Resource_App_Todos_c0kmGJA` postfixed with a generated string class is "aspect" bound class.
 * `$singleton('BEAR\\Resource\\RenderInterface-')` having singleton instance which bound `RenderInterface` interface.
 * `$instance->bindings` has `[{method name} => {interceptor}]` intercept information array.