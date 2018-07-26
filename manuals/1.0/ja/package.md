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

BEAR.Sundayアプリケーションのファイルレイアウトは[php-pds/skeleton](https://github.com/php-pds/skeleton)に準拠しています。

```
├── (bin)
├── bootstrap
│   ├── api.php
│   ├── bootstrap.php
│   └── web.php
├── composer.json
├── composer.lock
├── phpunit.xml.dist
├── public
│   └── index.php
├── src
│   ├── (Annotation)
│   ├── (Interceptor)
│   ├── Module
│   └── Resource
├── tests
│   ├── (Fake)
│   ├── bootstrap.php
│   └── tmp
├── var
│   ├── (conf)
│   ├── log
│   └── tmp
└── vendor
```


### 実行シークエンス

 1. コンソール入力またはWebサーバーのルーターファイルがbootファイル`bootstrap.php`を呼び出します。
 3. `bootstrap.php`ではコンテキストに応じたアプリケーションオブジェクト`$app`を作成します。
 4. `$app`に含まれるルーターは外部のリクエストをアプリケーション内部のリソースリクエストに変換します。
 4. リソースでリクエストが実行され結果がクライアントに転送されます。


### bootstrap/
BEARのリソースはコンソール入力とWebの双方からアクセスできます。
呼び出すスクリプトによってコンテキストが変わります。

```bash
php bootstrap/api.php options '/self/todo' # APIアクセス
```

```bash
php bootstrap/web.php get '/todo?id=1' # Webアクセス
```

```bash
php -S 127.0.0.1 bootstrap/api.php # PHPサーバー
```

コンテキストが変わるとアプリケーションの振る舞いが変わります。
ユーザーは独自のコンテキストを作成することができます。

### bin/

スクリプトで実行可能なコマンドを設置します。

### src/

アプリケーション固有のクラスファイルを設置します。

### publc/

Web公開フォルダ

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

PSR-7のミドルウエアのためのオプションパッケージです。

## ライブラリ・パッケージ

必要なライブラリ・パッケージを`composer`インストールします。

| **Category** | **Composer package** | **Library**
| ルーター |
| |[bear/aura-router-module](https://github.com/bearsunday/BEAR.AuraRouterModule) | [Aura.Router v2](https://github.com/auraphp/Aura.Router/tree/2.x) |
| データベース |
|| [ray/aura-sql-module](https://github.com/ray-di/Ray.AuraSqlModule) | [Aura.Sql v2](https://github.com/auraphp/Aura.Sql/tree/2.x)
|| [ray/dbal-module](https://github.com/ray-di/Ray.DbalModule) | [Doctrine DBAL](https://github.com/doctrine/dbal)
|| [ray/cake-database-module](https://github.com/ray-di/Ray.CakeDbModule) | [CakePHP v3 database](https://github.com/cakephp/database)
|| [ray/doctrine-orm-module](https://github.com/kawanamiyuu/Ray.DoctrineOrmModule) | [Doctrine ORM](https://github.com/doctrine/doctrine2)
| ストレージ |
||[bear/query-repository](https://github.com/bearsunday/BEAR.QueryRepository) | 読み書きリポジトリの分離（デフォルト）
||[bear/query-module](https://github.com/ray-di/Ray.QueryModule) | DBやWeb APIなどの外部アクセスの分離
| Web |
| |[madapaja/twig-module](http://bearsunday.github.io/manuals/1.0/ja/html.html) | [Twigテンプレートエンジン](http://twig.sensiolabs.org/)
| |[ray/web-form-module](http://bearsunday.github.io/manuals/1.0/ja/form.html) | Webフォーム & バリデーション
| |[ray/aura-web-module](https://github.com/Ray-Di/Ray.AuraWebModule) | [Aura.Web](https://github.com/auraphp/Aura.Web)
| |[ray/aura-session-module](https://github.com/ray-di/Ray.AuraSessionModule) | [Aura.Session](https://github.com/auraphp/Aura.Session)
| |[ray/symfony-session-module](https://github.com/kawanamiyuu/Ray.SymfonySessionModule) | [Symfony Session](https://github.com/symfony/http-foundation/tree/master/Session)
| バリデーション |
| |[ray/validate-module](https://github.com/ray-di/Ray.ValidateModule) | [Aura.Filter](https://github.com/auraphp/Aura.Filter)
| |[satomif/extra-aura-filter-module](https://github.com/satomif/ExtraAuraFilterModule) | [Aura.Filter](https://github.com/auraphp/Aura.Filter)
| 認証 |
| |[ray/oauth-module](https://github.com/Ray-Di/Ray.OAuthModule) | OAuth
| |[kuma-guy/jwt-auth-module](https://github.com/kuma-guy/BEAR.JwtAuthModule) | JSON Web Token
| |[ray/role-module](https://github.com/ray-di/Ray.RoleModule) | [Zend Acl](https://github.com/zendframework/zend-permissions-acl)　 Zend Acl
| |[bear/acl-resource](https://github.com/bearsunday/BEAR.AclResource) | ACLベースのエンベドリソース
| ハイパーメディア |
| |[kuma-guy/siren-module](https://github.com/kuma-guy/BEAR.SirenModule) | Siren
|  開発 |
| |[ray/test-double](https://github.com/ray-di/Ray.TestDouble) | テストダブル
|  非同期ハイパフォーマンス |
| |[MyVendor.Swoole](https://github.com/bearsunday/MyVendor.Swoole) | [Swoole](https://github.com/swoole/swoole-src)

## ベンダー・パッケージ

特定のパッケージやツールの組み合わせを１つのパッケージにして再利用することができます。
同じチームで複数のプロジェクトを行う場合のスケルトンになります。

[Koriym.DbAppPackage](https://github.com/koriym/Koriym.DbAppPackage)はマイグレーションツールのPhinxやAuraPHPのコンポーネントなどをセットにして、設定ファイルや実行ファイルを添付したDBを使ったWeb APIアプリ用のパッケージです。
カスタムパッケージを作る時の参考に。
