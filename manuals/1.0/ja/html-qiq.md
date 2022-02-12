---
layout: docs-ja
title: HTML (Qiq)
category: Manual
permalink: /manuals/1.0/ja/html-qiq.html
---

# HTML (Qiq)

## セットアップ

QiqでHTML表示をするためにcomposerで`bear/qiq-module`をインストールします。

```bash
composer require bear/qiq-module
```

次にテンプレートやヘルパーを格納するディレクトリを用意します。

```
cd /path/to/project
cp vendor/bear/qiq-module/var/qiq var
```

`html`コンテキストファイル`src/Module/HtmlModule.php`を用意して`QiqModule`をインストールします。

```php?start_inline
namespace MyVendor\MyPackage\Module;

use BEAR\AppMeta\AppMeta;
use BEAR\QiqModule\QiqModule;
use Ray\Di\AbstractModule;

class HtmlModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new QiqModule($this->appDir . '/var/qiq/template'));
    }
}
```

## コンテキスト変更

`bin/page.php`のコンテキストを変更して`html`を有効にします。

```bash
$context = 'cli-html-app';
```

## テンプレート

Indexリソースのテンプレートを`var/qiq/template/Index.php`に用意します。

```
{% raw %}<h1>{{ $this->greeting }}</h1>{% endraw %}
```

ResourceObjectの`$body`がテンプレートに`$this`としてアサインされます。

```bash
php bin/page.php get /
200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
```

## ヘルパー

[ヘルパー](https://qiqphp-ja.github.io/1.x/helpers/overview.html#1-8-1)は`Qiq\Helper\`のnamespaceで作成します。例: `Qiq\Helper\Foo`

composer.jsonを編集して(例: [composer.json](https://github.com/bearsunday/BEAR.QiqModule/blob/1.x/demo/composer.json#L26)) `Qiq\Helper`をクラスをオートロード可能にし`composr dump-autoload`を実行します。指定ディレクトリにヘルパークラスファイルを設置するとヘルパーが利用可能になります。

## ProdModule

プロダクション用にエラーページをHTMLにし、コンパイラキャッシュを有効にするためのモジュールをProdModuleでインストールします。

```php
class ProdModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new QiqErrorModule);
        $this->install(new QiqProdModule($this->appDir . '/var/tmp');
    }
}
```

