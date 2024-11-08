---
layout: docs-ja
title: ストリーム出力
category: Manual
permalink: /manuals/1.0/ja/stream.html
---

# ストリーム出力

通常、リソースはレンダラーでレンダリングされて1つの文字列になり、最終的に`echo`で出力されます。しかし、この方法ではPHPのメモリ制限を超えるサイズのコンテンツは出力できません。`StreamRenderer`を使用することでHTTP出力をストリーム化でき、メモリ消費を低く抑えることができます。このストリーム出力は、既存のレンダラーと共存することも可能です。

## トランスファーとレンダラーの変更

ストリーム出力用のレンダラーとレスポンダーをインジェクトするために、ページに[StreamTransferInject](https://github.com/bearsunday/BEAR.Streamer/blob/1.x/src/StreamTransferInject.php)トレイトを`use`します。

以下のダウンロードページの例では、`$body`をストリームのリソース変数としているため、インジェクトされたレンダラーは無視され、リソースが直接ストリーム出力されます：

```php
use BEAR\Streamer\StreamTransferInject;

class Download extends ResourceObject
{
    use StreamTransferInject;

    public $headers = [
        'Content-Type' => 'image/jpeg',
        'Content-Disposition' => 'attachment; filename="image.jpg"'
    ];

    public function onGet(): static
    {
        $fp = fopen(__DIR__ . '/BEAR.jpg', 'r');
        $this->body = $fp;

        return $this;
    }
}
```

## レンダラーとの共存

ストリーム出力は従来のレンダラーと共存できます。通常、TwigレンダラーやJSONレンダラーは文字列を生成しますが、その一部にストリームをアサインすると、全体がストリームとして出力されます。

以下は、Twigテンプレートに文字列とresource変数をアサインして、インライン画像のページを生成する例です。

テンプレート：

```twig
<!DOCTYPE html>
<html lang="en">
<body>
<p>Hello, {% raw  %}{{ name }}{% endraw %}</p>
<img src="data:image/jpg;base64,{% raw  %}{{ image }}{% endraw %}">
</body>
</html>
```

`name`には通常通り文字列をアサインし、`image`には画像ファイルのファイルポインタリソースを`base64-encode`フィルターを通してアサインします：

```php
class Image extends ResourceObject
{
    use StreamTransferInject;

    public function onGet(string $name = 'inline image'): static
    {
        $fp = fopen(__DIR__ . '/image.jpg', 'r');
        stream_filter_append($fp, 'convert.base64-encode'); // 画像をbase64形式に変換
        $this->body = [
            'name' => $name,
            'image' => $fp
        ];

        return $this;
    }
}
```

ストリーミングの帯域幅やタイミングをコントロールしたり、クラウドにアップロードしたりするなど、ストリーミングをさらに制御する場合は、[StreamResponder](https://github.com/bearsunday/BEAR.Streamer/blob/1.x/src/StreamResponder.php#L45-L48)を参考にして作成し、束縛します。

ストリーム出力のデモは[MyVendor.Stream](https://github.com/bearsunday/MyVendor.Stream)で確認できます。
