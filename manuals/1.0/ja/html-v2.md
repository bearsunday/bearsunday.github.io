---
layout: docs-ja
title: HTML (v2)
category: Manual
permalink: /manuals/1.0/ja/html-v2.html
---

(これは^2.0バージョンのドキュメントです。以前のTwig v1を使用する[twig-module ^1.0](html)も利用可能です。)

# HTML (v2)

## インストール

composerで[Twig v2](https://twig.symfony.com/doc/2.x/)のモジュール`madapaja/twig-module ^2.0`をインストールします。

```bash
composer require madapaja/twig-module ^2.0
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

`bootstrap/web.php`や`public/index.php`のコンテキストを変更して`html`を有効にします。

```bash
$context = 'cli-html-app';
```
## テンプレート

1つのリソースに１つのテンプレートファイルが対応します。リソースクラスファイルと同じ`src`か`templates`フォルダに設置します。
`src/Page/Index.php`に対応するのは`src/Resource/Page/Index.html.twig`か`templates/Page/Index.html.twig`になります。

テンプレートにアサインされるのはリソースの "**body**" とリソース全体 "**_ro**" です。

例）

`src/Page/Index.php`

```php
class Index extend ResourceObject
{
    public $body = [
        ['greeting' => 'Hello BEAR.Sunday']
    ];
}
```

`src/Page/Index.twig.php` または `templates/Page/Index.twig.php`

```twig
{% raw %}<h1>{{ greeting }}</h1>{% endraw %}
```

出力

```bash
php bootstrap/web.php get /

200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
```

## リソースのアサイン

リソースクラスのプロパティを参照するにはリソース全体がアサインされる`_ro`を使います。

例）

`Index.php`

```php
class Index extend ResourceObject
{
    public $text = [
        'name' => 'BEAR';
    ];
    
    public $code = 200;

    public $body = [
        ['title' => 'run']
    ];
}
```

`Index.hmtl.twig`

```twig
{% raw %}{{ _ro.code }} // 200
{{ _ro.text.name }} // 'BEAR'
{% for todo in _ro.body %}
  {{ todo.title }} // 'run'
{% endfor %}{% endraw %}
```

## ビューの階層構造

リソースクラス単位でビューを持つ事ができます。構造をよく表しキャッシュもリソース単位で行われるので効率的です。
`app://self/todos`を読み込む`page://self/index`のコードサンプルは以下のようになります。

### app://self/todos

```php
class Todos extends ResourceObject
{
    use AuraSqlInject;
    use QueryLocatorInject;

    public function onGet() : ResourceObject
    {
        $this->body = $this->pdo->fetchAll($this->query['todos_list'])
        return $this;
    }
}
```

```twig
{% raw %}{% for todo in _ro.body %}
  {{ todo.title }}</td>
{% endfor %}{% endraw %}
```

### page://self/todos

```php
class Index extends ResourceObject
{
    /**
     * @Embed(rel="todos", src="app://self/todos")
     */
    public function onGet() : ResourceObject
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
Twigを`addExtension()`メソッドで拡張する場合には、拡張を行うTwigのProviderクラスを用意し`Twig_Environment`クラスに`Provider`束縛します。


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
        // $twig is an original \Twig_Environment instance
        $this->twig = $twig;
    }

    public function get()
    {
        // Extending Twig
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
        $this->bind(\Twig_Environment::class)->toProvider(MyTwigProvider::class)->in(Scope::SINGLETON);
    }
}
```

## モバイル

モバイルサイト専用のテンプレートを使用するためには`MobileTwigModule`を加えてインストールします。

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

`index.html.twig`の代わりに`Index.mobile.twig`が **存在すれば**優先して使用されます。変更の必要なテンプレートだけを用意する事ができます。

## カスタム設定

コンテンキストに応じてオプション等を設定したり、テンプレートのパスを追加する場合は`@TwigPaths`と`@TwigOptions`に設定値を束縛します。

注）キャッシュを常に`var/tmp`フォルダに生成するので特にプロダクション用の設定などは特に必要ありません。

```php
namespace MyVendor\MyPackage\Module;

use Madapaja\TwigModule\Annotation\TwigDebug;
use Madapaja\TwigModule\Annotation\TwigOptions;
use Madapaja\TwigModule\Annotation\TwigPaths;
use Madapaja\TwigModule\TwigModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TwigModule);

        // テンプレートパスの指定
        $appDir = dirname(dirname(__DIR__));
        $paths = [
            $appDir . '/src/Resource',
            $appDir . '/var/templates'
        ];
        $this->bind()->annotatedWith(TwigPaths::class)->toInstance($paths);

        // オプション
        // @see http://twig.sensiolabs.org/doc/api.html#environment-options
        $options = [
            'debug' => false,
            'cache' => $appDir . '/tmp'
        ];
        $this->bind()->annotatedWith(TwigOptions::class)->toInstance($options);
        
        // debugオプションのみを指定する場合
        $this->bind()->annotatedWith(TwigDebug::class)->toInstance(true);
    }
}
```
