---
layout: docs-ja
title: ReactJS
category: Manual
permalink: /manuals/1.0/ja/reactjs.html
---

## インストール

まずプロジェクトを作成します。

```bash
composer create-project bear/skeleton MyVendor.MyReactJsApp
```
**vendor**名を`MyVendor`に**project**名を`MyReactJsApp`として入力します。

次に`BEAR.ReactJsModule`をインストールします。

```bash
cd MyVendor.MyReactJsApp
composer require bear/reactjs-module ~0.1
```

`php-reactjs-ui`をインストールします。

```bash
git clone git@github.com:koriym/php-reactjs-ui.git
mv php-reactjs-ui/ui .
mv php-reactjs-ui/package.json .
rm -rf php-reactjs-ui
npm install
```

`AppModule`にモジュールをインストールします。

```php?start_inline
use BEAR\ReactJsModule\ReactJsModule;
```

```php?start_inline
//configure()に追加
$baseDir = dirname(dirname(__DIR__));
$reactLibSrc = $baseDir . '/var/www/build/react.bundle.js';
$reactAppSrc = $baseDir . '/var/www/build/app.bundle.js';
$this->install(new ReactJsModule($reactLibSrc, $reactAppSrc));
```

## ビルド
