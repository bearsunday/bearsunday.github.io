---
layout: docs-ja
title: フォーム
category: Manual
permalink: /manuals/1.0/ja/form.html
---

# フォーム

[Aura.Input](https://github.com/auraphp/Aura.Input)と[Aura.Filter](https://github.com/auraphp/Aura.Filter)を使ったフォームクラスは
機能が単体のクラスに集約され、テストや変更が容易です。

テンプレートエンジンのフォームヘルパーやページコントローラーでのバリデーションを行うことない疎結合なフォームはAPIでの利用や将来的な拡張にすぐれています。

フォーム要素の登録やルールを定めたフォームクラスを作成して、特定のメソッドと`@FormValidation`アノテーションを使って束縛します。
送信されたデータがバリデートされた時のみメソッド内の処理が実行されます。

## インストール

Aura.Inputを使ったフォーム処理を追加するのにcomposerで`ray/web-form-module`をインストールします。

{% highlight bash %}
composer require ray/web-form-module
{% endhighlight %}

アプリケーションモジュール`src/Module/AppModule.php`で`AuraInputModule`をインストールします。

{% highlight php %}
<?php

use Ray\Di\AbstractModule;
use Ray\WebFormModule\WebFormModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new AuraInputModule);
    }
}
{% endhighlight %}

## フォームクラス

{% highlight php %}
<?php
use Ray\WebFormModule\AbstractForm;
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractForm
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // set input fields
        $this->setField('name', 'text')
             ->setAttribs([
                 'id' => 'name'
             ]);
        // set rules and user defined error message
        $this->filter->validate('name')->is('alnum');
        $this->filter->useFieldMessage('name', 'Name must be alphabetic only.');
    }
}
{% endhighlight %}

<<<<<<< HEAD
`init()`メソッドでinput属性を指定してフォームを登録し、フィルターやルールを適用します。
フォームクラスで利用できるメソッドについて詳しく`は[Aura.Input](https://github.com/auraphp/Aura.Input#self-initializing-forms)をご覧ください

バリデーションの対象となる入力はメソッドを読んだ時の引数です。変更したいときは
`SubmitInterface`インターフェイスの`submit()メソッド`を実装してメソッド内で入力にする値を返します。
### Controller
=======
 * `init()`メソッドではinput属性を指定してフォームを登録し、Aura.Filterのバリデーションやサニタイズをルールとして適用します。

 * 適用できるバリデーションについては[Rules To Validate Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/validate.md)を、
 サニタイズは[Rules To Sanitize Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/sanitize.md)をご覧ください。

 * フォームクラスで利用できるメソッドについて詳しく`は[Aura.Input](https://github.com/auraphp/Aura.Input#self-initializing-forms)をご覧ください

 * `submit()メソッド`ではフォームでバリデーションを行うための`$_POST`や`$_GET`を返します。

### @FormValidationアノテーション
>>>>>>> 95302347574fa7ae81b0d22d33c4c92345d36786

リソースオブジェクトクラスにフォームをインジェクトします。フォームのバリデーションを行うメソッドを`@FormValidation`で
アノテートします。この時フォームのプロパティ名を`form`で、バリデーションが失敗したときのメソッドを`onFailure`で指定します。

{% highlight php %}<?php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\WebFormModule\Annotation\FormValidation;
use Ray\WebFormModule\FormInterface;

class MyController
{
    /**
     * @var FormInterface
     */
    protected $contactForm;

    /**
     * @Inject
     * @Named("contact_form")
     */
    public function setForm(FormInterface $form)
    {
        $this->contactForm = $form;
    }

    /**
     * @FormValidation(form="contactForm", onFailure="badRequest")
     */
    public function onPost()
    {
        // validation success
    }

    public function badRequest()
    {
        // validation failed
    }
}
{% endhighlight %}

### ビュー

フォームの`input`要素やエラーメッセージを取得するには要素名を指定します。

{% highlight php %}<?php
  $form->input('name'); // <input id="name" type="text" name="name" size="20" maxlength="20" />
  $form->error('name'); // "Name must be alphabetic only." or blank.
{% endhighlight %}

テンプレートにTwigを使った場合でも同様です。

{% highlight php %}
{% raw %}
{{ form.input('name') }}
{{ form.error('name') }}
{% endraw %}
{% endhighlight %}

### クロスサイトリクエストフォージェリ（CSRF）対策

CSRF対策を行うためにはフォームにCSRFオブジェクトをセットします。

{% highlight php %}<?php
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractAuraForm
{
    use SetAntiCsrfTrait;
{% endhighlight %}

セキュリティレベルを高めるためにはユーザーの認証を含んだカスタムCsrfクラスを作成してフォームクラスにセットします。
詳しくはAura.Inputの[Applying CSRF Protections](https://github.com/auraphp/Aura.Input#applying-csrf-protections)をご覧ください。

## バリデーション例外

<<<<<<< HEAD
`@FormValidation`の代わりに`@InputValidation`とアノテートするとバリデーションが失敗したときに`Ray\WebFormModule\Exception\ValidationException`が投げられるよになります。
この場合はHTML表現は使われません。Appリソースに適用してどのクライアントからもバリデーションを行うことができます。Web APIアプリケーションにも便利です。

キャッチした例外の`error`プロパティを`echo`すると[application/vnd.error+json](https://tools.ietf.org/html/rfc6906)メディアタイプの表現が出力されます。
=======
以下のように `Ray\WebFormModule\FormVndErrorModule`をインストールするとフォームのバリデーションが失敗したときに
`Ray\WebFormModule\Exception\ValidationException`例外が投げられるよになります。

`src/Module/ApiModule`を設置して、APIコンテキストのときにインストールすると便利です。

{% highlight php %}<?php
use BEAR\Package\Provide\Error\VndErrorModule;
use Ray\Di\AbstractModule;
use BEAR\Package\Context\ApiModule as PackageApiModule;

class ApiModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new PackageApiModule);
        $this->override(new VndErrorModule);
    }
}
{% endhighlight %}

キャッチした例外の`error`プロパティを`echo`すると[application/vnd.error+json](https://tools.ietf.org/html/rfc6906)メディアタイプの表現が出力されます。


{% highlight php %}<?php
http_response_code(400);
echo $e->error;

// {
//     "message": "Validation failed",
//     "path": "/path/to/error",
//     "validation_messages": {
//         "name": [
//             "Name must be alphabetic only."
//         ]
//     }
// }
{% endhighlight %}

`@VndError`アノテーションで`vnd.error+json`に必要な情報を加えることができます。

{% highlight php %}<?php
/**
 * @FormValidation(form="contactForm")
 * @VndError(
 *   message="foo validation failed",
 *   logref="a1000", path="/path/to/error",
 *   href={"_self"="/path/to/error", "help"="/path/to/help"}
 * )
 */
 public function onPost()
{% endhighlight %}

このオプションのモジュールはAPIアプリケーションの時に有用です。

## デモ

[MyVendor.ContactForm](https://github.com/bearsunday/MyVendor.ContactForm)ではフォームのデモを試すことができます。1 URLに複数のフォームを設置したときの例も用意されています。
