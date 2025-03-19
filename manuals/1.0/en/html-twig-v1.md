---
layout: docs-en
title: HTML (Twig v1)
category: Manual
permalink: /manuals/1.0/en/html-twig-v1.html
---

# HTML (Twig v1)

In order to have an HTML reprensentation lets install `madapaja/twig-module` with composer.

```bash
composer require madapaja/twig-module ^1.0
```

Next create the context file `src/Module/HtmlModule.php` and install the `TwigModule`.

```php?start_inline
namespace MyVendor\MyPackage\Module;

use BEAR\AppMeta\AppMeta;
use Madapaja\TwigModule\TwigModule;
use BEAR\Package\AbstractAppModule;

class HtmlModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new TwigModule);
    }
}
```

Update the context in `bin/page.php` and enable `html`.

```bash
$context = 'cli-html-app';
```
We prepare twig templates by placing them in the same directory as the `page resource` that you want to bind it to. Replace the `.php` suffix with `.html.twig`. So a template for the `Page/Index.php` resource would be `Page/Index.html.twig`.

```hml
{% raw %}<h1>{{ greeting }}</h1>{% endraw %}
```

The `$body` in a resource is assigned to the template and then rendered.

```bash
php bin/page.php get /
200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
```

By default partials and template files are found in `var/lib/twig`.

## Custom Settings

If you would like to change options depending on the context or add a template path, configuration values are bound to `@TwigPaths`and `@TwigOptions` annotations.

```php?start_inline
namespace MyVendor\MyPackage\Module;

use Madapaja\TwigModule\Annotation\TwigOptions;
use Madapaja\TwigModule\Annotation\TwigPaths;
use Madapaja\TwigModule\TwigModule;
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
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
```

## Other template engines

You can not only select a template engine, but you can also provide multiple template engines and assign them to different resources.
