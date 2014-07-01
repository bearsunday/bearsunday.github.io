---
layout: default_ja
title: BEAR.Sunday | はじめてのアスペクト
category: My First - Tutorial
--- 

# はじめてのアスペクト

## 挨拶に現在時間を追加する

[greetingリソース](my_first_resource.html) に現在時刻を追加します。出来上がりイメージはこうです。

```
Hello, BEAR. It is 1:53 now !
```

このようにメッセージの後ろに時間を追加すれば簡単に実現できます。

{% highlight php startinline %}
    public function onGet($name = 'anonymous')
    {
        $time = date('g:i');
        return "{$this->message}, {$name}". " It is {$time} now";
    }
{% endhighlight %}

ではこの現在時刻の追加を他の10のリソースでも行いたいとしたらどうでしょう？
「何かのメッセージの後に時間情報を追加」という処理を他の10のリソースでも行います。
同じ処理を何度もするので関数にしてみます。

{% highlight php startinline %}
    public function onGet($name = 'anonymous')
    {
        return "{$this->message}, {$name}". timeMessage();
    }
{% endhighlight %}

集約され、再利用性が高まりました。

あるいはtraitを使ってみましょうか。

{% highlight php startinline %}
    use TimeMessageTrait;

    public function onGet($name = 'anonymous')
    {
        return "{$this->message}, {$name}". $this->getTimeMessage();
    }
{% endhighlight %}

同じですね。

しかし集約することはできましたが、利用メソッドの数だけ変更が必要です。

今度は時間ではなくて、挨拶の後は天気情報を付加するように変更がありました。
`timeMessage` を `weatherMessage` に変えましょうか？

それとも、後ろにメッセージが付加される事を汎用的に `postMessage` としましょうか...
だんだん無理が出てきました。
このようなメソッドを横断して同じ処理を適用する良い方法はないでしょうか？

## アスペクトにする

このようなメソッドを横断する処理はそもそもコーディングが難しい面があります。
ログやキャッシュ、トランザクションなど何かの処理の前後に行うようなコードは
あちこちに同じものを記述した経験はないでしょうか？

DBのトランザクションのコード、`begin, [query], (commit | rollback)` という処理は
`[query]` が変わるだけなのにいつも同じように全てを記述したりしなかったでしょうか。

ではこの横断的な処理を、元の本質的な処理と合成するようにしてみてはどうでしょうか。
元のメッセージと付加するメッセージを動的に結合するのです。

この例では、「時刻情報を追加する」という処理をクラスをまたがって使われる（＝横断的な）処理とみなし、アスペクトと呼びます。
このアスペクトと元の処理を合成するのがアスペクト指向プログラミングです。

## リフレクティブ・メソッドインボケーション(ReflectiveMethodInvocation)

この横断的な処理と本質的な処理を合成するのにBEAR.Sundayではインターセプターというパターンを使います。

横断処理を元のメソッドの中から利用するのではなくて、元のメソッドを横断処理が横取り（インターセプト）して、横断処理が元のメソッドを呼ぶようにします。

さっきの例でいえば「あいさつ」という処理から「時刻付加」という色々なメソッドから呼ばれる処理（＝横断処理）を呼ぶのではなくて、時刻付加という横断処理からメインの処理「あいさつ」を呼びます。

呼び出す側と呼ばれる側の関係が通常と逆になってるのが分かるでしょうか。
DBアクセスのコードから結果を記録するためにログを呼び出していたのを、
ログのコードからDBアクセスのコードを呼ぶようにするのです。

この時に元のコードの実行そのものがリフレクションを使ったメソッド実行（メソッドインボケーション）オブジェクトになっています。
そのオブジェクトを利用すると呼び出し引数を調べたり、呼び出しそのものを実行できるようになります。
そのオブジェクトを使ってインターセプトします。

## インターセプターを作成します

まずは元メソッドを横取りする横断処理、つまりインターセプターです。

まずは横断処理を「何もしない」インターセプターです。

*apps/Demo.Sandbox/src/Interceptor/TimeMessage.php*

{% highlight php startinline %}
<?php
/**
 * Time message
 */
namespace Demo\Sandbox\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

/**
 * +Time message add interceptor
 */
class TimeMessage implements MethodInterceptor
{
    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();
        return $result;
    }
}
{% endhighlight %}

元のメソッドを実行（`$invocation->proceed()`）し、その結果を返しています。

`$invocation->proceed()` でオリジナルのメソッドを実行し、その後ろに時刻メッセージを追加します。

{% highlight php startinline %}
    public function invoke(MethodInvocation $invocation)
    {
        $time = date('g:i');
        $result = $invocation->proceed() . ". It is {$time} now !";

        return $result;
    }
{% endhighlight %}

## このインターセプターを特定のメソッドにバインドする

これで横断処理から元メソッドの実行を実行するインターセプターができました。
つぎに特定のメソッドとこのアスペクトを束縛（バインド）します。

アノテーションを使う方法が一般的ですが、ここでは使わない最も簡単な方法で行ってみます。

以下のコードを `apps/Demo.Sandbox/src/Module/AppModule.php` の `configure` メソッドに追加します。

*apps/Demo.Sandbox/src/Module/AppModule.php*

{% highlight php startinline %}
    // time message binding
    $this->bindInterceptor(
        $this->matcher->subclassesOf('Demo\Sandbox\Resource\App\First\Greeting\Aop'),
        $this->matcher->any(),
        [$this->requestInjection('Demo\Sandbox\Interceptor\TimeMessage')]
    );
{% endhighlight %}

これで `Demo\Sandbox\Resource\App\First\Greeting\Aop` クラス（およびそのサブクラス）のどのメソッドにも `TimeMessage` インターセプターがバインドされます。

### 実行してみましょう

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get app://self/first/greeting/aop?name=BEAR

200 OK
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Tue, 01 Jul 2014 11:53:50 GMT"]
[BODY]
Hello, BEAR. It is 1:53 now !
```

時刻伝えるアスペクトと挨拶リソースが合成されました！
挨拶リソースは自身が加工編集されることに無関心です。
横断処理が元処理を呼ぶコードに乗っ取られています。

挨拶リソースには依存はなく、
またtraitを使った横断処理の追加と違って束縛は動的です。
このリソースに他のアスペクトを束縛できるし、このアスペクトは他のリソースにも束縛可能です。

このアスペクトの織り込みはリソースだけに適用可能ではありません。
他の一般オブジェクトにも適用可能です。
ただし型が元の型からWeaveという型に変わってしまい、この点では互換性が失われてしまう事に注意が必要です。
