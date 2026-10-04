# Changelog

Todos los cambios notables de BichoFit se documentan aquí.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.1.0/)
y este proyecto se adhiere a [Versionado Semántico](https://semver.org/lang/es/).

## [No publicado]

### Agregado

- Registro de entrenamientos: sesiones en vivo con series, reps y peso
  por serie, steppers rápidos y pre-llenado inteligente
- Rutinas semanales por día (Lunes-Domingo) con constructor por lotes
  (guardado único) y objetivos por ejercicio (`4x10`)
- Asistente de arranque: elegir rutina → elegir día → vista previa →
  cuenta regresiva 3·2·1
- Checklist de sesión: ejercicios completados/pendientes según objetivo,
  GIFs de guía visual, cronómetro en vivo
- Catálogo de 1,391 ejercicios en español (traducción automática del
  dataset) con búsqueda bilingüe y guías GIF/JPG
- Progreso: volumen semanal, progresión por ejercicio con 1RM estimado
  (Epley), récords personales y composición corporal (4 métricas)
- Cuerpo: OCR de báscula (Tesseract spa+eng) con confirmación editable,
  diccionario OKOK International (Gaabor) y métricas extra capturadas
- PWA: manifest, ícono de pantalla de inicio, modal de nueva versión
  forzada
- Auth completa: correo/contraseña, passkeys, 2FA (sin verificación de
  correo)
- Deploy: Docker multi-stage con FrankenPHP + Tesseract, BD externa por
  `DB_URL`, migraciones/seed idempotentes al arrancar
- Dataset auto-instalable: seeder descarga el media (137 MB) en
  streaming la primera vez; extracción en un solo golpe
- CI: GitHub Actions corriendo la suite en push/PR
- UI 100% en español, incluidos mensajes de validación del servidor

[No publicado]: https://github.com/Luis-Trinidad/BichoFit/commits/main
