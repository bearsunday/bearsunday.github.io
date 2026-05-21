---
layout: docs-ja
title: フォーム
category: Manual
permalink: /manuals/1.0/ja/form.html
---

# フォーム

Ray.WebFormModuleは、[Aura.Input](https://github.com/auraphp/Aura.Input)と[Ray.Di](https://github.com/ray-di/Ray.Di)を使ってアスペクト指向でWebフォームをバリデーションするモジュールです。フォームフィールド、バリデーションルール、送信値、レンダリングヘルパーを1つのフォームクラスに集約できるため、テストや変更が容易です。

## インストール

Composerで`ray/web-form-module`をインストールします。

```bash
composer require ray/web-form-module
```

アプリケーションモジュールに`WebFormModule`をインストールします。

```php
use Ray\Di\AbstractModule;
use Ray\WebFormModule\WebFormModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new WebFormModule());
    }
}
```

互換性のため`Ray\WebFormModule\AuraInputModule`クラスも`WebFormModule`の薄いサブクラスとして残されています。新規コードでは`WebFormModule`を使ってください。

## フォームクラス

自己初期化フォームクラスでは、`init()`メソッドでフィールドとバリデーションルールを定義します。フォームが`submit()`を実装している場合、その戻り値が送信データとして使われます。基礎となるフォームAPIについては[Aura.Input self-initializing forms](https://github.com/auraphp/Aura.Input/blob/1.x/README.md#self-initializing-forms)を参照してください。

```php
use Ray\WebFormModule\AbstractForm;
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractForm
{
    use SetAntiCsrfTrait;

    public function init()
    {
        $this->setField('name', 'text')
             ->setAttribs([
                 'id' => 'name'
             ]);

        $this->filter->validate('name')->is('alnum');
        $this->filter->useFieldMessage('name', 'Name must be alphanumeric only.');
    }

    public function submit()
    {
        return $_POST;
    }

    public function __toString()
    {
        $form = $this->form();
        $form .= $this->helper->tag('div', ['class' => 'form-group']);
        $form .= $this->helper->tag('label', ['for' => 'name']);
        $form .= 'Name:';
        $form .= $this->helper->tag('/label') . PHP_EOL;
        $form .= $this->input('name');
        $form .= $this->error('name');
        $form .= $this->helper->tag('/div') . PHP_EOL;
        $form .= $this->input('submit');
        $form .= $this->helper->tag('/form');

        return $form;
    }
}
```

## コントローラー

フォームのバリデーションが必要なメソッドに`#[FormValidation]`を付けます。`form`にはコントローラー上のフォームプロパティ名を、`onFailure`にはバリデーション失敗時に呼び出すメソッド名を指定します。

```php
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\WebFormModule\Annotation\FormValidation;
use Ray\WebFormModule\FormInterface;

class MyController
{
    /** @var FormInterface */
    protected $contactForm;

    #[Inject]
    public function setForm(#[Named("contact_form")] FormInterface $form)
    {
        $this->contactForm = $form;
    }

    #[FormValidation(form: "contactForm", onFailure: "badRequestAction")]
    public function createAction()
    {
        // validation success
        // vnd.error+json の詳細は #[VndError] で追加できます。
    }

    public function badRequestAction()
    {
        // validation failed
    }
}
```

## ビュー

フォームが文字列表現を提供している場合、フォームを`echo`するとフォームHTML全体がレンダリングされます。

```php
echo $form;
```

個別の入力要素やエラーメッセージもレンダリングできます。

```php
echo $form->input('name'); // <input id="name" type="text" name="name" size="20" maxlength="20" />
echo $form->error('name'); // "Name must be alphabetic only." または空文字
```

## CSRF Protections

CSRF対策は **opt-in** です。`SetAntiCsrfTrait`を使うフォームには`AntiCsrfInterface`が注入されますが、トークン検証はバリデーション対象メソッドに`#[CsrfProtection]`が付いている場合だけ行われます。`#[CsrfProtection]`がないメソッドでは、フォームがCSRFに対応していてもCSRFチェックは実行されません。

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
    #[FormValidation(form: "contactForm")]
    #[CsrfProtection]
    public function createAction()
    {
    }
}
```

独自の`AntiCsrf`クラスを提供することもできます。詳しくはAura.Inputの[Applying CSRF Protections](https://github.com/auraphp/Aura.Input#applying-csrf-protections)を参照してください。

## 0.xからのマイグレーション

1.0ではDoctrine Annotationsを廃止し、PHP 8のネイティブ属性に移行しました。型宣言も強化されています。主な書き換えは次の通りです。

| Before (0.x)                                                       | After (1.0)                                             |
|--------------------------------------------------------------------|---------------------------------------------------------|
| `@FormValidation(form="f", onFailure="badRequest")`                | `#[FormValidation(form: 'f', onFailure: 'badRequest')]` |
| `@FormValidation(form="f", antiCsrf=true)`                         | `#[FormValidation(form: 'f')]` + `#[CsrfProtection]`    |
| `@InputValidation(form="f")`                                       | `#[InputValidation(form: 'f')]`                         |
| `@VndError(message="...", logref="...")`                           | `#[VndError(message: '...', logref: '...')]`            |
| `new AuraInputInterceptor($injector, $reader)`                     | `new AuraInputInterceptor($injector)`                   |
| `public function input($input)` / `public function error($input)`  | `input(string $input): string` / `error(string $input): string` |

破壊的変更の完全なリストは[CHANGELOG.md](https://github.com/ray-di/Ray.WebFormModule/blob/1.x/CHANGELOG.md)を参照してください。

### Claude Codeによる自動マイグレーション

Ray.WebFormModuleにはClaude Code skillの[`.claude/skills/migrate-to-1.0/SKILL.md`](https://github.com/ray-di/Ray.WebFormModule/blob/1.x/.claude/skills/migrate-to-1.0/SKILL.md)が同梱されています。このskillは、アノテーションから属性への変更、`antiCsrf=true`から`#[CsrfProtection]`への分離、`Reader`引数の削除、`FormInterface`署名更新をAIアシスタントに案内します。利用側プロジェクトの`.claude/skills/`にディレクトリをコピーし、`/migrate-to-1.0`で起動してください。

## Validation Exception

`#[InputValidation]`を使うと、バリデーション失敗時に`Ray\WebFormModule\Exception\ValidationException`が投げられます。HTML表現を使わないAPIアプリケーションに便利です。

```php
use Ray\WebFormModule\Annotation\InputValidation;

class Foo
{
    #[InputValidation(form: "form1")]
    public function createAction($name)
    {
        // ...
    }
}
```

`Ray\WebFormModule\FormVndErrorModule`をインストールすると、`#[FormValidation]`を付けたメソッドもバリデーション失敗時に同じ例外を投げます。

```php
use Ray\Di\AbstractModule;
use Ray\WebFormModule\FormVndErrorModule;
use Ray\WebFormModule\WebFormModule;

class FakeVndErrorModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new WebFormModule());
        $this->override(new FormVndErrorModule());
    }
}
```

キャッチした例外の`error`プロパティを`echo`すると、[application/vnd.error+json](https://tools.ietf.org/html/rfc6906)メディアタイプの表現が出力されます。

```php
echo $e->error;

//{
//    "message": "Validation failed",
//    "path": "/path/to/error",
//    "validation_messages": {
//        "name": [
//            "Name must be alphabetic only."
//        ]
//    }
//}
```

`#[VndError]`属性で`vnd.error+json`に詳細情報を追加できます。

```php
#[FormValidation(form: "contactForm")]
#[VndError(message: "foo validation failed", logref: "a1000", path: "/path/to/error", href: ["_self" => "/path/to/error", "help" => "/path/to/help"])]
public function createAction()
{
}
```

## デモ

Ray.WebFormModuleリポジトリでデモアプリケーションを起動できます。

```bash
php -S docs/demo/1.csrf/web.php
```
