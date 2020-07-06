---
layout: docs-en
title: Type
category: Manual
permalink: /manuals/1.0/en/types.html
---

# Type

Use **PHPDoc types** for richer types that are not supported by native PHP.

E.g.: Assign an associative array of the resource class `body` as an "object-like array".

```php
/** @var array{greeting: string} */
public $body;
```

```php
/** @var list<array{name: string, age:int}> */
public $body;
```

The tool understands the type of an object retrieved by the resource client with `assert()`.

```php
$user = $this->resource->get('/user', []);
assert($user instanceof User);
$name = $user->body['name']; // The name key will be interpolated
$user->body['__invalid__']; // Accessing an undefined key is an error.
```

# Reference

## Atomic Type

If a type cannot be split any more, it is called an atomic type; all PHP7 types are of this type.
Union and intersection types use a combination of atomic types.

### Scalar Type

```php
/** @param int $i */
/** @param float $f */
/** @param string $str */
/** @param class-string $class */
/** @param class-string<AbstractFoo> $fooClass */
/** @param callable-string $callable */
/** @param numeric-string $num */ 
/** @param bool $isSet */
/** @param array-key $key */
/** @param numeric $num */
/** @param scalar $a */
```

### Object Type

#### Generic Object

```php
/** @return ArrayObject<int, string> */
```

#### Generator

```php
/** @return Generator<int, string, mixed, void> */
```

### Callable Type

```php
/** @return callable(Type1, OptionalType2=, SpreadType3...): ReturnType */
/** @return Closure(bool):int */
```

### Value Type

```php
/** @return null */
/** @return true */
/** @return 42 */
/** Foo\Bar::MY_SCALAR_CONST $a */
/** @param A::class|B::class $s */
```

### Array Type

#### Generic Array

```php
/** @return array<TKey, TValue> */
/** @return array<int, Foo> */
/** @return array<string, int|string> */

```

#### Object-like Arrays

```php
/** @return array{0: string, 1: string, foo: stdClass, 28: false} */
/** @return array{foo: string, bar: int} */
/** @return array{optional?: string, bar: int} */
```

#### List

```php
/** @param list<string> $stringList */
```

#### PHPDoc Array

```php
/** @param string[] $strings */
```

### Other Atomic Type


```php
/** @return iterable */
/** @return void */
/** @return empty */
/** @return mixed */
```

## Intersection Type

```php
/** @return A&B */
```

## Union Type

```php
/** @return int|false */
/** @return 0|1 */
/** @return 'a'|'b' */
```

----

See more..

* psalm - [atomic_types](https://psalm.dev/docs/annotating_code/type_syntax/atomic_types/) \| [union](https://psalm.dev/docs/annotating_code/type_syntax/union_types/) \| [intersection](https://psalm.dev/docs/annotating_code/type_syntax/intersection_types/)
* phpstan - [phpdoc type](https://phpstan.org/writing-php-code/phpdoc-types)

----

PHPStorm Plugin

* [deep-array-assoc](https://plugins.jetbrains.com/plugin/9927-deep-assoc-completion)
* [phpstan--psalm--generics](https://plugins.jetbrains.com/plugin/12754-phpstan--psalm--generics)