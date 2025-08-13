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

# Slow query log
slow_query_log = 1
slow_query_log_file = malt/logs/mysql_slow.log
long_query_time = 2

[mysqldump]
quick
max_allowed_packet = 16M
```

**Configuration Details:**

| Setting | Description | Dev Recommended | Production Recommended |
|---------|-------------|-----------------|------------------------|
| `innodb_buffer_pool_size` | InnoDB data and index cache | 128M-512M | 50-80% of RAM |
| `innodb_log_file_size` | Transaction log size | 64M | 256M-1G |
| `innodb_flush_log_at_trx_commit` | Log flush frequency<br>0: Every second<br>1: Every transaction<br>2: Every transaction (OS managed) | 2 (fast) | 1 (safe) |
| `query_cache_size` | Query cache size (MySQL 5.7 and earlier) | 32M | 64M-256M |
| `max_connections` | Maximum concurrent connections | 100 | 200-1000 |
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

---

## Docker Configuration Details

### Comprehensive docker-compose.yml

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
    volumes:
      - .:/var/www/html
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
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
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 3
    networks:
      - bear-network

volumes:
  mysql_data:
  redis_data:

networks:
  bear-network:
    driver: bridge
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

; Performance
memory_limit = 128M
max_execution_time = 30
realpath_cache_size = 16M
realpath_cache_ttl = 3600

; OPcache (production optimized)
opcache.enable = 1
opcache.memory_consumption = 512
opcache.interned_strings_buffer = 64
opcache.max_accelerated_files = 32531   # Production optimized value
opcache.revalidate_freq = 60
opcache.validate_timestamps = 0
opcache.save_comments = 0
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
innodb_dedicated_server = ON        # MySQL 8.0.3+ server-dedicated optimization

# Connection settings
max_connections = 500
thread_cache_size = 50
table_open_cache = 4000

# Binary logging
log-bin = mysql-bin
binlog_format = ROW
expire_logs_days = 7
```

---

## Performance Monitoring

### Xdebug Profiling

```bash
# Run profiling
php -dzend_extension=xdebug.so \
    -dxdebug.mode=profile \
    -dxdebug.output_dir=/tmp/xdebug \
    script.php
```

### XHProf Example

```php
<?php
// Start profiling
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