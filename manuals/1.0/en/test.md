---
layout: docs-en
title: Test
category: Manual
permalink: /manuals/1.0/en/test.html
---

# Test

Proper testing makes software better with continuity. A clean application of BEAR.Sunday is test friendly, with all dependencies injected and crosscutting interests provided in the AOP.

## Run test

Run `vendor/bin/phpunit` or `composer test`.　Other commands are as follows.

```
composer test    // phpunit test
composer tests   // test + sa + cs
composer coverge // test coverage
composer pcov    // test coverage (pcov)
composer sa      // static analysis
composer cs      // coding standards check
composer cs-fix  // coding standards fix
```

## Resource test

**Everything is a resource** - BEAR.Sunday application can be tested with resoure access.

This is a test that tests that `201 (Created)` will be returned by POSTing `['title' => 'test']` to URI `page://self/todo` of `Myvendor\MyProject` application in `html-app` context.

```php
<?php

use BEAR\Resource\ResourceInterface;

class TodoTest extends TestCase
{
    private ResourceInterface $resource;
    
    protected function setUp(): void
    {
        $injector = Injector::getInstance('test-html-app');
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testOnPost(): void
    {
        $page = $this->resource->post('page://self/todo', ['title' => 'test']);
        $this->assertSame(StatusCode::CREATED, $page->code);
    }
}
```

## Test Double

A Test Double is a substitute that replaces a component on which the software test object depends. Test doubles can have the following patterns

* Stub (provides "indirect input" to the test target)
* Mock ( validate "indirect output" from the test target inside a test double)
* Spy (records "indirect output" from the target to be tested)
* Fake (simpler implementation that works closer to the actual object)
* _Dummy_ (necessary to generate the test target but no call is made)

### Test Double Binding

There are two ways to change the bundling for a test. One is to change the bundling across all tests in the context module, and the other is to temporarily change the bundling only for a specific purpose within one test only.

#### Context Module

Create a ``TestModule`` to make the `test` context available in bootstrap.

```php
class TestModule extends AbstractModule
{
    public function configure(): void
    {
        $this->bind(DateTimeInterface::class)->toInstance(new DateTimeImmutable('1970-01-01 00:00:00'));
        $this->bind(Auth::class)->to(FakeAuth::class);    
    }
}
```

Injector with test context.

```php
$injector = Injector::getInstance('test-hal-app', $module);
```

#### Temporary binding change

Temporary bundle changes for a single test specify the bundle to override with `Injector::getOverrideInstance`.

```php
public function testBindFake(): void
{
    $module = new class extends AbstractModule {
        protected function configure(): void
        {
            $this->bind(FooInterface::class)->to(FakeFoo::class);
        }
    }
    $injector = Injector::getOverrideInstance('hal-app', $module);
}
```

### Mock

```php
public function testBindMock(): void
{ 
    $mock = $this->createMock(FooInterface::class);
    // expect that update() will be called once and the parameter will be the string 'something'.
    mock->expects($this->once())
             ->method('update')
             ->with($this->equalTo('something'));
    $module = new class($mock) extends AbstractModule {
        public function __constcuct(
            private FooInterface $foo
        ){}
        protected function configure(): void
        {
            $this->bind(FooInterface::class)->toInstance($this->foo);
        }
    };
    $injector = Injector::getOverrideInstance('hal-app', $module);
}
```
### spy

Installs a `SpyModule` by specifying the interface or class name of the spy target. [^spy-module] After running the SUT containing the spy target, verify the number of calls and the value of the calls in the spy log.

[^spy-module]: [ray/test-double](https://github.com/ray-di/Ray.TestDouble) must be installed to use SpyModule.

```php
public function testBindSpy(): void
{
    $module = new class extends AbstractModule {
        protected function configure(): void
        {
            $this->install(new SpyModule([FooInterface::class]));
        }
    };
    $injector = Injector::getOverrideInstance('hal-app', $module);
    $resource = $injector->getInstance(ResourceInterface::class);
    // Spy logs of FooInterface objects are logged, whether directly or indirectly.
    $resource->get('/');
    // Spyログの取り出し
    $spyLog = $injector->getInstance(\Ray\TestDouble\LoggerInterface::class);
    // @var array<int, Log> $addLog
    $addLog = $spyLog->getLogs(FooInterface, 'add');   
    $this->assertSame(1, count($addLog), 'Should have received once');
    // Argument validation from SUT
    $this->assertSame([1, 2], $addLog[0]->arguments);
    $this->assertSame(1, $addLog[0]->namedArguments['a']);
}
```

### Dummy

Use [Null Binding](https://ray-di.github.io/manuals/1.0/ja/null_object_binding.html) to bind a null object to an interface.

## Hypermedia Test

Resource testing is an input/output test for each endpoint. Hypermedia tests, on the other hand, test the workflow behavior of how the endpoints are connected.

Workflow tests are inherited from HTTP tests and are tested at both the PHP and HTTP levels in a single code. HTTP testing is done with `curl` and the request/response is logged in a log file.

## Best Practice

 * Test the interface, not the implementation.
 * Create a actual fake class rather than using a mock library.
 * Testing is a specification. Ease of reading rather than ease of coding.

Reference

* [Stop mocking, start testing](https://nedbatchelder.com/blog/201206/tldw_stop_mocking_start_testing.html)
* [Mockists Are Dead](https://www.thoughtworks.com/insights/blog/mockists-are-dead-long-live-classicists)
