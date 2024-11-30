<?php
error_reporting(E_ALL);
header("Cache-Control: no-cache, must-revalidate");
require_once "config.php";
require_once "conexion.php";
// require_once "./PHPMailer/PHPMailerAutoload.php";
$sid=session_id();
if (!ini_get('session.auto_start') and empty($sid)) {session_start();}


function ver_base_datos(){
	global $tipobd_portal,$conexion_portal;
	
	$querysel = "SELECT * FROM BD_MCH ORDER BY BD_ID_TIPO";
	$rss = querys($querysel, $tipobd_portal, $conexion_portal);
	while($v = ver_result($rss,$tipobd_portal)){
		$cargar[]=array(
					"BD_ID"  		=>trim($v["BD_ID"]),
					"NOMBRE"  		=>trim($v["BD_NOMBRE"]),
					"TIPO" 			=>$v["BD_ID_TIPO"],
					"HOST" 			=>$v["BD_HOST"],
					"USUARIO" 		=>$v["BD_USER"],
					"CLAVE" 		=>$v["BD_PASS"],
					"PUERTO" 		=>$v["BD_PORT"],
					"SID" 			=>$v["BD_SID_BD"],
					"ESTADO" 		=>$v["BD_ESTADO"],
					
			);
		
		
	}
	echo json_encode($cargar);
}
function editar_bd($id_bd){
		global $tipobd_portal,$conexion_portal;
	
	
	
	$querysel = "SELECT	BD_ID,BD_NOMBRE,	BD_ID_TIPO,	BD_HOST,	BD_USER,	BD_PASS,	BD_PORT,	BD_SID_BD,	BD_ESTADO 
					FROM
					BD_MCH
				WHERE BD_ID =  '$id_bd'";
	//echo $querysel;
	$rss = querys($querysel, $tipobd_portal, $conexion_portal);
	while($v = ver_result($rss,$tipobd_portal)){
		$editar[]=array(
			"BD_ID" 			=> trim($v["BD_ID"]),
			"NOMBRE" 		=> trim($v["BD_NOMBRE"]),
			"TIPO" 			=> trim($v["BD_ID_TIPO"]),
			"HOST" 			=> trim($v["BD_HOST"]),
			"USUARIO" 		=> trim($v["BD_USER"]),
			"CLAVE" 			=> trim($v["BD_PASS"]),		  
			"PUERTO"			=> trim($v["BD_PORT"]),			  
			"SID"	 			=> trim($v["BD_SID_BD"]), 
			"ESTADO"	 		=> trim($v["BD_ESTADO"])  
		);
	}
//	  echo "<pre>";
//    print_r($editar);
//    echo "</pre>";
	echo json_encode($editar);
}
function insertar_bd($post){
    global $tipobd_portal,$conexion_portal;

    //$bd_id      = $_POST["bd_id"];
	// echo "<pre>";
	//	print_r($post);
	// echo "</pre>";
	 
    $bd_id     = $post["bd_id"];
    $nombre     = $post["nombre"];
    $tipo       = $post["tipo"];
    $host       = $post["host"];
    $usuario    = $post["usuario"];
    $clave      = $post["clave"];
    $puerto     = $post["puerto"];
    $sid      	 = $post["sid"];
    $estado     = 1;
    $recno      = recno_tabla();

    $queryin = "INSERT INTO BD_MCH(BD_ID, BD_NOMBRE, BD_ID_TIPO, BD_HOST, BD_USER, BD_PASS, BD_PORT, BD_SID_BD, BD_ESTADO) 
						VALUES($recno, '$nombre', '$tipo', '$host', '$usuario', '$clave', '$puerto', '$sid', '$estado')";
    $rsi = querys($queryin, $tipobd_portal, $conexion_portal);

    echo "SERVIDOR INGRESADO !";

}
function recno_tabla(){
    global $tipobd_portal,$conexion_portal;

    $querysel = "SELECT MAX(BD_ID) as RECNO FROM BD_MCH";
    $rss = querys($querysel, $tipobd_portal, $conexion_portal);
    $fila = ver_result($rss, $tipobd_portal);
    if($fila["RECNO"]==null or $fila["RECNO"]==0){
        return 1;
    }else{
        return $fila["RECNO"]+1;
    }
}

function update_bd($post){
    global $tipobd_portal,$conexion_portal;

    $id         = $post["bd_id"];
    $nombre     = $post["nombre"];
    $tipo       = $post["tipo"];
    $host       = $post["host"];
    $usuario    = $post["usuario"];
    $clave      = $post["clave"];
    $puerto     = $post["puerto"];
    $sid      	 = $post["sid"];
    $estado     = $post["estado"];

    $queryup = "UPDATE BD_MCH 
						SET  BD_NOMBRE='$nombre', BD_ID_TIPO='$tipo', BD_HOST='$host', BD_USER='$usuario', BD_PASS='$clave', BD_PORT='$puerto', BD_SID_BD='$sid', BD_ESTADO='$estado'
						WHERE BD_ID = $id";
	// echo $queryup;
	$rsu = querys($queryup, $tipobd_portal, $conexion_portal);
	if($rsu){
		echo "SERVIDOR DE BASE DE DATOS $host ACTUALIZADO CON EXITO" ;
	}else{
		echo "ERROR: SERVIDOR DE BASE DATOS NO ACTUALIZADO" ;
	}
}
function existe_bd($id_bd){
	global $tipobd_portal,$conexion_portal;

	$queryexit = "SELECT	COUNT(*) AS FILAS FROM	BD_MCH WHERE BD_ID=$id_bd";
	echo $queryexit;
	$rse = querys($queryexit, $tipobd_portal, $conexion_portal);
	$fila = ver_result($rse, $tipobd_portal);
	if($fila["FILAS"]>0){
        return true;
    }else{
        return false;
    }
}
//getUsers();
/*MAIN*/

if(isset($_GET["ver"])){   

    ver_base_datos();
}
if(isset($_GET["cargar"])){
    $id_bd 		= $_GET["id_bd"];
    editar_bd($id_bd);
}
if(isset($_POST["insertar"])){

    if($_POST["bd_id"]==''){
        insertar_bd($_POST);
		  //echo "ACTUALIZADO";Q
    }else{
        update_bd($_POST);
		  //echo "INSERTADO";
    }
    
}
?>