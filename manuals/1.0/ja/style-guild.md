---
layout: docs-ja
title: 開発
category: Manual
permalink: /manuals/1.0/ja/style-guide.html
---

# BEAR.Sunday スタイルガイド

## PSR

[PSR1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md), [PSR2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), [PSR4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)に準拠します。

## グローバル

グローバルな値をリソースやアプリケーションのクラスで参照することは推奨されません。グローバルな値はモジュールでのみ使用します。

* [スーパーグローバル](http://php.net/manual/ja/language.variables.superglobals.php)の値を参照しない
* [define](http://php.net/manual/ja/function.define.php)は使用しない
* 設定値を保存するグローバルな`Config`クラスを使用しない
* グローバルなオブジェクトコンテナを使用しない
* 他のクラスのスタティックプロパティの参照しない
* [date](http://php.net/manual/ja/function.date.php)関数や[DateTime](http://php.net/manual/ja/class.datetime.php)クラスで現在時刻を直接取得することは推奨されません。[koriym/now](https://github.com/koriym/Koriym.Now)などを使って外部から時刻をインジェクトします。

スタティックメソッドなどのグローバルなメソッドコールも推奨されません。

アプリケーションコードが必要とする値は設定ファイルなどから取得するのではなく、基本的に全てインジェクトします。（設定ファイルはインジェクトのために使います）Web APIなど外部のシステムの値を利用する時には、クライアントクラスやWeb APIアクセスリソースなど１つにの場所に集中させDIやAOPでモッキングが容易にするようにします。

## プログラミング一般

* インジェクション以外で[トレイト](http://php.net/manual/ja/language.oop5.traits.php)は推奨されません。
* 親クラスのメソッドを子クラスが使うことは推奨されません。機能はクラスにして合成します。
* メソッドが１つだけのクラスは機能をクラス名に反映してメソッドの名前を`__invoke`にします。

## スクリプトコマンド

* `composer setup`コマンドでアプリケーションのセットアップが完了することが推奨されます。このスクリプトではデータベースの初期化、必要ライブラリの確認が含まれます。`.env`の設定などマニュアルな操作が必要な場合はその手順が画面表示されることが推奨されます。
* `composer cleanup`コマンドでアプリケーションのキャッシュやログが全てクリアされることが推奨されます。

## コードチェック

commit毎に以下のコマンドでコードのチェックすることを推奨します。

```
phpcs src tests
phpmd src text ./phpmd.xml
php-cs-fixer fix --config-file=./.php_cs
phpcbf src
```

## リソース

リンクを持つリソースは`@Link`で示すことが推奨されます。

```
/**
 * @param $id
 *
 * @Link(rel="profile", href="/profile{?id}")
 * @Link(rel="blog", href="app://self/blog{?id}")
 */
public function onGet(string $id)
```

リソースがリソースを含む時は`@Embed`が推奨されます。

```
/**
 * @Embed(rel="user", src="/user?{user_id}")
 */
public function onGet(string $userId)
{
```

```
/**
 * @Embed(rel="uid", src="/uid")
 */
public function onPost(string $userId, string $title) : ResourceObject
{
    $uid = $this['uid']()->body;
```

`@Embed`するリソースのリクエストに必要なクエリーがメソッド無いで決定する時はパラメーターが含まれない不完全なリソースを`@Embed`しておいてクエリーを指定します。

```
/**
 * @Embed(rel="user", src="/user")
 */
public function onGet()
{
    ...
    $query = ['userId' => $userId];
    $user = $this['user']->withQuery($query)()->body; // app://self/user?user={$userId}
```

@Embedで指定したURIにクエリーに付加する時は`addQuery()`を使います。

```
/**
 * @Embed(rel="user", src="/user&category=1")
 */
public function onGet()
{
    ...
    $query = ['userId' => $userId];
    $user = $this['user']->addQuery($query)()->body; // /user?category=1&user=$userId
```

`onGet`以外のメソッドで`_GET`の値を利用するには`@QueryParam`を使います。その他PHPのスーパーグローバル変数に格納される値は[Webコンテキストパラメーター
](https://github.com/ray-di/Ray.WebParamModule)で引数に束縛します。

```
/**
 * @QueryParam(key="id", param="userId")
 */
public function foo($userId = null)
{
   // $userId = $_GET['id'];
```

他のリソースの値を引数に利用する場合には`@ResourceParam`を使います。

```
/**
 * @ResourceParam(param=“name”, uri="/login#nickname")
 */
public function onGet($name)
{
```  

リソースクライアントは可能な限り使わないで `@Embed`で埋め込んだり`@Link`のリンクを使うようにします。埋め込まれたリソースは`toUri()`や`toUriWithMethod()`でリクエスト文字列になりテストが容易です。

## Ray.Di

 * ライブラリコードではセッターインジェクションは推奨されません。
 * `Provider`束縛を可能な限り避け`toConstructor`束縛を優先することが推奨されます。
 * `Module`で条件に応じて束縛をすることを避けます。 ([AvoidConditionalLogicInModules](https://github.com/google/guice/wiki/AvoidConditionalLogicInModules))

## 環境

Webだけでしか動作しないアプリケーションは推奨されません。テスト可能にするためにコンソールでも動作するようにします。

## テスト

リソースクライアントを使ったリソーステストを基本にします。リソースの値を確認して、必要があれば表現(HTMLやJSON)のテストを加えます。
