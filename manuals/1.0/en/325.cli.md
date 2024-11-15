---
layout: docs-en
title: Command Line Interface (CLI)
category: Manual
permalink: /manuals/1.0/en/cli.html
---
# Command Line Interface (CLI)

BEAR.Sunday's Resource Oriented Architecture (ROA) represents all application functionality as URI-addressable resources. This approach allows resources to be accessed through various means, not just through the web.

```bash
$ php bin/page.php '/greeting?name=World&lang=fr'
{
    "greeting": "Bonjour, World",
    "lang": "fr"
}
```

BEAR.Cli is a tool that converts these resources into native CLI commands and makes them distributable via Homebrew, which uses formula scripts to define installation procedures.

```bash
$ greet -n "World" -l fr
Bonjour, World
```

You can reuse existing application resources as standard CLI tools without writing additional code. Through Homebrew distribution, users can utilize these tools like any other command-line tool, without needing to know they're powered by PHP or BEAR.Sunday.

## Installation

Install using Composer:

```bash
composer require bear/cli
```

## Basic Usage

### Adding CLI Attributes to Resources

Add CLI attributes to your resource class to define the command-line interface:

```php
use BEAR\Cli\Attribute\Cli;
use BEAR\Cli\Attribute\Option;

class Greeting extends ResourceObject
{
    #[Cli(
        name: 'greet',
        description: 'Say hello in multiple languages',
        output: 'greeting'
    )]
    public function onGet(
        #[Option(shortName: 'n', description: 'Name to greet')]
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

### Generating CLI Commands and Formula

To convert a resource into a command, run the following command with your application name (vendor name and project name):

```bash
$ vendor/bin/bear-cli-gen 'MyVendor\MyProject'
# Generated files:
#   bin/cli/greet         # CLI command
#   var/homebrew/greet.rb # Homebrew formula
```

Note: Homebrew formula is generated only when a GitHub repository is configured.

## Command Usage

The generated command provides standard CLI features such as:

### Displaying Help

```bash
$ greet --help
Say hello in multiple languages

Usage: greet [options]

Options:
  --name, -n     Name to greet (required)
  --lang, -l     Language (en, ja, fr, es) (default: en)
  --help, -h     Show this help message
  --version, -v  Show version information
  --format       Output format (text|json) (default: text)
```

### Showing Version Information

```bash
$ greet --version
greet version 0.1.0
```

### Basic Usage Examples

```bash
# Basic greeting
$ greet -n "World"
Hello, World

# Specify language
$ greet -n "World" -l ja
こんにちは, World

# Short options
$ greet -n "World" -l fr
Bonjour, World

# Long options
$ greet --name "World" --lang es
¡Hola, World
```

### JSON Output

```bash
$ greet -n "World" -l ja --format json
{
    "greeting": "こんにちは, World",
    "lang": "ja"
}
```

### Output Behavior

CLI command output follows these specifications:

- **Default output**: Displays only the specified field value
- **`--format=json` option**: Displays full JSON response similar to API endpoint
- **Error messages**: Output to standard error (stderr)
- **HTTP status code mapping**: Maps to exit codes (0: success, 1: client error, 2: server error)

## Distribution

Commands created with BEAR.Cli can be distributed via Homebrew.
Formula generation requires the application to be published on GitHub:

### 1. Local Formula Distribution

For testing development versions:

```bash
$ brew install --formula ./var/homebrew/greet.rb
```

### 2. Homebrew Tap Distribution

Method for wide distribution using a public repository:

Note: The file name of the formula and the class name inside it are based on the name of the repository. For example, if the GH repository is `koriym/greet`, then `var/homebrew/greet.rb` will be generated, which contains the `Greet` class. In this case, `greet` will be the name of the tap that is published, but if you want to change it, please change the class name and file name of fomula script.

```bash
$ brew tap your-vendor/greet
$ brew install your-vendor/greet
```

This method is particularly suitable for:

- Open source projects
- Continuous updates provision

#### Testing Development Version

```bash
$ brew install --HEAD ./var/homebrew/greet.rb
```
```bash
$ greet --version
greet version 0.1.0
```

#### Stable Release

1. Create a tag:
```bash
$ git tag -a v0.1.0 -m "Initial stable release"
$ git push origin v0.1.0
```

2. Update formula:
```diff
 class Greet < Formula
+  desc "Your CLI tool description"
+  homepage "https://github.com/your-vendor/greet"
+  url "https://github.com/your-vendor/greet/archive/refs/tags/v0.1.0.tar.gz"
+  sha256 "..." # Add hash value obtained from the command below
+  version "0.1.0"
   head "https://github.com/your-vendor/greet.git", branch: "main"
   
   depends_on "php@8.1"
   depends_on "composer"
 end
```

You can add dependencies like databases to the formula as needed. However, it's recommended to handle database setup and other environment configuration in the `bin/setup` script.

3. Get SHA256 hash:
```bash
# Download tarball from GitHub and calculate hash
$ curl -sL https://github.com/your-vendor/greet/archive/refs/tags/v0.1.0.tar.gz | shasum -a 256
```

4. Create Homebrew tap:
   Create a repository using [GitHub CLI(gh)](https://cli.github.com/) or [github.com/new](https://github.com/new). The public repository name must start with `homebrew-`, for example `homebrew-greet`:
```bash
$ gh auth login
$ gh repo create your-vendor/homebrew-greet --public --clone
# Or create and clone repository using the web interface
$ cd homebrew-greet
```

5. Place and publish formula:
```bash
$ cp /path/to/project/var/homebrew/greet.rb .
$ git add greet.rb
$ git commit -m "Add formula for greet command"
$ git push
```

6. Installation and distribution:
   End users can start using the tool with just these commands. PHP environment and dependency package installation are handled automatically, so users don't need to worry about environment setup:
```bash
$ brew tap your-vendor/greet    # homebrew- prefix can be omitted
$ brew install your-vendor/greet
# Ready to use immediately
$ greet --version
greet version 0.1.0
```

## Formula Customization

You can edit the formula using the `brew edit` command as needed:

```bash
$ brew edit your-vendor/greet
```

```ruby
class Greet < Formula
  desc "Your CLI tool description"
  homepage "https://github.com/your-vendor/greet"
  url "https://github.com/your-vendor/greet/archive/refs/tags/v0.1.0.tar.gz"
  sha256 "..." # tgz SHA256
  version "0.1.0"
  
  depends_on "php@8.4"  # Specify PHP version
  depends_on "composer"

  # Add if required by the application
  # depends_on "mysql"
  # depends_on "redis"
end
```

## Clean Architecture

BEAR.Cli demonstrates the strengths of both Resource Oriented Architecture (ROA) and Clean Architecture. Following Clean Architecture's principle that "UI is a detail," you can add CLI as a new adapter alongside the web interface for the same resource.

Furthermore, BEAR.Cli supports not only command creation but also distribution and updates through Homebrew. This allows end users to start using tools with a single command, treating them as native UNIX commands without awareness of PHP or BEAR.Sunday.

Additionally, CLI tools can be version-controlled and updated independently from the application repository. This means they can maintain stability and continuous updates as command-line tools without being affected by API evolution. This represents a new form of API delivery, realized through the combination of Resource Oriented Architecture and Clean Architecture.
