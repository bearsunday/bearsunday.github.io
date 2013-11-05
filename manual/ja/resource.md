---
layout: default_ja
title: BEAR.Sunday | Resource Introduction
category: Resource
--- 

## Representational State Transfer

[http://ja.wikipedia.org/wiki/REST REST] (Representational State Transfer) は 2000年、HTTP 仕様の中心的作成者の 1 人である Roy Fielding の博士論文で初めて紹介されました。

リソースとは意味のある情報のかたまりです。リソースの状態から表現がつくられその表現がクライアントに渡ります。これがREST(Representational State Transfer=表現可能な状態の転送) です。例えば在庫情報はリソースです。その状態からHTMLページという表現がつくられWebブラウザに渡され在庫情報ページになります。これはRESTです。

## リソース指向アーキテクチャ (ROA)

BEARのリソースもHTTPアプリケーションと同じようにROA([http://en.wikipedia.org/wiki/Resource-oriented_architecture リソース指向設計]）の次の４つの特徴を持ちます。

 * アドレス可能性　**Addressability**
 * 統一インターフェイス **Unified Interface**
 * ステートレス **Statelessness**
 * 接続性 **Connectedness**

### アドレス可能性

リソースはWebのURLと同じようなURIを持ちます。


```
app://self/blog/posts/?id=3
```

```
page://self/index
```

MVCでモデルにあたるものがアプリケーションリソースです。
BEAR.Sundayでは内部APIとして機能しますがRESTでデザインされてるので外部APIの転換も用意です。BEAR.Sundayではページコントローラーの役割をする「ページ」もリソースです。
ページはURLに対応しアプリケーションリソースを呼び出し自身を構成します。

リソースは「それぞれの領域の関心」に注意を払います。
記事表示 *page* リソースは自らを構成するために「記事 *アプリケーション* リソース」を自身のコンテンツのとしてbodyプロパティにセットします
。ここでページ記事の詳細については関心を払いません。
アプリケーションリソースのリクエストを自身の一部に束縛するのがページの役割です。

`app`, `page`等それぞれの領域（情報体系）を表すスキーマは`http`等用意されてるものの他にも、ユーザーが登録可能です。例えば特定システムのスキーマを用意して、`office://self/room/meething/reservation/?room=3`等と旧来の情報システムに新しいURIを与えることができます。

独自のスキーマや別のサービスにリクエストをするための詳細は[resource_adapter リソースアダプター]をご覧ください。なお`self`は「自身のサービス」を表します。他のサービスのリソースを利用可能にするための識別です。

### 統一インターフェイス


リソースの状態（データ）はインターフェイスを通じて操作することができます。その操作を行うのがリソースクライアントです。

HTTPアプリケーションで考えてみましょう。リソースはHTTPクライアントでリクエストすることができますが、そのメソッドはGET/PUT/POST...等と既定の統一したものに限定されています。リソースに応じてそのメソッドが変わる事がありません。

BEAR.SundayのリソースもHTTPのメソッドに準じたインターフェイスを持ちます。

| **method** | **description**|
|--------|------------|
| GET | リソースの取得  |
| PUT | リソースの変更または作成 |
| POST | リソースの作成 |
| DELETE | リソースの削除 |
| OPTIONS | リソースアクセス方法の調査 |

#### GET 
リソースの読み出しです。このメソッドはリソースの状態には何の変化も与えません。副作用のない安全なメソッドです。

#### PUT 
リソースの変更、または作成を行います。このメソッドの特徴はリクエストを1度行っても、複数回行っても変わらないこ[http://ja.wikipedia.org/wiki/%E5%86%AA%E7%AD%89 冪等]の特徴がある事です。

#### POST 
リソースの作成を行います。リクエストを複数回行うとその回数に応じてリソースが作成されます。冪等性のないメソッドです。

#### DELETE 
リソースの消去です。PUTとおなじ冪等性があります。

#### OPTIONS 
リソースが利用可能なメソッドとパラメーターを調査します。`GET`と同じくリソースには影響を与えません。

### Stateless

これはリソースのリクエストにステート（状態）がないという事です。

例えば以下のようなオブジェクトモデルに対するアクセスはリクエストに状態があります。

```php
<?php
$user = new User($id);
$name = $user->getName(); // $idがセットされてる状態を前提にしている
```

リソースリクエストには状態がありません。

```php
<?php
$name = $resource
  ->get
  ->uri('app://self/user')
  ->eager
  ->request(['id' => 1])
  ->body['name'];
```

### 接続性

リソースは他のリソースと接続（リンク）することができます。リンクはリソース内部でカプセル化され、外部からはリソースからはリンクを辿ります。

htmlのaタグを考えてみてください。`href`で指定されたリンク情報をクライアントでは利用するだけです。
URIを組み立てるのはクライアントの仕事ではなくてサービス再度の仕事です。もしリンク先が変わったとしてもクライアントの利用方法は変わりません。

## REST and BEAR.Sunday

BEAR.Sundayの設計の中心はこのRESTです。
設計の中心にwebのアーキテクチャを用いて、アプリケーションをリソースの集合として構成します。

### アプリケーションリソース

MVCフレームワークでコントローラーがモデルにアクセスするように、BEAR.Sundayではページリソースがアプリケーションリソースにリクエストします。

モデルをオブジェクト設計されたものとして扱わず、RESTに従ったリソースとして扱います。
リソースはレイヤーとしても機能し、内部の処理やオブジェクトをラップします。
リソースクライアントはオブジェクトを直接扱う事なく、リクエストインターフェイスを通じて操作します。

#### オブジェクトモデル

| noun| verb |
|-----|:----:|
| cart | show, addItem, remoteItem, addCoupon, removeCoupon, increaseQuantity, decreaseQuantity |


#### REST

| noun | verb |
|------|------|
| cart | get, post, put, delete |
| cart/item | get, post, put, delete |
| cart/coupon | get, post, put, delete |


### REST リファレンスリンク

 * Wikipedia http://ja.wikipedia.org/wiki/REST
 * infoQ REST Introduction http://www.infoq.com/jp/articles/rest-introduction
 * Introduction to HTTP and REST http://net.tutsplus.com/tutorials/other/a-beginners-introduction-to-http-and-rest/
 * REST & ROA Best Practice http://www.ustream.tv/recorded/485516