# BichoFit — Documento de Diseño

**Fecha:** 2026-09-30
**Estado:** Aprobado (v1)
**Tipo:** PWA de registro de entrenamientos y composición corporal, self-hosted

---

## 1. Visión

BichoFit es una aplicación web progresiva (PWA) para llevar el control completo del
progreso físico: rutinas de ejercicio con series, repeticiones y peso por serie;
gráficas de progreso en el tiempo; y registro de composición corporal (peso, grasa,
músculo, agua, etc.) medido por una báscula de bioimpedancia, capturado mediante
OCR de capturas de pantalla.

Pensada como app de iPhone (instalable desde Safari a pantalla de inicio), por
ahora implementada como PWA. Hosteada en VPS propio del autor.

## 2. Decisiones tomadas

| Decisión | Valor | Razón |
|---|---|---|
| Usuarios | El autor + 2-3 amigos | Cuentas simples email/contraseña, datos aislados por usuario |
| Báscula | Marca genérica/desconocida | OCR genérico + paso de confirmación editable; funciona con cualquier app |
| Conectividad | Siempre online | Sin sincronización offline; el service worker solo cachea el shell |
| Hosting | VPS propio | Todo self-hosted con Docker, cero dependencias externas |
| Base de datos | **PostgreSQL 16** | Contenedor dedicado en docker compose; JSONB real para métricas extra |
| Stack | Laravel 12 + Svelte 5 | Elección explícita del autor |

## 3. Stack tecnológico

| Capa | Tecnología | Notas |
|---|---|---|
| Backend | Laravel 12 (PHP 8.3) | Eloquent, Form Requests, Policies |
| Frontend | Svelte 5 + Inertia v2 | SPA sin API separada; datos vía props compartidas |
| Estilos | Tailwind CSS | Estética de app nativa móvil |
| Base de datos | PostgreSQL 16 | Contenedor docker dedicado |
| Auth | Laravel Breeze (Svelte) | Login/registro incluidos |
| OCR | Tesseract (binario en VPS) vía `thiagoalessio/tesseract_ocr` | Gratis, síncrono (2-5 s por imagen) |
| Gráficas | Chart.js | Integración directa en Svelte |
| PWA | manifest + service worker (shell cache) | Instalable en iOS via Safari |
| Deploy | Docker compose: FrankenPHP + Postgres | HTTPS automático (requisito iOS PWA) |

## 4. Arquitectura

```
┌───────────────────────────── VPS (docker compose) ─────────────────────────────┐
│                                                                                │
│  ┌──────────────────────────┐      ┌──────────────────────────┐                │
│  │  FrankenPHP (app Laravel)│      │  PostgreSQL 16           │                │
│  │  - HTTPS automático      │─────▶│  - volumen persistente   │                │
│  │  - PHP-FPM integrado     │      │  - pg_dump cron backup   │                │
│  │  - Svelte compilado      │      └──────────────────────────┘                │
│  │  - Tesseract binario     │                                                  │
│  └──────────────────────────┘                                                  │
└────────────────────────────────────────────────────────────────────────────────┘
            ▲
            │ HTTPS
            ▼
   iPhone / navegador (PWA instalada)
```

- Inertia elimina la necesidad de una API REST: los controladores Laravel renderizan
  páginas Svelte con props. Los uploads de imagen usan POST multipart normales.
- Si en el futuro se construye una app nativa SwiftUI, se exponen los mismos
  controladores como endpoints JSON (Laravel lo permite sin duplicar lógica).

## 5. Modelo de datos

```sql
users             id, name, email, password            -- Breeze

exercises         id, name, muscle_group, user_id NULL -- user_id null = catálogo
                                                     -- global sembrado (~50 ejercicios)

routines          id, user_id, name                    -- plantillas ("Push", "Pierna"...)
routine_items     id, routine_id, exercise_id, position, target  -- target ej. "3x8-12"

workout_sessions  id, user_id, date, routine_id NULL, notes,
                  started_at, finished_at

workout_sets      id, session_id, exercise_id, set_number,
                  reps, weight_kg NUMERIC(6,2), done BOOLEAN

body_scans        id, user_id, scanned_at, weight_kg, body_fat_pct,
                  muscle_mass_kg, water_pct, bmi, visceral_fat,
                  metabolic_age, extra JSONB,          -- métricas inesperadas
                  image_path NULL, source 'ocr'|'manual'
```

Notas:
- `body_scans.extra` (JSONB) absorbe cualquier métrica adicional de cualquier
  báscula (proteína, grasa ósea, etc.) sin migraciones futuras.
- `image_path` guarda opcionalmente la captura original como referencia.
- Aislamiento entre usuarios mediante Laravel Policies en todos los recursos.

## 6. Pantallas (páginas Svelte/Inertia)

1. **Hoy (dashboard)** — sesión activa o "Empezar entrenamiento", último registro
   corporal, resumen semanal.
2. **Entrenamiento activo** ⭐ — ejercicios del día; tap en ejercicio → agregar serie
   (reps + peso) en dos taps; marcar serie completada; timer de descanso.
3. **Historial** — sesiones pasadas con detalle.
4. **Progreso** — gráficas: progresión y PRs por ejercicio, volumen semanal,
   composición corporal en el tiempo.
5. **Escanear báscula** — subir captura → OCR → confirmar → guardar; historial.
6. **Rutinas** — CRUD de plantillas.
7. **Ejercicios** — catálogo global + crear propios.

## 7. Flujo OCR (composición corporal)

```
1. Usuario sube captura (galería) o foto (cámara, attribute capture)
2. POST multipart /body-scans/ocr
3. Laravel: valida imagen (máx 5MB, jpg/png/webp) → guarda temporal
4. Tesseract lee el texto de la imagen (langs: spa+eng)
5. Parser: diccionario de sinónimos ("Peso/Weight", "Grasa/Fat", "Músculo/Muscle",
   "Agua/Water", "IMC/BMI", "Grasa visceral", "Edad metabólica"...) + patrones
   numéricos (kg, %, años) → pares etiqueta/valor con confianza por campo
6. Respuesta JSON al frontend
7. Formulario PRE-LLENADO con lo detectado; campos de baja confianza resaltados
8. Usuario corrige/ajusta fecha → POST /body-scans → guardado
```

**Regla de oro:** el OCR es una conveniencia, nunca un bloqueo. Si falla, lee mal o
no detecta nada → formulario manual vacío. Entrada manual siempre disponible.

## 8. PWA en iPhone

- `manifest.json`: nombre BichoFit, íconos 192/512, display standalone, theme color.
- Service worker mínimo: cachea app shell e íconos; los datos siempre van al
  servidor (decisión: app siempre online).
- Instalación: Safari → Compartir → "Añadir a pantalla de inicio". Experiencia
  fullscreen con ícono propio.
- Subida de imágenes usa selector nativo de iOS (galería/cámara).
- HTTPS obligatorio → resuelto por FrankenPHP/Caddy en el VPS.

## 9. Manejo de errores

- Validación server-side con Form Requests en todos los recursos.
- Policies: un usuario jamás accede a datos de otro (403).
- OCR: timeout; si tarda o falla → entrada manual. Nunca un error terminal.
- Imágenes: límite 5MB, formatos jpg/png/webp, guardadas en `storage/` fuera del
  repo, respaldadas con el volumen.

## 10. Estrategia de pruebas

- **Feature tests (PHPUnit)**: CRUD de sesiones/series/rutinas/escaneos con
  factories; aislamiento entre usuarios (policies).
- **Parser OCR**: tests unitarios con fixtures (capturas de ejemplo de varias apps
  de báscula) para el diccionario y patrones numéricos.
- **Smoke manual** en iPhone Safari al final de cada fase.

## 11. Roadmap

| Fase | Entregable | Qué se puede hacer |
|---|---|---|
| **F1** | Scaffold Laravel+Svelte+Inertia, auth Breeze, catálogo de ejercicios, entrenamiento en vivo, historial, deploy básico en VPS | Usar la app real en el gym |
| **F2** | Rutinas plantilla + gráficas de progreso | Preparar el día anterior y ver avance |
| **F3** | OCR báscula + historial corporal | Escanear capturas y ver composición en el tiempo |
| **F4** | PWA installable + timer de descanso + pulido UI | Experiencia de app nativa en iPhone |

## 12. Fuera de alcance (por ahora — YAGNI)

- Modo offline / sincronización diferida
- App nativa SwiftUI
- Notificaciones push
- Compartir rutinas entre usuarios / red social
- Integración con APIs de básculas (HealthKit, Google Fit)
- Exportación a CSV/PDF
