<?php
error_reporting(E_ALL);
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
require_once "conexion.php";
require_once "config.php";
// require_once "./PHPMailer/PHPMailerAutoload.php";
require_once "./fpdf185/fpdf.php";
$sid=session_id();
if (!ini_get('session.auto_start') and empty($sid)) {session_start();}
//require_once "page.ext";



function ver_informe($canal, $codigo_monarch, $fec_ini, $fec_fin){
	global $tipobd_totvsDev2, $conexion_totvsDev2;

	$querysel = "SELECT   ZC6_OC,  ZC6_CANAL,   ACY_DESCRI,   ZC6_LOCAL,   ZC6_DLOCAL,   ZC6_INTCOD,
   					 	ZC6_INTDES,    SUM(ZC6_CANT) AS ZC6_CANT,    ZC6_PRCVEN,    SUM(ZC6_VALOR) AS ZC6_VALOR
					FROM 
						ZC6010 Z LEFT JOIN  ACY010 A ON Z.ZC6_CANAL = A.ACY_GRPVEN
					WHERE 	ZC6_FILIAL = '01' 
						AND ZC6_CANAL = '$canal'
						AND ZC6_INTCOD LIKE '%$codigo_monarch%'
						AND ZC6_FEMIS BETWEEN '$fec_ini' AND '$fec_fin'
						AND Z.D_E_L_E_T_ <> '*'
					GROUP BY 
						ZC6_OC,ZC6_CANAL,ACY_DESCRI,ZC6_LOCAL,ZC6_DLOCAL,ZC6_INTCOD,ZC6_INTDES,ZC6_PRCVEN
					ORDER BY ZC6_OC,ZC6_INTCOD";
					// echo $querysel;
	$rss = querys($querysel, $tipobd_totvsDev2, $conexion_totvsDev2);
	MwriteSql('sql/reporte.sql', $querysel);
	while($v = ver_result($rss, $tipobd_totvsDev2)){
		$mostrar[]=array(
			
			"CANAL"	    =>trim($v["ZC6_CANAL"]).' - '.trim($v["ACY_DESCRI"]),
			"OC"	    =>trim($v["ZC6_OC"]),
			"LOCAL"	    =>trim($v["ZC6_LOCAL"]).' - '.trim($v["ZC6_DLOCAL"]),
			"INTCOD"    =>trim($v["ZC6_INTCOD"]),
			"INTDES"	=>trim($v["ZC6_INTDES"]),
			"CANTIDAD"	=>trim($v["ZC6_CANT"]),
			"PRVEN"		=>trim($v["ZC6_PRCVEN"]),
			"VALOR"	    =>trim($v["ZC6_VALOR"]),
			
		);
	}

	echo json_encode($mostrar);
}


?>