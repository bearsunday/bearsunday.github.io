---
layout: docs-ja
title: フォーム
category: Manual
permalink: /manuals/1.0/ja/form.html
---

# フォーム

[Aura.Input](https://github.com/auraphp/Aura.Input)と[Aura.Filter](https://github.com/auraphp/Aura.Filter)を使ったWebフォーム機能は、関連する処理を単一のクラスに集約するため、テストや変更が容易です。1つのフォームクラスをWebフォームの表示とバリデーションの両方に使用できます。

## インストール

composerで`ray/web-form-module`をインストールします：

```bash
composer require ray/web-form-module
```

アプリケーションモジュール`src/Module/AppModule.php`で`AuraInputModule`をインストールします：

```php
use BEAR\Package\AbstractAppModule;
use Ray\WebFormModule\AuraInputModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new AuraInputModule());
    }
}
```

## フォームクラス

フォーム要素の登録とバリデーションルールを定義する**フォームクラス**を作成し、`#[FormValidation]`アトリビュートで特定のメソッドに束縛します。バリデーションが成功したときだけ、そのメソッドが実行されます。

```php
use Ray\WebFormModule\AbstractForm;
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractForm
{
    use SetAntiCsrfTrait;

    public function init(): void
    {
        // フォームフィールドの登録
        $this->setField('name', 'text')
             ->setAttribs(['id' => 'name']);

        // バリデーションルールとエラーメッセージの設定
        $this->filter->validate('name')->is('alnum');
        $this->filter->useFieldMessage('name', '名前は英数字のみ使用できます。');
    }
}
```

`init()`メソッドでフォームの入力要素を登録し、バリデーションフィルターやサニタイズルールを適用します。詳しいルールは以下を参照してください：

- [Rules To Validate Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/validate.md)
- [Rules To Sanitize Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/sanitize.md)

メソッドの引数を連想配列にしたものをバリデーションします。入力値を加工したい場合は`SubmitInterface::submit()`を実装して値を返します。

## #[FormValidation]アトリビュート

`#[FormValidation]`アトリビュートを付けたメソッドは、実行前に`form`プロパティのフォームオブジェクトでバリデーションされます。バリデーションが失敗すると、メソッド名に`ValidationFailed`サフィックスを付けたメソッドが呼び出されます：

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\WebFormModule\Annotation\FormValidation;
use Ray\WebFormModule\FormInterface;

class MyController
{
    protected FormInterface $contactForm;

    #[Inject]
    public function setForm(#[Named('contact_form')] FormInterface $form): void
    {
        $this->contactForm = $form;
    }

    #[FormValidation(form: 'contactForm')]
    public function onPost(string $name, int $age): ResourceObject
    {
        // バリデーション成功時の処理
    }

    public function onPostValidationFailed(string $name, int $age): ResourceObject
    {
        // バリデーション失敗時の処理
    }
}
```

`#[FormValidation]`の`form`プロパティでフォームプロパティ名を、`onFailure`プロパティで失敗時に呼び出すメソッド名を明示できます：

```php
#[FormValidation(form: 'contactForm', onFailure: 'badRequestAction')]
public function onPost(string $name, int $age): ResourceObject
{
}
```

失敗時メソッドにはサブミットされた引数がそのまま渡されます。

## ビュー

フォームの`input`要素やエラーメッセージを取得するには要素名を指定します：

```php
$form->input('name');  // 例：<input id="name" type="text" name="name" size="20" maxlength="20" />
$form->error('name');  // 例：名前は英数字のみ使用できます。
```

Twigテンプレートでも同様です：

```twig
{% raw %}{{ form.input('name') }}
{{ form.error('name') }}{% endraw %}
```

フォームクラスが`ToStringInterface`を実装していれば、フォーム全体を文字列として出力できます：

```php
echo $form;  // フォーム全体のHTMLを描画
```

## CSRF

CSRF(クロスサイトリクエストフォージェリ)保護はopt-inです。フォームに`SetAntiCsrfTrait`を使うと`AntiCsrfInterface`が組み込まれますが、トークンの検証は`#[CsrfProtection]`アトリビュートを付けたメソッドでのみ実行されます。アトリビュートが無いメソッドでは、フォームが`AntiCsrf`オブジェクトを持っていてもCSRF検証は行われません。

```php
use Ray\WebFormModule\AbstractForm;
use Ray\WebFormModule\Annotation\CsrfProtection;
use Ray\WebFormModule\Annotation\FormValidation;
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractForm
{
    use SetAntiCsrfTrait;
}

class MyController
{
    #[FormValidation(form: 'contactForm')]
    #[CsrfProtection]
    public function onPost(string $name, int $age): ResourceObject
    {
        // CSRFトークンが正しい場合のみ実行される
    }
}
```

セキュリティレベルを高めるには、ユーザー認証を組み込んだカスタムCsrfクラスを作成してフォームクラスにセットします。詳しくはAura.Inputの[Applying CSRF Protections](https://github.com/auraphp/Aura.Input#applying-csrf-protections)を参照してください。

## #[InputValidation]

`#[FormValidation]`の代わりに`#[InputValidation]`を使うと、バリデーション失敗時に`Ray\WebFormModule\Exception\ValidationException`が投げられます。HTML表現を使用しないのでWeb APIに便利です。

キャッチした例外の`error`プロパティを`echo`すると、[application/vnd.error+json](https://github.com/blongden/vnd.error)メディアタイプの表現が出力されます：

```php
http_response_code(400);
echo $e->error;

// 出力例：
// {
//     "message": "Validation failed",
//     "path": "/path/to/error",
//     "validation_messages": {
//         "name": [
//             "名前は英数字のみ使用できます。"
//         ]
//     }
// }
```

`#[VndError]`アトリビュートで`vnd.error+json`に追加情報を付与できます：

```php
#[FormValidation(form: 'contactForm')]
#[VndError(
    message: 'foo validation failed',
    logref: 'a1000',
    path: '/path/to/error',
    href: ['_self' => '/path/to/error', 'help' => '/path/to/help']
)]
public function onPost(): ResourceObject
{
}
```

## FormVndErrorModule

`Ray\WebFormModule\FormVndErrorModule`をインストールすると、`#[FormValidation]`を付けたメソッドも`#[InputValidation]`と同様に例外を投げるようになります。Pageリソースをそのまま API として利用できます：

```php
use Ray\Di\AbstractModule;
use Ray\WebFormModule\AuraInputModule;
use Ray\WebFormModule\FormVndErrorModule;

class FooModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new AuraInputModule());
        $this->override(new FormVndErrorModule());
    }
}
```

## 0.x からの移行

1.0 では Doctrine Annotations から PHP 8 Attributes へ移行し、CSRF 保護が `#[CsrfProtection]` による opt-in に変わるなどの破壊的変更があります。移行手順は[Ray.WebFormModule README](https://github.com/ray-di/Ray.WebFormModule#migration-from-0x)と[CHANGELOG](https://github.com/ray-di/Ray.WebFormModule/blob/1.x/CHANGELOG.md)を参照してください。

## デモ

[MyVendor.ContactForm](https://github.com/bearsunday/MyVendor.ContactForm)で、確認画面付きフォームや複数フォームを1ページに設置した例などを試すことができます。
