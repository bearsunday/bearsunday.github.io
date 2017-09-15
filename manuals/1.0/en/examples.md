---
layout: docs-en
title: Examples
category: Manual
permalink: /manuals/1.0/en/examples.html
---

# Examples

This example application is built on the principles described in the [Coding Guide](http://bearsunday.github.io/manuals/1.0/en/coding-guide.html).

## [Todos](https://github.com/koriym/Polidog.Todo)

`Todos` is a basic CRUD application. The DB is accessed using the static　SQL file in the `var/sql` directory. Includes REST API using hyperlinks and testing, as well as form validation tests.

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
