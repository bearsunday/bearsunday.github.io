---
layout: docs-ja
title: バージョン
category: Manual
permalink: /manuals/1.0/ja/version.html
---

# バージョン

## サポートするPHP

BEAR.SundayはサポートされているPHP([Supported Versions](http://php.net/supported-versions.php))のバージョンのPHPをサポートします。

* `7.3` (古い安定板 6 Dec 2018 - 6 Dec 2021)
* `7.4` (古い安定板 28 Nov 2019 - 28 Nov 2022)
* `8.0` (現在の安定板 26 Nov 2020 - 26 Nov 2023)

End of life ([EOL](http://php.net/eol.php))

* `5.5` (21 Jul 2016)
* `5.6` (31 Dec 2018)
* `7.0` (3 Dec 2018)
* `7.1` (1 Dec 2019)
* `7.2` (30 Nov 2020)

新規のオプションパッケージは現在の安定板をベースに開発されます。機能とパフォーマンスそれにセキュリティの観点から現在の安定板のPHPを使うことを勧めします。

[BEAR.SupportedVersions](https://travis-ci.org/bearsunday/BEAR.SupportedVersions)ではバージョンの詳細を確認できます。

## Semver

BEAR.Sundayは[セマンティックバージョニング](http://
semver.org/lang/ja/)に従います。バージョン番号が`0.1`増えるだけのマイナーバージョンアップではアプリケーションコードの修正は必要ありません。

## バージョニング・ポリシー

 * フレームワークのコアパッケージはユーザーコードに変更が必要な破壊的変更(breaking change）を行いません。[^1]

 * サポートするPHPがEOLを迎え、必要とするPHPがメジャーバージョンアップ(`5.6` →`7.0`)してもフレームワークのメジャーバージョンアップは行いません。後方互換性は保たれます。

 * 新しいモジュールを使うために必要なPHPのバージョン番号が上がることはあっても、そのために破壊的変更が行われる事はありません。

 * 破壊的変更を行わないために、古く不要な機能も削除しないで[^3]、新しい機能は（置き換えではなく）常に追加されます。

BEAR.Sundayは堅牢で進化可能[^2]なメンテナンス性の良いコードが**長期的に利用できる**ことを重視します。

## パッケージのバージョン

フレームワークのバージョンはライブラリのバージョンの固定を行いません。ライブラリはフレームワークのバージョンと無関係にアップデートできます。`composer update`で常に依存を最新にする事を勧めます。

---

[^1]: つまりフレームワークはメジャーバージョンアップを行いません。利用ライブラリ（例えばTwig)モジュールはそのライブラリの破壊的変更に合わせてメジャーバージョンアップされます。
[^2]: [https://en.wikipedia.org/wiki/Software_evolution](https://en.wikipedia.org/wiki/Software_evolution)
[^3]: `deprecated`扱いにするだけです。
