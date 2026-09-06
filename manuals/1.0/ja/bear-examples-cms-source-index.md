---
layout: docs-ja
title: BEAR.Examples CMS ソース索引
category: Manual
permalink: /manuals/1.0/ja/bear-examples-cms-source-index.html
---

# BEAR.Examples CMS ソース索引

このページは、AIエージェントと人間が「これを実装したい時、公式サンプルのどこを見るか」を素早く引くためのソース索引です。チュートリアルではありません。説明は最小限にし、実装意図からソース、テスト、真似してよい要点へ直接到達することを目的にします。

## 前提

- この索引は、現在の `MyVendor.Cms` を元にした将来の `BEAR.Examples/cms` 配置を前提にしています。
- namespace も将来の `BEAR\Examples\Cms` を前提にします。
- `Source` と `Tests` は壊れた外部リンクを避けるため、URLではなく `BEAR.Examples/cms/...` のパスで示します。
- サンプルの位置付けは次の4種類です。

| Status | 意味 |
|---|---|
| `canonical` | 通常の実装で最初に真似する正規形 |
| `showcase` | 特定機能を切り出して見せる実例 |
| `comparison-only` | 比較理解用。デフォルト実装としてコピーしない |
| `support` | テスト、Fake、生成物など正規形を支える周辺実装 |

## 使い方

1. `Aliases` にある語で検索します。例: `streaming`, `DbQuery`, `PRG`, `FakeSqlQuery`。
2. `Status` で、そのコードをコピーしてよい正規形か、比較用かを確認します。
3. `Source` を読みます。
4. 実装前に `Tests` を読み、期待される振る舞いを確認します。

## Data access / BDR

### `db-read-one-entity` — DBから主キーで1件のEntityを読む

- **ID:** `db-read-one-entity`
- **Aliases:** read one row, fetch entity, primary key lookup, item query, `#[DbQuery]`, BDR read, article detail
- **Status:** `canonical`
- **Use when:** 主キーで1件取得し、型付きEntityとしてResourceで使いたい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onGet()`
  - `BEAR.Examples/cms/src/Query/ArticleQueryInterface.php::item()`
  - `BEAR.Examples/cms/src/Entity/Article.php`
  - `BEAR.Examples/cms/var/db/sql/article_item.sql`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
  - `BEAR.Examples/cms/tests/Resource/Page/ArticleTest.php`
- **Key points:** `item(int $id): Article|null`、SQLファイルは `article_item.sql`、ResourceはSQLを直接持たない。
- **Do not:** Resource内にSQLを書く。templateからDBを読む。

### `db-read-by-natural-key` — natural keyで1件読む

- **ID:** `db-read-by-natural-key`
- **Aliases:** natural key lookup, bySlug, byEmail, byFilename, after insert lookup, unique key read
- **Status:** `canonical`
- **Use when:** clientが指定した一意な値で再取得したい。特にINSERT後に新規IDを回収したい。
- **Source:**
  - `BEAR.Examples/cms/src/Query/ArticleQueryInterface.php::bySlug()`
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onPost()`
  - `BEAR.Examples/cms/var/db/sql/article_by_slug.sql`
  - `BEAR.Examples/cms/src/Query/AuthorQueryInterface.php::byEmail()`
  - `BEAR.Examples/cms/src/Query/MediaQueryInterface.php::byFilename()`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
  - `BEAR.Examples/cms/tests/Smoke/FakeSqlQueryTest.php`
- **Key points:** INSERT後は `lastInsertId()` ではなく、`bySlug()` などのnatural keyで再SELECTする。
- **Do not:** driver依存のID状態をResourceの標準経路に持ち込む。

### `db-read-list-pager` — DBから一覧をページングして読む

- **ID:** `db-read-list-pager`
- **Aliases:** list query, collection resource, pager, `PagesInterface`, `#[Pager]`, article list, filtering
- **Status:** `canonical`
- **Use when:** collection resourceで一覧、絞り込み、ページングを扱いたい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Articles.php::onGet()`
  - `BEAR.Examples/cms/src/Query/ArticleQueryInterface.php::list()`
  - `BEAR.Examples/cms/var/db/sql/article_list.sql`
  - `BEAR.Examples/cms/src/Resource/Page/ArticleList.php::onGet()`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/ArticlesTest.php`
  - `BEAR.Examples/cms/tests/Resource/Page/ArticleListTest.php`
- **Key points:** collectionは `list()`、SQLは `article_list.sql`、`perPage` は `#[Pager(perPage: 'perPage')]` と対応する。
- **Do not:** item resourceに一覧責務を混ぜる。template側でページング計算を始める。

### `db-command-write` — DB書き込みをCommand Interfaceに分ける

- **ID:** `db-command-write`
- **Aliases:** write command, command interface, POST, PUT, DELETE, add update delete, CQRS split
- **Status:** `canonical`
- **Use when:** ResourceからDBの作成、更新、削除を行いたい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onPost()`
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onPut()`
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onDelete()`
  - `BEAR.Examples/cms/src/Query/ArticleCommandInterface.php`
  - `BEAR.Examples/cms/var/db/sql/article_add.sql`
  - `BEAR.Examples/cms/var/db/sql/article_update.sql`
  - `BEAR.Examples/cms/var/db/sql/article_delete.sql`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
  - `BEAR.Examples/cms/tests/Integration/ArticleMySQLTest.php`
- **Key points:** readは `<Entity>QueryInterface`、writeは `<Entity>CommandInterface` に分ける。write methodは `add`, `update`, `delete` の命令形。
- **Do not:** read/write methodを同じinterfaceに混ぜる。

### `db-link-table-sync` — link tableをclear/linkで同期する

- **ID:** `db-link-table-sync`
- **Aliases:** many-to-many, tagIds, link table, clear links, replace relation, article tags
- **Status:** `canonical`
- **Use when:** 記事とタグのような関連テーブルを、入力されたIDリストに置き換えたい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Article.php::syncTags()`
  - `BEAR.Examples/cms/src/Query/ArticleTagCommandInterface.php`
  - `BEAR.Examples/cms/var/db/sql/article_tag_clear.sql`
  - `BEAR.Examples/cms/var/db/sql/article_tag_link.sql`
  - `BEAR.Examples/cms/tests/Fake/FakeSqlQuery.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
  - `BEAR.Examples/cms/tests/Smoke/FakePostQueryRows.php`
- **Key points:** relation更新は `clear($articleId)` 後に `link($articleId, $tagId)` を繰り返す。
- **Do not:** Resource内でlink tableのSQLを直接組み立てる。

### `db-result-projection` — Query結果を専用Result objectにする

- **ID:** `db-result-projection`
- **Aliases:** query projection, read model, SELECT result, `PostQueryInterface`, generator traversal, feed item
- **Status:** `showcase`
- **Use when:** canonical Entityそのものではなく、表示関心ごとのread-side projectionを作りたい。
- **Source:**
  - `BEAR.Examples/cms/src/Query/ArticleSelectionQueryInterface.php`
  - `BEAR.Examples/cms/src/Result/ArticleSelection.php`
  - `BEAR.Examples/cms/src/Result/ArticleFeedItem.php`
  - `BEAR.Examples/cms/src/Resource/Page/ArticleFeed.php`
  - `BEAR.Examples/cms/var/db/sql/article_selection_list.sql`
- **Tests:**
  - `BEAR.Examples/cms/tests/Entity/ArticleProjectionTest.php`
  - `BEAR.Examples/cms/tests/Resource/Page/ArticleFeedTest.php`
  - `BEAR.Examples/cms/tests/Smoke/MediaQuerySamplesTest.php`
- **Key points:** `ArticleSelection::published()` や `feed()` のようなnamed Generatorでtemplate側の条件分岐を減らす。
- **Do not:** presentation専用の加工を汎用Entityやtemplateに押し込む。

### `db-array-row-comparison` — Entityではなくarrayで読む比較例を見る

- **ID:** `db-array-row-comparison`
- **Aliases:** array row, no entity, row array, `type: row`, migration comparison
- **Status:** `comparison-only`
- **Use when:** Entityを使わない実装と正規形の責務差を理解したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Variations/ArticleAsArray.php`
  - `BEAR.Examples/cms/src/Query/Variations/ArticleAsArrayQueryInterface.php`
  - `BEAR.Examples/cms/var/db/sql/article_as_array_item.sql`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/Variations/ArticleAsArrayTest.php`
- **Key points:** 型変換、日付正規化、row shapeの防衛がResource側に寄ることを確認する。
- **Do not:** 長期保守の標準形としてarray rowを無条件に選ばない。

### `db-sqlquery-orchestration` — `SqlQueryInterface`で複数SQLを調停する

- **ID:** `db-sqlquery-orchestration`
- **Aliases:** `SqlQueryInterface`, multi query, previous next article, reading time, programmatic query
- **Status:** `comparison-only`
- **Use when:** 1つの `#[DbQuery]` methodに収まらない複数SQLの調停を理解したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Variations/ArticleSqlQuery.php`
  - `BEAR.Examples/cms/var/db/sql/article_sqlquery_item.sql`
  - `BEAR.Examples/cms/var/db/sql/article_sqlquery_previous.sql`
  - `BEAR.Examples/cms/var/db/sql/article_sqlquery_next.sql`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/Variations/ArticleSqlQueryTest.php`
- **Key points:** `SqlQueryInterface::getRow()` をResourceが直接呼び、複数queryの結果を組み立てる。
- **Do not:** 単純な1件取得まで `SqlQueryInterface` に寄せない。標準形はQuery Interface + `#[DbQuery]`。

### `db-raw-pdo-comparison` — Raw PDOとの違いを見る

- **ID:** `db-raw-pdo-comparison`
- **Aliases:** raw PDO, `ExtendedPdoInterface`, inline SQL, framework comparison, MediaQuery responsibility
- **Status:** `comparison-only`
- **Use when:** Ray.MediaQueryが外部化している責務を低レベル比較で理解したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Variations/ArticleRawPdo.php`
  - `BEAR.Examples/cms/tests/Fake/FakeExtendedPdoProvider.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/Variations/ArticleRawPdoTest.php`
- **Key points:** SQL、bind、fetch、型変換、日付正規化がResource近くに現れる。
- **Do not:** 正規のApp Resourceにinline SQLを戻さない。

## Resource / API

### `api-get-hal-resource` — GET ResourceをHAL+JSONで返す

- **ID:** `api-get-hal-resource`
- **Aliases:** GET resource, HAL JSON, ResourceObject body, API item resource, `onGet`
- **Status:** `canonical`
- **Use when:** App Resourceで1件の状態をAPI表現として返したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onGet()`
  - `BEAR.Examples/cms/src/Resource/App/Author.php::onGet()`
  - `BEAR.Examples/cms/docs/resources.md`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
  - `BEAR.Examples/cms/tests/Hypermedia/HalEnvelopeContractTest.php`
- **Key points:** ResourceObjectは状態を `$this->body` に置く。表現はrendererが作る。
- **Do not:** ResourceでJSON文字列を手作りしない。

### `api-post-input-dto` — POST入力をInput DTOで受ける

- **ID:** `api-post-input-dto`
- **Aliases:** POST resource, create resource, Input DTO, `#[Input]`, Ray.InputQuery, request DTO
- **Status:** `canonical`
- **Use when:** 入力項目が多い作成処理を、Resource methodの前でDTO化したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onPost()`
  - `BEAR.Examples/cms/src/Input/ArticleCreateInput.php`
  - `BEAR.Examples/cms/var/json_validate/article_create.json`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
  - `BEAR.Examples/cms/tests/params/query_args.php`
- **Key points:** Resource method parameterに `#[Input] ArticleCreateInput $input` を置く。schema validationは `#[JsonSchema(params: ...)]`。
- **Do not:** 多数の関連する入力を無理にflat scalar parameterへ増やし続けない。

### `api-put-tristate-input` — PUTでtri-state入力を扱う

- **ID:** `api-put-tristate-input`
- **Aliases:** PUT resource, update resource, tri-state, nullable array, tagIds, leave clear replace
- **Status:** `canonical`
- **Use when:** 省略、空配列、非空配列で異なる意味を持つ更新入力を扱いたい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onPut()`
  - `BEAR.Examples/cms/src/Input/ArticleUpdateInput.php`
  - `BEAR.Examples/cms/var/json_validate/article_update.json`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
- **Key points:** `tagIds === null` は維持、`[]` は全削除、listは置換。DTO内で `mixed` を `is_array` gateに通す。
- **Do not:** `null` と `[]` を同じ意味に潰さない。

### `api-delete-no-content` — DELETE成功を204で返す

- **ID:** `api-delete-no-content`
- **Aliases:** DELETE resource, no content, 204, delete command, not found before delete
- **Status:** `canonical`
- **Use when:** App Resourceで削除操作を公開したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onDelete()`
  - `BEAR.Examples/cms/src/Query/ArticleCommandInterface.php::delete()`
  - `BEAR.Examples/cms/var/db/sql/article_delete.sql`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
- **Key points:** 削除前に存在確認し、成功時は `Code::NO_CONTENT` と空body。
- **Do not:** 削除済みや未存在を成功扱いにしない。

### `not-found-response` — 見つからないResourceを404にする

- **ID:** `not-found-response`
- **Aliases:** 404, not found, missing entity, error body, not found branch
- **Status:** `canonical`
- **Use when:** Queryが `null` を返した時にResourceで404を返したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onGet()`
  - `BEAR.Examples/cms/src/Resource/Page/Article.php::onGet()`
  - `BEAR.Examples/cms/templates/Page/Article.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
  - `BEAR.Examples/cms/tests/Resource/Page/ArticleTest.php`
- **Key points:** not-found readは例外ではなくResourceで404 bodyを置く。Page templateは4xxでも呼ばれるためguardを置く。
- **Do not:** `src/` からgeneric runtime exceptionを投げてnot-found表現にしない。

### `json-schema-validation` — Request/ResponseをJSON Schemaで検証する

- **ID:** `json-schema-validation`
- **Aliases:** `#[JsonSchema]`, response schema, params schema, validation, json_validate, json_schema
- **Status:** `canonical`
- **Use when:** Resourceの入力と出力のshapeを宣言的に固定したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Article.php`
  - `BEAR.Examples/cms/var/json_schema/article.json`
  - `BEAR.Examples/cms/var/json_validate/article_create.json`
  - `BEAR.Examples/cms/var/json_validate/article_update.json`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
  - `BEAR.Examples/cms/tests/params/query_args.php`
- **Key points:** response schemaは `schema:`、request params schemaは `params:`。DTOとschemaは同じ境界を守る。
- **Do not:** Resource body shapeをテストやschemaなしで暗黙に変えない。

### `hal-link` — HAL `_links` を `#[Link]` で宣言する

- **ID:** `hal-link`
- **Aliases:** HAL link, `_links`, `#[Link]`, affordance, Choreography rel, URI template
- **Status:** `canonical`
- **Use when:** clientが次に遷移できるResourceをHAL linkとして表したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onGet()`
  - `BEAR.Examples/cms/src/Resource/App/Articles.php::onGet()`
  - `BEAR.Examples/cms/var/alps/profile.json`
- **Tests:**
  - `BEAR.Examples/cms/tests/Hypermedia/ReaderBrowsesByCategoryTest.php`
  - `BEAR.Examples/cms/tests/Hypermedia/ReaderBrowsesByTagTest.php`
- **Key points:** link relはALPS Choreography名。例: `goArticleList`, `goAuthor`, `goCategory`。
- **Do not:** embed用のtaxonomy名とlink用のchoreography名を混ぜない。

### `hal-embed` — HAL `_embedded` を `#[Embed]` と `addQuery()` で作る

- **ID:** `hal-embed`
- **Aliases:** HAL embed, `_embedded`, `#[Embed]`, `addQuery`, embedded resource, Taxonomy rel
- **Status:** `canonical`
- **Use when:** primary resourceの応答に関連Resourceを埋め込みたい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onGet()`
  - `BEAR.Examples/cms/src/Resource/App/Author.php::onGet()`
  - `BEAR.Examples/cms/src/Resource/App/Category.php::onGet()`
  - `BEAR.Examples/cms/src/Resource/App/Tags.php::onGet()`
- **Tests:**
  - `BEAR.Examples/cms/tests/Hypermedia/HalEnvelopeContractTest.php`
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
- **Key points:** `#[Embed]` が先にRequest slotを作るため、scalar fieldsは `$this->body += [...]` で足す。
- **Do not:** embed relに `go*` 名を使わない。embed slotを `$this->body = [...]` で上書きしない。

## HTML / Page

### `page-resource-qiq-detail` — Page Resourceで1件詳細HTMLを描画する

- **ID:** `page-resource-qiq-detail`
- **Aliases:** Page Resource, Qiq, HTML detail, template variables, article page
- **Status:** `canonical`
- **Use when:** App Resourceとは別に、HTML表示用のPage Resourceを作りたい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/Page/Article.php::onGet()`
  - `BEAR.Examples/cms/templates/Page/Article.php`
  - `BEAR.Examples/cms/src/Renderer/CmsQiqRenderer.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/Page/ArticleTest.php`
- **Key points:** Page Resourceが表示に必要なEntityや値をbodyに置く。Qiq templateは描画に集中する。
- **Do not:** templateからQuery Interfaceを呼ばない。

### `page-resource-list` — Page Resourceで一覧HTMLを描画する

- **ID:** `page-resource-list`
- **Aliases:** HTML list, page list, Qiq list, pager HTML, filter page
- **Status:** `canonical`
- **Use when:** 絞り込みやpager付きの一覧HTMLを表示したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/Page/ArticleList.php::onGet()`
  - `BEAR.Examples/cms/templates/Page/ArticleList.php`
  - `BEAR.Examples/cms/src/Resource/Page/CategoryList.php::onGet()`
  - `BEAR.Examples/cms/src/Resource/Page/TagList.php::onGet()`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/Page/ArticleListTest.php`
  - `BEAR.Examples/cms/tests/Resource/Page/CategoryListTest.php`
  - `BEAR.Examples/cms/tests/Resource/Page/TagListTest.php`
- **Key points:** list query、filter状態、pager表示用値をPage Resourceで準備する。
- **Do not:** template側でDB fetchや複雑なfilter解決を行わない。

### `markdown-to-html` — Markdown本文をHTMLへ変換する

- **ID:** `markdown-to-html`
- **Aliases:** Markdown, CommonMark, bodyHtml, renderer service, HTML conversion
- **Status:** `canonical`
- **Use when:** EntityのMarkdown本文をHTML templateに渡す前に変換したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/Page/Article.php::onGet()`
  - `BEAR.Examples/cms/src/Service/MarkdownRendererInterface.php`
  - `BEAR.Examples/cms/src/Service/CommonMarkRenderer.php`
  - `BEAR.Examples/cms/src/Provider/CommonMarkConverterProvider.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/Page/ArticleTest.php`
- **Key points:** 変換サービスはDIで注入し、Page Resourceで `bodyHtml` を作る。
- **Do not:** template内でMarkdown parserを生成しない。

### `admin-prg-form` — Admin formでPRGを使う

- **ID:** `admin-prg-form`
- **Aliases:** admin form, PRG, Post Redirect Get, form validation, author scoped admin, write UI
- **Status:** `showcase`
- **Use when:** HTML formからApp Resourceのwrite APIを呼び、成功時にredirectしたい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/Page/Admin/Article.php`
  - `BEAR.Examples/cms/src/Resource/Page/Admin/ArticleDelete.php`
  - `BEAR.Examples/cms/templates/Page/Admin/Article.php`
  - `BEAR.Examples/cms/templates/Page/Admin/ArticleDelete.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/Page/Admin/ArticleTest.php`
  - `BEAR.Examples/cms/tests/Resource/Page/Admin/ArticleDeleteTest.php`
  - `BEAR.Examples/cms/tests/Resource/Page/Admin/AuthBoundaryTest.php`
- **Key points:** Page Adminは `$this->resource->post/put/delete('app://self/article', ...)` でApp Resourceを包み、成功時は303 redirect。
- **Do not:** Page AdminとApp Resourceのwrite rulesを別々に二重実装しない。

## Runtime / representation

### `stream-response` — ファイルやバイナリをストリームで返す

- **ID:** `stream-response`
- **Aliases:** streaming, stream response, file download, binary response, BEAR.Streamer, `StreamTransferInject`, `Content-Disposition`
- **Status:** `showcase`
- **Use when:** JSONではなく、ファイル本体や大きなレスポンスを返したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Variations/MediaStream.php::onGet()`
  - `BEAR.Examples/cms/src/Query/MediaQueryInterface.php::item()`
  - `BEAR.Examples/cms/src/Entity/Media.php`
  - `BEAR.Examples/cms/var/media/media-005.svg`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/Variations/MediaStreamTest.php`
- **Key points:** `StreamTransferInject` を使い、`Content-Type`, `Content-Length`, `Content-Disposition` を明示し、open stream resourceを `$this->body` に置く。
- **Do not:** success stream bodyに `#[JsonSchema]` を付けない。ファイルopenをtemplateに置かない。

### `cacheable-leaf` — `#[Cacheable]` だけのleaf resourceを作る

- **ID:** `cacheable-leaf`
- **Aliases:** cache leaf, `#[Cacheable]`, self URI tag, auto purge, QueryRepository cache
- **Status:** `showcase`
- **Use when:** 単体ResourceのGET/PUTで、利用者コードなしにキャッシュと同一URI purgeを示したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Cache/Author.php`
  - `BEAR.Examples/cms/src/Resource/App/Cache/Tag.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/Cache/AuthorCacheTest.php`
  - `BEAR.Examples/cms/tests/Resource/App/CacheTest.php`
- **Key points:** class-level `#[Cacheable]` がcache surface。manual cache primitiveはResourceに出さない。
- **Do not:** leaf resourceに不要な `Surrogate-Key` 手書きコードを足さない。

### `cache-embed-dependency` — `#[Embed]` 親Resourceの依存を自動合成する

- **ID:** `cache-embed-dependency`
- **Aliases:** cache parent, embed dependency, auto dependency, ETag dependency, AuthorProfile
- **Status:** `showcase`
- **Use when:** 親Resourceが子Resourceをembedし、子の更新で親cacheも無効化したい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Cache/AuthorProfile.php`
  - `BEAR.Examples/cms/src/Resource/App/Cache/Author.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/Cache/AuthorProfileCacheTest.php`
  - `BEAR.Examples/cms/tests/Resource/App/CacheTest.php`
- **Key points:** `#[Embed]` 子のURI tagをQueryRepositoryが親へmergeする。親Resourceはmanual cache codeを持たない。
- **Do not:** `#[Embed]` で表せる単一子依存に `fromAssoc()` を使わない。

### `cache-body-derived-dependency` — body由来の可変長依存を `fromAssoc()` で宣言する

- **ID:** `cache-body-derived-dependency`
- **Aliases:** variable dependencies, body-derived dependency, `UriTagInterface::fromAssoc`, surrogate key, article tags cache
- **Status:** `showcase`
- **Use when:** DB結果から得たN個の子URIに依存するResourceをcacheしたい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Cache/ArticleTags.php`
  - `BEAR.Examples/cms/src/Resource/App/Cache/Tag.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/Cache/ArticleTagsCacheTest.php`
  - `BEAR.Examples/cms/tests/Resource/App/CacheTest.php`
- **Key points:** `UriTagInterface::fromAssoc('app://self/cache/tag{?id}', $items)` が唯一のmanual cache primitive。
- **Do not:** 可変長依存を静的 `#[Embed]` で無理に表現しない。空tag headerをセットしない。

### `async-embed-parallel` — embed graphを並列実行に載せる

- **ID:** `async-embed-parallel`
- **Aliases:** async, parallel embed, BEAR.Async, ext-parallel, Swoole, embedded resources
- **Status:** `showcase`
- **Use when:** 既存Resourceの `#[Embed]` graphを変更せずに、runtime overlayで並列化したい。
- **Source:**
  - `BEAR.Examples/cms/bin/async.php`
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onGet()`
  - `BEAR.Examples/cms/docker-compose.yml`
- **Tests:**
  - `BEAR.Examples/cms/tests/Hypermedia/HalEnvelopeContractTest.php`
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
- **Key points:** Resource codeは通常の `#[Embed]` のまま。runtime/context側でparallel moduleを重ねる。
- **Do not:** 並列化のためにResource body assemblyを別物に書き換えない。

### `cli-resource` — ResourceをCLIコマンドとして公開する

- **ID:** `cli-resource`
- **Aliases:** CLI, `#[Cli]`, `#[Option]`, bear-cli-gen, article-show, article-list
- **Status:** `showcase`
- **Use when:** 同じResource methodをHTTPだけでなくCLIからも呼びたい。
- **Source:**
  - `BEAR.Examples/cms/src/Resource/App/Article.php::onGet()`
  - `BEAR.Examples/cms/src/Resource/App/Articles.php::onGet()`
  - `BEAR.Examples/cms/bin/cli/article-show`
  - `BEAR.Examples/cms/bin/cli/article-list`
- **Tests:**
  - `BEAR.Examples/cms/tests/Smoke/MediaQuerySmokeTest.php`
- **Key points:** Resource methodに `#[Cli]` と `#[Option]` を付け、`composer cli` で生成する。
- **Do not:** CLI用に別のapplication serviceを重複実装しない。

## Tests / fake

### `fake-sql-query` — DBなしでMediaQueryをFakeする

- **ID:** `fake-sql-query`
- **Aliases:** FakeSqlQuery, no DB test, fake context, test context, hermetic tests
- **Status:** `support`
- **Use when:** DBなしでResource、Query、write flowをテストしたい。
- **Source:**
  - `BEAR.Examples/cms/tests/Fake/FakeSqlQuery.php`
  - `BEAR.Examples/cms/src/Module/FakeModule.php`
  - `BEAR.Examples/cms/src/Module/TestModule.php`
  - `BEAR.Examples/cms/var/fake/article.json`
- **Tests:**
  - `BEAR.Examples/cms/tests/Smoke/FakeSqlQueryTest.php`
  - `BEAR.Examples/cms/tests/Smoke/FakeEntityFetch.php`
  - `BEAR.Examples/cms/tests/Smoke/FakePostQueryRows.php`
- **Key points:** default PHPUnitはDBなしで動く。Fakeは便利データではなくQuery contractの実行可能な代替。
- **Do not:** すぐmockに逃げず、既存Fakeの意味を保つ。

### `app-resource-test` — App ResourceのAPI contractをテストする

- **ID:** `app-resource-test`
- **Aliases:** resource test, API test, HAL JSON test, fake-hal-api-app, ResourceInterface
- **Status:** `support`
- **Use when:** App Resourceのstatus code、body shape、write flowを固定したい。
- **Source:**
  - `BEAR.Examples/cms/tests/AbstractAppTestCase.php`
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
  - `BEAR.Examples/cms/tests/Resource/App/ArticlesTest.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
- **Key points:** client視点でResourceを呼び、status/body/schema/hypermediaの期待を pin する。
- **Do not:** 実装内部のprivate method単位を主テストにしない。

### `page-resource-test` — Page ResourceのHTML contractをテストする

- **ID:** `page-resource-test`
- **Aliases:** page test, Qiq test, HTML resource test, html-test-hal-api-app
- **Status:** `support`
- **Use when:** Page Resourceとtemplateが期待するHTMLやstatusを固定したい。
- **Source:**
  - `BEAR.Examples/cms/tests/AbstractPageTestCase.php`
  - `BEAR.Examples/cms/tests/Resource/Page/ArticleTest.php`
  - `BEAR.Examples/cms/tests/Resource/Page/ArticleListTest.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/Page/ArticleTest.php`
  - `BEAR.Examples/cms/tests/Resource/Page/IndexTest.php`
- **Key points:** HTML contextは `HtmlModule` とFakeを合成してDBなしで描画を検証する。
- **Do not:** Page testのためだけに実DBを必須にしない。

### `hypermedia-workflow-test` — Link/Embedを辿るworkflowをテストする

- **ID:** `hypermedia-workflow-test`
- **Aliases:** hypermedia test, HAL workflow, follow links, `_links`, `_embedded`, rel naming
- **Status:** `support`
- **Use when:** API clientがHAL linkやembedを使って遷移できることを固定したい。
- **Source:**
  - `BEAR.Examples/cms/tests/Hypermedia/AbstractWorkflowTestCase.php`
  - `BEAR.Examples/cms/tests/Hypermedia/ReaderBrowsesByCategoryTest.php`
  - `BEAR.Examples/cms/tests/Hypermedia/ReaderBrowsesByTagTest.php`
  - `BEAR.Examples/cms/tests/Hypermedia/EditorManagesArticleTest.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Hypermedia/HalEnvelopeContractTest.php`
- **Key points:** rel名の層分離、遷移可能性、HAL envelopeをclient視点で確認する。
- **Do not:** Resource単体のbody assertだけでhypermedia contractを済ませない。

### `mysql-integration-test` — 実DB経路を必要時だけ検証する

- **ID:** `mysql-integration-test`
- **Aliases:** MySQL integration, real DB test, migrations, seed, skip when unavailable
- **Status:** `support`
- **Use when:** SQL、migration、real backendの代表経路を確認したい。
- **Source:**
  - `BEAR.Examples/cms/tests/Integration/AbstractMySQLTestCase.php`
  - `BEAR.Examples/cms/tests/Integration/ArticleMySQLTest.php`
  - `BEAR.Examples/cms/migrations.php`
  - `BEAR.Examples/cms/bin/seed.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Integration/ArticleMySQLTest.php`
  - `BEAR.Examples/cms/tests/Integration/AuthorMySQLTest.php`
  - `BEAR.Examples/cms/tests/Integration/CategoryMySQLTest.php`
  - `BEAR.Examples/cms/tests/Integration/TagMySQLTest.php`
- **Key points:** default test suiteはhermetic。MySQL integrationは接続不可ならskipする。
- **Do not:** 全開発者にMySQL起動を必須にしない。

## Semantic / generated artifacts

### `alps-profile-ssot` — ALPS profileを意味のSSOTにする

- **ID:** `alps-profile-ssot`
- **Aliases:** ALPS, semantic profile, ontology, taxonomy, choreography, SSOT, profile.json
- **Status:** `support`
- **Use when:** Resource名、rel名、入力語彙を意味モデルから揃えたい。
- **Source:**
  - `BEAR.Examples/cms/var/alps/profile.json`
  - `BEAR.Examples/cms/docs/alps.md`
  - `BEAR.Examples/cms/docs/architecture.md`
- **Tests:**
  - `BEAR.Examples/cms/tests/Hypermedia/HalEnvelopeContractTest.php`
- **Key points:** Ontologyは `articleTitle` などの語彙、Taxonomyは `Article` などの名詞、Choreographyは `goArticle` などの遷移名。
- **Do not:** HAL link relとembed relに同じ命名層を使わない。

### `semantic-fake-data` — semantic-exで決定的fake dataを作る

- **ID:** `semantic-fake-data`
- **Aliases:** fake data, semantic-ex, deterministic data, observations, seed source
- **Status:** `support`
- **Use when:** DBなしテストとreal DB seedの両方で使う代表データを生成したい。
- **Source:**
  - `BEAR.Examples/cms/bin/semantic-ex/gen-fake.php`
  - `BEAR.Examples/cms/var/fake/article.json`
  - `BEAR.Examples/cms/var/fake/author.json`
  - `BEAR.Examples/cms/var/fake/observations.md`
  - `BEAR.Examples/cms/bin/seed.php`
- **Tests:**
  - `BEAR.Examples/cms/tests/Smoke/FakeSqlQueryTest.php`
  - `BEAR.Examples/cms/tests/Smoke/SqlSmokeTest.php`
- **Key points:** fake dataは決定的で、参照整合性を持ち、Fakeとreal seedの共通入力になる。
- **Do not:** testごとに意味の違うfixtureを散らさない。

### `json-schema-generated` — fake observationからJSON Schemaを生成する

- **ID:** `json-schema-generated`
- **Aliases:** generated schema, JSON Schema, semantic-ex constraints, response schema, validation schema
- **Status:** `support`
- **Use when:** 実例データから観察した制約をschemaとして固定したい。
- **Source:**
  - `BEAR.Examples/cms/bin/semantic-ex/gen-schemas.php`
  - `BEAR.Examples/cms/var/json_schema/article.json`
  - `BEAR.Examples/cms/var/json_schema/articleList.json`
  - `BEAR.Examples/cms/var/json_validate/article_create.json`
  - `BEAR.Examples/cms/var/json_validate/article_update.json`
- **Tests:**
  - `BEAR.Examples/cms/tests/Resource/App/ArticleTest.php`
  - `BEAR.Examples/cms/tests/params/sql_params.php`
- **Key points:** response schemaとinput validation schemaをResourceの `#[JsonSchema]` に接続する。
- **Do not:** Resource bodyを変えたのにschema更新を忘れない。

### `apidoc-llms-generated` — API docsとllms.txtを生成する

- **ID:** `apidoc-llms-generated`
- **Aliases:** ApiDoc, OpenAPI, llms.txt, docs generation, `composer doc`, API documentation
- **Status:** `support`
- **Use when:** Resource、schema、ALPSから人間向け・AI向けのAPI資料を生成したい。
- **Source:**
  - `BEAR.Examples/cms/apidoc.xml`
  - `BEAR.Examples/cms/docs/openapi.json`
  - `BEAR.Examples/cms/docs/llms.txt`
  - `BEAR.Examples/cms/docs/index.html`
  - `BEAR.Examples/cms/composer.json`
- **Tests:**
  - `BEAR.Examples/cms/tests/Smoke/MediaQuerySmokeTest.php`
- **Key points:** `composer doc` がApiDocとALPS HTMLを生成する。AI向けには `docs/llms.txt` が入口になる。
- **Do not:** 手書きドキュメントだけを正とし、Resourceやschemaとの同期を失わない。
