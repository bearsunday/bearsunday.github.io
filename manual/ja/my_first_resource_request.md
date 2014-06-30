---
layout: default_ja
title: BEAR.Sunday | はじめてのリソースリクエスト
category: My First - Tutorial
--- 

# はじめてのリソースリクエスト

## アプリケーションリソースを利用します

今までのチュートリアルではコンソールやWebブラウザからリソースをリクエストしていましたが、このチュートリアルではリソースからリソースのリクエストを行います。

ここでは [はじめてのWebページ](my_first_web_page.html) で作成したページを [はじめてのリソース](my_first_resource.html) を利用したものに変更します。

ページリソースがアプリケーションリソースをリクエストします。
ページリソースはページに関心を持つリソースです。
通常、ページリソースは自身（ページ）を構成するために必要なアプリケーションリソースをリクエストして自らを構成します。

Note: 例えていうと、コントローラーが "Hello World" を返してたページを、モデルが返した "Hello World" を表示するページに変更します。

## リソースクライアントの準備

BEAR.Sundayでは必要なサービス（オブジェクト）は基本的に全て外部からインジェクトしてもらうのを期待します。
リソースリクエストにはリソースクライアントが必要です。

リソースクライアントインターフェイス（`BEAR\Resource\ResourceInterface`）をタイプヒントにして `@Inject` とアノテーションでマークしインジェクト（外部から代入）してもらいます。

{% highlight php startinline %}
<?php

use BEAR\Resource\ResourceInterface;
use Ray\Di\Di\Inject;

class User
{
    /**
     * @Inject
     */
    public function setResource(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }
{% endhighlight %}

## traitセッターの利用

このセッターはtraitとして用意されていてこのように表記できます。

{% highlight php startinline %}
<?php

use BEAR\Sunday\Inject\ResourceInject;

class User
{
    use ResourceInject;
}
{% endhighlight %}

## GETリクエスト

`app://self/first/greeting` というURIのアプリケーションリソースに `?name=$name` のクエリーを付けたリソースリクエストを行うのはこのようなメソッドになります。

*apps/Demo.Sandbox/src/Resource/Page/First/Greeting.php*

{% highlight php startinline %}
    /**
     * @param  string $name
     */
    public function onGet($name = 'anonymous')
    {
        $this['greeting'] = $this->resource
            ->get
            ->uri('app://self/first/greeting')
            ->withQuery(['name' => $name])
            ->request();
        
        return $this;
    }
{% endhighlight %}

ここで `greeting` スロットには値ではなく「リクエスト」をアサインしています。もし、`eager` 句を追加すれば、リクエストが評価されて値がアサインされます。

## **$_GET** クエリー

`$_GET['name']` の内容が引き数の `$name` に渡ります。
`$_GET['name']が存在しない場合はデフォルトの 'anonymous' が渡されます。

## コマンドラインでページを確認

`greeting` スロットには `app://self/first/greeting` リソースのリクエストが格納されました。

## APIとして確認

まずAPIとしてページリソースを確認します。

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get 'page://self/first/greeting?name=BEAR'

200 OK
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Mon, 30 Jun 2014 01:22:01 GMT"]
[BODY]
greeting => Demo\Sandbox\Resource\App\First\Greeting(
  uri app://self/first/greeting?name=BEAR,
  code 200,
  headers => array(
  ),
  view ,
  links => array(
  ),
  body Hello, BEAR,
),
...
```

`greeting` スロットの `body` に **Hello, BEAR** が渡されてます。クエリーを無くすとどうなるでしょうか。

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get 'page://self/first/greeting'

200 OK
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Mon, 30 Jun 2014 01:25:24 GMT"]
[BODY]
greeting => Demo\Sandbox\Resource\App\First\Greeting(
  uri app://self/first/greeting?name=anonymous,
  code 200,
  headers => array(
  ),
  view ,
  links => array(
  ),
  body Hello, anonymous,
),
...
```

デフォルトの値が代入されてるのが確認できます。

## ページテンプレートを用意

ページリソース用のテンプレートは同じです。

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<h1>{$greeting|escape}</h1>
</body>
</html>
```

## HTMLをコマンドラインで確認

```
$ php apps/Demo.Sandbox/bootstrap/contexts/dev.php get '/first/greeting?name=Sunday'

200 OK
cache-control: ["no-cache"]
date: ["Mon, 30 Jun 2014 01:30:37 GMT"]
[BODY]
greeting app://self/first/greeting?name=Sunday,

[VIEW]
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<h1>Hello, Sunday</h1>
</body>
</html>
```

We can see the resource view as well as resource value. We can confirm `greeting` slot has request with underlined URI.
リソースのビューとリソースの値の両方が表示されています。`greeting` スロットにURIが明示されたリクエストを確認できます。

このリクエストはビューで現れたときに評価されます。このケースでは違いはありませんが、条件によりリソースが出現するかどうかは変わりますので、遅延リクエストの方がよい場合もあります。

## ページのテスト

ページもリソースです。テストの仕方は [はじめてのテスト](my_first_test.html) で紹介をしたようにページリソースをテストします。
