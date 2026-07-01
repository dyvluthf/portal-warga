#!/bin/bash
set -e

echo "=== Portal Warga Backend Deployment ==="

echo "-> Pulling latest code..."
git pull origin main

echo "-> Installing dependencies (no dev)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "-> Running database migrations..."
php artisan migrate --force

echo "-> Caching config..."
php artisan config:cache

echo "-> Caching routes..."
php artisan route:cache

echo "-> Caching views..."
php artisan view:cache

echo "-> Restarting queue worker..."
php artisan queue:restart

echo "-> Creating storage link..."
php artisan storage:link

echo "=== Deployment completed successfully! ==="
