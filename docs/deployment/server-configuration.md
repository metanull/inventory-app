---
layout: default
title: Server Configuration
parent: Deployment Guide
nav_order: 4
---

# Server Configuration Guide

{: .no_toc }

Detailed web server configuration for Apache and Nginx, including security, performance, and Laravel-specific optimizations.

## Table of Contents

{: .no_toc .text-delta }

1. TOC
   {:toc}

---

## Apache Configuration

### Basic Virtual Host Setup

The `deployment/apache.conf` and `deployment/apache-windows.conf` files provide ready-to-use configurations. Here's how to customize them for your environment:

#### Linux/Unix Setup

```apache
<VirtualHost *:80>
    ServerName inventory-app.your-domain.com
    ServerAlias www.inventory-app.your-domain.com
    DocumentRoot /var/www/inventory-app/public

    <Directory /var/www/inventory-app/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks

        # Laravel URL rewriting
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/inventory-app-error.log
    CustomLog ${APACHE_LOG_DIR}/inventory-app-access.log combined
</VirtualHost>
```

#### Windows Setup

```apache
<VirtualHost *:80>
    ServerName inventory-app.your-domain.com
    DocumentRoot "C:/inetpub/wwwroot/inventory-app/public"

    <Directory "C:/inetpub/wwwroot/inventory-app/public">
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks

        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>

    ErrorLog "C:/Apache24/logs/inventory-app-error.log"
    CustomLog "C:/Apache24/logs/inventory-app-access.log" combined
</VirtualHost>
```

### SSL/HTTPS Configuration

#### Production SSL Setup

```apache
<VirtualHost *:443>
    ServerName inventory-app.your-domain.com
    DocumentRoot "/var/www/inventory-app/public"

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/chain.crt

    # Modern SSL configuration
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384
    SSLHonorCipherOrder off
    SSLSessionTickets off

    # HSTS
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"

    # Directory configuration (same as HTTP)
    <Directory "/var/www/inventory-app/public">
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks

        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
</VirtualHost>

# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName inventory-app.your-domain.com
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
</VirtualHost>
```

### Performance Optimization

#### Enable Compression

```apache
# Load required modules
LoadModule deflate_module modules/mod_deflate.so
LoadModule filter_module modules/mod_filter.so

# Compression configuration
<Location />
    SetOutputFilter DEFLATE
    SetEnvIfNoCase Request_URI \
        \.(?:gif|jpe?g|png)$ no-gzip dont-vary
    SetEnvIfNoCase Request_URI \
        \.(?:exe|t?gz|zip|bz2|sit|rar)$ no-gzip dont-vary
    SetEnvIfNoCase Request_URI \
        \.pdf$ no-gzip dont-vary
    SetEnvIfNoCase Request_URI \
        \.avi$ no-gzip dont-vary
</Location>
```

#### Static File Caching

```apache
# Load expires module
LoadModule expires_module modules/mod_expires.so

# Cache static assets
<LocationMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 year"
    Header append Cache-Control "public, immutable"
</LocationMatch>

# Cache HTML with shorter expiry
<LocationMatch "\.html$">
    ExpiresActive On
    ExpiresDefault "access plus 1 hour"
    Header append Cache-Control "public"
</LocationMatch>
```

### Security Configuration

#### Security Headers

```apache
# Load headers module
LoadModule headers_module modules/mod_headers.so

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"

# Content Security Policy
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'"
```

#### File Access Restrictions

```apache
# Prevent access to sensitive files
<Files ~ "^\.">
    Require all denied
</Files>

<FilesMatch "\.(md|json|lock|yml|yaml|env|log)$">
    Require all denied
</FilesMatch>

# Prevent access to directories
<DirectoryMatch "(storage|bootstrap/cache|vendor|node_modules|\.git)">
    Require all denied
</DirectoryMatch>
```

### PHP Configuration for Apache

#### Using PHP-FPM (Recommended)

```apache
# Load proxy modules
LoadModule proxy_module modules/mod_proxy.so
LoadModule proxy_fcgi_module modules/mod_proxy_fcgi.so

# PHP-FPM configuration
<FilesMatch \.php$>
    SetHandler "proxy:unix:/var/run/php/php8.2-fpm.sock|fcgi://localhost"
</FilesMatch>

# Windows PHP-FPM
<FilesMatch \.php$>
    SetHandler "proxy:fcgi://127.0.0.1:9000"
</FilesMatch>
```

#### Using PHP Module (Alternative)

```apache
# Load PHP module
LoadModule php_module modules/libphp8.so

# PHP configuration
<FilesMatch \.php$>
    SetHandler application/x-httpd-php
</FilesMatch>

# PHP ini settings
php_value upload_max_filesize 64M
php_value post_max_size 64M
php_value memory_limit 256M
php_value max_execution_time 300
```

## Nginx Configuration

### Basic Server Block

The `deployment/nginx.conf` file provides a complete configuration. Here's the core setup:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name inventory-app.your-domain.com www.inventory-app.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;

    server_name inventory-app.your-domain.com www.inventory-app.your-domain.com;
    root /var/www/inventory-app/public;
    index index.php index.html index.htm;

    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    # Laravel URL rewriting
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handling
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### SSL Configuration

#### Modern SSL Setup

```nginx
# SSL Configuration
ssl_protocols TLSv1.2 TLSv1.3;
ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
ssl_prefer_server_ciphers off;
ssl_session_cache shared:SSL:10m;
ssl_session_timeout 1d;
ssl_session_tickets off;

# OCSP Stapling
ssl_stapling on;
ssl_stapling_verify on;
ssl_trusted_certificate /path/to/chain.crt;
resolver 8.8.8.8 8.8.4.4 valid=300s;
resolver_timeout 5s;
```

### Performance Optimization

#### Gzip Compression

```nginx
# Gzip Settings
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_proxied any;
gzip_comp_level 6;
gzip_types
    text/plain
    text/css
    text/xml
    text/javascript
    application/json
    application/javascript
    application/xml+rss
    application/atom+xml
    image/svg+xml;
```

#### Static File Caching

```nginx
# Cache static assets
location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    add_header X-Content-Type-Options nosniff;
    access_log off;
}

# Cache HTML files
location ~* \.html$ {
    expires 1h;
    add_header Cache-Control "public";
}
```

#### Buffer Optimization

```nginx
# Buffer settings
client_body_buffer_size 128k;
client_max_body_size 64m;
client_header_buffer_size 1k;
large_client_header_buffers 4 4k;
output_buffers 1 32k;
postpone_output 1460;
```

### Security Configuration

#### Security Headers

```nginx
# Security headers
add_header X-Content-Type-Options nosniff always;
add_header X-Frame-Options DENY always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;

# Content Security Policy
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'" always;
```

#### File Access Restrictions

```nginx
# Prevent access to sensitive files
location ~ /\. {
    deny all;
    access_log off;
    log_not_found off;
}

location ~* \.(md|json|lock|yml|yaml|env|log)$ {
    deny all;
    access_log off;
    log_not_found off;
}

# Prevent access to directories
location ~* /(storage|bootstrap/cache|vendor|node_modules|\.git)/ {
    deny all;
    access_log off;
    log_not_found off;
}
```

### Rate Limiting

#### API Rate Limiting

```nginx
# In http block (nginx.conf)
http {
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_zone $binary_remote_addr zone=login:10m rate=1r/s;

    # In server block
    location /api {
        limit_req zone=api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    location /api/login {
        limit_req zone=login burst=5 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

## PHP-FPM Configuration

### Pool Configuration

Create a dedicated pool for the application:

```ini
; /etc/php/8.2/fpm/pool.d/inventory-app.conf
[inventory-app]
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm-inventory-app.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

; Process management
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

; Resource limits
request_terminate_timeout = 300
rlimit_files = 65536
rlimit_core = 0

; Logging
php_admin_value[error_log] = /var/log/php8.2-fpm-inventory-app.log
php_admin_flag[log_errors] = on

; Environment variables
env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /tmp
env[TMPDIR] = /tmp
env[TEMP] = /tmp
```

### Performance Tuning

```ini
; Performance settings
pm.status_path = /status
ping.path = /ping
ping.response = pong

; Process limits
pm.process_idle_timeout = 10s
pm.max_requests = 1000

; Memory limits
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[max_input_time] = 300
```

## Load Balancing

### Nginx Load Balancer

```nginx
upstream inventory_app {
    least_conn;
    server 192.168.1.10:80 weight=3;
    server 192.168.1.11:80 weight=2;
    server 192.168.1.12:80 backup;
}

server {
    listen 80;
    server_name inventory-app.your-domain.com;

    location / {
        proxy_pass http://inventory_app;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Apache Load Balancer

```apache
# Load balancing modules
LoadModule proxy_module modules/mod_proxy.so
LoadModule proxy_balancer_module modules/mod_proxy_balancer.so
LoadModule proxy_http_module modules/mod_proxy_http.so
LoadModule lbmethod_byrequests_module modules/mod_lbmethod_byrequests.so

# Define balancer
<Proxy balancer://inventory-app>
    BalancerMember http://192.168.1.10:80 route=app1
    BalancerMember http://192.168.1.11:80 route=app2
    BalancerMember http://192.168.1.12:80 route=app3 status=+H
    ProxySet lbmethod byrequests
</Proxy>

<VirtualHost *:80>
    ServerName inventory-app.your-domain.com
    ProxyPass / balancer://inventory-app/
    ProxyPassReverse / balancer://inventory-app/
</VirtualHost>
```

## Monitoring and Logging

### Access Log Analysis

#### Apache Log Format

```apache
LogFormat "%h %l %u %t \"%r\" %>s %O \"%{Referer}i\" \"%{User-Agent}i\" %D" combined_with_time
CustomLog /var/log/apache2/inventory-app-access.log combined_with_time
```

#### Nginx Log Format

```nginx
log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                '$status $body_bytes_sent "$http_referer" '
                '"$http_user_agent" "$http_x_forwarded_for" '
                '$request_time $upstream_response_time';

access_log /var/log/nginx/inventory-app-access.log main;
```

### Error Monitoring

#### Log Rotation

```bash
# /etc/logrotate.d/inventory-app
/var/log/apache2/inventory-app-*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload apache2
    endscript
}
```

### Health Checks

#### Nginx Health Check

```nginx
location /health {
    access_log off;
    return 200 "healthy\n";
    add_header Content-Type text/plain;
}
```

#### Apache Health Check

```apache
<Location "/health">
    SetHandler server-info
    Require local
</Location>
```

## Security Best Practices

### Firewall Configuration

#### UFW (Ubuntu)

```bash
# Allow SSH
sudo ufw allow 22

# Allow HTTP and HTTPS
sudo ufw allow 80
sudo ufw allow 443

# Deny all other incoming traffic
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Enable firewall
sudo ufw enable
```

#### Windows Firewall

```powershell
# Allow HTTP
netsh advfirewall firewall add rule name="HTTP" dir=in action=allow protocol=TCP localport=80

# Allow HTTPS
netsh advfirewall firewall add rule name="HTTPS" dir=in action=allow protocol=TCP localport=443
```

### Fail2Ban Configuration

```ini
# /etc/fail2ban/jail.local
[apache-auth]
enabled = true
port = http,https
filter = apache-auth
logpath = /var/log/apache2/inventory-app-error.log
maxretry = 5
bantime = 3600

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
action = iptables-multiport[name=ReqLimit, port="http,https", protocol=tcp]
logpath = /var/log/nginx/inventory-app-error.log
findtime = 600
bantime = 7200
maxretry = 10
```

---

## Troubleshooting

### Common Issues

1. **502 Bad Gateway (Nginx)**
   - Check PHP-FPM status: `systemctl status php8.2-fpm`
   - Verify socket permissions
   - Check PHP-FPM logs

2. **500 Internal Server Error**
   - Check web server error logs
   - Verify file permissions
   - Check PHP error logs

3. **Static Files Not Loading**
   - Verify build process completed
   - Check file permissions
   - Verify web server configuration

### Performance Issues

1. **Slow Response Times**
   - Enable OPcache
   - Optimize database queries
   - Enable compression

2. **High Memory Usage**
   - Tune PHP-FPM pool settings
   - Increase server resources
   - Optimize application code

---

## Next Steps

- 📖 [Configuration Guide](configuration) - Application configuration
- [Development Setup](development-setup) - Local development
- 🔒 [Security Guide](security) - Security best practices
- [Monitoring](monitoring) - Application monitoring
