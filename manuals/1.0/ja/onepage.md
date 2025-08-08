---
layout: docs-ja
title: 1 Page Manual
category: Manual
permalink: /manuals/1.0/ja/1page.html
---
これはBEAR.Sundayの全てのマニュアルページを一つにまとめたページです。



# BEAR.Sundayとは

BEAR.Sundayは、クリーンなオブジェクト指向設計と、Webの基本原則に沿ったリソース指向アーキテクチャを組み合わせたPHPのアプリケーションフレームワークです。
このフレームワークは標準への準拠、長期的な視点、高効率性、柔軟性、自己記述性に加え、シンプルさを重視します。

## フレームワーク

BEAR.Sundayは3つのフレームワークで構成されています。

`Ray.Di`は[依存性逆転の原則](http://en.wikipedia.org/wiki/Dependency_inversion_principle)に基づいてオブジェクトの依存をインターフェイスで結びます。

`Ray.Aop`は[アスペクト指向プログラミング](http://en.wikipedia.org/wiki/Aspect-oriented_programming)で本質的関心と横断的関心を結びます。

`BEAR.Resource`はアプリケーションのデータや機能をリソースとして[REST制約](https://en.wikipedia.org/wiki/Representational_state_transfer)で結びます。

フレームワークは、アプリケーション全体に適用される制約と設計原則です。一貫性のある設計と実装を促進し、高品質でクリーンなアプリケーションの構築を支援します。

## ライブラリ

BEAR.Sundayはフルスタックフレームワークとは異なり、認証やデータベースなどの特定のタスクのための独自のライブラリは提供しません。その代わりに、高品質なサードパーティ製のライブラリを使用することを推奨します。

このアプローチは2つの設計思想に基づいています。1つ目は「フレームワークは変わらないがライブラリは変わる」という考え方です。フレームワークがアプリケーションの基盤として安定した構造を提供し続ける一方で、ライブラリは時間の経過とともに進化し、アプリケーションの特定のニーズを満たします。

2つ目は「ライブラリを選択する権利と責任はアプリケーションアーキテクトにある」というものです。アプリケーションアーキテクトは、アプリケーションの要件、制約、および目的に最も適したライブラリを選択する能力と責任を持ちます。

BEAR.Sundayは、フレームワークとライブラリの違いを"不易流行"（変わらぬ基本原則と時代とともに進化する要素）として明確に区別し、アプリケーション制約としてのフレームワークの役割を重視します。

## アーキテクチャ

BEAR.Sundayは、従来のMVC（Model-View-Controller）アーキテクチャとは異なり、リソース指向アーキテクチャ(ROA)を採用しています。このアーキテクチャでは、アプリケーションの設計において、データとビジネスロジックを統一してリソースとして扱い、それらに対するリンクと操作を中心に設計を行います。リソース指向アーキテクチャはREST APIの設計で広く使用されていますが、BEAR.SundayはそれをWebアプリケーション全体の設計にも適用しています。

## 長期的な視点

BEAR.Sundayは、アプリケーションの長期的な維持を念頭に置いて設計されています。

- **制約**: DI、AOP、RESTの制約に従った一貫したアプリケーション制約は、時間の経過とともに変わることはありません。

- **永遠の1.x**: 2015年の最初のリリース以来、BEAR.Sundayは後方互換性のない変更を導入することなく、継続的に進化してきました。開発者はフレームワークの定期的な互換性破壊への対応とそのテストが必要という将来の技術負債を心配する必要がありません。

- **標準準拠**: HTTP標準、JsonSchemaなどの標準に従い、DIはGoogle Guice、AOPはJavaのAop Allianceに基づいています。

## 接続性

BEAR.Sundayは、Webアプリケーションを超えて、さまざまなクライアントとのシームレスな統合を可能にします。

- **HTTPクライアント**:
  HTTPを使用して全てのリソースにアクセスすることが可能です。MVCのモデルやコントローラーと違い、BEAR.Sundayのリソースはクライアントから直接アクセスが可能です。

- **composerパッケージ**:
  composerでvendor下にインストールしたアプリケーションのリソースを直接呼び出すことができます。マイクロサービスを使わずに複数のアプリケーションを協調させることができます。

- **多言語フレームワーク**:
  BEAR.Thriftを使用して、PHP以外の言語や異なるバージョンのPHPとの連携を可能にします。

## Webキャッシュ

リソース指向アーキテクチャとモダンなCDNの技術を組み合わせることにより、従来のサーバーサイドのTTLキャッシュを超えるWeb本来の分散キャッシングを実現します。BEAR.Sundayの設計思想は、Webの基本原則に沿っており、CDNを中心に配置した分散キャッシュシステムを活用することで、高いパフォーマンスと可用性を実現します。

- **分散キャッシュ**: キャッシュをクライアント、CDN、サーバーサイドに保存することで、CPUコストとネットワークコストの両方を削減します。

- **同一性確認**:
  ETagを使用してキャッシュされたコンテンツの同一性を確認し、コンテンツの変更があった場合にのみ再取得することで、ネットワーク効率を向上させます。

- **耐障害性**:
  イベントドリブンコンテンツの採用により、キャッシュに有効期限を設けないCDNキャッシュを基本としたシステムは、PHPやDBがダウンした場合でもコンテンツを提供し続けることができます。

## パフォーマンス

BEAR.Sundayは、最大限の柔軟性を保ちながら、パフォーマンスと効率性を重視して設計されています。
極めて最適化されたブートストラップが実現され、ユーザー体験とシステムリソースの両方に好影響を与えています。
パフォーマンスは常にBEAR.Sundayの最重要課題の一つであり、設計と開発の決定において中心的な役割を果たしています。

## Because Everything is a Resource

「全てがリソース」のBEAR.Sundayは、Webの本質であるリソースを中心に設計されたPHPのWebアプリケーションフレームワークです。その真の価値は、オブジェクト指向原則とREST原則に基づいた優れた制約をアプリケーション全体の制約として提供することにあります。

この制約は、開発者に一貫性のある設計と実装を促し、長期的な視点に立ったアプリケーションの品質を高めます。同時に、この制約は開発者に自由をもたらし、アプリケーション構築における創造性を引き出します。


# AOP

アスペクト指向プログラミングは、**横断的関心事**の問題を解決します。対象メソッドの前後に、任意の処理をインターセプターで織り込むことができます。
対象となるメソッドはビジネスロジックなどの本質的関心事のみに関心を払い、インターセプターはログや検証などの横断的関心事に関心を払います。

BEAR.Sundayは[AOP Alliance](http://aopalliance.sourceforge.net/)に準拠したアスペクト指向プログラミングをサポートします。

## インターセプター

インターセプターの`invoke`メソッドでは`$invocation`メソッド実行変数を受け取り、メソッドの前後に処理を加えます。これはインターセプター元メソッドを実行するためだけの変数です。前後にログやトランザクションなどの横断的処理を記述します。

```php?start_inline
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class MyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        // メソッド実行前の処理
        // ...

        // メソッド実行
        $result = $invocation->proceed();

        // メソッド実行後の処理
        // ...

        return $result;
    }
}
```

## 束縛

[モジュール](module.html)で対象となるクラスとメソッドを`Matcher`で"検索"して、マッチするメソッドにインターセプターを束縛します。

```php?start_inline
$this->bindInterceptor(
    $this->matcher->any(),                   // どのクラスでも
    $this->matcher->startsWith('delete'),    // "delete"で始まるメソッド名のメソッドには
    [Logger::class]                          // Loggerインターセプターを束縛
);

$this->bindInterceptor(
    $this->matcher->subclassesOf(AdminPage::class),  // AdminPageの継承または実装クラスの
    $this->matcher->annotatedWith(Auth::class),      // @Authアノテーションがアノテートされているメソッドには
    [AdminAuthentication::class]                     // AdminAuthenticationインターセプターを束縛
);
```

`Matcher`では以下のような指定も可能です：

* [Matcher::any](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L16) - 無制限
* [Matcher::annotatedWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L23) - アノテーション
* [Matcher::subclassesOf](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L30) - 継承または実装されたクラス
* [Matcher::startsWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L37) - 名前の始めの文字列
* [Matcher::logicalOr](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L44) - OR条件
* [Matcher::logicalAnd](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L51) - AND条件
* [Matcher::logicalNot](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L58) - NOT条件

インターセプターに渡される`MethodInvocation`では、対象のメソッド実行に関連するオブジェクトやメソッド、引数にアクセスすることができます。

* [MethodInvocation::proceed](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php) - 対象メソッド実行
* [MethodInvocation::getMethod](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php) - 対象メソッドリフレクションの取得
* [MethodInvocation::getThis](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php) - 対象オブジェクトの取得
* [MethodInvocation::getArguments](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Invocation.php) - 呼び出し引数配列の取得

リフレクションのメソッドでアノテーションを取得することができます。

```php?start_inline
$method = $invocation->getMethod();
$class = $invocation->getMethod()->getDeclaringClass();
```

* `$method->getAnnotations()`    - メソッドアノテーションの取得
* `$method->getAnnotation($name)`
* `$class->getAnnotations()`     - クラスアノテーションの取得
* `$class->getAnnotation($name)`

## カスタムマッチャー

独自のカスタムマッチャーを作成するには、`AbstractMatcher`の`matchesClass`と`matchesMethod`を実装したクラスを作成します。

`contains`マッチャーを作成するには、2つのメソッドを持つクラスを提供する必要があります。
1つはクラスのマッチを行う`matchesClass`メソッド、もう1つはメソッドのマッチを行う`matchesMethod`メソッドです。いずれもマッチしたかどうかをboolで返します。

```php?start_inline
use Ray\Aop\AbstractMatcher;

/**
 * 特定の文字列が含まれているか
 */
class ContainsMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        list($contains) = $arguments;

        return (strpos($class->name, $contains) !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        list($contains) = $arguments;

        return (strpos($method->name, $contains) !== false);
    }
}
```

モジュール

```php?start_inline
class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            new ContainsMatcher('user'), // 'user'がメソッド名に含まれているか
            [UserLogger::class]
        );
    }
};
```



# リソース

BEAR.SundayアプリケーションはRESTfulなリソースの集合です。

## サービスとしてのオブジェクト

`ResourceObject`はHTTPのメソッドがPHPのメソッドにマップされたリソースの**サービスのためのオブジェクト**（Object-as-a-service）です。ステートレスリクエストから、リソースの状態がリソース表現として生成され、クライアントに転送されます。（[Representational State Transfer](http://ja.wikipedia.org/wiki/REST)）

以下は、ResourceObjectの例です。

```php
class Index extends ResourceObject
{
    public $code = 200;
    public $headers = [];

    public function onGet(int $a, int $b): static
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
    public function onPost(string $id, string $todo): static
    {
        $this->code = 201; // ステータスコード
        $this->headers = [ // ヘッダー
            'Location' => '/todo/new_id'
        ];

        return $this;
    }
}
```

PHPのリソースクラスはWebのURIと同じような`page://self/index`などのURIを持ち、HTTPのメソッドに準じた`onGet`、`onPost`などのonメソッドを持ちます。onメソッドで与えられたパラメーターから自身のリソース状態`code`、`headers`、`body`を決定し、`$this`を返します。

## URI

URIはPHPのクラスにマップされています。アプリケーションではクラス名の代わりにURIを使ってリソースにアクセスします。

| URI | Class |
|



# リソースパラメーター

## 基本

ResourceObjectが必要とするHTTPリクエストやCookieなどのWebランタイムの値は、メソッドの引数に直接渡されます。HTTPリクエストでは`onGet`、`onPost`メソッドの引数にはそれぞれ`$_GET`、`$_POST`が変数名に応じて渡されます。

例えば下記の`$id`は`$_GET['id']`が渡されます。入力がHTTPの場合、文字列として渡された引数は指定した型にキャストされます。

```php
class Index extends ResourceObject
{
    public function onGet(int $id): static
    {
        // ....
```

## パラメーターの型

### スカラーパラメーター

HTTPで渡されるパラメーターは全て文字列ですが、`int`など文字列以外の型を指定するとキャストされます。

### 配列パラメーター

パラメーターはネストされたデータ [^2] でも構いません。JSONやネストされたクエリ文字列で送信されたデータは配列で受け取ることができます。

[^2]: [parse_str](https://www.php.net/manual/ja/function.parse-str.php)参照

```php
class Index extends ResourceObject
{
    public function onPost(array $user): static
    {
        $name = $user['name']; // bear
```

### クラスパラメーター

パラメータ専用のInputクラスで受け取ることもできます。

```php
class Index extends ResourceObject
{
    public function onPost(User $user): static
    {
        $name = $user->name; // bear
```

Inputクラスは事前にパラメーターをpublicプロパティにしたものを定義しておきます。

```php
<?php
namespace Vendor\App\Input;

final class User
{
    public int $id;
    public string $name;
}
```

この時、コンストラクタがあるとコールされます。[^php8]

[^php8]: PHP8.xでは名前付き引数で呼ばれますが、PHP7.xでは順序引数でコールされます。

```php
<?php
namespace Vendor\App\Input;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {
    }
}
```

ネームスペースは任意です。Inputクラスでは入力データをまとめたり検証したりするメソッドを実装することができます。

### 列挙型パラメーター

PHP8.1の[列挙型](https://www.php.net/manual/ja/language.types.enumerations.php)を指定して取り得る値を制限することができます。

```php
enum IceCreamId: int
{
    case VANILLA = 1;
    case PISTACHIO = 2;
}
```

```php
class Index extends ResourceObject
{
    public function onGet(IceCreamId $iceCreamId): static
    {
        $id = $iceCreamId->value // 1 or 2
```

上記の場合、1か2以外が渡されると`ParameterInvalidEnumException`が発生します。

## Webコンテキスト束縛

`$_GET`や`$_COOKIE`などのPHPのスーパーグローバルの値をメソッド内で取得するのではなく、メソッドの引数に束縛することができます。

```php
use Ray\WebContextParam\Annotation\QueryParam;

class News extends ResourceObject
{
    public function foo(
        #[QueryParam('id')] string $id
    ): static {
        // $id = $_GET['id'];
```

その他`$_ENV`、`$_POST`、`$_SERVER`の値を束縛することができます。

```php
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

class News extends ResourceObject
{
    public function onGet(
        #[QueryParam('id')] string $userId,            // $_GET['id']
        #[CookieParam('id')] string $tokenId = "0000", // $_COOKIE['id'] or "0000" when unset
        #[EnvParam('app_mode')] string $app_mode,      // $_ENV['app_mode']
        #[FormParam('token')] string $token,           // $_POST['token']
        #[ServerParam('SERVER_NAME')] string $server   // $_SERVER['SERVER_NAME']
    ): static {
```

クライアントが値を指定した時は指定した値が優先され、束縛した値は無効になります。テストの時に便利です。

## リソース束縛

`#[ResourceParam]`アノテーションを使えば他のリソースリクエストの結果をメソッドの引数に束縛できます。

```php
use BEAR\Resource\Annotation\ResourceParam;

class News extends ResourceObject
{
    public function onGet(
        #[ResourceParam('app://self//login#nickname')] string $name
    ): static {
```

この例ではメソッドが呼ばれると`login`リソースに`get`リクエストを行い、`$body['nickname']`を`$name`で受け取ります。

## コンテントネゴシエーション

HTTPリクエストの`content-type`ヘッダーがサポートされています。`application/json`と`x-www-form-urlencoded`メディアタイプを判別してパラメーターに値が渡されます。[^json]

[^json]: APIリクエストをJSONで送信する場合には`content-type`ヘッダーに`application/json`をセットしてください。



## ベストプラクティス

RESTではリソースは他のリソースと接続されています。リンクをうまく使うとコードは簡潔になり、読みやすくテストや変更が容易なコードになります。

### #[Embed]

他のリソースの状態を`get`する代わりに`#[Embed]`でリソースを埋め込みます。

```php
// OK but not the best
class Index extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )
    
    public function onGet(string $status): static
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
    #[Embed(rel: 'todos', src: 'app://self/todos{?status}')]
    public function onGet(string $status): static
    {
        return $this;
    }
}
```

### #[Link]

他のリソースの状態を変えるときに`#[Link]`で示された次のアクションを`href()`（ハイパーリファレンス）を使って辿ります。

```php
// OK but not the best
class Todo extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )
    
    public function onPost(string $title): static
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
    public function __construct(
        private readonly ResourceInterface $resource
    )
    
    #[Link(rel: 'create', href: 'app://self/todo', method: 'post')]
    public function onPost(string $title): static
    {
        $this->resource->href('create', ['title' => $title]);
        $this->code = 301;
        $this->headers[ResponseHeader::LOCATION] = '/';
        return $this;
    }
}
```

### #[ResourceParam]

他のリソースをリクエストするために他のリソース結果が必要な場合は`#[ResourceParam]`を使います。

```php
// OK but not the best
class User extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )
    
    public function onGet(string $id): static
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
    public function __construct(
        private readonly ResourceInterface $resource
    )
    
    #[ResourceParam(param: 'name', uri: 'app://self//login-user#nickname')]
    public function onGet(string $id, string $name): static
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
    #[ResourceParam(param: 'name', uri: 'app://self//login-user#nickname')]
    #[Embed(rel: 'profile', src: 'app://self/profile')]
    public function onGet(string $id, string $name): static
    {
        $this->body['profile']->addQuery(['name' => $name]);
        return $this;
    }
}
```



# リソースリンク

リソースは他のリソースをリンクすることができます。リンクには外部のリソースをリンクする外部リンク[^LO]と、リソース自身に他のリソースを埋め込む内部リンク[^LE]の2種類があります。

[^LE]: [embedded links](http://amundsen.com/hypermedia/hfactor/#le) 例）HTMLは独立した画像リソースを埋め込むことができます。
[^LO]: [out-bound links](http://amundsen.com/hypermedia/hfactor/#le) 例）HTMLは関連した他のHTMLにリンクを張ることができます。

## 外部リンク

リンクをリンクの名前の`rel`（リレーション）と`href`で指定します。`href`には正規のURIの他に[RFC6570 URIテンプレート](https://github.com/ioseb/uri-template)を指定することができます。

```php
    #[Link(rel: 'profile', href: '/profile{?id}')]
    public function onGet($id): static
    {
        $this->body = [
            'id' => 10
        ];
        return $this;
    }
```

上記の例では`href`で表されていて、`$body['id']`が`{?id}`にアサインされます。[HAL](https://stateless.group/hal_specification.html)フォーマットでの出力は以下のようになります。

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

## 内部リンク

リソースは別のリソースを埋め込むことができます。`#[Embed]`の`src`でリソースを指定します。内部リンクされたリソースも他のリソースを内部リンクしているかもしれません。その場合また内部リンクのリソースが必要で、それが再帰的に繰り返され**リソースグラフ**が得られます。

クライアントはリソースを何度もフェッチすることなく目的とするリソース群を一度に取得できます。[^di] 例えば顧客リソースと商品リソースをそれぞれ呼び出す代わりに、注文リソースで両者を埋め込みます。

[^di]: DIで依存関係のツリーがグラフになっているオブジェクトグラフと同様です。

```php
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    #[Embed(rel: 'sports', src: '/news/sports')]
    #[Embed(rel: 'weather', src: '/news/weather')]
    public function onGet(): static
```

埋め込まれるのはリソース**リクエスト**です。レンダリングの時に実行されますが、その前に`addQuery()`メソッドで引数を加えたり`withQuery()`で引数を置き換えることができます。`src`にはURI templateが利用でき、**リクエストメソッドの引数**がバインドされます（外部リンクと違って`$body`ではありません）。

```php
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    #[Embed(rel: 'website', src: '/website{?id}')]
    public function onGet(string $id): static
    {
        // ...
        $this->body['website']->addQuery(['title' => $title]); // 引数追加
```

### セルフリンク

`#[Embed]`でリレーションを`_self`としてリンクすると、リンク先のリソース状態を自身のリソース状態にコピーします。

```php
namespace MyVendor\Weekday\Resource\Page;

class Weekday extends ResourceObject
{
    #[Embed(rel: '_self', src: 'app://self/weekday{?year,month,day}')]
    public function onGet(string $id): static
    {
```

この例ではPageリソースがAppリソースの`weekday`リソースの状態を自身にコピーしています。

### HALでの内部リンク

[HAL](https://github.com/blongden/hal)レンダラーでは`_embedded`として扱われます。

## リンクリクエスト

クライアントはハイパーリンクで接続されているリソースをリンクすることができます。

```php
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

リンクは3種類あります。`$rel`をキーにして元のリソースの`body`にリンク先のリソースが埋め込まれます。

* `linkSelf($rel)` - リンク先と入れ替わります。
* `linkNew($rel)` - リンク先のリソースがリンク元のリソースに追加されます
* `linkCrawl($rel)` - リンクをクロールしてリソースグラフを作成します。

### クロール

クロールはリスト（配列）になっているリソースを順番にリンクを辿り、複雑なリソースグラフを構成することができます。クローラーがWebページをクロールするように、リソースクライアントはハイパーリンクをクロールしてリソースグラフを生成します。

#### クロール例

author, post, meta, tag, tag/nameがそれぞれ関連づけられているリソースグラフを考えてみます。このリソースグラフに **post-tree** という名前を付け、それぞれのリソースの`#[Link]`アトリビュートでハイパーリファレンス **href** を指定します。

最初に起点となるauthorリソースにはpostリソースへのハイパーリンクがあります。1:nの関係です。

```php
#[Link(crawl: "post-tree", rel: "post", href: "app://self/post?author_id={id}")]
public function onGet($id = null)
```

postリソースにはmetaリソースとtagリソースのハイパーリンクがあります。1:nの関係です。

```php
#[Link(crawl: "post-tree", rel: "meta", href: "app://self/meta?post_id={id}")]
#[Link(crawl: "post-tree", rel: "tag", href: "app://self/tag?post_id={id}")]
public function onGet($author_id)
{
```

tagリソースはIDだけでそのIDに対応するtag/nameリソースへのハイパーリンクがあります。1:1の関係です。

```php
#[Link(crawl: "post-tree", rel: "tag_name", href: "app://self/tag/name?tag_id={tag_id}")]
public function onGet($post_id)
```

それぞれが接続されました。クロール名を指定してリクエストします。

```php
$graph = $resource
    ->get
    ->uri('app://self/marshal/author')
    ->linkCrawl('post-tree')
    ->eager
    ->request();
```

リソースクライアントは`#[Link]`アトリビュートに指定されたクロール名を発見するとその**rel**名でリソースを接続してリソースグラフを作成します。

```php
var_export($graph->body);
array (
    0 =>
    array (
        'name' => 'Athos',
        'post' =>
        array (
            0 =>
            array (
                'author_id' => '1',
                'body' => 'Anna post #1',
                'meta' =>
                array (
                    0 =>
                    array (
                        'data' => 'meta 1',
                    ),
                ),
                'tag' =>
                array (
                    0 =>
                    array (
                        'tag_name' =>
                        array (
                            0 =>
                            array (
                                'name' => 'zim',
                            ),
                        ),
                    ), 
                    // ...
```



# レンダリングと転送

<img src="https://bearsunday.github.io/images/screen/4r.png" alt="Resource object internal structure">

ResourceObjectのリクエストメソッドではリソースの表現について関心を持ちません。コンテキストに応じて注入されたレンダラーがリソースの表現を生成します。同じアプリケーションがコンテキストを変えるだけでHTMLで出力されたり、JSONで出力されたりします。

## 遅延評価

レンダリングはリソースが文字列評価された時に行われます。

```php
$weekday = $api->resource->get('app://self/weekday', ['year' => 2000, 'month'=> 1, 'day'=> 1]);
var_dump($weekday->body);
//array(1) {
//    ["weekday"]=>
//    string(3) "Sat"
//}

echo $weekday;
//{
//    "weekday": "Sat",
//    "_links": {
//        "self": {
//            "href": "/weekday/2000/1/1"
//        }
//    }
//}
```

## レンダラー

それぞれのResourceObjectはコンテキストによって指定されたその表現のためのレンダラーが注入されています。リソース特有のレンダリングを行う時は`renderer`プロパティを注入またはセットします。

例）デフォルトで用意されているJSON表現のレンダラーをスクラッチで書くと：

```php
class Index extends ResourceObject
{
    #[Inject]
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

ルートオブジェクト`$app`にインジェクトされたリソース表現をクライアント（コンソールやWebクライアント）に転送します。通常、出力は`header`関数や`echo`で行われますが、巨大なデータなどには[ストリーム転送](stream.html)が有効です。

リソース特有の転送を行う時は`transfer`メソッドをオーバーライドします。

```php
public function transfer(TransferInterface $responder, array $server)
{
    $responder($this, $server);
}
```

## リソースの自律性

リソースはリクエストによって自身のリソース状態を変更し、それを表現にして転送する機能を各クラスが持っています。


# 技術

BEAR.Sundayの特徴的な技術と機能を以下の章に分けて解説します。

* [アーキテクチャと設計原則](#アーキテクチャと設計原則)
* [パフォーマンスとスケーラビリティ](#パフォーマンスとスケーラビリティ)
* [開発者エクスペリエンス](#開発者エクスペリエンス)
* [拡張性と統合](#拡張性と統合)
* [設計思想と品質](#設計思想と品質)
* [BEAR.Sundayのもたらす価値](#bearsundayのもたらす価値)

## アーキテクチャと設計原則

### リソース指向アーキテクチャ (ROA)

BEAR.SundayのROAは、WebアプリケーションでRESTful APIを実現するアーキテクチャです。これはBEAR.Sundayの設計原則の核となるものであり、ハイパーメディアフレームワークであると同時にサービスとしてのオブジェクト（Object as a service）として扱います。Webと同様に、全てのデータや機能をリソースとみなし、GET、POST、PUT、DELETEなどの標準化されたインターフェースを通じて操作します。

#### URI

URI（Uniform Resource Identifier）はWebの成功の鍵となる要素であり、BEAR.SundayのROAの中核でもあります。アプリケーションが扱う全てのリソースにURIを割り当てることで、リソースを識別し、アクセスしやすくなります。URIは、リソースの識別子として機能するだけでなく、リソース間のリンクを表現するためにも使用されます。

#### ユニフォームインターフェース

リソースへのアクセスはHTTPのメソッド（GET, POST, PUT, DELETE）を用いて行われます。これらのメソッドはリソースに対して実行できる操作を規定しており、リソースの種類にかかわらず共通のインターフェースを提供します。

#### ハイパーメディア

BEAR.SundayのROAでは、各リソースがハイパーリンクを通じてアフォーダンス（クライアントが利用可能な操作や機能）を提供します。これらのリンクは、クライアントが利用できる操作を表し、アプリケーション内をナビゲートする方法を示します。

#### 状態と表現の分離

BEAR.SundayのROAでは、リソースの状態とそのリソース表現が明確に分離されています。リソースの状態はリソースクラスで管理され、リソースにインジェクトされたレンダラーが様々な形式（JSON, HTMLなど）でリソースの状態をリソース状態表現に変換します。ドメインロジックとプレゼンテーションロジックは疎結合で、同じコードでもコンテキストによって状態表現の束縛を変更すると表現も変わります。

#### MVCとの相違点

BEAR.SundayのROAは、従来のMVCアーキテクチャとは異なるアプローチを採用しています。
MVCはモデル、ビュー、コントローラーの3つのコンポーネントでアプリケーションを構成し、コントローラーはリクエストオブジェクトを受け取り、一連の処理を制御してレスポンスを返します。一方、リソースはリクエストメソッドにおいて、単一責任原則（SRP）に従い、リソースの状態の指定のみを行い、表現には関与しません。

MVCではコントローラーとモデルの関係に制約はありませんが、リソースはハイパーリンクとURIを使用した他のリソースを含める明示的な制約があります。これにより、呼び出されるリソースの情報隠蔽を維持しながら、宣言的な方法でコンテンツの内包関係とツリー構造を定義できます。

MVCのコントローラーはリクエストオブジェクトから手動で値を取得しますが、リソースは必要な変数をリクエストメソッドの引数として宣言的に定義します。そのため、入力バリデーションもJsonSchemaを使用して宣言的に実行され、引数とその制約が文書化されます。

### 依存性の注入 (DI)

依存性の注入（Dependency Injection, DI）は、オブジェクト指向プログラミングにおけるアプリケーションの設計と構造を強化するための重要な手法です。DIの中心的な目的は、アプリケーションの機能を複数の独立したドメインまたは役割を持つコンポーネントに分割し、それらの間の依存関係を管理することです。

DIは、1つの機能（関心事、責務）を複数の機能に水平分割するのに役立ちます。分割された機能は「依存」として各部分を独立して開発、テストできるようになります。単一責任原則に基づき明確な責任と役割を持つそれらの依存を外部から注入することで、オブジェクトの再利用性とテスト性を向上させます。また依存は他の依存へと垂直にも分割され、依存関係のツリーを形成します。

BEAR.SundayのDIは[Ray.Di](https://github.com/ray-di/Ray.Di)という独立したパッケージを使用しており、Google社製のDIフレームワークであるGuiceの設計思想を取り入れ、ほぼ全ての機能をカバーしています。

その他に以下の特徴があります。

* コンテキストにより束縛を変更し、テスト時に異なる実装を注入できます。
* アトリビュートによる設定でコードの自己記述性が高まります。
* Ray.Diはコンパイル時に依存性の解決を行うため、ランタイム時のパフォーマンスが向上します。これは、ランタイム時に依存性を解決する他のDIコンテナとは異なる点です。
* オブジェクトの依存関係をグラフで可視化できます。例）[ルートオブジェクト](/images/app.svg)

<img src="https://ray-di.github.io/images/logo.svg" width="180" alt="Ray.Di logo">

### アスペクト指向プログラミング (AOP)

アスペクト指向プログラミング（AOP）は、ビジネスロジックなどの本質的な関心と、ログやキャッシュなどの横断的関心を分離することで、柔軟なアプリケーションを実現するパターンです。横断的関心とは、複数のモジュールやレイヤーにまたがって存在する機能や処理のことを指します。探索条件に基づいた横断的処理の束縛が可能で、コンテキストに基づいた柔軟な構成が可能です。

BEAR.SundayのAOPはRay.Aopという独立したパッケージを使用しており、PHPのアトリビュートをクラスやメソッドに付与して、横断的処理を宣言的に束縛します。Ray.Aopは、Javaの[AOP Alliance](https://aopalliance.sourceforge.net/)に準拠しています。

AOPは「既存の秩序を壊す強い力」と誤解されがちな技術です。その存在意義は制約を超えた力の行使などではなく、マッチャーを使った探索的な機能の割り当てや横断的処理の分離など、オブジェクト指向が不得意とする分野の補完にあります。AOPはアプリケーションの横断的な制約を作ることのできる、つまりアプリケーションフレームワークとして機能するパラダイムです。

## パフォーマンスとスケーラビリティ

### モダンCDNとの統合によるROAベースのイベントドリブンコンテンツ戦略

BEAR.Sundayは、リソース指向アーキテクチャ（ROA）を中核として、Fastlyなどのインスタントパージ可能なCDNと統合することで、高度なイベントドリブンキャッシュ戦略を実現しています。この戦略では、従来のTTL（Time to Live）によるキャッシュの無効化ではなく、リソースの状態変更イベントに応じてCDNとサーバーサイドのキャッシュ、およびETag（エンティティタグ）を即座に無効化します。

このようにCDNに揮発性のない永続的なコンテンツを配置するというアプローチにより、SPOF（Single Point of Failure）を回避し、高い可用性と耐障害性を実現します。さらに、ユーザー体験とコスト効率を最大化させ、ダイナミックコンテンツでもスタティックコンテンツと同じWeb本来の分散キャッシングを実現します。これは、Webが1990年代から持っていたスケーラブルでネットワークコストを削減する分散キャッシュという原則を、現代的な技術で再実現するものです。

#### セマンティックメソッドと依存によるキャッシュ無効化

BEAR.SundayのROAでは、各リソース操作にセマンティック（意味的な役割）が与えられています。例えば、GETメソッドはリソースを取得し、PUTメソッドはリソースを更新します。これらのメソッドがイベントドリブン方式で連携し、関連するキャッシュを効率的に無効化します。例えば、特定のリソースが更新された際には、そのリソースを必要とするリソースのキャッシュが無効化されます。これにより、データの一貫性と新鮮さを保ち、ユーザーに最新の情報を提供します。

#### ETagによる同一性確認と高速な応答

システムがブートする前にETagを設定することで、コンテンツの同一性を迅速に確認し、変更がない場合は304 Not Modified応答を返してネットワークの負荷を最小化します。

#### ドーナッツキャッシュとESIによる部分的な更新

BEAR.Sundayでは、ドーナッツキャッシュ戦略を採用しており、ESI（Edge Side Includes）を使用してCDNエッジで部分的なコンテンツ更新を可能にしています。この技術により、ページ全体を再キャッシュすることなく、必要な部分だけを動的に更新してキャッシュ効率を向上させます。

このように、BEAR.SundayとFastlyの統合によるROAベースのキャッシュ戦略は、高度な分散キャッシングの実現とともに、アプリケーションのパフォーマンス向上と耐障害性の強化を実現しています。

### 起動の高速化

DIの本来の世界では、ユーザーは可能な限りインジェクター（DIコンテナ）を直接扱いません。その代わり、アプリケーションのエントリーポイントで1つのルートオブジェクトを生成してアプリケーションを起動します。BEAR.SundayのDIでは、設定時でもDIコンテナの操作が実質的に存在しません。ルートオブジェクトは巨大ですが1つの変数なので、リクエストを超えて再利用され、極限まで最適化したブートストラップを実現します。

## 開発者エクスペリエンス

### テストの容易性

BEAR.Sundayは、以下の設計上の特徴により、テストが容易で効果的に行えます。

* 各リソースは独立していて、RESTのステートレスリクエストの性質によりテストが容易です。
  リソースの状態と表現が明確に分離されているため、HTML表現の場合でもリソースの状態をテストできます。
* ハイパーメディアのリンクをたどりながらAPIのテストを行え、PHPとHTTPの同一コードでテストできます。
* コンテキストによる束縛により、テスト時に異なる実装を束縛できます。

### APIドキュメント生成

コードからAPIドキュメントを自動生成します。コードとドキュメントの整合性を保ち、保守性を高めます。

### 視覚化とデバッグ

リソースが自身でレンダリングする技術的特徴を生かし、開発時にHTML上でリソースの範囲を示し、リソース状態をモニターできます。また、PHPコードやHTMLテンプレートをオンラインエディターで編集し、リアルタイムに反映することもできます。

## 拡張性と統合

### PHPインターフェイスとSQL実行の統合

BEAR.SundayではPHPのインターフェイスを通じて、データベースとのやり取りを行うSQL文の実行を簡単に管理できます。クラスを実装することなく、PHPインターフェイスに直接SQLの実行オブジェクトを束縛することが可能です。ドメインとインフラストラクチャーの境界をPHPインターフェイスで結びます。

引数には型も指定でき、不足している分はDIが依存解決を行い文字列として利用されます。SQL実行に現在時刻が必要な場合でも渡す必要はなく、自動束縛されます。クライアントが全ての引数を渡す責任がなく、コードの簡潔さを保つことができます。

また、SQLの直接管理は、エラー発生時のデバッグを容易にします。SQLクエリの動作を直接観察し、問題の特定と修正を迅速に行うことができます。

### 他システムとの統合

コンソールアプリケーションと統合し、ソースコードを変えずにWebとコマンドライン双方からアクセスできます。また、同一PHPランタイム内で異なるBEAR.Sundayアプリケーションを並行実行できることで、マイクロサービスを構築することなく独立した複数のアプリケーションを連携できます。

### ストリーム出力

リソースのボディにファイルのポインタなどのストリームを割り当てることで、メモリ上では扱えない大規模なコンテンツを出力できます。その際、ストリームは通常の実変数と混在させることも可能で、大規模なレスポンスを柔軟に出力できます。

### 他のシステムからの段階的移行

BEAR.Sundayは段階的な移行パスを提供し、LaravelやSymfonyなどの他のフレームワークやシステムとのシームレスな統合を可能にします。このフレームワークはComposerパッケージとして実装できるため、開発者は既存のコードベースにBEAR.Sundayの機能を段階的に導入できます。

### 技術移行の柔軟性

BEAR.Sundayは、将来の技術的変化や要件の進化に備えて投資を保護します。このフレームワークから別のフレームワークや言語に移行する必要がある場合でも、構築したリソースは無駄になりません。PHP環境では、BEAR.SundayアプリケーションをComposerパッケージとして統合して継続的に利用できます。また、BEAR.Thriftを使用すると、他の言語からBEAR.Sundayリソースに効率的にアクセスでき、Thriftを使用しない場合でもHTTPでアクセスが可能です。さらに、SQLコードの再利用も容易です。

また、使用しているライブラリが特定のPHPバージョンに強く依存している場合でも、BEAR.Thriftを使用して異なるバージョンのPHPを共存させることができます。

## 設計思想と品質

### 標準技術の採用と独自規格の排除

BEAR.Sundayは、可能な限り標準技術を採用し、フレームワーク独自の規格やルールを排除するという設計思想を持っています。例えば、デフォルトでJSON形式とwwwフォーム形式のHTTPリクエストのコンテントネゴシエーションをサポートし、エラーレスポンスには[vnd.error+json](https://github.com/blongden/vnd.error)メディアタイプ形式を使用します。リソース間のリンクには[HAL](https://datatracker.ietf.org/doc/html/draft-kelly-json-hal)（Hypertext Application Language）を採用し、バリデーションには[JsonSchema](https://json-schema.org/)を用いるなど、標準的な技術や仕様を積極的に取り入れています。

一方で、独自のバリデーションルールや、フレームワーク特有の規格・ルールは可能な限り排除しています。

### オブジェクト指向原則

BEAR.Sundayはアプリケーションを長期的にメンテナンス可能とするためのオブジェクト指向原則を重視しています。

#### 継承より合成

継承クラスよりコンポジションを推奨します。一般に子クラスから親クラスのメソッドを直接呼び出すことは、クラス間の結合度を高くする可能性があります。設計上、ランタイムで継承が必要な抽象クラスはリソースクラスの`BEAR\Resource\ResourceObject`のみですが、これもResourceObjectのメソッドは他のクラスが利用するためだけに存在します。ユーザーが継承したフレームワークの親クラスのメソッドをランタイムに呼び出すことは、BEAR.Sundayではどのクラスにもありません。

#### 全てがインジェクション

フレームワークのクラスが「設定ファイル」や「デバッグ定数」を実行中に参照して振る舞いを決定することはありません。振る舞いに応じた依存が注入されます。これにより、アプリケーションの振る舞いを変更するためには、コードを変更する必要がなく、インターフェイスに対する依存性の実装の束縛を変更するだけで済みます。APP_DEBUGやAPP_MODE定数は存在しません。ソフトウェアが起動した後に現在どのモードで動作しているか知る方法はありませんし、知る必要もありません。

### 後方互換性の永続的確保

BEAR.Sundayは、ソフトウェアの進化において後方互換性の維持を重視して設計されており、リリース以来、後方互換性を破壊することなく進化を続けています。現代のソフトウェア開発では、頻繁な後方互換性の破壊と、それに伴う改修やテストの負担が課題となっていますが、BEAR.Sundayはこの問題を回避してきました。

BEAR.Sundayでは、セマンティックバージョニングを採用するだけでなく、破壊的な変更を伴うメジャーバージョンアップを行いません。新しい機能の追加や既存機能の変更が既存のコードに影響を与えることを防いでいます。古くなって使われなくなったコードには「deprecated」の属性が与えられますが、削除されることはなく、既存のコードの動作にも影響を与えません。その代わりに、新しい機能が追加され、進化が続けられます。

### 非環式依存原則

非環式依存原則（ADP）とは、依存関係が一方向であり、循環していないことを意味します。BEAR.Sundayフレームワークはこの原則に基づき、一連のパッケージで構成されており、大きなフレームワークパッケージが小さなフレームワークパッケージに依存する階層構造を持っています。各レベルはそれを包含する他のレベルの存在自体を知る必要はなく、依存関係は一方向のみで循環しません。例えば、Ray.AopはRay.Diの存在すら知りませんし、Ray.DiはBEAR.Sundayの存在を知りません。

<img src="/images/screen/package_adp.png" width="360px" alt="非環式依存原則に従ったフレームワーク構造">

後方互換性が保持されているため、各パッケージは独立して更新できます。また、他のフレームワークで見られるような全体をロックするバージョン番号は存在せず、オブジェクト間を横断する依存関係を持つオブジェクトプロキシーの機構もありません。

この非環式依存原則はDI（依存性注入）の原則と調和しており、BEAR.Sundayが起動する際に生成されるルートオブジェクトも、この非環式依存原則の構造に従って構築されます。

[<img src="/images/screen/clean-architecture.png" width="40%">](/images/screen/clean-architecture.png)

ランタイムも同様です。リソースにアクセスが行われる際、まずメソッドに結び付けられたAOPアスペクトの横断的な処理が行われ、その後でメソッドがリソースの状態を決定しますが、この時点でメソッドは結び付けられたアスペクトの存在を認識していません。リソースの状態に埋め込まれたリソースも同じです。それらは外側の層や要素の知識を持っていません。関心の分離が明確にされています。

### コード品質

高品質なアプリケーションを提供するため、BEAR.Sundayフレームワークも高い水準でコード品質を維持するよう努めています。

* フレームワークのコードは静的解析ツールのPsalmとPHPStan双方で最も厳しいレベルを適用しています。
* テストカバレッジ100%を保持しており、タイプカバレッジもほぼ100%です。
* 原則的にイミュータブルなシステムであり、テストでも毎回初期化が不要なほどクリーンです。SwooleのようなPHPの非同期通信エンジンの性能を最大限に引き出します。

## BEAR.Sundayのもたらす価値

### 開発者にとっての価値

* **生産性の向上**：
  堅牢な設計パターンと原則に基づく、時間が経過しても変わることのない制約により、開発者はコアとなるビジネスロジックに集中できます。

* **チームでの協業**：
  開発チームに一貫性のあるガイドラインと構造を提供することで、異なる開発者のコードを疎結合のまま統一的に保ち、コードの可読性とメンテナンス性を向上させます。

* **柔軟性と拡張性**：
  BEAR.Sundayがライブラリを含まないという方針により、開発者はコンポーネントの選択において高い柔軟性と自由度を得られます。

* **テストの容易性**：
  DI（依存性の注入）とROA（リソース指向アーキテクチャ）の採用により、効果的かつ効率的なテストの実施が可能です。

### ユーザーにとっての価値

* **高いパフォーマンス**：
  最適化された高速起動とCDNを中心としたキャッシュ戦略により、ユーザーには高速で応答性の優れたエクスペリエンスが提供されます。

* **信頼性と可用性**：
  CDNを中心としたキャッシュ戦略により、単一障害点（SPOF）を最小化し、ユーザーに安定したサービスを提供し続けることができます。

* **使いやすさ**：
  優れた接続性により、他の言語やシステムとの円滑な連携が実現します。

### ビジネスにとっての価値

* **開発コストの削減**：
  一貫性のあるガイドラインと構造の提供により、持続的で効率的な開発プロセスを実現し、開発コストを抑制します。

* **保守コストの削減**：
  後方互換性を重視するアプローチにより、技術的な継続性を高め、変更対応にかかる時間とコストを最小限に抑えます。

* **高い拡張性**：
  DI（依存性の注入）やAOP（アスペクト指向プログラミング）などの技術により、コードの変更を最小限に抑えながら振る舞いを変更でき、ビジネスの成長や変化に合わせて柔軟にアプリケーションを拡張できます。

* **優れたユーザーエクスペリエンス（UX）**：
  高いパフォーマンスと可用性の提供により、ユーザー満足度を向上させ、顧客ロイヤリティの強化と顧客基盤の拡大を通じて、ビジネスの成功に貢献します。

### まとめ

優れた制約は不変です。BEAR.Sundayが提供する制約は、開発者、ユーザー、ビジネスのそれぞれに対して、具体的かつ持続的な価値をもたらします。

BEAR.Sundayは、Webの原則と精神に基づいて設計されたフレームワークです。開発者に明確な制約を提供することで、柔軟性と堅牢性を兼ね備えたアプリケーションを構築する力を与えます。



# ルーター

ルーターはWebやコンソールなどの外部コンテキストのリソースリクエストを、BEAR.Sunday内部のリソースリクエストに変換します。

```php
$request = $app->router->match($GLOBALS, $_SERVER);
echo (string) $request;
// get page://self/user?name=bear
```

## Webルーター

デフォルトのWebルーターではHTTPリクエストのパス(`$_SERVER['REQUEST_URI']`)に対応したリソースクラスにアクセスされます。例えば`/index`のリクエストは`{Vendor名}\{Project名}\Resource\Page\Index`クラスのHTTPメソッドに応じたPHPメソッドにアクセスされます。

Webルーターは規約ベースのルーターです。設定やスクリプトは必要ありません。

```php
namespace MyVendor\MyProject\Resource\Page;

// page://self/index
class Index extends ResourceObject
{
    public function onGet(): static // GETリクエスト
    {
    }
}
```

## CLIルーター

`cli`コンテキストではコンソールからの引数が外部入力になります。

```bash
php bin/page.php get /
```

BEAR.SundayアプリケーションはWebとCLIの双方で動作します。

## 複数の単語を使ったURI

ハイフンを使い複数の単語を使ったURIのパスはキャメルケースのクラス名を使います。例えば`/wild-animal`のリクエストは`WildAnimal`クラスにアクセスされます。

## パラメーター

HTTPメソッドに対応して実行されるPHPメソッドの名前と渡される値は以下の通りです。

| HTTPメソッド | PHPメソッド | 渡される値 |
||---|
| GET | onGet | $_GET |
| POST | onPost | $_POST または 標準入力 |
| PUT | onPut | ※標準入力 |
| PATCH | onPatch | ※標準入力 |
| DELETE | onDelete | ※標準入力 |

リクエストのメディアタイプは以下の2つが利用できます：
* `application/x-www-form-urlencoded` // param1=one&param2=two
* `application/json` // {"param1": "one", "param2": "one"} (POSTの時は標準入力の値が使われます）

PHPマニュアルの[PUT メソッドのサポート](http://php.net/manual/ja/features.file-upload.put-method.php)もご覧ください。

## メソッドオーバーライド

HTTP PUT トラフィックやHTTP DELETE トラフィックを許可しないファイアウォールがあります。この制約に対応するため、次の2つの方法でこれらの要求を送ることができます：

* `X-HTTP-Method-Override` - POSTリクエストのヘッダーフィールドを使用してPUTリクエストやDELETEリクエストを送る。
* `_method` - URI パラメーターを使用する。例）POST /users?...&_method=PUT

## Auraルーター

リクエストのパスをパラメーターとして受け取る場合はAura Routerを使用します。

```bash
composer require bear/aura-router-module ^2.0
```

ルータースクリプトのパスを指定して`AuraRouterModule`をインストールします。

```php
use BEAR\Package\AbstractAppModule;
use BEAR\Package\Provide\Router\AuraRouterModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new AuraRouterModule($appDir . '/var/conf/aura.route.php'));
    }
}
```

キャッシュされているDIファイルを消去します。

```bash
rm -rf var/tmp/*
```

### ルータースクリプト

ルータースクリプトではグローバルで渡された`Map`オブジェクトに対してルートを設定します。ルーティングにメソッドを指定する必要はありません。1つ目の引数はルート名としてパス、2つ目の引数に名前付きトークンのプレイスフォルダーを含んだパスを指定します。

`var/conf/aura.route.php`
```php
<?php
/* @var \Aura\Router\Map $map */
$map->route('/blog', '/blog/{id}');
$map->route('/blog/comment', '/blog/{id}/comment');
$map->route('/user', '/user/{name}')->tokens(['name' => '[a-z]+']);
```

* 最初の行では`/blog/bear`とアクセスがあると`page://self/blog?id=bear`としてアクセスされます。（`Blog`クラスの`onGet($id)`メソッドに`$id`=`bear`の値でコールされます。）
* `/blog/{id}/comment`は`Blog\Comment`クラスにルートされます。
* `token()`はパラメーターを正規表現で制限するときに使用します。

### 優先ルーター

Auraルーターでルートされない場合は、Webルーターが使われます。つまりパスでパラメーターを渡すURIだけにルータースクリプトを用意すればOKです。

### パラメーター

パスからパラメーターを取得するためにAuraルーターは様々な方法が用意されています。

### カスタムマッチング

下のスクリプトは`{date}`が適切なフォーマットの時だけルートします。

```php
$map->route('/calendar/from', '/calendar/from/{date}')
    ->tokens([
        'date' => function ($date, $route, $request) {
            try {
                new \DateTime($date);
                return true;
            } catch(\Exception $e) {
                return false;
            }
        }
    ]);
```

### オプション

オプションのパラメーターを指定するためにはパスに`{/attribute1,attribute2,attribute3}`の表記を加えます。

例）
```php
$map->route('archive', '/archive{/year,month,day}')
    ->tokens([
        'year' => '\d{4}',
        'month' => '\d{2}',
        'day' => '\d{2}',
    ]);
```

プレイスホルダーの**内側に**最初のスラッシュがあるのに注意してください。そうすると下のパスは全て'archive'にルートされパラメーターの値が付加されます。

- `/archive            : ['year' => null,   'month' => null, 'day' = null]`
- `/archive/1979       : ['year' => '1979', 'month' => null, 'day' = null]`
- `/archive/1979/11    : ['year' => '1979', 'month' => '11', 'day' = null]`
- `/archive/1979/11/07 : ['year' => '1979', 'month' => '11', 'day' = '07']`

オプションパラメーターは**並ぶ順に**オプションです。つまり"month"なしで"day"を指定することはできません。

### ワイルドカード

任意の長さのパスの末尾パラメーターとして格納したいときには`wildcard()`メソッドを使います。

```php
$map->route('wild', '/wild')
    ->wildcard('card');
```

スラッシュで区切られたパスの値が配列になり`wildcard()`で指定したパラメーターに格納されます。

- `/wild             : ['card' => []]`
- `/wild/foo         : ['card' => ['foo']]`
- `/wild/foo/bar     : ['card' => ['foo', 'bar']]`
- `/wild/foo/bar/baz : ['card' => ['foo', 'bar', 'baz']]`

その他の高度なルートに関してはAura Routerの[defining-routes](https://github.com/auraphp/Aura.Router/blob/3.x/docs/defining-routes.md)をご覧ください。

### リバースルーティング

ルートの名前とパラメーターの値からURIを生成することができます。

```php
use BEAR\Sunday\Extension\Router\RouterInterface;

class Index extends ResourceObject
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onGet(): static
    {
        $userLink = $this->router->generate('/user', ['name' => 'bear']);
        // '/user/bear'
```

### リクエストメソッド

リクエストメソッドを指定する必要はありません。

### リクエストヘッダー

通常リクエストヘッダーはAura.Routerに渡されていませんが、`RequestHeaderModule`をインストールするとAura.Routerでヘッダーを使ったマッチングが可能になります。

```php
$this->install(new RequestHeaderModule());
```

## 独自のルーター

コンポーネント:
* [BEAR.AuraRouterModule](https://github.com/bearsunday/BEAR.AuraRouterModule)を参考に[RouterInterface](https://github.com/bearsunday/BEAR.Sunday/blob/1.x/src/Extension/Router/RouterInterface.php)を実装します。



# プロダクション

BEAR.Sunday既定の`prod`束縛に対して、アプリケーションがそれぞれの[ディプロイ環境](https://en.wikipedia.org/wiki/Deployment_environment)に応じたモジュールをカスタマイズして束縛を行います。

## 既定のProdModule

既定の`prod`束縛では以下のインターフェイスの束縛がされています。

* エラーページ生成ファクトリー
* PSRロガーインターフェイス
* ローカルキャッシュ
* 分散キャッシュ

詳細はBEAR.Packageの[ProdModule.php](https://github.com/bearsunday/BEAR.Package/blob/1.x/src/Context/ProdModule.php)参照。

## アプリケーションのProdModule

既定のProdModuleに対してアプリケーションの`ProdModule`を`src/Module/ProdModule.php`に設置してカスタマイズします。特にエラーページと分散キャッシュは重要です。

```php
<?php
namespace MyVendor\Todo\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\QueryRepository\CacheVersionModule;
use BEAR\Resource\Module\OptionsMethodModule;
use BEAR\Package\AbstractAppModule;

class ProdModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new PackageProdModule);       // デフォルトのprod設定
        $this->override(new OptionsMethodModule);    // OPTIONSメソッドをプロダクションでも有効に
        $this->install(new CacheVersionModule('1')); // リソースキャッシュのバージョン指定
        
        // 独自のエラーページ
        $this->bind(ErrorPageFactoryInterface::class)->to(MyErrorPageFactory::class);
    }
}
```

## キャッシュ

キャッシュはローカルキャッシュと、複数のWebサーバー間でシェアをする分散キャッシュの2種類があります。どちらのキャッシュもデフォルトは[PhpFileCache](https://www.doctrine-project.org/projects/doctrine-cache/en/1.10/index.html#phpfilecache)です。

### ローカルキャッシュ

ローカルキャッシュはdeploy後に変更のないアノテーション等のキャッシュ例に使われ、分散キャッシュはリソース状態の保存に使われます。

### 分散キャッシュ

2つ以上のWebサーバーでサービスを行うためには分散キャッシュの構成が必要です。代表的な[memcached](http://php.net/manual/ja/book.memcached.php)、[Redis](https://redis.io)のキャッシュエンジンのそれぞれのモジュールが用意されています。

### Memcached
```php
<?php
namespace BEAR\HelloWorld\Module;

use BEAR\QueryRepository\StorageMemcachedModule;
use BEAR\Resource\Module\ProdLoggerModule;
use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\Package\AbstractAppModule;
use Ray\Di\Scope;

class ProdModule extends AbstractModule
{
    protected function configure()
    {
        // memcache
        // {host}:{port}:{weight},...
        $memcachedServers = 'mem1.domain.com:11211:33,mem2.domain.com:11211:67';
        $this->install(new StorageMemcachedModule(memcachedServers));
        
        // Prodロガーのインストール
        $this->install(new ProdLoggerModule);
        
        // デフォルトのProdModuleのインストール
        $this->install(new PackageProdModule);
    }
}
```

### Redis

```php
// redis
$redisServer = 'localhost:6379'; // {host}:{port}
$this->install(new StorageRedisModule($redisServer));
```

リソースの状態保存は単にTTLによる時間更新のキャッシュとの他に、TTL時間では消えない永続的なストレージとして（CQRS）の運用も可能です。その場合には`Redis`で永続処理を行うか、Cassandraなどの他KVSのストレージアダプターを独自で用意する必要があります。

### キャッシュ時間の指定

デフォルトのTTLを変更する場合`StorageExpiryModule`をインストールします。

```php
// Cache time
$short = 60;
$medium = 3600;
$long = 24 * 3600;
$this->install(new StorageExpiryModule($short, $medium, $long));
```

### キャッシュバージョンの指定

リソースのスキーマが変わり、互換性が失われる時にはキャッシュバージョンを変更します。特にTTL時間で消えないCQRS運用の場合に重要です。

```php
$this->install(new CacheVersionModule($cacheVersion));
```

ディプロイの度にリソースキャッシュを破棄するためには`$cacheVersion`に時刻や乱数の値を割り当てると変更が不要で便利です。

## ログ

`ProdLoggerModule`はプロダクション用のリソース実行ログモジュールです。インストールするとGET以外のリクエストを`Psr\Log\LoggerInterface`にバインドされているロガーでログします。

特定のリソースや特定の状態でログしたい場合は、カスタムのログを[BEAR\Resource\LoggerInterface](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/LoggerInterface.php)にバインドします。

```php
use BEAR\Resource\LoggerInterface;
use Ray\Di\AbstractModule;

final class MyProdLoggerModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(LoggerInterface::class)->to(MyProdLogger::class);
    }
}
```

[LoggerInterface](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/LoggerInterface.php)の`__invoke`メソッドでリソースのURIとリソース状態が`ResourceObject`オブジェクトとして渡されるのでその内容で必要な部分をログします。作成には[既存の実装 ProdLogger](https://github.com/bearsunday/BEAR.Resource/blob/1.x/src/ProdLogger.php)を参考にしてください。

## デプロイ

### ⚠️ 上書き更新を避ける

#### サーバーにディプロイする場合

* 駆動中のプロジェクトフォルダを`rsync`などで上書きするのはキャッシュやオンデマンドで生成されるファイルの不一致や、高負荷のサイトではキャパシティを超えるリスクがあります。安全のために別のディレクトリでセットアップを行い、そのセットアップが成功すれば切り替えるようにします。
* [Deployer](http://deployer.org/)の[BEAR.Sundayレシピ](https://github.com/bearsunday/deploy)を利用することができます。

#### クラウドにディプロイする時には
* コンパイルが成功すると0、依存関係の問題を見つけるとコンパイラはexitコード1を出力します。それを利用してCIにコンパイルを組み込むことを推奨します。

### コンパイル

推奨セットアップを行う際に`vendor/bin/bear.compile`スクリプトを使ってプロジェクトを**ウォームアップ**することができます。コンパイルスクリプトはDI/AOP用の動的に作成されるファイルやアノテーションなどの静的なキャッシュファイルを全て事前に作成し、最適化されたautoload.phpファイルとpreload.phpを出力します。

* コンパイルをすれば全てのクラスでインジェクションを行うのでランタイムでDIのエラーが出る可能性が極めて低くなります。
* `.env`には含まれた内容はPHPファイルに取り込まれるのでコンパイル後に`.env`を消去可能です。コンテントネゴシエーションを行う場合など（例：api-app, html-app）1つのアプリケーションで複数コンテキストのコンパイルを行うときにはファイルの退避が必要です。

```bash
mv autoload.php api.autoload.php
```

`composer.json`を編集して`composer compile`の内容を変更します。

### autoload.php

`{project_path}/autoload.php`に最適化されたautoload.phpファイルが出力されます。`composer dumpa-autoload --optimize`で出力される`vendor/autoload.php`よりずっと高速です。

注意：`preload.php`を利用する場合、ほとんどの利用クラスが読み込まれた状態で起動するのでコンパイルされた`autoload.php`は不要です。composerが生成する`vendor/autload.php`をご利用ください。

### preload.php

`{project_path}/preload.php`に最適化されたpreload.phpファイルが出力されます。preloadを有効にするためにはphp.iniで[opcache.preload](https://www.php.net/manual/ja/opcache.configuration.php#ini.opcache.preload)、[opcache.preload_user](https://www.php.net/manual/ja/opcache.configuration.php#ini.opcache.preload-user)を指定する必要があります。

PHP 7.4でサポートされた機能ですが、`7.4`初期のバージョンでは不安定です。`7.4.4`以上の最新版を使いましょう。

例）
```ini
opcache.preload=/path/to/project/preload.php
opcache.preload_user=www-data
```

Note: パフォーマンスベンチマークは[benchmark](https://github.com/bearsunday/BEAR.HelloworldBenchmark/wiki/Intel-Core-i5-3.8-GHz-iMac-(Retina-5K,-27-inch,-2017)---PHP-7.4.4)を参考にしてください。

### .compile.php

実環境ではないと生成ができないクラス（例えば認証が成功しないとインジェクトが完了しないResourceObject）がある場合には、コンパイル時にのみ読み込まれるダミークラス読み込みをルートの`.compile.php`に記述することによってコンパイルをすることができます。

.compile.php
```php
<?php
require __DIR__ . '/tests/Null/AuthProvider.php'; // 常に生成可能なNullオブジェクト
$_SERVER[__REQUIRED_KEY__] = 'fake';
```

### module.dot

コンパイルをすると"dotファイル"が出力されるので[graphviz](https://graphviz.org/)で画像ファイルに変換するか、[GraphvizOnline](https://dreampuf.github.io/GraphvizOnline/)を利用すればオブジェクトグラフを表示することができます。スケルトンの[オブジェクトグラフ](/images/screen/skeleton.svg)もご覧ください。

```bash
dot -T svg module.dot > module.svg
```

## ブートストラップのパフォーマンスチューニング

[immutable_cache](https://pecl.php.net/package/immutable_cache)は、不変の値を共有メモリにキャッシュするためのPECLパッケージです。APCuをベースにしていますが、PHPのオブジェクトや配列などの不変の値を共有メモリに保存するため、APCuよりも高速です。また、APCuでもimmutable_cacheでも、PECLの[Igbinary](https://www.php.net/manual/ja/book.igbinary.php)をインストールすることでメモリ使用量が減り、さらなる高速化が期待できます。

現在、専用のキャッシュアダプターなどは用意されていません。[ImmutableBootstrap](https://github.com/koriym/BEAR.Hello/commit/507d1ee3ed514686be2d786cdaae1ba8bed63cc4)を参考に、専用のBootstrapを作成し呼び出してください。初期化コストを最小限に抑え、最大のパフォーマンスを得ることができます。

### php.ini
```ini
// エクステンション
extension="apcu.so"
extension="immutable_cache.so"
extension="igbinary.so"

// シリアライザーの指定
apc.serializer=igbinary
immutable_cache.serializer=igbinary
```



# インポート

BEARのアプリケーションは、マイクロサービスにすることなく複数のBEARアプリケーションを協調して1つのシステムにすることができます。また、他のアプリケーションからBEARのリソースを利用するのも容易です。

## composer インストール

利用するBEARアプリケーションをcomposerパッケージにしてインストールします。

composer.json
```json
{
  "require": {
    "bear/package": "^1.13",
    "my-vendor/weekday": "dev-master"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/bearsunday/tutorial1.git"
    }
  ]
}
```

`bear/package ^1.13`が必要です。

## モジュールインストール

インポートするホスト名とアプリケーション名（namespace）、コンテキストを指定して`ImportAppModule`で他のアプリケーションをインストールします。

AppModule.php
```diff
+use BEAR\Package\Module\ImportAppModule;
+use BEAR\Package\Module\Import\ImportApp;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        // ...
+        $this->install(new ImportAppModule([
+            new ImportApp('foo', 'MyVendor\Weekday', 'prod-app')
+        ]));
        $this->install(new PackageModule());
    }
}
```

`ImportAppModule`は`BEAR\Resource`ではなく`BEAR\Package`のものであることに注意してください。

## リクエスト

インポートしたリソースは指定したホスト名を指定して利用します。

```php
class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(string $name = 'BEAR.Sunday'): static
    {
        $weekday = $this->resource->get('app://foo/weekday?year=2022&month=1&day=1');
        $this->body = [
            'greeting' => 'Hello ' . $name,
            'weekday' => $weekday
        ];
        
        return $this;
    }
}
```

`#[Embed]`や`#[Link]`も同様に利用できます。

## 他のシステムから

他のフレームワークやCMSからBEARのリソースを利用するのも容易です。同じようにパッケージとしてインストールして、`Injector::getInstance`でrequireしたアプリケーションのリソースクライアントを取得してリクエストします。

```php
use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;

$resource = Injector::getInstance(
    'MyVendor\Weekday',
    'prod-api-app',
    dirname(__DIR__) . '/vendor/my-vendor/weekday'
)->getInstance(ResourceInterface::class);

$weekday = $resource->get('/weekday', ['year' => '2022', 'month' => '1', 'day' => 1]);
echo $weekday->body['weekday'] . PHP_EOL;
```

## 環境変数

環境変数はグローバルです。アプリケーション間でコンフリクトしないようにプリフィックスを付与するなどして注意する必要があります。インポートするアプリケーションは`.env`ファイルを使うのではなく、プロダクションと同じようにシェルの環境変数を取得します。

## システム境界

大きなアプリケーションを小さな複数のアプリケーションの集合体として構築できる点はマイクロサービスと同じですが、インフラストラクチャのオーバーヘッドの増加などのマイクロサービスのデメリットがありません。またモジュラーモノリスよりもコンポーネントの独立性や境界が明確です。

このページのコードは [bearsunday/example-app-import](https://github.com/bearsunday/example-import-app/commits/master) にあります。

## 多言語フレームワーク

[BEAR.Thrift](https://github.com/bearsunday/BEAR.Thrift)を使うと、Apache Thriftを使って他の言語や異なるバージョンのPHPやBEARアプリケーションからリソースにアクセスできます。[Apache Thrift](https://thrift.apache.org/)は、異なる言語間での効率的な通信を可能にするフレームワークです。



## AaaS (Application as a Service)

作成したAPIアプリケーションはWebやコンソール（バッチ）からアクセスできますが、他のPHPプロジェクトからライブラリとしてアクセスする事もできます。
このチュートリアルで作成したリポジトリは[https://github.com/bearsunday/Tutorial2.git](https://github.com/bearsunday/Tutorial2.git)にpushしてあります。

このプロジェクトをライブラリとして利用してみましょう。まず最初に新しいプロジェクトフォルダを作って`composer.json`を用意します。

```
mkdir app
cd app
mkdir -p ticket/log
mkdir ticket/tmp
```

composer.json

```json
{
    "name": "my-vendor/app",
    "description": "A BEAR.Sunday application",
    "type": "project",
    "license": "proprietary",
    "require": {
        "my-vendor/ticket": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/bearsunday/Tutorial2.git"
        }
    ]
}
```

composer installでプロジェクトがライブラリとしてインストールされます。

```
composer install
```

`Ticket API`はプロジェクトフォルダにある`.env`を読むように設定されてました。`vendor/my-vendor/app/.env`に保存出来なくもないですが、ここでは別の方法で環境変数をセットアップしましょう。

このような`app/.env`ファイルを用意します。

```bash
export TKT_DB_HOST=localhost
export TKT_DB_NAME=ticket
export TKT_DB_USER=root
export TKT_DB_PASS=''
export TKT_DB_SLAVE=''
export TKT_DB_DSN=mysql:host=${TKT_DB_HOST}\;dbname=${TKT_DB_NAME}
```

`source`コマンドで環境変数にexportすることができます。

```
source .env
```

`Ticket API`を他のプロジェクトから利用する最も簡単なスクリプトは以下のようなものです。
アプリケーション名とコンテキストを指定してアプリケーションオブジェクト`$ticket`を取得してリソースアクセスします。

```php
<?php
use BEAR\Package\Bootstrap;

require __DIR__ . '/vendor/autoload.php';

$ticket = (new Bootstrap)->getApp('MyVendor\Ticket', 'app');
$response = $ticket->resource->post('app://self/ticket',
    ['title' => 'run']
);

echo $response->code . PHP_EOL;


```

`index.php`と保存して実行してみましょう。

```
php index.php
```
```
201
```

APIを他のメソッドに渡したり、他のフレームワークなどののコンテナに格納するためには`callable`オブジェクトにします。
`$createTicket`は普通の関数のように扱うことができます。

```php
<?php
use BEAR\Package\Bootstrap;

require __DIR__ . '/vendor/autoload.php';

$ticket = (new Bootstrap)->getApp('MyVendor\Ticket', 'app');
$createTicket = $ticket->resource->post->uri('app://self/ticket');
// invoke callable object
$response = $createTicket(['title' => 'run']);
echo $response->code . PHP_EOL;
```

うまく動きましたか？しかし、このままでは`tmp`/ `log`ディレクトリは`vendor`の下のアプリが使われてしまいますね。
このようにアプリケーションのメタ情報を変更するとディレクトリの位置を変更することができます。

```php
<?php

use BEAR\AppMeta\Meta;
use BEAR\Package\Bootstrap;

require __DIR__ . '/vendor/autoload.php';

$meta = new Meta('MyVendor\Ticket', 'app');
$meta->tmpDir = __DIR__ . '/ticket/tmp';
$meta->logDir = __DIR__ . '/ticket/log';
$ticket = (new Bootstrap)->newApp($meta, 'app');
```

`Ticket API`はREST APIとしてHTTPやコンソールからアクセスできるだけでなく、BEAR.Sundayではない他のプロジェクトのライブラリとしても使えるようになりました！

----

# from tutorial1

## アプリケーションのインポート

BEAR.Sundayで作られたリソースは再利用性に優れています。複数のアプリケーションを同時に動作させ、他のアプリケーションのリソースを利用することができます。別々のWebサーバーを立てる必要はありません。

他のアプリケーションのリソースを利用して見ましょう。

通常はアプリケーションをパッケージとして利用しますが、ここではチュートリアルのために`my-vendor`に新規でアプリケーションを作成して手動でオートローダーを設定します。

```bash
mkdir my-vendor
cd my-vendor
composer create-project bear/skeleton Acme.Blog
```

`composer.json`で`autoload`のセクションに`Acme\\Blog`を追加します。

```json
"autoload": {
    "psr-4": {
        "MyVendor\\Weekday\\": "src/",
        "Acme\\Blog\\": "my-vendor/Acme.Blog/src/"
    }
},
```

`autoload`をダンプします。

```bash
composer dump-autoload
```

これで`Acme\Blog`アプリケーションが配置できました。

次にアプリケーションをインポートするために`src/Module/AppModule.php`で`ImportAppModule`を上書き(override)インストールします。

```php
<?php
// ...
use BEAR\Resource\Module\ImportAppModule; // add this line
use BEAR\Resource\ImportApp; // add this line
use BEAR\Package\Context; // add this line

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $importConfig = [
            new ImportApp('blog', 'Acme\Blog', 'prod-hal-app') // host, name, context
        ];
        $this->override(new ImportAppModule($importConfig , Context::class));
    }
}
```

これは`Acme\Blog`アプリケーションを`prod-hal-app`コンテキストで作成したリソースを`blog`というホストで使用することができます。

`src/Resource/App/Import.php`にImportリソースを作成して確かめてみましょう。

```php
<?php
namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Import extends ResourceObject
{
    use ResourceInject;

    public function onGet()
    {
        $this->body =[
            'blog' => $this->resource->uri('page://blog/index')['greeting']
        ];

        return $this;
    }
}
```

`page://blog/index`リソースの`greeting`が`blog`に代入されているはずです。`@Embed`も同様に使えます。

```bash
php bin/app.php get /import
```

```bash
200 OK
content-type: application/hal+json

{
    "blog": "Hello BEAR.Sunday",
    "_links": {
        "self": {
            "href": "/import"
        }
    }
}
```

他のアプリケーションのリソースを利用することができました！データ取得をHTTP越しにする必要もありません。

合成されたアプリケーションも他からみたら１つのアプリケーションの１つのレイヤーです。
[レイヤードシステム](http://en.wikipedia.org/wiki/Representational_state_transfer#Layered_system)はRESTの特徴の１つです。

それでは最後に作成したアプリケーションのリソースを呼び出す最小限のスクリプトをコーディングして見ましょう。`bin/test.php`を作成します。


```php?start_inline
use BEAR\Package\Bootstrap;

require dirname(__DIR__) . '/autoload.php';

$api = (new Bootstrap)->getApp('MyVendor\Weekday', 'prod-hal-app');

$blog = $api->resource->uri('app://self/import')['blog'];
var_dump($blog);
```

`MyVendor\Weekday`アプリを`prod-hal-app`で起動して`app://self/import`リソースの`blog`をvar_dumpするコードです。

試して見ましょう。

```
php bin/import.php
```
```
string(17) "Hello BEAR.Sunday"
```

他にも

```php?start_inline
$weekday = $api->resource->uri('app://self/weekday')(['year' => 2000, 'month'=>1, 'day'=>1]);
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

```php?start_inline
$html = (new Bootstrap)->getApp('MyVendor\Weekday', 'prod-html-app');
$index = $html->resource->uri('page://self/index')(['year' => 2000, 'month'=>1, 'day'=>1]);
var_dump($index->code);
//int(200)

echo $index;
//<!DOCTYPE html>
//<html>
//<body>
//The weekday of 2000/1/1 is Sat.
//</body>
//</html>
```

ステートレスなリクエストでレスポンスが返ってくるRESTのリソースはPHPの関数のようなものです。`body`で値を取得したり`(string)`でJSONやHTMLなどの表現にすることができます。autoloadの部分を除けば二行、連結すればたった一行のスクリプトで  アプリケーションのどのリソースでも操作することができます。

このようにBEAR.Sundayで作られたリソースは他のCMSやフレームワークからも簡単に利用することができます。複数のアプリケーションの値を一度に扱うことができます。




# データベース

データベースの利用のために、問題解決方法の異なった以下のモジュールが用意されています。いずれも[PDO](https://www.php.net/manual/ja/intro.pdo.php)をベースにしたSQLのための独立ライブラリです。

* PDOをextendしたExtendedPdo ([Aura.sql](https://github.com/auraphp/Aura.Sql))
* クエリービルダー ([Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery))
* PHPのインターフェイスとSQL実行を束縛 ([Ray.MediaQuery](database_media.html))

静的なSQLはファイルにすると[^locater]、管理や他のSQLツールでの検証などの使い勝手もよくなります。Aura.SqlQueryは動的にクエリーを組み立てることができますが、その他は基本静的なSQLの実行のためのライブラリです。また、Ray.MediaQueryではSQLの一部をビルダーで組み立てたものに入れ替えることもできます。

[^locater]: [query-locater](https://github.com/koriym/Koriym.QueryLocator)はSQLをファイルとして扱うライブラリです。Aura.Sqlと共に使うと便利です。

## モジュール

必要なライブラリに応じたモジュールをインストールします。

* [Ray.AuraSqlModule](database_aura.html)
* [Ray.MediaQuery](database_media.html)

`Ray.AuraSqlModule`はAura.SqlとAura.SqlQueryを含みます。

`Ray.MediaQuery`はユーザーが用意したインターフェイスとSQLから、SQL実行オブジェクトを生成しインジェクトする[^doma]高機能なDBアクセスフレームワークです。

[^doma]: JavaのDBアクセスフレームワーク[Doma](https://doma.readthedocs.io/en/latest/basic/#examples)と仕組みが似ています。

## その他

* [DBAL](database_dbal.html)
* [CakeDb](database_cake.html)
* [Ray.QueryModule](https://github.com/ray-di/Ray.QueryModule/blob/1.x/README.ja.md)

`DBAL`はDoctrine、`CakeDB`はCakePHPのDBライブラリです。`Ray.QueryModule`はRay.MediaQueryの以前のライブラリでSQLを無名関数に変換します。



# Ray.AuraSqlModule

`Ray.AuraSqlModule`はPDO拡張のAura.SqlとクエリビルダーAura.SqlQuery、その他にデータベースクエリー結果のページネーションのためのライブラリを提供します。

## インストール

composerで`ray/aura-sql-module`をインストールします。

```bash
composer require ray/aura-sql-module
```

アプリケーションモジュール`src/Module/AppModule.php`で`AuraSqlModule`をインストールします。

```php
use BEAR\Package\AbstractAppModule;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\AuraSqlModule\AuraSqlModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        $this->install(
            new AuraSqlModule(
                'mysql:host=localhost;dbname=test',  // または getenv('PDO_DSN')
                'username',
                'password'
            )
        );
        $this->install(new PackageModule);
    }
}
```

設定時に直接値を指定するのではなく、実行時に毎回環境変数から取得するためには`AuraSqlEnvModule`を使います。接続先と認証情報の値を直接指定する代わりに、該当する環境変数のキーを渡します。

```php
$this->install(
    new AuraSqlEnvModule(
        'PDO_DSN',      // getenv('PDO_DSN')
        'PDO_USER',     // getenv('PDO_USER')
        'PDO_PASSWORD', // getenv('PDO_PASSWORD')
        'PDO_SLAVE',    // getenv('PDO_SLAVE')
        $options,       // optional key=>value array of driver-specific connection options
        $queries        // Queries to execute after the connection.
    )
);
```

## Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPHPのPDOを拡張したデータベースライブラリです。コンストラクタインジェクションや`AuraSqlInject`トレイトを利用して`PDO`を拡張したDBオブジェクト`ExtendedPDO`を受け取ります。

```php
use Aura\Sql\ExtendedPdoInterface;

class Index
{
    public function __construct(
        private readonly ExtendedPdoInterface $pdo
    ) {}
}
```

```php
use Ray\AuraSqlModule\AuraSqlInject;

class Index
{
    use AuraSqlInject;
    
    public function onGet()
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```

`Ray.AuraSqlModule`は[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)を含んでいてMySQLやPostgresなどのSQLを組み立てるのに利用できます。

### perform() メソッド

`perform()`メソッドは、1つのプレイスホルダーしかないSQLに配列の値をバインドすることができます。

```php
$stm = 'SELECT * FROM test WHERE foo IN (:foo)';
$array = ['foo', 'bar', 'baz'];
```

既存のPDOの場合：

```php
// the native PDO way does not work (PHP Notice: Array to string conversion)
// ネイティブのPDOでは`:foo`に配列を指定することはできません
$sth = $pdo->prepare($stm);
$sth->bindValue('foo', $array);
```

Aura.SqlのExtendedPDOの場合：

```php
$stm = 'SELECT * FROM test WHERE foo IN (:foo)';
$values = ['foo' => ['foo', 'bar', 'baz']];
$sth = $pdo->perform($stm, $values);
```

`:foo`に`['foo', 'bar', 'baz']`がバインドされます。`queryString`で実際のクエリーを調べることができます。

```php
echo $sth->queryString;
// the query string has been modified by ExtendedPdo to become
// "SELECT * FROM test WHERE foo IN ('foo', 'bar', 'baz')"
```

### fetch*() メソッド

`prepare()`、`bindValue()`、`execute()`を繰り返してデータベースから値を取得する代わりに`fetch*()`メソッドを使うとボイラープレートコードを減らすことができます。（内部では`perform()`メソッドを実行しているので配列のプレイスホルダーもサポートしています）

```php
$stm = 'SELECT * FROM test WHERE foo = :foo AND bar = :bar';
$bind = ['foo' => 'baz', 'bar' => 'dib'];

// ネイティブのPDOで"fetch all"を行う場合
$pdo = new PDO(...);
$sth = $pdo->prepare($stm);
$sth->execute($bind);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

// ExtendedPdoで"fetch all"を行う場合
$pdo = new ExtendedPdo(...);
$result = $pdo->fetchAll($stm, $bind);

// fetchAssoc()は全ての行がコラム名のキーを持つ連想配列が返ります。
$result = $pdo->fetchAssoc($stm, $bind);

// fetchGroup()はfetchAssoc()のような動作ですが、値は配列にラップされません。
// 代わりに、単一カラムの値は1次元配列として、
// 複数カラムは配列の配列として返されます。
// 値が配列の場合（つまり、SELECTに2つ以上のカラムがある場合）は、
// スタイルをPDO::FETCH_NAMEDに設定します。
$result = $pdo->fetchGroup($stm, $bind, $style = PDO::FETCH_COLUMN);

// fetchOne()は最初の行をキーをコラム名にした連想配列で返します。
$result = $pdo->fetchOne($stm, $bind);

// fetchPairs()は最初の列の値をキーに二番目の列の値を値にした連想配列を返します  
$result = $pdo->fetchPairs($stm, $bind);

// fetchValue()は最初の列の値を返します。
$result = $pdo->fetchValue($stm, $bind);

// fetchAffected()は影響を受けた行数を返します。
$stm = "UPDATE test SET incr = incr + 1 WHERE foo = :foo AND bar = :bar";
$row_count = $pdo->fetchAffected($stm, $bind);
```

`fetchAll()`、`fetchAssoc()`、`fetchCol()`、および`fetchPairs()`のメソッドは、三番目のオプションの引数にそれぞれの列に適用されるコールバックを指定することができます。

```php
$result = $pdo->fetchAssoc($stm, $bind, function (&$row) {
    // 行にカラムを追加
    $row['my_new_col'] = 'Added this column from the callable.';
});
```

### yield*() メソッド

メモリを節約するために`yield*()`メソッドを使うことができます。`fetch*()`メソッドは全ての行を一度に取得しますが、`yield*()`メソッドはイテレーターを返します。

```php
$stm = 'SELECT * FROM test WHERE foo = :foo AND bar = :bar';
$bind = ['foo' => 'baz', 'bar' => 'dib'];

// fetchAll()のように行は連想配列です
foreach ($pdo->yieldAll($stm, $bind) as $row) {
    // ...
}

// fetchAssoc()のようにキーが最初の列名で行が連想配列です。
foreach ($pdo->yieldAssoc($stm, $bind) as $key => $row) {
    // ...
}

// fetchCol()のように最初の列が値になった値を返します。
foreach ($pdo->yieldCol($stm, $bind) as $val) {
    // ...
}

// fetchPairs()と同様に最初の列からキー/バリューのペアの値を返します。
foreach ($pdo->yieldPairs($stm, $bind) as $key => $val) {
    // ...
}
```

## リプリケーション

マスター／スレーブ構成のデータベース接続を行うためには4つ目の引数にスレーブDBのホストを指定します。

```php
$this->install(
    new AuraSqlModule(
        'mysql:host=localhost;dbname=test',
        'username',
        'password',
        'slave1,slave2' // スレーブのホストをカンマ区切りで指定
    )
);
```

これでHTTPリクエストがGETの時はスレーブDB、その他のメソッドの時はマスターDBのDBオブジェクトがコンストラクタに渡されます。

```php
use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;
use PDO;

class User extends ResourceObject
{
    public $pdo;
    
    public function __construct(ExtendedPdoInterface $pdo)
    {
        $this->pdo = $pdo;
    }
    
    public function onGet()
    {
        $this->pdo; // slave db
    }
    
    public function onPost($todo)
    {
        $this->pdo; // master db
    }
}
```

`#[ReadOnlyConnection]`、`#[WriteConnection]`でアノテートされたメソッドはメソッド名に関わらず、呼ばれた時にアノテーションに応じたDBオブジェクトが`$this->pdo`に上書きされます。

```php
use Ray\AuraSqlModule\Annotation\ReadOnlyConnection;  // important
use Ray\AuraSqlModule\Annotation\WriteConnection;     // important

class User
{
    public $pdo; // #[ReadOnlyConnection]や#[WriteConnection]のメソッドが呼ばれた時に上書きされる
    
    public function onPost($todo)
    {
        $this->read();
    }
    
    #[ReadOnlyConnection]
    public function read()
    {
        $this->pdo; // slave db
    }
    
    #[WriteConnection]
    public function write()
    {
        $this->pdo; // master db
    }
}
```

## 複数データベースの接続

接続先の異なるデータベースのPDOインスタンスをインジェクトするには識別子[^qualifier]をつけます。

```php
public function __construct(
    private readonly #[Log] ExtendedPdoInterface $logDb,
    private readonly #[Mail] ExtendedPdoInterface $mailDb,
) {}
```

[^qualifier]: 識別子（クオリファイアー）についてはRay.Diのマニュアルの[束縛アトリビュート](https://ray-di.github.io/manuals/1.0/ja/binding_attributes.html)をご覧ください。

`NamedPdoModule`でその識別子と接続情報を指定してインストールします。

```php
class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new NamedPdoModule(Log::class, 'mysql:host=localhost;dbname=log', 'username'));
        $this->install(new NamedPdoModule(Mail::class, 'mysql:host=localhost;dbname=mail', 'username'));
    }
}
```

接続情報を環境変数から都度取得するときは`NamedPdoEnvModule`を使います。

```php
class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new NamedPdoEnvModule(Log::class, 'LOG_DSN', 'LOG_USERNAME'));
        $this->install(new NamedPdoEnvModule(Mail::class, 'MAIL_DSN', 'MAIL_USERNAME'));
    }
}
```

## トランザクション

`#[Transactional]`アトリビュートを追加したメソッドはトランザクション管理されます。

```php
use Ray\AuraSqlModule\Annotation\Transactional;

#[Transactional]
public function write()
{
    // 例外発生したら\Ray\AuraSqlModule\Exception\RollbackExceptionになります
}
```

複数接続したデータベースのトランザクションを行うためには`#[Transactional]`アトリビュートにプロパティを指定します。指定しない場合は`{"pdo"}`になります。

```php
#[Transactional({"pdo", "userDb"})]
public function write()
{
    // ...
}
```

以下のように実行されます。

```php
$this->pdo->beginTransaction();
$this->userDb->beginTransaction();
// ...
$this->pdo->commit();
$this->userDb->commit();
```

## Aura.SqlQuery

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPDOを拡張したライブラリですが、[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)はMySQL、Postgres、SQLiteあるいはMicrosoft SQL Serverといったデータベース固有のSQLのビルダーを提供します。

データベースを指定してアプリケーションモジュール`src/Module/AppModule.php`でインストールします。

```php
// ...
$this->install(new AuraSqlQueryModule('mysql')); // pgsql, sqlite, or sqlsrv
```

### SELECT

リソースではDBクエリービルダオブジェクトを受け取り、下記のメソッドを使ってSELECTクエリーを組み立てます。メソッドに特定の順番はなく複数回呼ぶこともできます。

```php
use Aura\Sql\ExtendedPdoInterface;
use Aura\SqlQuery\Common\SelectInterface;

class User extends ResourceObject
{
    public function __construct(
        private readonly ExtendedPdoInterface $pdo,
        private readonly SelectInterface $select
    ) {}

    public function onGet()
    {
        $this->select
            ->distinct()                    // SELECT DISTINCT
            ->cols([                        // select these columns
                'id',                       // column name
                'name AS namecol',          // one way of aliasing
                'col_name' => 'col_alias',  // another way of aliasing
                'COUNT(foo) AS foo_count'   // embed calculations directly
            ])
            ->from('foo AS f')              // FROM these tables
            ->fromSubselect(                // FROM sub-select AS my_sub
                'SELECT ...',
                'my_sub'
            )
            ->join(                         // JOIN ...
                'LEFT',                     // left/inner/natural/etc
                'doom AS d'                 // this table name
                'foo.id = d.foo_id'         // ON these conditions
            )
            ->joinSubSelect(                // JOIN to a sub-select
                'INNER',                    // left/inner/natural/etc
                'SELECT ...',               // the subselect to join on
                'subjoin'                   // AS this name
                'sub.id = foo.id'           // ON these conditions
            )
            ->where('bar > :bar')           // AND WHERE these conditions
            ->where('zim = ?', 'zim_val')   // bind 'zim_val' to the ? placeholder
            ->orWhere('baz < :baz')         // OR WHERE these conditions
            ->groupBy(['dib'])              // GROUP BY these columns
            ->having('foo = :foo')          // AND HAVING these conditions
            ->having('bar > ?', 'bar_val')  // bind 'bar_val' to the ? placeholder
            ->orHaving('baz < :baz')        // OR HAVING these conditions
            ->orderBy(['baz'])              // ORDER BY these columns
            ->limit(10)                     // LIMIT 10
            ->offset(40)                    // OFFSET 40
            ->forUpdate()                   // FOR UPDATE
            ->union()                       // UNION with a followup SELECT
            ->unionAll()                    // UNION ALL with a followup SELECT
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to named placeholders
                'bar' => 'bar_val',
                'baz' => 'baz_val',
            ]);

        $sth = $this->pdo->prepare($this->select->getStatement());
        
        // bind the values and execute
        $sth->execute($this->select->getBindValues());
        
        $result = $sth->fetch(\PDO::FETCH_ASSOC);
        
        // または
        // $result = $this->pdo->fetchAssoc($stm, $bind);
    }
}
```

組み立てたクエリーは`getStatement()`で文字列にしてクエリーを行います。

### INSERT

#### 単一行のINSERT

```php
class User extends ResourceObject
{
    public function __construct(
        private readonly ExtendedPdoInterface $pdo,
        private readonly InsertInterface $insert
    ) {}

    public function onPost()
    {
        $this->insert
            ->into('foo')                   // INTO this table
            ->cols([                        // bind values as "(col) VALUES (:col)"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')           // raw value as "(ts) VALUES (NOW())"
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values
                'bar' => 'foo',
                'baz' => 'zim',
            ]);

        $sth = $this->pdo->prepare($this->insert->getStatement());
        $sth->execute($this->insert->getBindValues());
        
        // または
        // $sth = $this->pdo->perform($this->insert->getStatement(), $this->insert->getBindValues());
        
        // get the last insert ID
        $name = $this->insert->getLastInsertIdName('id');
        $id = $this->pdo->lastInsertId($name);
    }
}
```

`cols()`メソッドはキーがコラム名、値をバインドする値にした連想配列を渡すこともできます。

```php
$this->insert
    ->into('foo')                   // insert into this table
    ->cols([                        // insert these columns and bind these values
        'foo' => 'foo_value',
        'bar' => 'bar_value',
        'baz' => 'baz_value',
    ]);
```

#### 複数行のINSERT

複数の行のINSERTを行うためには、最初の行の最後で`addRow()`メソッドを使います。その後に次のクエリーを組み立てます。

```php
// テーブルの指定
$this->insert->into('foo');

// 1行目のセットアップ
$this->insert->cols([
    'bar' => 'bar-0',
    'baz' => 'baz-0'
]);
$this->insert->set('ts', 'NOW()');

// 2行目のセットアップ
// ここでのカラムの順序は1行目と異なりますが、問題ありません。
// INSERTオブジェクトが最初の行と同じ順序で構築します。
$this->insert->addRow();
$this->insert->set('ts', 'NOW()');
$this->insert->cols([
    'bar' => 'bar-1',
    'baz' => 'baz-1'
]);

// さらに行を追加...
$this->insert->addRow();
// ...

// 全ての行を一度にインサート
$sth = $this->pdo->prepare($insert->getStatement());
$sth->execute($insert->getBindValues());
```

> 注: 最初の行で初めて現れた列の値を指定しないで行を追加しようとすると例外が投げられます。
> `addRow()`に列の連想配列を渡すと次の行で使われます。つまり最初の行で`col()`や`cols()`を指定しないこともできます。

```php
// 1行目のセットアップ
$insert->addRow([
    'bar' => 'bar-0',
    'baz' => 'baz-0'
]);
$insert->set('ts', 'NOW()');

// 2行目のセットアップ
$insert->addRow([
    'bar' => 'bar-1',
    'baz' => 'baz-1'
]);
$insert->set('ts', 'NOW()');
// など
```

`addRows()`を使って複数の行を一度にセットすることもできます。

```php
$rows = [
    [
        'bar' => 'bar-0',
        'baz' => 'baz-0'
    ],
    [
        'bar' => 'bar-1',
        'baz' => 'baz-1'
    ],
];
$this->insert->addRows($rows);
```

### UPDATE

下記のメソッドを使ってUPDATEクエリーを組み立てます。メソッドに特定の順番はなく複数回呼ぶこともできます。

```php
$this->update
    ->table('foo')                  // update this table
    ->cols([                        // bind values as "SET bar = :bar"
        'bar',
        'baz',
    ])
    ->set('ts', 'NOW()')           // raw value as "SET ts = NOW()"
    ->where('zim = :zim')          // AND WHERE these conditions
    ->where('gir = ?', 'doom')     // bind this value to the condition
    ->orWhere('gir = :gir')        // OR WHERE these conditions
    ->bindValue('bar', 'bar_val')  // bind one value to a placeholder
    ->bindValues([                 // bind these values to the query
        'baz' => 99,
        'zim' => 'dib',
        'gir' => 'doom',
    ]);

$sth = $this->pdo->prepare($this->update->getStatement());
$sth->execute($this->update->getBindValues());

// または
// $sth = $this->pdo->perform($this->update->getStatement(), $this->update->getBindValues());
```

キーを列名、値をバインドされた値（RAW値ではありません）にした連想配列を`cols()`に渡すこともできます。

```php
$this->update
    ->table('foo')          // update this table
    ->cols([                // update these columns and bind these values
        'foo' => 'foo_value',
        'bar' => 'bar_value',
        'baz' => 'baz_value',
    ]);
```

### DELETE

下記のメソッドを使ってDELETEクエリーを組み立てます。メソッドに特定の順番はなく複数回呼ぶこともできます。

```php
$this->delete
    ->from('foo')                   // FROM this table
    ->where('zim = :zim')          // AND WHERE these conditions
    ->where('gir = ?', 'doom')     // bind this value to the condition
    ->orWhere('gir = :gir')        // OR WHERE these conditions
    ->bindValue('bar', 'bar_val')  // bind one value to a placeholder
    ->bindValues([                 // bind these values to the query
        'baz' => 99,
        'zim' => 'dib',
        'gir' => 'doom',
    ]);

$sth = $this->pdo->prepare($update->getStatement());
$sth->execute($this->delete->getBindValues());
```

### パジネーション

[ray/aura-sql-module](https://packagist.org/packages/ray/aura-sql-module)はRay.Sqlの生SQL、Ray.AuraSqlQueryのクエリービルダー双方でパジネーション（ページ分割）をサポートしています。

バインドする値と1ページあたりのアイテム数、それに`{page}`をページ番号にしたuri_templateでページャーファクトリーを`newInstance()`で生成して、ページ番号で配列アクセスします。

#### Aura.Sql用AuraSqlPagerFactoryInterface

```php
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlPagerFactoryInterface */
$pager = $factory->newInstance(
    $pdo,
    $sql,
    $params,
    10,                                     // 10 items per page
    '/?page={page}&category=sports'
);
$page = $pager[2];                          // page 2

/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
// $page->data             // sliced data (array|\Traversable)
// $page->current;         // (int)
// $page->total           // (int)
// $page->hasNext         // (bool)
// $page->hasPrevious     // (bool)
// $page->maxPerPage;     // (int)
// (string) $page         // pager html (string)
```

#### Aura.SqlQuery用AuraSqlQueryPagerFactoryInterface

```php
// for Select
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlQueryPagerFactoryInterface */
$pager = $factory->newInstance(
    $pdo,
    $select,
    10,
    '/?page={page}&category=sports'
);
$page = $pager[2]; // page 2

/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
```

> 注：Aura.Sqlは生SQLを直接編集していますが、現在MySql形式のLIMIT句しか対応していません。

`$page`はイテレータブルです。

```php
foreach ($page as $row) {
    // 各行の処理
}
```

ページャーのリンクHTMLのテンプレートを変更するには`TemplateInterface`の束縛を変更します。テンプレート詳細に関しては[Pagerfanta](https://github.com/whiteoctober/Pagerfanta#views)をご覧ください。

```php
use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap3Template;
use Ray\AuraSqlModule\Annotation\PagerViewOption;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->bind(TemplateInterface::class)->to(TwitterBootstrap3Template::class);
        $this->bind()->annotatedWith(PagerViewOption::class)->toInstance($pagerViewOption);
    }
}
```



# CakeDb

**CakeDb**はアクティブレコードとデータマッパーパターンのアイデアを使ったORMで、素早くシンプルにORMを使うことができます。CakePHP3で提供されているORMと同じものです。

composerで`Ray.CakeDbModule`をインストールします。

```bash
composer require ray/cake-database-module ~1.0
```

インストールの方法については[Ray.CakeDbModule](https://github.com/ray-di/Ray.CakeDbModule)を、ORMの利用には[CakePHP3 Database Access & ORM](http://book.cakephp.org/3.0/en/orm.html)をご覧ください。

Ray.CakeDbModuleはCakePHP3のORMを開発したJose([@lorenzo](https://github.com/lorenzo))さんにより提供されています。



# Doctrine DBAL

[Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html)はDoctrineが提供しているデータベースの抽象化レイヤーです。

composerで`Ray.DbalModule`をインストールします。

```bash
composer require ray/dbal-module
```

アプリケーションモジュールで`DbalModule`をインストールします。

```php
use Ray\DbalModule\DbalModule;
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new DbalModule('driver=pdo_sqlite&memory=true'));
    }
}
```

これでDIの設定が整いました。`DbalInject`トレイトを利用すると`$this->db`にDBオブジェクトがインジェクトされます。

```php
use Ray\DbalModule\DbalInject;

class Index
{
    use DbalInject;
    
    public function onGet()
    {
        return $this->db; // \Doctrine\DBAL\Driver\Connection
    }
}
```

### 複数DB

複数のデータベースの接続には二番目の引数に識別子を指定します。

```php
$this->install(new DbalModule($logDsn, 'log_db'));
$this->install(new DbalModule($jobDsn, 'job_db'));
```

```php
/**
 * @Inject
 * @Named("log_db")
 */
public function setLogDb(Connection $logDb)
```

[MasterSlaveConnection](http://www.doctrine-project.org/api/dbal/2.0/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html)というリプリケーションのためのマスター／スレーブ接続が標準で用意されています。



# Ray.MediaQuery

`Ray.MediaQuery`はDBやWeb APIなどの外部メディアのクエリーのインターフェイスから、クエリー実行オブジェクトを生成しインジェクトします。

* ドメイン層とインフラ層の境界を明確にします。
* ボイラープレートコードを削減します。
* 外部メディアの実体には無関係なので、後からストレージを変更することができます。並列開発やスタブ作成が容易です。

## インストール

```bash
$ composer require ray/media-query
```

## 利用方法

メディアアクセスするインターフェイスを定義します。

### データベースの場合

`DbQuery`アトリビュートでSQLのIDを指定します。

```php
interface TodoAddInterface
{
    #[DbQuery('user_add')]
    public function add(string $id, string $title): void;
}
```

### Web APIの場合

`WebQuery`アトリビュートでWeb APIのIDを指定します。

```php
interface PostItemInterface
{
    #[WebQuery('user_item')]
    public function get(string $id): array;
}
```

APIパスリストのファイルを`media_query.json`として作成します。

```json
{
    "$schema": "https://ray-di.github.io/Ray.MediaQuery/schema/web_query.json",
    "webQuery": [
        {
            "id": "user_item",
            "method": "GET",
            "path": "https://{domain}/users/{id}"
        }
    ]
}
```

MediaQueryModuleは、`DbQueryConfig`や`WebQueryConfig`、またはその両方の設定でSQLやWeb APIリクエストの実行をインターフェイスに束縛します。

```php
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\MediaQuery\ApiDomainModule;
use Ray\MediaQuery\DbQueryConfig;
use Ray\MediaQuery\MediaQueryModule;
use Ray\MediaQuery\Queries;
use Ray\MediaQuery\WebQueryConfig;

protected function configure(): void
{
    $this->install(
        new MediaQueryModule(
            Queries::fromDir('/path/to/queryInterface'),
            [
                new DbQueryConfig('/path/to/sql'),
                new WebQueryConfig('/path/to/web_query.json', ['domain' => 'api.example.com'])
            ],
        ),
    );
    $this->install(new AuraSqlModule(
        'mysql:host=localhost;dbname=test',
        'username',
        'password'
    ));
}
```

MediaQueryModuleはAuraSqlModuleのインストールが必要です。

### 注入

インターフェイスからオブジェクトが直接生成され、インジェクトされます。実装クラスのコーディングが不要です。

```php
class Todo
{
    public function __construct(
        private TodoAddInterface $todoAdd
    ) {}

    public function add(string $id, string $title): void
    {
        $this->todoAdd->add($id, $title);
    }
}
```

### DbQuery

SQL実行がメソッドにマップされ、IDで指定されたSQLをメソッドの引数でバインドして実行します。例えばIDが`todo_item`の指定では`todo_item.sql`SQL文に`['id => $id]`をバインドして実行します。

* `$sqlDir`ディレクトリにSQLファイルを用意します。
* SQLファイルには複数のSQL文が記述できます。最後の行のSELECTが返り値になります。

#### Entity

SQL実行結果を用意したエンティティクラスを`entity`で指定して変換（hydrate）することができます。

```php
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class)]
    public function getItem(string $id): Todo;
}
```

```php
final class Todo
{
    public string $id;
    public string $title;
}
```

プロパティをキャメルケースに変換する場合には`CamelCaseTrait`を使います。

```php
use Ray\MediaQuery\CamelCaseTrait;

class Invoice
{
    use CamelCaseTrait;
    public $userName;
}
```

コンストラクタがあると、フェッチしたデータでコールされます。

```php
final class Todo
{
    public function __construct(
        public string $id,
        public string $title
    ) {}
}
```

#### type: 'row'

SQL実行の戻り値が単一行なら`type: 'row'`のアトリビュートを指定します。ただし、インターフェイスの戻り値がエンティティクラスなら省略することができます。

```php
/** 返り値がEntityの場合 */
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class)]
    public function getItem(string $id): Todo;
}
```

```php
/** 返り値がarrayの場合 */
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class, type: 'row')]
    public function getItem(string $id): array;
}
```

### Web API

* メソッドの引数が `uri`で指定されたURI templateにバインドされ、Web APIリクエストオブジェクトが生成されます。
* 認証のためのヘッダーなどのカスタムはGuzzleの`ClinetInterface`をバインドして行います。

```php
$this->bind(ClientInterface::class)->toProvider(YourGuzzleClientProvider::class);
```

## パラメーター

### 日付時刻

パラメーターにバリューオブジェクトを渡すことができます。例えば、`DateTimeInterface`オブジェクトをこのように指定できます。

```php
interface TaskAddInterface
{
    #[DbQuery('task_add')]
    public function __invoke(string $title, DateTimeInterface $createdAt = null): void;
}
```

値はSQL実行時やWeb APIリクエスト時に日付フォーマットされた文字列に変換されます。

```sql
INSERT INTO task (title, created_at) VALUES (:title, :createdAt); # 2021-2-14 00:00:00
```

値を渡さないとバインドされている現在時刻がインジェクションされます。SQL内部で`NOW()`とハードコーディングする事や、毎回現在時刻を渡す手間を省きます。

### テスト時刻

テストの時には以下のように`DateTimeInterface`の束縛を1つの時刻にすることもできます。

```php
$this->bind(DateTimeInterface::class)->to(UnixEpochTime::class);
```

### バリューオブジェクト（VO）

`DateTime`以外のバリューオブジェクトが渡されると`ToScalarInterface`を実装した`toScalar()`メソッド、もしくは`__toString()`メソッドの返り値が引数になります。

```php
interface MemoAddInterface
{
    public function __invoke(string $memo, UserId $userId = null): void;
}
```

```php
class UserId implements ToScalarInterface
{
    public function __construct(
        private readonly LoginUser $user
    ) {}
    
    public function toScalar(): int
    {
        return $this->user->id;
    }
}
```

```sql
INSERT INTO memo (user_id, memo) VALUES (:user_id, :memo);
```

### パラメーターインジェクション

バリューオブジェクトの引数のデフォルトの値の`null`がSQLやWebリクエストで使われることはないことに注意してください。値が渡されないと、nullの代わりにパラメーターの型でインジェクトされたバリューオブジェクトのスカラー値が使われます。

```php
public function __invoke(Uuid $uuid = null): void; // UUIDが生成され渡される
```

## ページネーション

DBの場合、`#[Pager]`アトリビュートでSELECTクエリーをページングすることができます。

```php
use Ray\MediaQuery\PagesInterface;

interface TodoList
{
    #[DbQuery, Pager(perPage: 10, template: '/{?page}')]
    public function __invoke(): PagesInterface;
}
```

`count()`で件数が取得でき、ページ番号で配列アクセスをするとページオブジェクトが取得できます。`Pages`はSQL遅延実行オブジェクトです。

```php
$pages = ($todoList)();
$cnt = count($pages);    // count()をした時にカウントSQLが生成されクエリーが行われます。
$page = $pages[2];       // 配列アクセスをした時にそのページのDBクエリーが行われます。

// $page->data           // sliced data
// $page->current;       // 現在のページ番号
// $page->total          // 総件数
// $page->hasNext        // 次ページの有無
// $page->hasPrevious    // 前ページの有無
// $page->maxPerPage;    // 1ページあたりの最大件数
// (string) $page        // ページャーHTML
```

## SqlQuery

`SqlQuery`はSQLファイルのIDを指定してSQLを実行します。実装クラスを用意して詳細な実装を行う時に使用します。

```php
class TodoItem implements TodoItemInterface
{
    public function __construct(
        private SqlQueryInterface $sqlQuery
    ) {}

    public function __invoke(string $id): array
    {
        return $this->sqlQuery->getRow('todo_item', ['id' => $id]);
    }
}
```

## get* メソッド

SELECT結果を取得するためには取得する結果に応じた`get*`を使います。

```php
$sqlQuery->getRow($queryId, $params);        // 結果が単数行
$sqlQuery->getRowList($queryId, $params);    // 結果が複数行
$statement = $sqlQuery->getStatement();       // PDO Statementを取得
$pages = $sqlQuery->getPages();              // ページャーを取得
```

Ray.MediaQueryは[Ray.AuraSqlModule](https://github.com/ray-di/Ray.AuraSqlModule)を含んでいます。さらに低レイヤーの操作が必要な時はAura.Sqlの[Query Builder](https://github.com/ray-di/Ray.AuraSqlModule#query-builder)やPDOを拡張した[Aura.Sql](https://github.com/auraphp/Aura.Sql)のExtended PDOをお使いください。[doctrine/dbal](https://github.com/ray-di/Ray.DbalModule)も利用できます。

パラメーターインジェクションと同様、`DateTimeInterface`オブジェクトを渡すと日付フォーマットされた文字列に変換されます。

```php
$sqlQuery->exec('memo_add', [
    'memo' => 'run',
    'created_at' => new DateTime()
]);
```

他のオブジェクトが渡されると`toScalar()`または`__toString()`の値に変換されます。

## プロファイラー

メディアアクセスはロガーで記録されます。標準ではテストに使うメモリロガーがバインドされています。

```php
public function testAdd(): void
{
    $this->sqlQuery->exec('todo_add', $todoRun);
    $this->assertStringContainsString(
        'query: todo_add({"id":"1","title":"run"})',
        (string) $this->log
    );
}
```

独自の[MediaQueryLoggerInterface](src/MediaQueryLoggerInterface.php)を実装して、各メディアクエリーのベンチマークを行ったり、インジェクトしたPSRロガーでログをすることもできます。

## アノテーション / アトリビュート

属性を表すのに[doctrineアノテーション](https://github.com/doctrine/annotations/)、[アトリビュート](https://www.php.net/manual/ja/language.attributes.overview.php)どちらも利用できます。次の2つは同じものです。

```php
use Ray\MediaQuery\Annotation\DbQuery;

#[DbQuery('user_add')]
public function add1(string $id, string $title): void;

/** @DbQuery("user_add") */
public function add2(string $id, string $title): void;
```


# データベース

データベースライブラリの利用のため`Aura.Sql`、`Doctrine DBAL`, `CakeDB`などのモジュールが用意されています。

## Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPHPのPDOを拡張したデータベースライブラリです。

### インストール

composerで`Ray.AuraSqlModule`をインストールします。

```bash
composer require ray/aura-sql-module
```

アプリケーションモジュール`src/Module/AppModule.php`で`AuraSqlModule`をインストールします。

```php?start_inline
use BEAR\Package\AbstractAppModule;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\AuraSqlModule\AuraSqlModule; // この行を追加

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(
          new AuraSqlModule(
            'mysql:host=localhost;dbname=test',
            'username',
            'password',
            // $options,
            // $attributes
          )
        );  // この行を追加
        $this->install(new PackageModule));
    }
}
```

これでDIの設定が整いました。コンストラクタや`AuraSqlInject`トレイトを利用して`PDO`を拡張したDBオブジェクト`ExtendedPDO`を受け取ります。

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;

class Index
{
    public function __construct(ExtendedPdoInterface $pdo)
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;

class Index
{
    use AuraSqlInject;

    public function onGet()
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```

`Ray.AuraSqlModule`は[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)を含んでいてMySQLやPostgresなどのSQLを組み立てるのに利用できます。

### perform() メソッド

`perform()`メソッドは、1つのプレイスホルダーしかないSQLに配列の値をバインドすることが出来ます。

```php?start_inline
$stm = 'SELECT * FROM test WHERE foo IN (:foo)'
$array = ['foo', 'bar', 'baz'];
```

既存のPDOの場合

```php?start_inline
// the native PDO way does not work (PHP Notice:  Array to string conversion)
// ネイティブのPDOでは`:foo`に配列を指定することは出来ません
$sth = $pdo->prepare($stm);
$sth->bindValue('foo', $array);
```

Aura.SqlのExtendedPDOの場合

```php?start_inline
$stm = 'SELECT * FROM test WHERE foo IN (:foo)'
$values = ['foo' => ['foo', 'bar', 'baz']];
$sth = $pdo->perform($stm, $values);
```

`:foo`に`['foo', 'bar', 'baz']`がバインドがされます。`queryString`で実際のクエリーを調べることが出来ます。

```php?start_inline
echo $sth->queryString;
// the query string has been modified by ExtendedPdo to become
// "SELECT * FROM test WHERE foo IN ('foo', 'bar', 'baz')"
```

### fetch*() メソッド

`prepare()`、`bindValue()`、 `execute()`を繰り返してデータベースから値を取得する代わりに`fetch*()`メソッドを使うとボイラープレートコードを減らすことができます。
（内部では`perform()`メソッドを実行しているので配列のプレースフォルもサポートしています）

```php?start_inline
$stm  = 'SELECT * FROM test WHERE foo = :foo AND bar = :bar';
$bind = array('foo' => 'baz', 'bar' => 'dib');
// ネイティブのPDOで"fetch all"を行う場合
$pdo = new PDO(...);
$sth = $pdo->prepare($stm);
$sth->execute($bind);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

// ExtendedPdoで"fetch all"を行う場合
$pdo = new ExtendedPdo(...);
$result = $pdo->fetchAll($stm, $bind);

// fetchAssoc()は全ての行がコラム名のキーを持つ連想配列が返ります。
$result = $pdo->fetchAssoc($stm, $bind);

// fetchGroup() is like fetchAssoc() except that the values aren't wrapped in
// arrays. Instead, single column values are returned as a single dimensional
// array and multiple columns are returned as an array of arrays
// Set style to PDO::FETCH_NAMED when values are an array
// (i.e. there are more than two columns in the select)
$result = $pdo->fetchGroup($stm, $bind, $style = PDO::FETCH_COLUMN)

// fetchOne()は最初の行をキーをコラム名にした連想配列で返します。
$result = $pdo->fetchOne($stm, $bind);

// fetchPairs()は最初の列の値をキーに二番目の列の値を値にした連想配列を返します  
$result = $pdo->fetchPairs($stm, $bind);

// fetchValue()は最初の列の値を返します。
$result = $pdo->fetchValue($stm, $bind);

// fetchAffected()は影響を受けた行数を返します。
$stm = "UPDATE test SET incr = incr + 1 WHERE foo = :foo AND bar = :bar";
$row_count = $pdo->fetchAffected($stm, $bind);
?>
```

`fetchAll()`, `fetchAssoc()`, `fetchCol()`, 及び `fetchPairs()`のメソッドは三番目のオプションの引数に、それぞれの列に適用されるコールバックを指定することができます。

```php?start_inline
$result = $pdo->fetchAssoc($stm, $bind, function (&$row) {
    // add a column to the row
    $row['my_new_col'] = 'Added this column from the callable.';
});
?>
```
### yield*() メソッド

メモリを節約するために`yield*()`メソッドを使うことができます。 `fetch*()`メソッドは全ての行を一度に取得しますが、
`yield*()`メソッドはイテレーターが返ります。

```php
$stm  = 'SELECT * FROM test WHERE foo = :foo AND bar = :bar';
$bind = array('foo' => 'baz', 'bar' => 'dib');

// fetchAll()のように行は連想配列です
foreach ($pdo->yieldAll($stm, $bind) as $row) {
    // ...
}

// fetchAssoc()のようにキーが最初の列名で行が連想配列です。
foreach ($pdo->yieldAssoc($stm, $bind) as $key => $row) {
    // ...
}

// fetchCol()のように最初の列が値になった値を返します。
foreach ($pdo->yieldCol($stm, $bind) as $val) {
    // ...
}

// fetchPairs()と同様に最初の列からキー/バリューのペアの値を返します。
foreach ($pdo->yieldPairs($stm, $bind) as $key => $val) {
    // ...
}
```

### リプリケーション

マスター／スレーブの接続を自動で行うためには4つ目の引数にスレーブDBのIPを指定します。

```php?start_inline
$this->install(
  new AuraSqlModule(
    'mysql:host=localhost;dbname=test',
    'username',
    'password',
    'slave1,slave2' // スレーブIPをカンマ区切りで指定
  )
);
```

これでHTTPリクエストがGETの時がスレーブDB、その他のメソッドの時はマスターDBのDBオブジェクトがコンスタラクタに渡されます。

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;
use PDO;

class User extends ResourceObject
{
    public $pdo;

    public function __construct(ExtendedPdoInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    public function onGet()
    {
         $this->pdo; // slave db
    }

    public function onPost($todo)
    {
         $this->pdo; // master db
    }
}
```

`@ReadOnlyConnection`、`@WriteConnection`でアノテートされたメソッドはメソッド名に関わらず、呼ばれた時にアノテーションに応じたDBオブジェクトが`$this->pdo`に上書きされます。

```php?start_inline
use Ray\AuraSqlModule\Annotation\ReadOnlyConnection;  // important
use Ray\AuraSqlModule\Annotation\WriteConnection;     // important

class User
{
    public $pdo; // @ReadOnlyConnectionや@WriteConnectionのメソッドが呼ばれた時に上書きされる

    public function onPost($todo)
    {
         $this->read();
    }

    /**
     * @ReadOnlyConnection
     */
    public function read()
    {
         $this->pdo; // slave db
    }

    /**
     * @WriteConnection
     */
    public function write()
    {
         $this->pdo; // master db
    }
}
```

### 複数DB

接続先の違う複数の`PdoExtendedInterface`オブジェクトを受け取るためには
`@Named`アノテーションで指定します。

```php?start_inline
/**
 * @Inject
 * @Named("log_db")
 */
public function setLoggerDb(ExtendedPdoInterface $pdo)
{
    // ...
}
```

モジュールでは`NamedPdoModule`で識別子を指定して束縛します。

```php?start_inline
$this->install(
  new NamedPdoModule(
    'log_db', // @Namedで指定するデータベースの種類
    'mysql:host=localhost;dbname=log',
    'username',
    'pass',
    'slave1,slave12'
  )
);
```

### トランザクション

`@Transactional`とアノテートしたメソッドはトランザクション管理されます。

```php?start_inline
use Ray\AuraSqlModule\Annotation\Transactional;

// ....
    /**
     * @Transactional
     */
    public function write()
    {
         // 例外発生したら\Ray\AuraSqlModule\Exception\RollbackExceptionに
    }
```

複数接続したデータベースのトランザクションを行うためには`@Transactional`アノテーションにプロパティを指定します。
指定しない場合は`{"pdo"}`になります。

```php?start_inline
/**
 * @Transactional({"pdo", "userDb"})
 */
public function write()
```

以下のように実行されます。

```php?start_inline
$this->pdo->beginTransaction()
$this->userDb->beginTransaction()

// ...

$this->pdo->commit();
$this->userDb->commit();
```

## Aura.SqlQuery

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPDOを拡張したライブラリですが、[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)は MySQL、Postgres,、SQLiteあるいは Microsoft SQL Serverといったデータベース固有のSQLのビルダーを提供します。

データベースを指定してアプリケーションモジュール`src/Module/AppModule.php`でインストールします。

```php?start_inline
// ...
$this->install(new AuraSqlQueryModule('mysql')); // pgsql, sqlite, or sqlsrv
```

### SELECT

リソースではDBクエリービルダオブジェクトを受け取り、下記のメソッドを使ってSELECTクエリーを組み立てます。
メソッドに特定の順番はなく複数回呼ぶことこともできます。

```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlSelectInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlSelectInject;

    public function onGet()
    {
        $this->select
            ->distinct()                    // SELECT DISTINCT
            ->cols([                        // select these columns
                'id',                       // column name
                'name AS namecol',          // one way of aliasing
                'col_name' => 'col_alias',  // another way of aliasing
                'COUNT(foo) AS foo_count'   // embed calculations directly
            ])
            ->from('foo AS f')              // FROM these tables
            ->fromSubselect(                // FROM sub-select AS my_sub
                'SELECT ...',
                'my_sub'
            )
            ->join(                         // JOIN ...
                'LEFT',                     // left/inner/natural/etc
                'doom AS d'                 // this table name
                'foo.id = d.foo_id'         // ON these conditions
            )
            ->joinSubSelect(                // JOIN to a sub-select
                'INNER',                    // left/inner/natural/etc
                'SELECT ...',               // the subselect to join on
                'subjoin'                   // AS this name
                'sub.id = foo.id'           // ON these conditions
            )
            ->where('bar > :bar')           // AND WHERE these conditions
            ->where('zim = ?', 'zim_val')   // bind 'zim_val' to the ? placeholder
            ->orWhere('baz < :baz')         // OR WHERE these conditions
            ->groupBy(['dib'])              // GROUP BY these columns
            ->having('foo = :foo')          // AND HAVING these conditions
            ->having('bar > ?', 'bar_val')  // bind 'bar_val' to the ? placeholder
            ->orHaving('baz < :baz')        // OR HAVING these conditions
            ->orderBy(['baz'])              // ORDER BY these columns
            ->limit(10)                     // LIMIT 10
            ->offset(40)                    // OFFSET 40
            ->forUpdate()                   // FOR UPDATE
            ->union()                       // UNION with a followup SELECT
            ->unionAll()                    // UNION ALL with a followup SELECT
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to named placeholders
                'bar' => 'bar_val',
                'baz' => 'baz_val',
            ]);

        $sth = $this->pdo->prepare($this->select->getStatement());

        // bind the values and execute
        $sth->execute($this->select->getBindValues());
        $result = $sth->fetch(\PDO::FETCH_ASSOC);
        // or
        // $result = $this->pdo->fetchAssoc($stm, $bind);
```

組み立てたクエリーは`getStatement()`で文字列にしてクエリーを行います。

### INSERT

#### 単一行のINSERT


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlInsertInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlInsertInject;

    public function onPost()
    {
        $this->insert
            ->into('foo')                   // INTO this table
            ->cols([                        // bind values as "(col) VALUES (:col)"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values
                'bar' => 'foo',
                'baz' => 'zim',
            ]);

        $sth = $this->pdo->prepare($this->insert->getStatement());
        $sth->execute($this->insert->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->insert->getStatement(), this->insert->getBindValues());

        // get the last insert ID
        $name = $insert->getLastInsertIdName('id');
        $id = $pdo->lastInsertId($name);
```

`cols()`メソッドはキーがコラム名、値をバインドする値にした連想配列を渡すこともできます。

```php?start_inline
        $this->insert
            ->into('foo')                   // insert into this table
            ->cols([                        // insert these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
```

#### 複数行のINSERT

複数の行のINSERTを行うためには、最初の行の最後で`addRow()`メソッドを使います。その後に次のクエリーを組み立てます。

```php?start_inline
        // insert into this table
        $this->insert->into('foo');

        // set up the first row
        $this->insert->cols([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $this->insert->set('ts', 'NOW()');

        // set up the second row. the columns here are in a different order
        // than in the first row, but it doesn't matter; the INSERT object
        // keeps track and builds them the same order as the first row.
        $this->insert->addRow();
        $this->insert->set('ts', 'NOW()');
        $this->insert->cols([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);

        // set up further rows ...
        $this->insert->addRow();
        // ...

        // execute a bulk insert of all rows
        $sth = $this->pdo->prepare($insert->getStatement());
        $sth->execute($insert->getBindValues());

```

> 注:最初の行で始めて現れた列の値を指定しないで、行を追加しようとすると例外が投げられます。
> `addRow()`に列の連想配列を渡すと次の行で使われます。つまり最初の行で`col()`や`cols()`を指定しないこともできます。

```php?start_inline
        // set up the first row
        $insert->addRow([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $insert->set('ts', 'NOW()');

        // set up the second row
        $insert->addRow([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);
        $insert->set('ts', 'NOW()');

        // etc.
```

`addRows()`を使ってデータベースを一度にセットすることもできます。

```php?start_inline
        $rows = [
            [
                'bar' => 'bar-0',
                'baz' => 'baz-0'
            ],
            [
                'bar' => 'bar-1',
                'baz' => 'baz-1'
            ],
        ];
        $this->insert->addRows($rows);
```

### UPDATE
下記のメソッドを使ってUPDATEクエリーを組み立てます。 メソッドに特定の順番はなく複数回呼ぶことこともできます。

```php?start_inline
        $this->update
            ->table('foo')                  // update this table
            ->cols([                        // bind values as "SET bar = :bar"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->update->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->update->getStatement(), $this->update->getBindValues());
```

キーを列名、値をバインドされた値（RAW値ではなりません）にした連想配列を`cols()`に渡すこともできます。

```php?start_inline

        $this-update->table('foo')          // update this table
            ->cols([                        // update these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
?>
```

### DELETE
下記のメソッドを使ってDELETEクエリーを組み立てます。 メソッドに特定の順番はなく複数回呼ぶことこともできます。
```php?start_inline
        $this->delete
            ->from('foo')                   // FROM this table
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->delete->getBindValues());
```

### パジネーション

[ray/aura-sql-module](https://packagist.org/packages/ray/aura-sql-module)はRay.Sqlの生SQL、Ray.AuraSqlQueryのクエリービルダー双方でパジネーション（ページ分割）をサポートしています。
バインドする値と１ページあたりのアイテム数、それに{page}をページ番号にしたuri_templateでページャーファクトリーを`newInstance()`で生成して、ページ番号で配列アクセスします。

#### Aura.Sql用
AuraSqlPagerFactoryInterface

```php?start_inline
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $sql, $params, 10, '/?page={page}&category=sports'); // 10 items per page
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
// $page->data // sliced data (array|\Traversable)
// $page->current; (int)
// $page->total (int)
// $page->hasNext (bool)
// $page->hasPrevious (bool)
// $page->maxPerPage; (int)
// (string) $page // pager html (string)
```

#### Aura.SqlQuery用
AuraSqlQueryPagerFactoryInterface

```php?start_inline
// for Select
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlQueryPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $select, 10, '/?page={page}&category=sports');
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
```
> 注：Aura.Sqlは生SQLを直接編集していますが現在MySql形式のLIMIT句しか対応していません。

`$page`はイテレータブルです。

```php?start_inline
foreach ($page as $row) {
 // 各行の処理
}
```
ページャーのリンクHTMLのテンプレートを変更するには`TemplateInterface`の束縛を変更します。
テンプレート詳細に関しては[Pagerfanta](https://github.com/whiteoctober/Pagerfanta#views)をご覧ください。

```php?start_inline
use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap3Template;
use Ray\AuraSqlModule\Annotation\PagerViewOption;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ..
        $this->bind(TemplateInterface::class)->to(TwitterBootstrap3Template::class);
        $this->bind()->annotatedWith(PagerViewOption::class)->toInstance($pagerViewOption);
    }
}
```




# バリデーション

* BEAR.SundayのバリデーションはJSONスキーマで行います。
* Webフォームによるバリデーションは[フォーム](form.html)をご覧ください。

## JSONスキーマによるバリデーション

### 概要

[JSON Schema](http://json-schema.org/)を使用して、リソースAPIの入出力仕様を定義し検証することができます。
これにより、APIの仕様を人間とマシンの両方が理解できる形式で管理できます。またApiDocとしてAPIドキュメントを出力することもできます。

### セットアップ

#### モジュールの設定

バリデーションの適用範囲に応じて、以下のいずれかの方法で設定します：

- すべての環境でバリデーションを行う場合：`AppModule`に設定
- 開発環境のみでバリデーションを行う場合：`DevModule`に設定

```php
use BEAR\Resource\Module\JsonSchemaModule;
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        $this->install(
            new JsonSchemaModule(
                $appDir . '/var/json_schema',  // スキーマ定義用
                $appDir . '/var/json_validate' // バリデーション用
            )
        );
    }
}
```

#### 2. 必要なディレクトリの作成

```bash
mkdir -p var/json_schema
mkdir -p var/json_validate
```

### 基本的な使用方法

#### 1. リソースクラスの定義

```php
use BEAR\Resource\Annotation\JsonSchema;

class User extends ResourceObject
{
    #[JsonSchema('user.json')]
    public function onGet(): static
    {
        $this->body = [
            'firstName' => 'mucha',
            'lastName' => 'alfons',
            'age' => 12
        ];
        return $this;
    }
}
```

#### 2. JSONスキーマの定義

`var/json_schema/user.json`:

```json
{
    "type": "object",
    "properties": {
        "firstName": {
            "type": "string",
            "maxLength": 30,
            "pattern": "[a-z\\d~+-]+"
        },
        "lastName": {
            "type": "string",
            "maxLength": 30,
            "pattern": "[a-z\\d~+-]+"
        }
    },
    "required": ["firstName", "lastName"]
}
```

### 高度な使用方法

#### インデックスキーの指定

レスポンスボディにインデックスキーがある場合、`key`パラメータで指定します：

```php
class User extends ResourceObject
{
    #[JsonSchema(key: 'user', schema: 'user.json')]
    public function onGet(): static
    {
        $this->body = [
            'user' => [
                'firstName' => 'mucha',
                'lastName' => 'alfons',
                'age' => 12
            ]
        ];
        
        return $this;
    }
}
```

#### 引数のバリデーション

メソッドの引数をバリデーションする場合、`params`パラメータでスキーマを指定します：

```php
class Todo extends ResourceObject
{
    #[JsonSchema(
        key: 'user',
        schema: 'user.json',
        params: 'todo.post.json'
    )]
    public function onPost(string $title)
    {
        // メソッドの処理
    }
}
```

`var/json_validate/todo.post.json`:
```json
{
    "$schema": "http://json-schema.org/draft-04/schema#",
    "title": "/todo POST request validation",
    "properties": {
        "title": {
            "type": "string",
            "minLength": 1,
            "maxLength": 40
        }
    }
}
```

### target

ResourceObjectのbodyに対してでなく、リソースオブジェクトの表現（レンダリングされた結果）に対してスキーマバリデーションを適用にするには`target='view'`オプションを指定します。
HALフォーマットで`_link`のスキーマが記述できます。

```php
#[JsonSchema(schema: 'user.json', target: 'view')]
```


### スキーマ作成支援ツール

JSONスキーマの作成には以下のツールが便利です：

- [JSON Schema Generator](https://jsonschema.net/#/editor)
- [Understanding JSON Schema](https://spacetelescope.github.io/understanding-json-schema/)


これはBEAR.Sundayの全てのマニュアルページを一つにまとめたページです。



# BEAR.Sundayとは

BEAR.Sundayは、クリーンなオブジェクト指向設計と、Webの基本原則に沿ったリソース指向アーキテクチャを組み合わせたPHPのアプリケーションフレームワークです。
このフレームワークは標準への準拠、長期的な視点、高効率性、柔軟性、自己記述性に加え、シンプルさを重視します。

## フレームワーク

BEAR.Sundayは3つのフレームワークで構成されています。

`Ray.Di`は[依存性逆転の原則](http://en.wikipedia.org/wiki/Dependency_inversion_principle)に基づいてオブジェクトの依存をインターフェイスで結びます。

`Ray.Aop`は[アスペクト指向プログラミング](http://en.wikipedia.org/wiki/Aspect-oriented_programming)で本質的関心と横断的関心を結びます。

`BEAR.Resource`はアプリケーションのデータや機能をリソースとして[REST制約](https://en.wikipedia.org/wiki/Representational_state_transfer)で結びます。

フレームワークは、アプリケーション全体に適用される制約と設計原則です。一貫性のある設計と実装を促進し、高品質でクリーンなアプリケーションの構築を支援します。

## ライブラリ

BEAR.Sundayはフルスタックフレームワークとは異なり、認証やデータベースなどの特定のタスクのための独自のライブラリは提供しません。その代わりに、高品質なサードパーティ製のライブラリを使用することを推奨します。

このアプローチは2つの設計思想に基づいています。1つ目は「フレームワークは変わらないがライブラリは変わる」という考え方です。フレームワークがアプリケーションの基盤として安定した構造を提供し続ける一方で、ライブラリは時間の経過とともに進化し、アプリケーションの特定のニーズを満たします。

2つ目は「ライブラリを選択する権利と責任はアプリケーションアーキテクトにある」というものです。アプリケーションアーキテクトは、アプリケーションの要件、制約、および目的に最も適したライブラリを選択する能力と責任を持ちます。

BEAR.Sundayは、フレームワークとライブラリの違いを"不易流行"（変わらぬ基本原則と時代とともに進化する要素）として明確に区別し、アプリケーション制約としてのフレームワークの役割を重視します。

## アーキテクチャ

BEAR.Sundayは、従来のMVC（Model-View-Controller）アーキテクチャとは異なり、リソース指向アーキテクチャ(ROA)を採用しています。このアーキテクチャでは、アプリケーションの設計において、データとビジネスロジックを統一してリソースとして扱い、それらに対するリンクと操作を中心に設計を行います。リソース指向アーキテクチャはREST APIの設計で広く使用されていますが、BEAR.SundayはそれをWebアプリケーション全体の設計にも適用しています。

## 長期的な視点

BEAR.Sundayは、アプリケーションの長期的な維持を念頭に置いて設計されています。

- **制約**: DI、AOP、RESTの制約に従った一貫したアプリケーション制約は、時間の経過とともに変わることはありません。

- **永遠の1.x**: 2015年の最初のリリース以来、BEAR.Sundayは後方互換性のない変更を導入することなく、継続的に進化してきました。開発者はフレームワークの定期的な互換性破壊への対応とそのテストが必要という将来の技術負債を心配する必要がありません。

- **標準準拠**: HTTP標準、JsonSchemaなどの標準に従い、DIはGoogle Guice、AOPはJavaのAop Allianceに基づいています。

## 接続性

BEAR.Sundayは、Webアプリケーションを超えて、さまざまなクライアントとのシームレスな統合を可能にします。

- **HTTPクライアント**:
  HTTPを使用して全てのリソースにアクセスすることが可能です。MVCのモデルやコントローラーと違い、BEAR.Sundayのリソースはクライアントから直接アクセスが可能です。

- **composerパッケージ**:
  composerでvendor下にインストールしたアプリケーションのリソースを直接呼び出すことができます。マイクロサービスを使わずに複数のアプリケーションを協調させることができます。

- **多言語フレームワーク**:
  BEAR.Thriftを使用して、PHP以外の言語や異なるバージョンのPHPとの連携を可能にします。

## Webキャッシュ

リソース指向アーキテクチャとモダンなCDNの技術を組み合わせることにより、従来のサーバーサイドのTTLキャッシュを超えるWeb本来の分散キャッシングを実現します。BEAR.Sundayの設計思想は、Webの基本原則に沿っており、CDNを中心に配置した分散キャッシュシステムを活用することで、高いパフォーマンスと可用性を実現します。

- **分散キャッシュ**: キャッシュをクライアント、CDN、サーバーサイドに保存することで、CPUコストとネットワークコストの両方を削減します。

- **同一性確認**:
  ETagを使用してキャッシュされたコンテンツの同一性を確認し、コンテンツの変更があった場合にのみ再取得することで、ネットワーク効率を向上させます。

- **耐障害性**:
  イベントドリブンコンテンツの採用により、キャッシュに有効期限を設けないCDNキャッシュを基本としたシステムは、PHPやDBがダウンした場合でもコンテンツを提供し続けることができます。

## パフォーマンス

BEAR.Sundayは、最大限の柔軟性を保ちながら、パフォーマンスと効率性を重視して設計されています。
極めて最適化されたブートストラップが実現され、ユーザー体験とシステムリソースの両方に好影響を与えています。
パフォーマンスは常にBEAR.Sundayの最重要課題の一つであり、設計と開発の決定において中心的な役割を果たしています。

## Because Everything is a Resource

「全てがリソース」のBEAR.Sundayは、Webの本質であるリソースを中心に設計されたPHPのWebアプリケーションフレームワークです。その真の価値は、オブジェクト指向原則とREST原則に基づいた優れた制約をアプリケーション全体の制約として提供することにあります。

この制約は、開発者に一貫性のある設計と実装を促し、長期的な視点に立ったアプリケーションの品質を高めます。同時に、この制約は開発者に自由をもたらし、アプリケーション構築における創造性を引き出します。


# AOP

アスペクト指向プログラミングは、**横断的関心事**の問題を解決します。対象メソッドの前後に、任意の処理をインターセプターで織り込むことができます。
対象となるメソッドはビジネスロジックなどの本質的関心事のみに関心を払い、インターセプターはログや検証などの横断的関心事に関心を払います。

BEAR.Sundayは[AOP Alliance](http://aopalliance.sourceforge.net/)に準拠したアスペクト指向プログラミングをサポートします。

## インターセプター

インターセプターの`invoke`メソッドでは`$invocation`メソッド実行変数を受け取り、メソッドの前後に処理を加えます。これはインターセプター元メソッドを実行するためだけの変数です。前後にログやトランザクションなどの横断的処理を記述します。

```php?start_inline
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class MyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        // メソッド実行前の処理
        // ...

        // メソッド実行
        $result = $invocation->proceed();

        // メソッド実行後の処理
        // ...

        return $result;
    }
}
```

## 束縛

[モジュール](module.html)で対象となるクラスとメソッドを`Matcher`で"検索"して、マッチするメソッドにインターセプターを束縛します。

```php?start_inline
$this->bindInterceptor(
    $this->matcher->any(),                   // どのクラスでも
    $this->matcher->startsWith('delete'),    // "delete"で始まるメソッド名のメソッドには
    [Logger::class]                          // Loggerインターセプターを束縛
);

$this->bindInterceptor(
    $this->matcher->subclassesOf(AdminPage::class),  // AdminPageの継承または実装クラスの
    $this->matcher->annotatedWith(Auth::class),      // @Authアノテーションがアノテートされているメソッドには
    [AdminAuthentication::class]                     // AdminAuthenticationインターセプターを束縛
);
```

`Matcher`では以下のような指定も可能です：

* [Matcher::any](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L16) - 無制限
* [Matcher::annotatedWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L23) - アノテーション
* [Matcher::subclassesOf](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L30) - 継承または実装されたクラス
* [Matcher::startsWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L37) - 名前の始めの文字列
* [Matcher::logicalOr](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L44) - OR条件
* [Matcher::logicalAnd](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L51) - AND条件
* [Matcher::logicalNot](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L58) - NOT条件

インターセプターに渡される`MethodInvocation`では、対象のメソッド実行に関連するオブジェクトやメソッド、引数にアクセスすることができます。

* [MethodInvocation::proceed](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php) - 対象メソッド実行
* [MethodInvocation::getMethod](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php) - 対象メソッドリフレクションの取得
* [MethodInvocation::getThis](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php) - 対象オブジェクトの取得
* [MethodInvocation::getArguments](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Invocation.php) - 呼び出し引数配列の取得

リフレクションのメソッドでアノテーションを取得することができます。

```php?start_inline
$method = $invocation->getMethod();
$class = $invocation->getMethod()->getDeclaringClass();
```

* `$method->getAnnotations()`    - メソッドアノテーションの取得
* `$method->getAnnotation($name)`
* `$class->getAnnotations()`     - クラスアノテーションの取得
* `$class->getAnnotation($name)`

## カスタムマッチャー

独自のカスタムマッチャーを作成するには、`AbstractMatcher`の`matchesClass`と`matchesMethod`を実装したクラスを作成します。

`contains`マッチャーを作成するには、2つのメソッドを持つクラスを提供する必要があります。
1つはクラスのマッチを行う`matchesClass`メソッド、もう1つはメソッドのマッチを行う`matchesMethod`メソッドです。いずれもマッチしたかどうかをboolで返します。

```php?start_inline
use Ray\Aop\AbstractMatcher;

/**
 * 特定の文字列が含まれているか
 */
class ContainsMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        list($contains) = $arguments;

        return (strpos($class->name, $contains) !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        list($contains) = $arguments;

        return (strpos($method->name, $contains) !== false);
    }
}
```

モジュール

```php?start_inline
class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            new ContainsMatcher('user'), // 'user'がメソッド名に含まれているか
            [UserLogger::class]
        );
    }
};
```



# リソース

BEAR.SundayアプリケーションはRESTfulなリソースの集合です。

## サービスとしてのオブジェクト

`ResourceObject`はHTTPのメソッドがPHPのメソッドにマップされたリソースの**サービスのためのオブジェクト**（Object-as-a-service）です。ステートレスリクエストから、リソースの状態がリソース表現として生成され、クライアントに転送されます。（[Representational State Transfer](http://ja.wikipedia.org/wiki/REST)）

以下は、ResourceObjectの例です。

```php
class Index extends ResourceObject
{
    public $code = 200;
    public $headers = [];

    public function onGet(int $a, int $b): static
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
    public function onPost(string $id, string $todo): static
    {
        $this->code = 201; // ステータスコード
        $this->headers = [ // ヘッダー
            'Location' => '/todo/new_id'
        ];

        return $this;
    }
}
```

PHPのリソースクラスはWebのURIと同じような`page://self/index`などのURIを持ち、HTTPのメソッドに準じた`onGet`、`onPost`などのonメソッドを持ちます。onメソッドで与えられたパラメーターから自身のリソース状態`code`、`headers`、`body`を決定し、`$this`を返します。

## URI

URIはPHPのクラスにマップされています。アプリケーションではクラス名の代わりにURIを使ってリソースにアクセスします。

| URI | Class |
|



# リソースパラメーター

## 基本

ResourceObjectが必要とするHTTPリクエストやCookieなどのWebランタイムの値は、メソッドの引数に直接渡されます。HTTPリクエストでは`onGet`、`onPost`メソッドの引数にはそれぞれ`$_GET`、`$_POST`が変数名に応じて渡されます。

例えば下記の`$id`は`$_GET['id']`が渡されます。入力がHTTPの場合、文字列として渡された引数は指定した型にキャストされます。

```php
class Index extends ResourceObject
{
    public function onGet(int $id): static
    {
        // ....
```

## パラメーターの型

### スカラーパラメーター

HTTPで渡されるパラメーターは全て文字列ですが、`int`など文字列以外の型を指定するとキャストされます。

### 配列パラメーター

パラメーターはネストされたデータ [^2] でも構いません。JSONやネストされたクエリ文字列で送信されたデータは配列で受け取ることができます。

[^2]: [parse_str](https://www.php.net/manual/ja/function.parse-str.php)参照

```php
class Index extends ResourceObject
{
    public function onPost(array $user): static
    {
        $name = $user['name']; // bear
```

### クラスパラメーター

パラメータ専用のInputクラスで受け取ることもできます。

```php
class Index extends ResourceObject
{
    public function onPost(User $user): static
    {
        $name = $user->name; // bear
```

Inputクラスは事前にパラメーターをpublicプロパティにしたものを定義しておきます。

```php
<?php
namespace Vendor\App\Input;

final class User
{
    public int $id;
    public string $name;
}
```

この時、コンストラクタがあるとコールされます。[^php8]

[^php8]: PHP8.xでは名前付き引数で呼ばれますが、PHP7.xでは順序引数でコールされます。

```php
<?php
namespace Vendor\App\Input;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {
    }
}
```

ネームスペースは任意です。Inputクラスでは入力データをまとめたり検証したりするメソッドを実装することができます。

### 列挙型パラメーター

PHP8.1の[列挙型](https://www.php.net/manual/ja/language.types.enumerations.php)を指定して取り得る値を制限することができます。

```php
enum IceCreamId: int
{
    case VANILLA = 1;
    case PISTACHIO = 2;
}
```

```php
class Index extends ResourceObject
{
    public function onGet(IceCreamId $iceCreamId): static
    {
        $id = $iceCreamId->value // 1 or 2
```

上記の場合、1か2以外が渡されると`ParameterInvalidEnumException`が発生します。

## Webコンテキスト束縛

`$_GET`や`$_COOKIE`などのPHPのスーパーグローバルの値をメソッド内で取得するのではなく、メソッドの引数に束縛することができます。

```php
use Ray\WebContextParam\Annotation\QueryParam;

class News extends ResourceObject
{
    public function foo(
        #[QueryParam('id')] string $id
    ): static {
        // $id = $_GET['id'];
```

その他`$_ENV`、`$_POST`、`$_SERVER`の値を束縛することができます。

```php
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

class News extends ResourceObject
{
    public function onGet(
        #[QueryParam('id')] string $userId,            // $_GET['id']
        #[CookieParam('id')] string $tokenId = "0000", // $_COOKIE['id'] or "0000" when unset
        #[EnvParam('app_mode')] string $app_mode,      // $_ENV['app_mode']
        #[FormParam('token')] string $token,           // $_POST['token']
        #[ServerParam('SERVER_NAME')] string $server   // $_SERVER['SERVER_NAME']
    ): static {
```

クライアントが値を指定した時は指定した値が優先され、束縛した値は無効になります。テストの時に便利です。

## リソース束縛

`#[ResourceParam]`アノテーションを使えば他のリソースリクエストの結果をメソッドの引数に束縛できます。

```php
use BEAR\Resource\Annotation\ResourceParam;

class News extends ResourceObject
{
    public function onGet(
        #[ResourceParam('app://self//login#nickname')] string $name
    ): static {
```

この例ではメソッドが呼ばれると`login`リソースに`get`リクエストを行い、`$body['nickname']`を`$name`で受け取ります。

## コンテントネゴシエーション

HTTPリクエストの`content-type`ヘッダーがサポートされています。`application/json`と`x-www-form-urlencoded`メディアタイプを判別してパラメーターに値が渡されます。[^json]

[^json]: APIリクエストをJSONで送信する場合には`content-type`ヘッダーに`application/json`をセットしてください。



## ベストプラクティス

RESTではリソースは他のリソースと接続されています。リンクをうまく使うとコードは簡潔になり、読みやすくテストや変更が容易なコードになります。

### #[Embed]

他のリソースの状態を`get`する代わりに`#[Embed]`でリソースを埋め込みます。

```php
// OK but not the best
class Index extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )
    
    public function onGet(string $status): static
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
    #[Embed(rel: 'todos', src: 'app://self/todos{?status}')]
    public function onGet(string $status): static
    {
        return $this;
    }
}
```

### #[Link]

他のリソースの状態を変えるときに`#[Link]`で示された次のアクションを`href()`（ハイパーリファレンス）を使って辿ります。

```php
// OK but not the best
class Todo extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )
    
    public function onPost(string $title): static
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
    public function __construct(
        private readonly ResourceInterface $resource
    )
    
    #[Link(rel: 'create', href: 'app://self/todo', method: 'post')]
    public function onPost(string $title): static
    {
        $this->resource->href('create', ['title' => $title]);
        $this->code = 301;
        $this->headers[ResponseHeader::LOCATION] = '/';
        return $this;
    }
}
```

### #[ResourceParam]

他のリソースをリクエストするために他のリソース結果が必要な場合は`#[ResourceParam]`を使います。

```php
// OK but not the best
class User extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )
    
    public function onGet(string $id): static
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
    public function __construct(
        private readonly ResourceInterface $resource
    )
    
    #[ResourceParam(param: 'name', uri: 'app://self//login-user#nickname')]
    public function onGet(string $id, string $name): static
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
    #[ResourceParam(param: 'name', uri: 'app://self//login-user#nickname')]
    #[Embed(rel: 'profile', src: 'app://self/profile')]
    public function onGet(string $id, string $name): static
    {
        $this->body['profile']->addQuery(['name' => $name]);
        return $this;
    }
}
```



# リソースリンク

リソースは他のリソースをリンクすることができます。リンクには外部のリソースをリンクする外部リンク[^LO]と、リソース自身に他のリソースを埋め込む内部リンク[^LE]の2種類があります。

[^LE]: [embedded links](http://amundsen.com/hypermedia/hfactor/#le) 例）HTMLは独立した画像リソースを埋め込むことができます。
[^LO]: [out-bound links](http://amundsen.com/hypermedia/hfactor/#le) 例）HTMLは関連した他のHTMLにリンクを張ることができます。

## 外部リンク

リンクをリンクの名前の`rel`（リレーション）と`href`で指定します。`href`には正規のURIの他に[RFC6570 URIテンプレート](https://github.com/ioseb/uri-template)を指定することができます。

```php
    #[Link(rel: 'profile', href: '/profile{?id}')]
    public function onGet($id): static
    {
        $this->body = [
            'id' => 10
        ];
        return $this;
    }
```

上記の例では`href`で表されていて、`$body['id']`が`{?id}`にアサインされます。[HAL](https://stateless.group/hal_specification.html)フォーマットでの出力は以下のようになります。

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

## 内部リンク

リソースは別のリソースを埋め込むことができます。`#[Embed]`の`src`でリソースを指定します。内部リンクされたリソースも他のリソースを内部リンクしているかもしれません。その場合また内部リンクのリソースが必要で、それが再帰的に繰り返され**リソースグラフ**が得られます。

クライアントはリソースを何度もフェッチすることなく目的とするリソース群を一度に取得できます。[^di] 例えば顧客リソースと商品リソースをそれぞれ呼び出す代わりに、注文リソースで両者を埋め込みます。

[^di]: DIで依存関係のツリーがグラフになっているオブジェクトグラフと同様です。

```php
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    #[Embed(rel: 'sports', src: '/news/sports')]
    #[Embed(rel: 'weather', src: '/news/weather')]
    public function onGet(): static
```

埋め込まれるのはリソース**リクエスト**です。レンダリングの時に実行されますが、その前に`addQuery()`メソッドで引数を加えたり`withQuery()`で引数を置き換えることができます。`src`にはURI templateが利用でき、**リクエストメソッドの引数**がバインドされます（外部リンクと違って`$body`ではありません）。

```php
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    #[Embed(rel: 'website', src: '/website{?id}')]
    public function onGet(string $id): static
    {
        // ...
        $this->body['website']->addQuery(['title' => $title]); // 引数追加
```

### セルフリンク

`#[Embed]`でリレーションを`_self`としてリンクすると、リンク先のリソース状態を自身のリソース状態にコピーします。

```php
namespace MyVendor\Weekday\Resource\Page;

class Weekday extends ResourceObject
{
    #[Embed(rel: '_self', src: 'app://self/weekday{?year,month,day}')]
    public function onGet(string $id): static
    {
```

この例ではPageリソースがAppリソースの`weekday`リソースの状態を自身にコピーしています。

### HALでの内部リンク

[HAL](https://github.com/blongden/hal)レンダラーでは`_embedded`として扱われます。

## リンクリクエスト

クライアントはハイパーリンクで接続されているリソースをリンクすることができます。

```php
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

リンクは3種類あります。`$rel`をキーにして元のリソースの`body`にリンク先のリソースが埋め込まれます。

* `linkSelf($rel)` - リンク先と入れ替わります。
* `linkNew($rel)` - リンク先のリソースがリンク元のリソースに追加されます
* `linkCrawl($rel)` - リンクをクロールしてリソースグラフを作成します。

### クロール

クロールはリスト（配列）になっているリソースを順番にリンクを辿り、複雑なリソースグラフを構成することができます。クローラーがWebページをクロールするように、リソースクライアントはハイパーリンクをクロールしてリソースグラフを生成します。

#### クロール例

author, post, meta, tag, tag/nameがそれぞれ関連づけられているリソースグラフを考えてみます。このリソースグラフに **post-tree** という名前を付け、それぞれのリソースの`#[Link]`アトリビュートでハイパーリファレンス **href** を指定します。

最初に起点となるauthorリソースにはpostリソースへのハイパーリンクがあります。1:nの関係です。

```php
#[Link(crawl: "post-tree", rel: "post", href: "app://self/post?author_id={id}")]
public function onGet($id = null)
```

postリソースにはmetaリソースとtagリソースのハイパーリンクがあります。1:nの関係です。

```php
#[Link(crawl: "post-tree", rel: "meta", href: "app://self/meta?post_id={id}")]
#[Link(crawl: "post-tree", rel: "tag", href: "app://self/tag?post_id={id}")]
public function onGet($author_id)
{
```

tagリソースはIDだけでそのIDに対応するtag/nameリソースへのハイパーリンクがあります。1:1の関係です。

```php
#[Link(crawl: "post-tree", rel: "tag_name", href: "app://self/tag/name?tag_id={tag_id}")]
public function onGet($post_id)
```

それぞれが接続されました。クロール名を指定してリクエストします。

```php
$graph = $resource
    ->get
    ->uri('app://self/marshal/author')
    ->linkCrawl('post-tree')
    ->eager
    ->request();
```

リソースクライアントは`#[Link]`アトリビュートに指定されたクロール名を発見するとその**rel**名でリソースを接続してリソースグラフを作成します。

```php
var_export($graph->body);
array (
    0 =>
    array (
        'name' => 'Athos',
        'post' =>
        array (
            0 =>
            array (
                'author_id' => '1',
                'body' => 'Anna post #1',
                'meta' =>
                array (
                    0 =>
                    array (
                        'data' => 'meta 1',
                    ),
                ),
                'tag' =>
                array (
                    0 =>
                    array (
                        'tag_name' =>
                        array (
                            0 =>
                            array (
                                'name' => 'zim',
                            ),
                        ),
                    ), 
                    // ...
```



# レンダリングと転送

<img src="https://bearsunday.github.io/images/screen/4r.png" alt="Resource object internal structure">

ResourceObjectのリクエストメソッドではリソースの表現について関心を持ちません。コンテキストに応じて注入されたレンダラーがリソースの表現を生成します。同じアプリケーションがコンテキストを変えるだけでHTMLで出力されたり、JSONで出力されたりします。

## 遅延評価

レンダリングはリソースが文字列評価された時に行われます。

```php
$weekday = $api->resource->get('app://self/weekday', ['year' => 2000, 'month'=> 1, 'day'=> 1]);
var_dump($weekday->body);
//array(1) {
//    ["weekday"]=>
//    string(3) "Sat"
//}

echo $weekday;
//{
//    "weekday": "Sat",
//    "_links": {
//        "self": {
//            "href": "/weekday/2000/1/1"
//        }
//    }
//}
```

## レンダラー

それぞれのResourceObjectはコンテキストによって指定されたその表現のためのレンダラーが注入されています。リソース特有のレンダリングを行う時は`renderer`プロパティを注入またはセットします。

例）デフォルトで用意されているJSON表現のレンダラーをスクラッチで書くと：

```php
class Index extends ResourceObject
{
    #[Inject]
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

ルートオブジェクト`$app`にインジェクトされたリソース表現をクライアント（コンソールやWebクライアント）に転送します。通常、出力は`header`関数や`echo`で行われますが、巨大なデータなどには[ストリーム転送](stream.html)が有効です。

リソース特有の転送を行う時は`transfer`メソッドをオーバーライドします。

```php
public function transfer(TransferInterface $responder, array $server)
{
    $responder($this, $server);
}
```

## リソースの自律性

リソースはリクエストによって自身のリソース状態を変更し、それを表現にして転送する機能を各クラスが持っています。


# 技術

BEAR.Sundayの特徴的な技術と機能を以下の章に分けて解説します。

* [アーキテクチャと設計原則](#アーキテクチャと設計原則)
* [パフォーマンスとスケーラビリティ](#パフォーマンスとスケーラビリティ)
* [開発者エクスペリエンス](#開発者エクスペリエンス)
* [拡張性と統合](#拡張性と統合)
* [設計思想と品質](#設計思想と品質)
* [BEAR.Sundayのもたらす価値](#bearsundayのもたらす価値)

## アーキテクチャと設計原則

### リソース指向アーキテクチャ (ROA)

BEAR.SundayのROAは、WebアプリケーションでRESTful APIを実現するアーキテクチャです。これはBEAR.Sundayの設計原則の核となるものであり、ハイパーメディアフレームワークであると同時にサービスとしてのオブジェクト（Object as a service）として扱います。Webと同様に、全てのデータや機能をリソースとみなし、GET、POST、PUT、DELETEなどの標準化されたインターフェースを通じて操作します。

#### URI

URI（Uniform Resource Identifier）はWebの成功の鍵となる要素であり、BEAR.SundayのROAの中核でもあります。アプリケーションが扱う全てのリソースにURIを割り当てることで、リソースを識別し、アクセスしやすくなります。URIは、リソースの識別子として機能するだけでなく、リソース間のリンクを表現するためにも使用されます。

#### ユニフォームインターフェース

リソースへのアクセスはHTTPのメソッド（GET, POST, PUT, DELETE）を用いて行われます。これらのメソッドはリソースに対して実行できる操作を規定しており、リソースの種類にかかわらず共通のインターフェースを提供します。

#### ハイパーメディア

BEAR.SundayのROAでは、各リソースがハイパーリンクを通じてアフォーダンス（クライアントが利用可能な操作や機能）を提供します。これらのリンクは、クライアントが利用できる操作を表し、アプリケーション内をナビゲートする方法を示します。

#### 状態と表現の分離

BEAR.SundayのROAでは、リソースの状態とそのリソース表現が明確に分離されています。リソースの状態はリソースクラスで管理され、リソースにインジェクトされたレンダラーが様々な形式（JSON, HTMLなど）でリソースの状態をリソース状態表現に変換します。ドメインロジックとプレゼンテーションロジックは疎結合で、同じコードでもコンテキストによって状態表現の束縛を変更すると表現も変わります。

#### MVCとの相違点

BEAR.SundayのROAは、従来のMVCアーキテクチャとは異なるアプローチを採用しています。
MVCはモデル、ビュー、コントローラーの3つのコンポーネントでアプリケーションを構成し、コントローラーはリクエストオブジェクトを受け取り、一連の処理を制御してレスポンスを返します。一方、リソースはリクエストメソッドにおいて、単一責任原則（SRP）に従い、リソースの状態の指定のみを行い、表現には関与しません。

MVCではコントローラーとモデルの関係に制約はありませんが、リソースはハイパーリンクとURIを使用した他のリソースを含める明示的な制約があります。これにより、呼び出されるリソースの情報隠蔽を維持しながら、宣言的な方法でコンテンツの内包関係とツリー構造を定義できます。

MVCのコントローラーはリクエストオブジェクトから手動で値を取得しますが、リソースは必要な変数をリクエストメソッドの引数として宣言的に定義します。そのため、入力バリデーションもJsonSchemaを使用して宣言的に実行され、引数とその制約が文書化されます。

### 依存性の注入 (DI)

依存性の注入（Dependency Injection, DI）は、オブジェクト指向プログラミングにおけるアプリケーションの設計と構造を強化するための重要な手法です。DIの中心的な目的は、アプリケーションの機能を複数の独立したドメインまたは役割を持つコンポーネントに分割し、それらの間の依存関係を管理することです。

DIは、1つの機能（関心事、責務）を複数の機能に水平分割するのに役立ちます。分割された機能は「依存」として各部分を独立して開発、テストできるようになります。単一責任原則に基づき明確な責任と役割を持つそれらの依存を外部から注入することで、オブジェクトの再利用性とテスト性を向上させます。また依存は他の依存へと垂直にも分割され、依存関係のツリーを形成します。

BEAR.SundayのDIは[Ray.Di](https://github.com/ray-di/Ray.Di)という独立したパッケージを使用しており、Google社製のDIフレームワークであるGuiceの設計思想を取り入れ、ほぼ全ての機能をカバーしています。

その他に以下の特徴があります。

* コンテキストにより束縛を変更し、テスト時に異なる実装を注入できます。
* アトリビュートによる設定でコードの自己記述性が高まります。
* Ray.Diはコンパイル時に依存性の解決を行うため、ランタイム時のパフォーマンスが向上します。これは、ランタイム時に依存性を解決する他のDIコンテナとは異なる点です。
* オブジェクトの依存関係をグラフで可視化できます。例）[ルートオブジェクト](/images/app.svg)

<img src="https://ray-di.github.io/images/logo.svg" width="180" alt="Ray.Di logo">

### アスペクト指向プログラミング (AOP)

アスペクト指向プログラミング（AOP）は、ビジネスロジックなどの本質的な関心と、ログやキャッシュなどの横断的関心を分離することで、柔軟なアプリケーションを実現するパターンです。横断的関心とは、複数のモジュールやレイヤーにまたがって存在する機能や処理のことを指します。探索条件に基づいた横断的処理の束縛が可能で、コンテキストに基づいた柔軟な構成が可能です。

BEAR.SundayのAOPはRay.Aopという独立したパッケージを使用しており、PHPのアトリビュートをクラスやメソッドに付与して、横断的処理を宣言的に束縛します。Ray.Aopは、Javaの[AOP Alliance](https://aopalliance.sourceforge.net/)に準拠しています。

AOPは「既存の秩序を壊す強い力」と誤解されがちな技術です。その存在意義は制約を超えた力の行使などではなく、マッチャーを使った探索的な機能の割り当てや横断的処理の分離など、オブジェクト指向が不得意とする分野の補完にあります。AOPはアプリケーションの横断的な制約を作ることのできる、つまりアプリケーションフレームワークとして機能するパラダイムです。

## パフォーマンスとスケーラビリティ

### モダンCDNとの統合によるROAベースのイベントドリブンコンテンツ戦略

BEAR.Sundayは、リソース指向アーキテクチャ（ROA）を中核として、Fastlyなどのインスタントパージ可能なCDNと統合することで、高度なイベントドリブンキャッシュ戦略を実現しています。この戦略では、従来のTTL（Time to Live）によるキャッシュの無効化ではなく、リソースの状態変更イベントに応じてCDNとサーバーサイドのキャッシュ、およびETag（エンティティタグ）を即座に無効化します。

このようにCDNに揮発性のない永続的なコンテンツを配置するというアプローチにより、SPOF（Single Point of Failure）を回避し、高い可用性と耐障害性を実現します。さらに、ユーザー体験とコスト効率を最大化させ、ダイナミックコンテンツでもスタティックコンテンツと同じWeb本来の分散キャッシングを実現します。これは、Webが1990年代から持っていたスケーラブルでネットワークコストを削減する分散キャッシュという原則を、現代的な技術で再実現するものです。

#### セマンティックメソッドと依存によるキャッシュ無効化

BEAR.SundayのROAでは、各リソース操作にセマンティック（意味的な役割）が与えられています。例えば、GETメソッドはリソースを取得し、PUTメソッドはリソースを更新します。これらのメソッドがイベントドリブン方式で連携し、関連するキャッシュを効率的に無効化します。例えば、特定のリソースが更新された際には、そのリソースを必要とするリソースのキャッシュが無効化されます。これにより、データの一貫性と新鮮さを保ち、ユーザーに最新の情報を提供します。

#### ETagによる同一性確認と高速な応答

システムがブートする前にETagを設定することで、コンテンツの同一性を迅速に確認し、変更がない場合は304 Not Modified応答を返してネットワークの負荷を最小化します。

#### ドーナッツキャッシュとESIによる部分的な更新

BEAR.Sundayでは、ドーナッツキャッシュ戦略を採用しており、ESI（Edge Side Includes）を使用してCDNエッジで部分的なコンテンツ更新を可能にしています。この技術により、ページ全体を再キャッシュすることなく、必要な部分だけを動的に更新してキャッシュ効率を向上させます。

このように、BEAR.SundayとFastlyの統合によるROAベースのキャッシュ戦略は、高度な分散キャッシングの実現とともに、アプリケーションのパフォーマンス向上と耐障害性の強化を実現しています。

### 起動の高速化

DIの本来の世界では、ユーザーは可能な限りインジェクター（DIコンテナ）を直接扱いません。その代わり、アプリケーションのエントリーポイントで1つのルートオブジェクトを生成してアプリケーションを起動します。BEAR.SundayのDIでは、設定時でもDIコンテナの操作が実質的に存在しません。ルートオブジェクトは巨大ですが1つの変数なので、リクエストを超えて再利用され、極限まで最適化したブートストラップを実現します。

## 開発者エクスペリエンス

### テストの容易性

BEAR.Sundayは、以下の設計上の特徴により、テストが容易で効果的に行えます。

* 各リソースは独立していて、RESTのステートレスリクエストの性質によりテストが容易です。
  リソースの状態と表現が明確に分離されているため、HTML表現の場合でもリソースの状態をテストできます。
* ハイパーメディアのリンクをたどりながらAPIのテストを行え、PHPとHTTPの同一コードでテストできます。
* コンテキストによる束縛により、テスト時に異なる実装を束縛できます。

### APIドキュメント生成

コードからAPIドキュメントを自動生成します。コードとドキュメントの整合性を保ち、保守性を高めます。

### 視覚化とデバッグ

リソースが自身でレンダリングする技術的特徴を生かし、開発時にHTML上でリソースの範囲を示し、リソース状態をモニターできます。また、PHPコードやHTMLテンプレートをオンラインエディターで編集し、リアルタイムに反映することもできます。

## 拡張性と統合

### PHPインターフェイスとSQL実行の統合

BEAR.SundayではPHPのインターフェイスを通じて、データベースとのやり取りを行うSQL文の実行を簡単に管理できます。クラスを実装することなく、PHPインターフェイスに直接SQLの実行オブジェクトを束縛することが可能です。ドメインとインフラストラクチャーの境界をPHPインターフェイスで結びます。

引数には型も指定でき、不足している分はDIが依存解決を行い文字列として利用されます。SQL実行に現在時刻が必要な場合でも渡す必要はなく、自動束縛されます。クライアントが全ての引数を渡す責任がなく、コードの簡潔さを保つことができます。

また、SQLの直接管理は、エラー発生時のデバッグを容易にします。SQLクエリの動作を直接観察し、問題の特定と修正を迅速に行うことができます。

### 他システムとの統合

コンソールアプリケーションと統合し、ソースコードを変えずにWebとコマンドライン双方からアクセスできます。また、同一PHPランタイム内で異なるBEAR.Sundayアプリケーションを並行実行できることで、マイクロサービスを構築することなく独立した複数のアプリケーションを連携できます。

### ストリーム出力

リソースのボディにファイルのポインタなどのストリームを割り当てることで、メモリ上では扱えない大規模なコンテンツを出力できます。その際、ストリームは通常の実変数と混在させることも可能で、大規模なレスポンスを柔軟に出力できます。

### 他のシステムからの段階的移行

BEAR.Sundayは段階的な移行パスを提供し、LaravelやSymfonyなどの他のフレームワークやシステムとのシームレスな統合を可能にします。このフレームワークはComposerパッケージとして実装できるため、開発者は既存のコードベースにBEAR.Sundayの機能を段階的に導入できます。

### 技術移行の柔軟性

BEAR.Sundayは、将来の技術的変化や要件の進化に備えて投資を保護します。このフレームワークから別のフレームワークや言語に移行する必要がある場合でも、構築したリソースは無駄になりません。PHP環境では、BEAR.SundayアプリケーションをComposerパッケージとして統合して継続的に利用できます。また、BEAR.Thriftを使用すると、他の言語からBEAR.Sundayリソースに効率的にアクセスでき、Thriftを使用しない場合でもHTTPでアクセスが可能です。さらに、SQLコードの再利用も容易です。

また、使用しているライブラリが特定のPHPバージョンに強く依存している場合でも、BEAR.Thriftを使用して異なるバージョンのPHPを共存させることができます。

## 設計思想と品質

### 標準技術の採用と独自規格の排除

BEAR.Sundayは、可能な限り標準技術を採用し、フレームワーク独自の規格やルールを排除するという設計思想を持っています。例えば、デフォルトでJSON形式とwwwフォーム形式のHTTPリクエストのコンテントネゴシエーションをサポートし、エラーレスポンスには[vnd.error+json](https://github.com/blongden/vnd.error)メディアタイプ形式を使用します。リソース間のリンクには[HAL](https://datatracker.ietf.org/doc/html/draft-kelly-json-hal)（Hypertext Application Language）を採用し、バリデーションには[JsonSchema](https://json-schema.org/)を用いるなど、標準的な技術や仕様を積極的に取り入れています。

一方で、独自のバリデーションルールや、フレームワーク特有の規格・ルールは可能な限り排除しています。

### オブジェクト指向原則

BEAR.Sundayはアプリケーションを長期的にメンテナンス可能とするためのオブジェクト指向原則を重視しています。

#### 継承より合成

継承クラスよりコンポジションを推奨します。一般に子クラスから親クラスのメソッドを直接呼び出すことは、クラス間の結合度を高くする可能性があります。設計上、ランタイムで継承が必要な抽象クラスはリソースクラスの`BEAR\Resource\ResourceObject`のみですが、これもResourceObjectのメソッドは他のクラスが利用するためだけに存在します。ユーザーが継承したフレームワークの親クラスのメソッドをランタイムに呼び出すことは、BEAR.Sundayではどのクラスにもありません。

#### 全てがインジェクション

フレームワークのクラスが「設定ファイル」や「デバッグ定数」を実行中に参照して振る舞いを決定することはありません。振る舞いに応じた依存が注入されます。これにより、アプリケーションの振る舞いを変更するためには、コードを変更する必要がなく、インターフェイスに対する依存性の実装の束縛を変更するだけで済みます。APP_DEBUGやAPP_MODE定数は存在しません。ソフトウェアが起動した後に現在どのモードで動作しているか知る方法はありませんし、知る必要もありません。

### 後方互換性の永続的確保

BEAR.Sundayは、ソフトウェアの進化において後方互換性の維持を重視して設計されており、リリース以来、後方互換性を破壊することなく進化を続けています。現代のソフトウェア開発では、頻繁な後方互換性の破壊と、それに伴う改修やテストの負担が課題となっていますが、BEAR.Sundayはこの問題を回避してきました。

BEAR.Sundayでは、セマンティックバージョニングを採用するだけでなく、破壊的な変更を伴うメジャーバージョンアップを行いません。新しい機能の追加や既存機能の変更が既存のコードに影響を与えることを防いでいます。古くなって使われなくなったコードには「deprecated」の属性が与えられますが、削除されることはなく、既存のコードの動作にも影響を与えません。その代わりに、新しい機能が追加され、進化が続けられます。

### 非環式依存原則

非環式依存原則（ADP）とは、依存関係が一方向であり、循環していないことを意味します。BEAR.Sundayフレームワークはこの原則に基づき、一連のパッケージで構成されており、大きなフレームワークパッケージが小さなフレームワークパッケージに依存する階層構造を持っています。各レベルはそれを包含する他のレベルの存在自体を知る必要はなく、依存関係は一方向のみで循環しません。例えば、Ray.AopはRay.Diの存在すら知りませんし、Ray.DiはBEAR.Sundayの存在を知りません。

<img src="/images/screen/package_adp.png" width="360px" alt="非環式依存原則に従ったフレームワーク構造">

後方互換性が保持されているため、各パッケージは独立して更新できます。また、他のフレームワークで見られるような全体をロックするバージョン番号は存在せず、オブジェクト間を横断する依存関係を持つオブジェクトプロキシーの機構もありません。

この非環式依存原則はDI（依存性注入）の原則と調和しており、BEAR.Sundayが起動する際に生成されるルートオブジェクトも、この非環式依存原則の構造に従って構築されます。

[<img src="/images/screen/clean-architecture.png" width="40%">](/images/screen/clean-architecture.png)

ランタイムも同様です。リソースにアクセスが行われる際、まずメソッドに結び付けられたAOPアスペクトの横断的な処理が行われ、その後でメソッドがリソースの状態を決定しますが、この時点でメソッドは結び付けられたアスペクトの存在を認識していません。リソースの状態に埋め込まれたリソースも同じです。それらは外側の層や要素の知識を持っていません。関心の分離が明確にされています。

### コード品質

高品質なアプリケーションを提供するため、BEAR.Sundayフレームワークも高い水準でコード品質を維持するよう努めています。

* フレームワークのコードは静的解析ツールのPsalmとPHPStan双方で最も厳しいレベルを適用しています。
* テストカバレッジ100%を保持しており、タイプカバレッジもほぼ100%です。
* 原則的にイミュータブルなシステムであり、テストでも毎回初期化が不要なほどクリーンです。SwooleのようなPHPの非同期通信エンジンの性能を最大限に引き出します。

## BEAR.Sundayのもたらす価値

### 開発者にとっての価値

* **生産性の向上**：
  堅牢な設計パターンと原則に基づく、時間が経過しても変わることのない制約により、開発者はコアとなるビジネスロジックに集中できます。

* **チームでの協業**：
  開発チームに一貫性のあるガイドラインと構造を提供することで、異なる開発者のコードを疎結合のまま統一的に保ち、コードの可読性とメンテナンス性を向上させます。

* **柔軟性と拡張性**：
  BEAR.Sundayがライブラリを含まないという方針により、開発者はコンポーネントの選択において高い柔軟性と自由度を得られます。

* **テストの容易性**：
  DI（依存性の注入）とROA（リソース指向アーキテクチャ）の採用により、効果的かつ効率的なテストの実施が可能です。

### ユーザーにとっての価値

* **高いパフォーマンス**：
  最適化された高速起動とCDNを中心としたキャッシュ戦略により、ユーザーには高速で応答性の優れたエクスペリエンスが提供されます。

* **信頼性と可用性**：
  CDNを中心としたキャッシュ戦略により、単一障害点（SPOF）を最小化し、ユーザーに安定したサービスを提供し続けることができます。

* **使いやすさ**：
  優れた接続性により、他の言語やシステムとの円滑な連携が実現します。

### ビジネスにとっての価値

* **開発コストの削減**：
  一貫性のあるガイドラインと構造の提供により、持続的で効率的な開発プロセスを実現し、開発コストを抑制します。

* **保守コストの削減**：
  後方互換性を重視するアプローチにより、技術的な継続性を高め、変更対応にかかる時間とコストを最小限に抑えます。

* **高い拡張性**：
  DI（依存性の注入）やAOP（アスペクト指向プログラミング）などの技術により、コードの変更を最小限に抑えながら振る舞いを変更でき、ビジネスの成長や変化に合わせて柔軟にアプリケーションを拡張できます。

* **優れたユーザーエクスペリエンス（UX）**：
  高いパフォーマンスと可用性の提供により、ユーザー満足度を向上させ、顧客ロイヤリティの強化と顧客基盤の拡大を通じて、ビジネスの成功に貢献します。

### まとめ

優れた制約は不変です。BEAR.Sundayが提供する制約は、開発者、ユーザー、ビジネスのそれぞれに対して、具体的かつ持続的な価値をもたらします。

BEAR.Sundayは、Webの原則と精神に基づいて設計されたフレームワークです。開発者に明確な制約を提供することで、柔軟性と堅牢性を兼ね備えたアプリケーションを構築する力を与えます。



# ルーター

ルーターはWebやコンソールなどの外部コンテキストのリソースリクエストを、BEAR.Sunday内部のリソースリクエストに変換します。

```php
$request = $app->router->match($GLOBALS, $_SERVER);
echo (string) $request;
// get page://self/user?name=bear
```

## Webルーター

デフォルトのWebルーターではHTTPリクエストのパス(`$_SERVER['REQUEST_URI']`)に対応したリソースクラスにアクセスされます。例えば`/index`のリクエストは`{Vendor名}\{Project名}\Resource\Page\Index`クラスのHTTPメソッドに応じたPHPメソッドにアクセスされます。

Webルーターは規約ベースのルーターです。設定やスクリプトは必要ありません。

```php
namespace MyVendor\MyProject\Resource\Page;

// page://self/index
class Index extends ResourceObject
{
    public function onGet(): static // GETリクエスト
    {
    }
}
```

## CLIルーター

`cli`コンテキストではコンソールからの引数が外部入力になります。

```bash
php bin/page.php get /
```

BEAR.SundayアプリケーションはWebとCLIの双方で動作します。

## 複数の単語を使ったURI

ハイフンを使い複数の単語を使ったURIのパスはキャメルケースのクラス名を使います。例えば`/wild-animal`のリクエストは`WildAnimal`クラスにアクセスされます。

## パラメーター

HTTPメソッドに対応して実行されるPHPメソッドの名前と渡される値は以下の通りです。

| HTTPメソッド | PHPメソッド | 渡される値 |
||PHP-7.4.4)を参考にしてください。

### .compile.php

実環境ではないと生成ができないクラス（例えば認証が成功しないとインジェクトが完了しないResourceObject）がある場合には、コンパイル時にのみ読み込まれるダミークラス読み込みをルートの`.compile.php`に記述することによってコンパイルをすることができます。

.compile.php
```php
<?php
require __DIR__ . '/tests/Null/AuthProvider.php'; // 常に生成可能なNullオブジェクト
$_SERVER[__REQUIRED_KEY__] = 'fake';
```

### module.dot

コンパイルをすると"dotファイル"が出力されるので[graphviz](https://graphviz.org/)で画像ファイルに変換するか、[GraphvizOnline](https://dreampuf.github.io/GraphvizOnline/)を利用すればオブジェクトグラフを表示することができます。スケルトンの[オブジェクトグラフ](/images/screen/skeleton.svg)もご覧ください。

```bash
dot -T svg module.dot > module.svg
```

## ブートストラップのパフォーマンスチューニング

[immutable_cache](https://pecl.php.net/package/immutable_cache)は、不変の値を共有メモリにキャッシュするためのPECLパッケージです。APCuをベースにしていますが、PHPのオブジェクトや配列などの不変の値を共有メモリに保存するため、APCuよりも高速です。また、APCuでもimmutable_cacheでも、PECLの[Igbinary](https://www.php.net/manual/ja/book.igbinary.php)をインストールすることでメモリ使用量が減り、さらなる高速化が期待できます。

現在、専用のキャッシュアダプターなどは用意されていません。[ImmutableBootstrap](https://github.com/koriym/BEAR.Hello/commit/507d1ee3ed514686be2d786cdaae1ba8bed63cc4)を参考に、専用のBootstrapを作成し呼び出してください。初期化コストを最小限に抑え、最大のパフォーマンスを得ることができます。

### php.ini
```ini
// エクステンション
extension="apcu.so"
extension="immutable_cache.so"
extension="igbinary.so"

// シリアライザーの指定
apc.serializer=igbinary
immutable_cache.serializer=igbinary
```



# インポート

BEARのアプリケーションは、マイクロサービスにすることなく複数のBEARアプリケーションを協調して1つのシステムにすることができます。また、他のアプリケーションからBEARのリソースを利用するのも容易です。

## composer インストール

利用するBEARアプリケーションをcomposerパッケージにしてインストールします。

composer.json
```json
{
  "require": {
    "bear/package": "^1.13",
    "my-vendor/weekday": "dev-master"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/bearsunday/tutorial1.git"
    }
  ]
}
```

`bear/package ^1.13`が必要です。

## モジュールインストール

インポートするホスト名とアプリケーション名（namespace）、コンテキストを指定して`ImportAppModule`で他のアプリケーションをインストールします。

AppModule.php
```diff
+use BEAR\Package\Module\ImportAppModule;
+use BEAR\Package\Module\Import\ImportApp;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        // ...
+        $this->install(new ImportAppModule([
+            new ImportApp('foo', 'MyVendor\Weekday', 'prod-app')
+        ]));
        $this->install(new PackageModule());
    }
}
```

`ImportAppModule`は`BEAR\Resource`ではなく`BEAR\Package`のものであることに注意してください。

## リクエスト

インポートしたリソースは指定したホスト名を指定して利用します。

```php
class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(string $name = 'BEAR.Sunday'): static
    {
        $weekday = $this->resource->get('app://foo/weekday?year=2022&month=1&day=1');
        $this->body = [
            'greeting' => 'Hello ' . $name,
            'weekday' => $weekday
        ];
        
        return $this;
    }
}
```

`#[Embed]`や`#[Link]`も同様に利用できます。

## 他のシステムから

他のフレームワークやCMSからBEARのリソースを利用するのも容易です。同じようにパッケージとしてインストールして、`Injector::getInstance`でrequireしたアプリケーションのリソースクライアントを取得してリクエストします。

```php
use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;

$resource = Injector::getInstance(
    'MyVendor\Weekday',
    'prod-api-app',
    dirname(__DIR__) . '/vendor/my-vendor/weekday'
)->getInstance(ResourceInterface::class);

$weekday = $resource->get('/weekday', ['year' => '2022', 'month' => '1', 'day' => 1]);
echo $weekday->body['weekday'] . PHP_EOL;
```

## 環境変数

環境変数はグローバルです。アプリケーション間でコンフリクトしないようにプリフィックスを付与するなどして注意する必要があります。インポートするアプリケーションは`.env`ファイルを使うのではなく、プロダクションと同じようにシェルの環境変数を取得します。

## システム境界

大きなアプリケーションを小さな複数のアプリケーションの集合体として構築できる点はマイクロサービスと同じですが、インフラストラクチャのオーバーヘッドの増加などのマイクロサービスのデメリットがありません。またモジュラーモノリスよりもコンポーネントの独立性や境界が明確です。

このページのコードは [bearsunday/example-app-import](https://github.com/bearsunday/example-import-app/commits/master) にあります。

## 多言語フレームワーク

[BEAR.Thrift](https://github.com/bearsunday/BEAR.Thrift)を使うと、Apache Thriftを使って他の言語や異なるバージョンのPHPやBEARアプリケーションからリソースにアクセスできます。[Apache Thrift](https://thrift.apache.org/)は、異なる言語間での効率的な通信を可能にするフレームワークです。



## AaaS (Application as a Service)

作成したAPIアプリケーションはWebやコンソール（バッチ）からアクセスできますが、他のPHPプロジェクトからライブラリとしてアクセスする事もできます。
このチュートリアルで作成したリポジトリは[https://github.com/bearsunday/Tutorial2.git](https://github.com/bearsunday/Tutorial2.git)にpushしてあります。

このプロジェクトをライブラリとして利用してみましょう。まず最初に新しいプロジェクトフォルダを作って`composer.json`を用意します。

```
mkdir app
cd app
mkdir -p ticket/log
mkdir ticket/tmp
```

composer.json

```json
{
    "name": "my-vendor/app",
    "description": "A BEAR.Sunday application",
    "type": "project",
    "license": "proprietary",
    "require": {
        "my-vendor/ticket": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/bearsunday/Tutorial2.git"
        }
    ]
}
```

composer installでプロジェクトがライブラリとしてインストールされます。

```
composer install
```

`Ticket API`はプロジェクトフォルダにある`.env`を読むように設定されてました。`vendor/my-vendor/app/.env`に保存出来なくもないですが、ここでは別の方法で環境変数をセットアップしましょう。

このような`app/.env`ファイルを用意します。

```bash
export TKT_DB_HOST=localhost
export TKT_DB_NAME=ticket
export TKT_DB_USER=root
export TKT_DB_PASS=''
export TKT_DB_SLAVE=''
export TKT_DB_DSN=mysql:host=${TKT_DB_HOST}\;dbname=${TKT_DB_NAME}
```

`source`コマンドで環境変数にexportすることができます。

```
source .env
```

`Ticket API`を他のプロジェクトから利用する最も簡単なスクリプトは以下のようなものです。
アプリケーション名とコンテキストを指定してアプリケーションオブジェクト`$ticket`を取得してリソースアクセスします。

```php
<?php
use BEAR\Package\Bootstrap;

require __DIR__ . '/vendor/autoload.php';

$ticket = (new Bootstrap)->getApp('MyVendor\Ticket', 'app');
$response = $ticket->resource->post('app://self/ticket',
    ['title' => 'run']
);

echo $response->code . PHP_EOL;


```

`index.php`と保存して実行してみましょう。

```
php index.php
```
```
201
```

APIを他のメソッドに渡したり、他のフレームワークなどののコンテナに格納するためには`callable`オブジェクトにします。
`$createTicket`は普通の関数のように扱うことができます。

```php
<?php
use BEAR\Package\Bootstrap;

require __DIR__ . '/vendor/autoload.php';

$ticket = (new Bootstrap)->getApp('MyVendor\Ticket', 'app');
$createTicket = $ticket->resource->post->uri('app://self/ticket');
// invoke callable object
$response = $createTicket(['title' => 'run']);
echo $response->code . PHP_EOL;
```

うまく動きましたか？しかし、このままでは`tmp`/ `log`ディレクトリは`vendor`の下のアプリが使われてしまいますね。
このようにアプリケーションのメタ情報を変更するとディレクトリの位置を変更することができます。

```php
<?php

use BEAR\AppMeta\Meta;
use BEAR\Package\Bootstrap;

require __DIR__ . '/vendor/autoload.php';

$meta = new Meta('MyVendor\Ticket', 'app');
$meta->tmpDir = __DIR__ . '/ticket/tmp';
$meta->logDir = __DIR__ . '/ticket/log';
$ticket = (new Bootstrap)->newApp($meta, 'app');
```

`Ticket API`はREST APIとしてHTTPやコンソールからアクセスできるだけでなく、BEAR.Sundayではない他のプロジェクトのライブラリとしても使えるようになりました！

----

# from tutorial1

## アプリケーションのインポート

BEAR.Sundayで作られたリソースは再利用性に優れています。複数のアプリケーションを同時に動作させ、他のアプリケーションのリソースを利用することができます。別々のWebサーバーを立てる必要はありません。

他のアプリケーションのリソースを利用して見ましょう。

通常はアプリケーションをパッケージとして利用しますが、ここではチュートリアルのために`my-vendor`に新規でアプリケーションを作成して手動でオートローダーを設定します。

```bash
mkdir my-vendor
cd my-vendor
composer create-project bear/skeleton Acme.Blog
```

`composer.json`で`autoload`のセクションに`Acme\\Blog`を追加します。

```json
"autoload": {
    "psr-4": {
        "MyVendor\\Weekday\\": "src/",
        "Acme\\Blog\\": "my-vendor/Acme.Blog/src/"
    }
},
```

`autoload`をダンプします。

```bash
composer dump-autoload
```

これで`Acme\Blog`アプリケーションが配置できました。

次にアプリケーションをインポートするために`src/Module/AppModule.php`で`ImportAppModule`を上書き(override)インストールします。

```php
<?php
// ...
use BEAR\Resource\Module\ImportAppModule; // add this line
use BEAR\Resource\ImportApp; // add this line
use BEAR\Package\Context; // add this line

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $importConfig = [
            new ImportApp('blog', 'Acme\Blog', 'prod-hal-app') // host, name, context
        ];
        $this->override(new ImportAppModule($importConfig , Context::class));
    }
}
```

これは`Acme\Blog`アプリケーションを`prod-hal-app`コンテキストで作成したリソースを`blog`というホストで使用することができます。

`src/Resource/App/Import.php`にImportリソースを作成して確かめてみましょう。

```php
<?php
namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Import extends ResourceObject
{
    use ResourceInject;

    public function onGet()
    {
        $this->body =[
            'blog' => $this->resource->uri('page://blog/index')['greeting']
        ];

        return $this;
    }
}
```

`page://blog/index`リソースの`greeting`が`blog`に代入されているはずです。`@Embed`も同様に使えます。

```bash
php bin/app.php get /import
```

```bash
200 OK
content-type: application/hal+json

{
    "blog": "Hello BEAR.Sunday",
    "_links": {
        "self": {
            "href": "/import"
        }
    }
}
```

他のアプリケーションのリソースを利用することができました！データ取得をHTTP越しにする必要もありません。

合成されたアプリケーションも他からみたら１つのアプリケーションの１つのレイヤーです。
[レイヤードシステム](http://en.wikipedia.org/wiki/Representational_state_transfer#Layered_system)はRESTの特徴の１つです。

それでは最後に作成したアプリケーションのリソースを呼び出す最小限のスクリプトをコーディングして見ましょう。`bin/test.php`を作成します。


```php?start_inline
use BEAR\Package\Bootstrap;

require dirname(__DIR__) . '/autoload.php';

$api = (new Bootstrap)->getApp('MyVendor\Weekday', 'prod-hal-app');

$blog = $api->resource->uri('app://self/import')['blog'];
var_dump($blog);
```

`MyVendor\Weekday`アプリを`prod-hal-app`で起動して`app://self/import`リソースの`blog`をvar_dumpするコードです。

試して見ましょう。

```
php bin/import.php
```
```
string(17) "Hello BEAR.Sunday"
```

他にも

```php?start_inline
$weekday = $api->resource->uri('app://self/weekday')(['year' => 2000, 'month'=>1, 'day'=>1]);
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

```php?start_inline
$html = (new Bootstrap)->getApp('MyVendor\Weekday', 'prod-html-app');
$index = $html->resource->uri('page://self/index')(['year' => 2000, 'month'=>1, 'day'=>1]);
var_dump($index->code);
//int(200)

echo $index;
//<!DOCTYPE html>
//<html>
//<body>
//The weekday of 2000/1/1 is Sat.
//</body>
//</html>
```

ステートレスなリクエストでレスポンスが返ってくるRESTのリソースはPHPの関数のようなものです。`body`で値を取得したり`(string)`でJSONやHTMLなどの表現にすることができます。autoloadの部分を除けば二行、連結すればたった一行のスクリプトで  アプリケーションのどのリソースでも操作することができます。

このようにBEAR.Sundayで作られたリソースは他のCMSやフレームワークからも簡単に利用することができます。複数のアプリケーションの値を一度に扱うことができます。




# データベース

データベースの利用のために、問題解決方法の異なった以下のモジュールが用意されています。いずれも[PDO](https://www.php.net/manual/ja/intro.pdo.php)をベースにしたSQLのための独立ライブラリです。

* PDOをextendしたExtendedPdo ([Aura.sql](https://github.com/auraphp/Aura.Sql))
* クエリービルダー ([Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery))
* PHPのインターフェイスとSQL実行を束縛 ([Ray.MediaQuery](database_media.html))

静的なSQLはファイルにすると[^locater]、管理や他のSQLツールでの検証などの使い勝手もよくなります。Aura.SqlQueryは動的にクエリーを組み立てることができますが、その他は基本静的なSQLの実行のためのライブラリです。また、Ray.MediaQueryではSQLの一部をビルダーで組み立てたものに入れ替えることもできます。

[^locater]: [query-locater](https://github.com/koriym/Koriym.QueryLocator)はSQLをファイルとして扱うライブラリです。Aura.Sqlと共に使うと便利です。

## モジュール

必要なライブラリに応じたモジュールをインストールします。

* [Ray.AuraSqlModule](database_aura.html)
* [Ray.MediaQuery](database_media.html)

`Ray.AuraSqlModule`はAura.SqlとAura.SqlQueryを含みます。

`Ray.MediaQuery`はユーザーが用意したインターフェイスとSQLから、SQL実行オブジェクトを生成しインジェクトする[^doma]高機能なDBアクセスフレームワークです。

[^doma]: JavaのDBアクセスフレームワーク[Doma](https://doma.readthedocs.io/en/latest/basic/#examples)と仕組みが似ています。

## その他

* [DBAL](database_dbal.html)
* [CakeDb](database_cake.html)
* [Ray.QueryModule](https://github.com/ray-di/Ray.QueryModule/blob/1.x/README.ja.md)

`DBAL`はDoctrine、`CakeDB`はCakePHPのDBライブラリです。`Ray.QueryModule`はRay.MediaQueryの以前のライブラリでSQLを無名関数に変換します。



# Ray.AuraSqlModule

`Ray.AuraSqlModule`はPDO拡張のAura.SqlとクエリビルダーAura.SqlQuery、その他にデータベースクエリー結果のページネーションのためのライブラリを提供します。

## インストール

composerで`ray/aura-sql-module`をインストールします。

```bash
composer require ray/aura-sql-module
```

アプリケーションモジュール`src/Module/AppModule.php`で`AuraSqlModule`をインストールします。

```php
use BEAR\Package\AbstractAppModule;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\AuraSqlModule\AuraSqlModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        $this->install(
            new AuraSqlModule(
                'mysql:host=localhost;dbname=test',  // または getenv('PDO_DSN')
                'username',
                'password'
            )
        );
        $this->install(new PackageModule);
    }
}
```

設定時に直接値を指定するのではなく、実行時に毎回環境変数から取得するためには`AuraSqlEnvModule`を使います。接続先と認証情報の値を直接指定する代わりに、該当する環境変数のキーを渡します。

```php
$this->install(
    new AuraSqlEnvModule(
        'PDO_DSN',      // getenv('PDO_DSN')
        'PDO_USER',     // getenv('PDO_USER')
        'PDO_PASSWORD', // getenv('PDO_PASSWORD')
        'PDO_SLAVE',    // getenv('PDO_SLAVE')
        $options,       // optional key=>value array of driver-specific connection options
        $queries        // Queries to execute after the connection.
    )
);
```

## Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPHPのPDOを拡張したデータベースライブラリです。コンストラクタインジェクションや`AuraSqlInject`トレイトを利用して`PDO`を拡張したDBオブジェクト`ExtendedPDO`を受け取ります。

```php
use Aura\Sql\ExtendedPdoInterface;

class Index
{
    public function __construct(
        private readonly ExtendedPdoInterface $pdo
    ) {}
}
```

```php
use Ray\AuraSqlModule\AuraSqlInject;

class Index
{
    use AuraSqlInject;
    
    public function onGet()
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```

`Ray.AuraSqlModule`は[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)を含んでいてMySQLやPostgresなどのSQLを組み立てるのに利用できます。

### perform() メソッド

`perform()`メソッドは、1つのプレイスホルダーしかないSQLに配列の値をバインドすることができます。

```php
$stm = 'SELECT * FROM test WHERE foo IN (:foo)';
$array = ['foo', 'bar', 'baz'];
```

既存のPDOの場合：

```php
// the native PDO way does not work (PHP Notice: Array to string conversion)
// ネイティブのPDOでは`:foo`に配列を指定することはできません
$sth = $pdo->prepare($stm);
$sth->bindValue('foo', $array);
```

Aura.SqlのExtendedPDOの場合：

```php
$stm = 'SELECT * FROM test WHERE foo IN (:foo)';
$values = ['foo' => ['foo', 'bar', 'baz']];
$sth = $pdo->perform($stm, $values);
```

`:foo`に`['foo', 'bar', 'baz']`がバインドされます。`queryString`で実際のクエリーを調べることができます。

```php
echo $sth->queryString;
// the query string has been modified by ExtendedPdo to become
// "SELECT * FROM test WHERE foo IN ('foo', 'bar', 'baz')"
```

### fetch*() メソッド

`prepare()`、`bindValue()`、`execute()`を繰り返してデータベースから値を取得する代わりに`fetch*()`メソッドを使うとボイラープレートコードを減らすことができます。（内部では`perform()`メソッドを実行しているので配列のプレイスホルダーもサポートしています）

```php
$stm = 'SELECT * FROM test WHERE foo = :foo AND bar = :bar';
$bind = ['foo' => 'baz', 'bar' => 'dib'];

// ネイティブのPDOで"fetch all"を行う場合
$pdo = new PDO(...);
$sth = $pdo->prepare($stm);
$sth->execute($bind);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

// ExtendedPdoで"fetch all"を行う場合
$pdo = new ExtendedPdo(...);
$result = $pdo->fetchAll($stm, $bind);

// fetchAssoc()は全ての行がコラム名のキーを持つ連想配列が返ります。
$result = $pdo->fetchAssoc($stm, $bind);

// fetchGroup()はfetchAssoc()のような動作ですが、値は配列にラップされません。
// 代わりに、単一カラムの値は1次元配列として、
// 複数カラムは配列の配列として返されます。
// 値が配列の場合（つまり、SELECTに2つ以上のカラムがある場合）は、
// スタイルをPDO::FETCH_NAMEDに設定します。
$result = $pdo->fetchGroup($stm, $bind, $style = PDO::FETCH_COLUMN);

// fetchOne()は最初の行をキーをコラム名にした連想配列で返します。
$result = $pdo->fetchOne($stm, $bind);

// fetchPairs()は最初の列の値をキーに二番目の列の値を値にした連想配列を返します  
$result = $pdo->fetchPairs($stm, $bind);

// fetchValue()は最初の列の値を返します。
$result = $pdo->fetchValue($stm, $bind);

// fetchAffected()は影響を受けた行数を返します。
$stm = "UPDATE test SET incr = incr + 1 WHERE foo = :foo AND bar = :bar";
$row_count = $pdo->fetchAffected($stm, $bind);
```

`fetchAll()`、`fetchAssoc()`、`fetchCol()`、および`fetchPairs()`のメソッドは、三番目のオプションの引数にそれぞれの列に適用されるコールバックを指定することができます。

```php
$result = $pdo->fetchAssoc($stm, $bind, function (&$row) {
    // 行にカラムを追加
    $row['my_new_col'] = 'Added this column from the callable.';
});
```

### yield*() メソッド

メモリを節約するために`yield*()`メソッドを使うことができます。`fetch*()`メソッドは全ての行を一度に取得しますが、`yield*()`メソッドはイテレーターを返します。

```php
$stm = 'SELECT * FROM test WHERE foo = :foo AND bar = :bar';
$bind = ['foo' => 'baz', 'bar' => 'dib'];

// fetchAll()のように行は連想配列です
foreach ($pdo->yieldAll($stm, $bind) as $row) {
    // ...
}

// fetchAssoc()のようにキーが最初の列名で行が連想配列です。
foreach ($pdo->yieldAssoc($stm, $bind) as $key => $row) {
    // ...
}

// fetchCol()のように最初の列が値になった値を返します。
foreach ($pdo->yieldCol($stm, $bind) as $val) {
    // ...
}

// fetchPairs()と同様に最初の列からキー/バリューのペアの値を返します。
foreach ($pdo->yieldPairs($stm, $bind) as $key => $val) {
    // ...
}
```

## リプリケーション

マスター／スレーブ構成のデータベース接続を行うためには4つ目の引数にスレーブDBのホストを指定します。

```php
$this->install(
    new AuraSqlModule(
        'mysql:host=localhost;dbname=test',
        'username',
        'password',
        'slave1,slave2' // スレーブのホストをカンマ区切りで指定
    )
);
```

これでHTTPリクエストがGETの時はスレーブDB、その他のメソッドの時はマスターDBのDBオブジェクトがコンストラクタに渡されます。

```php
use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;
use PDO;

class User extends ResourceObject
{
    public $pdo;
    
    public function __construct(ExtendedPdoInterface $pdo)
    {
        $this->pdo = $pdo;
    }
    
    public function onGet()
    {
        $this->pdo; // slave db
    }
    
    public function onPost($todo)
    {
        $this->pdo; // master db
    }
}
```

`#[ReadOnlyConnection]`、`#[WriteConnection]`でアノテートされたメソッドはメソッド名に関わらず、呼ばれた時にアノテーションに応じたDBオブジェクトが`$this->pdo`に上書きされます。

```php
use Ray\AuraSqlModule\Annotation\ReadOnlyConnection;  // important
use Ray\AuraSqlModule\Annotation\WriteConnection;     // important

class User
{
    public $pdo; // #[ReadOnlyConnection]や#[WriteConnection]のメソッドが呼ばれた時に上書きされる
    
    public function onPost($todo)
    {
        $this->read();
    }
    
    #[ReadOnlyConnection]
    public function read()
    {
        $this->pdo; // slave db
    }
    
    #[WriteConnection]
    public function write()
    {
        $this->pdo; // master db
    }
}
```

## 複数データベースの接続

接続先の異なるデータベースのPDOインスタンスをインジェクトするには識別子[^qualifier]をつけます。

```php
public function __construct(
    private readonly #[Log] ExtendedPdoInterface $logDb,
    private readonly #[Mail] ExtendedPdoInterface $mailDb,
) {}
```

[^qualifier]: 識別子（クオリファイアー）についてはRay.Diのマニュアルの[束縛アトリビュート](https://ray-di.github.io/manuals/1.0/ja/binding_attributes.html)をご覧ください。

`NamedPdoModule`でその識別子と接続情報を指定してインストールします。

```php
class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new NamedPdoModule(Log::class, 'mysql:host=localhost;dbname=log', 'username'));
        $this->install(new NamedPdoModule(Mail::class, 'mysql:host=localhost;dbname=mail', 'username'));
    }
}
```

接続情報を環境変数から都度取得するときは`NamedPdoEnvModule`を使います。

```php
class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new NamedPdoEnvModule(Log::class, 'LOG_DSN', 'LOG_USERNAME'));
        $this->install(new NamedPdoEnvModule(Mail::class, 'MAIL_DSN', 'MAIL_USERNAME'));
    }
}
```

## トランザクション

`#[Transactional]`アトリビュートを追加したメソッドはトランザクション管理されます。

```php
use Ray\AuraSqlModule\Annotation\Transactional;

#[Transactional]
public function write()
{
    // 例外発生したら\Ray\AuraSqlModule\Exception\RollbackExceptionになります
}
```

複数接続したデータベースのトランザクションを行うためには`#[Transactional]`アトリビュートにプロパティを指定します。指定しない場合は`{"pdo"}`になります。

```php
#[Transactional({"pdo", "userDb"})]
public function write()
{
    // ...
}
```

以下のように実行されます。

```php
$this->pdo->beginTransaction();
$this->userDb->beginTransaction();
// ...
$this->pdo->commit();
$this->userDb->commit();
```

## Aura.SqlQuery

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPDOを拡張したライブラリですが、[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)はMySQL、Postgres、SQLiteあるいはMicrosoft SQL Serverといったデータベース固有のSQLのビルダーを提供します。

データベースを指定してアプリケーションモジュール`src/Module/AppModule.php`でインストールします。

```php
// ...
$this->install(new AuraSqlQueryModule('mysql')); // pgsql, sqlite, or sqlsrv
```

### SELECT

リソースではDBクエリービルダオブジェクトを受け取り、下記のメソッドを使ってSELECTクエリーを組み立てます。メソッドに特定の順番はなく複数回呼ぶこともできます。

```php
use Aura\Sql\ExtendedPdoInterface;
use Aura\SqlQuery\Common\SelectInterface;

class User extends ResourceObject
{
    public function __construct(
        private readonly ExtendedPdoInterface $pdo,
        private readonly SelectInterface $select
    ) {}

    public function onGet()
    {
        $this->select
            ->distinct()                    // SELECT DISTINCT
            ->cols([                        // select these columns
                'id',                       // column name
                'name AS namecol',          // one way of aliasing
                'col_name' => 'col_alias',  // another way of aliasing
                'COUNT(foo) AS foo_count'   // embed calculations directly
            ])
            ->from('foo AS f')              // FROM these tables
            ->fromSubselect(                // FROM sub-select AS my_sub
                'SELECT ...',
                'my_sub'
            )
            ->join(                         // JOIN ...
                'LEFT',                     // left/inner/natural/etc
                'doom AS d'                 // this table name
                'foo.id = d.foo_id'         // ON these conditions
            )
            ->joinSubSelect(                // JOIN to a sub-select
                'INNER',                    // left/inner/natural/etc
                'SELECT ...',               // the subselect to join on
                'subjoin'                   // AS this name
                'sub.id = foo.id'           // ON these conditions
            )
            ->where('bar > :bar')           // AND WHERE these conditions
            ->where('zim = ?', 'zim_val')   // bind 'zim_val' to the ? placeholder
            ->orWhere('baz < :baz')         // OR WHERE these conditions
            ->groupBy(['dib'])              // GROUP BY these columns
            ->having('foo = :foo')          // AND HAVING these conditions
            ->having('bar > ?', 'bar_val')  // bind 'bar_val' to the ? placeholder
            ->orHaving('baz < :baz')        // OR HAVING these conditions
            ->orderBy(['baz'])              // ORDER BY these columns
            ->limit(10)                     // LIMIT 10
            ->offset(40)                    // OFFSET 40
            ->forUpdate()                   // FOR UPDATE
            ->union()                       // UNION with a followup SELECT
            ->unionAll()                    // UNION ALL with a followup SELECT
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to named placeholders
                'bar' => 'bar_val',
                'baz' => 'baz_val',
            ]);

        $sth = $this->pdo->prepare($this->select->getStatement());
        
        // bind the values and execute
        $sth->execute($this->select->getBindValues());
        
        $result = $sth->fetch(\PDO::FETCH_ASSOC);
        
        // または
        // $result = $this->pdo->fetchAssoc($stm, $bind);
    }
}
```

組み立てたクエリーは`getStatement()`で文字列にしてクエリーを行います。

### INSERT

#### 単一行のINSERT

```php
class User extends ResourceObject
{
    public function __construct(
        private readonly ExtendedPdoInterface $pdo,
        private readonly InsertInterface $insert
    ) {}

    public function onPost()
    {
        $this->insert
            ->into('foo')                   // INTO this table
            ->cols([                        // bind values as "(col) VALUES (:col)"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')           // raw value as "(ts) VALUES (NOW())"
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values
                'bar' => 'foo',
                'baz' => 'zim',
            ]);

        $sth = $this->pdo->prepare($this->insert->getStatement());
        $sth->execute($this->insert->getBindValues());
        
        // または
        // $sth = $this->pdo->perform($this->insert->getStatement(), $this->insert->getBindValues());
        
        // get the last insert ID
        $name = $this->insert->getLastInsertIdName('id');
        $id = $this->pdo->lastInsertId($name);
    }
}
```

`cols()`メソッドはキーがコラム名、値をバインドする値にした連想配列を渡すこともできます。

```php
$this->insert
    ->into('foo')                   // insert into this table
    ->cols([                        // insert these columns and bind these values
        'foo' => 'foo_value',
        'bar' => 'bar_value',
        'baz' => 'baz_value',
    ]);
```

#### 複数行のINSERT

複数の行のINSERTを行うためには、最初の行の最後で`addRow()`メソッドを使います。その後に次のクエリーを組み立てます。

```php
// テーブルの指定
$this->insert->into('foo');

// 1行目のセットアップ
$this->insert->cols([
    'bar' => 'bar-0',
    'baz' => 'baz-0'
]);
$this->insert->set('ts', 'NOW()');

// 2行目のセットアップ
// ここでのカラムの順序は1行目と異なりますが、問題ありません。
// INSERTオブジェクトが最初の行と同じ順序で構築します。
$this->insert->addRow();
$this->insert->set('ts', 'NOW()');
$this->insert->cols([
    'bar' => 'bar-1',
    'baz' => 'baz-1'
]);

// さらに行を追加...
$this->insert->addRow();
// ...

// 全ての行を一度にインサート
$sth = $this->pdo->prepare($insert->getStatement());
$sth->execute($insert->getBindValues());
```

> 注: 最初の行で初めて現れた列の値を指定しないで行を追加しようとすると例外が投げられます。
> `addRow()`に列の連想配列を渡すと次の行で使われます。つまり最初の行で`col()`や`cols()`を指定しないこともできます。

```php
// 1行目のセットアップ
$insert->addRow([
    'bar' => 'bar-0',
    'baz' => 'baz-0'
]);
$insert->set('ts', 'NOW()');

// 2行目のセットアップ
$insert->addRow([
    'bar' => 'bar-1',
    'baz' => 'baz-1'
]);
$insert->set('ts', 'NOW()');
// など
```

`addRows()`を使って複数の行を一度にセットすることもできます。

```php
$rows = [
    [
        'bar' => 'bar-0',
        'baz' => 'baz-0'
    ],
    [
        'bar' => 'bar-1',
        'baz' => 'baz-1'
    ],
];
$this->insert->addRows($rows);
```

### UPDATE

下記のメソッドを使ってUPDATEクエリーを組み立てます。メソッドに特定の順番はなく複数回呼ぶこともできます。

```php
$this->update
    ->table('foo')                  // update this table
    ->cols([                        // bind values as "SET bar = :bar"
        'bar',
        'baz',
    ])
    ->set('ts', 'NOW()')           // raw value as "SET ts = NOW()"
    ->where('zim = :zim')          // AND WHERE these conditions
    ->where('gir = ?', 'doom')     // bind this value to the condition
    ->orWhere('gir = :gir')        // OR WHERE these conditions
    ->bindValue('bar', 'bar_val')  // bind one value to a placeholder
    ->bindValues([                 // bind these values to the query
        'baz' => 99,
        'zim' => 'dib',
        'gir' => 'doom',
    ]);

$sth = $this->pdo->prepare($this->update->getStatement());
$sth->execute($this->update->getBindValues());

// または
// $sth = $this->pdo->perform($this->update->getStatement(), $this->update->getBindValues());
```

キーを列名、値をバインドされた値（RAW値ではありません）にした連想配列を`cols()`に渡すこともできます。

```php
$this->update
    ->table('foo')          // update this table
    ->cols([                // update these columns and bind these values
        'foo' => 'foo_value',
        'bar' => 'bar_value',
        'baz' => 'baz_value',
    ]);
```

### DELETE

下記のメソッドを使ってDELETEクエリーを組み立てます。メソッドに特定の順番はなく複数回呼ぶこともできます。

```php
$this->delete
    ->from('foo')                   // FROM this table
    ->where('zim = :zim')          // AND WHERE these conditions
    ->where('gir = ?', 'doom')     // bind this value to the condition
    ->orWhere('gir = :gir')        // OR WHERE these conditions
    ->bindValue('bar', 'bar_val')  // bind one value to a placeholder
    ->bindValues([                 // bind these values to the query
        'baz' => 99,
        'zim' => 'dib',
        'gir' => 'doom',
    ]);

$sth = $this->pdo->prepare($update->getStatement());
$sth->execute($this->delete->getBindValues());
```

### パジネーション

[ray/aura-sql-module](https://packagist.org/packages/ray/aura-sql-module)はRay.Sqlの生SQL、Ray.AuraSqlQueryのクエリービルダー双方でパジネーション（ページ分割）をサポートしています。

バインドする値と1ページあたりのアイテム数、それに`{page}`をページ番号にしたuri_templateでページャーファクトリーを`newInstance()`で生成して、ページ番号で配列アクセスします。

#### Aura.Sql用AuraSqlPagerFactoryInterface

```php
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlPagerFactoryInterface */
$pager = $factory->newInstance(
    $pdo,
    $sql,
    $params,
    10,                                     // 10 items per page
    '/?page={page}&category=sports'
);
$page = $pager[2];                          // page 2

/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
// $page->data             // sliced data (array|\Traversable)
// $page->current;         // (int)
// $page->total           // (int)
// $page->hasNext         // (bool)
// $page->hasPrevious     // (bool)
// $page->maxPerPage;     // (int)
// (string) $page         // pager html (string)
```

#### Aura.SqlQuery用AuraSqlQueryPagerFactoryInterface

```php
// for Select
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlQueryPagerFactoryInterface */
$pager = $factory->newInstance(
    $pdo,
    $select,
    10,
    '/?page={page}&category=sports'
);
$page = $pager[2]; // page 2

/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
```

> 注：Aura.Sqlは生SQLを直接編集していますが、現在MySql形式のLIMIT句しか対応していません。

`$page`はイテレータブルです。

```php
foreach ($page as $row) {
    // 各行の処理
}
```

ページャーのリンクHTMLのテンプレートを変更するには`TemplateInterface`の束縛を変更します。テンプレート詳細に関しては[Pagerfanta](https://github.com/whiteoctober/Pagerfanta#views)をご覧ください。

```php
use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap3Template;
use Ray\AuraSqlModule\Annotation\PagerViewOption;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->bind(TemplateInterface::class)->to(TwitterBootstrap3Template::class);
        $this->bind()->annotatedWith(PagerViewOption::class)->toInstance($pagerViewOption);
    }
}
```



# CakeDb

**CakeDb**はアクティブレコードとデータマッパーパターンのアイデアを使ったORMで、素早くシンプルにORMを使うことができます。CakePHP3で提供されているORMと同じものです。

composerで`Ray.CakeDbModule`をインストールします。

```bash
composer require ray/cake-database-module ~1.0
```

インストールの方法については[Ray.CakeDbModule](https://github.com/ray-di/Ray.CakeDbModule)を、ORMの利用には[CakePHP3 Database Access & ORM](http://book.cakephp.org/3.0/en/orm.html)をご覧ください。

Ray.CakeDbModuleはCakePHP3のORMを開発したJose([@lorenzo](https://github.com/lorenzo))さんにより提供されています。



# Doctrine DBAL

[Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html)はDoctrineが提供しているデータベースの抽象化レイヤーです。

composerで`Ray.DbalModule`をインストールします。

```bash
composer require ray/dbal-module
```

アプリケーションモジュールで`DbalModule`をインストールします。

```php
use Ray\DbalModule\DbalModule;
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new DbalModule('driver=pdo_sqlite&memory=true'));
    }
}
```

これでDIの設定が整いました。`DbalInject`トレイトを利用すると`$this->db`にDBオブジェクトがインジェクトされます。

```php
use Ray\DbalModule\DbalInject;

class Index
{
    use DbalInject;
    
    public function onGet()
    {
        return $this->db; // \Doctrine\DBAL\Driver\Connection
    }
}
```

### 複数DB

複数のデータベースの接続には二番目の引数に識別子を指定します。

```php
$this->install(new DbalModule($logDsn, 'log_db'));
$this->install(new DbalModule($jobDsn, 'job_db'));
```

```php
/**
 * @Inject
 * @Named("log_db")
 */
public function setLogDb(Connection $logDb)
```

[MasterSlaveConnection](http://www.doctrine-project.org/api/dbal/2.0/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html)というリプリケーションのためのマスター／スレーブ接続が標準で用意されています。



# Ray.MediaQuery

`Ray.MediaQuery`はDBやWeb APIなどの外部メディアのクエリーのインターフェイスから、クエリー実行オブジェクトを生成しインジェクトします。

* ドメイン層とインフラ層の境界を明確にします。
* ボイラープレートコードを削減します。
* 外部メディアの実体には無関係なので、後からストレージを変更することができます。並列開発やスタブ作成が容易です。

## インストール

```bash
$ composer require ray/media-query
```

## 利用方法

メディアアクセスするインターフェイスを定義します。

### データベースの場合

`DbQuery`アトリビュートでSQLのIDを指定します。

```php
interface TodoAddInterface
{
    #[DbQuery('user_add')]
    public function add(string $id, string $title): void;
}
```

### Web APIの場合

`WebQuery`アトリビュートでWeb APIのIDを指定します。

```php
interface PostItemInterface
{
    #[WebQuery('user_item')]
    public function get(string $id): array;
}
```

APIパスリストのファイルを`media_query.json`として作成します。

```json
{
    "$schema": "https://ray-di.github.io/Ray.MediaQuery/schema/web_query.json",
    "webQuery": [
        {
            "id": "user_item",
            "method": "GET",
            "path": "https://{domain}/users/{id}"
        }
    ]
}
```

MediaQueryModuleは、`DbQueryConfig`や`WebQueryConfig`、またはその両方の設定でSQLやWeb APIリクエストの実行をインターフェイスに束縛します。

```php
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\MediaQuery\ApiDomainModule;
use Ray\MediaQuery\DbQueryConfig;
use Ray\MediaQuery\MediaQueryModule;
use Ray\MediaQuery\Queries;
use Ray\MediaQuery\WebQueryConfig;

protected function configure(): void
{
    $this->install(
        new MediaQueryModule(
            Queries::fromDir('/path/to/queryInterface'),
            [
                new DbQueryConfig('/path/to/sql'),
                new WebQueryConfig('/path/to/web_query.json', ['domain' => 'api.example.com'])
            ],
        ),
    );
    $this->install(new AuraSqlModule(
        'mysql:host=localhost;dbname=test',
        'username',
        'password'
    ));
}
```

MediaQueryModuleはAuraSqlModuleのインストールが必要です。

### 注入

インターフェイスからオブジェクトが直接生成され、インジェクトされます。実装クラスのコーディングが不要です。

```php
class Todo
{
    public function __construct(
        private TodoAddInterface $todoAdd
    ) {}

    public function add(string $id, string $title): void
    {
        $this->todoAdd->add($id, $title);
    }
}
```

### DbQuery

SQL実行がメソッドにマップされ、IDで指定されたSQLをメソッドの引数でバインドして実行します。例えばIDが`todo_item`の指定では`todo_item.sql`SQL文に`['id => $id]`をバインドして実行します。

* `$sqlDir`ディレクトリにSQLファイルを用意します。
* SQLファイルには複数のSQL文が記述できます。最後の行のSELECTが返り値になります。

#### Entity

SQL実行結果を用意したエンティティクラスを`entity`で指定して変換（hydrate）することができます。

```php
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class)]
    public function getItem(string $id): Todo;
}
```

```php
final class Todo
{
    public string $id;
    public string $title;
}
```

プロパティをキャメルケースに変換する場合には`CamelCaseTrait`を使います。

```php
use Ray\MediaQuery\CamelCaseTrait;

class Invoice
{
    use CamelCaseTrait;
    public $userName;
}
```

コンストラクタがあると、フェッチしたデータでコールされます。

```php
final class Todo
{
    public function __construct(
        public string $id,
        public string $title
    ) {}
}
```

#### type: 'row'

SQL実行の戻り値が単一行なら`type: 'row'`のアトリビュートを指定します。ただし、インターフェイスの戻り値がエンティティクラスなら省略することができます。

```php
/** 返り値がEntityの場合 */
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class)]
    public function getItem(string $id): Todo;
}
```

```php
/** 返り値がarrayの場合 */
interface TodoItemInterface
{
    #[DbQuery('todo_item', entity: Todo::class, type: 'row')]
    public function getItem(string $id): array;
}
```

### Web API

* メソッドの引数が `uri`で指定されたURI templateにバインドされ、Web APIリクエストオブジェクトが生成されます。
* 認証のためのヘッダーなどのカスタムはGuzzleの`ClinetInterface`をバインドして行います。

```php
$this->bind(ClientInterface::class)->toProvider(YourGuzzleClientProvider::class);
```

## パラメーター

### 日付時刻

パラメーターにバリューオブジェクトを渡すことができます。例えば、`DateTimeInterface`オブジェクトをこのように指定できます。

```php
interface TaskAddInterface
{
    #[DbQuery('task_add')]
    public function __invoke(string $title, DateTimeInterface $createdAt = null): void;
}
```

値はSQL実行時やWeb APIリクエスト時に日付フォーマットされた文字列に変換されます。

```sql
INSERT INTO task (title, created_at) VALUES (:title, :createdAt); # 2021-2-14 00:00:00
```

値を渡さないとバインドされている現在時刻がインジェクションされます。SQL内部で`NOW()`とハードコーディングする事や、毎回現在時刻を渡す手間を省きます。

### テスト時刻

テストの時には以下のように`DateTimeInterface`の束縛を1つの時刻にすることもできます。

```php
$this->bind(DateTimeInterface::class)->to(UnixEpochTime::class);
```

### バリューオブジェクト（VO）

`DateTime`以外のバリューオブジェクトが渡されると`ToScalarInterface`を実装した`toScalar()`メソッド、もしくは`__toString()`メソッドの返り値が引数になります。

```php
interface MemoAddInterface
{
    public function __invoke(string $memo, UserId $userId = null): void;
}
```

```php
class UserId implements ToScalarInterface
{
    public function __construct(
        private readonly LoginUser $user
    ) {}
    
    public function toScalar(): int
    {
        return $this->user->id;
    }
}
```

```sql
INSERT INTO memo (user_id, memo) VALUES (:user_id, :memo);
```

### パラメーターインジェクション

バリューオブジェクトの引数のデフォルトの値の`null`がSQLやWebリクエストで使われることはないことに注意してください。値が渡されないと、nullの代わりにパラメーターの型でインジェクトされたバリューオブジェクトのスカラー値が使われます。

```php
public function __invoke(Uuid $uuid = null): void; // UUIDが生成され渡される
```

## ページネーション

DBの場合、`#[Pager]`アトリビュートでSELECTクエリーをページングすることができます。

```php
use Ray\MediaQuery\PagesInterface;

interface TodoList
{
    #[DbQuery, Pager(perPage: 10, template: '/{?page}')]
    public function __invoke(): PagesInterface;
}
```

`count()`で件数が取得でき、ページ番号で配列アクセスをするとページオブジェクトが取得できます。`Pages`はSQL遅延実行オブジェクトです。

```php
$pages = ($todoList)();
$cnt = count($pages);    // count()をした時にカウントSQLが生成されクエリーが行われます。
$page = $pages[2];       // 配列アクセスをした時にそのページのDBクエリーが行われます。

// $page->data           // sliced data
// $page->current;       // 現在のページ番号
// $page->total          // 総件数
// $page->hasNext        // 次ページの有無
// $page->hasPrevious    // 前ページの有無
// $page->maxPerPage;    // 1ページあたりの最大件数
// (string) $page        // ページャーHTML
```

## SqlQuery

`SqlQuery`はSQLファイルのIDを指定してSQLを実行します。実装クラスを用意して詳細な実装を行う時に使用します。

```php
class TodoItem implements TodoItemInterface
{
    public function __construct(
        private SqlQueryInterface $sqlQuery
    ) {}

    public function __invoke(string $id): array
    {
        return $this->sqlQuery->getRow('todo_item', ['id' => $id]);
    }
}
```

## get* メソッド

SELECT結果を取得するためには取得する結果に応じた`get*`を使います。

```php
$sqlQuery->getRow($queryId, $params);        // 結果が単数行
$sqlQuery->getRowList($queryId, $params);    // 結果が複数行
$statement = $sqlQuery->getStatement();       // PDO Statementを取得
$pages = $sqlQuery->getPages();              // ページャーを取得
```

Ray.MediaQueryは[Ray.AuraSqlModule](https://github.com/ray-di/Ray.AuraSqlModule)を含んでいます。さらに低レイヤーの操作が必要な時はAura.Sqlの[Query Builder](https://github.com/ray-di/Ray.AuraSqlModule#query-builder)やPDOを拡張した[Aura.Sql](https://github.com/auraphp/Aura.Sql)のExtended PDOをお使いください。[doctrine/dbal](https://github.com/ray-di/Ray.DbalModule)も利用できます。

パラメーターインジェクションと同様、`DateTimeInterface`オブジェクトを渡すと日付フォーマットされた文字列に変換されます。

```php
$sqlQuery->exec('memo_add', [
    'memo' => 'run',
    'created_at' => new DateTime()
]);
```

他のオブジェクトが渡されると`toScalar()`または`__toString()`の値に変換されます。

## プロファイラー

メディアアクセスはロガーで記録されます。標準ではテストに使うメモリロガーがバインドされています。

```php
public function testAdd(): void
{
    $this->sqlQuery->exec('todo_add', $todoRun);
    $this->assertStringContainsString(
        'query: todo_add({"id":"1","title":"run"})',
        (string) $this->log
    );
}
```

独自の[MediaQueryLoggerInterface](src/MediaQueryLoggerInterface.php)を実装して、各メディアクエリーのベンチマークを行ったり、インジェクトしたPSRロガーでログをすることもできます。

## アノテーション / アトリビュート

属性を表すのに[doctrineアノテーション](https://github.com/doctrine/annotations/)、[アトリビュート](https://www.php.net/manual/ja/language.attributes.overview.php)どちらも利用できます。次の2つは同じものです。

```php
use Ray\MediaQuery\Annotation\DbQuery;

#[DbQuery('user_add')]
public function add1(string $id, string $title): void;

/** @DbQuery("user_add") */
public function add2(string $id, string $title): void;
```


# データベース

データベースライブラリの利用のため`Aura.Sql`、`Doctrine DBAL`, `CakeDB`などのモジュールが用意されています。

## Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPHPのPDOを拡張したデータベースライブラリです。

### インストール

composerで`Ray.AuraSqlModule`をインストールします。

```bash
composer require ray/aura-sql-module
```

アプリケーションモジュール`src/Module/AppModule.php`で`AuraSqlModule`をインストールします。

```php?start_inline
use BEAR\Package\AbstractAppModule;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\AuraSqlModule\AuraSqlModule; // この行を追加

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(
          new AuraSqlModule(
            'mysql:host=localhost;dbname=test',
            'username',
            'password',
            // $options,
            // $attributes
          )
        );  // この行を追加
        $this->install(new PackageModule));
    }
}
```

これでDIの設定が整いました。コンストラクタや`AuraSqlInject`トレイトを利用して`PDO`を拡張したDBオブジェクト`ExtendedPDO`を受け取ります。

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;

class Index
{
    public function __construct(ExtendedPdoInterface $pdo)
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;

class Index
{
    use AuraSqlInject;

    public function onGet()
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
```

`Ray.AuraSqlModule`は[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)を含んでいてMySQLやPostgresなどのSQLを組み立てるのに利用できます。

### perform() メソッド

`perform()`メソッドは、1つのプレイスホルダーしかないSQLに配列の値をバインドすることが出来ます。

```php?start_inline
$stm = 'SELECT * FROM test WHERE foo IN (:foo)'
$array = ['foo', 'bar', 'baz'];
```

既存のPDOの場合

```php?start_inline
// the native PDO way does not work (PHP Notice:  Array to string conversion)
// ネイティブのPDOでは`:foo`に配列を指定することは出来ません
$sth = $pdo->prepare($stm);
$sth->bindValue('foo', $array);
```

Aura.SqlのExtendedPDOの場合

```php?start_inline
$stm = 'SELECT * FROM test WHERE foo IN (:foo)'
$values = ['foo' => ['foo', 'bar', 'baz']];
$sth = $pdo->perform($stm, $values);
```

`:foo`に`['foo', 'bar', 'baz']`がバインドがされます。`queryString`で実際のクエリーを調べることが出来ます。

```php?start_inline
echo $sth->queryString;
// the query string has been modified by ExtendedPdo to become
// "SELECT * FROM test WHERE foo IN ('foo', 'bar', 'baz')"
```

### fetch*() メソッド

`prepare()`、`bindValue()`、 `execute()`を繰り返してデータベースから値を取得する代わりに`fetch*()`メソッドを使うとボイラープレートコードを減らすことができます。
（内部では`perform()`メソッドを実行しているので配列のプレースフォルもサポートしています）

```php?start_inline
$stm  = 'SELECT * FROM test WHERE foo = :foo AND bar = :bar';
$bind = array('foo' => 'baz', 'bar' => 'dib');
// ネイティブのPDOで"fetch all"を行う場合
$pdo = new PDO(...);
$sth = $pdo->prepare($stm);
$sth->execute($bind);
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

// ExtendedPdoで"fetch all"を行う場合
$pdo = new ExtendedPdo(...);
$result = $pdo->fetchAll($stm, $bind);

// fetchAssoc()は全ての行がコラム名のキーを持つ連想配列が返ります。
$result = $pdo->fetchAssoc($stm, $bind);

// fetchGroup() is like fetchAssoc() except that the values aren't wrapped in
// arrays. Instead, single column values are returned as a single dimensional
// array and multiple columns are returned as an array of arrays
// Set style to PDO::FETCH_NAMED when values are an array
// (i.e. there are more than two columns in the select)
$result = $pdo->fetchGroup($stm, $bind, $style = PDO::FETCH_COLUMN)

// fetchOne()は最初の行をキーをコラム名にした連想配列で返します。
$result = $pdo->fetchOne($stm, $bind);

// fetchPairs()は最初の列の値をキーに二番目の列の値を値にした連想配列を返します  
$result = $pdo->fetchPairs($stm, $bind);

// fetchValue()は最初の列の値を返します。
$result = $pdo->fetchValue($stm, $bind);

// fetchAffected()は影響を受けた行数を返します。
$stm = "UPDATE test SET incr = incr + 1 WHERE foo = :foo AND bar = :bar";
$row_count = $pdo->fetchAffected($stm, $bind);
?>
```

`fetchAll()`, `fetchAssoc()`, `fetchCol()`, 及び `fetchPairs()`のメソッドは三番目のオプションの引数に、それぞれの列に適用されるコールバックを指定することができます。

```php?start_inline
$result = $pdo->fetchAssoc($stm, $bind, function (&$row) {
    // add a column to the row
    $row['my_new_col'] = 'Added this column from the callable.';
});
?>
```
### yield*() メソッド

メモリを節約するために`yield*()`メソッドを使うことができます。 `fetch*()`メソッドは全ての行を一度に取得しますが、
`yield*()`メソッドはイテレーターが返ります。

```php
$stm  = 'SELECT * FROM test WHERE foo = :foo AND bar = :bar';
$bind = array('foo' => 'baz', 'bar' => 'dib');

// fetchAll()のように行は連想配列です
foreach ($pdo->yieldAll($stm, $bind) as $row) {
    // ...
}

// fetchAssoc()のようにキーが最初の列名で行が連想配列です。
foreach ($pdo->yieldAssoc($stm, $bind) as $key => $row) {
    // ...
}

// fetchCol()のように最初の列が値になった値を返します。
foreach ($pdo->yieldCol($stm, $bind) as $val) {
    // ...
}

// fetchPairs()と同様に最初の列からキー/バリューのペアの値を返します。
foreach ($pdo->yieldPairs($stm, $bind) as $key => $val) {
    // ...
}
```

### リプリケーション

マスター／スレーブの接続を自動で行うためには4つ目の引数にスレーブDBのIPを指定します。

```php?start_inline
$this->install(
  new AuraSqlModule(
    'mysql:host=localhost;dbname=test',
    'username',
    'password',
    'slave1,slave2' // スレーブIPをカンマ区切りで指定
  )
);
```

これでHTTPリクエストがGETの時がスレーブDB、その他のメソッドの時はマスターDBのDBオブジェクトがコンスタラクタに渡されます。

```php?start_inline
use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;
use PDO;

class User extends ResourceObject
{
    public $pdo;

    public function __construct(ExtendedPdoInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    public function onGet()
    {
         $this->pdo; // slave db
    }

    public function onPost($todo)
    {
         $this->pdo; // master db
    }
}
```

`@ReadOnlyConnection`、`@WriteConnection`でアノテートされたメソッドはメソッド名に関わらず、呼ばれた時にアノテーションに応じたDBオブジェクトが`$this->pdo`に上書きされます。

```php?start_inline
use Ray\AuraSqlModule\Annotation\ReadOnlyConnection;  // important
use Ray\AuraSqlModule\Annotation\WriteConnection;     // important

class User
{
    public $pdo; // @ReadOnlyConnectionや@WriteConnectionのメソッドが呼ばれた時に上書きされる

    public function onPost($todo)
    {
         $this->read();
    }

    /**
     * @ReadOnlyConnection
     */
    public function read()
    {
         $this->pdo; // slave db
    }

    /**
     * @WriteConnection
     */
    public function write()
    {
         $this->pdo; // master db
    }
}
```

### 複数DB

接続先の違う複数の`PdoExtendedInterface`オブジェクトを受け取るためには
`@Named`アノテーションで指定します。

```php?start_inline
/**
 * @Inject
 * @Named("log_db")
 */
public function setLoggerDb(ExtendedPdoInterface $pdo)
{
    // ...
}
```

モジュールでは`NamedPdoModule`で識別子を指定して束縛します。

```php?start_inline
$this->install(
  new NamedPdoModule(
    'log_db', // @Namedで指定するデータベースの種類
    'mysql:host=localhost;dbname=log',
    'username',
    'pass',
    'slave1,slave12'
  )
);
```

### トランザクション

`@Transactional`とアノテートしたメソッドはトランザクション管理されます。

```php?start_inline
use Ray\AuraSqlModule\Annotation\Transactional;

// ....
    /**
     * @Transactional
     */
    public function write()
    {
         // 例外発生したら\Ray\AuraSqlModule\Exception\RollbackExceptionに
    }
```

複数接続したデータベースのトランザクションを行うためには`@Transactional`アノテーションにプロパティを指定します。
指定しない場合は`{"pdo"}`になります。

```php?start_inline
/**
 * @Transactional({"pdo", "userDb"})
 */
public function write()
```

以下のように実行されます。

```php?start_inline
$this->pdo->beginTransaction()
$this->userDb->beginTransaction()

// ...

$this->pdo->commit();
$this->userDb->commit();
```

## Aura.SqlQuery

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPDOを拡張したライブラリですが、[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)は MySQL、Postgres,、SQLiteあるいは Microsoft SQL Serverといったデータベース固有のSQLのビルダーを提供します。

データベースを指定してアプリケーションモジュール`src/Module/AppModule.php`でインストールします。

```php?start_inline
// ...
$this->install(new AuraSqlQueryModule('mysql')); // pgsql, sqlite, or sqlsrv
```

### SELECT

リソースではDBクエリービルダオブジェクトを受け取り、下記のメソッドを使ってSELECTクエリーを組み立てます。
メソッドに特定の順番はなく複数回呼ぶことこともできます。

```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlSelectInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlSelectInject;

    public function onGet()
    {
        $this->select
            ->distinct()                    // SELECT DISTINCT
            ->cols([                        // select these columns
                'id',                       // column name
                'name AS namecol',          // one way of aliasing
                'col_name' => 'col_alias',  // another way of aliasing
                'COUNT(foo) AS foo_count'   // embed calculations directly
            ])
            ->from('foo AS f')              // FROM these tables
            ->fromSubselect(                // FROM sub-select AS my_sub
                'SELECT ...',
                'my_sub'
            )
            ->join(                         // JOIN ...
                'LEFT',                     // left/inner/natural/etc
                'doom AS d'                 // this table name
                'foo.id = d.foo_id'         // ON these conditions
            )
            ->joinSubSelect(                // JOIN to a sub-select
                'INNER',                    // left/inner/natural/etc
                'SELECT ...',               // the subselect to join on
                'subjoin'                   // AS this name
                'sub.id = foo.id'           // ON these conditions
            )
            ->where('bar > :bar')           // AND WHERE these conditions
            ->where('zim = ?', 'zim_val')   // bind 'zim_val' to the ? placeholder
            ->orWhere('baz < :baz')         // OR WHERE these conditions
            ->groupBy(['dib'])              // GROUP BY these columns
            ->having('foo = :foo')          // AND HAVING these conditions
            ->having('bar > ?', 'bar_val')  // bind 'bar_val' to the ? placeholder
            ->orHaving('baz < :baz')        // OR HAVING these conditions
            ->orderBy(['baz'])              // ORDER BY these columns
            ->limit(10)                     // LIMIT 10
            ->offset(40)                    // OFFSET 40
            ->forUpdate()                   // FOR UPDATE
            ->union()                       // UNION with a followup SELECT
            ->unionAll()                    // UNION ALL with a followup SELECT
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to named placeholders
                'bar' => 'bar_val',
                'baz' => 'baz_val',
            ]);

        $sth = $this->pdo->prepare($this->select->getStatement());

        // bind the values and execute
        $sth->execute($this->select->getBindValues());
        $result = $sth->fetch(\PDO::FETCH_ASSOC);
        // or
        // $result = $this->pdo->fetchAssoc($stm, $bind);
```

組み立てたクエリーは`getStatement()`で文字列にしてクエリーを行います。

### INSERT

#### 単一行のINSERT


```php?start_inline
use Ray\AuraSqlModule\AuraSqlInject;
use Ray\AuraSqlModule\AuraSqlInsertInject;

class User extend ResourceObject
{
    use AuraSqlInject;
    use AuraSqlInsertInject;

    public function onPost()
    {
        $this->insert
            ->into('foo')                   // INTO this table
            ->cols([                        // bind values as "(col) VALUES (:col)"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->bindValue('foo', 'foo_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values
                'bar' => 'foo',
                'baz' => 'zim',
            ]);

        $sth = $this->pdo->prepare($this->insert->getStatement());
        $sth->execute($this->insert->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->insert->getStatement(), this->insert->getBindValues());

        // get the last insert ID
        $name = $insert->getLastInsertIdName('id');
        $id = $pdo->lastInsertId($name);
```

`cols()`メソッドはキーがコラム名、値をバインドする値にした連想配列を渡すこともできます。

```php?start_inline
        $this->insert
            ->into('foo')                   // insert into this table
            ->cols([                        // insert these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
```

#### 複数行のINSERT

複数の行のINSERTを行うためには、最初の行の最後で`addRow()`メソッドを使います。その後に次のクエリーを組み立てます。

```php?start_inline
        // insert into this table
        $this->insert->into('foo');

        // set up the first row
        $this->insert->cols([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $this->insert->set('ts', 'NOW()');

        // set up the second row. the columns here are in a different order
        // than in the first row, but it doesn't matter; the INSERT object
        // keeps track and builds them the same order as the first row.
        $this->insert->addRow();
        $this->insert->set('ts', 'NOW()');
        $this->insert->cols([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);

        // set up further rows ...
        $this->insert->addRow();
        // ...

        // execute a bulk insert of all rows
        $sth = $this->pdo->prepare($insert->getStatement());
        $sth->execute($insert->getBindValues());

```

> 注:最初の行で始めて現れた列の値を指定しないで、行を追加しようとすると例外が投げられます。
> `addRow()`に列の連想配列を渡すと次の行で使われます。つまり最初の行で`col()`や`cols()`を指定しないこともできます。

```php?start_inline
        // set up the first row
        $insert->addRow([
            'bar' => 'bar-0',
            'baz' => 'baz-0'
        ]);
        $insert->set('ts', 'NOW()');

        // set up the second row
        $insert->addRow([
            'bar' => 'bar-1',
            'baz' => 'baz-1'
        ]);
        $insert->set('ts', 'NOW()');

        // etc.
```

`addRows()`を使ってデータベースを一度にセットすることもできます。

```php?start_inline
        $rows = [
            [
                'bar' => 'bar-0',
                'baz' => 'baz-0'
            ],
            [
                'bar' => 'bar-1',
                'baz' => 'baz-1'
            ],
        ];
        $this->insert->addRows($rows);
```

### UPDATE
下記のメソッドを使ってUPDATEクエリーを組み立てます。 メソッドに特定の順番はなく複数回呼ぶことこともできます。

```php?start_inline
        $this->update
            ->table('foo')                  // update this table
            ->cols([                        // bind values as "SET bar = :bar"
                'bar',
                'baz',
            ])
            ->set('ts', 'NOW()')            // raw value as "(ts) VALUES (NOW())"
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->update->getBindValues());
        // or
        // $sth = $this->pdo->perform($this->update->getStatement(), $this->update->getBindValues());
```

キーを列名、値をバインドされた値（RAW値ではなりません）にした連想配列を`cols()`に渡すこともできます。

```php?start_inline

        $this-update->table('foo')          // update this table
            ->cols([                        // update these columns and bind these values
                'foo' => 'foo_value',
                'bar' => 'bar_value',
                'baz' => 'baz_value',
            ]);
?>
```

### DELETE
下記のメソッドを使ってDELETEクエリーを組み立てます。 メソッドに特定の順番はなく複数回呼ぶことこともできます。
```php?start_inline
        $this->delete
            ->from('foo')                   // FROM this table
            ->where('zim = :zim')           // AND WHERE these conditions
            ->where('gir = ?', 'doom')      // bind this value to the condition
            ->orWhere('gir = :gir')         // OR WHERE these conditions
            ->bindValue('bar', 'bar_val')   // bind one value to a placeholder
            ->bindValues([                  // bind these values to the query
                'baz' => 99,
                'zim' => 'dib',
                'gir' => 'doom',
            ]);
        $sth = $this->pdo->prepare($update->getStatement())
        $sth->execute($this->delete->getBindValues());
```

### パジネーション

[ray/aura-sql-module](https://packagist.org/packages/ray/aura-sql-module)はRay.Sqlの生SQL、Ray.AuraSqlQueryのクエリービルダー双方でパジネーション（ページ分割）をサポートしています。
バインドする値と１ページあたりのアイテム数、それに{page}をページ番号にしたuri_templateでページャーファクトリーを`newInstance()`で生成して、ページ番号で配列アクセスします。

#### Aura.Sql用
AuraSqlPagerFactoryInterface

```php?start_inline
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $sql, $params, 10, '/?page={page}&category=sports'); // 10 items per page
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
// $page->data // sliced data (array|\Traversable)
// $page->current; (int)
// $page->total (int)
// $page->hasNext (bool)
// $page->hasPrevious (bool)
// $page->maxPerPage; (int)
// (string) $page // pager html (string)
```

#### Aura.SqlQuery用
AuraSqlQueryPagerFactoryInterface

```php?start_inline
// for Select
/* @var $factory \Ray\AuraSqlModule\Pagerfanta\AuraSqlQueryPagerFactoryInterface */
$pager = $factory->newInstance($pdo, $select, 10, '/?page={page}&category=sports');
$page = $pager[2]; // page 2
/* @var $page \Ray\AuraSqlModule\Pagerfanta\Page */
```
> 注：Aura.Sqlは生SQLを直接編集していますが現在MySql形式のLIMIT句しか対応していません。

`$page`はイテレータブルです。

```php?start_inline
foreach ($page as $row) {
 // 各行の処理
}
```
ページャーのリンクHTMLのテンプレートを変更するには`TemplateInterface`の束縛を変更します。
テンプレート詳細に関しては[Pagerfanta](https://github.com/whiteoctober/Pagerfanta#views)をご覧ください。

```php?start_inline
use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\View\Template\TwitterBootstrap3Template;
use Ray\AuraSqlModule\Annotation\PagerViewOption;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ..
        $this->bind(TemplateInterface::class)->to(TwitterBootstrap3Template::class);
        $this->bind()->annotatedWith(PagerViewOption::class)->toInstance($pagerViewOption);
    }
}
```





# バージョン

## サポートするPHP

[![Continuous Integration](https://github.com/bearsunday/BEAR.SupportedVersions/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.SupportedVersions/actions/workflows/continuous-integration.yml)

BEAR.SundayはPHPの公式サポート期間([Supported Versions](http://php.net/supported-versions.php))に準じてPHPバージョンをサポートしています。

* `8.1` (古い安定板 25 Nov 2021 - 25 Nov 2024)
* `8.2` (古い安定板 8 Dec 2022 - 8 Dec 2025)
* `8.3` (現在の安定板 23 Nov 2022 - 26 Nov 2026)

End of life ([EOL](http://php.net/eol.php))

* `5.5` (21 Jul 2016)
* `5.6` (31 Dec 2018)
* `7.0` (3 Dec 2018)
* `7.1` (1 Dec 2019)
* `7.2` (30 Nov 2020)
* `7.3` (6 Dec 2021)
* `7.4` (28 Nov 2022)
* `8.0` (26 Nov 2023)

## Semver

BEAR.Sundayは[セマンティックバージョニング](http://semver.org/lang/ja/)に従います。マイナーバージョンアップ（バージョン番号が`0.1`増加）ではアプリケーションコードの修正は不要です。

## バージョニング・ポリシー

* フレームワークのコアパッケージは破壊的変更を行いません。[^1]
* PHPのサポート要件が変更され、必要なPHPバージョンが上がっても（例：`5.6`→`7.0`）、フレームワークのメジャーバージョンアップは行いません。後方互換性は維持されます。
* 新しいモジュールの導入によりPHPバージョンの要件が上がることはありますが、それに伴う破壊的変更は行いません。
* 後方互換性維持のため、古い機能は削除せず[^3]、新機能は既存機能の置き換えではなく追加として実装されます。

BEAR.Sundayは堅牢で進化可能[^2]な、長期的な保守性を重視したフレームワークを目指しています。

## パッケージのバージョン

フレームワークは依存ライブラリのバージョンを固定しません。ライブラリはフレームワークのバージョンに関係なくアップデート可能です。`composer update`による定期的な依存関係の更新を推奨します。

---

[^1]: フレームワーク自体はメジャーバージョンアップを行いません。ただし、利用ライブラリ（例：Twig）のモジュールは、そのライブラリの破壊的変更に合わせてメジャーバージョンアップされることがあります。
[^2]: [https://en.wikipedia.org/wiki/Software_evolution](https://en.wikipedia.org/wiki/Software_evolution)
[^3]: 代わりに`deprecated`として扱います。


# HTML

BEAR.Sundayでは、複数のテンプレートエンジンを活用してHTML表示を実現できます。

## テンプレートエンジンの選択

### 対応テンプレートエンジン

- [Qiq](html-qiq.html)（v1.0以降）
- [Twig](html-twig.html)（v1およびv2）

### 特徴比較

| 機能 | Qiq | Twig |
||---|
| エスケープ方式 | 明示的 | 暗黙的 |
| 構文 | PHP準拠 | 独自構文 |
| コードベース | 軽量 | 豊富な機能 |
| IDE対応 | 優れている | 一般的 |

### 構文比較

PHP:
```php
<?= $var ?>
<?= htmlspecialchars($var, ENT_QUOTES|ENT_DISALLOWED, 'utf-8') ?>
<?= htmlspecialchars(helper($var, ENT_QUOTES|ENT_DISALLOWED, 'utf-8')) ?>
<?php foreach ($users as $user): ?>
    * <?= $user->name; ?>
<?php endforeach; ?>
```

Twig:

```
{% raw %}{{ var | raw }}
{{ var }}
{{ var | helper }}
{% for user in users %}
  * {{ user.name }}
{% endfor %}{% endraw %}
```

Qiq:

```
{% raw %}{{% var }}
{{h $var }}
{{h helper($var) }}
{{ foreach($users => $user) }}
  * {{h $user->name }}
{{ endforeach }}

{{ var }} // 表示されない{% endraw %}
```

Or

```php
<?php /** @var Template $this */ ?>
<?= $this->h($var) ?>
```

## レンダラー

`RenderInterface`にバインドされResourceObjectにインジェクトされるレンダラーがリソースの表現を生成します。リソース自身はその表現に関して無関心です。

リソース単位でインジェクトされるため、複数のテンプレートエンジンを同時に使用することも可能です。

## 開発用のハローUI

開発時にハロー(Halo, 後光) [^halo] と呼ばれる開発用のUIをレンダリングされたリソースの周囲に表示できます。

ハローは以下の情報を提供します：
- リソースの状態
- 表現
- 適用されたインターセプター
- PHPStormでリソースクラスやテンプレートを開くためのリンク

[^halo]: 名前はSmalltalkのフレームワーク [Seaside](https://github.com/seasidest/seaside)の同様の機能が由来しています。

<img src="https://user-images.githubusercontent.com/529021/211504531-37cd4a8d-80b3-4d77-903f-c8f5baf5dc37.png" alt="ハローがリソース状態を表示" width="50%">

<link href="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css" rel="stylesheet">

* <span class="glyphicon glyphicon-home" rel="tooltip" title="Home"></span> ハローホーム（ボーターとツール表示）
* <span class="glyphicon glyphicon-zoom-in" rel="tooltip" title="Status"></span> リソース状態
* <span class="glyphicon glyphicon-font" rel="tooltip" title="View"></span> リソース表現
* <span class="glyphicon glyphicon-info-sign" rel="tooltip" title="Info"></span> プロファイル

[demo](/docs/demo/halo/)でハローのモックを試すことができます。

### パフォーマンスモニタリング

ハローには以下のパフォーマンス情報が表示されます：
- リソースの実行時間
- メモリ使用量
- プロファイラへのリンク

<img src="https://user-images.githubusercontent.com/529021/212373901-fce7b2fd-41b0-478f-9d36-5e2eb3b97d9c.png" alt="ハローがパフォーマンスを表示" width="50%">

### インストール

プロファイリングには[xhprof](https://www.php.net/manual/ja/intro.xhprof.php)のインストールが必要です：

```bash
pecl install xhprof
# php.iniファイルに'extension=xhprof.so'を追加
```

コールグラフを可視化するには、[graphviz](https://graphviz.org/download/)のインストールが必要です：

```bash
# macOS
brew install graphviz

# Windows
# graphvizのWebサイトからインストーラをダウンロードしてインストール

# Linux (Ubuntu)
sudo apt-get install graphviz
```

アプリケーションではDevコンテキストモジュールなどを作成して`HaloModule`をインストールします：

```php
class DevModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new HaloModule($this));
    }
}
```

例）[コールグラフデモ](/docs/demo/halo/callgraph.svg)



# HTML (Qiq)

## セットアップ

QiqでHTML表示をするために、以下の手順で設定を行います：

1. composerで`bear/qiq-module`をインストールします：
```bash
composer require bear/qiq-module
```

2. テンプレートやヘルパーを格納するディレクトリを用意します：
```bash
cd /path/to/project
cp -r vendor/bear/qiq-module/var/qiq var/
```

3. `html`コンテキストファイル`src/Module/HtmlModule.php`を用意して`QiqModule`をインストールします：
```php
namespace MyVendor\MyPackage\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\QiqModule\QiqModule;

class HtmlModule extends AbstractAppModule
{
    protected function configure()
    {
        $this->install(
            new QiqModule($this->appMeta->appDir . '/var/qiq/template')
        );
    }
}
```

## コンテキスト変更

`bin/page.php`のコンテキストを変更して`html`を有効にします：

```bash
$context = 'cli-html-app';
```

## テンプレート

Indexリソースのテンプレートを`var/qiq/template/Page/Index.php`に用意します：

```
{% raw %}<h1>{{h $this->greeting }}</h1>{% endraw %}
```

ResourceObjectの`$body`がテンプレートに`$this`としてアサインされます：

```bash
php bin/page.php get /
200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
```

## カスタムヘルパー

[カスタムヘルパー](https://qiqphp-ja.github.io/1.x/helpers/custom.html#1-8-4)は`Qiq\Helper\`のnamespaceで作成します。

例: `Qiq\Helper\Foo`

composer.jsonの`autoload`に`Qiq\Helper`を指定し（例: [composer.json](https://github.com/bearsunday/BEAR.QiqModule/blob/1.x/demo/composer.json#L26)）、`composer dump-autoload`を実行してヘルパークラスをオートロード可能にします。指定ディレクトリに設置するとカスタムヘルパーが利用可能になります。

## ProdModule

プロダクション用にエラーページをHTMLにし、コンパイラキャッシュを有効にするためのモジュールをProdModuleでインストールします：

```php
class ProdModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new QiqErrorModule);
        $this->install(new QiqProdModule($this->appMeta->appDir . '/var/tmp'));
    }
}
```



# HTML (Twig v1)

HTML表示のためにcomposerで`madapaja/twig-module`をインストールします。

```bash
composer require madapaja/twig-module ^1.0
```

次に`html`コンテキストファイル`src/Module/HtmlModule.php`を用意して`TwigModule`をインストールします。

```php?start_inline
namespace MyVendor\MyPackage\Module;

use BEAR\AppMeta\AppMeta;
use Madapaja\TwigModule\TwigModule;
use Ray\Di\AbstractModule;

class HtmlModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TwigModule);
    }
}
```

`bin/page.php`のコンテキストを変更して`html`を有効にします。

```bash
$context = 'cli-html-app';
```
リソースのPHPファイル名に`.html.twig`拡張子をつけたファイルでテンプレートを用意します。
`Page/Index.php`に対応するのは`Page/Index.html.twig`になります。

```
{% raw %}<h1>{{ greeting }}</h1>{% endraw %}
```

`$body`がテンプレートにアサインされて出力されます。

```bash
php bin/page.php get /
200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
```

レイアウトや部分的なテンプレートファイルは`var/lib/twig`に設置します。

## カスタム設定

コンテンキストに応じてオプション等を設定したり、テンプレートのパスを追加したりする場合は
`@TwigPaths`と`@TwigOptions`に設定値を束縛します。

```php?start_inline
namespace MyVendor\MyPackage\Module;

use Madapaja\TwigModule\Annotation\TwigOptions;
use Madapaja\TwigModule\Annotation\TwigPaths;
use Madapaja\TwigModule\TwigModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TwigModule());

        // twig テンプレートパスを追加
        $appDir = dirname(dirname(__DIR__));
        $paths = [
            $appDir . '/src/Resource',
            $appDir . '/var/lib/twig'
        ];
        $this->bind()->annotatedWith(TwigPaths::class)->toInstance($paths);

        // 環境のオプションを設定することも可能
        // @see http://twig.sensiolabs.org/doc/api.html#environment-options
        $options = [
            'debug' => false,
            'cache' => $appDir . '/tmp'
        ];
        $this->bind()->annotatedWith(TwigOptions::class)->toInstance($options);
    }
}
```

## 他のテンプレートエンジン

テンプレートエンジンは選択できるだけでなく、複数のテンプレートエンジンをリソース単位で選択することもできます。



# HTML (Twig v2)

## インストール

HTML表示のためにcomposerで[Twig v2](https://twig.symfony.com/doc/2.x/)のモジュールをインストールします：

```bash
composer require madapaja/twig-module ^2.0
```

次に`html`コンテキストファイル`src/Module/HtmlModule.php`を用意して`TwigModule`をインストールします：

```php
namespace MyVendor\MyPackage\Module;

use Madapaja\TwigModule\TwigErrorPageModule;
use Madapaja\TwigModule\TwigModule;
use Ray\Di\AbstractModule;

class HtmlModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TwigModule);
        $this->install(new TwigErrorPageModule);
    }
}
```

`TwigErrorPageModule`はエラー表示をHTMLで行うオプションです。`HtmlModule`でインストールしないで`ProdModule`でインストールして開発時のエラー表示はJSONにすることもできます。

次に`templates`フォルダをコピーします：

```bash
cp -r vendor/madapaja/twig-module/var/templates var/templates
```

`bin/page.php`や`public/index.php`のコンテキストを変更して`html`を有効にします：

```bash
$context = 'cli-html-app'; // 'html-app'
```

## テンプレート

1つのリソースクラスに1つのテンプレートファイルが`var/templates`フォルダに必要です。例えば`src/Page/Index.php`には`var/templates/Page/Index.html.twig`が必要です。テンプレートにリソースの**body**がアサインされます。

例）`src/Page/Index.php`:
```php
class Index extends ResourceObject
{
    public $body = [
        'greeting' => 'Hello BEAR.Sunday'
    ];
}
```

`var/templates/Page/Index.html.twig`:
```twig
<h1>{{ greeting }}</h1>
```

出力：
```bash
php bin/page.php get /
200 OK
content-type: text/html; charset=utf-8
<h1>Hello BEAR.Sunday</h1>
```

## テンプレートファイルの選択

どのテンプレートを使用するかはリソースでは選択しません。リソースの状態によって`include`します：

```twig
{% raw %}{% if user.is_login %}
    {{ include('member.html.twig') }}
{% else %}
    {{ include('guest.html.twig') }}
{% endif %}{% endraw %}
```

リソースクラスはリソース状態だけに関心を持ち、テンプレートだけがリソース表現に関心を持ちます。このような設計原則を[関心の分離(SoC)](https://ja.wikipedia.org/wiki/%E9%96%A2%E5%BF%83%E3%81%AE%E5%88%86%E9%9B%A2)といいます。

## エラーページ

`var/templates/error.html.twig`を編集します。エラーページには以下の値がアサインされています：

| 変数 | 意味 | キー |
||---|
| status | HTTP ステータス | code, message |
| e | 例外 | code, message, class |
| logref | ログID | n/a |

例：
```twig
{% raw %}{% extends 'layout/base.html.twig' %}
{% block title %}{{ status.code }} {{ status.message }}{% endblock %}

{% block content %}
    <h1>{{ status.code }} {{ status.message }}</h1>
    {% if status.code == 404 %}
        <p>The requested URL was not found on this server.</p>
    {% else %}
        <p>The server is temporarily unable to service your request.</p>
        <p>reference number: {{ logref }}</p>
    {% endif %}
{% endblock %}{% endraw %}
```

## リソースのアサイン

リソースクラスのプロパティを参照するにはリソース全体がアサインされる`_ro`を参照します。

例）`Todos.php`:
```php
class Todos extends ResourceObject
{
    public $code = 200;
    public $text = [
        'name' => 'BEAR'
    ];
    public $body = [
        ['title' => 'run']
    ];
}
```

`Todos.html.twig`:
```twig
{% raw %}{{ _ro.code }}       {# 出力: 200 #}
{{ _ro.text.name }}  {# 出力: 'BEAR' #}
{% for todo in _ro.body %}
    {{ todo.title }} {# 出力: 'run' #}
{% endfor %}{% endraw %}
```

## ビューの階層構造

リソースクラス単位でビューを持つことができます。構造を良く表し、キャッシュもリソース単位で行われるので効率的です。

例）`app://self/todos`を読み込む`page://self/index`：

### app://self/todos
```php
class Todos extends ResourceObject
{
    use AuraSqlInject;
    use QueryLocatorInject;

    public function onGet(): static
    {
        $this->body = $this->pdo->fetchAll($this->query['todos_list']);
        return $this;
    }
}
```

```twig
{% raw %}{% for todo in _ro.body %}
    {{ todo.title }}
{% endfor %}{% endraw %}
```

### page://self/index
```php
class Index extends ResourceObject
{
    /**
     * @Embed(rel="todos", src="app://self/todos")
     */
    public function onGet(): static
    {
        return $this;
    }
}
```

```twig
{% raw %}{% extends 'layout/base.html.twig' %}

{% block content %}
    {{ todos|raw }}
{% endblock %}{% endraw %}
```

## 拡張

Twigを`addExtension()`メソッドで拡張する場合には、拡張を行うTwigのProviderクラスを用意し`Twig_Environment`クラスに`Provider`束縛します：

```php
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

class MyTwigProvider implements ProviderInterface
{
    private $twig;

    /**
     * @Named("original")
     */
    public function __construct(\Twig_Environment $twig)
    {
        // $twig は元の \Twig_Environment インスタンス
        $this->twig = $twig;
    }

    public function get()
    {
        // Twigの拡張
        $this->twig->addExtension(new MyTwigExtension());
        return $this->twig;
    }
}
```

```php
class HtmlModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TwigModule);
        $this->bind(\Twig_Environment::class)
             ->toProvider(MyTwigProvider::class)
             ->in(Scope::SINGLETON);
    }
}
```

## モバイル対応

モバイルサイト専用のテンプレートを使用するためには`MobileTwigModule`を加えてインストールします：

```php
class HtmlModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TwigModule);
        $this->install(new MobileTwigModule);
    }
}
```

`index.html.twig`の代わりに`Index.mobile.twig`が**存在すれば**優先して使用されます。変更の必要なテンプレートだけを用意することができます。

## カスタム設定

コンテキストに応じてオプション等を設定したり、テンプレートのパスを追加する場合は`@TwigPaths`と`@TwigOptions`に設定値を束縛します。

注）キャッシュを常に`var/tmp`フォルダに生成するので、特にプロダクション用の設定などは必要ありません。

```php
namespace MyVendor\MyPackage\Module;

use BEAR\Package\AbstractAppModule;
use Madapaja\TwigModule\Annotation\TwigDebug;
use Madapaja\TwigModule\Annotation\TwigOptions;
use Madapaja\TwigModule\Annotation\TwigPaths;
use Madapaja\TwigModule\TwigModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        $this->install(new TwigModule);

        // テンプレートパスの指定
        $appDir = $this->appMeta->appDir;
        $paths = [
            $appDir . '/src/Resource',
            $appDir . '/var/templates'
        ];
        $this->bind()
             ->annotatedWith(TwigPaths::class)
             ->toInstance($paths);

        // オプション
        // @see http://twig.sensiolabs.org/doc/api.html#environment-options
        $options = [
            'debug' => false,
            'cache' => $appDir . '/tmp'
        ];
        $this->bind()
             ->annotatedWith(TwigOptions::class)
             ->toInstance($options);

        // debugオプションのみを指定する場合
        $this->bind()
             ->annotatedWith(TwigDebug::class)
             ->toInstance(true);
    }
}
```



# フォーム

[Aura.Input](https://github.com/auraphp/Aura.Input)と[Aura.Filter](https://github.com/auraphp/Aura.Filter)を使用したWebフォーム機能は、関連する機能が単一のクラスに集約され、テストや変更が容易です。1つのクラスでWebフォームとバリデーションの両方の用途に使用できます。

## インストール

Aura.Inputを使用したフォーム処理を追加するために、composerで`ray/web-form-module`をインストールします：

```bash
composer require ray/web-form-module
```

アプリケーションモジュール`src/Module/AppModule.php`で`AuraInputModule`をインストールします：

```php
use BEAR\Package\AbstractAppModule;
use Ray\WebFormModule\WebFormModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new AuraInputModule);
    }
}
```

## Webフォーム

フォーム要素の登録やルールを定めた**フォームクラス**を作成して、`@FormValidation`アノテーションを使用して特定のメソッドと束縛します。メソッドは送信されたデータがバリデーションOKのときのみ実行されます。

```php
use Ray\WebFormModule\AbstractForm;
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractForm
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // フォームフィールドの設定
        $this->setField('name', 'text')
             ->setAttribs([
                 'id' => 'name'
             ]);

        // バリデーションルールとエラーメッセージの設定
        $this->filter->validate('name')->is('alnum');
        $this->filter->useFieldMessage('name', '名前は英数字のみ使用できます。');
    }
}
```

フォームクラスの`init()`メソッドでフォームのinput要素を登録し、バリデーションのフィルターやサニタイズのルールを適用します。

バリデーションルールについては以下を参照してください：
- [Rules To Validate Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/validate.md)
- [Rules To Sanitize Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/sanitize.md)

メソッドの引数を連想配列にしたものをバリデーションします。入力を変更したい場合は`SubmitInterface`インターフェイスの`submit()`メソッドを実装して入力する値を返します。

## @FormValidationアノテーション

フォームのバリデーションを行うメソッドを`@FormValidation`でアノテートすると、実行前に`form`プロパティのフォームオブジェクトでバリデーションが行われます。バリデーションに失敗するとメソッド名に`ValidationFailed`サフィックスをつけたメソッドが呼ばれます：

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\WebFormModule\Annotation\FormValidation;
use Ray\WebFormModule\FormInterface;

class MyController
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @Inject
     * @Named("contact_form")
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @FormValidation
     * // または
     * @FormValidation(form="form", onFailure="onPostValidationFailed")
     */
    public function onPost($name, $age)
    {
        // バリデーション成功時の処理
    }

    public function onPostValidationFailed($name, $age)
    {
        // バリデーション失敗時の処理
    }
}
```

`@FormValidation`アノテーションの`form`と`onValidationFailed`プロパティを変更して、`form`プロパティの名前やメソッドの名前を明示的に指定することもできます。`onPostValidationFailed`にはサブミットされた値が渡されます。

## ビュー

フォームの`input`要素やエラーメッセージを取得するには要素名を指定します：

```php
$form->input('name');  // 出力例：<input id="name" type="text" name="name" size="20" maxlength="20" />
$form->error('name');  // 出力例：名前は英数字のみ使用できます。
```

Twigテンプレートを使用する場合も同様です：

```twig
{% raw %}{{ form.input('name') }}
{{ form.error('name') }}{% endraw %}
```

## CSRF

CSRF(クロスサイトリクエストフォージェリ)対策を行うためには、フォームにCSRFオブジェクトをセットします：

```php
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractForm
{
    use SetAntiCsrfTrait;
}
```

セキュリティレベルを高めるには、ユーザーの認証を含んだカスタムCsrfクラスを作成してフォームクラスにセットします。詳しくはAura.Inputの[Applying CSRF Protections](https://github.com/auraphp/Aura.Input#applying-csrf-protections)をご覧ください。

## @InputValidation

`@FormValidation`の代わりに`@InputValidation`とアノテートすると、バリデーションが失敗したときに`Ray\WebFormModule\Exception\ValidationException`が投げられます。この場合はHTML表現は使用されません。Web APIに便利です。

キャッチした例外の`error`プロパティを`echo`すると[application/vnd.error+json](https://github.com/blongden/vnd.error)メディアタイプの表現が出力されます：

```php
http_response_code(400);
echo $e->error;

// 出力例：
// {
//     "message": "Validation failed",
//     "path": "/path/to/error",
//     "validation_messages": {
//         "name": [
//             "名前は英数字のみ使用できます。"
//         ]
//     }
// }
```

`@VndError`アノテーションで`vnd.error+json`に必要な情報を追加できます：

```php
/**
 * @FormValidation(form="contactForm")
 * @VndError(
 *   message="foo validation failed",
 *   logref="a1000",
 *   path="/path/to/error",
 *   href={"_self"="/path/to/error", "help"="/path/to/help"}
 * )
 */
public function onPost()
```

## Vnd Error

`Ray\WebFormModule\FormVndErrorModule`をインストールすると、`@FormValidation`でアノテートしたメソッドも`@InputValidation`とアノテートしたメソッドと同じように例外を投げるようになります。作成したPageリソースをAPIとして使用することができます：

```php
use BEAR\Package\AbstractAppModule;
use Ray\WebFormModule\FormVndErrorModule;

class FooModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new AuraInputModule);
        $this->override(new FormVndErrorModule);
    }
}
```

## デモ

[MyVendor.ContactForm](https://github.com/bearsunday/MyVendor.ContactForm)アプリケーションでフォームのデモを実行して試すことができます。確認付きのフォームページや、複数のフォームを1ページに設置したときの例などが用意されています。



# コンテントネゴシエーション

HTTPにおいてコンテントネゴシエーション ([content negotiation](https://en.wikipedia.org/wiki/Content_negotiation)) は、同じ URL に対してさまざまなバージョンのリソースを提供するために使用する仕組みです。
BEAR.Sundayではその内のメディアタイプの`Accept`と言語の`Accept-Language`のサーバーサイドのコンテントネゴシエーションをサポートします。アプリケーション単位またはリソース単位で指定することができます。

## インストール

composerで[BEAR.Accept](https://github.com/bearsunday/BEAR.Accept)をインストールします。

```bash
composer require bear/accept ^0.1
```

次に`Accept*`リクエストヘッダーに応じたコンテキストを`/var/locale/available.php`に保存します。

```php
<?php
return [
    'Accept' => [
        'text/hal+json' => 'hal-app',
        'application/json' => 'app',
        'cli' => 'cli-hal-app'
    ],
    'Accept-Language' => [ // キーを小文字で
        'ja-jp' => 'ja',
        'ja' => 'ja',
        'en-us' => 'en',
        'en' => 'en'
    ]
];
```

`Accept`キー配列はメディアタイプをキーにしてコンテキストが値にした配列を指定します。`cli`はコンソールアクセスでのコンテキストでwebアクセスで使われることはありません。

`Accept-Language`キー配列は言語をキーにしてコンテキストキーを値した配列を指定します。

## アプリケーション

アプリケーション全体でコンテントネゴシエーションを有効にするために`public/index.php`を変更します。

```php
<?php
use BEAR\Accept\Accept;

require dirname(__DIR__) . '/vendor/autoload.php';

$accept = new Accept(require dirname(__DIR__) . '/var/locale/available.php');
list($context, $vary) = $accept($_SERVER);
require dirname(__DIR__) . '/bootstrap/bootstrap.php';
```

上記の設定で例えば以下の`Accept*`ヘッダーのアクセスのコンテキストは`prod-hal-ja-app`になります：

```
Accept: application/hal+json
Accept-Language: ja-JP
```

この時`JaModule`で日本語テキストのための束縛が必要です。詳しくはデモアプリケーション[MyVendor.Locale](https://github.com/koriym/MyVendor.Locale)をごらんください。

## リソース

リソース単位でコンテントネゴシエーションを行う場合は`AcceptModule`モジュールをインストールして`@Produces`アノテーションを使います。

### モジュール

```php
protected function configure()
{
    // ...
    $available = $appDir . '/var/locale/available.php';
    $this->install(new AcceptModule($available));
}
```

## @Producesアノテーション

```php
use BEAR\Accept\Annotation\Produces;

/**
 * @Produces({"application/hal+json", "text/csv"})
 */
public function onGet()
```

利用可能なメディアタイプを左から優先順位でアノテートします。対応したコンテキストのレンダラーがAOPでセットされ表現が変わります。
アプリケーション単位でのネゴシエーションの時と違って、`Vary`ヘッダーを手動で付加する必要はありません。

## curlを使ったアクセス

`-H`オプションで`Accept*`ヘッダーを指定します：

```bash
curl -H 'Accept-Language: en' http://127.0.0.1:8080/
```

```bash
curl -i -H 'Accept-Language: en' -H 'Accept: application/hal+json' http://127.0.0.1:8080/
```

```
HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Date: Fri, 11 Aug 2017 08:32:33 +0200
Connection: close
X-Powered-By: PHP/7.1.4
Vary: Accept, Accept-Language
content-type: application/hal+json

{
    "greeting": "Hello BEAR.Sunday",
    "_links": {
        "self": {
            "href": "/index"
        }
    }
}
```



# ハイパーメディアAPI

## HAL

BEAR.Sundayは[HAL](https://en.wikipedia.org/wiki/Hypertext_Application_Language)ハイパーメディア（`application/hal+json`）APIをサポートしています。HALのリソースモデルは以下の要素で構成されます：

* リンク
* 埋め込みリソース
* 状態

HALは、従来のリソースの状態のみを表すJSONに、リンクの`_links`と他リソースを埋め込む`_embedded`を加えたものです。HALはAPIを探索可能にし、そのAPIドキュメントをAPI自体から発見することができます。

### Links

以下は有効なHALの例です。自身（`self`）のURIへのリンクを持っています：

```json
{
    "_links": {
        "self": { "href": "/user" }
    }
}
```

### Link Relations

リンクには`rel`（relation）があり、どのような関係でリンクされているかを表します。HTMLの`<link>`タグや`<a>`タグで使われる`rel`と同様です：

```json
{
    "_links": {
        "next": { "href": "/page=2" }
    }
}
```

HALについてさらに詳しくは[http://stateless.co/hal_specification.html](http://stateless.co/hal_specification.html)をご覧ください。

## リソースクラス

アノテーションを使用してリンクを貼ったり、他のリソースを埋め込んだりすることができます。

### #[Link]

リンクが静的なものは`#[Link]`属性で表し、動的なものは`body['_links']`に代入します。宣言的に記述できる`#[Link]`属性の使用を推奨します：

```php
#[Link(rel="user", href="/user")]
#[Link(rel="latest-post", href="/latest-post", title="latest post entry")]
public function onGet()
```

または：

```php
public function onGet() 
{
    // 権限のある場合のみリンクを貼る
    if ($hasCommentPrivilege) {
        $this->body += [
            '_links' => [
                'comment' => [
                    'href' => '/comments/{post-id}',
                    'templated' => true
                ]
            ]
        ];
    }
}
```

### #[Embed]

他のリソースを静的に埋め込むには`#[Embed]`アトリビュートを使い、動的に埋め込むには`body`にリクエストを代入します：

```php
#[Embed(rel="todos", src="/todos{?status}")]
#[Embed(rel="me", src="/me")]
public function onGet(string $status): static
```

または：

```php
$this->body['_embedded']['todos'] = $this->resource->uri('app://self/todos');
```

## APIドキュメント

Curiesが設定されたAPIサーバーをAPIドキュメントサーバーとして使用できます。これにより、APIドキュメントの作成の手間や、実際のAPIとの整合性の問題、検証やメンテナンスといった課題を解決できます。

サービスを提供するには、`bear/api-doc`をインストールして`BEAR\ApiDoc\ApiDoc`ページクラスを継承して設置します：

```bash
composer require bear/api-doc
```

```php
<?php
namespace MyVendor\MyProject\Resource\Page\Rels;

use BEAR\ApiDoc\ApiDoc;

class Index extends ApiDoc
{
}
```

JSON Schemaのフォルダをwebに公開します：

```bash
ln -s var/json_schema public/schemas
```

DocblockコメントとJSON Schemaを使ってAPIドキュメントが自動生成されます。ページクラスは独自のレンダラーを持ち、`$context`の影響を受けずに人のためのドキュメント（`text/html`）をサービスします。

`$context`の影響を受けないため、`App`、`Page`どちらにも設置可能です。CURIEsがルートに設定されていれば、API自体がハイパーメディアではない通常のJSONの場合でも利用可能です。

リアルタイムに生成されるドキュメントは、常にプロパティ情報やバリデーション制約が正確に反映されます。

### デモ

```bash
git clone https://github.com/koriym/Polidog.Todo.git
cd Polidog.Todo/
composer install
composer setup
composer doc
```

[docs/index.md](https://github.com/koriym/Polidog.Todo/blob/master/docs/index.md)にAPI docが作成されます。

## ブラウズ可能

HALで記述されたAPIセットは**ヘッドレスのRESTアプリケーション**として機能します。WebベースのHAL BrowserやコンソールのcURLコマンドで、Webサイトと同じようにルートからリンクを辿って、すべてのリソースにアクセスできます：

* [HAL Browser](https://github.com/mikekelly/hal-browser) - [example](http://haltalk.herokuapp.com/explorer/browser.html#/)
* [hyperagent.js](https://weluse.github.io/hyperagent/)

## Siren

[Siren](https://github.com/kevinswiber/siren)ハイパーメディア（`application/vnd.siren+json`）をサポートした[Sirenモジュール](https://github.com/kuma-guy/BEAR.SirenModule)も利用可能です。



# PSR-7

[PSR-7 HTTP message interface](https://www.php-fig.org/psr/psr-7/)[^1]を使って、サーバーサイドリクエストの情報を取得したり、BEAR.SundayアプリケーションをPSR-7ミドルウェアとして実行したりすることができます。

## HTTPリクエスト

PHPには[`$_SERVER`](http://php.net/manual/ja/reserved.variables.server.php)や[`$_COOKIE`](http://php.net/manual/ja/reserved.variables.cookies.php)などの[スーパーグローバル](http://php.net/manual/ja/language.variables.superglobals.php)がありますが、それらの代わりに[PSR-7 HTTP message interface](https://www.php-fig.org/psr/psr-7/)を使ってサーバーサイドリクエストの情報（`$_COOKIE`、`$_GET`、`$_POST`、`$_FILES`、`$_SERVER`）を受け取ることができます。

### ServerRequest（サーバーリクエスト全般）

```php
class Index extends ResourceObject
{
    public function __construct(ServerRequestInterface $serverRequest)
    {
        // クッキーの取得
        $cookie = $serverRequest->getCookieParams(); // $_COOKIE
    }
}
```

### アップロードファイル

```php
use Psr\Http\Message\UploadedFileInterface;
use Ray\HttpMessage\Annotation\UploadFiles;

class Index extends ResourceObject
{
    /**
     * @UploadFiles
     */
    public function __construct(array $files)
    {
        // ファイル名の取得
        $file = $files['my-form']['details']['avatar'][0];
        /* @var UploadedFileInterface $file */
        $name = $file->getClientFilename(); // my-avatar3.png
    }
}
```

### URI

```php
use Psr\Http\Message\UriInterface;

class Index extends ResourceObject
{
    public function __construct(UriInterface $uri)
    {
        // ホスト名の取得
        $host = $uri->getHost();
    }
}
```

## PSR-7ミドルウェア

既存のBEAR.Sundayアプリケーションは、特別な変更なしに[PSR-7](http://www.php-fig.org/psr/psr-7/)ミドルウェアとして動作させることができます。

以下のコマンドで`bear/middleware`を追加して、ミドルウェアとして動作させるための[bootstrapスクリプト](https://github.com/bearsunday/BEAR.Middleware/blob/1.x/bootstrap/bootstrap.php)に置き換えます：

```bash
composer require bear/middleware
cp vendor/bear/middleware/bootstrap/bootstrap.php bootstrap/bootstrap.php
```

次にスクリプトの`__PACKAGE__\__VENDOR__`をアプリケーションの名前に変更すれば完了です：

```bash
php -S 127.0.0.1:8080 -t public
```

### ストリーム

ミドルウェアに対応したBEAR.Sundayのリソースは[ストリーム](http://php.net/manual/ja/intro.stream.php)の出力に対応しています。HTTP出力は`StreamTransfer`が標準です。詳しくは[ストリーム出力](http://bearsunday.github.io/manuals/1.0/ja/stream.html)をご覧ください。

### 新規プロジェクト

新規でPSR-7のプロジェクトを始めることもできます：

```bash
composer create-project bear/project my-awesome-project
cd my-awesome-project/
php -S 127.0.0.1:8080 -t public
```

### PSR-7ミドルウェア

* [oscarotero/psr7-middlewares](https://github.com/oscarotero/psr7-middlewares)

---

[^1]: [PSR-7 HTTP message interfaces 日本語訳（by RitoLabo）](https://www.ritolab.com/entry/102)



# JavaScript UI

ビューのレンダリングをTwigなどのPHPのテンプレートエンジンが行う代わりに、サーバーサイドのJavaScriptが実行します。PHP側は認証・認可・初期状態・APIの提供を行い、JavaScriptがUIをレンダリングします。既存のプロジェクトの構造で、アノテーションが付与されたリソースのみに適用されるため、導入が容易です。

## 前提条件

* PHP 7.1以上
* [Node.js](https://nodejs.org/ja/)
* [yarn](https://yarnpkg.com/)
* [V8Js](http://php.net/manual/ja/book.v8js.php)（開発時はオプション）

注：V8Jsがインストールされていない場合、Node.jsでJavaScriptが実行されます。

## 用語

* **CSR**: クライアントサイドレンダリング（Webブラウザで描画）
* **SSR**: サーバーサイドレンダリング（サーバーサイドのV8またはNode.jsが描画）

## JavaScript

### インストール

プロジェクトに`koriym/ssr-module`をインストールします：

```bash
# 新規プロジェクトの場合
# composer create-project bear/skeleton MyVendor.MyProject; cd MyVendor.MyProject
composer require bear/ssr-module
```

UIスケルトンアプリケーション`koriym/js-ui-skeleton`をインストールします：

```bash
composer require koriym/js-ui-skeleton 1.x-dev
cp -r vendor/koriym/js-ui-skeleton/ui .
cp -r vendor/koriym/js-ui-skeleton/package.json .
yarn install
```

### UIアプリケーションの実行

まずはデモアプリケーションを動かしてみましょう。表示されたWebページからレンダリング方法を選択して、JavaScriptアプリケーションを実行します：

```bash
yarn run ui
```

このアプリケーションの入力は`ui/dev/config/`の設定ファイルで行います：

```php
<?php
$app = 'index';                   // index.bundle.jsを指定
$state = [                        // アプリケーションステート
    'hello' => ['name' => 'World']
];
$metas = [                        // SSRでのみ必要な値
    'title' => 'page-title'
];

return [$app, $state, $metas];
```

設定ファイルをコピーして、入力値を変更してみましょう：

```bash
cp ui/dev/config/index.php ui/dev/config/myapp.php
```

ブラウザをリロードして新しい設定を試します。このように、JavaScriptや本体のPHPアプリケーションを変更せずに、UIのデータを変更して動作を確認することができます。

このセクションで編集したPHPの設定ファイルは、`yarn run ui`で実行する時のみに使用されます。PHP側が必要とするのは、バンドルされて出力されたJavaScriptファイルのみです。

### UIアプリケーションの作成

PHPから渡された引数を使ってレンダリングした文字列を返す**render**関数を作成します：

```javascript
const render = (state, metas) => (
    __AWESOME_UI__ // SSR対応のライブラリやJSのテンプレートエンジンを使って文字列を返す
);
```

`state`はドキュメントルートに必要な値、`metas`はそれ以外の値（例えば`<head>`で使う値など）です。`render`という関数名は固定です。

ここでは名前を受け取って挨拶を返す関数を作成します：

```javascript
const render = state => (
    `Hello ${state.name}`
);
```

`ui/src/page/index/hello/server.js`として保存して、webpackのエントリーポイントを`ui/entry.js`に登録します：

```javascript
module.exports = {
    hello: 'src/page/hello/server'
};
```

これで`hello.bundle.js`というバンドルされたファイルが出力されるようになりました。

このhelloアプリケーションをテスト実行するためのファイルを`ui/dev/config/myapp.php`に作成します：

```php
<?php
$app = 'hello';
$state = [
    ['name' => 'World']
];
$metas = [];

return [$app, $state, $metas];
```

以上です！ブラウザをリロードして試してください。

render関数内では、ReactやVue.jsなどのUIフレームワークを使ってリッチなUIを作成できます。

通常のアプリケーションでは、依存を最小限にするために`server.js`エントリーファイルは以下のようにrenderモジュールを読み込むようにします：

```javascript
import render from './render';
global.render = render;
```

ここまでPHP側の作業はありません。SSRのアプリケーション開発は、PHP開発と独立して行うことができます。

## PHP

### モジュールインストール

AppModuleに`SsrModule`モジュールをインストールします：

```php
<?php
use BEAR\SsrModule\SsrModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $build = dirname(__DIR__, 2) . '/var/www/build';
        $this->install(new SsrModule($build));
    }
}
```

`$build`フォルダはJavaScriptファイルがあるディレクトリです（`ui/ui.config.js`で指定するwebpackの出力先）。

### @Ssrアノテーション

リソースをSSRするメソッドに`@Ssr`とアノテートします。`app`にJavaScriptアプリケーション名を指定する必要があります：

```php
<?php
namespace MyVendor\MyRedux\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\SsrModule\Annotation\Ssr;

class Index extends ResourceObject
{
    /**
     * @Ssr(app="index_ssr")
     */
    public function onGet($name = 'BEAR.Sunday')
    {
        $this->body = [
            'hello' => ['name' => $name]
        ];
        return $this;
    }
}
```

`$this->body`が`render`関数の第1引数として渡されます。

CSRとSSRの値を区別して渡したい場合は、`state`と`metas`でbodyのキーを指定します：

```php
/**
 * @Ssr(
 *     app="index_ssr",
 *     state={"name", "age"},
 *     metas={"title"}
 * )
 */
public function onGet()
{
    $this->body = [
        'name' => 'World',
        'age' => 4.6E8,
        'title' => 'Age of the World'
    ];
    return $this;
}
```

実際に`state`と`metas`をどのように渡してSSRを実現するかは、`ui/src/page/index/server`のサンプルアプリケーションをご覧ください。

影響を受けるのはアノテートしたメソッドだけで、APIやHTMLのレンダリングの設定はそのままです。

### PHPアプリケーションの実行設定

`ui/ui.config.js`を編集して、`public`にWeb公開ディレクトリを、`build`にwebpackのビルド先を指定します。`build`は`SsrModule`のインストール時に指定したディレクトリと同じにします：

```javascript
const path = require('path');

module.exports = {
    public: path.join(__dirname, '../var/www'),
    build: path.join(__dirname, '../var/www/build')
};
```

### PHPアプリケーションの実行

```bash
yarn run dev
```

ライブアップデートで実行します。PHPファイルの変更があれば自動でリロードされ、Reactのコンポーネントに変更があれば、リロードなしでコンポーネントがアップデートされます。

ライブアップデートなしで実行する場合は`yarn run start`を実行します。

`lint`や`test`などの他のコマンドについては、[コマンド](https://github.com/koriym/Koriym.JsUiSkeleton/blob/1.x/README.ja.md#コマンド)をご覧ください。

## パフォーマンス

V8のスナップショットをAPCuに保存する機能を使って、パフォーマンスの大幅な向上が可能です。`ProdModule`で`ApcSsrModule`をインストールしてください。Reactやアプリケーションのスナップショットが`APCu`に保存され再利用されます。V8Jsが必要です：

```php
$this->install(new ApcSsrModule);
```

APCu以外のキャッシュを利用するには、`ApcSsrModule`のコードを参考にモジュールを作成してください。PSR-16対応のキャッシュが利用可能です。

さらなる高速化のためには、V8をコンパイルする時点でJavaScriptコード（Reactなど）のスナップショットを取り込みます。詳しくは以下をご覧ください：

* [20x performance boost with V8Js snapshots](http://stesie.github.io/2016/02/snapshot-performance)
* [v8js - Possibility to Improve Performance with Precompiled Templates/Classes?](https://github.com/phpv8/v8js/issues/205)

## デバッグ

* Chromeプラグイン[React Developer Tools](https://chrome.google.com/webstore/detail/react-developer-tools/fmkadmapgofadopljbjfkapdkoienihi)、[Redux DevTools](https://chrome.google.com/webstore/detail/redux-devtools/lmhkpmbekcpmknklioeibfkpmmfibljd)が利用できます。
* 500エラーが返ってくる場合は、`var/log`や`curl`でアクセスしてレスポンスの詳細を確認してみましょう。

## リファレンス

* [ECMAScript 6](http://postd.cc/es6-cheatsheet/)
* [Airbnb JavaScript スタイルガイド](http://mitsuruog.github.io/javascript-style-guide/)
* [React](https://facebook.github.io/react/)
* [Redux](http://redux.js.org/)
* [Redux GitHub](https://github.com/reactjs/redux)
* [Redux DevTools](https://github.com/gaearon/redux-devtools)
* [Karma テストランナー](http://karma-runner.github.io/1.0/index.html)
* [Mocha テストフレームワーク](https://mochajs.org/)
* [Chai アサーションライブラリ](http://chaijs.com/)
* [Yarn パッケージマネージャー](https://yarnpkg.com/)
* [Webpack モジュールバンドラー](https://webpack.js.org/)

## その他ビューライブラリ

* [Vue.js](https://jp.vuejs.org/)
* [Handlebars.js](http://handlebarsjs.com/)
* [doT.js](http://olado.github.io/doT/index.html)
* [Pug](https://pugjs.org/api/getting-started.html)
* [Hogan](http://twitter.github.io/hogan.js/)（Twitter）
* [Nunjucks](https://mozilla.github.io/nunjucks/)（Mozilla）
* [Dust.js](http://www.dustjs.com/)（LinkedIn）
* [Marko](http://markojs.com/)（eBay）

*以前のReact JSページは[ReactJs](reactjs.html)をご覧ください。*



# ストリーム出力

通常、リソースはレンダラーでレンダリングされて1つの文字列になり、最終的に`echo`で出力されます。しかし、この方法ではPHPのメモリ制限を超えるサイズのコンテンツは出力できません。`StreamRenderer`を使用することでHTTP出力をストリーム化でき、メモリ消費を低く抑えることができます。このストリーム出力は、既存のレンダラーと共存することも可能です。

## トランスファーとレンダラーの変更

ストリーム出力用のレンダラーとレスポンダーをインジェクトするために、ページに[StreamTransferInject](https://github.com/bearsunday/BEAR.Streamer/blob/1.x/src/StreamTransferInject.php)トレイトを`use`します。

以下のダウンロードページの例では、`$body`をストリームのリソース変数としているため、インジェクトされたレンダラーは無視され、リソースが直接ストリーム出力されます：

```php
use BEAR\Streamer\StreamTransferInject;

class Download extends ResourceObject
{
    use StreamTransferInject;

    public $headers = [
        'Content-Type' => 'image/jpeg',
        'Content-Disposition' => 'attachment; filename="image.jpg"'
    ];

    public function onGet(): static
    {
        $fp = fopen(__DIR__ . '/BEAR.jpg', 'r');
        $this->body = $fp;

        return $this;
    }
}
```

## レンダラーとの共存

ストリーム出力は従来のレンダラーと共存できます。通常、TwigレンダラーやJSONレンダラーは文字列を生成しますが、その一部にストリームをアサインすると、全体がストリームとして出力されます。

以下は、Twigテンプレートに文字列とresource変数をアサインして、インライン画像のページを生成する例です。

テンプレート：

```twig
<!DOCTYPE html>
<html lang="en">
<body>
<p>Hello, {% raw  %}{{ name }}{% endraw %}</p>
<img src="data:image/jpg;base64,{% raw  %}{{ image }}{% endraw %}">
</body>
</html>
```

`name`には通常通り文字列をアサインし、`image`には画像ファイルのファイルポインタリソースを`base64-encode`フィルターを通してアサインします：

```php
class Image extends ResourceObject
{
    use StreamTransferInject;

    public function onGet(string $name = 'inline image'): static
    {
        $fp = fopen(__DIR__ . '/image.jpg', 'r');
        stream_filter_append($fp, 'convert.base64-encode'); // 画像をbase64形式に変換
        $this->body = [
            'name' => $name,
            'image' => $fp
        ];

        return $this;
    }
}
```

ストリーミングの帯域幅やタイミングをコントロールしたり、クラウドにアップロードしたりするなど、ストリーミングをさらに制御する場合は、[StreamResponder](https://github.com/bearsunday/BEAR.Streamer/blob/1.x/src/StreamResponder.php#L45-L48)を参考にして作成し、束縛します。

ストリーム出力のデモは[MyVendor.Stream](https://github.com/bearsunday/MyVendor.Stream)で確認できます。



# Cache

> There are only two hard things in Computer Science: cache invalidation and naming things.
>
> -- Phil Karlton

## 概要

優れたキャッシュシステムは、ユーザー体験の質を本質的に向上させ、資源利用コストと環境負荷を下げます。BEAR.Sundayは従来のTTLによる単純なキャッシュに加えて、以下のキャッシュ機能をサポートしています：

* イベント駆動のキャッシュ無効化
* キャッシュの依存解決
* ドーナッツキャッシュとドーナッツの穴キャッシュ
* CDNコントロール
* 条件付きリクエスト

### 分散キャッシュフレームワーク

REST制約に従った分散キャッシュシステムは、計算資源だけでなくネットワーク資源も節約します。PHPが直接扱うRedisやAPCなどの**サーバーサイドキャッシュ**、コンテンツ配信ネットワーク（CDN）として知られる**共有キャッシュ**、WebブラウザやAPIクライアントでキャッシュされる**クライアントサイドキャッシュ**、BEAR.SundayはこれらのキャッシュとモダンCDNを統合したキャッシングフレームワークを提供します。

<img src="https://user-images.githubusercontent.com/529021/137062427-c733c832-0631-4a43-a6ee-4204e6be007c.png" alt="distributed cache">

## タグベースでのキャッシュ無効化

<img width="369" alt="dependency graph 2021-10-19 21 38 02" src="https://user-images.githubusercontent.com/529021/137910748-b6e95839-eeb7-4ade-a564-3cdcd5fdc09e.png">

コンテンツキャッシュには依存性の問題があります。コンテンツAがコンテンツBに依存し、BがCに依存している場合、Cが更新されるとCのキャッシュとETagだけでなく、Cに依存するBのキャッシュとETag、Bに依存するAのキャッシュとETagも更新されなければなりません。

BEAR.Sundayはそれぞれのリソースが依存リソースのURIをタグとして保持することで、この問題を解決します。`#[Embed]`で埋め込まれたリソースに変更があると、関係する全てのリソースのキャッシュとETagが無効化され、次のリクエストのためにキャッシュの再生成が行われます。

## ドーナッツキャッシュ

<img width="200" alt="donut caching" src="https://user-images.githubusercontent.com/529021/137097856-f9428918-5b76-4c0e-8cea-2472c15d82e9.png">

ドーナッツキャッシュは、キャッシュの最適化のための部分キャッシュ技術の1つです。コンテンツをキャッシュ可能な箇所とそうでない箇所に分けて合成します。

例えば「`Welcome to $name`」というキャッシュできないリソースが含まれるコンテンツを考えてみてください。キャッシュできない（do-not cache）部分と、その他のキャッシュ可能な部分を合成して出力します。

<img width="557" alt="image" src="https://user-images.githubusercontent.com/529021/139617102-1f7f436c-a1f4-4c6c-b90b-de24491e4c8c.png">

この場合、コンテンツ全体としては動的なので、ドーナッツ全体はキャッシュされません。そのため、ETagも出力されません。

## ドーナッツの穴キャッシュ

<img width="544" alt="image" src="https://user-images.githubusercontent.com/529021/139617571-31aea99a-533f-4b95-b3f3-6c613407d377.png">

ドーナッツの穴部分がキャッシュ可能な場合も、ドーナッツキャッシュと同じように扱えます。上記の例では、1時間に一度変更される天気予報のリソースがキャッシュされ、ニュースリソースに含まれます。

この場合、ドーナッツ全体（ニュース）としてのコンテンツは静的なので、全体もキャッシュされ、ETagも付与されます。このとき、キャッシュの依存性が発生します。ドーナッツの穴部分のコンテンツが更新された時に、キャッシュされたドーナッツ全体も再生成される必要があります。

この依存解決は自動で行われます。計算資源を最小化するため、ドーナッツ部分の計算は再利用されます。穴の部分（天気リソース）が更新されると、全体のコンテンツのキャッシュとETagも自動で更新されます。

### リカーシブ・ドーナッツ

<img width="191" alt="recursive donut 2021-10-19 21 27 06" src="https://user-images.githubusercontent.com/529021/137909083-2c5176f7-edb7-422b-bccc-1db90460fc15.png">

ドーナッツ構造は再帰的に適用されます。例えば、AがBを含み、BがCを含むコンテンツの場合、Cが変更されたときに、変更されたCの部分を除いて、AのキャッシュとBのキャッシュは再利用されます。AとBのキャッシュ、ETagは再生成されますが、A、Bのコンテンツ取得のためのDBアクセスやビューのレンダリングは行われません。

最適化された構造の部分キャッシュが、最小のコストでコンテンツ再生成を行います。クライアントはコンテンツのキャッシュ構造について知る必要がありません。

## イベントドリブン型コンテンツ

従来、CDNはアプリケーションロジックを必要とするコンテンツは「動的」であり、したがってCDNではキャッシュできないと考えられてきました。しかし、FastlyやAkamaiなどの一部のCDNは、即時または数秒以内でのタグベースでのキャッシュ無効化が可能になり、[この考えは過去のもの](https://www.fastly.com/blog/leveraging-your-cdn-cache-uncacheable-content)になろうとしています。

BEAR.Sundayの依存解決は、サーバーサイドだけでなく共有キャッシュでも行われます。AOPが変更を検知し、共有キャッシュにPURGEリクエストを行うことで、サーバーサイドと同じように共有キャッシュ上の関連キャッシュの無効化が行われます。

## 条件付きリクエスト

<img width="468" alt="conditional request" src="https://user-images.githubusercontent.com/529021/137151061-8d7a5605-3aa3-494c-91c5-c1deddd987dd.png">

コンテンツの変更はAOPで管理され、コンテンツのエンティティタグ（ETag）は自動で更新されます。ETagを使ったHTTPの条件付きリクエストは計算資源の利用を最小化するだけでなく、`304 Not Modified`を返すだけの応答はネットワーク資源の利用も最小化します。

# 利用法

キャッシュ対象のクラスにドーナッツキャッシュの場合（埋め込みコンテンツがキャッシュ不可能な場合）は`#[DonutCache]`、それ以外の場合は`#[CacheableResponse]`とアトリビュートを付与します：

```php
use BEAR\RepositoryModule\Annotation\CacheableResponse;

#[CacheableResponse]
class BlogPosting extends ResourceObject
{
    public $headers = [
        RequestHeader::CACHE_CONTROL => CacheControl::NO_CACHE
    ];

    #[Embed(rel: "comment", src: "page://self/html/comment")]
    public function onGet(int $id = 0): static
    {
        $this->body['article'] = 'hello world';
        return $this;
    }

    public function onDelete(int $id = 0): static
    {
        return $this;
    }
}
```

キャッシュ対象メソッドを選択したい場合は、クラスにアトリビュートを指定しないで、メソッドに指定します。その場合は、キャッシュ変更メソッドに`#[RefreshCache]`というアトリビュートを付与します：

```php
class Todo extends ResourceObject
{
    #[CacheableResponse]
    public function onPut(int $id = 0, string $todo): static
    {
    }

    #[RefreshCache]
    public function onDelete(int $id = 0): static
    {
    }
}
```

どちらかの方法でアトリビュートを付与すると、概要で紹介した全ての機能が適用されます。イベントドリブン型コンテンツを想定してデフォルトでは時間（TTL）によるキャッシュの無効化は行われません。`#[DonutCache]`の場合はコンテンツ全体はキャッシュされず、`#[CacheableResponse]`の場合はされることに注意してください。

## TTL

TTLの指定は`DonutRepositoryInterface::put()`で行います。`ttl`はドーナツの穴以外のキャッシュ時間、`sMaxAge`はCDNのキャッシュ時間です：

```php
use BEAR\RepositoryModule\Annotation\CacheableResponse;

#[CacheableResponse]
class BlogPosting extends ResourceObject
{
    public function __construct(private DonutRepositoryInterface $repository)
    {
    }

    #[Embed(rel: "comment", src: "page://self/html/comment")]
    public function onGet(): static
    {
        // process ...
        $this->repository->put($this, ttl: 10, sMaxAge: 100);　
        return $this;
    }
}
```

### TTLの既定値

イベントドリブン型コンテンツでは、コンテンツが変更されたらキャッシュにすぐに反映されなければなりません。そのため、既定値のTTLはCDNのモジュールのインストールによって変わります。

CDNがタグベースでのキャッシュ無効化をサポートしていれば、TTLは無期限（1年間）です。サポートのない場合は10秒です。キャッシュ反映時間は、Fastlyなら即時、Akamaiなら数秒、それ以外なら10秒が期待される時間です。

カスタマイズするには`CdnCacheControlHeader`を参考に`CdnCacheControlHeaderSetterInterface`を実装して束縛します。

## キャッシュ無効化

手動でキャッシュを無効化するには`DonutRepositoryInterface`のメソッドを用います。指定されたキャッシュだけでなく、そのETag、依存にしている他のリソースのキャッシュとそのETagが、サーバーサイドおよび可能な場合はCDN上のキャッシュも共に無効化されます：

```php
interface DonutRepositoryInterface
{
    public function purge(AbstractUri $uri): void;
    public function invalidateTags(array $tags): void;
}
```

### URIによる無効化

```php
// example
$this->repository->purge(new Uri('app://self/blog/comment'));
```

### タグによる無効化

```php
$this->repository->invalidateTags(['template_a', 'campaign_b']);
```

### CDNでタグの無効化

CDNでタグベースでのキャッシュ無効化を有効にするためには、`PurgerInterface`を実装して束縛する必要があります：

```php
use BEAR\QueryRepository\PurgerInterface;

interface PurgerInterface
{
    public function __invoke(string $tag): void;
}
```

### 依存タグの指定

PURGE用のキーを指定するためには`SURROGATE_KEY`ヘッダーで指定します。複数文字列の場合はスペースをセパレータとして使用します：

```php
use BEAR\QueryRepository\Header;

class Foo
{
    public $headers = [
        Header::SURROGATE_KEY => 'template_a campaign_b'
    ];
}
```

`template_a`または`campaign_b`のタグによるキャッシュの無効化が行われた場合、FooのキャッシュとFooのETagはサーバーサイド、CDN共に無効になります。

### リソースの依存

`UriTagInterface`を使ってURIを依存タグ文字列に変換します：

```php
public function __construct(private UriTagInterface $uriTag)
{
}
```

```php
$this->headers[Header::SURROGATE_KEY] = ($this->uriTag)(new Uri('app://self/foo'));
```

`app://self/foo`に変更があった場合、このキャッシュはサーバーサイド、CDN共に無効化されます。

### 連想配列をリソースの依存に

```php
// bodyの内容
[
    ['id' => '1', 'name' => 'a'],
    ['id' => '2', 'name' => 'b'],
]
```

上記のような`body`連想配列から、依存するURIタグリストを生成する場合は`fromAssoc()`メソッドでURIテンプレートを指定します：

```php
$this->headers[Header::SURROGATE_KEY] = $this->uriTag->fromAssoc(
    uriTemplate: 'app://self/item{?id}',
    assoc: $this->body
);
```

上記の場合、`app://self/item?id=1`および`app://self/item?id=2`に変更があった場合に、このキャッシュはサーバーサイド、CDN共に無効化されます。

## CDN特定

特定CDN対応のモジュールをインストールすると、ベンダー固有のヘッダーが出力されます：

```php
$this->install(new FastlyModule());
$this->install(new AkamaiModule());
```

## マルチCDN

CDNを多段構成にして、役割に応じたTTLを設定することもできます。例えば以下の図では、上流に多機能なCDNを配置して、下流にはコンベンショナルなCDNを配置しています。コンテンツの無効化などは上流のCDNに対して行い、下流のCDNはそれを利用するようにします。

<img width="344" alt="multi cdn diagram" src="https://user-images.githubusercontent.com/529021/137098809-ec949a15-8efb-4d03-9808-3be15523ade7.png">

# レスポンスヘッダー

CDNのキャッシュコントロールについてはBEAR.Sundayが自動で行い、CDN用のヘッダーを出力します。クライアントのキャッシュコントロールはコンテンツに応じて`ResourceObject`の`$header`に記述します。

セキュリティやメンテナンスの観点から、このセクションは重要です。全ての`ResourceObject`で`Cache-Control`を指定するようにしましょう。

### キャッシュ不可

キャッシュができないコンテンツは必ず指定しましょう：

```php
ResponseHeader::CACHE_CONTROL => CacheControl::NO_STORE
```

### 条件付きリクエスト

サーバーにコンテンツ変更がないかを確認してから、キャッシュを利用します。サーバーサイドのコンテンツの変更は検知され反映されます：

```php
ResponseHeader::CACHE_CONTROL => CacheControl::NO_CACHE
```

### クライアントキャッシュ時間の指定

クライアントでキャッシュされます。最も効率的なキャッシュですが、サーバーサイドでコンテンツが変更されても指定した時間には反映されません。またブラウザのリロード動作ではこのキャッシュは利用されません。`<a>`タグで遷移、またはURL入力した場合にキャッシュが利用されます：

```php
ResponseHeader::CACHE_CONTROL => 'max-age=60'
```

レスポンス速度を重視する場合には、SWRの指定も検討しましょう：

```php
ResponseHeader::CACHE_CONTROL => 'max-age=30 stale-while-revalidate=10'
```

この場合、max-ageの30秒を超えた時にオリジンサーバーからフレッシュなレスポンス取得が完了するまで、SWRで指定された最大10秒間はそれまでの古いキャッシュ（stale）レスポンスを返します。つまりキャッシュが更新されるのは最後のキャッシュ更新から30秒から40秒間の間のいずれかになりますが、どのリクエストもキャッシュからの応答になり高速です。

#### RFC7234対応クライアント

APIでクライアントキャッシュを利用する場合には、RFC7234対応APIクライアントを利用します：

* iOS: [NSURLCache](https://nshipster.com/nsurlcache/)
* Android: [HttpResponseCache](https://developer.android.com/reference/android/net/http/HttpResponseCache)
* PHP: [guzzle-cache-middleware](https://github.com/Kevinrob/guzzle-cache-middleware)
* JavaScript(Node): [cacheable-request](https://www.npmjs.com/package/cacheable-request)
* Go: [lox/httpcache](https://github.com/lox/httpcache)
* Ruby: [faraday-http-cache](https://github.com/plataformatec/faraday-http-cache)
* Python: [requests-cache](https://pypi.org/project/requests-cache/)

### プライベートキャッシュ

他のクライアントと共有しない場合には`private`を指定します。クライアントサイドのみキャッシュが保存されます。この場合、サーバーサイドではキ


```php
ResponseHeader::CACHE_CONTROL => 'private, max-age=30'
```

## キャッシュ設計

API（またはコンテンツ）は**情報API**（Information API）と**計算API**（Computation API）の2つに分類できます。計算APIは再現が難しく真に動的でキャッシュに不適なコンテンツです。一方の情報APIはDBから読み出され、PHPで加工されたとしても本質的には静的なコンテンツのAPIです。

適切なキャッシュを適用するためにコンテンツを分析します：

* 情報APIか計算APIか
* 依存関係は何か
* 内包関係は何か
* 無効化はイベントがトリガーか、それともTTLか
* イベントはアプリケーションが検知可能か、監視が必要か
* TTLは予測可能か不可能か

キャッシュ設計をアプリケーション設計プロセスの一部として捉え、仕様に含めることも検討しましょう。ライフサイクルを通してプロジェクトの安全性にも寄与するはずです。

### アダプティブTTL

コンテンツの生存期間が予測可能で、その期間にイベントによる更新が行われない場合は、それをクライアントやCDNに正しく伝えます。

例えば株価のAPIを扱う場合、現在が金曜日の夜だとすると月曜の取引開始時間までは情報更新が行われないことが分かっています。その時間までの秒数を計算してTTLとして指定し、取引時間の時には適切なTTLを指定します。クライアントは更新がないと分かっているリソースにリクエストする必要はありません。

## #[Cacheable]

従来の#[Cacheable]によるTTLキャッシュもサポートされます。

例）サーバーサイドで30秒キャッシュ、クライアントでも30秒キャッシュ。サーバーサイドで指定しているので、クライアントサイドでも同じ秒数でキャッシュされます：

```php
use BEAR\RepositoryModule\Annotation\Cacheable;

#[Cacheable(expirySecond: 30)]
class CachedResource extends ResourceObject
{
```

例）指定した有効期限（`$body['expiry_at']`の日付）まで、サーバー、クライアント共にキャッシュ：

```php
use BEAR\RepositoryModule\Annotation\Cacheable;

#[Cacheable(expiryAt: 'expiry_at')]
class CachedResource extends ResourceObject
{
```

その他は[HTTPキャッシュ](https://bearsunday.github.io/manuals/1.0/ja/http-cache.html)ページをご覧ください。

## 結論

Webのコンテンツには情報（データ）型のものと計算（プロセス）型のものがあります。前者は本質的には静的ですが、コンテンツの変更や依存性の管理の問題で完全に静的コンテンツとして扱うのが難しく、コンテンツの変更が発生していないのにTTLによるキャッシュの無効化が行われていました。

BEAR.Sundayのキャッシングフレームワークは、情報型のコンテンツを可能な限り静的に扱い、キャッシュの力を最大化します。

## 用語

* [条件付きリクエスト](https://developer.mozilla.org/ja/docs/Web/HTTP/Conditional_requests)
* [ETag（バージョン識別子）](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/ETag)
* [イベントドリブン型コンテンツ](https://www.fastly.com/blog/rise-event-driven-content-or-how-cache-more-edge)
* [ドーナッツキャッシュ / 部分キャッシュ](https://www.infoq.com/jp/news/2011/12/MvcDonutCaching/)
* [サロゲートキー / タグベースの無効化](https://docs.fastly.com/ja/guides/getting-started-with-surrogate-keys)
* ヘッダー
  * [Cache-Control](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/Cache-Control)
  * [CDN-Cache-Control](https://blog.cloudflare.com/cdn-cache-control/)
  * [Vary](https://developer.mozilla.org/ja/docs/Web/HTTP/Headers/Vary)
  * [Stale-While-Revalidate（SWR）](https://www.infoq.com/jp/news/2020/12/ux-stale-while-revalidate/)



# Swoole

SwooleとはC/C++で書かれたPHP拡張の1つで、イベント駆動の非同期＆コルーチンベースの並行処理ネットワーキング通信エンジンです。
Swooleを使ってコマンドラインから直接BEAR.Sundayウェブアプリケーションを実行することができます。パフォーマンスが大幅に向上します。

## インストール

### Swooleのインストール

PECL経由:

```bash
pecl install swoole
```

ソースから:

```bash
git clone https://github.com/swoole/swoole-src.git && \
cd swoole-src && \
phpize && \
./configure && \
make && make install
```

`php.ini`で`extension=swoole.so`を追加してください。

### BEAR.Swooleのインストール

```bash
composer require bear/swoole ^0.4
```

`AppModule`でのインストールは必要ありません。

`bin/swoole.php`にスクリプトを設置します。

```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/vendor/bear/swoole/bootstrap.php')(
    'prod-hal-app',       // context
    'MyVendor\MyProject', // application name
    '127.0.0.1',          // IP
    8080                  // port
));
```

## 実行

サーバーをスタートさせます。

```bash
php bin/swoole.php
```

実行すると以下のメッセージが表示されます:

```
Swoole http server is started at http://127.0.0.1:8088
```

## ベンチマークサイト

特定環境でベンチマークテストをするための[BEAR.HelloworldBenchmark](https://github.com/bearsunday/BEAR.HelloworldBenchmark)が用意されています。

* [The benchmark result](https://github.com/bearsunday/BEAR.HelloworldBenchmark/wiki)

[<img src="https://github.com/swoole/swoole-src/raw/master/mascot.png">](https://github.com/swoole/swoole-src)



# コーディングガイド

## プロジェクト

`vendor`は会社の名前やチームの名前または個人の名前（`excite`, `koriym`等）を指定して、`package`にはアプリケーション（サービス）の名前（`blog`, `news`等）を指定します。
プロジェクトはアプリケーション単位で作成し、Web APIとHTMLを別ホストでサービスする場合でも1つのプロジェクトにします。

## スタイル

[PSR1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md), [PSR2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), [PSR4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)に準拠します。

```php
<?php
namespace Koriym\Blog\Resource\App;

use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

#[CacheableResponse]
class Entry extends ResourceObject
{
    public function __construct(
        private readonly ExtendPdoInterface $pdo,
        private readonly ResourceInterface $resource
    ) {}

    #[Embed(rel: "author", src: "/author{?author_id}")]
    public function onGet(string $author_id, string $slug): static
    {
        // ...
        return $this;
    }

    #[Link(rel: "next_action1", href: "/next_action1")]
    public function onPost(
        string $tile,
        string $body,
        string $uid,
        string $slug
    ): static {
        // ...
        $this->code = Code::CREATED;
        return $this;
    }
}
```

リソースの[docBlockコメント](https://phpdoc.org/docs/latest/getting-started/your-first-set-of-documentation.html)はオプションです。リソースURIや引数名だけで説明不十分な時にメソッドの要約（一行）、説明（複数行可）、`@params`を付加します。

```php
/**
 * A summary informing the user what the associated element does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 *
 * @param string $arg1 *description*
 * @param string $arg2 *description*
*/
```

## リソース

リソースについてのベストプラクティスは[リソースのベストプラクティス](resource_bp.html)をご覧ください。

### コード

適切なステータスコードを返します。テストが容易になり、botやクローラーにも正しい情報が伝えることができます。

* `100` Continue 複数のリクエストの継続
* `200` OK
* `201` Created リソース作成
* `202` Accepted キュー/バッチ 受付
* `204` No Content bodyがない場合
* `304` Not Modified 未更新
* `400` Bad Request リクエストに不備
* `401` Unauthorized 認証が必要
* `403` Forbidden 禁止
* `404` Not Found
* `405` Method Not Allowed
* `503` Service Unavailable サーバーサイドでの一時的エラー

`304`は`#[Cacheable]`アトリビュートを使っていると自動設定されます。`404`はリソースクラスがない場合、`405`はリソースのメソッドがない場合に自動設定されます。またDBの接続エラーなどは必ず`503`で返しクローラーに伝えます。

### HTMLのFormメソッド

BEAR.SundayはHTMLのWebフォームで`POST`リクエストの時に`X-HTTP-Method-Override`ヘッダーや`_method`クエリーを用いてメソッドを上書きする事ができますが、推奨しているわけではありません。Pageリソースでは`onGet`と`onPost`以外を実装しない方針でも問題ありません。

### ハイパーリンク

* リンクを持つリソースは`#[Link]`で示すことが推奨されます。
* リソースは意味のまとまりのグラフにして`#[Embed]`で埋め込む事が推奨されます。

## グローバル

グローバルな値をリソースやアプリケーションのクラスで参照することは推奨されません。(Modulesでのみ使用します)

* [スーパーグローバル](http://php.net/manual/ja/language.variables.superglobals.php)の値を参照しない
* [define](http://php.net/manual/ja/function.define.php)は使用しない
* 設定値を保持する`Config`クラスを作成しない
* グローバルなオブジェクトコンテナ（サービスロケータ）を使用しない
* [date](http://php.net/manual/ja/function.date.php)関数や[DateTime](http://php.net/manual/ja/class.datetime.php)クラスで現在時刻を直接取得することは推奨されません。外部から時刻をインジェクトします。[^now]
* スタティックメソッドなどのグローバルなメソッドコールも推奨されません。
* アプリケーションコードが必要とする値は設定ファイルなどから取得するのではなく、全てインジェクトします。[^inject-all]

[^now]: [koriym/now](https://github.com/koriym/Koriym.Now)
[^inject-all]: Web APIなど外部のシステムの値を利用する時には、クライアントクラスやWeb APIアクセスリソースなど１つにの場所に集中させDIやAOPでモッキングが容易にするようにします。

## クラスとオブジェクト

* [トレイト](http://php.net/manual/ja/language.oop5.traits.php)は推奨されません。[^no-trait]
* 親クラスのメソッドを子クラスが使うことは推奨されません。共通する機能は継承やtraitで共有ではなくクラスにしてインジェクトして使います。[継承より合成](https://en.wikipedia.org/wiki/Composition_over_inheritance)します。

[^no-trait]: `ResourceInject`などのインジェクション用トレイトはインジェクションのボイラープレートコードを削減するために存在しましたが、PHP8で追加された[コンストラクタの引数をプロパティへ昇格させる機能](https://www.php.net/manual/ja/language.oop5.decon.php#language.oop5.decon.constructor.promotion)により意味を失いました。コンストラクタインジェクションを使いましょう。

## DI

* 実行コンテキスト(prod, devなど)の値そのものをインジェクトしてはいけません。代わりにコンテキストに応じたインスタンスをインジェクトします。アプリケーションはどのコンテキストで動作しているのか無知にします。
* ライブラリコードではセッターインジェクションは推奨されません。
* `Provider`束縛を可能な限り避け`toConstructor`束縛を優先することが推奨されます。
* `Module`で条件に応じて束縛をすることを避けます。([AvoidConditionalLogicInModules](https://github.com/google/guice/wiki/AvoidConditionalLogicInModules))
* モジュールの`configure()`から環境変数を参照しないで、コンストラクタインジェクションにします。

## AOP

* インターセプターの適用を必須にしてはいけません。例えばログやDBのトランザクションなどはインターセプターの有無でプログラムの本質的な動作は変わりません。
* メソッド内の依存をインターセプターがインジェクトしないようにします。メソッド実装時にしか決定できない値は`@Assisted`インジェクションで引数にインジェクトします。
* 複数のインタセプターがある場合にその実行順に可能な限り依存しないようにします。
* 無条件に全メソッドに適用するインターセプターであれば`bootstrap.php`での記述を考慮してください。
* 横断的関心事と、本質的関心事を分けるために使われるものです。特定のメソッドのhackのためにインターセプトするような使い方は推奨されません。

## スクリプトコマンド

* `composer setup`コマンドでアプリケーションのセットアップが完了することが推奨されます。このスクリプトではデータベースの初期化、必要ライブラリの確認が含まれます。`.env`の設定などマニュアルな操作が必要な場合はその手順が画面表示されることが推奨されます。

## 環境

* Webだけでしか動作しないアプリケーションは推奨されません。テスト可能にするためにコンソールでも動作するようにします。
* `.env`ファイルをプロジェクトリポジトリに含まない事が推奨されます。
* `.env`の代わりにスキーマを記述する[Koriym.EnvJson](https://github.com/koriym/Koriym.EnvJson)の利用を検討してください。

## テスト

* リソースクライアントを使ったリソーステストを中心にし、必要があればリソースの表現のテスト(HTMLなど)を加えます。
* ハイパーメディアテストはユースケースをテストとして残すことができます。
* `prod`はプロダクション用のコンテキストです。テストで`prod`コンテキストの利用は最低限、できれば無しにしましょう。

## HTMLテンプレート

* 大きなループ文を避けます。ループの中のif文は[ジェネレーター](https://www.php.net/manual/ja/language.generators.overview.php)で置き換えれないか検討しましょう。



# クイックスタート

インストールは [composer](http://getcomposer.org) で行います。

```bash
composer create-project -n bear/skeleton MyVendor.MyProject
cd MyVendor.MyProject
```

次にPageリソースを作成します。PageリソースはWebページに対応したクラスです。`src/Resource/Page/Hello.php`に作成します。

```php
<?php

namespace MyVendor\MyProject\Resource\Page;

use BEAR\Resource\ResourceObject;

class Hello extends ResourceObject
{
    public function onGet(string $name = 'BEAR.Sunday'): static
    {
        $this->body = [
            'greeting' => 'Hello ' . $name
        ];

        return $this;
    }
}
```

GETメソッドでリクエストされると`$name`に`$_GET['name']`が渡されるので、挨拶を`greeting`にセットし`$this`を返します。

作成したアプリケーションはコンソールでもWebサーバーでも動作します。

```bash
php bin/page.php get /hello
php bin/page.php get '/hello?name=World'
```

```bash
200 OK
Content-Type: application/hal+json

{
    "greeting": "Hello World",
    "_links": {
        "self": {
            "href": "/hello?name=World"
        }
    }
}
```

ビルトインウェブサーバーを起動し

```bash
php -S 127.0.0.1:8080 -t public
```

webブラウザまたはcurlコマンドで[http://127.0.0.1:8080/hello](http://127.0.0.1:8080/hello)をリクエストします。

```bash
curl -i 127.0.0.1:8080/hello
```


# PHPDocタイプ

PHPは動的型付け言語ですが、psalmやphpstanといった静的解析ツールとPHPDocを使用することで、高度な型概念を表現し、静的解析時の型チェックの恩恵を受けることができます。このリファレンスでは、PHPDocで使用可能な型や関連する他の概念について説明します。

## 目次

1. [アトミック型](#アトミック型)
   - [スカラー型](#スカラー型)
   - [オブジェクト型](#オブジェクト型)
   - [配列型](#配列型)
   - [Callable型](#callable型)
   - [値型](#値型)
   - [特殊型](#特殊型)
2. [複合型](#複合型)
   - [ユニオン型](#ユニオン型)
   - [交差型](#交差型)
3. [高度な型システム](#高度な型システム)
   - [ジェネリック型](#ジェネリック型)
   - [テンプレート型](#テンプレート型)
   - [条件付き型](#条件付き型)
   - [型エイリアス](#型エイリアス)
   - [型の制約](#型の制約)
   - [共変性と反変性](#共変性と反変性)
4. [型の演算子（ユーティリティ型）](#型の演算子)
  - [キー取得型と値取得型（key-of と value-of）](#キー取得型と値取得型)
  - [プロパティ取得型（properties-of）](#プロパティ取得型)
  - [クラス名マッピング型（class-string-map<T of Foo, T>）](#クラス名マッピング型)
  - [インデックスアクセス型（T[K]）](#インデックスアクセス型)
5. [関数型プログラミングの概念](#関数型プログラミングの概念)
   - [純粋関数](#純粋関数)
   - [不変オブジェクト](#不変オブジェクト)
   - [副作用の注釈](#副作用の注釈)
   - [高階関数](#高階関数)
6. [アサート注釈](#アサート注釈)
7. [セキュリティ注釈](#セキュリティ注釈)
8. [例：デザインパターンでの型の使用](#例：デザインパターンでの型の使用)

---

## アトミック型

これ以上分割できない基本的な型です。

### スカラー型

```php
/** @param int $i */
/** @param float $f */
/** @param string $str */
/** @param lowercase-string $lowercaseStr */
/** @param non-empty-string $nonEmptyStr */
/** @param non-empty-lowercase-string $nonEmptyLowercaseStr */
/** @param class-string $class */
/** @param class-string<AbstractFoo> $fooClass */
/** @param callable-string $callable */
/** @param numeric-string $num */ 
/** @param bool $isSet */
/** @param array-key $key */
/** @param numeric $num */
/** @param scalar $a */
/** @param positive-int $positiveInt */
/** @param negative-int $negativeInt */
/** @param int-range<0, 100> $percentage */
/** @param int-mask<1, 2, 4> $flags */
/** @param int-mask-of<MyClass::CLASS_CONSTANT_*> $classFlags */
/** @param trait-string $trait */
/** @param enum-string $enum */
/** @param literal-string $literalStr */
/** @param literal-int $literalInt */
```

[複合型](#複合型)や[高度な型システム](#高度な型システムと使用パターン)でこれらの型を組み合わせて使用できます。

### オブジェクト型

```php
/** @param object $obj */
/** @param stdClass $std */
/** @param Foo\Bar $fooBar */
/** @param object{foo: string, bar?: int} $objWithProperties */
/** @return ArrayObject<int, string> */
/** @param Collection<User> $users */
/** @return Generator<int, string, mixed, void> */
```

オブジェクト型は[ジェネリック型](#ジェネリック型)と組み合わせて使用することができます。

### 配列型

#### ジェネリック配列

```php
/** @return array<TKey, TValue> */
/** @return array<int, Foo> */
/** @return array<string, int|string> */
/** @return non-empty-array<string, int> */
```

ジェネリック配列は[ジェネリック型](#ジェネリック型)の概念を使用しています。

#### オブジェクト風配列

```php
/** @return array{0: string, 1: string, foo: stdClass, 28: false} */
/** @return array{foo: string, bar: int} */
/** @return array{optional?: string, bar: int} */
```

#### リスト

```php
/** @param list<string> $stringList */
/** @param non-empty-list<int> $nonEmptyIntList */
```

#### PHPDoc配列（レガシー表記）

```php
/** @param string[] $strings */
/** @param int[][] $nestedInts */
```

### Callable型

```php
/** @return callable(Type1, OptionalType2=, SpreadType3...): ReturnType */
/** @return Closure(bool):int */
/** @param callable(int): string $callback */
```

Callable型は[高階関数](#高階関数)で特に重要です。

### 値型

```php
/** @return null */
/** @return true */
/** @return false */
/** @return 42 */
/** @return 3.14 */
/** @return "specific string" */
/** @param Foo\Bar::MY_SCALAR_CONST $const */
/** @param A::class|B::class $classNames */
```

### 特殊型

```php
/** @return void */
/** @return never */
/** @return empty */
/** @return mixed */
/** @return resource */
/** @return closed-resource */
/** @return iterable<TKey, TValue> */
```

## 複合型

複数の[アトミック型](#アトミック型)を組み合わせて作成される型です。

### ユニオン型

```php
/** @param int|string $id */
/** @return string|null */
/** @var array<string|int> $mixedArray */
/** @return 'success'|'error'|'pending' */
```

### 交差型

```php
/** @param Countable&Traversable $collection */
/** @param Renderable&Serializable $object */
```

交差型は[デザインパターン](#デザインパターンでの型の使用)の実装で役立つことがあります。

## 高度な型システムと使用パターン

より複雑で柔軟な型表現を可能にする高度な機能です。

### ジェネリック型

```php
/**
 * @template T
 * @param array<T> $items
 * @param callable(T): bool $predicate
 * @return array<T>
 */
function filter(array $items, callable $predicate): array {
    return array_filter($items, $predicate);
}
```

ジェネリック型は[高階関数](#高階関数)と組み合わせて使用されることが多いです。

### テンプレート型

```php
/**
 * @template T of object
 * @param class-string<T> $className
 * @return T
 */
function create(string $className)
{
    return new $className();
}
```

テンプレート型は[型の制約](#型の制約)と組み合わせて使用できます。

### 条件付き型

```php
/**
 * @template T
 * @param T $value
 * @return (T is string ? int : string)
 */
function processValue($value) {
    return is_string($value) ? strlen($value) : strval($value);
}
```

条件付き型は[ユニオン型](#ユニオン型)と組み合わせて使用されることがあります。

### 型エイリアス

```php
/**
 * @psalm-type UserId = positive-int
 * @psalm-type UserData = array{id: UserId, name: string, email: string}
 */

/**
 * @param UserData $userData
 * @return UserId
 */
function createUser(array $userData): int {
    // ユーザー作成ロジック
    return $userData['id'];
}
```

型エイリアスは複雑な型定義を簡略化するのに役立ちます。

### 型の制約

型パラメータに制約を加えることで、より具体的な型の要件を指定できます。

```php
/**
 * @template T of \DateTimeInterface
 * @param T $date
 * @return T
 */
function cloneDate($date) {
    return clone $date;
}

// 使用例
$dateTime = new DateTime();
$clonedDateTime = cloneDate($dateTime);
```

この例では、`T`は`\DateTimeInterface`を実装したクラスに制限されています。

### 共変性と反変性

ジェネリック型を扱う際に、[共変性（covariance）と反変性（contravariance](https://www.php.net/manual/ja/language.oop5.variance.php)）の概念が重要になります。

```php
/**
 * @template-covariant T
 */
interface Producer {
    /** @return T */
    public function produce();
}

/**
 * @template-contravariant T
 */
interface Consumer {
    /** @param T $item */
    public function consume($item);
}

// 使用例
/** @var Producer<Dog> $dogProducer */
/** @var Consumer<Animal> $animalConsumer */
```

共変性は、より派生した型（サブタイプ）を使用できることを意味し、反変性はより基本的な型（スーパータイプ）を使用できることを意味します。

## 型の演算子

型の演算子を使用して、既存の型から新しい型を生成できます。psalmではユーティリティ型と呼んでいます。


### キー取得型と値取得型

- `key-of` は、指定された配列またはオブジェクトのすべてのキーの型を取得し、`value-of` はその値の型を取得します。

```php
/**
 * @param key-of<UserData> $key
 * @return value-of<UserData>
 */
function getUserData(string $key) {
    $userData = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];
    return $userData[$key] ?? null;
}

/**
 * @return ArrayIterator<key-of<UserData>, value-of<UserData>>
 */
function getUserDataIterator() {
    $userData = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];
    return new ArrayIterator($userData);
}
```

### プロパティ取得型

`properties-of` は、クラスのすべてのプロパティの型を表します。これは、クラスのプロパティを動的に扱う場合に有用です。

```php
class User {
    public int $id;
    public string $name;
    public ?string $email;
}

/**
 * @param User $user
 * @param key-of<properties-of<User>> $property
 * @return value-of<properties-of<User>>
 */
function getUserProperty(User $user, string $property) {
    return $user->$property;
}

// 使用例
$user = new User();
$propertyValue = getUserProperty($user, 'name'); // $propertyValue は string 型
```

`properties-of` には以下のバリアントがあります：

- `public-properties-of<T>`: 公開プロパティのみを対象とします。
- `protected-properties-of<T>`: 保護されたプロパティのみを対象とします。
- `private-properties-of<T>`: プライベートプロパティのみを対象とします。

これらのバリアントを使用することで、特定のアクセス修飾子を持つプロパティのみを扱うことができます。

### クラス名マッピング型

`class-string-map` は、クラス名をキーとし、そのインスタンスを値とする配列を表します。これは、依存性注入コンテナやファクトリーパターンの実装に役立ちます。

```php
/**
 * @template T of object
 * @param class-string-map<T, T> $map
 * @param class-string<T> $className
 * @return T
 */
function getInstance(array $map, string $className) {
    return $map[$className] ?? new $className();
}

// 使用例
$container = [
    UserRepository::class => new UserRepository(),
    ProductRepository::class => new ProductRepository(),
];

$userRepo = getInstance($container, UserRepository::class);
```

### インデックスアクセス型

インデックスアクセス型（`T[K]`）は、型 `T` のインデックス `K` の要素を表します。これは、配列やオブジェクトのプロパティにアクセスする際の型を正確に表現するのに役立ちます。

```php
/**
 * @template T of array
 * @template K of key-of<T>
 * @param T $data
 * @param K $key
 * @return T[K]
 */
function getArrayValue(array $data, $key) {
    return $data[$key];
}

// 使用例
$config = ['debug' => true, 'version' => '1.0.0'];
$debugMode = getArrayValue($config, 'debug'); // $debugMode は bool 型
```

これらのユーティリティ型はpsalm固有のもので[高度な型システム](#高度な型システムと使用パターン)の一部として考えることができます。

## 関数型プログラミングの概念

PHPDocは、関数型プログラミングの影響を受けた重要な概念をサポートしています。これらの概念を使用することで、コードの予測可能性と信頼性を向上させることができます。

### 純粋関数

純粋関数は、副作用がなく、同じ入力に対して常に同じ出力を返す関数です。

```php
/**
 * @pure
 */
function add(int $a, int $b): int 
{
    return $a + $b;
}
```

関数の副作用がないこと、そして関数の結果が入力のみに依存することを明示できます。

### 不変オブジェクト

不変オブジェクトは、作成後に状態が変更されないオブジェクトです。

```php
/**
 * @immutable
 * - すべてのプロパティは実質的に`readonly`として扱われます。
 * - すべてのメソッドは暗黙的に`@psalm-mutation-free`として扱われます。
 */
class Point {
    public function __construct(
        private float $x, 
        private float $y
    ) {}

    public function withX(float $x): static 
    {
        return new self($x, $this->y);
    }

    public function withY(float $y): static
    {
        return new self($this->x, $y);
    }
}
```

#### @psalm-mutation-free

このアノテーションは、メソッドがクラスの内部状態も外部の状態も変更しないことを示します。`@immutable`クラスのメソッドは暗黙的にこの性質を持ちますが、非イミュータブルクラスの特定のメソッドに対しても使用できます。

```php
class Calculator {
    private float $lastResult = 0;

    /**
     * @psalm-mutation-free
     */
    public function add(float $a, float $b): float {
        return $a + $b;
    }

    public function addAndStore(float $a, float $b): float {
        $this->lastResult = $a + $b; // これは@psalm-mutation-freeでは許可されません
        return $this->lastResult;
    }
}
```

#### @psalm-external-mutation-free

このアノテーションは、メソッドがクラスの外部の状態を変更しないことを示します。内部状態の変更は許可されます。

```php
class Logger {
    private array $logs = [];

    /**
     * @psalm-external-mutation-free
     */
    public function log(string $message): void {
        $this->logs[] = $message; // クラス内部の状態変更は許可されます
    }

    public function writeToFile(string $filename): void {
        file_put_contents($filename, implode("\n", $this->logs)); // これは外部状態を変更するため、@psalm-external-mutation-freeでは使用できません
    }
}
```

#### 不変性アノテーションの使用ガイドライン

1. クラス全体が不変である場合は `@immutable` を使用します。
2. 特定のメソッドが状態を変更しない場合は `@psalm-mutation-free` を使用します。
3. メソッドが外部の状態は変更しないが、内部状態を変更する可能性がある場合は `@psalm-external-mutation-free` を使用します。

不変性を適切に表現することで、並行処理での安全性向上、副作用の減少、コードの理解しやすさの向上など、多くの利点を得ることができます。

### 副作用の注釈

関数が副作用を持つ場合、それを明示的に注釈することで、その関数の使用に注意を促すことができます。

```php
/**
 * @side-effect This function writes to the database
 */
function logMessage(string $message): void {
    // データベースにメッセージを書き込む処理
}
```

### 高階関数

高階関数は、関数を引数として受け取るか、関数を返す関数です。PHPDocを使用して、高階関数の型を正確に表現できます。

```php
/**
 * @param callable(int): bool $predicate
 * @param list<int>           $numbers
 * @return list<int>
 */
function filter(callable $predicate, array $numbers): array {
    return array_filter($numbers, $predicate);
}
```

高階関数は[Callable型](#callable型)と密接に関連しています。

## アサート注釈

アサート注釈は、静的解析ツールに対して特定の条件が満たされていることを伝えるために使用されます。

```php
/**
 * @psalm-assert string $value
 * @psalm-assert-if-true string $value
 * @psalm-assert-if-false null $value
 */
function isString($value): bool {
    return is_string($value);
}

/**
 * @psalm-assert !null $value
 */
function assertNotNull($value): void {
    if ($value === null) {
        throw new \InvalidArgumentException('Value must not be null');
    }
}

/**
 * @psalm-assert-if-true positive-int $number
 */
function isPositiveInteger($number): bool {
    return is_int($number) && $number > 0;
}
```

これらのアサート注釈は、以下のように使用されます：

- `@psalm-assert`: 関数が正常に終了した場合（例外をスローせずに）、アサーションが真であることを示します。
- `@psalm-assert-if-true`: 関数が `true` を返した場合、アサーションが真であることを示します。
- `@psalm-assert-if-false`: 関数が `false` を返した場合、アサーションが真であることを示します。

アサート注釈は[型の制約](#型の制約)と組み合わせて使用されることがあります。

## セキュリティ注釈

セキュリティ注釈は、コード内のセキュリティに関連する重要な部分を明示し、潜在的な脆弱性を追跡するために使用されます。主に以下の3つの注釈があります：

1. `@psalm-taint-source`: 信頼できない入力源を示します。
2. `@psalm-taint-sink`: セキュリティ上重要な操作が行われる場所を示します。
3. `@psalm-taint-escape`: データが安全にエスケープまたはサニタイズされた場所を示します。

以下は、これらの注釈の使用例です：

```php
/**
 * @psalm-taint-source input
 */
function getUserInput(): string {
    return $_GET['user_input'] ?? '';
}

/**
 * @psalm-taint-sink sql
 */
function executeQuery(string $query): void {
    // SQLクエリを実行
}

/**
 * @psalm-taint-escape sql
 */
function escapeForSql(string $input): string {
    return addslashes($input);
}

// 使用例
$userInput = getUserInput();
$safeSqlInput = escapeForSql($userInput);
executeQuery("SELECT * FROM users WHERE name = '$safeSqlInput'");
```

これらの注釈を使用することで、静的解析ツールは信頼できない入力の流れを追跡し、潜在的なセキュリティ問題（SQLインジェクションなど）を検出できます。

## 例：デザインパターンでの型の使用

型システムを活用して、一般的なデザインパターンをより型安全に実装できます。

#### ビルダーパターン

```php
/**
 * @template T
 */
interface BuilderInterface {
    /**
     * @return T
     */
    public function build();
}

/**
 * @template T
 * @template-implements BuilderInterface<T>
 */
abstract class AbstractBuilder implements BuilderInterface {
    /** @var array<string, mixed> */
    protected $data = [];

    /** @param mixed $value */
    public function set(string $name, $value): static {
        $this->data[$name] = $value;
        return $this;
    }
}

/**
 * @extends AbstractBuilder<User>
 */
class UserBuilder extends AbstractBuilder {
    public function build(): User {
        return new User($this->data);
    }
}

// 使用例
$user = (new UserBuilder())
    ->set('name', 'John Doe')
    ->set('email', 'john@example.com')
    ->build();
```

#### リポジトリパターン

```php
/**
 * @template T
 */
interface RepositoryInterface {
    /**
     * @param int $id
     * @return T|null
     */
    public function find(int $id);

    /**
     * @param T $entity
     */
    public function save($entity): void;
}

/**
 * @implements RepositoryInterface<User>
 */
class UserRepository implements RepositoryInterface {
    public function find(int $id): ?User {
        // データベースからユーザーを取得するロジック
    }

    public function save(User $user): void {
        // ユーザーをデータベースに保存するロジック
    }
}
```
## まとめ

PHPDocの型システムを深く理解して適切に使用することで、コードの自己文書化、静的解析による早期のバグ検出、IDEによる強力なコード補完と支援、コードの意図と構造の明確化、セキュリティリスクの軽減などの利点が得られ、より堅牢で保守性の高いPHPコードを書くことができます。以下は利用可能な型を網羅した例です。

```php
<?php

namespace App\Comprehensive\Types;

/**
 * アトミック型、スカラー型、ユニオン型、交差型、ジェネリック型を網羅するクラス
 * 
 * @psalm-type UserId = int
 * @psalm-type HtmlContent = string
 * @psalm-type PositiveFloat = float&positive
 * @psalm-type Numeric = int|float
 * @psalm-type QueryResult = array<string, mixed>
 */
class TypeExamples {
    /**
     * @param UserId|non-empty-string $id
     * @return HtmlContent
     */
    public function getUserContent(int|string $id): string {
        return "<p>User ID: {$id}</p>";
    }

    /**
     * @param PositiveFloat $amount
     * @return bool
     */
    public function processPositiveAmount(float $amount): bool {
        return $amount > 0;
    }
}

/**
 * イミュータブルクラス、関数型プログラミング、純粋関数の例
 * 
 * @immutable
 */
class ImmutableUser {
    /** @var non-empty-string */
    private string $name;

    /** @var positive-int */
    private int $age;

    /**
     * @param non-empty-string $name
     * @param positive-int $age
     */
    public function __construct(string $name, int $age) {
        $this->name = $name;
        $this->age = $age;
    }

    /**
     * @psalm-pure
     * @return ImmutableUser
     */
    public function withAdditionalYears(int $additionalYears): self {
        return new self($this->name, $this->age + $additionalYears);
    }
}

/**
 * テンプレート型、ジェネリック型、条件付き型、共変性と反変性の例
 * 
 * @template T
 * @template-covariant U
 */
class StorageContainer {
    /** @var array<T, U> */
    private array $items = [];

    /**
     * @param T $key
     * @param U $value
     */
    public function add(mixed $key, mixed $value): void {
        $this->items[$key] = $value;
    }

    /**
     * @param T $key
     * @return U|null
     */
    public function get(mixed $key): mixed {
        return $this->items[$key] ?? null;
    }
    
    /**
     * @template V
     * @param T $key
     * @return (T is string ? string : U|null)
     */
    public function get(mixed $key): mixed {
        return is_string($key) ? "default_string_value" : ($this->items[$key] ?? null);
    }
}

/**
 * 型の制約、ユーティリティ型、関数型プログラミング、アサート注釈の例
 * 
 * @template T of array-key
 */
class UtilityExamples {
    /**
     * @template T of array-key
     * @psalm-param array<T, mixed> $array
     * @psalm-return list<T>
     * @psalm-assert array<string, mixed> $array
     */
    public function getKeys(array $array): array {
        return array_keys($array);
    }

    /**
     * @template T of object
     * @psalm-param class-string-map<T, array-key> $classes
     * @psalm-return list<T>
     */
    public function mapClasses(array $classes): array {
        return array_map(fn(string $className): object => new $className(), array_keys($classes));
    }
}

/**
 * 高階関数、型エイリアス、インデックスアクセス型の例
 * 
 * @template T
 * @psalm-type Predicate = callable(T): bool
 */
class FunctionalExamples {
    /**
     * @param list<T> $items
     * @param Predicate<T> $predicate
     * @return list<T>
     */
    public function filter(array $items, callable $predicate): array {
        return array_filter($items, $predicate);
    }

    /**
     * @param array<string, T> $map
     * @param key-of $map $key
     * @return T|null
     */
    public function getValue(array $map, string $key): mixed {
        return $map[$key] ?? null;
    }
}

/**
 * セキュリティ注釈、型制約、インデックスアクセス型、プロパティ取得型、キー取得型、値取得型の例
 * 
 * @template T
 */
class SecureAccess {
    /**
     * @psalm-type UserProfile = array{
     *   id: int,
     *   name: non-empty-string,
     *   email: non-empty-string,
     *   roles: list<non-empty-string>
     * }
     * @psalm-param UserProfile $profile
     * @psalm-param key-of<UserProfile> $property
     * @return value-of<UserProfile>
     * @psalm-taint-escape system
     */
    public function getUserProperty(array $profile, string $property): mixed {
        return $profile[$property];
    }
}

/**
 * 非常に複雑な構造の型やセキュリティ・注釈、純粋関数の実装例
 * 
 * @template T of object
 * @template-covariant U of array-key
 * @psalm-type ErrorResponse = array{error: non-empty-string, code: positive-int}
 */
class ComplexExample {
    /** @var array<U, T> */
    private array $registry = [];

    /**
     * @param U $key
     * @param T $value
     */
    public function register(mixed $key, object $value): void {
        $this->registry[$key] = $value;
    }

    /**
     * @param U $key
     * @return T|null
     * @psalm-pure
     * @psalm-assert-if-true ErrorResponse $this->registry[$key]
     */
    public function getRegistered(mixed $key): ?object {
        return $this->registry[$key] ?? null;
    }
}

<?php

namespace App\Additional\Types;

/**
 * テンプレート型の制約とcontravariantの例
 * 
 * @template-contravariant T of \Throwable
 */
interface ErrorHandlerInterface {
    /**
     * @param T $error
     * @return void
     */
    public function handle(\Throwable $error): void;
}

/**
 * より具体的な型への実装例
 * 
 * @implements ErrorHandlerInterface<\RuntimeException>
 */
class RuntimeErrorHandler implements ErrorHandlerInterface {
    public function handle(\Throwable $error): void {
        // RuntimeExceptionの処理
    }
}

/**
 * 複雑な型の組み合わせと条件分岐の例
 * 
 * @psalm-type JsonPrimitive = string|int|float|bool|null
 * @psalm-type JsonArray = array<array-key, JsonValue>
 * @psalm-type JsonObject = array<string, JsonValue>
 * @psalm-type JsonValue = JsonPrimitive|JsonArray|JsonObject
 */
class JsonProcessor {
    /**
     * @param JsonValue $value
     * @return (JsonValue is JsonObject ? array<string, mixed> : (JsonValue is JsonArray ? list<mixed> : scalar|null))
     */
    public function process(mixed $value): mixed {
        if (is_array($value)) {
            return array_keys($value) === range(0, count($value) - 1) 
                ? array_values($value)
                : $value;
        }
        return $value;
    }
}

/**
 * より高度なタプル型とレコード型の例
 */
class AdvancedTypes {
    /**
     * @return array{0: int, 1: string, 2: bool}
     */
    public function getTuple(): array {
        return [42, "hello", true];
    }

    /**
     * @param array{id: int, name: string, meta: array{created: string, modified?: string}} $record
     * @return void
     */
    public function processRecord(array $record): void {
        // レコード型の処理
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<string, mixed> $properties
     * @return T
     */
    public function createInstance(string $className, array $properties): object {
        $instance = new $className();
        foreach ($properties as $key => $value) {
            $instance->$key = $value;
        }
        return $instance;
    }
}

/**
 * カスタム型ガードとアサーションの例
 */
class TypeGuards {
    /**
     * @psalm-assert-if-true non-empty-string $value
     */
    public function isNonEmptyString(mixed $value): bool {
        return is_string($value) && $value !== '';
    }

    /**
     * @template T of object
     * @param mixed $value
     * @param class-string<T> $className
     * @psalm-assert-if-true T $value
     */
    public function isInstanceOf(mixed $value, string $className): bool {
        return $value instanceof $className;
    }
}

/**
 * PHPUnit用のテスト関連の型アノテーションの例
 */
class TestTypes {
    /**
     * @param class-string<\Exception> $expectedClass
     * @param callable(): mixed $callback
     */
    public function expectException(string $expectedClass, callable $callback): void {
        try {
            $callback();
            $this->fail('Exception was not thrown');
        } catch (\Exception $e) {
            $this->assertInstanceOf($expectedClass, $e);
        }
    }

    /**
     * @template T
     * @param T $expected
     * @param T $actual
     * @param non-empty-string $message
     */
    public function assertEquals(mixed $expected, mixed $actual, string $message = ''): void {
        // 型安全な比較ロジック
    }
}

/**
 * コレクション型とイテレータの高度な例
 * 
 * @template-covariant TKey of array-key
 * @template-covariant TValue
 * @template-implements \IteratorAggregate<TKey, TValue>
 */
class TypedCollection implements \IteratorAggregate {
    /** @var array<TKey, TValue> */
    private array $items = [];

    /**
     * @return \Traversable<TKey, TValue>
     */
    public function getIterator(): \Traversable {
        yield from $this->items;
    }

    /**
     * @param TValue $item
     * @return void
     */
    public function add(mixed $item): void {
        $this->items[] = $item;
    }

    /**
     * @template TCallback
     * @param callable(TValue): TCallback $callback
     * @return TypedCollection<TKey, TCallback>
     */
    public function map(callable $callback): self {
        $result = new self();
        foreach ($this->items as $key => $value) {
            $result->items[$key] = $callback($value);
        }
        return $result;
    }
}

/**
 * 条件付きメソッドの例
 */
interface ConditionalInterface {
    /**
     * @template T
     * @param T $value
     * @return (T is numeric ? float : string)
     */
    public function process(mixed $value): mixed;
}

```

## リファレンス

PHPDoc型を最大限に活用するためには、PsalmやPHPStanといった静的解析ツールが必要です。詳細については、以下のリソースを参照してください：

- [Psalm - Typing in Psalm](https://koriym.github.io/psalm-ja/annotating_code/typing_in_psalm.html)
  - [Templating](https://koriym.github.io/psalm-ja/annotating_code/templated_annotations.html)
  - [Assertions](https://koriym.github.io/psalm-ja/annotating_code/adding_assertions.html)
  - [Security Analysis](https://koriym.github.io/psalm-ja/security_analysis.html)
- [PHPStan - PHPDoc Types](https://phpstan.org/writing-php-code/phpdoc-types.html)


# PHPDoc ユーティリティ型

ユーティリティ型は、既存の型を操作したり、動的に新しい型を生成するために使用される型です。これらの型を使用することで、より柔軟で表現力豊かな型定義が可能になります。

## 目次

1. [key-of<T>](#key-oft)
2. [value-of<T>](#value-oft)
3. [properties-of<T>](#properties-oft)
4. [class-string-map<T of Foo, T>](#class-string-mapt-of-foo-t)
5. [T[K]](#tk)
6. [Type aliases](#type-aliases)
7. [Variable templates](#variable-templates)

## key-of<T>

`key-of<T>` は、型 `T` のすべての可能なキーの型を表します。

```php
/**
 * @template T of array
 * @param T $data
 * @param key-of<T> $key
 * @return mixed
 */
function getValueByKey(array $data, $key) {
    return $data[$key];
}

// 使用例
$userData = ['id' => 1, 'name' => 'John'];
$name = getValueByKey($userData, 'name'); // OK
$age = getValueByKey($userData, 'age'); // Psalmは警告を出します
```

## value-of<T>

`value-of<T>` は、型 `T` のすべての可能な値の型を表します。

```php
/**
 * @template T of array
 * @param T $data
 * @return value-of<T>
 */
function getRandomValue(array $data) {
    return $data[array_rand($data)];
}

// 使用例
$numbers = [1, 2, 3, 4, 5];
$randomNumber = getRandomValue($numbers); // int型
```

## properties-of<T>

`properties-of<T>` は、型 `T` のすべてのプロパティの型を表します。

```php
class User {
    public int $id;
    public string $name;
    public ?string $email;
}

/**
 * @param User $user
 * @param key-of<properties-of<User>> $property
 * @return value-of<properties-of<User>>
 */
function getUserProperty(User $user, string $property) {
    return $user->$property;
}

// 使用例
$user = new User();
$name = getUserProperty($user, 'name'); // string型
$id = getUserProperty($user, 'id'); // int型
$unknown = getUserProperty($user, 'unknown'); // Psalmは警告を出します
```

## class-string-map<T of Foo, T>

`class-string-map` は、クラス名をキーとし、そのインスタンスを値とする配列を表します。

```php
interface Repository {}
class UserRepository implements Repository {}
class ProductRepository implements Repository {}

/**
 * @template T of Repository
 * @param class-string-map<T, T> $repositories
 * @param class-string<T> $className
 * @return T
 */
function getRepository(array $repositories, string $className): Repository {
    return $repositories[$className];
}

// 使用例
$repositories = [
    UserRepository::class => new UserRepository(),
    ProductRepository::class => new ProductRepository(),
];

$userRepo = getRepository($repositories, UserRepository::class);
```

## T[K]

`T[K]` は、型 `T` のインデックス `K` の要素を表します。

```php
/**
 * @template T of array
 * @template K of array-key
 * @param T $data
 * @param K $key
 * @return T[K]
 */
function getArrayElement(array $data, $key) {
    return $data[$key];
}

// 使用例
$config = ['debug' => true, 'version' => '1.0.0'];
$debugMode = getArrayElement($config, 'debug'); // bool型
```



# テスト

適切なテストは、ソフトウェアを継続性のある、より良いものにします。全ての依存がインジェクトされ、横断的関心事がAOPで提供されるBEAR.Sundayのクリーンなアプリケーションはテストフレンドリーです。

## テスト実行

composerコマンドが用意されています。

```
composer test     // phpunitテスト
composer tests    // test + sa + cs
composer coverage // テストカバレッジ
composer pcov     // テストカバレッジ (pcov)
composer sa       // 静的解析
composer cs       // コーディングスタンダード検査
composer cs-fix   // コーディングスタンダード修復
```

## リソーステストケース作成

**全てがリソース**のBEAR.Sundayではリソース操作がテストの基本です。`Injector::getInstance`でリソースクライアントを取得してリソースの入出力テストを行います。

```php
<?php
use BEAR\Resource\ResourceInterface;

class TodoTest extends TestCase
{
    private ResourceInterface $resource;
    
    protected function setUp(): void
    {
        $injector = Injector::getInstance('test-html-app');
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }
    
    public function testOnPost(): void
    {
        $page = $this->resource->post('page://self/todo', ['title' => 'test']);
        $this->assertSame(StatusCode::CREATED, $page->code);
    }
}
```

## テストダブル

[テストダブル](https://ja.wikipedia.org/wiki/%E3%83%86%E3%82%B9%E3%83%88%E3%83%80%E3%83%96%E3%83%AB) (Test Double) とは、ソフトウェアテストでテスト対象が依存しているコンポーネントを置き換える代用品のことです。テストダブルには以下のパターンがあります。

* スタブ (テスト対象に「間接的な入力」を提供)
* モック (テスト対象からの「間接的な出力」をテストダブルの内部で検証)
* スパイ (テスト対象からの「間接的な出力」を記録)
* フェイク (実際のオブジェクトに近い働きのより単純な実装)
* ダミー (テスト対象の生成に必要だが呼び出しが行われない)

テスト対象のシステム([SUT](https://ja.wikipedia.org/wiki/%E3%83%86%E3%82%B9%E3%83%88%E5%AF%BE%E8%B1%A1%E3%82%B7%E3%82%B9%E3%83%86%E3%83%A0))がテストダブルの出力を使用するのがスタブです。例えばいつも`true`を返すようなメソッドを持つテストダブルはスタブです。

モックはSUTからテストダブルへの間接的出力の検証をテストコードではなく、テストダブル内部で行います。スパイはモックと同じようにSUTの間接的出力の検証を行うためのものですが、その検証をテストコードで行うためにテストコードから読み取り可能な記録が行われます。

### テストダブルの束縛

テスト用に束縛を変更する方法は2つあります。コンテキストモジュールで全テストの束縛を横断的に変更する方法と、1テストの中だけで一時的に特定目的だけで束縛を変える方法です。

#### コンテキストモジュール

`TestModule`を作成してbootstrapで`test`コンテキストを利用可能にします。

```php
class TestModule extends AbstractModule
{
    public function configure(): void
    {
        $this->bind(DateTimeInterface::class)->toInstance(new DateTimeImmutable('1970-01-01 00:00:00'));
        $this->bind(Auth::class)->to(FakeAuth::class);
    }
}
```

テスト用束縛が上書きされたインジェクター：

```php
$injector = Injector::getInstance('test-hal-app', $module);
```

## 一時的束縛変更

1つのテストのための一時的な束縛の変更は`Injector::getOverrideInstance`で上書きする束縛を指定します。

### スタブ、フェイク

```php
public function testBindStub(): void
{
    $module = new class extends AbstractModule {
        protected function configure(): void
        {
            $this->bind(FooInterface::class)->to(FakeFoo::class);
        }
    };
    $injector = Injector::getOverrideInstance('hal-app', $module);
}
```

### モック

アサーションをテストダブル内部で実行します。

```php
public function testBindMock(): void
{
    $mock = $this->createMock(FooInterface::class);
    // update()が一度だけコールされ、その際のパラメータは文字列'something'となることを期待
    $mock->expects($this->once())
         ->method('update')
         ->with($this->equalTo('something'));
         
    $module = new class($mock) extends AbstractModule {
        public function __construct(
            private FooInterface $foo
        ){}
        
        protected function configure(): void
        {
            $this->bind(FooInterface::class)->toInstance($this->foo);
        }
    };
    $injector = Injector::getOverrideInstance('hal-app', $module);
}
```

### スパイ

スパイ対象のインターフェイスまたはクラス名を指定して`SpyModule`をインストールします。[^spy-module]
スパイ対象が含まれるSUTを動作させた後に、スパイログで呼び出し回数や呼び出しの値を検証します。

[^spy-module]: SpyModuleの利用には[ray/test-double](https://github.com/ray-di/Ray.TestDouble)のインストールが必要です。

```php
public function testBindSpy(): void
{
    $module = new class extends AbstractModule {
        protected function configure(): void
        {
            $this->install(new SpyModule([FooInterface::class]));
        }
    };
    $injector = Injector::getOverrideInstance('hal-app', $module);
    $resource = $injector->getInstance(ResourceInterface::class);
    
    // 直接、間接に関わらずFooInterfaceオブジェクトのSpyログが記録されます
    $resource->get('/');
    
    // Spyログの取り出し
    $spyLog = $injector->getInstance(\Ray\TestDouble\LoggerInterface::class);
    // @var array<int, Log> $addLog
    $addLog = $spyLog->getLogs(FooInterface::class, 'add');
    
    $this->assertSame(1, count($addLog), 'Should have received once');
    // SUTからの引数の検証
    $this->assertSame([1, 2], $addLog[0]->arguments);
    $this->assertSame(1, $addLog[0]->namedArguments['a']);
}
```

### ダミー

インターフェイスにNullオブジェクトを束縛するには[Null束縛](https://ray-di.github.io/manuals/1.0/ja/null_object_binding.html)を使います。

## ハイパーメディアテスト

リソーステストは各エンドポイントの入出力テストです。対してハイパーメディアテストはそのエンドポイントをどう繋ぐかというワークフローの振る舞いをテストします。

Workflowテストは HTTPテストに継承され、1つのコードでPHPとHTTP双方のレベルでテストされます。その際HTTPのテストは`curl`で行われ、そのリクエスト・レスポンスはログファイルに記録されます。

## 良いテストのために

* 実装ではなく、インターフェイスをテストします。
* モックライブラリを利用するよりフェイククラスを作成しましょう。
* テストは仕様です。書きやすさよりも読みやすさを重視しましょう。

参考URL:
* [Stop mocking, start testing](https://nedbatchelder.com/blog/201206/tldw_stop_mocking_start_testing.html)
* [Mockists Are Dead](https://www.thoughtworks.com/insights/blog/mockists-are-dead-long-live-classicists)
* 



# Examples

[Coding Guide](http://bearsunday.github.io/manuals/1.0/en/coding-guide.html)に従って作られたアプリケーションの例です。

## Polidog.Todo

[https://github.com/koriym/Polidog.Todo](https://github.com/koriym/Polidog.Todo)

基本的なCRUDのアプリケーションです。`var/sql`ディレクトリのSQLファイルでDBアクセスをしています。
ハイパーリンクを使ったREST APIとテスト、それにフォームのバリデーションテストも含まれます。

* [ray/aura-sql-module](https://github.com/ray-di/Ray.AuraSqlModule) - Extended PDO ([Aura.Sql](https://github.com/auraphp/Aura.Sql))
* [ray/web-form-module](https://github.com/ray-di/Ray.WebFormModule) - Web form ([Aura.Input](https://github.com/auraphp/Aura.Input))
* [madapaja/twig-module](https://github.com/madapaja/Madapaja.TwigModule) - Twig template engine
* [koriym/now](https://github.com/koriym/Koriym.Now) - Current datetime
* [koriym/query-locator](https://github.com/koriym/Koriym.QueryLocator) - SQL locator
* [koriym/http-constants](https://github.com/koriym/Koriym.HttpConstants) - Contains the values HTTP

## MyVendor.ContactForm

[https://github.com/bearsunday/MyVendor.ContactForm](https://github.com/bearsunday/MyVendor.ContactForm)

各種のフォームページのサンプルです。

* 最小限のフォーム
* 複数のフォーム
* INPUTエレメントをループで生成したフォーム
* チェックボックス、ラジオボタンを含んだプレビュー付きのフォーム



# アトリビュート

BEAR.SundayはBEAR.Package `^1.10.3`から従来のアノテーションに加えて、PHP8の[アトリビュート](https://www.php.net/manual/ja/language.attributes.overview.php)をサポートします。

**アノテーション**
```php
/**
 * @Inject
 * @Named('admin')
 */
public function setLogger(LoggerInterface $logger)
```

**アトリビュート**
```php
#[Inject, Named('admin')]
public function setLogger(LoggerInterface $logger)
```

```php
#[Embed(rel: 'weather', src: 'app://self/weather{?date}')]
#[Link(rel: 'event', href: 'app://self/event{?news_date}')]
public function onGet(string $date): self
```

## 引数に適用

アノテーションはメソッドにしか適用できず引数名を名前で指定する必要があるものがありましたが、PHP8では直接、引数のアトリビュートで指定することができます。

```php
public __construct(
    #[Named('payment')] LoggerInterface $paymentLogger,
    #[Named('debug')] LoggerInterface $debugLogger
)
```

```php
public function onGet($id, #[Assisted] DbInterface $db = null)
```

```php
public function onGet(#[CookieParam('id')] string $tokenId): void
```

```php
public function onGet(#[ResourceParam(uri: 'app://self/login#nickname')] string $nickname = null): static
```

## 互換性

アトリビュートとアノテーションは1つのプロジェクトに混在することもできます。[^1]
このマニュアルに表記されている全てのアノテーションはアトリビュートに変更しても動作します。

## パフォーマンス

最適化されるため、プロダクション用にアノテーション/アトリビュート読み込みコストがかかることはほとんどありませんが、
以下のようにアトリビュートリーダーしか使用しないと宣言すると開発時の速度が向上します。

```php
// tests/bootstap.php
use Ray\ServiceLocator\ServiceLocator;
ServiceLocator::setReader(new AttributeReader());
```

```php
// DevModule
$this->install(new AttributeModule());
```

---
[^1]: 1つのメソッドで混在するときはアトリビュートが優先されます。



# API Doc

BEAR.ApiDocは、アプリケーションからAPIドキュメントを生成します。コードとJSONスキーマから自動生成されるドキュメントは、手間を減らし正確なAPIドキュメントを維持し続けることができます。

## 利用方法

BEAR.ApiDocをインストールします。

```
composer require bear/api-doc --dev
```

設定ファイルをコピーします。

```
cp ./vendor/bear/api-doc/apidoc.xml.dist ./apidoc.xml
```

## ソース

BEAR.ApiDocはphpdoc、メソッドシグネチャ、JSONスキーマから情報を取得してドキュメントを生成します。

#### PHPDOC

phpdocでは以下の部分が取得されます。認証などリソースに横断的に適用される情報は別のドキュメントページを用意して`@link`でリンクします。

```php
/**
 * {title}
 *
 * {description}
 *
 * {@link htttp;//example.com/docs/auth 認証}
 */
class Foo extends ResourceObject { }
```

```php
/**
 * {title}
 *
 * {description}
 *
 * @param string $id ユーザーID
 */
public function onGet(string $id = 'kuma'): static { }
```

* メソッドのphpdocに`@param`記述が無い場合、メソッドシグネチャーから引数の情報を取得します。
* 情報取得の優先順はphpdoc、JSONスキーマ、プロファイルの順です。

## 設定ファイル

設定はXMLで記述されます。最低限の指定は以下の通りです。

```xml
<?xml version="1.0" encoding="UTF-8"?>
<apidoc
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="https://bearsunday.github.io/BEAR.ApiDoc/apidoc.xsd">
    <appName>MyVendor\MyProject</appName>
    <scheme>app</scheme>
    <docDir>docs</docDir>
    <format>html</format>
</apidoc>
```

### 必須属性

#### appName
アプリケーションの名前空間

#### scheme
APIドキュメントにするスキーマ名。`page`または`app`

#### docDir
出力ディレクトリ名

#### format
出力フォーマット。HTMLまたはMD (Markdown)

### オプション属性

#### title
APIタイトル
```xml
<title>MyBlog API</title>
```

#### description
APIディスクリプション
```xml
<description>MyBlog API description</description>
```

#### links
リンク。`href`でリンク先URL、`rel`でその内容を表します。
```xml
<links>
    <link href="https://www.example.com/issue" rel="issue" />
    <link href="https://www.example.com/help" rel="help" />
</links>
```

#### alps
APIで使われる語句を定義する"ALPSプロファイル"を指定します。
```xml
<alps>alps/profile.json</alps>
```

## プロファイル

BEAR.ApiDocはアプリケーションに追加情報を与える[RFC 6906 プロファイル](https://tools.ietf.org/html/rfc6906)の[ALPS](http://alps.io/)フォーマットをサポートします。

APIのリクエストやレスポンスのキーで使う語句をセマンティックディスクリプタ（意味的記述子）と呼びますが、プロファイルでその辞書を作っておけばリクエスト毎に語句を説明する必要がなくなります。語句の定義が集中することで表記揺れを防ぎ、理解共有を助けます。

以下は`firstName`,`familyName`というディスクリプタをそれぞれ`title`、`def`で定義した例です。`title`は言葉を記述して意味を明らかにしますが、`def`は[Schema.org](https://schema.org/)などのボキャブラリサイトで定義されたスタンダードな語句をリンクします。

ALPSプロファイルはXMLまたはJSONで記述します。

profile.xml
```xml
<?xml version="1.0" encoding="UTF-8"?>
<alps
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="https://alps-io.github.io/schemas/alps.xsd">
    <!-- Ontology -->
    <descriptor id="firstName" title="The person's first name."/>
    <descriptor id="familyName" def="https://schema.org/familyName"/>
</alps>
```

profile.json
```json
{
    "$schema": "https://alps-io.github.io/schemas/alps.json",
    "alps": {
        "descriptor": [
            {"id": "firstName", "title": "The person's first name."},
            {"id": "familyName", "def": "https://schema.org/familyName"}
        ]
    }
}
```

ApiDocに登場する語句の説明はphpdoc > JsonSchema > ALPSの順で優先します。

## リンク

* [Demo](https://bearsunday.github.io/BEAR.ApiDoc/)
* [ALPS](http://alps.io/)
* [ALPS-ASD](https://github.com/koriym/app-state-diagram)
* [メディアタイプとALPSプロファイル](https://qiita.com/koriym/items/2e928efb2167d559052e)



# リファレンス

## アトリビュート

| アトリビュート | 説明 |
|--||
| `#[CacheableResponse]` | キャッシュ可能なレスポンスを指定するアトリビュート。 |
| `#[Cacheable(int $expirySecond = 0)]` | リソースのキャッシュ可能性を指定するアトリビュート。`$expirySecond`はキャッシュの有効期間（秒）。 |
| `#[CookieParam(string $name)]` | クッキーからパラメータを受け取るためのアトリビュート。`$name`はクッキーの名前。 |
| `#[DonutCache]` | ドーナツキャッシュを指定するアトリビュート。 |
| `#[Embed(src: string $src, rel: string $rel)]` | 他のリソースを埋め込むことを指定するアトリビュート。`$src`は埋め込むリソースのURI、`$rel`はリレーション名。 |
| `#[EnvParam(string $name)]` | 環境変数からパラメータを受け取るためのアトリビュート。`$name`は環境変数の名前。 |
| `#[FormParam(string $name)]` | フォームデータからパラメータを受け取るためのアトリビュート。`$name`はフォームフィールドの名前。 |
| `#[Inject]` | セッターインジェクションを指定するアトリビュート。 |
| `#[InputValidation]` | 入力バリデーションを行うことを指定するアトリビュート。 |
| `#[JsonSchema(key: string $key = null, schema: string $schema = null, params: string $params = null)]` | リソースの入力/出力のJSONスキーマを指定するアトリビュート。`$key`はスキーマのキー、`$schema`はスキーマファイル名、`$params`はパラメータのスキーマファイル名。 |
| `#[Link(rel: string $rel, href: string $href, method: string $method = null)]` | リソース間のリンクを指定するアトリビュート。`$rel`はリレーション名、`$href`はリンク先のURI、`$method`はHTTPメソッド。 |
| `#[Named(string $name)]` | 名前付きバインディングを指定するアトリビュート。`$name`はバインディングの名前。 |
| `#[OnFailure(string $name = null)]` | バリデーション失敗時のメソッドを指定するアトリビュート。`$name`はバリデーションの名前。 |
| `#[OnValidate(string $name = null)]` | バリデーションメソッドを指定するアトリビュート。`$name`はバリデーションの名前。 |
| `#[Produces(array $mediaTypes)]` | リソースの出力メディアタイプを指定するアトリビュート。`$mediaTypes`は出力可能なメディアタイプの配列。 |
| `#[QueryParam(string $name)]` | クエリパラメータを受け取るためのアトリビュート。`$name`はクエリパラメータの名前。 |
| `#[RefreshCache]` | キャッシュのリフレッシュを指定するアトリビュート。 |
| `#[ResourceParam(uri: string $uri, param: string $param)]` | 他のリソースの結果をパラメータとして受け取るためのアトリビュート。`$uri`はリソースのURI、`$param`はパラメータ名。 |
| `#[ReturnCreatedResource]` | 作成されたリソースを返すことを指定するアトリビュート。 |
| `#[ServerParam(string $name)]` | サーバー変数からパラメータを受け取るためのアトリビュート。`$name`はサーバー変数の名前。 |
| `#[Ssr(app: string $appName, state: array $state = [], metas: array $metas = [])]` | サーバーサイドレンダリングを指定するアトリビュート。`$appName`はJSアプリケーション名、`$state`はアプリケーションの状態、`$metas`はメタ情報の配列。 |
| `#[Transactional(array $props = ['pdo'])]` | メソッドをトランザクション内で実行することを指定するアトリビュート。`$props`はトランザクションを適用するプロパティの配列。 |
| `#[UploadFiles]` | アップロードされたファイルを受け取るためのアトリビュート。 |
| `#[Valid(form: string $form = null, onFailure: string $onFailure = null)]` | リクエストの検証を行うことを指定するアトリビュート。`$form`はフォームクラス名、`$onFailure`は検証失敗時のメソッド名。 |

## モジュール

| モジュール名 | 説明 |
|||
| `ApcSsrModule` | APCuを使用したサーバーサイドレンダリング用のモジュール。 |
| `ApiDoc` | APIドキュメントを生成するためのモジュール。 |
| `AppModule` | アプリケーションのメインモジュール。他のモジュールのインストールや設定を行う。 |
| `AuraSqlModule` | Aura.Sqlを使用したデータベース接続用のモジュール。 |
| `AuraSqlQueryModule` | Aura.SqlQueryを使用したクエリビルダー用のモジュール。 |
| `CacheVersionModule` | キャッシュのバージョン管理を行うモジュール。 |
| `CliModule` | コマンドラインインターフェース用のモジュール。 |
| `DoctrineOrmModule` | Doctrine ORMを使用したデータベース接続用のモジュール。 |
| `FakeModule` | テスト用のフェイクモジュール。 |
| `HalModule` | HAL (Hypertext Application Language) 用のモジュール。 |
| `HtmlModule` | HTMLレンダリング用のモジュール。 |
| `ImportAppModule` | 他のアプリケーションを読み込むためのモジュール。 |
| `JsonSchemaModule` | JSONスキーマを使用したリソースの入力/出力バリデーション用のモジュール。 |
| `JwtAuthModule` | JSON Web Token (JWT) を使用した認証用のモジュール。 |
| `NamedPdoModule` | 名前付きのPDOインスタンスを提供するモジュール。 |
| `PackageModule` | BEAR.Packageが提供する基本的なモジュールをまとめてインストールするためのモジュール。 |
| `ProdModule` | 本番環境用の設定を行うモジュール。 |
| `QiqModule` | Qiqテンプレートエンジン用のモジュール。 |
| `ResourceModule` | リソースクラスに関する設定を行うモジュール。 |
| `AuraRouterModule` | Aura.Routerのルーティング用のモジュール。 |
| `SirenModule` | Siren (Hypermedia Specification) 用のモジュール。 |
| `SpyModule` | メソッドの呼び出しを記録するためのモジュール。 |
| `SsrModule` | サーバーサイドレンダリング用のモジュール。 |
| `TwigModule` | Twigテンプレートエンジン用のモジュール。 |
| `ValidationModule` | バリデーション用のモジュール。 |


# チュートリアル

このチュートリアルでは、BEAR.Sundayの基本機能である**依存性注入（DI）**、**アスペクト指向プログラミング（AOP）**、**REST API**を紹介します。
[tutorial1](https://github.com/bearsunday/tutorial1/commits/v3)のコミットを参考にして進めましょう。

## プロジェクト作成

年月日を入力すると対応する曜日を返すWebサービスを作成します。
まずプロジェクトを作成しましょう。

```bash
composer create-project bear/skeleton MyVendor.Weekday
```

**vendor**名を`MyVendor`に、**project**名を`Weekday`として入力します[^2]。

## リソース

最初にアプリケーションリソースファイルを`src/Resource/App/Weekday.php`に作成します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use DateTimeImmutable;

class Weekday extends ResourceObject
{
    public function onGet(int $year, int $month, int $day): static
    {
        $dateTime = (new DateTimeImmutable)->createFromFormat('Y-m-d', "$year-$month-$day");
        $weekday = $dateTime->format('D');
        $this->body = ['weekday' => $weekday];

        return $this;
    }
}
```

`MyVendor\Weekday\Resource\App\Weekday`クラスは`/weekday`というパスでアクセス可能なリソースです。
`GET`メソッドのクエリーパラメータが`onGet`メソッドの引数として渡されます。

コンソールでアクセスしてみましょう。まずはエラーケースを試します。

```bash
php bin/app.php get /weekday
```

```
400 Bad Request
content-type: application/vnd.error+json

{
    "message": "Bad Request",
    "logref": "e29567cd",
```

エラーは[application/vnd.error+json](https://github.com/blongden/vnd.error)メディアタイプで標準化された形式で返されます。
400はリクエストに問題があることを示すエラーコードです。エラーには`logref`IDが付与され、`var/log/`でエラーの詳細を参照できます。

次は引数を指定して正しいリクエストを試します。

```bash
php bin/app.php get '/weekday?year=2001&month=1&day=1'
```

```bash
200 OK
Content-Type: application/hal+json

{
    "weekday": "Mon",
    "_links": {
        "self": {
            "href": "/weekday?year=2001&month=1&day=1"
        }
    }
}
```

[application/hal+json](https://tools.ietf.org/html/draft-kelly-json-hal-06)メディアタイプで結果が返ってきました。

これをWeb APIサービスとして公開してみましょう。
まずBuilt-inサーバーを立ち上げます。

```bash
php -S 127.0.0.1:8080 bin/app.php
```

`curl`コマンドでHTTPの`GET`リクエストを実行する前に、`public/index.php`を以下のように書き換えます。

```diff
<?php

declare(strict_types=1);

use MyVendor\Weekday\Bootstrap;

require dirname(__DIR__) . '/autoload.php';
- exit((new Bootstrap())(PHP_SAPI === 'cli-server' ? 'hal-app' : 'prod-hal-app', $GLOBALS, $_SERVER));
+ exit((new Bootstrap())(PHP_SAPI === 'cli-server' ? 'hal-api-app' : 'prod-hal-api-app', $GLOBALS, $_SERVER));
```

```bash
curl -i 'http://127.0.0.1:8080/weekday?year=2001&month=1&day=1'
```

```
HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Date: Tue, 04 May 2021 01:55:59 GMT
Connection: close
X-Powered-By: PHP/8.0.3
Content-Type: application/hal+json

{
    "weekday": "Mon",
    "_links": {
        "self": {
            "href": "/weekday/2001/1/1"
        }
    }
}
```

このリソースクラスはGETメソッドのみ実装しているため、他のメソッドでアクセスすると`405 Method Not Allowed`が返されます。試してみましょう。

```bash
curl -i -X POST 'http://127.0.0.1:8080/weekday?year=2001&month=1&day=1'
```

```
HTTP/1.1 405 Method Not Allowed
...
```

また、HTTP `OPTIONS`メソッドを使用すると、利用可能なHTTPメソッドと必要なパラメーターを確認できます（[RFC7231](https://tools.ietf.org/html/rfc7231#section-4.3.7)）。

```bash
curl -i -X OPTIONS http://127.0.0.1:8080/weekday
```

```
HTTP/1.1 200 OK
...
Content-Type: application/json
Allow: GET

{
    "GET": {
        "parameters": {
            "year": {
                "type": "integer"
            },
            "month": {
                "type": "integer"
            },
            "day": {
                "type": "integer"
            }
        },
        "required": [
            "year",
            "month",
            "day"
        ]
    }
}
```

## テスト

[PHPUnit](https://phpunit.readthedocs.io/ja/latest/)を使用してリソースのテストを作成します。

`tests/Resource/App/WeekdayTest.php`に以下のテストコードを記述します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceInterface;
use MyVendor\Weekday\Injector;
use PHPUnit\Framework\TestCase;

class WeekdayTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $injector = Injector::getInstance('app');
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testOnGet(): void
    {
        $ro = $this->resource->get('app://self/weekday', ['year' => '2001', 'month' => '1', 'day' => '1']);
        $this->assertSame(200, $ro->code);
        $this->assertSame('Mon', $ro->body['weekday']);
    }
}
```

`setUp()`メソッドでは、コンテキスト（app）を指定してアプリケーションインジェクター（`Injector`）からリソースクライアント（`ResourceInterface`）を取得します。テストメソッド`testOnGet`では、このリソースクライアントを使用してリクエストを実行し、結果を検証します。

テストを実行してみましょう。

```bash
./vendor/bin/phpunit
```
```
PHPUnit 9.5.4 by Sebastian Bergmann and contributors.

....                                                                4 / 4 (100%)

Time: 00:00.281, Memory: 14.00 MB
```

プロジェクトにはテストと品質管理のための各種コマンドが用意されています。

テストカバレッジを取得するには：
```bash
composer coverage
```

より高速なカバレッジ計測を行う[pcov](https://pecl.php.net/package/pcov)を使用する場合：
```bash
composer pcov
```

カバレッジレポートは`build/coverage/index.html`をWebブラウザで開いて確認できます。

コーディング規約への準拠を確認：
```bash
composer cs
```

コーディング規約違反の自動修正：
```bash
composer cs-fix
```

## 静的解析

コードの静的解析は`composer sa`コマンドで実行します。

```bash
composer sa
```

これまでのコードを解析すると、以下のエラーがphpstanで検出されます。

```
   
  15     Cannot call method format() on DateTimeImmutable|false.  
  

[^1]:このプロジェクトのソースコードは各セクション毎に[bearsunday/Tutorial](https://github.com/bearsunday/tutorial1/commits/v3)にコミットされています。適宜参照してください。
[^2]:通常、**vendor**名には個人またはチーム（組織）の名前を使用します。GitHubのアカウント名やチーム名が適切です。**project**名にはアプリケーション名を指定します。


# チュートリアル2

このチュートリアルでは、以下のツールを用いて標準に基づいた高品質なREST（ハイパーメディア）アプリケーション開発を学びます。

* JSONのスキーマを定義し、バリデーションやドキュメンテーションに利用する [JSON Schema](https://json-schema.org/)
* ハイパーメディアタイプ [HAL (Hypertext Application Language)](https://stateless.group/hal_specification.html)  
* CakePHPが開発しているDBマイグレーションツール [Phinx](https://book.cakephp.org/phinx/0/en/index.html) 
* PHPのインターフェイスとSQL文実行を束縛する [Ray.MediaQuery](https://github.com/ray-di/Ray.MediaQuery)

[tutorial2](https://github.com/bearsunday/tutorial2/commits/v2-php8.2)のコミットを参考にして進めましょう。

## プロジェクト作成

プロジェクトスケルトンを作成します。

```
composer create-project bear/skeleton MyVendor.Ticket
```

**vendor**名を`MyVendor`に、**project**名を`Ticket`として入力します。

## マイグレーション

Phinxをインストールします。

```
composer require --dev robmorgan/phinx
```

プロジェクトルートフォルダの`.env.dist`ファイルにDB接続情報を記述します。

```
TKT_DB_HOST=127.0.0.1:3306
TKT_DB_NAME=ticket
TKT_DB_USER=root
TKT_DB_PASS=''
TKT_DB_SLAVE=''
TKT_DB_DSN=mysql:host=${TKT_DB_HOST}
```

`.env.dist`ファイルはこのようにして、実際の接続情報は`.env`に記述しましょう。[^1]

次にPhinxが利用するフォルダを作成します。

```bash
mkdir -p var/phinx/migrations
mkdir var/phinx/seeds
```

`.env`の接続情報をPhinxで利用するために`var/phinx/phinx.php`を設置します。

```php
<?php
use BEAR\Dotenv\Dotenv;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

(new Dotenv())->load(dirname(__DIR__, 2));

$development = new PDO(getenv('TKT_DB_DSN'), getenv('TKT_DB_USER'), getenv('TKT_DB_PASS'));
$test = new PDO(getenv('TKT_DB_DSN') . '_test', getenv('TKT_DB_USER'), getenv('TKT_DB_PASS'));
return [
    'paths' => [
        'migrations' => __DIR__ . '/migrations',
    ],
    'environments' => [
        'development' => [
            'name' => $development->query("SELECT DATABASE()")->fetchColumn(),
            'connection' => $development
        ],
        'test' => [
            'name' => $test->query("SELECT DATABASE()")->fetchColumn(),
            'connection' => $test
        ]
    ]
];
```

### setupスクリプト

データベース作成やマイグレーションを簡単に実行できるように、`bin/setup.php`を編集します。

```php
<?php
use BEAR\Dotenv\Dotenv;

require_once dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv())->load(dirname(__DIR__));

chdir(dirname(__DIR__));
passthru('rm -rf var/tmp/*');
passthru('chmod 775 var/tmp');
passthru('chmod 775 var/log');
// db
$pdo = new \PDO('mysql:host=' . getenv('TKT_DB_HOST'), getenv('TKT_DB_USER'), getenv('TKT_DB_PASS'));
$pdo->exec('CREATE DATABASE IF NOT EXISTS ' . getenv('TKT_DB_NAME'));
$pdo->exec('DROP DATABASE IF EXISTS ' . getenv('TKT_DB_NAME') . '_test');
$pdo->exec('CREATE DATABASE ' . getenv('TKT_DB_NAME') . '_test');
passthru('./vendor/bin/phinx migrate -c var/phinx/phinx.php -e development');
passthru('./vendor/bin/phinx migrate -c var/phinx/phinx.php -e test');
```

次に`ticket`テーブルを作成するためにマイグレーションクラスを作成します。

```
./vendor/bin/phinx create Ticket -c var/phinx/phinx.php
```
```
Phinx by CakePHP - https://phinx.org.

...
created var/phinx/migrations/20210520124501_ticket.php
```

`var/phinx/migrations/{current_date}_ticket.php`を編集して`change()`メソッドを実装します。

```php
<?php
use Phinx\Migration\AbstractMigration;

final class Ticket extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('ticket', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('title', 'string')
            ->addColumn('date_created', 'datetime')
            ->create();
    }
}
```

`.env.dist`ファイルを以下のように変更します。

```diff
 TKT_DB_USER=root
 TKT_DB_PASS=
 TKT_DB_SLAVE=
-TKT_DB_DSN=mysql:host=${TKT_DB_HOST}
+TKT_DB_DSN=mysql:host=${TKT_DB_HOST};dbname=${TKT_DB_NAME}
```

準備が完了したので、セットアップコマンドを実行してテーブルを作成します。

```
composer setup
```
```
> php bin/setup.php
...
All Done. Took 0.0248s
```

テーブルが作成されました。次回からこのプロジェクトのデータベース環境を整えるには`composer setup`を実行するだけで行えます。

マイグレーションクラスの記述について詳しくは[Phinxのマニュアル：マイグレーションを書く](https://book.cakephp.org/3.0/ja/phinx/migrations.html)をご覧ください。

## モジュール

モジュールをComposerでインストールします。

```
composer require ray/identity-value-module ray/media-query -w
```

AppModuleでパッケージをインストールします。

`src/Module/AppModule.php`

```php
<?php
namespace MyVendor\Ticket\Module;

use BEAR\Dotenv\Dotenv;
use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;

use BEAR\Resource\Module\JsonSchemaModule;
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\IdentityValueModule\IdentityValueModule;
use Ray\MediaQuery\DbQueryConfig;
use Ray\MediaQuery\MediaQueryModule;
use Ray\MediaQuery\Queries;
use function dirname;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        (new Dotenv())->load(dirname(__DIR__, 2));
        $this->install(
            new AuraSqlModule(
                (string) getenv('TKT_DB_DSN'),
                (string) getenv('TKT_DB_USER'),
                (string) getenv('TKT_DB_PASS'),
                (string) getenv('TKT_DB_SLAVE')
            )
        );
        $this->install(
            new MediaQueryModule(
                Queries::fromDir($this->appMeta->appDir . '/src/Query'), [
                    new DbQueryConfig($this->appMeta->appDir . '/var/sql'),
                ]
            )
        );
        $this->install(new IdentityValueModule());
        $this->install(
            new JsonSchemaModule(
                $this->appMeta->appDir . '/var/schema/response',
                $this->appMeta->appDir . '/var/schema/request'
            )
        );
        $this->install(new PackageModule());
    }
}
```

## SQL

チケット用の3つのSQLを`var/sql`に保存します。[^13]

`var/sql/ticket_add.sql`

```sql
/* ticket add */
INSERT INTO ticket (id, title, date_created)
VALUES (:id, :title, :dateCreated);
```

`var/sql/ticket_list.sql`

```sql
/* ticket list */
SELECT id, title, date_created
  FROM ticket
 LIMIT 3;
```

`var/sql/ticket_item.sql`

```sql
/* ticket item */
SELECT id, title, date_created
  FROM ticket
 WHERE id = :id;
```

作成時に単体でそのSQLが動作するか確認しましょう。

例えば、PHPStormにはデータベースツールの[DataGrip](https://www.jetbrains.com/ja-jp/datagrip/)が含まれていて、コード補完やSQLのリファクタリングなどSQL開発に必要な機能が揃っています。
DB接続などのセットアップを行えば、SQLファイルをIDEで直接実行できます。[^3][^4]

## JSON Schema

`Ticket`（チケットアイテム）、`Tickets`（チケットアイテムリスト）のリソース表現を[JSON Schema](http://json-schema.org/)で定義し、それぞれ保存します。

`var/schema/response/ticket.json`

```json
{
  "$id": "ticket.json",
  "$schema": "http://json-schema.org/draft-07/schema#",
  "title": "Ticket",
  "type": "object",
  "required": ["id", "title", "date_created"],
  "properties": {
    "id": {
      "description": "The unique identifier for a ticket.",
      "type": "string",
      "maxLength": 64
    },
    "title": {
      "description": "The title of the ticket.",
      "type": "string",
      "maxLength": 255
    },
    "date_created": {
      "description": "The date and time that the ticket was created.",
      "type": "string",
      "format": "date-time"
    }
  }
}
```

`var/schema/response/tickets.json`

Ticketsはticketの配列です。

```json
{
  "$id": "tickets.json",
  "$schema": "http://json-schema.org/draft-07/schema#",
  "title": "Tickets",
  "type": "object",
  "required": ["tickets"],
  "properties": {
    "tickets": {
      "type": "array",
      "items": { "$ref": "./ticket.json" }
    }
  }
}
```

* **$id** - ファイル名を指定しますが、公開する場合はURLを記述します。
* **title** - オブジェクト名としてAPIドキュメントで扱われます。
* **examples** - 適宜、例を指定しましょう。オブジェクト全体のものも指定できます。

PHPStormではエディタの右上に緑色のチェックが出ていて問題がないことが分かります。スキーマ作成時にスキーマ自身もバリデートしましょう。

## クエリーインターフェイス

インフラストラクチャへのアクセスを抽象化したPHPのインターフェイスを作成します。

* Ticketリソースを読み出す **TicketQueryInterface**
* Ticketリソースを作成する **TicketCommandInterface**

`src/Query/TicketQueryInterface.php`

```php
<?php

namespace MyVendor\Ticket\Query;

use MyVendor\Ticket\Entity\Ticket;
use Ray\MediaQuery\Annotation\DbQuery;

interface TicketQueryInterface
{
    #[DbQuery('ticket_item')]
    public function item(string $id): Ticket|null;

    /** @return array<Ticket> */
    #[DbQuery('ticket_list')]
    public function list(): array;
}
```

`src/Query/TicketCommandInterface.php`

```php
<?php

namespace MyVendor\Ticket\Query;

use DateTimeInterface;
use Ray\MediaQuery\Annotation\DbQuery;

interface TicketCommandInterface
{
    #[DbQuery('ticket_add')]
    public function add(string $id, string $title, DateTimeInterface $dateCreated = null): void;
}
```

`#[DbQuery]`アトリビュートでSQL文を指定します。

このインターフェイスに対する実装を用意する必要はありません。指定されたSQLのクエリを行うオブジェクトが自動生成されます。

インターフェイスを**副作用が発生するコマンド**または**値を返すクエリー**という2つの関心に分けていますが、リポジトリパターンのように1つにまとめたり、[ADRパターン](https://github.com/pmjones/adr)のように1インターフェイス1メソッドにしても構いません。アプリケーション設計者が方針を決定します。

## エンティティ

メソッドの返り値に`array`を指定すると、データベースの結果はそのまま連想配列として得られますが、メソッドの返り値にエンティティの型を指定すると、その型にハイドレーションされます。

```php
#[DbQuery('ticket_item')]
public function item(string $id): array // 配列が得られる
```

```php
#[DbQuery('ticket_item')]
public function item(string $id): Ticket|null; // Ticketエンティティが得られる
```

複数行（row_list）の時は`/** @return array<Ticket>*/`とPHPDocで`Ticket`が配列で返ることを指定します。

```php
/** @return array<Ticket> */
#[DbQuery('ticket_list')]
public function list(): array; // Ticketエンティティの配列が得られる
```

各行の値は名前引数でコンストラクタに渡されます。[^named]

[^named]: [PHP 8.0+ 名前付き引数 ¶](https://www.php.net/manual/ja/functions.arguments.php#functions.named-arguments)。PHP 7.xの場合にはカラムの順番になります。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Entity;

class Ticket
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $dateCreated
    ) {}
}
```

## リソース

リソースクラスはクエリーインターフェイスに依存します。

## ticketリソース

`ticket`リソースを`src/Resource/App/Ticket.php`に作成します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;
use MyVendor\Ticket\Query\TicketQueryInterface;

class Ticket extends ResourceObject
{
    public function __construct(
        private TicketQueryInterface $query
    ){}
    
    #[JsonSchema("ticket.json")]
    public function onGet(string $id = ''): static
    {
        $this->body = (array) $this->query->item($id);

        return $this;
    }
}
```

アトリビュート`#[JsonSchema]`は`onGet()`で出力される値が`ticket.json`のスキーマで定義されていることを表します。
AOPによってリクエスト毎にバリデートされます。

シードを入力してリソースをリクエストしてみましょう。[^8]

```bash 
% mysql -u root -e "INSERT INTO ticket (id, title, date_created) VALUES ('1', 'foo', '1970-01-01 00:00:00')" ticket
```

```bash
% php bin/app.php get '/ticket?id=1'
```
```bash
200 OK
Content-Type: application/hal+json

{
    "id": "1",
    "title": "foo",
    "date_created": "1970-01-01 00:00:00",
    "_links": {
        "self": {
            "href": "/ticket?id=1"
        }
    }
}
```

### Ray.MediaQuery

Ray.MediaQueryを使えば、ボイラープレートとなりやすい実装クラスをコーディングすることなく、インターフェイスから自動生成されたSQL実行オブジェクトがインジェクトされます。[^5]

SQL文には`;`で区切った複数のSQL文を記述することができ、複数のSQLに同じパラメータが名前でバインドされます。SELECT以外のクエリではトランザクションも実行されます。

利用クラスはインターフェイスにしか依存していないので、動的にSQLを生成したい場合にはRay.MediaQueryの代わりにクエリービルダーをインジェクトしたSQL実行クラスで組み立てたSQLを実行すれば良いでしょう。
詳しくはマニュアルの[データベース](database.html)をご覧ください。

## 埋め込みリンク

通常Webサイトのページは複数のリソースを内包します。例えばブログの記事ページであれば、記事以外にもおすすめや広告、カテゴリーリンクなどが含まれるかもしれません。
それらをクライアントがバラバラに取得する代わりに、独立したリソースとして埋め込みリンクで1つのリソースに束ねることができます。

HTMLとそこに記述される`<img>`タグをイメージしてください。どちらも独立したURLを持ちますが、画像リソースがHTMLリソースに埋め込まれていてHTMLを取得するとHTML内に画像が表示されます。
これらはハイパーメディアタイプの[Embedding links (LE)](http://amundsen.com/hypermedia/hfactor/#le)と呼ばれるもので、埋め込まれるリソースがリンクされています。

ticketリソースにprojectリソースを埋め込んでみましょう。Projectクラスを用意します。

`src/Resource/App/Project.php`

```php
<?php

namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\ResourceObject;

class Project extends ResourceObject
{
    public function onGet(): static
    {
        $this->body = ['title' => 'Project A'];

        return $this;
    }
}
```

Ticketリソースにアトリビュート`#[Embed]`を追加します。

```diff
+use BEAR\Resource\Annotation\Embed;
+use BEAR\Resource\Request;
+
+   #[Embed(src: '/project', rel: 'project')]
    #[JsonSchema("ticket.json")]
    public function onGet(string $id = ''): static
    {
+        assert($this->body['project'] instanceof Request);
-        $this->body = (array) $this->query->item($id);
+        $this->body += (array) $this->query->item($id);
```

`#[Embed]`アトリビュートの`src`で指定されたリソースのリクエストがbodyプロパティの`rel`キーにインジェクトされ、レンダリング時に遅延評価され文字列表現になります。

例を簡単にするためにこの例ではパラメータを渡していませんが、メソッド引数が受け取った値をURIテンプレートを使って渡すこともできますし、インジェクトされたリクエストのパラメータを修正、追加することもできます。
詳しくは[リソース](resource.html)をご覧ください。

もう一度リクエストすると`_embedded`というプロパティにprojectリソースの状態が追加されているのが分かります。

```
% php bin/app.php get '/ticket?id=1'
```
```diff
{
    "id": "1",
    "title": "foo",
    "date_created": "1970-01-01 00:00:00",
+    "_embedded": {
+        "project": {
+            "title": "Project A"
+        }
    }
}
```

埋め込みリソースはREST APIの重要な機能です。コンテンツにツリー構造を与えHTTPリクエストコストを削減します。
情報が他の何の情報を含んでいるかはドメインの関心事です。クライアントで都度取得するのではなく、その関心事はサーバーサイドのLE（埋め込みリンク）でうまく表すことができます。[^6]

## ticketsリソース

`POST`で作成、`GET`でチケットリストが取得できる`tickets`リソースを`src/Resource/App/Tickets.php`に作成します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;
use Koriym\HttpConstants\ResponseHeader;
use Koriym\HttpConstants\StatusCode;
use MyVendor\Ticket\Query\TicketCommandInterface;
use MyVendor\Ticket\Query\TicketQueryInterface;
use Ray\IdentityValueModule\UuidInterface;
use function uri_template;

class Tickets extends ResourceObject
{
    public function __construct(
        private TicketQueryInterface $query,
        private TicketCommandInterface $command,
        private UuidInterface $uuid
    ){}

    #[Link(rel: "doPost", href: '/tickets')]
    #[Link(rel: "goTicket", href: '/ticket{?id}')]
    #[JsonSchema("tickets.json")]
    public function onGet(): static
    {
        $this->body = [
            'tickets' => $this->query->list()
        ];
        
        return $this;
    }

    #[Link(rel: "goTickets", href: '/tickets')]
    public function onPost(string $title): static
    {
        $id = (string) $this->uuid;
        $this->command->add($id, $title);

        $this->code = StatusCode::CREATED;
        $this->headers[ResponseHeader::LOCATION] = uri_template('/ticket{?id}', ['id' => $id]);

        return $this;
    }
}
```

インジェクトされた`$uuid`を文字列にキャストすることでUUIDの文字列表現が得られます。また`#[Link]`は他のリソース（アプリケーション状態）へのリンクを表します。

`add()`メソッドで現在時刻を渡していないことに注目してください。
値が渡されない場合、nullではなく、MySQLの現在時刻文字列がSQLにバインドされます。
なぜなら`DateTimeInterface`に束縛された現在時刻DateTimeオブジェクトの文字列表現（現在時刻文字列）がSQLに束縛されているからです。

```php
public function add(string $id, string $title, DateTimeInterface $dateCreated = null): void;
```

SQL内部でNOW()とハードコーディングすることや、メソッドに毎回現在時刻を渡す手間を省きます。
`DateTimeオブジェクト`を渡すこともできますし、テストのコンテキストでは固定のテスト用時刻を束縛することもできます。

このようにクエリーの引数にインターフェイスを指定するとそのオブジェクトをDIを使って取得し、その文字列表現がSQLに束縛されます。
例えばログインユーザーIDなどを束縛してアプリケーションで横断的に利用できます。[^7]

## ハイパーメディアAPIテスト

> REST（Representational State Transfer）という用語は、2000年にRoy Fieldingが博士論文の中で紹介、定義したもので「適切に設計されたWebアプリケーションの動作」をイメージさせることを目的としています。
> それはWebリソースのネットワーク（仮想ステートマシン）であり、ユーザーはリソース識別子（URL）と、GETやPOSTなどのリソース操作（アプリケーションステートの遷移）を選択することで、アプリケーションを進行させ、その結果、次のリソースの表現（次のアプリケーションステート）がエンドユーザーに転送されて使用されるというものです。
>
> -- [Wikipedia (REST)](https://en.wikipedia.org/wiki/Representational_state_transfer)

RESTアプリケーションでは次のアクションがURLとしてサービスから提供され、クライアントはそれを選択します。

HTML Webアプリケーションは完全にRESTfulです。その操作は「（aタグなどで）**提供されたURLに遷移する**」または「**提供されたフォームを埋めて送信する**」のいずれかでしかありません。

REST APIのテストも同様に記述します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Hypermedia;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use Koriym\HttpConstants\ResponseHeader;
use MyVendor\Ticket\Injector;
use MyVendor\Ticket\Query\TicketQueryInterface;
use PHPUnit\Framework\TestCase;
use Ray\Di\InjectorInterface;
use function json_decode;

class WorkflowTest extends TestCase
{
    protected ResourceInterface $resource;
    protected InjectorInterface $injector;

    protected function setUp(): void
    {
        $this->injector = Injector::getInstance('hal-api-app');
        $this->resource = $this->injector->getInstance(ResourceInterface::class);
    }

    public function testIndex(): static
    {
        $index = $this->resource->get('/');
        $this->assertSame(200, $index->code);

        return $index;
    }

    /**
     * @depends testIndex
     */
    public function testGoTickets(ResourceObject $response): static
    {
        $json = (string) $response;
        $href = json_decode($json)->_links->{'goTickets'}->href;
        $ro = $this->resource->get($href);
        $this->assertSame(200, $ro->code);

        return $ro;
    }

    /**
     * @depends testGoTickets
     */
    public function testDoPost(ResourceObject $response): static
    {
        $json = (string) $response;
        $href = json_decode($json)->_links->{'doPost'}->href;
        $ro = $this->resource->post($href, ['title' => 'title1']);
        $this->assertSame(201, $ro->code);

        return $ro;
    }

    /**
     * @depends testDoPost
     */
    public function testGoTicket(ResourceObject $response): static
    {
        $href = $response->headers[ResponseHeader::LOCATION];
        $ro = $this->resource->get($href);
        $this->assertSame(200, $ro->code);

        return $ro;
    }
}
```

起点となるルートページも必要です。

`src/Resource/App/Index.php`

```php
<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    #[Link(rel: 'goTickets', href: '/tickets')]
    public function onGet(): static
    {
        return $this;
    }
}
```

* `setUp`ではリソースクライアントを生成し、`testIndex()`でルートページにアクセスしています。
* レスポンスを受け取った`testGoTickets()`メソッドではそのレスポンスオブジェクトをJSON表現にして、次のチケット一覧を取得するリンク`goTickets`を取得しています。
* リソースボディのテストを記述する必要はありません。レスポンスのJSON Schemaバリデーションが通ったという保証がされているので、ステータスコードの確認だけでOKです。
* RESTの統一インターフェイスに従い、次にアクセスするリクエストURLは常にレスポンスに含まれます。それを次々に検査します。

> **RESTの統一インターフェイス**
>
> 1) リソースの識別、2) 表現によるリソースの操作、3) 自己記述メッセージ、
> 4) アプリケーション状態のエンジンとしてのハイパーメディア（HATEOAS）の4つのインターフェイス制約です。[^11]

実行してみましょう。

```bash
./vendor/bin/phpunit --testsuite hypermedia
```

ハイパーメディアAPIテスト（RESTアプリケーションテスト）はRESTアプリケーションがステートマシンであるということをよく表し、ワークフローをユースケースとして記述することができます。
REST APIテストを見ればそのアプリケーションがどのように使われるか網羅されているのが理想です。

### HTTPテスト

HTTPでREST APIのテストを行うためにはテスト全体を継承して、`setUp`でクライアントをHTTPテストクライアントにします。

```php
class WorkflowTest extends Workflow
{
    protected function setUp(): void
    {
        $this->resource = new HttpResource('127.0.0.1:8080', __DIR__ . '/index.php', __DIR__ . '/log/workflow.log');
    }
}
```

このクライアントはリソースクライアントと同じインターフェイスを持ちますが、実際のリクエストはビルトインサーバーに対してHTTPリクエストで行われ、サーバーからのレスポンスを受け取ります。
1つ目の引数はビルトインサーバーのURLです。`new`されると2番目の引数で指定されたブートストラップスクリプトでビルトインサーバーが起動します。

テストサーバー用のブートストラップスクリプトもAPIコンテキストに変更します。

`tests/Http/index.php`

```diff
-exit((new Bootstrap())('hal-app', $GLOBALS, $_SERVER));
+exit((new Bootstrap())('hal-api-app', $GLOBALS, $_SERVER));
```

実行してみましょう。

```
./vendor/bin/phpunit --testsuite http
```

#### HTTPアクセスログ

curlで行われた実際のHTTPリクエスト/レスポンスログが3番目の引数のリソースログに記録されます。

```
curl -s -i 'http://127.0.0.1:8080/'

HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Date: Fri, 21 May 2021 22:41:02 GMT
Connection: close
X-Powered-By: PHP/8.0.6
Content-Type: application/hal+json

{
    "_links": {
        "self": {
            "href": "/index"
        },
        "goTickets": {
            "href": "/tickets"
        }
    }
}
```

```
curl -s -i -H 'Content-Type:application/json' -X POST -d '{"title":"title1"}' http://127.0.0.1:8080/tickets

HTTP/1.1 201 Created
Host: 127.0.0.1:8080
Date: Fri, 21 May 2021 22:41:02 GMT
Connection: close
X-Powered-By: PHP/8.0.6
Location: /ticket?id=421d997c-9a0e-4018-a6c2-9b8758cac6d6
```

実際に記録されたJSONは、特に複雑な構造を持つ場合に確認するのに役に立ちます。APIドキュメントと併せて確認するのにもいいでしょう。
HTTPクライアントはE2Eテストにも利用することができます。

## APIドキュメント

ResourceObjectではメソッドシグネチャがAPIの入力パラメータになっていて、レスポンスがスキーマ定義されています。
その自己記述性の高さからAPIドキュメントが自動生成することができます。

作成してみましょう。[docs](https://bearsunday.github.io/tutorial2/)フォルダにドキュメントが出力されます。

```
composer doc
```

IDL（インターフェイス定義言語）を記述する労力を削減しますが、より価値があるのはドキュメントが最新のPHPコードに追従し常に正確なことです。
CIに組み込み常にコードとAPIドキュメントが同期している状態にするのがいいでしょう。

関連ドキュメントをリンクすることもできます。設定について詳しくは[ApiDoc](apidoc.html)をご覧ください。

## コード例

以下のコード例も用意しています。

* `Test`コンテキストを追加してテスト毎にDBをクリアするTestModule [4e9704d](https://github.com/bearsunday/tutorial2/commit/4e9704d3bc65b9c7e7a8c13164dfe7cc3d6929b2)
* DBクエリで連想配列を返す代わりにハイドレートされたエンティティクラスを返す[Ray.MediaQuery](https://github.com/ray-di/Ray.MediaQuery)の`entity`オプション [29f0a1f](https://github.com/bearsunday/tutorial2/commit/29f0a1f4d4bf51e6c0a722fd6b2f44cb78de999e)
* 静的なSQLと動的なSQLを合成したクエリービルダー [9d095ac](https://github.com/bearsunday/tutorial2/commit/9d095acfed6150fb99f36d502ae13f03bdf2916d)

## RESTフレームワーク

Web APIには以下の3つのスタイルがあります。

* トンネル（SOAP、GraphQL）
* URI（オブジェクト、CRUD）
* ハイパーメディア（REST）

リソースを単なるRPCとして扱うURIスタイル[^9]に対して、このチュートリアルで学んだのはリソースがリンクされているRESTです。[^10]
リソースは`#[Link]`のLO（アウトバウンドリンク）で結ばれワークフローを表し、`#[Embed]`のLE（埋め込みリンク）でツリー構造を表しています。

BEAR.Sundayは標準に基づいたクリーンなコードであることを重視します。

フレームワーク固有のバリデータよりJSON Schema。独自ORMより標準SQL。独自構造JSONよりIANA標準メディアタイプ[^12]JSON。

アプリケーション設計は「実装が自由である」ことではなく「制約の選択が自由である」ということが重要です。
アプリケーションはその制約に基づき、開発効率やパフォーマンス、後方互換性を壊さない進化可能性を目指すと良いでしょう。

----

[^1]: `.env`はgit commitされないようにしておきます。
[^2]: 例えばECサイトであれば、商品一覧、カートに入れる、注文、支払いなど、それぞれのアプリケーションステートの遷移をテストで表します。
[^3]: [PHPStorm データベースツールおよび SQL](https://pleiades.io/help/phpstorm/relational-databases.html)
[^4]: [データベース図](https://pleiades.io/help/phpstorm/creating-diagrams.html)などでクエリプランや実行計画を確認し、作成するSQLの質を高めます。
[^5]: Ray.MediaQueryはHTTP APIリクエストにも対応しています。
[^6]: このようなコンテンツの階層構造のことを、IA（インフォメーションアーキテクチャ）では**タクソノミー**と呼びます。[Understanding Information Architecture](https://understandinggroup.com/ia-theory/understanding-information-architecture)参照
[^7]: Ray.MediaQuery [README](https://github.com/ray-di/Ray.MediaQuery/blob/1.x/README.ja.md#%E6%97%A5%E4%BB%98%E6%99%82%E5%88%BB)
[^8]: ここでは例としてMySQLから直接実行していますが、マイグレーションツールでシードを入力したりIDEのDBツールの利用方法も学びましょう。
[^9]: いわゆる"Restish API"。REST APIと紹介されている多くのAPIはこのURI/オブジェクトスタイルで、RESTが誤用されています。
[^10]: チュートリアルからリンクを取り除けばURIスタイルになります。
[^11]: 広く誤解されていますが、統一インターフェイスはHTTPメソッドのことではありません。[Uniform Interface](https://www.ics.uci.edu/~fielding/pubs/dissertation/rest_arch_style.htm)参照
[^12]: [https://www.iana.org/assignments/media-types/media-types.xhtml](https://www.iana.org/assignments/media-types/media-types.xhtml)
[^13]: このSQLは[SQLスタイルガイド](https://www.sqlstyle.guide/ja/)に準拠しています。PhpStormからは[Joe Celko](https://twitter.com/koriym/status/1410996122412150786)として設定できます。
コメントは説明になるだけでなく、スロークエリーログ等からもSQLを特定しやすくなります。

※ 以前のPHP 7対応のチュートリアルは[tutorial2_v1](tutorial2_v1.html)にあります。




# パッケージ

アプリケーションは独立したcomposerパッケージです。

フレームワークは依存として`composer install`でインストールしますが、他のアプリケーションも依存パッケージとして使うことができます。

## アプリケーション・パッケージ

### 構造

BEAR.Sundayアプリケーションのファイルレイアウトは[php-pds/skeleton](https://github.com/php-pds/skeleton)に準拠しています。

### bin/

実行可能なコマンドを設置します。

BEARのリソースはコンソール入力とWebの双方からアクセスできます。
使用するスクリプトによってコンテキストが変わります。

```bash
php bin/app.php options '/todos' # APIアクセス（appリソース）
```

```bash
php bin/page.php get '/todos?id=1' # Webアクセス（pageリソース）
```

```bash
php -S 127.0.0.1 bin/app.php # PHPサーバー
```

コンテキストが変わるとアプリケーションの振る舞いが変わります。
ユーザーは独自のコンテキストを作成することができます。

### src/

アプリケーション固有のクラスファイルを設置します。

### public/

Web公開フォルダです。

### var/

`log`、`tmp`フォルダは書き込み可能にします。`var/www`はWebドキュメントの公開エリアです。
`conf`など可変のファイルを設置します。

## 実行シーケンス

1. コンソール入力（`bin/app.php`、`page.php`）またはWebサーバーのエントリーファイル（`public/index.php`）が`bootstrap.php`を実行します。
2. `bootstrap.php`では実行コンテキストに応じたルートオブジェクト`$app`を作成します。
3. `$app`に含まれるルーターは外部のHTTPまたはCLIリクエストをアプリケーション内部のリソースリクエストに変換します。
4. リソースリクエストが実行され、結果がクライアントに転送されます。

## フレームワーク・パッケージ

フレームワークは以下のパッケージから構成されます。

### ray/aop
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/)
[![codecov](https://codecov.io/gh/ray-di/Ray.Aop/branch/2.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/ray-di/Ray.Aop)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Aop/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Aop)
[![Continuous Integration](https://github.com/ray-di/Ray.Aop/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ray-di/Ray.Aop/actions/workflows/continuous-integration.yml)

Javeの [AOPアライアンス](http://aopalliance.sourceforge.net/) に準拠したAOPフレームワークです。

### ray/di
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/)
[![codecov](https://codecov.io/gh/ray-di/Ray.Di/branch/2.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/ray-di/Ray.Di)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Di/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Di)
[![Continuous Integration](https://github.com/ray-di/Ray.Di/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ray-di/Ray.Di/actions/workflows/continuous-integration.yml)

[google/guice](https://github.com/google/guice) スタイルのDIフレームワークです。`ray/aop`を含みます。

### bear/resource
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Resource/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/bearsunday/BEAR.Resource)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Resource/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Resource)
[![Continuous Integration](https://github.com/bearsunday/BEAR.Resource/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.Resource/actions/workflows/continuous-integration.yml)

### bear/sunday
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Sunday/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/bearsunday/BEAR.Sunday)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Sunday/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Sunday)
[![Continuous Integration](https://github.com/bearsunday/BEAR.Sunday/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.Sunday/actions/workflows/continuous-integration.yml)

フレームワークのインターフェイスパッケージです。`bear/resource`を含みます。

### bear/package
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Package/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/bearsunday/BEAR.Pacakge)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Package/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Package)
[![Continuous Integration](https://github.com/bearsunday/BEAR.Package/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.Package/actions/workflows/continuous-integration.yml)

`bear/sunday`の実装パッケージです。`bear/sunday`を含みます。

## ライブラリ・パッケージ

必要なライブラリ・パッケージを`composer`でインストールします。

| **Category** | **Composer package** | **Library**
| ルーター |
| |[bear/aura-router-module](https://github.com/bearsunday/BEAR.AuraRouterModule) | [Aura.Router v2](https://github.com/auraphp/Aura.Router/tree/2.x) |
| データベース |
|| [ray/media-query](https://github.com/ray-di/Ray.MediaQuery) |
|| [ray/aura-sql-module](https://github.com/ray-di/Ray.AuraSqlModule) | [Aura.Sql v2](https://github.com/auraphp/Aura.Sql/tree/2.x)
|| [ray/dbal-module](https://github.com/ray-di/Ray.DbalModule) | [Doctrine DBAL](https://github.com/doctrine/dbal)
|| [ray/cake-database-module](https://github.com/ray-di/Ray.CakeDbModule) | [CakePHP v3 database](https://github.com/cakephp/database)
|| [ray/doctrine-orm-module](https://github.com/kawanamiyuu/Ray.DoctrineOrmModule) | [Doctrine ORM](https://github.com/doctrine/doctrine2)
| ストレージ |
||[bear/query-repository](https://github.com/bearsunday/BEAR.QueryRepository) | 読み書きリポジトリの分離（デフォルト）
||[bear/query-module](https://github.com/ray-di/Ray.QueryModule) | DBやWeb APIなどの外部アクセスの分離
| Web |
| |[madapaja/twig-module](http://bearsunday.github.io/manuals/1.0/ja/html.html) | [Twigテンプレートエンジン](http://twig.sensiolabs.org/)
| |[ray/web-form-module](http://bearsunday.github.io/manuals/1.0/ja/form.html) | Webフォーム & バリデーション
| |[ray/aura-web-module](https://github.com/Ray-Di/Ray.AuraWebModule) | [Aura.Web](https://github.com/auraphp/Aura.Web)
| |[ray/aura-session-module](https://github.com/ray-di/Ray.AuraSessionModule) | [Aura.Session](https://github.com/auraphp/Aura.Session)
| |[ray/symfony-session-module](https://github.com/kawanamiyuu/Ray.SymfonySessionModule) | [Symfony Session](https://github.com/symfony/http-foundation/tree/master/Session)
| バリデーション |
| |[ray/validate-module](https://github.com/ray-di/Ray.ValidateModule) | [Aura.Filter](https://github.com/auraphp/Aura.Filter)
| |[satomif/extra-aura-filter-module](https://github.com/satomif/ExtraAuraFilterModule) | [Aura.Filter](https://github.com/auraphp/Aura.Filter)
| 認証 |
| |[ray/oauth-module](https://github.com/Ray-Di/Ray.OAuthModule) | OAuth
| |[kuma-guy/jwt-auth-module](https://github.com/kuma-guy/BEAR.JwtAuthModule) | JSON Web Token
| |[ray/role-module](https://github.com/ray-di/Ray.RoleModule) | [Zend Acl](https://github.com/zendframework/zend-permissions-acl)　 Zend Acl
| |[bear/acl-resource](https://github.com/bearsunday/BEAR.AclResource) | ACLベースのエンベドリソース
| ハイパーメディア |
| |[kuma-guy/siren-module](https://github.com/kuma-guy/BEAR.SirenModule) | Siren
|  開発 |
| |[ray/test-double](https://github.com/ray-di/Ray.TestDouble) | テストダブル
|  非同期ハイパフォーマンス |
| |[MyVendor.Swoole](https://github.com/bearsunday/MyVendor.Swoole) | [Swoole](https://github.com/swoole/swoole-src)

## ベンダー・パッケージ

特定のパッケージやツールの組み合わせをモジュールだけのパッケージにして再利用し、同様のプロジェクトのモジュールを共通化することができます。[^1]

## Semver

すべてのパッケージは[セマンティックバージョニング](https://semver.org/lang/ja/)に従います。マイナーバージョンアップでは後方互換性が破壊されることはありません。

---

[^1]: 参考モジュール [Koriym.DbAppPackage](https://github.com/koriym/Koriym.DbAppPackage)


# アプリケーション

## 実行シーケンス
アプリケーションは、コンパイル、リクエスト、レスポンスの順で実行されます。

### 0. コンパイル

コンテキストに応じたDIとAOPの設定により、アプリケーションの実行に必要なルートオブジェクト`$app`が生成されます。$appは`router`や`transfer`などの、アプリケーション実行に必要なサービスオブジェクトで構成されます[^graph]。$appはシリアライズされ、各リクエストで再利用されます。

[^graph]: オブジェクトは、他のオブジェクトを保持しているか、保持されているかによって繋がっています。これを[Object Graph](http://en.wikipedia.org/wiki/Object_graph)といい、$appはそのルートオブジェクトとなります。

* router - 外部入力をリソースリクエストに変換
* resource - リソースクライアント
* transfer - 出力

### 1. リクエスト

リクエストに基づき、リソースオブジェクトが作成されます。

リクエストに応じて`onGet`や`onPost`などに応答するメソッドを持つリソースオブジェクトは、自身の**リソースの状態**として`code`または`body`プロパティを設定します。

リソースオブジェクトのメソッドは、リソースの状態を変更するためだけのものであり、表現そのもの（HTMLやJSONなど）には関心を持ちません。

メソッドの前後では、ログや認証などの、メソッドに束縛されたアプリケーションロジックがAOPにより実行されます。

### 2. レスポンス

リソースに注入されたレンダラーが、JSONやHTMLなどの**リソースの状態表現**を作成し、クライアントに**転送**します。
(**RE**presentational **S**tate **T**ransfer = REST)

 <img src="/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>


## bootスクリプト

`public/`や`bin/`などのエントリーポイントに設置され、アプリケーションを実行します。
スクリプトでは、アプリケーション実行コンテキストを指定して実行します。

```php
require dirname(__DIR__) . '/autoload.php';
exit((new Bootstrap())('app', $GLOBALS, $_SERVER));
```

デフォルトではWebサーバースクリプトとして動作します。

```bash
php -S 127.0.0.1:8080 public/index.php
```

`cli`コンテキストを付加すると、コンソールアプリケーションのスクリプトとなります。

```php
exit((new Bootstrap())('cli-app', $GLOBALS, $_SERVER));
```

```bash
php bin/app.php get /user/1
```

## コンテキスト

コンテキストは、特定の目的のためのDIとAOPの束縛のセットです。コードは同じでも束縛が変わることで、アプリケーションは異なる振る舞いをします。
コンテキストには、フレームワークが用意しているbuilt-inコンテキストと、アプリケーションが作成するカスタムコンテキストがあります。

### built-inコンテキスト

* `app` - ベースアプリケーション
* `api` - APIアプリケーション
* `cli` - コンソールアプリケーション
* `hal` - HALアプリケーション
* `prod` - プロダクション

`app`の場合、リソースはJSONでレンダリングされます。
`api`は、デフォルトのリソースのスキーマをpageからappに変更します。Webのルートアクセス（GET /）は、page://self/からapp://self/へのアクセスとなります。
`cli`を指定するとコンソールアプリケーションとなります。
`prod`は、キャッシュの設定などをプロダクション用に最適化します。

コンテキスト名は、それぞれのモジュールに対応します。例えば、appはAppModule、cliはCliModuleに対応します。
コンテキストは組み合わせて使用することができます。例えば、`prod-hal-api-app`は、プロダクション用HALのAPIアプリケーションとして動作します。

### カスタムコンテキスト

アプリケーションの`src/Module/`に設置します。built-inコンテキストと同名の場合、カスタムコンテキストが優先されます。カスタムコンテキストからbuilt-inコンテキストを呼び出すことで、一部の束縛を上書きすることができます。

### コンテキスト無知

コンテキストの値は、ルートオブジェクトの作成のみに使用され、その後に消滅します。アプリケーションから参照可能なグローバルな"モード"は存在せず、アプリケーションは現在実行されているコンテキストを知ることはできません。外部の値を参照して振る舞いを変えるのではなく、**インターフェイスのみに依存**[^dip]し、**コンテキストによる束縛の変更**で振る舞いを変更します。

---

[^dip]: [依存性逆転の法則](https://ja.wikipedia.org/wiki/依存性逆転の原則)


# モジュール

モジュールは、DIとAOPの束縛のセットです。BEAR.Sundayでは、一般的な設定ファイルやConfigクラス、実行モードなどは存在しません。各コンポーネントが必要とする値は、依存性の注入により提供されます。モジュールがこの依存性の束縛を担当します。

アプリケーションの起点となるモジュールは`AppModule`（src/Module/AppModule.php）です。
`AppModule`内で、他の必要なモジュールを`install`します。

モジュールが必要とする値（ランタイムではなく、コンパイルタイムで必要な値）は、手動のコンストラクタインジェクションにより束縛を行います。

```php
class AppModule extends AbstractAppModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // 追加モジュール
        $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', getenv('db_username'), getenv('db_password')));
        $this->install(new TwigModule());
        // package標準のモジュール
        $this->install(new PackageModule());
    }
}
```

## DIの束縛

以下に代表的な束縛パターンを示します：

```php
// クラスの束縛
$this->bind($interface)->to($class);

// プロバイダー（ファクトリー）の束縛
$this->bind($interface)->toProvider($provider);

// インスタンス束縛
$this->bind($interface)->toInstance($instance);

// 名前付き束縛
$this->bind($interface)->annotatedWith($annotation)->to($class);

// シングルトン
$this->bind($interface)->to($class)->in(Scope::SINGLETON);

// コンストラクタ束縛
$this->bind($interface)->toConstructor($class, $named);
```

詳細については[DI](di.html)をご参照ください。

## AOPの設定

AOPは、クラスとメソッドを`Matcher`で"検索"し、マッチするメソッドにインターセプターを束縛します。

```php
// 例1：メソッド名による束縛
$this->bindInterceptor(
    $this->matcher->any(),                   // どのクラスの
    $this->matcher->startsWith('delete'),    // "delete"で始まるメソッド名のメソッドには
    [Logger::class]                          // Loggerインターセプターを束縛
);

// 例2：クラスとアノテーションによる束縛
$this->bindInterceptor(
    $this->matcher->SubclassesOf(AdminPage::class),  // AdminPageの継承または実装クラスの
    $this->matcher->annotatedWith(Auth::class),      // @Authアノテーションがアノテートされているメソッドには
    [AdminAuthentication::class]                     // AdminAuthenticationインターセプターを束縛
);
```

詳細については[AOP](aop.html)をご参照ください。

## 束縛の優先順位

### 同一モジュール内での優先順位

同じモジュール内では、先に束縛された方が優先されます。以下の例では、Foo1が優先されます：

```php
$this->bind(FooInterface::class)->to(Foo1::class);
$this->bind(FooInterface::class)->to(Foo2::class);
```

### モジュールインストールでの優先順位

先にインストールされたモジュールが優先されます。以下の例では、Foo1Moduleが優先されます：

```php
$this->install(new Foo1Module);
$this->install(new Foo2Module);
```

後からのモジュールを優先させたい場合は、`override`を使用します。以下の例では、Foo2Moduleが優先されます：

```php
$this->install(new Foo1Module);
$this->override(new Foo2Module);
```

### コンテキスト文字列での優先順位

コンテキスト文字列では、左のモジュールの束縛が優先されます。例えば、`prod-hal-app`の場合：

- AppModuleよりHalModule
- HalModuleよりProdModule

の順で優先してインストールされます。


# DI

依存性の注入（Dependency Injection）とは、基本的にオブジェクトが必要とするオブジェクト（依存）を、オブジェクト自身に構築させるのではなく、オブジェクトに提供することです。

依存性の注入では、オブジェクトはそのコンストラクタで依存性を受け取ります。オブジェクトを構築するには、まずそのオブジェクトの依存関係を構築しますが、それぞれの依存を構築するためにはそのまた依存が必要、という繰り返しになります。つまり、オブジェクトを構築するにはオブジェクトグラフを構築する必要があるのです。

| オブジェクトグラフとは？
|  オブジェクト指向のアプリケーションは**相互に関係のある複雑なオブジェクト網**を持ちます。オブジェクトはあるオブジェクトから所有されているか、他のオブジェクト（またはそのリファレンス）を含んでいるか、そのどちらかでお互いに接続されています。このオブジェクト網をオブジェクトグラフと呼びます。- [Wikipedia (en)](http://en.wikipedia.org/wiki/Object_graph)

オブジェクトグラフを手作業で構築すると、労力がかかり、ミスが発生しやすく、テストが困難になります。その代わりに、Dependency Injector（Ray.Di）がオブジェクトグラフを構築します。

Ray.DiはBEAR.Sundayで使用されているDIフレームワークで、[Google Guice](https://github.com/google/guice)に大きく影響を受けています。詳しくは[Ray.Diのマニュアル](https://ray-di.github.io/manuals/1.0/ja/index.html)をご覧ください。

