---
layout: docs-ja
title: HTML (v2)
category: Manual
permalink: /manuals/1.0/ja/html-v2.html
---

(これはHTML v2のドキュメントです。以前のTwig v1を使用する[HTML v1](html)も利用可能です。)

# HTML

## インストール

HTML表示のためにcomposerで[Twig v2](https://twig.symfony.com/doc/2.x/)のモジュールをインストールします。

```bash
composer require madapaja/twig-module ^2.0
```

次に`html`コンテキストファイル`src/Module/HtmlModule.php`を用意して`TwigModule`をインストールします。

```php?start_inline
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

`TwigErrorPageModule`はエラー表示をHTMLで行うオプションです。`HtmlModule`でインストールしないで`ProdModule`でインストールして開発時のエラー表示はJSONにすることも出来ます。

次に`templates`フォルダをコピーします

```bash
cp -r vendor/madapaja/twig-module/var/templates var/templates
```

`bin/page.php`や`public/index.php`のコンテキストを変更して`html`を有効にします。

```bash
$context = 'cli-html-app'; // 'htm-app'
```
## テンプレート

1つのリソースクラスに１つのテンプレートファイルが`var/templates`フォルダに必要です。
例えば`src/Page/Index.php`には`var/templates/Page/Index.html.twig` が必要です。

テンプレートにリソースの **body**がアサインされます。

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

`src/Page/Index.twig.php` または `var/templates/Page/Index.twig.php`

```twig
{% raw %}<h1>{{ greeting }}</h1>{% endraw %}
```

出力

```bash
php bin/page.php get /
```
```bash
200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
```

## テンプレートファイルの選択

どのテンプレートを使用するかはリソースでは選択しません。リソースの状態によって`include`します。

```twig{% raw %}
{% if user.is_login %}
    {{ include('member.html.twig') }}
{% else %}
    {{ include('guest.html.twig') }}
{% endif %}{% endraw %}
```

リソースクラスはリソース状態だけに関心を持ち、テンプレートだけがリソース表現に関心を持ちます。
このような設計原則を[関心の分離(SoC)](https://ja.wikipedia.org/wiki/%E9%96%A2%E5%BF%83%E3%81%AE%E5%88%86%E9%9B%A2)といいます。

## エラーページ

`var/templates/error.html.twig`を編集します。エラーページには以下の値がアサインされています。

| 変数 | 意味 | キー |
|---|---|---|---|
| status | HTTP ステータス | code, message |
| e | 例外 | code, message, class |
| logref | ログID | n/a |

例

```twig
{% raw %}{% extends 'layout/base.html.twig' %}
{% block title %}{{ status.code }} {{ status.message }}{% endblock %}
{% block content %}
    <h1>{{ status.code }} {{ status.message }}</h1>
    {% if status.code == 404 %}
        <p>The requested URL was not found on this server.</p>
    {% else %}
        <p>The server is temporarily unable to service your request.</p>
        <p>refference number: {{ logref }}</p>
    {% endif %}
{% endblock %}{% endraw %}
```

## リソースのアサイン

リソースクラスのプロパティを参照するにはリソース全体がアサインされる`_ro`を参照します。

例）

`Todos.php`

```php
class Todos extend ResourceObject
{
    public $code = 200;

    public $text = [
        'name' => 'BEAR';
    ];    

    public $body = [
        ['title' => 'run']
    ];
}
```

`Todos.html.twig`

```twig
{% raw %}{{ _ro.code }} // 200
{{ _ro.text.name }} // 'BEAR'
{% for todo in _ro.body %}
  {{ todo.title }} // 'run'
{% endfor %}{% endraw %}
```

## ビューの階層構造

リソースクラス単位でビューを持つ事ができます。構造を良く表し、キャッシュもリソース単位で行われるので効率的です。

例）`app://self/todos`を読み込む`page://self/index`

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

### page://self/index

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
