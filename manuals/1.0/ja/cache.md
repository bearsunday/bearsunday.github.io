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

<img src="https://user-images.githubusercontent.com/529021/137062427-c733c832-0631-4a43-a6ee-4204e6be007c.png" alt="distributed cache">

キャッシュはREST制約に従った分散キャッシュシステムで、CPUだけでなくネットワーク資源も節約します。

PHPが直接扱うRedisやAPCなどの**サーバーサイドキャッシュ**、コンテンツ配信ネットワーク(CDN)として知られる**共有キャッシュ**、WebブラウザやAPIクライアントでキャッシュされる**クライアントサイドキャッシュ**、BEAR.SundayはこれらのキャッシュとモダンCDNを統合したキャッシングフレームワークを提供します。


## タグベースでのキャッシュ無効化

キャッシュコンテンツには依存性の問題があります。キャッシュコンテンツ(A)が他の単数または複数のキャッシュコンテンツ(B)を含んでいる場合、Bが更新されるとBのキャッシュとETag、Bを含んだAのキャッシュ、ETagの全てが更新されなければなりません。

BEAR.Sundayはサーバーサイド、共有キャッシュの双方で統合されたタグベースでのキャッシュ無効化が可能です。`#[Embed]`で埋め込まれたリソースに変更があると、埋め込んだリソースのキャッシュとETagが再生成されます。

HTTP標準のキャッシュのウイークポイントの１つはキャッシュ無効化の標準が無い事ですが、一部の先進的なCDNはこれをタグベースでの即時無効化の機能で補っていてこれをサポートします。

## ドーナッツキャッシュ

<img width="200" alt="donut caching" src="https://user-images.githubusercontent.com/529021/137097856-f9428918-5b76-4c0e-8cea-2472c15d82e9.png">

ドーナツキャッシュはキャッシュの最適化のための**部分キャッシュ** 技術の１つで、計算リソースを削減することができます。

あとで追記されるドーナツの穴を除いて、ドーナツ（ページ全体）をキャッシュします。例えばコメントを含むブログの記事ページではコメントが穴です。それ以外のビュー化された記事の部分をキャッシュして、穴部分のコメントを別途取得して埋め込みます。穴部分もそれ以外の部分も独立してキャッシュ管理され出力時に合成されます。

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

キャッシュ対象メソッドを選択したい場合はクラスに属性を指定しないで、メソッドに指定します。その場合はキャッシュ変更メソッドに`#[RefreshCache]`という属性を付与します。

```php
class Todo extends ResourceObject
{
    #[CacheableResponse]
    public function onPut(int $id = 0, string $todo)
    {
    }

    #[RefreshCache]
    public function onDelete(int $id = 0)
    {
    }	
}
```


これだけで、概要で紹介した全ての機能が適用されます。
イベントドリブン型コンテンツを想定して、時間(TTL)によるキャッシュの無効化は行われません

## TTL

TTLを`DonutRepositoryInterface::put()`で指定することもできます。

```php
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
### TTLの既定値

イベントドリブン型コンテンツでは、コンテンツが変更されたらキャッシュにすぐに反映されなければなりません。そのため、既定値のTTLはCDNのモジュールのインストールによって変わります。CDNがタグベースでのキャッシュ化を無効化をサポートしていればTTLは無期限（１年間）で、サポートの無い場合には10秒です。

つまりキャッシュ反映時間は、Fastlyなら即時、Akamaiなら数秒、それ以外なら10秒が期待される時間です。

カスタマイズするには`CdnCacheControlHeader`を参考に`CdnCacheControlHeaderSetterInterface`を実装して束縛します。


`ttl`はドーナツの穴以外のキャッシュ時間、`sMaxAge`はCDNのキャッシュ時間です。

## Purge

手動でキャッシュ破壊するには`DonutRepositoryInterface`のメソッドを利用します。

```php
interface DonutRepositoryInterface
{
    public function purge(AbstractUri $uri): void;
}
```

下記の場合`app://self/blog/comment`のレスポンスのキャッシュだけでなく、そのETag、`app://self/blog/comment`を依存にしている他の単数または複数のリソースのレスポンスのキャッシュとそのETagが破壊されます。

```php
// example
$this->repository->purge(new Uri('app://self/blog/comment'));
```


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

## メソッド


## 依存の指定

### リソースの依存

`#Embed`で他のリソースに含まないリソースの依存がある場合は`depends()`で明示的に示します。

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

## マルチCDN

CDNを多段構成にして、役割に応じたTTLを設定することもできます。例えばこの図では上流に多機能なCDNを配置して、下流にはコンベンショナルなCDNを配置しています。コンテンツのインバリデーションなどは上流のCDNに対して行い、下流のCDNはそれを利用するようにします。

<img width="344" alt="multi cdn diagram" src="https://user-images.githubusercontent.com/529021/137098809-ec949a15-8efb-4d03-9808-3be15523ade7.png">


## レスポンスヘッダー

CDNのキャッシュコントロールについてはBEAR.Sundayが自動で行いCDN用のヘッダーを出力します。クライアントのキャッシュコントロールはコンテンツに応じてResourceObjectの`$header`に記述します。

### キャッシュ不可

キャッシュができないコンテンツは必ずこのヘッダーを指定しましょう。

```php
ResponseHeader::CACHE_CONTROL => CacheControl::NO_STORE
```

### 条件付きリクエスト

サーバーにコンテンツ変更がないかを確認してから、キャッシュを利用します。サーバーサイドのコンテンツの変更は検知され反映されます。

```php
ResponseHeader::CACHE_CONTROL => CacheControl::NO_CACHE
```

### クライアントキャッシュ時間の指定

クライントでキャッシュされます。最も効率的なキャッシュですが、サーバーサイドでコンテンツが変更されても指定した時間に反映されません。

またブラウザのリロード動作ではこのキャッシュは利用されません。`<a>`タグで遷移、またはURL入力した場合にキャッシュが利用されます。

```php
ResponseHeader::CACHE_CONTROL => 'max-age=30'
```
APIでクライアントキャッシュを利用する場合にはRFC7234対応APIクライアントを利用します。

#### RFC7234対応クライアント

* iOS [NSURLCache](https://nshipster.com/nsurlcache/)
* Android [HttpResponseCache](https://developer.android.com/reference/android/net/http/HttpResponseCache)
* PHP [guzzle-cache-middleware](https://github.com/Kevinrob/guzzle-cache-middleware)
* JavaScript(Node) [cacheable-request](https://www.npmjs.com/package/cacheable-request)
* Go [lox/httpcache](https://github.com/lox/httpcache)
* Ruby [faraday-http-cache](https://github.com/plataformatec/faraday-http-cache)
* Python [requests-cache](https://pypi.org/project/requests-cache/)

### プライベート

キャッシュを他のクライアントと共有しない時には`private`を指定します。クライアントサイドのみキャッシュ保存されます。この場合サーバーサイドではキャッシュ指定をしないようにします。

```php
ResponseHeader::CACHE_CONTROL => 'private, max-age=30'
```

> 共用キャッシュを利用する場合でもほとんどの場合において`public`を指定する必要はありません。


## 用語

* [条件付きリクエスト](https://developer.mozilla.org/ja/docs/Web/HTTP/Conditional_requests)
* [ETag (バージョン識別子)](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/ETag)
* [イベントドリブン型コンテンツ](https://www.fastly.com/blog/rise-event-driven-content-or-how-cache-more-edge)
* [ドーナッツキャッシュ / 部分キャッシュ](https://www.infoq.com/jp/news/2011/12/MvcDonutCaching/)
* [サロゲートキー / タグベースの無効化](https://docs.fastly.com/ja/guides/getting-started-with-surrogate-keys)
* ヘッダー
  * [Cache-Control](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/Cache-Control)
  * [CDN-Cache-Control](https://blog.cloudflare.com/cdn-cache-control/)
  * [Vary](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/Vary)
  * [Stale-While-Revalidate (SWR)](https://www.infoq.com/jp/news/2020/12/ux-stale-while-revalidate/)
