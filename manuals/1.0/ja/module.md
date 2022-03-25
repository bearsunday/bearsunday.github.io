---
layout: docs-ja
title: モジュール
category: Manual
permalink: /manuals/1.0/ja/module.html
---
# モジュール

モジュールはDIとAOPの束縛のセットです。 BEAR.Sundayではいわゆる設定ファイルや、Configクラス、実行モードなどがありません。各コンポーネントが必要とする値は依存性の注入で与えられます。モジュールがその依存性束縛を行います。

起点となるモジュールが`AppModule` (src/Module/AppModule.php)です。
`AppModule`で他の必要なモジュールを`install`します。

モジュールが必要とする値（ランタイムではなく、コンパイルタイムで必要な値）は手動のコンストラクタインジェクションで束縛を行います。

```php
class AppModule extends AbstractAppModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // 追加モジュール
        $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', getenv('db_username'), getenv('db_password'));
        $this->install(new TwigModule));
        // package標準のモジュール
        $this->install(new PackageModule));
    }
}
```

## DIの束縛

代表的な束縛を以下に記します。

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

詳しくは[DI](di.html)をご覧ください。

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

詳しくは[AOP](aop.html)をご覧ください。

## 束縛の優先順位

### 同じモジュール内

先に束縛した方が優先します。この場合はFoo1が優先されます。

```php
$this->bind(FooInterface::class)->to(Foo1::class);
$this->bind(FooInterface::class)->to(Foo2::class);
```

### モジュールインストール

先にインストールしたモジュールが優先します。この場合はFoo1Moduleが優先されます。

```php
$this->install(new Foo1Module);
$this->install(new Foo2Module);
```

後からのモジュールを優先する場合には`override`を使います。この場合はFoo2Moduleが優先されます。

```php
$this->install(new Foo1Module);
$this->override(new Foo2Module);
```

### コンテキスト文字列

左のモジュールの束縛が優先されます。`prod-hal-app`ならAppModuleよりHalModule、HalModuleよりProdModuleが優先してインストールされます。
