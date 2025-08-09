# Tutorial 2 - Docker Setup Guide

Docker環境でTutorial 2を実行する手順です。

## 前提条件

- Docker Desktop がインストール済み
- Docker Compose が利用可能

## セットアップ手順

### 1. MySQL環境の起動

```bash
docker-compose up -d
```

### 2. 環境変数ファイルの準備

```bash
cp .env.docker .env
```

### 3. 依存関係のインストール

```bash
composer install
```

### 4. データベース作成とマイグレーション

```bash
# Phinx設定ファイルを使用
./vendor/bin/phinx migrate -c var/phinx/phinx.php
```

### 5. テストデータの投入

```bash
mysql -h 127.0.0.1 -P 3306 -u root -e "INSERT INTO ticket (id, title, date_created) VALUES ('1', 'foo', '1970-01-01 00:00:00')" ticket
```

### 6. 動作確認

```bash
# 個別チケット取得
php bin/app.php get '/ticket?id=1'

# チケット一覧取得  
php bin/app.php get '/tickets'

# チケット作成
php bin/app.php post '/tickets?title=test+ticket'
```

### 7. テスト実行

```bash
# ハイパーメディアテスト
./vendor/bin/phpunit tests/Hypermedia/WorkflowTest.php

# HTTPワークフローテスト
./vendor/bin/phpunit tests/Http/WorkflowTest.php
```

### 8. API文書生成

```bash
composer doc
```

## 追加機能

### phpMyAdmin
- URL: http://localhost:8081
- ユーザー: root
- パスワード: (空)

### サービスの停止

```bash
docker-compose down
```

### データベースデータの削除

```bash
docker-compose down -v
```

## トラブルシューティング

### ポート競合
もし3306ポートが使用中の場合、docker-compose.ymlの以下を変更：

```yaml
ports:
  - "3307:3306"  # 3307に変更
```

その場合、.envファイルも修正：

```
TKT_DB_DSN=mysql:host=127.0.0.1:3307;dbname=ticket
```

### MySQL接続エラー
コンテナが完全に起動するまで数秒待ってから接続してください。

```bash
# コンテナの起動状況確認
docker-compose ps
```