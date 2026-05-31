---
layout: docs-en
title: PHPDoc Types
category: Manual
permalink: /manuals/1.0/en/types.html
---
# PHPDoc Types

PHPDoc types let static analysis tools understand array structures, generics, string constraints, type guards, and security-related data flow that cannot be fully expressed with native PHP type declarations.

This page was checked on 2026-05-30 against the Psalm 5 documentation and PHPStan 2.2 documentation. PHPDoc, Psalm, and PHPStan do not support exactly the same syntax, so this page separates broadly portable types from Psalm-specific and PHPStan-specific features.

## How to Read This Page

| Area | Use |
|------|-----|
| Common PHPDoc | Basic types understood by IDEs, phpDocumentor, Psalm, and PHPStan |
| Psalm/PHPStan common | Advanced types that are practical in both major analyzers |
| Psalm-specific | `@psalm-*` tags, Psalm utility types, taint analysis |
| PHPStan-specific | `@phpstan-*` tags, PHPStan type aliases, PHPStan 2.x additions |

## Basic Types

### Scalar and Special Types

```php
/** @param int $id */
/** @param positive-int $count */
/** @param non-negative-int $offset */
/** @param non-zero-int $delta */
/** @param float $amount */
/** @param string $name */
/** @param non-empty-string $label */
/** @param bool $enabled */
/** @return null */
/** @return true */
/** @return false */
/** @return mixed */
/** @return never */
/** @return void */
```

`mixed` explicitly allows any type. PHPStan distinguishes implicit `mixed` from explicitly documented `mixed`.

`never` describes a function that never returns normally, for example because it always throws or exits. PHPStan also treats `noreturn`, `never-return`, `never-returns`, and `no-return` as equivalent names.

### Integer Ranges and Masks

```php
/** @param positive-int $id */
/** @param negative-int $delta */
/** @param non-positive-int $max */
/** @param non-negative-int $offset */
/** @param int-mask<1, 2, 4> $flags */
/** @param int-mask-of<Foo::INT_*> $flags */
```

Psalm uses `int-range<0, 100>` for integer ranges.

```php
/** @param int-range<0, 100> $percentage */
```

PHPStan uses `int<0, 100>`, `int<min, 100>`, and `int<50, max>`.

```php
/** @param int<0, 100> $percentage */
```

### String Types

```php
/** @param numeric-string $number */
/** @param lowercase-string $lower */
/** @param non-empty-lowercase-string $lower */
/** @param literal-string $sql */
/** @param callable-string $callable */
/** @param class-string $class */
/** @param class-string<User> $userClass */
/** @param trait-string $trait */
/** @param enum-string<Suit> $enum */
```

PHPStan also supports `non-falsy-string` / `truthy-string`, `non-empty-uppercase-string`, `non-empty-literal-string`, and `interface-string<T>`. PHPStan 2.2 adds `decimal-int-string` and `non-decimal-int-string` for safer string array keys. PHP converts string keys like `'123'` to integer keys, so strict `array<string, mixed>` APIs may need the safer type.

```php
/** @param non-empty-uppercase-string $upper */
/** @param non-falsy-string $truthy */
/** @param non-empty-literal-string $fragment */
/** @param interface-string<ServiceInterface> $service */
/** @param decimal-int-string $key */
/** @param non-decimal-int-string $safeStringKey */
```

### Object Types

```php
/** @param object $object */
/** @param \DateTimeInterface $date */
/** @return static */
/** @return $this */
/** @return object{foo: int, bar?: string} */
/** @return object{foo: int, bar?: string}&\stdClass */
```

`object{...}` is an object shape. In PHPStan, object shape properties are read-only; intersecting with a class such as `\stdClass` can express writable properties.

## Arrays and Lists

### Generic Arrays

```php
/** @return array<string, int> */
/** @return array<int, User> */
/** @return non-empty-array<string, User> */
/** @return iterable<int, User> */
```

`Type[]` is an older shorthand equivalent to `array<array-key, Type>`. Prefer `array<TKey, TValue>` in new code when the key type matters.

```php
/** @param User[] $users */
/** @param array<int, User> $users */
```

PHPStan also supports `associative-array` as a basic type.

```php
/** @return associative-array */
```

### Lists

`list<T>` is an array with continuous integer keys starting at zero.

```php
/** @param list<string> $names */
/** @return non-empty-list<User> */
```

Psalm 5 and PHPStan also support list shapes.

```php
/** @return list{string, int} */
```

PHPStan can also combine non-empty list syntax with shape syntax, for example `non-empty-list{string, int}`.

### Array Shapes

Array shapes describe different value types for known keys.

```php
/**
 * @return array{
 *   id: positive-int,
 *   name: non-empty-string,
 *   email?: non-empty-string,
 *   roles: list<non-empty-string>
 * }
 */
function userProfile(): array;
```

Psalm 5 and PHPStan 2.2 can describe unsealed/open shapes that allow extra keys.

```php
/** @param array{verbose: bool, ...} $options */
/** @param array{foo: int, ...<string, int>} $data */
/** @return list{string, int, ...<bool>} */
```

PHPStan 2.2 makes sealed array shape behavior stricter. If extra keys are intended, document that intent with an unsealed shape.

## Composite Types

### Union, Intersection, Parentheses

```php
/** @param int|string $id */
/** @return User|null */
/** @param Countable&Traversable $collection */
/** @param (A&B)|C $value */
```

### Literal and Constant Types

```php
/** @return 'success'|'error'|'pending' */
/** @return 200|400|500 */
/** @param Foo::STATUS_* $status */
/** @param Foo::* $constant */
```

## Callables

```php
/** @param callable(int, string): bool $callback */
/** @param callable(string &$value): void $normalizer */
/** @param callable(float ...$values): int|null $aggregate */
/** @param Closure(User): string $formatter */
/** @param pure-callable(User): string $formatter */
/** @param pure-Closure(User): string $formatter */
```

PHPStan supports `@param-closure-this` to specify `$this` inside a closure.

```php
/**
 * @param Closure(): void $callback
 * @param-closure-this User $callback
 */
function withUser(Closure $callback): void;
```

## Generics

### Function Templates

```php
/**
 * @template T
 * @param list<T> $items
 * @param callable(T): bool $predicate
 * @return list<T>
 */
function filter(array $items, callable $predicate): array;
```

### Class Templates

```php
/**
 * @template TKey of array-key
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 */
final class Collection implements IteratorAggregate
{
    /** @var array<TKey, TValue> */
    private array $items = [];

    /** @return Traversable<TKey, TValue> */
    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}
```

Psalm and PHPStan support `@template-covariant` and `@template-contravariant`. PHPStan also supports call-site variance such as `Collection<covariant Animal>` and `Collection<contravariant Dog>`, plus star projection such as `Collection<*>`.

## Conditional Types

```php
/**
 * @template T of int|array<int>
 * @param T $id
 * @return (T is int ? User : list<User>)
 */
function fetch(int|array $id): User|array;
```

PHPStan can also express negated conditions.

```php
/**
 * @return ($value is not null ? string : never)
 */
function stringify(mixed $value): string;
```

## Type Operators

### key-of / value-of

```php
/**
 * @param key-of<User::FIELDS> $field
 * @return value-of<User::FIELDS>
 */
function fieldValue(string $field): string|int;
```

In PHPStan, `value-of<BackedEnum>` can extract a backed enum's value type.

```php
/** @param value-of<Suit> $suit */
function selectSuit(string $suit): void;
```

### Offset Access

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
```

### Psalm Utility Types

Psalm provides utility types such as `properties-of<T>`, `public-properties-of<T>`, `protected-properties-of<T>`, `private-properties-of<T>`, `class-string-map<T of Foo, T>`, and variable templates. See [PHPDoc Utility Types](types-utility.html) for details.

```php
/**
 * @template T of object
 * @param class-string-map<T, T> $instances
 * @param class-string<T> $class
 * @return T
 */
function instance(array $instances, string $class): object
{
    return $instances[$class];
}
```

### PHPStan Utility Types

PHPStan supports `template-type` to extract a template type from an object argument, and `new` to create an object type from a `class-string<T>`.

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

## Type Aliases

### Psalm Type Aliases

```php
/**
 * @psalm-type UserId = positive-int
 * @psalm-type UserData = array{id: UserId, name: non-empty-string}
 */
final class UserTypes {}

/**
 * @psalm-import-type UserData from UserTypes
 * @param UserData $user
 */
function saveUser(array $user): void {}
```

### PHPStan Type Aliases

```php
/**
 * @phpstan-type UserId positive-int
 * @phpstan-type UserData array{id: UserId, name: non-empty-string}
 */
final class UserTypes {}

/**
 * @phpstan-import-type UserData from UserTypes
 * @param UserData $user
 */
function saveUser(array $user): void {}
```

PHPStan also supports global type aliases in `phpstan.neon`.

```neon
parameters:
    typeAliases:
        UserId: positive-int
        UserData: 'array{id: UserId, name: non-empty-string}'
```

## Assert and Type Guards

### Psalm Assertions

```php
/**
 * @psalm-assert non-empty-string $value
 */
function assertNonEmptyString(mixed $value): void
{
    if (! is_string($value) || $value === '') {
        throw new InvalidArgumentException();
    }
}

/**
 * @psalm-assert-if-true User $value
 */
function isUser(mixed $value): bool
{
    return $value instanceof User;
}
```

Psalm also supports `@psalm-if-this-is` and `@psalm-this-out` for changing the inferred type of `$this`.

### PHPStan Assertions

```php
/**
 * @phpstan-assert non-empty-string $value
 */
function assertNonEmptyString(mixed $value): void;

/**
 * @phpstan-assert-if-true User $value
 */
function isUser(mixed $value): bool;
```

PHPStan supports `@phpstan-self-out` and `@phpstan-this-out` for changing the current object's type after a method call.

```php
/**
 * @template TValue
 */
final class Bag
{
    /**
     * @template TItem
     * @param TItem $item
     * @phpstan-self-out self<TValue|TItem>
     */
    public function add(mixed $item): void {}
}
```

## By-Reference Output Types

Psalm and PHPStan support `@param-out` to describe the type of a by-reference parameter after a function call. Psalm also supports `@psalm-param-out`.

```php
/**
 * @param-out non-empty-string $value
 */
function fillDefault(?string &$value): void
{
    $value ??= 'default';
}
```

## Immutability and Side Effects

Psalm can describe purity, mutation, and immutability.

```php
/**
 * @psalm-immutable
 */
final class Point
{
    public function __construct(
        public float $x,
        public float $y
    ) {}

    /** @psalm-mutation-free */
    public function move(float $x, float $y): self
    {
        return new self($this->x + $x, $this->y + $y);
    }
}
```

Common Psalm tags:

| Tag | Meaning |
|-----|---------|
| `@psalm-pure` | Pure function depending only on its input |
| `@psalm-impure` | Function with side effects |
| `@psalm-mutation-free` | Method that mutates neither itself nor external state |
| `@psalm-external-mutation-free` | Method that does not mutate external state |
| `@psalm-immutable` | Immutable class |
| `@psalm-readonly` / `@readonly` | Read-only property |
| `@psalm-allow-private-mutation` | Property can be mutated only in private context |

PHPStan supports `@phpstan-pure` and `@phpstan-impure`. For readonly properties that can be changed privately, use `@phpstan-allow-private-mutation` or `@phpstan-readonly-allow-private-mutation`.

## Security Analysis

Psalm taint analysis can express untrusted input sources, dangerous sinks, escaping, and flow.

```php
/**
 * @psalm-taint-source input
 */
function userInput(): string
{
    return $_GET['q'] ?? '';
}

/**
 * @psalm-taint-sink sql $query
 */
function query(string $query): void {}

/**
 * @psalm-taint-escape sql
 */
function escapeSql(string $value): string
{
    return addslashes($value);
}
```

Main taint tags:

| Tag | Use |
|-----|-----|
| `@psalm-taint-source <type>` | Untrusted input source |
| `@psalm-taint-sink <type> <param>` | Dangerous sink |
| `@psalm-taint-escape <type>` | Escaping or sanitizing operation |
| `@psalm-taint-unescape <type>` | Mark escaped data as tainted again |
| `@psalm-taint-specialize` | Specialize taint in functions or classes |
| `@psalm-flow (...) -> return` | Explicit taint flow |

## Debugging and Checking Tags

```php
/** @psalm-trace $value */
$value = userInput();

/** @psalm-check-type $value = non-empty-string */
$value = 'BEAR';

/** @psalm-check-type-exact $value = 'BEAR' */
$value = 'BEAR';
```

These tags are development aids for checking Psalm's inferred types.

## BEAR.Sunday Recommendations

1. Use native PHP type declarations when they can express the type.
2. Use PHPDoc for element types, array shapes, lists, generics, and type aliases.
3. Prefer syntax that both Psalm and PHPStan understand for public APIs.
4. Use `@psalm-*` for Psalm-only analysis and `@phpstan-*` for PHPStan-only analysis.
5. When a shape becomes too large, consider a DTO, value object, or entity instead of a large array shape.
6. For SQL, HTML, file paths, commands, and other security boundaries, combine `literal-string`, taint annotations, and semantic types.

## Compatibility Notes

| Item | Note |
|------|------|
| `int-range` / `int<min, max>` | Psalm uses `int-range`; PHPStan uses `int<min, max>`. |
| `@psalm-type` / `@phpstan-type` | Tool-specific. Define both when both analyzers need the alias. |
| `properties-of` | Psalm utility type. Use another expression for PHPStan. |
| Unsealed shapes | Important in Psalm 5 and PHPStan 2.2. PHPStan's stricter sealed behavior is gradual. |
| `literal-string` | Useful for security-sensitive strings such as SQL and template fragments. |
| `array<string, T>` | PHP may cast decimal string keys to `int`. Consider PHPStan 2.2's `non-decimal-int-string`. |

## References

* [Psalm - Atomic types](https://psalm.dev/docs/annotating_code/type_syntax/atomic_types/)
* [Psalm - Array types](https://psalm.dev/docs/annotating_code/type_syntax/array_types/)
* [Psalm - Utility types](https://psalm.dev/docs/annotating_code/type_syntax/utility_types/)
* [Psalm - Supported annotations](https://psalm.dev/docs/annotating_code/supported_annotations/)
* [Psalm - Taint annotations](https://psalm.dev/docs/security_analysis/annotations/)
* [PHPStan - PHPDoc Types](https://phpstan.org/writing-php-code/phpdoc-types)
* [PHPStan - PHPDocs Basics](https://phpstan.org/writing-php-code/phpdocs-basics)
* [PHPStan 2.2: Unsealed Array Shapes, Safer Array Keys, and More](https://phpstan.org/blog/phpstan-2-2-unsealed-array-shapes-safer-array-keys)
