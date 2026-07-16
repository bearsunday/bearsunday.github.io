---
layout: docs-ja
title: リソース (v2)
category: Manual
permalink: /manuals/1.0/ja/resource.html
---

# リソース

BEAR.SundayアプリケーションはRESTfulなリソースの集合です。アプリケーションが扱う情報や機能のひとつひとつが、Webページと同じようにURIを持つ「リソース」として表されます。

すべてのリソースはGETやPOSTといったHTTPと共通の**統一インターフェイス**でアクセスされます。インターフェイスが統一されているので、同じリソースがWebからも、コンソールからも、他のリソースからのインプロセス呼び出しからも、同じ形で利用できます。アクセス方法ごとに別のコントローラーや入り口を用意する必要はなく、リソースの意味はHTTPの知識だけで理解できます。

## サービスとしてのオブジェクト

リソースをPHPで表したものが`ResourceObject`です。HTTPのメソッドがPHPのメソッドにマップされた、リソースの**サービスのためのオブジェクト**（Object-as-a-service）です。

```php
class Index extends ResourceObject
{
    public function onGet(int $a, int $b): static
    {
        $this->body = [
            'sum' => $a + $b  // $_GET['a'] + $_GET['b']
        ];

        return $this;
    }
}
```

リソースクラスはWebのURIと同じような`page://self/index`などのURIを持ち、HTTPのメソッドに準じた`onGet`、`onPost`などのonメソッドを持ちます。onメソッドは与えられたパラメーターから自身のリソース状態（`code`、`headers`、`body`）を決定し、`$this`を返します。ステートレスリクエストから、リソースの状態がリソース表現として生成され、クライアントに転送されます。（[Representational State Transfer](http://ja.wikipedia.org/wiki/REST)）

`code`と`headers`には既定値（`200`と空のヘッダー）があるので、上の例では`body`だけを決定しています。既定値と異なるレスポンスを返す時にだけ上書きします。

```php
class Todo extends ResourceObject
{
    public function onPost(string $id, string $todo): static
    {
        $this->code = 201; // 既定の200を上書き
        $this->headers = [
            'Location' => '/todo/new_id'
        ];

        return $this;
    }
}
```

## pageリソースとappリソース

リソースは役割によって2つのスキームに分かれます。

| スキーム | 役割 |
|--------|------|
| page | 外部に公開されるパブリックなリソース |
| app | 外部から直接アクセスできないプライベートなリソース |

Webやコンソールなどの外部からリクエストを受け取ったpageリソースは、appリソースをリクエストして自身のリソース状態を決定します。アプリケーションが「何をするか」というドメインの情報や機能はappリソースが、それを「どう見せるか」はpageリソースが受け持ち、関心が分離されます。

この分離のご利益はコンテキストを変える時に現れます。例えばHTMLアプリケーションではpageリソースがHTMLを出力し、appリソースはプライベートのままです。同じアプリケーションをモバイルアプリケーションのバックエンドにする時は、コンテキストの変更でappリソースをAPIとして外部公開しJSONを出力できます。リソースのコードはそのままに、公開範囲と表現だけが変わります。

## URI

URIはPHPのクラスにマップされています。アプリケーションではクラス名の代わりにURIを使ってリソースにアクセスします。

| URI | Class |
|-----+-------|
| page://self/ | Koriym\Todo\Resource\Page\Index |
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts?id=3 | Koriym\Todo\Resource\App\Blog\Posts |

* indexは省略可能です。

## メソッド

リソースはHTTPのメソッドに対応した6つのメソッドでアクセスすることができます。メソッドはCRUDとのマッピングではなく、リソース状態を変えない**安全**なものか、同じリクエストを繰り返しても結果が変わらない**冪等**なものかという特性で区別されます。

| メソッド | 意味 | [安全性](https://developer.mozilla.org/ja/docs/Glossary/safe) | [冪等性](https://developer.mozilla.org/ja/docs/Glossary/Idempotent) | [キャッシュ](https://developer.mozilla.org/ja/docs/Glossary/cacheable) |
|-|-|-|-|-|
| GET | リソースの表現の取得 | あり | あり | 可能 |
| POST | リクエストに含まれる表現の処理（新しいリソースの追加など） | なし | なし | 不可 |
| PUT | ペイロードによるリソースの置き換え、なければ作成 | なし | あり | 不可 |
| PATCH | リソースの部分的な変更 [^patch] | なし | なし | 不可 |
| DELETE | リソースの削除 | なし | あり | 不可 |
| OPTIONS | リクエストに必要なパラメーターとレスポンスに関する情報の取得 [^json-schema] | あり | あり | 不可 |

[^patch]: [https://www.rfc-editor.org/rfc/rfc5789](https://www.rfc-editor.org/rfc/rfc5789)

[^json-schema]: レスポンスの情報取得にはJsonSchemaの指定が必要です。

## パラメーター

レスポンスメソッドの引数には、変数名に対応したリクエストの値が渡されます。

```php
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
}
```

その他のメソッドや、Cookieなどの外部変数をパラメーターに渡す方法は[リソースパラメーター](resource_param.html)をご覧ください。

## レンダリングと転送

ResourceObjectのリクエストメソッドではリソースの表現について関心を持ちません。インジェクトされたレンダラーがリソースの表現を生成し、レスポンダーが出力します。詳しくは[レンダリングと転送](resource_renderer.html)をご覧ください。

## クライアント

インジェクトされたリソースクライアントを使って他のリソースをリクエストします。以下のリクエストは`app://self/blog/posts`リソースに`?id=1`というクエリーでリクエストを実行します。

```php
use BEAR\Resource\ResourceInterface;

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

        return $this;
    }
}
```

このリクエストはすぐに実行される`eager`リクエストです。[^history]

[^history]: 以前のバージョンで使われていた`$this->resource->get->uri('app://self/posts')->withQuery(['id' => 1])->eager->request();`（PHP 5.x以降）、`$this->resource->get->uri('app://self/posts')(['id' => 1]);`（PHP 7.x以降）などの歴史的表記も引き続き有効です。

## 遅延評価

リクエスト結果ではなくリクエストそのものを生成して、実行を遅延することもできます。

```php
$request = $this->resource->get->uri('app://self/posts')->withQuery(['id' => 1]); // callable
$posts = $request();
```

このリクエストをテンプレートやリソースに埋め込むと、遅延評価されます。つまり評価されない時はリクエストは行われず、実行コストがかかりません。

```php
$this->body = [
    'lazy' => $this->resource->get->uri('app://self/posts')->withQuery(['id' => 3])
];
```

`#[Embed]`アトリビュートを使うと、同じ遅延埋め込みを宣言的に記述できます。詳しくは[リソースリンク](resource_link.html)をご覧ください。

## キャッシュ

通常のTTLキャッシュと共に、RESTのクライアントキャッシュや、CDNを含めた高度な部分キャッシュ（ドーナッツキャッシュ）をサポートします。詳しくは[キャッシュ](cache.html)をご覧ください。また、従来の`@Cacheable`アノテーションに関しては以前の[リソース(v1)](resourcev1.html#リソースキャッシュ)ドキュメントをご覧ください。

## リンク

重要なREST制約の1つにリソースのリンクがあります。ResourceObjectは内部リンク、外部リンクの双方をサポートします。詳しくは[リソースリンク](resource_link.html)をご覧ください。

## BEAR.Resource

BEAR.Sundayのリソースオブジェクトの機能は独立したパッケージで単体使用もできます。BEAR.Resource[README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.ja.md)もご覧ください。

---
