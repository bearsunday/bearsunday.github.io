---
layout: docs-ja
title: リソース
category: Manual
permalink: /manuals/1.0/ja/resource.html
---

# リソース

BEAR.SundayアプリケーションはRESTfulで、相互接続されたリソースの集合で構成されています。

## Object as a service

`ResourceObject`はHTTPのメソッドがPHPのメソッドにマップされています。 ステートレスリクエストからそのリソースの状態がリソース表現として転送されます。([Representational State Transfer)](http://ja.wikipedia.org/wiki/REST)

以下は、ResourceObjectの例です。

```php
class Index extends ResourceObject
{
    public $code = 200;
    public $headers = [];

    public function onGet(int $a, int $b) : ResourceObject
    {
        $this->body = [
            'sum' => $a + $b  // $_GET['a'] + $_GET['b']
        ];

        return $this;
    }
}
```

```php?start_inline
class Todo extends ResourceObject
{
    public function onPost(string $id, string $todo) : ResourceObject
    {
        $this->code = 201; // ステータスコード
        $this->headers = [ // ヘッダー
            'Location' => '/todo/new_id'
        ];

        return $this;
    }
}
```

PHPのリソースクラスはWebのURIと同じような`page://self/index`などのURIを持ち、HTTPのメソッドに準じた`onGet`, `onPost`, `onPut`, `onPatch`, `onDelete`インターフェイスを持ちます。

与えられたパラメーターから自身のリソース状態`code`,`headers`,`body`を変更し`$this`を返します。

## URI

URIはPHPのクラスはマップされています。アプリケーションではクラス名の代わりにURIを使ってリソースにアクセスします。

| URI | Class |
|-----+-------|
| page://self/ | Koriym\Todo\Resource\Page\Index |
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts?id=3 | Koriym\Todo\Resource\App\Blog\Posts |

* indexは省略可能です。

### スキーマ

`page`はpublicなリソース、`app`はprivateなリソースです。Webやコンソールなどの外部からのリソースリクエストを受け取った`page`リソースは`app` リソースをリクエストしてリソース状態を決定します。

page、appはMVCフレームワークのコントロラー、モデルの役割を果たしますが`app`をそのまま外部公開することも可能です。例えばHTMLアプリケーションでpageリソースがHTMLを出力し、モバイルアプリケーションではappリソースがAPIとしてJSONを出力することが可能です。

## メソッド

リソースはHTTPのメソッドに対応した6つのメソッドでアクセスすることができます。

### GET
リソースの状態を取得します。安全なメソッドです。このメソッドではリソースの状態を変えてはいけません。

### PUT
リクエストしたURIでリソースの状態を置き換えます。このメソッドは安全ではなくリソースの状態を変更します。
メソッドには[冪等性](https://ja.wikipedia.org/wiki/%E5%86%AA%E7%AD%89)がありメソッドを何度実行しても結果は同じです。

### PATCH
リソースを部分的に変更します。

### POST
リクエストしたURIに新しいリソースを追加します。このメソッドは安全ではなくリソースの状態を変更します。冪等性はなくリクエストの回数分リソースが追加されます。

### DELETE
リソースの削除をします。冪等性があります。

### OPTIONS
リソースのリクエストに必要なパラメーターとレスポンスに関する情報を取得します。安全なメソッドです。

## パラメーター

HTTPからリクエストされた時に`onGet`メソッドの引数には`$_GET`、`onPost`には`$_POST`が変数名に応じて渡されます。例えば下記の$idは$_GET['id']が渡されます。


```php?start_inline
class Index extends ResourceObject
{
    public function onGet(int $id): static
    {
```

## レンダリング

`ResourceObject`のリクエストメソッドではリソースの表現について関心を持ちません。
コンテキストによって`ResourceObject`にインジェクトされたリソースレンダラーがJSONやHTMLにレンダリングしてリソース表現にします。レンダリングはリソースが文字列評価された時に行われます。

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

リソース特有の表現が必要な時は以下のように独自のレンダラーをインジェクトします。

```php?start_inline
class Index extends ResourceObject
{
    #[Inject]
    public function setRenderer(#[Named('my_renderer')] RenderInterface $renderer)
    {
        parent::setRenderer($renderer);
    }
}
```

or

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

トランスファーがリソース表現をクライアント（コンソールやWebクライアント）に転送します。
`header`関数や`echo`で行われるますが、[ストリーム転送](stream.html)も可能です。

リソース特有の転送を行う時は以下のメソッドをオーバーライドします。

```php?start_inline
class Index extends ResourceObject
{
    // ...
    public function transfer(TransferInterface $responder, array $server)
    {
        $responder($this, $server);
    }
}
```

このようにリソースはリクエストによって自身のリソース状態を変更、それを表現にして転送する機能を各クラスが持っています。

## クライアント

インジェクトしたリソースクライアントを使用して他のリソースのリクエストします。

```php?start_inline
use BEAR\Sunday\Inject\ResourceInject;

class Index extends ResourceObject
{
    public function __construct(
    	private readonly ResourceInterface $resource
    ){}

    public function onGet(): static
    {
        $this->body = [
            'posts' => $this->resource->get('app://self/blog/posts', ['id' => 1])
        ];
    }
}
```
このリクエストは`app://self/blog/posts`リソースに`?id=1`というクエリーでリクエストを実行します。この他にも以下の歴史的表記があります。

```php
// PHP 5.x and up
$posts = $this->resource->get->uri('app://self/posts')->withQuery(['id' => 1])->eager->request();
// PHP 7.x and up
$posts = $this->resource->get->uri('app://self/posts')(['id' => 1]);
// getは省略可
$posts = $this->resource->uri('app://self/posts')(['id' => 1]);
```

## 遅延評価

以上はリクエストをすぐに行う`eager`リクエストですが、リクエスト結果ではなくリクエストを生成し、実行を遅延することもできます。

```php
$request = $this->resource->get('app://self/posts'); // callable
$posts = $request(['id' => 1]);
```

このリクエストをテンプレートやリソースに埋め込むと、遅延評価されます。つまり評価されない時はリクエストは行われず実行コストがかかりません。

```php
$this->body = [
    'lazy' => $this->resource->get('app://self/posts')->withQuery(['id' => 3])->requrest();
];
```

## BEAR.Resource

リソースクラスに関するより詳しい情報はBEAR.Resourceの[README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.ja.md)もご覧ください。

---
[^1]:[PUT メソッドのサポート](https://www.php.net/manual/ja/features.file-upload.put-method.php)参照
[^2]:[parse_str](https://www.php.net/manual/ja/function.parse-str.php)参照 
[^3]:publicプロパティとして定義しないで、`__set()`マジックメソッドでバリデーションをする事もできます。
