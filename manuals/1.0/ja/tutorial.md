---
layout: docs-ja
title: チュートリアル
category: Manual
permalink: /manuals/1.0/ja/tutorial.html
---

## チュートリアル

年月日を入力すると曜日を返すWebサービスを作成してみましょう。
まずプロジェクトを作成します

{% highlight bash %}
composer create-project bear/skeleton MyVendor.Weekday ~1.0@dev
...
cd MyVendor.Weekday
composer install
...
{% endhighlight %}

最初にインストールされるアプリケーションリソースファイルを`src/Resource/App/Weekday.php`に作成します。

{% highlight php %}
<?php

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;

class Weekday extends ResourceObject
{
    public function onGet($year, $month, $day)
    {
        $date = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        $this['weekday'] = $date->format("D");

        return $this;
    }
}
{% endhighlight %}

この`MyVendor\Weekday\Resource\App\Weekday`リソースクラスは`/weekday`というパスでアクセスすることができます。
`GET`メソッドのクエリーが`onGet`メソッドの引数に渡されます。

コンソールでアクセスしてみましょう。まずはエラーを試してみます。

{% highlight bash %}
php bootstrap/api.php get '/weekday'

400 Bad Request
Content-Type: application/vnd.error+json

{"message":"Bad Request"}
...
{% endhighlight %}

400はリクエストに問題があるエラーコードです。
次は引数をつけて正しいリクエストします。

{% highlight bash %}
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
{% endhighlight %}

`application/hal+json`というメディアタイプで結果が正しく返って来ました。

これをWeb APIサービスにしてみましょう。
Built-inサーバーを立ち上げます。

{% highlight bash %}
php -S 127.0.0.1:8080 bootstrap/api.php
{% endhighlight %}

RESTクライアント（Chromeアプリの [Advanced REST client](https://chrome.google.com/webstore/detail/advanced-rest-client/hgmloofddffdnphfgcellkdfbfbjeloo/) など）で
`http://127.0.0.1:8080/weekday?year=2001&month=1&day=1` にGETリクエストを送って確かめてみましょう。

このリソースクラスにはGET以外のメソッドは用意されていないので他のメソッドを試すと`405 Method Not Allowed`が返されます。これも試してみましょう。

## ルーティング

デフォルトのルーターはURLをディレクトリにマップする`WebRouter`です。
これを動的なパラメーターをパスで受け取るためにAuraルーターを使用します。

`src/Modules/AppModule.php`で`AuraRouterModule`を上書き(override)インストールします。

{% highlight php %}

use BEAR\Package\Provide\Router\AuraRouterModule; // この行を追加

class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new PackageModule(new AppMeta('MyVendor\Weekday')));
        $this->override(new AuraRouterModule()); // この行を追加
    }
}
{% endhighlight %}

ルータースクリプトファイルを`var/conf/aura.route.php`に設置します。

{% highlight php %}
<?php

/** @var $router \Aura\Router\RouteCollection */

$router->add('/weekday', '/weekday/{year}/{month}/{day}')->addValues(['path' => '/weekday']);
{% endhighlight %}

試してみましょう。

{% highlight bash %}
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
{% endhighlight %}

## DI

[monolog](https://github.com/Seldaek/monolog) を使って結果をログする機能を追加してみましょう。
[composer](http://getcomposer.org)で取得します。

{% highlight bash %}
composer require monolog/monolog "~1.0"
{% endhighlight %}

monologログオブジェクトは`new`で直接作成しないで、作成されたログオブジェクトを受け取るようにします。
このように必要なもの（依存）を自らが取得するのではなく、外部からの代入する仕組みを [DI](http://ja.wikipedia.org/wiki/%E4%BE%9D%E5%AD%98%E6%80%A7%E3%81%AE%E6%B3%A8%E5%85%A5) といいます。

依存を提供する`MonologLoggerProvider`を`src/Module/MonologLoggerProvider.php`に作成します。

{% highlight php %}
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
{% endhighlight %}

ログをファイル記録するためにログフォルダのパスの情報が必要ですが、コンストラクタでアプリケーションのメタ情報を受け取っています。
依存は`get`メソッドで提供します。

次に[ロガーインターフェイス](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)とこの依存を生成するファクトリークラスを結びつけます。
`src/Modules/AppModule.php`の`configure`メソッドに以下を追加します。

{% highlight php %}
<?php
$this->bind(LoggerInterface::class)->toProvider(MonologLoggerProvider::class)->in(Scope::SINGLETON);
{% endhighlight %}
classキーワードでクラス名を解決するために以下のuse文も必要です。
{% highlight php %}
<?php
use Psr\Log\LoggerInterface;
use Ray\Di\Scope;
{% endhighlight %}

どのクラスでもコンストラクタでmonologオブジェクトを受け取ることができるようになりました。
`src/Resource/App/Weekday.php`を修正してlogを書きだしてみます。

{% highlight php %}
<?php

namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use Psr\Log\LoggerInterface;

class Weekday extends ResourceObject
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onGet($year, $month, $day)
    {
        $date = \DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        $this['weekday'] = $date->format("D");
        $this->logger->info("$year-$month-$day {$this['weekday']}");

        return $this;
    }
}

{% endhighlight %}

実行して`var/log/weekday.log`に結果が出力されていることを確認しましょう。

## AOP

メソッドの実行時間を計測するためのベンチマーク処理を考えてみます。

{% highlight php %}
<?php
$start = microtime(true);
// メソッド実行
$time = microtime(true) - $start;
{% endhighlight %}

ベンチマークを行う度にこのコードを付加して、不要になれば取り除くのは大変です。
**アスペクト志向プログラミング(AOP)**はこのようなメソッドの前後の特定処理をうまく合成することが出来ます。

まずAOPを実現するためにメソッドの実行を横取り（インターセプト）してベンチマークを行う**インターセプター**を`src/Interceptor/BenchMarker.php`に作成します。

{% highlight php %}
<?php

namespace MyVendor\Weekday\Interceptor;

use Psr\Log\LoggerInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class BenchMarker implements MethodInterceptor
{
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
        $msg = sprintf("%s: %s", $invocation->getMethod()->getName(),$time);
        $this->logger->info($msg);

        return $result;
    }
}

{% endhighlight %}

元のメソッドを横取りしたインターセプターの`Invoke`メソッドでは、元メソッドの実行を`$invocation->proceed();`で行うことができます。
その前後にタイマーのリセット、計測記録の処理を行います。（メソッド実行オブジェクト[MethodInvocation](http://www.bear-project.net/Ray.Aop/build/apigen/class-Ray.Aop.MethodInvocation.html) `$invocation`から元メソッドのオブジェクトとメソッドの名前を取得しています。）

次にベンチマークをしたいメソッドに目印をつけるための[アノテーション](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html)を`src/Annotation/BenchMark.php `に作成します。

{% highlight php %}
<?php

namespace MyVendor\Weekday\Annotation;

/**
 * @Annotation
 */
final class BenchMark
{
}
{% endhighlight %}

`AppModule`では`インターセプターを適用するメソッドを**Matcher**を使って束縛（バインド）します。

{% highlight php %}
<?php
use MyVendor\Weekday\Annotation\BenchMark;
use MyVendor\Weekday\Interceptor\BenchMarker;

// configure()に追加します。
$this->bindInterceptor(
    $this->matcher->any(),                           // どのクラスでも
    $this->matcher->annotatedWith(BenchMark::class), // @BenchMarkとアノテートされてるメソッドに
    [BenchMarker::class]                             // BenchMarkerInterceptorを適用
);
{% endhighlight %}

ベンチマークを行いたいメソッドに`@BenchMark`とアノテートします。

{% highlight php %}
<?php
use MyVendor\Weekday\Annotation\BenchMark;

/**
 * @BenchMark
 */
public function onGet($year, $month, $day)
{
{% endhighlight %}

これで計測したいメソッドに`@BenchMark`とアノテートすればいつでもベンチマークできるようになりました。

対象メソッドや、メソッドを呼ぶ側に変更はありません。アノテーションはそのままでも束縛を外せばベンチマークを行いません。
`production`では外したり、開発時に特定の秒数を越すと警告を行うことができます。

実行して`var/log/weekday.log`に実行時間のログが出力されることを確認しましょう。

## HTML

次に今のAPIアプリケーションをHTMLアプリケーションにしてみましょう。
今の`app`リソースに加えて、`src/Resource/Page/Index.php`に`page`リソースを追加します。

`page`リソースクラスは場所と役割が違うだけで`app`リソースと基本的に同じクラスです。

{% highlight php %}
<?php

namespace MyVendor\Weekday\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet($year, $month, $day)
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
{% endhighlight %}

`use ResourceInject;`でリソースリクエストを行うためのクライアントを受け取ったページリソースクラスは`$this->resource`リソースクライントを使って他のリソースにアクセスすることができます。
`onGet`メソッドで`app://self/weekday`リソースのリクエストを自身の`weekday`というスロットに埋め込んでいます。

この段階でこのページリソースがどのようなリソース表現をするのか試してみましょう。

{% highlight bash %}
php bootstrap/web.php get '/?year=1991&month=8&day=1'

200 OK
Content-Type: application/hal+json

{
    "_embedded": {
        "weekday": {
            "weekday": "Thu",
            "_links": {
                "self": {
                    "href": "/weekday/1991/8/1"
                }
            }
        }
    },
    "_links": {
        "self": {
            "href": "/?year=1991&month=8&day=1"
        }
    }
}
{% endhighlight %}

他のリソースが`_embedded`されているのが確認できます。
リソースのレンダラーに変更がないので`application/hal+json`メディアタイプで出力されていますが、これをHTML(text/html)で出力するために[HTMLのマニュアル](/manuals/1.0/ja/html.html)に従ってHTMLモジュールをインストールします。

composerインストール
{% highlight bash %}
composer require madapaja/twig-module
{% endhighlight %}

`src/Module/HtmlModule.php`を作成
{% highlight php %}
<?php

namespace MyVendor\Weekday\Module;

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
{% endhighlight %}

`bootstrap/web.php`を変更
{% highlight php %}
<?php

$context = 'cli-html-app';
require __DIR__ . '/bootstrap.php';
{% endhighlight %}

これで`text/html`メディア出力の準備はできました。最後に`src/Resource/Page/Index.html.twig`にtwigテンプレートを用意します。

{% highlight bash %}
<!DOCTYPE html>
<html>
<body>
{% raw %}The weekday of {{ year }}/{{ month }}/{{ day }} is {{ weekday.weekday }}.{% endraw %}
</body> 
</html>
{% endhighlight %}

準備完了です。まずはコンソールでこのようなHTMLが出力されるか確認しょうまう。

{% highlight bash %}

php bootstrap/web.php get '/?year=1991&month=8&day=1'
200 OK
content-type: text/html; charset=utf-8

<!DOCTYPE html>
<html>
<body>
The weekday of 1991/8/1 is <b>Thu</b>.
</body>
</html>
{% endhighlight %}

Webサービスを行うために`var/www/index.php`を変更します。

{% highlight php %}
<?php

$context = 'prod-html-app';
require dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';
{% endhighlight %}

PHPサーバーを立ち上げてwebブラウザで`http://127.0.0.1:8080/?year=2001&month=1&day=1`をアクセスして確認してみましょう。

{% highlight bash %}
php -S 127.0.0.1:8080 var/www/index.php 
{% endhighlight %}
