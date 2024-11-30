<?php
error_reporting(E_ALL);
require_once "lib/gestordb2.php";


	//CONEXION A TOTVS MONARCH 100.232 //TOTVS_MCHV12
	$resultado_totvs 	= selec_server('TOTVS_MCHV12');
	$tipobd_totvs		= $resultado_totvs[0];
	$conexion_totvs 	= $resultado_totvs[1];
	
	
	//CONEXION A ORACLE 100.71
	$resultado 			= selec_server('ORACLE_10072');
	$tipobd_portal 		= $resultado[0];
	$conexion_portal 	= $resultado[1];

	// // CONEXION A TOTVS MONARCH V12 100.159
	$resultado_totvsDev2 	= selec_server('TOTVS_MCHV12_DEV2');
	$tipobd_totvsDev2		= $resultado_totvsDev2[0];
	$conexion_totvsDev2 	= $resultado_totvsDev2[1];


?>
