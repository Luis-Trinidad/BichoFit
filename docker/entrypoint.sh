#!/bin/sh
set -e

cd /app

# El volumen de storage puede venir vacío: garantizar la estructura que Laravel espera
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

# Cachear config/rutas/vistas con las variables de entorno definitivas
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migraciones y catálogo de ejercicios (idempotentes)
php artisan migrate --force
php artisan db:seed --force

php artisan storage:link || true

exec frankenphp run --config /etc/caddy/Caddyfile
