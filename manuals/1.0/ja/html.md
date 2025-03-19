---
layout: docs-ja
title: HTML
category: Manual
permalink: /manuals/1.0/ja/html.html
---
# HTML

BEAR.Sundayでは、複数のテンプレートエンジンを活用してHTML表示を実現できます。

## テンプレートエンジンの選択

### 対応テンプレートエンジン

- [Qiq](html-qiq.html)（v1.0以降）
- [Twig](html-twig.html)（v1およびv2）

### 特徴比較

| 機能 | Qiq | Twig |
|------|-----|------|
| エスケープ方式 | 明示的 | 暗黙的 |
| 構文 | PHP準拠 | 独自構文 |
| コードベース | 軽量 | 豊富な機能 |
| IDE対応 | 優れている | 一般的 |

### 構文比較

PHP:
```php
<?= $var ?>
<?= htmlspecialchars($var, ENT_QUOTES|ENT_DISALLOWED, 'utf-8') ?>
<?= htmlspecialchars(helper($var, ENT_QUOTES|ENT_DISALLOWED, 'utf-8')) ?>
<?php foreach ($users as $user): ?>
    * <?= $user->name; ?>
<?php endforeach; ?>
```

Twig:

```
{% raw %}{{ var | raw }}
{{ var }}
{{ var | helper }}
{% for user in users %}
  * {{ user.name }}
{% endfor %}{% endraw %}
```

Qiq:

```
{% raw %}{{% var }}
{{h $var }}
{{h helper($var) }}
{{ foreach($users => $user) }}
  * {{h $user->name }}
{{ endforeach }}

{{ var }} // 表示されない{% endraw %}
```

Or

```php
<?php /** @var Template $this */ ?>
<?= $this->h($var) ?>
```

## レンダラー

`RenderInterface`にバインドされResourceObjectにインジェクトされるレンダラーがリソースの表現を生成します。リソース自身はその表現に関して無関心です。

リソース単位でインジェクトされるため、複数のテンプレートエンジンを同時に使用することも可能です。

## 開発用のハローUI

開発時にハロー(Halo, 後光) [^halo] と呼ばれる開発用のUIをレンダリングされたリソースの周囲に表示できます。

ハローは以下の情報を提供します：
- リソースの状態
- 表現
- 適用されたインターセプター
- PHPStormでリソースクラスやテンプレートを開くためのリンク

[^halo]: 名前はSmalltalkのフレームワーク [Seaside](https://github.com/seasidest/seaside)の同様の機能が由来しています。

<img src="https://user-images.githubusercontent.com/529021/211504531-37cd4a8d-80b3-4d77-903f-c8f5baf5dc37.png" alt="ハローがリソース状態を表示" width="50%">

<link href="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css" rel="stylesheet">

* <span class="glyphicon glyphicon-home" rel="tooltip" title="Home"></span> ハローホーム（ボーターとツール表示）
* <span class="glyphicon glyphicon-zoom-in" rel="tooltip" title="Status"></span> リソース状態
* <span class="glyphicon glyphicon-font" rel="tooltip" title="View"></span> リソース表現
* <span class="glyphicon glyphicon-info-sign" rel="tooltip" title="Info"></span> プロファイル

[demo](/docs/demo/halo/)でハローのモックを試すことができます。

### パフォーマンスモニタリング

ハローには以下のパフォーマンス情報が表示されます：
- リソースの実行時間
- メモリ使用量
- プロファイラへのリンク

<img src="https://user-images.githubusercontent.com/529021/212373901-fce7b2fd-41b0-478f-9d36-5e2eb3b97d9c.png" alt="ハローがパフォーマンスを表示" width="50%">

### インストール

プロファイリングには[xhprof](https://www.php.net/manual/ja/intro.xhprof.php)のインストールが必要です：

```bash
pecl install xhprof
# php.iniファイルに'extension=xhprof.so'を追加
```

コールグラフを可視化するには、[graphviz](https://graphviz.org/download/)のインストールが必要です：

```bash
# macOS
brew install graphviz

# Windows
# graphvizのWebサイトからインストーラをダウンロードしてインストール

# Linux (Ubuntu)
sudo apt-get install graphviz
```

アプリケーションではDevコンテキストモジュールなどを作成して`HaloModule`をインストールします：

```php
class DevModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new HaloModule($this));
    }
}
```

例）[コールグラフデモ](/docs/demo/halo/callgraph.svg)
