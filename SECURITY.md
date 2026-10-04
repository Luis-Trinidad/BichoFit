# Política de Seguridad

## Versiones soportadas

| Versión       | Soportada |
| ------------- | --------- |
| main (branch) | ✅        |

BichoFit se despliega desde `main`; solo la última versión recibe parches
de seguridad.

## Reportar una vulnerabilidad

Si encuentras una vulnerabilidad de seguridad, **NO abras un issue público**.

En su lugar:

1. Usa [GitHub Security Advisories](https://github.com/Luis-Trinidad/BichoFit/security/advisories/new)
   ("Report a vulnerability") — privado y solo visible para los mantenedores
2. Incluye: descripción, pasos para reproducir, impacto estimado y, si
   aplica, una prueba de concepto

Responderemos en un máximo de **72 horas** y trabajaremos contigo en un
fix antes de cualquier divulgación pública.

## Alcance y notas

- La app está diseñada para **self-hosted** con usuarios en la misma
  instancia; el aislamiento entre usuarios está cubierto por policies
  en todos los recursos (probado en la suite)
- La `APP_KEY` de Laravel es crítica: quien la posea puede descifrar
  sesiones y cookies. Nunca la compartas ni la subas al repo
- Los deployments con HTTPS gestionado por Traefik/Dokploy delegan la
  seguridad del transporte a esa capa

## Mejores prácticas al desplegar

- `APP_DEBUG=false` en producción
- Contraseñas fuertes de BD (especialmente si la BD es accesible desde
  otra red)
- Mantén Docker y el sistema operativo del VPS actualizados
