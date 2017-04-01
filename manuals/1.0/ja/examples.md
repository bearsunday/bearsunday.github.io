---
layout: docs-ja
title: Examples
category: Manual
permalink: /manuals/1.0/ja/examples.html
---

# Examples

[Coding Guide](http://bearsunday.github.io/manuals/1.0/en/coding-guide.html)に従って作られたアプリケーションの例です。

## [Todos](https://github.com/koriym/Polidog.Todo)

基本的なCRUDのアプリケーションです。`var/sql`ディレクトリのSQLファイルでDBアクセスをしています。ハイパーリンクを使ったREST APIとテスト、それにフォームのバリデーションテストも含まれます。

  * [ray/aura-sql-module](https://github.com/ray-di/Ray.AuraSqlModule) - Extended PDO ([Aura.Sql](https://github.com/auraphp/Aura.Sql))
  * [ray/web-form-module](https://github.com/ray-di/Ray.WebFormModule) - Web form ([Aura.Input](https://github.com/auraphp/Aura.Input))
  * [madapaja/twig-module](https://github.com/madapaja/Madapaja.TwigModule) - Twig template engine
  * [koriym/now](https://github.com/koriym/Koriym.Now) - Current datetime
  * [koriym/query-locator](https://github.com/koriym/Koriym.QueryLocator) - SQL locator
  * [koriym/http-constants](https://github.com/koriym/Koriym.HttpConstants) - Contains the values HTTP

```
git clone https://github.com/koriym/Polidog.Todo.git
cd Polidog.Todo
cp .env.dist .env
composer install
composer setup
composer test
composer serve
```
