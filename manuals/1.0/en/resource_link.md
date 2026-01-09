---
layout: docs-en
title: Resource Link
category: Manual
permalink: /manuals/1.0/en/resource_link.html
---

# Reousrce link

Resources can be linked to other resources. There are two types of links: external links [^LO], which link external resources, and internal links [^LE], which embed other resources in the resource itself.

[^LE]: [embedded links](http://amundsen.com/hypermedia/hfactor/#le) Example: html can embed independent image resources.
[^LO]: [out-bound links](http://amundsen.com/hypermedia/hfactor/#le) e.g.) html can link to other related html.

## Out-bound links

Specify links by `rel` (relation) and `href` of the link name. The `href` can be a regular URI or [RFC6570 URI template](https://github.com/ioseb/uri-template).

```php?start_inline
    #[Link rel: 'profile', href: '/profile{?id}']
    public function onGet($id): static
    {
        $this->body = [
            'id' => 10
        ];

        return $this;
    }
```

In the above example, `href` is represented by and `$body['id']` is assigned to `{?id}`. The output in [HAL](https://stateless.group/hal_specification.html) format is as follows

```json
{
    "id": 10,
    "_links": {
        "self": {
            "href": "/test"
        },
        "profile": {
            "href": "/profile?id=10"
        }
    }
}
```


## Internal links

A resource can embed another resource. Specify the resource in the `src` of `#[Embed]`.

Internally linked resources may also internally link other resources. In that case, another internally linked resource is needed, and the process is repeated recursively to obtain a **resource graph**. The client can retrieve the desired set of resources at once without having to fetch the resources multiple times. [^di] For example, instead of calling a customer resource and a product resource respectively, embed them both in an order resource.

[^di]:This is similar to an object graph where the dependency tree is a graph in DI.

```php?start_inline
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    #[Embed(rel: 'sports', src: '/news/sports')]
    #[Embed(rel: 'weather', src: '/news/weather')]
    public function onGet(): static
```

It is the resource **request** that is embedded. It is executed at rendering time, but before that you can add arguments with the `addQuery()` method or replace them with `withQuery()`.

A URI template can be used for the `src`, and **request method arguments** will be bound to it. (Unlike external links, it is not `$body`)

```php?start_inline
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    #[Embed(rel: 'website', src: '/website{?id}']
    public function onGet(string $id): static
    {
        // ...
        $this->body['website']->addQuery(['title' => $title]); // 引数追加
```

### Self linking

Linking a relation as `_self` in ``#[Embed]`` copies the linked resource state to its own resource state.

```php
namespace MyVendor\Weekday\ResourcePage;.

class Weekday extends ResourceObject
{
#[Embed(rel: '_self', src: 'app://self/weekday{?year,month,day}'])
public function onGet(string $id): static
{
```

In this example, the Page resource copies the state of the `weekday` resource of the App resource to itself.

### Internal links in HAL

Handled as `_embedded ` in the [HAL](https://github.com/blongden/hal) renderer.

## Link request

Clients can link resources connected by hyperlinks.

```php?start_inline
$blog = $this
    ->resource
    ->get
    ->uri('app://self/user')
    ->withQuery(['id' => 1])
    ->linkSelf("blog")
    ->eager
    ->request()
    ->body;
```

There are three types of links. The `body` linked resource of the original resource is embedded using `$rel` as the key.

* `linkSelf($rel)` which will be replaced with the link destination.
* `linkNew($rel)` the linked resource is added to the original resource
* `linkCrawl($rel)` crawl the link and create a resource graph.

## crawl

Crawls are lists (arrays) of resources, and links can be traversed in sequence to compose complex resource graphs.
Just as a crawler crawls a web page, the resource client crawls hyperlinks and generates a source graph.

#### Crawl Example

Consider a resource graph with author, post, meta, tag, and tag/name associated with each.
Name this resource graph **post-tree** and specify a hyperreference **href** in the `#[Link]' attribute of each resource.



The first starting point, the author resource, has a hyperlink to the post resource. 1:n relationship.

```php
#[Link(crawl: "post-tree", rel: "post", href: "app://self/post?author_id={id}")]
public function onGet($id = null)
```

The post resource has hyperlinks to the meta and tag resources. 1:n relationship.

```php
#[Link(crawl: "post-tree", rel: "meta", href: "app://self/meta?post_id={id}")]
#[Link(crawl: "post-tree", rel: "tag", href: "app://self/tag?post_id={id}")]
public function onGet($author_id)
{
```

A tag resource is just an ID with a hyperlink to the corresponding tag/name resource. 1:1 relationship.

```php
#[Link(crawl:"post-tree", rel:"tag_name", href:"app://self/tag/name?tag_id={tag_id}")]
public function onGet($post_id)
```

Each is now connected. Request with a crawl name.

```php
$graph = $resource
  ->get
  ->uri('app://self/marshal/author')
  ->linkCrawl('post-tree')
  ->eager
  ->request();
```

When a resource client finds a crawl name specified in the #[Link] attribute, it creates a resource graph by connecting resources by their **rel** names.

```
var_export($graph->body);

array (
    0 =>
    array (
        'name' => 'Athos',
        'post' =>
        array (
            0 =>
            array (
                'author_id' => '1',
                'body' => 'Anna post #1',
                'meta' =>
                array (
                    0 =>
                    array (
                        'data' => 'meta 1',
                    ),
                ),
                'tag' =>
                array (
                    0 =>
                    array (
                        'tag_name' =>
                        array (
                            0 =>
                            array (
                                'name' => 'zim',
                            ),
                        ),
                    ),
 ...
```

### DataLoader

Crawl operations can cause N+1 problems when each linked resource makes individual database queries. The DataLoader pattern solves this by batching multiple requests into a single query.

The DataLoader name comes from [Facebook's DataLoader](https://github.com/graphql/dataloader), which provides a consistent API for batching and caching data loading operations.

#### Without DataLoader

```
Author (1 query)
└── Post × 3 (1 query, returns 3)
    └── Meta (3 queries) ← N+1 problem!
```

#### With DataLoader

```
Author (1 query)
└── Post × 3 (1 query)
    └── Meta (1 batched query) ← Solved!
```

#### Implementation

1. Create a DataLoader class implementing `DataLoaderInterface`:

```php
use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\DataLoader\DataLoaderInterface;

class MetaDataLoader implements DataLoaderInterface
{
    public function __construct(
        private ExtendedPdoInterface $pdo
    ) {}

    public function __invoke(array $queries): array
    {
        // $queries contains query parameters for each request
        // e.g., [['post_id' => '1'], ['post_id' => '2'], ['post_id' => '3']]

        $postIds = array_column($queries, 'post_id');

        // Single bulk query with IN clause
        $sql = "SELECT * FROM meta WHERE post_id IN (:post_ids)";
        return $this->pdo->fetchAll($sql, ['post_ids' => $postIds]);
    }
}

// You can also use Ray.MediaQuery pattern:
// class MetaDataLoader implements DataLoaderInterface
// {
//     public function __construct(private MetaQueryInterface $query) {}
//     public function __invoke(array $queries): array
//     {
//         return $this->query->list(array_column($queries, 'post_id'));
//     }
// }
```

2. Specify the DataLoader in the `#[Link]` attribute:

```php
#[Link(crawl: "post-tree", rel: "meta", href: "app://self/meta{?post_id}", dataLoader: MetaDataLoader::class)]
public function onGet($author_id)
```

The DataLoader automatically:
- Collects all queries during crawl
- Calls your DataLoader once with all queries
- Distributes results to the appropriate resources based on key matching

#### Key Inference

The key for matching results is auto-inferred from the URI template:

| URI Template | Inferred Key |
|--------------|--------------|
| `{?post_id}` | `post_id` |
| `post_id={id}` | `post_id` |
| `{?post_id,locale}` | `post_id`, `locale` |

The returned rows must contain the key column(s) for proper distribution.

#### Multiple Keys

For multiple key parameters, use all keys in your query:

```php
// URI template: app://self/translation{?post_id,locale}
// $queries: [['post_id' => '1', 'locale' => 'en'], ['post_id' => '1', 'locale' => 'ja']]

public function __invoke(array $queries): array
{
    // Build query using both keys
    $sql = "SELECT * FROM translation WHERE (post_id, locale) IN (...)";
    // ...
}
```
