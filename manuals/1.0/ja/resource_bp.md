---
layout: docs-ja
title: リソースベストプラクティス
category: Manual
permalink: /manuals/1.0/ja/resource_bp.html
---



## ベストプラクティス

RESTではリソースは他のリソースと接続されています。リンクをうまく使うとコードは簡潔になり、読みやすくテストや変更が容易なコードになります。

### #[Embed]

他のリソースの状態を`get`する代わりに`@Embed`でリソースを埋め込みます。

```php?start_inline
// OK but not the best
class Index extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )

    public function onGet(string $status): static
    {
        $this->body = [
            'todos' => $this->resource->uri('app://self/todos')(['status' => $status]) // lazy request
        ];

        return $this;
    }
}

// Better
class Index extends ResourceObject
{
    #[@mbed(rel: 'todos', src: 'app://self/todos{?status}')]
    public function onGet(string $status): static
    {
        return $this;
    }
}
```

### #[Link]

他のリソースの状態を変えるときに`@Link`で示された次のアクションを`href()`（ハイパーリファレンス）を使って辿ります。

```php?start_inline
// OK but not the best
class Todo extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )

    public function onPost(string $title): static
    {
        $this->resource->post('app://self/todo', ['title' => $title]);
        $this->code = 301;
        $this->headers[ResponseHeader::LOCATION] = '/';

        return $this;
    }
}

// Better
class Todo extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )

    #[Link(rel: 'create', href: 'app://self/todo', method: 'post')]
    public function onPost(string $title) : ResourceObject
    {
        $this->resource->href('create', ['title' => $title]);
        $this->code = 301;
        $this->headers[ResponseHeader::LOCATION] = '/';

        return $this;
    }
}
```

### #[ResourceParam]

他のリソースをリクエストするために他のリソース結果が必要な場合は`#[ResourceParam]`を使います。

```php?start_inline
// OK but not the best
class User extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )

    public function onGet(string $id): static
    {
        $nickname = $this->resource->get('app://self/login-user', ['id' => $id])->body['nickname'];
        $this->body = [
            'profile'=> $this->resource->get('app://self/profile', ['name' => $nickname])->body
        ];

        return $this;
    }
}

// Better
class User extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource
    )

    #[ResourceParam(param: 'name', uri: 'app://self//login-user#nickname')]
    public function onGet(string $id, string $name) : ResourceObject
    {
        $this->body = [
            'profile' => $this->resource->get('app://self/profile', ['name' => $name])->body
        ];

        return $this;
    }
}

// Best
class User extends ResourceObject
{
    #[ResourceParam(param: 'name', uri: 'app://self//login-user#nickname')]
    #[Embed(rel: 'profile', src: 'app://self/profile')]
    public function onGet(string $id, string $name): static
    {
        $this->body['profile']->addQuery(['name'=>$name]);

        return $this;
    }
}
```

