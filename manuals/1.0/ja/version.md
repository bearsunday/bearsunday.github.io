---
layout: docs-ja
title: バージョン
category: Manual
permalink: /manuals/1.0/ja/version.html
---

# バージョン

## サポートするPHP

[![Continuous Integration](https://github.com/bearsunday/BEAR.SupportedVersions/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/bearsunday/BEAR.SupportedVersions/actions/workflows/continuous-integration.yml)

BEAR.SundayはPHPの公式サポート期間([Supported Versions](http://php.net/supported-versions.php))に準じてPHPバージョンをサポートしています。

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

## Semver

BEAR.Sundayは[セマンティックバージョニング](http://semver.org/lang/ja/)に従います。マイナーバージョンアップ（バージョン番号が`0.1`増加）ではアプリケーションコードの修正は不要です。

## バージョニング・ポリシー

* フレームワークのコアパッケージは破壊的変更を行いません。[^1]
* PHPのサポート要件が変更され、必要なPHPバージョンが上がっても（例：`5.6`→`7.0`）、フレームワークのメジャーバージョンアップは行いません。後方互換性は維持されます。
* 新しいモジュールの導入によりPHPバージョンの要件が上がることはありますが、それに伴う破壊的変更は行いません。
* 後方互換性維持のため、古い機能は削除せず[^3]、新機能は既存機能の置き換えではなく追加として実装されます。

BEAR.Sundayは堅牢で進化可能[^2]な、長期的な保守性を重視したフレームワークを目指しています。

## パッケージのバージョン

フレームワークは依存ライブラリのバージョンを固定しません。ライブラリはフレームワークのバージョンに関係なくアップデート可能です。`composer update`による定期的な依存関係の更新を推奨します。

---

[^1]: フレームワーク自体はメジャーバージョンアップを行いません。ただし、利用ライブラリ（例：Twig）のモジュールは、そのライブラリの破壊的変更に合わせてメジャーバージョンアップされることがあります。
[^2]: [https://en.wikipedia.org/wiki/Software_evolution](https://en.wikipedia.org/wiki/Software_evolution)
[^3]: 代わりに`deprecated`として扱います。
