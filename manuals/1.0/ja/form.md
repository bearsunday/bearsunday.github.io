---
layout: docs-ja
title: フォーム
category: Manual
permalink: /manuals/1.0/ja/form.html
---

# フォーム

[Aura.Input](https://github.com/auraphp/Aura.Input)と[Aura.Filter](https://github.com/auraphp/Aura.Filter)を使用したWebフォーム機能は、関連する機能が単一のクラスに集約され、テストや変更が容易です。1つのクラスでWebフォームとバリデーションの両方の用途に使用できます。

## インストール

Aura.Inputを使用したフォーム処理を追加するために、composerで`ray/web-form-module`をインストールします：

```bash
composer require ray/web-form-module
```

アプリケーションモジュール`src/Module/AppModule.php`で`AuraInputModule`をインストールします：

```php
use BEAR\Package\AbstractAppModule;
use Ray\WebFormModule\WebFormModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new WebFormModule);
    }
}
```

## Webフォーム

フォーム要素の登録やルールを定めた**フォームクラス**を作成して、`#[FormValidation]`アトリビュートを使用して特定のメソッドと束縛します。メソッドは送信されたデータがバリデーションOKのときのみ実行されます。

```php
use Ray\WebFormModule\AbstractForm;
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractForm
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // フォームフィールドの設定
        $this->setField('name', 'text')
             ->setAttribs([
                 'id' => 'name'
             ]);

        // バリデーションルールとエラーメッセージの設定
        $this->filter->validate('name')->is('alnum');
        $this->filter->useFieldMessage('name', '名前は英数字のみ使用できます。');
    }
}
```

フォームクラスの`init()`メソッドでフォームのinput要素を登録し、バリデーションのフィルターやサニタイズのルールを適用します。

バリデーションルールについては以下を参照してください：
- [Rules To Validate Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/validate.md)
- [Rules To Sanitize Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/sanitize.md)

メソッドの引数を連想配列にしたものをバリデーションします。入力を変更したい場合は`SubmitInterface`インターフェイスの`submit()`メソッドを実装して入力する値を返します。

## #[FormValidation]アトリビュート

フォームのバリデーションを行うメソッドを`#[FormValidation]`でアノテートすると、実行前に`form`プロパティのフォームオブジェクトでバリデーションが行われます。バリデーションに失敗するとメソッド名に`ValidationFailed`サフィックスをつけたメソッドが呼ばれます：

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\WebFormModule\Annotation\FormValidation;
use Ray\WebFormModule\FormInterface;

class MyController
{
    protected FormInterface $form;

    #[Inject]
    #[Named('contact_form')]
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    #[FormValidation]
    // または
    // #[FormValidation(form: 'form', onFailure: 'onPostValidationFailed')]
    public function onPost($name, $age)
    {
        // バリデーション成功時の処理
    }

    public function onPostValidationFailed($name, $age)
    {
        // バリデーション失敗時の処理
    }
}
```

`#[FormValidation]`アトリビュートの`form`と`onValidationFailed`プロパティを変更して、`form`プロパティの名前やメソッドの名前を明示的に指定することもできます。`onPostValidationFailed`にはサブミットされた値が渡されます。

## ビュー

フォームの`input`要素やエラーメッセージを取得するには要素名を指定します：

```php
$form->input('name');  // 出力例：<input id="name" type="text" name="name" size="20" maxlength="20" />
$form->error('name');  // 出力例：名前は英数字のみ使用できます。
```

Twigテンプレートを使用する場合も同様です：

```twig
{% raw %}{{ form.input('name') }}
{{ form.error('name') }}{% endraw %}
```

## CSRF

CSRF(クロスサイトリクエストフォージェリ)対策を行うためには、フォームにCSRFオブジェクトをセットします：

```php
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractForm
{
    use SetAntiCsrfTrait;
}
```

セキュリティレベルを高めるには、ユーザーの認証を含んだカスタムCsrfクラスを作成してフォームクラスにセットします。詳しくはAura.Inputの[Applying CSRF Protections](https://github.com/auraphp/Aura.Input#applying-csrf-protections)をご覧ください。

## #[InputValidation]

`#[FormValidation]`の代わりに`#[InputValidation]`とアノテートすると、バリデーションが失敗したときに`Ray\WebFormModule\Exception\ValidationException`が投げられます。この場合はHTML表現は使用されません。Web APIに便利です。

キャッチした例外の`error`プロパティを`echo`すると[application/vnd.error+json](https://github.com/blongden/vnd.error)メディアタイプの表現が出力されます：

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

`#[VndError]`アトリビュートで`vnd.error+json`に必要な情報を追加できます：

```php
#[FormValidation(form: 'contactForm')]
#[VndError(
    message: 'foo validation failed',
    logref: 'a1000',
    path: '/path/to/error',
    href: ['_self' => '/path/to/error', 'help' => '/path/to/help']
)]
public function onPost()
```

## Vnd Error

`Ray\WebFormModule\FormVndErrorModule`をインストールすると、`#[FormValidation]`でアノテートしたメソッドも`#[InputValidation]`とアノテートしたメソッドと同じように例外を投げるようになります。作成したPageリソースをAPIとして使用することができます：

```php
use BEAR\Package\AbstractAppModule;
use Ray\WebFormModule\FormVndErrorModule;

class FooModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new WebFormModule);
        $this->override(new FormVndErrorModule);
    }
}
```

## デモ

[MyVendor.ContactForm](https://github.com/bearsunday/MyVendor.ContactForm)アプリケーションでフォームのデモを実行して試すことができます。確認付きのフォームページや、複数のフォームを1ページに設置したときの例などが用意されています。
