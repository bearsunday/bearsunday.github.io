---
layout: docs-ja
title: テスト
category: Manual
permalink: /manuals/1.0/ja/test.html
---

# テスト

*Working in progress*

「全てがリソース」のBEAR.Sundayではリソースクライントでリソースを操作することがテストの基本になります。

AppリソースでCRUDテストはここを参考にしてください。

 * [App/TodoTest](https://github.com/koriym/Polidog.Todo/blob/master/tests/Resource/App/TodoTest.php)

Pageリソースのテストはここを参考にしてください。

 * [Page/Index](https://github.com/koriym/Polidog.Todo/blob/master/tests/Resource/Page/IndexTest.php)

## アプリケーション・インジェクター

AppInjector(アプリケーションインジェクター)はアプリケーションで利用するすべてのクラスのインスタンスを特定のコンテキストを指定して生成することができ、リソースオブジェクトやその依存を直接テストすることができます。

```php?start_inline
$injector = new AppInjector('MyVendor\MyProject', 'test-app'));

// resource client
$resource = $injector->getInstance(ResourceInterface::class);
$index = $resource->uri('page://self/index')();
/* @var $index Index */
$this->assertSame(StatusCode::OK, $page->code);
$todos = $page->body['todos'];

// resource classを直接取得
$user = $injector->getInstance(User::class);
$name = $index->onGet(1)->body['name']; // BEAR

// formのバリデーション検査
$form = $injector->getInstance(TodoForm::class);
$submit = ['name' => 'BEAR'];
$isValid = $this->form->apply($submit); // true
```

## テストダブル

[テストダブル](https://ja.wikipedia.org/wiki/%E3%83%86%E3%82%B9%E3%83%88%E3%83%80%E3%83%96%E3%83%AB) (Test Double) とは、ソフトウェアテストでテスト対象が依存しているコンポーネントを置き換える代用品のことです。以下のテストダブルのパターンをサポートします。

 * スタブ (テスト対象に「間接的な入力」を提供するために使う)
 * モック (テスト対象からの「間接的な出力」を検証するために使う)
 * フェイク (実際のオブジェクトに近い働きをするがより単純な実装を使う)
 * スパイ (入出力の記録しておいて、テスト実行後に値を取り出して検証できる)

サービスロケーターを使わず全ての依存がインジェクトされるBEAR.SundayではDIでテストダブルを実現することは容易ですが、テストダブルフレームワーク[Ray.TestDouble](https://github.com/ray-di/Ray.TestDouble)を使うとさらに便利になり**スパイ**もできるようになります。

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

代用のクラスには`Fake`プリフェックスをつけて`tests/fake-src`フォルダに保存します。元クラスを`extend`して入れ替えるクラスだけを実装します。

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
Fakeクラスに`@JsonSchema`をアノテートして検証することができます。

# スパイ

**スパイ**するクラスに`@Spy`とアノテートします。

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

`Spy`クラスから(クラス名,メソッド名)で指定するとメソッドの引数や実行結果が保存されている`SpyLog`値オブジェクトが取得でき、`@Spy`とアノテートしたクラスの入出力や呼び出し回数をテストすることができます。

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

テストダブルもスパイできます。実クラスの呼び出しを検査するスパイと違いテストダブルの呼び出しを検査する[モック](https://ja.wikipedia.org/wiki/%E3%83%A2%E3%83%83%E3%82%AF%E3%82%AA%E3%83%96%E3%82%B8%E3%82%A7%E3%82%AF%E3%83%88)です。

```php?start_inline
/**
 * @Spy
 */
class FakeFoo extend Foo
{
    public function getDate() {
        return '20170801';
    }
}
```
