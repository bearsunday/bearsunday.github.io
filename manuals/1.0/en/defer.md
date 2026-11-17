---
layout: docs-en
title: Deferred Execution
category: Manual
permalink: /manuals/1.0/en/defer.html
---

# Deferred Execution <sup style="font-size:0.5em; color:#666; font-weight:normal;">Alpha</sup>

BEAR.Defer moves heavy follow-up work *behind* the response. A resource accepts a request, returns `202 Accepted` immediately, and work such as search indexing, notification, or release-note generation runs after the response has reached the client.

The resource only declares *what* to defer. *When* and *where* it runs is decided outside the resource, so the resource code stays the same.

## Overview

```text
[normal execution] everything runs before the response
Request
│
├── save (light work)
├── update index (heavy work)
├── send notification (heavy work)
│
└── Response ──▶ client

[deferred execution] response returns first, heavy work runs after transfer
Request
│
├── save (light work)
├── Response 202 ──▶ client
│
│   (the rest runs after transfer)
├── update index (heavy work)
└── send notification (heavy work)
```

## When the response is returned early

Returning the response immediately and running the heavy work behind it depends on the runtime (SAPI):

- **PHP-FPM / LiteSpeed** — the connection is released and the client gets its response right away
- **Apache mod_php** — best-effort; an early return is not guaranteed, so run under PHP-FPM or LiteSpeed if you need it

The `#[Defer]` code is the same on every SAPI; switching environments switches the behavior automatically.

## Installation

```bash
composer require bear/defer
```

Wrap your existing responder module with `DeferModule` and install it.

```php
use BEAR\Defer\Module\DeferModule;

protected function configure(): void
{
    $this->install(new DeferModule(new YourHttpResponderModule()));
}
```

## Usage

### Declare what to defer

Annotate the accepting resource with `#[Defer]`, listing the `#[Link]` rels to defer. Each rel's `href` is resolved against the resource body after the method runs, so there are no hardcoded URIs.

```php
use BEAR\Defer\Attribute\Defer;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Article extends ResourceObject
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articles,
    ) {
    }

    #[Defer(['publish', 'release-note'])]
    #[Link(rel: 'publish',      href: 'app://self/article/publish{?id}', method: 'post')]
    #[Link(rel: 'release-note', href: 'app://self/release-note{?id}',    method: 'post')]
    public function onPost(string $title, string $body): static
    {
        $id = $this->articles->save($title, $body); // light work only
        $this->code = 202;                          // Accepted
        $this->body = ['id' => $id];

        return $this; // no defer call in the body
    }
}
```

The method contains no code that invokes the follow-up work. `#[Defer]` is a *declaration* that references `#[Link]`; the framework triggers the follow-up. The transition stays hypermedia-driven and surfaces in ALPS as a deferred transition.

### The follow-up resources are ordinary resources

They don't know they are deferred — any resource can be the target.

```php
class Publish extends ResourceObject
{
    public function onPost(string $id): static
    {
        $this->indexer->index($id);   // heavy work, runs after the response is sent
        $this->notifier->notify($id);

        return $this;
    }
}
```

### Conditional defer

When whether to run the follow-up work depends on a condition, inject `DeferInterface` directly and enqueue with `add()`.

```php
use BEAR\Defer\DeferInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class Article extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource,
        private readonly DeferInterface $defer,
    ) {
    }

    public function onPost(string $title, string $body, bool $publish = false): static
    {
        $id = $this->articles->save($title, $body);
        $this->code = 202;
        $this->body = ['id' => $id];

        if ($publish) {
            $request = $this->resource->post->uri('app://self/publish')->withQuery(['id' => $id]);
            $this->defer->add($request);
        }

        return $this;
    }
}
```

Use `#[Defer]` to always defer declaratively, or `add()` to defer based on a runtime condition.

## When to use it

Deferred work runs after the response is returned, so **a failure does not affect the client's response**. It fits follow-up work that does not need to reach the client immediately and can tolerate a delay or an occasional failure — search index updates, notifications, thumbnail generation, cache warming.

For work that must run reliably or be retried — billing, inventory reservation, records that cannot be lost — do not use deferred execution; use a job queue.

## Why it works without code changes

In BEAR.Sunday information is structured as resources addressed by URIs, and the relationships between resources are expressed declaratively. `#[Defer]` is a **declaration of a relationship** — "run this transition later" — not the execution procedure itself. Choosing whether it runs immediately or after transfer is done outside the resource, and the resource class never needs to know the difference.

This separation of execution strategy from code is not specific to BEAR.Defer; like [Parallel Execution](async.html), it is rooted in the design of BEAR.Sunday as a whole.

## See also

- [BEAR.Defer](https://github.com/bearsunday/BEAR.Defer)
- [Parallel Execution](async.html)
