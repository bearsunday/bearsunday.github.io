---
layout: docs-en
title: Validation
category: Manual
permalink: /manuals/1.0/en/validation.html
---

# Validation

[JSON Schema](http://json-schema.org/) is a vocabulary that allows you to annotate and validate JSON documents.
Validation with the `@Valid` annotation validates the input value with the user's PHP code.
For Web form validation, Please visit [Form](form.html) .


## JSON Schema

When annotating the resource object of BEAR.Sunday as `@JsonSchema`, the resource object `body` (resource state) is verified by the JSON schema.
At this time, the data representation need not be JSON.

### Install

If you want to validate in all contexts, including production, create `AppModule`, if validation is done only during development, create `DevModule` and install within it.


```php?start_inline
use BEAR\Resource\Module\JsonSchemalModule; // Add this line
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        // ...
        $this->install(new JsonSchemalModule);  // Add this line
    }
}
```

### @JsonSchema annotation

Annotate as `@JsonSchema` in the `onGet` method of the class to validate.

**Person.php**

```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class Person extends ResourceObject
{
    /**
     * @JsonSchema
     */
    public function onGet()
    {
        $this->body = [
            'firstName' => 'mucha',
            'lastName' => 'alfons',
            'age' => 12
        ];

        return $this;
    }
}
```

Write the JSON schema with the extension `json` in the same directory.


**Person.json**

```json
{
  "title": "Person",
  "type": "object",
  "properties": {
    "firstName": {
      "type": "string"
    },
    "lastName": {
      "type": "string"
    },
    "age": {
      "description": "Age in years",
      "type": "integer",
      "minimum": 20
    }
  },
  "required": ["firstName", "lastName"],
  "additionalProperties": false
}
```

By using the standardized JSON schema instead of leaving the data format that the developer outputs in its own document,
the restriction can be understood by both humans and machines.
You can make sure the declared schema is correct.


## @Valid annotation

The `@Valid` annotation is a validation for input. 
You can set up validation as AOP for your method.
By separating validation logic from the method, the code will be readable and testable.

Validation libraries are available such as [Aura.Filter](https://github.com/auraphp/Aura.Filter), [Respect\Validation](https://github.com/Respect/Validation), and [PHP Standard Filter](http://php.net/manual/en/book.filter.php)

### Install

Install `Ray.ValidateModule` via composer.

```bash
composer require ray/validate-module
```

Installing `ValidateModule` in your application module `src/Module/AppModule.php`.

```php?start_inline
use Ray\Validation\ValidateModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        // ...
        $this->install(new ValidateModule);
    }
}
```

### Annotation

There are three annotations `@Valid`, `@OnValidate`, `@OnFailure` for validation.

First of all, annotate method that you want to validate with `@Valid`.

```php?start_inline
use Ray\Validation\Annotation\Valid;

class News
{
    /**
     * @Valid
     */
    public function createUser($name)
    {
```

Validation will be conducted in the method annotated with `@OnValidate`.

The arguments of the method should be the same as the original method. The method name is free.

```php?start_inline
use Ray\Validation\Annotation\OnValidate;

class News
{
    /**
     * @OnValidate
     */
    public function onValidate($name)
    {
        $validation = new Validation;
        if (! is_string($name)) {
            $validation->addError('name', 'name should be string');
        }

        return $validation;
    }
```

Add unvalidated elements to your validation object by `addError ()` with `element name` and` error message`, And return the validation object.

When validation fail, the exception `Ray\Validation\Exception\InvalidArgumentException` will be thrown,
but if you have method annotated with `@OnFailure`, it will be called instead of throwing exception.

```php?start_inline
use Ray\Validation\Annotation\OnFailure;

class News
{
    /**
     * @OnFailure
     */
    public function onFailure(FailureInterface $failure)
    {
        // original parameters
        list($this->defaultName) = $failure->getInvocation()->getArguments();

        // errors
        foreach ($failure->getMessages() as $name => $messages) {
            foreach ($messages as $message) {
                echo "Input '{$name}': {$message}" . PHP_EOL;
            }
        }
    }
```

In the method annotated with `@OnFailure`, you can access to the validated messages with `$failure->getMessages()`
and also you can get the object of the original method with `$failure->getInvocation()`.

### Various validation

If you want to have various validation for a class, you can specify the name of validation like below.

```php?start_inline
use Ray\Validation\Annotation\Valid;
use Ray\Validation\Annotation\OnValidate;
use Ray\Validation\Annotation\OnFailure;

class News
{
    /**
     * @Valid("foo")
     */
    public function fooAction($name, $address, $zip)
    {

    /**
     * @OnValidate("foo")
     */
    public function onValidateFoo($name, $address, $zip)
    {

    /**
     * @OnFailure("foo")
     */
    public function onFailureFoo(FailureInterface $failure)
    {
```

### Other validation

If you need to implement the complex validation, you can have another class for validation and inject it.
And then call in the method annotated with `onValidate`.
You can also change your validation behavior by context with DI.
