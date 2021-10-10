---
layout: docs-ja
title: Cache
category: Manual
permalink: /manuals/1.0/ja/cache.html
---

# Cache

## 概要

BEAR.Sundayは従来のサーバーサイドでのキャッシュ有効時間（TTL）ベースの単純なキャッシュに加えて以下の機能をサポートします。

* 依存解決
* CDNコントロール
* イベントドリブン型コンテンツのキャッシュ更新
* HTTP条件付きリクエスト
* ドーナッツキャッシュ


## 分散キャッシュ

<img src="https://user-images.githubusercontent.com/529021/137062427-c733c832-0631-4a43-a6ee-4204e6be007c.png" alt="distributed cache diagram">

キャッシュはREST制約に従った分散キャッシュシステムで、CPUだけでなくネットワーク資源も節約します。

PHPが直接扱うRedisやAPCなどの**サーバーサイドキャッシュ**、コンテンツ配信ネットワーク(CDN)として知られる**共有キャッシュ**、WebブラウザやAPIクライアントでキャッシュされる**クライアントサイドキャッシュ**、BEAR.SundayはこれらのキャッシュとモダンCDNを統合したキャッシングフレームワークを提供します。


## タグベースでのキャッシュ無効化

キャッシュコンテンツには依存性の問題があります。キャッシュコンテンツ(A)が他の単数または複数のキャッシュコンテンツ(B)を含んでいる場合、Bが更新されるとBのキャッシュとETag、Bを含んだAのキャッシュ、ETagの全てが更新されなければなりません。

BEAR.Sundayはサーバーサイド、共有キャッシュの双方で統合されたタグベースでのキャッシュ無効化が可能です。`#[Embed]`で埋め込まれたリソースに変更があると、埋め込んだリソースのキャッシュとETagが再生成されます。

HTTP標準のキャッシュのウイークポイントの１つはキャッシュ無効化の標準が無い事ですが、一部の先進的なCDNはこれをタグベースでの即時無効化の機能で補っていてこれをサポートします。

## ドーナッツキャッシュ

ドーナツキャッシュは**部分キャッシュ** 技術の１つで、あとで追記される特定の“穴”を除いて、ページ全体をキャッシュします。例えばコメントを含むブログの記事ページではコメントが"穴"です。それ以外の部分をキャッシュして、穴部分のコメントを別途取得して埋め込みます。穴部分もそれ以外の部分も独立してコンテンツキャッシュ管理され、出力時に合成されます。


## イベントドリブン型コンテンツ

従来、CDNはアプリケーションロジックを必要とするコンテンツは「動的」であり、したがってCDNではキャッシュはできないと考えられてきました。FastlyやAkamaiなどのモダンなCDNは即時、または数秒以内でのタグベースでのキャッシュ無効化が可能になり、この考えは過去のものになろうとしています。

BEAR.Sundayはサーバーサイド、共有キャッシュの双方でリソースの更新とキャッシュ更新の同期が可能です。AOPが変更を検知し、ほとんどの場合キャッシュの更新は無指定かつ自動で行われます。

## 条件付きリクエスト

コンテンツの変更はAOPで管理され、コンテンツのエンティティタグ(ETag)は自動で更新されます。ETagを使ったHTTPの条件付きリクエストはPHPの実行を最小化し、計算リソースだけでなくネットワークリソースも最小化します。


# 利用法

キャッシュ対象のクラスに`#[CacheableResponse]`とアトリビュートを付与します。

```php
use BEAR\RepositoryModule\Annotation\DonutCache;

#[CacheableResponse]
class BlogPosting extends ResourceObject
{
    public $headers = [
        RequestHeader::CACHE_CONTROL => CacheControl::NO_CACHE
    ];

    #[Embed(rel: "comment", src: "page://self/html/comment")]
    public function onGet(int $id = 0)
    {
        $this->body['article'] = 'hello world';

        return $this;
    }

    public function onDelete(int $id = 0)
    {
        return $this;
    }
}
```

これだけで、概要で紹介した全ての機能が適用されます。
イベントドリブン型コンテンツを想定して、時間(TTL)によるキャッシュの無効化は行われません

## TTL

TTLを`DonutRepositoryInterface::put()`で指定することもできます。

```php
#[CacheableResponse]
class BlogPosting extends ResourceObject
{
    public function __construct(private DonutRepositoryInterface $repository)
    {}

    #[Embed(rel: "comment", src: "page://self/html/comment")]
    public function onGet()
    {
        $this->body['article'] = '1';

        $this->repository->put($this, ttl:10, sMaxAge:100);　

        return $this;
    }
}
```

`ttl`はドーナツの穴以外のキャッシュ時間、`sMaxAge`はCDNのキャッシュ時間です。

## CDN

特定CDN対応のモジュールをインストールするとベンダー固有のヘッダーが出力されます。

```php
$this->install(new FastlyModule())
$this->install(new AkamaiModule())
```

タグベースでのキャッシュ無効化を有効にするためには`PurgerInterface`を実装して束縛する必要があります。

```php
use BEAR\QueryRepository\PurgerInterface;

interface PurgerInterface
{
    public function __invoke(string $tag): void;
}
```

### TTLの既定値

イベントドリブン型コンテンツでは、コンテンツが変更されたらキャッシュにすぐに反映されなければなりません。そのため、既定値のTTLはCDNのモジュールのインストールによって変わります。CDNがタグベースでのキャッシュ化を無効化をサポートしていればTTLは無期限（１年間）で、サポートの無い場合には10秒です。

つまりキャッシュ反映時間は、Fastlyなら即時、Akamaiなら数秒、それ以外なら10秒が期待される時間です。

カスタマイズするには`CdnCacheControlHeader`を参考に`CdnCacheControlHeaderSetterInterface`を実装して束縛します。

## 依存の指定

### リソースの依存

リソースの依存性は`@Embed`で他のリソースに含むことで表しますが、`@Embed`で含まないリソースの依存がある場合に`CacheDependencyInterface`で明示的に示す事もできます。

```php
interface CacheDependencyInterface
{
    public function depends(ResourceObject $from, ResourceObject $to): void;
}
```

`$from`は`$to`リソースに依存している事を表します。

### 依存タグの指定

PURGE用の特定キーを指定するためには`PURGE_KEYS`ヘッダーで指定します。

```php
use BEAR\QueryRepository\Header;

class Foo
{
    public $headers = [
        Header::PURGE_KEYS => 'template_a campaign_b'
    ];
```
