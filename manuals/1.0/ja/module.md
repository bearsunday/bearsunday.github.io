---
layout: docs-ja
title: モジュール
category: Manual
permalink: /manuals/1.0/ja/module.html
---
# モジュール

モジュールは独立したライブラリの機能をDIやAOPで特定のインターフェイスやメソッドの束縛の集合でフレームワークの機能を拡張します。

例えば`LoggerInterface`に`MonoLog`を束縛すると`LoggerInterface`で宣言したコンストラクタに`MonoLog`がインジェクトされるようになり、`@Loggable`とメソッドにアノテートしたメソッドにロガーを束縛すると、実行結果がログされるようなります。前者がDI、後者がAOPの束縛です。

起点となるモジュールが`AppModule`です。アプリケーションモジュールクラスは`/src/Module/AppModule.php`に配置されいていて必要とする機能のモジュールを`install`することでアプリケーション全体を構成します。

{% highlight php %}
<?php
class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // package標準のモジュール
        $this->install(new PackageModule(new AppMeta('BEAR\HelloWorld')));
        // 追加モジュール
        $this->install(new MyModule1));
        $this->install(new MyModule2));
    }
}
{% endhighlight %}

モジュールではDIとAOPの設定を行います。

## DIの設定

BEAR.Sundayの使用するRay.Diでは、インターフェイスとクラスやそのクラスを生成するファクトリー等を束縛(バインド)してオブジェクトグラフを構成します。

{% highlight php %}
<?php
// クラスの束縛
$this->bind($interface)->to($class);
// プロバイダー（ファクトリー）の束縛
$this->bind($interface)->toProvider($provider);
// インスタンス束縛
$this->bind($interface)->toInstance($instance);
// 名前付き束縛
$this->bind($interface)->annotatedWith($annotation)->to($class);
// シングルトン
$this->bind($interface)->to($class)->in(Scope::SINGLETON);
// コンストラクタ束縛
$this->bind($interface)->toConstructor($class, $named);
{% endhighlight %}

束縛は先にされたものが優先されますが、モジュールを`override`すると先にされた束縛を上書きすることができます。

詳しくは[Ray.DiのREADME](https://github.com/ray-di/Ray.Di/blob/develop-2/README.ja.md)をご覧ください。


## AOPの設定

AOPはクラスとメソッドを`Matcher`で"検索"して、マッチするメソッドにインターセプターを束縛します。

{% highlight php %}
<?php
$this->bindInterceptor(
    $this->matcher->any(),                   // どのクラスの
    $this->matcher->annotatedWith('delete'), // "delete"で始まるメソッド名のメソッドには
    [Logger::class]                          // Loggerインターセプターを束縛
);

$this->bindInterceptor(
    $this->matcher->SubclassesOf(AdminPage::class),  // AdminPageの継承また実装クラスの
    $this->matcher->annotatedWith(Auth::class),      // @Authアノテーションがアノテートされてるメソッドには
    [AdminAuthentication::class]                     // AdminAuthenticationインターセプターを束縛
);
{% endhighlight %}

`Matcher`は他にこのような指定もできます。

 * [Matcher::any](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L16) - 無制限
 * [Matcher::annotatedWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L23) - アノテーション
 * [Matcher::subclassesOf](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L30) - 継承または実装されたクラス
 * [Matcher::startsWith](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L37) - 名前の始めの文字列
 * [Matcher::logicalOr](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L44) - OR条件
 * [Matcher::logicalAnd](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L51) - AND条件
 * [Matcher::logicalNot](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MatcherInterface.php#L58) - NOT条件

インターセプターの`invoke`メソッドでは`MethodInvocation`（メソッド実行）変数を受け取り、メソッドの前後に処理を加えることができます。

{% highlight php %}
<?php
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
{% endhighlight %}

`MethodInvocation`で対象のメソッド実行に関連するオブジェクトやメソッド、引数にアクセスすることができます。

 * [MethodInvocation::proceed](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Joinpoint.php#L39) - 対象メソッド実行
 * [MethodInvocation::getMethod](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/MethodInvocation.php) -  対象メソッドリフレクションの取得
 * [MethodInvocation::getThis](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Joinpoint.php#L48) - 対象オブジェクトの取得
 * [MethodInvocation::getArguments](https://github.com/ray-di/Ray.Aop/blob/develop-2/src/Invocation.php) - 呼び出し引数配列の取得
 
