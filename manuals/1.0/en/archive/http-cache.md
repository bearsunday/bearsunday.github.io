---
layout: docs-en
title: HTTP Cache
category: Manual
permalink: /manuals/1.0/en/http-cache.html
---

# HTTP Cache

[RFC2616 Hypertext Transfer Protocol (HTTP/1.1): Caching](https://www.rfc-editor.org/rfc/rfc2616#section-13) defines the purpose of HTTP caching as follows.

>  The purpose of HTTP/1.1 caching is **to eliminate the need to send a request, and to eliminate the need to send a full response**.

BEAR.Sunday provides server-side caching through the `@Cacheable` annotation, and applies the same mechanism to [RFC7234 HTTP caching](https://www.rfc-editor.org/rfc/rfc7234) to support network caches (client-side caches).

By following the standard REST cache constraints, HTTP clients that support **RFC7234** can use client-side caches and cache servers without any client-side coding.

## Cache-Control Header

### Directives

| Directive | Description |
|---|---|
| public | Use of shared caches between clients |
| private | Do not share the cache between clients |
| no-store | Do not cache |
| no-cache | Cache use requires validation with the origin server. [^3]|
| must-revalidate | Use of an expired cache always requires validation. |
| max-age | Cache expiration |
| s-maxage | Expiration of shared caches |

### public and private

`public` allows a response to be cached even if HTTP authentication is associated with it, or even if its status code is not normally cacheable. Usually an explicit cache directive such as `max-age` already marks the response as cacheable, so `public` is not necessary.

A `private` response can be stored in a browser cache, but since the target user is normally a single person, it is not allowed to be stored in an intermediate cache. For example, personal user information is cached only on that user's client and not on a CDN.

### no-cache and no-store

`no-cache` indicates that cacheability must be verified, that is, whether the response has changed must be checked. The validation token (ETag) in the request header is examined, and if the resource has not changed, the HTTP code `304` is returned and the response body can be omitted.

`no-store` simply prohibits all caching of the response.

### Defining an Optimal Cache-Control Policy[^5]

<img src="https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/images/http-cache-decision-tree.png?hl=en" alt="Cache-Control flowchart for deciding whether to store, revalidate, or share a response">

## @Cacheable

Example: cache on the server side for 30 seconds and on the client for 30 seconds.

Because the cache is specified on the server side, the client also caches for the same number of seconds. The `@HttpCache` annotation is not needed in this case.

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

/**
 * @Cacheable(expirySecond=30)
 */
class CachedResource extends ResourceObject
{
}
```

Example: cache on both server and client until the specified expiration (the date in `$body['expiry_at']`).

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

/**
 * @Cacheable(expiryAt="expiry_at")
 */
class CachedResource extends ResourceObject
{
}
```

## @HttpCache


Example: private cache for 30 seconds

Specify `isPrivate` for private information such as credentials that cannot be shared between clients.

```php?start_inline
use BEAR\RepositoryModule\Annotation\HttpCache;

/**
 * @HttpCache(isPrivate=true, maxAge=30)
 */
class CachedResource extends ResourceObject
{
}
```

Example: verify on every request whether the resource has been updated[^2].

```php?start_inline
use BEAR\RepositoryModule\Annotation\HttpCache;

/**
 * @HttpCache(noCache=true)
 */
class CachedResource extends ResourceObject
{
}
```

Example: same as above, but uses only the private cache if the resource has not been updated.

```php?start_inline
use BEAR\RepositoryModule\Annotation\HttpCache;

/**
 * @HttpCache(isPrivate=true, noCache=true)
 */
class CachedResource extends ResourceObject
{
}
```

### @NoHttpCache

Use `@NoHttpCache` to prevent client caching.

```php?start_inline
use BEAR\RepositoryModule\Annotation\NoHttpCache;

/**
 * @NoHttpCache
 */
class UncacheableResource extends ResourceObject
{
}
```

Server-side caching with `@Cacheable` is still possible in this case.

## Response Headers

Cached responses are given `Last-Modified`, `Etag`, and `Age` headers.
The `Last-Modified` header shows when the cache was stored, and the `Age` header shows the number of seconds elapsed since it was stored.

Whether the cache was hit can be determined by the presence of the `Age` header (seconds since the cache was created).[^1]

## Request Headers

The `Vary` header specifies that the response varies according to the given request headers. You can cache separately by language, user agent, authentication ID, and so on.


## Clients Supporting RFC7234

 * iOS [NSURLCache](https://nshipster.com/nsurlcache/)
 * Android [HttpResponseCache](https://developer.android.com/reference/android/net/http/HttpResponseCache)
 * PHP [guzzle-cache-middleware](https://github.com/Kevinrob/guzzle-cache-middleware)
 * JavaScript(Node) [cacheable-request](https://www.npmjs.com/package/cacheable-request)
 * Go [lox/httpcache](https://github.com/lox/httpcache)
 * Ruby [faraday-http-cache](https://github.com/plataformatec/faraday-http-cache)
 * Python [requests-cache](https://pypi.org/project/requests-cache/)

## For Effective Caching

 * Use consistent URIs.[^7]
 * Consider client-side caching first
 * Set an appropriate expiration (`max-age`).
 * Cache resources stored per user as `private`.
 * Use `no-store` only for content that truly cannot be cached.
 * Use an RFC7234-compliant client for HTTP API clients.

## Link

 * [HTTP Caching - Client and Network Caching with RFC 7234](https://www.youtube.com/watch?v=761puUy8ir4)
 * [Speeding Up APIs/Apps/Smart Toasters with HTTP Response Caching](https://blog.apisyouwonthate.com/speeding-up-apis-apps-smart-toasters-with-http-response-caching-a67becf829c6)

----

[^1]: Useful for cache testing.
[^2]: The `ETag` response header is used with `If-None-Match`, and `Last-Modified` with `If-Modified-Since`.
[^3]: Despite its name, `no-cache` still caches the response.
[^4]: https://tech.mercari.com/entry/2017/06/22/204500
[^5]: Quoted from https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/http-caching?hl=en
[^6]: A response can be cached even if HTTP authentication is associated with it, or even if its status code is not normally cacheable. Usually an explicit cache directive (such as `max-age`) already marks the response as cacheable, so `public` is not necessary.[^7]
[^7]: Case-sensitive.
