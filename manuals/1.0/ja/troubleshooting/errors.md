---
layout: docs-ja
title: エラーと警告
category: Manual
permalink: /manuals/1.0/ja/troubleshooting/errors.html
---

# エラーと警告

このページでは、アプリケーションや実行環境の変更が必要になる可能性がある
実行時エラーと警告を説明します。

## インジェクターキャッシュ警告
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
