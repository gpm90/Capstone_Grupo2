<?php
session_start();
error_reporting(E_ALL);
$sid=session_id();
if (!ini_get('session.auto_start') and empty($sid)) {session_start();}

require_once "config.php";
require_once "conexion.php";



$dominio = "http://".$_SERVER['SERVER_NAME'].substr($_SERVER['SCRIPT_NAME'],0,strripos($_SERVER['SCRIPT_NAME'],'/'));
//echo $dominio;
function cargaMenu(){
    global $tipobd_portal,$conexion_portal;
    global $dominio;
    
    if(!isset($_SESSION["user"])){
        $json=[
            "NOTLOGIN"=>"NOTLOGIN",
            "DOMINIO"=>$dominio,
                 ];
        echo json_encode($json);
    }else{
        $user = $_SESSION["user"];
        $querysel = "SELECT P.USUARIO,ME.NOMBRE, URL, NIVEL, ME.COD_MENU, ME.COD_ITEM
        FROM ".PERMISOS." P, ".MENU." ME, ".USUARIOS." U
        WHERE P.USUARIO='$user'
        AND P.USUARIO=U.USUARIO
        AND P.COD_ITEM_MENU=ME.COD_ITEM
        
        AND U.D_E_L_E_T_<>'*'
        AND ME.D_E_L_E_T_<>'*'
        ORDER BY ME.COD_MENU, ME.ORDEN";
        //echo $querysel;
        $rss = querys($querysel, $tipobd_portal,$conexion_portal);
        
        while($fila=ver_result($rss,$tipobd_portal)){
            //$menu[$fila["NOMBRE"]]='';
            $codMenu = $fila["COD_MENU"];
            
            $menu[]=[
                "USUARIO"   =>$fila["USUARIO"],
                "NOMBRE"    =>$fila["NOMBRE"],
                "URL"       =>$fila["URL"],
                "NIVEL"     =>$fila["NIVEL"],
                     ];
            
        }
        //echo "<pre>";
        //print_r($menu);
        //echo "<pre/>";
        $menu["LOGIN"]=true;
    
        echo json_encode($menu);
        
    }
    
    
}
function logOut(){
    global $conexion_mysql;
    global $dominio;
    
    session_destroy();
    $json = [
             "DOMINIO"=>$dominio,
             "LOGOUT"=>"LOGOUT",
             ];
    echo json_encode($json);
    
}
//cargaMenu();
/*MAIN*/

if(isset($_GET["cargaMenu"])){
    cargaMenu();
}
if(isset($_GET["logout"])){
    logOut();
}

?>