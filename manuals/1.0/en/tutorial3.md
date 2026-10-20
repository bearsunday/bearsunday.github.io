---
layout: docs-en
title: CLI Tutorial
category: Manual
permalink: /manuals/1.0/en/tutorial3.html
---
# BEAR.Sunday CLI Tutorial

## Prerequisites

- PHP 8.2 or later
- Composer
- Git

## Step 1: Create a Project

### 1.1 Create a New Project

```bash
VENDOR=MyVendor PACKAGE=Greet composer create-project bear/skeleton greet
cd greet
```

### 1.2 Verify the Development Server

```bash
php -S 127.0.0.1:8080 -t public
```

Open [http://127.0.0.1:8080](http://127.0.0.1:8080) in a browser and confirm that "Hello BEAR.Sunday" is displayed.

```json
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

## Step 3: Create a Greeting Resource

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
        description: 'A command that greets in multiple languages',
        output: 'greeting'
    )]
    public function onGet(
        #[Option(shortName: 'n', description: 'Name of the person to greet')]
        string $name,
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
            'greeting' => "{$greeting}, {$name}",
            'lang' => $lang
        ];

        return $this;
    }
}
```

## Step 4: Verify It Works as a Web Resource

Open the following URL in a browser to verify the behavior:

```
http://127.0.0.1:8080/greeting?name=World&lang=fr
```

You should see a JSON response like this:

```json
{
   "greeting": "Bonjour, World",
   "lang": "fr",
   "_links": {
      "self": {
         "href": "/greeting?name=World&lang=fr"
      }
   }
}
```

## Step 5: Generate the CLI Command

```bash
vendor/bin/bear-cli-gen 'MyVendor\Greet'
```

This generates the following file:
- `bin/cli/greet`: an executable CLI command

## Step 6: Test the Command

Test the generated command:

```bash
# Grant execute permission
chmod +x bin/cli/greet

# Show help
./bin/cli/greet --help

# Basic greeting
./bin/cli/greet -n "World"
# Output: Hello, World

# Greet in Japanese
./bin/cli/greet -n "世界" -l ja
# Output: こんにちは, 世界

# Output as JSON
./bin/cli/greet -n "World" -l ja --format json
# Output: {"greeting": "こんにちは, World", "lang": "ja"}
```

## Step 7: Test the Homebrew Formula Locally

### 7.1 Generate the Formula

Generating a formula requires an initialized Git repository:

```bash
# Initialize the Git repository (if not already done)
git init
git add .
git commit -m "Initial commit"
```

Generate the formula:

```bash
vendor/bin/bear-cli-gen 'MyVendor\Greet'
```

This generates the following files:
- `bin/cli/greet`: an executable CLI command
- `var/homebrew/greet.rb`: a Homebrew formula (when a Git repository is configured)

### 7.2 Test the Homebrew Installation Locally

You can test the generated formula locally:

```bash
# Install locally using the formula
brew install --formula ./var/homebrew/greet.rb

# Test the installed command
greet -n "Homebrew" -l ja
# Output: こんにちは, Homebrew

# Uninstall
brew uninstall greet
```

## Optional: Public Distribution

If you want to distribute the CLI tool to others, you can publish it as a Homebrew package:

1. Push the application to GitHub
2. Publish the generated formula (`var/homebrew/greet.rb`) in a GitHub repository with the `homebrew-` prefix
3. Users can install it with `brew tap your-vendor/greet && brew install greet`

For detailed publishing steps, see the [Homebrew documentation](https://docs.brew.sh/How-to-Create-and-Maintain-a-Tap).

**Note**: Formula generation requires the following conditions:
- The application's Git repository is initialized
- A GitHub remote repository is not required for local testing

If these conditions are not met, formula generation is skipped and the reason is displayed.

## Summary

The `Greeting` resource works as both a Web API and a CLI, and the CLI can be distributed as a Homebrew package.
There is no duplication of business logic, and a change needs to be made in only one place.

The same `Greeting` class is accessed from both the Web API and the CLI.

```php
// One resource
class Greeting extends ResourceObject {
    public function onGet(string $name, string $lang = 'en'): static
    {
        // Business logic lives in one place
    }
}
```

↓

```bash
# As a Web API
curl "http://localhost/greeting?name=World&lang=ja"

# As a CLI
./bin/cli/greet -n "World" -l ja

# As a CLI (installed with Homebrew)
brew install your-vendor/greet && greet -n "World" -l ja
```

### Long-term Maintainability and Productivity

Because domain logic is not coupled to an interface, you can verify it through resource tests. Parameter conversion and serialization for HTTP and CLI still need to be tested separately at each boundary. When adding a new boundary such as gRPC or GraphQL, the resource itself can be reused, but the boundary adapter and its tests must be added separately.

### Integration with Modern Distribution Systems

Distributing through Homebrew or Composer lets users use the CLI without knowing the details of PHP or BEAR.Sunday. They still need to prepare a runtime environment.

"Because Everything is a Resource" refers to this consistency.
