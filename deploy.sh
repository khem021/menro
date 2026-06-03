#!/bin/bash
# =============================================================================
#  MENRO — Hostinger VPS Auto-Deploy Script
#  Tested on: Ubuntu 22.04 LTS
#
#  Usage:
#    bash deploy.sh [domain] [db_name] [db_user]
#
#  Examples:
#    bash deploy.sh menro.example.com
#    bash deploy.sh menro.example.com menro menro_user
# =============================================================================
set -e

# ── Config (override with CLI args) ──────────────────────────────────────────
DOMAIN="${1:-your-domain.com}"
DB_NAME="${2:-menro}"
DB_USER="${3:-menro_user}"
DB_PASS="${4:-$(openssl rand -base64 20 | tr -dc 'a-zA-Z0-9' | head -c 20)}"
APP_DIR="/var/www/menro"
PHP_VERSION="8.3"

# ── Colors ───────────────────────────────────────────────────────────────────
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'
info()    { echo -e "${GREEN}[✓]${NC} $1"; }
warn()    { echo -e "${YELLOW}[!]${NC} $1"; }
section() { echo -e "\n${YELLOW}━━━ $1 ━━━${NC}"; }

# ── Must run as root ──────────────────────────────────────────────────────────
if [ "$(id -u)" -ne 0 ]; then
    echo -e "${RED}Run this script as root (sudo bash deploy.sh)${NC}"
    exit 1
fi

section "System update"
apt-get update -y && apt-get upgrade -y
info "System updated"

# ── Nginx ─────────────────────────────────────────────────────────────────────
section "Installing Nginx"
apt-get install -y nginx
systemctl enable nginx
info "Nginx installed"

# ── PHP 8.3 ──────────────────────────────────────────────────────────────────
section "Installing PHP ${PHP_VERSION}"
apt-get install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt-get update -y
apt-get install -y \
    php${PHP_VERSION} \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-tokenizer \
    php${PHP_VERSION}-opcache
systemctl enable php${PHP_VERSION}-fpm
info "PHP ${PHP_VERSION} installed"

# ── PHP.ini tweaks ────────────────────────────────────────────────────────────
PHP_INI="/etc/php/${PHP_VERSION}/fpm/conf.d/99-menro.ini"
cat > "$PHP_INI" <<EOF
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 60
memory_limit = 256M
opcache.enable = 1
opcache.memory_consumption = 128
opcache.validate_timestamps = 0
EOF
info "PHP ini configured"

# ── MySQL 8 ──────────────────────────────────────────────────────────────────
section "Installing MySQL 8"
apt-get install -y mysql-server
systemctl enable mysql

# Create database and user
mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
info "MySQL configured — DB: ${DB_NAME}, User: ${DB_USER}"

# ── Composer ──────────────────────────────────────────────────────────────────
section "Installing Composer"
if ! command -v composer &>/dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi
info "Composer $(composer --version --no-ansi | head -1)"

# ── Node.js 20 ────────────────────────────────────────────────────────────────
section "Installing Node.js 20"
if ! command -v node &>/dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi
info "Node $(node --version), npm $(npm --version)"

# ── App directory ─────────────────────────────────────────────────────────────
section "Creating app directory"
mkdir -p "$APP_DIR"
chown -R www-data:www-data "$APP_DIR"
info "App directory: ${APP_DIR}"

# ── Nginx virtual host ────────────────────────────────────────────────────────
section "Configuring Nginx"
cat > /etc/nginx/sites-available/menro <<NGINX
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${APP_DIR}/public;
    index index.php index.html;

    # Upload size
    client_max_body_size 15M;

    # Laravel routes
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # PHP-FPM
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    # Block hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2?)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
}
NGINX

ln -sf /etc/nginx/sites-available/menro /etc/nginx/sites-enabled/menro
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
info "Nginx configured for ${DOMAIN}"

# ── Generate .env ─────────────────────────────────────────────────────────────
section "Creating .env file"
APP_KEY_PLACEHOLDER="base64:$(openssl rand -base64 32)"

if [ ! -f "${APP_DIR}/.env" ]; then
cat > "${APP_DIR}/.env" <<ENV
APP_NAME=MENRO
APP_ENV=production
APP_DEBUG=false
APP_KEY=${APP_KEY_PLACEHOLDER}
APP_URL=https://${DOMAIN}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
ENV
    chmod 600 "${APP_DIR}/.env"
    info ".env created"
else
    warn ".env already exists — skipping (update DB credentials manually if needed)"
fi

# ── Laravel setup (only if composer.json exists) ──────────────────────────────
if [ -f "${APP_DIR}/composer.json" ]; then
    section "Installing Laravel dependencies"
    cd "$APP_DIR"

    # Ensure www-data owns all app files (handles git-clone or SCP uploads done as root)
    chown -R www-data:www-data "$APP_DIR"
    sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction
    info "Composer packages installed"

    if [ -f "package.json" ]; then
        sudo -u www-data npm ci --ignore-scripts
        sudo -u www-data npm run build
        rm -rf node_modules
        info "Frontend assets built"
    fi

    section "Running Laravel setup"

    # Directories & permissions
    mkdir -p storage/app/livewire-tmp storage/app/public/avatars \
             storage/framework/cache storage/framework/sessions storage/framework/views \
             storage/logs bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache

    # Storage symlink
    sudo -u www-data php artisan storage:link --force

    # Migrations
    sudo -u www-data php artisan migrate --force

    # Seed if no roles exist yet
    ROLE_COUNT=$(mysql -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" \
        -se "SELECT COUNT(*) FROM roles;" 2>/dev/null || echo "0")
    if [ "$ROLE_COUNT" = "0" ] || [ -z "$ROLE_COUNT" ]; then
        sudo -u www-data php artisan db:seed --force
        info "Database seeded with default data"
    else
        warn "Roles already exist — skipping seed"
    fi

    # Cache
    sudo -u www-data php artisan config:cache
    sudo -u www-data php artisan view:cache
    info "Config and views cached"
else
    warn "No composer.json found in ${APP_DIR}."
    warn "Upload your app files to ${APP_DIR} then run the Laravel setup commands from DEPLOY_HOSTINGER.md"
fi

# ── Restart services ──────────────────────────────────────────────────────────
section "Restarting services"
systemctl restart php${PHP_VERSION}-fpm
systemctl reload nginx
info "PHP-FPM and Nginx restarted"

# ── Summary ───────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║           MENRO Deployment Complete!                 ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${YELLOW}Site URL:${NC}      http://${DOMAIN}"
echo -e "  ${YELLOW}App path:${NC}      ${APP_DIR}"
echo -e "  ${YELLOW}DB Name:${NC}       ${DB_NAME}"
echo -e "  ${YELLOW}DB User:${NC}       ${DB_USER}"
echo ""
echo -e "  ${RED}⚠  SECURITY ACTIONS REQUIRED BEFORE USE:${NC}"
echo -e "  ${RED}   1. Change the default admin and menro passwords immediately.${NC}"
echo -e "  ${RED}   2. DB credentials are stored in ${APP_DIR}/.env — restrict access with: chmod 600 ${APP_DIR}/.env${NC}"
echo -e "  ${RED}   3. Enable HTTPS (see below) before any real data is entered.${NC}"
echo ""
echo -e "  ${YELLOW}Next step — enable HTTPS:${NC}"
echo -e "    apt-get install -y certbot python3-certbot-nginx"
echo -e "    certbot --nginx -d ${DOMAIN} -d www.${DOMAIN}"
echo ""
