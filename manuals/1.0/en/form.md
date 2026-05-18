---
layout: docs-en
title: Form
category: Manual
permalink: /manuals/1.0/en/form.html
---

# Form

Web form handling powered by [Aura.Input](https://github.com/auraphp/Aura.Input) and [Aura.Filter](https://github.com/auraphp/Aura.Filter) aggregates related functionality into a single class, making it easy to test and modify. A single form class can serve both rendering and validation.

## Install

Install `ray/web-form-module` with composer:

```bash
composer require ray/web-form-module
```

Install `AuraInputModule` in your application module at `src/Module/AppModule.php`:

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

## Form Class

Create a **form class** that defines input elements and validation rules, then bind it to a method with the `#[FormValidation]` attribute. The method only runs when validation succeeds.

```php
use Ray\WebFormModule\AbstractForm;
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractForm
{
    use SetAntiCsrfTrait;

    public function init(): void
    {
        // register input fields
        $this->setField('name', 'text')
             ->setAttribs(['id' => 'name']);

        // set validation rules and user-defined error messages
        $this->filter->validate('name')->is('alnum');
        $this->filter->useFieldMessage('name', 'Name must be alphabetic only.');
    }
}
```

Register input elements inside `init()` and apply validation and sanitization rules. See:

- [Rules To Validate Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/validate.md)
- [Rules To Sanitize Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/sanitize.md)

The associative array of method arguments is validated. To change the input, implement `SubmitInterface::submit()` and return the values to use.

## #[FormValidation] Attribute

A method annotated with `#[FormValidation]` is validated before execution against the form object referenced by the `form` property. When validation fails, the method whose name is suffixed with `ValidationFailed` is invoked instead:

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
        // validation success
    }

    public function onPostValidationFailed(string $name, int $age): ResourceObject
    {
        // validation failure
    }
}
```

Use the `form` property of `#[FormValidation]` to point to a different form property, and `onFailure` to specify a custom failure-handler method name:

```php
#[FormValidation(form: 'contactForm', onFailure: 'badRequestAction')]
public function onPost(string $name, int $age): ResourceObject
{
}
```

The submitted arguments are forwarded to the failure-handler method.

## View

Pass the element name to render the `input` markup or to fetch the error message:

```php
$form->input('name');  // e.g. <input id="name" type="text" name="name" size="20" maxlength="20" />
$form->error('name');  // e.g. "Name must be alphabetic only."
```

The same works in Twig templates:

```twig
{% raw %}{{ form.input('name') }}
{{ form.error('name') }}{% endraw %}
```

If the form class implements `ToStringInterface`, the whole form can be rendered by casting it to string:

```php
echo $form;  // render the entire form HTML
```

## CSRF Protections

CSRF protection is **opt-in**. A form that uses `SetAntiCsrfTrait` is wired with an `AntiCsrfInterface`, but the token is only verified on methods annotated with `#[CsrfProtection]`. Methods without `#[CsrfProtection]` perform no CSRF check even if the form has an `AntiCsrf` object set.

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
        // executed only when the CSRF token is valid
    }
}
```

To raise the security level, provide a custom CSRF class that incorporates user authentication and set it on the form. See [Applying CSRF Protections](https://github.com/auraphp/Aura.Input#applying-csrf-protections) in Aura.Input for details.

## #[InputValidation]

If a method is annotated with `#[InputValidation]` instead of `#[FormValidation]`, a `Ray\WebFormModule\Exception\ValidationException` is thrown when validation fails. No HTML representation is used, which is convenient for Web APIs.

`echo`ing the `error` property of the caught exception outputs an [application/vnd.error+json](https://github.com/blongden/vnd.error) representation:

```php
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
```

Use the `#[VndError]` attribute to enrich the `vnd.error+json` payload:

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

Install `Ray\WebFormModule\FormVndErrorModule` to make `#[FormValidation]` methods throw the same exception as `#[InputValidation]` methods. Page resources can then be reused as API endpoints:

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

## Migration from 0.x

Version 1.0 drops Doctrine Annotations in favour of native PHP 8 Attributes and turns CSRF protection into opt-in via `#[CsrfProtection]`, among other breaking changes. See the [Ray.WebFormModule README](https://github.com/ray-di/Ray.WebFormModule#migration-from-0x) and the [CHANGELOG](https://github.com/ray-di/Ray.WebFormModule/blob/1.x/CHANGELOG.md) for the upgrade steps.

## Demo

Try the demo app [MyVendor.ContactForm](https://github.com/bearsunday/MyVendor.ContactForm) to see how a confirmation form and multiple forms on a single page work.
