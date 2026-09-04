---
layout: docs-ja
title: Phar
category: Manual
permalink: /manuals/1.0/ja/phar.html
---

# Phar

[Phar](https://www.php.net/manual/ja/intro.phar.php)はアプリケーションを1ファイルにしたものです。コード、`vendor/`、コンパイル済みDIスクリプトが1つのアーカイブに収まります。アプリケーションはアーカイブに書き込みません。デプロイは1ファイルのコピーで済みます。BEAR.Package 1.24以降が必要です。

```text
app.phar                                            アプリケーション、vendor/、コンパイル済みDIスクリプト
{一時ディレクトリ}/MyVendor/MyProject/{appDirハッシュ}/var   実行時に書き込むもの（tmpとlog）
```

## ReadOnlyAppModule

`ReadOnlyAppModule`をインストールすると、実行時の書き込み（`var/tmp`と`var/log`）がアプリケーションディレクトリの外に移り、アプリケーションディレクトリは読み込みだけになります。

```php
<?php
// src/Module/ProdModule.php
namespace MyVendor\MyProject\Module;

use BEAR\Package\Context\ProdModule as PackageProdModule;
use BEAR\Package\Module\ReadOnlyAppModule;
use Ray\Di\AbstractModule;

class ProdModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new ReadOnlyAppModule());
        $this->install(new PackageProdModule());
    }
}
```

引数を省略したときは、起動したマシンの一時ディレクトリ（[`sys_get_temp_dir()`](https://www.php.net/sys_get_temp_dir)）の下に`var/tmp`と`var/log`が作られます。パスにはアプリケーションディレクトリのハッシュが含まれるため、同じアプリケーションを別の場所にチェックアウトしてもキャッシュは共有されません。

```text
{一時ディレクトリ}/MyVendor/MyProject/{appDirハッシュ}/var/tmp/prod-hal-app
{一時ディレクトリ}/MyVendor/MyProject/{appDirハッシュ}/var/log/prod-hal-app
```

通常は`sys_get_temp_dir()`が書き込み可能な一時ディレクトリを返すので、設定は不要です。
変えたい場合は、php.iniの`sys_temp_dir`か環境変数`TMPDIR`で指定します。
プロジェクトで固定したい場合は、パスを渡すとその通りに使われます。

```php
$this->install(new ReadOnlyAppModule('/var/tmp/myapp', '/var/log/myapp'));
```

片方だけ渡すこともできます。渡さなかった方は起動時に`sys_get_temp_dir()`で決定されます。

```php
$this->install(new ReadOnlyAppModule(logDir: '/var/log/myapp'));
```

## Pharにする

ビルドスクリプトでは、コンパイルの後に`phar()`メソッドを呼んでpharファイルを作ります。

```php
<?php
// bin/compile.php
use BEAR\Package\Compiler;

require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('memory_limit', '-1');

$context = 'prod-hal-app';

$compiler = new Compiler('MyVendor\MyProject', $context, dirname(__DIR__));
$code = $compiler();

exit($code === 0 ? $compiler->phar() : $code); // コンパイルだけなら exit($code);
```

このスクリプトに必要なのは、アプリケーション名、起動する context（`public/index.php`と同じもの）、アプリケーションのディレクトリです。残りは書く必要がありません。
この時何をアーカイブするか（pharファイルに収めるか）はフレームワークの仕事です: アーカイブに入るのは名前の決まったトップレベルのディレクトリだけで、`src`、`public`、`bin`、`vendor`、`var`、そしてインポートしたアプリケーションの置かれた場所です。`var/`のうち入るのはこのビルドの`var/build/{context}`だけで、そこにはコンパイルマーカーを含むDIスクリプトと、[compile step](production.html#compile-steps)が書いたものが入ります。`var/log`と`var/tmp`は入りません。`.env`、`autoload.php`、`tests/`も同じです。ルート直下のファイルで入るのは`preload.php`だけです。残ったディレクトリは`Not packed:`としてコンパイル後に表示されます。マーカーは`.bear-compile.json`（`app`、`context`、`time`）で、`phar()`はこれを見てコンパイル済みのビルドかどうかを判断します。`.env`ファイル自体は入りませんが、その値をインスタンス束縛していればDIスクリプトに記録され、アーカイブに含まれます。その場合はアーカイブも秘密情報として扱う必要があります。`phar.readonly`は子プロセスで処理されるので、iniフラグを覚える必要もありません。

```bash
php bin/compile.php
```

```text
Compiled: 16 resource classes
Phar: /app/app.phar (7.5MB, 2100 files)
Not packed: tests
```

`phar()`はディスク上にあるコンパイル結果からアーカイブを作ります。引数はエントリポイントのパス1つで、省略時は`public/index.php`です。コンパイルされていない context や、`ReadOnlyAppModule`なしでコンパイルしたビルドは、アーカイブにする前にエラーで止まります（[ビルド時のエラー](#when-the-build-stops)）。

`app.phar`は固定パスに書かれ、次の`phar()`で上書きされます。複数の context をPharにするときは、1つ作るごとにファイル名を変えます。

## 実行する

```bash
php app.phar get '/index?name=BEAR'
```

Pharのスタブがアーカイブ内の`public/index.php`を実行します。このとき`src/Injector.php`の`dirname(__DIR__)`は`phar:///path/app.phar`になります。エントリポイントのコードは[Read-only deployment](production.html#writable-paths)と同じで、変更は必要ありません。

php-fpmはアーカイブを直接実行できないので、エントリポイントをアーカイブと同じディレクトリに置き、そこからアーカイブ内のオートローダーを読み込みます。

```php
<?php
// index.php （app.pharと同じディレクトリ）
require 'phar://' . __DIR__ . '/app.phar/vendor/autoload.php';

exit((new MyVendor\MyProject\Bootstrap())('prod-hal-app', $GLOBALS, $_SERVER));
```

`preload.php`はアーカイブに含まれるので、`opcache.preload`にはアーカイブ内のパスを指定します。

```ini
opcache.preload=phar:///path/to/app.phar/preload.php
```

`autoload.php`はアーカイブに入りません。preloadを使う場合、[autoload.phpは不要になる](production.html#autoloadphp)からです。コンパイルが書いた`preload.php`をアーカイブの隣に置いて使うことはできません。`preload.php`のrequireは自身のディレクトリからの相対パスで書かれていて、アーカイブの外には`vendor/`がないため、起動時に`Failed opening required '…/vendor/autoload.php'`で失敗します。また`preload.php`はコンパイルごとに`{appDir}/preload.php`に上書きされるので、`phar()`は最後にコンパイルした context に対して実行します。別の context の`preload.php`が残っていると`PharPreloadForAnotherBuildException`になります。

## PHPを含んだワンバイナリ {#one-binary}

static-php-cliは`php`と`php-fpm`を作ります。ふつうにインストールするのと同じ実行ファイルで、違うのは共有ライブラリに依存しないことだけです。ホストにPHPをインストールする必要も、ディストリビューションのPHPバージョンに合わせる必要もありません。ビルド時には`phar`拡張と、アプリケーションが使う拡張を含めます。

```bash
./spc download --for-extensions=phar,opcache -P
./spc build phar,opcache --build-cli --build-fpm
```

実行方法は同じです。ホストの`php`の代わりにこのバイナリを使います。

```bash
./buildroot/bin/php app.phar get '/index?name=BEAR'
```

php-fpmを使う場合は、ホストのphp-fpmで使っている`php-fpm.conf`を`-y`で渡して`./buildroot/bin/php-fpm`を起動します。エントリポイントの置き方も、opcacheと`opcache.preload`の設定も、ホストのPHPと同じです。

### 1つの実行ファイル（CLI） {#one-executable}

コマンドラインのアプリケーションなら、アーカイブとPHPを1ファイルにできます。`--build-micro`はmicro SAPIを`micro.sfx`としてビルドします。後ろに付け足されたものを実行するPHPです。アーカイブを付け足します。

```bash
cat micro.sfx app.phar > myapp
chmod +x myapp
./myapp get '/index?name=BEAR'
```

できるのは、PHPとその拡張、アプリケーションとコンパイル済みDIスクリプトを持った1つの実行ファイルです。名前を変えても、同じプラットフォームの別のマシンにコピーしても、どのディレクトリから起動しても動き、書き込むのは[ReadOnlyAppModule](#readonlyappmodule)が決めた場所だけです。micro SAPIは1つのスクリプトを実行して終了するだけで、リクエストを処理するFPM相当のものはありません。Webのエントリポイントはphp-fpmと、その隣のアーカイブのままです。これはコマンドや[BEAR.Cli](cli.html)のツール向けです。PHPバージョンとプラットフォームごとのビルド済み`micro.sfx`は[dl.static-php.dev](https://dl.static-php.dev/static-php-cli/common/)にあります。

## 別の場所へのコピー

アーカイブは実行に必要なものをすべて含んでいるので、別のディレクトリや別のマシンにコピーしてそのまま起動できます。元のプロジェクトディレクトリを参照することはないので、ビルド後に削除しても問題ありません。

```bash
cp app.phar /srv/releases/2026-08-23.phar
php /srv/releases/2026-08-23.phar get '/index?name=BEAR'
```

ファイル名を変えても、別のディレクトリから実行しても同じように動きます。

## 注意点

**モジュールの`configure()`で実行時のファイルパスを決めることはできません。** コンパイル済みのアプリケーションでは、`configure()`はコンパイル時に一度だけ実行され、実行時には呼ばれません。`configure()`で計算した値はDIスクリプトに文字列として記録されます。出どころが`$this->appMeta->appDir`でも`__DIR__`でも同じです。テンプレートやSQLファイルなど実行時に読むファイルのパスは、それを読むクラスの中で`__DIR__`から組むか、Providerでリクエスト時に決めてください。`tmpDir`と`logDir`は`ReadOnlyAppModule`が決める書き込み先なので、そのまま使えます。

## インポートしたアプリケーション

[インポートしたアプリケーション](import.html)もアーカイブに含まれます。インポートしたアプリケーションは独立したアプリケーションとして、自身の`Meta`とコンパイル済みスクリプトを持ちます。インポートするためのコード変更は必要ありません。

```php
$this->install(new ImportAppModule([
    new ImportApp('greeting', 'ImportVendor\Greeting', 'prod-app')
]));
```

ホストアプリケーションをコンパイルすると、その過程でインポートしたアプリケーションもそれぞれのディレクトリにコンパイルされます（ビルドログの`Compiled DI scripts on demand`がこれにあたります）。生成されたDIスクリプトは自動的にアーカイブに含まれます。

インポートしたアプリケーションの書き込み先はホストの設定を引き継がないので、それぞれの`ProdModule`で`ReadOnlyAppModule`をインストールします。

```php
<?php
// imports/greeting/src/Module/ProdModule.php
$this->install(new ReadOnlyAppModule());
```

```text
{一時ディレクトリ}/ImportVendor/Greeting/{appDirハッシュ}/var/tmp/prod-app
{一時ディレクトリ}/ImportVendor/Greeting/{appDirハッシュ}/var/log/prod-app
```

インストールしていないと、インポートしたアプリケーションは自身のディレクトリ、つまりアーカイブの中に書き込む設定になります。この場合`phar()`は、該当するアプリケーション名を含む`PharWritesInsideArchiveException`で停止します。

## ビルド時のエラー {#when-the-build-stops}

次のエラーはビルド時に検出されます。メッセージには該当するパスが含まれます。

| エラー | 意味 |
|---|---|
| `PharNotCompiledException` | その context がコンパイルされていない。先に`$compiler()`を実行します |
| `PharPreloadForAnotherBuildException` | アプリケーションルートの`preload.php`が別の context のもの。最後にコンパイルした context に対して`phar()`を実行します |
| `PharImportsUnreadableException` | コンパイル済みコンテナのimport宣言が、このバージョンのBEAR.Packageでは読めない形式。同じバージョンで再コンパイルします |
| `PharWritesInsideArchiveException` | ホストまたはインポートしたアプリケーションが、アプリケーションディレクトリの中に書き込む設定でコンパイルされている。`ReadOnlyAppModule`をインストールして再コンパイルします |
| `PharImportOutsideTreeException` | インポートしたアプリケーションが、アプリケーションディレクトリの外にある |
| `PharEntryNotFoundException` | `public/index.php`がない。別のファイルをエントリにする場合は`phar()`の引数で指定します |
| `PharEntryNotPackedException` | エントリに指定したファイルがアーカイブに含まれない。アプリケーションルート直下のファイルで含まれるのは`preload.php`だけです |
| `PharStaleOutputException` | 出力先に前回のアーカイブが残っていて、削除できなかった |
| `PharSymlinkedDirectoryException` | アプリケーションディレクトリ内にシンボリックリンクのディレクトリがあり、`Phar`に追加できない |

コンパイルされていない状態でアーカイブを起動すると`NotCompiledException`になります。アーカイブには書き込めないので、起動時にコンパイルすることはできません。

このアーカイブをブラウザの中で動かすには[Wasm](wasm.html)を参照してください。動くデモは[koriym/wasm-todo](https://github.com/koriym/wasm-todo)（[公開ページ](https://koriym.github.io/wasm-todo/)）です。

背景: [BEAR.Package#426](https://github.com/bearsunday/BEAR.Package/issues/426)
