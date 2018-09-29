---
layout: docs-ja
title: コンテントネゴシエーション
category: Manual
permalink: /manuals/1.0/ja/content-negotiation.html
---

# コンテントネゴシエーション

HTTPにおいてコンテントネゴシエーション ([content negotiation](https://en.wikipedia.org/wiki/Content_negotiation)) は、同じ URL に対してさまざまなバージョンのリソースを提供するために使用する仕組みです。BEAR.Sundayではその内のメディアタイプの`Accept`と言語の`Accept-Language`のサーバーサイドのコンテントネゴシエーションをサポートします。アプリケーション単位またはリソース単位で指定することができます。

## インストール

composerで[BEAR.Accept](https://github.com/bearsunday/BEAR.Accept)をインストールします。

```bash
composer require bear/accept ^0.1
```

次に`Accept*`リクエストヘッダーに応じたコンテキストを`/var/locale/available.php`に保存します。


```php?
<?php
return [
    'Accept' => [
        'text/hal+json' => 'hal-app',
        'application/json' => 'app',
        'cli' => 'cli-hal-app'
    ],
    'Accept-Language' => [ // キーを小文字で
        'ja-jp' => 'ja',
        'ja' => 'ja',
        'en-us' => 'en',
        'en' => 'en'
    ]
];
```

`Accept`キー配列はメディアタイプをキーにしてコンテキストが値にした配列を指定します。`cli`はコンソールアクセスでのコンテキストでwebアクセスで使われることはありません。

`Accept-Language`キー配列は言語をキーにしてコンテキストキーを値した配列を指定します。

## アプリケーション

アプリケーション全体でコンテントネゴシエーションを有効にするために`public/index.php`を変更します。

```php
<?php
use BEAR\Accept\Accept;

require dirname(__DIR__) . '/vendor/autoload.php';

$accept = new Accept(require dirname(__DIR__) . '/var/locale/available.php');
list($context, $vary) = $accept($_SERVER);

require dirname(__DIR__) . '/bootstrap/bootstrap.php';
```

上記の設定で例えば以下の`Accept*`ヘッダーのアクセスのコンテキストは`prod-hal-ja-app`になります。

```
Accept: application/hal+json
Accept-Language: ja-JP
```

この時`JaModule`で日本語テキストのための束縛が必要です。詳しくはデモアプリケーション[MyVendor.Locale](https://github.com/koriym/MyVendor.Locale)をごらんください。

## リソース

リソース単位でコンテントネゴシエーションを行う場合は`AcceptModule`モジュールをインストールして`@Produces`アノテーションを使います。

### モジュール

```php?start_inline
protected function configure()
{
    // ...
    $available = $appDir . '/var/locale/available.php';
    $this->install(new AcceptModule(available));
}
```

## @Producesアノテーション

```php?start_inline
use use BEAR\Accept\Annotation\Produces;

/**
 * @Produces({"application/hal+json", "text/csv"})
 */
public function onGet()
```

利用可能なメディアタイプを左から優先順位でアノテートします。対応したコンテキストのレンダラーがAOPでセットされ表現が変わります。アプリケーション単位でのネゴシエーションの時と違って、`Vary`ヘッダーを手動で付加する必要はありません。

## curlを使ったアクセス

`-H`オプションで`Accept*`ヘッダーを指定します。

```
curl -H 'Accept-Language: en' http://127.0.0.1:8080/
```

```
curl -i -H 'Accept-Language: en' -H 'Accept: application/hal+json' http://127.0.0.1:8080/
```

```
HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Date: Fri, 11 Aug 2017 08:32:33 +0200
Connection: close
X-Powered-By: PHP/7.1.4
Vary: Accept, Accept-Language
content-type: application/hal+json

{
    "greeting": "Hello BEAR.Sunday",
    "_links": {
        "self": {
            "href": "/index"
        }
    }
}
```
