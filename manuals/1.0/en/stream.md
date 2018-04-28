---
layout: docs-en
title: Stream Response
category: Manual
permalink: /manuals/1.0/en/stream.html
---

# Stream Response

Normally, resources are rendered by renderers into one string and finally `echo`ed out, but then you cannot output content whose size exceeds the memory limit of PHP. With `StreamRenderer` you can stream HTTP output and you can output large size content while keeping memory consumption low. Stream output can also be used in coexistence with existing renderers.

## Change Transferer and Renderer

Use the [StreamTransferInject](https://github.com/bearsunday/BEAR.Streamer/blob/1.x/src/StreamTransferInject.php) trait on the page to render and respond to the stream output. In the example of this download page, since `$body` is made to be a resource variable of the stream, the injected renderer is ignored and the resource is streamed.

```php?start_inline
use use BEAR\Streamer\StreamTransferInject;

class Download extends ResourceObject
{
    use StreamTransferInject;

    public $headers = [
        'Content-Type' => 'image/jpeg',
        'Content-Disposition' => 'attachment; filename="image.jpg"'
    ];

    public function onGet() : ResourceObject
    {
        $fp = fopen(__DIR__ . '/BEAR.jpg', 'r');
        $this->body = $fp;

        return $this;
    }
}
```

## With Renderers

Stream output can coexist with conventional renderers. Normally, Twig renderers and JSON renderers generate character strings, but when a stream is assigned to a part of it, the whole is output as a stream.

This is an example of assigning a `string` and a `resource` variable to the Twig template and generating a page of inline image.

Template

```twig
<!DOCTYPE html>
<html lang="en">
<body>
<p>Hello, {% raw  %}{{ name }}{% endraw %}</p>
<img src="data:image/jpg;base64,{% raw  %}{{ image }}{% endraw %}">
</body>
</html>
```

`name` assigns the string as usual, but assigns the resource variable of the image file's pointer resource to` image` with the `base64-encode` filter.

```php?start_inline
class Image extends ResourceObject
{
    use StreamTransferInject;

    public function onGet(string $name = 'inline image') : ResourceObject
    {
        $fp = fopen(__DIR__ . '/image.jpg', 'r');
        stream_filter_append($fp, 'convert.base64-encode'); // image base64 format
        $this->body = [
            'name' => $name,
            'image' => $fp
        ];

        return $this;
    }
}
```

If you want to further control streaming such as streaming bandwidth and timing control, uploading to the cloud, etc use [StreamResponder](https://github.com/bearsunday/BEAR.Streamer/blob/1.x/src /StreamResponder.php ) which is build for it.

The demo is available at [MyVendor.Stream](https://github.com/bearsunday/MyVendor.Stream).


---
*[This document](https://github.com/bearsunday/bearsunday.github.io/blob/master/manuals/1.0/en/stream.md) needs to be proofread by an English speaker. If interested please send me a pull request. Thank you.*
