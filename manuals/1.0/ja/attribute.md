---
layout: docs-ja
title: Attribute
category: Manual
permalink: /manuals/1.0/ja/attribute.html
---
# アトリビュート

BEAR.SundayはBEAR.Package`^1.10.3`から従来のアノテーションに加えて、PHP8の[アトリビュート](https://www.php.net/manual/ja/language.attributes.overview.php)をサポートします。

**アノテーション**
```php?start_inline
/**
 * @Inject
 * @Named('admin')
 */
public function setLogger(LoggerInterface $logger)
```
**アトリビュート**
```php?start_inline
#[Inject, Named('admin')]
public function setLogger(LoggerInterface $logger)
```

```php?start_inline
#[Embed(rel: 'weather', src: 'app://self/weather{?date}')]
#[Link(rel: 'event', href: 'app://self/event{?news_date}')]
public function onGet(string $date): self
```

## 引数に適用

アノテーションはメソッドにしか適用できず引数名を名前で指定する必要があるものがありましたが、
アトリビュートでは直接引数を装飾することができます。

```php?start_inline
public __construct(#[Named('payment')] LoggerInterface $paymentLogger, #[Named('debug')] LoggerInterface $debugLogger)
```

```php?start_inline
public function doSomething($id, #[Assisted] DbInterface $db = null)
```

```php?start_inline
public function foo(#[CookieParam('id')]string $tokenId): void
```

```php?start_inline
public function onGet(#[ResourceParam(uri: 'app://self/login#nickname')] string $nickname = null): static
```
## 互換性

アトリビュートとアノテーションは１つのプロジェクトに混在する事もできます。[^1]
このマニュアルに表記されている全てのアノテーションはアトリビュートに変更しても動作します。

## パフォーマンス

最適化されるため、プロダクション用にアノテーション/アトリビュート読み込みコストがかかることはほとんどありませんが 以下のようにアトリビュートリーダーしか使用しないと宣言すると開発時の速度が向上します。

```php?start_inline
// tests/bootstap.php 

use Ray\ServiceLocator\ServiceLocator;

ServiceLocator::setReader(new AttributeReader());
```

```php?start_inline
// DevModule
 
$this->install(new AttributeModule());
```

---

[^1]:１つのメソッドで混在するときはアトリビュートが優先されます。