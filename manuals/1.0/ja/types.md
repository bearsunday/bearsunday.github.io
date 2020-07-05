---
layout: docs-ja
title: タイプ
category: Manual
permalink: /manuals/1.0/ja/types.html
---

# タイプ

phpのネイティブではサポートされていない型も"PHPdoc types"を記述すれば、QAツールが静的解析で検査を行い特定のエディターでは補完もされます。
また将来のPHPで採用予定の型も先に利用できます。

例）リソースクラスの`body`の連想配列をarray-shapeで型指定

```php
/** @var array{greeting: string} */
public $body;
```

```php
/** @var list<array{name: string, age:int}> */
public $body;
```

リソースクライアントで取得したオブジェクトは`assert()`するとツールが型を理解します。

```php
$user = $this->resource->get('/user', []);
assert($user instanceof User);
$name = $user->body['name']; // nameキーが補間
$user->body['__invalid__']; // 未定義キーのアクセスはエラーに
```

# リファレンス

## Scalar型

```php
/** @param scalar $a */
/** @param array-key $key */
/** @param numeric $num */
/** @param numeric-string $num */ 
/** @param class-string $class */
/** @param callable-string $callable */
```

## Object型

### Generic object

```php
/** ArrayObject<int, string> */
```

### Generators

```php
/** @return Generator<int, string, mixed, void> */
```
### 

## Array型

### Generic arrays

```php
/** @return array<TKey, TValue> */
/** @return array<string, int|string> */

```

### Object-like arrays

```php
/** @return array{0: string, 1: string, foo: stdClass, 28: false} */
/** @return array{foo: string, bar: int} */
/** @return array{optional?: string, bar: int} */
```

### Lists

```php
/** @param list<string> $arr */
```

## Callable型

```php
/** @return callable(Type1, OptionalType2=, SpreadType3...): ReturnType */
/** @return Closure(bool):int */
```

## 交差型

```php
/** @return A&B */
```
## ユニオン型

```php
/** @return int|bool*/
/** @return 0|1 */
/** @return 'a'|'b' */
```

詳しくはQAツールのマニュアルをご覧ください。

* [psalm - atomic_types](https://psalm.dev/docs/annotating_code/type_syntax/atomic_types/)
* [phpstan - phpdoc type](https://phpstan.org/writing-php-code/phpdoc-types)
