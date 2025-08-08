---
layout: docs-ja
title: アトリビュート
category: Manual
permalink: /manuals/1.0/ja/attribute.html
---

# アトリビュート

BEAR.SundayはBEAR.Package `^1.10.3`から従来のアノテーションに加えて、PHP8の[アトリビュート](https://www.php.net/manual/ja/language.attributes.overview.php)をサポートします。

**アノテーション**
```php
/**
 * @Inject
 * @Named('admin')
 */
public function setLogger(LoggerInterface $logger)
```

**アトリビュート**
```php
#[Inject, Named('admin')]
public function setLogger(LoggerInterface $logger)
```

```php
#[Embed(rel: 'weather', src: 'app://self/weather{?date}')]
#[Link(rel: 'event', href: 'app://self/event{?news_date}')]
public function onGet(string $date): self
```

## 引数に適用

アノテーションはメソッドにしか適用できず引数名を名前で指定する必要があるものがありましたが、PHP8では直接、引数のアトリビュートで指定することができます。

```php
public function __construct(
    #[Named('payment')] LoggerInterface $paymentLogger,
    #[Named('debug')] LoggerInterface $debugLogger
)
```

```php
public function onGet($id, #[Assisted] DbInterface $db = null)
```

```php
public function onGet(#[CookieParam('id')] string $tokenId): void
```

```php
public function onGet(#[ResourceParam(uri: 'app://self/login#nickname')] string $nickname = null): static
```

## 互換性

アトリビュートとアノテーションは1つのプロジェクトに混在することもできます。[^1]
このマニュアルに表記されている全てのアノテーションはアトリビュートに変更しても動作します。

## パフォーマンス

最適化されるため、プロダクション用にアノテーション/アトリビュート読み込みコストがかかることはほとんどありませんが、
以下のようにアトリビュートリーダーしか使用しないと宣言すると開発時の速度が向上します。

```php
// tests/bootstap.php
use Ray\ServiceLocator\ServiceLocator;
ServiceLocator::setReader(new AttributeReader());
```

```php
// DevModule
$this->install(new AttributeModule());
```

---
[^1]: 1つのメソッドで混在するときはアトリビュートが優先されます。
