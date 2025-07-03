---
layout: docs-ja
title: リソースパラメーター
category: Manual
permalink: /manuals/1.0/ja/resource_param.html
---

# リソースパラメーター

## 基本

ResourceObjectが必要とするHTTPリクエストやCookieなどのWebランタイムの値は、メソッドの引数に直接渡されます。HTTPリクエストでは`onGet`、`onPost`メソッドの引数にはそれぞれ`$_GET`、`$_POST`が変数名に応じて渡されます。

例えば下記の`$id`は`$_GET['id']`が渡されます。入力がHTTPの場合、文字列として渡された引数は指定した型にキャストされます。

```php
class Index extends ResourceObject
{
    public function onGet(int $id): static
    {
        // ....
```

## パラメーターの型

### スカラーパラメーター

HTTPで渡されるパラメーターは全て文字列ですが、`int`など文字列以外の型を指定するとキャストされます。

### 配列パラメーター

パラメーターはネストされたデータ [^2] でも構いません。JSONやネストされたクエリ文字列で送信されたデータは配列で受け取ることができます。

[^2]: [parse_str](https://www.php.net/manual/ja/function.parse-str.php)参照

```php
class Index extends ResourceObject
{
    public function onPost(array $user): static
    {
        $name = $user['name']; // bear
```

### クラスパラメーター

パラメータ専用のInputクラスで受け取ることもできます。

```php
class Index extends ResourceObject
{
    public function onPost(User $user): static
    {
        $name = $user->name; // bear
```

Inputクラスは事前にパラメーターをpublicプロパティにしたものを定義しておきます。

```php
<?php
namespace Vendor\App\Input;

final class User
{
    public int $id;
    public string $name;
}
```

この時、コンストラクタがあるとコールされます。[^php8]

[^php8]: PHP8.xでは名前付き引数で呼ばれますが、PHP7.xでは順序引数でコールされます。

```php
<?php
namespace Vendor\App\Input;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {
    }
}
```

ネームスペースは任意です。Inputクラスでは入力データをまとめたり検証したりするメソッドを実装することができます。

### Ray.InputQueryとの統合

`#[Input]`属性を使って、`Ray.InputQuery`ライブラリによる型安全な入力オブジェクトの生成を利用できます。

```php
use Ray\InputQuery\Attribute\Input;

class Index extends ResourceObject
{
    public function onPost(#[Input] ArticleInput $article): static
    {
        $this->body = [
            'title' => $article->title,
            'author' => $article->author->name
        ];
        return $this;
    }
}
```

`#[Input]`属性付きのパラメーターには、フラットなクエリデータから自動的に構造化されたオブジェクトが生成されて渡されます。

```php
use Ray\InputQuery\Attribute\Input;

final class ArticleInput
{
    public function __construct(
        #[Input] public readonly string $title,
        #[Input] public readonly AuthorInput $author
    ) {}
}

final class AuthorInput  
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $email
    ) {}
}
```

この場合、`title=Hello&authorName=John&authorEmail=john@example.com`のようなフラットなデータからネストしたオブジェクト構造が自動生成されます。

配列データも扱うことができます。

#### シンプルな配列

```php
final class TagsInput
{
    public function __construct(
        #[Input] public readonly string $title,
        #[Input] public readonly array $tags
    ) {}
}
```

```php
class Index extends ResourceObject
{
    public function onPost(#[Input] TagsInput $input): static
    {
        // tags[]=php&tags[]=web&title=Hello の場合
        // $input->tags = ['php', 'web']
        // $input->title = 'Hello'
```

#### オブジェクト配列

`item`パラメーターを使用して、配列の各要素を指定したInputクラスのオブジェクトとして生成できます。

```php
use Ray\InputQuery\Attribute\Input;

final class UserInput
{
    public function __construct(
        #[Input] public readonly string $id,
        #[Input] public readonly string $name
    ) {}
}

class Index extends ResourceObject
{
    public function onPost(
        #[Input(item: UserInput::class)] array $users
    ): static {
        foreach ($users as $user) {
            echo $user->name; // 各要素はUserInputインスタンス
        }
    }
}
```

この場合、以下のような形式のデータから配列を生成します：

```php
// users[0][id]=1&users[0][name]=John&users[1][id]=2&users[1][name]=Jane
$data = [
    'users' => [
        ['id' => '1', 'name' => 'John'],
        ['id' => '2', 'name' => 'Jane']
    ]
];
```

* パラメーターに`#[Input]`属性がある場合：Ray.InputQueryでオブジェクト生成
* パラメーターに`#[Input]`属性がない場合：従来通りの依存性注入

詳細は[Ray.InputQuery](https://github.com/ray-di/Ray.InputQuery)のドキュメントを参照してください。

### 列挙型パラメーター

PHP8.1の[列挙型](https://www.php.net/manual/ja/language.types.enumerations.php)を指定して取り得る値を制限することができます。

```php
enum IceCreamId: int
{
    case VANILLA = 1;
    case PISTACHIO = 2;
}
```

```php
class Index extends ResourceObject
{
    public function onGet(IceCreamId $iceCreamId): static
    {
        $id = $iceCreamId->value // 1 or 2
```

上記の場合、1か2以外が渡されると`ParameterInvalidEnumException`が発生します。

## Webコンテキスト束縛

`$_GET`や`$_COOKIE`などのPHPのスーパーグローバルの値をメソッド内で取得するのではなく、メソッドの引数に束縛することができます。

```php
use Ray\WebContextParam\Annotation\QueryParam;

class News extends ResourceObject
{
    public function foo(
        #[QueryParam('id')] string $id
    ): static {
        // $id = $_GET['id'];
```

その他`$_ENV`、`$_POST`、`$_SERVER`の値を束縛することができます。

```php
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

class News extends ResourceObject
{
    public function onGet(
        #[QueryParam('id')] string $userId,            // $_GET['id']
        #[CookieParam('id')] string $tokenId = "0000", // $_COOKIE['id'] or "0000" when unset
        #[EnvParam('app_mode')] string $app_mode,      // $_ENV['app_mode']
        #[FormParam('token')] string $token,           // $_POST['token']
        #[ServerParam('SERVER_NAME')] string $server   // $_SERVER['SERVER_NAME']
    ): static {
```

クライアントが値を指定した時は指定した値が優先され、束縛した値は無効になります。テストの時に便利です。

## リソース束縛

`#[ResourceParam]`アノテーションを使えば他のリソースリクエストの結果をメソッドの引数に束縛できます。

```php
use BEAR\Resource\Annotation\ResourceParam;

class News extends ResourceObject
{
    public function onGet(
        #[ResourceParam('app://self//login#nickname')] string $name
    ): static {
```

この例ではメソッドが呼ばれると`login`リソースに`get`リクエストを行い、`$body['nickname']`を`$name`で受け取ります。

## コンテントネゴシエーション

HTTPリクエストの`content-type`ヘッダーがサポートされています。`application/json`と`x-www-form-urlencoded`メディアタイプを判別してパラメーターに値が渡されます。[^json]

[^json]: APIリクエストをJSONで送信する場合には`content-type`ヘッダーに`application/json`をセットしてください。
