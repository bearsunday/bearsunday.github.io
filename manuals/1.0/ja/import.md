---
layout: docs-ja
title: インポート
category: Manual
permalink: /manuals/1.0/ja/import.html
---

# インポート

BEARのアプリケーションは全てがURIを持つリソースの集合です。この性質により、アプリケーション全体を別のBEARアプリケーションの「部品」としてインストールし、自身のリソースと同じインターフェイスで利用できます。マイクロサービスのようにシステムを独立した複数のアプリケーションに分割しながら、呼び出しはネットワークを介さない同一プロセス内の実行です。

## composerインストール

利用するBEARアプリケーションをcomposerパッケージとしてインストールします。[^1]

composer.json
```json
{
  "require": {
    "bear/package": "^1.13",
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

`ImportAppModule`でインポートするアプリケーションを登録します。`ImportApp`にはホスト名、アプリケーション名（namespace）、コンテキストの3つを指定します。

AppModule.php
```diff
+use BEAR\Package\Module\ImportAppModule;
+use BEAR\Package\Module\Import\ImportApp;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        // ...
+        $this->install(new ImportAppModule([
+            new ImportApp('foo', 'MyVendor\Weekday', 'prod-app')
+        ]));
        $this->install(new PackageModule());
    }
}
```

* `foo` — インポート先を指すホスト名。URIの`app://foo/`に対応します。
* `MyVendor\Weekday` — インポートするアプリケーション名（namespace）
* `prod-app` — インポートするアプリケーションを実行するコンテキスト

コンテキストをホスト側とは独立して指定できることに注目してください。自身が開発コンテキストで動作していても、インポートしたアプリケーションは`prod-app`の束縛で動作させることができます。アプリケーションごとにDIとAOPの構成が完結しているBEARだからこそ可能な合成です。[^2]

## リクエスト

インポートしたリソースは、登録したホスト名を指定してリクエストします。

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
```

自身のリソース（`app://self/`）との違いはホスト名だけで、`#[Embed]`や`#[Link]`も同様に利用できます。クライアントコードはリソースが自身のものかインポートされたものかを区別する必要がなく、リモートアクセスのように見えるこのリクエストに、分散システムのレイテンシーや通信障害といったコストはありません。

## 他のシステムから

BEAR以外のフレームワークやCMSからの利用も同様です。パッケージとしてインストールし、`Injector::getInstance`にアプリケーション名、コンテキスト、アプリケーションのパスを渡してインジェクターを取得し、そこからリソースクライアントを取得します。ブートストラップのスクリプトは不要で、BEARアプリケーションは指定したコンテキストで構成された1つのライブラリとして機能します。

```php
use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;

$resource = Injector::getInstance(
    'MyVendor\Weekday',
    'prod-api-app',
    dirname(__DIR__) . '/vendor/my-vendor/weekday'
)->getInstance(ResourceInterface::class);

$weekday = $resource->get('/weekday', ['year' => '2022', 'month' => '1', 'day' => 1]);
echo $weekday->body['weekday'] . PHP_EOL;
```

## 環境変数

環境変数はグローバルでアプリケーション間で共有されるため、プリフィックスを付与するなどしてコンフリクトを避ける必要があります。インポートされるアプリケーションは`.env`ファイルではなく、プロダクションと同じようにシェルの環境変数を取得します。

## システム境界

大きなアプリケーションを小さな複数のアプリケーションの集合体として構築できる点はマイクロサービスと同じですが、インフラストラクチャのオーバーヘッド増加などのマイクロサービスのデメリットがありません。また境界がnamespaceの規約ではなくアプリケーションそのものであるため、モジュラーモノリスよりもコンポーネントの独立性と境界が明確です。

このページのコードは [bearsunday/example-import-app](https://github.com/bearsunday/example-import-app/commits/master) にあります。

## 多言語フレームワーク

[BEAR.Thrift](https://github.com/bearsunday/BEAR.Thrift)を使うと、[Apache Thrift](https://thrift.apache.org/)を介して他の言語や、異なるバージョンのPHP・BEARのアプリケーションからリソースにアクセスできます。

[^1]: インポート機能には`bear/package ^1.13`が必要です。
[^2]: `ImportAppModule`は`BEAR\Resource`ではなく`BEAR\Package`のものを使用します。
