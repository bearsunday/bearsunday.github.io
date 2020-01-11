---
layout: docs-ja
title: リソース
category: Manual
permalink: /manuals/1.0/ja/resource.html
---

# リソース

BEAR.Sundayアプリケーションはリンクで接続されたリソースの集合です。

## サービスとしてのオブジェクト

`ResourceObject`はHTTPのメソッドがPHPのメソッドにマップされたサービスとしてのオブジェクト (Object as a servie)です。ステートレスなリクエストで自身のリソース状態を表現にして転送します。
([Representational State Transfer)](http://ja.wikipedia.org/wiki/REST)

```php?start_inline
class Index extends ResourceObject
{
    public $code = 200;
    public $headers = [];

    public function onGet(int $a, int $b) : ResourceObject
    {
        $this->body = [
            'sum' => $a + $b  // $_GET['a'] + $_GET['b']
        ];

        return $this;
    }
}
```

```php?start_inline
class Todo extends ResourceObject
{
    public function onPost(string $id, string $todo) : ResourceObject
    {
        $this->code = 201; // ステータスコード
        $this->headers = [ // ヘッダー
            'Location' => '/todo/new_id'
        ];

        return $this;
    }
}
```

PHPのリソースクラスはWebのURIと同じような`app://self/blog/posts/?id=3`, `page://self/index`などのURIを持ち、HTTPのメソッドに準じた`onGet`, `onPost`, `onPut`, `onPatch`, `onDelete`インターフェイスを持ちます。

メソッドでは自身のリソース状態`code`,`headers`,`body`を変更し`$this`を返します。

## URI

PHPのクラスはURIにマップされていて、アプリケーションではクラス名の代わりにURIを使ってリソースにアクセスします。
アプリケーション名が`koriym\todo`というアプリケーションの場合、URIとクラスはこのように対応します。

| URI | Class |
|-----+-------|
| `page://self/index` | `Koriym\Todo\Resource\Page\Index` |
| `app://self/blog/posts` | `Koriym\Todo\Resource\App\Blog\Posts` |


* page://self/indexのindexは省略する事ができます。page://self/indexとpage://self/は同じです。
* HTTPのURIと同じようにクエリを使う事もできます。 (app://self/blog/posts/?id=3)


標準ではリソースは二種類用意されています。１つは`App`アプリケーションリソースです。アプリケーションのAPIです。
もう１つは`Page`リソースです。`Page`リソースは`App`リソースを利用してWebページを表現します。

## メソッド

リソースはHTTPのメソッドに対応した6つのメソッドでアクセスすることができます。

### GET
リソースの状態を取得します。安全なメソッドです。このメソッドではリソースの状態を変えてはいけません。

### PUT
リクエストしたURIでリソースの状態を置き換えます。このメソッドは安全ではなくリソースの状態を変更します。
メソッドには[冪等性](https://ja.wikipedia.org/wiki/%E5%86%AA%E7%AD%89)がありメソッドを何度実行しても結果は同じです。

### PATCH
リソースを部分的に変更します。

### POST
リクエストしたURIに新しいリソースを追加します。このメソッドは安全ではなくリソースの状態を変更します。冪等性はなくリクエストの回数分リソースが追加されます。

### DELETE
リソースの削除をします。冪等性があります。

### OPTIONS
リソースのリクエストに必要なパラメーターとレスポンスに関する情報を取得します。安全なメソッドです。

## パラメーター

HTTPからリクエストされた時に`onGet`メソッドの引数には`$_GET`、`onPost`には`$_POST`が変数名に応じて渡されます。例えば下記の$idは$_GET['id']が渡されます。


```php?start_inline
class Index extends ResourceObject
{
    public function onGet(int $id) : ResourceObject
    {
```    

GET,POST以外のメソッド`onPut`,`onPatch`, `onDelete`の引数にはリクエストボディの値が`content-type`ヘッダーで指定されたフォーマットで与えられます。 [^1]
例えば`application/json`ならJSONフォーマットで、`x-www-form-urlencoded`ならURL経由で渡されるクエリ文字列と同じフォーマット key1=val1&key2=vale2&..として扱われます。

パラメーターはネストされたデータ [^2] でも構いません。
JSONやネストされたクエリ文字列で送信されたデータは配列やクラスでも受け取る事ができます。

```php?start_inline
class Index extends ResourceObject
{
    public function onPost(array $user) : ResourceObject
    {
        $name = $user['name']; // bear
```

```php?start_inline
class Index extends ResourceObject
{
    public function onPost(User $user) : ResourceObject
    {
        $name = $user->name; // bear
```

受け取るクラス（Inputクラス）は事前にパラメーターをpublicプロパティにしたものを定義しておきます。

```php?start_inline
<?php

namespace Vendor\App\Input;

final class User
{
    public $id;
    public $name;
}
```

ネームスペースは任意です。Inputクラスでは入力データをまとめたり検証したりするメソッドを実装する事ができます。[^3]

```php?start_inline
final class User
{
    public $givenName;
    public $familyName;
    
    public function getFullName() : string
    {
        return "{$this->givenName} {$this->familyName}";
    }
}
```

配列受け取りはInputクラスの集合として入力を受け取る時にも便利です。

### バインドパラメーター

リソースクラスのメソッドの引数をWebコンテキストや他リソースの状態と束縛することができます。

#### Webコンテキストパラメーター

`$_GET`や`$_COOKIE`などのPHPのスーパーグローバルの値をメソッド内で取得するのではなく、メソッドの引数に束縛することができます。

キーの名前と引数の名前が同じ場合

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;

class News extends ResourceObject
{
    /**
     * @QueryParam("id")
     */
    public function foo(string $id) : ResourceObject
    {
      // $id = $_GET['id'];
```

キーの名前と引数の名前が違う場合は`key`と`param`で指定します。

```php?start_inline
use Ray\WebContextParam\Annotation\CookieParam;

class News extends ResourceObject
{
    /**
     * @CookieParam(key="id", param="tokenId")
     */
    public function foo(string $tokenId) : ResourceObject
    {
      // $tokenId = $_COOKIE['id'];
```

フルリスト

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

class News extends ResourceObject
{
    /**
     * @QueryParam(key="id", param="userId")
     * @CookieParam(key="id", param="tokenId")
     * @EnvParam("app_mode")
     * @FormParam("token")
     * @ServerParam(key="SERVER_NAME", param="server")
     */
    public function foo(
        string $userId,           // $_GET['id'];
        string $tokenId = "0000", // $_COOKIE['id'] or "0000" when unset;
        string $app_mode,         // $_ENV['app_mode'];
        string $token,            // $_POST['token'];
        string $server            // $_SERVER['SERVER_NAME'];
    ) : ResourceObject {
```

この機能を使うためには引数のデフォルトに`null`が必要です。
またクライアントが値を指定した時は指定した値が優先され、束縛した値は無効になります。

#### リソースパラメーター

`@ResourceParam`アノテーションを使えば他のリソースリクエストの結果をメソッドの引数に束縛できます。

```php?start_inline
use BEAR\Resource\Annotation\ResourceParam;

class News extends ResourceObject
{
    /**
     * @ResourceParam(param=“name”, uri="app://self//login#nickname")
     */
    public function onGet(string $name) : ResourceObject
    {
```

この例ではメソッドが呼ばれると`login`リソースに`get`リクエストを行い`$body['nickname']`を`$name`で受け取ります。


## レンダリング

`ResourceObject`クラスのリクエストメソッド(`onGet`など）はリソースがHTMLで表現されるかJSONで表現されるかなどの表現に対して関心を持ちません。
コンテキストによって`ResourceObject`にインジェクトされたリソースレンダラーがJSONやHTMLにレンダリングしてリソース表現(view)にします。

レンダリングはリソースが文字列評価された時に行われます。

```php?start_inline

$weekday = $api->resource->get('app://self/weekday', ['year' => 2000, 'month'=> 1, 'day'=> 1]);
var_dump($weekday->body); // as array
//array(1) {
//    ["weekday"]=>
//  string(3) "Sat"
//}

echo $weekday; // as string
//{
//    "weekday": "Sat",
//    "_links": {
//    "self": {
//        "href": "/weekday/2000/1/1"
//        }
//    }
//}
```

コンテキストに応じてインジェクトされるので普段は意識する必要はありません。

リソース特有の表現が必要な時は以下のように独自のレンダラーをインジェクトします。

```php?start_inline
class Index extends ResourceObject
{
    // ...
    /**
     * @Inject
     * @Named("my_renderer")
     */
    public function setRenderer(RenderInterface $renderer)
    {
        parent::setRenderer($renderer);
    }
}
```

or

```php?start_inline
class Index extends ResourceObject
{
    /**
     * @Inject
     */
    public function setRenderer(RenderInterface $renderer)
    {
        $this->renderer = new class implements RenderInterface {
            public function render(ResourceObject $ro)
            {
                $ro->headers['content-type'] = 'application/json;';
                $ro->view = json_encode($ro->body);

                return $ro->view;
            }
        };
    }
}
```

## 転送

トランスポンダーが表現(view)をクライント（コンソールやWebクライアント）に転送します。
転送は単に`header`関数や`echo`で行われることがほとんどですが、[ストリーム出力](stream.html)で転送することもできます。

レンダラーと同じように普段は意識する必要はありません。

リソース特有の転送を行う時は以下のメソッドをオーバーライドします。

```php?start_inline
class Index extends ResourceObject
{
    // ...
    public function transfer(TransferInterface $responder, array $server)
    {
        $responder($this, $server);
    }
}
```

このようにリソースはリクエストによって自身のリソース状態を変更、それを表現にして転送する機能を各クラスが持っています。

## クライアント

インジェクトしたリソースクライアントを使用して他のリソースのリクエストをします。

```php?start_inline
use BEAR\Sunday\Inject\ResourceInject;

class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet() : ResourceObject
    {
        $this->body = [
            'posts' => $this->resource->get('app://self/blog/posts', ['id' => 1])
        ];
    }
}
```
このリクエストは`app://self/blog/posts`リソースに`?id=1`というクエリーでリクエストを実行します。
この他にも以下の表記があります。

```php?start_inline
// PHP 5.x and up 
$posts = $this->resource->get->uri('app://self/posts')->withQuery(['id' => 1])->eager->request();
// PHP 7.x and up
$posts = $this->resource->get->uri('app://self/posts')(['id' => 1]);
// getは省略可
$posts = $this->resource->uri('app://self/posts')(['id' => 1]);
// bear/resource 1.11 and up 
$posts = $this->resource->get('app://self/posts', ['id' => 1]);
```

以上はリクエストをすぐに行う`eager`リクエストですが、リクエスト結果ではなくリクエストそのものを取得し、実行を遅延することもできます。

```php?start_inline
$request = $this->resource->uri('app://self/posts'); // callable
$posts = $request(['id' => 1]);
```

このリクエストをテンプレートやリソースに埋め込むと、その要素が使用されるときに評価されリクエストが実行されます。つまり評価されない時はリクエストは行われず実行コストがかかりません。

## リンクリクエスト

クラインアントはハイパーリンクで接続されているリソースをリンクすることができます。

```php?start_inline
$blog = $this
    ->resource
    ->get
    ->uri('app://self/user')
    ->withQuery(['id' => 1])
    ->linkSelf("blog")
    ->eager
    ->request()
    ->body;
```

リンクは３種類あります。`$rel`をキーにして元のリソースの`body`リンク先のリソースが埋め込まれます。

 * `linkSelf($rel)` リンク先と入れ替わります。
 * `linkNew($rel)` リンク先のリソースがリンク元のリソースに追加されます
 * `linkCrawl($rel)` リンクをクロールして"リソースツリー"を作成します。

### クロールリンク

ツリー構造をもつリソース、例えばユーザーからブログ、ブログから記事、記事からコメント、コメントから評価にリンクされているようなツリー構造のリソースは`linkCrawl()`で上手く取得できます。
詳しくは[クロール](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.ja.md#%E3%82%AF%E3%83%AD%E3%83%BC%E3%83%AB)をご覧ください。

## リンクアノテーション

他のリソースをハイパーリンクする`@Link`と他のリソースを内部に埋め込む`@Embed`アノテーションが利用できます。

### @Link

リンクを`rel`と`href`で指定します。`hal`コンテキストではHALのリンクフォーマットとして扱われます。

```php?start_inline
    /**
     * @Link(rel="profile", href="/profile{?id}")
     */
    public function onGet($id) : ResourceObject
    {
        $this->body = [
            'id' => 10
        ];

        return $this;
    }
```

`@Link`の`href`のURIの`{?id}`は`$body`の値です。（メソッドの`$id`ではありません）[RFC6570 URI template](https://github.com/ioseb/uri-template)でURIが生成され、HALでの出力は以下のようになります。
```json
{
    "id": 10,
    "_links": {
        "self": {
            "href": "/test"
        },
        "profile": {
            "href": "/profile?id=10"
        }
    }
}
```

BEARのリソースリクエストでは`linkSelf()`, `linkNew`, `linkCrawl`の時にリソースリンクとして使われます。

```php?start_inline
use BEAR\Resource\Annotation\Link;

/**
 * @Link(crawl="post-tree", rel="post", href="app://self/post?author_id={id}")
 */
public function onGet($id = null) : ResourceObject
```

`linkCrawl`は`crawl`の付いたリンクを[クロール](https://github.com/koriym/BEAR.Resource#crawl)してリソースを集めます。

## 埋め込みリソース

リソースの中に`@Embed`の`src`で指定した別のリソースを埋め込むことができます。

```php?start_inline
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    /**
     * @Embed(rel="sports", src="/news/sports")
     * @Embed(rel="weater", src="/news/weather")
     */
    public function onGet() : ResourceObject
```

埋め込まれるのはリソース**リクエスト**です。レンダリングの時に実行されますが、その前に`addQuery()`メソッドで引数を加えたり`withQuery()`で引数を置き換えることができます。

`src`にはURI templateが利用でき、**リクエストメソッドの引数**がバインドされます。（リソースの`$body`ではありません）

```php?start_inline
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    /**
     * @Embed(rel="website", src="/website{?id}")
     */
    public function onGet(string $id) : ResourceObject
    {
        // ...
        $this->body['website']->addQuery(['title' => $title]); // 引数追加
```

[HAL](https://github.com/blongden/hal)レンダラーでは`_embedded `として扱われます。


## リソースキャッシュ

### @Cacheable

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

/**
 * @Cacheable
 */
class User extends ResourceObject
```

`@Cacheable`とアノテートすると`get`リクエストは読み込み用のレポジトリ`QueryRepository`が使われ、時間無制限のキャッシュとして機能します。
`get`以外のリクエストがあると該当する`QueryRepository`のリソースが更新されます。

`@Cacheable`から読まれるリソースオブジェクトはHTTPに準じた`Last-Modified`と`ETag`ヘッダーが付加されます。

同一クラスの`onGet`以外のリクエストメソッドがリクエストされ引数を見てリソースが変更されたと判断すると`QueryRepository`の内容も更新されます。


```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

/**
 * @Cacheable
 */
class Todo extends ResourceObject
{
    public function onGet(string $id) : ResourceObject
    {
        // read
    }

    public function onPost(string $id, string $name) : ResourceObject
    {
        // update
    }
}
```

例えばこのクラスでは`->post(10, 'shopping')`というリクエストがあると`id=10`の`QueryRepository`の内容が更新されます。
この自動更新を利用しない時は`update`をfalseにします。

```php?start_inline
/**
 * @Cacheable(update=false)
 */
```

時間を指定するには、`expiry`を使って、`short`, `medium`あるいは`long`のいずれかを指定できます。
```php?start_inline
/**
 * @Cacheable(expiry="short")
 */
```


## @Purge @Refresh

もう１つの方法は`@Purge`アノテーションや、`@Refresh`アノテーションで更新対象のURIを指定することです。


```php?start_inline
use BEAR\RepositoryModule\Annotation\Purge;
use BEAR\RepositoryModule\Annotation\Refresh;

class News extends ResourceObject
{
  /**
   * @Purge(uri="app://self/user/friend?user_id={id}")
   * @Refresh(uri="app://self/user/profile?user_id={id}")
   */
   public function onPut(string $id, string $name, int $age)) : ResourceObject
```

別のクラスのリソースや関連する複数のリソースの`QueryRepository`の内容を更新することができます。
`@Purge`はリソースのキャッシュを消去し`@Refresh`はキャッシュの再生成をメソッド実行直後に行います。

uri-templateに与えられる値は他と同様に`$body`にアサインした値が実引数に優先したものです。

```php?start_inline
use BEAR\RepositoryModule\Annotation\Purge;
use BEAR\RepositoryModule\Annotation\Refresh;

class News extends ResourceObject
{
  /**
   * @Purge(uri="app://self/user/friend?user_id={id}")
   * @Refresh(uri="app://self/user/profile?user_id={id}")
   */
   public function onPut($id, $name, $age) : ResourceObject
```

## クエリーリポジトリの直接操作

クエリーリポジトリに格納されているデータは`QueryRepositoryInterface`で受け取ったクライアントで直接`put`（保存）したり`get`したりすることができます。

```php?start_inline
use BEAR\QueryRepository\QueryRepositoryInterface;

class Foo
{
    public function __construct(QueryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function foo()
    {
        // 保存
        $this->repository->put($this);
        $this->repository->put($resourceObject);

        // 消去
        $this->repository->purge($resourceObject->uri);
        $this->repository->purge(new Uri('app://self/user'));
        $this->repository->purge(new Uri('app://self/ad/?id={id}', ['id' => 1]));

        // 読み込み
        list($code, $headers, $body, $view) = $this->repository->get(new Uri('app://self/user'));
     }
```

## ベストプラクティス<a name="best-practice"></a>

RESTではリソースは他のリソースと接続されています。リンクをうまく使うとコードは簡潔になり、読みやすくテストや変更が容易なコードになります。

### @Embed

他のリソースの状態を`get`する代わりに`@Embed`でリソースを埋め込みます。

```php?start_inline
// OK but not the best
class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(string $status) : ResourceObject
    {
        $this->body = [
            'todos' => $this->resource->uri('app://self/todos')(['status' => $status]) // lazy request
        ];

        return $this;
    }
}

// Better
class Index extends ResourceObject
{
    /**
     * @Embed(rel="todos", src="app://self/todos{?status}")
     */
    public function onGet(string $status) : ResourceObject
    {
        return $this;
    }
}
```

### @Link

他のリソースの状態を変えるときに`@Link`で示された次のアクションを`href()`（ハイパーリファレンス）を使って辿ります。

```php?start_inline
// OK but not the best
class Todo extends ResourceObject
{
    use ResourceInject;

    public function onPost(string $title) : ResourceObject
    {
        $this->resource->post('app://self/todo', ['title' => $title]);
        $this->code = 301;
        $this->headers[ResponseHeader::LOCATION] = '/';

        return $this;
    }
}

// Better
class Todo extends ResourceObject
{
    use ResourceInject;

    /**
     * @Link(rel="create", href="app://self/todo", method="post")
     */
    public function onPost(string $title) : ResourceObject
    {
        $this->resource->href('create', ['title' => $title]);
        $this->code = 301;
        $this->headers[ResponseHeader::LOCATION] = '/';

        return $this;
    }
}
```

### ＠ResourceParam

他のリソースをリクエストするために他のリソース結果が必要な場合は`＠ResourceParam`を使います。

```php?start_inline
// OK but not the best
class User extends ResourceObject
{
    use ResourceInject;

    public function onGet(string $id) : ResourceObject
    {
        $nickname = $this->resource->get('app://self/login-user', ['id' => $id])->body['nickname'];
        $this->body = [
            'profile'=> $this->resource->get('app://self/profile', ['name' => $nickname])->body
        ];

        return $this;
    }
}

// Better
class User extends ResourceObject
{
    use ResourceInject;

    /**
     * @ResourceParam(param=“name”, uri="app://self//login-user#nickname")
     */
    public function onGet(string $id, string $name) : ResourceObject
    {
        $this->body = [
            'profile' => $this->resource->get('app://self/profile', ['name' => $name])->body
        ];

        return $this;
    }
}

// Best
class User extends ResourceObject
{
    /**
     * @ResourceParam(param=“name”, uri="app://self//login-user#nickname")
     * @Embed(rel="profile", src="app://self/profile")
     */
    public function onGet(string $id, string $name) : ResourceObject
    {
        $this->body['profile']->addQuery(['name'=>$name]);

        return $this;
    }
}
```


### bodyのシンタックスシュガー

`$this`へのarrayアクセスは`$this->body`のアクセスになります。

```php?start_inline
$this['price'] = 10;
// is same as
$this->body['price'] = 10;
```

## BEAR.Resource

リソースクラスに関するより詳しい情報はBEAR.Resourceの[README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.ja.md)もご覧ください。

---
[^1]:[PUT メソッドのサポート](https://www.php.net/manual/ja/features.file-upload.put-method.php)参照
[^2]:[parse_str](https://www.php.net/manual/ja/function.parse-str.php)参照 
[^3]:publicプロパティとして定義しないで、`__set()`マジックメソッドでバリデーションをする事もできます。
