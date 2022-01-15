---
layout: docs-ja
title: インポート
category: Manual
permalink: /manuals/1.0/ja/import.html
---

# インポート

BEARのアプリケーションは、マイクロサービスにすることなく複数のBEARアプリケーションを協調して１つのシステムにすることができます。また、他のアプリケーションからBEARのリソースを利用するのも容易です。

## composer インストール

利用するBEARアプリケーションをcomposerパッケージにしてインストールします。

composer.json
```json
{
  "require": {
    "my-vendor/weekday": "dev-master"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/bearsunday/tutorial1.git"
    }
  ]
}
```

## モジュールインストール

インポートするホスト名とアプリケーション名（namespace)、コンテキストを指定して`ImportAppModule`で他のアプリケーションをインストールします。

AppModiule.php
```php
protected function configure(): void
{
    $this->install(new ImportAppModule([
        new ImportApp('foo', 'MyVendor\Weekday', 'prod-app')
    ]));
    $this->install(new PackageModule());
}
```

## リクエスト

インポートしたリソースは指定したホスト名を指定して利用します。

```php
class Index extends ResourceObject
{
    use ResourceInject;

    public function onGet(string $name = 'BEAR.Sunday'): static
    {
        $weekday = $this->resource->get('app://foo/weekday?year=2022&month=1&day=1');
        $this->body = [
            'greeting' => 'Hello ' . $name,
            'weekday' => $weekday
        ];

        return $this;
    }
}
````

## 他のシステムから

他のフレームワークやCMSからBEARのリソースを利用するのも容易です。

同じようにパッケージとしてインストールして、`Injector::getInstance`でrequireしたアプリケーションのリソースクライアントを取得してリクエストします。

```php

use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;

$resource = Injector::getInstance(
    'MyVendor\Weekday',
    'prod-api-app',
    dirname(__DIR__) . '/vendor/my-vendor/weekday'
)->getInstance(ResourceInterface::class);
$weekdday = $resource->get('/weekday', ['year' => '2022', 'month' => '1', 'day' => 1]);

echo $weekdday->body['weekday'] . PHP_EOL;
```

## システム境界

大きなアプリケーションを小さな複数のアプリケーションの集合体として構築できる点はマイクロサービスと同じですが、インフラストラクチャのオーバーヘッドの増加などのマイクロサービスのデメリットがありません。 またモジュラーモノリスよりもコンポーネントの独立性や境界が明確です。

このページのコードは [bearsunday/example-app-import](https://github.com/bearsunday/example-import-app/commits/master) にあります。
