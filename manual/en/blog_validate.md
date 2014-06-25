---
layout: default
title: BEAR.Sunday | Blog Tutorial(5) Validation
category: Blog Tutorial
---

# Form 

In the previous section we implemented the POST interface in the posts add page, we were then able to add posts by receiving a HTTP mtethod.

Next we will add to the POST interface validation, filtering, pre populated fields on error functionality as a web form.

Note: In this tutorial we won't use any special libraries we will just code in plain PHP. In reality it might be better to use a validation library that is part of Zend Framework or Symfony.

## Validation 

We will implement a form interceptor that doesn't depend on a specific library. First we will bind the form validation interceptor and the `@Form` annotation.

Annotation *\BEAR\Sunday\Annotation\Form*

{% highlight php startinline %}
<?php
namespace BEAR\Sunday\Annotation;

/**
 * Form
 *
 * @Annotation
 * @Target({"METHOD"})
 */
final class Form implements AnnotationInterface
{
}
{% endhighlight %}

Interceptor binding *src/Module/App/Aspect.php*

{% highlight php startinline %}
    /**
     * @Form - Plain form
     */
    private function installNewBlogPost()
    {
        $this->bindInterceptor(
            $this->matcher->logicalOr(
                $this->matcher->subclassesOf('Demo\Sandbox\Resource\Page\Blog\Posts\Newpost'),
                $this->matcher->subclassesOf('Demo\Sandbox\Resource\Page\Blog\Posts\Edit')
            ),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Form'),
            [$this->requestInjection('Demo\Sandbox\Interceptor\Form\BlogPost')]
        );
    }
{% endhighlight %}

In this case the `Demo\Sandbox\Interceptor\Form\BlogPost` is bound to methods annotated with `@Form`. Before the request calls the POST method this validation interceptor is called.

## @Form Validation Interceptor 

In the interceptor that is wedged between the request and the method, after the tag removal process is when the validation happens. (@TODO make sense?)

{% highlight php startinline %}
return $invocation->proceed();
{% endhighlight %}

If the validation fails then the *processing GET request page* that shows an error message and preset values etc is output. The POST interface method will not be called.

{% highlight php startinline %}
return $page->onGet();
{% endhighlight %}

When this is all wrapped up in the `Demo\Sandbox\Interceptor\Form\BlogPost` it looks like this.

{% highlight php startinline %}
<?php

namespace Demo\Sandbox\Interceptor\Form;

use BEAR\Sunday\Inject\NamedArgsInject;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

/**
 * Post form
 */
class BlogPost implements MethodInterceptor
{
    use NamedArgsInject;

    /**
     * Error
     *
     * @var array
     */
    private $errors = [
        'title' => '',
        'body' => ''
    ];

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        // retrieve page and query
        $args = $this->namedArgs->get($invocation);
        $page = $invocation->getThis();

        // strip tags
        foreach ($args as &$arg) {
            strip_tags($arg);
        }

        // required title
        if ($args['title'] === '') {
            $this->errors['title'] = 'title required.';
        }

        // required body
        if ($args['body'] === '') {
            $this->errors['body'] = 'body required.';
        }

        // valid form ?
        if (implode('', $this->errors) === '') {
            return $invocation->proceed();
        }

        // error, modify 'GET' page wih error message.
        $page['errors'] = $this->errors;
        $page['submit'] = [
            'title' => $args['title'],
            'body' => $args['body']
        ];

        return $page->onGet();
    }
}
{% endhighlight %}

[MethodInterceptor](https://github.com/koriym/Ray.Aop/blob/master/src/Ray/Aop/MethodInterceptor.php) which conforms to the [http://aopalliance.sourceforge.net/ AOP Alliance]. The `$invocation` object passed to the `invoke` method is as it suggests method invoking object of the `MethodInvocation` type.

The parameters at the time the method can be obtained by calling `$invocation->getArguments()` and  
the original page display resource object can be obtained by calling `$invocation->getThis()`.

Note: The parameters are not in the named parameters style, they are the ordered style that can normally be picked up in the method call.
