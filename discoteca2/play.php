<?php
/* Reproductor HTML5 para el catálogo histórico de Discoteca. */
$theme = trim((string) ($_GET['theme'] ?? ''), '/');
$data = (string) ($_GET['data'] ?? '');
$style = trim((string) ($_GET['style'] ?? ''), '/');

/* Solo se admiten segmentos de ruta del catálogo: evita salir de discoteca/. */
$segments = array_filter(explode('/', ($style !== '' ? $style . '/' : '') . $theme), 'strlen');
$validPath = !empty($segments);
foreach ($segments as $segment) {
    if (!preg_match('/^[A-Za-z0-9_-]+$/', $segment)) {
        $validPath = false;
        break;
    }
}

$relativeTrack = implode('/', $segments);
$audioFsPath = __DIR__ . '/../discoteca/' . $relativeTrack . '.mp3';
$audioUrl = '../discoteca/' . implode('/', array_map('rawurlencode', $segments)) . '.mp3';
$trackExists = $validPath && is_file($audioFsPath);

if ($trackExists) {
    /* El historial se registra sin la capa ADO/mysql_* que ya no existe en PHP 7. */
    require_once __DIR__ . '/historial.php';
    registrar_reproduccion($theme, $relativeTrack, $data, $style);
}

$titleParts = explode('-', $data, 2);
$title = trim($titleParts[0] ?? '') ?: 'Tema sin título';
$artist = trim($titleParts[1] ?? '');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
<style>
body { margin: 0; background: #203e2b; color: #f2f2f2; font: 14px Arial, sans-serif; }
main { box-sizing: border-box; max-width: 460px; margin: auto; padding: 16px; }
h1 { margin: 0 0 4px; font-size: 16px; }
p { margin: 0 0 12px; color: #d3e1d4; }
audio { width: 100%; }
.error { padding: 12px; border: 1px solid #a55; background: #512b2b; }
a { color: #b9d7ff; }
</style>
</head>
<body>
<main>
  <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
  <?php if ($artist !== ''): ?><p><?= htmlspecialchars($artist, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
  <?php if ($trackExists): ?>
    <audio controls autoplay preload="metadata">
      <source src="<?= htmlspecialchars($audioUrl, ENT_QUOTES, 'UTF-8') ?>" type="audio/mpeg">
      Tu navegador no admite la reproducción de audio HTML5.
    </audio>
    <p><a href="<?= htmlspecialchars($audioUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Abrir archivo MP3</a></p>
  <?php else: ?>
    <p class="error">No se encontró el archivo de audio solicitado.</p>
  <?php endif; ?>
</main>
</body>
</html>
