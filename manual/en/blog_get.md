---
layout: default
title: BEAR.Sunday | Blog Tutorial(3) Creating an posts resource
category: Blog Tutorial
---
# Resource Object 

BEAR.Sunday is a resource orientated framework. The relevant information is grouped into a resource, given a URI and has a request interface so that it works with GET/POST requests.

In terms of MVC the role of M and C component are taken care of in BEAR.Sunday each by a page resource which acts as a page controller, the model is carried by application (app) resource. These resources are basically mapped as 1 resource to one class, the class name including the namespace responding to a URI the request interface is described as a method.

For example, page for browsing posts is set up as a posts view resource (page://self/blog/posts), the so called state application resource (app://self/blog/posts) shows the article itself.  

## Post Resource 

The application resource so to speak is an application *internal* API. In MVC terms this would be as a model. This holds the internal database accessing or business logic and offers an application internal API to the page resource which has the role of a controller.

In the post resource in order to browse posts we need to implement an `onGet` method that responds to a GET request.

It is implemented in this kind of example resource class.

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

```php
<?php
namespace Demo\Sandbox\Resource\App\Blog;

use BEAR\Resource\ResourceObject;

class Posts extends ResourceObject
{
    public function onGet($id = null)
    {
        $this->body = '_Data that has been read from a database_';
        return $this;
    }
}
```

The inside the method that corresponds to the request (request interface) data is assigned to the `body` property and `$this` is returned.
 
Note: Instead of setting the $body property you can just directly return data. In which case in the receiving side the equivalent of `return $this;` is returned.

## Implement the Request Interface 

Next we will actually access a db and extract data to be used in an `onGet` method to respond to a GET request.

BEAR.Sunday doesn't have its own database usage library or database abstraction library. Inside the application resource by using other libraries you can directly use SQL or using an ORM. Inside the sandbox application [Docrine DBAL](http://www.doctrine-project.org/projects/dbal.html) is used.

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

```php
<?php

namespace Demo\Sandbox\Resource\App\Blog;

use BEAR\Package\Module\Database\Dbal\Setter\DbSetterTrait;
use BEAR\Resource\Header;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Code;
use BEAR\Resource\Annotation\Link;
use PDO;
use BEAR\Sunday\Annotation\Cache;
use BEAR\Sunday\Annotation\CacheUpdate;
use BEAR\Sunday\Annotation\Db;
use BEAR\Sunday\Annotation\Time;
use BEAR\Sunday\Annotation\Transactional;

/**
 * @Db
 */
class Posts extends ResourceObject
{
    use DbSetterTrait;

    /**
     * Current time
     *
     * @var string
     */
    public $time;

    public $links = [
        'page_post' => [Link::HREF => 'page://self/blog/posts/post'],
        'page_item' => [Link::HREF => 'page://self/blog/posts/post{?id}', Link::TEMPLATED => true],
        'page_edit' => [Link::HREF => 'page://self/blog/posts/edit{?id}', Link::TEMPLATED => true],
        'page_delete' => [Link::HREF => 'page://self/blog/posts/post']
    ];

    /**
     * @var string
     */
    protected $table = 'posts';

    /**
     * @param int $id
     *
     * @Cache(100)
     */
    public function onGet($id = null)
    {
        $sql = "SELECT id, title, body, created, modified FROM {$this->table}";
        if (is_null($id)) {
            $stmt = $this->db->query($sql);
            $this->body = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this;
        }
        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->execute();
        $this->body = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this;
    }

    /**
     * @param string $title
     * @param string $body
     *
     * @Time
     * @Transactional
     * @CacheUpdate
     */
    public function onPost($title, $body)
    {
        $values = [
            'title' => $title,
            'body' => $body,
            'created' => $this->time
        ];
        $this->db->insert($this->table, $values);
        //
        $lastId = $this->db->lastInsertId('id');
        $this->code = Code::CREATED;
        $this->headers[Header::LOCATION] = "app://self/posts/post?id={$lastId}";
        $this->headers[Header::X_ID] = $lastId;

        return $this;
    }

    /**
     * @param int    $id
     * @param string $title
     * @param string $body
     *
     * @Time
     * @CacheUpdate
     */
    public function onPut($id, $title, $body)
    {
        $values = [
            'title' => $title,
            'body' => $body,
            'created' => $this->time
        ];
        $this->db->update($this->table, $values, ['id' => $id]);
        $this->code = Code::NO_CONTENT;

        return $this;
    }

    /**
     * @param int $id
     *
     * @CacheUpdate
     */
    public function onDelete($id)
    {
        $this->db->delete($this->table, ['id' => $id]);
        $this->code = Code::NO_CONTENT;

        return $this;
    }
}
```
In the resource class a method that responds to the request interface is provided. In this resource if an $id is specified 1 post and if not set all posts are returned;

## Use the Resource from the Command Line 

_Demo.Sandbox/src/Resource/App/Blog/Posts.php_

The URI `app://self/blog/posts` is given to the app resource specified in the `Demo\Sandbox\Resource\App\Blog\Posts` class.

Let's take a look at the resource we made from the command line.

Let's make the call from the console.

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get app://self/blog/posts

200 OK
tag: [3959571851]
x-cache: ["{\"mode\":\"W\",\"date\":\"Thu, 29 May 2014 08:30:35 +0200\",\"life\":100}"]
content-type: ["application\/hal+json; charset=UTF-8"]
cache-control: ["no-cache"]
date: ["Thu, 29 May 2014 06:30:35 GMT"]
[BODY]
0 => array(
  id 1,
  title Perspective,
  body Perspective is worth 80 IQ points.

-- Alan Kay,
  created 2013-10-14 17:40:49,
  modified ,
),
1 => array(
  id 2,
  title Before it becomes normal,
  body Quite a few people have to believe something is normal before it becomes normal - a sort of 'voting' situation. But once the threshold is reached, then everyone demands to do whatever it is.

-- Alan Kay,
  created 2013-10-14 17:41:13,
  modified ,
),
2 => array(
  id 3,
  title Most software today,
  body Most software today is very much like an Egyptian pyramid with millions of bricks piled on top of each other, with no structural integrity, but just done by brute force and thousands of slaves.

-- Alan Kay,
  created 2013-10-14 17:41:37,
  modified ,
),
...
[VIEW]
{
    "0": {
        "id": "1",
        "title": "Perspective",
        "body": "Perspective is worth 80 IQ points.\r\n\r\n-- Alan Kay",
        "created": "2013-10-14 17:40:49",
        "modified": null
    },
    "1": {
        "id": "2",
        "title": "Before it becomes normal",
        "body": "Quite a few people have to believe something is normal before it becomes normal - a sort of 'voting' situation. But once the threshold is reached, then everyone demands to do whatever it is.\r\n\r\n-- Alan Kay",
        "created": "2013-10-14 17:41:13",
        "modified": null
    },
    "2": {
        "id": "3",
        "title": "Most software today",
        "body": "Most software today is very much like an Egyptian pyramid with millions of bricks piled on top of each other, with no structural integrity, but just done by brute force and thousands of slaves.\r\n\r\n-- Alan Kay",
        "created": "2013-10-14 17:41:37",
        "modified": null
    },
...
```

The result of the request shows `[BODY]` what the resouce has, `[VIEW]` how the resouce represents.

Note: `self` means a resource of the current application. In BEAR.Sunday it is possible to request a resource from another application or to set up and use resources that cross applications.

A query specifying parameters.

```
$ php apps/Demo.Sandbox/bootstrap/contexts/api.php get 'app://self/blog/posts?id=1'
```

## Alias Settings 

It is handy to create an alias to the full path in your shell. 

_~/.bash_profile_

```
alias api='php /path/to/apps/Demo.Sandbox/bootstrap/contexts/api.php'
alias web='php /path/to/apps/Demo.Sandbox/bootstrap/contexts/dev.php'
```

Using the resource API above you can make the following web request. This is then a simple notation and you can use resource using the console from any directory. This is handy when using scripts from the OS for batch processing and the like.

```
// API access
$ api get app://self/blog/posts

// web access
$ web get /blog/posts
```

## API Driven Development 

In this way in BEAR.Sunday internal API development is used as a base to create web applications. A resource functions as a service layer, a name(uri) is provided to access data resource or business logic which is bundled through a RESTful universal interface.

We _do not_ create/provide a web application based external API interface, we build an application as an API collection on an internal resource API base. 

## Runtime Injection 

Each time this app resource is accessed by a get request, the setDb() in DbSetterTrait get previously called and the DB object is injected from outside. It is not configured for this class to use any DB object, please focus on the injected object that is being relied on. In a *GET* request a slave DB object can be injected and for the other *PUT*,*POST*,*DELETE* requests a master DB object.

This is referred to as runtime injection. Binding between the particular method (in this case onGet) and the intercepter that is called before that method is executed (in this case DB object injector) are then acheived.

This architecture of the DB object being injected at runtime is not a BEAR.Sunday fixed structure it is the work of `DotrineDbalModule` which you install in `AppModule`. In  `DotrineDbalModule` class methods annotated with *@Db* binds the DB injector, that DB injector looks at the request method, decides whether master or slave should be used and sets the DB object.