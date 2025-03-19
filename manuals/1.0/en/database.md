---
layout: docs-en
title: Database
category: Manual
permalink: /manuals/1.0/en/database.html
---
# Database

The following modules are available for database use, with different problem solving methods. They are all independent libraries for SQL based on [PDO](https://www.php.net/manual/ja/intro.pdo.php).

* ExtendedPdo with PDO extended ([Aura.sql](https://github.com/auraphp/Aura.Sql))
* Query Builder ([Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery))
* Binding PHP interface and SQL execution ([Ray.MediaQuery](database_media.html))

Having static SQL in a file[^locator] makes it easier to use and tune with other SQL tools. SqlQuery can dynamically assemble queries, but the rest of the library is for basic static SQL execution. Ray.MediaQuery can also replace parts of the SQL with those assembled by the builder.

[^locator]: [query-locater](https://github.com/koriym/Koriym.QueryLocator) is a library for handling SQL as files, which is useful with Aura.Sql.

## Module

Modules are provided for using the database. They are all independent libraries for SQL.

* [Ray.AuraSqlModule](database_aura.html)
* [Ray.MediaQuery](database_media.html)

`Ray.AuraSqlModule` is a PDO extension [Aura.Sql](https://github.com/auraphp/Aura.Sql) and a query builder [Aura.SqlQuery](https://github.com/auraphp/) SqlQuery, plus a low-level module that provides pagination functionality.
`Ray.MediaQuery` is a high-performance DB access framework that generates and injects SQL execution objects from user-provided interfaces and SQL [^doma] .

[^doma]: The mechanism is similar to Java's DB access framework [Doma](https://doma.readthedocs.io/en/latest/basic/#examples).

## Other

* [DBAL](database_dbal.html)
* [CakeDb](database_cake.html)
* [Ray.QueryModule](https://github.com/ray-di/Ray.QueryModule/blob/1.x/README.md)

`DBAL` is Doctrine and `CakeDB` is CakePHP's DB library. `Ray.QueryModule` is an earlier library of Ray.MediaQuery that converts SQL to anonymous functions.

----
