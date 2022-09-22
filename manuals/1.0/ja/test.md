---
layout: docs-ja
title: テスト
category: Manual
permalink: /manuals/1.0/ja/test.html
---

# テスト

全ての依存がインジェクトされ、横断的関心事がAOPで提供されるBEAR.Sundayのクリーンなアプリケーションはテストフレンドリーです。

## テスト実行
composerコマンドが用意されています。

```
composer test     // phpunitテスト
composer coverge  // testカバレッジ
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
    
    protected function setUp()
    {
        $injector = Injector::getInstance('test-html-app');
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testOnPost()
    {
        $page = $this->resource->post('page://self/todo', ['title' => 'test']);
        $this->assertSame(StatusCode::CREATED, $page->code);
    }
}
```

## テスト用の束縛

テスト用に束縛を変更する方法は２つあります。全テストの束縛を横断的に変更する方法と、１テストの中だけで特定目的だけで束縛を変える方法です。前者は`TestModule`を用意して束縛します。


TestModule 例
`DateTimeInterface`でインジェクトされる現在時刻が全てUNIXエポックになります。

```php
$this->bind(DateTimeInterface::class)->toInstance(new DateTimeImmutable('1970-01-01 00:00:00'));
$this->bind(Auth::class)->to(FakeAuth::class);
```

１つのテストのための一時的な束縛の変更は`Injector::getOverrideInstance`で上書きする束縛を指定します。

```php
public function testBindFake(): void
{
    $module = new class extends AbstractModule {
        protected function configure()
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
protected function configure()
        {
            $this->bind(FooInterface::class)->toInstance($this->foo);
        }
    };
    $injector = Injector::getOverrideInstance('hal-app', $module);
}
```

## ハイパーメディアテスト

リソースの入出力だけなく、ハイパーメディアのテストを記述してワークフローをテストコードという形で表現することができます。ワークフローテストはHTTPテストで継承され１つのコードでPHPとHTTP双方のレベルで実行されます。PHPのテストは`curl`で行われ、その時、リクエスト・レスポンスがログが記録されます。

## テストダブル

[テストダブル](https://ja.wikipedia.org/wiki/%E3%83%86%E3%82%B9%E3%83%88%E3%83%80%E3%83%96%E3%83%AB) (Test Double) とは、ソフトウェアテストでテスト対象が依存しているコンポーネントを置き換える代用品のことです。テストダブルは以下のパターンがあります。

 * スタブ (テスト対象にダミーデータを与える)
 * モック (下位モジュールを正しく利用しているかを実際のモジュールを用いないで検証)
 * フェイク (実際のオブジェクトに近い働きをするがより単純な実装を使う)
 * スパイ (実際のオブジェクトに対する入出力の記録を検証)

全ての依存がインジェクトされるBEAR.SundayではDIでテストダブルを実現することは容易です。

## 良いテストのために

 * 実装ではなく、インターフェイスをテストする。
 * フェイククラスを好む。スタブはOK。モックには批判的な意見もあり利用には注意する。

参考URL

 * [Stop mocking, start testing]()
 * [Why is it so bad to mock classes?](https://stackoverflow.com/questions/1595166/why-is-it-so-bad-to-mock-classes)
 * [Why is mocking/stubbing dangerous?](https://www.thoughtworks.com/insights/blog/mockists-are-dead-long-live-classicists)
 * [All About Mocking with PHPUnit](https://code.tutsplus.com/tutorials/all-about-mocking-with-phpunit--net-27252)
