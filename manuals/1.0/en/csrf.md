---
layout: docs-en
title: CSRF
category: Manual
permalink: /manuals/1.0/en/csrf.html
---

# CSRF

[bear/csrf](https://github.com/bearsunday/BEAR.Csrf) protects `ResourceObject` methods from cross-site request forgery. It guards the state-changing methods a browser calls — POST, PUT, PATCH and DELETE. Two interceptors are bound by AOP to the methods you annotate: a same-origin check (`#[SameOrigin]`) and a token check (`#[CsrfToken]`). A failed check throws a `BEAR\Resource` 4xx exception, and the framework's error handling serves the response.

The CSRF token never appears as a resource method argument, in a request schema, or in API documentation. CSRF is a transport concern, not a resource parameter.

[Ray.WebFormModule](form.html#csrf-protections) verifies a form's token when `AbstractForm::apply()` runs. `bear/csrf` verifies at the `ResourceObject` method call, whichever path the request took to get there.

## Installation

```bash
composer require bear/csrf
```

## Module

Install `CsrfModule` in your application module. Which checks run is chosen by a named constructor, so a deployment cannot end up unprotected by leaving an argument out.

```php
use BEAR\Csrf\CsrfModule;

$this->install(CsrfModule::withSameOriginCheck('https://example.com'));
```

`allowedOrigin` is scheme and host only, with a port if needed — `https://example.com`, `http://localhost:8080`. A value with a path throws `LogicException` on every call to a protected method.

Where there is no browser origin to compare against — a CLI, or an API called by other servers — the same-origin check would reject every request. Say so explicitly. The token check stays on either way.

```php
$this->install(CsrfModule::withoutSameOriginCheck());
```

`tokenField` defaults to `_csrf_token` and `sessionKey` to `bear_csrf_token`. Change them only to match an existing field name or session layout.

```php
$this->install(CsrfModule::withSameOriginCheck(
    allowedOrigin: 'https://example.com',
    tokenField: 'csrfToken',
    sessionKey: 'cms_csrf_token',
));
```

The field name must survive the browser's submission and PHP's `$_POST` parsing unchanged. Only ASCII letters, digits, `_` and `-` are accepted. A name that does not qualify — an empty string, or `_charset_` in any letter case — throws `InvalidArgumentException` when the injector is built. `_charset_` is excluded because browsers overwrite that hidden field's value with the form's character encoding.

## Resource attributes

```php
use BEAR\Csrf\Attribute\CsrfToken;
use BEAR\Csrf\Attribute\SameOrigin;
use BEAR\Resource\ResourceObject;

class Article extends ResourceObject
{
    #[SameOrigin]
    #[CsrfToken]
    public function onPost(string $title, string $body): static
    {
        return $this;
    }
}
```

The attributes go on methods only. A protected resource must not be `final`: Ray.Aop applies interceptors by generating a subclass, and Ray.Di skips a `final` class without any error, so the method runs unprotected.

`#[SameOrigin]` inspects the origin signals a browser sends. It is armed by the `AllowedOrigin` binding: `withSameOriginCheck()` binds the origin you pass, `withoutSameOriginCheck()` binds `null`, and a null origin makes the interceptor proceed without checking. Otherwise, when `Sec-Fetch-Site` is present — current browsers send it to HTTPS origins — the request passes only if the value is `same-origin`; `same-site`, `cross-site`, `none` and unknown values are a 403, and the allowed origin is not consulted. Requests without `Sec-Fetch-Site` (older clients, non-browsers, plain HTTP) are judged by `Origin`, or `Referer` when `Origin` is absent, compared against the allowed origin. A request carrying none of the three is rejected.

`#[CsrfToken]` submits the token taken from the request to `CsrfTokenInterface::verify()` and rejects the request unless it matches the value stored in the session (this comparison is the synchronizer token pattern). The two checks are independent: `withoutSameOriginCheck()` does not affect the token check. The attribute takes no arguments. The module alone decides the field name, so the name a template renders and the name the interceptor reads cannot differ.

The `AllowedOrigin` binding is an extension point. A BEAR.Sunday application composes contexts over a neutral `AppModule`, so the module that knows the canonical origin is often not the one installing `CsrfModule`. Install `withoutSameOriginCheck()` in the shared module, then have the context that owns the origin rebind `AllowedOrigin` — a production module reading it from the environment, a test harness naming the origin it is about to send. The interceptor stays bound either way; only the value decides.

```php
// ProdModule
use BEAR\Csrf\Http\AllowedOrigin;

$this->bind(AllowedOrigin::class)->toInstance(new AllowedOrigin($origin));
```

A request with no token is not rejected by the interceptor on the spot. It is submitted to `CsrfTokenInterface::verify()` as `''`, and the bound implementation decides what is acceptable. For tests whose subject is not CSRF, bind an implementation whose `verify()` always returns `true` to `CsrfTokenInterface` rather than replacing the interceptor.

A rejection is a subclass of `BEAR\Csrf\Exception\ForbiddenException` (403): `MissingCsrfTokenForbiddenException` when no token was sent, `InvalidCsrfTokenForbiddenException` when it does not match, `CrossOriginForbiddenException` when the origin differs. A malformed `Origin` or `Referer` is `BEAR\Resource\Exception\BadRequestException` (400).

## Token sources

The submitted token is looked for in this order, and the first non-empty value found is used.

1. The `X-CSRF-Token` header
2. `ResourceObject->uri->query[$field]`
3. `$_POST[$field]`

Sources are never combined. A wrong token in the header is rejected even when `$_POST` carries the right one.

The header name is fixed and exposed as `HeaderRequestToken::HEADER_NAME`. `tokenField` changes only the key looked up in `uri->query` and `$_POST`.

BEAR.Sunday's web router puts `$_POST` into `uri->query` for POST, and the form-encoded or JSON request body for PUT, PATCH and DELETE. A browser form submission or a JSON body is therefore read by the second source. Do not put the token in the URL query string: it leaks through access logs, the `Referer` header, browser history and shared links.

## Templates

`CsrfHiddenInput` is a callable that returns the hidden field for the bound field name as an escaped HTML string. Inject it into a template helper and call it inside every form that submits to a protected method.

With Qiq, add a helper method to a subclass of `HtmlHelpers` and rebind `Helpers`.

```php
use BEAR\Csrf\Html\CsrfHiddenInput;
use Qiq\Helper\Html\HtmlHelpers;

class AppHelpers extends HtmlHelpers
{
    public function __construct(private readonly CsrfHiddenInput $csrfHiddenInput)
    {
        parent::__construct();
    }

    public function csrfField(): string
    {
        return ($this->csrfHiddenInput)();
    }
}
```

```php
use Qiq\Helpers;

$this->install(new QiqModule($templateDir));
$this->bind(Helpers::class)->to(AppHelpers::class);
```

```html
{% raw %}<form method="post" action="/admin/article">
  {{= csrfField() }}{% endraw %}
```

With Twig, register it as a `csrf_field` function. The return value is HTML, so declare `is_safe` to keep Twig from escaping it. Register it where you [extend Twig](html-twig-v2.html#extending-twig): in the Provider for `Twig_Environment`.

```php
use Twig\TwigFunction;

$twig->addFunction(new TwigFunction('csrf_field', $csrfHiddenInput, ['is_safe' => ['html']]));
```

```twig
{% raw %}<form method="post" action="/admin/article">
  {{ csrf_field() }}{% endraw %}
```

The output is `<input type="hidden" name="_csrf_token" value="…">` with the attribute value already escaped. Print it as is; escaping it again breaks the markup. The resource does not need to know the field exists.

Obtaining the helper does nothing by itself. The token is issued, and the session started, when the field is rendered. A rendered page then contains the token, so keep it out of `#[CacheableResponse]`, `#[DonutCache]` and CDN caches, and serve it with `Cache-Control: private, no-store` (see [private](cache.html#private) under Cache). `bear/csrf` sets no cache headers of its own.

To submit from JavaScript, expose the same token once in a `<meta>` tag and send it back in the `X-CSRF-Token` header. Attach the header only to same-origin requests. `$csrf` is the injected `CsrfTokenInterface`.

```php
<meta name="csrf-token" content="<?= htmlspecialchars($csrf->issue(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>">
```

```js
const token = document.querySelector('meta[name="csrf-token"]').content;
fetch('/admin/article', {method: 'POST', headers: {'X-CSRF-Token': token}, body});
```

Qiq accepts the `<?= ?>` above as is. With Twig, register a function that returns `$csrf->issue()` and let Twig escape the attribute value.

## Token lifecycle

The token is stored per session and stays the same across requests. It is not reissued for every form.

Rotate it whenever the authentication state changes, to mitigate login CSRF and session fixation. On login and logout, drop the old token and regenerate the session id; the next `issue()` then mints a token bound to the new session. `clear()` starts the session when none is active yet, so in this order `session_regenerate_id()` always has a started session to work on.

```php
// $csrf is the injected CsrfTokenInterface
$csrf->clear();                 // drop the old token
session_regenerate_id(true);
$csrf->issue();                 // mint a token for the new session
```

## Session store and supported hosts

The bundled `SessionCsrfStore` keeps the token in PHP's `$_SESSION`. Configuring the session is the application's responsibility: set the cookie's `HttpOnly`, `SameSite` and `Secure` flags before the store is first called.

When `session_start()` fails — headers already sent, an unwritable save path, a session backend that is down — the store throws `SessionStartFailedException`, on render and on verify alike. Ignoring the failure would store nothing, and every later submission would be a 403 for a missing token.

The session cookie travels in a header, so nothing may reach the output before the first `issue()` or `verify()`. BEAR.Sunday renders a template to a string before transfer, so calling `issue()` inside a template is fine. A CLI script that writes output first has to start the session before doing so.

`$_SESSION` belongs to the process, so the bundled store is correct only where one request owns one process: PHP-FPM, mod_php, the built-in server, CLI. **Do not use it where one persistent worker serves several requests.** There `$_SESSION` outlives the request and every visitor passing through that worker shares a single token. The server accepts the token a forged request carries, and nothing is visible from the outside because every request keeps succeeding.

The store throws `CoroutineUnsafeStoreException` when it detects that it is being called inside a coroutine. A persistent worker that does not use coroutines — a Swoole worker with `enable_coroutine` off, for instance — cannot be detected. The guard catches some misuse; it is not permission to use the store on a persistent-worker host.

On such a host, implement `CsrfStoreInterface` against something scoped to the request — coroutine context, or a shared backend keyed by the session id — and bind it.

```php
use BEAR\Csrf\CsrfStoreInterface;

$this->bind(CsrfStoreInterface::class)->to(MyRequestScopedStore::class);
```

Storage is a separate interface from `CsrfTokenInterface`. Minting with `random_bytes()` and comparing with `hash_equals()` stay in `StoredCsrfToken`, so changing where the token is kept does not mean rewriting the generation and comparison that have to be right.
