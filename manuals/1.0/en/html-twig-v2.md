---
layout: docs-en
title: HTML (Twig v2)
category: Manual
permalink: /manuals/1.0/en/html-twig-v2.html
---

# HTML (Twig v2)

## Install

In order to have an HTML reprensentation, Let's install [Twig v2](https://twig.symfony.com/doc/2.x/) module with composer.

```bash
composer require madapaja/twig-module ^2.0
```

Next create the context file `src/Module/HtmlModule.php` and install the `TwigModule`.

```php?start_inline
namespace MyVendor\MyPackage\Module;

use BEAR\AppMeta\AppMeta;
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

Update the context in `bin/page.php` or `public/index.php` and enable `html`.

```bash
$context = 'cli-html-app'; // or 'html-app'
```
## Template

One template file is required for one resource object class in `var/templates` directory to represent in HTML.
For example, for `src/Page/Index.php` resource class, a template file is required in `var/templates/Page/Index.html.twig`.

The body of the resource is assigned to the template.

example）

`src/Page/Index.php`

```php
class Index extends ResourceObject
{
    public $body = [
        'greeting' => 'Hello BEAR.Sunday'
    ];
}
```

`var/templates/Page/Index.html.twig`

```twig
{% raw %}<h1>{{ greeting }}</h1>{% endraw %}
```

Output:

```bash
php bin/page.php get /
```

```bash
200 OK
content-type: text/html; charset=utf-8

<h1>Hello BEAR.Sunday</h1>
```
## Select template file

Resource does not select the template file. It `includes` depending on the state of the resource.

```twig{% raw %}
{% if user.is_login %}
    {{ include('member.html.twig') }}
{% else %}
    {{ include('guest.html.twig') }}
{% endif %}{% endraw %}
```

In the resource class, you should only concern resource state. Then template should concern the resource representation.
See [Separation of concerns (SoC)](https://en.wikipedia.org/wiki/Separation_of_concerns).

## Error Page

Edit `var/templates/error.html.twig`. Following values are assigned to the error page.

| Variable | Title | Key |
|---|---|---|---|
| status | HTTP status | code, message |
| e | Exception | code, message, class |
| logref | Log ID | n/a |

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


## Assign resource

To refer to the properties of the resource object class, Use `_ro` (resource object) to which the entire resource object is assigned

example）

`Todos.php`

```php
class Todos extend ResourceObject
{
    public $code = 200;

    public $text = [
        'name' => 'BEAR'
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

## Hierarchical view structure

You can have a view on a resource class basis. It represents the structure well. Also, the cache is also hierarchically done on a resource basis, so it is efficient.

example) `page://self/index` which embeds `app://self/todos`

### app://self/todos

```php
class Todos extends ResourceObject
{
    use AuraSqlInject;
    use QueryLocatorInject;

    public function onGet(): static
    {
        $this->body = $this->pdo->fetchAll($this->query['todos_list']);
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
use BEAR\Resource\Annotation\Embed;

class Index extends ResourceObject
{
    #[Embed(rel: 'todos', src: 'app://self/todos')]
    public function onGet(): static
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

## Extending Twig

When you extend Twig with the `addExtension()` method, prepare Twig's Provider class which performs extension and bind `Provider` to `Twig_Environment` class.

```php
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

class MyTwigProvider implements ProviderInterface
{
    private \Twig_Environment $twig;

    public function __construct(
        #[Named('original')] \Twig_Environment $twig
    ) {
        // $twig is an original \Twig_Environment instance
        $this->twig = $twig;
    }

    public function get(): \Twig_Environment
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

## Template for mobile device

To use the template for mobile devices, install `MobileTwigModule`.

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

If **there is** a mobile site template `Index.mobile.twig` that will replace `Index.html.twig`, it will be used in preference.

## Custom Settings

If you would like to change options depending on the context or add a template path, configuration values are bound to `@TwigPaths`and `@TwigOptions` annotations.

Note: Since caches are always created in the `var/tmp` folder, there is no particular need for special settings for production.

```php
namespace MyVendor\MyPackage\Module;

use Madapaja\TwigModule\Annotation\TwigDebug;
use Madapaja\TwigModule\Annotation\TwigOptions;
use Madapaja\TwigModule\Annotation\TwigPaths;
use Madapaja\TwigModule\TwigModule;
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new TwigModule);

        // You can add twig template paths by the following
        $appDir = $this->appMeta->appDir;
        $paths = [
            $appDir . '/src/Resource',
            $appDir . '/var/templates'
        ];
        $this->bind()->annotatedWith(TwigPaths::class)->toInstance($paths);

        // Also you can set environment options
        // @see http://twig.sensiolabs.org/doc/api.html#environment-options
        $options = [
            'debug' => false,
            'cache' => $appDir . '/tmp'
        ];
        $this->bind()->annotatedWith(TwigOptions::class)->toInstance($options);
        
        // Only for debug option
        $this->bind()->annotatedWith(TwigDebug::class)->toInstance(true);
    }
}
```
