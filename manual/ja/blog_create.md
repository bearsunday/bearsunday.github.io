---
layout: default_ja
title: BEAR.Sunday | ブログチュートリアル(6) 記事の追加
category: Blog Tutorial
---

# POSTメソッド

これまでのステップでデータベースに登録されている記事を表示できるようになりました。次はいよいよフォームを作成しますが、まずはその前にコンソールのリソース操作で記事を追加できるようにしましょう。

## 記事リソースのPOSTインターフェイスを作成

GETインターフェイスメソッドしかない記事リソースに記事を追加することのできるPOSTインターフェイスを加えます。

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

{% highlight php startinline %}
public function onPost($title, $body, $created = null, $modified = null)
{
    return $this;
}
{% endhighlight %}

まず、この状態でPOSTしてみましょう。

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php post 'app://self/blog/posts'

400 Bad Request
x-exception-class: ["BEAR\\Resource\\Exception\\SignalParameter"]
x-exception-message: ["$title in Demo_Sandbox_Resource_App_Blog_Posts_cc436baec58dbc6a06237a589e2e39d8RayAop::onPost"]
x-exception-code-file-line: ["(0) ...\/vendor\/bear\/resource\/src\/SignalParameter.php:62"]
x-exception-previous: ["BEAR\\Resource\\Exception\\Parameter: $title in Demo_Sandbox_Resource_App_Blog_Posts_cc436baec58dbc6a06237a589e2e39d8RayAop::onPost"]
x-exception-id: ["e500-efa6b"]
x-exception-id-file: ["...\/vendor\/bear\/demo-apps\/Demo.Sandbox\/var\/log\/e500-efa6b.log"]
cache-control: ["no-cache"]
date: ["Tue, 24 Jun 2014 00:45:57 GMT"]
[BODY]
```

必要な引数を指定していないので、リクエスト不正という *400 Bad Request* のレスポンスが帰ってきました。

リソースリクエストに必要な引数は `options` メソッドで調べる事ができます。（@TODO optionsメソッドが現在すべてのメソッドの引数を表示しない）

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php options 'app://self/blog/posts'

200 OK
allow: ["get","post"]
param-post: ["title,body,(created),(modified)"]
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Tue, 24 Jun 2014 00:47:25 GMT"]
[BODY]
*NULL
[VIEW]
{
    "_links": {
        "self": {
            "href": "http://localhost/app/blog/posts/"
        }
    }
}
```

利用可能なメソッドは `allow` ヘッダーで表され、続いてそれぞれに利用可能な引数が表示されます。括弧で囲まれているのはオプション指定で省力可能です。

例えばGETメソッドは、引数なし、あるいは `id` を指定してリクエストします。POSTメソッドは *かならず* `title` と `body` が必要です。

必要な指定引数が明らかになりました。次はクエリーを付けてリクエストします。

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php post 'app://self/blog/posts?title=hello&body=this%20is%20first%20post'

200 OK
...
[BODY]
*NULL
...
```

コンテンツNULLの200 OKが帰ってきました。
問題はありませんが、*もっと* 正確な204（No Content）のステータスコードに変更してみましょう。

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

{% highlight php startinline %}
public function onPost($title, $body, $created = null, $modified = null)
{
    $this->code = 204;
    return $this;
}
{% endhighlight %}

リソースのステータスコードを変更するには `code` プロパティを指定します。

ステータスコードはよりリソースの正確なステータスを報告してくれるようになりました。ユニットテストにも役立ちそうです。

```
204 No Content
...
[BODY]
*NULL
...
```

POSTインターフェイスを実装します。

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

{% highlight php startinline %}
public function onPost($title, $body, $created = null, $modified = null)
{
    $values = [
        'title' => $title,
        'body' => $body,
        'created' => $created,
        'modified' => $modified,
    ];
    $this->db->insert($this->table, $values);
    $this->code = 204;

    return $this;
}
{% endhighlight %}

リクエストを受け取ったPOSTインターフェイスメソッドがDBに記事をインサートします。これで記事の追加ができるようになりました。

このメソッドでもGETインターフェイスの時と同じく外部からインジェクトされたDBオブジェクトを用いています。GETインターフェイスと違うの _slave_ ではなく、 _master_ のDBオブジェクトがインジェクトされていることです。

このクラスの `on` で始まる全てのメソッドに束縛したDBオブジェクトをインジェクトするインターセプターをバインドした事を思い出してください。束縛されたDBインジェクターはメソッドがリクエストされる直前にリソースリクエストに応じたDBオブジェクトをインジェクトします。リソースリクエストは *必要とする依存の準備や取得に関心を払う事なく、そのオブジェクトを利用してる* 事に注目してください。これはBEAR.Sundayが一貫して指向している *関心の分離* の原則に従っています。

## 記事リソースのテスト

記事が追加され、その追加した内容を確認するテストを作成します。DBのテストを含んだリソースのユニットテストはこのようなコードになります。

{% highlight php startinline %}
class AppPostsTest extends \PHPUnit_Extensions_Database_TestCase
{
    public function getConnection()
    {
        // DB接続
    }

    public function getDataSet()
    {
        // 初期データセット
    }

    /**
     * @test
     */
    public function post()
    {
        // +1
        $before = $this->getConnection()->getRowCount('posts');
        $response = $this->resource
            ->post
            ->uri('app://self/blog/posts')
            ->withQuery(['title' => 'test_title', 'body' => 'test_body'])
            ->eager
            ->request();
        $this->assertEquals($before + 1, $this->getConnection()->getRowCount('posts'), "faild to add post");

        // new post
        $body = $this->resource
            ->get
            ->uri('app://self/blog/posts')
            ->withQuery(['id' => 4])
            ->eager
            ->request()->body;
        return $body;
    }

    /**
     * @test
     * @depends post
     */
    public function postData($body)
    {
        $this->assertEquals('test_title', $body['title']);
        $this->assertEquals('test_body', $body['body']);
    }
{% endhighlight %}

postメソッドで記事が追加されたかをテストし、postDataメソッドでその内容を確認しています。 

## 記事を追加するページを作成

記事を追加するappリソースが出来たので、次はWebからの入力を受け取ってそのappリソースをリクエストするページリソースを作成します。

テンプレートを追加します。

*Demo.Sandbox/src/Resource/Page/Blog/Posts/Newpost.tpl*

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="container">
        <h1>New Post</h1>
        <form action="/blog/posts/newpost" method="POST">
            <input name="X-HTTP-Method-Override" type="hidden" value="POST" />
            <div class="control-group {if $errors.title}error{/if}">
                <label class="control-label" for="title">Title</label>
                <div class="controls">
                    <input type="text" id="title" name="title" value="{$submit.title}">
                    <p class="help-inline">{$errors.title}</p>
                </div>
            </div>
            <div class="control-group {if $errors.body}error{/if}">
                <label>Body</label>
                <textarea name="body" rows="10" cols="40">{$submit.body}</textarea>
                <p class="help-inline">{$errors.body}</p>
            </div>
            <input type="submit" value="Send">
        </form>
    </div>
</body>
</html>
```

Note: `X-HTTP-Method-Override` というhideen項目に注目してください。これはページリソースへのリクエストメソッドを指定しています。ブラウザやWebサーバーがGET/POSTしかサポートしていなくても、その外部プロトコルとは別にソフトウエアの内部プロトコルとして機能します。

Note: `$_GET` クエリーで指定するときは `$_GET['_method']` で指定します。

Newpostページリソースを作成しGETインターフェイスとPOSTインターフェイスを実装します。

*Demo.Sandbox/src/Resource/Page/Blog/Posts/Newpost.php*

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\Resource\Page\Blog\Posts;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Newpost extends ResourceObject
{
    use ResourceInject;

    public function onGet()
    {
        return $this;
    }

    /**
     * Post
     *
     * @param string $title
     * @param string $body
     */
    public function onPost($title, $body)
    {
        // create post
        $this->resource
            ->post
            ->uri('app://self/blog/posts')
            ->withQuery(['title' => $title, 'body' => $body])
            ->eager->request();

        // redirect
        $this->code = 303;
        $this->headers = ['Location' => '/blog/posts'];
        return $this;
    }
}
{% endhighlight %}

GETインターフェイスの時と違って `withQuery()` メソッドでリソースリクエストに引数を指定しています。通常のPHPのメソッド引数と違って順番でなく、名前で引数を指定しているのに注目してください。Webのリクエストと同じように `key=value` と並べたものクエリーとしてメソッドリクエストに用いてます（keyが変数名です）。

**eager->request()** は *すぐに* リソースリクエストを行う事を表しています。

コンソールから記事をページリソースリクエスト経由で `POST` してみます。

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php post 'page://self/blog/posts/newpost?title=hello%20again&body=how%20have%20you%20been%20?'
```

NewpostページにPOSTリクエストをすると記事リソースが追加されます。
