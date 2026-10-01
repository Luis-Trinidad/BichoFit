#!/bin/sh
set -e

cd /app

# Si la BD llega como URL única (postgres://usuario:clave@host:puerto/bd),
# descomponerla a las variables DB_* que Laravel entiende.
if [ -n "$DB_URL" ]; then
    PHP_BIN=$(command -v php)
    eval "$(
        $PHP_BIN -r '
        $u = parse_url(getenv("DB_URL"));
        if (!$u) exit(1);
        $q = [];
        if (isset($u["query"])) parse_str($u["query"], $q);
        printf("export DB_HOST=%s DB_PORT=%d DB_DATABASE=%s DB_USERNAME=%s DB_PASSWORD=%s\n",
            escapeshellarg($u["host"] ?? "127.0.0.1"),
            $u["port"] ?? 5432,
            escapeshellarg(ltrim($u["path"] ?? "/bichofit", "/")),
            escapeshellarg($u["user"] ?? "bichofit"),
            escapeshellarg($u["pass"] ?? "")
        );
        '
    )"
fi

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
