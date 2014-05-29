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

## リソース・スタブ

* **このセクションをスキップして「リクエストインターフェイス実装」へ進むことができます。** *

`onGet` メソッドを呼び出すまえに、ダミーのデータでリソースを利用してみましょう。Stubデータ（ダミーデータ）を使ったリソースはアプリケーションのプロトタイピングやテスト等に便利です。Sandboxアプリケーションはアプリケーションオブジェクトを作成するときにセットされるとカスタムの `STUBモード` で実行されます。

`apps/Demo.Sandbox/bootstrap/context/api.php`(API access) と `apps/Demo.Sandbox/bootstrap/context/dev.php`(web access)

```
$context = 'stub';
```

Lets prepare some stub data.

*apps/Demo.Sandbox/var/lib/stub/resource.php*

```php
<?php
return [
    'Demo\Sandbox\Resource\App\Blog\Posts' =>
        [
            [
                'id' => 0,
                'title' => 'Alan Kay 1',
                'body' => 'People who are really serious about software should make their own hardware.',
                'created' => '2011-05-07 16:13:11'
            ],
            [
                'id' => 1,
                'title' => 'Alan Kay 2',
                'body' => 'Perspective is worth 80 IQ points.',
                'created' => '2011-05-07 16:13:22'
            ],
            [
                'id' => 2,
                'title' => 'Alan Kay 3',
                'body' => 'The best way to predict the future is to invent it.',
                'created' => '2011-05-07 16:13:33'
            ]
         ]
];
```

Lets check the app we resource we made through via the console.

```php
<?php
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get app://self/blog/posts
200 OK
[BODY]
0:array (namespace Sandbox\Resource\App\Blog;

  'id' => 0,
  'title' => 'Alan Kay 1',
  'body' => 'People who are really serious about software should make their own hardware.',
  'created' => '2011-05-07 16:13:11',
)
1:array (
  'id' => 1,
  'title' => 'Alan Kay 2',
  'body' => 'Perspective is worth 80 IQ points.',
  'created' => '2011-05-07 16:13:22',
)
2:array (
  'id' => 2,
  'title' => 'Alan Kay 3',
  'body' => 'The best way to predict the future is to invent it.',
  'created' => '2011-05-07 16:13:33',
)
```

It has become clear what kind of request result you are looking for using dummy data.


## スタブ・モジュール

The resource returns stub data by the resource method name being bound to the stub interceptor 
and implemented through aspect orientated programming.

*src/Module/Stub/StubModule.php*

```php
<?php
class StubModule extends AbstractModule
{
    /**
     * @var array
     */
    private $stub;

    /**
     * @param array $stub
     */
    public function __construct(array $stub)
    {
        parent::__construct();
        $this->stub = $stub;
    }

    protected function configure()
    {
        foreach ($this->stub as $class => $value) {
            $this->bindInterceptor(
                $this->matcher->subclassesOf($class),
                $this->matcher->any(),
                [new Stub($value)]
            );
        }
    }
}
```
　
 Note: Even though the client intends to request a resource, in reality the stub data interceptor that is wedged (intercepted) between the client and the resource returns the dummy data.


You can install StubModule from other module like belows..

```php
<?php
$this->install(new StubModule($stubData));
```

## リクエストインターフェイス実装

Next we will actually access a db and extract data to be used in an `onGet` method to respond to a GET request.

BEAR.Sunday doesn't have its own database usage library or database abstraction library. Inside the application resource by using other libraries you can directly use SQL or using an ORM. Inside the sandbox application [http://www.doctrine-project.org/projects/dbal.html Docrine DBAL] is used.

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

```php
<?php

namespace Demo\Sandbox\Resource\App\Blog;

use BEAR\Package\Module\Database\Dbal\Setter\DbSetterTrait;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Link;
use BEAR\Resource\Code;
use PDO;

use BEAR\Sunday\Annotation\Db;
use BEAR\Sunday\Annotation\Time;
use BEAR\Sunday\Annotation\Transactional;
use BEAR\Sunday\Annotation\Cache;
use BEAR\Sunday\Annotation\CacheUpdate;

/**
 * @Db
 */
class Posts extends ResourceObject
{
    use DbSetter;

    /**
     * @var string
     */
    public $time;

    /**
     * @var string
     */
    protected $table = 'posts';

    /**
     * @var array
     */
    public $links = [
        'page_post' # > [Link::HREF > 'page://self/blog/posts/post'],
        'page_item' # > [Link::HREF => 'page://self/blog/posts/post{?id}', Link::TEMPLATED > true],
        'page_edit' # > [Link::HREF => 'page://self/blog/posts/edit{?id}', Link::TEMPLATED > true],
        'page_delete' # > [Link::HREF => 'page://self/blog/posts?_method=delete{&id}', Link::TEMPLATED > true]
    ];

    /**
     * @param int $id
     *
     * @return Posts
     * @Cache(100)
     */
    public function onGet($id = null)
    {
        $sql = "SELECT id, title, body, created, modified FROM {$this->table}";
        if (is_null($id)) {
            $stmt = $this->db->query($sql);
            $this->body = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sql .# " WHERE id  :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('id', $id);
            $stmt->execute();
            $this->body = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $this;
    }

        /**
         * @param string $title
         * @param string $body
         *
         * @return Posts
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
            $this->links['new_post'] # [Link::HREF => "app://self/blog/posts/post?id{$lastId}"];
            $this->links['page_new_post'] # [Link::HREF => "page://self/blog/posts/post?id{$lastId}"];
            return $this;
        }

    /**
     * @param int    $id
     * @param string $title
     * @param string $body
     *
     * @return Posts
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
     * @return Posts
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
In the resource class a method that responds to therequest interface is provided. In this resource if an $id is specified 1 post and if not set all posts are returned;

## コマンドラインからリソースの利用

_Demo.Sandbox/src/Resource/App/Blog/Posts.php_
The URI `app://self/blog/posts` is given to the app resource specified in the `Demo\Sandbox\Resource\App\Blog\Posts` class.

Let's take a look at the resource we made from the command line. Lets first go back to the application mode.

public/api.php
```
$mode = 'Stub";
$app = require '/path/to/script/instance.php';
```

Let's make the call from the console.

```
$ php api.php get app://self/blog/posts

200 OK
[BODY]
array (
  0 => 
  array (
    'id' => '1',
    'title' => 'Title',
    'body' => 'This is the article text',
    'created' => '2011-07-01 22:30:25',
    'modified' => NULL,
  ),
  1 => 
  array (
    'id' => '2',
    'title' => 'This is a title',
    'body' => 'here the text continues',
    'created' => '2011-07-01 22:30:25',
    'modified' => NULL,
  ),
  2 => 
  array (
    'id' => '3',
    'title' => 'Title counter attck',
    'body' => 'This is not really very interesting',
    'created' => '2011-07-01 22:30:27',
    'modified' => NULL,
  ),
)
```

We have got the same output as to when we called the stub mode.

By switching the mode the dummy data can be displayed anytime.

    Note: `self` means a resource of the current application. In BEAR.Sunday it is possible to request a resource from another application or to set up and use resources that cross applications.

A query specifying parameters.

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get 'app://self/blog/posts?id=1'
```


## aliasの設定

It is handy to create an alias to the full path in your shell. 

_~/.bash_profile_

```
alias api='php /path/to/apps/Demo.Sandbox/bootstrap/contexts/api.php'
alias web='php /path/to/apps/Demo.Sandbox/bootstrap/contexts/web.php'
```

Using the resource API above you can make the following web request. This is then a simple notation and you can use resource using the console from any directory. This is handy when using scripts from the OS for batch processing and the like.

```
// API access
$ api get app://self/blog/posts

// web access
$ web get /blog/posts
```

## API駆動開発

In this way in BEAR.Sunday internal API development is used as a base to create web applications. A resource functions as a service layer, a name(uri) is provided to access data resource or business logic which is bundled through a RESTful universal interface.

We _do not_ create/provide a web application based external API interface, we build an application as an API collection on an internal resource API base. 

## ランタイムインジェクション

Each time this app resource is accessed by a get request, the setDb() get previously called and the DB object is injected from outside. It is not configured for this class to use any DB object, please focus on the injected object that is being relied on. In a *GET* request a slave DB object can be injected and for the other *PUT*,*POST*,*DELETE* requests a master DB object.

This is referred to as runtime injection. Binding between the particular method (in this case onGet) and the intercepter that is called before that method is executed (in this case DB object injector) are then acheived.

This architecture of the DB object being injected at runtime is not a BEAR.Sunday fixed structure is is the work of `DotrineDbalModule` which you install in `AppModule`. In  `DotrineDbalModule` class methods annotated with *@Db* binds the DB injector, that DB injector looks at the request method, decides whether master or slave should be used and sets the DB object.