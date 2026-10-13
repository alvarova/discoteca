# LT10 Discoteca 2.0 — cambios

La plantilla `search.html` pasó a una consola responsive sin dependencias externas:

- cabecera institucional compacta con logo local LT10;
- tokens de color claro/oscuro y preferencia persistida en `localStorage`;
- resultados en filas compactas, con título neutro destacado y artista secundario;
- buscador y categorías existentes preservados;
- un único `HTMLAudioElement` por pestaña, progreso, seek, volumen, play/pausa/stop y manejo de error;
- panel lateral en escritorio y minirreproductor fijo en mobile;
- rutas locales del catálogo y de los assets verificadas.

No se modificó esquema de base de datos, consultas, autenticación ni servicios del servidor.
