---
layout: docs-ja
title: PHPDocタイプ
category: Manual
permalink: /manuals/1.0/ja/types.html
---
# PHPDocタイプ

PHPDocの型は、PHPのネイティブ型だけでは表現しきれない配列構造、ジェネリクス、文字列制約、型ガード、セキュリティ解析のための情報を静的解析ツールに伝えるために使います。

このページでは、共通して使いやすい型、Psalm固有の型、PHPStan固有の型を分けて整理しています。PHPDoc標準、Psalm、PHPStanの対応範囲は完全には一致しないため、公開APIやライブラリではできるだけ共通構文を使い、必要な場合だけツール固有タグを使います。

## 使い分け

| 区分 | 用途 |
|------|------|
| 共通PHPDoc | IDE、phpDocumentor、Psalm、PHPStanで広く理解される基本型 |
| Psalm/PHPStan共通 | 両方の静的解析ツールで実用的に使える高度な型 |
| Psalm固有 | `@psalm-*`タグ、Psalm utility types、taint analysis |
| PHPStan固有 | `@phpstan-*`タグ、PHPStanの型エイリアス、PHPStan固有の追加型 |

## 基本型

### スカラー型と特殊型

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

`mixed`は明示的に任意の型を受け入れることを表します。PHPStanでは暗黙の`mixed`と明示的な`mixed`が区別されます。

`never`は、例外送出や`exit`などで正常に戻らない関数を表します。PHPStanでは`noreturn`、`never-return`、`never-returns`、`no-return`も同義として扱われます。

### 数値範囲

```php
/** @param positive-int $id */
/** @param negative-int $delta */
/** @param non-positive-int $max */
/** @param non-negative-int $offset */
/** @param int-mask<1, 2, 4> $flags */
/** @param int-mask-of<Foo::INT_*> $flags */
```

Psalmでは範囲整数に`int-range<0, 100>`を使います。

```php
/** @param int-range<0, 100> $percentage */
```

PHPStanでは範囲整数に`int<0, 100>`、`int<min, 100>`、`int<50, max>`を使います。

```php
/** @param int<0, 100> $percentage */
```

### 文字列型

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

PHPStanには、さらに`uppercase-string`、`non-falsy-string` / `truthy-string`、`non-empty-uppercase-string`、`non-empty-literal-string`、`interface-string<T>`があります。配列キーとして安全な文字列を扱うための`decimal-int-string`と`non-decimal-int-string`もあります。PHPの配列では`'123'`のような文字列キーが整数キーに変換されるため、`array<string, mixed>`を厳密に扱う場面では注意が必要です。

```php
/** @param uppercase-string $upper */
/** @param non-empty-uppercase-string $upper */
/** @param non-falsy-string $truthy */
/** @param non-empty-literal-string $fragment */
/** @param interface-string<ServiceInterface> $service */
/** @param decimal-int-string $key */
/** @param non-decimal-int-string $safeStringKey */
```

### オブジェクト型

```php
/** @param object $object */
/** @param \DateTimeInterface $date */
/** @return static */
/** @return $this */
/** @return object{foo: int, bar?: string} */
/** @return object{foo: int, bar?: string}&\stdClass */
```

`object{...}`はobject shapeです。PHPStanではobject shapeのプロパティは読み取り専用として扱われ、`\stdClass`などと交差させることで書き込み可能な形を表せます。

## 配列とリスト

### 汎用配列

```php
/** @return array<string, int> */
/** @return array<int, User> */
/** @return non-empty-array<string, User> */
/** @return iterable<int, User> */
```

`Type[]`は`array<array-key, Type>`相当の古い表記です。新しいコードでは、キー型を明示できる`array<TKey, TValue>`を優先します。

```php
/** @param User[] $users */
/** @param array<int, User> $users */
```

PHPStanでは`associative-array`も基本型として使えます。

```php
/** @return associative-array */
```

### リスト

`list<T>`は0から始まる連続した整数キーの配列です。

```php
/** @param list<string> $names */
/** @return non-empty-list<User> */
```

PsalmとPHPStanはlist shapeも扱えます。

```php
/** @return list{string, int} */
```

PHPStanでは`non-empty-list{string, int}`のように、non-empty指定とshape構文を組み合わせることもできます。

### Array shape

Array shapeはキーごとに異なる型を表します。

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

Psalmは`array{..., ...}`でopen shapeを表し、PHPStanは`...<K, V>`（や`...<T>`のようなvariadic list shape）で型付きunsealed shapeを表せます。

```php
/** @param array{verbose: bool, ...} $options */
/** @param array{foo: int, ...<string, int>} $data */
/** @return list{string, int, ...<bool>} */
```

PHPStanではsealed array shapeの扱いが厳密です。extra keysを許す意図がある場合は、unsealed shapeを明示します。

## 複合型

### Union、Intersection、括弧

```php
/** @param int|string $id */
/** @return User|null */
/** @param Countable&Traversable $collection */
/** @param (A&B)|C $value */
```

### 値型と定数型

```php
/** @return 'success'|'error'|'pending' */
/** @return 200|400|500 */
/** @param Foo::STATUS_* $status */
/** @param Foo::* $constant */
```

## Callable

```php
/** @param callable(int, string): bool $callback */
/** @param callable(string &$value): void $normalizer */
/** @param callable(float ...$values): int|null $aggregate */
/** @param Closure(User): string $formatter */
/** @param pure-callable(User): string $formatter */
/** @param pure-Closure(User): string $formatter */
```

PHPStanでは`@param-closure-this`でクロージャ内の`$this`を指定できます。

```php
/**
 * @param Closure(): void $callback
 * @param-closure-this User $callback
 */
function withUser(Closure $callback): void;
```

## ジェネリクス

### 関数テンプレート

```php
/**
 * @template T
 * @param list<T> $items
 * @param callable(T): bool $predicate
 * @return list<T>
 */
function filter(array $items, callable $predicate): array;
```

### クラステンプレート

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

PsalmとPHPStanは`@template-covariant`と`@template-contravariant`を扱えます。PHPStanは利用箇所側の`Collection<covariant Animal>`、`Collection<contravariant Dog>`や、型引数を読み書き制限付きで任意にする`Collection<*>`も扱います。

## 条件付き型

```php
/**
 * @template T of int|array<int>
 * @param T $id
 * @return (T is int ? User : list<User>)
 */
function fetch(int|array $id): User|array;
```

PHPStanでは否定条件も扱えます。

```php
/**
 * @return ($value is not null ? string : never)
 */
function stringify(mixed $value): string;
```

## 型演算子

### key-of / value-of

```php
/**
 * @param key-of<User::FIELDS> $field
 * @return value-of<User::FIELDS>
 */
function fieldValue(string $field): string|int;
```

`value-of<BackedEnum>`はPHPStanでBackedEnumの値型を取り出す用途にも使えます。

```php
/** @param value-of<Suit> $suit */
function selectSuit(string $suit): void;
```

### Offset access

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

### Psalm utility types

Psalmは`properties-of<T>`、`public-properties-of<T>`、`protected-properties-of<T>`、`private-properties-of<T>`、`class-string-map<T of Foo, T>`、変数テンプレートなどのutility typesを提供します。詳細は[PHPDoc ユーティリティ型](types-utility.html)を参照してください。

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

### PHPStan utility types

PHPStanは`template-type`で渡されたオブジェクトからtemplate型を取得し、`new`で`class-string<T>`からオブジェクト型を作れます。

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

## 型エイリアス

### Psalmの型エイリアス

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

### PHPStanの型エイリアス

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

PHPStanは`phpstan.neon`の`typeAliases`でグローバル型エイリアスも定義できます。

```neon
parameters:
    typeAliases:
        UserId: positive-int
        UserData: 'array{id: UserId, name: non-empty-string}'
```

## Assert / type guard

### PsalmのAssert

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

Psalmは`@psalm-if-this-is`と`@psalm-this-out`で、メソッド呼び出し後の`$this`の型も表せます。

### PHPStanのAssert

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

PHPStanは`@phpstan-self-out`と`@phpstan-this-out`で、メソッド呼び出し後の現在オブジェクトの型を変化させられます。

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

## 参照渡しの出力型

PsalmとPHPStanは`@param-out`で、参照渡しパラメータの呼び出し後の型を表せます。Psalm固有タグとして`@psalm-param-out`も使えます。

```php
/**
 * @param-out non-empty-string $value
 */
function fillDefault(?string &$value): void
{
    $value ??= 'default';
}
```

## 不変性と副作用

Psalmでは副作用や不変性を明示できます。

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

主なタグ:

| タグ | 意味 |
|------|------|
| `@psalm-pure` | 入力だけに依存する純粋関数 |
| `@psalm-impure` | 副作用がある関数 |
| `@psalm-mutation-free` | 自身も外部状態も変更しないメソッド |
| `@psalm-external-mutation-free` | 外部状態を変更しないメソッド |
| `@psalm-immutable` | 不変クラス |
| `@psalm-readonly` / `@readonly` | 読み取り専用プロパティ |
| `@psalm-allow-private-mutation` | private文脈だけで変更可能 |

PHPStanでは`@phpstan-pure`と`@phpstan-impure`が使えます。読み取り専用でprivate変更を許す場合は`@phpstan-allow-private-mutation`や`@phpstan-readonly-allow-private-mutation`を使います。

## セキュリティ解析

Psalmのtaint analysisでは、入力源、危険な出力先、escape処理、flowをPHPDocで表現できます。

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

主なタグ:

| タグ | 用途 |
|------|------|
| `@psalm-taint-source <type>` | 信頼できない入力源 |
| `@psalm-taint-sink <type> <param>` | 危険な出力先 |
| `@psalm-taint-escape <type>` | taintを除去する処理 |
| `@psalm-taint-unescape <type>` | escape済みデータを再びtaint扱いにする |
| `@psalm-taint-specialize` | 関数やクラスでtaintを特殊化する |
| `@psalm-flow (...) -> return` | 明示的なtaint flow |

## デバッグと検証用タグ

```php
/** @psalm-trace $value */
$value = userInput();

/** @psalm-check-type $value = non-empty-string */
$value = 'BEAR';

/** @psalm-check-type-exact $value = 'BEAR' */
$value = 'BEAR';
```

これらはドキュメント用というより、Psalmの推論結果を確認するための開発支援タグです。

## BEAR.Sundayでの推奨

1. ネイティブ型で表せるものはPHPの型宣言に書きます。
2. 配列の要素型、array shape、list、ジェネリクス、型エイリアスはPHPDocに書きます。
3. 公開APIではPsalm/PHPStan両方で理解しやすい構文を優先します。
4. Psalm固有の解析には`@psalm-*`、PHPStan固有の解析には`@phpstan-*`を使い、意図的に分けます。
5. shapeが大きくなりすぎる場合は、array shapeよりDTO、Value Object、Entityを検討します。
6. SQL、HTML、ファイルパス、コマンドなどのセキュリティ境界では、`literal-string`、taint annotation、専用のSemantic Typeを組み合わせます。

## 互換注意点

| 項目 | 注意 |
|------|------|
| `int-range` / `int<min, max>` | Psalmは`int-range`、PHPStanは`int<min, max>`を使います。 |
| `@psalm-type` / `@phpstan-type` | ツール固有です。両方使う場合は同じ意味で二重定義します。 |
| `properties-of` | Psalm utility typeです。PHPStan向けには別表現を検討します。 |
| unsealed shape | extra keysを許す意図がある場合に明示します。 |
| `literal-string` | セキュリティ上有用ですが、通常の`string`とは別物です。SQLやテンプレート断片で使います。 |
| `array<string, T>` | PHPの配列キー変換により、整数文字列キーが`int`になることがあります。PHPStanの`non-decimal-int-string`を検討します。 |

## リファレンス

* [Psalm - Atomic types](https://psalm.dev/docs/annotating_code/type_syntax/atomic_types/)
* [Psalm - Array types](https://psalm.dev/docs/annotating_code/type_syntax/array_types/)
* [Psalm - Utility types](https://psalm.dev/docs/annotating_code/type_syntax/utility_types/)
* [Psalm - Supported annotations](https://psalm.dev/docs/annotating_code/supported_annotations/)
* [Psalm - Taint annotations](https://psalm.dev/docs/security_analysis/annotations/)
* [PHPStan - PHPDoc Types](https://phpstan.org/writing-php-code/phpdoc-types)
* [PHPStan - PHPDocs Basics](https://phpstan.org/writing-php-code/phpdocs-basics)
* [PHPStan - Unsealed Array Shapes, Safer Array Keys, and More](https://phpstan.org/blog/phpstan-2-2-unsealed-array-shapes-safer-array-keys)
