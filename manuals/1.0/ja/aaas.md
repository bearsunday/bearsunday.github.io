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

----

# from tutorial1

## アプリケーションのインポート

BEAR.Sundayで作られたリソースは再利用性に優れています。複数のアプリケーションを同時に動作させ、他のアプリケーションのリソースを利用することができます。別々のWebサーバーを立てる必要はありません。

他のアプリケーションのリソースを利用して見ましょう。

通常はアプリケーションをパッケージとして利用しますが、ここではチュートリアルのために`my-vendor`に新規でアプリケーションを作成して手動でオートローダーを設定します。

```bash
mkdir my-vendor
cd my-vendor
composer create-project bear/skeleton Acme.Blog
```

`composer.json`で`autoload`のセクションに`Acme\\Blog`を追加します。

```json
"autoload": {
    "psr-4": {
        "MyVendor\\Weekday\\": "src/",
        "Acme\\Blog\\": "my-vendor/Acme.Blog/src/"
    }
},
```

`autoload`をダンプします。

```bash
composer dump-autoload
```

これで`Acme\Blog`アプリケーションが配置できました。

次にアプリケーションをインポートするために`src/Module/AppModule.php`で`ImportAppModule`を上書き(override)インストールします。

```php
<?php
// ...
use BEAR\Resource\Module\ImportAppModule; // add this line
use BEAR\Resource\ImportApp; // add this line
use BEAR\Package\Context; // add this line

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $importConfig = [
            new ImportApp('blog', 'Acme\Blog', 'prod-hal-app') // host, name, context
        ];
        $this->override(new ImportAppModule($importConfig , Context::class));
    }
}
```

これは`Acme\Blog`アプリケーションを`prod-hal-app`コンテキストで作成したリソースを`blog`というホストで使用することができます。

`src/Resource/App/Import.php`にImportリソースを作成して確かめてみましょう。

```php
<?php
namespace MyVendor\Weekday\Resource\App;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Import extends ResourceObject
{
    use ResourceInject;

    public function onGet()
    {
        $this->body =[
            'blog' => $this->resource->uri('page://blog/index')['greeting']
        ];

        return $this;
    }
}
```

`page://blog/index`リソースの`greeting`が`blog`に代入されているはずです。`@Embed`も同様に使えます。

```bash
php bin/app.php get /import
```

```bash
200 OK
content-type: application/hal+json

{
    "blog": "Hello BEAR.Sunday",
    "_links": {
        "self": {
            "href": "/import"
        }
    }
}
```

他のアプリケーションのリソースを利用することができました！データ取得をHTTP越しにする必要もありません。

合成されたアプリケーションも他からみたら１つのアプリケーションの１つのレイヤーです。
[レイヤードシステム](http://en.wikipedia.org/wiki/Representational_state_transfer#Layered_system)はRESTの特徴の１つです。

それでは最後に作成したアプリケーションのリソースを呼び出す最小限のスクリプトをコーディングして見ましょう。`bin/test.php`を作成します。


```php?start_inline
use BEAR\Package\Bootstrap;

require dirname(__DIR__) . '/autoload.php';

$api = (new Bootstrap)->getApp('MyVendor\Weekday', 'prod-hal-app');

$blog = $api->resource->uri('app://self/import')['blog'];
var_dump($blog);
```

`MyVendor\Weekday`アプリを`prod-hal-app`で起動して`app://self/import`リソースの`blog`をvar_dumpするコードです。

試して見ましょう。

```
php bin/import.php
```
```
string(17) "Hello BEAR.Sunday"
```

他にも

```php?start_inline
$weekday = $api->resource->uri('app://self/weekday')(['year' => 2000, 'month'=>1, 'day'=>1]);
var_dump($weekday->body); // as array
//array(1) {
//    ["weekday"]=>
//  string(3) "Sat"
//}

echo $weekday; // as string
//{
//    "weekday": "Sat",
//    "_links": {
//    "self": {
//        "href": "/weekday/2000/1/1"
//        }
//    }
//}
```

```php?start_inline
$html = (new Bootstrap)->getApp('MyVendor\Weekday', 'prod-html-app');
$index = $html->resource->uri('page://self/index')(['year' => 2000, 'month'=>1, 'day'=>1]);
var_dump($index->code);
//int(200)

echo $index;
//<!DOCTYPE html>
//<html>
//<body>
//The weekday of 2000/1/1 is Sat.
//</body>
//</html>
```

ステートレスなリクエストでレスポンスが返ってくるRESTのリソースはPHPの関数のようなものです。`body`で値を取得したり`(string)`でJSONやHTMLなどの表現にすることができます。autoloadの部分を除けば二行、連結すればたった一行のスクリプトで  アプリケーションのどのリソースでも操作することができます。

このようにBEAR.Sundayで作られたリソースは他のCMSやフレームワークからも簡単に利用することができます。複数のアプリケーションの値を一度に扱うことができます。

