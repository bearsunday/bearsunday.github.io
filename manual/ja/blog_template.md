---
layout: default_ja
title: BEAR.Sunday | ブログチュートリアル テンプレートの作成
category: Blog Tutorial
---

# テンプレートの作成

## リソースレンダリング

### リソース状態を表現するリソースレンダラー

前回までのステップで記事リソースには記事情報が、ページリソースには記事リソースへのリクエストがセットされました。これらの *リソース状態* を *表現* にするためにHTMLレンダリングします。

リソースはそれぞれが内部にレンダラーを持っています。Sandboxアプリケーションでは、HTML出力するための Smarty 3 テンプレートエンジンが全てのリソースにインジェクトされています。

Note: コントローラがモデルから取得したデータをテンプレートエンジンに渡して出力用文字列を得るのではなく、BEAR.Sundayでは全てのリソースがビュー用レンダラーを内包しています。モデルの出力の責任はそれぞれのリソースが持ちます。

## 記事リソーステンプレート

*Demo.Sandbox/src/Resource/App/Blog/Posts.tpl*

```html
<script src="/assets/js/delete_post.js"></script>

<table class="table table-bordered table-striped">
    <tr>
        <th class="span1">Id</th>
        <th>Title</th>
        <th>Body</th>
        <th>CreatedAt</th>
    </tr>
{foreach from=$resource->body item=post}
    <tr>
        <td>{$post.id|escape}</td>
        <td><a href="posts/post?id={$post.id|escape:'url'}">{$post.title|escape}</a></td>
        <td>{$post.body|truncate:60|escape}</td>
        <td>{$post.created|escape}</td>
        <td>
            <a title="Edit post" class="btn" href="/blog/posts/edit?id={$post.id}"><span class="glyphicon glyphicon-edit"></span></a>
            <a title="Delete post" class="btn remove confirm" href="#"><span class="glyphicon glyphicon-trash" data-post-id="{$post.id}"></span></a>
        </td>
    </tr>
{/foreach}
</table>
```

記事リソース（$posts）のコンテンツ（bodyプロパティ）を展開しています。

## 記事表示ページテンプレート

*Demo.Sandbox/src/Resource/Page/Blog/Posts.tpl*

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
            <li class="active">Blog</li>
        </ul>
        
        <h1>Posts</h1>
        {$posts}
        <a href="posts/newpost" class="btn btn-primary btn-large">New Post</a>
    </div>
</body>
</html>
```

postsリソースが持つ情報の詳細はページテンプレートでは表されてないことに注目してください。postsリソースのプレースホルダだけがあり、記事が "title" や "body" 等どういうプロパティを持つかにページはページリソースもページテンプレートも関心を持っていません。記事リソースの構成（要素）が変わっても、それを利用するページリソースには変更がありません。

postsリソースをどう表現するかは、postsリソース自身が持つテンプレートで構成されます。リソースの表現はリソースの責任です。

Note: オブジェクトへの責任割り当てにおける一般原則で責任の遂行に必要な情報を持っているクラス、「情報エキスパート」に責任を割り当てる [情報エキスパート（Information Expert）パターン](http://ja.wikipedia.org/wiki/GRASP#.E6.83.85.E5.A0.B1.E3.82.A8.E3.82.AD.E3.82.B9.E3.83.91.E3.83.BC.E3.83.88) の原則に従っています。この場合、記事リソースだけが記事テンプレートに関わりがあり、記事表示ページは記事リソーステンプレートや記事リソースの構成に対して無関心です。

## リソース表現＝リソース状態＋リソーステンプレート

これまで見てきたようにリソース状態はリソーステンプレートと合成されレンダリングされた結果がリソース表現としてクライアントに伝えられます。 

コマンドラインで確認してみましょう。今度はapi.phpではなく、dev.phpを使ってWeb表現（HTML）をリクエストしてみます。

```
$ php apps/Demo.Sandbox/bootstrap/contexts/dev.php get page://self/blog/posts

200 OK
tag: [1850711642]
x-cache: ["{\"mode\":\"W\",\"date\":\"Mon, 23 Jun 2014 13:44:09 +0200\",\"life\":0}"]
cache-control: ["no-cache"]
date: ["Mon, 23 Jun 2014 11:44:09 GMT"]
[BODY]
posts app://self/blog/posts,

[VIEW]
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
            <li class="active">Blog</li>
        </ul>
        
        <h1>Posts</h1>
        <script src="/assets/js/delete_post.js"></script>

<table class="table table-bordered table-striped">
    <tr>
        <th class="span1">Id</th>
        <th>Title</th>
        <th>Body</th>
        <th>CreatedAt</th>
    </tr>
...
```

ヘッダーには開発に役立つめた情報が格納されていて、 `[VIEW]` では最終的に出力されるHTMLが確認できます。

記事リソースへのリクエストが行われリソースリクエスト結果が `posts` スロットに格納されています。

## レイジーリクエスト

ページリソースではpostsリソースへのリクエストが `{$posts}` にセットされていました。このリクエストはテンプレート内に `{$posts}` プレースホルダが出現した時点で実行されます。

つまり出現しなければこのリクエストは実行されず、リクエストを行うかどうかがテンプレートで決定されます。

## リソースオブジェクト

セットされたリソースは表現として扱うだけでなくその要素を直接とりだすこともできます。

```
{$posts.0.title}
```

もし必要ならメソッドやプロパティを指定してオブジェクトしても扱えます。

```
{$posts->owner}
{$posts->isPublic()}
```

Note: 独自のメソッドを持つというのはリソース指向設計ではありません。

テンプレートにアサインされたリソースはその扱い方で振る舞いが変わります。コントローラーであるページがアクセスが必要かどうか、どのようにアサインするかを決めるのではなく、ビュー側がコンテキストに応じて後から決定します。

これはBEAR.Sundayの特徴の一つです。
