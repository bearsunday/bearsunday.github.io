---
layout: docs-ja
title: コーディングガイド
category: Manual
permalink: /manuals/1.0/ja/coding-guide.html
---

# コーディングガイド

## プロジェクト

`vendor`は会社の名前やチームの名前または個人の名前（`excite`,`koriym`等)を指定して、`package`にはアプリケーション（サービス）の名前（`blog`, `news`等)を指定します。
プロジェクトはアプリケーション単位で作成し、Web APIとHTMLを別ホストでサービスする場合でも1つのプロジェクトにします。

## スタイル

[PSR1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md), [PSR2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), [PSR4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)に準拠します。

```php
<?php
namespace Koriym\Blog\Resource\App;

use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

#[CacheableResponse]
class Entry extends ResourceObject
{
    public function __construct(
        private readonly ExtendPdoInterface $pdo,
        private readonly ResourceInterface $resource
    ) {}

    #[Embed(rel: "author", src: "/author{?author_id}")]
    public function onGet(string $author_id, string $slug) : ResourceObject
    {
        // ...

        return $this;
    }

    #[Link(rel: "next_action1", href: "/next_action1")]
    public function onPost (
        string $tile,
        string $body,
        string $uid,
        string $slug
    ) : ResourceObject {
        // ...
        $this->code = Code::CREATED;

        return $this;
    }
}
```

リソースの[docBlockコメント]([https://phpdoc.org/docs/latest/getting-started/your-first-set-of-documentation.html])はオプションです。リソースURIや引数名だけで説明不十分な時にメソッドの要約（一行）、説明（複数行可）、`@params`を付加します。`@params`の後は空行を空けカスタムアノテーションはその後に記述します。

```php?start_inline
/**
 * A summary informing the user what the associated element does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 *
 * @param string $arg1 *description*
 * @param string $arg2 *description*
*/
```

## リソース

リソースについてのベストプラクティスは[リソースのベストプラクティス](resource_bp.html)をご覧ください。

## グローバル

グローバルな値をリソースやアプリケーションのクラスで参照することは推奨されません。(Modulesでのみ使用します)

* [スーパーグローバル](http://php.net/manual/ja/language.variables.superglobals.php)の値を参照しない
* [define](http://php.net/manual/ja/function.define.php)は使用しない
* 設定値を保持する`Config`クラスを作成しない
* グローバルなオブジェクトコンテナ（サービスロケータ）を使用しない [[1]](http://koriym.github.io/adv10/), [[2]](http://blog.ploeh.dk/2010/02/03/ServiceLocatorisanAnti-Pattern/)
* [date](http://php.net/manual/ja/function.date.php)関数や[DateTime](http://php.net/manual/ja/class.datetime.php)クラスで現在時刻を直接取得することは推奨されません。外部から時刻をインジェクトします。[^now]

[^now]: [koriym/now](https://github.com/koriym/Koriym.Now)

* スタティックメソッドなどのグローバルなメソッドコールも推奨されません。

* アプリケーションコードが必要とする値は設定ファイルなどから取得するのではなく、全てインジェクトします。[^inject-all]

[^inject-all]: Web APIなど外部のシステムの値を利用する時には、クライアントクラスやWeb APIアクセスリソースなど１つにの場所に集中させDIやAOPでモッキングが容易にするようにします。

## クラスとオブジェクト

* [トレイト](http://php.net/manual/ja/language.oop5.traits.php)は推奨されません。[^no-trait]
* 親クラスのメソッドを子クラスが使うことは推奨されません。共通する機能は継承やtraitで共有ではなくクラスにしてインジェクトして使います。[継承より合成](https://en.wikipedia.org/wiki/Composition_over_inheritance)します。

[^no-trait]:`ResourceInject`などのインジェクション用トレイトはインジェクションのボイラープレートコードを削減するために存在しましたが、PHP8で追加された[コンストラクタの引数をプロパティへ昇格させる機能](https://www.php.net/manual/ja/language.oop5.decon.php#language.oop5.decon.constructor.promotion)により意味を失いました。コンストラクタインジェクションを使いましょう。

## スクリプトコマンド

* `composer setup`コマンドでアプリケーションのセットアップが完了することが推奨されます。このスクリプトではデータベースの初期化、必要ライブラリの確認が含まれます。`.env`の設定などマニュアルな操作が必要な場合はその手順が画面表示されることが推奨されます。

## リソース

* `onPost`で作成したリソースのURIは`Location`ヘッダーで示します。

```php?start_inline
public function onPost(string $title): static
{
    // ...
    $this->code = 201;
    $this->headers['Location'] = "/task?id={$id}";

    return $this;
}
```

リソースのベストプラクティスは[リソースベストプラクティス](/manuals/1.0/ja/resource.html#best-practice)もご覧ください。

### コード

適切なステータスコードを返します。テストが容易になり、botやクローラーにも正しい情報が伝えることができます。

* `100` Continue 複数のリクエストの継続
* `200` OK
* `201` Created リソース作成
* `202` Accepted キュー/バッチ 受付
* `204` No Content bodyがない場合
* `304` Not Modified 未更新
* `400` Bad Request　リクエストに不備
* `401` Unauthorized 認証が必要
* `403` Forbidden 禁止
* `404` Not Found
* `405` Method Not Allowed
* `503` Service Unavailable サーバーサイドでの一時的エラー

`304`は`#[Cacheable]`アトリビュートを使っていると自動設定されます。`404`はリソースクラスがない場合、`405`はリソースのメソッドがない場合に自動設定されます。またDBの接続エラーなどは必ず`503`で返しクローラーに伝えます。[[1]](https://googlewebmastercentral-ja.blogspot.jp/2011/02/blog-post.html)

### HTMLのFormメソッド

BEAR.SundayはHTMLのWebフォームで`POST`リクエストの時に`X-HTTP-Method-Override`ヘッダーや`_method`クエリーを用いてメソッドを上書きする事ができますが、推奨しているわけではありません。Pageリソースでは`onGet`と`onPost`以外を実装しない方針でも問題ありません。[[1]](http://programmers.stackexchange.com/questions/114156/why-are-there-are-no-put-and-delete-methods-on-html-forms),[[2]](http://roy.gbiv.com/untangled/2009/it-is-okay-to-use-post)

### ハイパーリンク

 * リンクを持つリソースは`#[Link]`で示すことが推奨されます。
 * リソースは意味のまとまりのグラフにして`#[Embed]`で埋め込む事が推奨されます。

## DI

 * 実行コンテキスト(prod, devなど)の値そのものをインジェクトしてはいけません。代わりにコンテキストに応じたインスタンスをインジェクトします。アプリケーションはどのコンテキストで動作しているのか無知にします。
 * ライブラリコードではセッターインジェクションは推奨されません。
 * `Provider`束縛を可能な限り避け`toConstructor`束縛を優先することが推奨されます。
 * `Module`で条件に応じて束縛をすることを避けます。 ([AvoidConditionalLogicInModules](https://github.com/google/guice/wiki/AvoidConditionalLogicInModules))
 * モジュールの`configure()`から環境変数を参照しないで、コンストラクタインジェクションにします。

## AOP

 * インターセプターの適用を必須にしてはいけません。例えば`@Log`や`@Transactional`などのインターセプターを外しても、ログやトランザクションなどの横断的な機能は失われますがプログラムの本質的な動作しますがこのようにします。
 * メソッド内の依存をインターセプターがインジェクトしないようにします。メソッド実装時にしか決定できない値は`@Assisted`インジェクションで引数にインジェクトします。
 * 複数のインタセプターがある場合にその実行順に依存しないようにします。
 * 無条件に全メソッドに適用するインターセプターであれば`bootstrap.php`での記述を考慮してください。
 * 横断的関心事と、本質的関心事を分けるために使われるものです。特定のメソッドのhackのためにインターセプトするような使い方は推奨されません。

## 環境

 * Webだけでしか動作しないアプリケーションは推奨されません。テスト可能にするためにコンソールでも動作するようにします。
 * `.env`ファイルをプロジェクトリポジトリに含まない事が推奨されます。

## テスト

リソースクライアントを使ったリソーステストを基本にします。リソースの値を確認して、必要があれば表現(HTMLやJSON)のテストを加えます。

## 開発ツール

以下のPHPStormプラグインを推奨します。`PHPStorm > Preference > Plugins`で設定します。

* [BEAR Sunday](https://github.com/kuma-guy/idea-php-bear-sunday-plugin)
* [PHP Annotations](https://github.com/Haehnchen/idea-php-annotation-plugin)
* PHP Advanced AutoComplete
* Database Navigator

---
