---
layout: default_ja
title: BEAR.Sunday | ブログチュートリアル(8) 記事の削除
category: Blog Tutorial
---
# DELETEメソッド

## 記事ページの削除

記事ページから `id` 指定した記事を削除できるように、記事ページリソースに `onDelete()` メソッドを作成しDELETEリクエストに対応します。

*src/Resource/Page/Blog/Posts/Post.php*

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\Resource\Page\Blog\Posts;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Post extends ResourceObject
{
    use ResourceInject;

    /**
     * @param int $id entry id
     */
    public function onDelete($id)
    {
        // delete
        $this->resource
            ->delete
            ->uri('app://self/blog/posts')
            ->withQuery(['id' => $id])
            ->eager
            ->request();

        $this->code = 303;
        $this->headers = ['Location' => '/blog/posts'];

        return $this;
    }
}
{% endhighlight %}

Webブラウザからの `DELETE` リクエストを受け取ったページリソースは、記事リソースを同じように `DELETE` リクエストしています。

この記事ページリソースへのリンクは記事リソースのテンプレートに記述します。JavaScriptを使って確認ダイアログを出し、ページリクエストを `DELETE` にするために `_method` クエリーを使っています。 

Note: POSTの時にフォームに `X-HTTP-Method-Override` hiddenエレメントを埋め込んだり、GETクエリーで `_method` を使ったりするのはHTTPメソッドオーバーライドという方法でPUT/DELETEのサポートがないブラウザやサーバー環境でHTTP動詞をフルに使う為の仕組みです。

## 記事リソースのDELETEインターフェイスの作成

記事ページからリクエストを受け取った記事リソースがDBアクセスで記事を削除します。

*src/Resource/App/Blog/Posts.php*

{% highlight php startinline %}
    public function onDelete($id)
    {
        $this->db->delete($this->table, ['id' => $id]);
        $this->code = 204;

        return $this;
    }
{% endhighlight %}

Note: GETリクエストインターフェイスと同じく `$this->db` プロパティはインジェクターによって自動でセットされます。GETの時と違うのはマスターDB用の接続が使われる事です。

## コマンドで確認

ではコンソールで試してみましょう。codeに204を指定したのでこのような表示になるはずです。

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php delete app://self/blog/posts?id=1

204 No Content
...
[BODY]
*NULL
...
```

## ユニットテスト

DELETEアクセスすると記事が１つ減っているはずです。テストはこのようなものになるでしょう。

{% highlight php startinline %}
    /**
     * @test
     */
    public function delete()
    {
        // dec 1
        $before = $this->getConnection()->getRowCount('posts');
        $response = $this->resource
            ->delete
            ->uri('app://self/blog/posts')
            ->withQuery(['id' => 1])
            ->eager
            ->request();
        $this->assertEquals($before - 1, $this->getConnection()->getRowCount('posts'), "faild to delete post");
    }
{% endhighlight %}

## Javascript確認ダイアログ

削除の確認をするためにSandboxアプリケーションが持つJavaScriptライブラリを利用しています。

```html
<script src="/assets/js/delete_post.js"></script>
```
