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

ユーザー入力（`onGet($id)`の`$id`など）がコード内をどう流れるかを追跡します。適切なエスケープなしでデータベースクエリに到達した場合に報告します：

```bash
./vendor/bin/psalm --taint-analysis
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
