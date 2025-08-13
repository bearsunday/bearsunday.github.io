---
layout: docs-ja
title: 環境構築
category: Manual
permalink: /manuals/1.0/ja/setup.html
---
# 環境構築

お使いの OS / チーム体制に合わせて、**malt / Docker / 手動構築**のいずれかを選べます。本書はそれぞれの特徴・導入手順・運用ポイントを1か所にまとめた実践ガイドです。

---

## 構築方法の選択

| 方法         | 対象OS                  | 特徴                                            | 推奨用途             |
| ---------- | --------------------- |-----------------------------------------------| ---------------- |
| **malt**   | macOS, WSL2, Linux    | Homebrew ベース、軽量、設定共有可能、ローカル完結<br>サービス一括管理コマンド | 個人開発、チーム開発       |
| **Docker** | macOS, Windows, Linux | コンテナベースで環境を完全再現、CI/CD と親和                     | チーム開発、CI/CD、本番近似 |
| **手動構築**   | 全OS                   | 既存環境をそのまま活用、細かい制御                             | 既存インフラ活用、制約が多い環境 |

---

## malt による環境構築

### 概要

**malt** は Homebrew をベースとした開発環境管理ツールです。プロジェクト直下に設定・データを集約し、ローカルで完結します。

**主な特徴**

* **完全ローカル**： 全ての設定・データはプロジェクト内に保存
* **クリーンな削除**：フォルダ削除＝環境削除
* **専用ポートコマンド**：`mysql@3306` / `redis@6379` などのエイリアス
* **グローバル汚染なし**：システムの MySQL/Redis 等に影響しない
* **設定の可視化**：設定ファイルをプロジェクト内で共有・レビュー可能
* **全サービス一括管理**：`malt start` / `malt stop` で関連サービスをまとめて起動・停止可能

### 前提条件

* macOS または Linux（WSL2 含む）
* Homebrew がインストール済み

### インストール

```bash
# Homebrew tap の追加
brew tap shivammathur/php
brew tap shivammathur/extensions
brew tap koriym/malt

# malt のインストール
brew install malt
```

### 基本操作（最短導線）

malt init && malt install && malt create && malt start
source <(malt env)

### 設定ファイル

```
malt.json          # malt の設定
malt/
  conf/
    my_3306.cnf     # MySQL 設定
    php.ini         # PHP 設定
    httpd_8080.conf # Apache 設定
    nginx_80.conf   # Nginx 設定
```

これらのファイルはプロジェクトに含めることで、チーム全体で環境を共有できます。

### サービス管理

```bash
# 状態確認
malt status

# 全サービス開始 / 停止 / 再起動
malt start
malt stop
malt restart

# 特定サービスのみ
malt start mysql
malt stop nginx
```

### データベース操作

```bash
mysql@3306  # プロジェクト専用 MySQL へ接続
redis@6379  # プロジェクト専用 Redis へ接続
mysql@3306 -e "CREATE DATABASE IF NOT EXISTS myapp"
```

> **重要**：`mysql@3306` は **プロジェクト専用接続** です。システムのグローバル MySQL と分離されます。

---

## Docker による環境構築

### 概要

Dockerを使用することで、OS に依存しない一貫した開発環境を構築できます。

**Dockerの注意点:**
- **グローバルコマンドの競合**: システムの `mysql` コマンドはグローバルなMySQLを指す
- **コンテナ専用接続**: Dockerコンテナ内のMySQLには専用の接続方法が必要
- **ポート競合リスク**: 3306ポートなどがシステムサービスと競合する可能性
- **MacOSファイルアクセス**: ホストとコンテナ間のファイルマウントでアクセス速度が低下、大量のファイル操作（ビルド、テスト）で顕著

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
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 3
    
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

---

## 手動環境構築

### PHP

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

### MySQL

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

### 開発に有用な PHP 拡張

```bash
# Xdebug（デバッグ）
brew install shivammathur/extensions/xdebug@8.4    # Homebrew
sudo apt install php8.4-xdebug                    # Ubuntu

# Redis
brew install shivammathur/extensions/redis@8.4    # Homebrew
sudo apt install php8.4-redis                     # Ubuntu

# APCu（キャッシュ）
brew install shivammathur/extensions/apcu@8.4     # Homebrew
sudo apt install php8.4-apcu                      # Ubuntu
```

---

## BEAR.Sunday 最短導入例

```bash
composer create-project bear/skeleton my-app
cd my-app
malt init && malt install && malt create && malt start
source <(malt env)
```

---

## プロジェクト固有の設定

### .env（例）

```dotenv
# MySQL
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=myapp
DB_USER=root
DB_PASS=
DB_DSN=mysql:host=127.0.0.1;port=3306;dbname=myapp

# SQLite（切替用）
DB_DSN=sqlite:var/db.sqlite3

# Redis
REDIS_HOST=127.0.0.1:6379

# Memcached
MEMCACHED_HOST=127.0.0.1:11211
```

### マイグレーション（Phinx 例）

```bash
composer require --dev robmorgan/phinx
./vendor/bin/phinx init
./vendor/bin/phinx create MyMigration
./vendor/bin/phinx migrate
```

---

## 開発サーバー

### PHP 内蔵サーバー

```bash
# 8080 番で起動
php -S 127.0.0.1:8080 -t public

# Xdebug 有効で起動
php -dzend_extension=xdebug.so -S 127.0.0.1:8080 -t public
```

### malt サーバー

```bash
# Apache / Nginx を選択して起動
malt start apache   # http://127.0.0.1:8080
malt start nginx    # http://127.0.0.1:80

# サービス確認
malt status

# 全サービス起動/停止
malt start
malt stop

# 個別停止
malt stop apache
malt stop nginx
```

---

## トラブルシューティング

### ポート競合

```bash
# macOS/Linux
lsof -i :3306
netstat -tulpn | grep :3306

# 該当プロセスの終了
kill -9 <PID>
```

### PHP 設定の確認

```bash
php --ini     # 読み込み設定
php -m        # ロード済みモジュール
```

### MySQL 接続エラー

```bash
# 接続テスト
mysql -h 127.0.0.1 -P 3306 -u root -p

# Linux のサービス状況
sudo systemctl status mysql

# エラーログ
sudo tail -f /var/log/mysql/error.log
```

### malt 固有の問題

```bash
# 状態確認
malt status

# 設定をリセット
malt stop
rm -rf malt/
malt create
malt start
```

### チーム開発での .gitignore

```
malt/logs/
malt/data/
malt/tmp/
```

---

## CI/CD（GitHub Actions 例）

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
        options: >-
          --health-cmd="mysqladmin ping" 
          --health-interval=10s 
          --health-timeout=5s 
          --health-retries=3

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, xml, pdo_mysql, mysqli, intl, curl, zip

      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Run tests
        run: ./vendor/bin/phpunit
```

---

## 環境選択の指針

開発環境では透明性と直接性が重要で、本番環境では再現性と監視機能が重要です。

* **日常開発・学習**：malt（即座に `mysql@3306`、設定が見える、ファイルアクセスが速い）
* **チーム開発**：malt（設定共有）または Docker（再現性重視）
* **本番環境・CI/CD**：**Docker 一択**（どこでも同じ挙動、監視ツールが豊富）
* **複雑な構成**：Docker（依存サービスの統合やスケールを前提に）


> 各環境の詳細な設定や BEAR.Sunday のベストプラクティスは、プロジェクトのチュートリアルやチーム規約に従ってください。
