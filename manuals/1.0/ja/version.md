---
layout: docs-ja
title: バージョン
category: Manual
permalink: /manuals/1.0/ja/version.html
---

# バージョン

## サポートするPHP

BEAR.SundayはサポートされているPHP([Supported Versions](http://php.net/supported-versions.php))のバージョンのPHPをサポートします。

* `^5.6.0` (古い安定板 28 Aug 2014 - 31 Dec 2018)
* `^7.0.0` (古い安定板 3 Dec 2015 - 3 Dec 2018)
* `^7.1.0` (現在の安定板 1 Dec 2016 - 1 Dec 2019）

End of life ([EOL](http://php.net/eol.php))

* `5.5.*` (21 Jul 2016)

新規のオプションパッケージは現在の安定板をベースに開発されます。機能とパフォーマンスそれにセキュリティの観点から現在の安定板のPHPを使うことを勧めします。

[BEAR.SupportedVersions](https://travis-ci.org/bearsunday/BEAR.SupportedVersions)ではバージョンの詳細を確認できます。

## Semver

BEAR.Sundayは[セマンティックバージョニング](http://
semver.org/lang/ja/)に従います。バージョン番号が`0.1`増えるだけのマイナーバージョンアップではアプリケーションコードの修正は必要ありません。

semverに従っているパッケージでは`composer update`はいつでも行えます。

## ポリシー

サポートするPHPがEOLを迎え、必要とするPHPがメジャーバージョンアップ(`5.6` →`7.0`)しても既存のアプリケーションコードの後方互換性は保たれます。新しいモジュールを使うために必要なPHPのバージョン番号が上がることはあっても、そのために破壊的変更が必要になることはありません。

BEAR.Sundayはコードがクリーンで長期的に利用できることを重視します。

## パッケージのバージョン

フレームワークのバージョンはライブラリのバージョンの固定を行いません。ライブラリはフレームワークのバージョンと無関係にアップデートできます。
