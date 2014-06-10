---
layout: default_ja
title: BEAR.Sunday | Application Introduction 
category: Application
---

# Application Introduction 

bootstrapで生成されるアプリケーションオブジェクトはアプリケーションの実行が記述してあるアプリケーションスクリプトに必要な全てのサービスオブジェクトを含んでいます。利用する全てのオブジェクトはこのオブジェクトに含まれるか、含まれたファクトリーで生成されます。
オブジェクトを生成するためにはまずその依存が必要です。しかしその依存もまた依存が必要で、そのまた..と続き最終的にアプリケーションオブジェクトがオブジェクトグラフ（オブジェクト間の関係性）として取得されます。

## Compile and Runtime　

モジュールはオブジェクトの抽象（インターフェイスや抽象クラス）と実装（実クラスやファクトリー）の束縛、それにメソッドとその横断的振る舞い（アスペクト）の束縛の集合です。
コンテキストによる束縛でアプリケーションオブジェクトが生成されます。

BEAR.Sundayは「依存性の注入パターン」に基づいていて、アプリケーションオブジェクトはそのコンパイルタイムとランタイムにはっきりとした区別があります。

 * 生成：boot時にコンテキストによってアプリケーションオブジェクトが生成される**コンパイルタイム**
 * 実行：リクエスト毎の**ランタイム

## オブジェクトグラフの再利用

アプリケーションオブジェクトの組み立ては一度しか行われません。オブジェクトは実行モードに応じたモジュールに基づいて依存が注入されアスペクトが特定メソッドに織り込ます。
BEAR.Sundayアプリケーションではコンストラクタはサービスを開始してオブジェクトの最初の実行時のコンパイルの一度だけしか実行されません。二度目からはオブジェクト生成は行われずコンテナから取り出し再利用されます。

## 早期束縛

リクエスト毎に変わらない依存の注入はこコンパイルで行います。
configはオブジェクトがどのように構成されるかを決めるものであって、オブジェクトのランタイムでの実行を直接決めるものではありません。

例えばランタイムでconfigを見て振る舞いを変更する事は推奨されません。

{% highlight php startinline %}
<?php
// Not recommended
if ($config['debug'] === true) {
    //For use in debugging
}
{% endhighlight %}
その代わりに コンパイル時にconfigを見てコンテキストによって違うオブジェクトを作ります。

*Note: Fore example in the development screen there are tools that you can check the information about many objects, because the development renderer is bound to the rendering interface, the renderer does not check the application mode to change the rendering. The application mode is not a variable that changes runtime behavior when the application is executed, instead according to that mode an object is created.
Each of the objects that build up the `Sandbox` application are not aware of the current execution mode.*

## 遅延束縛

依存にはリクエストを通じて変わらないものと、ランタイムの時にしか決定できないものがあります。例えばDBオブジェクトはリクエストメソッドに応じてmaster/slaveが変更されたインスタンスが必要です。
インターセプターはメソッドが実行される直前に、その実行を見て依存を注入する事ができます。例えばGETリクエストにはslave、それ以外にはmaster DBオブジェクトを注入します。