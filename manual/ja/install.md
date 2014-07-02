---
layout: default_ja
title: BEAR.Sunday | インストール
category: Manual
---

# インストール

```
$ composer create-project bear/package {$PROJECT_PATH}
```

## 必要要件

 * PHP 5.4+

## オプション

 * [APC](http://php.net/manual/ja/book.apc.php)
 * [APCu](http://pecl.php.net/package/APCu) (PHP5.5+)
 * [curl](http://php.net/manual/ja/book.curl.php)
 * Profiler [xhprof](http://jp.php.net/manual/en/book.xhprof.php)
 * Graph Visualization [graphviz](http://www.graphviz.org/)

## 環境確認

```
$ cd {$PROJECT_PATH}
$ php bin/env.php
```

BEAR.SundayアプリケーションはWebまたはCLIからアクセスできます。

### Sandbox アプリケーション

```
$ bin/bear.server apps/Demo.Sandbox
```

https://github.com/koriym/BEAR.package#built-in-web-server-for-development をご覧ください。

## 新しいアプリケーションの作成

```
$ composer create-project bear/skeleton apps/{$Vendor.Application}
$ composer create-project bear/skeleton apps/{$Vendor.Application} {$SKELETON_VERSION}
```

## 新しいリソースの作成

```
$ bin/bear.create-resource apps/{$Vendor.Application} {$URI}
$ bin/bear.create-resource apps/Demo.Sandbox/ page://self/greeting
```

## 試してみましょう

新しいアプリケーション `My.Hello` を作成し、どのように動くか見てみましょう。

```
// BEAR.Package フレームワークのファイルを作成
$ composer create-project bear/package ./bear
$ cd bear

// 'My.Hello' アプリケーションのファイルを作成
$ composer create-project bear/skeleton apps/My.Hello

// ビルトインWebサーバーを起動
$ bin/bear.server apps/My.Hello
```

ブラウザで `http://0.0.0.0:8080` にアクセスし、BEAR.Sundayからのメッセージを確認してください。
