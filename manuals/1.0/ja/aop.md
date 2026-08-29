---
layout: docs-ja
title: AOP
category: Manual
permalink: /manuals/1.0/ja/aop.html
---
# AOP

ログ、認証、トランザクション、キャッシュ。こうした処理は特定のクラスに収まらず、アプリケーション全体に横断して現れます。アスペクト指向プログラミング（AOP）はこの**横断的関心事**をメソッド本体から分離します。対象メソッドはビジネスロジックなどの本質的関心事だけを記述し、横断的処理は**インターセプター**としてメソッドの前後に織り込みます。呼び出し側のコードは何も変わりません。

BEAR.SundayのAOPは[Ray.Aop](https://github.com/ray-di/Ray.Aop)によるもので、Javaの[AOP Alliance](http://aopalliance.sourceforge.net/)に準拠しています。

## インターセプター

インターセプターは`invoke`メソッドで`MethodInvocation`（メソッド実行オブジェクト）を受け取ります。`proceed()`が元のメソッドを実行するので、その前後に横断的処理を記述します。

```php
use Ray\Aop\MethodInterceptor;
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

元のメソッド実行を完全に手中にできるのがポイントです。`proceed()`を呼ばずに値を返せばメソッド実行そのものを省略でき（キャッシュヒット時など）、`try/catch`で囲めば例外を横断的に処理でき、戻り値を加工することもできます。インターセプター自身もインジェクターが生成するので、コンストラクタで依存を受け取れます。

## 束縛

どのメソッドに織り込むかは、対象メソッドの側には書きません。[モジュール](module.html)で対象となるクラスとメソッドを`Matcher`で"検索"し、マッチしたメソッドにインターセプターを束縛します。

```php
$this->bindInterceptor(
    $this->matcher->any(),                   // どのクラスでも
    $this->matcher->startsWith('delete'),    // "delete"で始まるメソッド名のメソッドには
    [Logger::class]                          // Loggerインターセプターを束縛
);

$this->bindInterceptor(
    $this->matcher->subclassesOf(AdminPage::class),  // AdminPageの継承または実装クラスの
    $this->matcher->annotatedWith(Auth::class),      // #[Auth]アトリビュートが付与されたメソッドには
    [AdminAuthentication::class]                     // AdminAuthenticationインターセプターを束縛
);
```

2つ目の例では、対象メソッドの側に残るのは`#[Auth]`という宣言だけです。「認証が必要」という意図はメソッドに、その実装と適用範囲はモジュールに、と役割が分かれます（アトリビュートについては[アトリビュート](attribute.html)を参照）。

束縛をモジュールに集約する形には、それゆえのご利益があります。第一に、横断的処理がどこに適用されるかが束縛の宣言として一覧でき、コードを探し回る必要がないこと。第二に、束縛はコンテキストごとに切り替えられること。プロダクションでだけキャッシュを織り込み、開発でだけ実行時間ログを織り込む、といった構成がアプリケーションコードの変更なしに実現します。織り込みは実行のたびにリフレクションで行うのではなく、コード生成によって行われるため、実行時のオーバーヘッドは最小限です。

`Matcher`には以下の指定が可能です。

* [Matcher::any](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) - 無制限
* [Matcher::annotatedWith](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) - アトリビュート/アノテーション
* [Matcher::subclassesOf](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) - 継承または実装されたクラス
* [Matcher::startsWith](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) - 名前の始めの文字列
* [Matcher::logicalOr](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) - OR条件
* [Matcher::logicalAnd](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) - AND条件
* [Matcher::logicalNot](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) - NOT条件

## MethodInvocation

インターセプターに渡される`MethodInvocation`からは、対象メソッドの実行に関わるオブジェクト・メソッド・引数にアクセスできます。

* [MethodInvocation::proceed](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php) - 対象メソッド実行
* [MethodInvocation::getMethod](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php) - 対象メソッドリフレクションの取得
* [MethodInvocation::getThis](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php) - 対象オブジェクトの取得
* [MethodInvocation::getArguments](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Invocation.php) - 呼び出し引数配列の取得

取得したリフレクションからは、対象に付与されたアトリビュート/アノテーションを読み取れます。`#[Auth('admin')]`のようにアトリビュートへ渡した値を、インターセプターの動作パラメータとして使えます。

```php
$method = $invocation->getMethod();
$class = $invocation->getMethod()->getDeclaringClass();
```

* `$method->getAnnotations()`    - メソッドアトリビュートの取得
* `$method->getAnnotation($name)`
* `$class->getAnnotations()`     - クラスアトリビュートの取得
* `$class->getAnnotation($name)`

## カスタムマッチャー

用意されたマッチャーで足りなければ、`AbstractMatcher`を継承して`matchesClass`と`matchesMethod`の2つを実装します。いずれもマッチしたかどうかをboolで返します。名前に特定の文字列が含まれるかを調べる`contains`マッチャーの例です。

```php
use Ray\Aop\AbstractMatcher;

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
        [$contains] = $arguments;

        return str_contains($class->name, $contains);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        [$contains] = $arguments;

        return str_contains($method->name, $contains);
    }
}
```

組み込みマッチャーと同じように`bindInterceptor()`に渡せます。

```php
class AppModule extends AbstractAppModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            new ContainsMatcher('user'), // 'user'がメソッド名に含まれているか
            [UserLogger::class]
        );
    }
};
```
