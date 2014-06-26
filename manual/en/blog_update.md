---
layout: default
title: BEAR.Sunday | Blog Tutorial(2) Editing a Post
category: Blog Tutorial
---

# PUT Method 

## Creating an Edit Page 

This is pretty much the same as posts create page. What is different is only that in the display (GET Request) is that the post data pre-populates the fields.

{% highlight php startinline %}
<?php
    /**
     * @param int $id
     */
    public function onGet($id)
    {
        $this['submit'] = $this
            ->resource
            ->get
            ->uri('app://self/posts')
            ->withQuery(['id' => $id])
            ->eager
            ->request()
            ->body;
        $this['id'] = $id;

        return $this;
    }

    /**
     * @param int    $id
     * @param string $title
     * @param string $body
     *
     * @Form
     */
    public function onPut($id, $title, $body)
    {
        // create post
        $this->resource
            ->put
            ->uri('app://self/posts')
            ->withQuery(['id' => $id, 'title' => $title, 'body' => $body])
            ->eager->request();

        // redirect
        $this->code = 303;
        $this->headers = ['Location' => '/blog/posts'];

        return $this;
    }
{% endhighlight %}

## Create a Posts Resource PUT interface 

Receive a request post from a posts page and through accessing the DB update the post. 

*Demo.Sandbox/src/Resource/App/Blog/Posts.php*

{% highlight php startinline %}
    /**
     * @param int    $id
     * @param string $title
     * @param string $body
     */
    public function onPut($id, $title, $body)
    {
        $values = [
            'title' => $title,
            'body' => $body,
            'modified' => $this->time
        ];
        $this->db->update($this->table, $values, ['id' => $id]);
        $this->code = 204;

        return $this;
    }
{% endhighlight %}

## Create a template

*Demo.Sandbox/src/Resource/Page/Blog/Posts/Edit.tpl*

{% highlight php startinline %}
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="container">
        <h1>New Post</h1>
        <form action="/blog/posts/edit" method="POST">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="id" value="{$id}">
            
            <div class="form-group {if $errors.title}error{/if}">
                <label class="control-label" for="title">Title</label>
                <div class="controls">
                    <input type="text" id="title" name="title" value="{$submit.title}">
                    <p class="help-block">{$errors.title}</p>
                </div>
            </div>
            <div class="form-group {if $errors.body}error{/if}">
                <label class="control-label" for="body">Body</label>
                <textarea name="body" rows="10" cols="40">{$submit.body}</textarea>
                <p class="help-block">{$errors.body}</p>
            </div>
            <input type="submit" value="Send">
        </form>
    </div>
</body>
</html>
{% endhighlight %}

## PUT Request

In order to update the record we use the `PUT` interface.

In order to make a `PUT` request we need to insert the HTTP method override field.

```html
<input type="hidden" name="_method" value="PUT">
```

Note: In this tutorial we have handles `POST` posts creation and `PUT` posts update. The difference between POST/PUT is *[Idempotence](http://en.wikipedia.org/wiki/Idempotence)*. If the same `POST` request is made multiple times to the posts resource the amount of post records will increase and increase, in an `PUT` update no matter whether the request is made once or multiple times has the same affect. Generally basing your choice of method upon indempotence is a good idea.
