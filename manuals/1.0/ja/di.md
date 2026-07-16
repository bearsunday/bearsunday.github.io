---
layout: docs-ja
title: DI
category: Manual
permalink: /manuals/1.0/ja/di.html
---
# DI

依存性の注入（Dependency Injection）とは、オブジェクトが必要とするオブジェクト（依存）を、自身で`new`して構築するのではなく、外部から提供することです。依存を構築するにはそのまた依存が必要、という繰り返しの結果、アプリケーションは相互に接続されたオブジェクトの網（オブジェクトグラフ）になります。このグラフを手作業で構築すると、労力がかかり、ミスが発生しやすく、テストが困難になります。BEAR.Sundayでは、その代わりにDIフレームワークの[Ray.Di](https://ray-di.github.io/manuals/1.0/ja/index.html)がオブジェクトグラフを構築します。

## 書くコード

クラスは、必要なものをコンストラクタで宣言するだけです。

```php
class Index extends ResourceObject
{
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    public function onGet(string $id): static
    {
        $this->body = $this->repository->get($id);

        return $this;
    }
}
```

`Index`は`UserRepositoryInterface`の実装が何であるか、それがどう作られるかを知りません。実装を決めるのは利用するクラスではなく、[モジュール](module.html)に記述する束縛です。

```php
$this->bind(UserRepositoryInterface::class)->to(UserRepository::class);
```

インジェクターはこの束縛に従い、依存の依存まで含めてグラフ全体を再帰的に組み立てます。オブジェクトの「利用」と「構築」が分離されているので、実装の差し替えが利用側のコードに波及せず、テストではモックを束縛するだけで済みます。

## 束縛が決める

束縛には目的に応じた種類があります。

* **リンク束縛** `to()` — インターフェイスに実装クラスを対応させる、最も基本の束縛
* **プロバイダー束縛** `toProvider()` — 生成にロジックが必要なとき、ファクトリーを束縛
* **インスタンス束縛** `toInstance()` — 接続文字列などコンパイルタイムに確定する値を直接束縛
* **名前付き束縛** `annotatedWith()` — 同じ型の依存を名前で区別

構文の一覧は[モジュール](module.html)を、それぞれの詳細は[Ray.Diマニュアル](https://ray-di.github.io/manuals/1.0/ja/index.html)を参照してください。メソッド横断の関心事（ログ、認証、トランザクションなど）は束縛の一種として[AOP](aop.html)で扱います。

## コンテキストでグラフが変わる

束縛のセットを切り替えれば、コードは同じままアプリケーションの振る舞いが変わります。BEAR.Sundayではこの束縛のセットを**コンテキスト**と呼び、`prod-hal-api-app`のように組み合わせて指定します。開発用・プロダクション用・API用で異なるオブジェクトグラフが構築されるため、設定ファイルや実行モードによる条件分岐は必要ありません。詳しくは[アプリケーション](application.html)を参照してください。

Ray.Diは[Google Guice](https://github.com/google/guice)に大きく影響を受けたDIフレームワークです。
