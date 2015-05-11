---
layout: docs-en
title: Database
category: Manual
permalink: /manuals/1.0/en/database.html
---

 * *[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/database.md) needs to be proofread by an English speaker. If interested please send me a pull request. Thank you.*

`Aura.Sql`、`Doctrine DBAL`, `CakeDB` module are provided for database.

# Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql) is `PDO` extended Aura database library.

### Install

Install `Ray.AuraSqlModule` via composer.

{% highlight bash %}
composer require ray/aura-sql-module
{% endhighlight %}

Install `AuraSqlModule` in application module`src/Module/AppModule.php`.

{% highlight php %}
<?php
use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\AuraSqlModule\AuraSqlModule; // add this line
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new PackageModule));
        $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', 'username', 'password');   // add this line
    }
}
{% endhighlight %}

Now DI bindings are ready. The db object will be injected via constructor or `AuraSqlInject` setter trait.

{% highlight php %}
<?php

use Aura\Sql\ExtendedPdoInterface;

class Index
{
    public function __construct(ExtendedPdoInterface $pdo)
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
{% endhighlight %}


{% highlight php %}
<?php
use Ray\AuraSqlModule\AuraSqlInject;

class Index
{
    use AuraSqlInject; 
 
    public function onGet()
    {
        return $this->pdo; // \Aura\Sql\ExtendedPdo
    }
}
{% endhighlight %}

`Ray.AuraSqlModule` contains [Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery) in order to help to build the sql.


## Replication connect

Install `AuraSqlReplicationModule` by `$locator` for master/slave connection.

{% highlight php %}
<?php
use Ray\Di\AbstractModule;
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\AuraSqlModule\Annotation\AuraSqlConfig;
use Aura\Sql\ConnectionLocator;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $locator = new ConnectionLocator;
        $locator->setWrite('master', new Connection('mysql:host=localhost;dbname=master', 'id', 'pass'));
        $locator->setRead('slave1',  new Connection('mysql:host=localhost;dbname=slave1', 'id', 'pass'));
        $locator->setRead('slave2',  new Connection('mysql:host=localhost;dbname=slave2', 'id', 'pass'));
        $this->install(new AuraSqlReplicationModule($locator));
    }
}

{% endhighlight %}

You will have a slave db connection in HTTP GET, or master db connection in other HTTP methods.

{% highlight php %}
<?php

use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;
use PDO;

class User extends ResourceObject
{
    public $pdo;

    public function __construct(ExtendedPdoInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    public function onGet()
    {
         $this->pdo; // slave db
    }

    public function onPost($todo)
    {
         $this->pdo; // master db
    }
}
{% endhighlight %}

`$this->pdo` is overwritten if the method is annotated with`@ReadOnlyConnection` or`@WriteConnection`. The master / slave db connection is correspond to the annotation.

{% highlight php %}
<?php
use Ray\AuraSqlModule\Annotation\ReadOnlyConnection;  // important
use Ray\AuraSqlModule\Annotation\WriteConnection;     // important

class User
{
    public $pdo; // override when @ReadOnlyConnection or @WriteConnection annotated method called
    
    public function onPost($todo)
    {
         $this->read();
    }

    /**
     * @ReadOnlyConnection
     */
    public function read()
    {
         $this->pdo; // slave db
    }

    /**
     * @WriteConnection
     */
    public function write()
    {
         $this->pdo; // master db
    }
}
{% endhighlight %}

## Transaction

`@Transactional` annotated method apply transaction.

{% highlight php %}
<?php
use Ray\AuraSqlModule\Annotation\Transactional;

// ....
    /**
     * @Transactional
     */
    public function write()
    {
         // \Ray\AuraSqlModule\Exception\RollbackException thrown if it failed.
    }
{% endhighlight %}

# Doctrine DBAL

[Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html) is also abstraction layer for database.

Install `Ray.DbalModule` via composer.

{% highlight bash %}
composer require ray/dbal-module
{% endhighlight %}

Install `DbalModule` in application module.

{% highlight php %}
<?php
use Ray\DbalModule\DbalModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new DbalModule('driver=pdo_sqlite&memory=true');
    }
}
{% endhighlight %}

DI bindings are ready. `$this->db` will be injected with `DbalInject` trait.

{% highlight php %}
<?php
use Ray\DbalModule\DbalInject;

class Index
{
    use DbalInject;
 
    public function onGet()
    {
        return $this->db; // \Doctrine\DBAL\Driver\Connection
    }
}
{% endhighlight %}

[MasterSlaveConnection](http://www.doctrine-project.org/api/dbal/2.0/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html) is provided for master/slave connection.

# CakeDb

**CakeDb** is the database access module for CakePHP3. The module is provided by [@lorenzo](https://github.com/lorenzo) (an original author of CakeDb).

Install `Ray.CakeDbModule` via composer.

{% highlight bash %}
composer require ray/cake-database-module. 

{% endhighlight %}

See more detail at [Ray.CakeDbModule](https://github.com/ray-di/Ray.CakeDbModule) and [CakePHP3 Database Access & ORM](http://book.cakephp.org/3.0/en/orm.html).



