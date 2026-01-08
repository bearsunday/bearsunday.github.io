---
layout: docs-ja
title: API Doc
category: Manual
permalink: /manuals/1.0/ja/apidoc.html
---

# API Doc

BEAR.ApiDocはAPIの設計を可視化し、人間とマシンの両方が理解できる形式で公開します。

- **HTML**: 開発者向けドキュメント
- **OpenAPI**: ツールチェーン連携（SDK生成、モックサーバー、Swagger UI）
- **JSON Schema**: クライアント側バリデーション、フォーム生成
- **ALPS**: 語彙の意味定義

コードとJSON Schemaから生成されるドキュメントは、常に実装と同期した正確なものになります。

## デモ

- [ApiDoc](https://bearsunday.github.io/BEAR.ApiDoc/)
- [OpenAPI](https://bearsunday.github.io/BEAR.ApiDoc/openapi/)

## 利用方法

### 動作環境

* PHP 8.2+

### インストール

    composer require bear/api-doc --dev

### 設定ファイルをコピー

    cp ./vendor/bear/api-doc/apidoc.xml.dist ./apidoc.xml

### 実行

```bash
composer docs        # 外部CSSでドキュメント生成
composer docs-dev    # インラインCSSでドキュメント生成（開発用）
composer docs-md     # Markdownドキュメント生成
composer docs-openapi # OpenAPI仕様を生成
```

## ソース

BEAR.ApiDocはPHP属性、メソッドシグネチャ、JSONスキーマから情報を取得してドキュメントを生成します。

### PHP属性

メソッドシグネチャと属性（例：`#[Title]`、`#[Description]`、`#[JsonSchema]`）を反映してドキュメントを生成します。

```php
use BEAR\ApiDoc\Annotation\Title;
use BEAR\ApiDoc\Annotation\Description;
use BEAR\Resource\Annotation\Link;

#[Title("User")]
#[Description("User resource")]
#[Link(rel: "friend", href: "/friend?id={id}")]
class User extends ResourceObject
{
    #[Title("Get User")]
    public function onGet(string $id): static
    {
    }
}
```

* 属性がない場合は、PHPDocまたはメソッドシグネチャから情報を取得します。
* 情報取得の優先順は属性、PHPDoc、JSONスキーマ、プロファイルの順です。

## 設定ファイル

設定はXMLで記述されます。最低限の指定は以下の通りです。

```xml
<?xml version="1.0" encoding="UTF-8"?>
<apidoc
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://bearsunday.github.io/BEAR.ApiDoc/apidoc.xsd">
    <appName>MyVendor\MyProject</appName>
    <scheme>app</scheme>
    <docDir>docs</docDir>
    <format>html</format>
</apidoc>
```

### 必須属性

#### appName

アプリケーションの名前空間

#### scheme

APIドキュメントにするリソーススキーム：`app`または`page`

#### docDir

出力ディレクトリ名

#### format

出力フォーマット：`html`、`md`（Markdown）、または`openapi`（OpenAPI 3.1）

### オプション属性

#### title

APIタイトル

```xml
<title>MyBlog API</title>
```

#### description

APIディスクリプション

```xml
<description>MyBlog API description</description>
```

#### links

リンク。`href`でリンク先URL、`rel`でその内容を表します。

```xml
<links>
    <link href="https://www.example.com/issue" rel="issue" />
    <link href="https://www.example.com/help" rel="help" />
</links>
```

#### alps

APIで使われる語句を定義する「ALPSプロファイル」を指定します。

```xml
<alps>alps.json</alps>
```

## プロファイル

BEAR.ApiDocはアプリケーションに追加情報を与える[RFC 6906 プロファイル](https://tools.ietf.org/html/rfc6906)の[ALPS](http://alps.io/)フォーマットをサポートします。

APIのリクエストやレスポンスのキーで使う語句をセマンティックディスクリプタ（意味的記述子）と呼びます。プロファイルでその辞書を作っておけば、リクエスト毎に語句を説明する必要がなくなります。語句の定義が集中することで表記揺れを防ぎ、理解共有を助けます。

以下は`firstName`、`familyName`というディスクリプタをそれぞれ`title`、`def`で定義した例です。`title`は言葉を記述して意味を明らかにしますが、`def`は[Schema.org](https://schema.org/)などのボキャブラリサイトで定義されたスタンダードな語句をリンクします。

ALPSプロファイルはXMLまたはJSONで記述します。

**profile.xml**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<alps
     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     xsi:noNamespaceSchemaLocation="https://alps-io.github.io/schemas/alps.xsd">
    <!-- Ontology -->
    <descriptor id="firstName" title="The person's first name."/>
    <descriptor id="familyName" def="https://schema.org/familyName"/>
</alps>
```

**profile.json**

```json
{
  "$schema": "https://alps-io.github.io/schemas/alps.json",
  "alps": {
    "descriptor": [
      {"id": "firstName", "title": "The person's first name."},
      {"id": "familyName", "def": "https://schema.org/familyName"}
    ]
  }
}
```

ApiDocに登場する語句の説明はPHPDoc > JSONSchema > ALPSの順で優先します。

## GitHub Actions

再利用可能なワークフローを使用して、APIドキュメントを自動的に生成・公開できます。

### セットアップ

1. bear/api-docをインストールし、`apidoc.xml`を設定
2. リポジトリに`.github/workflows/apidoc.yml`を作成：

```yaml
name: API Docs
on:
  push:
    branches: [main]

jobs:
  docs:
    uses: bearsunday/BEAR.ApiDoc/.github/workflows/apidoc.yml@v1
    with:
      format: 'html,openapi,alps'
      alps-profile: 'alps.json'
```

3. リポジトリ設定でGitHub Pagesを有効化：
   - Settings → Pages に移動
   - Source を「GitHub Actions」に設定

### 入力パラメータ

| 入力 | デフォルト | 説明 |
|------|-----------|------|
| `php-version` | `'8.2'` | PHPバージョン |
| `format` | `'html'` | カンマ区切り: html (apidoc), md, openapi, alps |
| `alps-profile` | `''` | ALPSプロファイルのパス（alps形式に必須） |
| `docs-path` | `'docs/api'` | 出力ディレクトリ |
| `publish-to` | `'github-pages'` | `github-pages`または`artifact-only` |

### 出力構造

```text
docs/
├── index.html          # apidoc
├── schemas/            # JSON Schema
│   └── *.json
├── openapi/
│   ├── openapi.json    # OpenAPI仕様
│   └── index.html      # Redocly HTML
└── alps/
    ├── alps.json       # ALPSプロファイル
    └── index.html      # ASD HTML
```

## リファレンス

* [ALPS](https://www.app-state-diagram.com/)
