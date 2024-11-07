---
layout: docs-ja
title: モジュール
category: Manual
permalink: /manuals/1.0/ja/module.html
---
# モジュール

モジュールは、DIとAOPの束縛のセットです。BEAR.Sundayでは、一般的な設定ファイルやConfigクラス、実行モードなどは存在しません。各コンポーネントが必要とする値は、依存性の注入により提供されます。モジュールがこの依存性の束縛を担当します。

アプリケーションの起点となるモジュールは`AppModule`（src/Module/AppModule.php）です。
`AppModule`内で、他の必要なモジュールを`install`します。

モジュールが必要とする値（ランタイムではなく、コンパイルタイムで必要な値）は、手動のコンストラクタインジェクションにより束縛を行います。

```php
class AppModule extends AbstractAppModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // 追加モジュール
        $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', getenv('db_username'), getenv('db_password')));
        $this->install(new TwigModule());
        // package標準のモジュール
        $this->install(new PackageModule());
    }
}
```

## DIの束縛

以下に代表的な束縛パターンを示します：

```php
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

詳細については[DI](di.html)をご参照ください。

## AOPの設定

AOPは、クラスとメソッドを`Matcher`で"検索"し、マッチするメソッドにインターセプターを束縛します。

```php
// 例1：メソッド名による束縛
$this->bindInterceptor(
    $this->matcher->any(),                   // どのクラスの
    $this->matcher->startsWith('delete'),    // "delete"で始まるメソッド名のメソッドには
    [Logger::class]                          // Loggerインターセプターを束縛
);

// 例2：クラスとアノテーションによる束縛
$this->bindInterceptor(
    $this->matcher->SubclassesOf(AdminPage::class),  // AdminPageの継承または実装クラスの
    $this->matcher->annotatedWith(Auth::class),      // @Authアノテーションがアノテートされているメソッドには
    [AdminAuthentication::class]                     // AdminAuthenticationインターセプターを束縛
);
```

詳細については[AOP](aop.html)をご参照ください。

## 束縛の優先順位

### 同一モジュール内での優先順位

同じモジュール内では、先に束縛された方が優先されます。以下の例では、Foo1が優先されます：

```php
$this->bind(FooInterface::class)->to(Foo1::class);
$this->bind(FooInterface::class)->to(Foo2::class);
```

### モジュールインストールでの優先順位

先にインストールされたモジュールが優先されます。以下の例では、Foo1Moduleが優先されます：

```php
$this->install(new Foo1Module);
$this->install(new Foo2Module);
```

後からのモジュールを優先させたい場合は、`override`を使用します。以下の例では、Foo2Moduleが優先されます：

```php
$this->install(new Foo1Module);
$this->override(new Foo2Module);
```

### コンテキスト文字列での優先順位

コンテキスト文字列では、左のモジュールの束縛が優先されます。例えば、`prod-hal-app`の場合：

- AppModuleよりHalModule
- HalModuleよりProdModule

の順で優先してインストールされます。
