---
layout: docs-ja
title: HTML
category: Manual
permalink: /manuals/1.0/ja/html.html
---

HTML表示のための**Ray.TwigModule**が用意されています。

# Twig

まずcomposerで`Madapaja.TwigModule`をインストールします。

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

`TwigModule`をインストールすることでデフォルトのレンダラーが`JsonからTwigに変更されました。
`bootstrap/web.php`を変更します。

{% highlight bash %}
$context = 'cli-app';
{% endhighlight %}
Twigがデオフォルトのレンダラーになったので`app`でHTML出力されます。

出力はこのようになります。

{% highlight bash %}
php bootstrap/web.php get /
200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
{% endhighlight %}
