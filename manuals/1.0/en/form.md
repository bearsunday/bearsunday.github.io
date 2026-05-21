---
layout: docs-en
title: Form
category: Manual
permalink: /manuals/1.0/en/form.html
---

# Form

Ray.WebFormModule provides aspect-oriented web form validation powered by [Aura.Input](https://github.com/auraphp/Aura.Input) and [Ray.Di](https://github.com/ray-di/Ray.Di). Form fields, validation rules, submitted values, and rendering helpers are collected in a single form class so the form is easy to test and change.

## Installation

Install `ray/web-form-module` with Composer.

```bash
composer require ray/web-form-module
```

Install `WebFormModule` in your application module.

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

The legacy `Ray\WebFormModule\AuraInputModule` class remains available as a thin subclass of `WebFormModule` for backward compatibility. New code should use `WebFormModule`.

## Form Class

A self-initializing form class defines fields and validation rules in `init()`. If the form implements `submit()`, the returned values are used as submitted data. See [Aura.Input self-initializing forms](https://github.com/auraphp/Aura.Input/blob/1.x/README.md#self-initializing-forms) for the underlying form API.

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

## Controller

Annotate methods that require form validation with `#[FormValidation]`. The `form` argument names the form property on the controller, and `onFailure` names the method called when validation fails.

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
        // More detail for vnd.error+json can be added with #[VndError].
    }

    public function badRequestAction()
    {
        // validation failed
    }
}
```

## View

When the form provides string rendering, echoing the form renders the complete HTML.

```php
echo $form;
```

You can also render individual inputs and errors.

```php
echo $form->input('name'); // <input id="name" type="text" name="name" size="20" maxlength="20" />
echo $form->error('name'); // "Name must be alphabetic only." or blank.
```

## CSRF Protections

CSRF protection is **opt-in**. A form that uses `SetAntiCsrfTrait` is wired with an `AntiCsrfInterface`, but the token is only verified when the validated method is annotated with `#[CsrfProtection]`. Methods without `#[CsrfProtection]` perform no CSRF check even if the form supports it.

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

You can provide a custom `AntiCsrf` class. See [Applying CSRF Protections](https://github.com/auraphp/Aura.Input#applying-csrf-protections) in Aura.Input for details.

## Migration From 0.x

Version 1.0 drops Doctrine Annotations in favor of native PHP 8 attributes and tightens type declarations. The most common rewrites are:

| Before (0.x)                                                       | After (1.0)                                             |
|--------------------------------------------------------------------|---------------------------------------------------------|
| `@FormValidation(form="f", onFailure="badRequest")`                | `#[FormValidation(form: 'f', onFailure: 'badRequest')]` |
| `@FormValidation(form="f", antiCsrf=true)`                         | `#[FormValidation(form: 'f')]` + `#[CsrfProtection]`    |
| `@InputValidation(form="f")`                                       | `#[InputValidation(form: 'f')]`                         |
| `@VndError(message="...", logref="...")`                           | `#[VndError(message: '...', logref: '...')]`            |
| `new AuraInputInterceptor($injector, $reader)`                     | `new AuraInputInterceptor($injector)`                   |
| `public function input($input)` / `public function error($input)`  | `input(string $input): string` / `error(string $input): string` |

See [CHANGELOG.md](https://github.com/ray-di/Ray.WebFormModule/blob/1.x/CHANGELOG.md) for the full list of breaking changes.

### Automated Migration With Claude Code

Ray.WebFormModule ships a Claude Code skill at [`.claude/skills/migrate-to-1.0/SKILL.md`](https://github.com/ray-di/Ray.WebFormModule/blob/1.x/.claude/skills/migrate-to-1.0/SKILL.md) that walks an AI assistant through the rewrites above: annotations to attributes, `antiCsrf=true` split into `#[CsrfProtection]`, `Reader` argument removal, and `FormInterface` signature updates. Copy that directory into your consuming project's `.claude/skills/` and invoke it with `/migrate-to-1.0`.

## Validation Exception

`#[InputValidation]` throws `Ray\WebFormModule\Exception\ValidationException` when validation fails. This is useful for API applications where the HTML representation is not used.

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

Installing `Ray\WebFormModule\FormVndErrorModule` makes methods annotated with `#[FormValidation]` throw the same validation exception on failure.

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

Echo the caught exception's `error` property to get an [application/vnd.error+json](https://tools.ietf.org/html/rfc6906) representation.

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

Add more detail to `vnd.error+json` with the `#[VndError]` attribute.

```php
#[FormValidation(form: "contactForm")]
#[VndError(message: "foo validation failed", logref: "a1000", path: "/path/to/error", href: ["_self" => "/path/to/error", "help" => "/path/to/help"])]
public function createAction()
{
}
```

## Demo

Run the demo application from the Ray.WebFormModule repository.

```bash
php -S docs/demo/1.csrf/web.php
```
