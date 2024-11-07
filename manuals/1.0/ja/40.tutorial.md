---
layout: docs-ja
title: チュートリアル (v3)
category: Manual
permalink: /manuals/1.0/ja/tutorial.html
---
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
 ------ --------------------------------------------------------- 
  Line   src/Resource/App/Weekday.php                             
 ------ --------------------------------------------------------- 
  15     Cannot call method format() on DateTimeImmutable|false.  
 ------ --------------------------------------------------------- 
```

現在のコードでは、`DateTimeImmutable::createFromFormat`が不正な値（年が-1など）を受け取った場合にfalseを返すことを考慮していません。

実際に試してみましょう。

```bash
php bin/app.php get '/weekday?year=-1&month=1&day=1'
```

PHPエラーが発生した場合でもエラーハンドラーがキャッチし、正しい`application/vnd.error+json`メディアタイプでエラーメッセージが返されます。しかし、静的解析の検査をパスするには、`DateTimeImmutable`の結果を`assert`するか型を検査して例外を投げるコードを追加する必要があります。

### assertを使用する場合

```php
$dateTime = (new DateTimeImmutable)->createFromFormat('Y-m-d', "$year-$month-$day");
assert($dateTime instanceof DateTimeImmutable);
```

### 例外を投げる場合

まず、専用の例外クラス`src/Exception/InvalidDateTimeException.php`を作成します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Exception;

use RuntimeException;

class InvalidDateTimeException extends RuntimeException
{
}
```

次に、値の検査を行うように元のコードを修正します。

```diff
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use DateTimeImmutable;
+use MyVendor\Weekday\Exception\InvalidDateTimeException;

class Weekday extends ResourceObject
{
    public function onGet(int $year, int $month, int $day): static
    {
        $dateTime = (new DateTimeImmutable)->createFromFormat('Y-m-d', "$year-$month-$day");
+        if (! $dateTime instanceof DateTimeImmutable) {
+            throw new InvalidDateTimeException("$year-$month-$day");
+        }

        $weekday = $dateTime->format('D');
        $this->body = ['weekday' => $weekday];

        return $this;
    }
}
```

テストケースも追加します。

```diff
+    public function testInvalidDateTime(): void
+    {
+        $this->expectException(InvalidDateTimeException::class);
+        $this->resource->get('app://self/weekday', ['year' => '-1', 'month' => '1', 'day' => '1']);
+    }
```

#### 例外作成のベストプラクティス

入力値の妥当性チェックで検出されるエラーは、コード自体の問題ではありません。このような実行時に判明するエラーは`RuntimeException`を使用します。一方、例外の発生がバグによるものでコードの修正が必要な場合は`LogicException`を使用します。エラーの種類はメッセージではなく、個別の例外クラスとして表現するのがベストプラクティスです。

#### 防御的プログラミング

この修正により、`$dateTime->format('D')`の実行時に`$dateTime`にfalseが入る可能性が排除されました。
このように問題の発生を事前に防ぐプログラミング手法を防御的プログラミング（defensive programming）と呼びます。静的解析はこのような問題の早期発見に役立ちます。

#### コミット前のチェック

`composer tests`コマンドは、`composer test`に加えて、コーディング規約（cs）と静的解析（sa）の検査も実行します。

```bash
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
        $weekday = (new DateTimeImmutable)->createFromFormat('Y-m-d', "$year-$month-$day")->format('D');
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

## アスペクト指向プログラミング（AOP）

メソッドの実行時間を計測するベンチマーク処理を例に、AOPの活用方法を見ていきましょう。
従来の方法では、以下のようなコードを各メソッドに追加する必要がありました：

```php
$start = microtime(true);
// メソッド実行
$time = microtime(true) - $start;
```

このようなコードを必要に応じて追加・削除するのは手間がかかり、ミスの原因にもなります。
**アスペクト指向プログラミング（AOP）** を使用すると、このようなメソッドの前後に実行される処理をうまく合成することができます。

まず、メソッドの実行を横取り（インターセプト）してベンチマークを行う**インターセプター**を`src/Interceptor/BenchMarker.php`に作成します。

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

このインターセプターでは、元のメソッドの実行（`$invocation->proceed()`）の前後で時間を計測し、実行時間をログに記録しています。

次に、ベンチマークを適用したいメソッドを指定するための[アトリビュート](https://www.php.net/manual/ja/language.attributes.overview.php)を`src/Annotation/BenchMark.php`に作成します。

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

`AppModule`で**Matcher**を使用して、このインターセプターを適用するメソッドを指定します。

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
+            $this->matcher->annotatedWith(BenchMark::class), // #[BenchMark]のアトリビュートが付けられたメソッドに
+            [BenchMarker::class]                             // BenchMarkerインターセプターを適用
+        );
        $this->install(new PackageModule());
    }
}
```

ベンチマークを行いたいメソッドには`#[BenchMark]`アトリビュートを付与します。

```diff
+use MyVendor\Weekday\Annotation\BenchMark;

class Weekday extends ResourceObject
{

+   #[BenchMark]
    public function onGet(int $year, int $month, int $day): static
    {
```

これにより、`#[BenchMark]`アトリビュートを付与したメソッドの実行時間が自動的に計測されるようになります。

AOPの利点は、このような機能追加が非常に柔軟に行えることです：
- 対象メソッドのコードを変更する必要がない
- メソッドを呼び出す側のコードも変更不要
- アトリビュートはそのままで束縛を外せば機能を無効化できる
- 開発時のみ有効にするなど、環境に応じた制御が容易

実際に動作を確認してみましょう。

```bash
php bin/app.php get '/weekday/2015/05/28'
```

ログファイルで実行時間を確認：
```bash
cat var/log/cli-hal-api-app/weekday.log
```

## HTMLの出力

これまでのAPIアプリケーションに、HTML出力機能を追加してみましょう。
既存の`app`リソースに加えて、新しく`src/Resource/Page/Index.php`に`page`リソースを作成します。

```php
<?php

declare(strict_types=1);

namespace MyVendor\Weekday\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Embed;

class Index extends ResourceObject
{
    #[Embed(rel:"_self", src: "app://self/weekday{?year,month,day}")]
    public function onGet(int $year, int $month, int $day): static
    {
        $this->body += [
            'year' => $year,
            'month' => $month,
            'day' => $day,
        ];

        return $this;
    }
}
```

`page`リソースクラスは`app`リソースと同じクラスですが、HTML表示に特化した役割を持せたい時などに公開リソースとして`page`リソースを使い、内部のリソースとして`app`リソースをつかったりと役割を変えて使うことができます。

動作を確認してみましょう。

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

現状では`application/hal+json`形式で出力されていますが、これをHTML（text/html）形式で出力できるようにします。
まず、HTML出力用のモジュールをインストールします。

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

テンプレートファイルを配置するディレクトリを作成します。

```bash
cp -r vendor/madapaja/twig-module/var/templates var
```

`bin/page.php`の出力形式をhtmlにするためにコンテキストを`html-app`に変更します。

```php
<?php
use MyVendor\Weekday\Bootstrap;

require dirname(__DIR__) . '/autoload.php';
exit((new Bootstrap())(PHP_SAPI === 'cli' ? 'cli-html-app' : 'html-app', $GLOBALS, $_SERVER));
```

最後に、表示用のテンプレート`var/templates/Page/Index.html.twig`を作成します。

```twig
{% raw %}{% extends 'layout/base.html.twig' %}
{% block title %}Weekday{% endblock %}
{% block content %}
The weekday of {{ year }}/{{ month }}/{{ day }} is {{ weekday }}.
{% endblock %}{% endraw %}
```

これでHTML出力の準備が完了しました。コンソールで動作を確認してみましょう。

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

HTMLが表示されない場合は、テンプレートエンジンでエラーが発生している可能性があります。
ログファイル（`var/log/cli-html-app/last.logref.log`）でエラー内容を確認してください。

Webサービスとして利用できるよう、`public/index.php`も同様に変更します。

```php
<?php

use MyVendor\Weekday\Bootstrap;

require dirname(__DIR__) . '/autoload.php';
exit((new Bootstrap())(PHP_SAPI === 'cli-server' ? 'html-app' : 'prod-html-app', $GLOBALS, $_SERVER));
```

PHPの開発サーバーを起動し、Webブラウザでアクセスして確認してみましょう。

```bash
php -S 127.0.0.1:8080 public/index.php
```

[http://127.0.0.1:8080/?year=2001&month=1&day=1](http://127.0.0.1:8080/?year=2001&month=1&day=1)

## コンテキストについて

[コンテキスト](/manuals/1.0/ja/application.html#context)は、アプリケーションの実行環境（開発・テスト・本番など）や表現形式（html・JSON)を表し、複数指定することができます。

例えば：

```php
<?php
// 最小構成のJSONアプリケーション
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')('app'));
```

```php
<?php
// プロダクション用HALアプリケーション
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/bootstrap.php')('prod-hal-app'));
```

コンテキストに応じたインスタンス生成用のPHPコードが自動的に生成されます。
これらのコードは`var/tmp/{context}/di`フォルダに保存されます。通常は確認する必要はありませんが、
インスタンスがどのように生成されているか知りたい場合に参照できます。

## REST API

ここでは、SQLite3を使用したRESTfulなTodoアプリケーションリソースを作成します。
まず、コンソールで`var/db/todo.sqlite3`にデータベースを作成します。

```bash
mkdir var/db
sqlite3 var/db/todo.sqlite3

sqlite> create table todo(id integer primary key, todo, created_at);
sqlite> .exit
```

データベースアクセスには[AuraSql](https://github.com/ray-di/Ray.AuraSqlModule)、[Doctrine Dbal](https://github.com/ray-di/Ray.DbalModule)、[CakeDB](https://github.com/ray-di/Ray.CakeDbModule)などが利用可能です。ここでは`Ray.AuraSqlModule`を使用します。

```bash
composer require ray/aura-sql-module
```

`src/Module/AppModule::configure()`でモジュールをインストールし、`DateTimeImmutable`を束縛します。

```diff
<?php
+use Ray\AuraSqlModule\AuraSqlModule;
+use DateTimeImmutable;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        // ...
+        $this->bind(DateTimeImmutable::class);        
+        $this->install(new AuraSqlModule(sprintf('sqlite:%s/var/db/todo.sqlite3', $this->appMeta->appDir)));
        $this->install(new PackageModule());
    }
}
```

次に、Todoリソースを`src/Resource/App/Todos.php`に作成します。

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
    public function __construct(
        private readonly ExtendedPdoInterface $pdo,
        private readonly DateTimeImmutable $date,
    ) {
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
        $this->headers['Location'] = sprintf('/todos?id=%s', $this->pdo->lastInsertId());

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

このリソースクラスには、以下のような重要なアトリビュートが付与されています：

#### #[Cacheable]
クラスに付与された`#[Cacheable]`は、このリソースのGETメソッドがキャッシュ可能であることを示します。

#### #[Transactional]
`onPost`と`onPut`メソッドの`#[Transactional]`は、データベースアクセスをトランザクション管理下に置くことを示します。

#### #[ReturnCreatedResource]
`onPost`メソッドの`#[ReturnCreatedResource]`は、新規作成されたリソースを返すことを示します。
この時`Location`ヘッダーで示されたURIに対して自動的に`onGet`が呼び出され、その結果がレスポンスに含まれます。これにより、
`Location`ヘッダーの内容が正しいことが保証され、同時にキャッシュも作成されます。

### APIの動作確認

まず、キャッシュを有効にしたテスト用のブートストラップファイル`bin/test.php`を作成します。

```php
<?php

declare(strict_types=1);

use MyVendor\Weekday\Bootstrap;

require dirname(__DIR__) . '/autoload.php';
exit((new Bootstrap())('prod-cli-hal-api-app', $GLOBALS, $_SERVER));
```

#### POSTリクエスト

新しいTodoを作成してみましょう。
BEAR.Sundayでは、POSTパラメータもクエリパラメータとして渡します。

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

ステータスコード`201 Created`は、リソースが正常に作成されたことを示します。
また、`Location`ヘッダーには、新しく作成されたリソースのURIが含まれています。
([RFC7231 Section-6.3.2](https://tools.ietf.org/html/rfc7231#section-6.3.2) [日本語訳](https://triple-underscore.github.io/RFC7231-ja.html#section-6.3.2))

#### GETリクエスト

作成したリソースを取得してみましょう。

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

これでハイパーメディアAPIが完成しました。
APIサーバーを起動して、実際にHTTPリクエストを送信してみましょう。

```bash
php -S 127.0.0.1:8081 bin/app.php
```

`curl`コマンドでGETリクエストを送信：

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
    "todo": "shopping",
    "created": "2024-11-07 15:58:03",
    "_links": {
        "self": {
            "href": "/todos?id=1"
        }
    }
}
```

複数回リクエストを送信して、`Last-Modified`の日付が変わらないことを確認してください。
この時、`onGet`メソッドは実際には実行されていません（試しにメソッド内に`echo`文を追加して確認できます）。

`#[Cacheable]`アトリビュートを使用したキャッシュは、有効期限を明示的に設定しない限り時間経過では無効化されません。
キャッシュの再生成は、`onPut($id, $todo)`や`onDelete($id)`などでリソースが変更された時に行われます。

#### PUTリクエスト

既存のリソースを更新してみましょう。

```bash
curl -i http://127.0.0.1:8081/todos -X PUT -d "id=1&todo=think"
```

レスポンスは、コンテンツがないことを示す`204 No Content`となります。

```
HTTP/1.1 204 No Content
...
```

`Content-Type`ヘッダーでメディアタイプを指定することもできます。
JSONでリクエストを送信してみましょう。

```bash
curl -i http://127.0.0.1:8081/todos -X PUT -H 'Content-Type: application/json' -d '{"id": 1, "todo":"think" }'
```

再度GETリクエストを送信すると、`Etag`と`Last-Modified`が更新されていることが確認できます。

```bash
curl -i 'http://127.0.0.1:8081/todos?id=1'
```

`#[Cacheable]`アトリビュートにより、`Last-Modified`の日付は自動的に管理されます。
アプリケーション側でこれらを管理したり、データベースに専用のカラムを用意したりする必要はありません。

`#[Cacheable]`を使用すると、リソースの内容は書き込み用データベースとは別の「クエリーリポジトリ」で管理され、
`Etag`や`Last-Modified`ヘッダーが自動的に付与されます。

## Because Everything is A Resource

BEARでは全てがリソースです。

リソースの識別子URI、統一されたインターフェイス、ステートレスなアクセス、強力なキャッシュシステム、ハイパーリンク、レイヤードシステム、自己記述性。
BEAR.SundayアプリケーションのリソースはこれらのRESTの特徴を備えたものです。HTTPの標準に従い再利用性に優れています。

BEAR.Sundayは**DI**で依存を結び、AOPで横断的関心事を結び、RESTの力でアプリケーションの情報をリソースとして結ぶコネクティングレイヤーのフレームワークです。

---

[^1]:このプロジェクトのソースコードは各セクション毎に[bearsunday/Tutorial](https://github.com/bearsunday/tutorial1/commits/v3)にコミットされています。適宜参照してください。
[^2]:通常、**vendor**名には個人またはチーム（組織）の名前を使用します。GitHubのアカウント名やチーム名が適切です。**project**名にはアプリケーション名を指定します。
