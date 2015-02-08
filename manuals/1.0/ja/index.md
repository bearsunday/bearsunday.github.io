---
layout: docs-ja
title: イントロダクション
category: Manual
permalink: /manuals/1.0/ja/
---

# BEAR.Sundayとは #

BEAR.Sundayの目標は、標準に準拠し高品質で粗結合なRESTfulアプリケーションの**フレームワーク**を提供することです。

## フレームワーク##

**BEAR.Sunday**は3つのオブジェクトフレームワークで構成されています。

`Ray.Di`は[依存関係逆転の原則](http://en.wikipedia.org/wiki/Dependency_inversion_principle)に基づいてオブジェクトを結びます。インターフェイスに対するプログラミングは、コンテキストによる振る舞いや将来の変更に柔軟です。

`Ray.Aop`は[アスペクト指向プログラミング](http://en.wikipedia.org/wiki/Aspect-oriented_programming)で本質的関心と横断的関心を結びます。アノテーションでログや認証を指定することができます。

`BEAR.Resource`は情報をリソースにして、ハイパーメディア制約で結びます。アプリケーション内部の情報もWebの世界と同じように統一されたURIとメソッドで扱うことができます。

## ライブラリ ##

BEAR.Sundayアプリケーションフレームワークは一般的なMVCフレームワークと違って認証やデータベースなどの特定の仕事のための独自のライブラリ（コンポーネント）を持ちません。
高品質なAuraフレームワークのライブラリや、Packagistで利用可能なライブラリを使用します。

