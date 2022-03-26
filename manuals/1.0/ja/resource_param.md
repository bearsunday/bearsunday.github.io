---
layout: docs-ja
title: リソースパラメーター
category: Manual
permalink: /manuals/1.0/ja/resource_param.html
---

# リソースパラメーター

## 基本

ResourceObjectが必要なHTTPリクエストやCookieなどのWebのランタイムの値は、メソッドの引数に直接渡されます。

HTTPからリクエストでは`onGet`、`onPost`メソッドの引数にはそれぞれ`$_GET`、`$_POST`が変数名に応じて渡されます。例えば下記の$idは$_GET['id']が渡されます。


```php?start_inline
class Index extends ResourceObject
{
    public function onGet(int $id): static
    {
```


## 配列パラメーター

パラメーターはネストされたデータ [^2] でも構いません。JSONやネストされたクエリ文字列で送信されたデータは配列で受け取る事ができます。

[^2]:[parse_str](https://www.php.net/manual/ja/function.parse-str.php)参照 

```php?start_inline
class Index extends ResourceObject
{
    public function onPost(array $user):static
    {
        $name = $user['name']; // bear
```

## Inputクラスパラメーター

パラメータ専用のInputオブジェクトで受け取ることもできます。

```php?start_inline
class Index extends ResourceObject
{
    public function onPost(User $user): static
    {
        $name = $user->name; // bear
```

Inputクラスは事前にパラメーターをpublicプロパティにしたものを定義しておきます。

```php?start_inline
<?php

namespace Vendor\App\Input;

final class User
{
    public int $id;
    public string $name;
}
```

この時、コンストラクタがあるとコールされます。[^php8]

[^php8]: この時PHP8.xでは名前付き引数で呼ばれますが、PHP7.xでは順序引数でコールされます。

```php?start_inline
<?php

namespace Vendor\App\Input;

final class User
{
    public function __constrcut(
        public readonly int $id,
        public readonly string $name
    } {}
}
```

ネームスペースは任意です。Inputクラスでは入力データをまとめたり検証したりするメソッドを実装する事ができます。

## Webコンテキスト束縛

`$_GET`や`$_COOKIE`などのPHPのスーパーグローバルの値をメソッド内で取得するのではなく、メソッドの引数に束縛することができます。

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;

class News extends ResourceObject
{
    public function foo(
    	  #[QueryParam('id')] string $id
    ): static {
       // $id = $_GET['id'];
```

その他`$_ENV`、`$_POST`、`$_SERVER`の値を束縛することでできます。

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

class News extends ResourceObject
{
    public function onGet(
        #[QueryParam('id')] string $userId,            // $_GET['id'];
        #[CookieParam('id')] string $tokenId = "0000", // $_COOKIE['id'] or "0000" when unset;
        #[EnvParam('app_mode')] string $app_mode,      // $_ENV['app_mode'];
        #[FormParam('token')] string $token,           // $_POST['token'];
        #[ServerParam('SERVER_NAME') string $server    // $_SERVER['SERVER_NAME'];
    ): static {
```

クライアントが値を指定した時は指定した値が優先され、束縛した値は無効になります。テストの時に便利です。

## リソース束縛

`#[ResourceParam]`アノテーションを使えば他のリソースリクエストの結果をメソッドの引数に束縛できます。

```php?start_inline
use BEAR\Resource\Annotation\ResourceParam;

class News extends ResourceObject
{
    public function onGet(
        #[ResourceParam('app://self//login#nickname') string $name
    ): static {
```

この例ではメソッドが呼ばれると`login`リソースに`get`リクエストを行い`$body['nickname']`を`$name`で受け取ります。

## コンテントネゴシエーション

HTTPリクエストの`content-type`ヘッダーがサポートされていてます。 `application/json`と`x-www-form-urlencoded`メディアタイプを判別してパラメーターに値が渡されます。[^json]

[^json]:APIリクエストをJSONで送信する場合には`content-type`ヘッダーに`application/json`をセットしてください。

