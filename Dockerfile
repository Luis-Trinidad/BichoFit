FROM dunglas/frankenphp:1-php8.4 AS base

# ---- Dependencias PHP (vendor) ----
FROM base AS vendor
WORKDIR /app
RUN install-php-extensions pdo_pgsql intl zip exif opcache @composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-autoloader --no-scripts --prefer-dist --no-interaction
COPY app/ app/
COPY bootstrap/ bootstrap/
COPY config/ config/
COPY database/ database/
COPY resources/ resources/
COPY routes/ routes/
COPY artisan ./
RUN composer dump-autoload --optimize --no-dev

# ---- Frontend (Svelte) ----
FROM node:22-alpine AS frontend
WORKDIR /app
# Sin PHP en este stage: wayfinder usa los archivos generados del repo
ENV WAYFINDER_COMMAND=true
COPY package.json package-lock.json .npmrc ./
RUN npm ci
COPY resources/ resources/
COPY vite.config.ts tsconfig.json svelte.config.js components.json ./
RUN npm run build

# ---- Imagen final ----
FROM base
WORKDIR /app

# Tesseract + español para el OCR de la báscula (Debian, no alpine)
RUN apt-get update \
    && apt-get install -y --no-install-recommends tesseract-ocr tesseract-ocr-spa \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP con verificación explícita: si pdo_pgsql no carga, el build falla aquí
RUN install-php-extensions pdo_pgsql intl zip exif opcache \
    && php -m | grep -q pdo_pgsql \
    || (echo 'FATAL: pdo_pgsql no cargó — diagnosticando:' && php -i | grep -i extension_dir && ls /usr/local/lib/php/extensions/*/ && exit 1)

COPY --from=vendor /app /app
COPY --from=frontend /app/public/build /app/public/build
COPY public/ public/
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080
ENTRYPOINT ["entrypoint.sh"]
