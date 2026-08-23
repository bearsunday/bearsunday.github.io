---
layout: docs-ja
title: Phar
category: Manual
permalink: /manuals/1.0/ja/phar.html
---

# Phar

[Phar](https://www.php.net/manual/ja/intro.phar.php)はアプリケーションを1ファイルにしたものです。コード、`vendor/`、コンパイル済みDIスクリプトが1つのアーカイブに収まります。起動はアーカイブを読むだけで、アーカイブには何も書き込みません。デプロイは1ファイルのコピーで、ロールバックは1つ前のファイルです。

```text
app.phar                                  アプリケーション、vendor/、コンパイル済みDIスクリプト
/tmp/MyVendor/MyProject/prod-hal-app      実行時に書き込むものすべて
```

BEAR.Package 1.24以降が必要です。

## Pharにする

ビルドスクリプトはコンパイルし、続けてアーカイブ化します。どちらもコンパイラのメソッドです。

```php
<?php
// bin/compile.php
use BEAR\Package\Compiler;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('memory_limit', '-1');

$context = 'prod-hal-app';
$writeDir = getenv('APP_WRITE_DIR') ?: null;

$compiler = new Compiler('MyVendor\MyProject', $context, dirname(__DIR__), $writeDir);
$code = $compiler();

exit($code === 0 ? $compiler->phar() : $code);
```

このスクリプトが名乗るのは、アプリケーション名、起動する context（`public/index.php`と同じもの）、そして環境変数から読む書き込み先です。残りは書く必要がありません。何を収めるかはフレームワークの仕事です: アーカイブに入るのは名前の決まったトップレベルのディレクトリだけで、`src`、`public`、`bin`、`vendor`、`var`、そしてインポートしたアプリケーションの置かれた場所です。`var/`のうち入るのはこのビルドの`var/build/{context}`だけで、そこにはコンパイルマーカーを含むDIスクリプトと、[compile step](production.html#compile-steps)が書いたものが入ります。`var/log`と`var/tmp`は入りません。`.env`、`autoload.php`、`tests/`も同じです。ルート直下のファイルで入るのは`preload.php`だけです。残ったディレクトリは`Not packed:`として表示されます。マーカーは`.bear-compile.json`で、`phar()`はこれを見て判断します（`app`、`context`、`tmpDir`、`time`）。`.env`ファイル自体は入りませんが、その値はDIスクリプトに焼き込まれ、そのスクリプトは同梱されます。アーカイブは秘密情報として扱ってください。`phar.readonly`は子プロセスで処理されるので、iniフラグを覚える必要もありません。

```bash
APP_WRITE_DIR=/tmp php bin/compile.php
```

```text
Compiled: 16 resource classes
Phar: /app/app.phar (7.5MB, 2100 files)
Not packed: tests
```

`__invoke()`と`phar()`は別の段階なので、CIでコンパイルとアーカイブ化を別ジョブに分けられます。`phar()`はディスク上のものを詰めるだけで、コンパイルされていない context や、ツリーの中に書くようコンパイルされたものは拒否します。出力先は`{appDir}/app.phar`で、コンパイルが書いた`autoload.php`と`preload.php`の隣です。引数は別のエントリを渡す1つだけです。

3つの出力はどれも固定パスです。複数 context のときは、次をコンパイルする前にパックしてアーカイブを退避するループにします。

```php
// bin/compile.php
$appDir = dirname(__DIR__);
$writeDir = getenv('APP_WRITE_DIR') ?: null;

foreach (['prod-hal-api-app', 'prod-html-app'] as $context) {
    $compiler = new Compiler('MyVendor\MyProject', $context, $appDir, $writeDir);
    $code = $compiler();
    if ($code !== 0) {
        exit($code);
    }

    $code = $compiler->phar();
    if ($code !== 0) {
        exit($code);
    }

    if (! rename($appDir . '/app.phar', $appDir . '/' . $context . '.phar')) {
        exit(1);
    }
}

exit(0);
```

[プロダクション](production.html#compilation-recommended)の`preload.php`のrenameはここではしません。あれはアーカイブにしないデプロイのためのもので、preloadがディスク上に並んでいる必要があるからです。アーカイブはそれぞれ自分のpreloadを`phar://…/{context}.phar/preload.php`に持ちます。パックの前にrenameすると、アーカイブはpreloadなしになります。しかも黙ってそうなります。preloadを使わないビルドも正当なので、何も止めません。

## 動かす

```bash
APP_WRITE_DIR=/tmp php app.phar get '/index?name=BEAR'
```

スタブがアーカイブの中の`public/index.php`を実行するので、`src/Injector.php`の`dirname(__DIR__)`は`phar:///path/app.phar`になります。エントリポイントは[読み取り専用デプロイ](production.html#writable-paths)のままで、他に変更はありません。

php-fpmが実行するのはアーカイブではなくファイルなので、エントリポイントはアーカイブの隣に置き、オートローダーを中から読みます。

```php
<?php
// index.php （app.pharと同じディレクトリ）
require 'phar://' . __DIR__ . '/app.phar/vendor/autoload.php';

exit((new MyVendor\MyProject\Bootstrap())('prod-hal-app', $GLOBALS, $_SERVER, getenv('APP_WRITE_DIR') ?: null));
```

`preload.php`はアーカイブに入るので、`opcache.preload`はアーカイブの中を指します。

```ini
opcache.preload=phar:///path/to/app.phar/preload.php
```

`autoload.php`は入りません。preloadを使うなら[することが残らない](production.html#autoloadphp)からです。同じ`preload.php`をアーカイブの隣に置くと、起動時に`Failed opening required '…/vendor/autoload.php'`で止まります。requireは置かれたディレクトリからの相対で書かれていて、アーカイブの外にそのディレクトリの`vendor/`はありません。preloadはコンパイルごとに1つ、固定パスに書かれます。最後にコンパイルした context をパックしてください。別の context が残したものはパックが拒否します。

## 移動できる

アーカイブがビルドそのものです。別のディレクトリでも別のマシンでも、コピーしてそこで起動できます。外のものは何も読まないので、パックした元のツリーは消して構いません。

```bash
cp app.phar /srv/releases/2026-08-23.phar
APP_WRITE_DIR=/tmp php /srv/releases/2026-08-23.phar get '/index?name=BEAR'
```

固定されているのは場所ではなく書き込み先です。起動時に合わせるのはそれだけで、違っていればアーカイブがそう言います → [ビルドが止まるとき](#when-the-build-stops)

## 守ること2つ

**書き込み先は1つで、ビルドと起動で同じ絶対パスにします。** コンパイル済みスクリプトはコンパイル時のパスを持つので、アーカイブは1つの書き込み先のために作られます。その1箇所が`APP_WRITE_DIR`です。ビルドが読み、起動が読みます。インポートしたアプリケーションにはコンテナが渡すので、他のどこにも書きません。

**`$appMeta->appDir`から実行時のパスを組むバインディングは書きません。** コンパイル済みスクリプトはビルド時の`Meta`を持つため、注入される`appDir`は`phar://…`ではなくビルド時のディレクトリです（`tmpDir`と`logDir`は書き込み先なので正しい値です）。実行時にファイルを読むもの（テンプレートのディレクトリ、データファイルなど）は`__DIR__`を基点にします。`__DIR__`はアーカイブの中を指します。

## インポートしたアプリケーション

アーカイブの中の[インポートしたアプリケーション](import.html)は別のアプリケーションです。`Meta`もコンパイル済みスクリプトも書き込み先も、それぞれのものを持ちます。変更は要りません。ホストに渡した書き込み先はコンテナが渡します。

```php
$this->install(new ImportAppModule([
    new ImportApp('greeting', 'ImportVendor\Greeting', 'prod-app')
]));
```

コンパイルはアプリケーションを起動し、その起動がインポートしたアプリケーションをそれぞれのツリーにコンパイルします（ビルドのログに出る`Compiled DI scripts on demand`がそれです）。DIスクリプトは自動でアーカイブに入ります。インポートしたアプリケーションのディレクトリは起動時に解決されるので、アーカイブの移動に追従します。

## ビルドが止まるとき {#when-the-build-stops}

以前はデプロイ先で起きていた失敗が、パス入りのメッセージでビルド時に止まります。

| エラー | 意味 |
|---|---|
| `PharNotCompiledException` | その context がコンパイルされていない。`phar()`はディスク上のものを詰めます |
| `PharPreloadForAnotherBuildException` | アプリケーションルートの`preload.php`が別の context のもの。最後にコンパイルした context をパックします |
| `PharImportsUnreadableException` | コンパイル済みコンテナのimport宣言がこのバージョンでは読めない形式。アーカイブ化するバージョンで再コンパイルします |
| `PharWritesInsideArchiveException` | ホストまたはインポートしたアプリケーションが、ツリーの中に書く設定でコンパイルされている。`APP_WRITE_DIR`を設定してコンパイルします |
| `PharImportOutsideTreeException` | インポートしたアプリケーションが、アーカイブにするツリーの外にある |
| `PharEntryNotFoundException` | `public/index.php`がない。別のエントリは`Compiler::phar()`に渡します |
| `PharEntryNotPackedException` | エントリは存在するが同梱されない。アプリケーションルートの直置きファイルで入るのは`preload.php`だけです |
| `PharStaleOutputException` | 出力先に前回のアーカイブが残っていて、削除できなかった |
| `PharSymlinkedDirectoryException` | ツリー内のディレクトリが symlink で、`Phar`が詰められない |

起動時に`APP_WRITE_DIR`なしでアーカイブを開始すると`WriteDirRequiredException`で、ビルドと違う`APP_WRITE_DIR`で開始すると両方のパスを名指しする`CompiledForAnotherWriteDirException`で止まります。

背景: [BEAR.Package#426](https://github.com/bearsunday/BEAR.Package/issues/426)
