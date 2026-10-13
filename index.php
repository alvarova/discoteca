<?
//
//Buscador de Contenidos Mp3
//

Class Historial{
  
  public $sBusquedas;
  public $sDescargas;
  
  function  procesar()
  {
	$xion = new DB("","","","");
  $xion->connect();
  $sql=
  $xion->query("SELECT * FROM `historial` WHERE id='1'");
  $xion->next_record();
  $this->cadena = $xion->Record["tema"] ?? "";
  $elementos = explode("|", $this->cadena, 2);
  $this->sBusquedas = $elementos[0] ?? "";
  $this->sDescargas = $elementos[1] ?? "";
  }
  
  function Historial()
  {
    $this->procesar();
  }
  
  function getBusquedas()
  {
    return $this->sBusquedas;
  }
  
  function getDescargas()
  {
    return $this->sDescargas;
  }
  
  
  function poner()
  {
  
  }


}


$nivel=0;  //Reseteo el nivel de acceso 0 bajo   9 alto
$usr = "visitante";
$objeto = "";
//setcookie("usr", "usr", time()+3600);
$usr2 = $_COOKIE["cookieusr"] ?? "Usuario";

$pas2 = $_COOKIE["cookiepas"] ?? "";

//$nivel=validar($usr2, $pas2);
$tiempo_inicio = microtime_float();

$base = __DIR__;
INCLUDE_ONCE($base . "/engine/id3v2.php");
INCLUDE_ONCE($base . "/engine/conf.php");
INCLUDE_ONCE($base . "/engine/config.php");   //Incluyo soporte para DB
INCLUDE_ONCE($base . "/engine/util.inc.php");  //Inclusion del PEAR 

inicializar($tpl, $base . "/search.html");       //Inicializo la plantilla

if (strtoupper($usr2)=="OPERADOR") $usr="root";

$historia = new Historial();
$c=$historia->getBusquedas();

 
$tpl->setVariable("titulohead", "Discoteca - Buscador V1.2");
$tpl->setVariable("dia", date('j/n/Y')); //Seteo la fecha para la plantilla
$buscar = trim((string) ($_POST['buscar'] ?? $_GET['buscar'] ?? ""));
$cat = (string) ($_POST['cat'] ?? $_GET['cat'] ?? "todas");
$categorias_validas = array("todas", "folklore", "tango", "latinos", "boleros", "espanioles", "italianos", "franceses", "brasil", "jazz", "rockinternacional", "rocknacional", "cumbia", "rafagasfx", "melodias", "salsa");
if (!in_array($cat, $categorias_validas, true)) $cat = "todas";
$cantidad = 20;
$pagina = max(0, (int) ($_GET['pag'] ?? 0));
$offset = $pagina * $cantidad;
$pagBegin = ""; $pagEnd = "";
$tpl->setVariable("buscada", $cat);
$tpl->setVariable("txtbusqueda", $buscar);
$conexion = new DB("", "", "", "");
$conexion->connect();
if ($buscar === "") {
  $filtro_categoria = $cat === "todas" ? "" : " WHERE categoria='" . $cat . "'";
  $sql = "SELECT DISTINCT * FROM temas" . $filtro_categoria . " ORDER BY RAND() LIMIT " . $cantidad;
} else {
  $terminos = preg_split('/\\s+/', $buscar, -1, PREG_SPLIT_NO_EMPTY);
  $condiciones = array();
  foreach ($terminos as $termino) { $condiciones[] = "comentario LIKE '%" . mysqli_real_escape_string($conexion->Link_ID, $termino) . "%'"; }
  $filtro_categoria = $cat === "todas" ? "" : "categoria='" . $cat . "' AND ";
  $sql = "SELECT DISTINCT * FROM temas WHERE " . $filtro_categoria . "(" . implode(" AND ", $condiciones) . ") ORDER BY id DESC LIMIT " . $offset . "," . $cantidad;
}
$conexion->query($sql);
$bloques = 0;
while($conexion->next_record()) {
  $titulo=$conexion->Record["titulo"]; $artista=$conexion->Record["artista"]; $album=$conexion->Record["album"]; $catego=$conexion->Record["categoria"]; $anio=$conexion->Record["anio"]; $ubicacion=$conexion->Record["ubicacion"]; $narchivo=$conexion->Record["narchivo"];
  $tpl->setCurrentBlock("bloque"); $tpl->setVariable("titulo", filtrAscii($titulo)); $tpl->setVariable("artista", filtrAscii($artista)); $tpl->setVariable("album", filtrAscii($album)); $tpl->setVariable("anio", filtrAscii($anio));
  if (file_exists($narchivo)) $ubicacion=$narchivo;
  $tpl->setVariable("download", $ubicacion); $cod=explode('.', $narchivo); $tpl->setVariable("id", ltrim($cod[1] ?? '', "/")); $tpl->setVariable("categ", $catego);
  if ($usr!="root") { $tpl->setVariable("DownOff1", " <!-- "); $tpl->setVariable("download", "Sin acceso"); $tpl->setVariable("DownOff2", " --> "); }
  $bloques++; $tpl->setVariable("blk", $bloques); $tpl->parseCurrentBlock("bloque");
}
$conexion->close();$recientes = new DB("", "", "", "");
$recientes->connect();
$terminal_reciente = mysqli_real_escape_string($recientes->Link_ID, ip());
$recientes->query("SELECT titulo, artista, categoria FROM reproducciones WHERE terminal='" . $terminal_reciente . "' ORDER BY id DESC LIMIT 10");
while ($recientes->next_record()) {
  $tpl->setCurrentBlock("recentplay");
  $tpl->setVariable("rp_titulo", filtrAscii($recientes->Record["titulo"]));
  $tpl->setVariable("rp_artista", filtrAscii($recientes->Record["artista"]));
  $tpl->setVariable("rp_categoria", filtrAscii($recientes->Record["categoria"]));
  $tpl->parseCurrentBlock("recentplay");
}
$recientes->close();$tiempo_final = microtime_float();
$tiempo = $tiempo_final - $tiempo_inicio;
//echo $_COOKIE["CookieDePrueba"];
$sig=$pagina+1;
$ant=$pagina-1;
if ($ant<0) {
 $ant=0;
}

$anterior="<a href='index.php?pag=".$ant."&buscar=".$buscar."&cat=".$cat."'><< Anterior </a>";
$siguiente="<a href='index.php?pag=".$sig."&buscar=".$buscar."&cat=".$cat."'>Siguiente >></a>";
$tpl->setVariable("paginado", $anterior." - ".$siguiente);
$tpl->setVariable("pagBegin", $pagBegin);
$tpl->setVariable("pagEnd", $pagEnd);
if ((strtoupper($usr2)=="JAVIER")&&($pas2==crypt("radio"))) $usr2="Administrador";
$tpl->setVariable("usr", strtoupper($usr2));
$tpl->setVariable("ubica", ip());




cargaCadena($busqueda, $descargas);
//echo $busqueda;
if ($buscar!="") guardaBusqueda($buscar, "1", $busqueda, $descargas, $cat);

//------------------> Inicio Ultima Busquedas

if ($bloques>2)
{
//$b=explode("#", $busqueda);
$c=0;  //acmulador;
for ($i=(count($busqueda)-1); $i>=0; $i--)
{
          ++$c;
          $links=explode("#", $busqueda[$i]);
          $tpl->setCurrentBlock("topbusqueda");
          $tpl->setVariable("tb", ($c).") ".$links[0]."<br/>");
          $tpl->setVariable("tb2", "index.php?pag=0&buscar=".$links[0]."&cat=".($links[1] ?? "todas"));
          $tpl->parseCurrentBlock("topbusqueda");
}
} else
{
$tpl->setvariable("busbeginoff1", "<!-- ");
$tpl->setvariable("busbeginoff2", "<!-- ");
$tpl->setvariable("busbeginoff3", "<!-- ");
$tpl->setvariable("busendoff1", " -->");
$tpl->setvariable("busendoff2", " -->");
$tpl->setvariable("busendoff3", " -->");
}

//------------------> Inicio Ultimos Bajados
for ($i=1; $i<10; $i++)
{
          $tpl->setCurrentBlock("topbajados");
         // $tpl->setVariable("tb", $i.")".filtrAscii($titulo)."<br/>");
          $tpl->parseCurrentBlock("topbajados");
}


$tpl->setVariable("tiempo", "Tiempo de carga: ".substr($tiempo,0,5). " segundos.");
$tpl->show();

//-----------------------Funciones ----------------------------
//
// Funcion para cargar obtener los resultados que se consultaron
//
//*************************************************************


function cargaCadena(&$busqueda, &$descargas)
{
 //Levanto la cadena con la data de ultimas busquedas y ultimas descargas
 // Items del 1 al 10 son busquedas del 11 al 20 son descargas
 // Separo cada item de cada busqueda o descargas con =
	
  $xion = new DB("","","","");
  $xion->connect();
  $sql=
  $xion->query("SELECT * FROM `historial` ORDER BY Id ASC limit 0,10 ");
  
  $lista_busca = array();
  while ($xion->next_record())
  {
    $lista_busca[]= $lista=$xion->Record["tema"];
  }
  
  //$archivo=$xion->Record["archivo"];
  //$elementos = explode("|", $this->cadena, 2);
  $busqueda=$lista_busca;
  $descargas = "";
}


//------------------------------------------------
//Funcion de reemplazo para Busquedas y Descargas
//------------------------------------------------

function guardaBusqueda($cadena, $id, $busqueda, $descargas, $categoria, $usu='visitante')
{
// $cadena, es la ultima cadena a agregar en la DB
//si $id = 1  guardo busquedas
//si $id = 2  guardo descargas

$bus="";
$des="";
$cadena2=filtrAscii($cadena);
$cadena2.="#".$categoria;
$xion = new DB("","","","");
$xion->connect();
if ($id==1)
 {
  $sql="DELETE FROM historial WHERE id='1'";
  $xion->query($sql);
  for ($li=2; $li<11; $li++)
  {
   $lo=$li-1;
   $sql="UPDATE historial SET id='".$lo."' WHERE id='".$li."'";
   $xion->query($sql);
  }
  $hoy = date("Y-m-d G:i:s");
  $ubicacion=ip();
  $sql="INSERT INTO historial (tema, archivo, usuario, fecha, terminal, tipo) VALUES ('".htmlentities($cadena2,ENT_QUOTES)."', '', '".$usu."', '".$hoy."', '".$ubicacion."', '".(int)$id."')";
  $xion->query($sql);
  $inserted=$xion->insert_id();
  $sql="UPDATE historial SET id='10' WHERE id='".$inserted."'";
  $xion->query($sql);

 } else {
  //$sql="UPDATE historial SET tema='".htmlentities($grabar,ENT_QUOTES)."' WHERE id='1'";
  //$xion->query($sql);
  $sql="DELETE FROM historial WHERE id='11'";
  $xion->query($sql);
  for ($li=12; $li<20; $li++)
  {
   $lo=$li-1;
   $sql="UPDATE historial SET id='".$lo."' WHERE id='".$li."'";
   $xion->query($sql);
  }
  $hoy = date("Y-m-d G:i:s");
  $ubicacion=ip();
  $sql="INSERT INTO historial (tema, archivo, usuario, fecha, terminal, tipo) VALUES ('".htmlentities($cadena2,ENT_QUOTES)."', '', '".$usu."', '".$hoy."', '".$ubicacion."', '".(int)$id."')";
  $xion->query($sql);
  $inserted=$xion->insert_id();
  $sql="UPDATE historial SET id='20' WHERE id='".$inserted."'";
  $xion->query($sql);
 }
  $xion->close();
 
}

function filtrAscii($cadena)
{
$sale="";

$pasoA  = str_replace("a", "a", $cadena);
$pasoB  = str_replace("e", "e", $pasoA);
$pasoC  = str_replace("i", "i", $pasoB);
$pasoD  = str_replace("o", "o", $pasoC);
$pasoE  = str_replace("u", "u", $pasoD);
$pasoF  = str_replace("A", "A", $pasoE);
$pasoG  = str_replace("E", "E", $pasoF);
$pasoH  = str_replace("I", "I", $pasoG);
$pasoI  = str_replace("O", "O", $pasoH);
$cadena  = str_replace("U", "U", $pasoI);

for ($i=0; $i<(strlen($cadena)); $i++)
{  
   if ((ord($cadena[$i])>31) and (ord($cadena[$i])<123)) 
   {
     $sale.=$cadena[$i];
   }else if ((ord($cadena[$i])==160) || (ord($cadena[$i])==131)||(ord($cadena[$i])==132)||(ord($cadena[$i])==133)||(ord($cadena[$i])==134))
    $sale.="a";
   else if ((ord($cadena[$i])==130) || (ord($cadena[$i])==136)||(ord($cadena[$i])==137)||(ord($cadena[$i])==138))
    $sale.="e";
   else if ((ord($cadena[$i])==139) || (ord($cadena[$i])==140)||(ord($cadena[$i])==141)||(ord($cadena[$i])==161))
    $sale.="i";
   else if ((ord($cadena[$i])==147) || (ord($cadena[$i])==148)||(ord($cadena[$i])==149)||(ord($cadena[$i])==162))
    $sale.="o";
   else if ((ord($cadena[$i])==150) || (ord($cadena[$i])==151)||(ord($cadena[$i])==163)) $sale.="u"; 

}
$solo = str_replace("ñ", "n", $sale);
$sale = str_replace("Ñ", "N", $solo);

return $sale;
}


function ip()
{
$dire=$_SERVER['REMOTE_ADDR'];
return (gethostbyaddr($dire));

}

function setlevel()
//Genera el nivel de confianza segun la PC, para nombres estaticos
{
//$clientes=["Aireamaux"]["Server"]["Gercom"]["Aiream"]["Aire-fmx"]["Ruben2"];
$bandera=false;
for ($lista=0; $lista<count($clientes)-1; $lista++)
     if (ip()==$clientes[$lista]) setcookie("CookieDePrueba", "usr", time()+3600);
}



//Funcion para tomar el tiempo...
function microtime_float()
{
    list($useg, $seg) = explode(" ", microtime());
    return ((float)$useg + (float)$seg);
}



function validar($us, $pa)
{
	$xion = new DB("","","","");
  $xion->connect();
  $sql=
  $xion->query("SELECT * FROM `usuario` WHERE nombre='$us'");
  $xion->next_record();
  $pase=$xion->Record["password"];
  if ($pa==crypt($pase)) {
    $level=$xion->Record["nivel"];
  }
  
}

?>
