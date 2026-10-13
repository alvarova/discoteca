<?php
header('Content-Type: application/json; charset=utf-8');
$offset = max(0, (int) ($_GET['offset'] ?? 0));
$limit = min(60, max(1, (int) ($_GET['limit'] ?? 36)));
$db = new mysqli('localhost', 'root', '', 'discoteca');
if ($db->connect_errno) { http_response_code(500); echo json_encode(array('ok'=>false)); exit; }
$db->set_charset('utf8');
$sql = "SELECT t.id,t.titulo,t.artista,t.narchivo,c.posicion,c.atajo,c.fijado FROM sampler_fx_config c INNER JOIN temas t ON t.id=c.tema_id WHERE t.categoria='rafagasfx' ORDER BY c.posicion LIMIT ?,?";
$stmt = $db->prepare($sql); $stmt->bind_param('ii', $offset, $limit); $stmt->execute(); $rs = $stmt->get_result(); $items=array();
while ($r=$rs->fetch_assoc()) { $partes=explode('.', $r['narchivo']); $ruta=ltrim($partes[1] ?? '', '/'); $r['audio']='discoteca/'.$ruta.'.mp3'; $items[]=$r; }
$total=(int)$db->query("SELECT COUNT(*) n FROM sampler_fx_config c INNER JOIN temas t ON t.id=c.tema_id WHERE t.categoria='rafagasfx'")->fetch_assoc()['n'];
echo json_encode(array('ok'=>true,'items'=>$items,'total'=>$total,'next'=>$offset+count($items)));
