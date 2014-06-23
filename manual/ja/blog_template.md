---
layout: default_ja
title: BEAR.Sunday | ブログチュートリアル(5) テンプレートの作成
category: Blog Tutorial
---

# リソースレンダリング

## リソース状態を表現するリソースレンダラー

前回までのステップで記事リソースには記事情報が、ページリソースには記事リソースへのリクエストがセットされました。これらの *リソース状態* を *表現* にするためにHTMLレンダリングします。

リソースはそれぞれが内部にレンダラーを持っています。Sandboxアプリケーションでは、HTML出力するための Smarty 3 テンプレートエンジンが全てのリソースにインジェクトされています。

Note: コントローラがモデルから取得したデータをテンプレートエンジンに渡して出力用文字列を得るのではなく、BEAR.Sundayでは全てのリソースがビュー用レンダラーを内包しています。モデルの出力の責任はそれぞれのリソースが持ちます。

## 記事リソーステンプレート

*Demo.Sandbox/src/Resource/App/Posts.tpl*
```
<table class="table table-bordered table-striped">
    <tr>
        <th class="span1">Id</th>
        <th>Title</th>
        <th>Body</th>
        <th>CreatedAt</th>
    </tr>
    {foreach from# $resource->body itempost}
    <tr>
        <td>{$post.id}</td>
        <td><a href="posts/post?id{$post.id}">{$post.title}</a></td>
        <td>{$post.body|truncate:60}</td>
        <td>{$post.created}</td>
    </tr>
    {/foreach}
</table>
```

記事リソース（$posts）のコンテンツ（bodyプロパティ）を展開しています。

## 記事表示ページテンプレート

*Demo.Sandbox/src/Resource/Page/Posts.tpl*
```
<html>
    <body>
    <h1>Posts</h1>
    {$posts}
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
...
x-interceptors: ["{\"onGet\":[\"BEAR\\\\Sunday\\\\Interceptor\\\\CacheLoader\"]}"]
x-query: ["[]"]
x-params: ["[]"]
x-cache: ["{\"mode\":\"W\",\"date\":\"Tue, 13 Nov 2012 10:49:19 +0100\",\"life\":false}"]
x-execution-time: [0.10759687423706]
x-memory-usage: [416528]
x-profile-id: ["50a2179f66680"]
cache-control: ["no-cache"]
date: ["Tue, 13 Nov 2012 09:49:19 GMT"]
content-type: ["text\/html; charset=UTF-8"]
[BODY]
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>Posts &laquo; BEAR.Sunday Blog</title>
```

ヘッダーには開発に役立つめた情報が格納されていて、[BODY] では最終的に出力されるHTMLが確認できます。

記事リソースへのリクエストが行われリソースリクエスト結果がpostsスロットに格納されています。

## レイジーリクエスト

ページリソースではpostsリソースへのリクエストが {$posts} にセットされていました。このリクエストはテンプレート内に {$posts} プレースホルダが出現した時点で実行されます。

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
