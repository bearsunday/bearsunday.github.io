---
layout: docs-ja
title: セキュリティ
category: Manual
permalink: /manuals/1.0/ja/security.html
---

# セキュリティ <sup style="font-size:0.5em; color:#666; font-weight:normal;">Beta</sup>

セキュリティツールでアプリケーションをスキャンして脆弱性診断ができます。静的解析・動的テスト・テイント解析・AI監査など、アーキテクチャを理解した専用ツールが多方面から解析するため、汎用ツールでは難しい脆弱性も検知します。

## インストール

[bear/security](https://github.com/bearsunday/BEAR.Security)をインストールします。

```bash
composer require --dev bear/security
```

## スキャンツール

| ツール | 機能 | 使用タイミング |
|--------|------|----------------|
| SAST[^sast] | 静的解析でコード内の危険なパターンを検出 | 開発中 |
| DAST[^dast] | 動的解析でアプリに攻撃リクエストを送信 | デプロイ前 |
| AI Auditor | AIがコードをレビュー | コードレビュー時 |
| Psalm Plugin | ユーザー入力の流れを追跡 | 開発中 |

[^sast]: Static Application Security Testing
[^dast]: Dynamic Application Security Testing

## 設計方針: Recall優先（見逃しゼロ志向）

BEAR.Securityは**Recall（再現率）を最優先**する設計を採用しています。

| 方針 | 特徴 | リスク |
|------|------|--------|
| Precision優先 | 確実なものだけ報告 | 見逃しリスク高い |
| **Recall優先** | 疑わしいものは報告 | 偽陽性が出るが見逃しゼロ ✓ |

セキュリティスキャナーにおいて、**脆弱性の見逃し（False Negative）は致命的**です。一方、**偽陽性（False Positive）は確認すれば除外できます**。

### 推奨ワークフロー

```bash
# 1. SAST実行
vendor/bin/bear.security-scan src

# 2. 結果を確認し、脆弱性を修正
# 偽陽性には @security-ignore コメントを付与

# 3. AI Auditorでビジネスロジックの問題を検出
vendor/bin/bear-security-audit src
```

偽陽性の抑制例：

```php
$path = $this->buildPath($id); // @security-ignore path-traversal: $id is validated integer from router
```

`@security-ignore`を付与すると次回スキャンから抑制されます。

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

各脆弱性の詳細は[脆弱性リファレンス](https://bearsunday.github.io/BEAR.Security/issues/ja/)を参照してください。

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
# 方法1: APIキー
export ANTHROPIC_API_KEY=sk-ant-...
./vendor/bin/bear-security-audit src

# 方法2: Claude CLI（Maxプラン - APIキー不要）
claude auth login
./vendor/bin/bear-security-audit src
```

| 問題 | 説明 |
|------|------|
| IDOR | 認可チェックなしで他ユーザーのデータにアクセス |
| マスアサインメント | 未検証のフィールドを更新で受け入れ |
| レースコンディション | チェック時と使用時の競合 |
| ビジネスロジック | アプリケーション固有のセキュリティ欠陥 |

## Psalm Plugin（テイント解析）

テイント解析は、ユーザー入力を汚染された変数とマークし、その汚染がコード内をどう伝播するかを追跡する静的解析手法です。汚染されたデータが適切なサニタイズなしにSQLクエリやHTML出力に到達した場合、脆弱性として報告します。

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
        "security": "./vendor/bin/bear.security-scan src",
        "taint": "./vendor/bin/psalm --taint-analysis 2>&1 | grep -E 'Tainted' || true"
    },
    "scripts-descriptions": {
        "security": "Run SAST security scan",
        "taint": "Run Psalm taint analysis"
    }
}
```

以下で実行：

```bash
composer security
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

## アーキテクチャとセキュリティ

BEAR.Sundayのアーキテクチャがセキュリティスキャンをより効果的にします：

- **明確なエントリーポイント**: すべてのエンドポイントは`onGet`、`onPost`メソッドを持つResourceObjectです。スキャナーはすべての入力を特定してデータフローを追跡できます。

- **隠れたマジックがない**: 依存関係はコンストラクタインジェクションで明示的です。スキャナーは完全なコードパスを解析できます。

- **フレームワークを理解するAI**: AI AuditorはBEAR.Sundayのパターンを理解し、一般的な脆弱性だけでなくビジネスロジックの欠陥も検出できます。

## AIエージェント用プロンプト

AIコーディングアシスタントでbear/securityをセットアップするには、このプロンプトを使用してください：

```
Follow the setup instructions at:
https://raw.githubusercontent.com/bearsunday/BEAR.Skills/1.x/.claude/skills/bear-security-setup/SKILL.md
```
