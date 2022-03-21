---
layout: docs-ja
title: リソース
category: Manual
permalink: /manuals/1.0/ja/resource_link.html
---

# リソースリンク



## リンクリクエスト

クライアントはハイパーリンクで接続されているリソースをリンクすることができます。

```php?start_inline
$blog = $this
    ->resource
    ->get
    ->uri('app://self/user')
    ->withQuery(['id' => 1])
    ->linkSelf("blog")
    ->eager
    ->request()
    ->body;
```

リンクは３種類あります。`$rel`をキーにして元のリソースの`body`リンク先のリソースが埋め込まれます。

 * `linkSelf($rel)` リンク先と入れ替わります。
 * `linkNew($rel)` リンク先のリソースがリンク元のリソースに追加されます
 * `linkCrawl($rel)` リンクをクロールして"リソースツリー"を作成します。

### クロールリンク

ツリー構造をもつリソース、例えばユーザーからブログ、ブログから記事、記事からコメント、コメントから評価にリンクされているようなツリー構造のリソースは`linkCrawl()`で上手く取得できます。
詳しくは[クロール](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.ja.md#%E3%82%AF%E3%83%AD%E3%83%BC%E3%83%AB)をご覧ください。

## リンクアノテーション

他のリソースをハイパーリンクする`@Link`と他のリソースを内部に埋め込む`@Embed`アノテーションが利用できます。

### #[Link]

リンクを`rel`と`href`で指定します。`hal`コンテキストではHALのリンクフォーマットとして扱われます。

```php?start_inline
    #[Link rel: 'profile', href: '/profile{?id}']
    public function onGet($id): static
    {
        $this->body = [
            'id' => 10
        ];

        return $this;
    }
```

`@Link`の`href`のURIの`{?id}`は`$body`の値です。（メソッドの`$id`ではありません）[RFC6570 URI template](https://github.com/ioseb/uri-template)でURIが生成され、HALでの出力は以下のようになります。
```json
{
    "id": 10,
    "_links": {
        "self": {
            "href": "/test"
        },
        "profile": {
            "href": "/profile?id=10"
        }
    }
}
```

BEARのリソースリクエストでは`linkSelf()`, `linkNew`, `linkCrawl`の時にリソースリンクとして使われます。

```php?start_inline
use BEAR\Resource\Annotation\Link;

#[Link crawl: 'post-tree', rel: 'post', href: 'app://self/post?author_id={id}']
public function onGet($id = null) : ResourceObject
```

`linkCrawl`は`crawl`の付いたリンクを[クロール](https://github.com/koriym/BEAR.Resource#crawl)してリソースを集めます。

## 埋め込みリソース

リソースの中に`@Embed`の`src`で指定した別のリソースを埋め込むことができます。

```php?start_inline
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    #Embed(rel: 'sports', src: '/news/sports')]
    public function onGet(): static
```

埋め込まれるのはリソース**リクエスト**です。レンダリングの時に実行されますが、その前に`addQuery()`メソッドで引数を加えたり`withQuery()`で引数を置き換えることができます。

`src`にはURI templateが利用でき、**リクエストメソッドの引数**がバインドされます。（リソースの`$body`ではありません）

```php?start_inline
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    #[Embed(rel: 'website', src: '/website{?id}']
    public function onGet(string $id): static
    {
        // ...
        $this->body['website']->addQuery(['title' => $title]); // 引数追加
```

[HAL](https://github.com/blongden/hal)レンダラーでは`_embedded `として扱われます。



