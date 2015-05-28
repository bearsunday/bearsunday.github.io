---
layout: docs-ja
title: データベース
category: Manual
permalink: /manuals/1.0/ja/database.html
---

データベースライブラリの利用のため`Aura.Sql`、`Doctrine DBAL`, `CakeDB`などのモジュールが用意されています。

# Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPHPのPDOを拡張したデータベースライブラリです。

### インストール

composerで`Ray.AuraSqlModule`をインストールします。

{% highlight bash %}
composer require ray/aura-sql-module
{% endhighlight %}

アプリケーションモジュール`src/Module/AppModule.php`で`AuraSqlModule`をインストールします。

{% highlight php %}
<?php
use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\AuraSqlModule\AuraSqlModule; // この行を追加
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new PackageModule));
        $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', 'username', 'password');  // この行を追加
    }
}
{% endhighlight %}

これでDIの設定が整いました。コンストラクタや`AuraSqlInject`トレイトを利用してDBオブジェクトを受け取ります。

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

`Ray.AuraSqlModule`は[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)を含んでいてMySQLやPostgresなどのSQLを組み立てるのに利用できます。

## リプリケーションのための接続

マスター／スレーブの接続を自動で行うためには接続オブジェクト`$locator`を作成して`AuraSqlReplicationModule`をインストールします。

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

これでHTTPリクエストがGETの時がスレーブDB、その他のメソッドの時はマスターDBのDBオブジェクトがコンスタラクタに渡されます。

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

`@ReadOnlyConnection`、`@WriteConnection`でアノテートされたメソッドはメソッド名に関わらず、呼ばれた時にアノテーションに応じたDBオブジェクトが`$this->pdo`に上書きされます。

{% highlight php %}
<?php
use Ray\AuraSqlModule\Annotation\ReadOnlyConnection;  // important
use Ray\AuraSqlModule\Annotation\WriteConnection;     // important

class User
{
    public $pdo; // @ReadOnlyConnectionや@WriteConnectionのメソッドが呼ばれた時に上書きされる
    
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

## トランザクション

`@Transactional`とアノテートしたメソッドはトランザクション管理されます。

{% highlight php %}
<?php
use Ray\AuraSqlModule\Annotation\Transactional;

// ....
    /**
     * @Transactional
     */
    public function write()
    {
         // 例外発生したら\Ray\AuraSqlModule\Exception\RollbackExceptionに
    }
{% endhighlight %}

# Doctrine DBAL

[Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html)もデータベースの抽象化レイヤーです。

composerで`Ray.DbalModule`をインストールします。

{% highlight bash %}
composer require ray/dbal-module
{% endhighlight %}

アプリケーションモジュールで`DbalModule`をインストールします。

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

これでDIの設定が整いました。
`DbalInject`トレイトを利用すると`$this->db`にDBオブジェクトがインジェクトされます。

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

[MasterSlaveConnection](http://www.doctrine-project.org/api/dbal/2.0/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html)というリプリケーションのためのマスター／スレーブ接続が標準で用意されています。

## 環境による接続先の変更

[phpdotenv](https://github.com/vlucas/phpdotenv)ライブラリなどを利用して環境先に応じた接続先を設定します。実装例の[Ex.Package](https://github.com/BEARSunday/Ex.Package)をご覧ください。

# CakeDb

**CakeDb**はアクティブレコードとデータマッパーパターンのアイデアを使ったORMで、素早くシンプルにORMを使うことができます。CakePHP3で提供されているORMと同じものです。

composerで`Ray.CakeDbModule`をインストールします。

{% highlight bash %}
composer require ray/cake-database-module ~1.0
{% endhighlight %}

インストールの方法については[Ray.CakeDbModule](https://github.com/ray-di/Ray.CakeDbModule)をORMの利用には[CakePHP3 Database Access & ORM](http://book.cakephp.org/3.0/en/orm.html)をご覧ください。

Ray.CakeDbModuleはCakePHP3のORMを開発したJose([@lorenzo](https://github.com/lorenzo))さんにより提供されています。


