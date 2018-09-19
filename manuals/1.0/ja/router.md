---
layout: docs-ja
title: ルーター
category: Manual
permalink: /manuals/1.0/ja/router.html
---

# ルーター

ルーターはWebやコンソールなどの外部コンテキストのリソースリクエストを、BEAR.Sunday内部のリソースリクエストに変換します。


```php?start_inline
$request = $app->router->match($GLOBALS, $_SERVER);
echo (string) $request;
// get page://self/user?name=bear
```

# Webルーター

デフォルトのWebルーターではHTTPリクエストのパス(`$_SERVER['REQUEST_URI']`)に対応したリソースクラスにアクセスされます。
例えば`/index`のリクエストは`{Vendor名}\{Project名}\Resource\Page\Index`クラスのHTTPメソッドに応じたPHPメソッドにアクセスされます。

ルーターの設定やスクリプトは必要ありません。

```php?start_inline
namespace MyVendor\MyProject\Resource\Page;

// page://self/index
class Index extends ResourceObject
{
    public function onGet() : ResourceObject // GETリクエスト
    {
    }
}
```

# CLIルーター

`cli`コンテキストではコンソールからの引数が外部入力になります。

```bash
php bin/page.php get /
```

BEAR.SundayアプリケーションはWebとCLIの双方で動作します。

## 複数の単語を使ったURI

ハイフンを使い複数の単語を使ったURIのパスはキャメルケースのクラス名を使います。
例えば`/wild-animal`のリクエストは`WildAnimal`クラスにアクセスされます。

## パラメーター

HTTPメソッドに対応して実行されるPHPメソッドの名前と渡される値は以下の通りです。


| HTTPメソッド | PHPメソッド  |　渡される値  |
|---|---|---|
| GET | onGet | $_GET |
| POST | onPost | $_POST または 標準入力 |
| PUT | onPut | ※標準入力 |
| PATCH | onPatch | ※標準入力 |
| DELETE | onDelete | ※標準入力　|

リクエストのメディアタイプは以下の２つが利用できます。

 * `application/x-www-form-urlencoded` // param1=one&param2=two
 * `application/json` // {"param1": "one", "param2": "one"} (POSTの時は標準入力の値が使われます）

PHPマニュアルの[PUT メソッドのサポート](http://php.net/manual/ja/features.file-upload.put-method.php)もご覧ください。

## メソッドオーバーライド

HTTP PUT トラフィックや HTTP DELETE トラフィックを許可しないファイアウォールがあります。
この制約に対応するため、次の2つの方法でこれらの要求を送ることができます。

 * `X-HTTP-Method-Override` POSTリクエストのヘッダーフィールドを使用してPUTリクエストやDELETEリクエストを送る。
 * `_method` URI パラメーターを使用する。例）POST /users?...&_method=PUT

# Auraルーター

リクエストのパスをパラメーターとして受け取る場合はAura Routerを使用します。

```bash
composer require bear/aura-router-module ^2.0
```

ルータースクリプトのパスを指定して`AuraRouterModule`をインストールします。

```php?start_inline
use Ray\Di\AbstractModule;
use BEAR\Package\Provide\Router\AuraRouterModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $appDir = dirname(dirname(__DIR__));
        $this->install(new AuraRouterModule($appDir . '/var/conf/aura.route.php');
    }
}
```
## ルータースクリプト

ルータースクリプトではグローバルで渡された`Map`オブジェクトに対してルートを設定します。
ルーティングにメソッドを指定する必要はありません。１つ目の引数はルート名としてパス、２つ目の引数に名前付きトークンのプレイスフォルダーを含んだパスを指定します。

`var/conf/aura.route.php`

```php
<?php
/* @var $map \Aura\Router\Map */
$map->route('/blog', '/blog/{id}');
$map->route('/user', '/user/{name}')->tokens(['name' => '[a-z]+']);
```

最初の行では`/blog/bear`とアクセスがあると`page://self/blog?id=bear`としてアクセスされます。
(=`Blog`クラスの`onGet($id)`メソッドに`$id`=`bear`の値でコールされます。)また`token`はパラメーターを正規表現で制限するときに使用します。

## 優先ルーター

Auraルーターでルートされない場合は、Webルーターが使われます。
つまりパスでパラメーターを渡すURIだけにルータースクリプトを用意すればOKです。

## パラメーター

パスからパラメーターを取得するためにAuraルーターは様々な方法が用意されています。

### カスタムマッチング

下のスクリプトは`{date}`が適切なフォーマットの時だけルートします。

```php?start_inline
$map->route('/calendar/from', '/calendar/from/{date}')
    ->tokens([
        'date' => function ($date, $route, $request) {
            try {
                new \DateTime($date);
                return true;
            } catch(\Exception $e) {
                return false;
            }
        }
    ]);
```

### オプション

オプションのパラメーターを指定するためにはパスに`{/attribute1,attribute2,attribute3}`の表記を加えます。

例）
```php?start_inline
$map->route('archive', '/archive{/year,month,day}')
    ->tokens([
        'year' => '\d{4}',
        'month' => '\d{2}',
        'day' => '\d{2}',
    ]);
```

プレイスホルダーの**内側に**最初のスラッシュがあるのに注意してください。
そうすると下のパスは全て'archive'にルートされパラメーターの値が付加されます。

- `/archive            : ['year' => null,   'month' => null, 'day' = null]`
- `/archive/1979       : ['year' => '1979', 'month' => null, 'day' = null]`
- `/archive/1979/11    : ['year' => '1979', 'month' => '11', 'day' = null]`
- `/archive/1979/11/07 : ['year' => '1979', 'month' => '11', 'day' = '07']`

オプションパラメーターは**並ぶ順に**オプションです。つまり"month"なしで"day"を指定することはできません。

### ワイルドカード

任意の長さのパスの末尾パラメーターとして格納したいときには`wildcard()`メソッドを使います。


```php?start_inline
$map->route('wild', '/wild')
    ->wildcard('card');
```
スラッシュで区切られたパスの値が配列になり`wildcard()`で指定したパラメーターに格納されます。

- `/wild             : ['card' => []]`
- `/wild/foo         : ['card' => ['foo']]`
- `/wild/foo/bar     : ['card' => ['foo', 'bar']]`
- `/wild/foo/bar/baz : ['card' => ['foo', 'bar', 'baz']]`

その他の高度なルートに関してはAura Routerの[defining-routes](https://github.com/auraphp/Aura.Router/blob/3.x/docs/defining-routes.md)をご覧ください。

## リバースルーティング

ルートの名前とパラメーターの値からURIを生成することができます。

```php?start_inline
use BEAR\Sunday\Extension\Router\RouterInterface;

class Index extends ResourceObject
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onGet() : ResourceObject
    {
        $userLink = $this->router->generate('/user', ['name' => 'bear']);
        // '/user/bear'
```

## 独自のルーターコンポーネント

 * [BEAR.AuraRouterModule](https://github.com/bearsunday/BEAR.AuraRouterModule)を参考に[RouterInterface](https://github.com/bearsunday/BEAR.Sunday/blob/1.x/src/Extension/Router/RouterInterface.php)を実装します。
