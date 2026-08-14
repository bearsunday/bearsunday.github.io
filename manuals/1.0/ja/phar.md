---
layout: docs-ja
title: Phar
category: Manual
permalink: /manuals/1.0/ja/phar.html
---

# Phar

[Phar](https://www.php.net/manual/ja/intro.phar.php)はアプリケーションを1ファイルにしたものです。コード、`vendor/`、コンパイル済みDIスクリプトが1つのアーカイブに収まります。起動はアーカイブを読むだけで、アーカイブには何も書き込みません。デプロイは1ファイルのコピーで、ロールバックは1つ前のファイルです。

```text
prod-app.phar                             アプリケーション、vendor/、コンパイル済みDIスクリプト
/tmp/MyVendor/MyProject/prod-app          実行時に書き込むものすべて
```

BEAR.Package 1.23以降が必要です。

## Pharにする

コンパイルはアプリケーション自身の`bin/compile.php`で行います。無い場合は一度だけコピーします（BEAR.Skeletonには同梱されています）。

```bash
cp vendor/bear/package/bin/compile.php bin/
```

```php
<?php
// bin/compile.php
use BEAR\Package\Console;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('memory_limit', '-1');

$console = new Console(dirname(__DIR__), getenv('APP_WRITE_DIR') ?: null);
$context = 'prod-hal-app';

$code = $console->compile($context);
exit($code === 0 ? $console->phar($context) : $code);
```

このスクリプトはあなたのものです。書くのは、アプリケーションが起動する context（`public/index.php`と同じ語）と、環境変数を読むことだけです。残りは書く必要がありません。アプリケーション名はオートローダーから決まり（`Module/AppModule.php`を持つ名前空間のうち、ソースがアプリケーション直下にあるもの）、何を収めるかはフレームワークの仕事です: `src/`、`public/`、`vendor/`、コンパイルマーカーを含むDIスクリプトが入り、ログ、キャッシュ、`.env`、`autoload.php`、`preload.php`、`tests/`は入りません。`phar.readonly`は子プロセスで処理されるので、iniフラグを覚える必要もありません。

```bash
APP_WRITE_DIR=/tmp php bin/compile.php
```

```text
Compiled: 16 resource classes
Phar: var/build/prod-hal-app.phar (7.5MB, 2100 files)
Boot: APP_WRITE_DIR=/tmp php var/build/prod-hal-app.phar
```

`compile()`と`phar()`は別の段階なので、CIでコンパイルとアーカイブ化を別ジョブに分けられます。`phar()`はディスク上のものを詰めるだけで、コンパイルされていない context や、別の書き込み先向けにコンパイルされたものは拒否します。複数 context のコンパイルはスクリプト内のループです。`preload.php`と`autoload.php`は固定パスに書かれるので、[プロダクション](production.html#compilation)のように context ごとに rename します。

## 動かす

```bash
APP_WRITE_DIR=/tmp php var/build/prod-hal-app.phar get '/index?name=BEAR'
```

スタブがアーカイブの中の`public/index.php`を実行するので、`src/Injector.php`の`dirname(__DIR__)`は`phar:///path/prod-hal-app.phar`になります。エントリポイントは[読み取り専用デプロイ](production.html#writable-paths)のままで、他に変更はありません。

php-fpmが実行するのはアーカイブではなくファイルなので、エントリポイントはアーカイブの隣に置き、オートローダーを中から読みます。

```php
<?php
// index.php （prod-hal-app.pharと同じディレクトリ）
require 'phar://' . __DIR__ . '/prod-hal-app.phar/vendor/autoload.php';

exit((new MyVendor\MyProject\Bootstrap())('prod-hal-api-app', $GLOBALS, $_SERVER, getenv('APP_WRITE_DIR') ?: null));
```

## 守ること2つ

**書き込み先は1つで、ビルドと起動で同じ絶対パスにします。** コンパイル済みスクリプトはコンパイル時のパスを持つので、アーカイブは1つの書き込み先のために作られます。その1箇所が`APP_WRITE_DIR`です。ビルドも起動も`AppModule`もこれを読みます。`AppModule`が書き込み先を使って設定したものは、スクリプトに焼き込まれます。

**`$appMeta->appDir`から実行時のパスを組むバインディングは書きません。** コンパイル済みスクリプトはビルド時の`Meta`を持つため、注入される`appDir`は`phar://…`ではなくビルド時のディレクトリです（`tmpDir`と`logDir`は書き込み先なので正しい値です）。実行時にファイルを読むもの（テンプレートのディレクトリ、データファイルなど）は`__DIR__`を基点にします。`__DIR__`はアーカイブの中を指します。

## インポートしたアプリケーション

アーカイブの中の[インポートしたアプリケーション](import.html)は別のアプリケーションです。`Meta`もコンパイル済みスクリプトも書き込み先も、それぞれのものを持ちます。同じ書き込み先を渡します。

```diff
 $this->install(new ImportAppModule([
-    new ImportApp('greeting', 'ImportVendor\Greeting', 'prod-app')
+    new ImportApp('greeting', 'ImportVendor\Greeting', 'prod-app', getenv('APP_WRITE_DIR') ?: null)
 ]));
```

変更はこれだけです。コンパイルはアプリケーションを起動し、その起動がインポートしたアプリケーションをそれぞれのツリーにコンパイルします（ビルドのログに出る`Compiled DI scripts on demand`がそれです）。DIスクリプトは自動でアーカイブに入ります。インポートしたアプリケーションのディレクトリは起動時に解決されるので、アーカイブの移動に追従します。

## ビルドが止まるとき

以前はデプロイ先で起きていた失敗が、パス入りのメッセージでビルド時に止まります。

| エラー | 意味 |
|---|---|
| `PharNotCompiledException` | その context がコンパイルされていない。`phar()`はディスク上のものを詰めます |
| `PharWritesInsideArchiveException` | ホストまたはインポートしたアプリケーションが、ツリーの中に書く設定でコンパイルされている。`APP_WRITE_DIR`を設定してコンパイルします（インポートしたアプリケーションは`AppModule`でも読みます） |
| `PharWriteDirMismatchException` | インポートしたアプリケーションが、宣言から導かれるのと違うディレクトリ向けにコンパイルされている。ビルドと`AppModule`が別の`APP_WRITE_DIR`を読んでいます |
| `PharImportOutsideTreeException` | インポートしたアプリケーションが、アーカイブにするツリーの外にある |
| `PharEntryNotFoundException` | `public/index.php`がない。別のエントリは`Compiler::phar()`に渡します |

起動時に`APP_WRITE_DIR`なしでアーカイブを開始すると`WriteDirRequiredException`で、ビルドと違う`APP_WRITE_DIR`で開始すると両方のパスを名指しする`PharWriteDirMismatchException`で止まります。

背景: [BEAR.Package#426](https://github.com/bearsunday/BEAR.Package/issues/426)
