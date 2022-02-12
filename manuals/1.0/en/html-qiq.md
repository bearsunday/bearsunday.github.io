---
layout: docs-en
title: HTML (Qiq)
category: Manual
permalink: /manuals/1.0/en/html-qiq.html
---

# HTML (Qiq)

## Setup

Install `bear/qiq-module` in composer to get HTML view in Qiq.

```bash
composer require bear/qiq-module
```

Next, Provide a directory to store templates and helpers.

```
cd /path/to/project
cp vendor/bear/qiq-module/var/qiq var
```

Provide the `html` context file `src/Module/HtmlModule.php` and install `QiqModule`.

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

## Change context

Change the context of `bin/page.php` to enable `html`.

```bash
$context = 'cli-html-app';
```

## Template 

Prepare the template for the Index resource in `var/qiq/template/Index.php`.

```
{% raw %}<h1>{{ $this->greeting }}</h1>{% endraw %}
```

The `$body` of the ResourceObject will be assigned to the template as `$this`.

```bash
php bin/page.php get /
200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
```

## Helper

[Helpers](https://qiqphp.com/1.x/helpers/overview.html#1-8-1) should be created in the `Qiq\Helper\` namespace. Example: `Qiq\Helper\Foo`.

Edit composer.json (e.g. [composer.json](https://github.com/bearsunday/BEAR.QiqModule/blob/1.x/demo/composer.json#L26))　to make the `Qiq\Helper` class autoloadable and run `composr dump-autoload`. Then place the helper class file in the specified directory and the helper will be available.

## ProdModule

Install a module in ProdModule to make the error page HTML for production and to enable compiler cache.

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

