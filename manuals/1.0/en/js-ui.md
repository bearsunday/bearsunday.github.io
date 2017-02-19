---
layout: docs-en
title: Javascript UI
category: Manual
permalink: /manuals/1.0/en/js-ui.html
---

# Javascript UI

Instead of rendering views with PHP termplate engines such Twig etc, we will be doing so using server-side JavaScript. On the PHP side we will be carrying out the authorisation, authentication, initialization and API delivery then we will do the rendering of the UI using JS.

Currently within our project architecture, we will only be making changes to annotated resources so should be simple.

## Prerequisites

 * PHP 7.1
 * [Node.js](https://nodejs.org/)
 * [yarn](https://yarnpkg.com/)
 * [V8Js](http://php.net/manual/en/book.v8js.php) (Development option)

Note: If you do not install V8Js then JS will be run using Node.js.

## Terminology

 * **CSR** Client Side Rendering (via Web Browser)
 * **SSR** Server Side Rendering (via V8 or Node.js)

# JavaScript

## Installation

Install `koriym/ssr-module` into the project.

```bash
// composer create-project bear/skeleton // When a new project
// cd MyVendor.MyApp
composer require bear/ssr-module 1.x-dev
```

Install the UI skeleton app `koriym/js-ui-skeleton`.

```bash
composer require koriym/js-ui-skeleton 1.x-dev
cp -r vendor/koriym/js-ui-skeleton/ui .
cp -r vendor/koriym/js-ui-skeleton/package.json .
yarn install
```

## Running the UI application

Lets start by running the demo application.
From the displayed web page lets select the rendering engine and run the JS application.

```
yarn run ui
```
This applications inputs can be set using the `ui/dev/config/` config files.

```php?
<?php
$app = 'index';                   // =index.bundle.js
$state = [                        // Application state
    'hello' =>['name' => 'World']
];
$metas = [                        // value used in SSR only
    'title' =>'page-title'
];

return [$app, $state, $metas];
```
Lets copy the configuration file and try changing the input values.

```
cp ui/dev/config/index.php ui/dev/config/myapp.php
```

Reload the browser and try out the new settings.

In this way without changing the JavaScript or core PHP application we can alter the UI data and check that it is working.

The PHP configuration files that have been edited in this section are only used when executing `yarn run ui`.
All the PHP side needs is the output bundled JS file.


## Creating the UI application.

Using the variables that have been passed in from PHP, create a **render** function that returns a rendered string.


```
const render = (state, metas) => (
  __AWESOME_UI__ // Using a SSR compatible library or JS template engine return an output string.
)
```

The `state` value is needed in the document root, `metas` contains other variables, such as those needed in <head>. The `render` function name cannot be changed.

Here we can grab the name and create a greeting string to be returned.

```
const render = state => (
  `Hello ${state.name}`
)
```

Save this as `ui/src/page/index/hello/server.js` and register this as a Webpack entry point in`ui/entry.js`.

```javascript?start_inline
module.exports = {
  hello: 'src/page/hello/server',
};
```

Having done this a `hello.bundle.js` bundled file is created for us.

Create a file at `ui/dev/config/myapp.php` to test run this application.

```php?
<?php
$app = 'hello';
$state = [
    ['name' => 'World']
];
$metas = [];

return [$app, $state, $metas];
```

Thats it! Reload the browser to try it out.

Inside the render function you can use any UI framework such as React or Vue.js to create a rich UI.
In a regular application in order to limit the number of dependencies in the `server.js` entry file import the render module as below.

```javascript
import render from './render';
global.render = render;
```

Thus far there has been nothing happening on the PHP side. Development on the SSR application and PHP development can done independently.

# PHP

## Module Installation

Install `SsrModule` in AppModule.

```php
<?php
use BEAR\SsrModule\SsrModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        // ...
        $build = dirname(__DIR__, 2) . '/var/www/build';
        $this->install(new SsrModule($build));
    }
}
```

The `$build` directory is where the JS files live.(The Webpack output location set in `ui/ui.config.js`)


## @Ssr Annotation

Annotate the resource function to be SSR'd with `@Ssr`. The JS application name is required in `app`.

```php?start_inline
<?php

namespace MyVendor\MyRedux\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\SsrModule\Annotation\Ssr;

class Index extends ResourceObject
{
    /**
     * @Ssr(app="index_ssr")
     */
    public function onGet($name = 'BEAR.Sunday')
    {
        $this->body = [
            'hello' => ['name' => $name]
        ];

        return $this;
    }
}
```

When you want to pass in distinct values for SSR and CSR set a key in `state` and `metas`.

```php?start_inline
/**
 * @Ssr(
 *   app="index_ssr",
 *   state={"name", "age"},
 *   metas={"title"}
 * )
 */
public function onGet()
{
    $this->body = [
        'name' => 'World',
        'age' => 4.6E8;
        'title' => 'Age of the World'
    ];

    return $this;
}
```

To see exactly how you pass in `state` and `metas` to achieve SSR see the sample application `ui/src/page/index/server`. The only influence is from the annotated method, the rest comes straight from the API or HTML rendering configuration.


# Runtime PHP Application Settings

Edit `ui/ui.config.js`, set the Webpack build location in `build` and web directory in `public`. The `build` directory is the same that you set in the SsrModule installation.

```javascript
const path = require('path');

module.exports = {
  public: path.join(__dirname, '../var/www'),
  build: path.join(__dirname, '../var/www/build')
};
```

## Running the PHP application

```
yarn run dev
```

Run using live updating.
When the PHP file is changed it will be automatically reloaded, if there is a change in a React component without hitting refresh the component will update. If you want to run the app without live updating you can by running `yarn run start`.

For other commands such `lint` or `test` etc. please see [commands](https://github.com/koriym/Koriym.JsUiSkeleton/blob/1.x/README.md#command).

## Performance

The ability to save the V8 Snapshot into APC means we can see dramatic performance benefits. In `ProdModule` install `ApcSsrModule`.
ReactJs or your application snapshot is saved in `APCu` and can be reused. V8 is required.

```php?start_inline
$this->install(new ApcSsrModule);
```
To use caches other than APC look at  the code in `ApcSsrModule` as a reference to make your own module. It is possible to use a cache compatible with PSR16.

In order to tune performance at compile time pulling in your JS code (and ReactJs etc) into the V8 snapshot can give you further performance improvements.
For more info please see the following.

 * [20x performance boost with V8Js snapshots](http://stesie.github.io/2016/02/snapshot-performance)
 * [v8js - Possibility to Improve Performance with Precompiled Templates/Classes ?](https://github.com/phpv8/v8js/issues/205)

## Debugging

 * Chrome Plugin [React developer tools](https://chrome.google.com/webstore/detail/react-developer-tools/fmkadmapgofadopljbjfkapdkoienihi) or [Redux devTools]( https://chrome.google.com/webstore/detail/redux-devtools/lmhkpmbekcpmknklioeibfkpmmfibljd) can be used.
 * When a 500 error is returned look at the response details by using `var/log` or `curl` etc.


## References

 * [ECMAScript 6](http://postd.cc/es6-cheatsheet/)
 * [Airbnb JavaScript Styleguide](https://github.com/airbnb/javascript)
 * [React](https://facebook.github.io/react/)
 * [Redux](http://redux.js.org/)
 * [Redux github](https://github.com/reactjs/redux)
 * [Redux devtools](https://github.com/gaearon/redux-devtools)
 * [Karma test runner](http://karma-runner.github.io/1.0/index.html)
 * [Mocha test framework](https://mochajs.org/)
 * [Chai assertion library](http://chaijs.com/)
 * [Yarn package manager](https://yarnpkg.com/)
 * [Webpack module bundler](https://webpack.js.org/)

## Other view libraries

  * [Vue.js](https://vuejs.org/)
  * [Handlesbar.js](http://handlebarsjs.com/)
  * [doT.js](http://olado.github.io/doT/index.html)
  * [pug](https://pugjs.org/api/getting-started.html)
  * [Hogan](http://twitter.github.io/hogan.js/) (Twitter)
  * [Nunjucks](https://mozilla.github.io/nunjucks/)(Mozilla)
  * [dust.js](http://www.dustjs.com/) (LinkedIn)
  * [marko](http://markojs.com/) (Ebay)
