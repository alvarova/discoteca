# LT10 Discoteca 2.0 — auditoría inicial

Fecha: 2026-10-09. Servidor: `serverv` (Ubuntu 18.04), XAMPP Apache/PHP 7.3.14.

- Entrada y consulta: `/opt/lampp/htdocs/discoteca/index.php` renderiza `search.html` con IT.php.
- Datos: base MariaDB `discoteca`, tabla `temas`; búsqueda por título, artista y álbum, con categorías existentes.
- Audio: MP3 reales bajo `/opt/lampp/htdocs/discoteca/discoteca/<categoria>/<id>.mp3`; Apache responde `audio/mpeg` y `Accept-Ranges`.
- Interfaz: `search.html` es la plantilla. Logo institucional reutilizable: `discoteca/includes/img/loguito.jpg`.
- Historial: tabla `historial`; preservado y ya adaptado anteriormente a PHP 7.3.
- Reproductor heredado: popup `play.php` inexistente en la raíz y JWPlayer/Flash obsoleto; se reemplazó por audio HTML5 directo.

Riesgo conocido: por render PHP tradicional, cambiar página mediante búsqueda/paginación recarga el documento y no puede conservar un objeto de audio en memoria. Se mantiene el comportamiento funcional actual; la persistencia entre navegaciones requeriría una etapa posterior sin recarga o un reproductor compartido fuera del documento.
