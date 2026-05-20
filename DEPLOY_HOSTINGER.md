# Deploying MENRO on Hostinger VPS

This guide walks you through deploying the MENRO Waste Management System on a **Hostinger KVM VPS** running **Ubuntu 22.04 LTS**.

---

## Prerequisites

| Item | Requirement |
|------|-------------|
| VPS plan | Hostinger KVM 1 or higher |
| OS | Ubuntu 22.04 LTS (select during setup) |
| Root SSH access | Required for initial setup |
| Domain name | Any domain pointed to your VPS IP |
| App repo | GitHub or local copy of this project |

---

## Step 1 — Point Your Domain to the VPS

1. In Hostinger → **Domains → DNS / Nameservers**
2. Add an **A Record**:
   - Name: `@` (root) or `menro`
   - Points to: `YOUR_VPS_IP`
3. Wait 5–15 minutes for propagation.

---

## Step 2 — SSH into Your VPS

```bash
ssh root@YOUR_VPS_IP
```

---

## Step 3 — Run the Automated Deploy Script

Upload `deploy.sh` to your server and run it:

```bash
# Option A — run directly from GitHub (if repo is public)
curl -fsSL https://raw.githubusercontent.com/YOUR_USER/menro/main/deploy.sh | bash -s -- your-domain.com

# Option B — upload manually via SCP then run
scp deploy.sh root@YOUR_VPS_IP:/root/
ssh root@YOUR_VPS_IP "bash /root/deploy.sh your-domain.com menro menro_user"
```

The script will:
- Install Nginx, PHP 8.3, MySQL 8, Composer, Node.js
- Create the database and user
- Configure Nginx for your domain
- Set up the Laravel `.env` file
- Run migrations and seed default data
- Fix permissions

> **Note:** The script prints DB credentials at the end — save them!

---

## Step 4 — Upload Your Application Files

### Option A — Git Clone (recommended)

```bash
cd /var/www/menro
git clone https://github.com/YOUR_USER/menro.git .
```

### Option B — FTP / SFTP Upload

Use **FileZilla** or Hostinger's **File Manager**:
- Host: `YOUR_VPS_IP`
- Port: `22` (SFTP)
- Upload all project files to `/var/www/menro/`

---

## Step 5 — Configure the Environment File

```bash
cd /var/www/menro
cp .env.example .env
nano .env
```

Fill in these values:

```env
APP_NAME=MENRO
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=menro
DB_USERNAME=menro_user
DB_PASSWORD=YOUR_DB_PASSWORD_FROM_SCRIPT

SESSION_DRIVER=file
CACHE_DRIVER=file
```

Save with `Ctrl+X → Y → Enter`.

---

## Step 6 — Finish Laravel Setup

```bash
cd /var/www/menro

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node & build assets
npm ci --ignore-scripts
npm run build
rm -rf node_modules

# Generate app key
php artisan key:generate

# Link storage
php artisan storage:link

# Create required directories
mkdir -p storage/app/livewire-tmp storage/app/public/avatars
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Run migrations + seed
php artisan migrate --force
php artisan db:seed --force

# Cache config and views
php artisan config:cache
php artisan view:cache
```

---

## Step 7 — Enable HTTPS (Free SSL via Let's Encrypt)

```bash
apt-get install -y certbot python3-certbot-nginx
certbot --nginx -d your-domain.com -d www.your-domain.com
```

Follow the prompts. Certbot auto-renews the certificate.

---

## Step 8 — Verify the Deployment

Open your browser and go to `https://your-domain.com`

**Default login credentials:**

| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | System Administrator |
| `menro` | `admin123` | MENRO Officer |
| `encoder` | `admin123` | Data Encoder |
| `inspector` | `admin123` | Field Inspector |

> ⚠️ **Change all passwords immediately after first login!**

---

## Updating the App (Re-deploy)

```bash
cd /var/www/menro
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci --ignore-scripts && npm run build && rm -rf node_modules
php artisan migrate --force
php artisan config:cache
php artisan view:cache
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
```

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| 500 error on first load | `php artisan config:cache` + check `.env` values |
| White screen | Set `APP_DEBUG=true` temporarily to see the error |
| Permission denied | `chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data storage` |
| DB connection refused | Check `DB_HOST=127.0.0.1` and MySQL is running: `systemctl status mysql` |
| Uploads not working | `php artisan storage:link` + check `storage/app/livewire-tmp` exists |
| Nginx 502 Bad Gateway | `systemctl restart php8.3-fpm` |

---

## File Structure on Server

```
/var/www/menro/          ← Laravel root
/var/www/menro/public/   ← Nginx document root (web-accessible)
/etc/nginx/sites-available/menro   ← Nginx config
/etc/php/8.3/fpm/        ← PHP-FPM config
```
