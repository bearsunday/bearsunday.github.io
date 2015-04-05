---
layout: docs-ja
title: リソース
category: Manual
permalink: /manuals/1.0/ja/resource.html
---

BEAR.Sundayアプリケーションはリソースの集合です。RESTfulアプリケーションを[REST](http://ja.wikipedia.org/wiki/REST)のスタイルで作成します。

# サービスとしてのオブジェクト

リソースクラスはHTTPのメソッドをPHPのメソッドにマップしてPHPのクラスをサービスとして扱います。

{% highlight php %}
<?php
class Index extends ResourceObject
{
    public function onGet($a, $b)
    {
        $this->code = 200; // 省略可
        $this['result'] = $a + $b; // $a = $_GET['a']; $b = $_GET['b'];

        return $this;
    }
}
{% endhighlight %}

{% highlight php %}
<?php
class Todo extends ResourceObject
{
    public function onPost($id, $todo)
    {
        $this->code = 201; // ステータスコード
        $this->headers['Location'] = '/todo/new_id'; // ヘッダー
        
        return $this;
    }
}
{% endhighlight %}

PHPのリソースクラスはWebのURIと同じような`app://self/blog/posts/?id=3`, `page://self/index`などのURIを持ち、HTTPのメソッドに準じた`onGet`, `onPost`, `onPut`, `onPatch`, `onDelete`インターフェイスを持ちます。

メソッドの引数には`onGet`には$_GET、`onPost`には$_POSTが変数名に応じて渡されます、それ以外の`onPut`,`onPatch`, `onDelete`のメソッドには`content-type`に応じて対応可能な値が引数になります。

メソッドでは引数に応じて自身のリソース状態`code`,`headers`,`body`を変更し`$this`を返します。

`body`のアクセスは`$this->body['price'] = 10;`を`$this['price'] = 10;`と短く記述することができます。

## リソースの種類

| URI | Class |
|-----+-------|
| page://self/index | Koriym\Todo\Resource\Page\Index |
| app://self/blog/posts | Koriym\Todo\Resource\App\Blog\Posts |

アプリケーション名が`koriym\todo`というアプリケーションの場合、URIとクラスはこのように対応します。
アプリケーションではクラス名の代わりにURIを使ってリソースにアクセスします。

標準ではリソースは二種類用意されています。１つは`App`リソースでアプリケーションのプログラミングインタフェース(**API**)です。
もう１つは`Page`リソースでHTTPに近いレイヤーのリソースです。`Page`リソースは`App`リソースを利用してWebページを作成します。

# クライント

リソースのリクエストにはリソースクライアントを使用します。

{% highlight php %}
<?php

use BEAR\Sunday\Inject\ResourceInject;

class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet($a, $b)
    {
        $this['post'] = $this
            ->resource
            ->get
            ->uri('app://self/blog/posts')
            ->withQuery(['id' => 1])
            ->eager
            ->request();
    }
}
{% endhighlight %}

このリクエストは`app://self/blog/posts`リソースに`?id=1`というクエリーでリクエストをすぐ`eager`に行います。

リソースのリクエストはlazyとeagerがあります。リクエストにlazyがついてないものがeagerリクエストです。

{% highlight php %}
<?php
$posts = $this->resource->get->uri('app://self/posts')->request(); //lazy
$posts = $this->resource->get->uri('app://self/posts')->eager->request(); // eager
{% endhighlight %}

lazy `request()`で帰って来るオブジェクトは実行可能なリクエストオブジェクトです。`$posts()`で実行することができます。
このリクエストをテンプレートやリソースに埋め込むと、その要素が使用されるときに評価されます。

## リンクリクエスト

クラインアントはハイパーリンクで接続されているリソースをリンクすることができます。

{% highlight php %}
<?php
$blog = $this
    ->resource
    ->get
    ->uri('app://self/User')
    ->withQuery(['id' => 1])
    ->linkSelf("blog")
    ->eager
    ->request()->body;
{% endhighlight %}

リンクは３種類あります。`$rel`をキーにして元のリソースの`body`リンク先のリソースが埋め込まれます。

 * `linkSelf($rel)` リンク先と入れ替わります。
 * `linkNew($rel)` リンク先のリソースがリンク元のリソースに追加されます
 * `linkCrawl($rel)` リンクをクロールして"リソースツリー"を作成します。



## リンクアノテーション

### @Link
{% highlight php %}
<?php
    /**
     * @Link(rel="profile", href="/profile{?id}")
     */
    public function onGet($id)
{% endhighlight %}

リンクを`rel`と`href`で指定します。`hal`コンテキストではHALのリンクフォーマットとして扱われます。BEARのリソースリクエストのときには`linkSelf()`, `linkNew`, `linkCrawl`の時にリソースリンクとして使われます。

{% highlight php %}
<?php
/**
 * @Link(crawl="post-tree", rel="post", href="app://self/post?author_id={id}")
 */
public function onGet($id = null)
{% endhighlight %}

`linkCrawl`は`crawl`の付いたリンクを[クロール](https://github.com/koriym/BEAR.Resource#crawl)してリソースを集めます。

### @Embed
{% highlight php %}
<?php
    /**
     * @Embed(rel="website", src="/website{?id}")
     */
    public function onGet($id)
{% endhighlight %}

リソースの中に`src`でリンクしたリソースを埋め込みます。HTMLページの中に別のURLの画像リソースを埋め込む`<img src="...">`タグをイメージしてみてください。HALレンダラーでは`__embed`として扱われます。

## バインドパラメーター

リソースクラスのメソッドの引数をWebコンテキストや他リソースの状態と束縛することができます。

### Webコンテキストパラメーター

`$_GET`や`$_COOKIE`などのPHPのスーパーグローバルの値をメソッド内で取得するのではなく、メソッドの引数に束縛することができます。

キーの名前と引数の名前が同じ場合
{% highlight php %}
<?php
use Ray\WebContextParam\Annotation\QueryParam;

    /**
     * @QueryParam("id")
     */
    public function foo($id = null)
    {
      // $id = $_GET['id'];
{% endhighlight %}


キーの名前と引数の名前が違う場合は`key`と`param`で指定
{% highlight php %}
<?php
use Ray\WebContextParam\Annotation\CookieParam;

    /**
     * @CookieParam(key="id", param="tokenId")
     */
    public function foo($tokenId = null)
    {
      // $tokenId = $_COOKIE['id'];
{% endhighlight %}

フルリスト
{% highlight php %}
<?php

use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

    /**
     * @QueryParam(key="id", param="userId")
     * @CookieParam(key="id", param="tokenId")
     * @EnvParam("app_mode")
     * @FormParam("token")
     * @ServerParam(key="SERVER_NAME", param="server")
     */
    public function foo($userId = null, $tokenId = "0000", $app_mode = null, $token = null, $server = null)
    {
       // $userId   = $_GET['id'];
       // $tokenId  = $_COOKIE['id'] or "0000" when unset;
       // $app_mode = $_ENV['app_mode'];
       // $token    = $_POST['token'];
       // $server   = $_SERVER['SERVER_NAME'];
{% endhighlight %}

### リソースパラメーター

`@ResourceParam`アノテーションを使えば他のリソースリクエストの結果をメソッドの引数に束縛できます。

{% highlight php %}
<?php
/**
 * @ResourceParam(param=“name”, uri="app://self//login#nickname")
 */
public function onGet($name)
{
{% endhighlight %}

この例ではメソッドが呼ばれると`login`リソースに`get`リクエストを行い`$body['nickname']`を`$name`で受け取ります。

## クエリーリポジトリー

### @ResourceRepository

{% highlight php %}
<?php
/**
 * @QueryRepository
 * @Etag
 */
class User extends ResourceObject
{% endhighlight %}

`@QueryRepository`とアノテートすると`get`リクエストは読み込み用のレポジトリ`QueryRepository`が使われ、時間無制限のキャッシュとして機能します。
`get`以外のリクエストがあると該当する`QueryRepository`のリソースが更新されます。

`@QueryRepository`から読まれるリソースオブジェクトはHTTPに準じた`Last-Modified`と`ETag`ヘッダーが付加されます。

同一クラスの`onGet`以外のリクエストメソッドがリクエストされ引数を見てリソースが変更されたと判断すると`QueryRepository`の内容も更新されます。

{% highlight php %}
<?php
class Todo
{
    public function onGet($id)
    {
        // read
    }

    public function onPost($id, $name)
    {
        // update
    }
}
{% endhighlight %}

例えばこのクラスでは`->post(10, 'shopping')`というリクエストがあると`id=10`の`QueryRepository`の内容が更新されます。

### @Purge @Refresh

もう１つの方法は`@Purge`アノテーションや、`@Refresh`アノテーションで更新対象のURIを指定することです。

 
{% highlight php %}
<?php
/**
 * @Purge(uri="app://self/user/friend?user_id={id}")
 * @Refresh(uri="app://self/user/profile?user_id={id}")
 */
public function onPut($id, $name, $age)
{% endhighlight %}

別のクラスのリソースや関連する複数のリソースの`QueryRepository`の内容を更新することができます。`@Purge`はリソースのキャッシュを消去し`@Refresh`はキャッシュの再生成を行います。

### @Etag

クラスにアノテートされていてHTTPリクエストに`Etag`が含まれていれば、コンテンツを照合し変更がなければ`304 Not Modified`を返します。

## BEAR.Resource

リソースクラスに関するより詳しい情報はBEAR.Resourceの[README](https://github.com/koriym/BEAR.Resource/blob/develop-2/README.ja.md)もご覧ください。
