---
layout: docs-en
title: DBAL
category: Manual
permalink: /manuals/1.0/en/database_dbal.html
---
# Doctrine DBAL

[Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html) is also abstraction layer for database.

Install `Ray.DbalModule` with composer.

```bash
composer require ray/dbal-module
```

Install `DbalModule` in application module.

```php?start_inline
use BEAR\Package\AbstractAppModule;
use Ray\DbalModule\DbalModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new DbalModule('driver=pdo_sqlite&memory=true');
    }
}
```

New DI bindings are now ready and `$this->db` can be injected with the `DbalInject` trait.

```php?start_inline
use Ray\DbalModule\DbalInject;

class Index
{
    use DbalInject;

    public function onGet()
    {
        return $this->db; // \Doctrine\DBAL\Driver\Connection
    }
}
```

### Connect to multiple databases

To connect to multiple databases, specify the identifier as the second argument.

```php?start_inline
$this->install(new DbalModule($logDsn, 'log_db');
$this->install(new DbalModule($jobDsn, 'job_db');
```

```php?start_inline
/**
 * @Inject
 * @Named("log_db")
 */
public function setLogDb(Connection $logDb)
```

[MasterSlaveConnection](http://www.doctrine-project.org/api/dbal/2.0/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html) is provided for master/slave connections.
