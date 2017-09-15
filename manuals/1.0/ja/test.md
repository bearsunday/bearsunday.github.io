---
layout: docs-ja
title: テスト
category: Manual
permalink: /manuals/1.0/ja/test.html
---

# テスト

適切なテストを書くことはより良いソフトウェアを書くのに役立ちます。

全ての依存がインジェクトされ、横断的関心事がAOPで提供されるクリーンなBEAR.Sundayのアプリケーションはテストフレンドリーです。フレームワーク固有の密結合したベースクラスやヘルパーメソッドなしで高いカバレッジのテストを記述することができます。

## テスト実行
`vendor/bin/phpunit`または`composer test`コマンドで`phpmd`や`phpcs`と共にテストを実行します。他にもこのようなcomposerコマンドがあります。

```
composer coverge  // testカバレッジ
composer cs       // コーディングスタンダード検査
composer cs-fix   // コーディングスタンダード修復
```

## リソース	テストケース作成

**全てがリソース**のBEAR.Sundayではリソース操作がテストの基本です。

これは`Myvendor\MyProject`アプリを`html-app`コンテキストで実行して、`page://self/todo`リソースに`POST`すれば`201 (Created)`が返ってくることをテストするコードです。

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

 * AppリソースでCRUDテストは[App/TodoTest](https://github.com/koriym/Polidog.Todo/blob/master/tests/Resource/App/TodoTest.php)を参考にしてください。
 * Pageリソースのテストは[Page/Index](https://github.com/koriym/Polidog.Todo/blob/master/tests/Resource/Page/IndexTest.php)を参考にしてください。

## アプリケーション・インジェクター

AppInjector(アプリケーションインジェクター)はアプリケーションで利用するすべてのクラスのインスタンスを特定のコンテキストを指定して生成することができ、リソースオブジェクトやその依存を直接テストすることができます。

```php?start_inline
$injector = new AppInjector('MyVendor\MyProject', 'test-app'));

// リソースクライアント
$resource = $injector->getInstance(ResourceInterface::class);
$index = $resource->uri('page://self/index')();
/* @var $index Index */
$this->assertSame(StatusCode::OK, $page->code);
$todos = $page->body['todos'];

// リソースクラスを直接生成して直接コール
$user = $resource->newInstance('app://self/user');
// or
$user = $injector->getInstance(User::class);
$name = $index->onGet(1)->body['name']; // BEAR

// フォームのバリデーション検査
$form = $injector->getInstance(TodoForm::class);
$submit = ['name' => 'BEAR'];
$isValid = $this->form->apply($submit); // true
```

## テストダブル

[テストダブル](https://ja.wikipedia.org/wiki/%E3%83%86%E3%82%B9%E3%83%88%E3%83%80%E3%83%96%E3%83%AB) (Test Double) とは、ソフトウェアテストでテスト対象が依存しているコンポーネントを置き換える代用品のことです。テストダブルは以下のパターンがあります。

 * スタブ (テスト対象にダミーデータを与える)
 * モック (下位モジュールを正しく利用しているかを実際のモジュールを用いないで検証)
 * フェイク (実際のオブジェクトに近い働きをするがより単純な実装を使う)
 * スパイ (実際のオブジェクトに対する入出力の記録を検証)

全ての依存がインジェクトされるBEAR.SundayではDIでテストダブルを実現することは容易ですが、テストダブルフレームワーク[Ray.TestDouble](https://github.com/ray-di/Ray.TestDouble)を使うとさらに便利になり**スパイ**もできるようになります。

composerインストール

```
$ composer require ray/test-double 1.x-dev --dev
```

`TestModule`など作成してモジュールインストールします。

```php?start_inline
use Ray\Di\AbstractModule;
use Ray\TestDouble\TestDoubleModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TestDoubleModule);
    }
}
```

テストダブルの対象に`@Fakeable`とアノテートとします。

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

テストダブルのクラスには`Fake`プリフェックスをつけて`tests/fake-src`フォルダに保存します。元クラスを`extend`して入れ替えるクラスだけを実装します。

```php?start_inline
class FakeFoo extend Foo
{
    public function getDate() {
        return '20170801'; // 単に値を返すだけのスタブ
    }
}
```

オートロードが働くように`composer.json`に`autoload-dev`を追加します。

```json
"autoload-dev": {
    "psr-4": {
        "MyVendor\\MyProject\\": "tests/fake-src"
    }
},
```

`test`コンテキストで実行すると`Foo`の代わりに`FakeFoo`が呼ばれるようになります。

```php?start_inline
$resource = (new AppInjector('MyVendor\MyProject', 'test-app'))->getInstance(ResourceInterface::class);
```

# スパイ

入出力を記録して**スパイ**するクラスに`@Spy`とアノテートします。

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

`Spy`クラスから(クラス名,メソッド名)を指定して`getLogs()`するとメソッドの引数や実行結果が保存されている`SpyLog`値オブジェクトが取得できます。そのオブジェクトを使って`@Spy`とアノテートしたクラスの入出力や呼び出し回数をテストすることができます。

```php?start_inline
public function testSpy()
{
    $injector = (new AppInjector('MyVendor\MyProject', 'test-app'))->getInstance(InjectorInterface::class);
    $calc = $injector->getInstance(Calc::class);
    $result = $calc->add(1, 2); // 3

    // スパイログを取得
    $spy = $injector->getInstance(Spy::class);
    $logs = $spy->getLogs(Calc::class, 'add');
    $this->assertSame(1, count($logs)); // call time
    /* @var $log SpyLog */
    $log = $logs[0]; // first call log

    // メソッドがコールされた時の引数と結果を検査
    $this->assertSame([1, 2], $log->arguments);
    $this->assertSame(3, $log->result);
}
```

Fakeクラスをスパイしてテストダブルへの呼び出しを検査することもできます。

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

## 無名クラスを使った束縛

PHPの無名クラスを使って一時的に依存を束縛することができます。

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

## スタブを束縛

phpunitの`createMock()`メソッドなどのモッキングツールでスタブを作成してそのインスタンスと束縛することもできます。

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

## ベストプラクティス

 * 実装ではなく、インターフェイスをテストする
 * フェイククラスを好む。スタブはOK。モックには批判的な意見もあり複雑なものを避ける。

参考URL

 * [Stop mocking, start testing]()
 * [Why is it so bad to mock classes?](https://stackoverflow.com/questions/1595166/why-is-it-so-bad-to-mock-classes)
 * [Why is mocking/stubbing dangerous?](https://www.thoughtworks.com/insights/blog/mockists-are-dead-long-live-classicists)
 * [All About Mocking with PHPUnit](https://code.tutsplus.com/tutorials/all-about-mocking-with-phpunit--net-27252)
