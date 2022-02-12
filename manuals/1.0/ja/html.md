---
layout: docs-ja
title: HTML
category: Manual
permalink: /manuals/1.0/ja/html.html
---

# HTML

HTML表現のために以下のテンプレートエンジンが利用可能です。

* [Twig v1](html-twig-v1.html)
* [Twig v2](html-twig-v2.html)
* [Qiq](html-qiq.html)

## Twig vs Qiq

[Twig](https://twig.symfony.com)は最初のリリースが2009年にされ多くのユーザーがいます。[Qiq](https://qiqphp-ja.github.io)は2021年にリリースされた新しいテンプレートエンジンです。

Twigが暗黙的エスケープをデフォルトにし制御構造などをTwig独自構文にしています。それに対して、Qiqは明示的なエスケープを要求し、PHP構文が基本のテンプレートです。 Twigのコードベースは大きく機能も豊富ですがそれに対してQiqはコンパクトでシンプルです。

### 構文比較

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

## レンダラー

`RenderInetrface`にバインドされResourceObjectにインジェクトされるレンダラーがリソースの表現を生成します。リソース自身はその表現に関して無関心です。

リソース単位でインジェクトすることができるので、複数のテンプレートエンジンを同時に使うこともできます。
