---
layout: docs-en
title: Validation
category: Manual
permalink: /manuals/1.0/en/validation.html
---

# Validation

 * You can define resource APIs in the JSON schema.
 * You can separate the validation code with `#[Valid]`, `#[OnValidate]` attribute.
 * Please see the form for validation by web form.

# JSON Schema

The [JSON Schema](http://json-schema.org/) is the standard for describing and validating JSON objects. `@JsonSchema` and the resource body returned by the method of annotated resource class are validated by JSON schema.


### Install

If you want to validate in all contexts including production, create `AppModule`, if validation is done only during development, create `DevModule` and install within it


```php?start_inline
use BEAR\Resource\Module\JsonSchemaModule; // Add this line
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(
            new JsonSchemaModule(
                $appDir . '/var/json_schema',
                $appDir . '/var/json_validate'
            )
        );  // Add this line
    }
}
```
Create directories for the JSON schema files

```bash
mkdir var/json_schema
mkdir var/json_validate
```

In the `var/json_schema/`, store the JSON schema file which is the specification of the body of the resource, and the `var/json_validate/` stores the JSON schema file for input validation.

### @JsonSchema annotation

Annotate the method of the resource class by adding `@JsonSchema`, then add the `schema` property by specifying the JSON schema file name, which is `user.json` for this purpose.

### schema

src/Resource/App/User.php

```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class User extends ResourceObject
{
    #[JsonSchema('user.json')]
    public function onGet(): static
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

We will create a JSON schema named `/var/json_schema/user.json`

```json
{
  "type": "object",
  "properties": {
    "firstName": {
      "type": "string",
      "maxLength": 30,
      "pattern": "[a-z\\d~+-]+"
    },
    "lastName": {
      "type": "string",
      "maxLength": 30,
      "pattern": "[a-z\\d~+-]+"
    }
  },
  "required": ["firstName", "lastName"]
}
```

### key

If the body has an index key, specify it with the key property of the annotation

```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class User extends ResourceObject
{
    #[JsonSchema(key:'user', schema:'user.json')]
    public function onGet()
    {
        $this->body = [
            'user' => [
                'firstName' => 'mucha',
                'lastName' => 'alfons',
                'age' => 12
            ]
        ];        

        return $this;
    }
}
```

### params

The `params` property specifies the JSON schema file name for the argument validation


```php?start_inline

use BEAR\Resource\Annotation\JsonSchema; // Add this line

class Todo extends ResourceObject
{
    #[JsonSchema(key:'user', schema:'user.json', params:'todo.post.json')]
    public function onPost(string $title)
```

We place the JSON schema file

**/var/json_validate/todo.post.json**

```json
{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "title": "/todo POST request validation",
  "properties": {
    "title": {
      "type": "string",
      "minLength": 1,
      "maxLength": 40
    }
}

```

By constantly verifying in a standardized way instead of proprietary documentation, the specification is **reliable and understandable** to both humans and machines.

### target

To apply schema validation to the representation of the resource object (the rendered result) rather than to the body of the ResourceObject, specify the option `target='view'`.

```php
#[JsonSchema(schema: 'user.json', target: 'view')]
```

### Related Links

 * [Example](http://json-schema.org/examples.html)
 * [Understanding JSON Schema](https://spacetelescope.github.io/understanding-json-schema/)
 * [JSON Schema Generator](https://jsonschema.net/#/editor)

## #[Valid] attribute

The `#[Valid]` attribute is a validation for input.
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

class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        // ...
        $this->install(new ValidateModule);
    }
}
```

### Attribute

There are three attributes `#[Valid]`, `#[OnValidate]`, `#[OnFailure]` for validation.

First of all, annotate the method that you want to validate with `#[Valid]`

```php?start_inline
use Ray\Validation\Annotation\Valid;

class News
{
    #[Valid]
    public function createUser($name)
    {
```

Validation will be conducted in the method annotated with `#[OnValidate]`.

The arguments of the method should be the same as the original method. The method name can be anything.

```php?start_inline
use Ray\Validation\Annotation\OnValidate;

class News
{
    #[OnValidate]
    public function onValidate($name)
    {
        $validation = new Validation;
        if (! is_string($name)) {
            $validation->addError('name', 'name should be string');
        }

        return $validation;
    }
```

Add validations to your elements by `addError()` with the `element name` and` error message` as parameters, then return the validation object.

When validation fails, the exception `Ray\Validation\Exception\InvalidArgumentException` will be thrown,
but if you have a method annotated with the `#[OnFailure]`, it will be called, instead of throwing an exception

```php?start_inline
use Ray\Validation\Annotation\OnFailure;

class News
{
    #[OnFailure]
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

In the method annotated with `#[OnFailure]`, you can access the validated messages with `$failure->getMessages()`
and also you can get the object of the original method with `$failure->getInvocation()`.

### Various validation

If you want to have different validations for a class, you can specify the name of the validation like below

```php?start_inline
use Ray\Validation\Annotation\Valid;
use Ray\Validation\Annotation\OnValidate;
use Ray\Validation\Annotation\OnFailure;

class News
{
    #[Valid('foo')]
    public function fooAction($name, $address, $zip)
    {

    #[OnValidate('foo')]
    public function onValidateFoo($name, $address, $zip)
    {

    #[OnFailure('foo')]
    public function onFailureFoo(FailureInterface $failure)
    {
```

### Other validation

If you need to implement complex validation, you can have another class for validation and inject it.
And then call in the method annotated with the `onValidate`.
You can also change your validation behavior by context with DI.
