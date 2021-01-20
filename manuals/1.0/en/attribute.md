---
layout: docs-en
title: Attribute
category: Manual
permalink: /manuals/1.0/en/attribute.html
---
# Attributes

BEAR.Sunday supports PHP8's [attributes](https://www.php.net/manual/en/language.attributes.overview.php) in addition to the annotations.

**Annotation**
```php?start_inline
/**
 * @Inject
 * @Named('admin')
 */
public function setLogger(LoggerInterface $logger)
```
**Attribute**
```php?start_inline
#[Inject, Named('admin')]
public function setLogger(LoggerInterface $logger)
```

```php?start_inline
#[Embed(rel: 'weather', src: 'app://self/weather{?date}')]
#[Link(rel: 'event', href: 'app://self/event{?news_date}')]
public function onGet(string $date): self
```

## Apply to parameters

While some annotations can only be applied to methods and require the argument names to be specified by name, the
Attributes can be used to decorate arguments directly.

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
## Compatibility

Attributes and annotations can be mixed in a single project. [^1]
All annotations described in this manual will work when converted to attributes.

## Performance

Although the cost of loading annotations/attributes for production is minimal due to optimization, you can speed up development by declaring that you will only use attribute readers, as follows

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

[^1]:Attributes take precedence when mixed in a single method.