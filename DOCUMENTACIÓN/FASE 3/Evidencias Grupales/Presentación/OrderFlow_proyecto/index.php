<?php
session_start();
error_reporting(E_ALL);

 require_once "conexion.php";
require_once "config.php";


function validaUser($user, $pass){
	global $tipobd_portal,$conexion_portal;

	// echo "<pre>";
	// print_r($resultado);
	// echo "</pre>";

    
    $count = "SELECT COUNT(*) AS NUMFILAS
    from USUARIOS
    where USUARIO='$user' and d_e_l_e_t_<>'*'";
    $rsc = querys($count,$tipobd_portal,$conexion_portal);
    $fila = ver_result($rsc,$tipobd_portal);
    //echo "FILAS : ".$fila['NUMFILAS'];
    
    if($fila['NUMFILAS']==1){
        $select = "SELECT USUARIO, PASS
        FROM  USUARIOS
        WHERE USUARIO='$user'
        AND D_E_L_E_T_<>'*'";
        $rss = querys($select,$tipobd_portal,$conexion_portal);
        
        $fila = ver_result($rss,$tipobd_portal);
        $usuario = trim($fila['USUARIO']);
        $clave = trim($fila['PASS']);
        
        if($usuario==$user and $clave==$pass){
            
            $_SESSION['user'] = $user;
            //session_write_close();
            echo "USER_VALIDO";
            //echo $user;
            
        }
        if($clave<>$pass){
            echo "Clave incorrecta!";
        }
    }else{
            echo "Usuario Incorrecto!";
    }
    
}


/*MAIN*/

if(isset($_GET["login"])){
    sleep(1);
    validaUser($_GET["txt-user"],$_GET["txt-pwd"]);
}

?>