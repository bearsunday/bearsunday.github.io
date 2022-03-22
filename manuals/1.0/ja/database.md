---
layout: docs-ja
title: データベース
category: Manual
permalink: /manuals/1.0/ja/database.html
---
# データベース

## ライブラリ

データベースの利用のためのモジュールが用意されています。いずれもSQLのための独立ライブラリです。

* [Ray.AuraSqlModule](database_aura.html)
* [Ray.MediaQuery](database_media.html)

`Ray.AuraSqlModule`はPDO拡張の[Aura.Sql](https://github.com/auraphp/Aura.Sql)と、クエリービルダーの[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)、それにページネーションの機能の提供をするローレベルなモジュールです。
`Ray.MediaQuery`はユーザーが用意したインターフェイスとSQLから、SQL実行オブジェクトを生成しインジェクトする[^doma]高機能なDBアクセスフレームワークです。

[^doma]:JavaのDBアクセスフレームワーク[Doma](https://doma.readthedocs.io/en/latest/basic/#examples)と仕組みが似ています。

## その他

* [DBAL](database_dbal.html)
* [CakeDb](database_cake.html)
* [Ray.QueryModule](https://github.com/ray-di/Ray.QueryModule/blob/1.x/README.ja.md)

`DBAL`はDoctrine、`CakeDB`はCakePHPのDBライブラリです。`Ray.QueryModule`はRay.MediaQueryの以前のライブラリでSQLを無名関数に変換します。

---
