---
layout: docs-en
title: Type
category: Manual
permalink: /manuals/1.0/en/types.html
---

# PHPDoc Types

PHP is a dynamically typed language, but by using static analysis tools like psalm or phpstan along with PHPDoc, we can express advanced type concepts and benefit from type checking during static analysis. This reference explains the types available in PHPDoc and other related concepts.

## Table of Contents

1. [Atomic Types](#atomic-types)
    - [Scalar Types](#scalar-types)
    - [Object Types](#object-types)
    - [Array Types](#array-types)
    - [Callable Types](#callable-types)
    - [Value Types](#value-types)
    - [Special Types](#special-types)
2. [Compound Types](#compound-types)
    - [Union Types](#union-types)
    - [Intersection Types](#intersection-types)
3. [Advanced Type System](#advanced-type-system)
    - [Generic Types](#generic-types)
    - [Template Types](#template-types)
    - [Conditional Types](#conditional-types)
    - [Type Aliases](#type-aliases)
    - [Type Constraints](#type-constraints)
    - [Covariance and Contravariance](#covariance-and-contravariance)
4. [Type Operators (Utility Types)](#type-operators)
    - [Key-of and Value-of Types](#key-of-and-value-of-types)
    - [Properties-of Type](#properties-of-type)
    - [Class Name Mapping Type](#class-name-mapping-type)
    - [Index Access Type](#index-access-type)
5. [Functional Programming Concepts](#functional-programming-concepts)
    - [Pure Functions](#pure-functions)
    - [Immutable Objects](#immutable-objects)
    - [Side Effect Annotations](#side-effect-annotations)
    - [Higher-Order Functions](#higher-order-functions)
6. [Assert Annotations](#assert-annotations)
7. [Security Annotations](#security-annotations)
8. [Example: Using Types in Design Patterns](#example-using-types-in-design-patterns)

---

## Atomic Types

These are the basic types that cannot be further divided.

### Scalar Types

```php
/** @param int $i */
/** @param float $f */
/** @param string $str */
/** @param lowercase-string $lowercaseStr */
/** @param non-empty-string $nonEmptyStr */
/** @param non-empty-lowercase-string $nonEmptyLowercaseStr */
/** @param class-string $class */
/** @param class-string<AbstractFoo> $fooClass */
/** @param callable-string $callable */
/** @param numeric-string $num */ 
/** @param bool $isSet */
/** @param array-key $key */
/** @param numeric $num */
/** @param scalar $a */
/** @param positive-int $positiveInt */
/** @param negative-int $negativeInt */
/** @param int-range<0, 100> $percentage */
/** @param int-mask<1, 2, 4> $flags */
/** @param int-mask-of<MyClass::CLASS_CONSTANT_*> $classFlags */
/** @param trait-string $trait */
/** @param enum-string $enum */
/** @param literal-string $literalStr */
/** @param literal-int $literalInt */
```

These types can be combined using [Compound Types](#compound-types) and [Advanced Type System](#advanced-type-system).

### Object Types

```php
/** @param object $obj */
/** @param stdClass $std */
/** @param Foo\Bar $fooBar */
/** @param object{foo: string, bar?: int} $objWithProperties */
/** @return ArrayObject<int, string> */
/** @param Collection<User> $users */
/** @return Generator<int, string, mixed, void> */
```

Object types can be combined with [Generic Types](#generic-types).

### Array Types

#### Generic Arrays

```php
/** @return array<TKey, TValue> */
/** @return array<int, Foo> */
/** @return array<string, int|string> */
/** @return non-empty-array<string, int> */
```

Generic arrays use the concept of [Generic Types](#generic-types).

#### Object-like Arrays

```php
/** @return array{0: string, 1: string, foo: stdClass, 28: false} */
/** @return array{foo: string, bar: int} */
/** @return array{optional?: string, bar: int} */
```

#### Lists

```php
/** @param list<string> $stringList */
/** @param non-empty-list<int> $nonEmptyIntList */
```

#### PHPDoc Arrays (Legacy Notation)

```php
/** @param string[] $strings */
/** @param int[][] $nestedInts */
```

### Callable Types

```php
/** @return callable(Type1, OptionalType2=, SpreadType3...): ReturnType */
/** @return Closure(bool):int */
/** @param callable(int): string $callback */
```

Callable types are especially important in [Higher-Order Functions](#higher-order-functions).

### Value Types

```php
/** @return null */
/** @return true */
/** @return false */
/** @return 42 */
/** @return 3.14 */
/** @return "specific string" */
/** @param Foo\Bar::MY_SCALAR_CONST $const */
/** @param A::class|B::class $classNames */
```

### Special Types

```php
/** @return void */
/** @return never */
/** @return empty */
/** @return mixed */
/** @return resource */
/** @return closed-resource */
/** @return iterable<TKey, TValue> */
```

## Compound Types

These are types created by combining multiple [Atomic Types](#atomic-types).

### Union Types

```php
/** @param int|string $id */
/** @return string|null */
/** @var array<string|int> $mixedArray */
/** @return 'success'|'error'|'pending' */
```

### Intersection Types

```php
/** @param Countable&Traversable $collection */
/** @param Renderable&Serializable $object */
```

Intersection types can be useful in implementing [Design Patterns](#example-using-types-in-design-patterns).

## Advanced Type System

These are advanced features that allow for more complex and flexible type expressions.

### Generic Types

```php
/**
 * @template T
 * @param array<T> $items
 * @param callable(T): bool $predicate
 * @return array<T>
 */
function filter(array $items, callable $predicate): array {
    return array_filter($items, $predicate);
}
```

Generic types are often used in combination with [Higher-Order Functions](#higher-order-functions).

### Template Types

```php
/**
 * @template T of object
 * @param class-string<T> $className
 * @return T
 */
function create(string $className)
{
    return new $className();
}
```

Template types can be used in combination with [Type Constraints](#type-constraints).

### Conditional Types

```php
/**
 * @template T
 * @param T $value
 * @return (T is string ? int : string)
 */
function processValue($value) {
    return is_string($value) ? strlen($value) : strval($value);
}
```

Conditional types may be used in combination with [Union Types](#union-types).

### Type Aliases

```php
/**
 * @psalm-type UserId = positive-int
 * @psalm-type UserData = array{id: UserId, name: string, email: string}
 */

/**
 * @param UserData $userData
 * @return UserId
 */
function createUser(array $userData): int {
    // User creation logic
    return $userData['id'];
}
```

Type aliases are helpful for simplifying complex type definitions.

### Type Constraints

Type constraints allow you to specify more concrete type requirements for type parameters.

```php
/**
 * @template T of \DateTimeInterface
 * @param T $date
 * @return T
 */
function cloneDate($date) {
    return clone $date;
}

// Usage example
$dateTime = new DateTime();
$clonedDateTime = cloneDate($dateTime);
```

In this example, `T` is constrained to classes that implement `\DateTimeInterface`.

### Covariance and Contravariance

When dealing with generic types, the concepts of [covariance and contravariance](https://www.php.net/manual/en/language.oop5.variance.php) become important.

```php
/**
 * @template-covariant T
 */
interface Producer {
    /** @return T */
    public function produce();
}

/**
 * @template-contravariant T
 */
interface Consumer {
    /** @param T $item */
    public function consume($item);
}

// Usage example
/** @var Producer<Dog> $dogProducer */
/** @var Consumer<Animal> $animalConsumer */
```

Covariance allows you to use a more specific type (subtype), while contravariance means you can use a more basic type (supertype).

## Type Operators

Type operators allow you to generate new types from existing ones. Psalm refers to these as utility types.

### Key-of and Value-of Types

- `key-of` retrieves the type of all keys in a specified array or object, while `value-of` retrieves the type of its values.

```php
/**
 * @param key-of<UserData> $key
 * @return value-of<UserData>
 */
function getUserData(string $key) {
    $userData = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];
    return $userData[$key] ?? null;
}

/**
 * @return ArrayIterator<key-of<UserData>, value-of<UserData>>
 */
function getUserDataIterator() {
    $userData = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];
    return new ArrayIterator($userData);
}
```

### Properties-of Type

`properties-of` represents the type of all properties of a class. This is useful when dealing with class properties dynamically.

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

// Usage example
$user = new User();
$propertyValue = getUserProperty($user, 'name'); // $propertyValue is of type string
```

`properties-of` has the following variants:

- `public-properties-of<T>`: Targets only public properties.
- `protected-properties-of<T>`: Targets only protected properties.
- `private-properties-of<T>`: Targets only private properties.

Using these variants allows you to deal with properties of specific access modifiers.

### Class Name Mapping Type

`class-string-map` represents an array with class names as keys and their instances as values. This is useful for implementing dependency injection containers or factory patterns.

```php
/**
 * @template T of object
 * @param class-string-map<T, T> $map
 * @param class-string<T> $className
 * @return T
 */
function getInstance(array $map, string $className) {
    return $map[$className] ?? new $className();
}

// Usage example
$container = [
    UserRepository::class => new UserRepository(),
    ProductRepository::class => new ProductRepository(),
];

$userRepo = getInstance($container, UserRepository::class);
```

### Index Access Type

The index access type (`T[K]`) represents the element of type `T` at index `K`. This is useful for accurately representing types when accessing array or object properties.

```php
/**
 * @template T of array
 * @template K of key-of<T>
 * @param T $data
 * @param K $key
 * @return T[K]
 */
function getArrayValue(array $data, $key) {
    return $data[$key];
}

// Usage example
$config = ['debug' => true, 'version' => '1.0.0'];
$debugMode = getArrayValue($config, 'debug'); // $debugMode is of type bool
```

These utility types are specific to psalm. They can be considered part of the [Advanced Type System](#advanced-type-system).

## Functional Programming Concepts

PHPDoc supports important concepts influenced by functional programming. Using these concepts can improve the predictability and reliability of your code.

### Pure Functions

Pure functions are functions without side effects that always return the same output for the same input.

```php
/**
 * @pure
 */
function add(int $a, int $b): int 
{
    return $a + $b;
}
```

This annotation indicates that the function has no side effects and always produces the same output for the same input.

### Immutable Objects

Immutable objects are objects whose state cannot be altered once they are created.

```php
/**
 * @immutable
 *
 * - All properties are considered readonly.
 * - All methods are implicitly treated as `@psalm-mutation-free`.
 */
class Point {
    public function __construct(
        private float $x, 
        private float $y
    ) {}

    public function withX(float $x): static 
    {
        return new self($x, $this->y);
    }

    public function withY(float $y): static
    {
        return new self($this->x, $y);
    }
}
```

#### @psalm-mutation-free

This annotation indicates that a method does not change the internal state of the class or any external state. Methods of `@immutable` classes implicitly have this property, but it can also be used for specific methods of non-immutable classes.

```php
class Calculator {
    private float $lastResult = 0;

    /**
     * @psalm-mutation-free
     */
    public function add(float $a, float $b): float {
        return $a + $b;
    }

    public function addAndStore(float $a, float $b): float {
        $this->lastResult = $a + $b; // This is not allowed with @psalm-mutation-free
        return $this->lastResult;
    }
}
```

#### @psalm-external-mutation-free

This annotation indicates that a method does not change any external state. Changes to the internal state of the class are allowed.

```php
class Logger {
    private array $logs = [];

    /**
     * @psalm-external-mutation-free
     */
    public function log(string $message): void {
        $this->logs[] = $message; // Internal state change is allowed
    }

    public function writeToFile(string $filename): void {
        file_put_contents($filename, implode("\n", $this->logs)); // This changes external state, so it can't be @psalm-external-mutation-free
    }
}
```

#### Guidelines for Using Immutability Annotations

1. Use `@immutable` when the entire class is immutable.
2. Use `@psalm-mutation-free` for specific methods that don't change any state.
3. Use `@psalm-external-mutation-free` for methods that don't change external state but may change internal state.

Properly expressing immutability can lead to many benefits, including improved safety in concurrent processing, reduced side effects, and easier-to-understand code.

### Side Effect Annotations

When a function has side effects, it can be explicitly annotated to caution its usage.

```php
/**
 * @side-effect This function writes to the database
 */
function logMessage(string $message): void {
    // Logic to write message to database
}
```

### Higher-Order Functions

Higher-order functions are functions that take functions as arguments or return functions. PHPDoc can be used to accurately express the types of higher-order functions.

```php
/**
 * @param callable(int): bool $predicate
 * @param list<int>           $numbers
 * @return list<int>
 */
function filter(callable $predicate, array $numbers): array {
    return array_filter($numbers, $predicate);
}
```

Higher-order functions are closely related to [Callable Types](#callable-types).

## Assert Annotations

Assert annotations are used to inform static analysis tools that certain conditions are met.

```php
/**
 * @psalm-assert string $value
 * @psalm-assert-if-true string $value
 * @psalm-assert-if-false null $value
 */
function isString($value): bool {
    return is_string($value);
}

/**
 * @psalm-assert !null $value
 */
function assertNotNull($value): void {
    if ($value === null) {
        throw new \InvalidArgumentException('Value must not be null');
    }
}

/**
 * @psalm-assert-if-true positive-int $number
 */
function isPositiveInteger($number): bool {
    return is_int($number) && $number > 0;
}
```

These assert annotations are used as follows:

- `@psalm-assert`: Indicates that the assertion is true if the function terminates normally (without throwing an exception).
- `@psalm-assert-if-true`: Indicates that the assertion is true if the function returns `true`.
- `@psalm-assert-if-false`: Indicates that the assertion is true if the function returns `false`.

Assert annotations may be used in combination with [Type Constraints](#type-constraints).

## Security Annotations

Security annotations are used to highlight security-critical parts of the code and track potential vulnerabilities. There are mainly three annotations:

1. `@psalm-taint-source`: Indicates an untrusted input source.
2. `@psalm-taint-sink`: Indicates where security-critical operations are performed.
3. `@psalm-taint-escape`: Indicates where data has been safely escaped or sanitized.

Here's an example of using these annotations:

```php
/**
 * @psalm-taint-source input
 */
function getUserInput(): string {
    return $_GET['user_input'] ?? '';
}

/**
 * @psalm-taint-sink sql
 */
function executeQuery(string $query): void {
    // Execute SQL query
}

/**
 * @psalm-taint-escape sql
 */
function escapeForSql(string $input): string {
    return addslashes($input);
}

// Usage example
$userInput = getUserInput();
$safeSqlInput = escapeForSql($userInput);
executeQuery("SELECT * FROM users WHERE name = '$safeSqlInput'");
```

By using these annotations, static analysis tools can track the flow of untrusted input and detect potential security issues (such as SQL injection).

## Example: Using Types in Design Patterns

You can use the type system to implement common design patterns in a more type-safe manner.

#### Builder Pattern

```php
/**
 * @template T
 */
interface BuilderInterface {
    /**
     * @return T
     */
    public function build();
}

/**
 * @template T
 * @template-implements BuilderInterface<T>
 */
abstract class AbstractBuilder implements BuilderInterface {
    /** @var array<string, mixed> */
    protected $data = [];

    /** @param mixed $value */
    public function set(string $name, $value): static {
        $this->data[$name] = $value;
        return $this;
    }
}

/**
 * @extends AbstractBuilder<User>
 */
class UserBuilder extends AbstractBuilder {
    public function build(): User {
        return new User($this->data);
    }
}

// Usage example
$user = (new UserBuilder())
    ->set('name', 'John Doe')
    ->set('email', 'john@example.com')
    ->build();
```

#### Repository Pattern

```php
/**
 * @template T
 */
interface RepositoryInterface {
    /**
     * @param int $id
     * @return T|null
     */
    public function find(int $id);

    /**
     * @param T $entity
     */
    public function save($entity): void;
}

/**
 * @implements RepositoryInterface<User>
 */
class UserRepository implements RepositoryInterface {
    public function find(int $id): ?User {
        // Logic to retrieve user from database
    }

    public function save(User $user): void {
        // Logic to save user to database
    }
}
```

#### Type Collection Demo

A comprehensive demonstration showcasing PHP's rich type system capabilities through practical code examples.

```php
<?php

namespace App\Final\Types;

/**
 * @psalm-type SqlQuery = string
 * @psalm-type HtmlContent = string
 * @psalm-type RegexPattern = regex-string
 * @psalm-type ClassConstant = class-constant-string
 * @psalm-type InterfaceClass = interface-string
 */
class SecurityContext {
    /**
     * @param non-empty-string $userInput
     * @return html-escaped-string
     * 
     * @psalm-taint-source input $userInput
     * @psalm-taint-escape html
     */
    public function escapeHtml(string $userInput): string {
        return htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param array<string, mixed> $params
     * @return SqlQuery
     * 
     * @psalm-taint-sink sql $query
     * @psalm-taint-escape sql
     */
    public function prepareSqlQuery(array $params): string {
        $query = "SELECT * FROM users WHERE id = :id";
        // prepared statements here
        return $query;
    }

    /**
     * @param RegexPattern $pattern
     * @param non-empty-string $subject
     * @return array<array-key, string>
     *
     * @psalm-taint-specialize
     */
    public function secureMatch(string $pattern, string $subject): array {
        if (@preg_match($pattern, '') === false) {
            throw new \InvalidArgumentException('Invalid regex pattern');
        }
        preg_match_all($pattern, $subject, $matches);
        return $matches[0];
    }
}

/**
 * @template T
 * @psalm-require-extends \Exception
 */
class TypedException extends \Exception {
    /** @var T */
    private mixed $context;

    /**
     * @param T $context
     */
    public function __construct(mixed $context) {
        $this->context = $context;
        parent::__construct();
    }

    /**
     * @return T
     * @psalm-mutation-free
     */
    public function getContext(): mixed {
        return $this->context;
    }
}

/**
 * @template T
 * @psalm-require-implements \Stringable
 */
class StringWrapper {
    /**
     * @param T $value
     */
    public function __construct(
        private readonly mixed $value
    ) {}

    /**
     * @return non-empty-string
     * @psalm-trace
     */
    public function toString(): string {
        return (string) $this->value;
    }
}

/**
 * @psalm-type MemoryTrace = array{
 *   allocation: positive-int,
 *   deallocated: bool,
 *   stack_trace: list<non-empty-string>
 * }
 */
class MemoryManager {
    /** @var array<string, MemoryTrace> */
    private array $traces = [];

    /**
     * @param object $object
     * @return positive-int
     * @psalm-flows-into $this->traces
     */
    public function track(object $object): int {
        $id = spl_object_id($object);
        $this->traces[spl_object_hash($object)] = [
            'allocation' => memory_get_usage(true),
            'deallocated' => false,
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ];
        return $id;
    }
}

/**
 * @template T of object
 * @psalm-type Middleware = callable(T, callable(T): void): void
 */
class MiddlewareChain {
    /** @var list<Middleware<T>> */
    private array $middlewares = [];

    /**
     * @param Middleware<T> $middleware
     */
    public function append(callable $middleware): void {
        $this->middlewares[] = $middleware;
    }

    /**
     * @param T $context
     * @psalm-taint-specialize $context
     */
    public function execute(object $context): void {
        $next = function($ctx) use (&$next): void {};
        
        foreach (array_reverse($this->middlewares) as $middleware) {
            $next = function($ctx) use ($middleware, $next): void {
                $middleware($ctx, $next);
            };
        }

        $next($context);
    }
}

/**
 * @template T
 */
interface Cache {
    /**
     * @param non-empty-string $key
     * @param T $value
     * @param positive-int|null $ttl
     * @psalm-taint-sink system $key
     */
    public function set(string $key, mixed $value, ?int $ttl = null): void;

    /**
     * @param non-empty-string $key
     * @return T|null
     * @psalm-taint-sink system $key
     */
    public function get(string $key): mixed;
}

/**
 * @template T
 * @implements Cache<T>
 */
class FileCache implements Cache {
    /**
     * @param non-empty-string $directory
     * @throws \RuntimeException
     */
    public function __construct(
        private readonly string $directory
    ) {
        if (!is_dir($directory) && !mkdir($directory, 0777, true)) {
            throw new \RuntimeException("Cannot create directory: {$directory}");
        }
    }

    /**
     * @param non-empty-string $key
     * @param T $value
     * @param positive-int|null $ttl
     * @psalm-taint-sink system $key
     * @psalm-taint-sink file $value
     */
    public function set(string $key, mixed $value, ?int $ttl = null): void {
        $path = $this->getPath($key);
        file_put_contents($path, serialize([
            'value' => $value,
            'expires_at' => $ttl ? time() + $ttl : null
        ]));
    }

    /**
     * @param non-empty-string $key
     * @return T|null
     * @psalm-taint-sink system $key
     * @psalm-taint-source file
     */
    public function get(string $key): mixed {
        $path = $this->getPath($key);
        if (!file_exists($path)) {
            return null;
        }

        $data = unserialize(file_get_contents($path));
        if ($data['expires_at'] !== null && $data['expires_at'] < time()) {
            unlink($path);
            return null;
        }

        return $data['value'];
    }

    /**
     * @param non-empty-string $key
     * @return non-empty-string
     * @psalm-taint-escape file
     */
    private function getPath(string $key): string {
        return $this->directory . '/' . hash('sha256', $key);
    }
}
```

## Summary

By deeply understanding and appropriately using the PHPDoc type system, you can benefit from self-documenting code, early bug detection through static analysis, powerful code completion and assistance from IDEs, clarification of code intentions and structure, and mitigation of security risks. This allows you to write more robust and maintainable PHP code.

The following is an example that covers all available types.

```php
<?php

namespace App\Comprehensive\Types;

/**
 * Example class covering atomic, scalar, union, intersection, and generic types
 *
 * @psalm-type UserId = int
 * @psalm-type HtmlContent = string
 * @psalm-type PositiveFloat = float&positive
 * @psalm-type Numeric = int|float
 * @psalm-type QueryResult = array<string, mixed>
 */
class TypeExamples {
    /**
     * Retrieves user content based on ID
     *
     * @param UserId|non-empty-string $id
     * @return HtmlContent
     */
    public function getUserContent(int|string $id): string {
        return "<p>User ID: {$id}</p>";
    }

    /**
     * Processes a positive float amount
     *
     * @param PositiveFloat $amount
     * @return bool
     */
    public function processPositiveAmount(float $amount): bool {
        return $amount > 0;
    }
}

/**
 * Immutable class, functional programming, pure function example
 *
 * @immutable
 */
class ImmutableUser {
    /** @var non-empty-string */
    private string $name;

    /** @var positive-int */
    private int $age;

    /**
     * Constructor for an immutable user
     *
     * @param non-empty-string $name
     * @param positive-int $age
     */
    public function __construct(string $name, int $age) {
        $this->name = $name;
        $this->age = $age;
    }

    /**
     * Returns a new user with additional years added
     *
     * @psalm-pure
     * @return ImmutableUser
     */
    public function withAdditionalYears(int $additionalYears): self {
        return new self($this->name, $this->age + $additionalYears);
    }
}

/**
 * Template type, generic type, conditional type, covariance and contravariance example
 *
 * @template T
 * @template-covariant U
 */
class StorageContainer {
    /** @var array<T, U> */
    private array $items = [];

    /**
     * Adds a new item to the container
     *
     * @param T $key
     * @param U $value
     */
    public function add(mixed $key, mixed $value): void {
        $this->items[$key] = $value;
    }

    /**
     * Retrieves an item by its key
     *
     * @param T $key
     * @return U|null
     * @psalm-assert-if-true string $key
     */
    public function get(mixed $key): mixed {
        return $this->items[$key] ?? null;
    }

    /**
     * @template V
     * @param T $key
     * @return (T is string ? string : U|null)
     */
    public function conditinalGet(mixed $key): mixed {
        return is_string($key) ? "default_string_value" : ($this->items[$key] ?? null);
    }
}

/**
 * Example of type constraints, utility types, functional programming, and assertion annotations
 *
 * @template T of array-key
 */
class UtilityExamples {
    /**
     * Returns the keys of an associative array
     *
     * @template T of array-key
     * @psalm-param array<T, mixed> $array
     * @psalm-return list<T>
     * @psalm-assert array<string, mixed> $array
     */
    public function getKeys(array $array): array {
        return array_keys($array);
    }

    /**
     * Maps classes to their instances
     *
     * @template T of object
     * @psalm-param class-string-map<T, array-key> $classes
     * @psalm-return list<T>
     */
    public function mapClasses(array $classes): array {
        return array_map(fn(string $className): object => new $className(), array_keys($classes));
    }
}

/**
 * High-order function, type alias, index access type example
 *
 * @template T
 * @psalm-type Predicate = callable(T): bool
 */
class FunctionalExamples {
    /**
     * Filters items based on a predicate
     *
     * @param list<T> $items
     * @param Predicate<T> $predicate
     * @return list<T>
     */
    public function filter(array $items, callable $predicate): array {
        return array_filter($items, $predicate);
    }

    /**
     * Retrieves a value from a map by key
     *
     * @param array<string, T> $map
     * @param key-of $map $key
     * @return T|null
     */
    public function getValue(array $map, string $key): mixed {
        return $map[$key] ?? null;
    }
}

/**
 * Security annotation, type constraint, index access type, property access type, key and value access type example
 *
 * @template T
 */
class SecureAccess {
    /**
     * Retrieves a property from a user profile
     *
     * @psalm-type UserProfile = array{
     *   id: int,
     *   name: non-empty-string,
     *   email: non-empty-string,
     *   roles: list<non-empty-string>
     * }
     * @psalm-param UserProfile $profile
     * @psalm-param key-of<UserProfile> $property
     * @return value-of<UserProfile>
     * @psalm-taint-escape system
     */
    public function getUserProperty(array $profile, string $property): mixed {
        return $profile[$property];
    }
}

/**
 * Complex structure type, security annotations, pure function example
 *
 * @template T of object
 * @template-covariant U of array-key
 * @psalm-type ErrorResponse = array{error: non-empty-string, code: positive-int}
 */
class ComplexExample {
    /** @var array<U, T> */
    private array $registry = [];

    /**
     * Registers an object by key
     *
     * @param U $key
     * @param T $value
     */
    public function register(mixed $key, object $value): void {
        $this->registry[$key] = $value;
    }

    /**
     * Retrieves a registered object by key
     *
     * @param U $key
     * @return T|null
     * @psalm-pure
     * @psalm-assert-if-true ErrorResponse $this->registry[$key]
     */
    public function getRegistered(mixed $key): ?object {
        return $this->registry[$key] ?? null;
    }
}

```


## References

To make the most of PHPDoc types, static analysis tools like Psalm or PHPStan are necessary. For more details, refer to the following resources:

- [Psalm - Typing in Psalm](https://psalm.dev/docs/annotating_code/typing_in_psalm/)
   - [Atomic Types](https://psalm.dev/docs/annotating_code/type_syntax/atomic_types/)
   - [Templating](https://psalm.dev/docs/annotating_code/templated_annotations/)
   - [Assertions](https://psalm.dev/docs/annotating_code/adding_assertions/)
   - [Security Analysis](https://psalm.dev/docs/security_analysis/)
- [PHPStan - PHPDoc Types](https://phpstan.org/writing-php-code/phpdoc-types)
