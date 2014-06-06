---
layout: default_ja
title: BEAR.Sunday | Aspect Orientated Programming 
category: Manual
---

Aspect Oriented Framework
=========================

**Ray.Aop** パッケージはメソッドインターセプションの機能を提供します。マッチするメソッドが実行される度に実行されるコードを記述する事ができます。トランザクション、セキュリティやログといった横断的な”アスペクト”に向いています。なぜならインターセプターが問題をオブジェクトというよりアスペクトに分けるからです。これらの用法はアスペクトオリエンティッドプログラム（AOP）と呼ばれます。

[Matcher](http://koriym.github.io/Ray.Aop/api/interfaces/Ray_Aop_Matchable.html) は値を受け取ったり拒否したりするシンプルなインターフェイスです。例えばRay.Aopでは２つの **Matcher** が必要です。１つはどのクラスに適用するかを決め、もう１つはそのクラスのどのメソッドに適用するかを決めます。これらを簡単に利用するためのファクトリークラスがあります。

[MethodInterceptors](http://koriym.github.io/Ray.Aop/api/interfaces/Ray_Aop_MethodInterceptor.html) はマッチしたメソッドが呼ばれる度に実行されます。呼び出しやメソッド、それらの引き数、インスタンスを調べる事ができます。横断的なロジックと委譲されたメソッドが実行されます。最後に返り値を調べて返します。インターセプターは沢山のメソッドに適用され沢山のコールを受け取るので、実装は効果的で透過的なものになります。

Example: Forbidding method calls on weekends
--------------------------------------------

メソッドインターセプターがRay.Aopでどのように機能するかを明らかにするために、終末にはピザの注文を禁止するようにしてみましょう。デリバリーは平日だけ受け付ける事にして、ピザの注文を週末には受け付けないようにします！この例はAOPで認証を使用するときにのパターンと構造的に似ています。

週末だけにするための[アノテーション](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html)を定義します。

```php
<?php
/**
 * NotOnWeekends
 *
 * @Annotation
 * @Target("METHOD")
 */
final class NotOnWeekends
{
}
```

インターセプトさせるメソッドに適用します。

```php
<?php
class RealBillingService
{
    /**
     * @NotOnWeekends
     */
    chargeOrder(PizzaOrder $order, CreditCard $creditCard)
    {
```

次に、MethodInterceptorインターフェイスを実装します。元のメソッドを実行するためには **$invocation->proceed()** と実行します。

```php
<?php
class WeekendBlocker implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $today = getdate();
        if ($today['weekday'][0] === 'S') {
            throw new \RuntimeException(
          		$invocation->getMethod()->getName() . " not allowed on weekends!"
            );
        }
        return $invocation->proceed();
    }
}
```

設定完了しました。このコードでは「どのクラスでも」「メソッドに@NotOnWeekendsアノテーション」という条件にマッチします。

```php
<?php
$bind = new Bind;
$matcher = new Matcher(new Reader);
$interceptors = [new WeekendBlocker];
$pointcut = new Pointcut(
		$matcher->any(),
		$matcher->annotatedWith('Ray\Aop\Sample\Annotation\NotOnWeekends'),
		$interceptors
);
$bind->bind('Ray\Aop\Sample\AnnotationRealBillingService', [$pointcut]);

$compiler = require dirname(__DIR__) . '/scripts/instance.php';
$billing = $compiler->newInstance('RealBillingService', [], $bind);
try {
    echo $billing->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
```

全てをまとめ（土曜日まで待って）、メソッドをコールするとインターセプターにより拒否されます。

```php
<?php
RuntimeException: chargeOrder not allowed on weekends! in /apps/pizza/WeekendBlocker.php on line 14

Call Stack:
    0.0022     228296   1. {main}() /apps/pizza/main.php:0
    0.0054     317424   2. Ray\Aop\Weaver->chargeOrder() /apps/pizza/main.php:14
    0.0054     317608   3. Ray\Aop\Weaver->__call() /libs/Ray.Aop/src/Weaver.php:14
    0.0055     318384   4. Ray\Aop\ReflectiveMethodInvocation->proceed() /libs/Ray.Aop/src/Weaver.php:68
    0.0056     318784   5. Ray\Aop\Sample\WeekendBlocker->invoke() /libs/Ray.Aop/src/ReflectiveMethodInvocation.php:65
```

Explicit method name match
---------------------------

```php
<?php
	$bind = new Bind;
	$bind->bindInterceptors('chargeOrder', [new WeekendBlocker]);

    $compiler = require dirname(__DIR__) . '/scripts/instance.php';
	$billing = $compiler->newInstance('RealBillingService', [], $bind);
	try {
	   echo $billing->chargeOrder();
	} catch (\RuntimeException $e) {
	   echo $e->getMessage() . "\n";
	   exit(1);
	}
```

Limitations
-----------

この機能の背後ではメソッドのインターセプションを事前にコードを生成する事で可能にしています。Ray.Aopはダイナミックにサブクラスを生成してメソッドをオーバーライドするインターセプターを適用します。
クラスとメソッドは以下のものである必要があります。

 * クラスは *final* ではない
 * メソッドは *public*
 * メソッドは *final* ではない

AOP Alliance
------------
このメソッドインターセプターのAPIは [AOP Alliance](http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/MethodInterceptor.html) の実装です。

## インターセプター

インターセプターはメソッドの呼び出しに割り込んで、クラスの横断的処理を行います。インターセプターはinvokeメソッドを実装し、そのメソッド内でオリジナルのメソッドを呼び出す事で横断的処理を実現します。

```php
<?php
public function invoke(MethodInvocation $invocation);
```

以下は受け取った引数と実行した出力をログに記録するロガーインターセプターです。

```php
<?php
class Logger implements MethodInterceptor
{
    use LogInject;

    public function invoke(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();
        $class = get_class($invocation->getThis());
        $args = $invocation->getArguments();
        $input = json_encode($args);
        $output = json_encode($result);
        $log # "target = [{$class}], input = [{$input}], result  [{$output}]";
        $this->log->log($log);
        return $result;
    }
}
```

このインターセプターにはインジェクトされたLogオブジェクトを使って、呼び出し引数とその結果をJSON形式でログに記録します。
このロガーがバインドされたメソッドには何の変更もありませんがログ機能が追加されました。

元のメソッドはロガーの更新、着脱に元のメソッドは無関心です。関心は元のメソッドが本来もつ **本質的関心時（core concern）** と、
ログをとるというメソッドをまたいで適用される **横断的関心事（cross cutting concern）** に分離されています。

フレームワークで使用する場合にはドメインロジックとアプリケーションロジックに分かれてるともいえます。例えばドメインロジックは「ユーザーリソースのSQL操作」、
アプリケーションロジックはそれをアプリケーションとして有効に利用するように「ログ」や「トランザクション」です。

ロガーが使うログオブジェクトもインジェクトされ、ロガーはそのログオブジェクトの実装に依存することなく抽象（インターフェイス）に依存しています。

## マッチャーバインディング

作成したインターセプターはメソッドにバインドすることで機能します。どのメソッドにバインドするかに利用するのがmatcher です。以下はログオブジェクトをインジェクトしたLoggerオブジェクトをBEAR\Resource\Objectを継承したクラスの'on'で始まる全てのメソッドに束縛します。

```php
<?php
$logger = $this->requestInjection('BEAR\Framework\Interceptor\Logger');
$this->bindInterceptor(
    $this->matcher->subclassesOf('BEAR\Resource\ResourceObject'),
    $this->matcher->startWith('on'),
    [$logger]
);
```

bindInterceptorは３つのパラメーターをとり、１つめがクラスマッチ、２つ目がメソッドマッチ、３つ目がインターセプターです。

| Method Signature |　Function |
|----------------- | ----------|
| bool subclassesOf($class) | サブクラスを指定します。第二引数には指定できません。  |
| bool any() | どれにもマッチします。|
| bool annotatedWith($annotation) | $annotationはアノテーションのフルパスです。このアノテーションが付いているものにマッチします。 |
| bool startWith($prefix) | 指定した文字列で始まるクラス／メソッドにマッチします。|

例えば以下をメソッドマッチで指定するとsetXXという名前のメソッドにマッチします。

```php
<?php
$this->matcher->startWith('set');
```

## MethodInvocation

インターセプターはMethodInvocation?（メソッド実行）型の変数を受け取り、メソッドの実行の前後に処理を挟んだり、その変数を使って元のメソッドを実行します。MethodInvocationの主なメソッドは以下の通りです。

| Method Signature |　Function |
|----------------- | ----------|
| void proceed() | 対象メソッド実行  |
| Reflectionmethod getMethod() | 対象メソッドリフレクションの取得 |
| Object getThis() | 対象オブジェクトの取得  |
| array getArguments() | 呼び出し引数配列の取得  |
| array getAnnotations() | 対象メソッドのアノテーション取得 |

## 名前付き引数

メソッドインターセプターの引数は通常のPHPのファンクション呼び出しと同じく順序による変数です。
これを変数名をキーに、値を変数の値にした名前付き変数の連想配列に変換することができます。

```php
 public function onGet($userId)
```

上記の`$userId`という変数はこのように利用します。

```php
use NamedArgsInject;

public function invoke(MethodInvocation $invocation)
{
    $args = $this->namedArgs->get($invocation);
    $userId = $args['userId'] // 引数$userIdの値
    ...
```
