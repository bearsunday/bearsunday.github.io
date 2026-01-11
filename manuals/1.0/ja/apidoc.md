---
layout: docs-ja
title: API Doc
category: Manual
permalink: /manuals/1.0/ja/apidoc.html
---

# API Doc

アプリケーションがそのままドキュメントになります。

- **ApiDoc HTML**: 開発者向けドキュメント
- **OpenAPI 3.1**: ツールチェーン連携
- **JSON Schema**: 情報モデル
- **ALPS**: AIが理解できる語彙の意味論
- **llms.txt**: AI向けアプリケーション概要

## デモ

- [HTML](https://bearsunday.github.io/BEAR.ApiDoc/)
- [OpenAPI](https://bearsunday.github.io/BEAR.ApiDoc/openapi/)

## インストール

```bash
composer require bear/api-doc --dev
./vendor/bin/apidoc init
```

`init`コマンドは`composer.json`から`apidoc.xml`を生成します。必要に応じて編集してください。

```xml
<apidoc>
    <appName>MyVendor\MyProject</appName>  <!-- アプリケーションの名前空間 -->
    <scheme>app</scheme>                    <!-- app または page -->
    <docDir>docs/api</docDir>
    <format>html</format>                   <!-- html, openapi など -->
</apidoc>
```

`format`には`html`、`md`、`openapi`、`llms`をカンマ区切りで指定できます。

## 使い方

コマンドラインからドキュメントを生成します。

```bash
./vendor/bin/apidoc
```

### OpenAPI HTML生成

`openapi`形式を指定すると`openapi.json`が生成されます。これをHTMLに変換するにはRedocly CLIを使用します。

```bash
npm install -g @redocly/cli
redocly build-docs docs/api/openapi.json -o docs/api/openapi.html
```

### llms.txt

`llms`フォーマットは[llms.txt仕様](https://llmstxt.org/)に従った`llms.txt`を生成します。出力にはAPIエンドポイント、リソースオブジェクト、インフラインターフェイス（Query/Command）、SQL文、エンティティ定義が含まれます。

### Composerスクリプト

`composer.json`にスクリプトを追加すると便利です。

```json
{
    "scripts": {
        "docs": "./vendor/bin/apidoc"
    },
    "scripts-descriptions": {
        "docs": "Generate API documentation"
    }
}
```

```bash
composer docs
```

## GitHub Actions

mainブランチにプッシュすると、APIドキュメントが自動的に生成されGitHub Pagesに公開されます。再利用可能なワークフローがHTML生成、RedoclyによるOpenAPI変換、ALPS状態遷移図の作成を処理します。

```yaml
name: API Docs
on:
  push:
    branches: [main]

jobs:
  docs:
    uses: bearsunday/BEAR.ApiDoc/.github/workflows/apidoc.yml@v1
    with:
      format: 'html,openapi,llms'
```

GitHub Pagesを有効化: Settings → Pages → Source: "GitHub Actions"

### 入力パラメータ

| 入力 | デフォルト | 説明 |
|------|-----------|------|
| `php-version` | `'8.2'` | PHPバージョン |
| `format` | `'html,openapi,llms'` | カンマ区切り: html, md, openapi, llms |
| `docs-path` | `'docs'` | 出力ディレクトリ |
| `publish-to` | `'github-pages'` | `github-pages`または`artifact-only` |

### 出力構造

```text
docs/
├── index.html          # APIドキュメント
├── llms.txt            # AI向け概要
├── openapi.json        # OpenAPI仕様
└── schemas/
    ├── index.html      # スキーマ一覧
    └── *.json          # JSON Schema
```

## 設定ファイル

```xml
<?xml version="1.0" encoding="UTF-8"?>
<apidoc
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="https://bearsunday.github.io/BEAR.ApiDoc/apidoc.xsd">
    <appName>MyVendor\MyProject</appName>
    <scheme>app</scheme>
    <docDir>docs</docDir>
    <format>html</format>
    <alps>alps.json</alps>
</apidoc>
```

| オプション | 必須 | 説明 |
|-----------|------|------|
| `appName` | Yes | アプリケーションの名前空間 |
| `scheme` | Yes | `app`または`page` |
| `docDir` | Yes | 出力ディレクトリ |
| `format` | Yes | `html`, `md`, `openapi`, `llms` |
| `title` | | APIタイトル |
| `alps` | | ALPSプロファイルのパス |

## プロファイル

ALPSプロファイルはAPIの語彙を定義します。定義を集中させることで表記揺れを防ぎ、理解共有を助けます。

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

## Application as Documentation

コードこそが唯一の信頼できる情報源です。アプリケーションから生成されるドキュメントは実装と決して乖離しません。

[llms.txt](https://llmstxt.org/)はAIが読み取れるアプリケーション概要を提供します。AIエージェントがアプリケーションに触れると、コードから直接生成されたこの単一ドキュメントを通じて全体構造を素早く把握できます。エンドポイントを列挙する一般的なAPIリファレンスとは異なり、llms.txtはDan Klynの[情報アーキテクチャフレームワーク](https://understandinggroup.com/ia-theory/explaining-information-architecture)—Ontology、Taxonomy、Choreography—に従って完全な情報アーキテクチャを捉えます。ALPSの語彙意味論とJSON Schemaの情報モデルと組み合わせることで、AIエージェントはオペレーションだけでなく、その背後にある意味と構造を理解できます。

## リファレンス

- [BEAR.ApiDoc](https://github.com/bearsunday/BEAR.ApiDoc) - APIドキュメント生成ツール
- [ALPS](https://www.app-state-diagram.com/manuals/1.0/ja/) - Application-Level Profile Semantics
- [JSON Schema](https://json-schema.org/) - データ検証とドキュメンテーション
- [Redocly CLI](https://redocly.com/docs/cli/installation/) - OpenAPIからHTMLへの変換