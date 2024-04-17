---
layout: docs-ja
title: バージョン
category: Manual
permalink: /manuals/1.0/ja/version.html
---

# バージョン

## サポートするPHP

[![Continuous Integration](https://github.com/bearsunday/BEAR.SupportedVersions/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.SupportedVersions/actions/workflows/continuous-integration.yml)

BEAR.SundayはサポートされているPHP([Supported Versions](http://php.net/supported-versions.php)) のバージョンのPHPをサポートします。

* `8.1` (古い安定板 25 Nov 2021 - 25 Nov 2024)
* `8.2` (古い安定板 8 Dec 2022 - 8 Dec 2025)
* `8.3` (現在の安定板 23 Nov 2022 - 26 Nov 2026)

End of life ([EOL](http://php.net/eol.php))

* `5.5` (21 Jul 2016)
* `5.6` (31 Dec 2018)
* `7.0` (3 Dec 2018)
* `7.1` (1 Dec 2019)
* `7.2` (30 Nov 2020)
* `7.3` (6 Dec 2021)
* `7.4` (28 Nov 2022)
* `8.0` (26 Nov 2023)

新規のオプションパッケージは現在の安定板をベースに開発されます。機能とパフォーマンスそれにセキュリティの観点から現在の安定板のPHPを使うことを勧めします。[BEAR.SupportedVersions](https://github.com/bearsunday/BEAR.SupportedVersions/)のCIで各バージョンのテストが確認できます。

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
