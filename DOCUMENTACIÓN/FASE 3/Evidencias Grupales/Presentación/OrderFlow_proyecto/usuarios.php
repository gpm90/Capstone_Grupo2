<?php
error_reporting(E_ALL);

require_once "conexion.php";
require_once "config.php";



function renco_usuarios(){
  global $tipobd_portal,$conexion_portal;
    
    $querysel = "SELECT MAX(RECNO) + 1 AS RECNO FROM ".USUARIOS."";
    $rss = querys($querysel,$tipobd_portal,$conexion_portal);
    $v = ver_result($rss,$tipobd_portal);
    $recno = $v["RECNO"];
    
    return $recno;
    
}

function insertUser($post){
  global $tipobd_portal,$conexion_portal;
    
    $usuario = $post["txt_user_name"];
    $nombre = $post["txt_name"];
    $pass = $post["txt_pass"];
    $correo = $post["txt_correo"];
    $recno = renco_usuarios();
    
    $querysel = "INSERT INTO ".USUARIOS."
    (USUARIO, NOMBRE, PASS,D_E_L_E_T_, RECNO, CORREO)
    VALUES
    ('$usuario', '$nombre', '$pass', ' ',$recno,'$correo')";
		// echo $querysel;
    $rss = querys($querysel,$tipobd_portal,$conexion_portal);
    if(db_num_fil($tipobd_portal, $rss)<>false or db_num_fil($tipobd_portal,$rss)<>false){
        echo "USUARIO $usuario INGRESADO CORRECTAMENTE!", EOL ;
    }else{
        echo "ERROR: USUARIO $usuario NO INGRESADO!", EOL ;
    }
}
function getUsers(){
  global $tipobd_portal,$conexion_portal;
    
    $querysel = "SELECT USUARIO, NOMBRE
    FROM ".USUARIOS."
    WHERE D_E_L_E_T_<>'*'
	ORDER BY USUARIO";
    $rss = querys($querysel,$tipobd_portal,$conexion_portal);
    while($fila=ver_result($rss,$tipobd_portal)){
        $user[]=array(
            "USER"=>$fila["USUARIO"],
            "NOMBRE"=>$fila["NOMBRE"],
            //"NOMBRE"=>$fila["DESC_DEPARTAMENTO"]
        );
    }
    //$dpto["success"]=true;
    echo json_encode($user);
    
}
function verPermisos($user){
  global $tipobd_portal,$conexion_portal;
    
    $querysel = "SELECT COD_MENU, COD_ITEM, NIVEL, NOMBRE
    FROM ".MENU."
    WHERE D_E_L_E_T_<>'*'
    ORDER BY COD_MENU, RECNO";
    $rss = querys($querysel,$tipobd_portal,$conexion_portal);
    while($fila = ver_result($rss,$tipobd_portal)){
        $cod_item = $fila["COD_ITEM"];
        $querycount = "select count(*) as CANTFILA
        from ".PERMISOS."
        where USUARIO='$user'
        and COD_ITEM_MENU='$cod_item'";
        $rsc = querys($querycount,$tipobd_portal,$conexion_portal);
        $filac = ver_result($rsc, $tipobd_portal);
        if($filac["CANTFILA"]==0){
            $checked = false; 
        }else{
            $checked = true;
        }
        
        $permisos[]=[
            "CODMENU"=>$fila["COD_MENU"],
            "CODITEM"=>$cod_item,
            "NIVEL"=>$fila["NIVEL"],
            "NOMBRE"=>$fila["NOMBRE"],
            "CHECKED"=>$checked,
                     ];
    }
    echo json_encode($permisos);
    
}
function gestionaPermisos($formArray){
  global $tipobd_portal,$conexion_portal;
    //echo "<pre> inicio print";
    //print_r($formArray);
    //echo "<pre/> fin print";
    //echo "<pre> inicio vardum";
    //var_dump($formArray);
    //echo "<pre/>";
    $usuario = $formArray["user"];
    foreach($formArray as $clave => $valor){
        if($clave<>"user" and $clave<>"insertPermiso"){
            if($valor == 'true' and !existePermiso($usuario,$clave)){
                insertarPermiso($usuario, $clave);
            }
            if($valor == 'false' and existePermiso($usuario,$clave)){
                borrarPermiso($usuario, $clave);
            }
        }
    }
}
function existePermiso($usuario,$itemMenu){
  global $tipobd_portal,$conexion_portal;
    $select = "SELECT COUNT(*) AS NUMFILAS FROM ".PERMISOS."
    WHERE USUARIO='$usuario' AND COD_ITEM_MENU='$itemMenu'";
    // echo $select.'<br>';
    $rss = querys($select,$tipobd_portal,$conexion_portal);
    $fila = ver_result($rss, $tipobd_portal);
    if($fila['NUMFILAS']==1){
        return true;
    }else{
        return false;
    }
}
function renco_permisos(){
  global $tipobd_portal,$conexion_portal;
    
    $querysel = "SELECT MAX(RECNO) AS RECNO FROM ".PERMISOS."";
    $rss = querys($querysel,$tipobd_portal,$conexion_portal);
    $v = ver_result($rss, $tipobd_portal);
    $recno = $v["RECNO"];
    
    return $recno;
    
}
function insertarPermiso($user,$itemMenu){
  global $tipobd_portal,$conexion_portal;
    
    $recno = renco_permisos();
    $itemMenu = trim($itemMenu);
    $insert = "INSERT INTO ".PERMISOS."
    (USUARIO, COD_ITEM_MENU, RECNO)
    VALUES
    ('$user', '$itemMenu',$recno)";
		//echo $insert.'<br>';
    $result = querys($insert,$tipobd_portal,$conexion_portal);
		//echo mysqli_num_rows($result) . " rows deleted.<br />\n";
    if(!empty($result) and oci_num_rows($result)>0){
        echo "PERMISO $itemMenu ASIGNADO A $user CON ÉXITO !!!";
    }
}

function borrarPermiso($user,$itemMenu){
  global $tipobd_portal,$conexion_portal;
    
    $delete = "DELETE FROM ".PERMISOS."
    WHERE USUARIO='$user'
    AND COD_ITEM_MENU='$itemMenu'";
    //echo $delete.'<br>';
    $result = querys($delete,$tipobd_portal,$conexion_portal);
   
    if(oci_num_rows($result)<>0 or oci_num_rows($result)<>false){
        echo "PERMISO $itemMenu BORRADO A $user CON ÉXITO !!!", EOL;
    }
}
function deleteUsuario($codigoUsuario){
  global $tipobd_portal,$conexion_portal;
    
    $queryup = "UPDATE ".USUARIOS."
    SET D_E_L_E_T_='*'
    WHERE USUARIO='$codigoUsuario'";
    $rsu = querys($queryup, $tipobd_portal,$conexion_portal);

    $delete = "DELETE FROM ".PERMISOS."
    WHERE USUARIO='$codigoUsuario'";
    //echo $delete.'<br>';
    $result = querys($delete,$tipobd_portal,$conexion_portal);
    if(db_num_fil($tipobd_portal, $rsu)<>null or db_num_fil($tipobd_portal, $rsu)<>0){
        echo "USUARIO BORRADO CON EXITO!";
    }else{
        echo "ERROR: USUARIO NO BORRADO!";
    }
}
function getUsuarioEdit($usuario){
  global $tipobd_portal,$conexion_portal;
    
    $querysel = "SELECT USUARIO, NOMBRE,PASS
    FROM ".USUARIOS."
    WHERE USUARIO='$usuario'
    AND D_E_L_E_T_<>'*'";
    $rss = querys($querysel,$tipobd_portal,$conexion_portal);
    while($fila=ver_result($rss, $tipobd_portal)){
        $user[]=array(
            "USER"=>$fila["USUARIO"],
            "NOMBRE"=>$fila["NOMBRE"],
            "PASS"=>$fila["PASS"]
        );
    }
    //$dpto["success"]=true;
    echo json_encode($user);    
}
function existeUsuario($usuario){
  global $tipobd_portal,$conexion_portal;
    global $usuario;
    
    $querysel = "SELECT COUNT(*) AS FILAS
    FROM ".USUARIOS."
    WHERE USUARIO='$usuario' 
    AND D_E_L_E_T_<>'*'";
    $rss=querys($querysel,$tipobd_portal,$conexion_portal);
    $fila = ver_result($rss, $tipobd_portal);
    if($fila["FILAS"]> 0){
        return true;
    }else{
        return false;
    }
}
function actualizarUsuario($post){
  global $tipobd_portal,$conexion_portal;
    
    $txt_usuario     = $post["txt_user_name"];
    $nombre          = $post["txt_name"];
    $pass            = $post["txt_pass"];
    $correo          = $post["txt_correo"];
    
    $queryup = "UPDATE ".USUARIOS."
    SET  NOMBRE='$nombre',PASS='$pass', CORREO = '$correo'
    WHERE USUARIO='$txt_usuario'
    AND D_E_L_E_T_<>'*'";
    $rsu = querys($queryup,$tipobd_portal,$conexion_portal);
    if(db_num_fil($tipobd_portal,$rsu)<>null or db_num_fil($tipobd_portal,$rsu)<>0){
        echo "USUARIO $txt_usuario ACTUALIZADO CON EXITO!";
    }else{
        echo "ERROR: USUARIO $txt_usuario NO ACTUALIZADO!";
    }
    
}
//getUsers();
/*MAIN*/
if(isset($_POST["insertUser"])){
    $usuario = $_POST["txt_user_name"];
     if(existeUsuario($usuario)){
        actualizarUsuario($_POST);
    }else{
        insertUser($_POST);
    }       
}
if(isset($_GET["getUsers"])){
    getUsers();    
}
if(isset($_GET["verPermisos"])){
    verPermisos($_GET["user"]);    
}
if(isset($_POST["insertPermiso"])){
    gestionaPermisos($_POST);    
}
if(isset($_GET["deleteUsuario"])){
    deleteUsuario($_GET["codUsuario"]);
}
if(isset($_GET["editUsuario"])){
    getUsuarioEdit($_GET["codUsuario"]);
}
?>