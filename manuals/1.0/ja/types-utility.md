---
layout: docs-ja
title: PHPDoc ユーティリティ型
category: Manual
permalink: /manuals/1.0/ja/types-utility.html
---
# PHPDoc ユーティリティ型

ユーティリティ型は、既存の型からキー、値、プロパティ、インデックス先の型などを取り出すための型演算子です。PHPDoc型全体の整理は[PHPDocタイプ](types.html)を参照してください。

このページはPsalm 5系とPHPStan 2.2系の現行ドキュメントを基準にしています。`properties-of`や`class-string-map`のようにPsalm固有のものもあるため、利用する静的解析ツールを確認して使います。

## key-of<T>

`key-of<T>`は配列型、array shape、定数配列などからキーの型を取り出します。

```php
final class User
{
    public const FIELDS = [
        'id' => 1,
        'name' => 2,
        'email' => 3,
    ];
}

/**
 * @param key-of<User::FIELDS> $field
 */
function selectField(string $field): void {}

selectField('name'); // OK
selectField('age');  // static analysis error
```

templateと組み合わせる場合は、`T`を配列型に制限します。

```php
/**
 * @template T of array
 * @param T $data
 * @return list<key-of<T>>
 */
function keys(array $data): array
{
    return array_keys($data);
}
```

## value-of<T>

`value-of<T>`は値の型を取り出します。PHPStanではBackedEnumの値型にも使えます。

```php
enum Suit: string
{
    case Hearts = 'H';
    case Spades = 'S';
}

/**
 * @param value-of<Suit> $suit
 */
function chooseSuit(string $suit): void {}
```

配列定数と組み合わせると、値のリテラル型を得られます。

```php
/**
 * @param value-of<User::FIELDS> $fieldId
 */
function byFieldId(int $fieldId): void {}
```

## T[K]

`T[K]`は、配列型`T`のキー`K`に対応する値の型を表します。array shapeや設定配列に対して、キーと値の関係を保ったまま型付けできます。

```php
/**
 * @template T of array<string, mixed>
 * @template K of key-of<T>
 * @param T $data
 * @param K $key
 * @return T[K]
 */
function get(array $data, string $key): mixed
{
    return $data[$key];
}

$config = [
    'debug' => true,
    'name' => 'BEAR.Sunday',
];

$debug = get($config, 'debug'); // bool
$name = get($config, 'name');   // string
```

## properties-of<T>（Psalm）

`properties-of<T>`は、クラスのプロパティ名とプロパティ型をarray shapeのように扱うPsalm utility typeです。

```php
final class Profile
{
    public int $id;
    public string $name;
    protected string $token;
}

/**
 * @param Profile $profile
 * @param key-of<public-properties-of<Profile>> $property
 * @return value-of<public-properties-of<Profile>>
 */
function publicProperty(Profile $profile, string $property): mixed
{
    return $profile->$property;
}
```

Psalmには次のバリアントがあります。

| 型 | 対象 |
|----|------|
| `properties-of<T>` | すべてのプロパティ |
| `public-properties-of<T>` | publicプロパティ |
| `protected-properties-of<T>` | protectedプロパティ |
| `private-properties-of<T>` | privateプロパティ |

## class-string-map<T of Foo, T>（Psalm）

`class-string-map<T of Foo, T>`は、クラス名文字列をキー、そのクラスのインスタンスを値にする配列を表します。

```php
interface Handler {}

final class CreateHandler implements Handler {}
final class DeleteHandler implements Handler {}

/**
 * @template T of Handler
 * @param class-string-map<T, T> $handlers
 * @param class-string<T> $class
 * @return T
 */
function handler(array $handlers, string $class): Handler
{
    return $handlers[$class];
}
```

DIコンテナ、ファクトリ、レジストリの型を表すときに有用です。

## template-type と new（PHPStan）

PHPStanは`template-type`で渡されたオブジェクトからtemplate型を取得し、`new`で`class-string<T>`から生成されるオブジェクト型を表せます。

```php
/**
 * @template T of object
 * @param class-string<T> $class
 * @return new<T>
 */
function create(string $class): object
{
    return new $class();
}
```

`new<T>`はファクトリ関数やコンテナで、クラス名文字列と戻り値の関係を表すときに使います。

## 型エイリアスとの組み合わせ

複雑なutility typeは、型エイリアスにして名前を与えると読みやすくなります。

```php
/**
 * @psalm-type UserProfile = array{
 *   id: positive-int,
 *   name: non-empty-string,
 *   roles: list<non-empty-string>
 * }
 */
final class UserTypes {}

/**
 * @psalm-import-type UserProfile from UserTypes
 * @param UserProfile $profile
 * @param key-of<UserProfile> $property
 * @return value-of<UserProfile>
 */
function profileValue(array $profile, string $property): mixed
{
    return $profile[$property];
}
```

PHPStan向けには`@phpstan-type`と`@phpstan-import-type`を使います。

## 互換メモ

| 型 | Psalm | PHPStan | メモ |
|----|-------|---------|------|
| `key-of<T>` | 対応 | 対応 | 配列、array shape、定数配列で有用 |
| `value-of<T>` | 対応 | 対応 | PHPStanはBackedEnumにも対応 |
| `T[K]` | 対応 | 対応 | offset access |
| `properties-of<T>` | 対応 | 非対応 | Psalm utility type |
| `class-string-map<T of Foo, T>` | 対応 | 非対応 | Psalm utility type |
| `template-type` | 非対応 | 対応 | PHPStan utility type |
| `new<T>` | 非対応 | 対応 | PHPStan utility type |

## リファレンス

* [Psalm - Utility types](https://psalm.dev/docs/annotating_code/type_syntax/utility_types/)
* [Psalm - Array types](https://psalm.dev/docs/annotating_code/type_syntax/array_types/)
* [PHPStan - PHPDoc Types](https://phpstan.org/writing-php-code/phpdoc-types)
