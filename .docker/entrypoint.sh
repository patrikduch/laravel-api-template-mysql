#!/usr/bin/env bash
set -e
cd /var/www

# If APP_KEY is not set in env, generate one
if [ -z "$APP_KEY" ]; then
  echo "Generating APP_KEY..."
  php artisan key:generate --force
fi

# Clear and rebuild Laravel caches
php artisan config:clear || true
php artisan route:clear || true
php artisan cache:clear || true

php artisan config:cache
php artisan route:cache

# Run migrations automatically (optional — comment out if you prefer jobs)
# php artisan migrate --force || true

# Start Apache
exec apache2-foreground
