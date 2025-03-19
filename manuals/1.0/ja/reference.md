---
layout: docs-ja
title: リファレンス
category: Manual
permalink: /manuals/1.0/ja/reference.html
---

# リファレンス

## アトリビュート

| アトリビュート | 説明 |
|--------------|------|
| `#[CacheableResponse]` | キャッシュ可能なレスポンスを指定するアトリビュート。 |
| `#[Cacheable(int $expirySecond = 0)]` | リソースのキャッシュ可能性を指定するアトリビュート。`$expirySecond`はキャッシュの有効期間（秒）。 |
| `#[CookieParam(string $name)]` | クッキーからパラメータを受け取るためのアトリビュート。`$name`はクッキーの名前。 |
| `#[DonutCache]` | ドーナツキャッシュを指定するアトリビュート。 |
| `#[Embed(src: string $src, rel: string $rel)]` | 他のリソースを埋め込むことを指定するアトリビュート。`$src`は埋め込むリソースのURI、`$rel`はリレーション名。 |
| `#[EnvParam(string $name)]` | 環境変数からパラメータを受け取るためのアトリビュート。`$name`は環境変数の名前。 |
| `#[FormParam(string $name)]` | フォームデータからパラメータを受け取るためのアトリビュート。`$name`はフォームフィールドの名前。 |
| `#[Inject]` | セッターインジェクションを指定するアトリビュート。 |
| `#[InputValidation]` | 入力バリデーションを行うことを指定するアトリビュート。 |
| `#[JsonSchema(key: string $key = null, schema: string $schema = null, params: string $params = null)]` | リソースの入力/出力のJSONスキーマを指定するアトリビュート。`$key`はスキーマのキー、`$schema`はスキーマファイル名、`$params`はパラメータのスキーマファイル名。 |
| `#[Link(rel: string $rel, href: string $href, method: string $method = null)]` | リソース間のリンクを指定するアトリビュート。`$rel`はリレーション名、`$href`はリンク先のURI、`$method`はHTTPメソッド。 |
| `#[Named(string $name)]` | 名前付きバインディングを指定するアトリビュート。`$name`はバインディングの名前。 |
| `#[OnFailure(string $name = null)]` | バリデーション失敗時のメソッドを指定するアトリビュート。`$name`はバリデーションの名前。 |
| `#[OnValidate(string $name = null)]` | バリデーションメソッドを指定するアトリビュート。`$name`はバリデーションの名前。 |
| `#[Produces(array $mediaTypes)]` | リソースの出力メディアタイプを指定するアトリビュート。`$mediaTypes`は出力可能なメディアタイプの配列。 |
| `#[QueryParam(string $name)]` | クエリパラメータを受け取るためのアトリビュート。`$name`はクエリパラメータの名前。 |
| `#[RefreshCache]` | キャッシュのリフレッシュを指定するアトリビュート。 |
| `#[ResourceParam(uri: string $uri, param: string $param)]` | 他のリソースの結果をパラメータとして受け取るためのアトリビュート。`$uri`はリソースのURI、`$param`はパラメータ名。 |
| `#[ReturnCreatedResource]` | 作成されたリソースを返すことを指定するアトリビュート。 |
| `#[ServerParam(string $name)]` | サーバー変数からパラメータを受け取るためのアトリビュート。`$name`はサーバー変数の名前。 |
| `#[Ssr(app: string $appName, state: array $state = [], metas: array $metas = [])]` | サーバーサイドレンダリングを指定するアトリビュート。`$appName`はJSアプリケーション名、`$state`はアプリケーションの状態、`$metas`はメタ情報の配列。 |
| `#[Transactional(array $props = ['pdo'])]` | メソッドをトランザクション内で実行することを指定するアトリビュート。`$props`はトランザクションを適用するプロパティの配列。 |
| `#[UploadFiles]` | アップロードされたファイルを受け取るためのアトリビュート。 |
| `#[Valid(form: string $form = null, onFailure: string $onFailure = null)]` | リクエストの検証を行うことを指定するアトリビュート。`$form`はフォームクラス名、`$onFailure`は検証失敗時のメソッド名。 |

## モジュール

| モジュール名 | 説明 |
|------------|------|
| `ApcSsrModule` | APCuを使用したサーバーサイドレンダリング用のモジュール。 |
| `ApiDoc` | APIドキュメントを生成するためのモジュール。 |
| `AppModule` | アプリケーションのメインモジュール。他のモジュールのインストールや設定を行う。 |
| `AuraSqlModule` | Aura.Sqlを使用したデータベース接続用のモジュール。 |
| `AuraSqlQueryModule` | Aura.SqlQueryを使用したクエリビルダー用のモジュール。 |
| `CacheVersionModule` | キャッシュのバージョン管理を行うモジュール。 |
| `CliModule` | コマンドラインインターフェース用のモジュール。 |
| `DoctrineOrmModule` | Doctrine ORMを使用したデータベース接続用のモジュール。 |
| `FakeModule` | テスト用のフェイクモジュール。 |
| `HalModule` | HAL (Hypertext Application Language) 用のモジュール。 |
| `HtmlModule` | HTMLレンダリング用のモジュール。 |
| `ImportAppModule` | 他のアプリケーションを読み込むためのモジュール。 |
| `JsonSchemaModule` | JSONスキーマを使用したリソースの入力/出力バリデーション用のモジュール。 |
| `JwtAuthModule` | JSON Web Token (JWT) を使用した認証用のモジュール。 |
| `NamedPdoModule` | 名前付きのPDOインスタンスを提供するモジュール。 |
| `PackageModule` | BEAR.Packageが提供する基本的なモジュールをまとめてインストールするためのモジュール。 |
| `ProdModule` | 本番環境用の設定を行うモジュール。 |
| `QiqModule` | Qiqテンプレートエンジン用のモジュール。 |
| `ResourceModule` | リソースクラスに関する設定を行うモジュール。 |
| `AuraRouterModule` | Aura.Routerのルーティング用のモジュール。 |
| `SirenModule` | Siren (Hypermedia Specification) 用のモジュール。 |
| `SpyModule` | メソッドの呼び出しを記録するためのモジュール。 |
| `SsrModule` | サーバーサイドレンダリング用のモジュール。 |
| `TwigModule` | Twigテンプレートエンジン用のモジュール。 |
| `ValidationModule` | バリデーション用のモジュール。 |
