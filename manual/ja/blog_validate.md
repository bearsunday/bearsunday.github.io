---
layout: default_ja
title: BEAR.Sunday | ブログチュートリアル バリデーション
category: Blog Tutorial
---

# バリデーション

前回のセクションで記事追加ページにPOSTインターフェイスが実装され、記事の追加をHTTPメソッドで受ける事ができるようになりました。 

次はできあがったPOSTインターフェイスに、バリデーション、フィルター、エラー再入力時のデフォルト値設定などのWebフォームとして機能を加えましょう。

Note: このチュートリアルでは特別な専用ライブラリを使用しないでプレーンなPHPでコーディングしています。実際にはZend FrameworkやSymfony、あるいはその他のバリデーションライブラリやフォームライブラリーを利用するのがいいでしょう。

## Formインターセプターの実装

特定のライブラリに依存しないフォームをインターセプターとして実装してみます。まずは `@Form` アノテーションとフォームバリデーションインターセプターの束縛です。

アノテーション *\BEAR\Sunday\Annotation\Form*

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

インターセプターの束縛 *src/Module/App/Aspect.php*

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

これで `@Form` とアノテートされているメソッドに `Demo\Sandbox\Interceptor\Form\BlogPost` が束縛されました。リクエストがPOSTメソッドをコールする前にこのバリデートインターセプターが呼ばれます。

最後に `src/Resource/Page/Blog/Posts/Newpost.php` の `onPost` メソッドのコメントに `@Form` アノテーションを追加します。忘れずに、 `use BEAR\Sunday\Annotation\Form;` も追加してください。

## BlogPostインターセプター

リクエストとメソッドに割り込んだインターセプターでは、タグを取り除くフィルター処理の後にバリデーションをしています。バリデーションが通れば元のPOSTメソッドを呼びます。

{% highlight php startinline %}
return $invocation->proceed();
{% endhighlight %}

バリデーションNGならエラーメッセージや初期値などをセットし *加工したGETリクエストのページ* を出力します。POSTインターフェイスメソッドは呼ばれません。

{% highlight php startinline %}
return $page->onGet();
{% endhighlight %}

すべてをまとめた `Demo\Sandbox\Interceptor\Form\BlogPost` はこのようになります。

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
        // retrieve query (reference)
        $args = $invocation->getArguments();
        // retrieve page
        $page = $invocation->getThis();

        // change values of query
        // strip tags
        foreach ($args as &$arg) {
            $arg = strip_tags($arg);
        }

        // retrieve named query. this is copy of values, not reference
        $args = $this->namedArgs->get($invocation); // this is copy of args

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

        // on PUT we need id
        $id = isset($args['id']) ? $args['id'] : null;
        
        // error, modify 'GET' page wih error message.
        $page['errors'] = $this->errors;
        $page['submit'] = [
            'title' => $args['title'],
            'body' => $args['body']
        ];

        return $page->onGet($id);
    }
}
{% endhighlight %}

[Aopアライアンス](http://aopalliance.sourceforge.net/) 準拠の [MethodInterceptor](https://github.com/koriym/Ray.Aop/blob/master/src/Ray/Aop/MethodInterceptor.php) インターフェイスを実装します。`invoke` メソッドに渡される `$invocation` は `MethodInvocation` 型のメソッド実行オブジェクトです。

`$invocation->getArguments()` でメソッド呼び出し時の引数が、
`$invocation->getThis()` で呼び出し元の記事表示ページリソースオブジェクトが得られています。

Note: 引数は名前付き引数でなく、メソッドコールの時と同じ様に順番で指定され渡ります。

`$this->namedArgs->get($invocation)` ではメソッド呼び出し時の引数が名前付きので得られますが、これは値のコピーであり、変更しても呼び出し元には影響しません。
