---
layout: default_ja
title: BEAR.Sunday | はじめてのWebページ
category: My First - Tutorial
---

# はじめてのWebページ

## Webページを作りましょう

## ページリソース

まず最初にアプリケーションリソースを利用しない最小限のページクラスを作成します。
（モデルを使わないコントローラーだけの **Hello World** ページのようなページです。)

## 最小構成のページから始めます

アプリケーションリソースがリソースの状態を構成したように、
ページリソースがページの状態を構成します。

挨拶が **Hello** と固定化されている静的なページです。

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\Resource\Page\First;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

/**
 * Greeting page
 */
class Greeting extends ResourceObject
{
    use ResourceInject;

    /**
     * @var array
     */
    public $body = [
        'greeting' => 'Hello.'
    ];

    public function onGet()
    {
        return $this;
    }
}
{% endhighlight %}

ページのコンテンツの `greeting` というスロットに `Hello.` という文字列を格納しています。
GETリクエストは呼ばれると何もしないで自身を返しています。 

## コマンドラインでページリソース状態確認します

このリソースをコマンドラインで確認してみましょう。

```
$ cd  {$PROJECT_PATH}/apps/Demo.Sandbox/bootstrap/contexts/
$ php api.php get page://self/first/greeting

200 OK
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Sun, 29 Jun 2014 09:11:01 GMT"]
[BODY]
greeting Hello.,
...
```

`greeting` というスロットに `Hello.` という文字列が入っているのが確認できました。

## ページリソースの状態を表現にします

このページリソースの状態をHTML表現としてレンダリングするためにテンプレートが必要です。
リソースと同じ場所に拡張子だけ変更します。

### ファイルパス

|URI|リソースクラス| リソーステンプレート |
|---|--------------|-------------------|
|page://self/first/greeting | apps/Demo.Sandbox/Resource/Page/First/Greeting.php | apps/Demo.Sandbox/Resource/Page/First/Greeting.tpl |

### テンプレート

```html
<!DOCTYPE html>
<html lang="en">
<body>
<head>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<h1>{$greeting|escape}</h1>
</body>
</html>
```

## コマンドラインでページHTMLを確認します

リソースの状態をテンプレートにアサインしてレンダリングするとリソースのHTML表現になります。
つまりHTMLページになります。これもコマンドラインで 確認することができます。 

では確認してみましょう。

```
$ php dev.php get /first/greeting
```

```html
200 OK
cache-control: ["no-cache"]
date: ["Sun, 29 Jun 2014 09:15:37 GMT"]
[BODY]
greeting Hello.,

[VIEW]
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<h1>Hello.</h1>
</body>
</html>
```

HTMLが確認できました。

## WebブラウザでページHTMLを確認します

```
$ php -S localhost:8088 dev.php
```

http://localhost:8088/first/greeting にアクセスします。
無事ページが見えたでしょうか？

## ページの役割、RESTとは？

ページは自身を構成するために必要な情報のかたまり（リソース）を集めます。
ここでは１つの `greeting` というスロットに `Hello.` という文字列の情報を格納しましたが、多くのページは複数のスロットがあるでしょう。

ページの役割はページを構成する複数のリソースを合成しページの状態を決定する事です。
ページのリソース状態はリソーステンプレートと合成されリソース表現になりHTML等としてユーザーに転送されます。

リソース状態（REpresentational State）のTransfer（転送）、それがRESTです。
