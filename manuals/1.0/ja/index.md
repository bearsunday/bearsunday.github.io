---
layout: docs-ja
title: イントロダクション
category: Manual
permalink: /manuals/1.0/ja/
---
# BEAR.Sundayとは

BEAR.SundayとはPHPのWebアプリケーションフレームワークです。
BEAR.Sundayの目標は、標準に準拠し高品質で**API中心**のRESTfulアプリケーションの**フレームワーク**を提供することです。

## フレームワーク

**BEAR.Sunday**は3つのオブジェクトフレームワークで構成されています。

`Ray.Di`は[依存関係逆転の原則](http://en.wikipedia.org/wiki/Dependency_inversion_principle)に基づいてオブジェクトをインターフェイスで結びます。

`Ray.Aop`は[アスペクト指向プログラミング](http://en.wikipedia.org/wiki/Aspect-oriented_programming)で本質的関心と横断的関心を結びます。

`BEAR.Resource`はアプリケーションの情報や機能をリソースにして[REST制約](https://en.wikipedia.org/wiki/Representational_state_transfer)で結びます。

アプリケーションもWebと同じように統一されたメソッドとURIでリソースを操作します。強力なDIとAOPでREST API中心に構築されたクリーンなアプリケーションは変更に強く拡張性に優れます。


## ライブラリ

BEAR.Sundayアプリケーションフレームワークは一般的なMVCフレームワークと違って認証やデータベースなどの特定の仕事のための独自のライブラリを持ちません。
高品質なAuraフレームワークのライブラリや、Packagistで利用可能なサードパーティのライブラリを使用します。

## リソース指向パターン

BEAR.SundayはMVCパターンではなく、RESTfulアプリケーション用の[Resource-Method-Representation](http://www.peej.co.uk/articles/rmr-architecture.html)パターンのバリエーションです。

![4R](/images/screen/4r.png)

ステートレスなリクエストは`Method`で`Resource`状態をつくり、内部のRendererが`Representation`にしてレスポンスになります。

## コンポーネント

### Resource

WebのリソースをオブジェクトにしたものがResourceです。アプリケーション内で固有のURIやHTTPに準じたリクエストインターフェイスを持ち、オブジェクトはサービスとして機能します。

他のリソースを`@Embed`したり`@Link`することでハイパーメディアとして機能します。

### Method

Webからのリクエストはユニークにルートされます。HTTPメソッドに応じたパラメーターで呼ばれ、Method内で自身のリソースプロパティを構成します。
MVCのコントローラーのようにドメインモデルや他のリソースにアクセスする事もあります。

Methodの構造は[オニオンアーキテクチャ](http://www.infoq.com/jp/news/2014/11/ddd-onion-architecture)や[クリーンアーキテクチャ](http://blog.8thlight.com/uncle-bob/2012/08/13/the-clean-architecture.html)のバリエーションの１つです。
認証やバリデーション、ログなどのユースケースはアスペクト指向プログラミングでMethodに任意の層でラップされます。

### Representation

個別に注入されたRendererは文字列評価時にリソースの状態をRepresentationにします。MethodではRepresentationに関心を持ちません。Representationになったリソースはリソース内の`Responder`でクライアントに出力されます。

![Clean Method](/images/screen/clean-method.png)

## コラボレーション

 1. ウェブハンドラーはクライアントリクエストをアプリケーションのリソースリクエストに変更します。

 1. リクエストを受けた`Resource`内の`Method`は自身を構成します。

 1. 文字列評価で`Resource`内のレンダラーがリソース状態を`Representation`にします。

 1. `Resource`内のレスポンダーが`Representation`をクライアントにレスポンスとして返します。


## なぜ新しいパターン？

従来のパターンはオブジェクト指向パラダイムのアプリケーションをHTTPにマップしたものです。
純粋なコントローラーはHTTPやRESTに対して無知です。

新パターンではHTTPにマップするオブジェクトを作成します。RESTをフレームワークとして、適合するコンポーネントを作成します。

RESTの力を引き出し、HTTPをアプリケーションプロトコルとして扱うリソース指向のためのパターンです。
