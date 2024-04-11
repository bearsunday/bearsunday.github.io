---
layout: docs-ja
title: イントロダクション
category: Manual
permalink: /manuals/1.0/ja/
---
# BEAR.Sundayとは

BEAR.SundayとはPHPのWebアプリケーションフレームワークです。
BEAR.Sundayの目標は、標準に準拠し高品質で**API中心**のRESTfulアプリケーションの**フレームワーク**を提供することです。

## フレームワーク

**BEAR.Sunday**は3つのオブジェクトフレームワークで構成されています。

`Ray.Di`は[依存関係逆転の原則](http://en.wikipedia.org/wiki/Dependency_inversion_principle)に基づいてオブジェクトをインターフェイスで結びます。

`Ray.Aop`は[アスペクト指向プログラミング](http://en.wikipedia.org/wiki/Aspect-oriented_programming)で本質的関心と横断的関心を結びます。

`BEAR.Resource`はアプリケーションの情報や機能をリソースにして[REST制約](https://en.wikipedia.org/wiki/Representational_state_transfer)で結びます。

アプリケーションもWebと同じように統一されたメソッドとURIでリソースを操作します。強力なDIとAOPでREST API中心に構築されたクリーンなアプリケーションは変更に強く拡張性に優れます。


## ライブラリ

BEAR.Sundayアプリケーションフレームワークはフルスタックフレームワークと違って認証やデータベースなどの特定の仕事のための独自のライブラリを持ちません。
高品質なAuraフレームワークのライブラリや、Packagistで利用可能なサードパーティのライブラリを使用します。

## リソース指向パターン

BEAR.SundayはMVCパターンではなく、Webアプリケーションの設計をリソース（データやビジネスロジック）を中心に設計を行うパターンです。RESTful APIの設計でよく使用されるパターンですが、Webアプリケーション全体の設計にも適用します。

リソース指向パターンの主な特徴は以下の通りです：

 * リソースの特定：アプリケーションで扱うデータやビジネスロジックをリソースとして特定し、統一された識別子(URI)で表現します（例：/user、/orderなど）。

* リソースの操作：リソースに対する操作をHTTPメソッド（GET、POST、PUT、DELETEなど）にマッピングします。これにより、リソースに対する操作が統一的かつ直感的になります。

* ステートレス性：リソース指向パターンでは、サーバーはクライアントの状態を保持せず、各リクエストに必要な情報をすべて含めます。これにより、スケーラビリティと信頼性が向上します。

WebアプリケーションがRESTfulである事の利点をアプリケーション内部でも享受できるように、BEAR.Sundayはリソース指向パターンを採用しています。

メンテナンスの容易さ: リソース指向パターンでは、アプリケーションの各コンポーネントが独立しているため、特定のリソースや機能に対する変更が他の部分に影響を与えにくくなります。これにより、アプリケーションのメンテナンスや将来的なアップデートが容易になります。

開発速度の向上: このパターンは、RESTful APIの設計原則に従っているため、開発者は一貫性のあるルールセットに従って効率的に作業することができます。また、リソースの明確な定義と操作方法の標準化により、開発プロセスが加速されます。

アプリケーションの拡張性: リソース指向パターンは、新しいリソースの追加や既存リソースの更新を容易にするための柔軟性を提供します。これは、アプリケーションの成長に伴う拡張ニーズに対応する際に特に有効です。

クライアントとの統合性の向上: リソース指向パターンにより、APIはより直感的になり、クライアント（フロントエンド、他のサービスなど）との統合が容易になります。統一されたHTTPメソッドの使用とリソースへの明確なアクセスポイントは、多様なクライアントからのアクセスをシンプルにします。

スケーラビリティと信頼性の向上: ステートレスなアーキテクチャにより、サーバーのリソースを効率的に利用でき、負荷の増大にも柔軟に対応可能です。各リクエストが独立しているため、システム全体の信頼性も向上します。




![4R](/images/screen/4r.png)

ステートレスなリクエストは`Method`で`Resource`状態をつくり、内部のRendererが`Representation`にしてレスポンスになります。

```php?start_inline
class Index extends ResourceObject
{
    public $code = 200;
    public $headers = ['access-control-allow-origin' => '*'];
    public $body = [];

    private $renderer;

    public function __construct(RenderInterface $render)
    {
        $this->renderer = $render;
    }

    public function onGet(string $name): static
    {
        // set resource state
        $this->body = $state;

        return $this;
    }

    public function __toString()
    {
        // contextual renderer makes representation (JSON, HTML)
        return $this->renderer->render($this);
    }

    public function transfer(TransferInterface $responder, array $server)
    {
        // contextual responder output (CLI, HTTP)
        $responder($this, $server);
    }
}
```

### Resource

WebのリソースをオブジェクトにしたものがResourceObjectです。(Object as a Service)
固有のURIがマップされHTTPに準じたリクエストメソッドでリソースの状態を変更します。
他のリソースを`@Embed`で埋め込んだり、次のアクションに`@Link`することでハイパーメディアにすることもできます。

### Method

Webからのリクエストはユニークにルートされます。HTTPメソッドに応じたパラメーターで呼ばれ、Method内で自身のリソースプロパティを構成します。
MVCのコントローラーのようにドメインモデルや他のリソースにアクセスする事もあります。

Methodの構造は[オニオンアーキテクチャ](http://www.infoq.com/jp/news/2014/11/ddd-onion-architecture)や[クリーンアーキテクチャ](http://blog.8thlight.com/uncle-bob/2012/08/13/the-clean-architecture.html)のバリエーションの１つです。
認証やバリデーション、ログなどのユースケースはアスペクト指向プログラミングでMethodに任意の層でラップされます。

### Representation

個別に注入されたRendererは文字列評価時にリソースの状態をRepresentationにします。MethodではRepresentationに関心を持ちません。Representationになったリソースはリソース内の`Responder`でクライアントに出力されます。

![Clean Method](/images/screen/clean-method.png)

## コラボレーション

 1. ウェブハンドラーはクライアントリクエストをアプリケーションのリソースリクエストに変更します。

 1. リクエストを受けた`Resource`内の`Method`は自身を構成します。

 1. 文字列評価で`Resource`内のレンダラーがリソース状態を`Representation`にします。

 1. レスポンダーが`Representation`をクライアントにレスポンスとして返します。


## なぜ新しいパターン？

従来のパターンはオブジェクト指向パラダイムのアプリケーションをHTTPにマップしたものです。
純粋なコントローラーはHTTPやRESTに対して無知です。

新パターンではHTTPにマップするオブジェクトを作成します。RESTをフレームワークとして、適合するコンポーネントを作成します。

RESTの力を引き出し、HTTPをアプリケーションプロトコルとして扱うリソース指向のためのパターンです。
