---
layout: docs-ja
title: リソースリンク
category: Manual
permalink: /manuals/1.0/ja/resource_link.html
---

# リソースリンク

リソースは他のリソースをリンクすることができます。リンクには外部のリソースをリンクする外部リンク[^LO]と、リソース自身に他のリソースを埋め込む内部リンク[^LE]の2種類があります。

[^LE]: [embedded links](http://amundsen.com/hypermedia/hfactor/#le) 例）HTMLは独立した画像リソースを埋め込むことができます。
[^LO]: [out-bound links](http://amundsen.com/hypermedia/hfactor/#le) 例）HTMLは関連した他のHTMLにリンクを張ることができます。

## 外部リンク

リンクをリンクの名前の`rel`（リレーション）と`href`で指定します。`href`には正規のURIの他に[RFC6570 URIテンプレート](https://github.com/ioseb/uri-template)を指定することができます。

```php
    #[Link(rel: 'profile', href: '/profile{?id}')]
    public function onGet($id): static
    {
        $this->body = [
            'id' => 10
        ];
        return $this;
    }
```

上記の例では`href`で表されていて、`$body['id']`が`{?id}`にアサインされます。[HAL](https://stateless.group/hal_specification.html)フォーマットでの出力は以下のようになります。

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

## 内部リンク

リソースは別のリソースを埋め込むことができます。`#[Embed]`の`src`でリソースを指定します。内部リンクされたリソースも他のリソースを内部リンクしているかもしれません。その場合また内部リンクのリソースが必要で、それが再帰的に繰り返され**リソースグラフ**が得られます。

クライアントはリソースを何度もフェッチすることなく目的とするリソース群を一度に取得できます。[^di] 例えば顧客リソースと商品リソースをそれぞれ呼び出す代わりに、注文リソースで両者を埋め込みます。

[^di]: DIで依存関係のツリーがグラフになっているオブジェクトグラフと同様です。

```php
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    #[Embed(rel: 'sports', src: '/news/sports')]
    #[Embed(rel: 'weather', src: '/news/weather')]
    public function onGet(): static
```

埋め込まれるのはリソース**リクエスト**です。レンダリングの時に実行されますが、その前に`addQuery()`メソッドで引数を加えたり`withQuery()`で引数を置き換えることができます。`src`にはURI templateが利用でき、**リクエストメソッドの引数**がバインドされます（外部リンクと違って`$body`ではありません）。

```php
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    #[Embed(rel: 'website', src: '/website{?id}')]
    public function onGet(string $id): static
    {
        // ...
        $this->body['website']->addQuery(['title' => $title]); // 引数追加
```

### セルフリンク

`#[Embed]`でリレーションを`_self`としてリンクすると、リンク先のリソース状態を自身のリソース状態にコピーします。

```php
namespace MyVendor\Weekday\Resource\Page;

class Weekday extends ResourceObject
{
    #[Embed(rel: '_self', src: 'app://self/weekday{?year,month,day}')]
    public function onGet(string $id): static
    {
```

この例ではPageリソースがAppリソースの`weekday`リソースの状態を自身にコピーしています。

### HALでの内部リンク

[HAL](https://github.com/blongden/hal)レンダラーでは`_embedded`として扱われます。

## リンクリクエスト

クライアントはハイパーリンクで接続されているリソースをリンクすることができます。

```php
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

リンクは3種類あります。`$rel`をキーにして元のリソースの`body`にリンク先のリソースが埋め込まれます。

* `linkSelf($rel)` - リンク先と入れ替わります。
* `linkNew($rel)` - リンク先のリソースがリンク元のリソースに追加されます
* `linkCrawl($rel)` - リンクをクロールしてリソースグラフを作成します。

### クロール

クロールはリスト（配列）になっているリソースを順番にリンクを辿り、複雑なリソースグラフを構成することができます。クローラーがWebページをクロールするように、リソースクライアントはハイパーリンクをクロールしてリソースグラフを生成します。

#### クロール例

author, post, meta, tag, tag/nameがそれぞれ関連づけられているリソースグラフを考えてみます。このリソースグラフに **post-tree** という名前を付け、それぞれのリソースの`#[Link]`アトリビュートでハイパーリファレンス **href** を指定します。

最初に起点となるauthorリソースにはpostリソースへのハイパーリンクがあります。1:nの関係です。

```php
#[Link(crawl: "post-tree", rel: "post", href: "app://self/post?author_id={id}")]
public function onGet($id = null)
```

postリソースにはmetaリソースとtagリソースのハイパーリンクがあります。1:nの関係です。

```php
#[Link(crawl: "post-tree", rel: "meta", href: "app://self/meta?post_id={id}")]
#[Link(crawl: "post-tree", rel: "tag", href: "app://self/tag?post_id={id}")]
public function onGet($author_id)
{
```

tagリソースはIDだけでそのIDに対応するtag/nameリソースへのハイパーリンクがあります。1:1の関係です。

```php
#[Link(crawl: "post-tree", rel: "tag_name", href: "app://self/tag/name?tag_id={tag_id}")]
public function onGet($post_id)
```

それぞれが接続されました。クロール名を指定してリクエストします。

```php
$graph = $resource
    ->get
    ->uri('app://self/marshal/author')
    ->linkCrawl('post-tree')
    ->eager
    ->request();
```

リソースクライアントは`#[Link]`アトリビュートに指定されたクロール名を発見するとその**rel**名でリソースを接続してリソースグラフを作成します。

```php
var_export($graph->body);
array (
    0 =>
    array (
        'name' => 'Athos',
        'post' =>
        array (
            0 =>
            array (
                'author_id' => '1',
                'body' => 'Anna post #1',
                'meta' =>
                array (
                    0 =>
                    array (
                        'data' => 'meta 1',
                    ),
                ),
                'tag' =>
                array (
                    0 =>
                    array (
                        'tag_name' =>
                        array (
                            0 =>
                            array (
                                'name' => 'zim',
                            ),
                        ),
                    ), 
                    // ...
```

### DataLoader

Crawl操作では、リンクされた各リソースが個別にデータベースクエリを実行するとN+1問題が発生します。DataLoaderパターンは、複数のリクエストを単一のクエリにバッチ処理することでこの問題を解決します。

DataLoaderという名前は、[Facebook's DataLoader](https://github.com/graphql/dataloader)パターンに由来しています。

#### DataLoaderなし

```
Author (1クエリ)
└── Post × 3 (1クエリ、3件返却)
    └── Meta (3クエリ) ← N+1問題！
```

#### DataLoaderあり

```
Author (1クエリ)
└── Post × 3 (1クエリ)
    └── Meta (1バッチクエリ) ← 解決！
```

#### 実装方法

1. `DataLoaderInterface`を実装したDataLoaderクラスを作成します:

```php
use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\DataLoader\DataLoaderInterface;

class MetaDataLoader implements DataLoaderInterface
{
    public function __construct(
        private ExtendedPdoInterface $pdo
    ) {}

    public function __invoke(array $queries): array
    {
        // $queriesには各リクエストのクエリパラメータが含まれます
        // 例: [['post_id' => '1'], ['post_id' => '2'], ['post_id' => '3']]

        $postIds = array_column($queries, 'post_id');

        // IN句を使用した単一のバルククエリ
        $sql = "SELECT * FROM meta WHERE post_id IN (:post_ids)";
        return $this->pdo->fetchAll($sql, ['post_ids' => $postIds]);
    }
}

// Ray.MediaQueryパターンも使用できます:
// class MetaDataLoader implements DataLoaderInterface
// {
//     public function __construct(private MetaQueryInterface $query) {}
//     public function __invoke(array $queries): array
//     {
//         return $this->query->list(array_column($queries, 'post_id'));
//     }
// }
```

2. `#[Link]`アトリビュートでDataLoaderを指定します:

```php
#[Link(crawl: "post-tree", rel: "meta", href: "app://self/meta{?post_id}", dataLoader: MetaDataLoader::class)]
public function onGet($author_id)
```

DataLoaderは自動的に:
- Crawl中にすべてのクエリを収集
- DataLoaderを一度だけ呼び出し
- キーマッチングに基づいて結果を適切なリソースに分配

#### キーの推論

結果のマッチングに使用するキーは、URIテンプレートから自動的に推論されます:

| URIテンプレート | 推論されるキー |
|--------------|--------------|
| `{?post_id}` | `post_id` |
| `post_id={id}` | `post_id` |
| `{?post_id,locale}` | `post_id`, `locale` |

返される行には、適切な分配のためにキーカラムが含まれている必要があります。

#### 複数キー

複数のキーパラメータの場合、クエリですべてのキーを使用します:

```php
// URIテンプレート: app://self/translation{?post_id,locale}
// $queries: [['post_id' => '1', 'locale' => 'en'], ['post_id' => '1', 'locale' => 'ja']]

public function __invoke(array $queries): array
{
    // 両方のキーを使用してクエリを構築
    $sql = "SELECT * FROM translation WHERE (post_id, locale) IN (...)";
    // ...
}
```
