---
layout: docs-ja
title: リソース
category: Manual
permalink: /manuals/1.0/ja/resource.html
---

# リソース

BEAR.Sundayアプリケーションは[REST](http://ja.wikipedia.org/wiki/REST)リソースの集合です。

## サービスとしてのオブジェクト

リソースクラスはHTTPのメソッドをPHPのメソッドにマップしてPHPのクラスをサービスとして扱います。

```php?start_inline
class Index extends ResourceObject
{
    public function onGet($a, $b)
    {
        $this->code = 200; // 省略可
        $this['result'] = $a + $b; // $_GET['a'] + $_GET['b']

        return $this;
    }
}
```

```php?start_inline
class Todo extends ResourceObject
{
    public function onPost($id, $todo)
    {
        $this->code = 201; // ステータスコード
        $this->headers['Location'] = '/todo/new_id'; // ヘッダー

        return $this;
    }
}
```

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

# クライアント

リソースのリクエストにはリソースクライアントを使用します。

```php?start_inline

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
```

このリクエストは`app://self/blog/posts`リソースに`?id=1`というクエリーでリクエストをすぐ`eager`に行います。

リソースのリクエストはlazyとeagerがあります。リクエストにeagerがついてないものがlazyリクエストです。

```php?start_inline
$posts = $this->resource->get->uri('app://self/posts')->request(); //lazy
$posts = $this->resource->get->uri('app://self/posts')->eager->request(); // eager
```

lazy `request()`で帰って来るオブジェクトは実行可能なリクエストオブジェクトです。`$posts()`で実行することができます。
このリクエストをテンプレートやリソースに埋め込むと、その要素が使用されるときに評価されます。

## リンクリクエスト

クラインアントはハイパーリンクで接続されているリソースをリンクすることができます。

```php?start_inline
$blog = $this
    ->resource
    ->get
    ->uri('app://self/User')
    ->withQuery(['id' => 1])
    ->linkSelf("blog")
    ->eager
    ->request()->body;
```

リンクは３種類あります。`$rel`をキーにして元のリソースの`body`リンク先のリソースが埋め込まれます。

 * `linkSelf($rel)` リンク先と入れ替わります。
 * `linkNew($rel)` リンク先のリソースがリンク元のリソースに追加されます
 * `linkCrawl($rel)` リンクをクロールして"リソースツリー"を作成します。



## リンクアノテーション

他のリソースをハイパーリンクする`@Link`と他のリソースを内部に埋め込む`@Embed`アノテーションが利用できます。

### @Link

リンクを`rel`と`href`で指定します。`hal`コンテキストではHALのリンクフォーマットとして扱われます。

```php?start_inline
    /**
     * @Link(rel="profile", href="/profile{?id}")
     */
    public function onGet($id)
    {
        $this['id'] = 10;

        return $this;
    }
```

`href`のURIの`{?id}`は`$body`の値です。[RFC6570 URI template](https://github.com/ioseb/uri-template)でURIが生成され、HALでの出力は以下のようになります。
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
public function onGet($id = null)
```

`linkCrawl`は`crawl`の付いたリンクを[クロール](https://github.com/koriym/BEAR.Resource#crawl)してリソースを集めます。

## 埋め込みリソース

リソースの中に`src`で指定した別のリソースを埋め込むことができます。

```php?start_inline
use BEAR\Resource\Annotation\Embed;

class News
{
    /**
     * @Embed(rel="sports", src="/news/sports")
     * @Embed(rel="weater", src="/news/weather")
     */
    public function onGet()
```

埋め込まれるのはリソース**リクエスト**です。レンダリングの時に実行されますが、その前に`addQuery()`メソッドで引数を加えたり`withQuery()`で引数を置き換えることができます。

```php?start_inline
    /**
     * @Embed(rel="website", src="/website{?id}")
     */
    public function onGet($id)
    {
        // ...
        $this['website']->addQuery(['title' => $title]); // 引数追加
```

[HAL](https://github.com/blongden/hal)レンダラーでは`_embedded `として扱われます。

## バインドパラメーター

リソースクラスのメソッドの引数をWebコンテキストや他リソースの状態と束縛することができます。

### Webコンテキストパラメーター

`$_GET`や`$_COOKIE`などのPHPのスーパーグローバルの値をメソッド内で取得するのではなく、メソッドの引数に束縛することができます。

キーの名前と引数の名前が同じ場合
```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;

    /**
     * @QueryParam("id")
     */
    public function foo($id = null)
    {
      // $id = $_GET['id'];
```


キーの名前と引数の名前が違う場合は`key`と`param`で指定
```php?start_inline
use Ray\WebContextParam\Annotation\CookieParam;

    /**
     * @CookieParam(key="id", param="tokenId")
     */
    public function foo($tokenId = null)
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

    /**
     * @QueryParam(key="id", param="userId")
     * @CookieParam(key="id", param="tokenId")
     * @EnvParam("app_mode")
     * @FormParam("token")
     * @ServerParam(key="SERVER_NAME", param="server")
     */
    public function foo($userId = null, $tokenId = null, $app_mode = null, $token = null, $server = null)
    {
       // $userId   = $_GET['id'];
       // $tokenId  = $_COOKIE['id'];
       // $app_mode = $_ENV['app_mode'];
       // $token    = $_POST['token'];
       // $server   = $_SERVER['SERVER_NAME'];
```

この機能を使うためには引数のデフォルトに`null`が必要です。
またクライアントが値を指定した時は指定した値が優先され、束縛した値は無効になります。

### リソースパラメーター

`@ResourceParam`アノテーションを使えば他のリソースリクエストの結果をメソッドの引数に束縛できます。

```php?start_inline
/**
 * @ResourceParam(param=“name”, uri="app://self//login#nickname")
 */
public function onGet($name)
{
```

この例ではメソッドが呼ばれると`login`リソースに`get`リクエストを行い`$body['nickname']`を`$name`で受け取ります。

## リソースキャッシュ

### @Cacheable

```php?start_inline
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

/**
 * @Cacheable
 */
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
/**
 * @Purge(uri="app://self/user/friend?user_id={id}")
 * @Refresh(uri="app://self/user/profile?user_id={id}")
 */
public function onPut($id, $name, $age)
```

別のクラスのリソースや関連する複数のリソースの`QueryRepository`の内容を更新することができます。
`@Purge`はリソースのキャッシュを消去し`@Refresh`はキャッシュの再生成をメソッド実行直後に行います。

uri-templateに与えられる値は他と同様に`$body`にアサインした値が実引数に優先したものです。

```php?start_inline
/**
 * @Refresh(uri="/user/profile{?id,name}")
 */
public function onPut($id, $name)
{
    $this['id'] = 5; // [1, 'bear']で呼ばれると /user/profile?id=5&name=bearを@Refresh
```


## クエリーリポジトリの直接操作

クエリージポジトリに格納されているデータは`QueryRepositoryInterface`で受け取ったクライアントで直接`put`（保存）したり`get`したりすることができます。

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
        $this->queryRepository->purge(new Uri('app://self/ad/?id={id}', ['id' => 1]));

        // 読み込み
        list($code, $headers, $body, $view) = $repository->get(new Uri('app://self/user'));
     }
```

## BEAR.Resource

リソースクラスに関するより詳しい情報はBEAR.Resourceの[README](https://github.com/bearsunday/BEAR.Resource/blob/1.x/README.ja.md)もご覧ください。
