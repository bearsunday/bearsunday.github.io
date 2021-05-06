---
layout: docs-ja
title: チュートリアル (v2)
category: Manual
permalink: /manuals/1.0/ja/tutorial.html
---
# チュートリアル

このチュートリアルではBEAR.Sundayの基本機能の**DI**、**AOP**、**REST API**を紹介します。[^1]

## プロジェクト作成

年月日を入力すると曜日を返すWebサービスを作成してみましょう。
まずプロジェクトを作成します。

```bash
composer create-project bear/skeleton MyVendor.Weekday
```

**vendor**名を`MyVendor`に**project**名を`Weekday`として入力します。[^2]

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
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");
        $weekday = $dateTime->format('D');
        $this->body = ['weekday' => $weekday];

        return $this;
    }
}
```

この`MyVendor\Weekday\Resource\App\Weekday`リソースクラスは`/weekday`というパスでアクセスすることができます。
`GET`メソッドのクエリーが`onGet`メソッドの引数に渡されます。

コンソールでアクセスしてみましょう。まずはエラーを試してみます。

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

エラーは[application/vnd.error+json](https://github.com/blongden/vnd.error)メディアタイプで返されます。
400はリクエストに問題があるエラーコードです。エラーには`logref`IDがつけられ`var/log/`でエラーの詳しい内容を参照することができます。

次は引数をつけて正しいリクエストを試します。

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

[application/hal+json](https://tools.ietf.org/html/draft-kelly-json-hal-06)というメディアタイプで結果が正しく返って来ました。

これをWeb APIサービスにしてみましょう。
Built-inサーバーを立ち上げます。

```bash
php -S 127.0.0.1:8080 bin/app.php
```

`curl`でHTTPの`GET`リクエストを行って確かめてみましょう。

```
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

[PHPUnit](https://phpunit.readthedocs.io/ja/latest/)を使ったリソースのテストを作成しましょう。

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

`setUp()`ではコンテキスト(app)を指定するとアプリケーションのどのオブジェクトでも生成できるアプリケーションのインジェクター
`Injector`を使ってリソースクライアント(`ResourceInterface`)を取得していて、テストメソッド`testOnGet`でリソースをリクエストしてテストします。

実行してみましょう。

```
./vendor/bin/phpunit
```
```
PHPUnit 9.5.4 by Sebastian Bergmann and contributors.

....                                                                4 / 4 (100%)

Time: 00:00.281, Memory: 14.00 MB
```

インストールされたプロジェクトには他にはテストやコード検査を実行するコマンドが用意されています。
テストカバレッジを取得するには`composer coverage`を実行します。

```
composer coverage
```

[pcov](https://pecl.php.net/package/pcov)はより高速にカバレッジ計測を行います。

```
composer pcov
```

カバレッジの詳細を`build/coverage/index.html`をWebブラウザで開くことで見ることができます。

コーディングスタンダードにしたがっているかのチェックは`composer cs`コマンドで確認できます。
その自動修正は`composer cs-fix`コマンドでできます。

```
composer cs
```
```
composer cs-fix
```
## 静的解析

コードの静的解析は`composer sa`コマンドでおこないます。

```
composer sa
```

これまでのコードで実行してみると、以下のエラーがphpstanで検出されました。

```
 ------ --------------------------------------------------------- 
  Line   src/Resource/App/Weekday.php                             
 ------ --------------------------------------------------------- 
  15     Cannot call method format() on DateTimeImmutable|false.  
 ------ --------------------------------------------------------- 
```

先程のコードは`DateTimeImmutable::createFromFormat`で不正な値(年が-1など）を渡した時にfalseが帰ってくる事を考慮していませんでした。

試してみましょう。

```
php bin/app.php get '/weekday?year=-1&month=1&day=1
```

PHPエラーが発生した場合でもエラーハンドラーがキャッチして、正しい`application/vnd.error+json`メディアタイプでエラーメッセージが表示されていますが、
静的解析の検査をパスするには`DateTimeImmutable`の結果を`assert`するか型を検査して例外を投げるコードを追加します。

### assertの場合

```php
$dateTime =DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");
assert($dateTime instanceof DateTimeImmutable);
```

### 例外を投げる場合

まず専用の例外`src/Exception/InvalidDateTimeException.php`を作成します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Exception;

use RuntimeException;

class InvalidDateTimeException extends RuntimeException
{
}
```

値の検査をしたコードに修正します。

```diff
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
+use DateTimeImmutable;
use MyVendor\Weekday\Exception\InvalidDateTimeException;

class Weekday extends ResourceObject
{
    public function onGet(int $year, int $month, int $day): static
    {
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-$day");
+        if (! $dateTime instanceof DateTimeImmutable) {
+            throw new InvalidDateTimeException("$year-$month-$day");
+        }

        $weekday = $dateTime->format('D');
        $this->body = ['weekday' => $weekday];

        return $this;
    }
}
```

テストも追加します。

```diff
+    public function tesInvalidDateTime(): void
+    {
+        $this->expectException(InvalidDateTimeException::class);
+        $this->resource->get('app://self/weekday', ['year' => '-1', 'month' => '1', 'day' => '1']);
+    }
```

#### 例外作成のベストプラクティス
>
> 
> 入力のミスのために起こった例外なので、コード自身には問題がありません。このような実行時になって判明する例外は`RuntimeException`です。それを拡張して専用の例外を作成しました。
> 反対に例外の発生がバグによるものでコードの修正が必要なら`LogicException`を拡張して例外を作成します。例外のメッセージで種類を説明するのでなく、それぞれ専用の例外を作るようにします。


#### 防御的プログラミング

> この修正で` $dateTime->format('D');`の実行時に`$dateTime`にfalseが入る可能性がなくなりました。
> このように問題を発生の前に回避するプログラミングを防御的プログラミング(defensive programming)と呼び、その検査に静的解析が役立ちます。

#### コミット前のテスト

`composer tests`は　`composer test`に加えて、コーディング規約(cs)、静的解析(sa)の検査も行います。

```
composer tests
```

## ルーティング

デフォルトのルーターはURLをディレクトリにマップする`WebRouter`です。
ここでは動的なパラメーターをパスで受け取るためにAuraルーターを使用します。

最初にcomposerでインストールします。
```bash
composer require bear/aura-router-module ^2.0
```

次に`src/Module/AppModule.php`で`AuraRouterModule`を`PackageModule`の前でインストールします。

```diff
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Module;

use BEAR\Dotenv\Dotenv;
use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;
+use BEAR\Package\Provide\Router\AuraRouterModule;
use function dirname;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        (new Dotenv())->load(dirname(__DIR__, 2));
+        $appDir = $this->appMeta->appDir;
+        $this->install(new AuraRouterModule($appDir . '/var/conf/aura.route.php'));
        $this->install(new PackageModule());
    }
}

```

ルータースクリプトファイルを`var/conf/aura.route.php`に設置します。

```php
<?php
/** 
 * @see http://bearsunday.github.io/manuals/1.0/ja/router.html
 * @var \Aura\Router\Map $map 
 */

$map->route('/weekday', '/weekday/{year}/{month}/{day}');
```

試してみましょう。

```bash
php bin/app.php get /weekday/1981/09/08
```

```bash
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

求めた曜日をログする機能を追加してみましょう。

まず曜日をログする`src/MyLoggerInterface.php`を作成します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday;

interface MyLoggerInterface
{
    public function log(string $message): void;
}
```

リソースはこのログ機能を使うように変更します。

```diff
<?php
namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use MyVendor\Weekday\MyLoggerInterface;

class Weekday extends ResourceObject
{
+    public function __construct(public MyLoggerInterface $logger)
+    {
+    }

    public function onGet(int $year, int $month, int $day): static
    {
        $weekday = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day")->format('D');
        $this->body = [
            'weekday' => $weekday
        ];
+        $this->logger->log("$year-$month-$day {$weekday}");

        return $this;
    }
}
```
`Weekday`クラスはロガーサービスをコンストラクタで受け取って利用しています。
このように必要なもの（依存）を`new`で生成したりコンテナから取得しないで、外部から代入してもらう仕組みを [DI](http://ja.wikipedia.org/wiki/%E4%BE%9D%E5%AD%98%E6%80%A7%E3%81%AE%E6%B3%A8%E5%85%A5) といいます。


次に`MyLoggerInterface`を`MyLogger`に実装します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday;

use BEAR\AppMeta\AbstractAppMeta;

use function error_log;

use const PHP_EOL;

class MyLogger implements MyLoggerInterface
{
    private string $logFile;

    public function __construct(AbstractAppMeta $meta)
    {
        $this->logFile = $meta->logDir . '/weekday.log';
    }

    public function log(string $message): void
    {
        error_log($message . PHP_EOL, 3, $this->logFile);
    }
}
```

`MyLogger`を実装するためにはアプリケーションのログディレクトリの情報(`AbstractAppMeta`)が必要ですが、これも`依存`としてコンストラクタで受け取ります。
つまり`Weekday`リソースは`MyLogger`に依存していますが、`MyLogger`もログディレクトリ情報を依存にしています。このようにDIで構築されたオブジェクトは、依存が依存を..と繰り返し依存の代入が行われます。

この依存解決を行うのがDIツール(dependency injector)です。

DIツールで`MyLoggerInterface`と`MyLogger`を束縛(bind)するために`src/Module/AppModule.php`の`configure`メソッドを編集します。

```diff
class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        (new Dotenv())->load(dirname(__DIR__, 2));
        $appDir = $this->appMeta->appDir;
        $this->install(new AuraRouterModule($appDir . '/var/conf/aura.route.php'));
+        $this->bind(MyLoggerInterface::class)->to(MyLogger::class);
        $this->install(new PackageModule());
    }
}
```

これでどのクラスでもコンストラクタで`MyLoggerInterface`でロガーを受け取ることができるようになりました。

実行して`var/log/cli-hal-api-app/weekday.log`に結果が出力されていることを確認しましょう。

```bash
php bin/app.php get /weekday/2011/05/23
```

```bash
cat var/log/cli-hal-api-app/weekday.log
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

declare(strict_types=1);

namespace MyVendor\Weekday\Interceptor;

use MyVendor\Weekday\MyLoggerInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

use function microtime;
use function sprintf;

class BenchMarker implements MethodInterceptor
{
    public function __construct(private MyLoggerInterface $logger)
    {
    }

    public function invoke(MethodInvocation $invocation): mixed
    {
        $start = microtime(true);
        $result = $invocation->proceed(); // 元のメソッドの実行
        $time = microtime(true) - $start;
        $message = sprintf('%s: %0.5f(µs)', $invocation->getMethod()->getName(), $time);
        $this->logger->log($message);

        return $result;
    }
}
```

元のメソッドを横取りしたインターセプターの`invoke`メソッドでは、元メソッドの実行を`$invocation->proceed();`で行うことができます。
その前後にタイマーのリセット、計測記録の処理を行います。（メソッド実行オブジェクト[MethodInvocation](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php) `$invocation`から元メソッドのオブジェクトとメソッドの名前を取得しています。）

次にベンチマークをしたいメソッドに目印をつけるための[アトリビュート](https://www.php.net/manual/ja/language.attributes.overview.php)を`src/Annotation/BenchMark.php `に作成します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class BenchMark
{
}
```

`AppModule`で**Matcher**を使ってインターセプターを適用するメソッドを束縛（バインド）します。

```diff
+use MyVendor\Weekday\Annotation\BenchMark;
+use MyVendor\Weekday\Interceptor\BenchMarker;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        (new Dotenv())->load(dirname(__DIR__, 2));
        $appDir = $this->appMeta->appDir;
        $this->install(new AuraRouterModule($appDir . '/var/conf/aura.route.php'));
        $this->bind(MyLoggerInterface::class)->to(MyLogger::class);
+        $this->bindInterceptor(
+            $this->matcher->any(),                           // どのクラスでも
+            $this->matcher->annotatedWith(BenchMark::class), // #[BenchMark]と属性の付けられたメソッドに
+            [BenchMarker::class]                             // BenchMarkerインターセプターを適用
+        );
        $this->install(new PackageModule());
    }
}
```

ベンチマークを行いたいメソッドに`#[BenchMark]`のアトリビュートを付与します。

```diff
+use MyVendor\Weekday\Annotation\BenchMark;

class Weekday extends ResourceObject
{

+   #[BenchMark]
    public function onGet(int $year, int $month, int $day): static
    {
```

これで計測したいメソッドに`#[BenchMark]`のアトリビュートを付与すればいつでもベンチマークできるようになりました。

アトリビュートとインターセプターによる機能追加は柔軟です。対象メソッドやメソッドを呼ぶ側に変更はありません。
アトリビュートはそのままでも束縛を外せばベンチマークを行いません。例えば、開発時にのみ束縛を行い特定の秒数を越すと警告を行うこともできます。

実行して`var/log/weekday.log`に実行時間のログが出力されることを確認しましょう。

```bash
php bin/app.php get '/weekday/2015/05/28'
```

```bash
cat var/log/cli-hal-api-app/weekday.log
```

## HTML

次に今のAPIアプリケーションをHTMLアプリケーションにしてみましょう。
今の`app`リソースに加えて、`src/Resource/Page/Index.php`に`page`リソースを追加します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\Page;

use BEAR\Resource\ResourceObject;
use MyVendor\Weekday\Resource\App\Weekday;

class Index extends ResourceObject
{
    public function __construct(private Weekday $weekday)
    {
    }

    public function onGet(int $year, int $month, int $day): static
    {
        $weekday = $this->weekday->onGet($year, $month, $day);
        $this->body = [
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'weekday' => $weekday->body['weekday']
        ];

        return $this;
    }
}
```

`page`リソースクラスは場所と役割が違うだけで`app`リソースと基本的に同じクラスです。
このリソースではインジェクトしたappリソースをに処理を委譲(delegate)していいます。

（典型的なシナリオは`page`はパブリック公開されたHTMLページ、`app`はDBなどインフラレイヤーに近いところで、`page`と共に使う時は非公開のリソースです。
どちらを公開にするかは、実行時のコンテキストで決定されます。MVCに例えるとappリソースがモデルの役割を果たし、pageリソースがコントローラーの役割を果たしています。
）

このリソースがどのような表現になるのか試してみましょう。

```bash
php bin/page.php get '/?year=2000&month=1&day=1'
```

```
200 OK
Content-Type: application/hal+json

{
    "year": 2000,
    "month": 1,
    "day": 1,
    "weekday": "Sat",
    "_links": {
        "self": {
            "href": "/index?year=2000&month=1&day=1"
        }
    }
}

```

リソースは`application/hal+json`メディアタイプで出力されていますが、これをHTML(text/html)で出力するためにHTMLモジュールをインストールします。[HTMLのマニュアル](/manuals/1.0/ja/html.html)参照。

composerインストール

```bash
composer require madapaja/twig-module ^2.0
```

`src/Module/HtmlModule.php`を作成します。

```php
<?php
namespace MyVendor\Weekday\Module;

use Madapaja\TwigModule\TwigErrorPageModule;
use Madapaja\TwigModule\TwigModule;
use Ray\Di\AbstractModule;

class HtmlModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new TwigModule);
        $this->install(new TwigErrorPageModule);
    }
}
```

`templates`フォルダをコピーします

```bash
cp -r vendor/madapaja/twig-module/var/templates var
```

`bin/page.php`を変更してコンテキストを`html-app`にします。

```php
<?php
use MyVendor\Weekday\Bootstrap;

require dirname(__DIR__) . '/autoload.php';
exit((new Bootstrap())(PHP_SAPI === 'cli' ? 'cli-html-app' : 'html-app', $GLOBALS, $_SERVER));
```

これで`text/html`出力の準備はできました。
最後に`var/templates/Page/Index.html.twig`ファイルを編集します。

```bash
{% raw %}{% extends 'layout/base.html.twig' %}
{% block title %}Weekday{% endblock %}
{% block content %}
The weekday of {{ year }}/{{ month }}/{{ day }} is {{ weekday.weekday }}.
{% endblock %}{% endraw %}
```

準備完了です。まずはコンソールでこのようなHTMLが出力されるか確認してみましょう。

```bash
php bin/page.php get '/?year=1991&month=8&day=1'
```

```html
200 OK
Content-Type: text/html; charset=utf-8

<!DOCTYPE html>
<html>
...
```

もしこの時htmlが表示されなければ、テンプレートエンジンのエラーが発生しています。
その時はログファイル(`var/log/app.cli-html-app.log`)でエラーを確認しましょう。

次にWebサービスを行うために`public/index.php`も変更します。


```php
<?php

use MyVendor\Weekday\Bootstrap;

require dirname(__DIR__) . '/autoload.php';
exit((new Bootstrap())(PHP_SAPI === 'cli-server' ? 'html-app' : 'prod-html-app', $GLOBALS, $_SERVER));
```

PHPサーバーを立ち上げてwebブラウザで[http://127.0.0.1:8080/?year=2001&month=1&day=1](http://127.0.0.1:8080/?year=2001&month=1&day=1)をアクセスして確認してみましょう。

```bash
php -S 127.0.0.1:8080 public/index.php
```

[コンテキスト](/manuals/1.0/ja/application.html#context)はアプリケーションの実行モードのようなもので、複数指定できます。試してみましょう。

```php?start_inline
<?php
// JSONアプリケーション （最小）
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')('app'));
```

```php?start_inline
<?php
// プロダクション用HALアプリケーション
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')('prod-hal-app'));
```

コンテキストに応じたインスタンスを生成するPHPコードが生成されます。アプリケーションの`var/tmp/{context}/di`フォルダを確認してみましょう。
これらのファイルは普段見る必要はありませんが、オブジェクトがどのように作られているかを確認することができます。

## REST API

sqlite3を使ったアプリケーションリソースを作成してみましょう。
まずはコンソールで`var/db/todo.sqlite3`にDBを作成します。

```bash
mkdir var/db
sqlite3 var/db/todo.sqlite3

sqlite> create table todo(id integer primary key, todo, created_at);
sqlite> .exit
```

データベースアクセスは[AuraSql](https://github.com/ray-di/Ray.AuraSqlModule)や, [Doctrine Dbal](https://github.com/ray-di/Ray.DbalModule)、[CakeDB](https://github.com/ray-di/Ray.CakeDbModule)などから選べますが
ここではRay.AuraSqlModuleをインストールしてみましょう。

```bash
composer require ray/aura-sql-module
```

`src/Module/AppModule::configure()`でモジュールのインストールをします。

```diff
<?php
+use Ray\AuraSqlModule\AuraSqlModule;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        // ...
+        $this->install(new AuraSqlModule(sprintf('sqlite:%s/var/db/todo.sq3', $this->appMeta->appDir)));
        $this->install(new PackageModule());
    }
}
```

Todoリソースを`src/Resource/App/Todos.php`に設置します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\App;

use Aura\Sql\ExtendedPdoInterface;
use BEAR\Package\Annotation\ReturnCreatedResource;
use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\ResourceObject;
use DateTimeImmutable;
use Ray\AuraSqlModule\Annotation\Transactional;

use function sprintf;

#[Cacheable]
class Todos extends ResourceObject
{
    public function __construct(private ExtendedPdoInterface $pdo, private DateTimeImmutable $date)
    {
    }

    public function onGet(string $id = ''): static
    {
        $sql = $id ? /** @lang SQL */'SELECT * FROM todo WHERE id=:id' : /** @lang SQL */'SELECT * FROM todo';
        $this->body = $this->pdo->fetchAssoc($sql, ['id' => $id]);

        return $this;
    }

    #[Transactional, ReturnCreatedResource]
    public function onPost(string $todo): static
    {
        $this->pdo->perform(/** @lang SQL */'INSERT INTO todo (todo, created_at) VALUES (:todo, :created_at)', [
            'todo' => $todo,
            'created_at' => $this->date->format('Y-m-d H:i:s')
        ]);
        $this->code = 201; // Created
        $this->headers['Location'] = sprintf('/todos?id=%s', $this->pdo->lastInsertId()); // new URL

        return $this;
    }

    #[Transactional]
    public function onPut(int $id, string $todo): static
    {
        $this->pdo->perform(/** @lang SQL */'UPDATE todo SET todo = :todo WHERE id = :id', [
            'id' => $id,
            'todo' => $todo
        ]);
        $this->code = 204; // No content

        return $this;
    }
}
```

アトリビュートに注目してください。

#### #[Cacheable]

クラスのアトリビュート`#[Cacheable]`はこのリソースのGETメソッドがキャッシュ可能なことを示しています。

#### #[Transactional]

`onPost`や`onPut`の`#[Transactional]`はデータベースアクセスのトランザクションを示しています。

#### #[ReturnCreatedResource]

`onPost`の`#[ReturnCreatedResource]`作成し`Location`でURLが示されているリソースをbodyに含んで返します。
この時`Location`ヘッダーのURIで実際に`onGet`がコールされるので`Location`ヘッダーの内容が正しいことが保証されると同時に`onGet`をコールすることでキャッシュも作られます。


### POSTリクエスト

`POST`してみましょう。

まずキャッシュを有効にしたテストをするために`bin/test.php`コンテキストのブートファイル`bin/test.php`を作成します。

```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')('prod-cli-hal-api-app'));
```

コンソールコマンドでリクエストします。`POST`ですがBEAR.Sundayではクエリーの形でパラメーターを渡します。

```bash
php bin/test.php post '/todos?todo=shopping'
```

```bash
201 Created
Location: /todos?id=1

{
    "id": "1",
    "todo": "shopping",
    "created": "2017-06-04 15:58:03",
    "_links": {
        "self": {
            "href": "/todos?id=1"
        }
    }
}
```

ステータスコードは`201 Created`。`Location`ヘッダーで新しいリソースが`/todos/?id=1`に作成された事がわかります。
[RFC7231 Section-6.3.2](https://tools.ietf.org/html/rfc7231#section-6.3.2) [日本語訳](https://triple-underscore.github.io/RFC7231-ja.html#section-6.3.2)

次にこのリソースを`GET`します。

```bash
php bin/test.php get '/todos?id=1'
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
            "href": "/todos?id=1"
        }
    }
}
```

ハイパーメディアAPIの完成です！APIサーバーを立ち上げましょう。

```bash
php -S 127.0.0.1:8081 bin/app.php
```

`curl`コマンドでGETします。

```bash
curl -i 'http://127.0.0.1:8081/todos?id=1'
```

```bash
HTTP/1.1 200 OK
Host: 127.0.0.1:8081
Date: Sun, 02 May 2021 17:10:55 GMT
Connection: close
X-Powered-By: PHP/8.0.3
Content-Type: application/hal+json
ETag: 197839553
Last-Modified: Sun, 02 May 2021 17:10:55 GMT
Cache-Control: max-age=31536000

{
    "id": "1",
```

何回かリクエストして`Last-Modified`の日付が変わらないことを確認しましょう。この時`onGet`メソッド内は実行されていません。（試しにメソッド内で`echo`などを追加して確認してみましょう）

`expiry`を設定してない`Cacheable`アトリビュートのキャッシュは時間でキャッシュが無効になる事はありません。
`onPut($id, $todo)`や`onDelete($id)` でリソースの変更が行われるとキャッシュが再生成されます。

次に`PUT`メソッドでこのリソースを変更します。

```bash
curl -i http://127.0.0.1:8081/todos -X PUT -d "id=1&todo=think"
```
ボディがない事を示す`204 No Content`のレスポンスが返ってきます。

```
HTTP/1.1 204 No Content
...
```

 `Content-Type` ヘッダーでメディアタイプを指定する事ができます。`application/json`でも試してみましょう。

```bash
curl -i http://127.0.0.1:8081/todos -X PUT -H 'Content-Type: application/json' -d '{"id": 1, "todo":"think" }'
```

再度GETを行うと`Etag`と`Last-Modified`が変わっているのが確認できます。

```bash
curl -i 'http://127.0.0.1:8081/todos?id=1'
```

この`Last-Modified`の日付は`#[Cacheable]`で提供されるものです。
アプリケーションが管理したり、データベースのカラムを用意したりする必要はありません。

`#[Cacheable]`を使うと、リソースコンテンツは書き込み用のデータベースとは違うリソースの保存専用の「クエリーリポジトリ」で管理され`Etag`や`Last-Modified`のヘッダーの付加が自動で行われます。

## Because Everything is A Resource.

BEARでは全てがリソースです。

リソースの識別子URI、統一されたインターフェイス、ステートレスなアクセス、強力なキャッシュシステム、ハイパーリンク、レイヤードシステム、自己記述性。
BEAR.SundayアプリケーションのリソースはこれらのRESTの特徴を備えたものです。HTTPの標準に従い再利用性に優れています。

BEAR.Sundayは**DI**で依存を結び、AOPで横断的関心事を結び、RESTの力でアプリケーションの情報をリソースとして結ぶコネクティングレイヤーのフレームワークです。

---

※ 以前のPHP7対応のチュートリアルは[tutorial_v1](tutorial_v1.html)にあります。

[^1]:このプロジェクトのソースコードは各セクション毎に[bearsunday/Tutorial](https://github.com/bearsunday/Tutorial/commits/v2)にコミットしています。適宜参照してください。
[^2]:通常は**vendor**名は個人またはチーム（組織）の名前を入力します。githubのアカウント名やチーム名が適当でしょう。**project**にはアプリケーション名を入力します。
