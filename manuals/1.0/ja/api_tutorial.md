---
layout: docs-ja
title: APIチュートリアル
category: Manual
permalink: /manuals/1.0/ja/api_tutorial.html
---
# APIチュートリアル

BEAR.Sundayで[HAL](http://stateless.co/hal_specification.html)を使ったRESTfulなWeb APIを作成します。
最初にアプリケーションの雛形を`MyVendor.MyApi`という名前でインストールします。名前は[PSR](http://www.php-fig.org/psr/psr-0/)準拠です。

{% highlight bash %}
composer create-project bear/skeleton -n MyVendor.MyApi ~1.0@dev
cd MyVendor.MyApi
composer install
{% endhighlight %}


次にデータベースを準備します。

`Aura.Sql`モジュールを`composer`で取得します。

{% highlight bash %}
composer require ray/aura-sql-module ~1.0
{% endhighlight %}

DBデータを作成します。

{% highlight bash %}
mkdir var/db
sqlite3 var/db/post.sqlite3

sqlite> create table post(id integer primary key, title, body);
sqlite> create table comment(id integer primary key, post_id integer, body);
sqlite> .exit
{% endhighlight %}

` src/Module/AppModule.php`で`Aura.Sql`のモジュールをインストールします。

{% highlight php %}
<?php

namespace MyVendor\MyApi\Module;

use BEAR\Package\PackageModule;
use Ray\Di\AbstractModule;
use Ray\AuraSqlModule\AuraSqlModule; // この行を追加

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new PackageModule);

         // この2行を追加
        $dbConfig = 'sqlite:' . dirname(dirname(__DIR__)). '/var/db/post.sqlite3';
        $this->install(new AuraSqlModule($dbConfig));
    }
}
{% endhighlight %}

`Post(投稿)`と`Comment(コメント)`の2つリソースを作成します。

{% highlight php %}
<?php

namespace MyVendor\MyApi\Resource\App;

use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Exception\ResourceNotFoundException;
use BEAR\Resource\ResourceObject;
use Ray\AuraSqlModule\AuraSqlInject;

/**
 * @Cacheable
 */
class Post extends ResourceObject
{
    use AuraSqlInject;

   /**
     * @Embed(rel="comment", src="app://self/comment?post_id={id}")
     * @Link(rel="comment", href="app://self/comment?post_id={id}")
     */
    public function onGet($id)
    {
        $sql  = 'SELECT * FROM post WHERE id = :id';
        $bind = ['id' => $id];
        $post =  $this->pdo->fetchOne($sql, $bind);
        if (! $post) {
            throw new ResourceNotFoundException;
        }
        $this->body += $post;

        return $this;
    }

    public function onPost($title, $body)
    {
        $sql = 'INSERT INTO post (title, body) VALUES(:title, :body)';
        $statement = $this->pdo->prepare($sql);
        $bind = [
            'title' => $title,
            'body' => $body
        ];
        $statement->execute($bind);
        $id = $this->pdo->lastInsertId();

        $this->code = 201;
        $this->headers['Location'] = "/post?id={$id}";

        return $this;
    }
}
{% endhighlight %}

`src/Resource/App/Comment.php`

{% highlight php %}
<?php

namespace MyVendor\MyApi\Resource\App;

use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\RepositoryModule\Annotation\Refresh;
use BEAR\Resource\ResourceObject;
use Ray\AuraSqlModule\AuraSqlInject;

/**
 * @Cacheable
 */
class Comment extends ResourceObject
{
    use AuraSqlInject;

    public function onGet($post_id)
    {
        $sql  = 'SELECT * FROM comment WHERE post_id = :post_id';
        $bind = ['post_id' => $post_id];
        $this->body = $this->pdo->fetchAll($sql, $bind);

        return $this;
    }

    /**
     * @Refresh(uri="app://self/post?id={post_id}")
     */
    public function onPost($post_id, $body)
    {
        $sql = 'INSERT INTO comment (post_id, body) VALUES(:post_id, :body)';
        $statement = $this->pdo->prepare($sql);
        $bind = [
            'post_id' => $post_id,
            'body' => $body
        ];
        $statement->execute($bind);
        $id = $this->pdo->lastInsertId();

        $this->code = 201;
        $this->headers['Location'] = "/comment?id={$id}";

        return $this;
    }
}
{% endhighlight %}

これで実行準備は完了しました。
まずは記事リソースでどのメソッドが利用可能かコンソールで`OPTIONS`してみましょう。

{% highlight bash %}
php bootstrap/api.php options 'app://self/post'
{% endhighlight %}

{% highlight bash %}
200 OK
allow: get, post
{% endhighlight %}

次に新しい記事を`POST`します。

{% highlight bash %}
php bootstrap/api.php post 'app://self/post?title=greeting&body=hello'
{% endhighlight %}

{% highlight bash %}
201 Created
Location: /post/?id=1
content-type: application/hal+json

{
    "_links": {
        "self": {
            "href": "/post?title=greeting&body=hello"
        }
    }
}
{% endhighlight %}

レスポンスコード`201`でリソースが作成されたこと、そのURIは`Location`ヘッダーで`/post/?id=1`であることが確認できます。

もし以下のようなエラーが出たらエラーのログ`var/log/`にあるログを見てみましょう。

{% highlight bash %}
500 Internal Server Error
content-type: application/vnd.error+json

{"message":"500 Server Error"}
{% endhighlight %}

次に作成された投稿リソースを`GET`してみましょう。

{% highlight bash %}
php bootstrap/api.php get "app://self/post?id=1"
{% endhighlight %}

{% highlight bash %}
200 OK
content-type: application/hal+json
Etag: 3940718867
Last-Modified: Fri, 15 May 2015 03:07:42 GMT

{
    "id": "1",
    "title": "greeting",
    "body": "hello",
    "_embedded": {
        "comment": {
            "_links": {
                "self": {
                    "href": "/comment?post_id=1"
                }
            }
        }
    },
    "_links": {
        "self": {
            "href": "/post?id=1"
        },
        "comment": {
            "href": "app://self/comment?post_id=1"
        }
    }
}
{% endhighlight %}
投稿した`title`、`body`、インデックスの`id`の他に[HAL](http://stateless.co/hal_specification.html)の仕様に従って`_embedded`や`_links`といったリソースのメタ情報が埋め込まれています。

まだコメントが投稿されていないので、投稿リソースは空です。コメントを投稿してみましょう。コメントの`URI`は`_links`を利用します。1つの記事に複数のコメントを投稿してみましょう。

{% highlight bash %}
php bootstrap/api.php post 'app://self/comment?post_id=1&body=nice post !'
php bootstrap/api.php post 'app://self/comment?post_id=1&body=awesome post !'
{% endhighlight %}

{% highlight bash %}
201 Created
Location: /comment/?id=5
content-type: application/hal+json
...
{% endhighlight %}

投稿と同様にコードと`Location`ヘッダーに作成されたリソースURIが表されています。

投稿リソースを`GET`してコメントが埋め込まれたのを確認します。

{% highlight bash %}
php bootstrap/api.php get 'app://self/post?id=1'
{% endhighlight %}
{% highlight bash %}
200 OK
content-type: application/hal+json

{
    "id": "23",
    "title": "greeting",
    "body": "hello",
    "_embedded": {
        "comment": {
            "0": {
                "id": "1",
                "post_id": "1",
                "body": "nice post"
            },
            "1": {
                "id": "2",
                "post_id": "1",
                "body": "awesome post"
            },
            "_links": {
                "self": {
                    "href": "/comment?post_id=1"
                }
            }
        }
    },
    "_links": {
        "self": {
            "href": "/post?id=1"
        },
        "comment": {
            "href": "app://self/comment?post_id=1"
        }
    }
}
{% endhighlight %}

`HAL`の埋め込みリソースを利用すると複数のリソースを効率よく扱えます。

## APIサーバー

次にコンソールアプリケーションではなく、`HTTP`アプリケーションで動作を確認します。

### コンテキスト
Webサービスを`prod`の`hal`でサービスするためにbootファイル `var/www/index.php `のコンテキストを変更します。

{% highlight php %}
<?php

$context = 'prod-hal-api-app'; // プロダクション用のHAL APIアプリケーション
require dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';
{% endhighlight %}

### 304 (Not Modified)
`HttpCache`をスクリプトで使うために`App`クラスで`HttpCacheInject`のtraitを使って`HttpCache`をインジェクトします。

`src/Module/App.php`
{% highlight php %}
<?php

namespace MyVendor\MyApi\Module;

use BEAR\QueryRepository\HttpCacheInject; // この行を追加
use BEAR\Sunday\Extension\Application\AbstractApp;
use Ray\Di\Di\Inject;

class App extends AbstractApp
{
    use HttpCacheInject; // この行を追加
}
{% endhighlight %}

`bootstrap/bootstrap.php`の`route:`のセクションを変更して`if`文を追加して、コンテンツに変更がないときは`304`を返すようにします。

`bootstrap/bootstrap.php`
{% highlight bash %}
...
route: {
    /* @var $app App */
    $app = (new Bootstrap)->getApp(__NAMESPACE__, $context);
    if ($app->httpCache->isNotModified($_SERVER)) { //このif文を追加
        http_response_code(304);
        exit(0);
    }
    $request = $app->router->match($GLOBALS, $_SERVER);
}
{% endhighlight %}

スクリプトの準備は完了しました。
PHPサーバーを立ち上げます。

{% highlight bash %}
php -S 127.0.0.1:8080 var/www/index.php 
{% endhighlight %}

`curl`でコンソールと同じように操作してみましょう。まずは同じように`OPTIONS`から始めます。

{% highlight bash %}
curl -i 'http://127.0.0.1:8080/post' -X OPTIONS
{% endhighlight %}

{% highlight bash %}
HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Connection: close
X-Powered-By: PHP/5.6.8
allow: get, post
Content-type: text/html; charset=UTF-8
{% endhighlight %}

同じように`allow: get, post`が確認できます。

次は`GET`です。

{% highlight bash %}
curl -i 'http://127.0.0.1:8080/post?id=1'
{% endhighlight %}

{% highlight bash %}
HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Connection: close
X-Powered-By: PHP/5.6.8
content-type: application/hal+json
Etag: 2793553754
Last-Modified: Fri, 15 May 2015 01:25:12 GMT
{% endhighlight %}

このリソースは`@Cacheable`なので`GET`を繰り返しても`Last-Modified`に変更がありません。確かめてみましょう。`Post`の`onGet`で`error_log`などを記述しても実行されないことを確認しましょう。キャッシュされています。

次に`GET`リクエストで`Etag`ヘッダーに与えられた`ETag`を使ってリクエストを行います。（`1234567890`の部分は表示された`Etag`の値に変更します）

{% highlight bash %}
curl -i 'http://127.0.0.1:8080/post?id=1' --header 'If-None-Match: 1234567890'
{% endhighlight %}

{% highlight bash %}
HTTP/1.1 304 Not Modified
Host: 127.0.0.1:8080
Connection: close
X-Powered-By: PHP/5.6.8
Content-type: text/html; charset=UTF-8
{% endhighlight %}


コンテンツに変更がないので`304`のレスポンスが返っています。記事リソースに変更がない限りこのレスポンスは変わりません。


次に新しいコメントを`POST`して同じ記事にコメントを追加します。

{% highlight bash %}
curl -i http://127.0.0.1:8080/comment -X POST -d'post_id=1&body=marvelous post !'
{% endhighlight %}

{% highlight bash %}
HTTP/1.1 201 Created
Host: 127.0.0.1:8080
Connection: close
X-Powered-By: PHP/5.6.8
Location: /comment/?id=51
Content-type: text/html; charset=UTF-8
...
{% endhighlight %}

元の記事リソースを`GET`しますがコメントリソースは`@Refresh`で記事のリフレッシュを要求したので、有効だったETagが無効になってるはずです。試してみましょう。

{% highlight bash %}
curl -i 'http://127.0.0.1:8080/post?id=1' --header 'If-None-Match: 1234567890'
{% endhighlight %}

{% highlight bash %}
HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Connection: close
X-Powered-By: PHP/5.6.8
content-type: application/hal+json
ETag: 3895878753
Last-Modified: Sun, 17 May 2015 02:30:36 GMT
{% endhighlight %}

`304`ではなく、レスポンスコード`200`で新しい`ETag`がレスポンスされました。コンテンツが更新されたので古い`ETag`が無効になっています。

クライントは新しい`ETag`を使ってリクエストすることができます。

{% highlight bash %}
curl -i 'http://127.0.0.1:8080/post?id=1' --header 'If-None-Match: {new etag}'
{% endhighlight %}

{% highlight bash %}
HTTP/1.1 304 Not Modified
....
{% endhighlight %}

BEAR.Sundayの`Etag`は単にコンテンツのハッシュを返してネットワークの転送量を減らしているだけではありません。ハイパーリンクされたコンテンツはキャッシュ管理され次回更新までメソッドが実行されることがありません。

# RESTful Web API

いかがだったでしょうか？

このようにBEAR.SundayはAPIサイトの高速開発が可能です。
しかしこのAPIはRESTを単なる[HTTPのCRUD](http://www.infoq.com/jp/news/2009/08/CRUDREST)システムとしてとらえいない、ハイパーメディア制約を使った**RESTful Web API**です。
長期的運用に優れスケールも可能です。

 * 適切なレスポンスコードを返します。（`200`,`201`,`304`,`403`,`404`,`500`） 
 * HTTPに従ったリソースの適切なキャッシュにフレームワークが対応しています。（`ETag`レスポンスと`If-None-Match:`リクエストに対応）
 * 最終更新日付が`Last-Modified`で表現されます。
>  **RFC2616:** HTTP/1.1 servers **SHOULD** send Last-Modified whenever feasible.

 * ハイパーメディア `application/hal+json`を使用しています。（`application/json`はハイパーメディアではありません）
 * 作成されたリソースURIを`Location`ヘッダーで伝えます。
 * リソースの関係性を`rel`属性で持ち`ハイパーリンク`でリンクしています。
 * `HAL`の`__embed`を使って他のリソースを自身のリソースに埋め込んでいます。
 * エラーは`HAL`と互換性のある`application/vnd.error+json`メディアタイプでレスポンスを返します。
 * キャッシュコントロールをサーバーサイドで行っています（リソースの自己記述性）
 * 起点となるAPIの以外の`URI`はサーバーから受け取り、クライアントでURIを組み立てません。
 * リソースの階層がレイヤーになっています。
 * スケールに優れています。

BEAR.SundayはRESTシステムの**フレームワーク**を提供し、これらの機能を標準で利用することができます。

 * *TBD:バリデーション*
 
