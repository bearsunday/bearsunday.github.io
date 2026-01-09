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

## デモ

- [HTML](https://bearsunday.github.io/BEAR.ApiDoc/)
- [OpenAPI](https://bearsunday.github.io/BEAR.ApiDoc/openapi/)

## インストール

```bash
composer require bear/api-doc --dev
cp vendor/bear/api-doc/apidoc.xml.dist apidoc.xml
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
      format: 'html,openapi,alps'
      alps-profile: 'alps.json'
```

GitHub Pagesを有効化: Settings → Pages → Source: "GitHub Actions"

### 入力パラメータ

| 入力 | デフォルト | 説明 |
|------|-----------|------|
| `php-version` | `'8.2'` | PHPバージョン |
| `format` | `'html,openapi'` | カンマ区切り: html, md, openapi, alps |
| `alps-profile` | `''` | ALPSプロファイルのパス（alps形式に必須） |
| `docs-path` | `'docs/api'` | 出力ディレクトリ |
| `publish-to` | `'github-pages'` | `github-pages`または`artifact-only` |

### 出力構造

```
docs/
├── index.html          # APIドキュメント
├── schemas/
│   ├── index.html      # スキーマ一覧
│   └── *.json          # JSON Schema
├── openapi/
│   ├── openapi.json    # OpenAPI仕様
│   └── index.html      # Redocly HTML
└── alps/
    ├── alps.json       # ALPSプロファイル
    └── index.html      # ASD状態遷移図
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
| `format` | Yes | `html`, `md`, `openapi` |
| `title` | | APIタイトル |
| `alps` | | ALPSプロファイルのパス |

## プロファイル

[ALPS](http://alps.io/)プロファイルはAPIの語彙を定義します。定義を集中させることで表記揺れを防ぎ、理解共有を助けます。

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

コードこそが唯一の信頼できる情報源です。アプリケーションから生成されるドキュメントは実装と決して乖離しません。JSON Schemaは単なるエンドポイントのリストではなく情報モデルを公開し、クライアント側でのバリデーションやフォーム生成を可能にします。ALPSは語彙の意味論を定義し、AIエージェントがAPIの構造だけでなく意味も理解できるようにします。

## リファレンス

- [ALPS](https://www.app-state-diagram.com/manuals/1.0/ja/)