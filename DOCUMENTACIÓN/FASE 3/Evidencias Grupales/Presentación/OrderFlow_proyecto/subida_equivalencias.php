<?php

error_reporting();
require_once "config.php";
require_once "conexion.php";
require_once "generar_insert.php";
require 'PHPExcel-1.8/Classes/PHPExcel.php';
$sid=session_id();
if (!ini_get('session.auto_start') and empty($sid)) {session_start();}


function digito_verificador($codigo_barra){
	
	$posiciones_pares = substr($codigo_barra, 0, 1) + substr($codigo_barra, 2, 1) + substr($codigo_barra, 4, 1) + substr($codigo_barra, 6, 1) + substr($codigo_barra,8,1)+substr($codigo_barra,10,1);
	$posiciones_inpares = substr($codigo_barra, 1, 1) + substr($codigo_barra, 3, 1) + substr($codigo_barra, 5, 1) + substr($codigo_barra, 7, 1) + substr($codigo_barra,9,1)+substr($codigo_barra,11,1);
	
	$imparesx3 = $posiciones_inpares * 3;
	$suma = $imparesx3 + $posiciones_pares;
	
	$round_decena = ceil($suma / 10) * 10;
	
	$dig_verif = $round_decena - $suma;
	
	return $codigo_barra.$dig_verif;
	
}


function subirArchivo(){
	global $tipobd_ptl,$conexion_ptl;
	global $jerarquia;
	$dir_subida = './archivos_subidos/equivalencias/';
	$fichero_subido = $dir_subida.basename($_FILES['file_cventas']['name']);
	$nombre = $_FILES['file_cventas']['tmp_name'];	
	//$jerarquia = $_POST["jerarquia"];
	
	if (move_uploaded_file($_FILES['file_cventas']['tmp_name'], $fichero_subido)) {
		
		$error = $_FILES['file_cventas']['error'];		 
		$type  = $_FILES['file_cventas']['type'];

		if($error == 1){
			echo "TAMAÑO ARCHIVO EXCEDE MAXIMO PERMITIDO";
		}else{
			// echo "El fichero es valido y subido con Exito !\n<br>";
			//echo "Tipo Archivo : ".$type."<br>";
		}	

	}

}
function ver_datos($canal, $articulo){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$querysel = "SELECT ZEQ_CANAL,ZEQ_DCANAL, ZEQ_COD,ZEQ_BARCOD,ZEQ_CLICOD, ZEQ_CLIBAR, ZEQ_PRCCLI, R_E_C_N_O_  
					FROM ".TBL_ZEQ."   
					WHERE ZEQ_CANAL='$canal' AND ZEQ_COD like '%$articulo%'
					AND D_E_L_E_T_<>'*'
					ORDER BY ZEQ_CLICOD";
					
	//echo $querysel;
	$rss = querys($querysel,$tipobd_totvsDev2, $conexion_totvsDev2);
	while($v = ver_result($rss, $tipobd_totvsDev2)){
		$datos[]=array(
					"ZEQ_CANAL" 		=> trim($v["ZEQ_CANAL"]).' - '. trim($v["ZEQ_DCANAL"]),
					"ZEQ_DCANAL" 	=> trim($v["ZEQ_DCANAL"]),
					"ZEQ_COD"   		=> trim($v["ZEQ_COD"]), 
					"ZEQ_BARCOD" 	=> $v["ZEQ_BARCOD"],
					"ZEQ_CLICOD"   	=> trim($v["ZEQ_CLICOD"]), 
					"ZEQ_CLIBAR"   	=> trim($v["ZEQ_CLIBAR"]),
					"ZEQ_PRCCLI"   	=> trim($v["ZEQ_PRCCLI"]),
					"RECNO"   		=> trim($v["R_E_C_N_O_"])
				);
		}

	echo json_encode($datos);
	
	cierra_conexion($tipobd_totvsDev2, $conexion_totvsDev2);
}

function recno_tabla(){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$select = "SELECT nvl(MAX(R_E_C_N_O_),0)+1 AS R_E_C_N_O_ FROM ".TBL_ZEQ." ";
	$rs = querys($select,$tipobd_totvsDev2, $conexion_totvsDev2);
	$fila = ver_result($rs, $tipobd_totvsDev2);
	$recno = $fila['R_E_C_N_O_'];

	return $recno;

	cierra_conexion($tipobd_totvsDev2, $conexion_totvsDev2);
}





function articulo($codigo_articulo){
	global $tipobd_totvs,$conexion_totvs;
	global $validez_art;
	global $b1_cod, $b1_codbar, $b1_desc, $b1_um, $b1_locpad, $b1_segum, $b1_conv, $b1_grupo, $b1_cc, $b1_itemcc, $b1_clvl, $b1_conta,$b1_factor;
	
	$count = "select count(*) as numfilas from SB1010 where  b1_cod='$codigo_articulo'  and d_e_l_e_t_<>'*'";
	
	//echo $count;
	$rsc = querys($count,$tipobd_totvs,$conexion_totvs);
	$filac = ver_result($rsc, $tipobd_totvs);
	if($filac['NUMFILAS'] == 1){
		
		$query = "select trim(b1_cod) as b1_cod, b1_codbar, b1_desc, b1_um, b1_locpad, b1_segum, nvl(b1_conv,1) as conv, b1_grupo, b1_cc, b1_itemcc, b1_clvl, b1_conta,B1_FACTOR 
		from SB1010 where b1_cod='$codigo_articulo'  and d_e_l_e_t_<>'*'";
		
	  //  echo $query;	
		$rs = querys($query,$tipobd_totvs,$conexion_totvs);
		$fila= ver_result($rs, $tipobd_totvs);
		$b1_cod		= $fila['B1_COD'];
		$b1_codbar		= $fila['B1_CODBAR'];
		$b1_desc 	= $fila['B1_DESC'];
		$b1_um		= $fila['B1_UM'];
		$b1_locpad	= $fila['B1_LOCPAD'];
		$b1_segum	= $fila['B1_SEGUM'];
		$b1_conv	= $fila['CONV'];
		$b1_grupo	= $fila['B1_GRUPO'];
		$b1_cc		= $fila['B1_CC'];
		$b1_itemcc	= $fila['B1_ITEMCC'];
		$b1_clvl	= $fila['B1_CLVL'];
		$b1_conta	= $fila['B1_CONTA'];
		$b1_factor	= $fila['B1_FACTOR'];
		
	
	}elseif($filac['NUMFILAS'] == 2){
		
		$validez_art = "DA"; //artículo duplicado en tabla producto
		
		$b1_cod		= "DA";
		$b1_desc 	= "DA";
		$b1_um		= "DA";
		$b1_locpad	= "DA";
		$b1_segum	= "DA";
		$b1_conv	= "DA";
		$b1_grupo	= "DA";
		$b1_cc		= "DA";
		$b1_itemcc	= "DA";
		$b1_clvl	= "DA";
		$b1_conta	= 0;
	}else{
		$validez_art = "NA"; //articulo no existe en tabla producto
		
		$b1_cod		= "NA";
		$b1_desc 	= "NA";
		$b1_um		= "NA";
		$b1_locpad	= "NA";
		$b1_segum	= "NA";
		$b1_conv	= "NA";
		$b1_grupo	= "NA";
		$b1_cc		= "NA";
		$b1_itemcc	= "NA";
		$b1_clvl	= "NA";
		$b1_conta	= 0;
		
		$da1_prcven	= 0;
	}
}
function existe_equivalencia($canal, $sku_cliente){
	 global $tipobd_totvsDev2, $conexion_totvsDev2;
	 
	 $queryexist = "SELECT count(*) as FILAS
					FROM ".TBL_ZEQ."   
					WHERE ZEQ_CANAL='$canal' AND ZEQ_CLICOD='$sku_cliente'";
	// echo "QUERY  : ".$queryexist."<br>";
	$rse = querys($queryexist, $tipobd_totvsDev2, $conexion_totvsDev2);
	$v = ver_result($rse, $tipobd_totvsDev2);
	$filas = $v["FILAS"];
	if($filas>0){
	    return true;
	}else{
	    return false;
	}
	
}
function valida_sb1_manual($cod_monarch){
	global $tipobd_totvs,$conexion_totvs;
	
	$querysel = "SELECT  NVL(MAX(B1_COD),0) AS  B1_COD FROM SB1010 WHERE B1_COD='$cod_monarch' AND D_E_L_E_T_<>'*'";
	// echo $querysel."<br>";
	$rss = querys($querysel, $tipobd_totvs, $conexion_totvs);
	while($v = ver_result($rss, $tipobd_totvs)){
		$cod 		= trim($v['B1_COD']);
		
		$querysel_1 = "SELECT count(*) AS FILAS FROM sb1010 WHERE B1_COD='$cod' AND D_E_L_E_T_<>'*'";
		//echo $querysel_1."<br>";
		$rss_1 = querys($querysel_1, $tipobd_totvs, $conexion_totvs);
		$v1 = ver_result($rss_1, $tipobd_totvs);
		$filas = $v1["FILAS"];
		if($filas == 0){
			//echo '<script language="javascript">alert("ERROR : CODIGO '.$cod.' NO EXISTEN EN BODEGA DE DESTINO");</script>';
			echo "ERROR-01 : CODIGO <strong>$cod_monarch</strong> NO EXISTE EN MAESTRO DE ARTICULOS";
			die();
		}
		
	}
	//return $filas;
}
function insertar_manual($post){
    global $tipobd_totvsDev2, $conexion_totvsDev2;

    //$bd_id      = $_POST["bd_id"];
	// echo "<pre>";
	//	print_r($post);
	// echo "</pre>";
	 
    $canal     		= $post["canal_1"];
    $cod_monarch    = $post["cod_mch_1"];
    $clicod       	= $post["sku_cliente_1"];
    $clibar       	= $post["barra_cliente_1"];
    $cli_des    	= $post["descr_cliente_1"];
    $precio_venta  	= $post["precio_venta_1"];
    $recno      	= recno_tabla();

	$precio_lista = lista_precios($canal, $cod_monarch);	
	$usuario = $_SESSION["user"];
	//VALIDA SI EL CODIGO EXISTE EN MAESTRO SB1
	valida_sb1_manual($cod_monarch);
		//die();
		
		articulo($cod_monarch); //consulta datos propios del artículo o producto como, codigo monarch, descripción, bodega, factor de convesión
		global $b1_cod, $b1_codbar, $b1_desc, $b1_um, $b1_locpad, $b1_segum, $b1_conv, $b1_grupo, $b1_cc, $b1_itemcc, $b1_clvl, $b1_conta, $b1_factor;
		$hoy = date('Ymd');
	switch($canal){
			case '4001'://fallabella
				$dcanal = 'FALABELLA';
				$rut = '77261280K';
				break;
			case '4002'://paris
				$dcanal = 'PARIS';
				$rut = '81201000K';
				break;
			case '4003'://ripley
				$dcanal = 'RIPLEY';
				$rut = '833827006';
				break;
			case '4109'://tricot
				$dcanal = 'TRICOT';
				$rut = '840000001';
				break;
			case '4101'://lapolar
				$dcanal = 'LA POLAR';
				$rut = '96874030K';
				break;
			case '4109'://hites
				$dcanal = 'HITES';
				$rut = '816756006';
				break;
			case '4302'://hites
				$dcanal = 'LIDER';
				$rut = '76042014K';
							break;
			case '4301'://hites
				$dcanal = 'JUMBO';
				$rut = '81201000K';
				break;
			case '4304'://hites
				$dcanal = 'TOTTUS';
				$rut = '786272106';
				break;
	}
	
			
	$tabla=TBL_ZEQ;
    $mfield=genera_estructura_desarrollo($tabla);	
	$mfield['ZEQ_FILIAL']['value']='01';
	$mfield['ZEQ_CLIENT']['value']=$rut;
	$mfield['ZEQ_CANAL']['value']=$canal;
	$mfield['ZEQ_DCANAL']['value']=$dcanal;
	$mfield['ZEQ_COD']['value']=trim($cod_monarch);
	$mfield['ZEQ_BARCOD']['value']=TRIM($b1_codbar);
	$mfield['ZEQ_DESC']['value']=$b1_desc;
	$mfield['ZEQ_CLICOD']['value']=$clicod;
	$mfield['ZEQ_CLIBAR']['value']=$clibar;
	$mfield['ZEQ_CLIDES']['value']=$cli_des;
	$mfield['ZEQ_FACTOR']['value']=$b1_factor;
	$mfield['ZEQ_LOCPAD']['value']=$b1_locpad;
	$mfield['ZEQ_UM']['value']=$b1_um;
	$mfield['ZEQ_SEGUM']['value']=$b1_segum;
	$mfield['ZEQ_PRLIST']['value']=$precio_lista;
	$mfield['ZEQ_FEMIS']['value']=$hoy;
	$mfield['R_E_C_N_O_']['value']=$recno;
	$mfield['ZEQ_PRCCLI']['value']=$precio_venta;
	$mfield['ZEQ_DTMOD']['value']=$hoy;
	$mfield['ZEQ_OKWMS']['value']='N';
	$mfield['D_E_L_E_T_']['value']=' ';
	
	$sql=genera_insert($tabla,$mfield);
	$rsi = querys($sql,$tipobd_totvsDev2, $conexion_totvsDev2);
	
	//echo $sql;
	
	if($rsi){
		echo "ARTICULO $cod_monarch CON EQUIVALENCIA $clicod  INGRESADO CON EXITO" ;
	}else{
		echo "ERROR: ARTICULO NO INGRESADO" ;
	}

}
function update_manual($post){
    global $tipobd_totvsDev2, $conexion_totvsDev2;

	$canal     		= $post["canal_1"];
    $cod_monarch    = $post["cod_mch_1"];
    $clicod       	= $post["sku_cliente_1"];
    $clibar       	= $post["barra_cliente_1"];
    $desc	    	= $post["descr_cliente_1"];
    $precio_venta  	= $post["precio_venta_1"];
    $recno      	= $post["recno"];

	$precio_lista = lista_precios($canal, $cod_monarch);	
	$usuario = $_SESSION["user"];
	$hoy = date('Ymd');	
	switch($canal){
			case '4001'://fallabella
				$dcanal = 'FALABELLA';
				$rut = '77261280K';
				break;
			case '4002'://paris
				$dcanal = 'PARIS';
				$rut = '81201000K';
				break;
			case '4003'://ripley
				$dcanal = 'RIPLEY';
				$rut = '833827006';
				break;
			case '4109'://tricot
				$dcanal = 'TRICOT';
				$rut = '840000001';
				break;
			case '4101'://lapolar
				$dcanal = 'LA POLAR';
				$rut = '96874030K';
				break;
			case '4109'://hites
				$dcanal = 'HITES';
				$rut = '816756006';
				break;
			case '4304'://hites
					$dcanal = 'TOTTUS';
					$rut = '786272106';
					break;
	}
	valida_sb1_manual($cod_monarch);	
		articulo($cod_monarch); //consulta datos propios del artículo o producto como, codigo monarch, descripción, bodega, factor de convesión
		global $b1_cod, $b1_codbar, $b1_desc, $b1_um, $b1_locpad, $b1_segum, $b1_conv, $b1_grupo, $b1_cc, $b1_itemcc, $b1_clvl, $b1_conta, $b1_factor;
		
		

    $queryup = "UPDATE ".TBL_ZEQ." 
					SET ZEQ_CLIENT='$rut',
					ZEQ_CANAL='$canal',
					ZEQ_DCANAL='$dcanal',
					ZEQ_COD='$cod_monarch',
					ZEQ_BARCOD=trim('$b1_codbar'),
					ZEQ_DESC='$b1_desc',
					ZEQ_CLICOD='$clicod',
					ZEQ_CLIBAR='$clibar',
					ZEQ_CLIDES='$desc',
					ZEQ_FACTOR=$b1_factor,
					ZEQ_LOCPAD='$b1_locpad',
					ZEQ_UM='$b1_um',
					ZEQ_SEGUM='$b1_segum',
					ZEQ_PRLIST=$precio_lista,
					ZEQ_PRCCLI=$precio_venta,
					ZEQ_DTMOD='$hoy',
					D_E_L_E_T_=' '
					WHERE R_E_C_N_O_=$recno";
	 //echo $queryup;
	$rsu = querys($queryup, $tipobd_totvsDev2, $conexion_totvsDev2);
	if($rsu){
		echo "ARTICULO $cod_monarch CON EQUIVALENCIA $clicod  ACTUALIZADO CON EXITO" ;
	}else{
		echo "ERROR: ARTICULO NO ACTUALIZADO" ;
	}
}
function valida_formato($archivo){
	// Especifica las columnas esperadas
	$expectedColumns = [
		'DCANAL',	'CODIGO MONARCH',	'COD TIENDA (SKU)',	'COD BARRA TIENDA(13)',	'DESCRIPCION TIENDA',	'PRECIO VENTA'

	];
	
	// Ruta del archivo Excel
	$filePath = './archivos_subidos/equivalencias/' . $archivo;
	
	// Cargar el archivo Excel
	$objPHPExcel = PHPExcel_IOFactory::load($filePath);
	$sheet = $objPHPExcel->getActiveSheet();
	
	// Obtener las columnas de la primera fila
	$headerRow = [];
	$highestColumn = $sheet->getHighestColumn();
	$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
	
	for ($col = 0; $col < $highestColumnIndex; $col++) {
		$headerRow[] = strtoupper(trim($sheet->getCellByColumnAndRow($col, 1)->getValue()));
	}
	
	// Convertir las columnas esperadas a mayúsculas para asegurar una comparación correcta
	$expectedColumns = array_map('strtoupper', $expectedColumns);
	
	// Verificar si faltan columnas o si hay columnas adicionales
	$missingColumns = array_diff($expectedColumns, $headerRow);
	$extraColumns = array_diff($headerRow, $expectedColumns);
	
	if (!empty($missingColumns) || !empty($extraColumns)) {
		$errorMessage = "ERROR-02: ";
		if (!empty($missingColumns)) {
			$errorMessage .= "Faltan las siguientes columnas: " . implode(', ', $missingColumns) . ". ";
		}
		if (!empty($extraColumns)) {
			$errorMessage .= "Hay columnas adicionales no esperadas: " . implode(', ', $extraColumns) . "<br>";
		}
		echo $errorMessage;
		die();
	} else {
		echo "El archivo tiene el formato correcto.";
		// die();
	}
	
}
function leer_archivo($archivo){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	valida_formato($archivo);
	$path='./archivos_subidos/equivalencias/';
    $nombreArchivo = $path.$archivo;
	$objPHPExcel = PHPExcel_IOFactory::load($nombreArchivo);
	
	// Asigno la hoja de calculo activa
	$objPHPExcel->setActiveSheetIndex(0);
	// Obtengo el numero de filas del archivo
	$numRows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
	
	$hoy = date('Ymd');	

	for ($i = 2; $i <= $numRows; $i++) {
		
		$dcanal 	 	= trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue());
		$cod_monarch	= trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue());
		$clicod 	 	= trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue());
		$upc			= trim($objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue());
		$cli_des 		= trim($objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue());
		$precio_venta	= trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue());
		$recno = recno_tabla();

		$hoy = date('Ymd');
			switch($dcanal){
			case 'FALABELLA'://fallabella
				$canal = '4001';
				$rut = '77261280K';
				break;
			case 'PARIS'://paris
				$canal = '4002';
				$rut = '81201000K';
				break;
			case 'RIPLEY'://ripley
				$canal = '4003';
				$rut = '833827006';
				break;
			case 'TRICOT'://tricot
				$canal = '4109';
				$rut = '840000001';
				break;
			case 'LA POLAR'://lapolar
				$canal = '4101';
				$rut = '96874030K';
				break;
			case 'HITES'://hites
				$canal = '4109';
				$rut = '816756006';
				break;
			case 'WALMART'://hites
				$canal = '4302';
				$rut = '76042014K';
				break;
			case 'JUMBO'://hites
				$canal = '4301';
				$rut = '81201000K';
				break;
			case 'TOTTUS'://hites
				$canal = '4304';
				$rut = '786272106';
				break;
	}
		
		
		$precio_lista = lista_precios($canal, $cod_monarch);	
		$usuario = $_SESSION["user"];
		
		//die();
		
		articulo($cod_monarch); //consulta datos propios del artículo o producto como, codigo monarch, descripción, bodega, factor de convesión
		global $b1_cod, $b1_codbar, $b1_desc, $b1_um, $b1_locpad, $b1_segum, $b1_conv, $b1_grupo, $b1_cc, $b1_itemcc, $b1_clvl, $b1_conta, $b1_factor;
		valida_sb1_manual($cod_monarch);	
				
		if(existe_equivalencia($canal, $clicod)){
			
			$queryup = "UPDATE ".TBL_ZEQ."  
					SET ZEQ_CLIENT='$rut',
					ZEQ_CANAL='$canal',
					ZEQ_DCANAL='$dcanal',
					ZEQ_COD='$cod_monarch',
					ZEQ_BARCOD=trim('$b1_codbar'),
					ZEQ_DESC='$b1_desc',
					ZEQ_CLICOD=trim('$clicod'),
					ZEQ_CLIBAR=trim('$upc'),
					ZEQ_CLIDES='$cli_des',
					ZEQ_FACTOR=$b1_factor,
					ZEQ_LOCPAD='$b1_locpad',
					ZEQ_UM='$b1_um',
					ZEQ_SEGUM='$b1_segum',
					ZEQ_PRLIST=$precio_lista,
					ZEQ_PRCCLI=$precio_venta,
					D_E_L_E_T_=' '
					WHERE ZEQ_CLICOD='$clicod'";
			//echo $queryup;
			echo "ARTICULO $cod_monarch CON EQUIVALENCIA $clicod ACTUALIZADO<br>";
			$rsu = querys($queryup, $tipobd_totvsDev2, $conexion_totvsDev2);
		}else{
			
			$tabla=TBL_ZEQ;
			$mfield=genera_estructura_desarrollo($tabla);	
			$mfield['ZEQ_FILIAL']['value']='01';
			$mfield['ZEQ_CLIENT']['value']=$rut;
			$mfield['ZEQ_CANAL']['value']=$canal;
			$mfield['ZEQ_DCANAL']['value']=$dcanal;
			$mfield['ZEQ_COD']['value']=trim($cod_monarch);
			$mfield['ZEQ_BARCOD']['value']=trim($b1_codbar);
			$mfield['ZEQ_DESC']['value']=$b1_desc;
			$mfield['ZEQ_CLICOD']['value']=trim($clicod);
			$mfield['ZEQ_CLIBAR']['value']=trim($upc);
			$mfield['ZEQ_CLIDES']['value']=$cli_des;
			$mfield['ZEQ_FACTOR']['value']=$b1_factor;
			$mfield['ZEQ_LOCPAD']['value']=$b1_locpad;
			$mfield['ZEQ_UM']['value']=$b1_um;
			$mfield['ZEQ_SEGUM']['value']=$b1_segum;
			$mfield['ZEQ_PRLIST']['value']=$precio_lista;
			$mfield['ZEQ_FEMIS']['value']=$hoy;
			$mfield['ZEQ_PRCCLI']['value']=$precio_venta;
			$mfield['ZEQ_DTMOD']['value']=$hoy;
			$mfield['ZEQ_OKWMS']['value']='N';
			$mfield['R_E_C_N_O_']['value']=$recno;
			
			$sql=genera_insert($tabla,$mfield);
			$result = querys($sql,$tipobd_totvsDev2, $conexion_totvsDev2);
			
			//echo "SQL : ".$sql."<br>";
			echo "ARTICULO $cod_monarch CON EQUIVALENCIA $clicod INGRESADO <br>";
			
			//$queryin_wms = "INSERT INTO dbo.ItemCustomer( CustomerCode, OwnCode, ItemCode, ItemCodeCustomer, LongItemName,
			//			Capacity, Price, Status, SpecialField1, SpecialField2, SpecialField3, SpecialField4,
			//			StateInterface, DateCreatedERP, DateReadWMS, DepartmentItem, DepartmentDescription, BarCode) 
			//				VALUES( '77261280K-4001', '90991000-5', '001010-A-ANT', '16446355', 'PANTY PUNTA INVISIBLE 20D 1010. A-ANTILOPE', 1, 2800, 1, '', '', '', '', 'C', '', '', '', '', '7805813001038')";
			//
		}			
			
	}
	cierra_conexion($tipobd_totvsDev2, $conexion_totvsDev2);
}

function borrar_equivalencia($sku_cliente){
	global $tipobd_totvsDev2, $conexion_totvsDev2;

	$query = "UPDATE ".TBL_ZEQ."  SET D_E_L_E_T_='*' WHERE ZEQ_CLICOD = '$sku_cliente'";
	$rss = querys($query,$tipobd_totvsDev2, $conexion_totvsDev2);
	// echo $query;

	echo "Equivalencia de articulo <strong>$sku_cliente</strong> borrado con exito!!";

}


//============================================================================================
//============================================================================================
//============================================================================================

function lista_precios($canal, $cod_monarch){
	global $tipobd_totvs,$conexion_totvs;
	
	switch($canal){
			case '4001'://fallabella
				$cod_tab = '009';
				break;
			case '4002'://paris
				$cod_tab = '011';
				break;
			case '4003'://ripley
				$cod_tab = '010';
				break;
			case '4109'://tricot
				$cod_tab = 'E23';
				break;
			case '4101'://lapolar
				$cod_tab = '002';
				break;
			case '4109'://hites
				$cod_tab = '013';
				break;
			case '4301'://JUMBO
				$cod_tab = '005';
				break;
			case '4302'://WAL
				$cod_tab = '005';
				break;
			case '4304'://TOTTUS
				$cod_tab = '005';
				break;
				
	}
	
	$querysel = "SELECT NVL(MAX(DA1_PRCVEN),0) AS DA1_PRCVEN FROM DA1010 WHERE DA1_CODPRO='$cod_monarch' AND DA1_CODTAB='$cod_tab'";
	$rss = querys($querysel, $tipobd_totvs, $conexion_totvs);
	$fila = ver_result($rss, $tipobd_totvs);
	$pr_venta = $fila['DA1_PRCVEN'];
	return $pr_venta;
}


function cliente($glncliente){
	global $tipobd_totvs,$conexion_totvs;
	global $a1_cod, $a1_nreduz, $a1_cond, $a1_naturez, $a1_tabela, $a1_grpven, $a1_loja, $a1_vend, $dconpag;
	global $valida_cli;
	
	$count = "SELECT COUNT(*) AS NUMFILAS FROM SA1010, SE4010
	WHERE A1_COND=E4_CODIGO AND A1_COD='$glncliente' AND A1_LOJA = '01' AND  SA1010.D_E_L_E_T_ <> '*' AND SE4010.D_E_L_E_T_ <> '*'";//
	$rs = querys($count,$tipobd_totvs,$conexion_totvs);
	$filac = ver_result($rs, $tipobd_totvs);
	if($filac['NUMFILAS'] == 1){
		$query = "SELECT A1_COD, A1_NREDUZ, A1_COND, A1_NATUREZ, A1_TABELA, A1_GRPVEN, A1_LOJA, A1_VEND, E4_DESCRI
		FROM  SA1010, SE4010
		WHERE A1_COND=E4_CODIGO AND A1_COD='$glncliente' AND A1_LOJA = '01' AND  SA1010.D_E_L_E_T_ <> '*' AND SE4010.D_E_L_E_T_ <> '*'";//
	
		//echo $query.'<br>';
		$rs = querys($query,$tipobd_totvs,$conexion_totvs);
		//OBTENER RESULTADO
		$fila=ver_result($rs, $tipobd_totvs);
		$a1_cod 	= $fila['A1_COD'];
		$a1_nreduz 	= $fila['A1_NREDUZ'];
		$a1_cond 	= $fila['A1_COND'];
		$a1_naturez 	= $fila['A1_NATUREZ'];
		$a1_tabela 	= $fila['A1_TABELA'];
		$a1_grpven 	= $fila['A1_GRPVEN'];
		$a1_loja	= $fila['A1_LOJA'];
		$a1_vend	= $fila['A1_VEND'];
		$dconpag	= $fila['E4_DESCRI'];
		$valida_cli = "*";
	}else{
		$valida_cli = "N";
	}
}
function editar_codigos($articulo,$canal){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$articulo 	= utf8_decode($articulo);
	$canal 		= utf8_decode(substr($canal,0,4));
	
	$querysel = "SELECT ZEQ_CANAL, ZEQ_COD, ZEQ_CLICOD, ZEQ_CLIBAR, ZEQ_CLIDES, ZEQ_PRCCLI, R_E_C_N_O_
				FROM ".TBL_ZEQ."   
				WHERE ZEQ_CANAL='$canal' AND ZEQ_CLICOD='$articulo'";
	//echo $querysel;
	$rss = querys($querysel, $tipobd_totvsDev2, $conexion_totvsDev2);
	while($v = ver_result($rss, $tipobd_totvsDev2)){
		$editar[]=array(
			"ZEQ_CANAL" 		=> trim($v["ZEQ_CANAL"]),			  
			"ZEQ_COD" 		=> trim($v["ZEQ_COD"]),
			"ZEQ_CLICOD"		=> trim(utf8_encode($v["ZEQ_CLICOD"])),
			"ZEQ_CLIBAR" 	=> trim($v["ZEQ_CLIBAR"]),
			"ZEQ_CLIDES" 	=> trim($v["ZEQ_CLIDES"]),
			"ZEQ_PRCCLI" 	=> $v["ZEQ_PRCCLI"],
			"RECNO" 	=> trim($v["R_E_C_N_O_"]),
			
		);
	}
//	  echo "<pre>";
//    print_r($editar);
//    echo "</pre>";
	echo json_encode($editar);
}





//============================================================================================
//============================================================================================
//============================================================================================

if(isset($_FILES['file_cventas']['name'])){
	
	$nombre_archivo = $_FILES['file_cventas']['name'];
    subirArchivo();
	//$nombre_archivo = 'corona.csv';
	leer_archivo($nombre_archivo);
}
if(isset($_POST["insertar"])){
	
	$canal = $_POST["canal_1"];
	$sku_cliente = $_POST["sku_cliente_1"];
    if(existe_equivalencia($canal, $sku_cliente)){
        update_manual($_POST);
		  //echo "ACTUALIZADO";
    }else{
        insertar_manual($_POST);
		  //echo "INSERTADO";
    }
}
  
if(isset($_GET["cargar"])){
    $articulo 	= $_GET["articulo"];
    $canal 		= $_GET["canal"];
    editar_codigos($articulo,$canal);
}
if(isset($_GET["borrar_articulo"])){
    $cliente_codigo 	= $_GET["cliente_codigo"];
    borrar_equivalencia($cliente_codigo);
}



if(isset($_GET["ver"])){
	$canal = $_GET["canal"];
	$cod_monarch = $_GET["cod_monarch"];
    ver_datos($canal, $cod_monarch);
}





?>