---
layout: docs-en
title: Validation
category: Manual
permalink: /manuals/1.0/en/validation.html
---

# Validation

* BEAR.Sunday validates with JSON Schema.
* For validation with web forms, see [Forms](form.html).

## Validation with JSON Schema

### Overview

You can define and validate the input and output specifications of resource APIs with [JSON Schema](http://json-schema.org/).
This lets you manage the API specification in a form that both humans and machines understand, and also output API documentation as ApiDoc.

### Setup

#### Module configuration

Configure one of the following depending on where validation should apply:

- Validate in all environments: configure in `AppModule`
- Validate only in the development environment: configure in `DevModule`

```php
use BEAR\Resource\Module\JsonSchemaModule;
use BEAR\Package\AbstractAppModule;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        $this->install(
            new JsonSchemaModule(
                $appDir . '/var/json_schema',  // for schema definitions
                $appDir . '/var/json_validate' // for validation
            )
        );
    }
}
```

#### 2. Create the required directories

```bash
mkdir -p var/json_schema
mkdir -p var/json_validate
```

### Basic usage

#### 1. Define a resource class

```php
use BEAR\Resource\Annotation\JsonSchema;

class User extends ResourceObject
{
    #[JsonSchema('user.json')]
    public function onGet(): static
    {
        $this->body = [
            'firstName' => 'mucha',
            'lastName' => 'alfons',
            'age' => 12
        ];
        return $this;
    }
}
```

#### 2. Define the JSON Schema

`var/json_schema/user.json`:

```json
{
    "type": "object",
    "properties": {
        "firstName": {
            "type": "string",
            "maxLength": 30,
            "pattern": "[a-z\\d~+-]+"
        },
        "lastName": {
            "type": "string",
            "maxLength": 30,
            "pattern": "[a-z\\d~+-]+"
        }
    },
    "required": ["firstName", "lastName"]
}
```

### Advanced usage

#### Specifying an index key

When the response body has an index key, specify it with the `key` parameter:

```php
class User extends ResourceObject
{
    #[JsonSchema(key: 'user', schema: 'user.json')]
    public function onGet(): static
    {
        $this->body = [
            'user' => [
                'firstName' => 'mucha',
                'lastName' => 'alfons',
                'age' => 12
            ]
        ];

        return $this;
    }
}
```

#### Validating arguments

To validate method arguments, specify a schema with the `params` parameter:

```php
class Todo extends ResourceObject
{
    #[JsonSchema(
        key: 'user',
        schema: 'user.json',
        params: 'todo.post.json'
    )]
    public function onPost(string $title)
    {
        // method implementation
    }
}
```

`var/json_validate/todo.post.json`:
```json
{
    "$schema": "http://json-schema.org/draft-04/schema#",
    "title": "/todo POST request validation",
    "type": "object",
    "properties": {
        "title": {
            "type": "string",
            "minLength": 1,
            "maxLength": 40
        }
    },
    "required": ["title"]
}
```

### target

To apply schema validation to the representation of the resource object (the rendered result) rather than to the body of the ResourceObject, specify the `target='view'` option.
In HAL format, this allows you to describe a schema for `_link`.

```php
#[JsonSchema(schema: 'user.json', target: 'view')]
```

### Schema authoring tools

The following tools are useful for creating JSON Schemas:

- [JSON Schema Generator](https://jsonschema.net/#/editor)
- [Understanding JSON Schema](https://spacetelescope.github.io/understanding-json-schema/)

## Exception Handling

When JSON Schema validation fails, BEAR.Resource (1.33.0+) throws a typed exception that tells you whether the problem was in the **request** (client error) or the **response** (server bug):

| Exception | HTTP | Meaning |
|-----------|------|---------|
| `JsonSchemaRequestException` | 400 | Input params failed schema validation |
| `JsonSchemaResponseException` | 500 | Response body failed schema validation |

Both extend `JsonSchemaException`, so you can catch either specifically or together.

### Accessing Structured Errors

`$e->getErrors()` returns a `JsonSchemaErrors` collection:

```php
use BEAR\Resource\Exception\JsonSchemaRequestException;
use BEAR\Resource\Exception\JsonSchemaResponseException;

try {
    $response = $resource->post('app://self/user', $params);
} catch (JsonSchemaRequestException $e) {
    $errors = $e->getErrors();        // JsonSchemaErrors
    $errors->count();                 // number of violations
    $errors->first();                 // first JsonSchemaError or null

    // Iterate over each error
    foreach ($errors as $error) {
        echo $error->property;        // e.g. "email"
        echo $error->message;         // human-readable message
    }

    // Group by field — useful for building API error responses
    $byField = $errors->byProperty(); // array<string, list<JsonSchemaError>>

    // Bulk format with a template
    $text = $errors->format('{property}: {message}\n');
} catch (JsonSchemaResponseException $e) {
    // This is a server-side bug: the resource returned data that
    // does not match its own declared schema.
    error_log((string) $e);
}
```

### Default Framework Behaviour

BEAR.Sunday's default `ThrowableHandler` (1.x) already maps `JsonSchemaRequestException` to a 400 `application/vnd.error+json` response. No extra setup is needed for API (JSON) contexts.

### JSON vs HTML Contexts

The appropriate response format depends on context:

- **API / JSON context** — return a JSON error body (e.g. `{"code": 400, "message": "..."}`)  
- **HTML context** — render an HTML error page for browser users

Production apps typically override the default handler for each context. See [BEAR.Examples](https://github.com/bearsunday/BEAR.Examples) for a reference implementation using `AppThrowableHandler` (JSON) and `HtmlThrowableHandler` (HTML), backed by a shared `ExceptionStatusMapper`.
