---
layout: default_ja
title: BEAR.Sunday | ブログチュートリアル 記事の編集
category: Blog Tutorial
---

# 記事の編集

## PUTメソッド

### 記事編集ページの作成

記事作成ページとほとんど同じです。違いは最初の表示（GETリクエスト）で指定された記事データを読み込みデフォルトをセットしてることだけです。

*Demo.Sandbox/src/Resource/Page/Blog/Posts/Edit.php*

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\Resource\Page\Blog\Posts;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;
use BEAR\Sunday\Annotation\Form;
use BEAR\Resource\Annotation\Embed;

class Edit extends ResourceObject
{
    use ResourceInject;

    /**
     * @Embed(rel="submit", src="app://self/blog/posts{?id}")
     */
    public function onGet($id)
    {
        $this['id'] = $id;

        return $this;
    }

    /**
     * @param int    $id
     * @param string $title
     * @param string $body
     *
     * @Form
     */
    public function onPut($id, $title, $body)
    {
        // update post
        $this->resource
            ->put
            ->uri('app://self/blog/posts')
            ->withQuery(['id' => $id, 'title' => $title, 'body' => $body])
            ->eager
            ->request();

        // redirect
        $this->code = 303;
        $this->headers = ['Location' => '/blog/posts'];

        return $this;
    }
}
{% endhighlight %}

@TODO `@Embed` の説明

### 記事リソースのPUTインターフェイスの作成

記事ページからリクエストを受け取った記事リソースがDBアクセスで記事を変更します。

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

{% highlight php startinline %}
    /**
     * @param int    $id
     * @param string $title
     * @param string $body
     */
    public function onPut($id, $title, $body)
    {
        $values = [
            'title' => $title,
            'body' => $body,
            'modified' => $this->time
        ];
        $this->db->update($this->table, $values, ['id' => $id]);
        $this->code = 204;

        return $this;
    }
{% endhighlight %}

### テンプレートの作成

*Demo.Sandbox/src/Resource/Page/Blog/Posts/Edit.tpl*

{% highlight php startinline %}
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
            <li class="active">Edit Post</li>
        </ul>
        
        <h1>Edit Post</h1>
        <form action="/blog/posts/edit" method="POST" role="form">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="id" value="{$id|escape}">

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
{% endhighlight %}

Note: `_method` というhidden項目に注目してください。これはページリソースへのリクエストメソッドを指定しています。ブラウザやWebサーバーがGET/POSTしかサポートしていなくても、その外部プロトコルとは別にソフトウエアの内部プロトコルとして機能します。

### PUTリクエスト

変更には `PUT` インターフェイスを使っています。

`PUT` リクエストにするためにフォームにHTTPメソッドオーバーライドのための項目を埋め込みます。

```html
<input type="hidden" name="_method" value="PUT">
```

Note: このチュートリアルでは　`POST` を記事の作成、`PUT` を記事の変更と扱ってきました。POST/PUTの区別は *[べき等性](http://ja.wikipedia.org/wiki/%E5%86%AA%E7%AD%89)* により行われます。記事リソースに同じ `POST` リクエストを複数回行うとどんどん記事が増えていきますが、`PUT` による変更では１回行っても複数回行っても同じです。一般にメソッドの選択はこのべき等性に基づいて行うのが適当でしょう。
