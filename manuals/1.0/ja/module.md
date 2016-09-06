---
layout: docs-ja
title: モジュール
category: Manual
permalink: /manuals/1.0/ja/module.html
---
# モジュール

モジュールはアプリケーションの設定です。DIとAOPの束縛を行います。

BEAR.Sundayでは設置場所や記述フォーマットが固定されているいわゆる設定ファイルや、Configクラスはありません。
その代わりに機能ごとに独立したモジュールに設定値を与え、DIとAOPの設定をします。
起点となるモジュールが`AppModule` (/src/Module/AppModule.php)です。
`AppModule`で必要なモジュールを`install`してアプリケーション全体を構成します。

コンテキストに依存しない設定値はモジュールにそのまま記述し、環境により変更されるようなものやクレデンシャル情報は`$_ENV`値を使います。

```php?start_inline
class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // package標準のモジュール
        $this->install(new PackageModule));
        // 追加モジュール
        $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', 'username', 'password');
        $this->install(new TwigModule));
    }
}
```

## DIの設定

BEAR.Sundayの使用するRay.Diでは、インターフェイスとクラスやそのクラスを生成するファクトリー等を束縛(バインド)してオブジェクトグラフを構成します。

```php?start_inline
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
```

束縛は先にされたものが優先されますが、モジュールを`override`すると先にされた束縛を上書きすることができます。

詳しくは[Ray.DiのREADME](https://github.com/ray-di/Ray.Di/blob/2.x/README.ja.md)をご覧ください。


## AOPの設定

AOPはクラスとメソッドを`Matcher`で"検索"して、マッチするメソッドにインターセプターを束縛します。

```php?start_inline
$this->bindInterceptor(
    $this->matcher->any(),                   // どのクラスの
    $this->matcher->startsWith('delete'),    // "delete"で始まるメソッド名のメソッドには
    [Logger::class]                          // Loggerインターセプターを束縛
);

$this->bindInterceptor(
    $this->matcher->SubclassesOf(AdminPage::class),  // AdminPageの継承または実装クラスの
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

## インターセプター

インターセプターの`invoke`メソッドでは`MethodInvocation`（メソッド実行）変数を受け取り、メソッドの前後に処理を加えます。


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
 * `$class->->getAnnotations()`    - クラスアノテーションの取得
 * `$class->->getAnnotation($name)`
