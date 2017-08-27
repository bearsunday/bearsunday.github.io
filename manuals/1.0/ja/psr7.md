---
layout: docs-ja
title: PSR-7
category: Manual
permalink: /manuals/1.0/ja/psr7.html
---

# PSR-7

既存のBEAR.Sundayアプリケーションは特別な変更無しに[PSR-7](http://www.php-fig.org/psr/psr-7/)ミドルウエアとして動作させることができます。

以下のコマンドで`bear/middleware`を追加して、ミドルウエアとして動作させるための[bootstrapスクリプト](https://github.com/bearsunday/BEAR.Middleware/blob/1.x/bootstrap/bootstrap.php)に置き換えます。

```bash
composer require bear/middleware
cp vendor/bear/middleware/bootstrap/bootstrap.php bootstrap/bootstrap.php
```

次にスクリプトの`__PACKAGE__\__VENDOR__`をアプリケーションの名前に変更すれば完了です。

```bash
php -S 127.0.0.1:8080 -t public
```

## ストリーム

ミドルウエアに対応したBEAR.Sundayのリソースは[ストリーム](http://php.net/manual/ja/intro.stream.php)の出力に対応しています。

HTTP出力は`StreamTransfer`が標準です。詳しくは[ストリーム出力](http://bearsunday.github.io/manuals/1.0/ja/stream.html)をご覧ください。

## 新規プロジェクト

新規でPSR-7のプロジェクトを始めることもできます。

```
composer create-project bear/project my-awesome-project
cd my-awesome-project/
php -S 127.0.0.1:8080 -t public
```

## PSR-7ミドルウエア

 * [oscarotero/psr7-middlewares](https://github.com/oscarotero/psr7-middlewares)
