---
layout: docs-ja
title: 環境構築
category: Manual
permalink: /manuals/1.0/ja/setup.html
---
# 環境構築

BEAR.Sundayプロジェクトの開発環境構築方法を説明します。お使いの環境に応じて適切な方法を選択してください。

## 構築方法の選択

| 方法 | 対象OS                  | 特徴 | 推奨用途 |
|---|-----------------------|---|---|
| **malt** | macOS, WSL2, Linux    | Homebrew ベース、軽量、設定共有可能 | 個人開発、チーム開発 |
| **Docker** | macOS, Windows, Linux | コンテナベース、環境の完全再現 | チーム開発、CI/CD |
| **手動構築** | 全OS                   | 既存環境活用、細かい制御可能 | 既存インフラ活用 |

## malt による環境構築

### 概要

[malt](https://koriym.github.io/homebrew-malt/) は Homebrew をベースとした開発環境管理ツールです。

**maltの主な特徴:**
- **完全にローカル**: 全ての設定・データがプロジェクトディレクトリ内に保存
- **クリーンな削除**: プロジェクトフォルダを削除するだけで環境が完全に消える
- **専用ポートコマンド**: `mysql@3306`、`redis@6379` など専用ポートのエイリアス
- **グローバル汚染なし**: システム全体のMySQL/Redis等に影響を与えない
- **設定の可視化**: 全ての設定ファイルがプロジェクト内で管理・共有可能

### 前提条件

- macOS または Linux
- [Homebrew](https://brew.sh/) がインストール済み

### インストール

```bash
# 必要なHomebrew tapの追加
brew tap shivammathur/php        # PHP本体
brew tap shivammathur/extensions # PHP拡張
brew tap koriym/malt            # maltツール

# malt のインストール
brew install malt
```

**💡 Homebrew tapとは:**  
Homebrewの標準リポジトリ以外のパッケージ（Formula）を提供するサードパーティリポジトリです。`brew tap <name>` で追加すると、そのtapのパッケージを短縮名でインストールできるようになります。

### 基本的な使い方

```bash
# プロジェクトの初期化
malt init

# 設定ファイルの生成
malt create

# 依存関係のインストール（必要に応じて）
malt install

# サービス開始
malt start

# 環境変数の設定（各セッションで実行）
source <(malt env)
```

### 設定ファイル

malt は以下のファイルで環境を管理します：

```
malt.json          # malt の設定
malt/
  conf/
    my_3306.cnf    # MySQL設定
    php.ini        # PHP設定
    httpd_8080.conf # Apache設定
    nginx_80.conf   # Nginx設定
```

これらのファイルはプロジェクトに含めることで、チーム全体で環境を共有できます。

### サービス管理

```bash
# 状態確認
malt status

# 開始/停止/再起動
malt start
malt stop  
malt restart

# 特定のサービスのみ
malt start mysql
malt stop nginx
```

### データベース操作

```bash
# 専用ポートコマンド（推奨）
mysql@3306  # プロジェクト専用のMySQL接続
redis@6379  # プロジェクト専用のRedis接続

# 従来の方法
mysql --defaults-file=malt/conf/my_3306.cnf -h 127.0.0.1

# データベース作成例
mysql@3306 -e "CREATE DATABASE IF NOT EXISTS myapp"
```

**重要**: `mysql@3306` はプロジェクト専用の接続で、システムのグローバルなMySQLとは完全に分離されています。

## Docker による環境構築

### 概要

Dockerを使用することで、OS に依存しない一貫した開発環境を構築できます。

**Dockerの注意点:**
- **グローバルコマンドの競合**: システムの `mysql` コマンドはグローバルなMySQLを指す
- **コンテナ専用接続**: Dockerコンテナ内のMySQLには専用の接続方法が必要
- **ポート競合リスク**: 3306ポートなどがシステムサービスと競合する可能性

### 前提条件

- [Docker Desktop](https://www.docker.com/products/docker-desktop) がインストール済み
- Docker Compose が利用可能

### 基本的な docker-compose.yml

```yaml
version: '3.8'

services:
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: ""
      MYSQL_ALLOW_EMPTY_PASSWORD: "yes"
      MYSQL_DATABASE: myapp
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/init.sql:/docker-entrypoint-initdb.d/init.sql
    command: --default-authentication-plugin=mysql_native_password
    
  redis:
    image: redis:alpine
    ports:
      - "6379:6379"
      
  memcached:
    image: memcached:alpine
    ports:
      - "11211:11211"

volumes:
  mysql_data:
```

### 使用方法

```bash
# 環境の起動
docker-compose up -d

# 状態確認
docker-compose ps

# ログ確認
docker-compose logs mysql

# 環境の停止
docker-compose stop

# 完全削除（データも削除）
docker-compose down -v
```

### データベース接続

```bash
# ホストから接続（ポート指定必須）
mysql -h 127.0.0.1 -P 3306 -u root

# コンテナ内から接続（推奨）
docker-compose exec mysql mysql -u root
```

**注意**: システムに `mysql` がインストールされている場合、単に `mysql` と実行するとシステムのMySQLに接続してしまいます。Dockerコンテナのデータベースにアクセスするには、必ずホスト・ポート指定またはコンテナ内実行が必要です。

## 手動環境構築

### PHP環境

```bash
# macOS (Homebrew)
brew install php@8.4
brew install composer

# Ubuntu/Debian
sudo apt update
sudo apt install php8.4 php8.4-{cli,mysql,mbstring,xml,zip,curl}
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# CentOS/RHEL
sudo dnf install php php-{cli,mysql,mbstring,xml,zip,curl}
```

### MySQL環境

```bash
# macOS (Homebrew) 
brew install mysql@8.0
brew services start mysql@8.0

# Ubuntu/Debian
sudo apt install mysql-server-8.0
sudo systemctl start mysql

# CentOS/RHEL
sudo dnf install mysql-server
sudo systemctl start mysqld
```

### 拡張機能

開発に有用なPHP拡張：

```bash
# 拡張用tapの追加（初回のみ）
brew tap shivammathur/extensions

# Xdebug（デバッグ用）
brew install xdebug@8.4  # Homebrew
sudo apt install php8.4-xdebug  # Ubuntu

# Redis
brew install redis@8.4  # Homebrew  
sudo apt install php8.4-redis  # Ubuntu

# APCu（キャッシュ）
brew install apcu@8.4  # Homebrew
sudo apt install php8.4-apcu  # Ubuntu
```

## プロジェクト固有の設定

### 環境変数

プロジェクトルートに `.env` ファイルを作成：

```bash
# データベース接続（MySQL）
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=myapp  
DB_USER=root
DB_PASS=
DB_DSN=mysql:host=127.0.0.1;port=3306;dbname=myapp

# データベース接続（SQLite）
DB_DSN=sqlite:var/db.sqlite3

# キャッシュ（Redis）
REDIS_HOST=127.0.0.1:6379

# セッション（Memcached）  
MEMCACHED_HOST=127.0.0.1:11211
```

### データベースマイグレーション

Phinxを使用する場合：

```bash
# Phinx インストール
composer require --dev robmorgan/phinx

# 設定ファイル作成
./vendor/bin/phinx init

# マイグレーション作成
./vendor/bin/phinx create MyMigration

# マイグレーション実行
./vendor/bin/phinx migrate
```

## 開発サーバー

### PHP内蔵サーバー

```bash
# ポート8080で起動
php -S 127.0.0.1:8080 -t public

# Xdebug有効
php -dzend_extension=xdebug.so -S 127.0.0.1:8080 -t public
```

### malt サーバー

```bash
# ApacheまたはNginxを選択して起動
malt start apache   # Apache (http://127.0.0.1:8080)
malt start nginx    # Nginx (http://127.0.0.1:80)

# サービス確認
malt status

# 全てのサービス起動
malt start

# 全てのサービス停止
malt stop

# 特定サーバー停止
malt stop apache
malt stop nginx
```

## トラブルシューティング

### ポート競合

使用中のポートを確認：

```bash
# macOS/Linux
lsof -i :3306
netstat -tulpn | grep :3306

# プロセス終了
kill -9 PID
```

### PHP設定

設定ファイルの場所確認：

```bash
php --ini
php -m  # ロード済みモジュール確認
```

### MySQL接続エラー

```bash
# 接続テスト
mysql -h 127.0.0.1 -P 3306 -u root -p

# サービス状況確認（Linux）
sudo systemctl status mysql

# エラーログ確認
sudo tail -f /var/log/mysql/error.log
```

### malt 固有の問題

```bash
# サービス状況確認
malt status

# 設定リセット
malt stop
rm -rf malt/
malt create
malt start
```

## CI/CD での環境構築

### GitHub Actions

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: ""
          MYSQL_ALLOW_EMPTY_PASSWORD: yes
          MYSQL_DATABASE: test_db
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, xml, pdo_mysql, mysqli, intl, curl, zip
          
      - name: Install dependencies
        run: composer install
        
      - name: Run tests
        run: ./vendor/bin/phpunit
```

## 環境選択の指針

### 開発環境と本番環境の違い

**開発環境では透明性と直接性が重要**
- malt: `mysql@3306`で即座にプロジェクト専用DBアクセス、設定ファイル直接編集、**ネイティブファイルアクセス**
- Docker: 有名。コンテナ経由のアクセス、設定の確認が複雑、仮想化オーバーヘッド

開発環境の共有に関しては、どちらの方法も優秀です。

**本番環境では再現性と監視機能が重要**  
- Docker: どこでも同じ動作、豊富な監視ツールエコシステムで実質Docker一択

## まとめ

- **日常開発・学習**: malt推奨
- **チーム開発**: malt（設定共有）またはDocker
- **本番環境・CI/CD**: Docker一択
- **複雑な構成**: Docker

各環境の詳細な設定例は、個別のチュートリアルを参照してください。
