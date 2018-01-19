---
layout: docs-en
title: Form
category: Manual
permalink: /manuals/1.0/en/form.html
---

# Form

Each related function of Web Forms using [Aura.Input](https://github.com/auraphp/Aura.Input) and [Aura.Filter](https://github.com/auraphp/Aura.Filter) is aggregated to a single class so that it is easy to test and change.
We can use a corresponding class for the use of Web Forms and validation.

## Install

Install `ray/web-form-module` via composer to add form using Aura.Input

```bash
composer require ray/web-form-module
```

Install `AuraInputModule` in our application module `src/Module/AppModule.php`

```php?start_inline
use Ray\Di\AbstractModule;
use Ray\WebFormModule\WebFormModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new AuraInputModule);
    }
}
```

##  Web Form

Create **a form class** that defines the registration and the rules of form elements, then bind it to a method using `@FormValidation` annotation.
The method runs only when the sent data is validated.

```php?start_inline
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
```

We can register the input elements in the `init()` method of the form class and apply the rules of validation and sanitation.
Please refer to [Rules To Validate Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/validate.md) of Aura.Filter with respect to validation rules, and [Rules To Sanitize Fields](https://github.com/auraphp/Aura.Filter/blob/2.x/docs/sanitize.md) with respect to sanitize rules.

We validate an associative array of the argument of the method.
If we want to change the input, we can set the values by implementing `submit()` method of `SubmitInterface` interface.

## @FormValidation Annotation

Annotate the method that we want to validate with the `@FormValidation`, so that the validation is done in the form object specified by the `form` property before execution.
When validation fails, the method with the `ValidationFailed` suffix is called.

```php?start_inline
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
     * // or
     * @FormValidation(form="form", onFailure="onPostValidationFailed")
     */
    public function onPost($name, $age)
    {
        // validation success
    }

    public function onPostValidationFailed($name, $age)
    {
        // validation failed
    }
}
```

We can explicitly specify the name and the method by changing the `form` property of `@FormValidation` annotation or the `onValidationFailed` property.

The submit parameters will be passed to the `onPostValidationFailed` method.

### View

Specify the element name to get the `input` elements and error messages

```php?start_inline
  $form->input('name'); // <input id="name" type="text" name="name" size="20" maxlength="20" />
  $form->error('name'); // "Please enter a double-byte characters or letters in the name." or blank
```

The same applies to Twig template

```php?start_inline
{% raw %}{{ form.input('name') }}
{{ form.error('name') }}{% endraw %}
```

### Cross site request forgeries（CSRF）Protections

We can add a CSRF object to the form to apply CSRF protections.

```php?start_inline
use Ray\WebFormModule\SetAntiCsrfTrait;

class MyForm extends AbstractAuraForm
{
    use SetAntiCsrfTrait;
```

In order to increase the security level, add a custom CSRF class that contains the user authentication to the form class.
Please refer to the [Applying CSRF Protections](https://github.com/auraphp/Aura.Input#applying-csrf-protections) of Aura.Input for more information.

## @InputValidation Annotation

If we annotate the method with `@InputValidation` instead of `@FormValidation`, the exception `Ray\WebFormModule\Exception\ValidationException` is thrown when validation fails.
For convenience, HTML representation is not used in this case.

When we `echo` the `error` property of the caught exception, we can see the representation of the media type [application/vnd.error+json](https://github.com/blongden/vnd.error).

```php?start_inline
http_response_code(400);
echo $e->error;

// {
//     "message": "Validation failed",
//     "path": "/path/to/error",
//     "validation_messages": {
//         "name": [
//             "Please enter a double-byte characters or letters in the name."
//         ]
//     }
// }
```

We can add the necessary information to `vnd.error+json` using `@VndError` annotation.

```php?start_inline
/**
 * @FormValidation(form="contactForm")
 * @VndError(
 *   message="foo validation failed",
 *   logref="a1000", path="/path/to/error",
 *   href={"_self"="/path/to/error", "help"="/path/to/help"}
 * )
 */
 public function onPost()
```

## FormVndErrorModule

If we install `Ray\WebFormModule\FormVndErrorModule`, the method annotated with `@FormValidation`
will throw an exception in the same way as the method annotated with `@InputValidation`.
We can use the page resources as API.

```php?start_inline
use Ray\Di\AbstractModule;
use Ray\WebFormModule\FormVndErrorModule;

class FooModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new AuraInputModule);
        $this->override(new FormVndErrorModule);
    }
}
```

## Demo

Try the demo app [MyVendor.ContactForm](https://github.com/bearsunday/MyVendor.ContactForm) to get an idea on how forms such as
a confirmation form and multiple forms in a single page work.
