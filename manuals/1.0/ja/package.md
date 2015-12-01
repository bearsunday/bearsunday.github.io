---
layout: docs-ja
title: パッケージ
category: Manual
permalink: /manuals/1.0/ja/package.html
---

# パッケージ

BEAR.Sundayは機能別に独立した`composer`のパッケージで構成されています。
アプリケーションもパッケージです。フレームワークは依存として`composer install`します。

## アプリケーション・パッケージ

### 構造

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
### 実行シークエンス

 1. `bootstrap/`または`var/www/`のbootファイルをコンソールまたはWebサーバーのルーターファイルとして呼び出します。
 2. bootファイルは実行コンテキストを指定してbootします。設定のため`Module/`のモジュールが使われます。
 3. `bootstrap.php`でルーターは外部のリクエストをアプリケーション内部のリソースリクエストに変換します。
 4. `/Resource`のリソースでリクエストが実行され結果がクライアントに転送されます。


### bootstrap/
`bootstrap`フォルダのスクリプトはユーザーが直接コンソールで実行するか、またはPHPサーバーのスクリプトとしてアクセスします。

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

# フレームワーク・パッケージ

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

## bear/middleware
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Middleware/badges/quality-score.png?b=1.x)]
(https://scrutinizer-ci.com/g/bearsunday/BEAR.Middleware/?branch=1.x) [![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Middleware/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=develop-2)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Middleware.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Middleware)

PSR7のミドルウエアのためのモジュールです。

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

# ライブラリ・パッケージ

必要なライブラリ・パッケージはcomposerで個別にインストールします。

例 CakePHPデータベースのインストール

{% highlight bash %}
composer require ray/cake-database-module
{% endhighlight %}

### ルーター

 *  [bear/aura-router-module](https://github.com/bearsunday/BEAR.AuraRouterModule) Aura.Router v2

### データベース

 * [ray/aura-sql-module](https://github.com/ray-di/Ray.AuraSqlModule) Aura.Sql v2
 * [ray/dbal-module](https://github.com/ray-di/Ray.DbalModule) Doctrine DBAL
 * [ray/cake-database-module](https://github.com/ray-di/Ray.CakeDbModule) CakePHP v3 database

### ストレージ

 * [bear/query-repository](https://github.com/bearsunday/BEAR.QueryRepository) r/wリポジトリの分離

### Web

 * [madapaja/twig-module](http://bearsunday.github.io/manuals/1.0/ja/html.html) Twig テンプレートエンジン
 * [ray/web-form-module](http://bearsunday.github.io/manuals/1.0/ja/form.html) Webフォーム & バリデーション
 * [ray/aura-web-module](https://github.com/Ray-Di/Ray.AuraWebModule) Aura.Web

### 認証

 * [ray/oauth-module](https://github.com/Ray-Di/Ray.OAuthModule) OAuth
 * [ray/role-module](https://github.com/ray-di/Ray.RoleModule) Zend Acl

### ハイパーメディア

 * [kuma-guy/siren-module](https://github.com/kuma-guy/BEAR.SirenModule) Siren

### 開発

 * [ray/fake-module](https://github.com/shingo-kumagai/Ray.FakeModule) モッキング

# Semver

BEAR.Sundayはパッケージの依存管理のために[セマンティックバージョニング](http://semver.org/lang/ja/)に従います。

各パッケージのメジャーバージョン番号は「後方互換性を失う」以外の特別な意味はありません。
全体のバージョンをロックする機構を持たず、バージョンアップは個別に行われます。
