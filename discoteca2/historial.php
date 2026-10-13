<?php
/* Registro compatible con PHP 7.3+ y la estructura actual de discoteca.historial. */
function registrar_reproduccion($theme, $archivo, $data, $style)
{
    require __DIR__ . '/localconf.php';

    $conexion = @new mysqli($dbhost, $dbuname, $dbpass, $dbname);
    if ($conexion->connect_errno) {
        error_log('Discoteca: no se pudo registrar la reproducción: ' . $conexion->connect_error);
        return false;
    }

    $conexion->set_charset('utf8');
    $usuario = substr(trim($data . ($style !== '' ? '|' . $style : '')), 0, 50);
    $terminal = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 15);
    $tipo = 2;
    $consulta = $conexion->prepare(
        'INSERT INTO historial (tema, archivo, usuario, fecha, terminal, tipo) VALUES (?, ?, ?, NOW(), ?, ?)'
    );
    if ($consulta === false) {
        error_log('Discoteca: no se pudo preparar historial: ' . $conexion->error);
        $conexion->close();
        return false;
    }

    $consulta->bind_param('ssssi', $theme, $archivo, $usuario, $terminal, $tipo);
    $ok = $consulta->execute();
    if (!$ok) {
        error_log('Discoteca: no se pudo registrar reproducción: ' . $consulta->error);
    }
    $consulta->close();
    $conexion->close();
    return $ok;
}
