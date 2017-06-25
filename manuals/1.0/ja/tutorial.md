---
layout: docs-ja
title: チュートリアル
category: Manual
permalink: /manuals/1.0/ja/tutorial.html
---

## チュートリアル

年月日を入力すると曜日を返すWebサービスを作成してみましょう。
まずプロジェクトを作成します。

```bash
composer create-project bear/skeleton MyVendor.Weekday
```
**vendor**名を`MyVendor`に**project**名を`Weekday`として入力します。

最初にインストールされるアプリケーションリソースファイルを`src/Resource/App/Weekday.php`に作成します。

```php
<?php
namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;

class Weekday extends ResourceObject
{
    public function onGet(int $year, int $month, int $day) : ResourceObject
    {
        $date = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        $this['weekday'] = $date->format('D');

        return $this;
    }
}

```

この`MyVendor\Weekday\Resource\App\Weekday`リソースクラスは`/weekday`というパスでアクセスすることができます。
`GET`メソッドのクエリーが`onGet`メソッドの引数に渡されます。

コンソールでアクセスしてみましょう。まずはエラーを試してみます。

```
404 Not Found
content-type: application/vnd.error+json

{
    "message": "Not Found",
    "logref": "466fa1ee",
...
```


400はリクエストに問題があるエラーコードです。
次は引数をつけて正しいリクエストを試します。

```bash
php bootstrap/api.php get '/weekday?year=2001&month=1&day=1'

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

`application/hal+json`というメディアタイプで結果が正しく返って来ました。

これをWeb APIサービスにしてみましょう。
Built-inサーバーを立ち上げます。

```bash
php -S 127.0.0.1:8080 bootstrap/api.php
```

`curl`でHTTPの`GET`リクエストを行って確かめてみましょう。

```
curl -i 'http://127.0.0.1:8080/weekday?year=2001&month=1&day=1'
```

```
HTTP/1.1 200 OK
Host: 127.0.0.1:8080
Date: Sun, 04 Jun 2017 19:48:09 +0200
Connection: close
X-Powered-By: PHP/7.1.4
content-type: application/hal+json

{
    "weekday": "Mon",
    "_links": {
        "self": {
            "href": "/weekday/2001/1/1"
        }
    }
}
```

このリソースクラスにはGET以外のメソッドは用意されていないので、他のメソッドを試すと`405 Method Not Allowed`が返されます。これも試してみましょう。

```
curl -i -X POST 'http://127.0.0.1:8080/weekday?year=2001&month=1&day=1'
```

```
HTTP/1.1 405 Method Not Allowed
...
```

HTTP `OPTIONS` メソッドリクエストで利用可能なHTTPメソッドと必要なパラメーターを調べることができます。([RFC7231](https://tools.ietf.org/html/rfc7231#section-4.3.7))

```
curl -i -X OPTIONS 'http://127.0.0.1:8080/weekday'
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

## ルーティング

デフォルトのルーターはURLをディレクトリにマップする`WebRouter`です。
ここでは動的なパラメーターをパスで受け取るためにAuraルーターを使用します。

最初にcompoerでインストールします。
```bash
composer require bear/aura-router-module ~1.0
```

次に`src/Module/AppModule.php`で`AuraRouterModule`を上書き(override)インストールします。

```php
<?php
namespace MyVendor\Weekday\Module;

use BEAR\Package\PackageModule;
use Ray\Di\AbstractModule;
use josegonzalez\Dotenv\Loader as Dotenv;
use BEAR\Package\Provide\Router\AuraRouterModule; // この行を追加

class AppModule extends AbstractModule
{
    protected function configure()
    {
        Dotenv::load([
            'filepath' => dirname(dirname(__DIR__)) . '/.env',
            'toEnv' => true
        ]);
        $this->install(new PackageModule);
        $this->override(new AuraRouterModule); // この行を追加
    }
}
```

ルータースクリプトファイルを`var/conf/aura.route.php`に設置します。

```php?start_inline
<?php
/* @var $router \BEAR\Package\Provide\Router\AuraRoute */
/* @var $schemeHost string */

$router->route('/weekday', '/weekday/{year}/{month}/{day}');
```

試してみましょう。

```bash
php bootstrap/api.php get '/weekday/1981/09/08'
200 OK
Content-Type: application/hal+json

{
    "weekday": "Tue",
    "_links": {
        "self": {
            "href": "/weekday/1981/09/08"
        }
    }
}
```

## DI

[monolog](https://github.com/Seldaek/monolog) を使って結果をログする機能を追加してみましょう。
[composer](http://getcomposer.org)で取得します。

```bash
composer require monolog/monolog ~1.0
```

monologログオブジェクトは`new`で直接作成しないで、作成されたログオブジェクトを受け取るようにします。
このように必要なもの（依存）を自らが取得するのではなく、外部から代入する仕組みを [DI](http://ja.wikipedia.org/wiki/%E4%BE%9D%E5%AD%98%E6%80%A7%E3%81%AE%E6%B3%A8%E5%85%A5) といいます。

依存を提供する`MonologLoggerProvider`を`src/Module/MonologLoggerProvider.php`に作成します。

```php
<?php
namespace MyVendor\Weekday\Module;

use BEAR\AppMeta\AbstractAppMeta;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ray\Di\ProviderInterface;

class MonologLoggerProvider implements ProviderInterface
{
    /**
     * @var AbstractAppMeta
     */
    private $appMeta;

    public function __construct(AbstractAppMeta $appMeta)
    {
        $this->appMeta = $appMeta;
    }

    public function get()
    {
        $log = new Logger('weekday');
        $log->pushHandler(
            new StreamHandler($this->appMeta->logDir . '/weekday.log')
        );

        return $log;
    }
}
```

ログをファイル記録するために必要なログフォルダのパスの情報は、コンストラクタで受け取ったアプリケーションのメタ情報から取得します。
依存は`get`メソッドで提供します。

次に[ロガーインターフェイス](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)と、この依存を生成するファクトリークラスを結びつけます。
`src/Modules/AppModule.php`の`configure`メソッドに以下を追加します。

```php?start_inline
$this->bind(LoggerInterface::class)->toProvider(MonologLoggerProvider::class)->in(Scope::SINGLETON);
```

classキーワードでクラス名を解決するために以下のuse文も必要です。

```php?start_inline
use Psr\Log\LoggerInterface;
use Ray\Di\Scope;
```

どのクラスでもコンストラクタでmonologオブジェクトを受け取ることができるようになりました。
`src/Resource/App/Weekday.php`を修正してlogを書きだしてみます。

```php
<?php
namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use Psr\Log\LoggerInterface;

class Weekday extends ResourceObject
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onGet(int $year, int $month, int $day) : ResourceObject
    {
        $date = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        $this['weekday'] = $date->format('D');
        $this->logger->info("$year-$month-$day {$this['weekday']}");

        return $this;
    }
}
```

実行して`var/log/weekday.log`に結果が出力されていることを確認しましょう。

```bash
php bootstrap/api.php get '/weekday/2011/05/23'
cat var/log/weekday.log
```

## AOP

メソッドの実行時間を計測するためのベンチマーク処理を考えてみます。

```php?start_inline
$start = microtime(true);
// メソッド実行
$time = microtime(true) - $start;
```

ベンチマークを行う度にこのコードを付加して、不要になれば取り除くのは大変です。
**アスペクト指向プログラミング(AOP)**はこのようなメソッドの前後の特定処理をうまく合成することが出来ます。

まずAOPを実現するためにメソッドの実行を横取り（インターセプト）してベンチマークを行う**インターセプター**を`src/Interceptor/BenchMarker.php`に作成します。

```php
<?php
namespace MyVendor\Weekday\Interceptor;

use Psr\Log\LoggerInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class BenchMarker implements MethodInterceptor
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function invoke(MethodInvocation $invocation)
    {
        $start = microtime(true);
        $result = $invocation->proceed(); // 元のメソッドの実行
        $time = microtime(true) - $start;
        $msg = sprintf("%s: %s", $invocation->getMethod()->getName(), $time);
        $this->logger->info($msg);

        return $result;
    }
}
```

元のメソッドを横取りしたインターセプターの`invoke`メソッドでは、元メソッドの実行を`$invocation->proceed();`で行うことができます。
その前後にタイマーのリセット、計測記録の処理を行います。（メソッド実行オブジェクト[MethodInvocation](http://www.bear-project.net/Ray.Aop/build/apigen/class-Ray.Aop.MethodInvocation.html) `$invocation`から元メソッドのオブジェクトとメソッドの名前を取得しています。）

次にベンチマークをしたいメソッドに目印をつけるための[アノテーション](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html)を`src/Annotation/BenchMark.php `に作成します。

```php
<?php
namespace MyVendor\Weekday\Annotation;

/**
 * @Annotation
 */
final class BenchMark
{
}
```

`AppModule`で**Matcher**を使ってインターセプターを適用するメソッドを束縛（バインド）します。

```php?start_inline
use MyVendor\Weekday\Annotation\BenchMark;
use MyVendor\Weekday\Interceptor\BenchMarker;

// configure()に追加します。
$this->bindInterceptor(
    $this->matcher->any(),                           // どのクラスでも
    $this->matcher->annotatedWith(BenchMark::class), // @BenchMarkとアノテートされているメソッドに
    [BenchMarker::class]                             // BenchMarkerインターセプターを適用
);
```

ベンチマークを行いたいメソッドに`@BenchMark`とアノテートします。

```php
<?php
use MyVendor\Weekday\Annotation\BenchMark;

class Weekday
{
    /**
     * @BenchMark
     */
    public function onGet($year, $month, $day)
    {
```

これで計測したいメソッドに`@BenchMark`とアノテートすればいつでもベンチマークできるようになりました。

アノテーションとインターセプターによる機能追加は柔軟です。対象メソッドやメソッドを呼ぶ側に変更はありません。
アノテーションはそのままでも束縛を外せばベンチマークを行いません。例えば、開発時にのみ束縛を行い特定の秒数を越すと警告を行うこともできます。

実行して`var/log/weekday.log`に実行時間のログが出力されることを確認しましょう。

```bash
php bootstrap/api.php get '/weekday/2015/05/28'
cat var/log/weekday.log
```

## HTML

次に今のAPIアプリケーションをHTMLアプリケーションにしてみましょう。
今の`app`リソースに加えて、`src/Resource/Page/Index.php`に`page`リソースを追加します。

`page`リソースクラスは場所と役割が違うだけで`app`リソースと基本的に同じクラスです。

```php
<?php
namespace MyVendor\Weekday\Resource\Page;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    /**
     * @Embed(rel="weekday", src="app://self/weekday{?year,month,day}")
     */
    public function onGet(int $year, int $month, int $day) : ResourceObject
    {
        $this['year'] = $year;
        $this['month'] = $month;
        $this['day'] = $day;

        return $this;
    }
}
```

`@Embed`アノテーションで`app://self/weekday`リソースをbodyの`weekday`キーに埋め込んでいます。その際にクエリーを[RFC6570 URI template](https://github.com/ioseb/uri-template)を使って`{?year,month,day}`として同じものを渡しています。下記の２つのURI templateは同じURIを示しています。

 * `app://self/weekday{?year,month,day}`
 * `app://self/weekday?year={year},month={month},day={day}`

`<iframe>`や`<img>`タグで他のリソースを含むページをイメージしてください。これらもHTMLページが画像や他のHTMLなどのリソースを自身に埋め込んでいます。

上記のページクラスは下記のページクラスと同じものです。こちらは`@Embed`でリソースを埋め込むかわりに` use ResourceInject;`で`resource`リソースクライアントをインジェクトしてそのクラインアトでappリソースをセットしています。

```php
 <?php
namespace MyVendor\Weekday\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(int $year, int $month, int $day) : ResourceObject
    {
        $this['year'] = $year;
        $this['month'] = $month;
        $this['day'] = $day;
        $this['weekday'] = $this->resource
            ->get
            ->uri('app://self/weekday')
            ->withQuery(['year' => $year, 'month' => $month, 'day' => $day])
            ->request();

        return $this;
    }
}
```
最初の`@Embed`を使った方法は[宣言型プログラミング(Declative Programming)
](https://ja.wikipedia.org/wiki/%E5%AE%A3%E8%A8%80%E5%9E%8B%E3%83%97%E3%83%AD%E3%82%B0%E3%83%A9%E3%83%9F%E3%83%B3%E3%82%B0)、後者は[命令型プログラミング(Imperative Programming)](https://ja.wikipedia.org/wiki/%E5%91%BD%E4%BB%A4%E5%9E%8B%E3%83%97%E3%83%AD%E3%82%B0%E3%83%A9%E3%83%9F%E3%83%B3%E3%82%B0)です。`@Embed`を使った前者は簡潔で可読性が高くリソースの関係を良く表しています。

このリソースがどのような表現になるのか試してみましょう。

```bash
php bootstrap/web.php get '/?year=2000&month=1&day=1'
```

```
200 OK
content-type: application/hal+json

{
    "year": 2000,
    "month": 1,
    "day": 1,
    "_embedded": {
        "weekday": {
            "weekday": "Sat"
        }
    },
    "_links": {
        "self": {
            "href": "/index?year=2000&month=1&day=1"
        }
    }
}
```

他のリソースが`_embedded`されているのが確認できます。
リソースは`application/hal+json`メディアタイプで出力されていますが、これをHTML(text/html)で出力するためにHTMLモジュールをインストールします。[HTMLのマニュアル](/manuals/1.0/ja/html.html)参照。

composerインストール

```bash
composer require madapaja/twig-module ^1.0
```

`src/Module/HtmlModule.php`を作成

```php
<?php
namespace MyVendor\Weekday\Module;

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

`bootstrap/web.php`を変更

```php?start_inline
$context = 'cli-html-app';
require __DIR__ . '/bootstrap.php';
```

これで`text/html`メディア出力の準備はできました。最後に`src/Resource/Page/Index.html.twig`にtwigテンプレートを用意します。

```bash
<!DOCTYPE html>
<html>
<body>
{% raw %}The weekday of {{ year }}/{{ month }}/{{ day }} is {{ weekday.weekday }}.{% endraw %}
</body>
</html>
```

準備完了です。まずはコンソールでこのようなHTMLが出力されるか確認してみましょう。

```bash
php bootstrap/web.php get '/?year=1991&month=8&day=1'
```

```bash
200 OK
content-type: text/html; charset=utf-8

<!DOCTYPE html>
<html>
<body>
The weekday of 1991/8/1 is Thu.
</body>
</html>
```

もしこの時htmlが表示されなければ、テンプレートエンジンのエラーが発生しています。
その時はログファイル(`var/log/app.cli-html-app.log`)でエラーを確認しましょう。

次にWebサービスを行うために`public/index.php`を変更します。

```php?start_inline
$context = 'prod-html-app';
require dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';
```

PHPサーバーを立ち上げてwebブラウザで[http://127.0.0.1:8080/?year=2001&month=1&day=1](http://127.0.0.1:8080/?year=2001&month=1&day=1)をアクセスして確認してみましょう。

```bash
php -S 127.0.0.1:8080 public/index.php
```

[コンテキスト](/manuals/1.0/ja/application.html#context)はアプリケーションの実行モードのようなもので、複数指定できます。試してみましょう。

```php?start_inline
$context = 'app';           // JSONアプリケーション （最小）
$context = 'prod-hal-app';  // プロダクション用HALアプリケーション
```

コンテキストに応じたインスタンスを生成するPHPコードが生成されます。アプリケーションの`var/tmp/`フォルダを確認してみましょう。これらのファイルは普段見る必要はありませんが、オブジェクトがどのように作られているかを確認することができます。`diff`コマンドでコンテキストによってどのように依存が変更されているかを確認してみましょう。

```bash
diff -q var/tmp/app/ var/tmp/prod-hal-app/
```

## データベースを使ったハイパーメディアAPI

sqlite3を使ったアプリケーションリソースを作成してみましょう。
まずはコンソールで`var/db/todo.sqlite3`にDBを作成します。

```bash
mkdir var/db
sqlite3 var/db/todo.sqlite3

sqlite> create table todo(id integer primary key, todo, created);
sqlite> .exit
```

データベースは[AuraSql](https://github.com/ray-di/Ray.AuraSqlModule)や, [Doctrine Dbal](https://github.com/ray-di/Ray.DbalModule)、[CakeDB](https://github.com/ray-di/Ray.CakeDbModule)などから選べますが
ここではCakePHP3でも使われているCakeDBをインストールしてみましょう。

```bash
composer require ray/cake-database-module ~1.0
```

`src/Module/AppModule::configure()`でモジュールのインストールをします。

```php?start_inline
use Ray\CakeDbModule\CakeDbModule;
// ...

$dbConfig = [
    'driver' => 'Cake\Database\Driver\Sqlite',
    'database' => dirname(dirname(__DIR__)) . '/var/db/todo.sqlite3'
];
$this->install(new CakeDbModule($dbConfig));
```

セッターメソッドのtrait `DatabaseInject`を使うと`$this->db`でCakeDBオブジェクトを使えます。

Todoリソースを`src/Resource/App/Todo.php`に設置します。

```php
<?php
namespace MyVendor\Weekday\Resource\App;

use BEAR\Package\Annotation\ReturnCreatedResource;
use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\ResourceObject;
use Ray\CakeDbModule\Annotation\Transactional;
use Ray\CakeDbModule\DatabaseInject;

/**
 * @Cacheable
 */
class Todo extends ResourceObject
{
    use DatabaseInject;

    public function onGet(int $id) : ResourceObject
    {
        $this->body = $this
            ->db
            ->newQuery()
            ->select('*')
            ->from('todo')
            ->where(['id' => $id])
            ->execute()
            ->fetch('assoc');

        return $this;
    }

    /**
     * @Transactional
     * @ReturnCreatedResource
     */
    public function onPost(string $todo) : ResourceObject
    {
        $statement = $this->db->insert(
            'todo',
            ['todo' => $todo, 'created' => new \DateTime('now')],
            ['created' => 'datetime']
        );
        // hyper link
        $id = $statement->lastInsertId();
        // status code
        $this->code = 201;
        // created resource
        $this->headers['Location'] = '/todo?id=' . $id;

        return $this;
    }

    /**
     * @Transactional
     */
    public function onPut(int $id, string $todo) : ResourceObject
    {
        $this->db->update(
            'todo',
            ['todo' => $todo],
            ['id' => (int) $id]
        );
        $this->headers['Location'] = '/todo/?id=' . $id;
        // status code
        $this->code = 204;

        $this->body = (string) $this->onGet($id);

        return $this;
    }
}
```
アノテーションに注目してください。クラスに付いている`@Cacheable`はこのリソースのGETメソッドがキャッシュ可能なことを示しています。`OnPost`や`onPut`の`@Transactional`はデータベースアクセスのトランザクションを示し、`onPost`の


`POST`してみましょう。

```bash
201 Created
Location: /todo?id=1

{
    "id": "1",
    "todo": "shopping",
    "created": "2017-06-04 15:58:03",
    "_links": {
        "self": {
            "href": "/todo?id=1"
        }
    }
}
```

ステータスコード(`201 Created`)と`Location`ヘッダーで新しいリソースが`/todo/?id=1`に作成された事がわかります。[RFC7231 Section-6.3.2](https://tools.ietf.org/html/rfc7231#section-6.3.2) [日本語訳](https://triple-underscore.github.io/RFC7231-ja.html#section-6.3.2)

`@ReturnCreatedResource`とアノテートされているのでボディに作成されたリソースを返します。

次にこのリソースを`GET`します。

```bash
php bootstrap/api.php get '/todo?id=1'
```

```
200 OK
ETag: 2527085682
Last-Modified: Sun, 04 Jun 2017 15:23:39 GMT
content-type: application/hal+json

{
    "id": "1",
    "todo": "shopping",
    "created": "2017-06-04 15:58:03",
    "_links": {
        "self": {
            "href": "/todo?id=1"
        }
    }
}
```

ハイパーメディアAPIの完成です！

次にAPIサーバーを立ち上げます。

```bash
php -S 127.0.0.1:8081 bootstrap/api.php
```

今度は`curl`コマンドでGETしてみましょう。

```bash
curl -i 'http://127.0.0.1:8081/todo?id=1'
```

```bash
HTTP/1.1 200 OK
Host: 127.0.0.1:8081
Date: Sun, 04 Jun 2017 18:02:55 +0200
Connection: close
X-Powered-By: PHP/7.1.4
ETag: 2527085682
Last-Modified: Sun, 04 Jun 2017 16:02:55 GMT
content-type: application/hal+json

{
    "id": "1",
    "todo": "shopping",
    "created": "2017-06-04 15:58:03",
    "_links": {
        "self": {
            "href": "/todo?id=1"
        }
    }
}
```

何回かリクエストして`Last-Modified`の日付が変わらないことを確認しましょう。この時`onGet`メソッド内は実行されていません。試しにメソッド内で`echo`などを追加して確認してみましょう。

次に`PUT`メソッドでこのリソースを変更します。

```bash
curl -i http://127.0.0.1:8081/todo -X PUT -d "id=1&todo=think"
```

 `Content-Type` ヘッダーを使ってJSONでも指定することができます。

```bash
curl -i http://127.0.0.1:8081/todo -X PUT -H 'Content-Type: application/json' -d '{"id": "1", "todo":"think" }'
```

再度GETを行うと`Last-Modified`が変わっているのが確認できます。

```bash
curl -i 'http://127.0.0.1:8081/todo?id=1'
```

この`Last-Modified`の日付は`@Cacheable`で提供されるものです。
アプリケーションが管理したり、データベースのカラムを用意したりする必要はありません。

`@Cacheable`を使うと、リソースコンテンツは書き込み用のデータベースとは違うリソースの保存専用の「クエリーリポジトリ」で管理され、データの更新や`Etag`や`Last-Modified`のヘッダーの付加が透過的に行われます。



## アプリケーションのインポート

BEAR.Sundayで作られたリソースは再利用性に優れています。
他のアプリケーションのリソースを利用してみましょう。

ここではチュートリアルのために`my-vendor`に新規でアプリケーションを作成して手動でオートローダーを設定します。
（通常はアプリケーションをパッケージとして利用します）

```bash
mkdir my-vendor
cd my-vendor
composer create-project bear/skeleton Acme.Blog ~1.0@dev
```

`composer.json`で`autoload`のセクションに`Acme\\Blog`を追加します。

```bash

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

```php?start_inline
use BEAR\Resource\Module\ImportAppModule;
use BEAR\Resource\ImportApp;
use BEAR\Package\Context;

$importConfig = [
    new ImportApp('blog', 'Acme\Blog', 'prod-hal-app') // ホスト, 名前, コンテキスト
];
$this->override(new ImportAppModule($importConfig , Context::class));
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
        $this['blog'] = $this->resource->uri('page://blog/index')['greeting'];

        return $this;
    }
}
```
(`$this->resource->uri('page://blog/index')['greeting'];`は`$this->resource->get->uri('page://blog/index')->eager->request()->body['greeting']`と同じです。)

`page://blog/index`リソースが`blog`に代入されているはずです。`@Embed`も同様に使えます。

```bash
php bootstrap/api.php get /import
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

次にBEAR.Sundayでは無いシステムからこのリソースを利用してみましょう。
`bin/test.php`を作成します。

```php?start_inline
<?php
use BEAR\Package\Bootstrap;

require __DIR__ . '/autoload.php';

$app = (new Bootstrap)->getApp('MyVendor\Weekday', 'prod-hal-app');
echo $app->resource->uri('app://self/import')['blog'] . PHP_EOL;
```

試してみます。

```bash
php bint/test.php
```

```
Hello BEAR.Sunday
```

このようにBEAR.Sundayで作られたリソースは他のCMSやフレームワークからも簡単に再利用することができます。


## Because everything is a resource

情報の識別子URI、統一されたインターフェイス、ステートテレスなアクセス、強力なキャッシュシステム、ハイパーリンク、レイヤードシステム、自己記述性。
BEAR.SundayアプリケーションのリソースはこれらのRESTの特徴を備えたもので、再利用性に優れています。

異なるアプリケーションの情報もハイパーリンクで接続することができ、他のCMSやフレームワークからの利用やAPIサイトにすることも容易です。
リソースの値と表現は分離されていて、Webページですら他のアプリケーションのAPIになることができます。
