---
layout: docs-ja
title: API Doc
category: Manual
permalink: /manuals/1.0/ja/apidoc.html
---

# API Doc

BEAR.ApiDocは、アプリケーションからAPIドキュメントを生成します。コードとJSONスキーマから自動生成されるドキュメントは、手間を減らし正確なAPIドキュメントを維持し続けることができます。

## 利用方法

BEAR.ApiDocをインストールします。

```
composer require bear/api-doc --dev
```

設定ファイルをコピーします。

```
cp ./vendor/bear/api-doc/apidoc.xml.dist ./apidoc.xml
```

## ソース

BEAR.ApiDocはphpdoc、メソッドシグネチャ、JSONスキーマから情報を取得してドキュメントを生成します。

#### PHPDOC

phpdocでは以下の部分が取得されます。認証などリソースに横断的に適用される情報は別のドキュメントページを用意して`@link`でリンクします。

```php
/**
 * {title}
 *
 * {description}
 *
 * {@link htttp;//example.com/docs/auth 認証}
 */
class Foo extends ResourceObject { }
```

```php
/**
 * {title}
 *
 * {description}
 *
 * @param string $id ユーザーID
 */
public function onGet(string $id = 'kuma'): static { }
```

* メソッドのphpdocに`@param`記述が無い場合、メソッドシグネチャーから引数の情報を取得します。
* 情報取得の優先順はphpdoc、JSONスキーマ、プロファイルの順です。

## 設定ファイル

設定はXMLで記述されます。最低限の指定は以下の通りです。

```xml
<?xml version="1.0" encoding="UTF-8"?>
<apidoc
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="https://bearsunday.github.io/BEAR.ApiDoc/apidoc.xsd">
    <appName>MyVendor\MyProject</appName>
    <scheme>app</scheme>
    <docDir>docs</docDir>
    <format>html</format>
</apidoc>
```

### 必須属性

#### appName
アプリケーションの名前空間

#### scheme
APIドキュメントにするスキーマ名。`page`または`app`

#### docDir
出力ディレクトリ名

#### format
出力フォーマット。HTMLまたはMD (Markdown)

### オプション属性

#### title
APIタイトル
```xml
<title>MyBlog API</title>
```

#### description
APIディスクリプション
```xml
<description>MyBlog API description</description>
```

#### links
リンク。`href`でリンク先URL、`rel`でその内容を表します。
```xml
<links>
    <link href="https://www.example.com/issue" rel="issue" />
    <link href="https://www.example.com/help" rel="help" />
</links>
```

#### alps
APIで使われる語句を定義する"ALPSプロファイル"を指定します。
```xml
<alps>alps/profile.json</alps>
```

## プロファイル

BEAR.ApiDocはアプリケーションに追加情報を与える[RFC 6906 プロファイル](https://tools.ietf.org/html/rfc6906)の[ALPS](http://alps.io/)フォーマットをサポートします。

APIのリクエストやレスポンスのキーで使う語句をセマンティックディスクリプタ（意味的記述子）と呼びますが、プロファイルでその辞書を作っておけばリクエスト毎に語句を説明する必要がなくなります。語句の定義が集中することで表記揺れを防ぎ、理解共有を助けます。

以下は`firstName`,`familyName`というディスクリプタをそれぞれ`title`、`def`で定義した例です。`title`は言葉を記述して意味を明らかにしますが、`def`は[Schema.org](https://schema.org/)などのボキャブラリサイトで定義されたスタンダードな語句をリンクします。

ALPSプロファイルはXMLまたはJSONで記述します。

profile.xml
```xml
<?xml version="1.0" encoding="UTF-8"?>
<alps
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="https://alps-io.github.io/schemas/alps.xsd">
    <!-- Ontology -->
    <descriptor id="firstName" title="The person's first name."/>
    <descriptor id="familyName" def="https://schema.org/familyName"/>
</alps>
```

profile.json
```json
{
    "$schema": "https://alps-io.github.io/schemas/alps.json",
    "alps": {
        "descriptor": [
            {"id": "firstName", "title": "The person's first name."},
            {"id": "familyName", "def": "https://schema.org/familyName"}
        ]
    }
}
```

ApiDocに登場する語句の説明はphpdoc > JsonSchema > ALPSの順で優先します。

## リンク

* [Demo](https://bearsunday.github.io/BEAR.ApiDoc/)
* [ALPS](http://alps.io/)
* [ALPS-ASD](https://github.com/koriym/app-state-diagram)
* [メディアタイプとALPSプロファイル](https://qiita.com/koriym/items/2e928efb2167d559052e)
