---
layout: docs-ja
title: フォーム
category: Manual
permalink: /manuals/1.0/ja/form.html
---

# フォーム

[Aura.Input](https://github.com/auraphp/Aura.Input)と[Aura.Filter](https://github.com/auraphp/Aura.Filter)を使ったWebフォーム機能は
関連する機能が単体のクラスに集約され、テストや変更が容易です。１つのクラスでWebフォームとバリデーションのみの両方の用途に使えます。

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

##  Webフォーム

フォーム要素の登録やルールを定めた**フォームクラス**を作成して、`@FormValidation`アノテーションを使って特定のメソッドと束縛します。
メソッドは送信されたデータがバリデーションOKのときのみ実行されます。

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

フォームクラスの`init()`メソッドでフォームのinput要素を登録し、バリデーションのフィルターやサニタイズのルールを適用します。バリデーションルールに関してはAura.Filterの[Rules To Validate Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/validate.md)、サニタイズに関しては[Rules To Sanitize Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/sanitize.md)をご覧ください。

メソッドの引数を連想配列にしたもをバリデーションします。入力を変更したいときは
`SubmitInterface`インターフェイスの`submit()メソッド`を実装して入力にする値を返します。

## @FormValidationアノテーション

フォームのバリデーションを行うメソッドを`@FormValidation`でアノテートすると、実行前に`form`プロパティのフォームオブジェクトでバリデーションが行われます。
バリデーションに失敗するとメソッド名に`ValidationFailed`サフィックスをつけたメソッドが呼ばれます。

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
    protected $form;

    /**
     * @Inject
     * @Named("contact_form")
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @FormValidation
     * // または
     * @FormValidation(form="form", onFailure="onPostValidationFailed")
     */
    public function onPost()
    {
        // validation success
    }

    public function onPostValidationFailed()
    {
        // validation failed
    }
}
{% endhighlight %}

`@FormValidation`アノテーションの`form`,`onValidationFailed`プロパティを変更して`form`プロパティの名前やメソッドの名前を明示的に指定こともできます。

### ビュー

フォームの`input`要素やエラーメッセージを取得するには要素名を指定します。

{% highlight php %}<?php
  $form->input('name'); // <input id="name" type="text" name="name" size="20" maxlength="20" />
  $form->error('name'); // 文字列「名前には全角文字またはアルファベットを入力して下さい。」またはブランク
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

セキュリティレベルを高めるためには、ユーザーの認証を含んだカスタムCsrfクラスを作成してフォームクラスにセットします。
詳しくはAura.Inputの[Applying CSRF Protections](https://github.com/auraphp/Aura.Input#applying-csrf-protections)をご覧ください。

## @InputValidationアノテーション

`@FormValidation`の代わりに`@InputValidation`とアノテートするとバリデーションが失敗したときに`Ray\WebFormModule\Exception\ValidationException`が投げられるようになります。
この場合はHTML表現は使われません。Web APIに便利です。

キャッチした例外の`error`プロパティを`echo`すると[application/vnd.error+json](https://github.com/blongden/vnd.error)メディアタイプの表現が出力されます。


{% highlight php %}<?php
http_response_code(400);
echo $e->error;

// {
//     "message": "Validation failed",
//     "path": "/path/to/error",
//     "validation_messages": {
//         "name": [
//             "名前には全角文字またはアルファベットを入力して下さい。"
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

## VndErrorModuleモジュール

`VndErrorModule`をインストールすると`@FormValidation`フォームとアノートしたメソッドも`@InputValidation`とアノテートしたメソッドと同じように例外を投げるようになります。
作成したPageリソースをAPIとして使うことが出来ます。

{% highlight php %}
<?php
use BEAR\Package\Provide\Error\VndErrorModule;
use Ray\Di\AbstractModule;

class FooModule extends AbstractModule
{
    protected function configure()
    {
        $this->override(new VndErrorModule);
    }
}
{% endhighlight %}

## デモ

[MyVendor.ContactForm](https://github.com/bearsunday/MyVendor.ContactForm)アプリケーションでフォームのデモを実行して試すことができます。
確認付きのフォームページや、複数のフォームを１ページに設置したときの例などが用意されています。
