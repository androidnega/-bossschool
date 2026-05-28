#!/usr/bin/env bash
# =============================================================================
# BossSchool — incremental deploy script.
# Run this AFTER you have pulled the latest code on the server.
# (install.sh is the first-time bootstrap; this script is for every subsequent
# release.)
# =============================================================================

set -euo pipefail

APP_DIR="$(pwd)"
echo "→ Deploying in: ${APP_DIR}"

# 1. Put the app into maintenance mode (best-effort).
php artisan down --render="errors::503" --retry=30 || true

# 2. Update PHP deps.
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 3. Run any new migrations.
php artisan migrate --force --no-interaction

# 4. Refresh caches.
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Permissions (in case anything new was added).
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 6. Bring the app back up.
php artisan up || true

echo "✓ Deploy complete."
