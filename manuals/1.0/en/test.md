---
layout: docs-en
title: Test
category: Manual
permalink: /manuals/1.0/en/test.html
---

# Test

Writing appropriate tests will help you write better software.

Clean BEAR.Sunday application where all dependencies are injected and cross-cutting concerns are offered at AOP is test friendly. You can write high coverage tests without framework specific tightly coupled base classes or helper methods.

## Run test

Run `vendor/bin/phpunit` or `composer test`.　Other commands are as follows.

```
composer coverge  // test coverage
composer cs       // very coding standard
composer cs-fix   // fix coding standard
```

## Create resource test case

**Everything is a resource** - BEAR.Sunday application can be tested with resoure access.

This is a test that tests that `201 (Created)` will be returned by POSTing `['title' => 'test']` to URI `page://self/todo` of `Myvendor\MyProject` application in `html-app` context.

```php
<?php

class TodoTest extends TestCase
{
    /**
     * @var \BEAR\Resource\ResourceInterface
     */
    private $resource;
    protected function setUp()
    {
        $this->resource = (new AppInjector('Myvendor\MyProject', 'html-app'))->getInstance(ResourceInterface::class);
    }
    public function testOnPost()
    {
        $page = $this->resource->post->uri('page://self/todo')(['title' => 'test']);
        /* @var $page ResourceObject */
        $this->assertSame(StatusCode::CREATED, $page->code);
    }
}
```

 * Please refer to [App/TodoTest](https://github.com/koriym/Polidog.Todo/blob/master/tests/Resource/App/TodoTest.php) for CRUD test on App resource.
 * For Page Resource testing, please refer to [Page/Index](https://github.com/koriym/Polidog.Todo/blob/master/tests/Resource/Page/IndexTest.php).

## Application Injector

An application-injector (AppInjector) can generate instances of all classes used in an application with a specific context, and can directly test resource objects and their dependencies.


```php?start_inline
$injector = new AppInjector('MyVendor\MyProject', 'test-app'));

// resource client
$resource = $injector->getInstance(ResourceInterface::class);
$index = $resource->uri('page://self/index')();
/* @var $index Index */
$this->assertSame(StatusCode::OK, $page->code);
$todos = $page->body['todos'];

// Generate resource class directly
$user = $resource->newInstance('app://self/user');
// or
$user = $injector->getInstance(User::class);
$name = $index->onGet(1)->body['name']; // BEAR

// Verify form validation
$form = $injector->getInstance(TodoForm::class);
$submit = ['name' => 'BEAR'];
$isValid = $this->form->apply($submit); // true
```

## Test Double

[Test Double](https://en.wikipedia.org/wiki/Test_double) is a generic term for any case where you replace a production object for testing purposes.

In BEAR.Sunday where all dependencies are injected, it is easy to realize a test double with DI, but the test double framework [Ray.TestDouble](https://github.com/ray-di/Ray.TestDouble) Will make it even more useful **"Spy"** will be available as well.

composer install

```
$ composer require ray/test-double 1.x-dev --dev
```

`TestModule` and create modules and install modules.

```php?start_inline
use BEAR\Package\AbstractAppModule;
use Ray\TestDouble\TestDoubleModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new TestDoubleModule);
    }
}
```

We will annotate `@Fakeable` for the subject of the test double.

```php?start_inline
use Ray\TestDouble\Annotation\Fakeable;

/**
 * @Fakeable
 */
class Foo
{
    public function getDate() {
        return date("Ymd");
    }
}
```

Save the test double class to `tests/fake-src` folder with` Fake` prefix attached. We only implement classes that will extend and replace the original class.

```php?start_inline
class FakeFoo extend Foo
{
    public function getDate() {
        return '20170801'; // A stub that simply returns a value
    }
}
```

Add `autoload-dev` to` composer.json` so that autoload works.

```json
"autoload-dev": {
    "psr-4": {
        "MyVendor\\MyProject\\": "tests/fake-src"
    }
},
```

`FakeFoo` will be called instead of` Foo` in `test-*` context.

```php?start_inline
$resource = (new AppInjector('MyVendor\MyProject', 'test-app'))->getInstance(ResourceInterface::class);
```

# Spy

Annotate the class to spy ** with `@Spy` to record the input / output target class.

```php
<?php
use Ray\TestDouble\Annotation\Spy;

/**
 * @Spy
 */
class Calc
{
    public function add($a, $b)
    {
        return $a + $b;
    }
}
```

`Spy::GetLogs($className, $methodName)` will returns the` SpyLog` value object containing method arguments and execution results. You can test the input / output and the number of calls of classes.

```php?start_inline
public function testSpy()
{
    $injector = (new AppInjector('MyVendor\MyProject', 'test-app'))->getInstance(InjectorInterface::class);
    $calc = $injector->getInstance(Calc::class);
    $result = $calc->add(1, 2); // 3

    // get spy logs
    $spy = $injector->getInstance(Spy::class);
    $logs = $spy->getLogs(Calc::class, 'add');
    $this->assertSame(1, count($logs)); // call time
    /* @var $log SpyLog */
    $log = $logs[0]; // first call log

    // check arugments and result of method call of `@Spy` annotated method or class.
    $this->assertSame([1, 2], $log->arguments);
    $this->assertSame(3, $log->result);
}
```

You can also spy the Fake class and inspect calls to test double.

```php?start_inline
/**
 * @Spy
 */
class FakeUserRole extend UserRole
{
    public function getRoleById(string $id) : string
    {
        // ...条件
        return $role
    }
}
```

## Bindings using anonymous classes

You can temporarily bind dependencies with PHP's anonymous class.

```
public function testAnonymousClassBinding()
    $injector = new AppInjector('FakeVendor\HelloWorld', 'hal-app');
    $module = new class extends AbstractModule {
        protected function configure()
        {
            $this->bind(FooInterface::class)->to(Foo::class);
        }
    };
app');
    $index = $injector->getOverrideInstance($module, Index::class);
    $name = $index(['id' => 1])->body['name'];
    $this->assertSame('BEAR', $name);
}
```

## Binding the stub

You can also create a stub with a mocking tool such as phpunit `createMock()` method and bind it with that instance.

```
public function testStub()
{
    $injector = new AppInjector('FakeVendor\HelloWorld', 'hal-app');
    $stub = $this->createMock(FooInterface::class);
    $stub->method('doSomething')
    　　　　->willReturn('foo');
    $module = new class($stub) extends AbstractModule {

        private $stub;

        public function __construct(FooInterface $stub)
        {
            $this->stub = $stub;
        }

        protected function configure()
        {
            $this->bind(FooInterface::class)->toInstance($this->mock);
        }
    };
    $index = $injector->getOverrideInstance($module, Index::class);
    $name = $index(['id' => 1])->body['name'];
    $this->assertSame('BEAR', $name);
}
```

## Best Practice

 * Test the interface, not the implementation.
 * Prefer fake class. The stub is ok. Mock should be avoid in complicated usage.

Reference

 * [Stop mocking, start testing]()
 * [Why is it so bad to mock classes?](https://stackoverflow.com/questions/1595166/why-is-it-so-bad-to-mock-classes)
 * [All About Mocking with PHPUnit](https://code.tutsplus.com/tutorials/all-about-mocking-with-phpunit--net-27252)

---
*[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/test.md) needs to be proofread by an English speaker. If interested please send me a pull request. Thank you.*
