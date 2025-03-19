---
layout: docs-ja
title: Cache
category: Manual
permalink: /manuals/1.0/ja/cache.html
---

# Cache

> There are only two hard things in Computer Science: cache invalidation and naming things.
>
> -- Phil Karlton

## 概要

優れたキャッシュシステムは、ユーザー体験の質を本質的に向上させ、資源利用コストと環境負荷を下げます。BEAR.Sundayは従来のTTLによる単純なキャッシュに加えて、以下のキャッシュ機能をサポートしています：

* イベント駆動のキャッシュ無効化
* キャッシュの依存解決
* ドーナッツキャッシュとドーナッツの穴キャッシュ
* CDNコントロール
* 条件付きリクエスト

### 分散キャッシュフレームワーク

REST制約に従った分散キャッシュシステムは、計算資源だけでなくネットワーク資源も節約します。PHPが直接扱うRedisやAPCなどの**サーバーサイドキャッシュ**、コンテンツ配信ネットワーク（CDN）として知られる**共有キャッシュ**、WebブラウザやAPIクライアントでキャッシュされる**クライアントサイドキャッシュ**、BEAR.SundayはこれらのキャッシュとモダンCDNを統合したキャッシングフレームワークを提供します。

<img src="https://user-images.githubusercontent.com/529021/137062427-c733c832-0631-4a43-a6ee-4204e6be007c.png" alt="distributed cache">

## タグベースでのキャッシュ無効化

<img width="369" alt="dependency graph 2021-10-19 21 38 02" src="https://user-images.githubusercontent.com/529021/137910748-b6e95839-eeb7-4ade-a564-3cdcd5fdc09e.png">

コンテンツキャッシュには依存性の問題があります。コンテンツAがコンテンツBに依存し、BがCに依存している場合、Cが更新されるとCのキャッシュとETagだけでなく、Cに依存するBのキャッシュとETag、Bに依存するAのキャッシュとETagも更新されなければなりません。

BEAR.Sundayはそれぞれのリソースが依存リソースのURIをタグとして保持することで、この問題を解決します。`#[Embed]`で埋め込まれたリソースに変更があると、関係する全てのリソースのキャッシュとETagが無効化され、次のリクエストのためにキャッシュの再生成が行われます。

## ドーナッツキャッシュ

<img width="200" alt="donut caching" src="https://user-images.githubusercontent.com/529021/137097856-f9428918-5b76-4c0e-8cea-2472c15d82e9.png">

ドーナッツキャッシュは、キャッシュの最適化のための部分キャッシュ技術の1つです。コンテンツをキャッシュ可能な箇所とそうでない箇所に分けて合成します。

例えば「`Welcome to $name`」というキャッシュできないリソースが含まれるコンテンツを考えてみてください。キャッシュできない（do-not cache）部分と、その他のキャッシュ可能な部分を合成して出力します。

<img width="557" alt="image" src="https://user-images.githubusercontent.com/529021/139617102-1f7f436c-a1f4-4c6c-b90b-de24491e4c8c.png">

この場合、コンテンツ全体としては動的なので、ドーナッツ全体はキャッシュされません。そのため、ETagも出力されません。

## ドーナッツの穴キャッシュ

<img width="544" alt="image" src="https://user-images.githubusercontent.com/529021/139617571-31aea99a-533f-4b95-b3f3-6c613407d377.png">

ドーナッツの穴部分がキャッシュ可能な場合も、ドーナッツキャッシュと同じように扱えます。上記の例では、1時間に一度変更される天気予報のリソースがキャッシュされ、ニュースリソースに含まれます。

この場合、ドーナッツ全体（ニュース）としてのコンテンツは静的なので、全体もキャッシュされ、ETagも付与されます。このとき、キャッシュの依存性が発生します。ドーナッツの穴部分のコンテンツが更新された時に、キャッシュされたドーナッツ全体も再生成される必要があります。

この依存解決は自動で行われます。計算資源を最小化するため、ドーナッツ部分の計算は再利用されます。穴の部分（天気リソース）が更新されると、全体のコンテンツのキャッシュとETagも自動で更新されます。

### リカーシブ・ドーナッツ

<img width="191" alt="recursive donut 2021-10-19 21 27 06" src="https://user-images.githubusercontent.com/529021/137909083-2c5176f7-edb7-422b-bccc-1db90460fc15.png">

ドーナッツ構造は再帰的に適用されます。例えば、AがBを含み、BがCを含むコンテンツの場合、Cが変更されたときに、変更されたCの部分を除いて、AのキャッシュとBのキャッシュは再利用されます。AとBのキャッシュ、ETagは再生成されますが、A、Bのコンテンツ取得のためのDBアクセスやビューのレンダリングは行われません。

最適化された構造の部分キャッシュが、最小のコストでコンテンツ再生成を行います。クライアントはコンテンツのキャッシュ構造について知る必要がありません。

## イベントドリブン型コンテンツ

従来、CDNはアプリケーションロジックを必要とするコンテンツは「動的」であり、したがってCDNではキャッシュできないと考えられてきました。しかし、FastlyやAkamaiなどの一部のCDNは、即時または数秒以内でのタグベースでのキャッシュ無効化が可能になり、[この考えは過去のもの](https://www.fastly.com/blog/leveraging-your-cdn-cache-uncacheable-content)になろうとしています。

BEAR.Sundayの依存解決は、サーバーサイドだけでなく共有キャッシュでも行われます。AOPが変更を検知し、共有キャッシュにPURGEリクエストを行うことで、サーバーサイドと同じように共有キャッシュ上の関連キャッシュの無効化が行われます。

## 条件付きリクエスト

<img width="468" alt="conditional request" src="https://user-images.githubusercontent.com/529021/137151061-8d7a5605-3aa3-494c-91c5-c1deddd987dd.png">

コンテンツの変更はAOPで管理され、コンテンツのエンティティタグ（ETag）は自動で更新されます。ETagを使ったHTTPの条件付きリクエストは計算資源の利用を最小化するだけでなく、`304 Not Modified`を返すだけの応答はネットワーク資源の利用も最小化します。

# 利用法

キャッシュ対象のクラスにドーナッツキャッシュの場合（埋め込みコンテンツがキャッシュ不可能な場合）は`#[DonutCache]`、それ以外の場合は`#[CacheableResponse]`とアトリビュートを付与します：

```php
use BEAR\RepositoryModule\Annotation\CacheableResponse;

#[CacheableResponse]
class BlogPosting extends ResourceObject
{
    public $headers = [
        RequestHeader::CACHE_CONTROL => CacheControl::NO_CACHE
    ];

    #[Embed(rel: "comment", src: "page://self/html/comment")]
    public function onGet(int $id = 0): static
    {
        $this->body['article'] = 'hello world';
        return $this;
    }

    public function onDelete(int $id = 0): static
    {
        return $this;
    }
}
```

キャッシュ対象メソッドを選択したい場合は、クラスにアトリビュートを指定しないで、メソッドに指定します。その場合は、キャッシュ変更メソッドに`#[RefreshCache]`というアトリビュートを付与します：

```php
class Todo extends ResourceObject
{
    #[CacheableResponse]
    public function onPut(int $id = 0, string $todo): static
    {
    }

    #[RefreshCache]
    public function onDelete(int $id = 0): static
    {
    }
}
```

どちらかの方法でアトリビュートを付与すると、概要で紹介した全ての機能が適用されます。イベントドリブン型コンテンツを想定してデフォルトでは時間（TTL）によるキャッシュの無効化は行われません。`#[DonutCache]`の場合はコンテンツ全体はキャッシュされず、`#[CacheableResponse]`の場合はされることに注意してください。

## TTL

TTLの指定は`DonutRepositoryInterface::put()`で行います。`ttl`はドーナツの穴以外のキャッシュ時間、`sMaxAge`はCDNのキャッシュ時間です：

```php
use BEAR\RepositoryModule\Annotation\CacheableResponse;

#[CacheableResponse]
class BlogPosting extends ResourceObject
{
    public function __construct(private DonutRepositoryInterface $repository)
    {
    }

    #[Embed(rel: "comment", src: "page://self/html/comment")]
    public function onGet(): static
    {
        // process ...
        $this->repository->put($this, ttl: 10, sMaxAge: 100);　
        return $this;
    }
}
```

### TTLの既定値

イベントドリブン型コンテンツでは、コンテンツが変更されたらキャッシュにすぐに反映されなければなりません。そのため、既定値のTTLはCDNのモジュールのインストールによって変わります。

CDNがタグベースでのキャッシュ無効化をサポートしていれば、TTLは無期限（1年間）です。サポートのない場合は10秒です。キャッシュ反映時間は、Fastlyなら即時、Akamaiなら数秒、それ以外なら10秒が期待される時間です。

カスタマイズするには`CdnCacheControlHeader`を参考に`CdnCacheControlHeaderSetterInterface`を実装して束縛します。

## キャッシュ無効化

手動でキャッシュを無効化するには`DonutRepositoryInterface`のメソッドを用います。指定されたキャッシュだけでなく、そのETag、依存にしている他のリソースのキャッシュとそのETagが、サーバーサイドおよび可能な場合はCDN上のキャッシュも共に無効化されます：

```php
interface DonutRepositoryInterface
{
    public function purge(AbstractUri $uri): void;
    public function invalidateTags(array $tags): void;
}
```

### URIによる無効化

```php
// example
$this->repository->purge(new Uri('app://self/blog/comment'));
```

### タグによる無効化

```php
$this->repository->invalidateTags(['template_a', 'campaign_b']);
```

### CDNでタグの無効化

CDNでタグベースでのキャッシュ無効化を有効にするためには、`PurgerInterface`を実装して束縛する必要があります：

```php
use BEAR\QueryRepository\PurgerInterface;

interface PurgerInterface
{
    public function __invoke(string $tag): void;
}
```

### 依存タグの指定

PURGE用のキーを指定するためには`SURROGATE_KEY`ヘッダーで指定します。複数文字列の場合はスペースをセパレータとして使用します：

```php
use BEAR\QueryRepository\Header;

class Foo
{
    public $headers = [
        Header::SURROGATE_KEY => 'template_a campaign_b'
    ];
}
```

`template_a`または`campaign_b`のタグによるキャッシュの無効化が行われた場合、FooのキャッシュとFooのETagはサーバーサイド、CDN共に無効になります。

### リソースの依存

`UriTagInterface`を使ってURIを依存タグ文字列に変換します：

```php
public function __construct(private UriTagInterface $uriTag)
{
}
```

```php
$this->headers[Header::SURROGATE_KEY] = ($this->uriTag)(new Uri('app://self/foo'));
```

`app://self/foo`に変更があった場合、このキャッシュはサーバーサイド、CDN共に無効化されます。

### 連想配列をリソースの依存に

```php
// bodyの内容
[
    ['id' => '1', 'name' => 'a'],
    ['id' => '2', 'name' => 'b'],
]
```

上記のような`body`連想配列から、依存するURIタグリストを生成する場合は`fromAssoc()`メソッドでURIテンプレートを指定します：

```php
$this->headers[Header::SURROGATE_KEY] = $this->uriTag->fromAssoc(
    uriTemplate: 'app://self/item{?id}',
    assoc: $this->body
);
```

上記の場合、`app://self/item?id=1`および`app://self/item?id=2`に変更があった場合に、このキャッシュはサーバーサイド、CDN共に無効化されます。

## CDN特定

特定CDN対応のモジュールをインストールすると、ベンダー固有のヘッダーが出力されます：

```php
$this->install(new FastlyModule());
$this->install(new AkamaiModule());
```

## マルチCDN

CDNを多段構成にして、役割に応じたTTLを設定することもできます。例えば以下の図では、上流に多機能なCDNを配置して、下流にはコンベンショナルなCDNを配置しています。コンテンツの無効化などは上流のCDNに対して行い、下流のCDNはそれを利用するようにします。

<img width="344" alt="multi cdn diagram" src="https://user-images.githubusercontent.com/529021/137098809-ec949a15-8efb-4d03-9808-3be15523ade7.png">

# レスポンスヘッダー

CDNのキャッシュコントロールについてはBEAR.Sundayが自動で行い、CDN用のヘッダーを出力します。クライアントのキャッシュコントロールはコンテンツに応じて`ResourceObject`の`$header`に記述します。

セキュリティやメンテナンスの観点から、このセクションは重要です。全ての`ResourceObject`で`Cache-Control`を指定するようにしましょう。

### キャッシュ不可

キャッシュができないコンテンツは必ず指定しましょう：

```php
ResponseHeader::CACHE_CONTROL => CacheControl::NO_STORE
```

### 条件付きリクエスト

サーバーにコンテンツ変更がないかを確認してから、キャッシュを利用します。サーバーサイドのコンテンツの変更は検知され反映されます：

```php
ResponseHeader::CACHE_CONTROL => CacheControl::NO_CACHE
```

### クライアントキャッシュ時間の指定

クライアントでキャッシュされます。最も効率的なキャッシュですが、サーバーサイドでコンテンツが変更されても指定した時間には反映されません。またブラウザのリロード動作ではこのキャッシュは利用されません。`<a>`タグで遷移、またはURL入力した場合にキャッシュが利用されます：

```php
ResponseHeader::CACHE_CONTROL => 'max-age=60'
```

レスポンス速度を重視する場合には、SWRの指定も検討しましょう：

```php
ResponseHeader::CACHE_CONTROL => 'max-age=30 stale-while-revalidate=10'
```

この場合、max-ageの30秒を超えた時にオリジンサーバーからフレッシュなレスポンス取得が完了するまで、SWRで指定された最大10秒間はそれまでの古いキャッシュ（stale）レスポンスを返します。つまりキャッシュが更新されるのは最後のキャッシュ更新から30秒から40秒間の間のいずれかになりますが、どのリクエストもキャッシュからの応答になり高速です。

#### RFC7234対応クライアント

APIでクライアントキャッシュを利用する場合には、RFC7234対応APIクライアントを利用します：

* iOS: [NSURLCache](https://nshipster.com/nsurlcache/)
* Android: [HttpResponseCache](https://developer.android.com/reference/android/net/http/HttpResponseCache)
* PHP: [guzzle-cache-middleware](https://github.com/Kevinrob/guzzle-cache-middleware)
* JavaScript(Node): [cacheable-request](https://www.npmjs.com/package/cacheable-request)
* Go: [lox/httpcache](https://github.com/lox/httpcache)
* Ruby: [faraday-http-cache](https://github.com/plataformatec/faraday-http-cache)
* Python: [requests-cache](https://pypi.org/project/requests-cache/)

### プライベートキャッシュ

他のクライアントと共有しない場合には`private`を指定します。クライアントサイドのみキャッシュが保存されます。この場合、サーバーサイドでキャッシュを指定しないでください。

```php
ResponseHeader::CACHE_CONTROL => 'private, max-age=30'
```

## キャッシュ設計

API（またはコンテンツ）は**情報API**（Information API）と**計算API**（Computation API）の2つに分類できます。計算APIは再現が難しく真に動的でキャッシュに不適なコンテンツです。一方の情報APIはDBから読み出され、PHPで加工されたとしても本質的には静的なコンテンツのAPIです。

適切なキャッシュを適用するためにコンテンツを分析します：

* 情報APIか計算APIか
* 依存関係は何か
* 内包関係は何か
* 無効化はイベントがトリガーか、それともTTLか
* イベントはアプリケーションが検知可能か、監視が必要か
* TTLは予測可能か不可能か

キャッシュ設計をアプリケーション設計プロセスの一部として捉え、仕様に含めることも検討しましょう。ライフサイクルを通してプロジェクトの安全性にも寄与するはずです。

### アダプティブTTL

コンテンツの生存期間が予測可能で、その期間にイベントによる更新が行われない場合は、それをクライアントやCDNに正しく伝えます。

例えば株価のAPIを扱う場合、現在が金曜日の夜だとすると月曜の取引開始時間までは情報更新が行われないことが分かっています。その時間までの秒数を計算してTTLとして指定し、取引時間の時には適切なTTLを指定します。クライアントは更新がないと分かっているリソースにリクエストする必要はありません。

## #[Cacheable]

従来の#[Cacheable]によるTTLキャッシュもサポートされます。

例）サーバーサイドで30秒キャッシュ、クライアントでも30秒キャッシュ。サーバーサイドで指定しているので、クライアントサイドでも同じ秒数でキャッシュされます：

```php
use BEAR\RepositoryModule\Annotation\Cacheable;

#[Cacheable(expirySecond: 30)]
class CachedResource extends ResourceObject
{
```

例）指定した有効期限（`$body['expiry_at']`の日付）まで、サーバー、クライアント共にキャッシュ：

```php
use BEAR\RepositoryModule\Annotation\Cacheable;

#[Cacheable(expiryAt: 'expiry_at')]
class CachedResource extends ResourceObject
{
```

その他は[HTTPキャッシュ](https://bearsunday.github.io/manuals/1.0/ja/http-cache.html)ページをご覧ください。

## 結論

Webのコンテンツには情報（データ）型のものと計算（プロセス）型のものがあります。前者は本質的には静的ですが、コンテンツの変更や依存性の管理の問題で完全に静的コンテンツとして扱うのが難しく、コンテンツの変更が発生していないのにTTLによるキャッシュの無効化が行われていました。

BEAR.Sundayのキャッシングフレームワークは、情報型のコンテンツを可能な限り静的に扱い、キャッシュの力を最大化します。

## 用語

* [条件付きリクエスト](https://developer.mozilla.org/ja/docs/Web/HTTP/Conditional_requests)
* [ETag（バージョン識別子）](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/ETag)
* [イベントドリブン型コンテンツ](https://www.fastly.com/blog/rise-event-driven-content-or-how-cache-more-edge)
* [ドーナッツキャッシュ / 部分キャッシュ](https://www.infoq.com/jp/news/2011/12/MvcDonutCaching/)
* [サロゲートキー / タグベースの無効化](https://docs.fastly.com/ja/guides/getting-started-with-surrogate-keys)
* ヘッダー
  * [Cache-Control](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/Cache-Control)
  * [CDN-Cache-Control](https://blog.cloudflare.com/cdn-cache-control/)
  * [Vary](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/Vary)
  * [Stale-While-Revalidate（SWR）](https://www.infoq.com/jp/news/2020/12/ux-stale-while-revalidate/)
