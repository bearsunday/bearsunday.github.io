---
layout: docs-ja
title: コマンドラインインターフェイス (CLI)
category: Manual
permalink: /manuals/1.0/ja/cli.html
---
# コマンドラインインターフェイス (CLI)

BEAR.Sundayのリソース指向アーキテクチャ（ROA）は、アプリケーションのあらゆる機能をURIでアドレス可能なリソースとして表現します。このアプローチにより、Webに限らず様々な方法でリソースにアクセスできます。

```bash
$ php bin/page.php '/greeting?name=World&lang=ja'
{
    "greeting": "こんにちは, World",
    "lang": "ja"
}
```

BEAR.Cliは、このようなリソースをネイティブなCLIコマンドに変換し、Homebrewで配布可能にするツールです：

```bash
$ greet -n "World" -l ja
こんにちは, World
```

追加のコードを書くことなく、既存のアプリケーションリソースを標準的なCLIツールとして再利用できます。Homebrewを通じた配布により、PHPやBEAR.Sundayで動作していることを知ることなく、一般的なコマンドラインツールと同じように利用できます。

## インストール

Composerでインストールします。

```bash
composer require bear/cli
```

## 基本的な使い方

### リソースへのCLI属性の追加

リソースクラスにCLI属性を追加して、コマンドラインインターフェースを定義します。

```php
use BEAR\Cli\Attribute\Cli;
use BEAR\Cli\Attribute\Option;

class Greeting extends ResourceObject
{
    #[Cli(
        name: 'greet',
        description: 'Say hello in multiple languages',
        output: 'greeting'
    )]
    public function onGet(
        #[Option(shortName: 'n', description: 'Name to greet')]
        string $name,
        #[Option(shortName: 'l', description: 'Language (en, ja, fr, es)')]
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

### CLIコマンドとフォーミュラの生成

リソースをコマンドにするには、以下のようにアプリケーション名（ベンダー名とプロジェクト名）を指定してコマンドを実行します：

```bash
$ vendor/bin/bear-cli-gen 'MyVendor\MyProject'
# 生成されたファイル:
#   bin/cli/greet         # CLIコマンド
#   var/homebrew/greet.rb # Homebrewフォーミュラ
```

Homebrewフォーミュラが生成されるのはGitHubでリポジトリが設定されている場合のみです。

## コマンドの使用方法

生成されたコマンドは以下のような標準的なCLI機能を提供します：

### ヘルプの表示

```bash
$ greet --help
Say hello in multiple languages

Usage: greet [options]

Options:
  --name, -n     Name to greet (required)
  --lang, -l     Language (en, ja, fr, es) (default: en)
  --help, -h     Show this help message
  --version, -v  Show version information
  --format       Output format (text|json) (default: text)
```

### バージョン情報の表示

```bash
$ greet --version
greet version 0.1.0
```

### 基本的な使用例

```bash
# 基本的な挨拶
$ greet -n "World"
Hello, World

# 言語を指定
$ greet -n "World" -l ja
こんにちは, World

# 短いオプション
$ greet -n "World" -l fr
Bonjour, World

# 長いオプション
$ greet --name "World" --lang es
¡Hola, World
```

### JSON出力

```bash
$ greet -n "World" -l ja --format json
{
    "greeting": "こんにちは, World",
    "lang": "ja"
}
```

### 出力の挙動

CLIコマンドの出力は以下の仕様に基づきます：

- **デフォルト出力**: 指定されたフィールドの値のみを表示
- **`--format=json` オプション**: APIエンドポイントと同様に、フルJSONレスポンスを表示
- **エラーメッセージ**: 標準エラー出力（stderr）に表示
- **HTTPステータスコードのマッピング**: 終了コードにHTTPステータスコードをマップ（0: 成功、1: クライアントエラー、2: サーバーエラー）

## 配布方法

BEAR.Cliで作成したコマンドは、Homebrewを通じて配布できます。
フォーミュラの生成にはアプリケーションがGitHubで公開されていることが必要です。

フォーミュラのファイル名および中のクラス名はリポジトリの名前に基づいています。例えばGHリポジトリが`koriym/greet`の場合、`Greet`クラスを含む`var/homebrew/greet.rb`が生成されます。この時`greet`が公開するタップ名になりますが変更したい場合はフォーミュラのクラス名とファイル名を変更してください。

### 1. ローカルフォーミュラによる配布

開発版をテストする場合：

```bash
$ brew install --formula ./var/homebrew/greet.rb
```

### 2. Homebrewタップによる配布

公開リポジトリを使用して広く配布する方法です：

```bash
$ brew tap your-vendor/greet
$ brew install greet
```

この方法は特に以下の場合に適しています：

- オープンソースプロジェクト
- 継続的なアップデートの提供

#### 開発版のテスト

```bash
$ brew install --HEAD ./var/homebrew/greet.rb
```
```bash
$ greet --version
greet version 0.1.0
```

#### 安定版のリリース

1. タグを作成：
```bash
$ git tag -a v0.1.0 -m "Initial stable release"
$ git push origin v0.1.0
```

2. フォーミュラを更新：
```diff
 class Greet < Formula
+  desc "Your CLI tool description"
+  homepage "https://github.com/your-vendor/greet"
+  url "https://github.com/your-vendor/greet/archive/refs/tags/v0.1.0.tar.gz"
+  sha256 "..." # 以下のコマンドで取得したハッシュ値を記述
+  version "0.1.0"
   head "https://github.com/your-vendor/greet.git", branch: "main"
   
   depends_on "php@8.1"
   depends_on "composer"
 end
```
フォーミュラには必要に応じてデータベースなどの依存関係を追加できます。ただし、データベースのセットアップなどの環境構築は `bin/setup` スクリプトで行うことを推奨します。

3. SHA256ハッシュの取得：
```bash
# GitHubからtarballをダウンロードしてハッシュを計算
$ curl -sL https://github.com/your-vendor/greet/archive/refs/tags/v0.1.0.tar.gz | shasum -a 256
```

4. Homebrewタップの作成:
   [GitHub CLI(gh)](https://cli.github.com/)または[github.com/new](https://github.com/new)でリポジトリを作成してください。公開リポジトリ名は`homebrew-`で始める必要があります。たとえば`homebrew-greet`です：

```bash
$ gh auth login
$ gh repo create your-vendor/homebrew-greet --public --clone
# または、Webインターフェースを使用してリポジトリを作成してcloneしてください
$ cd homebrew-greet
```

5. フォーミュラの配置と公開：
```bash
$ cp /path/to/project/var/homebrew/greet.rb .
$ git add greet.rb
$ git commit -m "Add formula for greet command"
$ git push
```

6. インストールと配布:
   エンドユーザーは以下のコマンドだけでツールを使い始めることができます。PHP環境や依存パッケージのインストールは自動的に行われるため、ユーザーが環境構築について心配する必要はありません：
```bash
$ brew tap your-vendor/greet    # homebrew-プレフィックスは省略可能
$ brew install your-vendor/greet
# すぐに使用可能
$ greet --version
greet version 0.1.0
```

## フォーミュラのカスタマイズ

必要に応じて、`brew edit` コマンドでフォーミュラを編集できます：

```bash
$ brew edit your-vendor/greet
```

```ruby
class Greet < Formula
  desc "Your CLI tool description"
  homepage "https://github.com/your-vendor/greet"
  url "https://github.com/your-vendor/greet/archive/refs/tags/v0.1.0.tar.gz"
  sha256 "..." # tgzのSHA256
  version "0.1.0"
  
  depends_on "php@8.4"  # PHPバージョンの指定
  depends_on "composer"

  # アプリケーションが必要とする場合は追加
  # depends_on "mysql"
  # depends_on "redis"
end
```

## クリーンアーキテクチャ

BEAR.Cliは、リソース指向アーキテクチャ（ROA）とクリーンアーキテクチャの強みを実証しています。クリーンアーキテクチャが目指す「UIは詳細である」という原則に従い、同じリソースに対してWebインターフェースだけでなく、CLIという新しいアダプターを追加できます。

さらに、BEAR.Cliはコマンドの作成だけでなく、Homebrewによる配布や更新もサポートしています。これにより、エンドユーザーはコマンド一つでツールを使い始めることができ、PHPやBEAR.Sundayの存在を意識せず、ネイティブなUNIXコマンドのように扱えます。

また、CLIツールはアプリケーションリポジトリから独立してバージョン管理および更新が可能です。そのため、APIの進化に影響されず、コマンドラインツールとしての安定性と継続的なアップデートを保つことができます。これは、リソース指向アーキテクチャとクリーンアーキテクチャの組み合わせにより実現した、APIの新しい提供形態です。
