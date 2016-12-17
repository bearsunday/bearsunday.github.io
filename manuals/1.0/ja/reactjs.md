---
layout: docs-ja
title: ReactJS
category: Manual
permalink: /manuals/1.0/ja/reactjs.html
---

*このドキュメントは未完成です。レビューをお願いしています。*

# Redux UIチュートリアル

このチュートリアルではTwigテンプレートエンジン等の代わりにV8JsとRudux-ReactJsを使ってHTMLレンダリングします。
マルチエントリーの非SPAアプリケーションを主な対象としていてJavaScriptではルーターは使用していません。

既存のテンプレートエンジンを使ったWebアプリケーションから、ページ単位でRedux React UIを使ったアプリケーションに移行することができます。

## 前提条件

 * php7
 * V8Js
 * node
 * yarn

## インストール

BEAR.Sundayプロジェクトを作成します。

```bash
composer create-project bear/skeleton MyVendor.MyRedux
```
**vendor**名を`MyVendor`に**project**名を`MyRedux`として入力します。

次に`BEAR.ReactJsModule`をインストールします。

```bash
cd MyVendor.MyRedux
composer require bear/reactjs-module
```

Reduxのスケルトンアプリををインストールします。

```bash
cp -r vendor/bear/reactjs-module/ui-skeleton/redux/ui .
cp vendor/bear/reactjs-module/ui-skeleton/redux/package.json .
yarn install
```

## Redux UIの作成

`example`からcpして`hello`ページを作成します。

```
cp -r ui/src/page/example ui/src/page/hello
```

`ui/entry.js`を変更します。

```javascript?start_inline
module.exports = {
  react: 'src/react-bundle.js',
  ssr_hello: 'src/page/hello/app/server',
  hello: [
    'webpack/hot/dev-server',
    'webpack-hot-middleware/client',
    'src/page/hello/app/client',
  ],
};
```

lintやtest、buildを試してみましょう。（必須ではありません）

```
yarn run lint
yarn run test
yarn run build
```

`src/Module/AppModule.php`にモジュールをインストールします。

```php?start_inline
<?php

namespace MyVendor\MyRedux\Module;

use BEAR\Package\PackageModule;
use BEAR\ReactJsModule\ReduxModule;
use Ray\Di\AbstractModule;
use josegonzalez\Dotenv\Loader as Dotenv;

class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        Dotenv::load([
            'filepath' => dirname(dirname(__DIR__)) . '/.env',
            'toEnv' => true
        ]);
        $this->install(new PackageModule);
        //configure()に追加
        $distDir = dirname(__DIR__, 2) . '/var/www/dist';
        $this->install(new ReduxModule($distDir, 'ssr_hello'));
    }
}
```

## リソースの作成

既存の`src/Resource/Page/Index.php`を変更します。リソースオブジェクトのレンダラーをRedux UIにするために`setRenderer`セッターインジェクションに`@Inject`と`@Named`をアノテートします。`@Named`の値は`ReduxModule`で指定したJSアプリケーションの名前と同じにします。

```php?start_inline
<?php

namespace MyVendor\MyRedux\Resource\Page;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class Index extends ResourceObject
{
    /**
     * @Inject
     * @Named("ssr_hello")
     */
    public function setRenderer(RenderInterface $renderer)
    {
        parent::setRenderer($renderer);
    }

    public function onGet($name = 'BEAR.Sunday')
    {
        $this->body = [
            'title' => 'To ' . $name,
            'hello' => ['message' => 'Hello ' . $name]
        ];

        return $this;
    }
}
```

## テンプレートの作成

リソースのテンプレート`src/Resource/Page/Index.html.php `を作成します。

```php?start_inline
<?php
/* @var $ssr BEAR\ReactJsModule\Ssr */
list($markup, $script) = $ssr->render(['hello']);

return <<<"EOT"
<!doctype>
<html>
<head>
  <title>{$ssr->escape('title')}</title>
</head>
<body>
  <div id="root">{$markup}</div>
  <script src="build/react.bundle.js"></script>
  <script src="build/hello.bundle.js"></script>
  <script>{$script}</script>
</body>
</html>
EOT;
```

リソースオブジェクトから`preloadedState`として使う値のキーだけを指定して`render()`します。上記リソースから**ページのタイトル**に`title`、**ReduxのpreloadedState**に`hello`を使用します。

## 実行

`start`コマンドを実行するとwebpackが実行され`127.0.0.1:8080`でWebサーバーが実行されます。

```
yarn run start
```

開発用に`dev`コマンドを実行すると`phpcs`/`phpmd`監視や[HMR](https://github.com/gaearon/react-hot-loader)、[browserSync](https://browsersync.io/)の機能と共に`start`が実行されます。

```
yarn run dev
```

## デバック
 * `{"title":"To BEAR.Sunday","message":"Hello BEAR.Sunday"}`などとJSONが出力された場合はレンダラーのインジェクションが行われていなくて、Json Rendererが使用されています。
 * `Unexpected key "{key}" found in preloadedState`の例外は存在しないResouceObject::$bodyのキーを指定していることを示しています。
 * 500エラーが帰ってくる場合は`var/log`や`curl` にアクセスしてレスポンス詳細を見てみましょう

 このチュートリアルで作成したアプリケーションは[MyVendor.MyRedux](https://github.com/bearsunday/MyVendor.MyRedux)で参照できます。

## リファレンス

 * (Airbnb JavaScript スタイルガイド)[http://mitsuruog.github.io/javascript-style-guide/]
 * [readux](https://github.com/reactjs/redux) [Documentation](http://redux.js.org/)
