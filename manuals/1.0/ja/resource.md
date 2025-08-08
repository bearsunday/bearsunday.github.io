---
layout: docs-ja
title: リソース (v2)
category: Manual
permalink: /manuals/1.0/ja/resource.html
---

# リソース

BEAR.SundayアプリケーションはRESTfulなリソースの集合です。

## サービスとしてのオブジェクト

`ResourceObject`はHTTPのメソッドがPHPのメソッドにマップされたリソースの**サービスのためのオブジェクト**（Object-as-a-service）です。ステートレスリクエストから、リソースの状態がリソース表現として生成され、クライアントに転送されます。（[Representational State Transfer](http://ja.wikipedia.org/wiki/REST)）

以下は、ResourceObjectの例です。

```php
class Index extends ResourceObject
{
    public $code = 200;
    public $headers = [];

    public function onGet(int $a, int $b): static
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
    public function onPost(string $id, string $todo): static
    {
        $this->code = 201; // ステータスコード
        $this->headers = [ // ヘッダー
            'Location' => '/todo/new_id'
        ];

        return $this;
    }
}
```

PHPのリソースクラスはWebのURIと同じような`page://self/index`などのURIを持ち、HTTPのメソッドに準じた`onGet`、`onPost`などのonメソッドを持ちます。onメソッドで与えられたパラメーターから自身のリソース状態`code`、`headers`、`body`を決定し、`$this`を返します。

## URI

URIはPHPのクラスにマップされています。アプリケーションではクラス名の代わりにURIを使ってリソースにアクセスします。

| URI | Class |
|-----+-------|
| page://self/ | Koriym\Todo\Resource\Page\Index |
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts?id=3 | Koriym\Todo\Resource\App\Blog\Posts |

* indexは省略可能です。

### スキーマ

`page`は外部公開するパブリックなリソース、`app`は外部からアクセスのできないプライベートなリソースです。Webやコンソールなどの外部からのリソースリクエストを受け取ったpageリソースは、appリソースをリクエストしてリソース状態を決定します。[^context]

[^context]: コンテキストを変えると、プライベートなappリソースを外部公開することもできます。例えばHTMLアプリケーションでpageリソースがHTMLを出力し（この時appリソースはプライベート）、モバイルアプリケーションではappリソースがAPIとして公開しJSONを出力することができます。

## メソッド

リソースはHTTPのメソッドに対応した6つのメソッドでアクセスすることができます。[^method]

[^method]: RESTのメソッドはCRUDとのマッピングではありません。リソース状態を変えない安全なものか、冪等性があるかなどで分けられます。

### GET

特定のリソースの表現をリクエストします。このメソッドはリソースの状態を変更することのない安全なメソッドです。

### POST

POSTメソッドは、リクエストに含まれる表現の処理を要求します。例えば、対象のURIに新しいリソースを追加することや、既存のリソースに表現を追加することなどです。PUTと違ってリクエストには冪等性がなく、連続した複数回の実行は同じ結果になりません。

### PUT

リクエストしたURIでリソースをリクエストのペイロードで置き換えます。対象のリソースが存在しない場合には作成します。
POSTと違って冪等性があります。

### DELETE

特定のリソースを削除します。冪等性があります。

### PATCH

リソースを部分的に変更します。冪等性は保証されません。[^patch]

[^patch]: [https://www.rfc-editor.org/rfc/rfc5789](https://www.rfc-editor.org/rfc/rfc5789)

### OPTIONS

リソースのリクエストに必要なパラメーターとレスポンスに関する情報を取得します。GETと同じように安全です。[^json-schema]

[^json-schema]: レスポンスの情報取得にはJsonSchemaの指定が必要です。

#### メソッドの特性一覧

| メソッド | [安全性](https://developer.mozilla.org/ja/docs/Glossary/safe) | [冪等性](https://developer.mozilla.org/ja/docs/Glossary/Idempotent) | [キャッシュ](https://developer.mozilla.org/ja/docs/Glossary/cacheable) | 
|-|-|-|-|
| GET | あり | あり | 可能
| POST | なし | なし | 不可
| PUT | なし | あり | 不可
| PATCH | なし | なし | 不可
| DELETE | なし | あり | 不可
| OPTIONS | あり | あり | 不可

## パラメーター

レスポンスメソッドの引数には、変数名に対応したリクエストの値が渡されます。

```php?start_inline
class Index extends ResourceObject
{
    // $_GET['id']が$idに
    public function onGet(int $id): static
    {
    }

    // $_POST['name']が$nameに
    public function onPost(string $name): static
    {
    }
```

その他のメソッドや、Cookieなどの外部変数をパラメーターに渡す方法は[リソースパラメーター](resource_param.html)をご覧ください。

## レンダリングと転送

ResourceObjectのリクエストメソッドではリソースの表現について関心を持ちません。インジェクトされたレンダラーがリソースの表現を生成し、レスポンダーが出力します。詳しくは[レンダリングと転送](resource_renderer.html)をご覧ください。

## クライアント

リソースクライアントを使用して他のリソースをリクエストします。以下のリクエストは`app://self/blog/posts`リソースに`?id=1`というクエリーでリクエストを実行します。

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

この他にも以下の歴史的表記があります。

```php
// PHP 5.x and up
$posts = $this->resource->get->uri('app://self/posts')->withQuery(['id' => 1])->eager->request();
// PHP 7.x and up
$posts = $this->resource->get->uri('app://self/posts')(['id' => 1]);
// getは省略可
$posts = $this->resource->uri('app://self/posts')(['id' => 1]);
```

## 遅延評価

これまでの例はリクエストをすぐに行う`eager`リクエストですが、リクエスト結果ではなくリクエストを生成し、実行を遅延することもできます。

```php
$request = $this->resource->get('app://self/posts'); // callable
$posts = $request(['id' => 1]);
```

このリクエストをテンプレートやリソースに埋め込むと、遅延評価されます。つまり評価されない時はリクエストは行われず、実行コストがかかりません。

```php
$this->body = [
    'lazy' => $this->resource->get('app://self/posts')->withQuery(['id' => 3])->request()
];
```

## キャッシュ

通常のTTLキャッシュと共に、RESTのクライアントキャッシュや、CDNを含めた高度な部分キャッシュ（ドーナッツキャッシュ）をサポートします。詳しくは[キャッシュ](cache.html)をご覧ください。また、従来の`@Cacheable`アノテーションに関しては以前の[リソース(v1)](resourcev1.html#リソースキャッシュ)ドキュメントをご覧ください。

## リンク

重要なREST制約の1つにリソースのリンクがあります。ResourceObjectは内部リンク、外部リンクの双方をサポートします。詳しくは[リソースリンク](resource_link.html)をご覧ください。

## BEAR.Resource

BEAR.Sundayのリソースオブジェクトの機能は独立したパッケージで単体使用もできます。BEAR.Resource[README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.ja.md)もご覧ください。

---
