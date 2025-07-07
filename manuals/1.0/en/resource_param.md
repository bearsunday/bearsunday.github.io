---
layout: docs-en
title: Resource Parameter
category: Manual
permalink: /manuals/1.0/en/resource_param.html
---

# Resource Parameters

## Basics

Web runtime values such as HTTP requests and cookies that require ResourceObjects are passed directly to the method arguments.

For requests from HTTP, the arguments of the `onGet` and `onPost` methods are passed `$_GET` and `$_POST`, respectively, depending on the variable name. For example, `$id` in the following is passed `$_GET['id']`. Arguments passed as strings when the input is HTTP will be casted to the specified type.


```php?start_inline
class Index extends ResourceObject
{
    public function onGet(int $id): static
    {
        // ....
```

## Parameter type

### Scalar parameters

All parameters passed via HTTP are strings, but if you specify a non-string type such as `int`, it will be cast.

### Array parameters

Parameters can be nested data [^2]; data sent as JSON or nested query strings can be received as arrays.

[^2]:[parse_str](https://www.php.net/manual/ja/function.parse-str.php)参照

```php?start_inline
class Index extends ResourceObject
{
    public function onPost(array $user):static
    {
        $name = $user['name']; // bear
```

### Class Parameters

Parameters can also be received in a dedicated Input class.

```php?start_inline
class Index extends ResourceObject
{
    public function onPost(User $user): static
    {
        $name = $user->name; // bear
```

The Input class is defined in advance with parameters as public properties.

```php?start_inline
<?php

namespace Vendor\App\Input;

final class User
{
    public int $id;
    public string $name;
}
```

At this time, if there is a constructor, it will be called. [^php8]

[^php8]: This is called with named arguments in PHP8.x, but with ordinal arguments in PHP7.x.

```php?start_inline
<?php

namespace Vendor\App\Input;

final class User
{
    public function __constrcut(
        public readonly int $id,
        public readonly string $name
    } {}
}
```

The Input class can implement methods to summarize and validate input data.

### Ray.InputQuery Integration

Use the `#[Input]` attribute to leverage type-safe input object generation from the `Ray.InputQuery` library.

```php
use Ray\InputQuery\Attribute\Input;

class Index extends ResourceObject
{
    public function onPost(#[Input] ArticleInput $article): static
    {
        $this->body = [
            'title' => $article->title,
            'author' => $article->author->name
        ];
        return $this;
    }
}
```

Parameters with the `#[Input]` attribute automatically receive structured objects generated from flat query data.

```php
use Ray\InputQuery\Attribute\Input;

final class ArticleInput
{
    public function __construct(
        #[Input] public readonly string $title,
        #[Input] public readonly AuthorInput $author
    ) {}
}

final class AuthorInput  
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $email
    ) {}
}
```

In this case, nested object structures are automatically generated from flat data like `title=Hello&authorName=John&authorEmail=john@example.com`.

Array data can also be handled.

#### Simple Arrays

```php
final class TagsInput
{
    public function __construct(
        #[Input] public readonly string $title,
        #[Input] public readonly array $tags
    ) {}
}
```

```php
class Index extends ResourceObject
{
    public function onPost(#[Input] TagsInput $input): static
    {
        // For tags[]=php&tags[]=web&title=Hello
        // $input->tags = ['php', 'web']
        // $input->title = 'Hello'
```

#### Object Arrays

Use the `item` parameter to generate array elements as objects of the specified Input class.

```php
use Ray\InputQuery\Attribute\Input;

final class UserInput
{
    public function __construct(
        #[Input] public readonly string $id,
        #[Input] public readonly string $name
    ) {}
}

class Index extends ResourceObject
{
    public function onPost(
        #[Input(item: UserInput::class)] array $users
    ): static {
        foreach ($users as $user) {
            echo $user->name; // Each element is a UserInput instance
        }
    }
}
```

This generates arrays from data in the following format:

```php
// users[0][id]=1&users[0][name]=John&users[1][id]=2&users[1][name]=Jane
$data = [
    'users' => [
        ['id' => '1', 'name' => 'John'],
        ['id' => '2', 'name' => 'Jane']
    ]
];
```

* When a parameter has the `#[Input]` attribute: Object generation with Ray.InputQuery
* When a parameter doesn't have the `#[Input]` attribute: Traditional dependency injection

### File Upload

Use the `#[InputFile]` attribute to implement type-safe file upload processing with direct mapping between HTML forms and PHP code. Form `name` attributes correspond directly to method parameter names, making code the specification and improving readability.

#### Single File Upload

HTML Form:
```html
<form method="post" enctype="multipart/form-data" action="/image-upload">
    <input type="file" name="image" accept="image/*" required>
    <input type="text" name="title" placeholder="Image title">
    <button type="submit">Upload</button>
</form>
```

Corresponding resource method:
```php
use Ray\InputQuery\Attribute\InputFile;
use Koriym\FileUpload\FileUpload;
use Koriym\FileUpload\ErrorFileUpload;

class ImageUpload extends ResourceObject
{
    public function onPost(
        #[InputFile(
            maxSize: 1024 * 1024, // 1MB
            allowedTypes: ['image/jpeg', 'image/png', 'image/svg+xml'],
            allowedExtensions: ['jpg', 'jpeg', 'png', 'svg'],
            required: false  // Make file upload optional
        )]
        FileUpload|ErrorFileUpload|null $image = null, // null when no file specified
        string $title = 'Default Title'
    ): static {
        if ($image === null) {
            // Handle case when no file is specified
            $this->body = ['title' => $title, 'image' => null];
            return $this;
        }
        
        if ($image instanceof ErrorFileUpload) {
            // Handle validation errors
            $this->code = 400;
            $this->body = [
                'error' => true,
                'message' => $image->message
            ];
            return $this;
        }

        // Handle successful file upload - move file to destination directory
        $uploadDir = '/var/www/uploads/';
        $filename = uniqid() . '_' . $image->name;
        $image->move($uploadDir . $filename);

        $this->body = [
            'success' => true,
            'filename' => $image->name,
            'savedAs' => $filename,
            'size' => $image->size,
            'type' => $image->type,
            'title' => $title
        ];
        return $this;
    }
}
```

#### Multiple File Upload

HTML Form:
```html
<form method="post" enctype="multipart/form-data" action="/gallery-upload">
    <input type="file" name="images[]" multiple accept="image/*" required>
    <input type="text" name="galleryName" placeholder="Gallery name">
    <button type="submit">Upload</button>
</form>
```

Corresponding resource method:
```php
class GalleryUpload extends ResourceObject
{
    /**
     * @param array<FileUpload|ErrorFileUpload> $images
     */
    public function onPost(
        #[InputFile(
            maxSize: 2 * 1024 * 1024, // 2MB
            allowedTypes: ['image/jpeg', 'image/png', 'image/svg+xml']
        )]
        array $images, // Receive multiple files as array
        string $galleryName = 'Default Gallery'
    ): static {
        $uploadDir = '/var/www/uploads/gallery/';
        $results = [];
        $hasError = false;

        foreach ($images as $index => $image) {
            if ($image instanceof ErrorFileUpload) {
                $hasError = true;
                $results[] = [
                    'index' => $index,
                    'error' => true,
                    'message' => $image->message
                ];
                continue;
            }

            // Save file
            $filename = uniqid() . '_' . $image->name;
            $image->move($uploadDir . $filename);

            $results[] = [
                'index' => $index,
                'success' => true,
                'filename' => $image->name,
                'savedAs' => $filename,
                'size' => $image->size,
                'type' => $image->type
            ];
        }

        $this->code = $hasError ? 207 : 200; // 207 Multi-Status
        $this->body = [
            'galleryName' => $galleryName,
            'files' => $results,
            'total' => count($images),
            'hasErrors' => $hasError
        ];
        return $this;
    }
}
```

#### Testing File Uploads

File upload functionality can be easily tested:

```php
use Koriym\FileUpload\FileUpload;
use Koriym\FileUpload\ErrorFileUpload;

class FileUploadTest extends TestCase
{
    public function testSuccessfulFileUpload(): void
    {
        // Create FileUpload object from actual file
        $fileUpload = FileUpload::fromFile(__DIR__ . '/fixtures/test.jpg');
        
        $resource = $this->getResource();
        $result = $resource->post('app://self/image-upload', [
            'image' => $fileUpload,
            'title' => 'Test Image'
        ]);
        
        $this->assertSame(200, $result->code);
        $this->assertTrue($result->body['success']);
        $this->assertSame('test.jpg', $result->body['filename']);
    }
    
    public function testFileUploadValidationError(): void
    {
        // Simulate validation error
        $errorFileUpload = new ErrorFileUpload([
            'name' => 'large.jpg',
            'type' => 'image/jpeg',
            'size' => 5 * 1024 * 1024, // 5MB - exceeds size limit
            'tmp_name' => '/tmp/test',
            'error' => UPLOAD_ERR_OK
        ], 'File size exceeds maximum allowed size');
        
        $resource = $this->getResource();
        $result = $resource->post('app://self/image-upload', [
            'image' => $errorFileUpload
        ]);
        
        $this->assertSame(400, $result->code);
        $this->assertTrue($result->body['error']);
        $this->assertStringContainsString('exceeds maximum allowed size', $result->body['message']);
    }
    
    public function testMultipleFileUpload(): void
    {
        // Test multiple files
        $file1 = FileUpload::fromFile(__DIR__ . '/fixtures/image1.jpg');
        $file2 = FileUpload::fromFile(__DIR__ . '/fixtures/image2.png');
        
        $resource = $this->getResource();
        $result = $resource->post('app://self/gallery-upload', [
            'images' => [$file1, $file2],
            'galleryName' => 'Test Gallery'
        ]);
        
        $this->assertSame(200, $result->code);
        $this->assertSame(2, $result->body['total']);
        $this->assertCount(2, $result->body['files']);
    }
}
```

The `#[InputFile]` attribute enables direct correspondence between HTML form `input` elements and PHP method parameters, achieving type-safe and intuitive file upload processing. Array support makes multiple file uploads easy to implement, and testing is also straightforward.

For more details, see the [Ray.InputQuery](https://github.com/ray-di/Ray.InputQuery) documentation.

### Enum parameters

You can specify an [enumerated type](https://www.php.net/manual/en/language.types.enumerations.php) in PHP8.1 to limit the possible values.

```php
enum IceCreamId: int
{
    case VANILLA = 1;
    case PISTACHIO = 2;
}
```

```php
class Index extends ResourceObject
{
    public function onGet(IceCreamId $iceCreamId): static
    {
        $id = $iceCreamId->value // 1 or 2
```

In the above case, if anything other than 1 or 2 is passed, a `ParameterInvalidEnumException` will be raised.

## Web context binding

PHP superglobals such as `$_GET` and `$_COOKIE` can be bound to method arguments instead of being retrieved in the method.

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;

class News extends ResourceObject
{
    public function foo(
    	  #[QueryParam('id')] string $id
    ): static {
       // $id = $_GET['id'];
```

Others can be done by binding the values of `$_ENV`, `$_POST`, and `$_SERVER`.

```php?start_inline
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

class News extends ResourceObject
{
    public function onGet(
        #[QueryParam('id')] string $userId,            // $_GET['id'];
        #[CookieParam('id')] string $tokenId = "0000", // $_COOKIE['id'] or "0000" when unset;
        #[EnvParam('app_mode')] string $app_mode,      // $_ENV['app_mode'];
        #[FormParam('token')] string $token,           // $_POST['token'];
        #[ServerParam('SERVER_NAME') string $server    // $_SERVER['SERVER_NAME'];
    ): static {
```

When the client specifies a value, the specified value takes precedence and the bound value is invalid. This is useful for testing.

## Resource Binding

The `#[ResourceParam]` annotation can be used to bind the results of other resource requests to the method argument.

```php?start_inline
use BEAR\Resource\Annotation\ResourceParam;

class News extends ResourceObject
{
    public function onGet(
        #[ResourceParam('app://self//login#nickname') string $name
    ): static {
```

In this example, when the method is called, it makes a `get` request to the `login` resource and receives `$body['nickname']` with `$name`.

## Content negotiation

The `content-type` header of HTTP requests is supported. The `application/json` and `x-www-form-urlencoded` media types are determined and values are passed to the parameters. [^json].

[^json]:Set the `content-type` header to `application/json` if you are sending API requests in JSON.

