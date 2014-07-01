---
layout: default_ja
title: BEAR.Sunday | ブログチュートリアル 記事の追加
category: Blog Tutorial
---

# 記事の追加

## POSTメソッド

これまでのステップでデータベースに登録されている記事を表示できるようになりました。次はいよいよフォームを作成しますが、まずはその前にコンソールのリソース操作で記事を追加できるようにしましょう。

### 記事リソースのPOSTインターフェイスを作成

GETインターフェイスメソッドしかない記事リソースに記事を追加することのできるPOSTインターフェイスを加えます。

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

{% highlight php startinline %}
public function onPost($title, $body)
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
param-post: ["title,body"]
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
問題はありませんが、 *もっと* 正確な204（No Content）のステータスコードに変更してみましょう。

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

{% highlight php startinline %}
public function onPost($title, $body)
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
public function onPost($title, $body)
{
    $values = [
        'title' => $title,
        'body' => $body,
        'created' => $this->time,
    ];
    $this->db->insert($this->table, $values);
    $this->code = 204;

    return $this;
}
{% endhighlight %}

リクエストを受け取ったPOSTインターフェイスメソッドがDBに記事をインサートします。これで記事の追加ができるようになりました。

このメソッドでもGETインターフェイスの時と同じく外部からインジェクトされたDBオブジェクトを用いています。GETインターフェイスと違うの _slave_ ではなく、 _master_ のDBオブジェクトがインジェクトされていることです。

このクラスの `on` で始まる全てのメソッドに束縛したDBオブジェクトをインジェクトするインターセプターをバインドした事を思い出してください。束縛されたDBインジェクターはメソッドがリクエストされる直前にリソースリクエストに応じたDBオブジェクトをインジェクトします。リソースリクエストは *必要とする依存の準備や取得に関心を払う事なく、そのオブジェクトを利用してる* 事に注目してください。これはBEAR.Sundayが一貫して指向している *関心の分離* の原則に従っています。

### 記事リソースのテスト

記事が追加され、その追加した内容を確認するテストを作成します。DBのテストを含んだリソースのユニットテストはこのようなコードになります。

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\tests\Resource\App\Blog;

use BEAR\Resource\Code;
use BEAR\Resource\Header;

class PostsTest extends \PHPUnit_Extensions_Database_TestCase
{
    private $resource;

    public function getConnection()
    {
        $pdo = require $_ENV['APP_DIR'] . '/tests/scripts/db.php';

        return $this->createDefaultDBConnection($pdo, 'sqlite');
    }

    public function getDataSet()
    {
        $seed = $this->createFlatXmlDataSet($_ENV['APP_DIR'] . '/tests/mock/seed.xml');
        return $seed;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->resource = $GLOBALS['RESOURCE'];
    }

    public function testOnPost()
    {
        // inc 1
        $before = $this->getConnection()->getRowCount('posts');
        $resourceObject = $this->resource
            ->post
            ->uri('app://self/blog/posts')
            ->withQuery(['title' => 'test_title', 'body' => 'test_body'])
            ->eager
            ->request();

        $this->assertEquals($before + 1, $this->getConnection()->getRowCount('posts'), "failed to add");
    }

    /**
     * @depends testOnPost
     */
    public function testOnPostNewRow()
    {
        $this->resource
            ->post
            ->uri('app://self/blog/posts')
            ->withQuery(['title' => 'test_title', 'body' => 'test_body'])
            ->eager
            ->request();

        // new post
        $entries = $this->resource->get->uri('app://self/blog/posts')->withQuery([])->eager->request()->body;
        $body = array_pop($entries);

        $this->assertEquals('test_title', $body['title']);
        $this->assertEquals('test_body', $body['body']);
    }
}
{% endhighlight %}

`testOnPost` メソッドで記事が追加されたかをテストし、`testOnPostNewRow` メソッドで内容を確認しています。

### 記事を追加するページを作成

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
        <ul class="breadcrumb">
            <li><a href="/">Home</a> <span class="divider">/</span></li>
            <li><a href="/blog/posts">Blog</a> <span class="divider">/</span></li>
            <li class="active">New Post</li>
        </ul>
        
        <h1>New Post</h1>
        <form action="/blog/posts/newpost" method="POST" role="form">
            <div class="form-group {if $errors.title}has-error{/if}">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="{$submit.title|escape}" class="form-control">
                <label class="control-label" for="title">{$errors.title|escape}</label>
            </div>
            <div class="form-group {if $errors.body}has-error{/if}">
                <label for="body">Body</label>
                <textarea name="body" rows="10" cols="40" class="form-control" id="body">{$submit.body|escape}</textarea>
                <label class="control-label" for="body">{$errors.body|escape}</label>
            </div>
            <button type="submit" class="btn btn-default">Submit</button>
        </form>
    </div>
</body>
</html>
```

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
