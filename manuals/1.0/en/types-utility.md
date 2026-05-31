---
layout: docs-en
title: PHPDoc Utility Types
category: Manual
permalink: /manuals/1.0/en/types-utility.html
---
# PHPDoc Utility Types

Utility types are type operators that derive keys, values, property maps, or indexed value types from existing types. For the broader PHPDoc type reference, see [PHPDoc Types](types.html).

This page is based on the current Psalm 6 and PHPStan 2.2 documentation. Some types, such as `properties-of` and `class-string-map`, are Psalm-specific, so check the analyzer you use before adopting them.

## key-of<T>

`key-of<T>` extracts the key type from an array type, array shape, or constant array.

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

When combined with templates, constrain `T` to an array type.

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

`value-of<T>` extracts the value type. PHPStan also supports `value-of<BackedEnum>`.

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

With constant arrays, it extracts literal value types.

```php
/**
 * @param value-of<User::FIELDS> $fieldId
 */
function byFieldId(int $fieldId): void {}
```

## T[K]

`T[K]` represents the value type of array type `T` at key `K`. It preserves the relationship between a key and its value in array shapes and configuration arrays.

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

## properties-of<T> (Psalm)

`properties-of<T>` represents a class' properties as an array-shape-like map of property names to property types.

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

Psalm provides these variants:

| Type | Scope |
|------|-------|
| `properties-of<T>` | All properties |
| `public-properties-of<T>` | Public properties |
| `protected-properties-of<T>` | Protected properties |
| `private-properties-of<T>` | Private properties |

## class-string-map<T of Foo, T> (Psalm)

`class-string-map<T of Foo, T>` describes an array whose keys are class-name strings and whose values are instances of the corresponding classes.

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

This is useful for containers, factories, and registries.

## template-type and new (PHPStan)

PHPStan supports `template-type` to extract a template type from an object argument, and `new` to represent the object type created from a `class-string<T>`.

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

`new<T>` is useful when a factory or container returns an instance related to a class-name string.

## Combining with Type Aliases

Complex utility types are easier to read when named with type aliases.

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

For PHPStan, use `@phpstan-type` and `@phpstan-import-type`.

## Compatibility Notes

| Type | Psalm | PHPStan | Notes |
|------|-------|---------|-------|
| `key-of<T>` | Supported | Supported | Useful with arrays, array shapes, and constant arrays |
| `value-of<T>` | Supported | Supported | PHPStan also supports backed enums |
| `T[K]` | Supported | Supported | Offset access |
| `properties-of<T>` | Supported | Not supported | Psalm utility type |
| `class-string-map<T of Foo, T>` | Supported | Not supported | Psalm utility type |
| `template-type` | Not supported | Supported | PHPStan utility type |
| `new<T>` | Not supported | Supported | PHPStan utility type |

## References

* [Psalm - Utility types](https://psalm.dev/docs/annotating_code/type_syntax/utility_types/)
* [Psalm - Array types](https://psalm.dev/docs/annotating_code/type_syntax/array_types/)
* [PHPStan - PHPDoc Types](https://phpstan.org/writing-php-code/phpdoc-types)
