---
layout: default_ja
title: BEAR.Sunday | はじめてのWeb API
category: My First - Tutorial
--- 

# はじめてのWeb API

[はじめてのリソース](my_first_resource.html) でつくったリソースをWeb APIとして利用してみましょう。

API用のビルトインWebサーバーを起動します。

```
$ bin/bear.server --port=8081 --context=api apps/Demo.Sandbox
```

ブラウザのアドオンのRESTクライアント（[FireFox用](https://addons.mozilla.org/ja/firefox/addon/restclient/#id=9780) や [Chrome用](https://chrome.google.com/webstore/detail/advanced-rest-client/hgmloofddffdnphfgcellkdfbfbjeloo)）等でアクセスします。

```
GET http://localhost:8081/app/first/greeting?name=BEAR
```

JSONで挨拶がかえってきたでしょうか？

```json
{
    "value": "Hello, BEAR1",
    "_links": {
        "self": {
            "href": "app://self/first/greeting?name=BEAR1"
        }
    }
}
```

これであなたの作成したリソースはWeb APIとして利用できるようになりました。
Apache等のサーバーで運用すれば世界中の人からこのリソースをWeb APIとして利用できます！
