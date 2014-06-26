---
layout: default_ja
title: BEAR.Sunday | はじめてのハイパーメディア
category: My First - Tutorial
---

# はじめてのハイパーメディア

## ハイパーメディアとはなんでしょうか

1962年、Ted Nelson氏が [**ハイパーテキスト**](http://en.wikipedia.org/wiki/Hypertext) を発案しました。これはテキストが他のテキストを参照するための参照リンクをテキストに埋め込むというもので、テキスト間を結びつける参照をハイパーリンクと呼びます。

最も有名で成功したハイパーテキスト実装がWWWです（<a>タグのhrefはハイパーリファレンスの略です）。

これをテキストに制限しないであらゆるメディアにしたのがハイパーメディアです。重要なのは相互参照（hyper reference）のためのリンクが埋め込まれてるということです。

また、PHPは *PHP: Hypertext Preprocessor* の略です。[PHP とは何の略ですか?](http://www.php.net/manual/ja/faq.general.php#faq.general.acronym))

## ハイパーメディアではないもの

例えばコーヒーショップでコーヒーをオーダーする、これをREST APIとして考えてみます。

飲み物を注文するREST APIが以下の様に与えられています。

| type   | value                               |
|--------|-------------------------------------|
| METHOD | POST                                |
| URI    | http://restbucks.com/order/{?drink} |
| Query  | drink=Drink Name                    |

この `API` を使って飲み物を注文します。これのAPIを使って `注文リソース` を作成（POST）します。

```
post http://restbucks.com/order/?drink=latte
```

注文リソースは作成され注文内容が返ってきました。

```json
{
    "drink": "latte",
    "cost": 2.5,
    "id": "5052",
}
```

これは **ハイパーメディアではありません**。情報を一意に現すURIが付いていないし参照リンクもありません。

## HAL - Hypertext Application Language

JSONは本来ハイパーメディアのためのフォーマットではありませんが、JSON+HALというメディアタイプを与えハイパーメディアとしてJSONを扱おうという [HAL - Hypertext Application Language](http://stateless.co/hal_specification.html) という [RFCドラフト規格](http://tools.ietf.org/html/draft-kelly-json-hal-00) があります。

BEAR.Sundayではリソースのレンダリングを `HalRenderer` にすることでHALフォーマットで出力することができます。

```json
{
    "drink": "latte",
    "cost": 2.5,
    "id": "1545",
    "_links": {
        "self": {
            "href": "app://self/restbucks/order?id=1545"
        },
        "payment": {
            "href": "app://self/restbucks/payment?id=1545"
        }
    }
}
```

これがHALのフォーマットで出力された注文リソースです。
自己のURIと関連するリンクの情報が `_links` に埋め込まれています。
注文と支払いの関係性をクライアントでなくサービスが保持しています。

サービス側はサービスの都合でリンク先を変える事ができます。
そのときにクライアントの利用に変更はありません。リンクを辿るだけです。
リンクを持つ事でデータは単なるフォーマットから自己記述的なハイパーメディアになりました。

## ハイパーリンクを追加する

リソースオブジェクトの `links` プロパティでこのように指定します。

{% highlight php startinline %}
    public $links = [
        'news' => [Link::HREF > 'page://self/news/today']
    ];
{% endhighlight %}

## クエリーにURIテンプレートを使う

URIが動的に決まる場合にはこのようにonPost等のメソッド内でクエリーをつくることもできます。

{% highlight php startinline %}
$this->links['friend'] = [Link::HREF => "app://self/sns/friend?id{$id}"];
{% endhighlight %}

`links` プロパティでこのようにURIテンプレートを指定することもできます。 

{% highlight php startinline %}
    public $links => [
        'friend' => [Link::HREF => 'app://self/sns/friend{?id}', Link::TEMPLATED => true]
    ];
{% endhighlight %}

ここに必要な変数 `{id}` はリソース `body` から取得されます。

## 試してみましょう

`$item` を指定すると注文リソースを作成するクラスです。

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\Resource\App\First\Hypermedia;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Link;

/**
 * Order resource
 */
class Order extends ResourceObject
{
    /**
     * @param string $item
     *
     * @return Order
     */
    public function onPost($item)
    {
        $this['item'] = $item;
        $this['id'] = date('is'); // min+sec
        return $this;
    }
}
{% endhighlight %}

これにハイパーリンクを加えるために `links` プロパティを設置します。

{% highlight php startinline %}
    public $links = [
        'payment' => [Link::HREF => 'app://self/first/hypermedia/payment{?id}', Link::TEMPLATED => true]
    ];
{% endhighlight %}

## コンソールでAPIリクエストしてみます

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php post app://self/first/hypermedia/order?item=book

200 OK
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Thu, 26 Jun 2014 07:26:01 GMT"]
[BODY]
item book,
id 2601,

[VIEW]
{
    "item": "book",
    "id": "2601",
    "_links": {
        "self": {
            "href": "http://localhost/app/first/hypermedia/order/?item=book"
        },
        "payment": {
            "href": "http://localhost/app/first/hypermedia/payment{/?id}",
            "templated": true
        }
    }
}
```

`payment` リンクが現れるようになりました。

## リンクをプログラムで利用する

リンクをコードで利用するためにはトレイト `AInject` を使い、`A` オブジェクトをインジェクトしてその `href` メソッドでリンクを取得します。
リソースのボディがURIテンプレートに合成されてリンクが取得できます。

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\Resource\App\First\Hypermedia;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\AInject;
use BEAR\Sunday\Inject\ResourceInject;

/**
 * Shop resource
 */
class Shop extends ResourceObject
{
    use ResourceInject;
    use AInject;

    /**
     * @param string $item
     * @param string $card_no
     *
     * @return Shop
     */
    public function onPost($item, $card_no)
    {
        $order = $this
            ->resource
            ->post
            ->uri('app://self/first/hypermedia/order')
            ->withQuery(['item' => $item])
            ->eager
            ->request();

        $payment = $this->a->href('payment', $order);

        $this->resource
            ->put
            ->uri($payment)
            ->withQuery(['card_no' => $card_no])
            ->request();

        $this->code = 204;

        return $this;
    }
}
{% endhighlight %}

Webページでリンクをクリックするだけで次のページに移れるように、次のリンクをサービス側がコントロールできるようになりました。
リンク先に変更があってもクライアントには変更がありません。
