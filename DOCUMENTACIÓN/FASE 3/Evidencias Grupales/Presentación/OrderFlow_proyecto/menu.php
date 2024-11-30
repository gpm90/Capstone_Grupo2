<?php
error_reporting(E_ALL);
        

require_once "config.php";
require_once "conexion.php";

function renco_menu(){
    global $tipobd_portal,$conexion_portal;

    $querysel = "SELECT MAX(RECNO)+1 AS RECNO FROM ".MENU."";
    $rss = querys($querysel,$tipobd_portal,$conexion_portal);
    $v = ver_result($rss,$tipobd_portal);
    $recno = $v["RECNO"];
    
    return $recno;
    
}
function ingresarMenu($form){
global $tipobd_portal,$conexion_portal;
    //global $conexion;
    //global $nivel, $nombre, $url;
    
    for($i=1; $i<=$form["max"];$i++){

        $codigo     = $form["codigo".$i];
        $nivel      = $form["nivel".$i];
        $orden      = $form["orden".$i];
        $codItem    = $form["cod_item".$i];
        $nombre     = $form["nombre".$i];
        $url        = $form["url".$i];
        $recno = renco_menu();
        
       
        $queryin1 = "INSERT INTO MENU VALUES('$codigo', '$codItem', $nivel, '$nombre', '$url', $orden, $recno, ' ')";
        $rss = querys($queryin1,$tipobd_portal,$conexion_portal);
        echo $queryin1."<br>";          
       
            echo "MENÚ $nombre INGRESADO CORRECTAMENTE! <br>";
    }

}


function verMenu(){
  global $tipobd_portal,$conexion_portal;
    
    $querysel = "SELECT COD_MENU, COD_ITEM, NIVEL, NOMBRE, URL, ORDEN
    FROM ".MENU."
    WHERE D_E_L_E_T_ <> '*'
    ORDER BY COD_MENU ,RECNO ";
    //echo $querysel;
    $rss = querys($querysel,$tipobd_portal,$conexion_portal);
    while($fila=ver_result($rss,$tipobd_portal)){
        $menu[]=array(
            "CODMENU"   =>$fila["COD_MENU"],
            "CODITEM"   =>$fila["COD_ITEM"],
            "NIVEL"     =>$fila["NIVEL"],
            "NOMBRE"    =>$fila["NOMBRE"],
            "URL"       =>$fila["URL"],
            "ORDEN"     =>$fila["ORDEN"],
            //"ESTADO"=>$fila["D_E_L_E_T_"],
        );
    }
    //$dpto["success"]=true;
    echo json_encode($menu);
}
function verNivelPadre($nivelPadre){
   global $tipobd_portal,$conexion_portal;
    
    $querysel = "SELECT COD_MENU, NOMBRE
    FROM ".MENU."
    WHERE NIVEL=$nivelPadre
    ORDER BY ORDEN ASC";
    $rss = querys($querysel,$tipobd_portal,$conexion_portal);
    while($fila=ver_result($rss,$tipobd_portal)){
        $npadre[]=array(
            "CODIGO"=>$fila["COD_MENU"],
            "NOMBRE"=>$fila["NOMBRE"],
            //"NOMBRE"=>$fila["DESC_DEPARTAMENTO"]
        );
    }
    //$dpto["success"]=true;
    echo json_encode($npadre);
}
function getOrdenItem($codMenu, $nivel){
   global $tipobd_portal,$conexion_portal;
    
    $querysel = "SELECT NVL(MAX(ORDEN),1) AS NEXTORDEN
    FROM ".MENU."
    WHERE COD_MENU='$codMenu'
    AND NIVEL=$nivel";
    //echo $querysel.'<br>';
    $rss = querys($querysel,$tipobd_portal,$conexion_portal);
    $fila = ver_result($rss,$tipobd_portal);
    if($fila["NEXTORDEN"]==null or $fila["NEXTORDEN"]==''){
       return 1;
    }else{
        echo $fila["NEXTORDEN"]+1;
    }
    
}
function deleteMenu($cod_menu){
    global $tipobd_portal,$conexion_portal;
    
    $queryup = "UPDATE ".MENU."
    SET D_E_L_E_T_='*'
    WHERE COD_ITEM='$cod_menu'";
    $rsu = querys($queryup,$tipobd_portal,$conexion_portal);
    if(db_num_fil($tipobd_portal,$rsu)<>null or db_num_fil($tipobd_portal,$rsu)<>0){
        echo "MENU BORRADO CON EXITO!";
    }else{
        echo "ERROR: MENU NO BORRADO!";
    }
}
function getMenuEdit($cod_item){
   global $tipobd_portal,$conexion_portal;
    
    $querysel = "SELECT DISTINCT COD_MENU,COD_ITEM,NIVEL, NOMBRE,URL,ORDEN,RECNO 
    FROM ".MENU."
    WHERE COD_ITEM='$cod_item'
    AND D_E_L_E_T_<>'*'";
    $rss = querys($querysel,$tipobd_portal,$conexion_portal);
    while($fila=ver_result($rss,$tipobd_portal)){
        $menu[]=array(
            "CODIGO"        =>$fila["COD_MENU"],
            "CODIGO_ITEM"   =>$fila["COD_ITEM"],
            "NIVEL"         =>$fila["NIVEL"],
            "ORDEN"         =>$fila["ORDEN"],
            "NOMBRE"        =>$fila["NOMBRE"],
            "URL"           =>$fila["URL"],
            "RECNO"         =>$fila["RECNO"],
        );
    }
    //$dpto["success"]=true;
    echo json_encode($menu);    
}
function actualizarMenu($post){

  global $tipobd_portal,$conexion_portal;
    
    $cod_recno   = $post["txt_codigo_menu"];
    $nivel       = $post["cmb_nivel"];
    $nivel_padre = $post["cmb_nivel_padre"];
    $nombre      = $post["txt_nombre"];
    $url         = $post["txt_url"];
    
    $queryup = "UPDATE ".MENU."
    SET  NIVEL='$nivel', COD_MENU='$nivel_padre',NOMBRE = '$nombre', URL = '$url'
    WHERE RECNO='$cod_recno'
    AND D_E_L_E_T_<>'*'";
    $rsu = querys($queryup,$tipobd_portal,$conexion_portal);
    if(db_num_fil($tipobd_portal,$rsu)<>null or db_num_fil($tipobd_portal,$rsu)<>0){
        echo "MENU $nombre ACTUALIZADO CON EXITO!";
    }else{
        echo "ERROR: MENU $nombre NO ACTUALIZADO!";
    }    
    
}

/*MAIN*/
if(isset($_POST["ingresoMenu"])){
    if(isset($_POST["RECNO"])){
        actualizarMenu($_POST);
    }else{
        ingresarMenu($_POST);
    }
    
}
if(isset($_GET["verMenu"])){
    verMenu();
}
if(isset($_GET["editMenu"])){
    getMenuEdit($_GET["codMenu"]);
}    
if(isset($_GET["nivelPadre"])){
    $nivelPadre = $_GET["nivelHijo"]-1;
    verNivelPadre($nivelPadre);
}
if(isset($_GET["ordenItemMenu"])){
    $codMenu = $_GET["cod"];
    $nivel = $_GET["nivel"];
    getOrdenItem($codMenu, $nivel);
}
if(isset($_GET["deleteMenu"])){
    deleteMenu($_GET["codMenu"]);
}
  

?>