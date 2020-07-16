---
layout: docs-ja
title: ハイパーメディアAPI
category: Manual
permalink: /manuals/1.0/ja/hypermedia-api.html
---

# ハイパーメディアAPI

## HAL

BEAR.Sundayは[HAL](https://en.wikipedia.org/wiki/Hypertext_Application_Language)ハイパーメディア(`application/hal+json`)APIをサポートします。

HALのリソースモデルは以下の要素で構成されます。

 * リンク
 * 埋め込みリソース
 * 状態

HALは従来のリソースの状態のみを表すJSONにリンクの`_links`と他リソースを埋め込む`_embedded`を加えたものです。HALはAPIを探索可能にしてそのAPIドキュメントをAPI自体から発見することができます。

### Links

以下は有効なHALの例です。自身(`self`)のURIへのリンクを持っています。

```
{
    "_links": {
        "self": { "href": "/user" }
    }
}
```

### Link Relations

リンクには`rel`（relation）があり、どの様な関係でリンクされているかを表ます。HTMLの`<link>`タグや`<a>`タグで使われる`rel`と同様です。

```
{
    "_links": {
        "next": { "href": "/page=2" }
    }
}
```

HALについてさらに詳しくは[http://stateless.co/hal_specification.html](http://stateless.co/hal_specification.html)をご覧ください。

## リソースクラス

アノテーションでリンクを貼ったり他のリソースを埋め込むことができます。

### @Link

リンクが静的なものは`@Link`アノテーションで表し、動的なものは`body['_links']`に代入します。宣言的に記述できる`@Link`アノテーションが良いでしょう。

```php?start_inline
/**
 * @Link(rel="user", href="/user")
 * @Link(rel="latest-post", href="/latest-post", title="latest post entrty")
 */
public function onGet()
```

or

```php?start_inline
public function onGet() {
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

### @Embed

他のリソースを静的に埋め込むには`@Embed`アノテーションを使い、動的に埋め込むには`body`にリクエストを代入します。

```php?start_inline
/**
 * @Embed(rel="todos", src="/todos{?status}")
 * @Embed(rel="me", src="/me")
 */
public function onGet(string $status) : ResourceObject

```

or

```php?start_inline
$this->body['_embedded']['todos'] = $this->resource->uri('app://self/todos');
```

## CURIEs

APIの見つけやすさ(API Discoverability)を実現するために`HAL`は[CURIEs]()を使います。

それぞれのAPIのドキュメントへのリンクを貼った`index.json`、またはこのようなリソースクラスをルートに設置します。

```php
<?php

use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    public $body = [
        'message' => 'Welcome to the Polidog.Todo API ! Our hope is to be as self-documenting and RESTful as possible.',
        '_links' => [
            'self' => [
                'href' => '/',
            ],
            'curies' => [
                'name' => 'doc',
                'href' => 'http://apidoc.example.com/rels/{?rel}',
                'templated' => true
            ],
            'doc:todo' => [
                'href' => '/todo/{id}',
                'title' => 'todo item',
                'templated' => true
            ]
        ]
    ];

    public function onGet()
    {
        return $this;
    }
}
```

`_links`内で`curies`というドキュメントを定義する特別なトークンを指定します。`curies`では、リソースのドキュメントURIを示す`href`とその名前を`name`で指定します。

この例では`todo`リソースに関するドキュメントを取得するためには`http://apidoc.example.com/rels/?rel=todo` URLにアクセスすれば良いと分かります。

## APIドキュメント

Curiesの設置されたAPIサーバーをAPIドキュメントサーバーにもすることができます。APIドキュメントの作成の手間や実際のAPIとのずれやその検証、メンテナンスといった問題を解決します。

サービスするためには`bear/api-doc`をインストールして`BEAR\ApiDoc\ApiDoc`ページクラスを継承して設置します。

```
composer require bear/api-doc
```

```php
<?php
namespace MyVendor\MyPorject\Resource\Page\Rels;

use BEAR\ApiDoc\ApiDoc;

class Index extends ApiDoc
{
}
```

Json Schemaのフォルダをwebに公開します。

```
ln -s var/json_schema public/schemas
```

DocblockコメントとJson Shcemaを使ってAPIドキュメントが自動生成されます。ページクラスは独自のレンダラーを持ち`$context`の影響を受けないで、人のためのドキュメント(`text/html`) をサービスします。`$context`の影響を受けないので`App`、`Page`どちらでも設置可能です。

CURIEsがルートに設置されていれば、API自体がハイパーメディアではない生JSONの場合でも利用可能です。リアルタイムに生成されるドキュメントは常にプロパティ情報やバリデーション制約が正確に反映されます。

### デモ

```
git clone https://github.com/koriym/Polidog.Todo.git
cd Polidog.Todo/
composer install
composer setup
composer serve
```

[docs/index.md](https://github.com/koriym/Polidog.Todo/blob/master/docs/index.md)にAPI docが作成されます。

## ブラウズ可能

HALで記述されたAPIセットは**ヘッドレスのRESTアプリケーション**として機能します。

WebベースのHAL BrowserやコンソールのCURLコマンドでWebサイトと同じようにルートからリンクを辿って始めて全てのリソースにアクセスできます。

 * [HAL Browser](https://github.com/mikekelly/hal-browser) - [example](http://haltalk.herokuapp.com/explorer/browser.html#/)
 * [hyperagent.js](https://weluse.github.io/hyperagent/)


## Siren

[Siren](https://github.com/kevinswiber/siren)ハイパーメディア(`application/vnd.siren+json`)をサポートした[Sirenモジュール](https://github.com/kuma-guy/BEAR.SirenModule) も利用可能です。
