---
layout: docs-ja
title: 環境構築詳細リファレンス
category: Manual
permalink: /manuals/1.0/ja/setup-reference.html
---

# 環境構築詳細リファレンス

この詳細リファレンスでは、BEAR.Sunday 開発環境の各設定ファイルとオプションについて包括的に解説します。

---

## malt 設定詳細

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

**主要設定項目：**

- `services.php.version`: PHP バージョン (8.1, 8.2, 8.3, 8.4)
- `services.php.extensions`: 自動インストールする PHP 拡張
- `services.mysql.port`: MySQL ポート（デフォルト 3306）
- `services.redis.port`: Redis ポート（デフォルト 6379）
- `env`: プロジェクト固有の環境変数

### MySQL 設定 (my_3306.cnf)

```ini
[client]
port = 3306
socket = /tmp/mysql_3306.sock
default-character-set = utf8mb4

[mysql]
default-character-set = utf8mb4

[mysqld]
# 基本設定
port = 3306
socket = /tmp/mysql_3306.sock
datadir = malt/data/mysql
pid-file = malt/tmp/mysql_3306.pid
log-error = malt/logs/mysql_error.log

# 文字セット
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# パフォーマンス設定
innodb_buffer_pool_size = 128M      # InnoDB バッファプールサイズ
innodb_log_file_size = 64M          # InnoDB ログファイルサイズ（注：MySQL 8.0.30+ではinnodb_redo_log_capacityを使用）
innodb_flush_log_at_trx_commit = 2  # ログフラッシュ設定（開発用）
innodb_dedicated_server = OFF       # 開発環境では通常無効

# クエリキャッシュ（MySQL 8.0で廃止、5.7以前のみ）
# query_cache_type = 1              # クエリキャッシュ有効
# query_cache_size = 32M            # クエリキャッシュサイズ

# 接続設定
max_connections = 100               # 最大接続数
wait_timeout = 600                  # 待機タイムアウト
interactive_timeout = 600           # インタラクティブタイムアウト

# バイナリログ（レプリケーション用、開発では通常無効）
# log-bin = mysql-bin
# server-id = 1

# スロークエリログ
slow_query_log = 1
slow_query_log_file = malt/logs/mysql_slow.log
long_query_time = 2

# 一般クエリログ（デバッグ用、パフォーマンス影響大）
# general_log = 1
# general_log_file = malt/logs/mysql_general.log

[mysqldump]
quick
max_allowed_packet = 16M

[mysql]
no-auto-rehash
```

**設定項目詳細：**

| 項目 | 説明 | 開発推奨値 | 本番推奨値 |
|------|------|------------|------------|
| `innodb_buffer_pool_size` | InnoDB データとインデックスをキャッシュ | 128M-512M | RAM の 50-80% |
| `innodb_log_file_size` | トランザクションログサイズ | 64M | 256M-1G |
| `innodb_flush_log_at_trx_commit` | ログフラッシュ頻度<br>0: 1秒毎<br>1: 毎トランザクション<br>2: 毎トランザクション（OS任せ） | 2 (高速) | 1 (安全) |
| `query_cache_size` | クエリキャッシュサイズ（MySQL 5.7まで） | 32M | 64M-256M |
| `max_connections` | 最大同時接続数 | 100 | 200-1000 |
| `slow_query_log` | スロークエリログ記録 | 1 (有効) | 1 (有効) |
| `long_query_time` | スロークエリ判定時間（秒） | 2 | 1 |

### PHP 設定 (php.ini)

```ini
[PHP]
; エラー表示（開発用）
display_errors = On
display_startup_errors = On
log_errors = On
error_log = malt/logs/php_error.log
error_reporting = E_ALL

; リソース制限
memory_limit = 256M
max_execution_time = 30
max_input_time = 60
upload_max_filesize = 64M
post_max_size = 64M
max_file_uploads = 20

; パフォーマンス
realpath_cache_size = 4M
realpath_cache_ttl = 600
opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 8000     # PHP 8.4推奨値（増加）
opcache.revalidate_freq = 2
opcache.save_comments = 1
opcache.enable_file_override = 0

; セッション
session.save_handler = files
session.save_path = malt/tmp/sessions
session.gc_maxlifetime = 1440
session.cookie_httponly = 1
session.cookie_secure = 0    # 開発用（HTTPSでは1に設定）
session.cookie_samesite = Lax    # 本番ではStrictを推奨

; 日本語設定
default_charset = UTF-8
mbstring.language = Japanese
mbstring.internal_encoding = UTF-8
date.timezone = Asia/Tokyo

[xdebug]
; Xdebug 3.x 設定（パフォーマンス影響のため通常はコメントアウト）
; xdebug.mode = debug
; xdebug.start_with_request = yes
; xdebug.client_host = 127.0.0.1
; xdebug.client_port = 9003
; xdebug.log = malt/logs/xdebug.log

[xhprof]
; XHProf 設定（パフォーマンス影響のため通常はコメントアウト）
; xhprof.output_dir = malt/tmp/xhprof
```

**設定項目詳細：**

| 項目 | 説明 | 開発推奨値 | 本番推奨値 |
|------|------|------------|------------|
| `memory_limit` | PHP スクリプトの最大メモリ使用量 | 256M | 128M-512M |
| `max_execution_time` | スクリプト最大実行時間（秒） | 30 | 30 |
| `upload_max_filesize` | アップロード可能な最大ファイルサイズ | 64M | 用途に応じて |
| `opcache.memory_consumption` | OPcache メモリ使用量（MB） | 128 | 256-512 |
| `opcache.revalidate_freq` | ファイル更新チェック間隔（秒） | 2 | 60 |
| `realpath_cache_size` | パスキャッシュサイズ | 4M | 16M |
| `session.gc_maxlifetime` | セッション有効期限（秒） | 1440 | 1440 |

### Apache 設定 (httpd_8080.conf)

```apache
# 基本設定
ServerRoot malt/apache
PidFile malt/tmp/httpd_8080.pid
Listen 127.0.0.1:8080
ServerName localhost:8080

# モジュール（パスは環境に応じて調整）
# macOS Homebrew の例: /opt/homebrew/lib/httpd/modules/
# Ubuntu/CentOS の例: /usr/lib/apache2/modules/ または /usr/lib64/httpd/modules/
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule php_module modules/libphp.so
LoadModule dir_module modules/mod_dir.so

# PHP
<FilesMatch \.php$>
    SetHandler application/x-httpd-php
</FilesMatch>

# ドキュメントルート
DocumentRoot "public"
<Directory "public">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
    
    # URL書き換え
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</Directory>

# ログ
ErrorLog malt/logs/apache_error.log
CustomLog malt/logs/apache_access.log combined
LogLevel warn

# セキュリティ
ServerTokens Prod
ServerSignature Off

# パフォーマンス
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript application/json
</IfModule>

# キャッシュ
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

### Nginx 設定 (nginx_80.conf)

```nginx
# 基本設定
# ユーザーは環境に応じて調整（Ubuntu: www-data, CentOS: nginx, macOS: _www）
user www-data;  # または nginx, _www など
worker_processes auto;
pid malt/tmp/nginx.pid;
error_log malt/logs/nginx_error.log;

events {
    worker_connections 1024;
    use epoll;
}

http {
    # 基本設定
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 64M;
    
    # MIME タイプ
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    
    # ログ形式
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                   '$status $body_bytes_sent "$http_referer" '
                   '"$http_user_agent" "$http_x_forwarded_for"';
    
    access_log malt/logs/nginx_access.log main;
    
    # Gzip 圧縮
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
    
    # サーバー設定
    server {
        listen 127.0.0.1:80;
        server_name localhost;
        root public;
        index index.php index.html;
        
        # アクセスログ
        access_log malt/logs/nginx_site_access.log;
        error_log malt/logs/nginx_site_error.log;
        
        # PHP処理
        location ~ \.php$ {
            try_files $uri =404;
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
        
        # URL書き換え（BEAR.Sunday）
        location / {
            try_files $uri $uri/ @rewrite;
        }
        
        location @rewrite {
            rewrite ^(.*)$ /index.php last;
        }
        
        # 静的ファイルキャッシュ
        location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
            expires 1M;
            add_header Cache-Control "public, immutable";
        }
        
        # セキュリティ
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

## Docker 設定詳細

### docker-compose.yml（本格版）

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

  # 開発用ツール
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

# システム依存関係のインストール
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

# PHP 拡張のインストール
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

# Redis 拡張
RUN pecl install redis \
    && docker-php-ext-enable redis

# APCu 拡張
RUN pecl install apcu \
    && docker-php-ext-enable apcu

# Xdebug（開発時のみ）
ARG INSTALL_XDEBUG=false
RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    pecl install xdebug \
    && docker-php-ext-enable xdebug; \
fi

# Composer のインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリ設定
WORKDIR /var/www/html

# ユーザー設定
RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S www -G www

# 権限設定
RUN chown -R www:www /var/www/html
USER www

EXPOSE 9000

CMD ["php-fpm"]
```

---

## 本番環境最適化設定

### PHP 本番設定

```ini
[PHP]
; セキュリティ
expose_php = Off
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; パフォーマンス
memory_limit = 128M
max_execution_time = 30
realpath_cache_size = 16M
realpath_cache_ttl = 3600

; OPcache（本番最適化）
opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 512
opcache.interned_strings_buffer = 64
opcache.max_accelerated_files = 32531
opcache.revalidate_freq = 60
opcache.validate_timestamps = 0
opcache.save_comments = 0
opcache.enable_file_override = 1

; セッション（Redis使用）
session.save_handler = redis
session.save_path = "tcp://127.0.0.1:6379"
session.gc_maxlifetime = 3600
session.cookie_secure = 1
session.cookie_httponly = 1
session.cookie_samesite = Strict
session.use_strict_mode = 1      # セッションIDの厳密モード
```

### MySQL 本番設定

```ini
[mysqld]
# パフォーマンス最適化
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M         # MySQL 8.0.30+ではinnodb_redo_log_capacityを使用
innodb_redo_log_capacity = 512M     # MySQL 8.0.30+ 新しいredo log設定
innodb_flush_log_at_trx_commit = 1
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1
innodb_dedicated_server = ON        # MySQL 8.0.3+ サーバー専用最適化

# 接続設定
max_connections = 500
thread_cache_size = 50
table_open_cache = 4000

# クエリキャッシュ（MySQL 5.7まで、8.0では廃止）
# query_cache_type = 1
# query_cache_size = 128M

# バイナリログ
log-bin = mysql-bin
binlog_format = ROW
expire_logs_days = 7

# セキュリティ
skip-name-resolve
local-infile = 0
```

---

## パフォーマンス監視設定

### New Relic 設定例

```ini
; New Relic PHP エージェント
extension = newrelic.so
newrelic.license = "YOUR_LICENSE_KEY"
newrelic.appname = "BEAR.Sunday App"
newrelic.daemon.address = "/tmp/.newrelic.sock"
newrelic.logfile = "/var/log/newrelic/php_agent.log"
newrelic.loglevel = "info"
```

### Xdebug プロファイリング設定

```bash
# プロファイリング実行例
php -dzend_extension=xdebug.so \
    -dxdebug.mode=profile \
    -dxdebug.output_dir=/tmp/xdebug \
    -dxdebug.profiler_output_name=cachegrind.out.%p \
    script.php
```

### XHProf 設定例

```php
<?php
// XHProf プロファイリング開始
if (extension_loaded('xhprof')) {
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}

// アプリケーション実行
// ...

// プロファイリング結果保存
if (extension_loaded('xhprof')) {
    $xhprof_data = xhprof_disable();
    $xhprof_runs = new XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, "myapp");
    echo "XHProf Run ID: {$run_id}\n";
}
?>
```

---

## トラブルシューティング詳細

### ログ分析

```bash
# PHP エラーログ分析
tail -f malt/logs/php_error.log | grep -E "(Fatal|Error|Warning)"

# MySQL スロークエリ分析
mysqldumpslow -s t -t 10 malt/logs/mysql_slow.log

# Apache/Nginx アクセスログ分析
awk '{print $1}' malt/logs/apache_access.log | sort | uniq -c | sort -nr
```

### パフォーマンス測定

```bash
# Apache Bench テスト
ab -n 1000 -c 10 http://localhost:8080/

# シンプルな負荷テスト
for i in {1..100}; do
    curl -w "@curl-format.txt" -o /dev/null -s http://localhost:8080/
done
```

この詳細リファレンスは、開発チームが BEAR.Sunday 環境を深く理解し、適切にカスタマイズするための包括的なガイドです。