---
layout: docs-ja
title: Swoole
category: Manual
permalink: /manuals/1.0/ja/swoole.html
---

# Swoole

SwooleとはC/C++で書かれたPHP拡張の1つで、イベント駆動の非同期＆コルーチンベースの並行処理ネットワーキング通信エンジンです。
Swooleを使ってコマンドラインから直接BEAR.Sundayウェブアプリケーションを実行することができます。パフォーマンスが大幅に向上します。

## インストール

### Swooleのインストール

PECL経由:

```bash
pecl install swoole
```

ソースから:

```bash
git clone https://github.com/swoole/swoole-src.git && \
cd swoole-src && \
phpize && \
./configure && \
make && make install
```

`php.ini`で`extension=swoole.so`を追加してください。

### BEAR.Swooleのインストール

```bash
composer require bear/swoole ^0.4
```

`AppModule`でのインストールは必要ありません。

`bin/swoole.php`にスクリプトを設置します。

```php
<?php
require dirname(__DIR__) . '/autoload.php';
exit((require dirname(__DIR__) . '/vendor/bear/swoole/bootstrap.php')(
    'prod-hal-app',       // context
    'MyVendor\MyProject', // application name
    '127.0.0.1',          // IP
    8080                  // port
));
```

## 実行

サーバーをスタートさせます。

```bash
php bin/swoole.php
```

実行すると以下のメッセージが表示されます:

```
Swoole http server is started at http://127.0.0.1:8088
```

## ベンチマークサイト

特定環境でベンチマークテストをするための[BEAR.HelloworldBenchmark](https://github.com/bearsunday/BEAR.HelloworldBenchmark)が用意されています。

* [The benchmark result](https://github.com/bearsunday/BEAR.HelloworldBenchmark/wiki)

[<img src="https://github.com/swoole/swoole-src/raw/master/mascot.png">](https://github.com/swoole/swoole-src)
