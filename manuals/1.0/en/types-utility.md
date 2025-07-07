---
layout: docs-en
title: PHPDoc Utility Types
category: Manual
permalink: /manuals/1.0/en/types-utility.html
---
# PHPDoc Utility Types

Utility types are used to manipulate existing types or dynamically generate new types. Using these types enables more flexible and expressive type definitions.

## Table of Contents

1. [key-of<T>](#key-oft)
2. [value-of<T>](#value-oft)
3. [properties-of<T>](#properties-oft)
4. [class-string-map<T of Foo, T>](#class-string-mapt-of-foo-t)
5. [T[K]](#tk)
6. [Type aliases](#type-aliases)
7. [Variable templates](#variable-templates)

## key-of<T>

`key-of<T>` represents the type of all possible keys of type `T`.

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

// Usage example
$userData = ['id' => 1, 'name' => 'John'];
$name = getValueByKey($userData, 'name'); // OK
$age = getValueByKey($userData, 'age'); // Psalm will warn
```

## value-of<T>

`value-of<T>` represents the type of all possible values of type `T`.

```php
/**
 * @template T of array
 * @param T $data
 * @return value-of<T>
 */
function getRandomValue(array $data) {
    return $data[array_rand($data)];
}

// Usage example
$numbers = [1, 2, 3, 4, 5];
$randomNumber = getRandomValue($numbers); // int type
```

## properties-of<T>

`properties-of<T>` represents the type of all properties of type `T`.

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
$name = getUserProperty($user, 'name'); // string type
$id = getUserProperty($user, 'id'); // int type
$unknown = getUserProperty($user, 'unknown'); // Psalm will warn
```

## class-string-map<T of Foo, T>

`class-string-map` represents an array with class names as keys and their instances as values.

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
function getRepository(array $repositories, string $className) {
    return $repositories[$className];
}

// Usage example
$repositories = [
    UserRepository::class => new UserRepository(),
    ProductRepository::class => new ProductRepository(),
];

$userRepo = getRepository($repositories, UserRepository::class); // UserRepository type
```

## T[K]

`T[K]` represents indexed access to type `T` with key `K`.

```php
/**
 * @template T of array
 * @template K of key-of<T>
 * @param T $data
 * @param K $key
 * @return T[K]
 */
function getTypedValue(array $data, $key) {
    return $data[$key];
}

// Usage example
$config = [
    'database' => ['host' => 'localhost', 'port' => 3306],
    'cache' => ['driver' => 'redis', 'ttl' => 3600]
];

$dbConfig = getTypedValue($config, 'database'); // array{host: string, port: int}
$host = getTypedValue($config['database'], 'host'); // string
```

## Type aliases

Type aliases allow you to create reusable type definitions.

```php
/**
 * @psalm-type UserId = positive-int
 * @psalm-type UserData = array{id: UserId, name: string, email: string}
 * @psalm-type UserCollection = array<UserId, UserData>
 */

class UserService {
    /**
     * @param UserData $userData
     * @return UserId
     */
    public function createUser(array $userData): int {
        // Implementation
        return $userData['id'];
    }
    
    /**
     * @param UserCollection $users
     * @param UserId $id
     * @return UserData|null
     */
    public function findUser(array $users, int $id): ?array {
        return $users[$id] ?? null;
    }
}
```

## Variable templates

Variable templates allow for more dynamic type definitions.

```php
/**
 * @template T
 * @template K of key-of<T>
 * @param T $data
 * @param K ...$keys
 * @return array<K, T[K]>
 */
function pick(array $data, ...$keys): array {
    $result = [];
    foreach ($keys as $key) {
        if (array_key_exists($key, $data)) {
            $result[$key] = $data[$key];
        }
    }
    return $result;
}

// Usage example
$user = [
    'id' => 1,
    'name' => 'John',
    'email' => 'john@example.com',
    'password' => 'secret'
];

$publicData = pick($user, 'id', 'name', 'email'); 
// array{id: int, name: string, email: string}
```

## Advanced Utility Type Examples

### Conditional Types

```php
/**
 * @template T
 * @psalm-type NonEmpty<T> = T is array ? non-empty-array<T> : T
 */

/**
 * @template T of array
 * @param T $data
 * @return NonEmpty<T>
 * @throws InvalidArgumentException
 */
function ensureNonEmpty(array $data): array {
    if (empty($data)) {
        throw new InvalidArgumentException('Array cannot be empty');
    }
    return $data;
}
```

### Recursive Types

```php
/**
 * @psalm-type JsonValue = scalar|null|JsonArray|JsonObject
 * @psalm-type JsonArray = array<JsonValue>
 * @psalm-type JsonObject = array<string, JsonValue>
 */

class JsonParser {
    /**
     * @param string $json
     * @return JsonValue
     */
    public function parse(string $json) {
        return json_decode($json, true);
    }
}
```

### Mapped Types

```php
/**
 * @template T of object
 * @psalm-type Partial<T> = array<key-of<properties-of<T>>, value-of<properties-of<T>>|null>
 */

class UserUpdateService {
    /**
     * @param User $user
     * @param Partial<User> $updates
     * @return User
     */
    public function updateUser(User $user, array $updates): User {
        foreach ($updates as $property => $value) {
            if ($value !== null && property_exists($user, $property)) {
                $user->$property = $value;
            }
        }
        return $user;
    }
}

// Usage
$user = new User();
$updates = ['name' => 'Jane', 'email' => null]; // Partial<User>
$updatedUser = $service->updateUser($user, $updates);
```

## Best Practices

### 1. Use Descriptive Type Names

```php
// Good
/**
 * @psalm-type DatabaseConfig = array{host: string, port: positive-int, database: string}
 */

// Avoid
/**
 * @psalm-type Config = array{host: string, port: positive-int, database: string}
 */
```

### 2. Combine Utility Types for Complex Scenarios

```php
/**
 * @template T of object
 * @template K of key-of<properties-of<T>>
 * @param T $object
 * @param K $property
 * @return properties-of<T>[K]
 */
function getProperty(object $object, string $property) {
    return $object->$property;
}
```

### 3. Document Complex Type Relationships

```php
/**
 * Repository pattern with typed collections
 * 
 * @template TEntity of object
 * @template TId of scalar
 * @psalm-type EntityCollection<TEntity, TId> = array<TId, TEntity>
 * @psalm-type EntitySpec<TEntity> = array<key-of<properties-of<TEntity>>, mixed>
 */
interface Repository {
    /**
     * @param TId $id
     * @return TEntity|null
     */
    public function find($id): ?object;
    
    /**
     * @param EntitySpec<TEntity> $criteria
     * @return EntityCollection<TEntity, TId>
     */
    public function findBy(array $criteria): array;
}
```

## Integration with Static Analysis Tools

These utility types work best with static analysis tools like Psalm and PHPStan:

### Psalm Configuration

```xml
<!-- psalm.xml -->
<psalm>
    <projectFiles>
        <directory name="src" />
    </projectFiles>
    <plugins>
        <pluginClass class="Psalm\Plugin\DocblockTypeProvider" />
    </plugins>
</psalm>
```

### PHPStan Configuration

```neon
# phpstan.neon
parameters:
    level: 8
    paths:
        - src
    treatPhpDocTypesAsCertain: false
```

Utility types provide powerful abstractions for type-safe PHP development, enabling more robust code with better IDE support and static analysis capabilities.
