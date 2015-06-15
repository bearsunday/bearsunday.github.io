---
layout: docs-en
title: Validation
category: Manual
permalink: /manuals/1.0/en/validation.html
---

# Validation

In this section, we'll cover how to implement validation using AOP with BEAR.Sunday.

Using `@Valid` annotation, you can set up validation as AOP for your method.
By separating validation logic from the method, the code will be readable and testable.

Validation libraries are available such as [Aura.Filter](https://github.com/auraphp/Aura.Filter), [Respect\Validation](https://github.com/Respect/Validation), and [PHP Standard Filter](http://php.net/manual/en/book.filter.php)

## Install

Install `Ray.ValidateModule` via composer.

{% highlight bash %}
composer require ray/validate-module
{% endhighlight %}

Installing `ValidateModule` in your application module `src/Module/AppModule.php`.

{% highlight php %}
<?php
use Ray\Validation\ValidateModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        // ...
        $this->install(new ValidateModule);
    }
}
{% endhighlight %}

## Annotation

There are three annotations `@Valid`, `@OnValidate`, `@OnFailure` for validation.

First of all, annotate method that you want to validate with `@Valid`.

{% highlight php %}
<?php
use Ray\Validation\Annotation\Valid;
// ...
    /**
     * @Valid
     */
    public function createUser($name)
    {
{% endhighlight %}

Validation will be conducted in the method annotated with `@OnValidate`. 

The arguments of the method should be the same as the original method. The method name is free.

{% highlight php %}
<?php
use Ray\Validation\Annotation\OnValidate;
// ...
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
{% endhighlight %}

Add unvalidated elements to your validation object by `addError ()` with `element name` and` error message`, And return the validation object.

When validation fail, the exception `Ray\Validation\Exception\InvalidArgumentException` will be thrown,
but if you have method annotated with `@OnFailure`, it will be called instead of throwing exception.

{% highlight php %}
<?php
use Ray\Validation\Annotation\OnFailure;
// ...
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
{% endhighlight %}

In the method annotated with `@OnFailure`, you can access to the validated messages with `$failure->getMessages()`
and also you can get the object of the original method with `$failure->getInvocation()`.

## Various validation

If you want to have various validation for a class, you can specify the name of validation like below.

{% highlight php %}
<?php
use Ray\Validation\Annotation\Valid;
use Ray\Validation\Annotation\OnValidate;
use Ray\Validation\Annotation\OnFailure;
// ...

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
{% endhighlight %}

## Other validation

If you need to implement the complex validation, you can have another class for validation and inject it.
And then call in the method annotate with `onValidate`.
You can also change your validation behavior by context with DI.

