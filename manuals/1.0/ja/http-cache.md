---
layout: docs-ja
title: HTTPキャッシュ
category: Manual
permalink: /manuals/1.0/ja/http-cache.html
---

*Work in Progress (`bear/package ^1.9`が必要です）*

# HTTPキャッシュ

[RFC2616 Hypertext Transfer Protocol (HTTP/1.1): Caching](https://triple-underscore.github.io/RFC2616-ja.html#section-13)ではHTTPキャッシュの目的を以下のように定めています。

>  HTTP/1.1 のキャッシングの目的は**リクエストを送る必要を無くしたり、全レスポンスを送る必要を無くす事**です。

BEAR.Sundayでは`@Cacheable`アノテーションによるサーバーサイドでのキャッシュが仕組みがありますが、これを[RFC7234 HTTPキャッシュ](https://triple-underscore.github.io/RFC7234-ja.html)にも適用してネットワークキャッシュ（クライアントサイドキャッシュ）をサポートします。

REST標準のキャッシュ制約に従う事で**RFC7234**をサポートしたHTTPクライアントでは、クラアイントでのコーディングなしにクライアントサイドキャッシュやキャッシュサーバーの使用が可能になります。

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


## @Cacheable

例）サーバーサイドで30秒キャシュ、クライアントでも30秒キャシュ。

サーバーサイドで指定してるのでクライアントサイドでも同じ秒数でキャッシュされます。この時に`@HttpCache`アノテーションは必要ありません。

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

/**
 * @Cacheable(expirySecond=30)
 */
class CachedResource extends ResourceObject
{
```

例）指定した有効期限(`$body['expiry_at']`の日付)までサーバー、クライアント供にキャッシュ

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

/**
 * @Cacheable(expiryAt="expiry_at")
 */
class CachedResource extends ResourceObject
{
```

## @HttpCache


例）プライベートで30秒キャシュ

認証情報などクライアントで共用できないプラベート情報は`isPrivate`を指定します。

```php?start_inline
use BEAR\RepositoryModule\Annotation\HttpCache;

/**
 * @HttpCache(isPrivate=true, max-age=60)
 */
class CachedResource extends ResourceObject
{
```

例）更新されていないか都度確認します[^2]。

```php?start_inline
use BEAR\RepositoryModule\Annotation\NoHttpCache;

/**
 * @HttpCache(noCache=true)
 */
class CachedResource extends ResourceObject
{
```

例）上記と同じですが、更新されていないならプライベートキャッシュのみを利用します。

```php?start_inline
use BEAR\RepositoryModule\Annotation\NoHttpCache;

/**
 * @HttpCache(is_private=true, noCache=true)
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

この時に`@Cacheable`でサーバーサイドでキャッシュすることは可能です。

## レスポンスヘッダ

キャッシュされたレスポンスには`Last-Modified`、`Etag`、`Age`ヘッダーが付加されます。
`Last-Modified`ヘッダーはキャッシュが保存された時間、`Age`ヘッダーは保存されてからの経過時間が表示されます。

キャッシュがヒットしたかどうかは`Age`ヘッダー（キャッシュ生成後経過秒数）の存在で分かります。[^1]

## リクエストヘッダ

`Vary`ヘッダーで指定したリクエストヘッダでレスポンスが変わることを指定できます。言語や、ユーザーエージェント等、認証ID別にキャッシュを持つことができます。


## RFC7234対応クライアント

 * iOS [NSURLCache](https://nshipster.com/nsurlcache/)
 * Android [HttpResponseCache](https://developer.android.com/reference/android/net/http/HttpResponseCache)
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
[^2]: `ETag`と`If-not-modified`リクエストヘッダーが使われます。
