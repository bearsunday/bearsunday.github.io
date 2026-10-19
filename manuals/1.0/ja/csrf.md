---
layout: docs-ja
title: CSRF
category: Manual
permalink: /manuals/1.0/ja/csrf.html
---

# CSRF

[bear/csrf](https://github.com/bearsunday/BEAR.Csrf)は、`ResourceObject`のブラウザに面した安全でないメソッドをクロスサイトリクエストフォージェリから守ります。属性を付けたメソッドにAOPで2つの門を束縛し、拒否は`BEAR\Resource`の4xx例外として投げられるので、レスポンスはフレームワーク自身のエラー処理が返します。

トークンはリソースのセマンティクスの外に置かれます。リソースメソッドの引数にCSRFトークンは現れず、リクエストスキーマやAPIドキュメントにも露出しません。CSRFはトランスポートの関心事であって、リソースの引数ではありません。

[Ray.WebFormModule](form.html#csrf-protections)はフォームを適用する時にトークンを検証します。`bear/csrf`はリクエストがどう組み立てられたかによらずリソースメソッドで検証します。両者は併用できます。

## インストール

```bash
composer require bear/csrf
```

## モジュール

アプリケーションモジュールで`CsrfModule`をインストールします。どの門を動かすかはコンストラクタ名で選ぶので、引数を省いたために無防備な配備になることはありません。

```php
use BEAR\Csrf\CsrfModule;

$this->install(CsrfModule::withSameOriginCheck('https://example.com'));
```

CLIやマシン間APIのように比較するブラウザオリジンが無いホストでは、オリジンの門はすべてのリクエストを拒否してしまいます。その場合は明示的にそう宣言します。トークンの門はどちらでも有効です。

```php
$this->install(CsrfModule::withoutSameOriginCheck());
```

`tokenField`の既定値は`_csrf_token`、`sessionKey`の既定値は`bear_csrf_token`です。既存のフィールド名やセッション構成に合わせる必要がある時だけ上書きします。

```php
$this->install(CsrfModule::withSameOriginCheck(
    allowedOrigin: 'https://example.com',
    tokenField: 'csrfToken',
    sessionKey: 'cms_csrf_token',
));
```

フィールド名はブラウザの送信とPHPの`$_POST`解析の両方を無傷で通る必要があります。使えるのは英数字と`_`、`-`だけです。`_charset_`は、ブラウザがそのhiddenフィールドの値をフォームの文字コードで上書きするため拒否されます。

## リソース属性

```php
use BEAR\Csrf\Attribute\CsrfToken;
use BEAR\Csrf\Attribute\SameOrigin;

class Article extends ResourceObject
{
    #[SameOrigin]
    #[CsrfToken]
    public function onPost(string $title, string $body): static
    {
        return $this;
    }
}
```

リソースを`final`にしてはいけません。Ray.Aopはサブクラス化で門を適用し、Ray.Diは`final`クラスを黙って飛ばすので、そのメソッドは無防備のまま動きます。

`#[SameOrigin]`はブラウザのオリジン信号（`Sec-Fetch-Site`、`Origin`、`Referer`）を検証し、`withSameOriginCheck()`の下でだけ動きます。現行のブラウザがすべて送る`Sec-Fetch-Site`があれば、ブラウザが計算したその信号だけで判定します。許可オリジンは、古いクライアントや非ブラウザが使う`Origin` / `Referer`のフォールバックでだけ参照されます。3つのどれも送らないリクエストは拒否されます。

`#[CsrfToken]`はシンクロナイザートークンを検証します。2つの門は独立していて、`withoutSameOriginCheck()`はこちらに影響しません。属性は引数を取りません。フィールド名の出所はモジュールだけなので、テンプレートが描画する名前とインターセプターが読む名前が食い違うことはありません。

トークンが無い場合、インターセプターが拒否するのではなく`''`として`CsrfTokenInterface::verify()`に渡されます。何を受け入れるかを決めるのは束縛された実装だけです。CSRFが主題でないテストは、インターセプターを差し替えるのではなく寛容な実装を束縛します。

## トークンの取得元

送信されたトークンは次の順で読まれます。

1. `X-CSRF-Token`ヘッダー
2. `ResourceObject->uri->query[$field]`
3. `$_POST[$field]`

ヘッダー名は固定で、`HeaderRequestToken::HEADER_NAME`として公開されています。`tokenField`が変えるのはクエリと`$_POST`の名前だけです。

ブラウザに面したリクエストでクエリ文字列を使うのは避けてください。URLに置いたトークンはアクセスログ、`Referer`ヘッダー、ブラウザ履歴、共有されたリンクから漏れます。ブラウザからはヘッダーか`$_POST`を使い、`uri->query`はURIがブラウザに見えないサーバー側のBEAR.Resourceリクエストのために取っておきます。

## テンプレート

`CsrfHiddenInput`は、束縛されたフィールド名でhiddenフィールドをエスケープ済みの1つの文字列として描画します。テンプレートヘルパーに注入し、保護されたメソッドに送信するすべてのフォームの中で呼びます。

```php
// Qiq: HtmlHelpersの代わりに束縛したHelpersのサブクラス
public function csrfField(): string
{
    return ($this->csrfHiddenInput)();
}
```

```html
{% raw %}<form method="post" action="/admin/article">
  {{= csrfField() }}{% endraw %}
```

```php
// Twig: 関数はマークアップを返すので安全と宣言する
new TwigFunction('csrf_field', $csrfHiddenInput, ['is_safe' => ['html']]);
```

出力は`<input type="hidden" name="_csrf_token" value="…">`で、両方の属性はエスケープ済みです。そのまま出力してください。もう一度エスケープするとマークアップが壊れます。リソースはこのフィールドの存在を知る必要がありません。

ヘルパーを解決しただけでは何も発行されません。トークンの発行とセッションの開始はフィールドが描画された時です。描画されたページはトークンを含み、パッケージ自身はキャッシュヘッダーを設定しません。本文にトークンを含むリソースを`#[Cacheable]`やCDNの後ろに置かず、そのページは`Cache-Control: private, no-store`で送ってください。

JavaScriptには同じトークンを`<meta>`タグで1度だけ公開し、`X-CSRF-Token`ヘッダーで送り返します。付けるのは同一オリジンへのリクエストだけです。`$csrf`は注入された`CsrfTokenInterface`です。

```php
<meta name="csrf-token" content="<?= htmlspecialchars($csrf->issue(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>">
```

Qiqは上の`<?= ?>`をそのまま受け付けます。Twigは受け付けないので、`$csrf->issue()`を関数として公開し、属性の中でTwigにエスケープさせます。

## トークンのライフサイクル

トークンはセッションごとに保存され、リクエストをまたいで安定しているので、フォームごとに再発行はされません。

ログインCSRFとセッション固定を緩和するため、認証状態が変わるたびに回してください。ログインとログアウトでセッションIDを再生成しトークンを消すと、次の`issue()`が新しいセッションに束縛された新しいトークンを発行します。

```php
// $csrfは注入されたCsrfTokenInterface
session_regenerate_id(true);
$csrf->clear();   // 古いトークンを捨てる
$csrf->issue();   // 新しいセッション用のトークンを発行する
```

## セッションストアとホスト

同梱の`SessionCsrfStore`はトークンをPHPの`$_SESSION`に保存します。セッションの設定はアプリケーションが持ちます。最初にストアが呼ばれる前にクッキー（`HttpOnly`、`SameSite`、`Secure`）を設定してください。

`session_start()`が失敗した時、つまりヘッダー送信済み、保存先に書けない、セッションバックエンドが落ちている時、ストアは描画でも検証でも`SessionStartFailedException`を投げます。黙って続けると何も保存されず、以降の送信がすべてトークンのせいにされた403になります。

`$_SESSION`はプロセスのものなので、同梱のストアが正しく動くのはリクエストがプロセスを占有するホストだけです。PHP-FPM、mod_php、ビルトインサーバー、CLIがそれにあたります。**1つの常駐ワーカーが複数のリクエストを処理するホストでは使わないでください。**そこでは`$_SESSION`がリクエストより長生きし、そのワーカーの訪問者全員が1つのトークンを共有して、偽造リクエストのトークンをサーバーが受け入れます。すべてのリクエストが成功し続けるので、外からは何も見えません。

ストアはコルーチンの中にいることを検出すると`CoroutineUnsafeStoreException`を投げます。`enable_coroutine`を切ったSwooleワーカーのような非コルーチンの場合は検出できないので、このガードは間違いの一形態を捕まえるものであって、常駐ワーカーのホストに配備してよい許可ではありません。

そのようなホストでは、リクエストに閉じたもの（コルーチンコンテキスト、またはセッションIDをキーにした共有バックエンド）に対して`CsrfStoreInterface`を実装し、束縛します。

```php
$this->bind(CsrfStoreInterface::class)->to(MyRequestScopedStore::class);
```

ストレージは`CsrfTokenInterface`とは別のインターフェイスです。`random_bytes()`による発行と`hash_equals()`による比較は`StoredCsrfToken`に残るので、トークンの置き場所を変えても正しくなければならない部分を書き直すことにはなりません。
