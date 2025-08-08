---
layout: docs-ja
title: テスト
category: Manual
permalink: /manuals/1.0/ja/test.html
---

# テスト

適切なテストは、ソフトウェアを継続性のある、より良いものにします。全ての依存がインジェクトされ、横断的関心事がAOPで提供されるBEAR.Sundayのクリーンなアプリケーションはテストフレンドリーです。

## テスト実行

composerコマンドが用意されています。

```
composer test     // phpunitテスト
composer tests    // test + sa + cs
composer coverage // テストカバレッジ
composer pcov     // テストカバレッジ (pcov)
composer sa       // 静的解析
composer cs       // コーディングスタンダード検査
composer cs-fix   // コーディングスタンダード修復
```

## リソーステストケース作成

**全てがリソース**のBEAR.Sundayではリソース操作がテストの基本です。`Injector::getInstance`でリソースクライアントを取得してリソースの入出力テストを行います。

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

## テストダブル

[テストダブル](https://ja.wikipedia.org/wiki/%E3%83%86%E3%82%B9%E3%83%88%E3%83%80%E3%83%96%E3%83%AB) (Test Double) とは、ソフトウェアテストでテスト対象が依存しているコンポーネントを置き換える代用品のことです。テストダブルには以下のパターンがあります。

* スタブ (テスト対象に「間接的な入力」を提供)
* モック (テスト対象からの「間接的な出力」をテストダブルの内部で検証)
* スパイ (テスト対象からの「間接的な出力」を記録)
* フェイク (実際のオブジェクトに近い働きのより単純な実装)
* ダミー (テスト対象の生成に必要だが呼び出しが行われない)

テスト対象のシステム([SUT](https://ja.wikipedia.org/wiki/%E3%83%86%E3%82%B9%E3%83%88%E5%AF%BE%E8%B1%A1%E3%82%B7%E3%82%B9%E3%83%86%E3%83%A0))がテストダブルの出力を使用するのがスタブです。例えばいつも`true`を返すようなメソッドを持つテストダブルはスタブです。

モックはSUTからテストダブルへの間接的出力の検証をテストコードではなく、テストダブル内部で行います。スパイはモックと同じようにSUTの間接的出力の検証を行うためのものですが、その検証をテストコードで行うためにテストコードから読み取り可能な記録が行われます。

### テストダブルの束縛

テスト用に束縛を変更する方法は2つあります。コンテキストモジュールで全テストの束縛を横断的に変更する方法と、1テストの中だけで一時的に特定目的だけで束縛を変える方法です。

#### コンテキストモジュール

`TestModule`を作成してbootstrapで`test`コンテキストを利用可能にします。

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

テスト用束縛が上書きされたインジェクター：

```php
$injector = Injector::getInstance('test-hal-app', $module);
```

## 一時的束縛変更

1つのテストのための一時的な束縛の変更は`Injector::getOverrideInstance`で上書きする束縛を指定します。

### スタブ、フェイク

```php
public function testBindStub(): void
{
    $module = new class extends AbstractModule {
        protected function configure(): void
        {
            $this->bind(FooInterface::class)->to(FakeFoo::class);
        }
    };
    $injector = Injector::getOverrideInstance('hal-app', $module);
}
```

### モック

アサーションをテストダブル内部で実行します。

```php
public function testBindMock(): void
{
    $mock = $this->createMock(FooInterface::class);
    // update()が一度だけコールされ、その際のパラメータは文字列'something'となることを期待
    $mock->expects($this->once())
         ->method('update')
         ->with($this->equalTo('something'));
         
    $module = new class($mock) extends AbstractModule {
        public function __construct(
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

### スパイ

スパイ対象のインターフェイスまたはクラス名を指定して`SpyModule`をインストールします。[^spy-module]
スパイ対象が含まれるSUTを動作させた後に、スパイログで呼び出し回数や呼び出しの値を検証します。

[^spy-module]: SpyModuleの利用には[ray/test-double](https://github.com/ray-di/Ray.TestDouble)のインストールが必要です。

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
    
    // 直接、間接に関わらずFooInterfaceオブジェクトのSpyログが記録されます
    $resource->get('/');
    
    // Spyログの取り出し
    $spyLog = $injector->getInstance(\Ray\TestDouble\LoggerInterface::class);
    // @var array<int, Log> $addLog
    $addLog = $spyLog->getLogs(FooInterface::class, 'add');
    
    $this->assertSame(1, count($addLog), 'Should have received once');
    // SUTからの引数の検証
    $this->assertSame([1, 2], $addLog[0]->arguments);
    $this->assertSame(1, $addLog[0]->namedArguments['a']);
}
```

### ダミー

インターフェイスにNullオブジェクトを束縛するには[Null束縛](https://ray-di.github.io/manuals/1.0/ja/null_object_binding.html)を使います。

## ハイパーメディアテスト

リソーステストは各エンドポイントの入出力テストです。対してハイパーメディアテストはそのエンドポイントをどう繋ぐかというワークフローの振る舞いをテストします。

Workflowテストは HTTPテストに継承され、1つのコードでPHPとHTTP双方のレベルでテストされます。その際HTTPのテストは`curl`で行われ、そのリクエスト・レスポンスはログファイルに記録されます。

## 良いテストのために

* 実装ではなく、インターフェイスをテストします。
* モックライブラリを利用するよりフェイククラスを作成しましょう。
* テストは仕様です。書きやすさよりも読みやすさを重視しましょう。

参考URL:
* [Stop mocking, start testing](https://nedbatchelder.com/blog/201206/tldw_stop_mocking_start_testing.html)
* [Mockists Are Dead](https://www.thoughtworks.com/insights/blog/mockists-are-dead-long-live-classicists)
