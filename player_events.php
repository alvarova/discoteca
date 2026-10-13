<?php
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('ok' => false));
    exit;
}

$pista = trim((string) ($_POST['pista'] ?? ''), '/');
$titulo = trim((string) ($_POST['titulo'] ?? ''));
$artista = trim((string) ($_POST['artista'] ?? ''));
$categoria = trim((string) ($_POST['categoria'] ?? ''));
if (!preg_match('#^[A-Za-z0-9_-]+/[A-Za-z0-9_-]+$#', $pista) || $titulo === '') {
    http_response_code(400);
    echo json_encode(array('ok' => false));
    exit;
}

$db = @new mysqli('localhost', 'root', '', 'discoteca');
if ($db->connect_errno) {
    http_response_code(500);
    echo json_encode(array('ok' => false));
    exit;
}
$db->set_charset('utf8');
$terminal = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
$stmt = $db->prepare('INSERT INTO reproducciones (pista, titulo, artista, categoria, terminal, reproducido_en) VALUES (?, ?, ?, ?, ?, NOW())');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(array('ok' => false));
    exit;
}
$stmt->bind_param('sssss', $pista, $titulo, $artista, $categoria, $terminal);
$ok = $stmt->execute();
$stmt->close();
$db->close();
echo json_encode(array('ok' => $ok));
