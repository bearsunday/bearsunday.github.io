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

## 例外ハンドリング

JSONスキーマバリデーションが失敗すると、BEAR.Resource (1.33.0+) は問題が**リクエスト**（クライアントエラー）にあるか**レスポンス**（サーバーのバグ）にあるかを示す型付き例外をスローします。

| 例外 | HTTP | 意味 |
|------|------|------|
| `JsonSchemaRequestException` | 400 | 入力パラメータがスキーマ検証に失敗 |
| `JsonSchemaResponseException` | 500 | レスポンスボディがスキーマ検証に失敗 |

どちらも `JsonSchemaException` を継承しているため、個別にキャッチすることも、まとめてキャッチすることもできます。

### 構造化エラーへのアクセス

`$e->getErrors()` は `JsonSchemaErrors` コレクションを返します。

```php
use BEAR\Resource\Exception\JsonSchemaRequestException;
use BEAR\Resource\Exception\JsonSchemaResponseException;

try {
    $response = $resource->post('app://self/user', $params);
} catch (JsonSchemaRequestException $e) {
    $errors = $e->getErrors();        // JsonSchemaErrors
    $errors->count();                 // バリデーション違反の件数
    $errors->first();                 // 最初の JsonSchemaError または null

    // 各エラーを反復処理
    foreach ($errors as $error) {
        echo $error->property;        // 例: "email"
        echo $error->message;         // 人間が読めるメッセージ
    }

    // フィールド別にグループ化 — APIエラーレスポンス構築に便利
    $byField = $errors->byProperty(); // array<string, list<JsonSchemaError>>

    // テンプレートで一括フォーマット
    $text = $errors->format('{property}: {message}\n');
} catch (JsonSchemaResponseException $e) {
    // リソースが自身の宣言したスキーマに合わないデータを返した
    // サーバー側のバグとして扱う
    error_log((string) $e);
}
```

### フレームワークのデフォルト動作

BEAR.Sunday 1.x のデフォルト `ThrowableHandler` は `JsonSchemaRequestException` を 400 の `application/vnd.error+json` レスポンスにマッピングします。API（JSON）コンテキストでは追加設定は不要です。

### JSON vs HTML コンテキスト

適切なレスポンス形式はコンテキストによって異なります。

- **API / JSON コンテキスト** — JSON エラーボディ（例: `{"code": 400, "message": "..."}`)を返す
- **HTML コンテキスト** — ブラウザユーザー向けに HTML エラーページをレンダリングする

本番アプリケーションでは、通常、各コンテキストに応じてデフォルトハンドラーをオーバーライドします。`AppThrowableHandler`（JSON）と `HtmlThrowableHandler`（HTML）を共有の `ExceptionStatusMapper` で使い分けるリファレンス実装は [BEAR.Examples](https://github.com/bearsunday/BEAR.Examples) を参照してください。

