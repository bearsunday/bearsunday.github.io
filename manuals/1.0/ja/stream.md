---
layout: docs-ja
title: ストリーム出力
category: Manual
permalink: /manuals/1.0/ja/stream.html
---

# ストリーム出力

通常リソースはレンダラーでレンダリングされて１つの文字列になり最終的にechoで出力されますが、それではサイズがPHPのメモリの限界を超えるようなコンテンツは出力できません。`StreamRenderer`を使うとHTTP出力をストリームでき、メモリ消費を低く抑えられます。ストリーム出力は既存のレンダラーと共存して使うこともできます。

## トランスファーとレンダラーの変更

ストリーム出力用のレンダラーとレスポンダーをインジェクトするために、ページに[StreamTransferInject](https://github.com/bearsunday/BEAR.Streamer/blob/1.x/src/StreamTransferInject.php)トレイトを`use`します。このダウンロードページの例では`$body`をストリームのリソース変数にしているので、インジェクトされたレンダラーは無視されリソースがストリーム出力されます。

```php?start_inline
use BEAR\Streamer\StreamTransferInject;

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

## レンダラーとの共存

ストリーム出力は従来のレンダラーと共存可能です。通常、TwigレンダラーやJSONレンダラーは文字列を生成しますが、その一部にストリームをアサインすると全体がストリームとして出力されます。

これはTwigテンプレートに文字列とresource変数をアサインして、インライン画像のページを生成する例です。

テンプレート

```twig
<!DOCTYPE html>
<html lang="en">
<body>
<p>Hello, {% raw  %}{{ name }}{% endraw %}</p>
<img src="data:image/jpg;base64,{% raw  %}{{ image }}{% endraw %}">
</body>
</html>
```

`name`には通常通り文字列をアサインしていますが、`image`に画像ファイルのファイルポインタリソースのresource変数を`base64-encode`フィルターを通してアサインしています。

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

ストリーミングのバンドワイズやタイミングをコントロールしたり、クラウドにアップロードしたり等ストリーミングを更にコントロールする場合には[StreamResponder](https://github.com/bearsunday/BEAR.Streamer/blob/1.x/src/StreamResponder.php#L45-L48)を参考にして作成して束縛します。

ストリーム出力のdemoが[MyVendor.Stream](https://github.com/bearsunday/MyVendor.Stream)にあります。
