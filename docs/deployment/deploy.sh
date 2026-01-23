#!/usr/bin/env bash
set -euo pipefail

# -------------------------------------------------
# Paths
# -------------------------------------------------
BASE="/var/www/neutrino.bg"
RELEASES="$BASE/releases"
SHARED="$BASE/shared"
CURRENT="$BASE/current"

# -------------------------------------------------
# Repo
# -------------------------------------------------
REPO="git@github.com:vasildakov/neutrino.git"
BRANCH="main"

# -------------------------------------------------
# Services
# -------------------------------------------------
PHP_FPM_SERVICE="php8.4-fpm"
NGINX_SERVICE="nginx"

# -------------------------------------------------
# New release
# -------------------------------------------------
TS="$(date +%Y%m%d%H%M%S)"
NEW_RELEASE="$RELEASES/$TS"

echo "== Deploy neutrino.bg: $TS =="

# Make sure base dirs exist
mkdir -p "$RELEASES" "$SHARED"

# Make sure shared dirs exist (persistent)
mkdir -p \
  "$SHARED/uploads" \
  "$SHARED/log" \
  "$SHARED/fixtures" \
  "$SHARED/cache/doctrine/proxies"

# Ensure CURRENT is a symlink (not a real directory)
# If it's a directory, back it up so ln -sfn can work.
if [ -d "$CURRENT" ] && [ ! -L "$CURRENT" ]; then
  echo "WARNING: $CURRENT is a directory, converting to symlink (backup will be kept)"
  mv "$CURRENT" "$BASE/current.bak.$TS"
fi

echo "Cloning -> $NEW_RELEASE"
sudo -u deploy git clone --branch "$BRANCH" --depth 1 "$REPO" "$NEW_RELEASE"

cd "$NEW_RELEASE"

echo "Composer install (prod)"
sudo -u deploy composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# -------------------------------------------------
# Release dirs
# -------------------------------------------------
mkdir -p "$NEW_RELEASE/data/cache/doctrine" "$NEW_RELEASE/public" "$NEW_RELEASE/data"

# IMPORTANT:
# Keep data/cache per-release (config cache can include absolute paths),
# but share ONLY doctrine proxies:
rm -rf "$NEW_RELEASE/data/cache/doctrine/proxies"
ln -sfn "$SHARED/cache/doctrine/proxies" "$NEW_RELEASE/data/cache/doctrine/proxies"

# log + fixtures persistent
rm -rf "$NEW_RELEASE/data/log"
ln -sfn "$SHARED/log" "$NEW_RELEASE/data/log"

rm -rf "$NEW_RELEASE/data/fixtures"
ln -sfn "$SHARED/fixtures" "$NEW_RELEASE/data/fixtures"

# uploads persistent
rm -rf "$NEW_RELEASE/public/uploads"
ln -sfn "$SHARED/uploads" "$NEW_RELEASE/public/uploads"

# Shared .env
touch "$SHARED/.env"
rm -f "$NEW_RELEASE/.env"
ln -sfn "$SHARED/.env" "$NEW_RELEASE/.env"

# Clear release-specific config cache (safe)
rm -f "$NEW_RELEASE/data/cache/config-cache.php" || true
rm -f "$NEW_RELEASE/data/config-cache.php" || true

# -------------------------------------------------
# Permissions (release + shared)
# -------------------------------------------------
echo "Fixing permissions"
chown -R deploy:www-data "$NEW_RELEASE" "$SHARED"
find "$NEW_RELEASE" -type d -exec chmod 2775 {} \;
find "$NEW_RELEASE" -type f -exec chmod 0664 {} \;
chmod -R 2775 "$SHARED"

# -------------------------------------------------
# Doctrine proxies (avoid CLI opcache cross-release issues)
# -------------------------------------------------
if [ -f "$NEW_RELEASE/bin/doctrine" ]; then
  echo "Generating Doctrine proxies"
  sudo -u deploy php -d opcache.enable_cli=0 "$NEW_RELEASE/bin/doctrine" orm:generate-proxies
fi

# -------------------------------------------------
# Clear config cache (optional; do NOT fail deploy if missing)
# -------------------------------------------------
if [ -f "$NEW_RELEASE/bin/clear-config-cache.php" ]; then
  echo "Clearing config cache"
  sudo -u deploy php "$NEW_RELEASE/bin/clear-config-cache.php" || true
fi

# -------------------------------------------------
# Atomic switch (ONLY after everything succeeded)
# -------------------------------------------------
echo "Switching current -> $NEW_RELEASE"
ln -sfn "$NEW_RELEASE" "$CURRENT"

# -------------------------------------------------
# Reload services (restart FPM so opcache doesn't keep old code)
# -------------------------------------------------
echo "Reloading services"
systemctl restart "$PHP_FPM_SERVICE"
systemctl reload "$NGINX_SERVICE"

# -------------------------------------------------
# Cleanup old releases (keep last 5)
# -------------------------------------------------
echo "Cleanup old releases (keep last 5)"
cd "$RELEASES"
ls -1dt */ 2>/dev/null | tail -n +6 | xargs -r rm -rf

echo "== Deploy finished: $TS =="