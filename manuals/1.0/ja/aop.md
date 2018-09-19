---
layout: docs-ja
title: AOP
category: Manual
permalink: /manuals/1.0/ja/aop.html
---
# AOP

アスペクト指向プログラミングは、**横断的関心事**の問題を解決します。対象メソッドの前後に任意の処理をインターセプターで織り込むことができます。
対象となるメソッドはビジネスロジックなど本質的関心事のみに関心を払い、インターセプターはログや検証などの横断的関心事に関心を払います。

BEAR.Sundayは[AOP Alliance](http://aopalliance.sourceforge.net/)に準拠したアスペクト指向プログラミングをサポートします。

## インターセプター

インターセプターの`invoke`メソッドで`$invocation`メソッド実行変数を受け取り、メソッドの前後に処理を加えます。
この変数は、インターセプター元メソッドを実行するためだけの変数です。前後にログやトランザクションなどの横断的処理を記述します。

```php?start_inline
use Ray\Aop\MethodInvocation;

class MyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        // メソッド実行前の処理
        // ...

        // メソッド実行
        $result = $invocation->proceed();

        // メソッド実行後の処理
        // ...

        return $result;
    }
}
```

## 束縛

[モジュール](module.html)で対象となるクラスとメソッドを`Matcher`で"検索"して、マッチするメソッドにインターセプターを束縛します。

```php?start_inline
$this->bindInterceptor(
    $this->matcher->any(),                   // どのクラスでも
    $this->matcher->startsWith('delete'),    // "delete"で始まるメソッド名のメソッドには
    [Logger::class]                          // Loggerインターセプターを束縛
);

$this->bindInterceptor(
    $this->matcher->subclassesOf(AdminPage::class),  // AdminPageの継承または実装クラスの
    $this->matcher->annotatedWith(Auth::class),      // @Authアノテーションがアノテートされているメソッドには
    [AdminAuthentication::class]                     // AdminAuthenticationインターセプターを束縛
);
```

`Matcher`は他にこのような指定もできます。

 * [Matcher::any](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L16) - 無制限
 * [Matcher::annotatedWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L23) - アノテーション
 * [Matcher::subclassesOf](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L30) - 継承または実装されたクラス
 * [Matcher::startsWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L37) - 名前の始めの文字列
 * [Matcher::logicalOr](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L44) - OR条件
 * [Matcher::logicalAnd](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L51) - AND条件
 * [Matcher::logicalNot](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L58) - NOT条件
```

インターセプターに渡される`MethodInvocation`で対象のメソッド実行に関連するオブジェクトやメソッド、引数にアクセスすることができます。

 * [MethodInvocation::proceed](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php) - 対象メソッド実行
 * [MethodInvocation::getMethod](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php) -  対象メソッドリフレクションの取得
 * [MethodInvocation::getThis](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php) - 対象オブジェクトの取得
 * [MethodInvocation::getArguments](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Invocation.php) - 呼び出し引数配列の取得


リフレクションのメソッドでアノテーションを取得することができます。

```php?start_inline
$method = $invocation->getMethod();
$class = $invocation->getMethod()->getDeclaringClass();
```

 * `$method->getAnnotations()`     - メソッドアノテーションの取得
 * `$method->getAnnotation($name)`
 * `$class->getAnnotations()`    - クラスアノテーションの取得
 * `$class->getAnnotation($name)`

## カスタムマッチャー

独自のカスタムマッチャーを作成するためには`AbstractMatcher`の`matchesClass`と`matchesMethod`を実装したクラスを作成します。

`contains` マッチャーを作成するためには、２つのメソッドを持つクラスを提供する必要があります。
１つはクラスのマッチを行う`matchesClass`メソッド、もう１つはメソッドのマッチを行う`matchesMethod`メソッドです。いずれもマッチしたかどうかをboolで返します。

```php?start_inline
use Ray\Aop\AbstractMatcher;
use Ray\Aop\Matcher;

/**
 * 特定の文字列が含まれているか
 */
class ContainsMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        list($contains) = $arguments;

        return (strpos($class->name, $contains) !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        list($contains) = $arguments;

        return (strpos($method->name, $contains) !== false);
    }
}
```

モジュール

```php?start_inline
class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            new ContainsMatcher('user'), // 'user'がメソッド名に含まれてるか
            [UserLogger::class]
        );
    }
};
```
