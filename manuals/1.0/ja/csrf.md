---
layout: docs-ja
title: CSRF
category: Manual
permalink: /manuals/1.0/ja/csrf.html
---

# CSRF

[bear/csrf](https://github.com/bearsunday/BEAR.Csrf)は`ResourceObject`のメソッドをクロスサイトリクエストフォージェリ（CSRF）から守ります。守る対象は、ブラウザから呼ばれる状態変更メソッド（POST / PUT / PATCH / DELETE）です。属性を付けたメソッドに、同一オリジン検査（`#[SameOrigin]`）とトークン検査（`#[CsrfToken]`）の2つのインターセプターをAOPで束縛します。検査に失敗すると`BEAR\Resource`の4xx例外が投げられ、エラーレスポンスはフレームワークのエラー処理が返します。

CSRFトークンはリソースメソッドの引数には現れません。リクエストスキーマやAPIドキュメントにも出てきません。CSRFはトランスポート層の関心事であり、リソースの引数ではないからです。

[Ray.WebFormModule](form.html#csrf-protections)は`AbstractForm::apply()`の実行時にフォームのトークンを検証します。`bear/csrf`はリクエストの経路によらず、`ResourceObject`のメソッド呼び出し時に検証します。

## インストール

```bash
composer require bear/csrf
```

## モジュール

アプリケーションモジュールで`CsrfModule`をインストールします。どの検査を有効にするかは名前付きコンストラクタで選びます。引数を省略したために無防備な設定になることはありません。

```php
use BEAR\Csrf\CsrfModule;

$this->install(CsrfModule::withSameOriginCheck('https://example.com'));
```

`allowedOrigin`は`https://example.com`や`http://localhost:8080`のように、スキームとホスト（必要ならポート）だけで指定します。パスを含めると、保護されたメソッドを呼ぶたびに`LogicException`になります。

CLIやサーバー間APIのように、比較するブラウザオリジンが無い環境では、同一オリジン検査はすべてのリクエストを拒否します。そのような環境では、同一オリジン検査を行わないことを明示します。トークン検査はどちらでも有効です。

```php
$this->install(CsrfModule::withoutSameOriginCheck());
```

`tokenField`の既定値は`_csrf_token`、`sessionKey`の既定値は`bear_csrf_token`です。既存のフィールド名やセッション構成に合わせる必要があるときだけ変更します。

```php
$this->install(CsrfModule::withSameOriginCheck(
    allowedOrigin: 'https://example.com',
    tokenField: 'csrfToken',
    sessionKey: 'cms_csrf_token',
));
```

フィールド名は、ブラウザの送信と`$_POST`の解析を経てもそのまま届く名前にします。使えるのはASCIIの英数字、`_`、`-`だけです。空文字や`_charset_`（大文字小文字を問わず）など条件を満たさない名前は、インジェクターの構築時に`InvalidArgumentException`になります。`_charset_`を除外するのは、ブラウザがそのhiddenフィールドの値をフォームの文字コードで上書きするためです。

## リソース属性

```php
use BEAR\Csrf\Attribute\CsrfToken;
use BEAR\Csrf\Attribute\SameOrigin;
use BEAR\Resource\ResourceObject;

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

属性はメソッドにだけ付けられます。保護するリソースを`final`にしてはいけません。Ray.Aopはサブクラスを生成してインターセプターを適用し、Ray.Diは`final`クラスを黙って飛ばすので、そのメソッドは無防備のまま動きます。

`#[SameOrigin]`はブラウザが送るオリジン情報を検査します。有効かどうかは`AllowedOrigin`の束縛で決まります。`withSameOriginCheck()`は渡されたオリジンを、`withoutSameOriginCheck()`は`null`を束縛し、オリジンが`null`ならインターセプターは検査せずに通します。現行のブラウザがすべて送る`Sec-Fetch-Site`がある場合は、値が`same-origin`のときだけ通します。`same-site`、`cross-site`、`none`、未知の値は403です。このとき許可オリジンとは比較しません。`Sec-Fetch-Site`を送らない古いクライアントや非ブラウザでは、`Origin`、無ければ`Referer`を許可オリジンと比較します。3つのどれも無いリクエストは拒否します。

`#[CsrfToken]`はシンクロナイザートークンを検査します。2つの検査は独立しており、`withoutSameOriginCheck()`はトークン検査に影響しません。属性は引数を取りません。フィールド名を決めるのはモジュールだけなので、テンプレートが描画する名前とインターセプターが読む名前が食い違うことはありません。

`AllowedOrigin`の束縛は拡張点です。BEAR.Sundayのアプリケーションは中立な`AppModule`の上にコンテキストを合成するので、正規のオリジンを知っているモジュールは`CsrfModule`をインストールするモジュールと別であることが多いです。共有モジュールでは`withoutSameOriginCheck()`をインストールし、オリジンを持つコンテキスト — 環境変数から読む本番モジュール、これから送るオリジンを名指しするテストハーネス — が`AllowedOrigin`を束縛し直します。インターセプターはどちらでも束縛されたままで、値だけが検査の有無を決めます。

```php
// ProdModule
use BEAR\Csrf\Http\AllowedOrigin;

$this->bind(AllowedOrigin::class)->toInstance(new AllowedOrigin($origin));
```

トークンが無いリクエストは、インターセプターがその場で拒否するのではなく、`''`として`CsrfTokenInterface::verify()`に渡されます。何を受け入れるかは束縛された実装が決めます。CSRFが主題でないテストでは、インターセプターを差し替えるのではなく、`verify()`が常に`true`を返す実装を`CsrfTokenInterface`に束縛します。

拒否は`BEAR\Csrf\Exception\ForbiddenException`のサブクラス（403）です。トークンが無ければ`MissingCsrfTokenForbiddenException`、一致しなければ`InvalidCsrfTokenForbiddenException`、オリジンが異なれば`CrossOriginForbiddenException`が投げられます。`Origin`や`Referer`の形式が不正な場合は`BEAR\Resource\Exception\BadRequestException`（400）です。

## トークンの取得元

送信されたトークンは次の順で探し、最初に見つかった空でない値を使います。

1. `X-CSRF-Token`ヘッダー
2. `ResourceObject->uri->query[$field]`
3. `$_POST[$field]`

複数の取得元を組み合わせることはありません。ヘッダーのトークンが間違っていれば、`$_POST`に正しいトークンがあっても拒否されます。

ヘッダー名は固定で、`HeaderRequestToken::HEADER_NAME`として公開されています。`tokenField`が変えるのは`uri->query`と`$_POST`で探すキーだけです。

BEAR.SundayのWebルーターは、POSTでは`$_POST`を、PUT / PATCH / DELETEではフォームエンコードまたはJSONのリクエストボディを`uri->query`に入れます。ブラウザのフォーム送信やJSONボディのトークンは、2番目の取得元で読まれます。URLのクエリ文字列にトークンを載せないでください。アクセスログ、`Referer`ヘッダー、ブラウザ履歴、共有されたリンクから漏れます。

## テンプレート

`CsrfHiddenInput`は、束縛されたフィールド名のhiddenフィールドをエスケープ済みのHTML文字列として返す呼び出し可能オブジェクトです。テンプレートヘルパーに注入し、保護されたメソッドに送信するすべてのフォームの中で呼びます。

Qiqでは`HtmlHelpers`のサブクラスにヘルパーメソッドを追加し、`Helpers`の束縛を差し替えます。

```php
use BEAR\Csrf\Html\CsrfHiddenInput;
use Qiq\Helper\Html\HtmlHelpers;

class AppHelpers extends HtmlHelpers
{
    public function __construct(private readonly CsrfHiddenInput $csrfHiddenInput)
    {
        parent::__construct();
    }

    public function csrfField(): string
    {
        return ($this->csrfHiddenInput)();
    }
}
```

```php
use Qiq\Helpers;

$this->install(new QiqModule($templateDir));
$this->bind(Helpers::class)->to(AppHelpers::class);
```

```html
{% raw %}<form method="post" action="/admin/article">
  {{= csrfField() }}{% endraw %}
```

Twigでは`csrf_field`関数として登録します。戻り値はHTMLなので、Twigがエスケープしないよう`is_safe`を宣言します。登録する場所は[Twigの拡張](html-twig-v2.html)と同じく、`Twig_Environment`のProviderです。

```php
use Twig\TwigFunction;

$twig->addFunction(new TwigFunction('csrf_field', $csrfHiddenInput, ['is_safe' => ['html']]));
```

```twig
{% raw %}<form method="post" action="/admin/article">
  {{ csrf_field() }}{% endraw %}
```

出力は`<input type="hidden" name="_csrf_token" value="…">`で、属性値はエスケープ済みです。そのまま出力してください。重ねてエスケープするとマークアップが壊れます。リソース側はこのフィールドの存在を知る必要がありません。

ヘルパーを取得しただけでは何も起きません。トークンの発行とセッションの開始は、フィールドを描画したときに行われます。描画されたページにはトークンが含まれるので、`#[CacheableResponse]`や`#[DonutCache]`のキャッシュ対象やCDNに載せず、`Cache-Control: private, no-store`で返してください（[キャッシュ](cache.html)の「レスポンスヘッダー」を参照）。`bear/csrf`自身はキャッシュヘッダーを設定しません。

JavaScriptから送る場合は、同じトークンを`<meta>`タグで1度だけ公開し、`X-CSRF-Token`ヘッダーに載せて送り返します。ヘッダーを付けるのは同一オリジンへのリクエストだけにします。`$csrf`は注入された`CsrfTokenInterface`です。

```php
<meta name="csrf-token" content="<?= htmlspecialchars($csrf->issue(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>">
```

```js
const token = document.querySelector('meta[name="csrf-token"]').content;
fetch('/admin/article', {method: 'POST', headers: {'X-CSRF-Token': token}, body});
```

Qiqは上の`<?= ?>`をそのまま受け付けます。Twigでは`$csrf->issue()`を返す関数を登録し、属性値のエスケープはTwigに任せます。

## トークンのライフサイクル

トークンはセッション単位で保存され、リクエストをまたいで同じ値が使われます。フォームごとに発行し直すことはありません。

ログインCSRFとセッション固定を緩和するため、認証状態が変わるたびにトークンを更新します。ログインとログアウトで古いトークンを捨ててセッションIDを再生成すると、次の`issue()`が新しいセッションに紐づいたトークンを発行します。`clear()`はセッションが未開始なら開始するので、この順で呼べば`session_regenerate_id()`は必ず開始済みのセッションに対して動きます。

```php
// $csrfは注入されたCsrfTokenInterface
$csrf->clear();                 // 古いトークンを捨てる
session_regenerate_id(true);
$csrf->issue();                 // 新しいセッション用のトークンを発行する
```

## セッションストアと動作環境

同梱の`SessionCsrfStore`はトークンをPHPの`$_SESSION`に保存します。セッションの設定はアプリケーションの責任です。ストアが最初に呼ばれる前に、クッキーの`HttpOnly`、`SameSite`、`Secure`を設定しておきます。

ヘッダー送信済み、保存先に書けない、セッションバックエンドの停止などで`session_start()`が失敗すると、ストアは`SessionStartFailedException`を投げます。フィールドの描画時でも検証時でも同じです。失敗を無視して続けると何も保存されず、以降の送信がすべてトークン不備を理由とする403になるためです。

セッションクッキーはヘッダーで送られるので、最初の`issue()`や`verify()`より前に出力を始めてはいけません。BEAR.Sundayはテンプレートを文字列に描画してから送信するので、テンプレート内で`issue()`を呼ぶ分には問題ありません。CLIスクリプトのように先に出力する場合は、出力前にセッションを開始しておきます。

`$_SESSION`はプロセス単位なので、同梱のストアが正しく動くのは、1つのリクエストが1つのプロセスを占有する環境だけです。PHP-FPM、mod_php、ビルトインサーバー、CLIがこれにあたります。**1つの常駐ワーカーが複数のリクエストを処理する環境では使わないでください。**`$_SESSION`がリクエストより長く生き残り、そのワーカーを通る訪問者全員が1つのトークンを共有します。偽造リクエストが持つトークンをサーバーが受け入れてしまいますが、すべてのリクエストが成功し続けるので、外からは気づけません。

ストアはコルーチンの中で呼ばれたことを検出すると`CoroutineUnsafeStoreException`を投げます。`enable_coroutine`を切ったSwooleワーカーのように、コルーチンを使わない常駐ワーカーは検出できません。このガードは誤用の一部を捕まえるものであり、常駐ワーカー環境で使ってよいという保証ではありません。

常駐ワーカー環境では、コルーチンコンテキストやセッションIDをキーにした共有バックエンドのように、リクエストに閉じた保存先で`CsrfStoreInterface`を実装して束縛します。

```php
use BEAR\Csrf\CsrfStoreInterface;

$this->bind(CsrfStoreInterface::class)->to(MyRequestScopedStore::class);
```

保存先は`CsrfTokenInterface`とは別のインターフェイスです。`random_bytes()`による発行と`hash_equals()`による比較は`StoredCsrfToken`に残るので、保存先を変えても、正しさが求められるトークンの生成と比較を書き直す必要はありません。
