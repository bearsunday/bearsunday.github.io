---
layout: docs-ja
title: セキュリティ
category: Manual
permalink: /manuals/1.0/ja/security.html
---

# セキュリティ

[bear/security](https://github.com/bearsunday/BEAR.Security)パッケージは、BEAR.Sundayアプリケーションのセキュリティ脆弱性を検出します。

## インストール

```bash
composer require --dev bear/security
```

## スキャンツール

| ツール | 機能 | 使用タイミング |
|--------|------|----------------|
| SAST | コード内の危険なパターンを検出 | 開発中 |
| DAST | アプリに攻撃リクエストを送信 | デプロイ前 |
| AI Auditor | AIがコードをレビュー | コードレビュー時 |
| Psalm Plugin | ユーザー入力の流れを追跡 | 開発中 |

## SAST

ソースコードをスキャンして危険なパターンを検出します：

```bash
./vendor/bin/bear.security-scan src
```

14種類の脆弱性を検出：

| カテゴリ | 例 |
|----------|-----|
| インジェクション | SQLインジェクション、コマンドインジェクション、XSS |
| アクセス制御 | パストラバーサル、オープンリダイレクト |
| 暗号 | 弱いハッシュアルゴリズム、ハードコードされた秘密鍵 |
| データ保護 | 安全でないデシリアライゼーション、XXE |
| セッション | セッション固定、CSRF |
| ネットワーク | SSRF、リモートファイルインクルージョン |

## DAST

実行中のアプリケーションに攻撃ペイロードを送信して脆弱性をテストします：

```bash
./vendor/bin/bear-security-dast 'MyVendor\MyApp' prod-app /path/to/app
```

テスト内容：

| テスト | 送信内容 |
|--------|----------|
| SQLインジェクション | `' OR '1'='1`, `; DROP TABLE` |
| XSS | `<script>alert(1)</script>` |
| コマンドインジェクション | `; ls -la`, `\| cat /etc/passwd` |
| パストラバーサル | `../../../etc/passwd` |
| セキュリティヘッダー | 欠落ヘッダーのチェック |

## AI Auditor

パターンマッチングでは検出できないセキュリティ問題をClaude AIが検出します：

```bash
# ANTHROPIC_API_KEY または Claude CLI認証が必要
./vendor/bin/bear-security-audit src
```

| 問題 | 説明 |
|------|------|
| IDOR | 認可チェックなしで他ユーザーのデータにアクセス |
| マスアサインメント | 未検証のフィールドを更新で受け入れ |
| レースコンディション | チェック時と使用時の競合 |
| ビジネスロジック | アプリケーション固有のセキュリティ欠陥 |

## Psalm Plugin

ユーザー入力（`onGet($id)`の`$id`など）を汚染されたものとマークしておいてコード内をどう流れるかを追跡します。適切なエスケープなしでデータベースクエリやHTML表示などに到達した場合に報告します。

### セットアップ

`psalm.xml`にプラグインとスタブを追加：

```xml
<?xml version="1.0"?>
<psalm
    xmlns="https://getpsalm.org/schema/config"
    errorLevel="1"
>
    <projectFiles>
        <directory name="src"/>
    </projectFiles>
    <stubs>
        <file name="vendor/bear/security/stubs/AuraSql.phpstub"/>
        <file name="vendor/bear/security/stubs/PDO.phpstub"/>
        <file name="vendor/bear/security/stubs/Qiq.phpstub"/>
    </stubs>
    <plugins>
        <pluginClass class="BEAR\Security\Psalm\ResourceTaintPlugin">
            <targets>
                <target>Page</target>
                <target>App</target>
            </targets>
        </pluginClass>
    </plugins>
</psalm>
```

`targets`で外部入力を受け取るリソースを指定します。`html`コンテキストでWebページを提供する場合は`Page`、`api`コンテキストでAPIを提供する場合は`App`を指定します。

### スタブ

スタブはサードパーティライブラリにテイントアノテーションを提供します：

| スタブ | 目的 |
|--------|------|
| `AuraSql.phpstub` | SQLクエリメソッドをテイントシンクとしてマーク |
| `PDO.phpstub` | PDOメソッドをテイントシンクとしてマーク |
| `Qiq.phpstub` | テンプレート出力をテイントシンクとしてマーク |

### 実行

テイント解析を実行：

```bash
./vendor/bin/psalm --taint-analysis
```

`composer.json`に便利スクリプトを追加：

```json
{
    "scripts": {
        "taint": "./vendor/bin/psalm --taint-analysis 2>&1 | grep -E 'Tainted' || true"
    }
}
```

これにより、テイントエラーのみが表示されます。

以下で実行：

```bash
composer taint
```

## GitHub Actions

CIパイプラインにセキュリティスキャンを追加：

```bash
cp vendor/bear/security/workflows/security-sast.yml .github/workflows/
```

このワークフローはプッシュとプルリクエストごとに実行されます：

| ジョブ | 機能 |
|--------|------|
| SAST Scan | コードをスキャンしてGitHub Securityタブに結果をアップロード |
| Psalm Taint | ユーザー入力の流れを追跡してGitHub Securityタブに結果をアップロード |

結果はリポジトリの **Security > Code scanning** セクションに表示されます。

## なぜ効果的か

BEAR.Sundayのアーキテクチャがセキュリティスキャンをより効果的にします：

- **明確なエントリーポイント**: すべてのエンドポイントは`onGet`、`onPost`メソッドを持つResourceObjectです。スキャナーはすべての入力を特定してデータフローを追跡できます。

- **隠れたマジックがない**: 依存関係はコンストラクタインジェクションで明示的です。スキャナーは完全なコードパスを解析できます。

- **フレームワークを理解するAI**: AI AuditorはBEAR.Sundayのパターンを理解し、一般的な脆弱性だけでなくビジネスロジックの欠陥も検出できます。
