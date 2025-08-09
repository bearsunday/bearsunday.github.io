---
layout: docs-en
title: Environment Setup
category: Manual
permalink: /manuals/1.0/en/setup.html
---
# Environment Setup

This guide explains how to set up a development environment for BEAR.Sunday projects. Choose the appropriate method for your environment.

## Setup Methods Comparison

| Method | OS Support | Features | Recommended For |
|---|---|---|---|
| **malt** | macOS, Linux | Homebrew-based, lightweight, sharable config | Individual & team development |
| **Docker** | Windows, macOS, Linux | Container-based, complete reproducibility | Team development, CI/CD |
| **Manual Setup** | All OS | Use existing infrastructure, fine control | Existing infrastructure |

## Setup with malt

### Overview

[malt](https://github.com/koriym/homebrew-malt) is a development environment management tool based on Homebrew.

**Key Features of malt:**
- **Completely Local**: All settings and data are stored within the project directory
- **Clean Removal**: Simply delete the project folder to completely remove the environment
- **Dedicated Port Commands**: Aliases like `mysql@3306`, `redis@6379` for port-specific connections
- **No Global Pollution**: No impact on system-wide MySQL/Redis or other services
- **Visible Configuration**: All configuration files are managed and shareable within the project

### Prerequisites

- macOS or Linux
- [Homebrew](https://brew.sh/) installed

### Installation

```bash
# Add Homebrew tap
brew tap koriym/homebrew-malt

# Install malt
brew install malt
```

**💡 About Homebrew taps:**  
A tap is a third-party repository that provides packages (formulae) beyond Homebrew's core repository. Adding a tap with `brew tap <name>` allows you to install packages from that tap using short names instead of full repository paths.

### Basic Usage

```bash
# Initialize project
malt init

# Generate configuration files
malt create

# Install dependencies (if needed)
malt install

# Start services
malt start

# Set environment variables (run in each session)
source <(malt env)
```

### Configuration Files

malt manages the environment with these files:

```
malt.json          # malt configuration
malt/
  conf/
    my_3306.cnf    # MySQL config
    php.ini        # PHP config
    httpd_8080.conf # Apache config
    nginx_80.conf   # Nginx config
```

These files can be included in your project for team environment sharing.

### Service Management

```bash
# Check status
malt status

# Start/stop/restart
malt start
malt stop  
malt restart

# Specific services only
malt start mysql
malt stop nginx
```

### Database Operations

```bash
# Dedicated port commands (recommended)
mysql@3306  # Project-specific MySQL connection
redis@6379  # Project-specific Redis connection

# Traditional method
mysql --defaults-file=malt/conf/my_3306.cnf -h 127.0.0.1

# Database creation example
mysql@3306 -e "CREATE DATABASE IF NOT EXISTS myapp"
```

**Important**: `mysql@3306` is a project-specific connection, completely isolated from your system's global MySQL installation.

## Setup with Docker

### Overview

Docker provides OS-independent, consistent development environments.

**Docker Considerations:**
- **Global Command Conflicts**: The system `mysql` command points to global MySQL installation
- **Container-specific Access**: Requires specific connection methods for Docker container databases
- **Port Conflict Risk**: Ports like 3306 may conflict with system services

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop) installed
- Docker Compose available

### Basic docker-compose.yml

```yaml
version: '3.8'

services:
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: ""
      MYSQL_ALLOW_EMPTY_PASSWORD: "yes"
      MYSQL_DATABASE: myapp
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/init.sql:/docker-entrypoint-initdb.d/init.sql
    command: --default-authentication-plugin=mysql_native_password
    
  redis:
    image: redis:alpine
    ports:
      - "6379:6379"
      
  memcached:
    image: memcached:alpine
    ports:
      - "11211:11211"

volumes:
  mysql_data:
```

### Usage

```bash
# Start environment
docker-compose up -d

# Check status
docker-compose ps

# View logs
docker-compose logs mysql

# Stop environment
docker-compose stop

# Complete removal (including data)
docker-compose down -v
```

### Database Connection

```bash
# From host (port specification required)
mysql -h 127.0.0.1 -P 3306 -u root

# From inside container (recommended)
docker-compose exec mysql mysql -u root

# Via phpMyAdmin (browser)
open http://localhost:8080
```

**Warning**: If `mysql` is installed on your system, running just `mysql` will connect to your system's MySQL, not the Docker container. To access the Docker database, you must either specify host/port or execute from within the container.

## Manual Setup

### PHP Environment

```bash
# macOS (Homebrew)
brew install php@8.4
brew install composer

# Ubuntu/Debian
sudo apt update
sudo apt install php8.4 php8.4-{cli,mysql,mbstring,xml,zip,curl}
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# CentOS/RHEL
sudo dnf install php php-{cli,mysql,mbstring,xml,zip,curl}
```

### MySQL Environment

```bash
# macOS (Homebrew) 
brew install mysql@8.0
brew services start mysql@8.0

# Ubuntu/Debian
sudo apt install mysql-server-8.0
sudo systemctl start mysql

# CentOS/RHEL
sudo dnf install mysql-server
sudo systemctl start mysqld
```

### PHP Extensions

Useful PHP extensions for development:

```bash
# Add extensions tap (one-time setup)
brew tap shivammathur/extensions

# Xdebug (for debugging)
brew install xdebug@8.4  # macOS
sudo apt install php8.4-xdebug  # Ubuntu

# Redis
brew install redis@8.4  # macOS  
sudo apt install php8.4-redis  # Ubuntu

# APCu (caching)
brew install apcu@8.4  # macOS
sudo apt install php8.4-apcu  # Ubuntu
```

## Project-specific Configuration

### Environment Variables

Create `.env` file in project root:

```bash
# Database connection (MySQL)
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=myapp  
DB_USER=root
DB_PASS=
DB_DSN=mysql:host=127.0.0.1;port=3306;dbname=myapp

# Database connection (SQLite)
DB_DSN=sqlite:var/db.sqlite3

# Cache (Redis)
REDIS_HOST=127.0.0.1:6379

# Session (Memcached)  
MEMCACHED_HOST=127.0.0.1:11211
```

### Database Migrations

Using Phinx:

```bash
# Install Phinx
composer require --dev robmorgan/phinx

# Create config file
./vendor/bin/phinx init

# Create migration
./vendor/bin/phinx create MyMigration

# Run migration
./vendor/bin/phinx migrate
```

## Development Server

### PHP Built-in Server

```bash
# Start on port 8080
php -S 127.0.0.1:8080 -t public

# With Xdebug enabled
php -dzend_extension=xdebug.so -S 127.0.0.1:8080 -t public
```

### malt Server

```bash
# Choose Apache or Nginx to start
malt start apache   # Apache (http://127.0.0.1:8080)
malt start nginx    # Nginx (http://127.0.0.1:80)

# Check service status
malt status

# Stop specific server
malt stop apache
malt stop nginx
```

## Troubleshooting

### Port Conflicts

Check port usage:

```bash
# macOS/Linux
lsof -i :3306
netstat -tulpn | grep :3306

# Kill process
kill -9 PID
```

### PHP Configuration

Check configuration files:

```bash
php --ini
php -m  # Check loaded modules
```

### MySQL Connection Errors

```bash
# Connection test
mysql -h 127.0.0.1 -P 3306 -u root -p

# Service status (Linux)
sudo systemctl status mysql

# Check error logs
sudo tail -f /var/log/mysql/error.log
```

### malt-specific Issues

```bash
# Check service status
malt status

# Reset configuration
malt stop
rm -rf malt/
malt create
malt start
```

## CI/CD Environment Setup

### GitHub Actions

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: ""
          MYSQL_ALLOW_EMPTY_PASSWORD: yes
          MYSQL_DATABASE: test_db
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, xml, pdo_mysql, mysqli, intl, curl, zip
          
      - name: Install dependencies
        run: composer install
        
      - name: Run tests
        run: ./vendor/bin/phpunit
```

## Environment Selection Guidelines

### Development vs Production Environment Differences

**Development environments prioritize transparency and directness**
- malt: `mysql@3306` for instant project-specific DB access, direct config file editing, **native file system performance**
- Docker: Well-known. Container-mediated access, complex configuration inspection, virtualization overhead

Environment sharing works excellently with both approaches.

**Production environments prioritize reproducibility and monitoring capabilities**
- Docker: Identical behavior anywhere, rich monitoring tool ecosystem - essentially Docker only

## Summary

- **Daily Development/Learning**: malt recommended
- **Team Development**: malt (with config sharing) or Docker
- **Production/CI/CD**: Docker only
- **Complex Configurations**: Docker

Refer to individual tutorials for detailed configuration examples for each environment.