---
layout: docs-ja
title: フォーム
category: Manual
permalink: /manuals/1.0/ja/form.html
---

# フォーム

最初にフォーム要素の登録やバリデーションで出来るフォームクラスを定義し、次にリソースオブジェクトクラスにフォームオブジェクトを束縛します。
バリデーションを行うメソッドでは`@FormValidation`とアノテートし、どのフォームでバリデーションするかを`form`プロパティで指定します。
送信されたデーターがバリデーションに通った時のみメソッド内の処理が実行されます。

## インストール

Aura.Inputを使ったフォーム処理を追加するのにcomposerで`ray/web-form-module`をインストールします。

{% highlight bash %}
composer require ray/web-form-module ~0.1
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

フォームのinput要素を登録するinit()メソッドとフォーム送信を行うsubmit()メソッドを持つフォームクラスを用意します。

{% highlight php %}
<?php
use Ray\WebFormModule\AbstractAuraForm;
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractAuraForm
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
        // set input filters
        /** @var $filter Filter */
        $filter = $this->getFilter();
        $filter->setRule(
            'name',
            'Name must be alphabetic only.',
            function ($value) {
                return ctype_alpha($value);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function submit()
    {
        return $_POST;
    }
}
{% endhighlight %}

 * `init()`メソッドではinput属性を指定してフォームを登録し、フィルターやルールを適用します。
フォームクラスで利用できるメソッドについて詳しく`は[Aura.Input](https://github.com/auraphp/Aura.Input#self-initializing-forms)をご覧ください

 * `submit()メソッド`ではフォームでバリデーションを行うための`$_POST`や`$_GET`を返します。

### @FormValidationアノテーション

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

### CSRF Protections

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
        $this->install(new VndErrorModule);
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
