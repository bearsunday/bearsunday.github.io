---
layout: docs-ja
title: CLI チュートリアル
category: Manual
permalink: /manuals/1.0/ja/tutorial3.html
---
# BEAR.Sunday CLI チュートリアル

## 前提条件

- PHP 8.2以上
- Composer
- Git

## ステップ1: プロジェクトの作成

### 1.1 新規プロジェクトの作成

```bash
composer create-project -n bear/skeleton MyVendor.Greet
cd MyVendor.Greet
```

### 1.2 開発サーバーの起動確認

```bash
php -S 127.0.0.1:8080 -t public
```

ブラウザで [http://127.0.0.1:8080](http://127.0.0.1:8080) にアクセスし、"Hello BEAR.Sunday" が表示されることを確認します。

```json
{
    "greeting": "Hello BEAR.Sunday",
    "_links": {
        "self": {
            "href": "/index"
        }
    }
}
```

## ステップ2: BEAR.Cliのインストール

```bash
composer require bear/cli
```

## ステップ3: 挨拶リソースの作成

`src/Resource/Page/Greeting.php`を作成します：

```php
<?php

namespace MyVendor\Greet\Resource\Page;

use BEAR\Cli\Attribute\Cli;
use BEAR\Cli\Attribute\Option;
use BEAR\Resource\ResourceObject;

class Greeting extends ResourceObject
{
    #[Cli(
        name: 'greet',
        description: '多言語で挨拶するコマンド',
        output: 'greeting'
    )]
    public function onGet(
        #[Option(shortName: 'n', description: '挨拶する相手の名前')]
        string $name,
        #[Option(shortName: 'l', description: '言語 (en, ja, fr, es)')]
        string $lang = 'en'
    ): static {
        $greeting = match ($lang) {
            'ja' => 'こんにちは',
            'fr' => 'Bonjour',
            'es' => '¡Hola',
            default => 'Hello',
        };
        
        $this->body = [
            'greeting' => "{$greeting}, {$name}",
            'lang' => $lang
        ];

        return $this;
    }
}
```

## ステップ4: Webリソースとしての動作確認

ブラウザで以下のURLにアクセスして動作確認します：

```
http://127.0.0.1:8080/greeting?name=World&lang=fr
```

以下のようなJSONレスポンスが表示されるはずです：

```json
{
   "greeting": "Bonjour, World",
   "lang": "fr",
   "_links": {
      "self": {
         "href": "/greeting?name=World&lang=fr"
      }
   }
}
```

## ステップ5: CLIコマンドの生成

```bash
vendor/bin/bear-cli-gen MyVendor.Greet
```

これにより以下のファイルが生成されます：
- `bin/cli/greet`：実行可能なCLIコマンド

## ステップ6: コマンドのテスト

生成されたコマンドをテストします：

```bash
# 実行権限を付与
chmod +x bin/cli/greet

# ヘルプの表示
./bin/cli/greet --help

# 基本的な挨拶
./bin/cli/greet -n "World"
# 出力: Hello, World

# 日本語で挨拶
./bin/cli/greet -n "世界" -l ja
# 出力: こんにちは, 世界

# JSON形式で出力
./bin/cli/greet -n "World" -l ja --format json
# 出力: {"greeting": "こんにちは, World", "lang": "ja"}
```

## ステップ7: ローカルでのHomebrewフォーミュラのテスト

### 7.1 フォーミュラの生成

フォーミュラを生成するには、Gitリポジトリが初期化されている必要があります：

```bash
# Gitリポジトリの初期化（まだの場合）
git init
git add .
git commit -m "Initial commit"
```

フォーミュラを生成します：

```bash
vendor/bin/bear-cli-gen MyVendor.Greet
```

これにより以下のファイルが生成されます：
- `bin/cli/greet`：実行可能なCLIコマンド
- `var/homebrew/greet.rb`：Homebrewフォーミュラ（Gitリポジトリが設定されている場合）

### 7.2 ローカルでのHomebrewインストールテスト

生成されたフォーミュラをローカルでテストできます：

```bash
# フォーミュラを使ってローカルインストール
brew install --formula ./var/homebrew/greet.rb

# インストールされたコマンドのテスト
greet -n "Homebrew" -l ja
# 出力: こんにちは, Homebrew

# アンインストール
brew uninstall greet
```

## オプション: 公開配布について

実際にCLIツールを他の人に配布したい場合は、以下の流れでHomebrewパッケージとして公開できます：

1. アプリケーションをGitHubにプッシュ
2. 生成されたフォーミュラ（`var/homebrew/greet.rb`）を`homebrew-`プレフィックス付きのGitHubリポジトリで公開
3. ユーザーは`brew tap your-vendor/greet && brew install greet`でインストール可能

詳細な公開手順については、[Homebrew公式ドキュメント](https://docs.brew.sh/How-to-Create-and-Maintain-a-Tap)を参照してください。

**注意**: フォーミュラの生成には以下の条件が必要です：
- アプリケーションのGitリポジトリが初期化されている
- ローカルテストの場合はGitHubリモートリポジトリは不要

これらの条件が満たされていない場合、フォーミュラ生成はスキップされ、その理由が表示されます。

## まとめ

このチュートリアルでは、単なるCLIツール作成を超えた、BEAR.Sundayの本質的な価値を体験しました：

### リソース指向アーキテクチャの真価

**同じリソース、複数の境界**
- `Greeting`リソースは一度書くだけで、Web API、CLI、Homebrewパッケージとして機能
- ビジネスロジックの重複なし、保守も一箇所で完結

### 境界横断フレームワーク

BEAR.Sundayは**境界のフレームワーク**として機能し、以下の境界を透過的に扱います：

- **プロトコル境界**: HTTP ↔ コマンドライン
- **インターフェース境界**: Web ↔ CLI ↔ パッケージ配布  
- **環境境界**: 開発環境 ↔ 本番環境 ↔ ユーザー環境

### 設計思想の実現

```php
// 1つのリソース
class Greeting extends ResourceObject {
    public function onGet(string $name, string $lang = 'en'): static
    {
        // ビジネスロジックは一箇所に
    }
}
```

↓

```bash
# Web API として
curl "http://localhost/greeting?name=World&lang=ja"

# CLI として  
./bin/cli/greet -n "World" -l ja

# Homebrewパッケージとして
brew install your-vendor/greet && greet -n "World" -l ja
```

### 長期的な保守性と生産性

- **DRY原則の徹底**: ドメインロジックがインターフェイスと結合していません
- **統一されたテスト**: 1つのリソースをテストすれば全境界をカバーします
- **一貫したAPI設計**: Web APIとCLIで同じパラメーター構造
- **将来の拡張性**: 新しい境界（gRPC、GraphQLなど）も同じリソースで対応可能
- **PHPバージョンの独立**: 使い続ける自由があります

### 現代的な配布システムとの統合

BEAR.Sundayのリソースは、現代的なパッケージシステムとも自然に統合できます。HomebrewのようなパッケージマネージャーやComposerのエコシステムを活用することで、ユーザーは実行環境を意識することなく、統一されたインターフェースでツールを利用できます。

BEAR.Sundayの「Because Everything is a Resource」は、単なるスローガンではなく、境界を越えた一貫性と保守性を実現する設計哲学です。このチュートリアルで体験したように、リソース指向アーキテクチャは境界のないソフトウェアを実現し、開発体験だけでなく利用体験にも新しい地平をもたらします
