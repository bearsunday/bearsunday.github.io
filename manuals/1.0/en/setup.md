---
layout: docs-en
title: Environment Setup
category: Manual
permalink: /manuals/1.0/en/setup.html
---

# Environment Setup

Choose **malt / Docker / manual setup** based on your OS and team structure. This is a practical guide that consolidates the features, setup procedures, and operational points for each method in one place.

---

## Method Selection

| Method        | Target OS              | Features                                           | Recommended Use      |
| ------------- | --------------------- | ------------------------------------------------- | -------------------- |
| **malt**      | macOS, WSL2, Linux    | Homebrew-based, lightweight, configuration shareable, local-complete, batch service management commands | Individual dev, team dev |
| **Docker**    | macOS, Windows, Linux | Container-based complete environment reproduction, CI/CD friendly | Team dev, CI/CD, production-like |
| **Manual**    | All OS                | Use existing environment as-is, fine-grained control | Existing infrastructure, constrained environments |

---

## Environment Setup with malt

### Overview

**malt** is a development environment management tool based on Homebrew. It consolidates configuration and data directly under the project, achieving local completion.

**Key Features**

* **Completely Local**: All configuration and data stored within the project
* **Clean Deletion**: Folder deletion = environment deletion
* **Dedicated Port Commands**: Aliases like `mysql@3306` / `redis@6379`
* **No Global Pollution**: No impact on system MySQL/Redis etc.
* **Configuration Visibility**: Configuration files can be shared and reviewed within the project
* **Batch Service Management**: `malt start` / `malt stop` can start/stop related services together

### Prerequisites

* macOS or Linux (including WSL2)
* Homebrew installed

### Installation

```bash
# Add Homebrew taps
brew tap shivammathur/php
brew tap shivammathur/extensions
brew tap koriym/malt

# Install malt
brew install malt
```

### Basic Operations (Shortest Path)

```bash
malt init && malt install && malt create && malt start
source <(malt env)
```

### Configuration Files

```
malt.json          # malt configuration
malt/
  conf/
    my_3306.cnf     # MySQL configuration
    php.ini         # PHP configuration
    httpd_8080.conf # Apache configuration
    nginx_80.conf   # Nginx configuration
```

These files can be included in your project for team environment sharing.

### Service Management

```bash
# Status check
malt status

# Start / stop / restart all services
malt start
malt stop
malt restart

# Specific services only
malt start mysql
malt stop nginx
```

### Database Operations

```bash
mysql@3306  # Connect to project-specific MySQL
redis@6379  # Connect to project-specific Redis
mysql@3306 -e "CREATE DATABASE IF NOT EXISTS myapp"
```

> **Important**: `mysql@3306` is **project-specific connection**. It's isolated from the system's global MySQL.

---

## Environment Setup with Docker

### Overview

Docker provides OS-independent, consistent development environments.

**Docker Considerations:**
- **Global Command Conflicts**: The system `mysql` command points to global MySQL installation
- **Container-specific Access**: Requires specific connection methods for Docker container databases
- **Port Conflict Risk**: Ports like 3306 may conflict with system services
- **macOS File Access**: Host-container file mount performance degradation, especially noticeable during bulk file operations (builds, tests)
- **Security**: `MYSQL_ALLOW_EMPTY_PASSWORD` should be limited to development use only

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
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 3
    
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

# Check logs
docker-compose logs mysql

# Stop environment
docker-compose stop

# Complete removal (including data)
docker-compose down -v
```

### Database Connection

```bash
# Connect from host (port specification required)
mysql -h 127.0.0.1 -P 3306 -u root

# Connect from within container (recommended)
docker-compose exec mysql mysql -u root
```

**Warning**: If `mysql` is installed on your system, running just `mysql` will connect to your system's MySQL, not the Docker container. To access the Docker database, you must either specify host/port or execute from within the container.

---

## Manual Environment Setup

### PHP

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

### MySQL

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

### Useful PHP Extensions for Development

```bash
# Xdebug (debugging)
brew install shivammathur/extensions/xdebug@8.4    # Homebrew
sudo apt install php8.4-xdebug                    # Ubuntu

# Redis
brew install shivammathur/extensions/redis@8.4    # Homebrew
sudo apt install php8.4-redis                     # Ubuntu

# APCu (caching)
brew install shivammathur/extensions/apcu@8.4     # Homebrew
sudo apt install php8.4-apcu                      # Ubuntu
```

---

## BEAR.Sunday Quick Start Example

```bash
composer create-project bear/skeleton my-app
cd my-app
malt init && malt install && malt create && malt start
source <(malt env)
```

---

## Project-specific Configuration

### .env (Example)

```dotenv
# MySQL
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=myapp
DB_USER=root
DB_PASS=
DB_DSN=mysql:host=127.0.0.1;port=3306;dbname=myapp

# SQLite (for switching)
DB_DSN=sqlite:var/db.sqlite3

# Redis
REDIS_HOST=127.0.0.1:6379

# Memcached
MEMCACHED_HOST=127.0.0.1:11211
```

### Migration (Phinx Example)

```bash
composer require --dev robmorgan/phinx
./vendor/bin/phinx init
./vendor/bin/phinx create MyMigration
./vendor/bin/phinx migrate
```

---

## Development Server

### PHP Built-in Server

```bash
# Start on port 8080
php -S 127.0.0.1:8080 -t public

# Start with Xdebug enabled
php -dzend_extension=xdebug.so -S 127.0.0.1:8080 -t public
```

### malt Server

```bash
# Choose Apache / Nginx and start
malt start apache   # http://127.0.0.1:8080
malt start nginx    # http://127.0.0.1:80

# Check services
malt status

# Start/stop all services
malt start
malt stop

# Individual stop
malt stop apache
malt stop nginx
```

---

## Troubleshooting

### Port Conflicts

```bash
# macOS/Linux
lsof -i :3306
netstat -tulpn | grep :3306

# Kill the process
kill -9 <PID>
```

### PHP Configuration Check

```bash
php --ini     # Loaded configuration
php -m        # Loaded modules
```

### MySQL Connection Errors

```bash
# Connection test
mysql -h 127.0.0.1 -P 3306 -u root -p

# Linux service status
sudo systemctl status mysql

# Error logs
sudo tail -f /var/log/mysql/error.log
```

### malt-specific Issues

```bash
# Status check
malt status

# Reset configuration
malt stop
rm -rf malt/
malt create
malt start
```

### .gitignore for Team Development

```
malt/logs/
malt/data/
malt/tmp/
```

---

## CI/CD (GitHub Actions Example)

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
        options: >-
          --health-cmd="mysqladmin ping" 
          --health-interval=10s 
          --health-timeout=5s 
          --health-retries=3

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, xml, pdo_mysql, mysqli, intl, curl, zip

      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Run tests
        run: ./vendor/bin/phpunit
```

---

## Environment Selection Guidelines (Summary)

Development environments prioritize transparency and direct access, while production environments prioritize reproducibility and monitoring capabilities.

* **Daily Development & Learning**: malt (instant `mysql@3306`, visible configuration, fast file access)
* **Team Development**: malt (configuration sharing) or Docker (reproducibility priority)
* **Production & CI/CD**: **Docker only** (same behavior anywhere, rich monitoring tool ecosystem)
* **Complex Configurations**: Docker (assuming integration and scale of dependent services)

- Docker: Well-known. Container-mediated access, complex configuration inspection, virtualization overhead

- Docker: Identical behavior anywhere, rich monitoring tool ecosystem - essentially Docker only

- **Team Development**: malt (with config sharing) or Docker
- **Production/CI/CD**: Docker only
- **Complex Configurations**: Docker

> Follow your project's tutorials and team conventions for detailed configuration and BEAR.Sunday best practices.