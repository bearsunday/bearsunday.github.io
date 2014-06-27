---
layout: default_ja
title: BEAR.Sunday | ブログチュートリアル 記事リソースの作成
category: Blog Tutorial
---

# 記事リソースの作成

## リソースオブジェクト

BEAR.Sundayはリソース指向のフレームワークです。意味のある情報のまとまりにリソースとしてURIが与えられ、GET/POSTリクエストに対応するリクエストインターフェイスを持ちます。

MVCでいうとコントローラーやモデルというコンポーネントの役割は、BEAR.Sundayではそれぞれページコントローラーとしてのページリソース、モデルはアプリケーションリソース（appリソース）です。 これらのリソースは１リソース＝１クラスにマップされます。リソースのURIは名前空間を含んだクラス名が対応し、リクエストインターフェイスはメソッドに対応します。

例えば記事を閲覧するページは記事表示リソース（page://self/blog/posts）、記事そのものを表すのはいわゆる状態アプリケーションリソース（app://self/blog/posts）です。

## 記事リソース

アプリケーションリソースはアプリケーションAPIです。MVCでいうとモデルに当たります。内部にデータベースアクセスやビジネスロジックを持ち、コントローラーの役割をするページリソースからアクセスされます。
この記事リソースでは閲覧のためにGETリクエストに対応する `onGet` メソッドを実装します。

このようなリソースクラスのひな形から実装してみましょう。

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\Resource\App\Blog;

use BEAR\Resource\ResourceObject;

class Posts extends ResourceObject
{
    public function onGet($id = null)
    {
        $this->body = '_DBから読み出したデータ_';
        return $this;
    }
}
{% endhighlight %}

リクエストに応じたメソッド（リクエストインターフェイス）内ではデータを `body` プロパティにセットして `$this` を返します。

Note: データを直接返すとクライアントには値が `$body` プロパティにセットされた `$this` が返ります。

## リクエストインターフェイス実装

次は実際にDBをアクセスしてデータを取り出すGETリクエストに対する `onGet` メソッドを実装します。

BEAR.Sundayは自身のデータベース利用ライブラリや抽象化ライブラリを持ちません。アプリケーションリソースクラス内でSQLを直接利用したり、ORMを使用したりします。Sandboxアプリケーションでは [PDO](http://www.php.net/manual/ja/book.pdo.php) でSQLを記述します。

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\Resource\App\Blog;

use BEAR\Sunday\Annotation\Db;
use BEAR\Resource\ResourceObject;
use BEAR\Package\Module\Database\Dbal\Setter\DbSetterTrait;
use PDO;

/**
 * @Db
 */
class Posts extends ResourceObject
{
    use DbSetterTrait;

    /**
     * Current time
     *
     * @var string
     */
    public $time;

    /**
     * @var string
     */
    protected $table = 'posts';

    /**
     * @param int $id
     */
    public function onGet($id = null)
    {
        $sql = "SELECT id, title, body, created, modified FROM {$this->table}";
        if (is_null($id)) {
            $stmt = $this->db->query($sql);
            $this->body = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this;
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->execute();
        $this->body = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this;
    }
}
{% endhighlight %}

リソースクラスではリソースのリクエストインターフェイスに対応するメソッドを記述します。この記事リソースでは引き数として `$id` が指定されると記事１つ、指定されないと全ての記事を返します。

## コマンドラインからリソースの利用

_Demo.Sandbox/src/Resource/App/Blog/Posts.php_

URI `app://self/blog/posts` は `Demo\Sandbox\Resource\App\Blog\Posts` クラスで指定されているアプリケーションリソースに対して与えられます。

作成したリソースはをコマンドラインからみてみましょう。

コンソールで呼び出します。

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get app://self/blog/posts

200 OK
tag: [3959571851]
x-cache: ["{\"mode\":\"W\",\"date\":\"Thu, 29 May 2014 08:30:35 +0200\",\"life\":100}"]
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Thu, 29 May 2014 06:30:35 GMT"]
[BODY]
0 => array(
  id 1,
  title Perspective,
  body Perspective is worth 80 IQ points.

-- Alan Kay,
  created 2013-10-14 17:40:49,
  modified ,
),
1 => array(
  id 2,
  title Before it becomes normal,
  body Quite a few people have to believe something is normal before it becomes normal - a sort of 'voting' situation. But once the threshold is reached, then everyone demands to do whatever it is.

-- Alan Kay,
  created 2013-10-14 17:41:13,
  modified ,
),
2 => array(
  id 3,
  title Most software today,
  body Most software today is very much like an Egyptian pyramid with millions of bricks piled on top of each other, with no structural integrity, but just done by brute force and thousands of slaves.

-- Alan Kay,
  created 2013-10-14 17:41:37,
  modified ,
),
...
[VIEW]
{
    "0": {
        "id": "1",
        "title": "Perspective",
        "body": "Perspective is worth 80 IQ points.\r\n\r\n-- Alan Kay",
        "created": "2013-10-14 17:40:49",
        "modified": null
    },
    "1": {
        "id": "2",
        "title": "Before it becomes normal",
        "body": "Quite a few people have to believe something is normal before it becomes normal - a sort of 'voting' situation. But once the threshold is reached, then everyone demands to do whatever it is.\r\n\r\n-- Alan Kay",
        "created": "2013-10-14 17:41:13",
        "modified": null
    },
    "2": {
        "id": "3",
        "title": "Most software today",
        "body": "Most software today is very much like an Egyptian pyramid with millions of bricks piled on top of each other, with no structural integrity, but just done by brute force and thousands of slaves.\r\n\r\n-- Alan Kay",
        "created": "2013-10-14 17:41:37",
        "modified": null
    },
...
```

リソースがどのような値を持つか `[BODY]` 、それがどのように表現されるか `[VIEW]` が表されてます。

Note: `self` は現在のアプリケーションを意味します。BEAR.Sundayでは他のアプリケーションからリソースをリクエストしたり、アプリケーションを横断するリソースをセットアップしたり利用できます。

引き数はクエリーの形式で指定します。

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get 'app://self/blog/posts?id=1'
```

## aliasの設定

シェルスクリプトでaliasをフルパスで設定しておくと便利です。

_~/.bash_profile_

```
alias api='php /path/to/apps/Demo.Sandbox/bootstrap/contexts/api.php'
alias web='php /path/to/apps/Demo.Sandbox/bootstrap/contexts/dev.php'
```

上のリソースのAPIを使い、次のWebリクエストを発行できます。シンプルな表現でコンソールを使いどのディレクトリからでもリソースを利用できます。これは、バッチ処理などのためにOSからスクリプトを使う場合に便利です。

```
// APIアクセス
$ api get app://self/blog/posts

// Webアクセス
$ web get /blog/posts
```

## API駆動開発

このようにBEAR.Sundayではリソース作成（アプリケーションAPI開発）が開発のベースになります。
作成したリソースには名前（URI）が与えられアプリケーション内部、外部ともに同様のアクセスをすることができます。
APIの集合がBEAR.Sundayアプリケーションになります。

## ランタイムインジェクション

アプリケーションリソースがGETリクエストでアクセスされる度に `DbSetterTrait` の `setDb` セッターメソッドを通してDBオブジェクトが注入（外部から代入）されます。
`GET` リクエストではスレーブDBが注入され、その他の `PUT`、`POST`、`DELETE` リクエストではマスターDBオブジェクトが注入されます。
これをランタイムインジェクションと呼びます。指定した特定のメソッドがコールされる直前に `インターセプター` と呼ばれる割り込み処理が実行されメソッド名から（`GET` かそれ以外か）適切なDBオブジェクトを選択して注入します。
