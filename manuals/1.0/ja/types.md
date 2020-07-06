---
layout: docs-ja
title: タイプ
category: Manual
permalink: /manuals/1.0/ja/types.html
---

# タイプ

phpのネイティブではサポートされていない型も**PHPdoc type**を記述すれば、静的解析ツールが検査を行い対応するエディターでは補完もされます。
また将来のPHPで採用予定の型も先に利用できます。

例）リソースクラスの`body`の連想配列を"Object-like array"で指定

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

## アトミック型

これ以上分割できない型をアトミック型と呼び、PHP7の型は全てこの型です。ユニオン型や交差型ではアトミック型を組み合わせて利用します。

### スカラー型

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

### オブジェクト型

#### ジェネリックオブジェクト

```php
/** @return ArrayObject<int, string> */
```

#### ジェネレーター

```php
/** @return Generator<int, string, mixed, void> */
```

### Callable型

```php
/** @return callable(Type1, OptionalType2=, SpreadType3...): ReturnType */
/** @return Closure(bool):int */
```

### 値型

```php
/** @return null */
/** @return true */
/** @return 42 */
/** Foo\Bar::MY_SCALAR_CONST $a */
/** @param A::class|B::class $s */
```

### 配列型

#### ジェネリック配列

```php
/** @return array<TKey, TValue> */
/** @return array<int, Foo> */
/** @return array<string, int|string> */

```

#### オブジェクト風配列 (Object-like arrays)

```php
/** @return array{0: string, 1: string, foo: stdClass, 28: false} */
/** @return array{foo: string, bar: int} */
/** @return array{optional?: string, bar: int} */
```

#### リスト

```php
/** @param list<string> $stringList */
```

#### PHPDoc配列

```php
/** @param string[] $strings */
```

レガシーシンタックスのPHPDoc配列表記は`array<array-key, ValueType>`とジェネリック配列型として扱われます。

### その他のアトミック型


```php
/** @return iterable */
/** @return void */
/** @return empty */
/** @return mixed */
```

## 交差型

```php
/** @return A&B */
```

## ユニオン型

```php
/** @return int|false */
/** @return 0|1 */
/** @return 'a'|'b' */
```

----

代表的な型を列挙しました。ここにリストした全てのPHPDoc Typesはpsam/phpstan双方の静的解析ツールでサポートされます。その他詳細はこちらをご覧ください。

* psalm - [atomic_types](https://psalm.dev/docs/annotating_code/type_syntax/atomic_types/) \| [union](https://psalm.dev/docs/annotating_code/type_syntax/union_types/) \| [intersection](https://psalm.dev/docs/annotating_code/type_syntax/intersection_types/)
* phpstan - [phpdoc type](https://phpstan.org/writing-php-code/phpdoc-types)

----

関連 PHPStormプラグイン

* [deep-array-assoc](https://plugins.jetbrains.com/plugin/9927-deep-assoc-completion)
* [phpstan--psalm--generics](https://plugins.jetbrains.com/plugin/12754-phpstan--psalm--generics)