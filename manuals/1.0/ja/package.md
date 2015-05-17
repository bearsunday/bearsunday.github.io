---
layout: docs-ja
title: パッケージ
category: Manual
permalink: /manuals/1.0/ja/package.html
---

# パッケージ

BEAR.Sundayはライブラリやフレームワークは機能別に独立した`composer`のパッケージです。フレームワークのパッケージはアプリケーションの依存として`composer install`されます。

## アプリケーション・パッケージ

アプリケーションもパッケージです。

```
├── bootstrap 
│   ├── api.php 
│   ├── bootstrap.php
│   └── web.php
├── composer.json
├── composer.lock
├── phpunit.xml.dist
├── src
│   ├── (Annotation)
│   ├── (Interceptor)
│   ├── Module
│   └── Resource
├── tests
│   ├── bootstrap.php
│   └── tmp
├── var
│   ├── (conf)
│   ├── log
│   ├── tmp
│   └── www
└── vendor

*カッコで囲まれたフォルダは必要があれば作成します

```
### bootstrap/
`bootstrap`フォルダのスクリプトはユーザーが直接コンソールで実行またはPHPサーバーのスクリプトとしてアクセスします。

{% highlight php %}
php bootstrap/api.php options 'app://self/todo' // APIアクセス
{% endhighlight %}

{% highlight php %}
php bootstrap/web.php get '/todo?id=1' // Webアクセス
{% endhighlight %}

{% highlight php %}
php -S 127.0.0.1bootstrap/api.php // PHPサーバー    
{% endhighlight %}

### src/

アプリケーション固有のクラスファイルを設置します。（共通クラスは別のパッケージにします）

### var/
`log`,`tmp`フォルダは書き込み可能にします。`var/www`はWebドキュメントの公開エリアです。

## フレームワーク・パッケージ

## bear/package
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Package.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Package)


`bear/sunday`を実装したフレームワークの基本パッケージです。

## bear/sunday 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Sunday.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Sunday?branch=1.x)

フレームワークのインターフェイスパッケージです。リファレンスの実装を含みます。

## bear/resource
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/quality-score.png?b=1.x)]
(https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=1.x) [![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=develop-2)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Resource.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Resource)

PHPのオブジェクトをRESTサービスとして使用するRESTフレームワークです。

## ray/di
 [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/)
 [![Code Coverage](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/)
 [![Build Status](https://secure.travis-ci.org/ray-di/Ray.Di.png?b=2.x)](http://travis-ci.org/ray-di/Ray.Di)
  
Google GuiceスタイルのDIフレームワークです。

## ray/aop
 [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/)
 [![Code Coverage](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/)
 [![Build Status](https://secure.travis-ci.org/ray-di/Ray.Aop.png?b=2.x)](http://travis-ci.org/ray-di/Ray.Aop) 
 
AOPアライアンスに準拠したAOPフレームワークです。


以上が主なパッケージです。他にユーザーがオプションでインストールできるモジュールが[BEAR.Sundayパッケージ](https://github.com/bearsunday)や[Ray.Diパッケージ](https://github.com/ray-di)にも用意されていて、
`AppModule`でインストールすることができます。

## Semver

BEAR.Sundayはパッケージの依存管理のために[セマンティックバージョニング](http://semver.org/lang/ja/)に従います。

各パッケージのメジャーバージョン番号は「後方互換性を失う」以外の特別な意味はありません。
各パッケージはsemverを守り、独立して個別にバージョンアップが行われます。全てのパッケージのバージョンセットを管理するものはアプリケーションの`composer.lock`のみです。

フレームワーク全体で互換性を失うようなメジャーバージョンアップは計画にありません。`BEAR.Sunday`のために作成したリソースやクラスの後方互換性を最大限保護したいと考えています。

