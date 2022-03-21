---
layout: docs-ja
title: リソース
category: Manual
permalink: /manuals/1.0/ja/resource_cache.html
---


## リソースキャッシュ

### #[Cacheable]

```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

#[Cacheable]
class User extends ResourceObject
```

`@Cacheable`とアノテートすると`get`リクエストは読み込み用のレポジトリ`QueryRepository`が使われ、時間無制限のキャッシュとして機能します。
`get`以外のリクエストがあると該当する`QueryRepository`のリソースが更新されます。

`@Cacheable`から読まれるリソースオブジェクトはHTTPに準じた`Last-Modified`と`ETag`ヘッダーが付加されます。

同一クラスの`onGet`以外のリクエストメソッドがリクエストされ引数を見てリソースが変更されたと判断すると`QueryRepository`の内容も更新されます。


```php?start_inline
use BEAR\RepositoryModule\Annotation\Cacheable;

#[Cacheable]
class Todo extends ResourceObject
{
    public function onGet(string $id): static
    {
        // read
    }

    public function onPost(string $id, string $name): static
    {
        // update
    }
}
```

例えばこのクラスでは`->post(10, 'shopping')`というリクエストがあると`id=10`の`QueryRepository`の内容が更新されます。
この自動更新を利用しない時は`update`をfalseにします。

```php?start_inline
 #[Cacheable update: false]
```

時間を指定するには、`expiry`を使って、`short`, `medium`あるいは`long`のいずれかを指定できます。
```php?start_inline
 #[Cacheable expiry: 'short']
```


## #[Purge] #[Refresh]

もう１つの方法は`#[Purge]`アノテーションや、`#[Refresh]`アノテーションで更新対象のURIを指定することです。


```php?start_inline
use BEAR\RepositoryModule\Annotation\Purge;
use BEAR\RepositoryModule\Annotation\Refresh;

class News extends ResourceObject
{
   #[Purge(uri: 'app://self/user/friend?user_id={id}')]
   #[Refresh(uri: 'app://self/user/profile?user_id={id}')]
   public function onPut(string $id, string $name, int $age)): static
```

別のクラスのリソースや関連する複数のリソースの`QueryRepository`の内容を更新することができます。
`@Purge`はリソースのキャッシュを消去し`@Refresh`はキャッシュの再生成をメソッド実行直後に行います。

uri-templateに与えられる値は他と同様に`$body`にアサインした値が実引数に優先したものです。

```php?start_inline
use BEAR\RepositoryModule\Annotation\Purge;
use BEAR\RepositoryModule\Annotation\Refresh;

class News extends ResourceObject
{
   #[Purge(uri: 'app://self/user/friend?user_id={id}')]
   #[Refresh(uri: 'app://self/user/profile?user_id={id}')]
   public function onPut($id, $name, $age): static
```

## クエリーリポジトリの直接操作

クエリーリポジトリに格納されているデータは`QueryRepositoryInterface`で受け取ったクライアントで直接`put`（保存）したり`get`したりすることができます。

```php?start_inline
use BEAR\QueryRepository\QueryRepositoryInterface;

class Foo
{
    public function __construct(
    	  private readonly QueryRepositoryInterface $repository
    ) {}

    public function foo()
    {
        // 保存
        $this->repository->put($this);
        $this->repository->put($resourceObject);

        // 消去
        $this->repository->purge($resourceObject->uri);
        $this->repository->purge(new Uri('app://self/user'));
        $this->repository->purge(new Uri('app://self/ad/?id={id}', ['id' => 1]));

        // 読み込み
        [$code, $headers, $body, $view] = $this->repository->get(new Uri('app://self/user'));
     }
```
