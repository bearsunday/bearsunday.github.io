---
layout: docs-ja
title: Javascrript UI
category: Manual
permalink: /manuals/1.0/ja/ssr.html
---

# Javascript UI

ビューのレンダリングをTwigなどのPHPのテンプレートエンジン等の代わりにサーバーサイドまたはサーバーサイドのJavascriptが行います。

## 前提条件

 * PHP 7.1
 * Node.js
 * yarn
 * V8js (開発ではオプション)

## 用語

 * CSR クライアントサイドレンダリング　- webブラウザで描画
 * SSR サーバーサイドレンダリング - サーバーサイドのV8またはNode.jsが描画

## インストール

BEAR.Sundayプロジェクトに`koriym/ssr-module`をインストールします。

```bash
cd /path/to/MyVedor.MyApp
composer require bear/ssr-module 1.0-x
```

Reduxのスケルトンアプリ`koriym/js-ui-skeleto`をインストールします。

```bash
composer require koriym/js-ui-skeleton 1.0-x
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


このアプリケーションの入力値は設定ファイルで与えられています。

```php?
<?php
$app = 'index';                   // index.bundle.js (JSアプリケーション)
$preloadState = [                 // アプリケーションに与えられる引数
    'hello' =>['name' => 'World']
];
$metas = [                        // SSRでのみ必要な値
    'title' =>'page-title'
];

return [$app, $preloadState, $metas];
```

設定ファイルをコピーして、入力値を変えてみましょう。


```
cp ui/dev/config/index.php ui/dev/config/myapp.php
```

ブラウザをリロードすると`myapp`のリンクが現れ試すことができます。

Note: Javascriptや本体のPHPアプリケーションを変更しないでUIのデータを変更できます。


## UIアプリケーションの作成


名前(name)を受け取って挨拶(Hello name)を返す最小限のアプリケーションを作成します。

```
const render = state => (
  `Hello ${state.name}`
)
```

`ui/src/page/index/hello/server.js`として保存してください。

次にこのプログラムをコンパイルするために`ui/entry.js`にエントリーポイントを登録します。

```javascript?start_inline
module.exports = {
  hello: 'src/page/hello/server',
};
```

この`hello`アプリケーションを実行するためのファイルを`ui/dev/config/myapp.php`に作成します。

```php?
<?php
$app = 'hello';
$preloadState = [
    ['name' => 'World']
];
$metas = [];

return [$app, $preloadState, $metas];
```

以上です！

今回は単純なテンプレート文字列を使っただけの合成でしたが、`render`関数の中の処理を`Redux React`や`Vue.js`などのUIフレームワークを使ってよりリッチなUIを作成できます。
SSRでのPHPとJSのインターフェイスはこの部分です。

このように2つの引数を受け取って出力する文字列を返します。

```
const render = (state, metas) => (
  __SUPER_AWESOME_UI__
)
```

引数の名前は変更することができますが、`render`という関数名はglobelで**変更ができません**。

ここまでPHPアプリケーションにノータッチです。SSRのアプリケーション開発はPHP開発と独立して行うことができます。

## @Ssrアノテーション

リソースをSSRするメソッドに`@Ssr`とアノテートします。

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
     * @Ssr(app="hello")
     */
    public function onGet($name = 'BEAR.Sunday')
    {
        $this->body = [
            'name' => $name
        ];

        return $this;
    }
}
```

`app`にアプリケーション名が必要です。これでリソースボディが`render`関数の1つ目の引数として渡されます。
CSRとSSRの値を区別して渡したい場合は`state`と`metas`でキーを指定します。

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

実際`state`と`metas`をどのようにして渡してSSRを実現するかは`ui/src/page/index/server`のサンプルアプリケーションをご覧ください。


## デバック

 * Chromeプラグイン [React developer tools](https://chrome.google.com/webstore/detail/react-developer-tools/fmkadmapgofadopljbjfkapdkoienihi)、[Redux devTools]( https://chrome.google.com/webstore/detail/redux-devtools/lmhkpmbekcpmknklioeibfkpmmfibljd)が利用できます。
 * 500エラーが帰ってくる場合は`var/log`や`curl` でアクセスしてレスポンス詳細を見てみましょう

## リファレンス

 * [Airbnb JavaScript スタイルガイド](http://mitsuruog.github.io/javascript-style-guide/)
 * [React](https://facebook.github.io/react/)
 * [Redux](http://redux.js.org/)
 * [Redux github](https://github.com/reactjs/redux)
 * [Redux devtools](https://github.com/gaearon/redux-devtools)
 * [Karma test runner](http://karma-runner.github.io/1.0/index.html)
 * [Mocha test framework](https://mochajs.org/)
 * [Chai assertion library](http://chaijs.com/)
 * [Yarn package manager](https://yarnpkg.com/)
 * [Webapack module bunduler](https://webpack.github.io/)
