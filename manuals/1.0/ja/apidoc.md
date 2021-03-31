---
layout: docs-ja
title: API Doc
category: Manual
permalink: /manuals/1.0/ja/apidoc.html
---
# API Doc
(WIP)

BEAR.ApiDocは、アプリケーションからAPIドキュメントを生成します。

コードとJSONスキーマから自動生成されるドキュメントは、APIドキュメントと実際のアプリケーションがマッチします。
IDLを書く手間が省け、正確なドキュメントを維持し続けることができます。

## 利用方法

BEAR.ApiDocをインストールします。

    composer require bear/api-doc 1.x-dev --dev

`bin/doc.php`に生成スクリプトを用意します。


```php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use BEAR\ApiDoc\DocApp;

$docApp = new DocApp('MyVendor\MyProject');
$docApp->dumpHtml('/path/to/docs', 'app');
```

アプリケーションのnamespaceを指定して `DocApp `を生成、`dumpHtml()`でドキュメントの出力先とドキュメントを出力するページスキーマ`app`または`page`を選びます。

`php bin/doc.php`実行でドキュメントが生成されます。composer scriptコマンドに登録してもいいでしょう。

## プロファイル

BEAR.ApiDocはアプリケーションに追加情報を与える[RFC 6906 プロファイル](https://tools.ietf.org/html/rfc6906)の[ALPS](http://alps.io/)フォーマットをサポートします。

APIのリクエストやレスポンスのキーで使う語句をセマンティックディスクリプタ（意味的記述子）と呼びます。プロファイルで語句（セマンティックディスクリプタ）の辞書を作っておけば、リクエスト毎に語句を説明する必要がなくなります。語句の定義が集中することで表記揺れを防ぎ、理解共有を助ける効果もあります。

## ALPSプロファイル

以下は`firstName`,`familyName`というセマンティくディスクリプタを定義した例です。

profile.json

```json
{
  "$schema": "https://alps-io.github.io/schemas/alps.json",
  "alps": {
    "descriptor": [
      {"id": "firstName", "title": "The person's first name."},
      {"id": "familyName", "def": "https://schema.org/familyName"},
    ]
  }
}
```

`firstName`は`title`によって文章で説明されています。`familyName`は[schema.org](https://schema.org)で定義されている語句を`def`でリンクする事で語句を定義しています。定義するとJSONスキーマやPHPDOCで再説明しなくてもAPIドキュメントに反映されます。

プロファイルを使って出力するにはdumpHtml()の三番目の引数でプロファイルを指定します。

```
$docApp->dumpHtml('/path/to/docs', 'app', 'path/to/profile.json');
```

## ソース

BEAR.ApiDocはphpdoc、メソッドシグネチャ、JSONスキーマから情報を取得してドキュメントを生成します。

#### PHPDOC

phpdocでは以下の部分が取得されます。
認証などリソースに横断的に適用される情報は別のドキュメントページを用意して`@link`でリンクします。


```php
/**
 * {title}
 *
 * {description}
 *
 * {@link htttp;//example.com/docs/auth 認証}
 */
 class Foo extends ResourceObject
 {
 }
```

```php
/**
 * {title}
 *
 * {description}
 *
 * @param string $id ユーザーID
 */
 public function onGet(string $id ='kuma'): static
 {
 }
```

* メソッドのphpdocに`@param`記述が無い場合、メソッドシグネチャーから引数の情報を取得します。
* 情報取得の優先順はphpdoc、JSONスキーマ、プロファイルの順です。

## リンク

* [ALPS](http://alps.io/)
* [ALPS-ASD](https://github.com/koriym/app-state-diagram)
* [メディアタイプとALPSプロファイル](https://qiita.com/koriym/items/2e928efb2167d559052e)