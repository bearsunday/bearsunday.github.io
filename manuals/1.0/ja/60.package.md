---
layout: docs-ja
title: パッケージ
category: Manual
permalink: /manuals/1.0/ja/package.html
---

# パッケージ

アプリケーションは独立したcomposerパッケージです。

フレームワークは依存として`composer install`でインストールしますが、他のアプリケーションも依存パッケージとして使うことができます。

## アプリケーション・パッケージ

### 構造

BEAR.Sundayアプリケーションのファイルレイアウトは[php-pds/skeleton](https://github.com/php-pds/skeleton)に準拠しています。

### bin/

実行可能なコマンドを設置します。

BEARのリソースはコンソール入力とWebの双方からアクセスできます。
使用するスクリプトによってコンテキストが変わります。

```bash
php bin/app.php options '/todos' # APIアクセス（appリソース）
```

```bash
php bin/page.php get '/todos?id=1' # Webアクセス（pageリソース）
```

```bash
php -S 127.0.0.1 bin/app.php # PHPサーバー
```

コンテキストが変わるとアプリケーションの振る舞いが変わります。
ユーザーは独自のコンテキストを作成することができます。

### src/

アプリケーション固有のクラスファイルを設置します。

### public/

Web公開フォルダです。

### var/

`log`、`tmp`フォルダは書き込み可能にします。`var/www`はWebドキュメントの公開エリアです。
`conf`など可変のファイルを設置します。

## 実行シーケンス

1. コンソール入力（`bin/app.php`、`page.php`）またはWebサーバーのエントリーファイル（`public/index.php`）が`bootstrap.php`を実行します。
2. `bootstrap.php`では実行コンテキストに応じたルートオブジェクト`$app`を作成します。
3. `$app`に含まれるルーターは外部のHTTPまたはCLIリクエストをアプリケーション内部のリソースリクエストに変換します。
4. リソースリクエストが実行され、結果がクライアントに転送されます。

## フレームワーク・パッケージ

フレームワークは以下のパッケージから構成されます。

### ray/aop
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/)
[![codecov](https://codecov.io/gh/ray-di/Ray.Aop/branch/2.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/ray-di/Ray.Aop)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Aop/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Aop)
[![Continuous Integration](https://github.com/ray-di/Ray.Aop/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ray-di/Ray.Aop/actions/workflows/continuous-integration.yml)

Javeの [AOPアライアンス](http://aopalliance.sourceforge.net/) に準拠したAOPフレームワークです。

### ray/di
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/)
[![codecov](https://codecov.io/gh/ray-di/Ray.Di/branch/2.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/ray-di/Ray.Di)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Di/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Di)
[![Continuous Integration](https://github.com/ray-di/Ray.Di/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ray-di/Ray.Di/actions/workflows/continuous-integration.yml)

[google/guice](https://github.com/google/guice) スタイルのDIフレームワークです。`ray/aop`を含みます。

### bear/resource
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Resource/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Resource/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/bearsunday/BEAR.Resource)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Resource/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Resource)
[![Continuous Integration](https://github.com/bearsunday/BEAR.Resource/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.Resource/actions/workflows/continuous-integration.yml)

### bear/sunday
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Sunday/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Sunday/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/bearsunday/BEAR.Sunday)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Sunday/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Sunday)
[![Continuous Integration](https://github.com/bearsunday/BEAR.Sunday/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.Sunday/actions/workflows/continuous-integration.yml)

フレームワークのインターフェイスパッケージです。`bear/resource`を含みます。

### bear/package
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Package/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/bearsunday/BEAR.Pacakge)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Package/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Package)
[![Continuous Integration](https://github.com/bearsunday/BEAR.Package/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.Package/actions/workflows/continuous-integration.yml)

`bear/sunday`の実装パッケージです。`bear/sunday`を含みます。

## ライブラリ・パッケージ

必要なライブラリ・パッケージを`composer`でインストールします。

| **Category** | **Composer package** | **Library**
| ルーター |
| |[bear/aura-router-module](https://github.com/bearsunday/BEAR.AuraRouterModule) | [Aura.Router v2](https://github.com/auraphp/Aura.Router/tree/2.x) |
| データベース |
|| [ray/media-query](https://github.com/ray-di/Ray.MediaQuery) |
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

特定のパッケージやツールの組み合わせをモジュールだけのパッケージにして再利用し、同様のプロジェクトのモジュールを共通化することができます。[^1]

## Semver

すべてのパッケージは[セマンティックバージョニング](https://semver.org/lang/ja/)に従います。マイナーバージョンアップでは後方互換性が破壊されることはありません。

---

[^1]: 参考モジュール [Koriym.DbAppPackage](https://github.com/koriym/Koriym.DbAppPackage)
