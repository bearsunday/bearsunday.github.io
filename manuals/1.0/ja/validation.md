---
layout: docs-ja
title: バリデーション
category: Manual
permalink: /manuals/1.0/ja/validation.html
---

# バリデーション

[JSONスキーマ](http://json-schema.org/)はJSONデータの構造を検証するための強力なツールです。
リソースがどのような制約を持つデータなのかを規定することができます。

`@Valid`アノテーションによるバリデーションは入力値をユーザーのPHPコードで検証します。
Webフォームによりバリデーションは[フォーム](form.html)をご覧ください。

## JSONスキーマ

BEAR.Sundayのリソースオブジェクトを`＠JsonSchema`とアノテートするとリソースオブジェクトの`body`（リソース状態）にJSONスキーマによる検証が行われます。
この時、データ表現はJSONである必要はありません。

### インストール

プロダクション含めてすべてのコンテキストでバリデーションを行うなら`AppModule`、開発中のみバリデーションを行うなら例えば`DevModule`を作成してその中でインストールします。

```php?start_inline
use BEAR\Resource\Module\JsonSchemalModule; // この行を追加
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        // ...
        $this->install(new JsonSchemalModule);  // この行を追加
    }
}
```

### @JsonSchema アノテーション

検証を行うクラスの`onGet`メソッドで`@JsonSchema`とアノテートします。

**Person.php**

```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // この行を追加

class Person extends ResourceObject
{
    /**
     * @JsonSchema
     */
    public function onGet()
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

同じディレクトリで拡張子を`json`にしてJSONスキーマを記述します。

**Person.json**

```json
{
  "title": "Person",
  "type": "object",
  "properties": {
    "firstName": {
      "type": "string"
    },
    "lastName": {
      "type": "string"
    },
    "age": {
      "description": "Age in years",
      "type": "integer",
      "minimum": 20
    }
  },
  "required": ["firstName", "lastName"],
  "additionalProperties": false
}
```

このようにJSONスキーマはリクエストしたリソースがどのような制約を持ったリソースなのかを規程することができます。

開発者が出力されるデータフォーマットを独自のドキュメントに残す代わりに標準化されたJSONスキーマを利用することで、その制約が**人間にもマシンにも理解できるものとなり
宣言されたスキーマが確実に正しい**と確証を得ることができます。

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

class AppModule extends AbstractModule
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
