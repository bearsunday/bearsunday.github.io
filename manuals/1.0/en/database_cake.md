---
layout: docs-en
title: CakeDB
category: Manual
permalink: /manuals/1.0/en/database_cake.html
---
# CakeDb

**CakeDb** is an ORM using the active record and data mapper pattern idea. It is the same as the one provided in CakePHP3.

Install `Ray.CakeDbModule` with composer.

```bash
composer require ray/cake-database-module ~1.0
```

Please refer to [Ray.CakeDbModule](https://github.com/ray-di/Ray.CakeDbModule) for installation and refer to [CakePHP3 Database Access & ORM](http://book.cakephp.org/3.0/en/orm.html) for the ORM usage.

Ray.CakeDbModule is provided by Jose ([@lorenzo](https://github.com/lorenzo)) who developed the ORM of CakePHP3.

## Connection settings

Use the [phpdotenv](https://github.com/vlucas/phpdotenv) library etc. to set the connection according to the environment destination. Please see the [Ex.Package](https://github.com/BEARSunday/Ex.Package) for implementation.
