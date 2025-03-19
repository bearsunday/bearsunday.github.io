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

```php
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
namespace MyVendor\MyProject\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\Cli\Attribute\Cli;
use BEAR\Cli\Attribute\Option;

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

## ステップ7: Homebrewフォーミュラの作成と配布

Homebrewフォーミュラの公開には2つのGitHubリポジトリが必要です：

1. アプリケーション本体のリポジトリ（例: your-vendor/greet）
2. Homebrewフォーミュラ用のリポジトリ（例: your-vendor/homebrew-greet）

### 7.1 アプリケーションのリポジトリ作成

まず、アプリケーション本体のリポジトリをGitHubで作成します：

#### 方法1: GitHubのWebインターフェースを使用

1. [https://github.com/new](https://github.com/new) にアクセス
   - Repository name: greet
   - Visibility: Public
   - "Create repository" をクリック

2. ローカルプロジェクトをプッシュ
```bash
cd /path/to/project
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/your-vendor/greet.git
git push -u origin main
```

#### 方法2: GitHub CLI (ghコマンド) を使用

ghコマンドがインストール済みで認証設定が完了している場合：

```bash
cd /path/to/project
gh repo create your-vendor/greet --public --source=. --remote=origin
git push -u origin main
```

### 7.2 CLIパッケージとフォーミュラの生成

アプリケーションがGitHubにプッシュされた後、フォーミュラを生成します：

```bash
vendor/bin/bear-cli-gen MyVendor.Greet
```

これにより以下のファイルが生成されます：
- `bin/cli/greet`：実行可能なCLIコマンド
- `var/homebrew/greet.rb`：Homebrewフォーミュラ

### 7.3 フォーミュラ用リポジトリの作成

次に、生成されたフォーミュラを公開するためのリポジトリを作成します：

#### 方法1: GitHubのWebインターフェースを使用

1. [https://github.com/new](https://github.com/new) にアクセス
   - Repository name: homebrew-greet （必ずhomebrew-プレフィックスをつける）
   - Visibility: Public
   - "Create repository" をクリック

2. フォーミュラを配置
```bash
git clone https://github.com/your-vendor/homebrew-greet.git
cd homebrew-greet
cp /path/to/project/var/homebrew/greet.rb .
git add greet.rb
git commit -m "Add greet formula"
git push
```

#### 方法2: GitHub CLI (ghコマンド) を使用

```bash
gh repo create your-vendor/homebrew-greet --public --clone
cd homebrew-greet
cp /path/to/project/var/homebrew/greet.rb .
git add greet.rb
git commit -m "Add greet formula"
git push
```

### 7.4 インストールのテスト

ローカルテスト:
```bash
brew install --formula ./var/homebrew/greet.rb
```

公開後のインストール:
```bash
brew tap your-vendor/greet
brew install greet
```

注意: フォーミュラの生成には以下の条件が必要です:
- アプリケーションのGitリポジトリが初期化されている
- GitHubリモートリポジトリが設定されている
- メインブランチ（main/master）が設定されている

これらの条件が満たされていない場合、フォーミュラ生成はスキップされ、その理由が表示されます。

## まとめ

このチュートリアルでは以下のことを学びました：

1. WebリソースをCLIコマンドとして利用可能にする方法
2. BEAR.Cliの属性を使用したコマンドラインインターフェースの設計
3. Homebrewを通じたCLIツールの配布方法

BEAR.Cliを使用することで、既存のBEAR.SundayアプリケーションをCLIツールとして簡単に提供でき、Homebrewを通じて広く配布することができます。フォーミュラの生成と公開には適切なGitHub環境が必要ですが、一度設定すれば自動的にHomebrewパッケージとして配布できる利点があります。
