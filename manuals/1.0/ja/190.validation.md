---
layout: docs-ja
title: バリデーション
category: Manual
permalink: /manuals/1.0/ja/validation.html
---

# バリデーション

* BEAR.SundayのバリデーションはJSONスキーマで行います。
* Webフォームによるバリデーションは[フォーム](form.html)をご覧ください。

## JSONスキーマによるバリデーション

### 概要

[JSON Schema](http://json-schema.org/)を使用して、リソースAPIの入出力仕様を定義し検証することができます。
これにより、APIの仕様を人間とマシンの両方が理解できる形式で管理できます。またApiDocとしてAPIドキュメントを出力することもできます。

### セットアップ

#### モジュールの設定

バリデーションの適用範囲に応じて、以下のいずれかの方法で設定します：

- すべての環境でバリデーションを行う場合：`AppModule`に設定
- 開発環境のみでバリデーションを行う場合：`DevModule`に設定

```php
use BEAR\Resource\Module\JsonSchemaModule;
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        $this->install(
            new JsonSchemaModule(
                $appDir . '/var/json_schema',  // スキーマ定義用
                $appDir . '/var/json_validate' // バリデーション用
            )
        );
    }
}
```

#### 2. 必要なディレクトリの作成

```bash
mkdir -p var/json_schema
mkdir -p var/json_validate
```

### 基本的な使用方法

#### 1. リソースクラスの定義

```php
use BEAR\Resource\Annotation\JsonSchema;

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

#### 2. JSONスキーマの定義

`var/json_schema/user.json`:

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

### 高度な使用方法

#### インデックスキーの指定

レスポンスボディにインデックスキーがある場合、`key`パラメータで指定します：

```php
class User extends ResourceObject
{
    #[JsonSchema(key: 'user', schema: 'user.json')]
    public function onGet(): static
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

#### 引数のバリデーション

メソッドの引数をバリデーションする場合、`params`パラメータでスキーマを指定します：

```php
class Todo extends ResourceObject
{
    #[JsonSchema(
        key: 'user',
        schema: 'user.json',
        params: 'todo.post.json'
    )]
    public function onPost(string $title)
    {
        // メソッドの処理
    }
}
```

`var/json_validate/todo.post.json`:
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
}
```

### target

ResourceObjectのbodyに対してでなく、リソースオブジェクトの表現（レンダリングされた結果）に対してスキーマバリデーションを適用にするには`target='view'`オプションを指定します。
HALフォーマットで`_link`のスキーマが記述できます。

```php
#[JsonSchema(schema: 'user.json', target: 'view')]
```


### スキーマ作成支援ツール

JSONスキーマの作成には以下のツールが便利です：

- [JSON Schema Generator](https://jsonschema.net/#/editor)
- [Understanding JSON Schema](https://spacetelescope.github.io/understanding-json-schema/)
