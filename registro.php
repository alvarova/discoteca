<?php
declare(strict_types=1);

$categorias = array('folklore','tango','latinos','boleros','espanioles','italianos','franceses','brasil','jazz','rockinternacional','rocknacional','cumbia','rafagasfx','melodias','salsa');
$mensaje = '';
$error = '';

function e_registro(string $valor): string { return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $artista = trim((string) ($_POST['artista'] ?? ''));
    $album = trim((string) ($_POST['album'] ?? ''));
    $categoria = (string) ($_POST['categoria'] ?? '');
    $anio = trim((string) ($_POST['anio'] ?? ''));
    $archivo = $_FILES['audio'] ?? null;
    if ($titulo === '' || $artista === '' || !in_array($categoria, $categorias, true)) {
        $error = 'Completá título, artista y una categoría válida.';
    } elseif (!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
        $error = 'No se pudo recibir el archivo de audio.';
    } elseif ($archivo['size'] <= 0 || $archivo['size'] > 104857600) {
        $error = 'El archivo debe pesar entre 1 byte y 100 MB.';
    } else {
        $extension = strtolower(pathinfo((string) $archivo['name'], PATHINFO_EXTENSION));
        $permitidas = array('mp3','wav','ogg','m4a');
        if (!in_array($extension, $permitidas, true)) {
            $error = 'Formato no permitido. Usá MP3, WAV, OGG o M4A.';
        } else {
            $nombreSeguro = date('YmdHis') . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
            $destinoFisico = __DIR__ . '/discoteca/temporal/' . $nombreSeguro;
            if (!move_uploaded_file($archivo['tmp_name'], $destinoFisico)) {
                $error = 'No se pudo guardar el archivo temporal.';
            } else {
                $db = new mysqli('localhost', 'root', '', 'discoteca');
                if ($db->connect_errno) {
                    @unlink($destinoFisico);
                    $error = 'No se pudo registrar la carga en la base de datos.';
                } else {
                    $db->set_charset('utf8');
                    $ruta = 'temporal/' . $nombreSeguro;
                    $terminal = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
                    $anioDb = ctype_digit($anio) && (int) $anio >= 1900 && (int) $anio <= 2100 ? (int) $anio : null;
                    $stmt = $db->prepare('INSERT INTO registros_temporales (archivo_original, archivo_ruta, titulo, artista, album, anio, categoria, terminal, creado_en) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
                    $stmt->bind_param('sssssiss', $archivo['name'], $ruta, $titulo, $artista, $album, $anioDb, $categoria, $terminal);
                    if ($stmt->execute()) $mensaje = 'Registro temporal creado correctamente.';
                    else { @unlink($destinoFisico); $error = 'No se pudo guardar el registro.'; }
                    $stmt->close(); $db->close();
                }
            }
        }
    }
}

$items = array();
$db = @new mysqli('localhost', 'root', '', 'discoteca');
if (!$db->connect_errno) {
    $db->set_charset('utf8');
    $result = $db->query('SELECT titulo, artista, album, anio, categoria, archivo_ruta, creado_en FROM registros_temporales ORDER BY id DESC LIMIT 30');
    if ($result) while ($fila = $result->fetch_assoc()) $items[] = $fila;
    $db->close();
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Registro temporal · Discoteca LT10</title><style>
:root{--green:#087b32;--bg:#f5f8f6;--surface:#fff;--text:#19251f;--muted:#5a6a60;--border:#dbe5de}*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font:14px system-ui,-apple-system,"Segoe UI",Arial,sans-serif}.wrap{max-width:980px;margin:0 auto;padding:24px}.top{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px}.top a{color:var(--green);font-weight:700;text-decoration:none}.card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;margin-bottom:18px;box-shadow:0 5px 22px rgb(12 44 24 / 7%)}h1{font-size:23px;margin:0 0 6px}h2{font-size:16px;margin:0 0 12px}.muted{color:var(--muted)}.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.field{display:flex;flex-direction:column;gap:6px}.field.full{grid-column:1/-1}label{font-weight:700}input,select{height:42px;border:1px solid var(--border);border-radius:8px;padding:0 10px;font:inherit;background:#fff}.submit{margin-top:18px;background:var(--green);color:#fff;border:0;border-radius:8px;padding:12px 17px;font-weight:700;cursor:pointer}.ok,.error{padding:12px;border-radius:8px;margin-bottom:14px}.ok{background:#e4f3e9;color:#075f29}.error{background:#fff0ef;color:#a52a20}.entry{display:grid;grid-template-columns:1fr auto;gap:10px;padding:12px 0;border-top:1px solid var(--border)}.entry:first-of-type{border-top:0}.entry strong{display:block}.entry small{color:var(--muted)}audio{width:230px;max-width:100%}@media(max-width:650px){.wrap{padding:14px}.grid{grid-template-columns:1fr}.field.full{grid-column:auto}.entry{grid-template-columns:1fr}.top{align-items:flex-start;gap:10px;flex-direction:column}}
</style></head><body><main class="wrap"><header class="top"><div><h1>Registro temporal</h1><span class="muted">Carga de audios para revisión; no altera el catálogo definitivo.</span></div><a href="index.php">← Volver a Discoteca</a></header>
<section class="card"><?php if ($mensaje): ?><div class="ok"><?= e_registro($mensaje) ?></div><?php endif; ?><?php if ($error): ?><div class="error"><?= e_registro($error) ?></div><?php endif; ?><form method="post" enctype="multipart/form-data"><div class="grid"><div class="field"><label for="titulo">Título *</label><input id="titulo" name="titulo" required maxlength="255"></div><div class="field"><label for="artista">Artista *</label><input id="artista" name="artista" required maxlength="255"></div><div class="field"><label for="album">Álbum</label><input id="album" name="album" maxlength="255"></div><div class="field"><label for="anio">Año</label><input id="anio" name="anio" inputmode="numeric" pattern="[0-9]{4}" maxlength="4"></div><div class="field"><label for="categoria">Categoría *</label><select id="categoria" name="categoria" required><?php foreach ($categorias as $cat): ?><option value="<?= e_registro($cat) ?>"><?= e_registro($cat === 'rafagasfx' ? 'Ráfagas / Fx' : ucfirst($cat)) ?></option><?php endforeach; ?></select></div><div class="field"><label for="audio">Archivo de audio *</label><input id="audio" name="audio" type="file" accept=".mp3,.wav,.ogg,.m4a,audio/*" required></div></div><button class="submit" type="submit">Subir registro temporal</button></form></section>
<section class="card"><h2>Últimas cargas temporales</h2><?php if (!$items): ?><p class="muted">Todavía no hay audios temporales cargados.</p><?php endif; ?><?php foreach ($items as $item): ?><article class="entry"><div><strong><?= e_registro($item['titulo']) ?></strong><span><?= e_registro($item['artista']) ?></span><br><small><?= e_registro($item['categoria']) ?> · <?= e_registro($item['album']) ?> · <?= e_registro((string) $item['anio']) ?> · <?= e_registro($item['creado_en']) ?></small></div><audio controls preload="none" src="./discoteca/<?= e_registro($item['archivo_ruta']) ?>"></audio></article><?php endforeach; ?></section></main></body></html>
