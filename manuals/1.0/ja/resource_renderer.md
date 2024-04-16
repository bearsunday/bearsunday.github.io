---
layout: docs-ja
title: レンダリングと転送
category: Manual
permalink: /manuals/1.0/ja/resource_renderer.html
---

# レンダリングと転送

<img src="https://bearsunday.github.io/images/screen/4r.png" alt="Resource object internal stucture">

ResourceObjectのリクエストメソッドではリソースの表現について関心を持ちません。コンテキストに応じて注入されたレンダラーがリソースの表現を生成します。同じアプリケーションがコンテキストを変えるだけでHTMLで出力されたり、JSONで出力されたりします。

## 遅延評価

レンダリングはリソースが文字列評価された時に行われます。

```php?start_inline

$weekday = $api->resource->get('app://self/weekday', ['year' => 2000, 'month'=> 1, 'day'=> 1]);
var_dump($weekday->body);
//array(1) {
//    ["weekday"]=>
//  string(3) "Sat"
//}

echo $weekday;
//{
//    "weekday": "Sat",
//    "_links": {
//    "self": {
//        "href": "/weekday/2000/1/1"
//        }
//    }
//}
```
## レンダラー

それぞれのResourceObjectはコンテキストによって指定されたその表現のためのレンダラーが注入されています。リソース特有のレンダリング行う時は`renderer`プロパティを注入またはセットします。

例）デフォルトで用意されているJSON表現のレンダラーをスクラッチで書くと

```php?start_inline
class Index extends ResourceObject
{
    #[Inject]
    public function setRenderer(RenderInterface $renderer)
    {
        $this->renderer = new class implements RenderInterface {
            public function render(ResourceObject $ro)
            {
                $ro->headers['content-type'] = 'application/json;';
                $ro->view = json_encode($ro->body);

                return $ro->view;
            }
        };
    }
}
```

## 転送

ルートオブジェクト`$app`にインジェクトされたリソース表現をクライアント（コンソールやWebクライアント）に転送します。通常、出力は`header`関数や`echo`で行われるますが、巨大なデータなどには[ストリーム転送](stream.html)が有効です。

リソース特有の転送を行う時は`transfer`メソッドをオーバーライドします。

```php
public function transfer(TransferInterface $responder, array $server)
{
    $responder($this, $server);
}
```

## リソースの自律性

リソースはリクエストによって自身のリソース状態を変更、それを表現にして転送する機能を各クラスが持っています。
