---
layout: default_ja
title: BEAR.Sunday | ブログチュートリアル(3) 記事リソースの作成
category: Blog Tutorial
---
# リソースオブジェクト

BEAR.Sundayはリソース指向のフレームワークです。意味のある情報のまとまりにリソースとしてURIが与えられ、GET/POSTリクエストに対応するリクエストインターフェイスを持ちます。

MVCでいうとコントローラーやモデルというコンポーネントの役割は、BEAR.Sundayではそれぞれページコントローラーとしてのページリソース、モデルはアプリケーション（app）リソースです。 これらのリソースは１リソース＝１クラスにマップされます。リソースのURIは名前空間を含んだクラス名が対応し、リクエストインターフェイスはメソッドに対応します。

例えば、記事を閲覧するページは記事表示リソース（page://self/blog/posts）、記事そのものを表すのはいわゆる状態アプリケーションリソース（app://self/blog/posts）です。

## 記事リソース

アプリケーションリソースは、いわばアプリケーションの *内部* APIです。MVCでいうとモデルに当たります。内部にデータベースアクセスやビジネスロジックを持ち、コントローラーとしての役割を持つページリソースにアプリケーションの内部APIを提供します。

記事リソースでは閲覧のためにGETリクエストに対応する `onGet` メソッドを実装します。

このようなリソースクラスのひな形から実装してみましょう。

```php
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
```

リクエストに応じたメソッド（リクエストインターフェイス）内ではデータを `body` プロパティにセットして `$this` を返します。

 Note: $bodyプロパティにセットする代わりにデータを直接返すこともできます。その場合、受け取った側は `return $this;` が返された場合と同様です。

## リクエストインターフェイス実装

次は実際にDBをアクセスしてデータを取り出すGETリクエストに対する `onGet` メソッドを実装します。

BEAR.Sundayは自身のデータベース利用ライブラリや抽象化ライブラリを持ちません。アプリケーションリソースクラス内で他のライブラリを使ってSQLを直接利用したり、ORMを使用したりします。Sandboxアプリケーションでは [Docrine DBAL](http://www.doctrine-project.org/projects/dbal.html) でSQLを記述します。

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

```php
<?php

namespace Demo\Sandbox\Resource\App\Blog;

use BEAR\Package\Module\Database\Dbal\Setter\DbSetterTrait;
use BEAR\Resource\Header;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Code;
use BEAR\Resource\Annotation\Link;
use PDO;
use BEAR\Sunday\Annotation\Cache;
use BEAR\Sunday\Annotation\CacheUpdate;
use BEAR\Sunday\Annotation\Db;
use BEAR\Sunday\Annotation\Time;
use BEAR\Sunday\Annotation\Transactional;

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

    public $links = [
        'page_post' => [Link::HREF => 'page://self/blog/posts/post'],
        'page_item' => [Link::HREF => 'page://self/blog/posts/post{?id}', Link::TEMPLATED => true],
        'page_edit' => [Link::HREF => 'page://self/blog/posts/edit{?id}', Link::TEMPLATED => true],
        'page_delete' => [Link::HREF => 'page://self/blog/posts/post']
    ];

    /**
     * @var string
     */
    protected $table = 'posts';

    /**
     * @param int $id
     *
     * @Cache(100)
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

    /**
     * @param string $title
     * @param string $body
     *
     * @Time
     * @Transactional
     * @CacheUpdate
     */
    public function onPost($title, $body)
    {
        $values = [
            'title' => $title,
            'body' => $body,
            'created' => $this->time
        ];
        $this->db->insert($this->table, $values);
        //
        $lastId = $this->db->lastInsertId('id');
        $this->code = Code::CREATED;
        $this->headers[Header::LOCATION] = "app://self/posts/post?id={$lastId}";
        $this->headers[Header::X_ID] = $lastId;

        return $this;
    }

    /**
     * @param int    $id
     * @param string $title
     * @param string $body
     *
     * @Time
     * @CacheUpdate
     */
    public function onPut($id, $title, $body)
    {
        $values = [
            'title' => $title,
            'body' => $body,
            'created' => $this->time
        ];
        $this->db->update($this->table, $values, ['id' => $id]);
        $this->code = Code::NO_CONTENT;

        return $this;
    }

    /**
     * @param int $id
     *
     * @CacheUpdate
     */
    public function onDelete($id)
    {
        $this->db->delete($this->table, ['id' => $id]);
        $this->code = Code::NO_CONTENT;

        return $this;
    }
}
```

リソースクラスではリソースのリクエストインターフェイスに対応するメソッドを記述します。この記事リソースでは$idが指定されると記事１つが、指定されないと記事全てを返しています。

## コマンドラインからリソースの利用

_Demo.Sandbox/src/Resource/App/Blog/Posts.php_
The URI `app://self/blog/posts` is given to the app resource specified in the `Demo\Sandbox\Resource\App\Blog\Posts` class.

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

リクエスト結果ではリソースがどのような値を持つか `[BODY]` 、それがどのように表現されるか `[VIEW]` が表されてます。

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

このようにBEAR.Sundayでは内部のAPI開発がWebアプリケーション開発のベースとして使われます。リソースはサービスレイヤーとして機能し、誰もが使っているRESTfulインターフェイスを通じてバンドルされるデータリソースやビジネスロジックにアクセスするために名前（uri）が提供されます。

われわれは外部APIインターフェイスをベースとしたWebアプリケーションを開発したり提供しません。内部リソースのAPIをベースとしたAPIの集合としてのアプリケーションを開発します。

## ランタイムインジェクション

GETリクエストによりこのアプリケーションリソースがアクセスされるたびに、DbSetterTraitのsetDb()は以前に呼ばれたDBオブジェクトを外部から注入されます。このクラスのためにどのDBオブジェクトが使われるかは構成されていませんが、注入されたオブジェクトを信頼することに注目してください。*GET* リクエストではスレーブDBオブジェクトが注入され、その他の *PUT*、*POST*、*DELETE* リクエストではマスターDBオブジェクトが注入されます。

これをランタイムインジェクションと呼びます。特定のメソッド（このケースではonGet）とそのメソッドが実行される前に呼び出されるインターセプター（このケースではDBオブジェクトインジェクター）のバインディングにより実現されています。

ランタイムにDBオブジェクトが注入されるこのアーキテクチャーはBEAR.Sundayの固定した仕組みではなく、`AppModule` の中でインストールした `DotrineDbalModule` の仕事です。`DotrineDbalModule` クラスが *@Db* でアノテートされたメソッドをBDインジェクターにバインドし、DBインジェクターがリクエストメソッドを見て、マスターまたはスレーブを使うか決定し、DBオブジェクトをセットします。
