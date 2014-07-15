---
layout: default_ja
title: BEAR.Sunday | はじめてのプル
category: My First - Tutorial
---

# はじめてのプル

## テンプレートからリソースを呼び出してみましょう

ビュー（PHPスクリプト）から直接リソースを利用してみましょう。
以前つくった挨拶リソースをHTMLから直接利用するにはこうなります。

{% highlight php startinline %}
<?php
$app = require '{$APP_PATH}/bootstrap/instance.php';
$message = $app->resource
    ->get
    ->uri('app://self/first/greeting')
    ->withQuery(['name' > 'BEAR'])
    ->eager->request()
    ->body;
?>
<html>
    <body><?php echo $message; ?></body>
</html>
{% endhighlight %}

この *プレーンなPHPスクリプト* はWebドキュメント領域に直接公開して、Webクライアントから直接リクエストを受けます。

アプリケーションオブジェクトを（$app）をスクリプトから取得しそのリソースクライアントをつかってアプリケーションリソースを取り出しています。

Note: `$app` はアプリケーションのコントロールに必要なリソースクライントやディペンデンシーインジェクター、HTTP出力などアプリケーションの重要な機能をもっています。

この短いスクリプトはBEAR.Sundayでは全ての情報はリソースで、簡単に利用可能という事を表しています。

## これまでと違うところ

これまでリソースをコマンドラインかWebから利用していました。
リソースをAPIとしてコマンドラインでの確認やWeb APIとして利用しました。
またページはWebからアクセスしてWebページとして表示されました。

どちらにしてもフロントスクリプトがあり、それからリソースがリクエストされ出力されてました。

一方、このスクリプトではリソースが直接利用されています。

## これはいいのでしょうか

MVCでいえばこれはビューからモデルが取得されてる事と同じです。
順序が逆になってないでしょうか？これで良いのでしょうか？デザインとロジックが混ざってる、良くないコードとも言えないのでしょうか？

## プル型アーキテクチャ

この表示レイヤから処理を開始し、必要に応じて結果を「プル」するアーキテクチャを [プル型アーキテクチャ](http://ja.wikipedia.org/wiki/Web%E3%82%A2%E3%83%97%E3%83%AA%E3%82%B1%E3%83%BC%E3%82%B7%E3%83%A7%E3%83%B3%E3%83%95%E3%83%AC%E3%83%BC%E3%83%A0%E3%83%AF%E3%83%BC%E3%82%AF#.E3.83.97.E3.83.83.E3.82.B7.E3.83.A5.E5.9E.8B_vs._.E3.83.97.E3.83.AB.E5.9E.8B) といいます。

多くのWeb MVCフレームワークはこの逆の「プッシュ型」です。
処理を要求するアクションを実行し、次に結果を出力するためにデータを表示のレイヤに「プッシュ」します。
「プル型」はその逆です。

Note: [Comparison of web application frameworks](http://en.wikipedia.org/wiki/Comparison_of_web_application_frameworks#PHP_2) では様々なフレームワークでのPUSH/PULL分類がわかります。

Again BEAR.Sunday's 'resource pull' pulls resources using PHP code, however logic like domain logic or controller logic is not mixed up in the view.
In reality the resource is only being bound to the resource placeholder (`<?php echo $message;?>`).
またこのBEAR.Sundayの「リソースプル」はPHPのコードでリソースをプルしていますが、ドメインロジック、コントロールロジックなどのロジックがこのビューに紛れ込んでるわけではありません。
あくまでリソースのプレースホルダー（`<?php echo $message; ?>`）とそのリソースを束縛してるだけす。

## APIとしてリソースを使う

このPHPスクリプトはBEAR.Sundayフレームワークを利用していますが、利用はされていません。主がこのスクリプトで、直接リソースを呼び出して利用しています。

それにはリソースクライアントを取得しリクエストするだけです。
準備なしにこれだけでリソースを使う事ができます。
HTTPを介する事なくAPIとして、他のCMSやフレームワークのPHPプログラムからリソースを利用できます。

Note: HTTP越しにしたり、[Thrift](http://thrift.apache.org/) 等の多言語フレームワークを使う事で更にポータビリティは高まります。ドキュメントDB、クライアントサイドMVC、モバイルデバイス、SNSアプリ等... 今日の多様化するアプリケーションに対してAPIをコアにアプリケーションを構築する事は大きな意味があります。
