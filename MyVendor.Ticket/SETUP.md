# Tutorial 2 環境構築ガイド

Tutorial 2を実行するための環境構築方法を説明します。お使いの環境に応じて選択してください。

## Option 1: malt (macOS / Linux)

[malt](https://github.com/koriym/homebrew-malt) は Homebrew を使った開発環境管理ツールです。

### インストール

```bash
# Homebrew tap の追加
brew tap koriym/homebrew-malt

# malt のインストール
brew install malt
```

### セットアップ

```bash
# プロジェクトディレクトリで実行
malt init
malt create
malt install  # 必要に応じて依存関係をインストール
malt start

# 環境変数の設定（セッション毎に必要）
source <(malt env)
```

### 使用方法

```bash
# MySQL接続例
mysql --defaults-file=malt/conf/my_3306.cnf -h 127.0.0.1

# サービス管理
malt start    # サービス開始
malt stop     # サービス停止
malt restart  # サービス再起動
```

### メリット
- Homebrew ベースで軽量
- 複数バージョンのPHP/MySQLを管理可能
- プロジェクトごとの設定ファイル共有可能
- チーム開発でも環境統一可能

## Option 2: Docker (クロスプラットフォーム)

Docker環境は Windows、Linux、macOS で利用できます。

### 前提条件

- [Docker Desktop](https://www.docker.com/products/docker-desktop) がインストール済み
- Docker Compose が利用可能

### セットアップ

```bash
# MySQL環境の起動
docker-compose up -d

# 環境変数ファイルの準備
cp .env.docker .env
```

### 使用方法

```bash
# MySQL接続例
mysql -h 127.0.0.1 -P 3306 -u root

# phpMyAdmin（ブラウザ）
# http://localhost:8081

# サービス管理
docker-compose up -d      # 開始
docker-compose stop       # 停止
docker-compose down       # 停止＋コンテナ削除
docker-compose down -v    # 停止＋コンテナ削除＋データ削除
```

### メリット
- クロスプラットフォーム対応
- 環境の再現性が高い
- チーム開発での環境統一が容易

## データベース初期化

どちらの方法でも、以下の手順でデータベースを準備します：

```bash
# マイグレーション実行
./vendor/bin/phinx migrate -c var/phinx/phinx.php

# テストデータ投入
mysql -h 127.0.0.1 -P 3306 -u root -e "INSERT INTO ticket (id, title, date_created) VALUES ('1', 'foo', '1970-01-01 00:00:00')" ticket
```

## トラブルシューティング

### ポートが使用中の場合

**malt の場合:**
```bash
malt stop
# 他のMySQLプロセスを確認
brew services list | grep mysql
```

**Docker の場合:**
`docker-compose.yml` のポート番号を変更：
```yaml
ports:
  - "3307:3306"  # 3306 → 3307 に変更
```

### 接続エラーの場合

- サービスが起動しているか確認
- ポート番号が正しいか確認  
- ファイアウォールの設定を確認

## 推奨環境

- **Homebrew ユーザー (macOS/Linux)**: malt または Docker
- **Windows ユーザー**: Docker
- **チーム開発**: malt または Docker（どちらも環境統一可能）
- **初心者**: Docker（セットアップが簡単）