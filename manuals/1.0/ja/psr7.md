---
layout: docs-ja
title: PSR7
category: Manual
permalink: /manuals/1.0/ja/psr7.html
---

## ミドルウエア

既存のBEAR.Sundayアプリケーションは特別な変更無しに[PSR7](http://www.php-fig.org/psr/psr-7/)ミドルウエアとして動作させることができます。

以下のコマンドで`bear/middleware`を追加して、ミドルウエアとして動作させるためのbootstrapスクリプトに置き換えます。

{% highlight bash %}
composer require bear/middleware
cp vendor/bear/middleware/bootstrap/bootstrap.php bootstrap/bootstrap.php
{% endhighlight %}

{% highlight bash %}
{% endhighlight %}

通常のBEAR.Sundayアプリケーションと同様に公開Webディレクトリとして`var/www`を指定します。

{% highlight bash %}
php -S 127.0.0.1:8080 -t var/www
{% endhighlight %}

## ストリーム

ミドルウエアに対応したBEAR.Sundayのリソースは[ストリーム](http://php.net/manual/ja/intro.stream.php)の出力に対応しています。

以下のスクリプトは`image.jpg`画像ファイルをbase64エンコードしてHTTP出力します。

{% highlight bash %}
<?php
    public function onGet($name = 'BEAR.Sunday')
    {
        $fp = fopen(__DIR__ . '/image.jpg', 'r');
        stream_filter_append($fp, 'convert.base64-encode');
        $this['greeting'] = 'Hello ' . $name;
        $this['image'] = $fp; // image in base64 format

        return $this;
    }
{% endhighlight %}

$this['image']には[fopen](http://php.net/manual/ja/function.fopen.php)のファイルポインタリソースがアサインされているだけですが、
他でアサインされた文字列（$this['greeting']）を含めて全てストリームに変換されて出力されます。

HTTP出力がストリーム出力に完全に対応していればPHPのメモリ制限を受けないで巨大なファイルを出力することができます。
大きなサイズのファイルダウンロードに向いています。

## 新規プロジェクト

新規でPSR7のプロジェクトを始める場合のパッケージが用意されています。

{% highlight bash %}
composer create-project bear/project my-awesome-project
cd my-awesome-project/
php -S 127.0.0.1:8080 -t var/www/
{% endhighlight %}

必要に応じて他のPSR7ミドルウエアを追加したり、Rayのモジュールを追加します。

 * [oscarotero/psr7-middlewares](https://github.com/oscarotero/psr7-middlewares)
 * [Packages from Ray](https://packagist.org/packages/ray/)
 * [Packages from BEAR](https://packagist.org/packages/bear/)
