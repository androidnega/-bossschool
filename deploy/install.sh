#!/usr/bin/env bash
# =============================================================================
# BossSchool — first-time install / re-install script for the production
# server (cPanel shared hosting, VPS, anywhere with shell + composer + php).
#
# Run this from the directory where your Laravel source lives, e.g.
#   cd ~/public_html  &&  bash deploy/install.sh
# or
#   cd ~/repos/bossschool  &&  bash deploy/install.sh
#
# Idempotent: safe to re-run. Will NOT overwrite an existing APP_KEY or
# clobber an existing .env.
# =============================================================================

set -euo pipefail

APP_DIR="$(pwd)"
echo "→ Running install in: ${APP_DIR}"

# ------------------------------------------------------------------- 1. composer
if ! command -v composer >/dev/null 2>&1; then
    echo "✗ 'composer' is not in PATH." >&2
    echo "  On cPanel you can usually invoke it as:" >&2
    echo "      /usr/local/bin/ea-php82 /opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader" >&2
    echo "  Or contact your host to enable Composer for your shell user." >&2
    exit 1
fi

echo "→ Installing PHP dependencies (no dev, optimized autoloader)..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# ------------------------------------------------------------------- 2. .env
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "→ Created .env from .env.example."
        echo "  ⚠  Open .env now and fill in real values for DB_*, APP_URL,"
        echo "     MAIL_*, PAYSTACK_* before continuing."
        echo "     Then re-run this script."
        exit 0
    else
        echo "✗ No .env or .env.example found. Cannot continue." >&2
        exit 1
    fi
fi

# ------------------------------------------------------------------- 3. APP_KEY
if grep -Eq '^APP_KEY=base64:.+' .env; then
    echo "→ APP_KEY already set, leaving it alone."
else
    echo "→ Generating APP_KEY..."
    php artisan key:generate --force --no-interaction
fi

# ------------------------------------------------------------------- 4. permissions
echo "→ Ensuring storage/ and bootstrap/cache/ are writable..."
mkdir -p storage/framework/{cache,sessions,views,testing} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# ------------------------------------------------------------------- 5. storage symlink
echo "→ Linking storage..."
php artisan storage:link --no-interaction 2>/dev/null || true

# ------------------------------------------------------------------- 6. migrations
echo "→ Running database migrations..."
php artisan migrate --force --no-interaction

# ------------------------------------------------------------------- 7. platform bootstrap (idempotent)
echo "→ Seeding platform settings, Ghana templates, plans (idempotent)..."
php artisan db:seed --class=PlatformBootstrapSeeder --force --no-interaction || true
php artisan db:seed --class=GhanaBasicSchoolTemplateSeeder --force --no-interaction || true
php artisan db:seed --class=PlansSeeder --force --no-interaction || true
php artisan db:seed --class=PermissionsSeeder --force --no-interaction || true

# ------------------------------------------------------------------- 8. caches
echo "→ Caching config / routes / views for production..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ------------------------------------------------------------------- 9. summary
echo ""
echo "✓ Install complete."
echo ""
echo "Next:"
echo "  1. If this is your primary domain on cPanel and you haven't already,"
echo "     change the domain's Document Root to: ${APP_DIR}/public"
echo "     (or copy deploy/public_html/.htaccess + index.php into public_html/)."
echo "  2. Visit https://your-domain.tld/setup/superadmin to create the first"
echo "     SuperAdmin account (the route self-locks after the account exists)."
