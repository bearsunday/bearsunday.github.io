---
layout: docs-ja
title: JavaScript UI
category: Manual
permalink: /manuals/1.0/ja/js-ui.html
---

# JavaScript UI

ビューのレンダリングをTwigなどのPHPのテンプレートエンジンが行う代わりに、サーバーサイドのJavaScriptが実行します。PHP側は認証・認可・初期状態・APIの提供を行い、JavaScriptがUIをレンダリングします。既存のプロジェクトの構造で、アノテーションが付与されたリソースのみに適用されるため、導入が容易です。

## 前提条件

* PHP 7.1以上
* [Node.js](https://nodejs.org/ja/)
* [yarn](https://yarnpkg.com/)
* [V8Js](http://php.net/manual/ja/book.v8js.php)（開発時はオプション）

注：V8Jsがインストールされていない場合、Node.jsでJavaScriptが実行されます。

## 用語

* **CSR**: クライアントサイドレンダリング（Webブラウザで描画）
* **SSR**: サーバーサイドレンダリング（サーバーサイドのV8またはNode.jsが描画）

## JavaScript

### インストール

プロジェクトに`koriym/ssr-module`をインストールします：

```bash
# 新規プロジェクトの場合
# composer create-project bear/skeleton MyVendor.MyProject; cd MyVendor.MyProject
composer require bear/ssr-module
```

UIスケルトンアプリケーション`koriym/js-ui-skeleton`をインストールします：

```bash
composer require koriym/js-ui-skeleton 1.x-dev
cp -r vendor/koriym/js-ui-skeleton/ui .
cp -r vendor/koriym/js-ui-skeleton/package.json .
yarn install
```

### UIアプリケーションの実行

まずはデモアプリケーションを動かしてみましょう。表示されたWebページからレンダリング方法を選択して、JavaScriptアプリケーションを実行します：

```bash
yarn run ui
```

このアプリケーションの入力は`ui/dev/config/`の設定ファイルで行います：

```php
<?php
$app = 'index';                   // index.bundle.jsを指定
$state = [                        // アプリケーションステート
    'hello' => ['name' => 'World']
];
$metas = [                        // SSRでのみ必要な値
    'title' => 'page-title'
];

return [$app, $state, $metas];
```

設定ファイルをコピーして、入力値を変更してみましょう：

```bash
cp ui/dev/config/index.php ui/dev/config/myapp.php
```

ブラウザをリロードして新しい設定を試します。このように、JavaScriptや本体のPHPアプリケーションを変更せずに、UIのデータを変更して動作を確認することができます。

このセクションで編集したPHPの設定ファイルは、`yarn run ui`で実行する時のみに使用されます。PHP側が必要とするのは、バンドルされて出力されたJavaScriptファイルのみです。

### UIアプリケーションの作成

PHPから渡された引数を使ってレンダリングした文字列を返す**render**関数を作成します：

```javascript
const render = (state, metas) => (
    __AWESOME_UI__ // SSR対応のライブラリやJSのテンプレートエンジンを使って文字列を返す
);
```

`state`はドキュメントルートに必要な値、`metas`はそれ以外の値（例えば`<head>`で使う値など）です。`render`という関数名は固定です。

ここでは名前を受け取って挨拶を返す関数を作成します：

```javascript
const render = state => (
    `Hello ${state.name}`
);
```

`ui/src/page/index/hello/server.js`として保存して、webpackのエントリーポイントを`ui/entry.js`に登録します：

```javascript
module.exports = {
    hello: 'src/page/hello/server'
};
```

これで`hello.bundle.js`というバンドルされたファイルが出力されるようになりました。

このhelloアプリケーションをテスト実行するためのファイルを`ui/dev/config/myapp.php`に作成します：

```php
<?php
$app = 'hello';
$state = [
    ['name' => 'World']
];
$metas = [];

return [$app, $state, $metas];
```

以上です！ブラウザをリロードして試してください。

render関数内では、ReactやVue.jsなどのUIフレームワークを使ってリッチなUIを作成できます。

通常のアプリケーションでは、依存を最小限にするために`server.js`エントリーファイルは以下のようにrenderモジュールを読み込むようにします：

```javascript
import render from './render';
global.render = render;
```

ここまでPHP側の作業はありません。SSRのアプリケーション開発は、PHP開発と独立して行うことができます。

## PHP

### モジュールインストール

AppModuleに`SsrModule`モジュールをインストールします：

```php
<?php
use BEAR\SsrModule\SsrModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $build = dirname(__DIR__, 2) . '/var/www/build';
        $this->install(new SsrModule($build));
    }
}
```

`$build`フォルダはJavaScriptファイルがあるディレクトリです（`ui/ui.config.js`で指定するwebpackの出力先）。

### @Ssrアノテーション

リソースをSSRするメソッドに`@Ssr`とアノテートします。`app`にJavaScriptアプリケーション名を指定する必要があります：

```php
<?php
namespace MyVendor\MyRedux\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\SsrModule\Annotation\Ssr;

class Index extends ResourceObject
{
    /**
     * @Ssr(app="index_ssr")
     */
    public function onGet($name = 'BEAR.Sunday')
    {
        $this->body = [
            'hello' => ['name' => $name]
        ];
        return $this;
    }
}
```

`$this->body`が`render`関数の第1引数として渡されます。

CSRとSSRの値を区別して渡したい場合は、`state`と`metas`でbodyのキーを指定します：

```php
/**
 * @Ssr(
 *     app="index_ssr",
 *     state={"name", "age"},
 *     metas={"title"}
 * )
 */
public function onGet()
{
    $this->body = [
        'name' => 'World',
        'age' => 4.6E8,
        'title' => 'Age of the World'
    ];
    return $this;
}
```

実際に`state`と`metas`をどのように渡してSSRを実現するかは、`ui/src/page/index/server`のサンプルアプリケーションをご覧ください。

影響を受けるのはアノテートしたメソッドだけで、APIやHTMLのレンダリングの設定はそのままです。

### PHPアプリケーションの実行設定

`ui/ui.config.js`を編集して、`public`にWeb公開ディレクトリを、`build`にwebpackのビルド先を指定します。`build`は`SsrModule`のインストール時に指定したディレクトリと同じにします：

```javascript
const path = require('path');

module.exports = {
    public: path.join(__dirname, '../var/www'),
    build: path.join(__dirname, '../var/www/build')
};
```

### PHPアプリケーションの実行

```bash
yarn run dev
```

ライブアップデートで実行します。PHPファイルの変更があれば自動でリロードされ、Reactのコンポーネントに変更があれば、リロードなしでコンポーネントがアップデートされます。

ライブアップデートなしで実行する場合は`yarn run start`を実行します。

`lint`や`test`などの他のコマンドについては、[コマンド](https://github.com/koriym/Koriym.JsUiSkeleton/blob/1.x/README.ja.md#コマンド)をご覧ください。

## パフォーマンス

V8のスナップショットをAPCuに保存する機能を使って、パフォーマンスの大幅な向上が可能です。`ProdModule`で`ApcSsrModule`をインストールしてください。Reactやアプリケーションのスナップショットが`APCu`に保存され再利用されます。V8Jsが必要です：

```php
$this->install(new ApcSsrModule);
```

APCu以外のキャッシュを利用するには、`ApcSsrModule`のコードを参考にモジュールを作成してください。PSR-16対応のキャッシュが利用可能です。

さらなる高速化のためには、V8をコンパイルする時点でJavaScriptコード（Reactなど）のスナップショットを取り込みます。詳しくは以下をご覧ください：

* [20x performance boost with V8Js snapshots](http://stesie.github.io/2016/02/snapshot-performance)
* [v8js - Possibility to Improve Performance with Precompiled Templates/Classes?](https://github.com/phpv8/v8js/issues/205)

## デバッグ

* Chromeプラグイン[React Developer Tools](https://chrome.google.com/webstore/detail/react-developer-tools/fmkadmapgofadopljbjfkapdkoienihi)、[Redux DevTools](https://chrome.google.com/webstore/detail/redux-devtools/lmhkpmbekcpmknklioeibfkpmmfibljd)が利用できます。
* 500エラーが返ってくる場合は、`var/log`や`curl`でアクセスしてレスポンスの詳細を確認してみましょう。

## リファレンス

* [ECMAScript 6](http://postd.cc/es6-cheatsheet/)
* [Airbnb JavaScript スタイルガイド](http://mitsuruog.github.io/javascript-style-guide/)
* [React](https://facebook.github.io/react/)
* [Redux](http://redux.js.org/)
* [Redux GitHub](https://github.com/reactjs/redux)
* [Redux DevTools](https://github.com/gaearon/redux-devtools)
* [Karma テストランナー](http://karma-runner.github.io/1.0/index.html)
* [Mocha テストフレームワーク](https://mochajs.org/)
* [Chai アサーションライブラリ](http://chaijs.com/)
* [Yarn パッケージマネージャー](https://yarnpkg.com/)
* [Webpack モジュールバンドラー](https://webpack.js.org/)

## その他ビューライブラリ

* [Vue.js](https://jp.vuejs.org/)
* [Handlebars.js](http://handlebarsjs.com/)
* [doT.js](http://olado.github.io/doT/index.html)
* [Pug](https://pugjs.org/api/getting-started.html)
* [Hogan](http://twitter.github.io/hogan.js/)（Twitter）
* [Nunjucks](https://mozilla.github.io/nunjucks/)（Mozilla）
* [Dust.js](http://www.dustjs.com/)（LinkedIn）
* [Marko](http://markojs.com/)（eBay）

*以前のReact JSページは[ReactJs](reactjs.html)をご覧ください。*
