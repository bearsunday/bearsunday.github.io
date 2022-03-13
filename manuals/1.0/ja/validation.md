---
layout: docs-ja
title: バリデーション
category: Manual
permalink: /manuals/1.0/ja/validation.html
---

# バリデーション

 * JSONスキーマでリソースAPIを定義する事ができます。
 * `@Valid`, `@OnValidate`アノテーションでバリデーションコードを分離する事ができます。
 * Webフォームによるバリデーションは[フォーム](form.html)をご覧ください。

# JSONスキーマ

[JSON スキーマ](http://json-schema.org/)とは、JSON objectの記述と検証のための標準です。`#[JsonSchema]`アトリビュートが付加されたリソースクラスのメソッドが返すリソース`body`に対してJSONスキーマによる検証が行われます。

### インストール

全てのコンテキストで常にバリデーションを行うなら`AppModule`、開発中のみバリデーションを行うなら`DevModule`などのクラスを作成してその中でインストールします。

```php?start_inline
use BEAR\Resource\Module\JsonSchemaModule; // この行を追加
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        // ...
        $this->install(
            new JsonSchemaModule(
                $appDir . '/var/json_schema',
                $appDir . '/var/json_validate'
            )
        );   // この行を追加
    }
}
```

ディレクトリ作成

```bash
mkdir var/json_schema
mkdir var/json_validate
```

`var/json_schema/`にリソースのbodyの仕様となるJSONスキーマファイル、`var/json_validate/`には入力バリデーションのためのJSONスキーマファイルを格納します。

### #[JsonSchema]アトリビュート

リソースクラスのメソッドで`#[JsonSchema]`のアトリビュートを加えます。`schema`プロパティにはJSONスキーマファイル名を指定します。

### schema

src/Resource/App/User.php

```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // この行を追加

class User extends ResourceObject
{
    #[JsonSchema('user.json')]
    public function onGet(): static
    {
        $this->body = [
            'firstName' => 'mucha',
            'lastName' => 'alfons',
            'age' => 12
        ];

        return $this;
    }
}
```

JSONスキーマを設置します。

/var/json_schema/user.json

```json
{
  "type": "object",
  "properties": {
    "firstName": {
      "type": "string",
      "maxLength": 30,
      "pattern": "[a-z\\d~+-]+"
    },
    "lastName": {
      "type": "string",
      "maxLength": 30,
      "pattern": "[a-z\\d~+-]+"
    }
  },
  "required": ["firstName", "lastName"]
}
```

### key

bodyにインデックスキーがある場合にはアノテーションの`key`プロパティで指定します。


```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class User extends ResourceObject
{
    #[JsonSchema(key:'user', schema:'user.json')]
    public function onGet()
    {
        $this->body = [
            'user' => [
                'firstName' => 'mucha',
                'lastName' => 'alfons',
                'age' => 12
            ]
        ];        

        return $this;
    }
}
```

### params

`params `プロパティには引数のバリデーションのためのJSONスキーマファイル名を指定します。


```php?start_inline
use BEAR\Resource\Annotation\JsonSchema; // この行を追加

class Todo extends ResourceObject
{
    #[JsonSchema(key:'user', schema:'user.json', params:'todo.post.json')]
    public function onPost(string $title)
```

JSONスキーマを設置します。

**/var/json_validate/todo.post.json**

```json
{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "title": "/todo POST request validation",
  "properties": {
    "title": {
      "type": "string",
      "minLength": 1,
      "maxLength": 40
    }
}

```

独自ドキュメントの代わりに標準化された方法で常に検証することで、その仕様が**人間にもマシンにも理解できる**確実なものになります。

### target

ResourceObjectのbodyに対してでなく、リソースオブジェクトの表現（レンダリングされた結果）に対してスキーマバリデーションを適用にするには`target='view'`オプションを指定します。
HALフォーマットで`_link`のスキーマが記述できます。

```php
#[JsonSchema(schema: 'user.json', target: 'view')]
```

###  関連リンク

 * [Example](http://json-schema.org/examples.html)
 * [Understanding JSON Schema](https://spacetelescope.github.io/understanding-json-schema/)
 * [JSON Schema Generator](https://jsonschema.net/#/editor)

## @Validアノテーション

`@Valid`アノテーションは入力のためのバリデーションです。メソッドの実行前にバリデーションメソッドが実行され、
エラーを検知すると例外が発生されエラー処理のためのメソッドを呼ぶこともできます。

分離したバリデーションのコードは可読性に優れテストが容易です。バリデーションのライブラリは[Aura.Filter](https://github.com/auraphp/Aura.Filter)や[Respect\Validation](https://github.com/Respect/Validation)、あるいは[PHP標準のFilter](http://php.net/manual/ja/book.filter.php)を使います。

### インストール

composerインストール

```bash
composer require ray/validate-module
```

アプリケーションモジュール`src/Module/AppModule.php`で`ValidateModule`をインストールします。

```php?start_inline
use Ray\Validation\ValidateModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new ValidateModule);
    }
}
```

### アノテーション

バリデーションのために`@Valid`、`@OnValidate`、`@OnFailure`の３つのアノテーションが用意されています。


まず、バリデーションを行いたいメソッドに`@Valid`とアノテートします。

```php?start_inline
use Ray\Validation\Annotation\Valid;
// ...
    /**
     * @Valid
     */
    public function createUser($name)
    {
```

`@OnValidate`とアノテートしたメソッドでバリデーションを行います。引数は元のメソッドと同じにします。メソッド名は自由です。

```php?start_inline
use Ray\Validation\Annotation\OnValidate;
// ...
    /**
     * @OnValidate
     */
    public function onValidate($name)
    {
        $validation = new Validation;
        if (! is_string($name)) {
            $validation->addError('name', 'name should be string');
        }

        return $validation;
    }
```

バリデーション失敗した要素には`要素名`と`エラーメッセージ`を指定してValidationオブジェクトに`addError()`し、最後にValidationオブジェクトを返します。

バリデーションが失敗すれば`Ray\Validation\Exception\InvalidArgumentException`例外が投げられますが、
`@OnFailure`メソッドが用意されていればそのメソッドの結果が返されます。

```php?start_inline
use Ray\Validation\Annotation\OnFailure;
// ...
    /**
     * @OnFailure
     */
    public function onFailure(FailureInterface $failure)
    {
        // original parameters
        list($this->defaultName) = $failure->getInvocation()->getArguments();

        // errors
        foreach ($failure->getMessages() as $name => $messages) {
            foreach ($messages as $message) {
                echo "Input '{$name}': {$message}" . PHP_EOL;
            }
        }
    }
```
`@OnFailure`メソッドには`$failure`が渡され`($failure->getMessages()`でエラーメッセージや`$failure->getInvocation()`でオリジナルメソッド実行のオブジェクトが取得できます。

### 複数のバリデーション

１つのクラスに複数のバリデーションメソッドが必要なときは以下のようにバリデーションの名前を指定します。

```php?start_inline
use Ray\Validation\Annotation\Valid;
use Ray\Validation\Annotation\OnValidate;
use Ray\Validation\Annotation\OnFailure;
// ...

    /**
     * @Valid("foo")
     */
    public function fooAction($name, $address, $zip)
    {

    /**
     * @OnValidate("foo")
     */
    public function onValidateFoo($name, $address, $zip)
    {

    /**
     * @OnFailure("foo")
     */
    public function onFailureFoo(FailureInterface $failure)
    {
```

### その他のバリデーション

複雑なバリデーションの時は別にバリデーションクラスをインジェクトして、`onValidate`メソッドから呼び出してバリデーションを行います。DIなのでコンテキストによってバリデーションを変えることもできます。
