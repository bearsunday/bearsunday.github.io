---
layout: docs-en
title: API Doc
category: Manual
permalink: /manuals/1.0/en/apidoc.html
---
# API Doc

ApiDoc generates API documentation from your application.

The auto-generated documentation from your code and JSON schema will reduce your effort and keep your API documentation accurate.

## Usage

### Requirements

* PHP 8.2+

### Installation

    composer require bear/api-doc --dev

### Copy Configuration

    cp ./vendor/bear/api-doc/apidoc.xml.dist ./apidoc.xml

### Run

```bash
composer docs        # Generate docs with external CSS
composer docs-dev    # Generate docs with inline CSS for development
composer docs-md     # Generate Markdown docs
composer docs-openapi # Generate OpenAPI spec
```

## Source

ApiDoc generates documentation by retrieving information from PHP Attributes, method signatures, and JSON schema.

#### PHP Attributes

Reflecting the method signature and attributes (e.g. `#[Title]`, `#[Description]`, `#[JsonSchema]`) generates the documentation.

```php
use BEAR\ApiDoc\Annotation\Title;
use BEAR\ApiDoc\Annotation\Description;
use BEAR\Resource\Annotation\Link;

#[Title("User")]
#[Description("User resource")]
#[Link(rel: "friend", href: "/friend?id={id}")]
class User extends ResourceObject
{
    #[Title("Get User")]
    public function onGet(string $id): static
    {
    }
}
```

* If there is no attribute, information is retrieved from PHPDoc or method signature.
* The order of priority for information acquisition is Attribute, PHPDoc, JSON schema, and profile.

## Configuration

The configuration is written in XML.
The minimum specification is as follows.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<apidoc
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://bearsunday.github.io/BEAR.ApiDoc/apidoc.xsd">
    <appName>MyVendor\MyProject</appName>
    <scheme>app</scheme>
    <docDir>docs</docDir>
    <format>html</format>
</apidoc>
```

### Required Attributes

#### appName

Application namespaces

#### scheme

The name of the schema to use for API documentation. `page` or `app`.

#### docDir

Output directory name.

#### format

The output format: `html`, `md` (Markdown), or `openapi` (OpenAPI 3.1).

### Optional attributes

#### title

API title

```xml
<title>MyBlog API</title>
```

#### description

API description

```xml
<description>MyBlog API description</description>
```

#### links

Links. The `href` is the URL of the link, and the `rel` is its content.

```xml
<links>
    <link href="https://www.example.com/issue" rel="issue" />
    <link href="https://www.example.com/help" rel="help" />
</links>
```

#### alps

Specifies an "ALPS profile" that defines the terms used by the API.

```xml
<alps>alps/profile.json</alps>.
```

## Profile

ApiDoc supports the [ALPS](http://alps.io/) format of the [RFC 6906 Profile](https://tools.ietf.org/html/rfc6906) which gives additional information to the application.

Words used in API request and response keys are called semantic descriptors, and if you create a dictionary of profiles, you don't need to describe the words for each request.
Centralized definitions of words and phrases prevent notational errors and aid in shared understanding.

The words used in API request and response keys are called semantic descriptors, and creating a dictionary of profiles eliminates the need to explain the words for each request.
Centralized definitions of words and phrases prevent shaky notation and aid in shared understanding.

The following is an example of defining descriptors `firstName` and `familyName` with `title` and `def` respectively.
While `title` describes a word and clarifies its meaning, `def` links standard words defined in vocabulary sites such as [Schema.org](https://schema.org/).

ALPS profiles can be written in XML or JSON.

profile.xml
```xml
<?xml version="1.0" encoding="UTF-8"?>
<alps
     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     xsi:noNamespaceSchemaLocation="https://alps-io.github.io/schemas/alps.xsd">
    <!-- Ontology -->
    <descriptor id="firstName" title="The person's first name."/>
    <descriptor id="familyName" def="https://schema.org/familyName"/>
</alps>
```

profile.json

```json
{
  "$schema": "https://alps-io.github.io/schemas/alps.json",
  "alps": {
    "descriptor": [
      {"id": "firstName", "title": "The person's first name."}
      {"id": "familyName", "def": "https://schema.org/familyName"},
    ]
  }
}
```

Descriptions of words appearing in ApiDoc take precedence over phpdoc > JsonSchema > ALPS in that order.

## Reference

* [Demo](https://bearsunday.github.io/BEAR.ApiDoc/)
* [ALPS](http://alps.io/)
* [ALPS-ASD](https://github.com/koriym/app-state-diagram)
