---
layout: docs-ja
title: PSR-7
category: Manual
permalink: /manuals/1.0/ja/psr7.html
---

# PSR-7

[PSR-7 HTTP message interface](https://www.php-fig.org/psr/psr-7/)[^1]を使って、サーバーサイドリクエストの情報を取得したり、BEAR.SundayアプリケーションをPSR-7ミドルウェアとして実行したりすることができます。

## HTTPリクエスト

PHPには[`$_SERVER`](http://php.net/manual/ja/reserved.variables.server.php)や[`$_COOKIE`](http://php.net/manual/ja/reserved.variables.cookies.php)などの[スーパーグローバル](http://php.net/manual/ja/language.variables.superglobals.php)がありますが、それらの代わりに[PSR-7 HTTP message interface](https://www.php-fig.org/psr/psr-7/)を使ってサーバーサイドリクエストの情報（`$_COOKIE`、`$_GET`、`$_POST`、`$_FILES`、`$_SERVER`）を受け取ることができます。

### ServerRequest（サーバーリクエスト全般）

```php
class Index extends ResourceObject
{
    public function __construct(ServerRequestInterface $serverRequest)
    {
        // クッキーの取得
        $cookie = $serverRequest->getCookieParams(); // $_COOKIE
    }
}
```

### アップロードファイル

```php
use Psr\Http\Message\UploadedFileInterface;
use Ray\HttpMessage\Annotation\UploadFiles;

class Index extends ResourceObject
{
    /**
     * @UploadFiles
     */
    public function __construct(array $files)
    {
        // ファイル名の取得
        $file = $files['my-form']['details']['avatar'][0];
        /* @var UploadedFileInterface $file */
        $name = $file->getClientFilename(); // my-avatar3.png
    }
}
```

### URI

```php
use Psr\Http\Message\UriInterface;

class Index extends ResourceObject
{
    public function __construct(UriInterface $uri)
    {
        // ホスト名の取得
        $host = $uri->getHost();
    }
}
```

## PSR-7ミドルウェア

既存のBEAR.Sundayアプリケーションは、特別な変更なしに[PSR-7](http://www.php-fig.org/psr/psr-7/)ミドルウェアとして動作させることができます。

以下のコマンドで`bear/middleware`を追加して、ミドルウェアとして動作させるための[bootstrapスクリプト](https://github.com/bearsunday/BEAR.Middleware/blob/1.x/bootstrap/bootstrap.php)に置き換えます：

```bash
composer require bear/middleware
cp vendor/bear/middleware/bootstrap/bootstrap.php bootstrap/bootstrap.php
```

次にスクリプトの`__PACKAGE__\__VENDOR__`をアプリケーションの名前に変更すれば完了です：

```bash
php -S 127.0.0.1:8080 -t public
```

### ストリーム

ミドルウェアに対応したBEAR.Sundayのリソースは[ストリーム](http://php.net/manual/ja/intro.stream.php)の出力に対応しています。HTTP出力は`StreamTransfer`が標準です。詳しくは[ストリーム出力](http://bearsunday.github.io/manuals/1.0/ja/stream.html)をご覧ください。

### 新規プロジェクト

新規でPSR-7のプロジェクトを始めることもできます：

```bash
composer create-project bear/project my-awesome-project
cd my-awesome-project/
php -S 127.0.0.1:8080 -t public
```

### PSR-7ミドルウェア

* [oscarotero/psr7-middlewares](https://github.com/oscarotero/psr7-middlewares)

---

[^1]: [PSR-7 HTTP message interfaces 日本語訳（by RitoLabo）](https://www.ritolab.com/entry/102)
