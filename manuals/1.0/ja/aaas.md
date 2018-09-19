---
layout: docs-ja
title: Application as a Service
category: Manual
permalink: /manuals/1.0/ja/aaas.html
---

## AaaS (Application as a Service)

作成したAPIアプリケーションはWebやコンソール（バッチ）からアクセスできますが、他のPHPプロジェクトからライブラリとしてアクセスする事もできます。
このチュートリアルで作成したリポジトリは[https://github.com/bearsunday/Tutorial2.git](https://github.com/bearsunday/Tutorial2.git)にpushしてあります。

このプロジェクトをライブラリとして利用してみましょう。まず最初に新しいプロジェクトフォルダを作って`composer.json`を用意します。

```
mkdir app
cd app
mkdir -p ticket/log
mkdir ticket/tmp
```

composer.json

```json
{
    "name": "my-vendor/app",
    "description": "A BEAR.Sunday application",
    "type": "project",
    "license": "proprietary",
    "require": {
        "my-vendor/ticket": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/bearsunday/Tutorial2.git"
        }
    ]
}
```

composer installでプロジェクトがライブラリとしてインストールされます。

```
composer install
```

`Ticket API`はプロジェクトフォルダにある`.env`を読むように設定されてました。`vendor/my-vendor/app/.env`に保存出来なくもないですが、ここでは別の方法で環境変数をセットアップしましょう。

このような`app/.env`ファイルを用意します。

```bash
export TKT_DB_HOST=localhost
export TKT_DB_NAME=ticket
export TKT_DB_USER=root
export TKT_DB_PASS=''
export TKT_DB_SLAVE=''
export TKT_DB_DSN=mysql:host=${TKT_DB_HOST}\;dbname=${TKT_DB_NAME}
```

`source`コマンドで環境変数にexportすることができます。

```
source .env
```

`Ticket API`を他のプロジェクトから利用する最も簡単なスクリプトは以下のようなものです。
アプリケーション名とコンテキストを指定してアプリケーションオブジェクト`$ticket`を取得してリソースアクセスします。

```php
<?php
use BEAR\Package\Bootstrap;

require __DIR__ . '/vendor/autoload.php';

$ticket = (new Bootstrap)->getApp('MyVendor\Ticket', 'app');
$response = $ticket->resource->post('app://self/ticket',
    ['title' => 'run']
);

echo $response->code . PHP_EOL;


```

`index.php`と保存して実行してみましょう。

```
php index.php
```
```
201
```

APIを他のメソッドに渡したり、他のフレームワークなどののコンテナに格納するためには`callable`オブジェクトにします。
`$createTicket`は普通の関数のように扱うことができます。

```php
<?php
use BEAR\Package\Bootstrap;

require __DIR__ . '/vendor/autoload.php';

$ticket = (new Bootstrap)->getApp('MyVendor\Ticket', 'app');
$createTicket = $ticket->resource->post->uri('app://self/ticket');
// invoke callable object
$response = $createTicket(['title' => 'run']);
echo $response->code . PHP_EOL;
```

うまく動きましたか？しかし、このままでは`tmp`/ `log`ディレクトリは`vendor`の下のアプリが使われてしまいますね。
このようにアプリケーションのメタ情報を変更するとディレクトリの位置を変更することができます。

```php
<?php

use BEAR\AppMeta\Meta;
use BEAR\Package\Bootstrap;

require __DIR__ . '/vendor/autoload.php';

$meta = new Meta('MyVendor\Ticket', 'app');
$meta->tmpDir = __DIR__ . '/ticket/tmp';
$meta->logDir = __DIR__ . '/ticket/log';
$ticket = (new Bootstrap)->newApp($meta, 'app');
```

`Ticket API`はREST APIとしてHTTPやコンソールからアクセスできるだけでなく、BEAR.Sundayではない他のプロジェクトのライブラリとしても使えるようになりました！
