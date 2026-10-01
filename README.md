# BichoFit 🏋️

PWA para llevar el control completo de tus entrenamientos: rutinas con series,
repeticiones y peso por serie; progreso en el tiempo; y (Fase 3) composición
corporal medida por báscula de bioimpedancia, capturada con OCR.

**Stack:** Laravel 13 · Inertia 3 · Svelte 5 · Tailwind CSS · PostgreSQL 16 ·
Docker (FrankenPHP con HTTPS automático).

Diseño completo en [`docs/plans/2026-09-30-bichofit-design.md`](docs/plans/2026-09-30-bichofit-design.md).

---

## Desarrollo local

Requisitos: PHP 8.3+, Composer, Node 22+, Docker.

```bash
# 1. Dependencias
composer install
npm install

# 2. Base de datos (Postgres en Docker, puerto 5433 del host)
docker compose up -d db

# 3. Configuración
cp .env.example .env
php artisan key:generate

# 4. Migraciones + catálogo de ejercicios (67)
php artisan migrate --seed

# 5. Servir
php artisan serve --port=8088   # app en http://127.0.0.1:8088
npm run dev                      # (opcional) assets en caliente
```

Registrar una cuenta y a entrenar. El catálogo ya viene sembrado; puedes crear
ejercicios propios desde la pestaña Ejercicios.

### Pruebas

```bash
php artisan test        # 59 pruebas (dominio + flujo completo + starter)
npm run build           # verificación de compilación del frontend
```

## Deploy en VPS (producción)

El stack de producción es un solo `docker compose up`: la app (FrankenPHP + PHP
8.4) y PostgreSQL, con migraciones y seed idempotentes al arrancar.

```bash
# En el VPS, con el repo clonado:
docker compose build

# Configura las variables de producción (usa tu dominio y contraseña real):
cat > .env <<'EOF'
APP_KEY=base64:...        # genera una: docker compose run --rm app php artisan key:generate --show
APP_URL=https://bichofit.tudominio.com
SERVER_NAME=bichofit.tudominio.com:443
DB_PASSWORD=una-password-segura
EOF

docker compose up -d      # TLS automático vía Let's Encrypt (puertos 80/443 abiertos)
```

El primer usuario registrado es el tuyo; comparte el registro con tus amigos.

- Respaldo de datos: `docker compose exec db pg_dump -U bichofit bichofit > backup.sql`
- Logs: `docker compose logs -f app`
- Actualizar: `git pull && docker compose up -d --build`

> Sin dominio todavía: omite `SERVER_NAME` y la app responde en `http://IP:8080`
> (sin HTTPS no se puede instalar como PWA en iPhone).

## Roadmap

| Fase | Estado | Entregable |
|---|---|---|
| F1 | ✅ | Auth, catálogo, entrenamiento en vivo, historial, deploy Docker |
| F2 | ⬜ | Rutinas plantilla + gráficas de progreso (Chart.js) |
| F3 | ⬜ | OCR de báscula (Tesseract) + historial de composición corporal |
| F4 | ⬜ | PWA installable, timer de descanso, pulido UI móvil |
