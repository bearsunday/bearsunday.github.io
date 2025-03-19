---
layout: docs-ja
title: ハイパーメディアAPI
category: Manual
permalink: /manuals/1.0/ja/hypermedia-api.html
---

# ハイパーメディアAPI

## HAL

BEAR.Sundayは[HAL](https://en.wikipedia.org/wiki/Hypertext_Application_Language)ハイパーメディア（`application/hal+json`）APIをサポートしています。HALのリソースモデルは以下の要素で構成されます：

* リンク
* 埋め込みリソース
* 状態

HALは、従来のリソースの状態のみを表すJSONに、リンクの`_links`と他リソースを埋め込む`_embedded`を加えたものです。HALはAPIを探索可能にし、そのAPIドキュメントをAPI自体から発見することができます。

### Links

以下は有効なHALの例です。自身（`self`）のURIへのリンクを持っています：

```json
{
    "_links": {
        "self": { "href": "/user" }
    }
}
```

### Link Relations

リンクには`rel`（relation）があり、どのような関係でリンクされているかを表します。HTMLの`<link>`タグや`<a>`タグで使われる`rel`と同様です：

```json
{
    "_links": {
        "next": { "href": "/page=2" }
    }
}
```

HALについてさらに詳しくは[http://stateless.co/hal_specification.html](http://stateless.co/hal_specification.html)をご覧ください。

## リソースクラス

アノテーションを使用してリンクを貼ったり、他のリソースを埋め込んだりすることができます。

### #[Link]

リンクが静的なものは`#[Link]`属性で表し、動的なものは`body['_links']`に代入します。宣言的に記述できる`#[Link]`属性の使用を推奨します：

```php
#[Link(rel="user", href="/user")]
#[Link(rel="latest-post", href="/latest-post", title="latest post entry")]
public function onGet()
```

または：

```php
public function onGet() 
{
    // 権限のある場合のみリンクを貼る
    if ($hasCommentPrivilege) {
        $this->body += [
            '_links' => [
                'comment' => [
                    'href' => '/comments/{post-id}',
                    'templated' => true
                ]
            ]
        ];
    }
}
```

### #[Embed]

他のリソースを静的に埋め込むには`#[Embed]`アトリビュートを使い、動的に埋め込むには`body`にリクエストを代入します：

```php
#[Embed(rel="todos", src="/todos{?status}")]
#[Embed(rel="me", src="/me")]
public function onGet(string $status): static
```

または：

```php
$this->body['_embedded']['todos'] = $this->resource->uri('app://self/todos');
```

## APIドキュメント

Curiesが設定されたAPIサーバーをAPIドキュメントサーバーとして使用できます。これにより、APIドキュメントの作成の手間や、実際のAPIとの整合性の問題、検証やメンテナンスといった課題を解決できます。

サービスを提供するには、`bear/api-doc`をインストールして`BEAR\ApiDoc\ApiDoc`ページクラスを継承して設置します：

```bash
composer require bear/api-doc
```

```php
<?php
namespace MyVendor\MyProject\Resource\Page\Rels;

use BEAR\ApiDoc\ApiDoc;

class Index extends ApiDoc
{
}
```

JSON Schemaのフォルダをwebに公開します：

```bash
ln -s var/json_schema public/schemas
```

DocblockコメントとJSON Schemaを使ってAPIドキュメントが自動生成されます。ページクラスは独自のレンダラーを持ち、`$context`の影響を受けずに人のためのドキュメント（`text/html`）をサービスします。

`$context`の影響を受けないため、`App`、`Page`どちらにも設置可能です。CURIEsがルートに設定されていれば、API自体がハイパーメディアではない通常のJSONの場合でも利用可能です。

リアルタイムに生成されるドキュメントは、常にプロパティ情報やバリデーション制約が正確に反映されます。

### デモ

```bash
git clone https://github.com/koriym/Polidog.Todo.git
cd Polidog.Todo/
composer install
composer setup
composer doc
```

[docs/index.md](https://github.com/koriym/Polidog.Todo/blob/master/docs/index.md)にAPI docが作成されます。

## ブラウズ可能

HALで記述されたAPIセットは**ヘッドレスのRESTアプリケーション**として機能します。WebベースのHAL BrowserやコンソールのcURLコマンドで、Webサイトと同じようにルートからリンクを辿って、すべてのリソースにアクセスできます：

* [HAL Browser](https://github.com/mikekelly/hal-browser) - [example](http://haltalk.herokuapp.com/explorer/browser.html#/)
* [hyperagent.js](https://weluse.github.io/hyperagent/)

## Siren

[Siren](https://github.com/kevinswiber/siren)ハイパーメディア（`application/vnd.siren+json`）をサポートした[Sirenモジュール](https://github.com/kuma-guy/BEAR.SirenModule)も利用可能です。
