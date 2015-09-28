---
layout: docs-ja
title: HTML
category: Manual
permalink: /manuals/1.0/ja/html.html
---

# HTML
## Twig

HTML表示のためにcomposerで`madapaja/twig-module`をインストールします。

{% highlight bash %}
composer require madapaja/twig-module
{% endhighlight %}

次に`html`コンテキストファイル`src/Module/HtmlModule.php`を用意して`TwigModule`をインストールします。

{% highlight php %}
<?php

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
{% endhighlight %}

`bootstrap/web.php`のコンテキストを変更して`html`を有効にします。

{% highlight bash %}
$context = 'cli-html-app';
{% endhighlight %}
リソースのPHPファイル名に`.html.twig`拡張子をつけたファイルでテンプレートを用意します。
`Page/Index.php`に対応するのは`Page/Index.html.twig`になります。

{% highlight html %}
{% raw %}
<h1>{{ greeting }}</h1>
{% endraw %}
{% endhighlight %}

`$body`がテンプレートにアサインされて出力されます。

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
{% endhighlight %}

## 他のテンプレートエンジン

テンプレートエンジンは選択できるだけでなく、複数のテンプレートエンジンをリソース単位で選択することもできます。
