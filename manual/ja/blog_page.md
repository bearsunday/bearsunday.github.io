---
layout: default_ja
title: BEAR.Sunday | ブログチュートリアル(4) 記事表示ページの作成
category: Blog Tutorial
---
# ページリソース

## ページの作成

BEAR.Sundayで新しくページを作成する場合、通常次の２つを作成します。

 * ページリソースクラス
 * ページリソーステンプレート

ページリソースもアプリケーションリソースと同じ構成、インターフェイスを持ちます。アプリケーションリソースが標準の状態では何も持たず必要なDBオブジェクトをDIでインジェクトしたように、ページリソースも依存をインジェクトして使用します。

## ページコントローラー

MVCのコントローラにあたる部分はBEARではページリソースです。ページはWebのリクエストを受け取り、アプリケーションリソースをリクエストして、自らを構成します。ページはそのまま出力用のオブジェクトとしても扱われます。

Note: BEAR.Sundayではルーターも利用できますが、このブログアプリでは利用しません。

Note: BEAR.Sundayではサイトの１ページが１ページリソースクラスに相当し [Page Controller](http://www.martinfowler.com/eaaCatalog/pageController.html) の働きをします。

## ページクラス

この記事表示ページの役割は「アプリケーションAPIの記事リソースをGETリクエストで取得してページのpostsスロットにアサインする」ということです。

[アプリケーションリソース](blog_get.html) のセクションではコンソールからアプリケーションリソースを実行しましたが、PHPでリソースをリクエストするにはリソースクライアントを使います。リソースクライントはtraitでインジェクトすることが出来ます。

traitを使用する `use` 文で次のように記述するとリソースクライアントが `$resource` プロパティにインジェクトされます。

{% highlight php startinline %}
<?php
    use ResourceInject;
{% endhighlight %}

インジェクトされたリソースクライアントを使ってリソースリクエストを行うにはこのようにします。

{% highlight php startinline %}
<?php
$this->resource->get->uri('app://self/posts')->request()
{% endhighlight %}

まとめるとこうなります。

{% highlight php startinline %}
<?php
namespace Sandbox\Resource\Page\Blog;

use BEAR\Resource\AbstractObject as Page;
use BEAR\Sunday\Inject\ResourceInject;
use BEAR\Sunday\Annotation;

class Posts extends Page
{
    use ResourceInject;
	
    public $body = [
        'posts' => ''
    ];

    /**
     * Get
     *
     * @Cache
     */
    public function onGet()
    {
        $this['posts'] = $this->resource->get->uri('app://self/posts')->request();
        return $this;
    }
}
{% endhighlight %}

`app://self/posts` リソースへのリクエストを自らのpostsというスロットに格納しています。

Note: $this['posts'] は $this->body['body'] の省略した書き方のシンタックスシュガー（＝読み書きのしやすさのために導入される構文）です。
Note: MVCのコントローラーと違って、出力に関心が払われてないのに注目してみてください。テンプレートファイルの指定や、テンプレートに対しての変数のアサイン等がありません。

## リソースとしてのページ

それではページリソースをアプリケーションリソースと同じようにコンソールからアクセスしてみましょう。

```
$ php api.php get page://self/blog/posts

200 OK
...
\[BODY]
{
    "posts": {
...
```

postsというスロットに *get app://self/posts* というリクエスト結果が格納されてます。

ページリソースはページコントローラーの役割をするとともに出力用のオブジェクトの役割も果たしています。しかしどのように表現されるかにはまだ関心が払われていません。

## リソースキャッシュ

ページリソースには `@Cache` とアノテートされていて、sandboxアプリケーションではこのアノテーションを持つメソッドにはキャシュインターセプターがバインドされています。例えば30秒間リソースをキャッシュしたいならこのように表記します。

{% highlight php startinline %}
<php
use BEAR\Sunday\Annotation\Cache;

/**
 * @Cache(30)
 */
{% endhighlight %}

Note: CacheのFQNのために `use` 文が必要です。

## 無期限キャッシュ

このページリソースには時間が指定されていないので、リソースのGETリクエストは無期限にキャッシュされonGetメソッドが実行されるのは最初の一回のみです。それでは記事が追加されたり削除されてもこの記事表示ページは変更されないのでしょうか？

この記事表示ページリソースの役割は、記事リソースをリクエストしてpostsにセットすることです。その役割はリクエストによらず不変でこの役割がキャッシュされます。

ページリソースにセットしているのはリクエストの結果ではなくて、リクエストそのものです。@Cacheで無期限のキャッシュを指定してもキャッシュされた記事リソースリクエストは毎回実行され、記事リソースのリソース状態は反映されます（この場合、@CacheでセーブされるのはOnGet()メソッド内でリクエストを作るわずかなコストだけです）。

つまりこのキャッシュでカットしているのはリソースリクエストを組み立てるコストです。
