---
layout: docs-ja
title: パッケージ
category: Manual
permalink: /manuals/1.0/ja/package.html
---

# パッケージ

アプリケーションは独立したcomposerパッケージです。

フレームワークは依存として`composer install`しますが、他のアプリケーションも依存パッケージとして使うことができます。

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

 1. コンソール入力またはWebサーバーのルーターファイルがbootファイル`bootstrap.php`を呼び出します。
 3. `bootstrap.php`ではコンテキストに応じたアプリケーションオブジェクト`$app`を作成します。
 4. `$app`に含まれるルーターは外部のリクエストをアプリケーション内部のリソースリクエストに変換します。
 4. リソースでリクエストが実行され結果がクライアントに転送されます。


### bootstrap/
BEARのリソースはコンソール入力とWebの双方からアクセスできます。
呼び出すスクリプトによってコンテキストが変わります。

{% highlight php %}
php bootstrap/api.php options '/self/todo' // APIアクセス
{% endhighlight %}

{% highlight php %}
php bootstrap/web.php get '/todo?id=1' // Webアクセス
{% endhighlight %}

{% highlight php %}
php -S 127.0.0.1bootstrap/api.php // PHPサーバー    
{% endhighlight %}

コンテキストが変わるとアプリケーションの振る舞いが変わります。
ユーザーは独自のコンテキストを作成することができます。

### src/

アプリケーション固有のクラスファイルを設置します。

### var/

`log`,`tmp`フォルダは書き込み可能にします。`var/www`はWebドキュメントの公開エリアです。

## フレームワーク・パッケージ

フレームワークは以下のパッケージから構成されます。

## bear/sunday
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Sunday.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Sunday?branch=1.x)

フレームワークのインターフェイスパッケージです。リファレンスの実装を含みます。

## bear/package
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Package.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Package)


`bear/sunday`を実装したフレームワークの基本パッケージです。

## bear/resource
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=1.x)
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

## bear/middleware
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Middleware/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Middleware/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/bearsunday/BEAR.Middleware/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Middleware/?branch=1.x)
[![Build Status](https://travis-ci.org/bearsunday/BEAR.Middleware.svg?branch=1.x)](https://travis-ci.org/bearsunday/BEAR.Middleware)

PSR7のミドルウエアのためのオプションパッケージです。

## ライブラリ・パッケージ

必要なライブラリ・パッケージは`composer`インストールします。

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

 * [madapaja/twig-module](http://bearsunday.github.io/manuals/1.0/ja/html.html) [Twigテンプレートエンジン](http://twig.sensiolabs.org/)
 * [ray/web-form-module](http://bearsunday.github.io/manuals/1.0/ja/form.html) Webフォーム & バリデーション
 * [ray/aura-web-module](https://github.com/Ray-Di/Ray.AuraWebModule) [Aura.Web](https://github.com/auraphp/Aura.Web)
 * [ray/aura-session-module](https://github.com/ray-di/Ray.AuraSessionModule) [Aura.Session](https://github.com/auraphp/Aura.Session)

### 認証

 * [ray/oauth-module](https://github.com/Ray-Di/Ray.OAuthModule) OAuth
 * [ray/role-module](https://github.com/ray-di/Ray.RoleModule) Zend Acl

### ハイパーメディア

 * [kuma-guy/siren-module](https://github.com/kuma-guy/BEAR.SirenModule) Siren

### 開発

 * [ray/fake-module](https://github.com/shingo-kumagai/Ray.FakeModule) モッキング

## ベンダー・パッケージ

特定のパッケージやツールの組み合わせを１つのパッケージにして再利用することができます。
同じチームで複数のプロジェクトを行う場合のスケルトンになります。

[Koriym.DbAppPackage](https://github.com/koriym/Koriym.DbAppPackage)はマイグレーションツールのPhinxやAuraPHPのコンポーネントなどをセットにして、設定ファイルや実行ファイルを添付したDBを使ったWeb APIアプリ用のパッケージです。
カスタムパッケージを作る時の参考に。

# Semver

BEAR.Sundayはパッケージの依存管理のために[セマンティックバージョニング](http://semver.org/lang/ja/)に従います。

各パッケージのメジャーバージョン番号は「後方互換性を失う」以外の特別な意味はありません。
全体のバージョンをロックする機構を持たず、バージョンアップは個別に行われます。
