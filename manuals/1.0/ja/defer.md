---
layout: docs-ja
title: 遅延リソース実行
category: Manual
permalink: /manuals/1.0/ja/defer.html
---

# 遅延リソース実行 <sup style="font-size:0.5em; color:#666; font-weight:normal;">Alpha</sup>

BEAR.Deferは、重い後続処理をレスポンス転送の**後ろ**に回します。リソースはリクエストを受け取ると即座に`202 Accepted`を返し、インデックス更新・通知送信・リリースノート生成といった処理は、レスポンスがクライアントへ渡ったあとに実行されます。

リソースは「**何を**遅延するか」を宣言するだけです。「いつ・どこで実行するか」はリソースの外側で決まるため、リソースのコードは変わりません。

## 概要

```text
[通常実行] すべて処理してから応答
Request
│
├── 保存（軽い処理）
├── インデックス更新（重い処理）
├── 通知送信（重い処理）
│
└── Response ──▶ クライアント

[遅延実行] 応答を先に返し、重い処理は転送後に実行
Request
│
├── 保存（軽い処理）
├── Response 202 ──▶ クライアント
│
│   （以下は転送後に実行）
├── インデックス更新（重い処理）
└── 通知送信（重い処理）
```

## レスポンスが先に返る条件

「レスポンスを即座に返して重い処理を後ろに回す」効果は、実行環境（SAPI）によって変わります。

- **PHP-FPM / LiteSpeed** — 接続が解放され、クライアントはすぐにレスポンスを受け取ります
- **Apache mod_php** — ベストエフォート。早期返却は保証されないため、確実に効かせたいなら PHP-FPM か LiteSpeed を使ってください

`#[Defer]`のコードはどの環境でも同じで、環境を変えれば自動的に切り替わります。

## インストール

```bash
composer require bear/defer
```

既存のレスポンダモジュールを`DeferModule`で包んでインストールします。

```php
use BEAR\Defer\Module\DeferModule;

protected function configure(): void
{
    $this->install(new DeferModule(new YourHttpResponderModule()));
}
```

## 使用方法

### 何を遅延するかを宣言する

リクエストを受け取るリソースに`#[Defer]`を付け、遅延する`#[Link]`のrel名を列挙します。各relの`href`はメソッド実行後のリソースボディに対して解決されるので、URIをハードコードする必要はありません。

```php
use BEAR\Defer\Attribute\Defer;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Article extends ResourceObject
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articles,
    ) {
    }

    #[Defer(['publish', 'release-note'])]
    #[Link(rel: 'publish',      href: 'app://self/article/publish{?id}', method: 'post')]
    #[Link(rel: 'release-note', href: 'app://self/release-note{?id}',    method: 'post')]
    public function onPost(string $title, string $body): static
    {
        $id = $this->articles->save($title, $body); // 軽い処理のみ
        $this->code = 202;                          // Accepted
        $this->body = ['id' => $id];

        return $this; // ボディに遅延呼び出しのコードは書かない
    }
}
```

メソッドには遅延処理を呼び出すコードを書きません。`#[Defer]`は`#[Link]`を参照する**宣言**であり、後続処理の起動はフレームワークが行います。遷移はハイパーメディア駆動のまま保たれ、ALPSにも遅延遷移として現れます。

### 後続リソースは通常のリソース

後続リソースは自分が遅延実行されることを知りません。どんなリソースでも遅延対象にできます。

```php
class Publish extends ResourceObject
{
    public function onPost(string $id): static
    {
        $this->indexer->index($id);   // 重い処理。レスポンス転送後に実行される
        $this->notifier->notify($id);

        return $this;
    }
}
```

### 条件によって遅延する

後続処理を出すかどうかが条件で変わる場合は、`DeferInterface`を直接インジェクトして`add()`で登録します。

```php
use BEAR\Defer\DeferInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class Article extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource,
        private readonly DeferInterface $defer,
    ) {
    }

    public function onPost(string $title, string $body, bool $publish = false): static
    {
        $id = $this->articles->save($title, $body);
        $this->code = 202;
        $this->body = ['id' => $id];

        if ($publish) {
            $request = $this->resource->post->uri('app://self/publish')->withQuery(['id' => $id]);
            $this->defer->add($request);
        }

        return $this;
    }
}
```

宣言的に常に遅延するなら`#[Defer]`、実行時の条件で出し分けるなら`add()`、と使い分けます。

## いつ使うか

遅延処理はレスポンスを返したあとに実行されるため、**失敗してもクライアントの応答には影響しません**。検索インデックスの更新・通知・サムネイル生成・キャッシュのウォームアップなど、多少遅れたり失敗してもクライアントへ即座に伝える必要のない後続処理に向いています。

一方、課金・在庫引き当て・欠落の許されない記録など、確実な実行やリトライが必要な処理には遅延実行を使わず、ジョブキューを使ってください。

## なぜコード変更なしで動くのか

BEAR.Sundayでは情報がリソースとしてURIで構造化され、リソース間の関係が宣言で表されます。`#[Defer]`は「この遷移を後で実行する」という**関係の宣言**であって、実行手続きそのものではありません。即時に実行するか転送後に実行するかを選ぶのは外側の役割で、リソースクラスはその違いを知る必要がありません。

この「実行戦略をコードから分離する」性質はBEAR.Defer固有のものではなく、[並列リソース実行](async.html)と同じくBEAR.Sunday全体の設計に根ざしています。

## 参考リンク

- [BEAR.Defer](https://github.com/bearsunday/BEAR.Defer)
- [並列リソース実行](async.html)
