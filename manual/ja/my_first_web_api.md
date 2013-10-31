---
layout: default
title: BEAR.Sunday | My First Web API
category: My First - Tutorial
--- 
# My First Web API 

Lets use the resource we made in [my_first_resource My First Resource] as a Web API.

Start the built in web server for the API.

```
$ php -S 0.0.0.0:8081 {$PROJECT_PATH}/apps/Sandbox/bootstrap/contexts/api.php
```

We can then access it through a REST client ([for FireFox](https://addons.mozilla.org/ja/firefox/addon/restclient/#id=9780), or [for Chrome](https://chrome.google.com/webstore/detail/advanced-rest-client/hgmloofddffdnphfgcellkdfbfbjeloo))

```
GET http://localhost:8081/first/greeting?name=BEAR
```
Did the greeting come back as JSON data like this ?

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

In this way the resource you created can be used as a Web API.
If you run a web server like apache you can have people from across the world use your resource as a web api.