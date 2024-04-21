---
layout: docs-en
title: Reference
category: Manual
permalink: /manuals/1.0/en/reference.html
---

# Reference

## Attributes

| Attribute | Description |
| --- | --- |
| `#[CacheableResponse]` | An attribute to indicate a cacheable response. |
| `#[Cacheable(int $expirySecond = 0)]` | An attribute to indicate the cacheability of a resource. `$expirySecond` is the cache expiration time in seconds. |
| `#[CookieParam(string $name)]` | An attribute to receive parameters from cookies. `$name` is the name of the cookie. |
| `#[DonutCache]` | An attribute to indicate Donut cache. |
| `#[Embed(src: string $src, rel: string $rel)]` | An attribute to indicate embedding other resources. `$src` is the URI of the embedded resource, `$rel` is the relation name. |
| `#[EnvParam(string $name)]` | An attribute to receive parameters from environment variables. `$name` is the name of the environment variable. |
| `#[FormParam(string $name)]` | An attribute to receive parameters from form data. `$name` is the name of the form field. |
| `#[Inject]` | An attribute to indicate setter injection. |
| `#[InputValidation]` | An attribute to indicate input validation. |
| `#[JsonSchema(key: string $key = null, schema: string $schema = null, params: string $params = null)]` | An attribute to specify the JSON schema for input/output of a resource. `$key` is the schema key, `$schema` is the schema file name, `$params` is the schema file name for parameters. |
| `#[Link(rel: string $rel, href: string $href, method: string $method = null)]` | An attribute to indicate links between resources. `$rel` is the relation name, `$href` is the URI of the linked resource, `$method` is the HTTP method. |
| `#[Named(string $name)]` | An attribute to indicate named binding. `$name` is the binding name. |
| `#[OnFailure(string $name = null)]` | An attribute to specify a method for validation failure. `$name` is the name of the validation. |
| `#[OnValidate(string $name = null)]` | An attribute to specify a validation method. `$name` is the name of the validation. |
| `#[Produces(array $mediaTypes)]` | An attribute to specify the output media types of a resource. `$mediaTypes` is an array of producible media types. |
| `#[QueryParam(string $name)]` | An attribute to receive query parameters. `$name` is the name of the query parameter. |
| `#[RefreshCache]` | An attribute to indicate cache refresh. |
| `#[ResourceParam(uri: string $uri, param: string $param)]` | An attribute to receive the result of another resource as a parameter. `$uri` is the URI of the resource, `$param` is the parameter name. |
| `#[ReturnCreatedResource]` | An attribute to indicate that the created resource will be returned. |
| `#[ServerParam(string $name)]` | An attribute to receive parameters from server variables. `$name` is the name of the server variable. |
| `#[Ssr(app: string $appName, state: array $state = [], metas: array $metas = [])]` | An attribute to indicate server-side rendering. `$appName` is the name of the JS application, `$state` is the state of the application, `$metas` is an array of meta information. |
| `#[Transactional(array $props = ['pdo'])]` | An attribute to indicate that a method will be executed within a transaction. `$props` is an array of properties to which the transaction will be applied. |
| `#[UploadFiles]` | An attribute to receive uploaded files. |
| `#[Valid(form: string $form = null, onFailure: string $onFailure = null)]` | An attribute to indicate request validation. `$form` is the form class name, `$onFailure` is the method name for validation failure. |

## Modules

| Module Name | Description |
| --- | --- |
| `ApcSsrModule` | A module for server-side rendering using APCu. |
| `ApiDoc` | A module for generating API documentation. |
| `AppModule` | The main module of the application. It installs and configures other modules. |
| `AuraSqlModule` | A module for database connection using Aura.Sql. |
| `AuraSqlQueryModule` | A module for query builder using Aura.SqlQuery. |
| `CacheVersionModule` | A module for cache version management. |
| `CliModule` | A module for command-line interface. |
| `DoctrineOrmModule` | A module for database connection using Doctrine ORM. |
| `FakeModule` | A fake module for testing purposes. |
| `HalModule` | A module for HAL (Hypertext Application Language). |
| `HtmlModule` | A module for HTML rendering. |
| `ImportAppModule` | A module for loading other applications. |
| `JsonSchemaModule` | A module for input/output validation of resources using JSON schema. |
| `JwtAuthModule` | A module for authentication using JSON Web Token (JWT). |
| `NamedPdoModule` | A module that provides named PDO instances. |
| `PackageModule` | A module that installs the basic modules provided by BEAR.Package together. |
| `ProdModule` | A module for production environment settings. |
| `QiqModule` | A module for the Qiq template engine. |
| `ResourceModule` | A module for settings related to resource classes. |
| `AuraRouterModule` | A module for routing using Aura.Router. |
| `SirenModule` | A module for Siren (Hypermedia Specification). |
| `SpyModule` | A module for recording method calls. |
| `SsrModule` | A module for server-side rendering. |
| `TwigModule` | A module for the Twig template engine. |
| `ValidationModule` | A module for validation. |
