#!/usr/bin/env bash
#
# provision.sh — Standalone VPS provisioning for inventory-app
#
# Fully self-contained: installs all infrastructure (PHP, Nginx, MySQL, Valkey,
# UFW, fail2ban, Certbot) and configures the inventory-app-specific resources.
# Safe to run on a fresh server or alongside motivya-laravel (each app is isolated).
#
# Run as root (or via sudo):
#   sudo bash provision.sh [SSH_PUBLIC_KEY]
#
# Required env vars (set in scripts/infra.local or export before running):
#   INVENTORY_DOMAIN       — the app domain (e.g. inventory.metanull.eu)
#   INVENTORY_ADMIN_EMAIL  — email for Certbot Let's Encrypt registration
#
# This script is idempotent — safe to re-run after updates.
#
# What it does:
#   1. System basics: timezone, locale, packages
#   2. Creates 'deploy' user (non-privileged, no sudo)
#   3. Hardens SSH (key-only, no root login)
#   4. Installs PHP-FPM, Nginx, and PHP extensions
#   5. Installs MySQL and creates application database + user
#   6. Installs Valkey (Redis-compatible) for cache/sessions/queues
#   7. Configures Nginx vhost for inventory-app
#   8. Sets up UFW firewall (22, 80, 443)
#   9. Installs Fail2ban and unattended-upgrades
#   10. Creates the application directory structure (owned by deploy)
#   11. Sets up shared storage with www-data group permissions
#   12. Installs Certbot + auto-renewal timer
#   13. Creates queue worker systemd service
#   14. Sets up daily MySQL backup cron
#
# The 'deploy' user has NO sudo. All privileged operations belong here.
#
set -euo pipefail

# --- Optional: source local infra config (gitignored, never committed) ------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
[[ -f "${SCRIPT_DIR}/infra.local" ]] && source "${SCRIPT_DIR}/infra.local"

# --- Configuration -----------------------------------------------------------
DEPLOY_USER="deploy"
APP_DIR="/opt/inventory"
DOMAIN="${INVENTORY_DOMAIN:?ERROR: Set INVENTORY_DOMAIN in scripts/infra.local or export it as an env var}"
ADMIN_EMAIL="${INVENTORY_ADMIN_EMAIL:?ERROR: Set INVENTORY_ADMIN_EMAIL in scripts/infra.local or export it as an env var}"
PHP_VERSION="8.5"
TIMEZONE="Europe/Brussels"
LOCALE="fr_BE.UTF-8"

# MySQL (generated on first run, stored in /root/.inventory-db-credentials)
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
[[ ! -f /etc/os-release ]] && error "Cannot detect OS"
source /etc/os-release
version_ge() { [ "$(printf '%s\n%s' "$1" "$2" | sort -V | head -1)" = "$2" ]; }
version_ge "$VERSION_ID" "26.04" || warn "Expected Ubuntu 26.04 or later, got $VERSION_ID. Proceeding anyway..."

# --- SSH public key (optional, for first run) ---------------------------------
SSH_PUBLIC_KEY="${1:-}"

# =============================================================================
# 1. System basics
# =============================================================================
info "Updating system packages..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get upgrade -y -qq

info "Setting timezone to ${TIMEZONE}..."
timedatectl set-timezone "$TIMEZONE"

info "Setting locale to ${LOCALE}..."
locale-gen "$LOCALE" > /dev/null 2>&1 || true
update-locale LANG="$LOCALE"

# =============================================================================
# 2. Create deploy user (non-privileged)
# =============================================================================
if id "$DEPLOY_USER" &>/dev/null; then
    info "User '${DEPLOY_USER}' already exists."
else
    info "Creating '${DEPLOY_USER}' user..."
    adduser --disabled-password --gecos "App Deploy" --home "/home/${DEPLOY_USER}" "$DEPLOY_USER"
fi

# IMPORTANT: Remove deploy from sudo group (least privilege)
if groups "$DEPLOY_USER" | grep -qw sudo; then
    info "Removing '${DEPLOY_USER}' from sudo group (least privilege)..."
    gpasswd -d "$DEPLOY_USER" sudo 2>/dev/null || true
fi

# Remove any leftover sudoers file
rm -f "/etc/sudoers.d/${DEPLOY_USER}"

# Add deploy to www-data group (for shared file permissions)
usermod -aG www-data "$DEPLOY_USER"

# Set up SSH key for deploy user (append-if-not-present so co-hosted apps keep their keys)
if [[ -n "$SSH_PUBLIC_KEY" ]]; then
    DEPLOY_HOME="/home/${DEPLOY_USER}"
    mkdir -p "${DEPLOY_HOME}/.ssh"
    touch "${DEPLOY_HOME}/.ssh/authorized_keys"
    grep -qxF "$SSH_PUBLIC_KEY" "${DEPLOY_HOME}/.ssh/authorized_keys" || \
        echo "$SSH_PUBLIC_KEY" >> "${DEPLOY_HOME}/.ssh/authorized_keys"
    chmod 700 "${DEPLOY_HOME}/.ssh"
    chmod 600 "${DEPLOY_HOME}/.ssh/authorized_keys"
    chown -R "${DEPLOY_USER}:${DEPLOY_USER}" "${DEPLOY_HOME}/.ssh"
    info "SSH key added for '${DEPLOY_USER}' (pre-existing keys preserved)."
else
    info "No SSH key provided — skipping (use: provision.sh 'ssh-ed25519 AAAA...')"
fi

# =============================================================================
# 3. Harden SSH
# =============================================================================
info "Hardening SSH configuration..."
SSHD_CONFIG="/etc/ssh/sshd_config"

sed -i 's/^#\?PermitRootLogin.*/PermitRootLogin no/' "$SSHD_CONFIG"
sed -i 's/^#\?PasswordAuthentication.*/PasswordAuthentication no/' "$SSHD_CONFIG"
sed -i 's/^#\?PubkeyAuthentication.*/PubkeyAuthentication yes/' "$SSHD_CONFIG"
sed -i 's/^#\?ChallengeResponseAuthentication.*/ChallengeResponseAuthentication no/' "$SSHD_CONFIG"

systemctl restart sshd

# =============================================================================
# 4. Install PHP-FPM, Nginx, and extensions
# =============================================================================
info "Installing PHP ${PHP_VERSION}-FPM, Nginx, and extensions..."

apt-get install -y -qq \
    nginx \
    "php${PHP_VERSION}-fpm" \
    "php${PHP_VERSION}-mysql" \
    "php${PHP_VERSION}-sqlite3" \
    "php${PHP_VERSION}-mbstring" \
    "php${PHP_VERSION}-xml" \
    "php${PHP_VERSION}-curl" \
    "php${PHP_VERSION}-zip" \
    "php${PHP_VERSION}-bcmath" \
    "php${PHP_VERSION}-intl" \
    "php${PHP_VERSION}-gd" \
    "php${PHP_VERSION}-redis" \
    unzip curl sqlite3

# Enable and start services
systemctl enable "php${PHP_VERSION}-fpm" nginx
systemctl start "php${PHP_VERSION}-fpm"

# =============================================================================
# 5. Install MySQL
# =============================================================================
info "Installing MySQL server..."
apt-get install -y -qq mysql-server

systemctl enable mysql
systemctl start mysql

# Generate password once, store in credentials file
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
    info "Loading existing MySQL credentials from ${DB_CREDENTIALS_FILE}"
    source "$DB_CREDENTIALS_FILE"
fi

# Create database and user (idempotent)
mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL
info "MySQL database '${DB_NAME}' and user '${DB_USER}' configured."

# Harden: bind to localhost only
if ! grep -q '^bind-address.*=.*127.0.0.1' /etc/mysql/mysql.conf.d/mysqld.cnf 2>/dev/null; then
    sed -i 's/^#\?bind-address.*/bind-address = 127.0.0.1/' /etc/mysql/mysql.conf.d/mysqld.cnf
    systemctl restart mysql
fi

# =============================================================================
# 6. Install Valkey (Redis-compatible cache/session/queue backend)
# =============================================================================
info "Installing Valkey..."
apt-get install -y -qq valkey-server

# Bind to localhost only, enable as systemd service
VALKEY_CONF="/etc/valkey/valkey.conf"
if [[ -f "$VALKEY_CONF" ]]; then
    sed -i 's/^bind .*/bind 127.0.0.1 -::1/' "$VALKEY_CONF"
    sed -i 's/^protected-mode .*/protected-mode yes/' "$VALKEY_CONF"
    if ! grep -q '^maxmemory ' "$VALKEY_CONF"; then
        echo 'maxmemory 256mb' >> "$VALKEY_CONF"
        echo 'maxmemory-policy allkeys-lru' >> "$VALKEY_CONF"
    fi
fi

systemctl enable valkey-server
systemctl restart valkey-server
info "Valkey installed and running on localhost:6379."

# =============================================================================
# 7. Firewall (UFW)
# =============================================================================
info "Configuring UFW firewall..."
apt-get install -y -qq ufw
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp comment "SSH"
ufw allow 80/tcp comment "HTTP"
ufw allow 443/tcp comment "HTTPS"
ufw --force enable

# =============================================================================
# 8. Fail2ban + unattended-upgrades
# =============================================================================
info "Installing Fail2ban..."
apt-get install -y -qq fail2ban
cat > /etc/fail2ban/jail.local <<'EOF'
[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 5
bantime = 3600
findtime = 600
EOF
systemctl enable fail2ban
systemctl restart fail2ban

info "Enabling automatic security updates..."
apt-get install -y -qq unattended-upgrades
dpkg-reconfigure -f noninteractive unattended-upgrades

# =============================================================================
# 9. Certbot
# =============================================================================
info "Installing Certbot..."
apt-get install -y -qq certbot

# Enable auto-renewal timer
if systemctl list-timers | grep -q certbot; then
    info "Certbot auto-renewal timer already active."
else
    systemctl enable --now certbot.timer 2>/dev/null || \
        info "Certbot timer not found — renewal via cron should be in place."
fi

# =============================================================================
# 10. Configure Nginx vhost
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

# Enable site (do not remove default — another app may rely on it)
ln -sfn "$NGINX_CONF" /etc/nginx/sites-enabled/inventory

# Test and reload
nginx -t || error "Nginx config test failed!"
systemctl reload nginx
info "Nginx configured and reloaded."

# =============================================================================
# 11. SSL certificate
# =============================================================================
if [[ ! -f "$SSL_CERT" ]]; then
    info "Requesting SSL certificate for ${DOMAIN} (standalone mode)..."
    systemctl stop nginx
    if certbot certonly --standalone -d "${DOMAIN}" --agree-tos -m "${ADMIN_EMAIL}" --non-interactive; then
        systemctl start nginx
        info "SSL certificate obtained. Re-running to update Nginx config with HTTPS..."
        exec "$0" "$@"
    else
        systemctl start nginx
        warn "Certbot failed — site will run on HTTP only until SSL is configured manually."
        warn "Run: systemctl stop nginx && certbot certonly --standalone -d ${DOMAIN} --agree-tos -m ${ADMIN_EMAIL} && systemctl start nginx"
        warn "Then re-run this script to update Nginx with HTTPS config."
    fi
fi

# =============================================================================
# 12. Application directory structure
# =============================================================================
info "Creating application directories at ${APP_DIR}..."

mkdir -p "${APP_DIR}/releases"
mkdir -p "${APP_DIR}/shared/storage/app/public"
mkdir -p "${APP_DIR}/shared/storage/framework/cache/data"
mkdir -p "${APP_DIR}/shared/storage/framework/sessions"
mkdir -p "${APP_DIR}/shared/storage/framework/views"
mkdir -p "${APP_DIR}/shared/storage/logs"
mkdir -p "${APP_DIR}/backups"

# Ownership: deploy owns everything, www-data group for PHP-FPM
chown -R "${DEPLOY_USER}:www-data" "${APP_DIR}"
chmod -R 775 "${APP_DIR}/shared/storage"
find "${APP_DIR}/shared/storage" -type d -exec chmod g+s {} +

info "Application directories created."

# =============================================================================
# 13. Queue worker systemd service
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
ExecStart=/usr/bin/php artisan queue:work redis --queue=glossary,default --sleep=3 --tries=3 --max-time=3600 --memory=128
Restart=always
RestartSec=5
UMask=0002
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
# 14. Daily MySQL backup
# =============================================================================
info "Setting up daily MySQL backup..."

DEPLOY_HOME="/home/${DEPLOY_USER}"
DEFAULTS_FILE="${DEPLOY_HOME}/.inventory-my.cnf"

if [[ -f "$DB_CREDENTIALS_FILE" ]]; then
    source "$DB_CREDENTIALS_FILE"

    cat > "$DEFAULTS_FILE" <<MYCNF
[mysqldump]
user=${DB_USER}
password=${DB_PASSWORD}
MYCNF
    chmod 600 "$DEFAULTS_FILE"
    chown "${DEPLOY_USER}:${DEPLOY_USER}" "$DEFAULTS_FILE"

    # Copy credentials for deploy.sh to read during first deploy
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

# Cron: 3:30 AM Brussels time (offset from motivya's 3 AM backup when co-hosted)
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
info "  Deploy user:   ${DEPLOY_USER} (no sudo)"
info "  App directory: ${APP_DIR} (owned by ${DEPLOY_USER}:www-data)"
info "  PHP-FPM:       ${PHP_VERSION}"
info "  Nginx:         configured for ${DOMAIN}"
info ""
info "  MySQL:         ${DB_NAME} (credentials in ${DB_CREDENTIALS_FILE})"
info "  Valkey:        localhost:6379 (use REDIS_DB=2, REDIS_CACHE_DB=3)"
info "  Queue worker:  inventory-queue.service"
info "  Backup:        daily at 3:30 AM (14-day retention)"
info ""
info "  Next steps:"
info "  1. Verify SSH:  ssh -i ~/.ssh/inventory_deploy ${DEPLOY_USER}@<VPS_IP> whoami"
info "  2. If SSL cert failed above, run:"
info "     systemctl stop nginx && certbot certonly --standalone -d ${DOMAIN} --agree-tos -m ${ADMIN_EMAIL} && systemctl start nginx"
info "     Then re-run this script to update Nginx with HTTPS config."
info "  3. GitHub: ensure environment 'inventory.metanull.eu' has VPS_HOST, VPS_SSH_KEY, VPS_SSH_USER secrets"
info "  4. Push code to main to trigger automatic deploy via GitHub Actions."
info ""
