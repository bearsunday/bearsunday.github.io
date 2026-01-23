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

## DataLoader <sup style="font-size:0.5em; color:#666; font-weight:normal;">Beta</sup>

> `bear/resource:1.x-dev`で利用可能

リソースをクロールする際、各子リソースが個別のクエリを発行するためN+1問題が発生します。DataLoaderは複数のリソースリクエストを1つの効率的なクエリにバッチ処理することでこの問題を解決します。

### N+1問題

```text
リクエスト: GET /author/1 with linkCrawl('post-tree')

[クエリ 1] SELECT * FROM author WHERE id = 1
  └─ 著者は3つの投稿を持っている

[クエリ 2] SELECT * FROM post WHERE author_id = 1
  └─ 3つの投稿を返す (id: 10, 11, 12)

[クエリ 3] SELECT * FROM meta WHERE post_id = 10  ← N+1の始まり
[クエリ 4] SELECT * FROM meta WHERE post_id = 11
[クエリ 5] SELECT * FROM meta WHERE post_id = 12

合計: 5クエリ (データ量に比例して増加！)
```

### DataLoader使用時

```text
[クエリ 1] SELECT * FROM author WHERE id = 1
[クエリ 2] SELECT * FROM post WHERE author_id = 1
[クエリ 3] SELECT * FROM meta WHERE post_id IN (10, 11, 12)  ← バッチ化！

合計: 3クエリ (データ量に関係なく一定)
```

### 使用方法

`#[Link]`アトリビュートに`dataLoader`パラメータを追加します:

```php
#[Link(crawl: 'post-tree', rel: 'meta', href: 'app://self/meta{?post_id}', dataLoader: MetaDataLoader::class)]
public function onGet($author_id)
{
```

### DataLoaderの実装

`DataLoaderInterface`を実装してクエリをバッチ処理します:

```php
use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\DataLoader\DataLoaderInterface;

class MetaDataLoader implements DataLoaderInterface
{
    public function __construct(
        private ExtendedPdoInterface $pdo
    ){}

    /**
     * @param list<array<string, mixed>> $queries
     * @return list<array<string, mixed>>
     */
    public function __invoke(array $queries): array
    {
        $postIds = array_column($queries, 'post_id');

        // バッチクエリ: SELECT * FROM meta WHERE post_id IN (...)
        return $this->pdo->fetchAll(
            'SELECT * FROM meta WHERE post_id IN (:post_ids)',
            ['post_ids' => $postIds]
        );
    }
}
```

ここでは説明のためにSQLを直接記述していますが、[Ray.MediaQuery](database_media.html)を使った実装も可能です。

### キーの推論

結果のマッチングに使用するキーは、URIテンプレートから自動的に推論されます:

| URIテンプレート | 推論されるキー |
|--------------|--------------|
| `{?post_id}` | `post_id` |
| `post_id={id}` | `post_id` |
| `{?post_id,locale}` | `post_id`, `locale` |

返される行には、適切な分配のためにキーカラムが含まれている必要があります。

### 複数キー

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
