---
layout: docs-ja
title: Javascript UI
category: Manual
permalink: /manuals/1.0/ja/js-ui.html
---

# Javascript UI

ビューのレンダリングをTwigなどのPHPのテンプレートエンジン等が行う代わりに、サーバーサイドのJavaScriptが行います。
PHP側は認証/認可/初期状態/APIの提供を行い、JSがUIをレンダリングします。

既存のプロジェクトの構造で、アノテートされたリソースのみに適用されるので導入が容易です。

## 前提条件

 * PHP 7.1
 * [Node.js](https://nodejs.org/ja/)
 * [yarn](https://yarnpkg.com/)
 * [V8Js](http://php.net/manual/ja/book.v8js.php) (開発ではオプション)

Note: V8JsがインストールされていないとNode.jsでJSが実行されます。

## 用語

 * **CSR** クライアントサイドレンダリング (Webブラウザで描画)
 * **SSR** サーバーサイドレンダリング (サーバーサイドのV8またはNode.jsが描画)

# JavaScript

## インストール

プロジェクトに`koriym/ssr-module`をインストールします。

```bash
// composer create-project bear/skeleton // 新規の場合
// cd MyVendor.MyApp
composer require bear/ssr-module 1.x-dev
```

UIスケルトンアプリ`koriym/js-ui-skeleton`をインストールします。

```bash
composer require koriym/js-ui-skeleton 1.x-dev
cp -r vendor/koriym/js-ui-skeleton/ui .
cp -r vendor/koriym/js-ui-skeleton/package.json .
yarn install
```

## UIアプリケーションの実行

まずはデモアプリケーションを動かして見ましょう。
現れたWebページからレンダリング方法を選択してJSアプリケーションを実行します。

```
yarn run ui
```
このアプリケーションの入力は`ui/dev/config/`の設定ファイルで設定します。

```php?
<?php
$app = 'index';                   // =index.bundle.js
$state = [                        // アプリケーションステート
    'hello' =>['name' => 'World']
];
$metas = [                        // SSRでのみ必要な値
    'title' =>'page-title'
];

return [$app, $state, $metas];
```

設定ファイルをコピーして、入力値を変えてみましょう。

```
cp ui/dev/config/index.php ui/dev/config/myapp.php
```

ブラウザをリロードして新しい設定を試します。
このようにJavascriptや本体のPHPアプリケーションを変更しないでUIのデータを変更して動作を確認することができます。

このセクションで編集したPHPの設定ファイルはあくまで`yarn run ui`で実行する時のみに使用されます。
PHP側が必要とするのはバンドルされて出力されたJSのみです。


## UIアプリケーションの作成

PHPから渡された引数を使ってレンダリングした文字列を返す**render**関数を作成します。

```
const render = (state, metas) => (
  __AWESOME_UI__ // SSR対応のライブラリやJSのテンプレートエンジンを使って文字列を返す
)
```

`state`はドキュメントルートに必要な値、`metas`はそれ以外の値、例えば<head>で使う値などです。`render`という関数名は固定です。

ここでは名前を受け取って挨拶を返す関数を作成します。

```
const render = state => (
  `Hello ${state.name}`
)
```

`ui/src/page/index/hello/server.js`として保存して、webpackのエントリーポイントを`ui/entry.js`に登録します。

```javascript?start_inline
module.exports = {
  hello: 'src/page/hello/server',
};
```

これで`hello.bundle.js`というバンドルされたファイルが出力されるようになりました。

このhelloアプリケーションをテスト実行するためのファイルを`ui/dev/config/myapp.php`に作成します。

```php?
<?php
$app = 'hello';
$state = [
    ['name' => 'World']
];
$metas = [];

return [$app, $state, $metas];
```

以上です！ヴラウザをリロードして試してください。

render関数の中の処理をReactやVue.jsなどのUIフレームワークを使ってリッチなUIを作成できます。
通常のアプリケーションでは依存を最小限にするために`server.js`エントリーファイルは以下のようにrenderモジュールを読み込むようにします。

```javascript
import render from './render';
global.render = render;
```

ここまでPHP側の作業はありません。SSRのアプリケーション開発はPHP開発と独立して行うことができます。

# PHP

## モジュールインストール

AppModuleに`SsrModule`モジュールをインストールします。

```php
<?php
use BEAR\SsrModule\SsrModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        // ...
        $build = dirname(__DIR__, 2) . '/var/www/build';
        $this->install(new SsrModule($build));
    }
}
```

`$build`フォルダはJSのファイルがあるディレクトリです。(`ui/ui.config.js`で指定するwebpackの出力先)

## @Ssrアノテーション

リソースをSSRするメソッドに`@Ssr`とアノテートします。`app`にJSアプリケーション名が必要です。

```php?start_inline
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

`$this->body`が`render`関数に1つ目の引数として渡されます。
CSRとSSRの値を区別して渡したい場合は`state`と`metas`でbodyのキーを指定します。

```php?start_inline
/**
 * @Ssr(
 *   app="index_ssr",
 *   state={"name", "age"},
 *   metas={"title"}
 * )
 */
public function onGet()
{
    $this->body = [
        'name' => 'World',
        'age' => 4.6E8;
        'title' => 'Age of the World'
    ];

    return $this;
}
```

実際`state`と`metas`をどのようにして渡してSSRを実現するかは`ui/src/page/index/server`のサンプルアプリケーションをご覧ください。影響を受けるのはアノテートしたメソッドだけで、APIやHTMLのレンダリングの設定はそのままです。

# PHPアプリケーションの実行設定

`ui/ui.config.js`を編集して、`public`にweb公開ディレクトリを`build`にwebpackのbuild先を指定します。
`build`はSsrModuleのインストールで指定したディレクトリと同じです。

```javascript
const path = require('path');

module.exports = {
  public: path.join(__dirname, '../var/www'),
  build: path.join(__dirname, '../var/www/build')
};
```

## PHPアプリケーションの実行

```
yarn run dev
```

ライブアップデートで実行します。
PHPファイルの変更があれば自動でリロードされ、Reactのコンポーネントに変更があればリロードなしでコンポーネントをアップデートします。ライブアップデートなしで実行する場合には`yarn run start`を実行します。

`lint`や`test`などの他のコマンドは[コマンド](https://github.com/koriym/Koriym.JsUiSkeleton/blob/1.x/README.ja.md#コマンド)をご覧ください。

## パフォーマンス

V8のスナップショットをApc保存する機能を使ってパフォーマンスの大幅な向上が可能です。
`ProdModule`で`ApcSsrModule`をインストールしてください。
ReactJsやアプリケーションのスナップショットが`APCu`に保存され再利用されます。V8jsが必要です。

```php?start_inline
$this->install(new ApcSsrModule);
```

Apc以外のキャッシュを利用するには`ApcSsrModule`のコードを参考にモジュールを作成してください。
PSR16対応のキャッシュが利用可能です。

さらなる高速化のためにはV8をコンパイルする時点でJSコード(ReactJsなど）のスナップショットを取り込みます。
詳しくは以下をご覧ください。

 * [20x performance boost with V8Js snapshots](http://stesie.github.io/2016/02/snapshot-performance)
 * [v8js - Possibility to Improve Performance with Precompiled Templates/Classes ?](https://github.com/phpv8/v8js/issues/205)

## デバック

 * Chromeプラグイン [React developer tools](https://chrome.google.com/webstore/detail/react-developer-tools/fmkadmapgofadopljbjfkapdkoienihi)、[Redux devTools]( https://chrome.google.com/webstore/detail/redux-devtools/lmhkpmbekcpmknklioeibfkpmmfibljd)が利用できます。
 * 500エラーが帰ってくる場合は`var/log`や`curl` でアクセスしてレスポンス詳細を見てみましょう


## リファレンス

 * [ECMAScript 6](http://postd.cc/es6-cheatsheet/)
 * [Airbnb JavaScript スタイルガイド](http://mitsuruog.github.io/javascript-style-guide/)
 * [React](https://facebook.github.io/react/)
 * [Redux](http://redux.js.org/)
 * [Redux github](https://github.com/reactjs/redux)
 * [Redux devtools](https://github.com/gaearon/redux-devtools)
 * [Karma test runner](http://karma-runner.github.io/1.0/index.html)
 * [Mocha test framework](https://mochajs.org/)
 * [Chai assertion library](http://chaijs.com/)
 * [Yarn package manager](https://yarnpkg.com/)
 * [Webpack module bundler](https://webpack.js.org/)

## その他ビューライブラリ

  * [Vue.js](https://jp.vuejs.org/)
  * [Handlesbar.js](http://handlebarsjs.com/)
  * [doT.js](http://olado.github.io/doT/index.html)
  * [pug](https://pugjs.org/api/getting-started.html)
  * [Hogan](http://twitter.github.io/hogan.js/) (Twitter)
  * [Nunjucks](https://mozilla.github.io/nunjucks/)(Mozilla)
  * [dust.js](http://www.dustjs.com/) (LinkedIn)
  * [marko](http://markojs.com/) (Ebay)

* 以前のReact JSページは[ReactJs](reactjs.html)へ*
