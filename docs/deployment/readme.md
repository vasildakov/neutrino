
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

