---
layout: docs-en
title: Resource Parameters
category: Manual
permalink: /manuals/1.0/en/resource_param.html
---

# Resource Parameters

## Basics

Web runtime values such as HTTP requests and cookies that ResourceObjects require are passed directly to method arguments. For HTTP requests, the `onGet` and `onPost` method arguments receive `$_GET` and `$_POST` respectively, according to variable names.

For example, the following `$id` receives `$_GET['id']`. When input is from HTTP, string arguments are cast to the specified type.

```php
class Index extends ResourceObject
{
    public function onGet(int $id): static
    {
        // ....
```

## Parameter Types

### Scalar Parameters

All parameters passed via HTTP are strings, but specifying non-string types like `int` will cast them.

### Array Parameters

Parameters can be nested data [^2]. Data sent as JSON or nested query strings can be received as arrays.

[^2]: See [parse_str](https://www.php.net/manual/en/function.parse-str.php)

```php
class Index extends ResourceObject
{
    public function onPost(array $user): static
    {
        $name = $user['name']; // bear
```

### Class Parameters

Parameters can also be received as dedicated Input classes.

```php
class Index extends ResourceObject
{
    public function onPost(User $user): static
    {
        $name = $user->name; // bear
```

Input classes are pre-defined with parameters as public properties.

```php
<?php
namespace Vendor\App\Input;

final class User
{
    public int $id;
    public string $name;
}
```

If a constructor exists, it will be called. [^php8]

[^php8]: Called with named arguments in PHP8.x, but with positional arguments in PHP7.x.

```php
<?php
namespace Vendor\App\Input;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {}
}
```

Namespaces are arbitrary. Input classes can implement methods to aggregate or validate input data.

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
    }
}
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
        $originalName = basename($image->name);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '', pathinfo($originalName, PATHINFO_FILENAME));
        $filename = bin2hex(random_bytes(8)) . '_' . uniqid() . '_' . $safeName . '.' . $extension;
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
            $originalName = basename($image->name);
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '', pathinfo($originalName, PATHINFO_FILENAME));
            $filename = bin2hex(random_bytes(8)) . '_' . uniqid() . '_' . $safeName . '.' . $extension;
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

### Enum Parameters

You can specify PHP8.1 [enumerations](https://www.php.net/manual/en/language.types.enumerations.php) to restrict possible values.

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
        $id = $iceCreamId->value; // 1 or 2
    }
}
```

In the above case, passing anything other than 1 or 2 will raise a `ParameterInvalidEnumException`.

## Web Context Binding

Values from PHP superglobals like `$_GET` and `$_COOKIE` can be bound to method arguments instead of retrieving them within methods.

```php
use Ray\WebContextParam\Annotation\QueryParam;

class News extends ResourceObject
{
    public function foo(
        #[QueryParam('id')] string $id
    ): static {
        // $id = $_GET['id'];
```

You can also bind values from `$_ENV`, `$_POST`, and `$_SERVER`.

```php
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\ServerParam;

class News extends ResourceObject
{
    public function onGet(
        #[QueryParam('id')] string $userId,            // $_GET['id']
        #[CookieParam('id')] string $tokenId = "0000", // $_COOKIE['id'] or "0000" when unset
        #[EnvParam('app_mode')] string $app_mode,      // $_ENV['app_mode']
        #[FormParam('token')] string $token,           // $_POST['token']
        #[ServerParam('SERVER_NAME')] string $server   // $_SERVER['SERVER_NAME']
    ): static {
```

When clients specify values, those values take precedence and bound values become invalid. This is useful for testing.

## Resource Binding

The `#[ResourceParam]` annotation can bind results from other resource requests to method arguments.

```php
use BEAR\Resource\Annotation\ResourceParam;

class News extends ResourceObject
{
    public function onGet(
        #[ResourceParam('app://self//login#nickname')] string $name
    ): static {
```

In this example, when the method is called, it makes a `get` request to the `login` resource and receives `$body['nickname']` as `$name`.

## Content Negotiation

HTTP request `content-type` headers are supported. `application/json` and `x-www-form-urlencoded` media types are distinguished and values are passed to parameters. [^json]

[^json]: When sending API requests as JSON, set the `content-type` header to `application/json`.

