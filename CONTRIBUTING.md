# Contribuir a BichoFit

¡Gracias por tu interés en contribuir! 🦎 Toda contribución es bienvenida:
código, documentación, traducciones, reportes de bugs o ideas.

## Cómo empezar

1. **Fork** el repositorio
2. Clona tu fork:
    ```bash
    git clone https://github.com/TU-USUARIO/BichoFit.git
    cd BichoFit
    ```
3. Instala dependencias y levanta el entorno (ver [README](README.md#desarrollo-local))
4. Crea una rama para tu cambio:
    ```bash
    git checkout -b mi-mejora
    ```

## Antes de abrir un Pull Request

- ✅ Los tests pasan: `php artisan test` (todos, no solo los tuyos)
- ✅ El frontend compila: `npm run build`
- ✅ Código formateado con Pint: `./vendor/bin/pint`
- ✅ Sin credenciales ni datos personales en el diff
- ✅ Si agregas una funcionalidad visible, está en **español** (la UI completa es en español)
- ✅ Si cambias el modelo de datos: migración + tests incluidos

### Convenciones

- **Commits**: mensajes descriptivos en minúsculas, estilo
  `feat: ...` / `fix: ...` / `docs: ...` (Conventional Commits)
- **PHP**: sigue el estilo de Pint (PSR-12)
- **Svelte/TS**: sigue el formato del proyecto (`npm run format` si está disponible)
- **UI nueva**: móvil primero, sin emojis en la interfaz, usa los iconos
  Lucide existentes

## Áreas que agradecen contribuciones

- 🌎 **Traducciones**: la app está en español; agregar i18n para más idiomas
  es una contribución valiosa
- 🏋️ **Catálogo de ejercicios**: mejorar traducciones de nombres,
  sinónimos de equipos, nuevos grupos musculares
- 📊 **Gráficas y progreso**: más métricas, mejores visualizaciones
- 📱 **PWA**: service worker offline, notificaciones push
- 🧪 **Tests**: nunca sobran

## Reportar bugs

Abre un [issue](https://github.com/Luis-Trinidad/BichoFit/issues) con:

- Descripción clara del problema
- Pasos para reproducirlo
- Comportamiento esperado vs. obtenido
- Entorno (navegador, dispositivo, versión)

## Código de conducta

Sé respetuoso. Detalles en [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).
