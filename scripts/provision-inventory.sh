#!/usr/bin/env bash
#
# provision-inventory.sh — Add inventory-app resources to the Motivya VPS
#
# Run as root (or via sudo) on the VPS AFTER motivya's provision.sh:
#   sudo bash provision-inventory.sh
#
# Prerequisites (from motivya provision.sh, already running on VPS):
#   - PHP 8.4-FPM, Nginx, MySQL, Valkey installed
#   - 'deploy' user exists (non-privileged, no sudo, in www-data group)
#   - UFW firewall configured (22, 80, 443)
#   - Certbot installed
#
# What this script does:
#   1. Creates MySQL database + user for inventory-app
#   2. Creates application directory structure (/opt/inventory/)
#   3. Configures Nginx vhost for inventory.metanull.eu
#   4. Obtains SSL certificate for inventory.metanull.eu
#   5. Creates queue worker systemd service
#   6. Sets up daily MySQL backup cron
#
# This script is idempotent — safe to re-run.
# The 'deploy' user has NO sudo. All privileged operations belong here.
#
set -euo pipefail

# --- Configuration -----------------------------------------------------------
DEPLOY_USER="deploy"
APP_DIR="/opt/inventory"
DOMAIN="inventory.metanull.eu"
PHP_VERSION="8.4"

# MySQL (generated on first run, stored in credentials file)
DB_NAME="inventory"
DB_USER="inventory"
DB_CREDENTIALS_FILE="/root/.inventory-db-credentials"

# --- Colors -------------------------------------------------------------------
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

info()  { echo -e "${GREEN}[PROVISION]${NC} $1"; }
warn()  { echo -e "${YELLOW}[PROVISION]${NC} $1"; }
error() { echo -e "${RED}[PROVISION]${NC} $1"; exit 1; }

# --- Pre-flight checks -------------------------------------------------------
[[ $EUID -ne 0 ]] && error "This script must be run as root (or via sudo)."
id "$DEPLOY_USER" &>/dev/null || error "'${DEPLOY_USER}' user does not exist. Run motivya provision.sh first."
command -v php &>/dev/null || error "PHP not found. Run motivya provision.sh first."
command -v nginx &>/dev/null || error "Nginx not found. Run motivya provision.sh first."
command -v mysql &>/dev/null || error "MySQL not found. Run motivya provision.sh first."

# =============================================================================
# 1. MySQL database + user
# =============================================================================
info "Configuring MySQL for inventory-app..."

if [[ ! -f "$DB_CREDENTIALS_FILE" ]]; then
    DB_PASSWORD=$(openssl rand -base64 32 | tr -d '/+=|' | head -c 32)
    cat > "$DB_CREDENTIALS_FILE" <<CRED
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASSWORD=${DB_PASSWORD}
CRED
    chmod 600 "$DB_CREDENTIALS_FILE"
    info "MySQL credentials saved to ${DB_CREDENTIALS_FILE}"
else
    info "Loading existing credentials from ${DB_CREDENTIALS_FILE}"
    source "$DB_CREDENTIALS_FILE"
fi

mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL
info "MySQL database '${DB_NAME}' and user '${DB_USER}' configured."

# =============================================================================
# 2. Application directory structure
# =============================================================================
info "Creating application directories at ${APP_DIR}..."

mkdir -p "${APP_DIR}/releases"
mkdir -p "${APP_DIR}/shared/storage/app/public"
mkdir -p "${APP_DIR}/shared/storage/framework/cache/data"
mkdir -p "${APP_DIR}/shared/storage/framework/sessions"
mkdir -p "${APP_DIR}/shared/storage/framework/views"
mkdir -p "${APP_DIR}/shared/storage/logs"
mkdir -p "${APP_DIR}/backups"

# Create initial 'current' placeholder (first deploy will replace with symlink)
if [ ! -e "${APP_DIR}/current" ]; then
    mkdir -p "${APP_DIR}/current/scripts"
fi

# Ownership: deploy owns everything, www-data group for PHP-FPM
chown -R "${DEPLOY_USER}:www-data" "${APP_DIR}"
chmod -R 775 "${APP_DIR}/shared/storage"
find "${APP_DIR}/shared/storage" -type d -exec chmod g+s {} +

info "Application directories created."

# =============================================================================
# 3. Nginx vhost
# =============================================================================
info "Configuring Nginx for ${DOMAIN}..."
NGINX_CONF="/etc/nginx/sites-available/inventory"
SSL_CERT="/etc/letsencrypt/live/${DOMAIN}/fullchain.pem"
SSL_KEY="/etc/letsencrypt/live/${DOMAIN}/privkey.pem"

if [[ -f "$SSL_CERT" && -f "$SSL_KEY" ]]; then
    info "SSL certificate found — configuring HTTPS."
    cat > "$NGINX_CONF" <<NGINX
# HTTP -> HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    return 301 https://${DOMAIN}\$request_uri;
}

# Inventory App (HTTPS)
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name ${DOMAIN};

    ssl_certificate     ${SSL_CERT};
    ssl_certificate_key ${SSL_KEY};

    root ${APP_DIR}/current/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX
else
    warn "No SSL certificate yet — configuring HTTP only."
    warn "After this script, run: systemctl stop nginx && certbot certonly --standalone -d ${DOMAIN} --agree-tos -m admin@metanull.eu && systemctl start nginx"
    cat > "$NGINX_CONF" <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};

    root ${APP_DIR}/current/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX
fi

# Enable site
ln -sfn "$NGINX_CONF" /etc/nginx/sites-enabled/inventory

# Test and reload
nginx -t || error "Nginx config test failed!"
systemctl reload nginx
info "Nginx configured and reloaded."

# =============================================================================
# 4. SSL certificate
# =============================================================================
if [[ ! -f "$SSL_CERT" ]]; then
    info "Requesting SSL certificate for ${DOMAIN} (standalone mode)..."
    # Stop nginx so certbot can bind port 80
    systemctl stop nginx
    if certbot certonly --standalone -d "${DOMAIN}" --agree-tos -m admin@metanull.eu --non-interactive; then
        systemctl start nginx
        info "SSL certificate obtained. Re-running to update Nginx config with HTTPS..."
        exec "$0" "$@"
    else
        systemctl start nginx
        warn "Certbot failed — site will run on HTTP only until SSL is configured manually."
        warn "Run: systemctl stop nginx && certbot certonly --standalone -d ${DOMAIN} --agree-tos -m admin@metanull.eu && systemctl start nginx"
    fi
fi

# =============================================================================
# 5. Queue worker systemd service
# =============================================================================
info "Creating queue worker systemd service..."
cat > /etc/systemd/system/inventory-queue.service <<UNIT
[Unit]
Description=Inventory App Laravel Queue Worker
After=network.target mysql.service valkey-server.service

[Service]
User=www-data
Group=www-data
WorkingDirectory=${APP_DIR}/current
ExecStart=/usr/bin/php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --memory=128
Restart=always
RestartSec=5
StandardOutput=append:${APP_DIR}/shared/storage/logs/queue-worker.log
StandardError=append:${APP_DIR}/shared/storage/logs/queue-worker.log

[Install]
WantedBy=multi-user.target
UNIT

systemctl daemon-reload
systemctl enable inventory-queue
if [[ -L "${APP_DIR}/current" ]]; then
    systemctl restart inventory-queue
    info "Queue worker started."
else
    info "Queue worker configured (will start after first deploy)."
fi

# =============================================================================
# 6. Daily MySQL backup
# =============================================================================
info "Setting up daily MySQL backup..."

DEPLOY_HOME="/home/${DEPLOY_USER}"
DEFAULTS_FILE="${DEPLOY_HOME}/.inventory-my.cnf"

# Create mysqldump credentials file (avoids password on command line)
if [[ -f "$DB_CREDENTIALS_FILE" ]]; then
    source "$DB_CREDENTIALS_FILE"

    cat > "$DEFAULTS_FILE" <<MYCNF
[mysqldump]
user=${DB_USER}
password=${DB_PASSWORD}
MYCNF
    chmod 600 "$DEFAULTS_FILE"
    chown "${DEPLOY_USER}:${DEPLOY_USER}" "$DEFAULTS_FILE"

    # Copy credentials for deploy-ovh.sh to read during first deploy
    cp "$DB_CREDENTIALS_FILE" "${DEPLOY_HOME}/.inventory-db-credentials"
    chmod 600 "${DEPLOY_HOME}/.inventory-db-credentials"
    chown "${DEPLOY_USER}:${DEPLOY_USER}" "${DEPLOY_HOME}/.inventory-db-credentials"
fi

BACKUP_SCRIPT="${APP_DIR}/backup-db.sh"
cat > "$BACKUP_SCRIPT" <<BEOF
#!/usr/bin/env bash
set -euo pipefail
BACKUP_DIR="${APP_DIR}/backups"
DEFAULTS_FILE="${DEFAULTS_FILE}"
TIMESTAMP=\$(date +%F)
mysqldump --defaults-extra-file="\$DEFAULTS_FILE" "${DB_NAME}" | gzip > "\${BACKUP_DIR}/${DB_NAME}-\${TIMESTAMP}.sql.gz"
find "\$BACKUP_DIR" -name "${DB_NAME}-*.sql.gz" -mtime +14 -delete
BEOF
chmod +x "$BACKUP_SCRIPT"
chown "${DEPLOY_USER}:www-data" "$BACKUP_SCRIPT"

# Cron: 3:30 AM Brussels time (offset from motivya's 3 AM backup)
echo "30 3 * * * ${DEPLOY_USER} ${BACKUP_SCRIPT}" > /etc/cron.d/inventory-backup
chmod 644 /etc/cron.d/inventory-backup
info "Daily MySQL backup configured (3:30 AM, 14-day retention)."

# =============================================================================
# Done
# =============================================================================
echo ""
info "============================================="
info "  Inventory-App Provisioning complete!"
info "============================================="
info ""
info "  Deploy user:   ${DEPLOY_USER} (no sudo, shared with motivya)"
info "  App directory:  ${APP_DIR} (owned by ${DEPLOY_USER}:www-data)"
info "  Nginx:          configured for ${DOMAIN}"
info ""
info "  MySQL:          ${DB_NAME} (credentials in ${DB_CREDENTIALS_FILE})"
info "  Valkey:         localhost:6379 (use REDIS_DB=2, REDIS_CACHE_DB=3)"
info "  Queue worker:   inventory-queue.service"
info "  Backup:         daily at 3:30 AM (14-day retention)"
info ""
info "  Next steps:"
info "  1. DNS: Add CNAME record 'inventory' -> 'metanull.eu' in OVH DNS zone"
info "  2. SSL: If certbot failed above, run:"
info "     systemctl stop nginx && certbot certonly --standalone -d ${DOMAIN} --agree-tos -m admin@metanull.eu && systemctl start nginx"
info "     Then re-run this script to update Nginx with HTTPS config."
info "  3. GitHub: Create environment 'inventory.metanull.eu' with VPS secrets"
info "  4. Push code to main to trigger automatic deploy via GitHub Actions."
info ""
