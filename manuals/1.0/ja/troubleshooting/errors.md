---
layout: docs-ja
title: エラーと警告
category: Manual
permalink: /manuals/1.0/ja/troubleshooting/errors.html
---

# エラーと警告

このページでは、BEAR.Sunday の実行時診断をドメイン例外クラス名または
警告メッセージから探せるように整理します。

## 例外

### `BEAR\Package\Exception\InvalidCliContextException`
{: #invalid-cli-context-exception }

CLI コンテキストが CLI 引数を持たない server data を受け取ったときに
投げられます。

通常は、Web リクエストが CLI コンテキストで処理されていることを意味します。
Web リクエストには非 CLI コンテキストを使い、CLI コンテキストはコンソール
エントリーポイントにだけ使ってください。

親例外:

```text
BEAR\Package\Exception\InvalidContextException
```

## 警告

### インジェクターキャッシュ警告
{: #injector-cache-warning }

メッセージ:

```text
Failed to cache the injector(...). The cache adapter could not store the item.
```

`PackageInjector` はコンパイル済みインジェクターを設定されたキャッシュ
アダプターに保存し、その項目を読み戻して復元できることを確認します。

警告に `Serialization failed:` が含まれる場合、インジェクターまたは
bootstrap が生成したルートオブジェクトグラフにシリアライズできない
オブジェクトが含まれています。コンパイル済みインジェクターを
シリアライズできるように、オブジェクトグラフを修正してください。

シリアライズには成功しているのにキャッシュ項目を復元できない場合、
キャッシュアダプターまたは実行環境が期待どおりに項目を保存していないことを
意味します。`NullAdapter` のような意図的に永続化しないアダプターや、
現在の実行コンテキストで利用できないキャッシュバックエンドで発生します。
キャッシュを意図的に永続化しない場合、この警告は無視できます。
