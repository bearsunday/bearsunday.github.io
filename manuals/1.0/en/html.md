---
layout: docs-en
title: HTML
category: Manual
permalink: /manuals/1.0/en/html.html
---

# HTML

The following template engines are available for HTML representation.

* [Twig v1](html-twig-v1.html)
* [Twig v2](html-twig-v2.html)
* [Qiq](html-qiq.html)

## Twig vs Qiq

[Twig](https://twig.symfony.com) was first released in 2009 and has a large user base. [Qiq](https://qiqphp.github.io) is a new template engine released in 2021.

Twig uses implicit escaping by default and has custom syntax for control structures. In contrast, Qiq requires explicit escaping and uses PHP syntax as the base template language. Twig has a large codebase and rich features, while Qiq is compact and simple. (Using pure PHP syntax in Qiq makes it IDE and static analysis-friendly, although it may be redundant.)

### Syntax Comparison

PHP
```php
<?= htmlspecialchars($var, ENT_QUOTES|ENT_DISALLOWED, 'utf-8') ?>
<?= htmlspecialchars(helper($var, ENT_QUOTES|ENT_DISALLOWED, 'utf-8')) ?>
<?php foreach ($users => $user): ?>
 * <?= $user->name; ?>
<?php endforeach; ?>
```

Twig

```
{% raw %}{{ var | raw }}
{{ var }}
{{ var | helper }}
{% for user in users %}
  * {{ user.name }}
{% endfor %}{% endraw %}
```

Qiq

```
{% raw %}{{% var }}
{{h $var }}
{{h helper($var) }}
{{ foreach($users => $user) }}
  * {{h $user->name }}
{{ endforeach }}

{{ var }} // Not displayed {% endraw %}
```
```php
<?php /** @var Template $this */ ?>
<?= $this->h($var) ?>
```

## Renderer

The renderer, bound to `RenderInterface` and injected into the ResourceObject, generates the representation of the resource. The resource itself is agnostic about its representation.

Since the renderer is injected per resource, it is possible to use multiple template engines simultaneously.

## Halo UI for Development

During development, you can render a UI element called Halo [^halo] around the rendered resource. Halo provides information about the resource's state, representation, and applied interceptors. It also provides links to open the corresponding resource class or resource template in PHPStorm.

[^halo]: The name is derived from a similar feature in the [Seaside](https://github.com/seasidest/seaside) framework for Smalltalk.

<img src="https://user-images.githubusercontent.com/529021/211504531-37cd4a8d-80b3-4d77-903f-c8f5baf5dc37.png" alt="Halo displays resource state" width="50%">

<link href="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css" rel="stylesheet">

* <span class="glyphicon glyphicon-home" rel="tooltip" title="Home"></span> Halo Home (Border and Tools Display)
* <span class="glyphicon glyphicon-zoom-in" rel="tooltip" title="Status"></span> Resource State
* <span class="glyphicon glyphicon-font" rel="tooltip" title="View"></span> Resource Representation
* <span class="glyphicon glyphicon-info-sign" rel="tooltip" title="Info"></span> Profile

You can try a demo of Halo in the [demo](/docs/demo/halo/).

### Performance Monitoring

Halo also displays performance information about the resource, including execution time, memory usage, and a link to the profiler.

<img src="https://user-images.githubusercontent.com/529021/212373901-fce7b2fd-41b0-478f-9d36-5e2eb3b97d9c.png" alt="Halo displays performance"  width="50%">

### Installation

To enable profiling, you need to install [xhprof](https://www.php.net/manual/en/intro.xhprof.php), which helps identify performance bottlenecks.

```
pecl install xhprof
// Also add 'extension=xhprof.so' to your php.ini file
```

To visualize and graphically display call graphs, you need to install [graphviz](https://graphviz.org/download/).
Example: [Call Graph Demo](/docs/demo/halo/callgraph.svg)

```
// macOS
brew install graphviz

// Windows
// Download and install the installer from the graphviz website

// Linux (Ubuntu)
sudo apt-get install graphviz
```

In your application, create a Dev context module and install the `HaloModule`.

```php
class DevModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new HaloModule($this));
    }
}
```

---
