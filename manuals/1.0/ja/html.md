---
layout: docs-ja
title: HTML
category: Manual
permalink: /manuals/1.0/ja/html.html
---

# Twig

HTML表示のためにcomposerで`madapaja/twig-module`をインストールします。

{% highlight bash %}
composer require madapaja/twig-module
{% endhighlight %}

次にアプリケーションモジュール`src/Module/AppModule.php`で`TwigModule`をインストールします。

{% highlight php %}
<?php

use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Madapaja\TwigModule\TwigModule; // この行を追加
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new PackageModule(new AppMeta('MyVendor\MyPackage')));
        $this->override(new TwigModule); // この行を追加
    }
}
{% endhighlight %}

`TwigModule`をインストールすることでデフォルトのレンダラーが`Json`から`Twig`に変更されました。
`bootstrap/web.php`でコンテクストを変更します。

{% highlight bash %}
$context = 'cli-app';
{% endhighlight %}
Twigがデフォルトのレンダラーになったので`app`でHTML出力されます。
リソースのphpファイルに`.html.twig`拡張子をつけたファイルでテンプレートを用意します。
`Page/Index.php`に対応するのは`Page/Index.html.twig`になります。

{% highlight bash %}
php bootstrap/web.php get /
200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
{% endhighlight %}

レイアウトや部分的なテンプレートファイルは`var/lib/twig`に設置します。

## カスタム設定

コンテンキストに応じてオプション等を設定したり、テンプレートのパスを追加したりする場合は
`@TwigPaths`と`@TwigOptions`に設定値を束縛します。

{% highlight php %}
<?php

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

        // You can add twig template paths by the following
        $appDir = dirname(dirname(__DIR__));
        $paths = [
            $appDir . '/src/Resource',
            $appDir . '/var/lib/twig'
        ];
        $this->bind()->annotatedWith(TwigPaths::class)->toInstance($paths);

        // Also you can set environment options
        // @see http://twig.sensiolabs.org/doc/api.html#environment-options
        $options = [
            'debug' => false,
            'cache' => $appDir . '/tmp'
        ];
        $this->bind()->annotatedWith(TwigOptions::class)->toInstance($options);
    }
}
{% endhighlight %}

## 他のテンプレートエンジン

テンプレートエンジンは選択できるだけでなく複数のテンプレートエンジンをリソース単位で選択することもできます。
現在は`Twig`のみがサポートされてます。

