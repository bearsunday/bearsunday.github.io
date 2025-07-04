---
layout: docs-ja
title: PHPDoc ユーティリティ型
category: Manual
permalink: /manuals/1.0/ja/types-utility.html
---
# PHPDoc ユーティリティ型

ユーティリティ型は、既存の型を操作したり、動的に新しい型を生成するために使用される型です。これらの型を使用することで、より柔軟で表現力豊かな型定義が可能になります。

## 目次

1. [key-of<T>](#key-oft)
2. [value-of<T>](#value-oft)
3. [properties-of<T>](#properties-oft)
4. [class-string-map<T of Foo, T>](#class-string-mapt-of-foo-t)
5. [T[K]](#tk)
6. [Type aliases](#type-aliases)
7. [Variable templates](#variable-templates)

## key-of<T>

`key-of<T>` は、型 `T` のすべての可能なキーの型を表します。

```php
/**
 * @template T of array
 * @param T $data
 * @param key-of<T> $key
 * @return mixed
 */
function getValueByKey(array $data, $key) {
    return $data[$key];
}

// 使用例
$userData = ['id' => 1, 'name' => 'John'];
$name = getValueByKey($userData, 'name'); // OK
$age = getValueByKey($userData, 'age'); // Psalmは警告を出します
```

## value-of<T>

`value-of<T>` は、型 `T` のすべての可能な値の型を表します。

```php
/**
 * @template T of array
 * @param T $data
 * @return value-of<T>
 */
function getRandomValue(array $data) {
    return $data[array_rand($data)];
}

// 使用例
$numbers = [1, 2, 3, 4, 5];
$randomNumber = getRandomValue($numbers); // int型
```

## properties-of<T>

`properties-of<T>` は、型 `T` のすべてのプロパティの型を表します。

```php
class User {
    public int $id;
    public string $name;
    public ?string $email;
}

/**
 * @param User $user
 * @param key-of<properties-of<User>> $property
 * @return value-of<properties-of<User>>
 */
function getUserProperty(User $user, string $property) {
    return $user->$property;
}

// 使用例
$user = new User();
$name = getUserProperty($user, 'name'); // string型
$id = getUserProperty($user, 'id'); // int型
$unknown = getUserProperty($user, 'unknown'); // Psalmは警告を出します
```

## class-string-map<T of Foo, T>

`class-string-map` は、クラス名をキーとし、そのインスタンスを値とする配列を表します。

```php
interface Repository {}
class UserRepository implements Repository {}
class ProductRepository implements Repository {}

/**
 * @template T of Repository
 * @param class-string-map<T, T> $repositories
 * @param class-string<T> $className
 * @return T
 */
function getRepository(array $repositories, string $className): Repository {
    return $repositories[$className];
}

// 使用例
$repositories = [
    UserRepository::class => new UserRepository(),
    ProductRepository::class => new ProductRepository(),
];

$userRepo = getRepository($repositories, UserRepository::class);
```

## T[K]

`T[K]` は、型 `T` のインデックス `K` の要素を表します。

```php
/**
 * @template T of array
 * @template K of array-key
 * @param T $data
 * @param K $key
 * @return T[K]
 */
function getArrayElement(array $data, $key) {
    return $data[$key];
}

// 使用例
$config = ['debug' => true, 'version' => '1.0.0'];
$debugMode = getArrayElement($config, 'debug'); // bool型
```
