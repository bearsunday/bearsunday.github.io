---
layout: docs-ja
title: データベース
category: Manual
permalink: /manuals/1.0/ja/database.html
---

# データベース

データベースの利用のために、問題解決方法の異なった以下のモジュールが用意されています。いずれも[PDO](https://www.php.net/manual/ja/intro.pdo.php)をベースにしたSQLのための独立ライブラリです。

* PDOをextendしたExtendedPdo ([Aura.sql](https://github.com/auraphp/Aura.Sql))
* クエリービルダー ([Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery))
* PHPのインターフェイスとSQL実行を束縛 ([Ray.MediaQuery](database_media.html))

静的なSQLはファイルにすると[^locater]、管理や他のSQLツールでの検証などの使い勝手もよくなります。Aura.SqlQueryは動的にクエリーを組み立てることができますが、その他は基本静的なSQLの実行のためのライブラリです。また、Ray.MediaQueryではSQLの一部をビルダーで組み立てたものに入れ替えることもできます。

[^locater]: [query-locater](https://github.com/koriym/Koriym.QueryLocator)はSQLをファイルとして扱うライブラリです。Aura.Sqlと共に使うと便利です。

## モジュール

必要なライブラリに応じたモジュールをインストールします。

* [Ray.AuraSqlModule](database_aura.html)
* [Ray.MediaQuery](database_media.html)

`Ray.AuraSqlModule`はAura.SqlとAura.SqlQueryを含みます。

`Ray.MediaQuery`はユーザーが用意したインターフェイスとSQLから、SQL実行オブジェクトを生成しインジェクトする[^doma]高機能なDBアクセスフレームワークです。

[^doma]: JavaのDBアクセスフレームワーク[Doma](https://doma.readthedocs.io/en/latest/basic/#examples)と仕組みが似ています。

## その他

* [DBAL](database_dbal.html)
* [CakeDb](database_cake.html)
* [Ray.QueryModule](https://github.com/ray-di/Ray.QueryModule/blob/1.x/README.ja.md)

`DBAL`はDoctrine、`CakeDB`はCakePHPのDBライブラリです。`Ray.QueryModule`はRay.MediaQueryの以前のライブラリでSQLを無名関数に変換します。
