---
layout: docs-ja
title: テスト
category: Manual
permalink: /manuals/1.0/ja/test.html
---

# テスト

適切なテストは、ソフトウェアを継続性のあるより良いものにします。 全ての依存がインジェクトされ、横断的関心事がAOPで提供されるBEAR.Sundayのクリーンなアプリケーションはテストフレンドリーです。

## テスト実行
composerコマンドが用意されています。

```
composer test     // phpunitテスト
composer tests    // test + sa + cs
composer coverge  // testカバレッジ
composer pcov     // testカバレッジ (pcov)
composer sa       // 静的解析
composer cs       // コーディングスタンダード検査
composer cs-fix   // コーディングスタンダード修復

```

## リソース	テストケース作成

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

[テストダブル](https://ja.wikipedia.org/wiki/%E3%83%86%E3%82%B9%E3%83%88%E3%83%80%E3%83%96%E3%83%AB) (Test Double) とは、ソフトウェアテストでテスト対象が依存しているコンポーネントを置き換える代用品のことです。テストダブルは以下のパターンがあります。

* スタブ (テスト対象にダミーデータを与える)
* モック (下位モジュールを正しく利用しているかを実際のモジュールを用いないで検証)
* フェイク (実際のオブジェクトに近い働きをするがより単純な実装を使う)
* スパイ (実際のオブジェクトに対する入出力の記録を検証)

### テストダブルの束縛

テスト用に束縛を変更する方法は２つあります。コンテキストモジュールで全テストの束縛を横断的に変更する方法と、１テストの中だけで一時的に特定目的だけで束縛を変える方法です。


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

テスト用束縛が上書きされたインジェクター

```php
$injector = Injector::getInstance('test-hal-app', $module);
```

#### 一時的束縛変更

１つのテストのための一時的な束縛の変更は`Injector::getOverrideInstance`で上書きする束縛を指定します。

```php
public function testBindFake(): void
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

モックの例

```php
public function testMockBInd(): void
{
  
    $mock = $this->createMock(FooInterface::class);
    $mock->method('doSomething')->willReturn('foo');
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
## ハイパーメディアテスト

リソーステストは各エンドポイントの入出力テストです。対してハイパーメディアテストはそのエンドポイントどう繋ぐかというワークフローの振る舞いをテストします。

WorkflowテストはHTTPテストに継承され、１つのコードでPHPとHTTP双方のレベルでテストされます。その際HTTPのテストは`curl`で行われ、そのリクエスト・レスポンスはログファイルに記録されます。

## 良いテストのために

 * 実装ではなく、インターフェイスをテストします。
 * モックライブラリを利用するよりフェイククラスを作成しましょう。
 * テストは仕様です。書きやすさよりも読みやすさを。

参考URL

 * [Stop mocking, start testing](https://nedbatchelder.com/blog/201206/tldw_stop_mocking_start_testing.html)
 * [Mockists Are Dead](https://www.thoughtworks.com/insights/blog/mockists-are-dead-long-live-classicists)
