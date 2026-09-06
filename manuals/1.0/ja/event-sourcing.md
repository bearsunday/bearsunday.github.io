---
layout: docs-ja
title: Event Sourcing
category: Manual
permalink: /manuals/1.0/ja/event-sourcing.html
---

# Event Sourcing

[bear/event-sourcing](https://github.com/bearsunday/BEAR.EventSourcing) は、Semantic Logger によるリソース実行の観測から不変のイベントを導出します。ドメインコードは何も dispatch しません。書き込みモデルはリソースそのもので、イベントは「アプリケーションが何をしたと観測されたか」から抽出されます。

```text
Semantic Logger observations -> Events -> optional EventStore
```

イベントはどれもリソース操作 — `POST app://self/users` のような `uri` への `method` — なので、同じストリームがそのまま監査履歴になります。何が、いつ、どのリソースに起きたか。

## インストール

```bash
composer require bear/event-sourcing
```

コアが要求するのは `koriym/semantic-logger` と `ray/di` だけです。BEAR.Resource 観測ブリッジ(`bear/resource`)と SQL イベントストア(`ray/media-query`)は、使うときだけ入れる optional な機能です。

## 記録と観測

ログとイベントストリームは別の問いに答えます。イベントストリームが記録するのは**再現**できるもの — 境界の書き込みリクエスト、つまり再生が再実行する入力です。ログが観測するのは**起きた**こと — 読み取り・失敗・入れ子のリクエスト・所要時間まで、すべてのノードです。透明性とデバッグはこちらが担います。

だから抽出はルートのエントリだけを取ります。handler の中で `PUT app://self/inventory` を発行する `POST app://self/orders` からは、イベントが 1 つ — POST だけ — 生まれます。再生で POST を再実行すれば handler がもう一度 PUT を発行するので、PUT まで記録してあると再生で二重に適用されてしまう。入れ子の PUT は観測としてログに残ります。

再実行による再生は、パッケージが前提にする(しかし強制はしない)2 つの条件の上に成り立ちます。

- **handler が決定的であること。** handler が自分で clock や乱数や外部の値を読むと、そのリクエストは再生できません。そうした値は params として渡し、記録に含めます。
- **リクエストがトランザクション境界であること。** 1 つのリクエストの書き込みは、全部 commit されるか、全部されないか。

## リソース実行の観測

`ResourceObservationModule` は実物の `InvokerInterface` を装飾し、リソース呼び出し 1 回につき open/close のペアを 1 つ書きます。

```php
use BEAR\EventSourcing\Resource\ResourceObservationModule;
use BEAR\Resource\Module\ResourceClientModule;
use Ray\Di\Injector;

$injector = new Injector(new ResourceObservationModule(
    module: new ResourceClientModule(),
));
```

既定では `NullBodyStore` が入るため、レスポンスボディは保存されません。ブリッジは flush しません。リクエスト境界を持つのはアプリケーションです。リクエストごとに 1 回 flush し、返ってきたログから抽出します。

```php
use BEAR\EventSourcing\SemanticLogExtractor;

$log = $semanticLogger->flush();
$events = (new SemanticLogExtractor())->extract($log);
```

flush -> 抽出 -> (必要なら)保存 を 1 呼び出しに束ねるのが `EventCollector` です。リクエスト終端の handler から呼びます。

```php
use BEAR\EventSourcing\EventCollector;

$collect = new EventCollector($logger, $extractor, $store); // store は省略可

$events = $collect(); // リクエストごとに 1 回、境界で
```

ローカルの AI・デバッグ作業には `DevLogModule` を使います。書き込みに加えて `GET` も記録し、レンダリング済みボディを `body_ref` ポインタの先のファイルに置くので、ログの木は小さいままボディも調べられます。

```php
use BEAR\EventSourcing\Resource\DevLogModule;
use BEAR\Resource\Module\ResourceClientModule;
use Ray\Di\Injector;

$injector = new Injector(new DevLogModule(
    bodyDir: __DIR__ . '/var/es/bodies',
    module: new ResourceClientModule(),
));
```

木として描画すると、観測されたリクエストは「意図が入って、結果が出る」形で読めます。リソースがリソースを呼べば、親子としてネストします。

```text
request="POST app://self/orders?order_id=O-1000"
├── request="PUT app://self/inventory/SKU-1?sku=SKU-1&quantity=1"
│   └── code=200 body_ref=file://var/es/bodies/000001.json
└── code=201 body_ref=file://var/es/bodies/000002.json
```

## イベントとは何か

`Event` が運ぶのは `uri`・`method`・`params`・`timestamp`・`result`、そして決定的な `id` — method、uri、UTC に正規化した timestamp、キーでソートした params の sha256 です。同じログを 2 回抽出すると同じ id が出ます。ストアの冪等性はこの同一性が支えています。

既定で対象になるのは状態を変えるメソッド(`POST`/`PUT`/`PATCH`/`DELETE`)です。ルートの `GET` は opt-in の読み取りポリシーを入れたときだけ対象になります。

```php
use BEAR\EventSourcing\Module\EventSourcingModule;
use BEAR\EventSourcing\RecordedMethods;

$this->install(new EventSourcingModule(
    methods: new RecordedMethods(RecordedMethods::WITH_READS),
));
```

## フィルタと再生

`Events` は countable で iterable なコレクションで、クエリメソッドを持ちません。選択は PHP 標準のイテレータで行います。

```php
use BEAR\EventSourcing\Event;

$orderEvents = new CallbackFilterIterator(
    $events->getIterator(),
    static fn (Event $event): bool => str_starts_with($event->uri, 'app://self/orders'),
);
```

## イベントの保存

`EventStoreInterface` は小さな永続化ポート(`append`・`appendAll`・`all`)で、ランタイムフックではありません。append は `Event::$id` ごとに冪等なので、バッチをリトライしても事実は重複しません。テストには `InMemoryEventStore` を、SQL には Ray.MediaQuery 経由の `MediaQueryEventStore` を使います。データベースはアプリケーションが所有します。

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

先に `sql/event_store/schema.sql` をマイグレーションツールで適用します。`event_id` の UNIQUE 制約が append の冪等性の実体です。`appendAll` はアトミックではないので、全か無かが要るバッチは自前のトランザクションで包みます。

## BEAR.QueryRepository と 1 本の木

BEAR.QueryRepository のセマンティックキャッシュログは `#[CacheLog]` で修飾された `SemanticLoggerInterface` に書きます。同じインスタンスを両方のキーに束縛すると、2 つの観測が 1 本の木になります。キャッシュのスコープが、それを起こした `resource_request` スコープの中にネストします。

```php
use BEAR\RepositoryModule\Annotation\CacheLog;
use Koriym\SemanticLogger\SemanticLogger;
use Koriym\SemanticLogger\SemanticLoggerInterface;

$logger = new SemanticLogger();

$this->install(new DevLogModule($bodyDir, logger: $logger, module: $appModule));
$this->bind(SemanticLoggerInterface::class)->annotatedWith(CacheLog::class)->toInstance($logger);
```

木が合流しても抽出は安全です。イベントになるのは `resource_request` エントリだけなので、キャッシュのスコープが状態変化と誤読されることはありません。

## デモとスキーマ

すべてのコンテキストが自分の JSON Schema を `schemaUrl` で名乗ります。正典のスキーマは [bearsunday.github.io/BEAR.EventSourcing/schemas](https://bearsunday.github.io/BEAR.EventSourcing/schemas/) で公開されています。リポジトリには実走するウォークスルーが入っていて、`composer observe` が実アプリケーションを端から端まで動かします — 入れ子の書き込み、ボディの外部化、木の描画、抽出、決定的 id、2 つのストア、再生、そして契約が壊れるとデモ自体が落ちるスキーマ検証まで。

worker ランタイムでの flush の規則、既存の Ray.MediaQuery 設定との `#[SqlDir]` の共有、BEAR.Sunday コンテキスト内での配線は [README](https://github.com/bearsunday/BEAR.EventSourcing) にあります。
