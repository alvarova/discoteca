# LT10 Discoteca 2.0 — pruebas

Ejecutado el 2026-10-09:

- POST de búsqueda `Lucia` / `todas`: renderizó la plantilla nueva, resultados y sin errores PHP visibles.
- URL de audio generada: `./discoteca/espanioles/17184.mp3`.
- HTTP 200 comprobado para MP3 `latinos/17315.mp3` y assets de interfaz.
- Revisión del HTML: una sola instancia `new Audio()` y controles asociados a filas reales.

Pendiente: validación manual en Chromium/Firefox con reproducción audible, prueba mobile real y segundo terminal LAN.
