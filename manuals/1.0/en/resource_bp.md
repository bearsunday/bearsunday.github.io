---
layout: docs-en
title: Resource Best Practise
category: Manual
permalink: /manuals/1.0/en/resource_bp.html
---

## Best Practices

In REST, resources are connected to other resources. Good use of links makes code concise, easy to read, test and change.

### #[Embed].

Embed a resource with `#[Embed]` instead of `get` the state of another resource.

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
    #[@Embed(rel: 'todos', src: 'app://self/todos{?status}')]
    public function onGet(string $status): static
    {
        return $this;
    }
}
```

### #[Link]

The next action indicated by `#[Link]` when changing the state of another resource is traced using `href()` (hyper reference).

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
    public function onPost(string $title): static
    {
        $this->resource->href('create', ['title' => $title]);
        $this->code = 301;
        $this->headers[ResponseHeader::LOCATION] = '/';

        return $this;
    }
}
```

### #[ResourceParam]

Use `#[ResourceParam]` if you need other resource results to request other resources.

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
    public function onGet(string $id, string $name): static
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
