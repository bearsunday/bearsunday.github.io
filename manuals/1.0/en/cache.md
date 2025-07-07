---
layout: docs-en
title: Cache
category: Manual
permalink: /manuals/1.0/en/cache.html
---

# Cache

> There are only two hard things in Computer Science: cache invalidation and naming things.
>
> -- Phil Karlton

## Overview

A good caching system fundamentally improves the quality of user experience and reduces resource utilization costs and environmental impact. BEAR.Sunday supports the following caching features in addition to traditional simple TTL-based caching:

* Event-driven cache invalidation
* Cache dependency resolution
* Donut cache and donut hole cache
* CDN control
* Conditional requests

### Distributed Cache Framework

A distributed caching system that follows REST constraints saves not only computational resources but also network resources. BEAR.Sunday provides a caching framework that integrates **server-side caches** (such as Redis and APC handled directly by PHP), **shared caches** (known as content delivery networks - CDNs), and **client-side caches** (cached by web browsers and API clients) with modern CDNs.

<img src="https://user-images.githubusercontent.com/529021/137062427-c733c832-0631-4a43-a6ee-4204e6be007c.png" alt="distributed cache">

## Tag-based Cache Invalidation

<img width="369" alt="dependency graph 2021-10-19 21 38 02" src="https://user-images.githubusercontent.com/529021/137910748-b6e95839-eeb7-4ade-a564-3cdcd5fdc09e.png">

Content caching has dependency issues. If content A depends on content B, and B depends on C, then when C is updated, not only must C's cache and ETag be updated, but also B's cache and ETag (which depends on C), and A's cache and ETag (which depends on B).

BEAR.Sunday solves this problem by having each resource hold the URI of dependent resources as tags. When a resource embedded with `#[Embed]` is modified, the cache and ETag of all related resources are invalidated, and cache regeneration occurs for the next request.

## Donut Cache

<img width="200" alt="donut caching" src="https://user-images.githubusercontent.com/529021/137097856-f9428918-5b76-4c0e-8cea-2472c15d82e9.png">

Donut caching is a partial caching technique for cache optimization. It separates content into cacheable and non-cacheable parts and combines them for output.

For example, consider content containing a non-cacheable resource like "`Welcome to $name`". The non-cacheable (do-not-cache) part is combined with other cacheable parts for output.

<img width="557" alt="image" src="https://user-images.githubusercontent.com/529021/139617102-1f7f436c-a1f4-4c6c-b90b-de24491e4c8c.png">

In this case, since the entire content is dynamic, the whole donut is not cached. Therefore, no ETag is output either.

## Donut Hole Cache

<img width="544" alt="image" src="https://user-images.githubusercontent.com/529021/139617571-31aea99a-533f-4b95-b3f3-6c613407d377.png">

When the donut hole part is cacheable, it can be handled the same way as donut cache. In the example above, a weather forecast resource that changes once per hour is cached and included in the news resource.

In this case, since the donut content as a whole (news) is static, the entire content is also cached and given an ETag. This creates cache dependency. When the donut hole content is updated, the entire cached donut needs to be regenerated.

This dependency resolution happens automatically. To minimize computational resources, the donut part computation is reused. When the hole part (weather resource) is updated, the cache and ETag of the entire content are also automatically updated.

### Recursive Donut

<img width="191" alt="recursive donut 2021-10-19 21 27 06" src="https://user-images.githubusercontent.com/529021/137909083-2c5176f7-edb7-422b-bccc-1db90460fc15.png">

The donut structure is applied recursively. For example, if A contains B and B contains C, when C is modified, A's cache and B's cache are reused except for the modified C part. A's and B's caches and ETags are regenerated, but database access for A and B content retrieval and view rendering are not performed.

The optimized partial cache structure performs content regeneration with minimal cost. Clients don't need to know about the content cache structure.

## Event-Driven Content

Traditionally, CDNs considered content requiring application logic as "dynamic" and therefore not cacheable by CDNs. However, some CDNs like Fastly and Akamai now support immediate or tag-based cache invalidation within seconds, making [this thinking obsolete](https://www.fastly.com/blog/leveraging-your-cdn-cache-uncacheable-content).

BEAR.Sunday dependency resolution works not only server-side but also on shared caches. When AOP detects changes and makes PURGE requests to shared caches, related cache invalidation occurs on shared caches just like server-side.

## Conditional Requests

<img width="468" alt="conditional request" src="https://user-images.githubusercontent.com/529021/137151061-8d7a5605-3aa3-494c-91c5-c1deddd987dd.png">

Content changes are managed by AOP, and content entity tags (ETags) are automatically updated. HTTP conditional requests using ETags not only minimize computational resource usage, but responses returning only `304 Not Modified` also minimize network resource usage.

# Usage

For classes to be cached, use the `#[DonutCache]` attribute for donut cache (when embedded content is not cacheable), and `#[CacheableResponse]` for other cases:

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

### recursive donut

<img width="191" alt="recursive donut 2021-10-19 21 27 06" src="https://user-images.githubusercontent.com/529021/137909083-2c5176f7-edb7-422b-bccc-1db90460fc15.png">

The donut structure will be recursively applied.
For example, if A contains B and B contains C and C is modified, A's cache and B's cache will be reused except for the modified C. A's and B's caches and ETags will be regenerated, but DB access to retrieve A's and B's content and rendering of views will not be done.

The optimized structure of the partial cache performs content regeneration with minimal cost. The client does not need to know about the content cache structure.

## Event-driven content

Traditionally, CDNs have believed that content that requires application logic is "dynamic" and therefore cannot be cached by a CDN. However, some CDNs, such as Fastly and Akamai, allow immediate or tag-based cache invalidation within seconds, [this idea is a thing of the past](https://www.fastly.com/blog/leveraging-your-cdn-cache- uncacheable-content).

Sunday dependency resolution is done not only on the server side, but also on the shared cache; when AOP detects a change and makes a PURGE request to the shared cache, the related cache on the shared cache will be invalidated, just like on the server side.

## Conditional request

<img width="468" alt="conditional request" src="https://user-images.githubusercontent.com/529021/137151061-8d7a5605-3aa3-494c-91c5-c1 deddd987dd.png">

Content changes are managed by AOP, and the entity tag (ETag) of the content is automatically updated. conditional requests for HTTP using ETag not only minimize the use of computational resources, but responses that only return `304 Not Modified` also minimize the use of network resources. Conditional HTTP requests using ETag not only minimize the use of computational resources, but also minimize the use of network resources by simply returning `304 Not Modified`.


# Usage

Give the class to be cached the attribute `#[DonutCache]` if it is a donut cache (embedded content is not cacheable) and `#[CacheableResponse]` otherwise.


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

If you give attributes in either way, all the features introduced in the overview will apply.
Caching is not disabled by time (TTL) by default, assuming event-driven content

Note that with `#[DonutCache]` the whole content will not be cached, but with `#[CacheableResponse]` it will be.

## TTL

TTL is specified with `DonutRepositoryInterface::put()`.
`ttl` is the cache time for non-donut holes, `sMaxAge` is the cache time for CDNs.

```php
use BEAR\RepositoryModule\Annotation\CacheableResponse;

#[CacheableResponse]
class BlogPosting extends ResourceObject
{
    public function __construct(private DonutRepositoryInterface $repository)
    {}

    #[Embed(rel: "comment", src: "page://self/html/comment")]
    public function onGet(): static
    {
        // process ...
        $this->repository->put($this, ttl:10, sMaxAge:100);　

        return $this;
    }
}
```
### Default TTL value

For event-driven content, changes to the content must be reflected immediately in the cache, so the default TTL varies depending on the CDN module installed. Therefore, the default TTL will vary depending on the CDN module installed: indefinitely (1 year) if the CDN supports tag-based disabling of caching, or 10 seconds if it does not.

The expected cache reflection time is immediate for Fastly, a few seconds for Akamai, and 10 seconds for others.

To customize it, bind it by implementing `CdnCacheControlHeaderSetterInterface` with reference to `CdnCacheControlHeader`.

## Cache invalidation

Use the methods of `DonutRepositoryInterface` to manually invalidate the cache.
This will invalidate not only the specified cache, but also the cache of the ETag, any other resources it depends on, and the cache of the ETag on the server side and, if possible, on the CDN.

```php
interface DonutRepositoryInterface
{
    public function purge(AbstractUri $uri): void;
    public function invalidateTags(array $tags): void;
}
```

### Invalidate by URI

```php
// example
$this->repository->purge(new Uri('app://self/blog/comment'));
```

### Disable by tag

```php
$this->repository->invalidateTags(['template_a', 'campaign_b']);
```
### Tag Invalidation in CDN

In order to enable tag-based cache invalidation in CDN, you need to implement and bind `PurgerInterface`.

```php
use BEAR\QueryRepository\PurgerInterface;

interface PurgerInterface
{
    public function __invoke(string $tag): void;
}
```

### Specify dependent tags.

Use the `SURROGATE_KEY` header to specify the key for PURGE. Use a space as a separator for multiple strings.

```php
use BEAR\QueryRepository\Header;

class Foo
{
    public $headers = [
        Header::SURROGATE_KEY => 'template_a campaign_b'
    ];
```

If the cache is invalidated by `template_a` or `campaign_b` tags, Foo's cache and Foo's ETag will be invalidated both server-side and CDN.

### Resource Dependencies.

Use `UriTagInterface` to convert a URI into a dependency tag string.

```php
public function __construct(private UriTagInterface $uriTag)
{}
```
```php
$this->headers[Header::SURROGATE_KEY] = ($this->uriTag)(new Uri('app://self/foo'));
```

This cache will be invalidated both server-side and CDN when `app://self/foo` is modified.

### Make associative array a resource dependency.

```php
// bodyの内容
[
    ['id' => '1', 'name' => 'a'],
    ['id' => '2', 'name' => 'b'],
]
```
If you want to generate a list of dependent URI tags from a `body` associative array like the above, you can specify the URI template with the `fromAssoc()` method.

```php
$this->headers[Header::SURROGATE_KEY] = $this->uriTag->fromAssoc(
    uriTemplate: 'app://self/item{?id}',
    assoc: $this->body
);
```

In the above case, this cache will be invalidated for both server-side and CDN when `app://self/item?id=1` and `app://self/item?id=2` are changed.

## CDN

If you install a module that supports a specific CDN, vendor-specific headers will be output.

```php
$this->install(new FastlyModule())
$this->install(new AkamaiModule())
```

## Multi-CDN

You can also configure a multi-tier CDN and set the TTL according to the role. For example, in this diagram, a multi-functional CDN is placed upstream, and a conventional CDN is placed downstream. Content invalidation is done for the upstream CDN, and the downstream CDN uses it.

<img width="344" alt="multi cdn diagram" src="https://user-images.githubusercontent.com/529021/137098809-ec949a15-8efb-4d03-9808-3be15523ade7.png">


# Response headers

Sunday will automatically do the cache control for the CDN and output the header for the CDN. Client cache control is described in `$header` of ResourceObject depending on the content.

This section is important for security and maintenance purposes.
Make sure to specify the `Cache-Control` in all ResourceObjects.

### Cannot cache

Always specify content that cannot be cached.

```php
ResponseHeader::CACHE_CONTROL => CacheControl::NO_STORE
```

### Conditional requests

Check the server for content changes before using the cache. Server-side content changes will be detected and reflected.

```php
ResponseHeader::CACHE_CONTROL => CacheControl::NO_CACHE
```

### Specify client cache time.

The client is cached on the client. This is the most efficient cache, but server-side content changes will not be reflected at the specified time.

Also, this cache is not used when the browser reloads. The cache is used when a transition is made with the `<a>` tag or when a URL is entered.

```php
ResponseHeader::CACHE_CONTROL => 'max-age=60'
```

If response time is important to you, consider specifying SWR.

```php
ResponseHeader::CACHE_CONTROL => 'max-age=30 stale-while-revalidate=10'
```

In this case, when the max-age of 30 seconds is exceeded, the old cached (stale) response will be returned for up to 10 seconds, as specified in the SWR, until a fresh response is obtained from the origin server. This means that the cache will be updated sometime between 30 and 40 seconds after the last cache update, but every request will be a response from the cache and will be fast.

#### RFC7234 compliant clients

To use the client cache with APIs, use an RFC7234 compliant API client.

* iOS [NSURLCache](https://nshipster.com/nsurlcache/)
* Android [HttpResponseCache](https://developer.android.com/reference/android/net/http/HttpResponseCache)
* PHP [guzzle-cache-middleware](https://github.com/Kevinrob/guzzle-cache-middleware)
* JavaScript(Node) [cacheable-request](https://www.npmjs.com/package/cacheable-request)
* Go [lox/httpcache](https://github.com/lox/httpcache)
* Ruby [faraday-http-cache](https://github.com/plataformatec/faraday-http-cache)
* Python [requests-cache](https://pypi.org/project/requests-cache/)

### private

Specify `private` if you do not want to share the cache with other clients. The cache will be saved only on the client side. In this case, do not specify the cache on the server side.

````php
ResponseHeader::CACHE_CONTROL => 'private, max-age=30'
````

> Even if you use shared cache, you don't need to specify `public` in most cases.

## Cache design

APIs (or content) can be divided into two categories: **Information APIs** (Information APIs) and **Computation APIs** (Computation APIs). The **Computation API** is content that is difficult to reproduce and is truly dynamic, making it unsuitable for caching. The Information API, on the other hand, is an API for content that is essentially static, even if it is read from a DB and processed by PHP.

It analyzes the content in order to apply the appropriate cache.

* Information API or Computation API?
* Dependencies are
* Are the comprehension relationships
* Is the invalidation triggered by an event or TTL?
* Is the event detectable by the application or does it need to be monitored?
* Is the TTL predictable or unpredictable?

Consider making cache design a part of the application design process and make it a specification. It should also contribute to the safety of your project throughout its lifecycle.

### Adaptive TTL

Adaptive TTL is the ability to predict the lifetime of content and correctly tell the client or CDN when it will not be updated by an event during that time. For example, when dealing with a stock API, if it is Friday night, we know that the information will not be updated until the start of trading on Monday. We calculate the number of seconds until that time, specify it as the TTL, and then specify the appropriate TTL when it is time to trade.

The client does not need to request a resource that it knows will not be updated.

## #[Cacheable].

The traditional ##[Cacheable] TTL caching is also supported.

Example: 30 seconds cache on the server side, 30 seconds cache on the client.

The same number of seconds will be cached on the client side since it is specified on the server side.

The same number of seconds will be cached on the client side.
use BEAR\RepositoryModule\Annotation\Cacheable;

#[Cacheable(expirySecond: 30)]]
class CachedResource extends ResourceObject
{
````

Example: Cache the resource on the server and client until the specified expiration date (the date in `$body['expiry_at']`)

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

#[Cacheable(expiryAt: 'expiry_at')]]
class CachedResource extends ResourceObject
{
```.

See the [HTTP Cache](https://bearsunday.github.io/manuals/1.0/ja/http-cache.html) page for more information.

## Conclusion

Web content can be of the information (data) type or the computation (process) type. Although the former is essentially static, it is difficult to treat it as completely static content due to the problems of managing content changes and dependencies, so the cache was invalidated by TTL even though no content changes occurred. Sunday's caching framework treats information type content as static as possible, maximizing the power of the cache.


## Terminology

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
