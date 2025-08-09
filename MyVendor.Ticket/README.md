# MyVendor.Ticket

Tutorial 2 - ハイパーメディアAPIアプリケーション

## Environment Setup

Tutorial 2を実行するには、MySQL環境が必要です。

**📋 包括的な環境構築ガイド: [BEAR.Sunday 環境構築](/manuals/1.0/ja/setup.html)**  
**📋 このプロジェクト固有の手順: [SETUP.md](SETUP.md)**

### Quick Start Options

- **malt (macOS/Linux)**: `malt init && malt create && malt start`  
- **Docker (全OS)**: `docker-compose up -d && cp .env.docker .env`

## Installation

    composer install

## Usage

### Invoke Request

    composer page get /

### Available Commands

    composer serve             // start builtin server
    composer test              // run unit test
    composer tests             // test and quality checks
    composer coverage          // test coverage
    composer cs-fix            // fix the coding standard
    composer doc               // generate API document
    composer run-script --list // list all commands
    
## Links

 * BEAR.Sunday http://bearsunday.github.io/
