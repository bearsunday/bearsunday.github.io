---
layout: docs-en
title: Environment Setup - Detailed Reference
category: Manual
permalink: /manuals/1.0/en/setup-reference.html
---

# Environment Setup - Detailed Reference

This detailed reference provides comprehensive documentation for all configuration files and options in BEAR.Sunday development environments.

---

## malt Configuration Details

### malt.json

```json
{
  "services": {
    "php": {
      "version": "8.4",
      "extensions": ["mysql", "redis", "apcu"],
      "ini": {
        "memory_limit": "256M",
        "max_execution_time": "30",
        "display_errors": "On",
        "error_reporting": "E_ALL"
      }
    },
    "mysql": {
      "version": "8.0",
      "port": 3306,
      "data_dir": "malt/data/mysql",
      "config": "malt/conf/my_3306.cnf"
    },
    "redis": {
      "version": "latest",
      "port": 6379,
      "data_dir": "malt/data/redis"
    },
    "apache": {
      "version": "2.4",
      "port": 8080,
      "config": "malt/conf/httpd_8080.conf",
      "document_root": "public"
    },
    "nginx": {
      "version": "latest", 
      "port": 80,
      "config": "malt/conf/nginx_80.conf",
      "document_root": "public"
    }
  },
  "env": {
    "PATH": "malt/bin:${PATH}",
    "DB_HOST": "127.0.0.1",
    "DB_PORT": "3306",
    "REDIS_HOST": "127.0.0.1:6379"
  }
}
```

**Key Configuration Items:**

- `services.php.version`: PHP version (8.1, 8.2, 8.3, 8.4)
- `services.php.extensions`: PHP extensions to auto-install
- `services.mysql.port`: MySQL port (default 3306)
- `services.redis.port`: Redis port (default 6379)
- `env`: Project-specific environment variables

### MySQL Configuration (my_3306.cnf)

```ini
[client]
port = 3306
socket = /tmp/mysql_3306.sock
default-character-set = utf8mb4

[mysql]
default-character-set = utf8mb4

[mysqld]
# Basic settings
port = 3306
socket = /tmp/mysql_3306.sock
datadir = malt/data/mysql
pid-file = malt/tmp/mysql_3306.pid
log-error = malt/logs/mysql_error.log

# Character set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Performance settings
innodb_buffer_pool_size = 128M      # InnoDB buffer pool size
innodb_log_file_size = 64M          # InnoDB log file size (Note: Use innodb_redo_log_capacity for MySQL 8.0.30+)
innodb_flush_log_at_trx_commit = 2  # Log flush setting (dev)
innodb_dedicated_server = OFF       # Usually disabled in development environment

# Query cache (removed in MySQL 8.0, only for 5.7 and earlier)
# query_cache_type = 1              # Query cache enabled
# query_cache_size = 32M            # Query cache size

# Connection settings
max_connections = 100               # Maximum connections
wait_timeout = 600                  # Wait timeout
interactive_timeout = 600           # Interactive timeout

# Binary log (for replication, usually disabled in development)
# log-bin = mysql-bin
# server-id = 1

# Slow query log
slow_query_log = 1
slow_query_log_file = malt/logs/mysql_slow.log
long_query_time = 2

# General query log (for debugging, large performance impact)
# general_log = 1
# general_log_file = malt/logs/mysql_general.log

[mysqldump]
quick
max_allowed_packet = 16M

[mysql]
no-auto-rehash
```

**Configuration Details:**

| Setting | Description | Dev Recommended | Production Recommended |
|---------|-------------|-----------------|------------------------|
| `innodb_buffer_pool_size` | InnoDB data and index cache | 128M-512M | 50-80% of RAM |
| `innodb_log_file_size` | Transaction log size | 64M | 256M-1G |
| `innodb_flush_log_at_trx_commit` | Log flush frequency<br>0: Every second<br>1: Every transaction<br>2: Every transaction (OS managed) | 2 (fast) | 1 (safe) |
| `query_cache_size` | Query cache size (MySQL 5.7 and earlier) | 32M | 64M-256M |
| `max_connections` | Maximum concurrent connections | 100 | 200-1000 |
| `slow_query_log` | Slow query log recording | 1 (enabled) | 1 (enabled) |
| `long_query_time` | Slow query threshold (seconds) | 2 | 1 |

### PHP Configuration (php.ini)

```ini
[PHP]
; Error display (development)
display_errors = On
display_startup_errors = On
log_errors = On
error_log = malt/logs/php_error.log
error_reporting = E_ALL

; Resource limits
memory_limit = 256M
max_execution_time = 30
max_input_time = 60
upload_max_filesize = 64M
post_max_size = 64M
max_file_uploads = 20

; Performance
realpath_cache_size = 4M
realpath_cache_ttl = 600
opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 8000     # PHP 8.4 recommended value (increased)
opcache.revalidate_freq = 2
opcache.save_comments = 1
opcache.enable_file_override = 0

; Sessions
session.save_handler = files
session.save_path = malt/tmp/sessions
session.gc_maxlifetime = 1440
session.cookie_httponly = 1
session.cookie_secure = 0    # For development (set to 1 for HTTPS)
session.cookie_samesite = Lax    # Strict recommended for production

; Japanese settings
default_charset = UTF-8
mbstring.language = Japanese
mbstring.internal_encoding = UTF-8
date.timezone = Asia/Tokyo

[xdebug]
; Xdebug 3.x settings (usually commented out for performance)
; xdebug.mode = debug
; xdebug.start_with_request = yes
; xdebug.client_host = 127.0.0.1
; xdebug.client_port = 9003
; xdebug.log = malt/logs/xdebug.log

[xhprof]
; XHProf settings (usually commented out for performance)
; xhprof.output_dir = malt/tmp/xhprof
```

**Configuration Details:**

| Setting | Description | Dev Recommended | Production Recommended |
|---------|-------------|-----------------|------------------------|
| `memory_limit` | Maximum memory usage per script | 256M | 128M-512M |
| `max_execution_time` | Maximum script execution time (seconds) | 30 | 30 |
| `upload_max_filesize` | Maximum upload file size | 64M | As needed |
| `opcache.memory_consumption` | OPcache memory usage (MB) | 128 | 256-512 |
| `opcache.revalidate_freq` | File update check interval (seconds) | 2 | 60 |
| `realpath_cache_size` | Path cache size | 4M | 16M |
| `session.gc_maxlifetime` | Session lifetime (seconds) | 1440 | 1440 |

### Apache Configuration (httpd_8080.conf)

```apache
# Basic settings
ServerRoot malt/apache
PidFile malt/tmp/httpd_8080.pid
Listen 127.0.0.1:8080
ServerName localhost:8080

# Modules (adjust paths based on your environment)
# macOS Homebrew example: /opt/homebrew/lib/httpd/modules/
# Ubuntu/CentOS example: /usr/lib/apache2/modules/ or /usr/lib64/httpd/modules/
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule php_module modules/libphp.so
LoadModule dir_module modules/mod_dir.so

# PHP
<FilesMatch \.php$>
    SetHandler application/x-httpd-php
</FilesMatch>

# Document root
DocumentRoot "public"
<Directory "public">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
    
    # URL rewriting
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</Directory>

# Logs
ErrorLog malt/logs/apache_error.log
CustomLog malt/logs/apache_access.log combined
LogLevel warn

# Security
ServerTokens Prod
ServerSignature Off

# Performance
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript application/json
</IfModule>

# Cache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
</IfModule>
```

### Nginx Configuration (nginx_80.conf)

```nginx
# Basic settings
# Adjust user based on your environment (Ubuntu: www-data, CentOS: nginx, macOS: _www)
user www-data;  # or nginx, _www, etc.
worker_processes auto;
pid malt/tmp/nginx.pid;
error_log malt/logs/nginx_error.log;

events {
    worker_connections 1024;
    use epoll;
}

http {
    # Basic settings
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 64M;
    
    # MIME types
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    
    # Log format
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                   '$status $body_bytes_sent "$http_referer" '
                   '"$http_user_agent" "$http_x_forwarded_for"';
    
    access_log malt/logs/nginx_access.log main;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1000;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/json
        application/xml+rss;
    
    # Server settings
    server {
        listen 127.0.0.1:80;
        server_name localhost;
        root public;
        index index.php index.html;
        
        # Access logs
        access_log malt/logs/nginx_site_access.log;
        error_log malt/logs/nginx_site_error.log;
        
        # PHP processing
        location ~ \.php$ {
            try_files $uri =404;
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
        
        # URL rewriting (BEAR.Sunday)
        location / {
            try_files $uri $uri/ @rewrite;
        }
        
        location @rewrite {
            rewrite ^(.*)$ /index.php last;
        }
        
        # Static file cache
        location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
            expires 1M;
            add_header Cache-Control "public, immutable";
        }
        
        # Security
        location ~ /\. {
            deny all;
        }
        
        location ~* \.(md|lock|json|xml|yml|yaml|log)$ {
            deny all;
        }
    }
}
```

---

## Docker Configuration Details

### docker-compose.yml (Comprehensive)

```yaml
version: '3.8'

services:
  php:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    volumes:
      - .:/var/www/html
      - ./docker/php/php.ini:/usr/local/etc/php/conf.d/custom.ini
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_started
    environment:
      - DB_HOST=mysql
      - DB_PORT=3306
      - REDIS_HOST=redis:6379
    networks:
      - bear-network

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/var/www/html
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - ./docker/nginx/ssl:/etc/nginx/ssl
    depends_on:
      - php
    networks:
      - bear-network

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-secret}
      MYSQL_DATABASE: ${DB_NAME:-myapp}
      MYSQL_USER: ${DB_USER:-user}
      MYSQL_PASSWORD: ${DB_PASSWORD:-password}
    ports:
      - "${DB_PORT:-3306}:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/custom.cnf
      - ./docker/mysql/init:/docker-entrypoint-initdb.d
    command: >
      --character-set-server=utf8mb4
      --collation-server=utf8mb4_unicode_ci
      --default-authentication-plugin=mysql_native_password
      --innodb-buffer-pool-size=256M
      --innodb-log-file-size=128M
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p$$MYSQL_ROOT_PASSWORD"]
      interval: 10s
      timeout: 5s
      retries: 5
      start_period: 30s
    networks:
      - bear-network

  redis:
    image: redis:alpine
    ports:
      - "${REDIS_PORT:-6379}:6379"
    volumes:
      - redis_data:/data
      - ./docker/redis/redis.conf:/usr/local/etc/redis/redis.conf
    command: ["redis-server", "/usr/local/etc/redis/redis.conf"]
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 3
    networks:
      - bear-network

  memcached:
    image: memcached:alpine
    ports:
      - "${MEMCACHED_PORT:-11211}:11211"
    command: ["-m", "64"]
    networks:
      - bear-network

  # Development tools
  phpmyadmin:
    image: phpmyadmin:latest
    environment:
      PMA_HOST: mysql
      PMA_PORT: 3306
    ports:
      - "8080:80"
    depends_on:
      mysql:
        condition: service_healthy
    networks:
      - bear-network

  redis-commander:
    image: rediscommander/redis-commander:latest
    environment:
      REDIS_HOSTS: local:redis:6379
    ports:
      - "8081:8081"
    depends_on:
      - redis
    networks:
      - bear-network

volumes:
  mysql_data:
    driver: local
  redis_data:
    driver: local

networks:
  bear-network:
    driver: bridge
```

### Docker PHP Dockerfile

```dockerfile
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    icu-dev \
    autoconf \
    g++ \
    make

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mysqli \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# APCu extension
RUN pecl install apcu \
    && docker-php-ext-enable apcu

# Xdebug (development only)
ARG INSTALL_XDEBUG=false
RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    pecl install xdebug \
    && docker-php-ext-enable xdebug; \
fi

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# User configuration
RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S www -G www

# Permissions
RUN chown -R www:www /var/www/html
USER www

EXPOSE 9000

CMD ["php-fpm"]
```

---

## Production Optimization

### PHP Production Settings

```ini
[PHP]
; Security
expose_php = Off
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; Performance
memory_limit = 128M
max_execution_time = 30
realpath_cache_size = 16M
realpath_cache_ttl = 3600

; OPcache (production optimized)
opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 512
opcache.interned_strings_buffer = 64
opcache.max_accelerated_files = 32531
opcache.revalidate_freq = 60
opcache.validate_timestamps = 0
opcache.save_comments = 0
opcache.enable_file_override = 1

; Sessions (using Redis)
session.save_handler = redis
session.save_path = "tcp://127.0.0.1:6379"
session.gc_maxlifetime = 3600
session.cookie_secure = 1
session.cookie_httponly = 1
session.cookie_samesite = Strict
session.use_strict_mode = 1      # Strict mode for session IDs
```

### MySQL Production Settings

```ini
[mysqld]
# Performance optimization
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M         # Use innodb_redo_log_capacity for MySQL 8.0.30+
innodb_redo_log_capacity = 512M     # MySQL 8.0.30+ new redo log setting
innodb_flush_log_at_trx_commit = 1
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1
innodb_dedicated_server = ON        # MySQL 8.0.3+ server-dedicated optimization

# Connection settings
max_connections = 500
thread_cache_size = 50
table_open_cache = 4000

# Query cache (MySQL 5.7 and earlier, removed in 8.0)
# query_cache_type = 1
# query_cache_size = 128M

# Binary logging
log-bin = mysql-bin
binlog_format = ROW
expire_logs_days = 7

# Security
skip-name-resolve
local-infile = 0
```

---

## Performance Monitoring

### New Relic Configuration Example

```ini
; New Relic PHP agent
extension = newrelic.so
newrelic.license = "YOUR_LICENSE_KEY"
newrelic.appname = "BEAR.Sunday App"
newrelic.daemon.address = "/tmp/.newrelic.sock"
newrelic.logfile = "/var/log/newrelic/php_agent.log"
newrelic.loglevel = "info"
```

### Xdebug Profiling

```bash
# Run profiling
php -dzend_extension=xdebug.so \
    -dxdebug.mode=profile \
    -dxdebug.output_dir=/tmp/xdebug \
    -dxdebug.profiler_output_name=cachegrind.out.%p \
    script.php
```

### XHProf Example

```php
<?php
// Start XHProf profiling
if (extension_loaded('xhprof')) {
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}

// Application execution
// ...

// Save profiling results
if (extension_loaded('xhprof')) {
    $xhprof_data = xhprof_disable();
    $xhprof_runs = new XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, "myapp");
    echo "XHProf Run ID: {$run_id}\n";
}
?>
```

---

## Troubleshooting

### Log Analysis

```bash
# PHP error log analysis
tail -f malt/logs/php_error.log | grep -E "(Fatal|Error|Warning)"

# MySQL slow query analysis
mysqldumpslow -s t -t 10 malt/logs/mysql_slow.log

# Apache/Nginx access log analysis
awk '{print $1}' malt/logs/apache_access.log | sort | uniq -c | sort -nr
```

### Performance Testing

```bash
# Apache Bench test
ab -n 1000 -c 10 http://localhost:8080/

# Simple load test
for i in {1..100}; do
    curl -w "@curl-format.txt" -o /dev/null -s http://localhost:8080/
done
```

This detailed reference provides comprehensive guidance for teams to deeply understand and properly customize their BEAR.Sunday environments.
