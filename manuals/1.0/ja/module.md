---
layout: docs-ja
title: モジュール
category: Manual
permalink: /manuals/1.0/ja/module.html
---
# モジュール

モジュールはアプリケーションの設定です。DIとAOPの束縛を行います。

BEAR.Sundayでは設置場所や記述フォーマットが固定されているいわゆる設定ファイルや、Configクラスはありません。
その代わりに機能ごとに独立したモジュールに設定値をコンストラクタインジェクションしてDIとAOPの設定をします。
（設定もインジェククトするので設定をコンテキスト毎に変えるのも簡単です）

起点となるモジュールが`AppModule` (src/Module/AppModule.php)です。
`AppModule`で必要なモジュールを`install`してアプリケーション全体を構成します。

コンテキストに依存しない設定値はモジュールにそのまま記述し、環境により変更されるようなものやクレデンシャル情報は`$_ENV`値を使います。

```php?start_inline
class AppModule extends AbstractAppModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // 追加モジュール
        $this->install(new AuraSqlModule('mysql:host=localhost;dbname=test', 'username', 'password');
        $this->install(new TwigModule));
        // package標準のモジュール
        $this->install(new PackageModule));
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

## 環境

BEAR.Sundayは"取得可能"な`dev`や`prod`などのグローバルな環境値（現在の動作モード）はありません。

アプリケーションはどの環境値(コンテキスト)で動作しているか無知です。これはコードをクリーンに保つための意図的なもので、環境値に応じてプログラムが`if`文で振る舞いを変えるのではなく、環境値に応じたオブジェクトがインジェクトされ振る舞いが変ります。

環境値はDI/AOPの構成のためだけにローカル変数として使われ、値は保持されません。
