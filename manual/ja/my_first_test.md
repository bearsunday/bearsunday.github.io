---
layout: default_ja
title: BEAR.Sunday | はじめてのテスト
category: My First - Tutorial
--- 

# はじめてのテスト

## リソーステスト

[はじめてのリソース](my_first_resource.html) で作成した挨拶リソーステストします。

## テストファイルの配置場所

テストファイルを配置します。リソースファイルとの場所はこのようになります。

| type          | file path |
|---------------|----------------------------------------------------------------|
| リソースファイル | apps/Demo.Sandbox/src/Sandbox/Resource/App/First/GreetingTest.php   |
| テストファイル   | apps/Demo.Sandbox/tests/Sandbox/Resource/App/First/GreetingTest.php |

## テストクラスファイルを作成します

このクラスを `apps/Demo.Sandbox/tests/Resource/App/First/GreetingTest.php` として保存します。

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

## テストを実行してみましょう

アプリケーションディレクトリに移動します。

```
$ cd apps/Demo.Sandbox/
```

テスト実行します。

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

OKでました！

### カバレッジレポート

`build/coverage/index.html` にはどの範囲のコードが今のテストでカバーできたら確認することができます。

## テストコードをみてみましょう

### setUp()

{% highlight php startinline %}
$this->resource = clone $GLOBALS['RESOURCE'];
{% endhighlight %}

テスト用のリソースクライアントを取得しています。

### resource()

{% highlight php startinline %}
$resource = $this->resource->get->uri('app://self/first/greeting')->withQuery(
            ['name' => 'BEAR']
        )->eager->request();
{% endhighlight %}

resource()メソッド内ではリソースクライアントを使ってリソースにアクセスしています。

### その他のテストメソッド

その他の `@test` とアノテートされたメソッドではresource()で得られた結果をチェックしています。
