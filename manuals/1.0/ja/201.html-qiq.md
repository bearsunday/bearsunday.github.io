---
layout: docs-ja
title: HTML (Qiq)
category: Manual
permalink: /manuals/1.0/ja/html-qiq.html
---

# HTML (Qiq)

## セットアップ

QiqでHTML表示をするために、以下の手順で設定を行います：

1. composerで`bear/qiq-module`をインストールします：
```bash
composer require bear/qiq-module
```

2. テンプレートやヘルパーを格納するディレクトリを用意します：
```bash
cd /path/to/project
cp -r vendor/bear/qiq-module/var/qiq var/
```

3. `html`コンテキストファイル`src/Module/HtmlModule.php`を用意して`QiqModule`をインストールします：
```php
namespace MyVendor\MyPackage\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\QiqModule\QiqModule;

class HtmlModule extends AbstractAppModule
{
    protected function configure()
    {
        $this->install(
            new QiqModule($this->appMeta->appDir . '/var/qiq/template')
        );
    }
}
```

## コンテキスト変更

`bin/page.php`のコンテキストを変更して`html`を有効にします：

```bash
$context = 'cli-html-app';
```

## テンプレート

Indexリソースのテンプレートを`var/qiq/template/Page/Index.php`に用意します：

```
{% raw %}<h1>{{h $this->greeting }}</h1>{% endraw %}
```

ResourceObjectの`$body`がテンプレートに`$this`としてアサインされます：

```bash
php bin/page.php get /
200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
```

## カスタムヘルパー

[カスタムヘルパー](https://qiqphp-ja.github.io/1.x/helpers/custom.html#1-8-4)は`Qiq\Helper\`のnamespaceで作成します。

例: `Qiq\Helper\Foo`

composer.jsonの`autoload`に`Qiq\Helper`を指定し（例: [composer.json](https://github.com/bearsunday/BEAR.QiqModule/blob/1.x/demo/composer.json#L26)）、`composer dump-autoload`を実行してヘルパークラスをオートロード可能にします。指定ディレクトリに設置するとカスタムヘルパーが利用可能になります。

## ProdModule

プロダクション用にエラーページをHTMLにし、コンパイラキャッシュを有効にするためのモジュールをProdModuleでインストールします：

```php
class ProdModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new QiqErrorModule);
        $this->install(new QiqProdModule($this->appDir . '/var/tmp'));
    }
}
```
