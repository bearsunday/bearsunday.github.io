---
layout: docs-ja
title: データベース
category: Manual
permalink: /manuals/1.0/ja/database.html
---

データベースライブラリの利用のための**Ray.Diモジュール**が用意されています。
composerのインストールとアプリケーションモジュールにインストールして準備します。

# Aura.Sql

[Aura.Sql](https://github.com/auraphp/Aura.Sql)はPHPのPDOを拡張したデータベースライブラリです。

composerで`Ray.AuraSqlModule`をインストールします。

{% highlight bash %}
composer require ray/aura-sql-module
{% endhighlight %}

アプリケーションモジュールで`AuraSqlModule`をインストールします。

{% highlight php %}
<?php
use Ray\Di\AbstractModule;
use Ray\AuraSqlModule\AuraSqlModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', 'username', 'password');
    }
}
{% endhighlight %}

これでDIの設定が整いました。コンストラクタや`AuraSqlInject`トレイトを利用してDBオブジェクトを受け取ります。

{% highlight php %}
<?php

use Aura\Sql\ExtendedPdoInterfaceに

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

マスター／スレーブの接続を自動で行うためには、
接続オブジェクト`$locator`を作成して`AuraSqlLocatorModule`をインストールします。

{% highlight php %}
<?php
use Ray\Di\AbstractModule;
use Ray\AuraSqlModule\AuraSqlModule;
use Aura\Sql\ConnectionLocator;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $locator = new ConnectionLocator;
        $locator->setWrite(
            'master',
            new Connection('mysql:host=localhost;dbname=master', 'id', 'password')
        );
        $locator->setRead(
            'slave1',
            new Connection('mysql:host=localhost;dbname=slave1', 'id', 'password')
        );
        $locator->setRead(
            'slave2',
            new Connection('mysql:host=localhost;dbname=slave2', 'id', 'password')
        );
        $this->install(new new AuraSqlLocatorModule($locator);
    }
}
{% endhighlight %}

これで`@ReadOnlyConnection`、`@WriteConnection`でアノテートされたメソッドが呼ばれたタイミングで`$this->db`にアノテーションに応じたDBオブジェクトがインジェクトされます。

{% highlight php %}
<?php
use Ray\AuraSqlModule\Annotation\ReadOnlyConnection;  // important
use Ray\AuraSqlModule\Annotation\WriteConnection;     // important

class User
{
    public $pdo;

    /**
     * @ReadOnlyConnection
     */
    public function read()
    {
         $this->pdo: // slave db
    }

    /**
     * @WriteConnection
     */
    public function write()
    {
         $this->pdo: // master db
    }
}
{% endhighlight %}

`AuraSqlLocatorModule`をインストールするときにメソッド名を指定すると、そのメソッドが呼ばれた時にマスタースレーブDBがインジェクトされる機能が追加されます。

{% highlight php %}
<?php
$this->install(new new AuraSqlLocatorModule(
    $locator,
    ['onGet'],                         // slave
    ['onPost', 'onUpdate', 'onDelete'] // master
);
{% endhighlight %}

# Doctrine DBAL

[Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html)もデータベースの抽象化レイヤーです。

composerで`Ray.DbalModule`をインストールします。

{% highlight bash %}
composer require ray/dbal-module
{% endhighlight %}

アプリケーションモジュールで`AuraSqlModule`をインストールします。

{% highlight php %}
<?php
use BEAR\DbalModule\DbalModule;
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

## 環境による接続先の変更

[phpdotenv](https://github.com/vlucas/phpdotenv)ライブラリなどを利用して環境先に応じた接続先を設定します。実装例の[Ex.Package](https://github.com/BEARSunday/Ex.Package)をご覧ください。
