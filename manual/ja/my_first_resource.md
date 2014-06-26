---
layout: default_ja
title: BEAR.Sunday | はじめてのリソース
category: My First - Tutorial
---

# はじめてのリソース

## アプリケーションリソース

ここでは `name` を渡すと挨拶を返してくれる `greeting` リソースをつくってみます。
MVCでいうと `モデル` にあたる部分をBEAR.Sundayでは `アプリケーション（app）リソース` と呼びます。
アプリケーションリソースはアプリケーションの内部APIとして利用されます。

## リソース設計

リソースとは情報のかたまりです。
ここでは挨拶（`greeting`）が「挨拶リソース」として使われます。
リソースオブジェクトクラスには以下のものが必要です。

 * URI
 * リクエストインターフェイス

ここではこういう風に決めました。

| メソッド | URI                         | クエリー    |
|--------|-----------------------------|------------|
| get    | app://self/first/greeting   |?name=名前  |

期待するgreetingリソースはこういうものです。

リクエスト

```
get app://self/first/greeting?name=BEAR
```

レスポンス

```
Hello, BEAR.
```

## リソースオブジェクト

Sandboxアプリケーションに実装します。URIとPHPのクラス、ファイル位置はこのように対応します。

| URI | Class | File |
|-----|--------|-----|
| app://self/first/greeting | Demo\Sandbox\Resource\App\First\Greeting | apps/Demo.Sandbox/src/Sandbox/Resource/App/First/Greeting.php |

リクエストインターフェイス（メソッド）を実装します。

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\Resource\App\First;

use BEAR\Resource\ResourceObject;

/**
 * Greeting resource
 */
class Greeting extends ResourceObject
{
    /**
     * @param string $name
     *
     * @return string
     */
    public function onGet($name)
    {
        return "Hello, {$name}";
    }
}
{% endhighlight %}

## コマンドラインで試してみましょう

コンソールから入力します。まずは *失敗* から。

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get app://self/first/greeting
```

400 Bad Requestのレスポンスが帰ってきます。

```
400 Bad Request
...
[BODY]
```

ヘッダーをみると例外発生の情報があり、
クエリーに `name` が必要だというこ とがわかります。
*`OPTIONS` メソッド* を使ってもっと正確に調べてみることができます。

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php options app://self/first/greeting

200 OK
allow: ["get"]
param-get: ["name"]
...
```

このリソースは `GET` メソッドだけが有効で、パラメーターは１つ、`name` が必要だというのが分かります。
もしこの `name` パラメーターがオプションであるなら `(name)` と表示されます。
では引き数がOPTIONSメソッドでわかったところで再度試してみます。

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get app://self/first/greeting?name=BEAR

200 OK
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Thu, 26 Jun 2014 11:25:07 GMT"]
[BODY]
Hello, BEAR
```

今度は正しいレスポンスが返ってきました。成功です！

## リソースオブジェクトが返ります

この挨拶リソース実装では文字列を返していますが、
以下の記述と同じものとして扱われます。
どちらの記述でもリクエストしたクライアントのはリソースオブジェクトが返ります。

{% highlight php startinline %}
public function onGet($name)
{
    $this->body = "Hello, {$name}";
    return $this;
}
{% endhighlight %}

`onGet` メソッド内をこのように変えてレスポンスが変わらない事を確認してみましょう。
