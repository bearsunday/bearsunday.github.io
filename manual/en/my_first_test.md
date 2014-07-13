---
layout: default
title: BEAR.Sunday | My First Test
category: My First - Tutorial
--- 

# My First Test

## Resource Test 

Let's test the greeting resource that we made in [my_first_resource My First Resource].

## Test File Arrangement 

Let's create the test file structure. In relation to the greeting file it will look like the following.

| type          | file path |
|---------------|----------------------------------------------------------------|
| Resource File | apps/Demo.Sandbox/src/Sandbox/Resource/App/First/GreetingTest.php   |
| Test File   　| apps/Demo.Sandbox/tests/Sandbox/Resource/App/First/GreetingTest.php |

## Creating The Test Class File 

We will save the class as `apps/Demo.Sandbox/tests/Resource/App/First/GreetingTest.php`.

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\tests\Resource\App\First;

class GreetingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Resource client
     *
     * @var \BEAR\Resource\Resource
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();
        $this->resource = clone $GLOBALS['RESOURCE'];
    }

    /**
     * resource
     *
     * @test
     */
    public function resource()
    {
        // resource request
        $resource = $this->resource->get->uri('app://self/first/greeting')->withQuery(
            ['name' => 'BEAR']
        )->eager->request();
        $this->assertSame(200, $resource->code);

        return $resource;
    }

    /**
     * Type ?
     *
     * @depends resource
     * @test
     */
    public function type($resource)
    {
        $this->assertInternalType('string', $resource->body);
    }

    /**
     * Renderable ?
     *
     * @depends resource
     * @test
     */
    public function render($resource)
    {
        $html = (string)$resource;
        $this->assertInternalType('string', $html);
    }

    /**
     * @depends resource
     * @test
     */
    public function body($resource)
    {
        $this->assertSame('Hello, BEAR', $resource->body);
    }
}
{% endhighlight %}

## Let's Run the Tests　

We navigate to the application directory.

```
$ cd apps/Demo.Sandbox/
```

And run the tests.

```
$ phpunit tests/Resource/App/First/GreetingTest.php
```

```
...

Time: 598 ms, Memory: 8.25Mb

OK (3 tests, 3 assertions)

Generating code coverage report in Clover XML format ... done

Generating code coverage report in HTML format ... done
```

We did it!

### Coverage Report 

In `build/coverage/index.html` we can see the scope of the tests covered.

## Let's Look At The Test Code 

### setUp()

{% highlight php startinline %}
$this->resource = clone $GLOBALS['RESOURCE'];
{% endhighlight %}

Getting resource client for testing.

### resource()

{% highlight php startinline %}
$resource = $this->resource->get->uri('app://self/first/greeting')->withQuery(
            ['name' => 'BEAR']
        )->eager->request();
{% endhighlight %}

We use the resource client inside the resource() method to access the resource.

### Other Test Methods

In other `@test` annotated methods we check the results received through the resource method.
