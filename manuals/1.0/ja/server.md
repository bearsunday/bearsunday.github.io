---
layout: docs-ja
title: 高性能サーバー
category: Manual
permalink: /manuals/1.0/ja/swoole.html
---

# 高性能サーバー

BEAR.Sundayアプリケーションは、リクエストごとのブートストラップオーバーヘッドを排除する高性能PHPサーバー上で実行できます。このガイドでは、Swoole、RoadRunner、FrankenPHPの3つのサーバーオプションについて説明します。

## 概要

従来のPHP-FPMでは、各リクエストごとにアプリケーション全体をブートストラップします：

```text
Request -> Boot Framework -> Route -> Execute -> Response -> Shutdown
Request -> Boot Framework -> Route -> Execute -> Response -> Shutdown
Request -> Boot Framework -> Route -> Execute -> Response -> Shutdown
```

永続的なワーカーモードでは、アプリケーションは一度だけブートします：

```text
Boot Framework (once)
    |
Request -> Route -> Execute -> Response
Request -> Route -> Execute -> Response
Request -> Route -> Execute -> Response
```

これによりブートオーバーヘッドが排除され、レイテンシが大幅に低下し、スループットが向上します。

BEAR.Sundayのステートレスなリソース設計とイミュータブルなアーキテクチャは、永続ワーカー環境と相性が良く、グローバル状態の問題なくワーカーモードに移行できます。

## サーバー比較

| 機能 | Swoole | RoadRunner | FrankenPHP |
|------|--------|------------|------------|
| 言語 | C + PHP | Go + PHP | Go + PHP |
| ワーカーモード | あり | あり | あり |
| HTTP/2 | あり | あり | あり |
| HTTP/3 | なし | なし | あり |
| WebSocket | ネイティブ | ネイティブ | Caddy経由 |
| コルーチン | あり | なし | なし |
| ホットリロード | 手動 | あり | あり |
| メモリ制限 | 共有 | ワーカー単位 | ワーカー単位 |

## Dockerクイックスタート

[bear-sunday-servers](https://github.com/bearsunday/bear-sunday-servers)リポジトリは、3つのサーバーすべてのDocker設定を提供しています。

```bash
git clone https://github.com/bearsunday/bear-sunday-servers.git
cd bear-sunday-servers

# Swoole (port 8081)
cd swoole && docker compose up -d && curl http://localhost:8081/

# RoadRunner (port 8082)
cd roadrunner && docker compose up -d && curl http://localhost:8082/

# FrankenPHP (port 8080)
cd frankenphp && docker compose up -d && curl http://localhost:8080/
```

---

## Swoole

[Swoole](https://www.swoole.com/)は、イベント駆動の非同期I/Oを提供するコルーチンベースのPHP拡張です。

### 特徴

- **イベント駆動**: 非同期I/O処理
- **コルーチン**: スレッドなしの並行リクエスト処理
- **リクエスト分離**: コルーチンコンテキストによるリクエスト分離
- **高性能**: リクエストごとのブートオーバーヘッドを排除
- **メモリ効率**: ワーカー間でメモリを共有

### インストール

#### Swoole拡張（ext-swoole ^6.1）

```bash
pecl install swoole
```

またはソースからコンパイル：

```bash
git clone https://github.com/swoole/swoole-src.git && \
cd swoole-src && \
phpize && \
./configure && \
make && make install
```

`php.ini`に`extension=swoole.so`を追加してください。

#### BEAR.Swooleパッケージ

```bash
composer require bear/swoole
```

### ブートストラップスクリプト

`bin/swoole.php`を作成：

```php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';

$bootstrap = dirname(__DIR__) . '/vendor/bear/swoole/bootstrap.php';

$context = getenv('BEAR_CONTEXT') ?: 'prod-hal-app';
$ip = getenv('SWOOLE_IP') ?: '0.0.0.0';
$port = (int) (getenv('SWOOLE_PORT') ?: 8080);

exit((require $bootstrap)(
    $context,
    'MyVendor\MyProject',
    $ip,
    $port
));
```

### 実行

```bash
php bin/swoole.php
```

```text
Swoole http server is started at http://127.0.0.1:8080
```

### 環境変数

| 変数 | デフォルト | 説明 |
|------|-----------|------|
| `BEAR_CONTEXT` | prod-hal-app | BEAR.Sundayコンテキスト |
| `SWOOLE_IP` | 0.0.0.0 | サーバーバインドアドレス |
| `SWOOLE_PORT` | 8080 | サーバーポート |

### 開発時の注意

XdebugはSwooleのコルーチンと完全に互換性がありません。デバッグには：
- `var_dump()` / `error_log()`を使用
- またはSwooleを無効にしてPHPのビルトインサーバー + Xdebugを使用

Swooleは自動ホットリロードをサポートしていません。コード変更後は再起動が必要です：

```bash
# Dockerの場合
docker compose restart

# Dockerなしの場合
pkill -f swoole.php && php bin/swoole.php
```

### 並列実行

Swooleサーバーには2つの独立した関心事があります：

- **サーバー**: アプリケーションをどう実行するか（このページ）
- **並列実行**: 埋め込みリソースをどう並行処理するか

BEAR.Asyncを使用すると、`#[Embed]`リソースがSwooleコルーチンで自動的に並列実行されます。詳細は[並列リソース実行](async.html)を参照してください。

---

## RoadRunner

[RoadRunner](https://roadrunner.dev/)は、PSR-7 PHPワーカーを持つ高性能Goアプリケーションサーバーです。

### 特徴

- **Goアプリケーションサーバー**: 高性能プロセスマネージャー
- **PSR-7ワーカー**: 標準HTTPメッセージインターフェース
- **ビルトインメトリクス**: Prometheus互換エンドポイント
- **ホットリロード**: ファイル変更時の自動ワーカー再起動

### インストール

#### RoadRunnerバイナリ

[リリースページ](https://github.com/roadrunner-server/roadrunner/releases)からダウンロード、またはDockerを使用。

#### PHP依存関係

```bash
composer require spiral/roadrunner-http nyholm/psr7
```

### 設定

`.rr.yaml`を作成（[bin/worker.php](https://github.com/bearsunday/bear-sunday-servers/blob/main/roadrunner/bin/worker.php)の実装例を参照）：

```yaml
version: "3"

server:
  command: "php bin/worker.php"
  relay: pipes

http:
  address: "0.0.0.0:8082"
  pool:
    num_workers: 4
    max_jobs: 1000
    allocate_timeout: 60s
    destroy_timeout: 60s

logs:
  mode: production
  level: info
  output: stdout

status:
  address: "0.0.0.0:2112"
```

### 実行

```bash
./rr serve -c .rr.yaml
```

### 環境変数

| 変数 | デフォルト | 説明 |
|------|-----------|------|
| `BEAR_CONTEXT` | prod-hal-app | BEAR.Sundayコンテキスト |
| `MAX_REQUESTS` | 1000 | ワーカー再起動までのリクエスト数 |

### メトリクス

Prometheusメトリクスは`http://localhost:2112/metrics`で利用可能です。

---

## FrankenPHP

[FrankenPHP](https://frankenphp.dev/)は、ワーカーモードをサポートするCaddyベースのモダンPHPアプリケーションサーバーです。

### 特徴

- **ワーカーモード**: リクエストごとのアプリケーションブートコストを排除
- **HTTP/2 & HTTP/3**: Caddyによる自動HTTPS
- **本番環境対応**: OPcache JIT、マルチステージビルド
- **開発環境対応**: Xdebug、ホットリロード

### インストール

FrankenPHPは通常Dockerで使用します。スタンドアロンインストールについては、[FrankenPHPドキュメント](https://frankenphp.dev/docs/)を参照してください。

### Dockerで実行

```bash
docker run -v $PWD:/app -p 8080:8080 dunglas/frankenphp
```

### 環境変数

| 変数 | デフォルト | 説明 |
|------|-----------|------|
| `BEAR_CONTEXT` | prod-hal-app | BEAR.Sundayコンテキスト |
| `MAX_REQUESTS` | 1000 | ワーカー再起動までのリクエスト数 |
| `SERVER_NAME` | :8080 | リッスンアドレス |
| `FRANKENPHP_NUM_WORKERS` | 4 | ワーカープロセス数 |

### メモリ管理

- ワーカーは`MAX_REQUESTS`後に自動的に再起動してメモリリークを防止
- 各リクエスト後に`gc_collect_cycles()`を実行
- 無制限リクエストには`MAX_REQUESTS=0`を設定（開発時のみ）

---

## 本番デプロイ

本番デプロイには、[bear-sunday-servers](https://github.com/bearsunday/bear-sunday-servers)の各サーバーディレクトリに以下が含まれています：

- `Dockerfile` - 最適化された本番ビルド
- `docker-compose.prod.yml` - 本番設定
- ヘルスチェックエンドポイント
- OPcache最適化

本番デプロイの例：

```bash
cd swoole  # または roadrunner, frankenphp
docker compose -f docker-compose.prod.yml up -d
```

## 関連

- [並列リソース実行](async.html) - BEAR.Asyncによる`#[Embed]`リソースの並列実行

## 参考リンク

- [Swoole](https://www.swoole.com/) - [ドキュメント](https://wiki.swoole.com/)
- [RoadRunner](https://roadrunner.dev/) - [ドキュメント](https://roadrunner.dev/docs)
- [FrankenPHP](https://frankenphp.dev/) - [ドキュメント](https://frankenphp.dev/docs/)
- [BEAR.Swoole](https://github.com/bearsunday/BEAR.Swoole)
- [bear-sunday-servers](https://github.com/bearsunday/bear-sunday-servers)
