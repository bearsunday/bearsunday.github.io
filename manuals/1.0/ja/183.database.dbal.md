---
layout: docs-ja
title: Doctrine DBAL
category: Manual
permalink: /manuals/1.0/ja/database_dbal.html
---

# Doctrine DBAL

[Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html)はDoctrineが提供しているデータベースの抽象化レイヤーです。

composerで`Ray.DbalModule`をインストールします。

```bash
composer require ray/dbal-module
```

アプリケーションモジュールで`DbalModule`をインストールします。

```php
use Ray\DbalModule\DbalModule;
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new DbalModule('driver=pdo_sqlite&memory=true'));
    }
}
```

これでDIの設定が整いました。`DbalInject`トレイトを利用すると`$this->db`にDBオブジェクトがインジェクトされます。

```php
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

### 複数DB

複数のデータベースの接続には二番目の引数に識別子を指定します。

```php
$this->install(new DbalModule($logDsn, 'log_db'));
$this->install(new DbalModule($jobDsn, 'job_db'));
```

```php
/**
 * @Inject
 * @Named("log_db")
 */
public function setLogDb(Connection $logDb)
```

[MasterSlaveConnection](http://www.doctrine-project.org/api/dbal/2.0/class-Doctrine.DBAL.Connections.MasterSlaveConnection.html)というリプリケーションのためのマスター／スレーブ接続が標準で用意されています。
