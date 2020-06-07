---
layout: docs-ja
title: インジェクターアップグレードガイド
category: Manual
permalink: /manuals/1.0/ja/upgrade/injector.html
---

# インジェクターアップグレードガイド

## 変更点

[BEAR.Package 1.10](https://github.com/bearsunday/BEAR.Package/releases/tag/1.10.0) では従来の`AppInjector`, `Bootstrap`は`@deprecated`になり、統合された`Injectpr`になりました。

```diff
-AppInjector
-Bootstrap
+Injector
```

`BEAR\Package\Injector::getInstance()`ではコンテキストに応じたインジェクターが渡されます。
プロダクションでは従来のDIのスクリプトファイルを書き出す`ScriptInjector`、開発用ではDIファイルを書き出さない`Ray\Di\Injector`が渡されます。

利用方法は変わりません。

```php
$injector = Injector::getInstance($context);
$instance = $injector->getInsntance($interface, name);
```

## 利点

 * `ScriptInjector`は最初のリクエストでもファイル消去しなくなりより安全になります。
 
 * `Ray\Di\Injector`は`var\tmp`にDIファイルを出力しません。開発時のインジェクションが（特にDockerで）高速になります。

 * コンパイルと実行の環境が違うコンテナ環境にも最適化されました。

 * テストが高速になります。

従来の`AppInjector`ではインジェクターインスタンスの取得を毎回行っていましたが、新しい`Injector`ではシングルトンにテスト間で共用されます。
速度が劇的に改善され、テスト毎のDB接続で接続数が枯渇するような事がありません。

アプリケーションやコンテキストを超えたアクセスが同一メモリ空間で可能になるほど、実装がクリーンに改善されました。
Swooleなど（PHPのシェアドナッシングアーキテクチャではない）のランタイム環境でもより安全、高速に動作します。

## アップグレード方法

### Step 1

`src/Injector.php`にアプリケーションの`Injector`を配置します。`Vendor\Package`を自分のプロジェクト名に変更してください。

```php
<?php
namespace Vendor\Package;

use BEAR\Package\Injector as PackageInjector;
use Ray\Di\InjectorInterface;

final class Injector
{
    private function __construct()
    {
    }

    public static function getInstance(string $context) : InjectorInterface
    {
        return PackageInjector::getInstance(__NAMESPACE__, $context, dirname(__DIR__));
    }
}
```

### Step 2

`bootstrap.php`を変更します。

before:
```diff
-$app = (new Bootstrap)->getApp($name, $context, __DIR__);
+$app = Vendor\Package\Injector::getInstance($context)->getInstance(\BEAR\Sunday\Extension\Application\AppInterface::class);
```

### Step 3

`tests/`で使用してるAppInjectorを変更します。

```diff
-new AppInjector('Vendor\Package', 'test-hal-api-app');
+Vendor\Package\Injector::getInstance('test-hal-api-app');
```

複数アプリケーションのプロジェクトで、他のアプリケーションのインジェクターを取得する場合にはBEAR\PackageのInjectorを使います。

```diff
-new AppInjector('Vendor\Package', 'test-hal-api-app');
+Vendor\Package\Injector::getInstance('Vendor\Package', 'test-hal-api-app', $appDir);
```

FCQN（完全修飾名）の長いクラス名はツールで変換するのが便利です。[^1]

以上です。

## 互換性について

後方互換性は保たれます。`@deprecate`になったクラスも引き続き使用でき、廃止の予定もありません。
BEAR.Sundayはsemverを遵守します。

---

[^1]: `.php_cs.dist`の`global_namespace_import`オプションを有効にして`composer cs-fix`すると変換されます。詳細はPHP-CS-Fixerの[README](https://github.com/FriendsOfPHP/PHP-CS-Fixer) をご覧ください。