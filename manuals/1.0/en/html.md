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
* [Qiq](html-qiq.html) (WIP) 

## Twig vs Qiq

While [Twig](https://twig.symfony.com) defaults to implicit escaping and has a proprietary syntax for control structures, [Qiq](https://qiqphp-ja.github.io) is a native PHP template that requires explicit escaping and minimizes proprietary syntax. Qiq]() is a native PHP template that requires explicit escaping and minimizes proprietary syntax.

Twig was first released in 2009 and has a large user base. Qiq is a new template engine released in 2021. The Twig codebase is large and feature-rich, while Qiq is compact and simple.

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
{% raw %}{{ var }}
{{ var | helper }}
{% for user in users %}
  * {{ user.name }}
{% endfor %}{% endraw %}
```


Qiq

```
{% raw %}{{h $var }}または {{ $this->h($var }}
{{h helper($var) }}
{{ foreach($users => $user) }}
  * {{ $user->name }}
{{ endforeach }}{% endraw %}
```

## Renderer

A renderer bound to a `RenderInetrface` and injected into a ResourceObject generates a representation of the resource. The resource itself is indifferent about its representation.

It can be injected on a per-resource basis, so multiple template engines can be used simultaneously.
