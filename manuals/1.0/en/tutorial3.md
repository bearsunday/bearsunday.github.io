k---
layout: docs-en
title: CLI Tutorial
category: Manual
permalink: /manuals/1.0/en/tutorial3.html
---
# BEAR.Sunday CLI Tutorial

## Prerequisites

- PHP 8.2 or higher
- Composer
- Git

## Step 1: Project Creation

### 1.1 Create a New Project

```bash
composer create-project -n bear/skeleton MyVendor.Greet
cd MyVendor.Greet
```

### 1.2 Verify Development Server

```bash
php -S 127.0.0.1:8080 -t public
```

Access [http://127.0.0.1:8080](http://127.0.0.1:8080) in your browser and confirm that "Hello BEAR.Sunday" is displayed.

```php
{
    "greeting": "Hello BEAR.Sunday",
    "_links": {
        "self": {
            "href": "/index"
        }
    }
}
```

## Step 2: Install BEAR.Cli

```bash
composer require bear/cli
```

## Step 3: Create Greeting Resource

Create `src/Resource/Page/Greeting.php`:

```php
<?php

namespace MyVendor\Greet\Resource\Page;

use BEAR\Cli\Attribute\Cli;
use BEAR\Cli\Attribute\Option;
use BEAR\Resource\ResourceObject;

class Greeting extends ResourceObject
{
    #[Cli(
        name: 'greet',
        description: 'Generate a greeting message',
        output: 'message'
    )]
    public function onGet(
        #[Option(shortName: 'n', description: 'Name to greet')]
        string $name = 'World',
        #[Option(shortName: 'l', description: 'Language (en, ja, fr, es)')]
        string $lang = 'en'
    ): static {
        $greeting = match ($lang) {
            'ja' => 'こんにちは',
            'fr' => 'Bonjour',
            'es' => '¡Hola',
            default => 'Hello',
        };
        
        $this->body = [
            'message' => "{$greeting}, {$name}!",
            'language' => $lang
        ];

        return $this;
    }
}
```

## Step 4: Generate CLI Command

Generate the CLI command using your application namespace:

```bash
$ vendor/bin/bear-cli-gen 'MyVendor\Greet'
# Generated files:
#   bin/cli/greet         # CLI command
#   var/homebrew/greet.rb # Homebrew formula (if Git repository is configured)
```

## Step 5: Test the CLI Command

### 5.1 Basic Usage

```bash
$ bin/cli/greet --help
Generate a greeting message

Usage: greet [options]

Options:
  --name, -n     Name to greet (default: World)
  --lang, -l     Language (en, ja, fr, es) (default: en)
  --help, -h     Show this help message

$ bin/cli/greet
Hello, World!

$ bin/cli/greet -n "Alice" -l ja
こんにちは, Alice!
```

### 5.2 Advanced Examples

```bash
# French greeting
$ bin/cli/greet --name "Pierre" --lang fr
Bonjour, Pierre!

# Spanish greeting
$ bin/cli/greet -n "Carlos" -l es
¡Hola, Carlos!
```

## Step 6: Add More Complex Features

### 6.1 Add Time-Based Greetings

Update the `Greeting` resource to include time-based greetings:

```php
<?php

namespace MyVendor\Greet\Resource\Page;

use BEAR\Cli\Attribute\Cli;
use BEAR\Cli\Attribute\Option;
use BEAR\Resource\ResourceObject;
use DateTimeImmutable;

class Greeting extends ResourceObject
{
    #[Cli(
        name: 'greet',
        description: 'Generate a time-aware greeting message',
        output: 'message'
    )]
    public function onGet(
        #[Option(shortName: 'n', description: 'Name to greet')]
        string $name = 'World',
        #[Option(shortName: 'l', description: 'Language (en, ja, fr, es)')]
        string $lang = 'en',
        #[Option(shortName: 't', description: 'Include time-based greeting')]
        bool $timeGreeting = false
    ): static {
        $greeting = $this->getGreeting($lang, $timeGreeting);
        
        $this->body = [
            'message' => "{$greeting}, {$name}!",
            'language' => $lang,
            'time' => (new DateTimeImmutable())->format('Y-m-d H:i:s')
        ];

        return $this;
    }
    
    private function getGreeting(string $lang, bool $timeGreeting): string
    {
        $baseGreeting = match ($lang) {
            'ja' => 'こんにちは',
            'fr' => 'Bonjour',
            'es' => '¡Hola',
            default => 'Hello',
        };
        
        if (!$timeGreeting) {
            return $baseGreeting;
        }
        
        $hour = (int) (new DateTimeImmutable())->format('H');
        
        return match ($lang) {
            'ja' => match (true) {
                $hour < 12 => 'おはようございます',
                $hour < 18 => 'こんにちは',
                default => 'こんばんは'
            },
            'fr' => match (true) {
                $hour < 12 => 'Bonjour',
                $hour < 18 => 'Bon après-midi',
                default => 'Bonsoir'
            },
            'es' => match (true) {
                $hour < 12 => 'Buenos días',
                $hour < 18 => 'Buenas tardes',
                default => 'Buenas noches'
            },
            default => match (true) {
                $hour < 12 => 'Good morning',
                $hour < 18 => 'Good afternoon',
                default => 'Good evening'
            }
        };
    }
}
```

### 6.2 Test Enhanced Features

```bash
# Regenerate CLI command after changes
$ vendor/bin/bear-cli-gen 'MyVendor\Greet'

# Test time-based greetings
$ bin/cli/greet -n "Alice" -l en -t
Good morning, Alice!  # (if run in the morning)

$ bin/cli/greet -n "田中" -l ja -t
おはようございます, 田中!  # (if run in the morning)
```

## Step 7: Testing

### 7.1 Create Unit Tests

Create `tests/Resource/Page/GreetingTest.php`:

```php
<?php

namespace MyVendor\Greet\Resource\Page;

use BEAR\Resource\ResourceInterface;
use MyVendor\Greet\Injector;
use PHPUnit\Framework\TestCase;

class GreetingTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $this->resource = Injector::getInstance('test-cli-app')
            ->getInstance(ResourceInterface::class);
    }

    public function testDefaultGreeting(): void
    {
        $response = $this->resource->get('page://self/greeting');
        
        $this->assertSame(200, $response->code);
        $this->assertSame('Hello, World!', $response->body['message']);
        $this->assertSame('en', $response->body['language']);
    }

    public function testJapaneseGreeting(): void
    {
        $response = $this->resource->get('page://self/greeting', [
            'name' => '太郎',
            'lang' => 'ja'
        ]);
        
        $this->assertSame('こんにちは, 太郎!', $response->body['message']);
        $this->assertSame('ja', $response->body['language']);
    }

    public function testTimeBasedGreeting(): void
    {
        $response = $this->resource->get('page://self/greeting', [
            'name' => 'Alice',
            'lang' => 'en',
            'timeGreeting' => true
        ]);
        
        $this->assertStringContains('Alice!', $response->body['message']);
        $this->assertArrayHasKey('time', $response->body);
    }
}
```

### 7.2 Run Tests

```bash
$ composer test
```

## Step 8: Deployment and Distribution

### 8.1 GitHub Repository Setup

If you have a GitHub repository configured, a Homebrew formula will be generated automatically. You can distribute your CLI tool via Homebrew:

```bash
# Create a tap repository
$ git clone https://github.com/yourusername/homebrew-tap.git
$ cp var/homebrew/greet.rb homebrew-tap/greet.rb
$ cd homebrew-tap
$ git add greet.rb
$ git commit -m "Add greet formula"
$ git push
```

### 8.2 Install via Homebrew

Users can then install your CLI tool:

```bash
$ brew tap yourusername/tap
$ brew install greet
$ greet -n "User" -l en
Hello, User!
```

## Conclusion

This tutorial has demonstrated more than just CLI tool creation—it has revealed the essential value of BEAR.Sunday:

### The True Value of Resource-Oriented Architecture

**One Resource, Multiple Boundaries**
- The `Greeting` resource functions as Web API, CLI, and Homebrew package with a single implementation
- No duplication of business logic, maintenance in one place

### Boundary-Crossing Framework

BEAR.Sunday functions as a **boundary framework**, transparently handling:

- **Protocol boundaries**: HTTP ↔ Command line
- **Interface boundaries**: Web ↔ CLI ↔ Package distribution  
- **Environment boundaries**: Development ↔ Production ↔ User environments

### Design Philosophy in Action

```php
// One resource
class Greeting extends ResourceObject {
    public function onGet(string $name, string $lang = 'en'): static
    {
        // Business logic in one place
    }
}
```

↓

```bash
# As Web API
curl "http://localhost/greeting?name=World&lang=ja"

# As CLI  
./bin/cli/greet -n "World" -l ja

# As Homebrew package
brew install your-vendor/greet && greet -n "World" -l ja
```

### Long-term Maintainability and Productivity

- **DRY Principle**: Domain logic is not coupled with interfaces
- **Unified Testing**: Testing one resource covers all boundaries
- **Consistent API Design**: Same parameter structure for Web API and CLI
- **Future Extensibility**: New boundaries (gRPC, GraphQL, etc.) can use the same resource
- **PHP Version Independence**: Freedom to continue using what works

### Integration with Modern Distribution Systems

BEAR.Sunday resources integrate naturally with modern package systems. By leveraging package managers like Homebrew and the Composer ecosystem, users can utilize tools through unified interfaces without being aware of the execution environment.

BEAR.Sunday's "Because Everything is a Resource" is not just a slogan, but a design philosophy that realizes consistency and maintainability across boundaries. As experienced in this tutorial, resource-oriented architecture creates boundary-free software and brings new horizons to both development and user experiences.

## Next Steps

- Explore more complex CLI patterns
- Add configuration file support
- Implement subcommands
- Add logging and error handling
- Create interactive CLI interfaces

For more information, see the [CLI documentation](cli.html) and [BEAR.Cli repository](https://github.com/bearsunday/BEAR.Cli).
