#!/bin/sh
set -e

cd /app

# Si la BD llega como URL única (postgres://usuario:clave@host:puerto/bd),
# descomponerla a las variables DB_* que Laravel entiende — incluyendo
# DB_CONNECTION para que no caiga al default sqlite.
if [ -n "$DB_URL" ]; then
    eval "$(php -r '
        $u = parse_url(getenv("DB_URL"));
        if (!$u) exit(1);
        $scheme = $u["scheme"] ?? "pgsql";
        if ($scheme === "postgres" || $scheme === "postgresql") $scheme = "pgsql";
        printf("export DB_HOST=%s DB_PORT=%d DB_DATABASE=%s DB_USERNAME=%s DB_PASSWORD=%s DB_CONNECTION=%s\n",
            escapeshellarg($u["host"] ?? "127.0.0.1"),
            $u["port"] ?? 5432,
            escapeshellarg(ltrim($u["path"] ?? "/bichofit", "/")),
            escapeshellarg($u["user"] ?? "bichofit"),
            escapeshellarg($u["pass"] ?? ""),
            escapeshellarg($scheme)
        );
    ')"
fi

# El volumen de storage puede venir vacío: garantizar la estructura que Laravel espera
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

# Limpiar cachés viejos PRIMERO (la config cachea el hash del manifest;
# si no se limpia, Inertia ve versión desactualizada y no hidrata)
php artisan config:clear >/dev/null 2>&1 || true
php artisan route:clear >/dev/null 2>&1 || true
php artisan view:clear >/dev/null 2>&1 || true

# Cachear config/rutas/vistas con las variables de entorno definitivas
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migraciones y catálogo de ejercicios (idempotentes)
php artisan migrate --force
php artisan db:seed --force

php artisan storage:link || true

exec frankenphp run --config /etc/caddy/Caddyfile
