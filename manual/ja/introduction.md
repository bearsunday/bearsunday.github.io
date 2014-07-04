---
layout: default_ja
title: BEAR.Sunday | イントロダクション
category: Manual
---

# イントロダクション

## BEAR.Sundayとは

BEAR.SundayはアプリケーションをRESTアーキテクチャで構築するリソース指向フレームワークです。「依存性の注入」と「アスペクト指向プログラミング」を用いた疎結合なシステムは意図が読みやすく簡潔なコーディングを可能にします。

BEAR.Sundayは独自のコンポーネントをほとんど持ちません。再発明を避け定評ある既存ライブラリを利用します。 コンポーネントそのものよりそれらの接続に注目し、開発者が構成可能でSOLIDなフレームを提供します。

## リソース指向フレームワーク

BEAR.Sundayではコントローラーもモデルも統一したリソースとして扱います。名前（URI）と統一インターフェイスを持った各リソースはアプリケーション内部/外部に関わらずAPIとして機能し、モバイルアプリケーションへの早期対応や高いインターオペラビリティ、長期運用を可能にします。

### すべてはリソース

<img src="/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>

上の図はリソースオブジェクトがどうのように機能するかを示したものです。

TBD.

# パッケージ

BEAR.Sundayアプリケーションフレームワークは３種類のパッケージ、[Packagist](https://packagist.org/) から入手できる **独立したパッケージ** から構成されています。
**Rayパッケージ** は、DIとAOPのオブジェクトフレームワークです。**BEAR.* パッケージ** はそれら２つのパッケージをアプリケーションフレームワークとして構成します。

## BEAR.* パッケージ

### BEAR.Resource ⊂ BEAR.Sunday ⊂ BEAR.Package

[BEAR.Resource](https://github.com/koriym/BEAR.Resource) はリソースをオブジェクトとして振る舞うようにする **ハイパーメディアフレームワーク** です。

オブジェクトに、クライアントサーバー、統一インターフェイス、ステートレス、相互結合とレイヤー化コンポーネントと言ったRESTfulなWebサービスの恩恵をもたらします。

[BEAR.Sunday](https://github.com/koriym/BEAR.Sunday) はBEAR.Resourceをアプリケーションフレームワークにするための、アノテーション、例外、インターフェイスなどの抽象化の集まりです。
実際の実装はほとんど持ちません。

[BEAR.Package](https://github.com/koriym/BEAR.Package) は、BEAR.Sundayの抽象化に、AuraライブラリやSymfonyコンポーネントと言った実際の実装への束縛を提供します。
アプリケーションスクリプトと開発ツールを持ったWebアプリケーションフレームワークを構成します。

## Ray DI/AOP パッケージ

BEAR.SundayではDI, Dependency Injection（依存性の注入）パターンとAOP, Aspect Oriented Programing（アスペクト指向プログラミング）をコードの全域に渡って利用しています。

アノテーションを使用したオブジェクトへの依存性の注入をサポートしている [Ray.Di](https://github.com/koriym/Ray.Di)、[Ray.Aop](https://github.com/koriym/Ray.Aop) という [Google Guice](http://en.wikipedia.org/wiki/Google_Guice) のPHPクローンのDI/AOPフレームワークを利用します。
RayというGoogle GuiceのPHPクローンのDI/AOPフレームワークを利用していて、アノテーションを使用したオブジェクトへの依存性の注入 をサポートしているのが特徴です。 

BEAR.SundayのAOPは [AOP Alliance](http://aopalliance.sourceforge.net/) が策定したインターフェイスをPHPで実装しています。アノテーションや名前で指定した特定のメソッドに複数の横断的処理を束縛する事ができます。

動的言語のDI/AOP導入はしばしばパフォーマンス上の懸念がもたれます。
しかし、BEAR.Sundayは低結合で多くの抽象化機能を持ちながら、キャッシュを使いオブジェクトグラフの生成を再利用することでDI/AOP導入によるパフォーマンス低下がほとんどありません。（@TODO 英訳が難しかったので、古い日本語を利用した。また、この説明は現状と合っていない？）

## フレームワークの構成

フレームワークパッケージ（BEAR.Package）のディレクトリ構成はこのようになります。

```
{$PACKAGE_DIR}
├── apps                     # applications
│   ├── Demo.Helloworld
│   └── Demo.Sandbox
├── bin                      # command-line script invokers
│   ├── bear.compile
│   ├── bear.server
│   ├── bear.create-resource
│   └── env.php
├── src                      # BEAR.Package source code organized for PSR-4
├── tests                    # test files for phpunit
├── var
│   ├── lib                  # system script
│   ├── log                  # system log
│   └── www
│       ├── admin            # public web folder for system
│       └── dev              # public web folder for application development (/dev)
└── vendor
```

## アプリケーションの構成

アプリケーションのディレクトリ構成はこのようになります。

```
├── bin                  # command-line script invokers
│   ├── clear.php
├── bootstrap
│   ├── autoload.php     # autoloader
│   ├── contexts         # contextual application scripts
│   │   ├── api.php
│   │   ├── dev.php
│   │   └── prod.php
│   └── instance.php     # application script for production
├── src
│   └── {Vendor.Application}
│       ├── Annotation   # application annotation
│       ├── App.php      # application class
│       ├── Interceptor  # AOP interceptors
│       ├── Module       # DI binding modules
│       ├── Params       # signal parameter providers
│       └── Resource     # Resources
├── var                  # application variable directories
│   ├── conf             # application configuration files
│   ├── db               # application data base files
│   ├── lib              # vendor (packagist) related files
│   ├── log              # application log directory
│   ├── tmp              # application tmp files
│   └── www              # public web folder
└── vendor
```
