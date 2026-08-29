---
layout: docs-ja
title: PhpStorm と Xdebug
category: Manual
permalink: /manuals/1.0/ja/phpstorm-xdebug.html
---

# PhpStorm と Xdebug

このガイドでは、BEAR.Skeleton で作成した BEAR.Sunday アプリケーションを Docker、PhpStorm、Xdebug でデバッグする手順を説明します。

Docker 側は PHP と Xdebug を提供します。ただし PhpStorm 側では、Docker Compose interpreter、PHP Server 名、パスマッピング、PHP Script のデバッグ構成を一度だけ設定する必要があります。

## 必須値

| 設定 | 値 |
| ---- | -- |
| デバッグポート | `9003` |
| Server name | `BEAR.Skeleton` |
| サーバー上のパス | `/app` |
| パスマッピング | プロジェクトルート → `/app` |
| `PHP_IDE_CONFIG` | `serverName=BEAR.Skeleton` |
| Xdebug client host | `host.docker.internal` |

Server name は重要です。Docker と PhpStorm で同じ値にしてください。

## Docker を起動する

アプリケーションコンテナを起動します。

```bash
docker compose up -d
```

デバッグ前に、通常の CLI 実行が成功することを確認します。

```bash
docker compose exec -T app php bin/page.php get /
```

期待する出力です。

```text
200 OK
Content-Type: application/hal+json
```

## Docker Compose interpreter を設定する

**Settings | PHP | CLI Interpreters** を開き、Docker Compose interpreter を追加します。

プロジェクトの `compose.yaml` と `app` サービスを指定します。

<img src="/images/screen/phpstorm-xdebug/docker-compose-interpreter.svg" alt="Docker Compose interpreter のスクリーンショット" style="max-width: 100%; height: auto;" />

## PHP Server を設定する

**Settings | PHP | Servers** を開き、サーバーを追加します。

以下の値を設定します。

- Name: `BEAR.Skeleton`
- Host: `localhost`
- Port: `8080`
- Debugger: `Xdebug`
- Use path mappings: 有効
- ホスト側のプロジェクトルート: ローカルのプロジェクトディレクトリ
- サーバー上の絶対パス: `/app`

<img src="/images/screen/phpstorm-xdebug/php-server.svg" alt="PHP Server パスマッピングのスクリーンショット" style="max-width: 100%; height: auto;" />

## PHP Script のデバッグ構成を作成する

**PHP Script** の Run/Debug configuration を作成します。

以下の値を設定します。

- Name: `page get /`
- File: `bin/page.php`
- Arguments: `get /`
- Interpreter: Docker Compose interpreter
- Server: `BEAR.Skeleton`

<img src="/images/screen/phpstorm-xdebug/run-configuration.svg" alt="PHP Script デバッグ構成のスクリーンショット" style="max-width: 100%; height: auto;" />

## bug ボタンでデバッグする

`bin/page.php` に breakpoint を置き、`page get /` 構成の bug ボタンをクリックします。

設定が正しければ breakpoint で停止します。

<img src="/images/screen/phpstorm-xdebug/breakpoint.svg" alt="PhpStorm breakpoint のスクリーンショット" style="max-width: 100%; height: auto;" />

Resume すると、コンソールは exit code `0` で終了します。

<img src="/images/screen/phpstorm-xdebug/console-success.svg" alt="PhpStorm デバッグコンソール成功時のスクリーンショット" style="max-width: 100%; height: auto;" />

## Xdebug mode の方針

`Dockerfile` や `compose.yaml` で `XDEBUG_MODE=off` を固定しないでください。

コンテナ側のデフォルトは以下にします。

```ini
xdebug.mode=develop
xdebug.start_with_request=trigger
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
```

この設定では、通常実行で debug session は開始されません。PhpStorm の bug ボタンをクリックしたときだけ、PhpStorm が次のようなオプション付きでスクリプトを起動します。

```text
-dxdebug.mode=debug -dxdebug.client_port=9003 -dxdebug.client_host=host.docker.internal
```

これにより、通常の CLI 実行を軽く保ちながら、デバッグ時だけ IDE が debug を有効化できます。

## トラブルシューティング

### PhpStorm が `xdebug.remote_host` の警告を出す

この警告は misleading な場合があります。Xdebug 3 では、まず以下を確認してください。

- Docker で `XDEBUG_MODE=off` が固定されていない。
- PhpStorm の Server name が正確に `BEAR.Skeleton` になっている。
- Docker に `PHP_IDE_CONFIG=serverName=BEAR.Skeleton` が設定されている。
- プロジェクトルートが `/app` にマッピングされている。
- PhpStorm が port `9003` で listen している。

### breakpoint で止まらない

まず、デバッガなしでアプリケーションが動くことを確認します。

```bash
docker compose exec -T app php bin/page.php get /
```

次に、PhpStorm のコンソールに表示される debug command を確認します。`-dxdebug.mode=debug` が含まれている必要があります。

### 通常実行が遅い

Xdebug は debug mode が有効なときにパフォーマンスへ影響します。コンテナのデフォルトは `xdebug.mode=develop` にして、PhpStorm からのデバッグセッション時だけ `debug` を有効にしてください。
