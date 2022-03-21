---
layout: docs-ja
title: データベース
category: Manual
permalink: /manuals/1.0/ja/database.html
---
# データベース

データベースの利用のためのモジュールが用意されています。いずれもSQLのための独立ライブラリです。

* [Ray.AuraSqlModule](database_aura.html)
* [Ray.MediaQuery](database_media.html)

`Rat.AuraSqlModule`はPDO拡張の[Aura.Sql](https://github.com/auraphp/Aura.Sql)と、クエリービルダーの[Aura.SqlQuery](https://github.com/auraphp/Aura.SqlQuery)、それにページネーションの機能の提供をするモジュールです。
`Ray.MediaQuery`はSQLとインターフェイスから、クエリー実行オブジェクトを生成し注入します。
ドメイン層とインフラ層の境界が明確になり、多くのボイラープレートコードを削減します。また、どちらもインストールして、抽象度が違うこれらのライブラリを組み合わせて使うこともできます。

* [DBAL](database_dbal.html)
* [CakeDb](database_cake.html)
* [Ray.QueryModule](https://github.com/ray-di/Ray.QueryModule/blob/1.x/README.ja.md)

DBAL、CakeDBはAura.Sqlと同等のライブラリです。`Ray.QueryModule`はRay.MediaQueryの以前のライブラリでSQLを無名関数に変換します。
