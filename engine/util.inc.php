<?
	$path = dirname(dirname(dirname(__FILE__)));
	require_once(__DIR__ . "/PEAR.php");
	require_once(__DIR__ . "/IT.php");
 		
	function inicializar(&$template,$archivo="") {
    	$template= new HTML_Template_IT();
	    if(!is_file($archivo)){
	    	die("EL ARCHIVO $archivo ES INEXISTENTE O NO ESTA EN EL LUGAR ESPECIFICADO");	
	    }
		$template->loadTemplatefile($archivo);
    	
		$template->setVariable("fin","");
	 }
	
	function convertirFechaHora($fecha,$disc_fecha="/",$disc_hora=" "){
		$aDate = explode(" ",$fecha);
		$aFecha = explode("-",$aDate[0]);
		
		return $aFecha[2].$disc_fecha.$aFecha[1].$disc_fecha.$aFecha[0].$disc_hora.$aDate[1];
	}
	
	function convertirFecha($fecha,$disc_fecha="/"){
		$aDate = explode(" ",$fecha);
		$aFecha = explode("-",$aDate[0]);
		
		return $aFecha[2].$disc_fecha.$aFecha[1].$disc_fecha.$aFecha[0];
	}
	
	function obtenerExtension($file){
		$aFile = explode(".",$file);
		return array_pop($aFile);
	}
	
	function get_prev_date($d) {
   		$aFecha = explode("-",$d);
		$year = "";
		$month = "";
		$day = "";
		
   		if ($aFecha[1] == 1 && $aFecha[2] == 1) {
       		$day = 31;
        	$month = 12;
        	$year = $aFecha[0] - 1;
        	return $year."-".$month."-".$day;
   		}
					
   		if ($aFecha[1] == 3) { //for leap years and feb
       		if ($aFecha[2] == 1 && date("L", mktime(0, 0, 0, $aFecha[1], $aFecha[2], $aFecha[0])) == 1)
           		$ret->day = 29;
       		else if ($aFecha[2] == 1) 
           		$day = 28;
			    $month = 2;
		        $year  = $year ;
   							
       		return $year."-".$month."-".$day;
   		}
						
   		if ($aFecha[2] == 1) {
       		if ($aFecha[1] == 5 || $aFecha[1] == 7 || $aFecha[1] == 8 || $aFecha[1] == 10 || $aFecha[1] == 12) 
           		$day = 30;
       		else
           		$day = 31;
       		
	   		$month = $aFecha[1] - 1;
	        $year = $aFecha[0];
       		
    	   return $year."-".$month."-".$day;
   		}
		
		$day  = $aFecha[2] - 1;
	    $month = $aFecha[1];
	    $year  = $aFecha[0];
		
		return $year."-".$month."-".$day;
   }
   
   function tiempoLectura($texto){
		$spc=0.045; //segundos por caracter
		
		$tiempo = $spc * strlen($texto);
		$hora = floor($tiempo/3600);
			
		if ($hora<=0) $hora='';
			
		else $hora = $hora."h ";
			
		$mins = floor($tiempo/60)-(floor($tiempo/3600)*60).":";
		$segs = (integer) ($tiempo%60);
			
		if (strlen($segs)<=1) $segs = "0$segs";
			
		$segs = "$segs";
		$tiempo = "$hora$mins$segs";
			
		return $tiempo;
	}		
		
	function cortarParrafo($texto,$indice,$cantCar) {
		$result = array();
			
		if(strlen($texto) < $cantCar) {
			$result[0] = 0;
			$result[1] = 0;
			$result[2] = $texto;
		}	
		else {
			$pos = $cantCar + $indice;
			$posAux = $pos;
				
			while(($texto[$posAux] != "\n") && ($posAux < strlen($texto))) $posAux++;
				
			$textCut = substr($texto,$indice,($posAux-$indice));
				
			$result[0] = $posAux;
			$result[1] = $posAux - $pos;
			$result[2] = $textCut;
		}
		
		return $result;
	}
?>
