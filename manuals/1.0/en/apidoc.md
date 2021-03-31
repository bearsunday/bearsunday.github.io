---
layout: docs-en
title: API Doc
category: Manual
permalink: /manuals/1.0/en/apidoc.html
---
# API Doc
(WIP)

ApiDoc generates API documentation from your application.

ApiDoc generates API documentation from your application. The documentation is automatically generated from the application code and JSON schema, so there is no difference between the API documentation and the actual application.

It not only saves you the trouble of writing IDL, but also helps you maintain accurate documentation.

## How to use

Install BEAR.ApiDoc.

    composer require bear/api-doc 1.x-dev --dev

Create `bin/doc.php` script.


```php
<php

require dirname(__DIR__) . '/vendor/autoload.php';

use BEAR\ApiDoc\DocApp;

$docApp = new DocApp('MyVendor\MyProject');
$docApp->dumpHtml('/path/to/docs', 'app');
```

Generate a `DocApp ` with the namespace of your application, and use `dumpHtml()` to select the destination of the document and the page schema app or page to output the document.

You can also register it with the composer script command.

## Profiles

BEAR.ApiDoc supports the [ALPS](http://alps.io/) format of [RFC 6906 Profile](https://tools.ietf.org/html/rfc6906) which gives additional information to applications.

Words used in API request and response keys are called semantic descriptors (semantic descriptors). If a dictionary of words (semantic descriptors) is created in a profile, there is no need to describe the words for each request. The centralized definition of words and phrases also prevents notational shakiness and aids in shared understanding.

## ALPS profile

The following is an example of defining semantic descriptors named `firstName`,`familyName`.

profile.json

```json
{
  "$schema": "https://alps-io.github.io/schemas/alps.json",
  "alps": {
    "descriptor": [
      {"id": "firstName", "title": "The person's first name."},
      {"id": "familyName", "def": "https://schema.org/familyName"},
    ]
  }
}
```

The `firstName` is described in text by the `title`. The `familyName` is defined by linking words defined in [schema.org](https://schema.org) with `def`. Once defined, they will be reflected in the API documentation without having to be re-described in JSON schema or PHPDOC.

To output using a profile, specify the profile as the third argument of dumpHtml().

```
$docApp->dumpHtml('/path/to/docs', 'app', 'path/to/profile.json');
```

## Source

BEAR.ApiDoc takes information from phpdoc, method signatures and JSON schema to generate documentation.

#### PHPDOC

The following parts are retrieved in phpdoc. For information that applies across resources, such as authentication, a separate documentation page is prepared and linked with `@link`.


```php
/**
 * {title}
 *
 * {description}
 *
 * {@link htttp;//example.com/docs/auth 認証}
 */
 class Foo extends ResourceObject
 {
 }
```

```php
/**
 * {title}
 *
 * {description}
 *
 * @param string $id User ID
 */
 public function onGet(string $id ='kuma'): static
 {
 }
```

* If there is no `@param` description for a method, information is retrieved from the method signature.
* The order of priority for information retrieval is phpdoc, JSON schema, and profile.

## Link

* [ALPS](http://alps.io/)
* [ALPS-ASD](https://github.com/koriym/app-state-diagram)
* [メディアタイプとALPSプロファイル](https://qiita.com/koriym/items/2e928efb2167d559052e)