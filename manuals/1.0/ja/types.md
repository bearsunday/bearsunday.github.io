---
layout: docs-ja
title: PHPDocタイプ
category: Manual
permalink: /manuals/1.0/ja/types.html
---
# PHPDocタイプ

PHPは動的型付け言語ですが、psalmやphpstanといった静的解析ツールとPHPDocを使用することで、高度な型概念を表現し、静的解析時の型チェックの恩恵を受けることができます。このリファレンスでは、PHPDocで使用可能な型や関連する他の概念について説明します。

## 目次

1. [アトミック型](#アトミック型)
   - [スカラー型](#スカラー型)
   - [オブジェクト型](#オブジェクト型)
   - [配列型](#配列型)
   - [Callable型](#callable型)
   - [値型](#値型)
   - [特殊型](#特殊型)
2. [複合型](#複合型)
   - [ユニオン型](#ユニオン型)
   - [交差型](#交差型)
3. [高度な型システム](#高度な型システム)
   - [ジェネリック型](#ジェネリック型)
   - [テンプレート型](#テンプレート型)
   - [条件付き型](#条件付き型)
   - [型エイリアス](#型エイリアス)
   - [型の制約](#型の制約)
   - [共変性と反変性](#共変性と反変性)
4. [型の演算子（ユーティリティ型）](#型の演算子)
  - [キー取得型と値取得型（key-of と value-of）](#キー取得型と値取得型)
  - [プロパティ取得型（properties-of）](#プロパティ取得型)
  - [クラス名マッピング型（class-string-map<T of Foo, T>）](#クラス名マッピング型)
  - [インデックスアクセス型（T[K]）](#インデックスアクセス型)
5. [関数型プログラミングの概念](#関数型プログラミングの概念)
   - [純粋関数](#純粋関数)
   - [不変オブジェクト](#不変オブジェクト)
   - [副作用の注釈](#副作用の注釈)
   - [高階関数](#高階関数)
6. [アサート注釈](#アサート注釈)
7. [セキュリティ注釈](#セキュリティ注釈)
8. [例：デザインパターンでの型の使用](#例：デザインパターンでの型の使用)

---

## アトミック型

これ以上分割できない基本的な型です。

### スカラー型

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

[複合型](#複合型)や[高度な型システム](#高度な型システムと使用パターン)でこれらの型を組み合わせて使用できます。

### オブジェクト型

```php
/** @param object $obj */
/** @param stdClass $std */
/** @param Foo\Bar $fooBar */
/** @param object{foo: string, bar?: int} $objWithProperties */
/** @return ArrayObject<int, string> */
/** @param Collection<User> $users */
/** @return Generator<int, string, mixed, void> */
```

オブジェクト型は[ジェネリック型](#ジェネリック型)と組み合わせて使用することができます。

### 配列型

#### ジェネリック配列

```php
/** @return array<TKey, TValue> */
/** @return array<int, Foo> */
/** @return array<string, int|string> */
/** @return non-empty-array<string, int> */
```

ジェネリック配列は[ジェネリック型](#ジェネリック型)の概念を使用しています。

#### オブジェクト風配列

```php
/** @return array{0: string, 1: string, foo: stdClass, 28: false} */
/** @return array{foo: string, bar: int} */
/** @return array{optional?: string, bar: int} */
```

#### リスト

```php
/** @param list<string> $stringList */
/** @param non-empty-list<int> $nonEmptyIntList */
```

#### PHPDoc配列（レガシー表記）

```php
/** @param string[] $strings */
/** @param int[][] $nestedInts */
```

### Callable型

```php
/** @return callable(Type1, OptionalType2=, SpreadType3...): ReturnType */
/** @return Closure(bool):int */
/** @param callable(int): string $callback */
```

Callable型は[高階関数](#高階関数)で特に重要です。

### 値型

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

### 特殊型

```php
/** @return void */
/** @return never */
/** @return empty */
/** @return mixed */
/** @return resource */
/** @return closed-resource */
/** @return iterable<TKey, TValue> */
```

## 複合型

複数の[アトミック型](#アトミック型)を組み合わせて作成される型です。

### ユニオン型

```php
/** @param int|string $id */
/** @return string|null */
/** @var array<string|int> $mixedArray */
/** @return 'success'|'error'|'pending' */
```

### 交差型

```php
/** @param Countable&Traversable $collection */
/** @param Renderable&Serializable $object */
```

交差型は[デザインパターン](#デザインパターンでの型の使用)の実装で役立つことがあります。

## 高度な型システムと使用パターン

より複雑で柔軟な型表現を可能にする高度な機能です。

### ジェネリック型

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

ジェネリック型は[高階関数](#高階関数)と組み合わせて使用されることが多いです。

### テンプレート型

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

テンプレート型は[型の制約](#型の制約)と組み合わせて使用できます。

### 条件付き型

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

条件付き型は[ユニオン型](#ユニオン型)と組み合わせて使用されることがあります。

### 型エイリアス

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
    // ユーザー作成ロジック
    return $userData['id'];
}
```

型エイリアスは複雑な型定義を簡略化するのに役立ちます。

### 型の制約

型パラメータに制約を加えることで、より具体的な型の要件を指定できます。

```php
/**
 * @template T of \DateTimeInterface
 * @param T $date
 * @return T
 */
function cloneDate($date) {
    return clone $date;
}

// 使用例
$dateTime = new DateTime();
$clonedDateTime = cloneDate($dateTime);
```

この例では、`T`は`\DateTimeInterface`を実装したクラスに制限されています。

### 共変性と反変性

ジェネリック型を扱う際に、[共変性（covariance）と反変性（contravariance](https://www.php.net/manual/ja/language.oop5.variance.php)）の概念が重要になります。

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

// 使用例
/** @var Producer<Dog> $dogProducer */
/** @var Consumer<Animal> $animalConsumer */
```

共変性は、より派生した型（サブタイプ）を使用できることを意味し、反変性はより基本的な型（スーパータイプ）を使用できることを意味します。

## 型の演算子

型の演算子を使用して、既存の型から新しい型を生成できます。psalmではユーティリティ型と呼んでいます。


### キー取得型と値取得型

- `key-of` は、指定された配列またはオブジェクトのすべてのキーの型を取得し、`value-of` はその値の型を取得します。

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

### プロパティ取得型

`properties-of` は、クラスのすべてのプロパティの型を表します。これは、クラスのプロパティを動的に扱う場合に有用です。

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
$propertyValue = getUserProperty($user, 'name'); // $propertyValue は string 型
```

`properties-of` には以下のバリアントがあります：

- `public-properties-of<T>`: 公開プロパティのみを対象とします。
- `protected-properties-of<T>`: 保護されたプロパティのみを対象とします。
- `private-properties-of<T>`: プライベートプロパティのみを対象とします。

これらのバリアントを使用することで、特定のアクセス修飾子を持つプロパティのみを扱うことができます。

### クラス名マッピング型

`class-string-map` は、クラス名をキーとし、そのインスタンスを値とする配列を表します。これは、依存性注入コンテナやファクトリーパターンの実装に役立ちます。

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

// 使用例
$container = [
    UserRepository::class => new UserRepository(),
    ProductRepository::class => new ProductRepository(),
];

$userRepo = getInstance($container, UserRepository::class);
```

### インデックスアクセス型

インデックスアクセス型（`T[K]`）は、型 `T` のインデックス `K` の要素を表します。これは、配列やオブジェクトのプロパティにアクセスする際の型を正確に表現するのに役立ちます。

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

// 使用例
$config = ['debug' => true, 'version' => '1.0.0'];
$debugMode = getArrayValue($config, 'debug'); // $debugMode は bool 型
```

これらのユーティリティ型はpsalm固有のもので[高度な型システム](#高度な型システムと使用パターン)の一部として考えることができます。

## 関数型プログラミングの概念

PHPDocは、関数型プログラミングの影響を受けた重要な概念をサポートしています。これらの概念を使用することで、コードの予測可能性と信頼性を向上させることができます。

### 純粋関数

純粋関数は、副作用がなく、同じ入力に対して常に同じ出力を返す関数です。

```php
/**
 * @pure
 */
function add(int $a, int $b): int 
{
    return $a + $b;
}
```

関数の副作用がないこと、そして関数の結果が入力のみに依存することを明示できます。

### 不変オブジェクト

不変オブジェクトは、作成後に状態が変更されないオブジェクトです。

```php
/**
 * @immutable
 * - すべてのプロパティは実質的に`readonly`として扱われます。
 * - すべてのメソッドは暗黙的に`@psalm-mutation-free`として扱われます。
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

このアノテーションは、メソッドがクラスの内部状態も外部の状態も変更しないことを示します。`@immutable`クラスのメソッドは暗黙的にこの性質を持ちますが、非イミュータブルクラスの特定のメソッドに対しても使用できます。

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
        $this->lastResult = $a + $b; // これは@psalm-mutation-freeでは許可されません
        return $this->lastResult;
    }
}
```

#### @psalm-external-mutation-free

このアノテーションは、メソッドがクラスの外部の状態を変更しないことを示します。内部状態の変更は許可されます。

```php
class Logger {
    private array $logs = [];

    /**
     * @psalm-external-mutation-free
     */
    public function log(string $message): void {
        $this->logs[] = $message; // クラス内部の状態変更は許可されます
    }

    public function writeToFile(string $filename): void {
        file_put_contents($filename, implode("\n", $this->logs)); // これは外部状態を変更するため、@psalm-external-mutation-freeでは使用できません
    }
}
```

#### 不変性アノテーションの使用ガイドライン

1. クラス全体が不変である場合は `@immutable` を使用します。
2. 特定のメソッドが状態を変更しない場合は `@psalm-mutation-free` を使用します。
3. メソッドが外部の状態は変更しないが、内部状態を変更する可能性がある場合は `@psalm-external-mutation-free` を使用します。

不変性を適切に表現することで、並行処理での安全性向上、副作用の減少、コードの理解しやすさの向上など、多くの利点を得ることができます。

### 副作用の注釈

関数が副作用を持つ場合、それを明示的に注釈することで、その関数の使用に注意を促すことができます。

```php
/**
 * @side-effect This function writes to the database
 */
function logMessage(string $message): void {
    // データベースにメッセージを書き込む処理
}
```

### 高階関数

高階関数は、関数を引数として受け取るか、関数を返す関数です。PHPDocを使用して、高階関数の型を正確に表現できます。

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

高階関数は[Callable型](#callable型)と密接に関連しています。

## アサート注釈

アサート注釈は、静的解析ツールに対して特定の条件が満たされていることを伝えるために使用されます。

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

これらのアサート注釈は、以下のように使用されます：

- `@psalm-assert`: 関数が正常に終了した場合（例外をスローせずに）、アサーションが真であることを示します。
- `@psalm-assert-if-true`: 関数が `true` を返した場合、アサーションが真であることを示します。
- `@psalm-assert-if-false`: 関数が `false` を返した場合、アサーションが真であることを示します。

アサート注釈は[型の制約](#型の制約)と組み合わせて使用されることがあります。

## セキュリティ注釈

セキュリティ注釈は、コード内のセキュリティに関連する重要な部分を明示し、潜在的な脆弱性を追跡するために使用されます。主に以下の3つの注釈があります：

1. `@psalm-taint-source`: 信頼できない入力源を示します。
2. `@psalm-taint-sink`: セキュリティ上重要な操作が行われる場所を示します。
3. `@psalm-taint-escape`: データが安全にエスケープまたはサニタイズされた場所を示します。

以下は、これらの注釈の使用例です：

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
    // SQLクエリを実行
}

/**
 * @psalm-taint-escape sql
 */
function escapeForSql(string $input): string {
    return addslashes($input);
}

// 使用例
$userInput = getUserInput();
$safeSqlInput = escapeForSql($userInput);
executeQuery("SELECT * FROM users WHERE name = '$safeSqlInput'");
```

これらの注釈を使用することで、静的解析ツールは信頼できない入力の流れを追跡し、潜在的なセキュリティ問題（SQLインジェクションなど）を検出できます。

## 例：デザインパターンでの型の使用

型システムを活用して、一般的なデザインパターンをより型安全に実装できます。

#### ビルダーパターン

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

// 使用例
$user = (new UserBuilder())
    ->set('name', 'John Doe')
    ->set('email', 'john@example.com')
    ->build();
```

#### リポジトリパターン

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
        // データベースからユーザーを取得するロジック
    }

    public function save(User $user): void {
        // ユーザーをデータベースに保存するロジック
    }
}
```
## まとめ

PHPDocの型システムを深く理解して適切に使用することで、コードの自己文書化、静的解析による早期のバグ検出、IDEによる強力なコード補完と支援、コードの意図と構造の明確化、セキュリティリスクの軽減などの利点が得られ、より堅牢で保守性の高いPHPコードを書くことができます。以下は利用可能な型を網羅した例です。

```php
<?php

namespace App\Comprehensive\Types;

/**
 * アトミック型、スカラー型、ユニオン型、交差型、ジェネリック型を網羅するクラス
 * 
 * @psalm-type UserId = int
 * @psalm-type HtmlContent = string
 * @psalm-type PositiveFloat = float&positive
 * @psalm-type Numeric = int|float
 * @psalm-type QueryResult = array<string, mixed>
 */
class TypeExamples {
    /**
     * @param UserId|non-empty-string $id
     * @return HtmlContent
     */
    public function getUserContent(int|string $id): string {
        return "<p>User ID: {$id}</p>";
    }

    /**
     * @param PositiveFloat $amount
     * @return bool
     */
    public function processPositiveAmount(float $amount): bool {
        return $amount > 0;
    }
}

/**
 * イミュータブルクラス、関数型プログラミング、純粋関数の例
 * 
 * @immutable
 */
class ImmutableUser {
    /** @var non-empty-string */
    private string $name;

    /** @var positive-int */
    private int $age;

    /**
     * @param non-empty-string $name
     * @param positive-int $age
     */
    public function __construct(string $name, int $age) {
        $this->name = $name;
        $this->age = $age;
    }

    /**
     * @psalm-pure
     * @return ImmutableUser
     */
    public function withAdditionalYears(int $additionalYears): self {
        return new self($this->name, $this->age + $additionalYears);
    }
}

/**
 * テンプレート型、ジェネリック型、条件付き型、共変性と反変性の例
 * 
 * @template T
 * @template-covariant U
 */
class StorageContainer {
    /** @var array<T, U> */
    private array $items = [];

    /**
     * @param T $key
     * @param U $value
     */
    public function add(mixed $key, mixed $value): void {
        $this->items[$key] = $value;
    }

    /**
     * @param T $key
     * @return U|null
     */
    public function get(mixed $key): mixed {
        return $this->items[$key] ?? null;
    }
    
    /**
     * @template V
     * @param T $key
     * @return (T is string ? string : U|null)
     */
    public function get(mixed $key): mixed {
        return is_string($key) ? "default_string_value" : ($this->items[$key] ?? null);
    }
}

/**
 * 型の制約、ユーティリティ型、関数型プログラミング、アサート注釈の例
 * 
 * @template T of array-key
 */
class UtilityExamples {
    /**
     * @template T of array-key
     * @psalm-param array<T, mixed> $array
     * @psalm-return list<T>
     * @psalm-assert array<string, mixed> $array
     */
    public function getKeys(array $array): array {
        return array_keys($array);
    }

    /**
     * @template T of object
     * @psalm-param class-string-map<T, array-key> $classes
     * @psalm-return list<T>
     */
    public function mapClasses(array $classes): array {
        return array_map(fn(string $className): object => new $className(), array_keys($classes));
    }
}

/**
 * 高階関数、型エイリアス、インデックスアクセス型の例
 * 
 * @template T
 * @psalm-type Predicate = callable(T): bool
 */
class FunctionalExamples {
    /**
     * @param list<T> $items
     * @param Predicate<T> $predicate
     * @return list<T>
     */
    public function filter(array $items, callable $predicate): array {
        return array_filter($items, $predicate);
    }

    /**
     * @param array<string, T> $map
     * @param key-of $map $key
     * @return T|null
     */
    public function getValue(array $map, string $key): mixed {
        return $map[$key] ?? null;
    }
}

/**
 * セキュリティ注釈、型制約、インデックスアクセス型、プロパティ取得型、キー取得型、値取得型の例
 * 
 * @template T
 */
class SecureAccess {
    /**
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
 * 非常に複雑な構造の型やセキュリティ・注釈、純粋関数の実装例
 * 
 * @template T of object
 * @template-covariant U of array-key
 * @psalm-type ErrorResponse = array{error: non-empty-string, code: positive-int}
 */
class ComplexExample {
    /** @var array<U, T> */
    private array $registry = [];

    /**
     * @param U $key
     * @param T $value
     */
    public function register(mixed $key, object $value): void {
        $this->registry[$key] = $value;
    }

    /**
     * @param U $key
     * @return T|null
     * @psalm-pure
     * @psalm-assert-if-true ErrorResponse $this->registry[$key]
     */
    public function getRegistered(mixed $key): ?object {
        return $this->registry[$key] ?? null;
    }
}

<?php

namespace App\Additional\Types;

/**
 * テンプレート型の制約とcontravariantの例
 * 
 * @template-contravariant T of \Throwable
 */
interface ErrorHandlerInterface {
    /**
     * @param T $error
     * @return void
     */
    public function handle(\Throwable $error): void;
}

/**
 * より具体的な型への実装例
 * 
 * @implements ErrorHandlerInterface<\RuntimeException>
 */
class RuntimeErrorHandler implements ErrorHandlerInterface {
    public function handle(\Throwable $error): void {
        // RuntimeExceptionの処理
    }
}

/**
 * 複雑な型の組み合わせと条件分岐の例
 * 
 * @psalm-type JsonPrimitive = string|int|float|bool|null
 * @psalm-type JsonArray = array<array-key, JsonValue>
 * @psalm-type JsonObject = array<string, JsonValue>
 * @psalm-type JsonValue = JsonPrimitive|JsonArray|JsonObject
 */
class JsonProcessor {
    /**
     * @param JsonValue $value
     * @return (JsonValue is JsonObject ? array<string, mixed> : (JsonValue is JsonArray ? list<mixed> : scalar|null))
     */
    public function process(mixed $value): mixed {
        if (is_array($value)) {
            return array_keys($value) === range(0, count($value) - 1) 
                ? array_values($value)
                : $value;
        }
        return $value;
    }
}

/**
 * より高度なタプル型とレコード型の例
 */
class AdvancedTypes {
    /**
     * @return array{0: int, 1: string, 2: bool}
     */
    public function getTuple(): array {
        return [42, "hello", true];
    }

    /**
     * @param array{id: int, name: string, meta: array{created: string, modified?: string}} $record
     * @return void
     */
    public function processRecord(array $record): void {
        // レコード型の処理
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<string, mixed> $properties
     * @return T
     */
    public function createInstance(string $className, array $properties): object {
        $instance = new $className();
        foreach ($properties as $key => $value) {
            $instance->$key = $value;
        }
        return $instance;
    }
}

/**
 * カスタム型ガードとアサーションの例
 */
class TypeGuards {
    /**
     * @psalm-assert-if-true non-empty-string $value
     */
    public function isNonEmptyString(mixed $value): bool {
        return is_string($value) && $value !== '';
    }

    /**
     * @template T of object
     * @param mixed $value
     * @param class-string<T> $className
     * @psalm-assert-if-true T $value
     */
    public function isInstanceOf(mixed $value, string $className): bool {
        return $value instanceof $className;
    }
}

/**
 * PHPUnit用のテスト関連の型アノテーションの例
 */
class TestTypes {
    /**
     * @param class-string<\Exception> $expectedClass
     * @param callable(): mixed $callback
     */
    public function expectException(string $expectedClass, callable $callback): void {
        try {
            $callback();
            $this->fail('Exception was not thrown');
        } catch (\Exception $e) {
            $this->assertInstanceOf($expectedClass, $e);
        }
    }

    /**
     * @template T
     * @param T $expected
     * @param T $actual
     * @param non-empty-string $message
     */
    public function assertEquals(mixed $expected, mixed $actual, string $message = ''): void {
        // 型安全な比較ロジック
    }
}

/**
 * コレクション型とイテレータの高度な例
 * 
 * @template-covariant TKey of array-key
 * @template-covariant TValue
 * @template-implements \IteratorAggregate<TKey, TValue>
 */
class TypedCollection implements \IteratorAggregate {
    /** @var array<TKey, TValue> */
    private array $items = [];

    /**
     * @return \Traversable<TKey, TValue>
     */
    public function getIterator(): \Traversable {
        yield from $this->items;
    }

    /**
     * @param TValue $item
     * @return void
     */
    public function add(mixed $item): void {
        $this->items[] = $item;
    }

    /**
     * @template TCallback
     * @param callable(TValue): TCallback $callback
     * @return TypedCollection<TKey, TCallback>
     */
    public function map(callable $callback): self {
        $result = new self();
        foreach ($this->items as $key => $value) {
            $result->items[$key] = $callback($value);
        }
        return $result;
    }
}

/**
 * 条件付きメソッドの例
 */
interface ConditionalInterface {
    /**
     * @template T
     * @param T $value
     * @return (T is numeric ? float : string)
     */
    public function process(mixed $value): mixed;
}

```

## リファレンス

PHPDoc型を最大限に活用するためには、PsalmやPHPStanといった静的解析ツールが必要です。詳細については、以下のリソースを参照してください：

- [Psalm - Typing in Psalm](https://koriym.github.io/psalm-ja/annotating_code/typing_in_psalm.html)
  - [Templating](https://koriym.github.io/psalm-ja/annotating_code/templated_annotations.html)
  - [Assertions](https://koriym.github.io/psalm-ja/annotating_code/adding_assertions.html)
  - [Security Analysis](https://koriym.github.io/psalm-ja/security_analysis.html)
- [PHPStan - PHPDoc Types](https://phpstan.org/writing-php-code/phpdoc-types.html)
