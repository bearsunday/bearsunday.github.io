---
layout: docs-en
title: Event Sourcing
category: Manual
permalink: /manuals/1.0/en/event-sourcing.html
---

# Event Sourcing

[bear/event-sourcing](https://github.com/bearsunday/BEAR.EventSourcing) derives immutable events from Semantic Logger observations of resource execution. Your domain code dispatches nothing: the write model is the resource, and events are extracted from what the application was observed doing.

```text
Semantic Logger observations -> Events -> optional EventStore
```

Every event is a resource operation (a `method` on a `uri`, like `POST app://self/users`), so the same stream doubles as an audit history: what happened, when, to which resource.

## Installation

```bash
composer require bear/event-sourcing
```

The core requires only `koriym/semantic-logger` and `ray/di`. Two features are optional: the BEAR.Resource observation bridge (`bear/resource`) and the SQL event store (`ray/media-query`).

## Recording and observation

The log and the event stream answer different questions. The event stream records what can be **reproduced**: the boundary write requests, the input a replay re-executes. The log observes what **happened**: every node (reads, failures, nested requests, durations) for transparency and debugging.

Extraction therefore takes root entries only. A `POST app://self/orders` whose handler issues `PUT app://self/inventory` yields one event, the POST. Replaying it re-executes the handler, which issues the PUT again; had the PUT been recorded as well, replay would apply it twice. The nested PUT stays in the log, as observation.

Replay by re-execution rests on two conditions the package assumes but does not enforce:

- **Handlers are deterministic.** A clock, a random value, or an external read the handler consults on its own makes a request unsafe to replay; pass such values in as params so they are recorded.
- **A request is a transaction boundary.** All of a request's writes commit or none of them do.

## Observing resource execution

The bridge uses the optional `bear/resource`:

```bash
composer require bear/resource
```

`ResourceObservationModule` decorates the real `InvokerInterface` and writes one open/close pair per resource invocation:

```php
use BEAR\EventSourcing\Resource\ResourceObservationModule;
use BEAR\Resource\Module\ResourceClientModule;
use Ray\Di\Injector;

$injector = new Injector(new ResourceObservationModule(
    module: new ResourceClientModule(),
));
```

By default the bridge installs `NullBodyStore`, so no response body is persisted. The bridge never flushes: the application owns the request boundary. Flush once per request and extract from the returned log:

```php
use BEAR\EventSourcing\SemanticLogExtractor;

$log = $semanticLogger->flush();
$events = (new SemanticLogExtractor())->extract($log);
```

`EventCollector` packages flush -> extract -> optional append into one call for a request-end handler. Pass an `EventStoreInterface` as the third argument to append as well:

```php
use BEAR\EventSourcing\EventCollector;

$collect = new EventCollector($logger, $extractor);

$events = $collect(); // once per request, at the boundary
```

For local AI/debug work use `DevLogModule`: it records `GET` as well as writes and stores rendered bodies as files behind `body_ref` pointers, so the log tree stays small while the payloads remain inspectable:

```php
use BEAR\EventSourcing\Resource\DevLogModule;
use BEAR\Resource\Module\ResourceClientModule;
use Ray\Di\Injector;

$injector = new Injector(new DevLogModule(
    bodyDir: __DIR__ . '/var/es/bodies',
    module: new ResourceClientModule(),
));
```

Rendered as a tree, an observed request reads as intent in, result out. A resource calling a resource nests as parent and child:

```text
request="POST app://self/orders?order_id=O-1000"
├── request="PUT app://self/inventory/SKU-1?sku=SKU-1&quantity=1"
│   └── code=200 body_ref=file://var/es/bodies/000001.json
└── code=201 body_ref=file://var/es/bodies/000002.json
```

## What is an event

An `Event` carries `uri`, `method`, `params`, `timestamp`, `result`, and a deterministic `id` (a sha256 of method, uri, UTC-normalized timestamp, and key-sorted params). Extracting the same log twice yields the same ids; that identity is what makes stores idempotent.

State-changing methods (`POST`/`PUT`/`PATCH`/`DELETE`) qualify by default. A root `GET` qualifies only under the opt-in read policy:

```php
use BEAR\EventSourcing\Module\EventSourcingModule;
use BEAR\EventSourcing\RecordedMethods;

$this->install(new EventSourcingModule(
    methods: new RecordedMethods(RecordedMethods::WITH_READS),
));
```

## Filtering and replay

`Events` is a countable, iterable collection with no query methods; select with PHP's standard iterators:

```php
use BEAR\EventSourcing\Event;

$orderEvents = new CallbackFilterIterator(
    $events->getIterator(),
    static fn (Event $event): bool => str_starts_with($event->uri, 'app://self/orders'),
);
```

## Storing events

The SQL store uses the optional `ray/media-query` and `ray/aura-sql-module`:

```bash
composer require ray/media-query ray/aura-sql-module
```

`EventStoreInterface` is a small persistence port (`append`, `appendAll`, `all`), not a runtime hook. Appending is idempotent per `Event::$id`, so retrying a batch never duplicates facts. Use `InMemoryEventStore` for tests; use `MediaQueryEventStore` for SQL through Ray.MediaQuery, where the database stays application-owned:

```php
use BEAR\EventSourcing\EventStoreInterface;
use BEAR\EventSourcing\Module\EventSourcingModule;
use BEAR\EventSourcing\Module\MediaQueryEventStoreModule;
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\Di\AbstractModule;
use Ray\MediaQuery\MediaQuerySqlModule;

final class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $packageDir = __DIR__ . '/vendor/bear/event-sourcing';
        $this->install(new AuraSqlModule('sqlite:' . __DIR__ . '/events.sqlite'));
        $this->install(new MediaQuerySqlModule(
            interfaceDir: $packageDir . '/src/Query',
            sqlDir: $packageDir . '/sql/event_store',
        ));
        $this->install(new EventSourcingModule());
        $this->install(new MediaQueryEventStoreModule());
    }
}
```

Apply `sql/event_store/schema.sql` with your migration tool first; `event_id` is UNIQUE: that constraint is what makes appends idempotent. `appendAll` is not atomic, so wrap a batch in your own transaction when you need all-or-nothing.

## One tree with BEAR.QueryRepository

BEAR.QueryRepository's semantic cache log records through a `#[CacheLog]`-qualified `SemanticLoggerInterface`. Bind the same instance under both keys and the two observations become one tree: cache scopes nest inside the `resource_request` scope that caused them.

```php
use BEAR\RepositoryModule\Annotation\CacheLog;
use Koriym\SemanticLogger\SemanticLogger;
use Koriym\SemanticLogger\SemanticLoggerInterface;

$logger = new SemanticLogger();

$this->install(new DevLogModule($bodyDir, logger: $logger, module: $appModule));
$this->bind(SemanticLoggerInterface::class)->annotatedWith(CacheLog::class)->toInstance($logger);
```

Extraction stays safe in the merged tree: only `resource_request` entries become events, so cache scopes are never misread as state changes.

## Demo and schemas

Every context names its JSON Schema (`schemaUrl`); the canonical files are published at [bearsunday.github.io/BEAR.EventSourcing/schemas](https://bearsunday.github.io/BEAR.EventSourcing/schemas/). The repository ships a live walkthrough. `composer observe` runs a real application end to end: nested writes, body externalization, tree rendering, extraction, deterministic ids, both stores, replay, and schema validation that fails the demo on a contract break.

See the [README](https://github.com/bearsunday/BEAR.EventSourcing) for operational notes: worker-runtime flush rules, sharing `#[SqlDir]` with an existing Ray.MediaQuery setup, and wiring inside a BEAR.Sunday context.
