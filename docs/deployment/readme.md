
## 1) VPS layout for neutrino.bg

On the VPS:

```sh
sudo mkdir -p /var/www/neutrino.bg/{releases,shared}
sudo mkdir -p /var/www/neutrino.bg/shared/{uploads,log,fixtures,cache/doctrine/proxies}
sudo chown -R deploy:www-data /var/www/neutrino.bg
sudo chmod -R 2775 /var/www/neutrino.bg
```

Shared dirs

```sh
sudo mkdir -p /var/www/neutrino.bg/shared/{uploads,log,cache/doctrine/proxies,fixtures}
sudo chown -R deploy:www-data /var/www/neutrino.bg/shared
sudo chmod -R 2775 /var/www/neutrino.bg/shared
```

## 2) Nginx vhost (root points to current/public)

Create /etc/nginx/sites-available/neutrino.bg:
```nginx
server {
    server_name neutrino.bg www.neutrino.bg;

    root /var/www/neutrino.bg/current/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }

    location ~* \.(jpg|jpeg|png|gif|webp|svg|css|js|ico)$ {
        expires 30d;
        access_log off;
    }
}
```

Enable + reload:

```sh
sudo ln -sfn /etc/nginx/sites-available/neutrino.bg /etc/nginx/sites-enabled/neutrino.bg
sudo nginx -t && sudo systemctl reload nginx
```
SSL setup with certbot:


## 3) Deploy script for neutrino.bg

Create /usr/local/bin/deploy-neutrino:

```sh
sudo nano /usr/local/bin/deploy-neutrino
sudo chmod +x /usr/local/bin/deploy-neutrino
```

Paste (adapt repo URL + branch):

```shell
#!/usr/bin/env bash
set -euo pipefail

BASE="/var/www/neutrino.bg"
RELEASES="$BASE/releases"
SHARED="$BASE/shared"
CURRENT="$BASE/current"

REPO="git@github.com:vasildakov/neutrino.git"
BRANCH="main"

TS="$(date +%Y%m%d%H%M%S)"
NEW_RELEASE="$RELEASES/$TS"

PHP_FPM_SERVICE="php8.4-fpm"
NGINX_SERVICE="nginx"

echo "== Deploy neutrino.bg: $TS =="

mkdir -p "$RELEASES" "$SHARED"

echo "Cloning -> $NEW_RELEASE"
sudo -u deploy git clone --branch "$BRANCH" --depth 1 "$REPO" "$NEW_RELEASE"

cd "$NEW_RELEASE"

echo "Composer install (prod)"
sudo -u deploy composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# -------------------------------------------------
# Shared dirs (persistent)
# -------------------------------------------------
mkdir -p \
  "$SHARED/uploads" \
  "$SHARED/log" \
  "$SHARED/fixtures" \
  "$SHARED/cache/doctrine/proxies"

# Ensure release dirs exist
mkdir -p "$NEW_RELEASE/data/cache/doctrine" "$NEW_RELEASE/public" "$NEW_RELEASE/data"

# IMPORTANT:
# Keep data/cache per-release (config cache may contain absolute paths),
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

# Permissions
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

# Atomic switch (end)
echo "Switching current -> $NEW_RELEASE"
ln -sfn "$NEW_RELEASE" "$CURRENT"

echo "Reloading services"
systemctl reload "$PHP_FPM_SERVICE"
systemctl reload "$NGINX_SERVICE"

echo "Cleanup old releases (keep last 5)"
cd "$RELEASES"
ls -1dt */ 2>/dev/null | tail -n +6 | xargs -r rm -rf

echo "== Deploy finished: $TS =="

```

## 4) GitHub Actions workflow for neutrino.bg

In the neutrino repo: .github/workflows/deploy-production.yml

```yaml
name: Deploy production

on:
  push:
    branches: ["main"]

jobs:
  deploy:
    runs-on: ubuntu-latest
    environment: production
    steps:
      - name: Run deploy script on VPS
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.VPS_HOST }}
          username: ${{ secrets.VPS_USER }}
          key: ${{ secrets.VPS_SSH_KEY }}
          port: ${{ secrets.VPS_PORT }}
          script: |
            set -euo pipefail
            sudo /usr/local/bin/deploy-neutrino
```

## 5) Secrets

One-time: configure neutrino.bg .env
```
sudo nano /var/www/neutrino.bg/shared/.env
```

If neutrino.bg is on the same VPS, you can reuse the same secrets:
- VPS_HOST
- VPS_USER
- VPS_SSH_KEY
- VPS_PORT

If it’s a different VPS, use a new set of secrets in that repo.

Quick checklist to avoid the issues you hit last time
•	Don’t share the entire data/cache across releases; share only data/cache/doctrine/proxies.
•	Always symlink uploads into $NEW_RELEASE/public/uploads, not $CURRENT.
•	Run Doctrine CLI with -d opcache.enable_cli=0.

Quick smoke test after first deploy

```shell
readlink -f /var/www/neutrino.bg/current
ls -la /var/www/neutrino.bg/current/public | grep uploads
sudo nginx -t
curl -I https://neutrino.bg
```

