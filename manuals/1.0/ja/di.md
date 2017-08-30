---
layout: docs-ja
title: DI
category: Manual
permalink: /manuals/1.0/ja/di.html
---
# DI

依存性の注入(Dependency Injection)とはクラスが必要とするサービスや値などのインスタンス（依存性）を、
サービス自身が取得したり生成するのではなく外部から渡す(注入）デザインパターンのことです。

**Ray.Di**はGoogleの[Guice](http://code.google.com/p/google-guice/wiki/Motivation?tm=6)の主要な機能を持つPHPのDIフレームワークでBEAR.SundayはDIにRay.Diを使っています。

## 概要

Ray.Diには以下の機能があります。

- コンストラクタインジェクションとセッターインジェクション

- 自動インジェクション

- コンストラクタの後の初期化メソッド指定(`@PostConstruct`)

- 高速化のためPHPのファクトリーコード生成

- 名前付きインターフェイス

- インジェクション先のメタデータの取得

- アノテーション([Doctrine Annotation](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html))はオプション

- [AOP Alliance](http://aopalliance.sourceforge.net/)に準拠したアスペクト指向プログラミング

## 注入

クラスが依存を受け取る箇所はコンストラクタ、セッターメソッド、実行メソッドの三種類がありそれをインジェクションポイントと呼びます。
コンストラクタでの注入は必須ですが、セッターメソッドには通常のメソッドと区別するための`@Injet`アノテーションの印が必要です。

コンストラクターインジェクション

```php?start_inline
use Ray\Di\Di\Inject;

class Index
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
```

セッターインジェクション

```php?start_inline
use Ray\Di\Di\Inject;

class Index
{
    private $logger;

    /**
     * @Inject
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
```

アシスティッドインジェクション

```php?start_inline
use Ray\Di\Di\Assisted;

class Index
{
    /**
     * @Assisted({"logger"})
     */
    public function doSomething(LoggerInterface $logger = null)
    {
        $logger-log('log message..');
    }
```

## 束縛

インジェクタの仕事はオブジェクトグラフを作成することです。
型を指定してインスタンスを要求し、依存関係を解決し、すべてを結びつけます。 依存関係の解決方法を指定するにバインディングを設定します。

| オブジェクトグラフとは？
|
| オブジェクト指向のアプリケーションは**相互に関係のある複雑なオブジェクト網**を持ちます。オブジェクトはあるオブジェクトから所有されているか、他のオブジェクト（またはそのリファレンス）を含んでいるか、そのどちらかでお互いに接続されています。このオブジェクト網をオブジェクトグラフと呼びます。

## 束縛作成

束縛を作るには`AbstractModule`クラスを拡張して、`configure`メソッドをオーバーライドします。メソッド内では`bind()`でそれぞれの束縛をします。

```php?start_inline
class Tweet
extends AbstractModule
{
    protected function configure()
    {
        $this->bind(TweetClient::class);
        $this->bind(TweeterInterface::class)->to(SmsTweeter::class)->in(Scope::SINGLETON);
        $this->bind(UrlShortenerInterface)->toProvider(TinyUrlShortener::class)
        $this->bind('')->annotatedWith(Username::class)->toInstance("koriym")
    }
}
```

モジュールでは以下のいずれかの束縛を行います。

 * リンク束縛

```php?start_inline
 $this->bind($interface)->to($class);
```

* 名前付き束縛

```php?start_inline
$this->bind($interface)->annotatedWith($name)->to($class);
```

 * コンストラクタ束縛

```php?start_inline
$this->bind($interface)->toConstructor($class, [$varName => $name]);
```

 * アンターゲット束縛

```php?start_inline
$this->bind($class);
```

 * プロバイダー束縛

```php?start_inline
$this->bind($interface)->toProvider($provider);
```

 * インスタンス束縛

```php?start_inline
$this->bind($interface)->toInstance($instance);
```

## リンク束縛

リンク束縛は最も基本の束縛です。インターフェイスとその実装クラスを束縛します。

```php?start_inline
class ListerModule extends AbstractModule
{
    public function configure()
    {
        $this->bind(LoggerInterface::class)->to(Logger::class);
    }
}
```

## 名前付き束縛

１つのインターフェイスに複数の実装クラスがあったり、インターフェイスを持たないスカラータイプの依存の場合に依存に名前をつけて束縛します。

```php?start_inline
class ListerModule extends AbstractModule
{
   public function configure()
   {
       $this->bind(LoggerInterface::class)->annotatedWith('prod')->to(Logger::class);
       $this->bind(LoggerInterface::class)->annotatedWith('dev')->to(Logger::class);
   }
}
```

名前付き束縛で束縛した依存は`@Named`アノテーションで指定して受け取ります。

```php?start_inline
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class Index
{
    private $logger;

    /**
     * @Inject
     * @Named("prod")
     */
    public function setLogger(LoggerInterface $foo)
    {
        $this->logger = $logger;
    }
```

定数の代わりにアノテーションにすることもできます。

```php?start_inline
/**
 * @Annotation
 * @Target("METHOD")
 */
final class Prod
{
}
```

```php?start_inline
$this->bind(LoggerInterface::class)->annotatedWith(Prod::class)->to(Logger::class);
```

```php?start_inline
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class Index
{
    private $logger;

    /**
     * @Inject
     * @Prod
     */
    public function setLogger(LoggerInterface $foo)
    {
        $this->logger = $logger;
    }
```

引数が複数の場合は`変数名=名前`のペアでカンマ区切りの文字列を指定します。


```php?start_inline
/**
 * @Inject
 * @Named("paymentLogger=payment_logger,debugLogger=debug_logger")
 */
public __construct(LoggerInterface $paymentLogger, LoggerInterface $debugLogger)
{
```

## アンターゲット束縛

インターフェイスなしのクラスの束縛に使います。

```php

protected function configure()
{
    $this->bind(MyConcreteClass::class);
    $this->bind(AnotherConcreteClass::class)->in(Scope::SINGLETON);
}
```

リソースクラスは全てアンターゲット束縛されていて使用しないリソースでも依存解決に問題があるとエラーになります。

## コンストラクタ束縛

`@Inject`アノテーションのないサードパーティーのクラスやアノテーションを使わない場合には**コンストラクタ束縛で束縛することができます。
最初の引数にはクラス名前、２番目の引数の`変数名`=>`名前`の名前束縛、３番目の引数にセッターインジェクションを指定します。

```php
<?php
protected function configure()
{
    $this
        ->bind(CarInterface::class)
        ->toConstructor(
            Car::class,                                 // $class_name
            [
                ['enginne' => 'na'],                    // $name
                ['number' => 'registrtion_number']
            ],
            (new InjectionPoints)                       // $setter_injection
                ->addMethod('setWheel', "right")
                ->addOptionalMethod('setTurboCharger'),
                'initialize'                            // $postCosntruct
        );
}
```
この例では`Car`クラスでは`EngineInterface $engine, $carName`と二つの引数が必要ですが、それぞれの変数名に`Named binding`束縛を行い依存解決をしています。

＃## PDO Example

[PDO](http://php.net/manual/ja/pdo.construct.php)クラスの束縛の例です.

```php?start_inline
public PDO::__construct ( string $dsn [, string $username [, string $password [, array $options ]]] )
```

```php?start_inline
protected function configure()
{
    $this->bind(\PDO::class)->toConstructor(
        \PDO::class,
        [
            ['pdo' => 'pdo_dsn'],
            ['username' => 'pdo_username'],
            ['password' => 'pdo_password']
        ]
    )->in(Scope::SINGLETON);
    $this->bind()->annotatedWith('pdo_dsn')->toInstance($dsn);
    $this->bind()->annotatedWith('pdo_username')->toInstance($username);
    $this->bind()->annotatedWith('pdo_password')->toInstance($password);
}
```

PDOのどのインターフェイスがないので`toConstructor()`メソッドの二番目の引数の名前束縛でP束縛しています

## プロバイダ束縛

インターフェイスとインスタンスの**プロバイダー**を束縛します。
プロバイダーは依存のファクトリーです。`get`メソッドで依存を返します。

```php?start_inline
use Ray\Di\ProviderInterface;

interface ProviderInterface
{
    public function get();
}
```

プロバイダーにも依存は注入できます。

```php?start_inline
use Ray\Di\ProviderInterface;

class DatabaseTransactionLogProvider implements Provider
{
    private $pdo;

    /**
     * @Named("original")
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get()
    {
        $this->pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);

        return $pdo;
    }
}
```

注意：get()内で`new`して生成したインスタンスはAOPできません。この束縛は他の束縛でできない時にのみするべきです。

### コンテンキストプロバイダ束縛

同じプロバイダーでコンテキスト別にオブジェクトを生成したい場合があります。例えば接続先の違う複数のDBオブジェクトを同じインターフェイスでインジェクトしたい場合などです。そういう場合には`toProvider()`でコンテキスト（文字列）を指定して束縛をします。

```php?start_inline
$dbConfig = ['user' => $userDsn, 'job'=> $jobDsn, 'log' => $logDsn];
$this->bind()->annotatedWith('db_config')->toInstance(dbConfig);
$this->bind(Connection::class)->annotatedWith('usr_db')->toProvider(DbalProvider::class, 'user');
$this->bind(Connection::class)->annotatedWith('job_db')->toProvider(DbalProvider::class, 'job');
$this->bind(Connection::class)->annotatedWith('log_db')->toProvider(DbalProvider::class, 'log');
```

プロバイダーはコンテキスト別に生成します。

```php?start_inline
class DbalProvider implements ProviderInterface, SetContextInterface
{
    private $dbConfigs;

    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @Named("db_config")
     */
    public function __construct(array $dbConfigs)
    {
        $this->dbConfigs = $dbConfigs;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $config = $this->dbConfigs[$this->context];
        $conn = DriverManager::getConnection(config);

        return $conn;
    }
}
```
同じインターフェイスですが、接続先の違う別々のDBオブジェクトを受け取ります。

```php?start_inline
/**
 * @Named("userDb=user_db,jobDb=job_db,logDb=log_db")
 */
public function __construct(Connection $userDb, Connection $jobDb, Connection $logDb)
{
  //...
}
```

### インジェクションポイント

プロバイダでは`InjectionPointInterface`で依存が注入されるインジェクションポイントの情報を受け取ることができます。
この例では`Logger`の引数の最初に「注入先のクラス名」を指定しています。

```php?start_inline
class Psr3LoggerProvider implements ProviderInterface
{
    /**
     * @var InjectionPoint
     */
    private $ip;

    public function __construct(InjectionPointInterface $ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return Logger
     */
    public function get()
    {
        $targetClass = $this->ip->getClass()->getName();
        $logger = new \Monolog\Logger(targetClass);
        $logger->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));

        return $logger;
    }
}
```
`InjectionPointInterface`は以下のメソッドがありインジェクション先のアノテーションを読むこともできます。
インジェクションポイントに応じたインスタンスを用意することができます。

```php?start_inline
$ip->getClass();      // \ReflectionClass
$ip->getMethod();     // \ReflectionMethod
$ip->getParameter();  // \ReflectionParameter
$ip->getQualifiers(); // (array) $qualifierAnnotation[]
```

## インスタンス束縛

`toInstance()`で値を直接束縛します。定数の束縛に使います。

```php?start_inline
protected function configure()
{
    $this->bind()->annotatedWith("message")->toInstance('Hello');
}
```

定数をまとめて束縛する時は`NamedModule`を使います。

```php?start_inline
protected function configure()
{
    $names = [
        'lang' => 'ja',
        'message' => 'こんにちは'
    ];
    $this->install(new NamedModule($names));
}
```

```php?start_inline
/**
 * @Named("message")
 */
public function setMessage(string $message) // こんにちは
{
```

オブジェクトも束縛できますが、単純な値オブジェクトだけにするべきです。

```php?start_inline
protected function configure()
{
    $this->bind(UserInterface::class)->toInstance(new User); // シリアライズして保存されます
}
```

## オブジェクトライフサイクル

`@PostConstruct`でアノテートしたメソッドはコンストラクタインジェクション、セッターインジェクションが全て完了した後に呼ばれます。
依存注入後の初期化に使用します。
ある場合などでも全ての必要な依存が注入された前提にすることができます。

```php?start_inline
use Ray\Di\Di\PostConstruct;

/**
 * @PostConstruct
 */
public function onInit()
{
    //....
}
```

まとめ：メソッドは以下の順番で呼ばれます。

 * コンストラクタ
 * セッターメソッド（順不同）
 * `@PostConstruct`メソッド
 * デストラクタ


## スコープ

デフォルトでは、Rayは毎回新しいインスタンスを生成します（＝プロトタイプ）がシングルトンに変更するには`in`で指定します。

```php?start_inline
use Ray\Di\Scope;

protected function configure()
{
    $this->bind(TransactionLog::class)->to(InMemoryTransactionLog::class)->in(Scope::SINGLETON);
}
```

## アシスティッドインジェクション

メソッドが実行されるタイミングでメソッドの引数に依存を渡すことができます。そのためには依存を受け取る引数を引数を`@Assisted`で指定し、引数リストの終わり（右）に移動して`null`をディフォルトとして与える必要があります。

```php?start_inline
use Ray\Di\Di\Assisted;

class Index
{
    /**
     * @Assisted({"db"})
     */
    public function doSomething($id, DbInterface $db = null)
    {
        $this->db = $db;
    }
```

`@Assisted`で提供される依存は、その時に渡された他の引数を参照して決定することもできます。そのためには依存を`プロバイダーバインディング`で束縛して、その[プロバイダー束縛](#provider-bidning)は`MethodInvocationProvider`を依存として受け取るようにします。`get()`メソッドでメソッド実行オブジェクト [MethodInvocation](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php) を取得することができ、引数の値や対象のメソッドのプロパティにアクセスすることができます。

```php?start_inline
class HorizontalScaleDbProvider implements ProviderInterface
{
    /**
     * @var MethodInvocationProvider
     */
    private $invocationProvider;

    public function __construct(MethodInvocationProvider $invocationProvider)
    {
        $this->invocationProvider = $invocationProvider;
    }

    public function get()
    {
        $methodInvocation = $this->invocationProvider->get();
        list($id) = methodInvocation->getArguments()->getArrayCopy();

        return new UserDb($id); // $idによって接続データベースを切り替えます
    }
}
```

## デバック

複雑は束縛も最終的には単純なPHPのファクトリーコードにコンパイルされて`var/tmp/{context}`フォルダに出力されます。
生成されたファイルを見ればどのセッターメソッドが有効でどの依存をどのように(Singleton ?)注入したかが分かります。

ファイル名は`{インターフェイス}-{名前}`で中身はこのようなコードです。

```
<?php

$instance = new \MyVendor_Todo_Resource_App_Todos_c0kmGJA();
$instance->setRenderer($singleton('BEAR\\Resource\\RenderInterface-'));
$instance->setAuraSql($singleton('Aura\\Sql\\ExtendedPdoInterface-'));
$instance->setQueryLocator($prototype('Koriym\\QueryLocator\\QueryLocatorInterface-'));
$instance->bindings = array('onGet' => array($singleton('BEAR\\Resource\\Interceptor\\JsonSchemaInterceptor-')));
return $instance;
```

 * `MyVendor_Todo_Resource_App_Todos_c0kmGJA` という語尾に文字列がつく生成されたクラス名はAOPがバインドされていることを表します。
 * `$singleton('BEAR\\Resource\\RenderInterface-')`は`RenderInterface`インターフェイスに束縛されてあるインスタンスをシングルトンで取得するという意味です。
 * `$instance->bindings`の`[{メソッド名} => {インターセプター}]`の配列がインターセプターの束縛を表します。
