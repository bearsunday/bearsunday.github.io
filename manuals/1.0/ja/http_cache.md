---
layout: docs-ja
title: HTTPキャッシュ
category: Manual
permalink: /manuals/1.0/ja/http_cache.html
---

*Work in Progress*

# HTTPキャッシュ

[RFC2616 Hypertext Transfer Protocol (HTTP/1.1): Caching](https://triple-underscore.github.io/RFC2616-ja.html#section-13)ではHTTPキャッシュの目的を以下のように定めています。

>  HTTP/1.1 におけるキャッシングが目指すものは、多くの場合でリクエストを送る必要を無くし、また別の場合において全レスポンスを送る必要を無くす事です。

レスポンスに一度取得したリソースの有効期限が含まれたり、他のクライアントが取得したリソースを共有できればサーバーにリクエストを送る必要がありません。

またサーバーサイドでメソッド実行を行う前にリソースが更新したかどうかに確信が持てれば、「更新されていません」というコードだけを送り（**304 Not Modified**) CPUリソースとネットワーク帯域を節約することができます。

BEAR.Sundayでは`@Cacheable`アノテーションによるサーバーサイドでのキャッシュが仕組みがありますが、これを[RFC7234 HTTPキャッシュ](https://triple-underscore.github.io/RFC7234-ja.html)にも適用してクライアント(Webブラウザ、APIクライアント）サイドでのキャッシュをサポートします。
標準のキャッシュ制約に従う事で**RFC7234**をサポートしたHTTPクライアントでは、リクエストはそのままででキャッシュを透過的に扱うことができます。

## Cache-Controlヘッダー


### ディレクティブ

| ディレクティブ | 説明 |
|--------------+-----|
| public | クライアント間の共有キャッシュの利用 |
| private | クライアントでキャッシュを共有しない |
| no-store | キャッシュしません |
| no-cache | キャッシュの利用に都度検証が必要。**注意）`no-cache`という名前ですがキャッシュは行います** |
| must-revalidate | 期限の切れたキャッシュの利用に必ず検証が必要。 |
| max-age | キャッシュの有効期限 |
| s-maxage | 共有キャッシュの有効期限 |


### @NoHttpCache

クライアントキャッシュを`@HttpCache`で指定できます。

例）サーバーサイドで30秒キャシュ、クライアントでもプライベートで30秒キャシュ

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\RepositoryModule\Annotation\NoHttpCache;

/**
 * @Cacheable(expirySecond=30)
 * @HttpCache(isPrivate=true)
 */
class CachedResource extends ResourceObject
{
```

例）指定した有効期限('$body['expiry_at']の日付)までサーバー、クライアント供にキャッシュ

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\RepositoryModule\Annotation\NoHttpCache;

/**
 * @Cacheable(expiryAt="expiry_at")
 * @HttpCache
 */
class CachedResource extends ResourceObject
{
```

例）クライアントのみ60秒共有キャッシュ

```php?start_inline
use BEAR\RepositoryModule\Annotation\NoHttpCache;

/**
 * @HttpCache(maxage=60)
 */
class CachedResource extends ResourceObject
{
```

例）都度更新されていないか確認。更新されていないならクライアント共有キャッシュを利用。

```php?start_inline
use BEAR\RepositoryModule\Annotation\NoHttpCache;

/**
 * @HttpCache(noCache=true)
 */
class CachedResource extends ResourceObject
{
```

### @NoHttpCache

クライアントキャッシュしない時は`@NoHttpCache`で指定します。

```php?start_inline
use BEAR\RepositoryModule\Annotation\NoHttpCache;

/**
 * @NoHttpCache
 */
class UncacheableResource extends ResourceObject
{
```

同時に`@Cacheable`でサーバーサイドでキャッシュすることは可能です。

## レスポンスヘッダ

キャッシュされたレスポンスには`Last-Modified`、`Etag`、`Age`ヘッダーが付加されます。
`Last-Modified`ヘッダーはキャッシュが保存された時間、`Age`ヘッダーは保存されてからの経過時間が分かります。

キャッシュがヒットしたかどうかは`Age`ヘッダーの存在で分かります。[^1]

## リクエストヘッダ

`Vary`ヘッダーで指定したリクエストヘッダでレスポンスが変わることを指定できます。言語や、ユーザーエージェント等、認証ID別にキャッシュを持つことができます。


## RFC7234対応クライアント

 * PHP [guzzle-cache-middleware](https://github.com/Kevinrob/guzzle-cache-middleware)
 * JavaScript(Node) [cacheable-request](https://www.npmjs.com/package/cacheable-request)
 * Go [lox/httpcache](https://github.com/lox/httpcache)
 * Ruby [faraday-http-cache](https://github.com/plataformatec/faraday-http-cache)
 * Python [requests-cache](https://pypi.org/project/requests-cache/)

## Link

 * [HTTP Caching - Client and Network Caching with RFC 7234](https://www.youtube.com/watch?v=761puUy8ir4)
 * [Speeding Up APIs/Apps/Smart Toasters with HTTP Response Caching](https://blog.apisyouwonthate.com/speeding-up-apis-apps-smart-toasters-with-http-response-caching-a67becf829c6)

----

[^1]: キャッシュテストに便利です。