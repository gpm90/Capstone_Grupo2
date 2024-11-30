<?php

error_reporting();

require_once "../../config.php";
require_once "../../conexion.php";
require_once "../../generar_insert.php";
require '../../PHPExcel-1.8/Classes/PHPExcel.php';
require_once "outbound_dev.php";
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
	
	$dir_subida = '../../archivos_subidos/archivos_distribucion/ripley_distribucion/';
	$fichero_subido = $dir_subida.basename($_FILES['file_cventas']['name']);
	$nombre = $_FILES['file_cventas']['tmp_name'];	
	

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

function correlativo($var, $largo){
	//echo $var.'__'.$largo.'<br>';
        $limite[0] = '1'.rellena('',$largo,'0','D');
        $numero[0] =  $var - $limite[0];
        $divide[0] = 10;
        $can_let = 0;

        for ($i = 1; $i <= $largo; $i++) {
            $limite[$i] = substr($limite[0],0,($i*-1)) * pow(26,$i);
            $numero[$i] = $numero[$i-1] - $limite[$i];
            $divide[$i] = $divide[$i-1] * 10;
            if ($numero[$i-1] >= 0){$can_let = $i;}
        }

        if ($numero[$largo] >= 0){$can_let = ($largo+1);}
        switch (true) {
		case $can_let == ($largo+1):
    //                  echo "el numero sobre pasa el limite <br>";
			$retorna = '';
			break;
		
		case $can_let == 0:
    //              	echo "el numero no necesita letras <br>";
			$retorna = $var;
			break;
		
		default:
			$hasta = $can_let - 1;
			$h = $hasta;
			$d = 0;
			$retorna='';
			for ($x = 0; $x <= $hasta; $x++) {
				$val1 = ($numero[$hasta] / ($limite[$h]/$divide[$d]) +1);
				$mod1 = $numero[$hasta] % ($limite[$h]/$divide[$d]);

				if ($x == 0){
					$resta = 0;
					$htres = "0";
				}else{
					$divi = $limite[$h] / $divide[$d];
					$resta = (int) (($numero[$hasta] / $divi)/26);
					$resta = ($resta * 26);
				}

				$decimal = $mod1;
				$post_letra[$x] = $val1-$resta;
				$h--;
				$d++;
				$decim = rellena($decimal,($largo-$can_let),'0','D');
				$retorna = $retorna.chr(64+((int) ($post_letra[$x])));

			}

			$retorna = substr($retorna.$decim,0,$largo);

        }

        return($retorna);
		
}

///////////////////////////////////////////////////ABAJO//////////////////////////////////////////////////////////
///////////////////////////////////////////////CODIGO NUEVO//////////////////////////////////////////////////////////

function datos_subidos(){
	//global $tipobd_ptl,$conexion_ptl;
	//global $tipobd_totvs, $conexion_totvs;
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$querysel = "SELECT 
					ZC6_ARCHIV, ZC6_OC, ZC6_FECOC, ZC6_OKDIGI, ZC6_NUM,
					SUM(ZC6_CANT) AS UNIDADES, COUNT(DISTINCT ZC6_LOCAL) AS LOCALES,
					COUNT(DISTINCT ZC6_CLICOD) AS CANT_TOTAL, NVL((SELECT MAX(D2_DTDIGIT) FROM SD2010 WHERE D2_FILIAL='01' and D2_PEDIDO=ZC6_NUM AND D_E_L_E_T_<>'*'),' ') AS FEC_FACT,
					CASE WHEN COUNT( DISTINCT ZC6_TICKET) >1 THEN 'S' ELSE 'N' END AS EN_WMS
				FROM ZC6010 Z
				WHERE ZC6_FILIAL='01'
				AND ZC6_CANAL = '4003'
				AND D_E_L_E_T_<>'*'
				GROUP BY ZC6_ARCHIV, ZC6_OC, ZC6_FECOC, ZC6_OKDIGI, ZC6_NUM
				order BY ZC6_FECOC DESC";

	$rss = querys($querysel,$tipobd_totvsDev2, $conexion_totvsDev2);
	while($v = ver_result($rss, $tipobd_totvsDev2)){
		$nombre_archivo = $v["ZC6_ARCHIV"];
		$datos[]=array(
					"NOM_ARCHIVO" 	 	=> $v["ZC6_ARCHIV"],
					"ARCHIVO_DESCARGA" 	 	=> "<a href='archivos_subidos/archivos_distribucion/ripley_distribucion/$nombre_archivo'>$nombre_archivo</a>",
					"NRO_ORDEN"   		 	=> $v["ZC6_OC"], 
					"FECEMISION" 	=> formatDate($v["ZC6_FECOC"]), 
					"LOCALES" 	 	=> $v["LOCALES"],
					"UNIDADES" 	 	=> $v["UNIDADES"],
					"CANT_TOTAL" 	=> $v["CANT_TOTAL"],
					"OK_DIGITACION" => $v["ZC6_OKDIGI"],
					"FEC_FACT"   	=> trim(formatDate($v["FEC_FACT"])),
					"NUM_TOTVS"   	=> $v["ZC6_NUM"],
					"EN_WMS"   	=> $v["EN_WMS"]
				);
	}
	echo json_encode($datos);
	cierra_conexion($tipobd_totvsDev2, $conexion_totvsDev2);
}

function borrarPlanilla($archivo){
	global $tipobd_totvsDev2, $conexion_totvsDev2;

	$queryDel = "DELETE FROM ".TBL_ZC6010." WHERE ZC6_ARCHIV = '$archivo'";
	$rss = querys($queryDel,$tipobd_totvsDev2, $conexion_totvsDev2);
	echo $queryDel;


}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////ABAJO PARA PROCESAR A TOTVS////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function getZitem($orcom){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$select = "SELECT ZC6_ZITEM FROM ".TBL_ZC6010." WHERE ZC6_OC = '$orcom'";
	// echo "CONTAR UNI ".$select.'<br>';
	$rs = querys($select,$tipobd_totvsDev2, $conexion_totvsDev2);

	while($fila=ver_result($rs,$tipobd_totvsDev2)){
		$arr[]=array(
			"ZC6_ZITEM"  =>$fila["ZC6_ZITEM"]
		);
	}
	return $arr;
}

function contar_uni($oc){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$select = "SELECT SUM(ZC6_CANT) AS TOTAL FROM ".TBL_ZC6010." WHERE ZC6_OC='$oc' AND ZC6_CANAL='4003'";
	// echo "CONTAR UNI ".$select.'<br>';
	$rs = querys($select,$tipobd_totvsDev2, $conexion_totvsDev2);
	$fila = ver_result($rs, $tipobd_totvsDev2);
	$num = $fila['TOTAL'];
	return $num;
}

function getNum($orcom){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$select = "SELECT TRIM(C5_NUM) AS C_NUM FROM SC5010 WHERE C5_ORCOM = '$orcom'";

	$rs = querys($select, $tipobd_totvsDev2, $conexion_totvsDev2);
	$fila = ver_result($rs, $tipobd_totvsDev2);
	$num = $fila['C_NUM'];
	return $num;

}

function c5_num(){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	//global $conexion3;
	
	//$select = "select max(to_number(c5_num))+1 as num from SC5010 where c5_num between '100000' and '199999' and d_e_l_e_t_ <> '*'";
	$select = "SELECT NVL(MAX(TO_NUMBER(C5_NUM))+1,0) AS NUM FROM SC5010 WHERE C5_NUM BETWEEN '100000' AND '499999'";
	$rs = querys($select,$tipobd_totvsDev2, $conexion_totvsDev2);
	$fila = ver_result($rs, $tipobd_totvsDev2);
	$num = $fila['NUM'];
	return $num;
}

function recno(){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$select = "SELECT NVL(MAX(R_E_C_N_O_),0)+1 AS R_E_C_N_O_ FROM ".TBL_ZC6010."";
	$rs = querys($select,$tipobd_totvsDev2, $conexion_totvsDev2);
	$fila = ver_result($rs, $tipobd_totvsDev2);
	$recno = $fila['R_E_C_N_O_'];

	return $recno;
	cierra_conexion($tipobd_totvsDev2, $conexion_totvsDev2);

}

function recno_head(){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$select = "SELECT NVL(MAX(R_E_C_N_O_),0)+1 AS R_E_C_N_O_ FROM SC5010";
	$rs = querys($select,$tipobd_totvsDev2, $conexion_totvsDev2);
	$fila = ver_result($rs, $tipobd_totvsDev2);
	$recno = $fila['R_E_C_N_O_'];

	return $recno;
	cierra_conexion($tipobd_totvsDev2, $conexion_totvsDev2);

}

function recno_detail(){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	//global $conexion3;
	
	$select = "SELECT NVL(MAX(R_E_C_N_O_),0)+1 AS CORRELATIVO FROM SC6010";
	$rs = querys($select, $tipobd_totvsDev2, $conexion_totvsDev2);
	$fila = ver_result($rs, $tipobd_totvsDev2);
	$recno = $fila['CORRELATIVO'];
	return $recno;
	
}

function orcom($nompla){ ////MODIFICAR PARA APUNTAR A LA ZC6010;
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	$select = "SELECT
		ZC6_OC, ZC6_FEMIS, ZC6_LOCAL, ZC6_DLOCAL
	FROM ".TBL_ZC6010."
	WHERE ZC6_ARCHIV = '$nompla'";

	$rss = querys($select,$tipobd_totvsDev2, $conexion_totvsDev2);
	while($fila=ver_result($rss,$tipobd_totvsDev2)){
		$arr[]=array(
			"ORCOM"        =>$fila["ZC6_OC"],
			"FECPRO"       =>$fila["ZC6_FEMIS"],
			"MLOCAL"       =>$fila["ZC6_LOCAL"],
			"MDLOCAL"      =>$fila["ZC6_DLOCAL"],
		);
	}
	return $arr;

}

function articulo($codartMCH){
	global $tipobd_totvs,$conexion_totvs;
	global $validez_art;
	global $b1_cod, $b1_desc, $b1_um, $b1_locpad, $b1_segum, $b1_conv, $b1_grupo, $b1_cc, $b1_itemcc, $b1_clvl, $b1_conta, $b1_factor, $b1_codbar;
	
	$count = "select count(*) as numfilas from SB1010 where  B1_COD='$codartMCH'  and d_e_l_e_t_<>'*'";
	
	//echo $count;
	$rsc = querys($count,$tipobd_totvs,$conexion_totvs);
	$filac = ver_result($rsc, $tipobd_totvs);
	if($filac['NUMFILAS'] == 1){
		
		$query = "select trim(b1_cod) as b1_cod, b1_desc, b1_um, b1_locpad, b1_segum, nvl(b1_conv,1) as conv, b1_grupo, b1_cc, b1_itemcc, b1_clvl, b1_conta, B1_FACTOR, B1_CODBAR 
		from SB1010 where B1_COD='$codartMCH'  and d_e_l_e_t_<>'*'";
		
	  //  echo $query;	
		$rs = querys($query,$tipobd_totvs,$conexion_totvs);
		$fila= ver_result($rs, $tipobd_totvs);
		$b1_cod		= $fila['B1_COD'];
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
		$b1_codbar  = $fila['B1_CODBAR'];
		
	
	}elseif($filac['NUMFILAS'] == 2){
		
		$validez_art = "DA"; //artículo duplicado en tabla producto
		
		$b1_cod		= "DA";
		$b1_codbar	= "DA";
		$b1_desc 	= "DA";
		$b1_um		= "DA";
		$b1_locpad	= "DA";
		$b1_segum	= "DA";
		$b1_conv	= 0;
		$b1_grupo	= "DA";
		$b1_cc		= "DA";
		$b1_itemcc	= "DA";
		$b1_clvl	= "DA";
		$b1_conta	= 0;
		$b1_factor	= 0;
	}else{
		$validez_art = "NA"; //articulo no existe en tabla producto
		
		$b1_cod		= "NA";
		$b1_codbar	= "NA";
		$b1_desc 	= "NA";
		$b1_um		= "NA";
		$b1_locpad	= "NA";
		$b1_segum	= "NA";
		$b1_conv	= 0;
		$b1_grupo	= "NA";
		$b1_cc		= "NA";
		$b1_itemcc	= "NA";
		$b1_clvl	= "NA";
		$b1_conta	= 0;
		$b1_factor	= 0;
		
		$da1_prcven	= 0;
	}
}


function cliente($glncliente){
	global $tipobd_totvs, $conexion_totvs;

	global $a1_cod, $a1_nreduz, $a1_cond, $a1_naturez, $a1_tabela, $a1_grpven, $a1_loja, $a1_vend, $a1_mdescu1, $dconpag;
	global $valida_cli;
	
	$count = "SELECT COUNT(*) AS NUMFILAS FROM SA1010, SE4010
	WHERE A1_COND=E4_CODIGO AND A1_COD='$glncliente' AND A1_LOJA = '00' AND  SA1010.D_E_L_E_T_ <> '*' AND SE4010.D_E_L_E_T_ <> '*'";//
	$rs = querys($count, $tipobd_totvs, $conexion_totvs);
	$filac = ver_result($rs, $tipobd_totvs);
	if($filac['NUMFILAS'] == 1){
		$query = "SELECT A1_COD, A1_NREDUZ, A1_COND, A1_NATUREZ, A1_TABELA, A1_GRPVEN, A1_LOJA, A1_VEND, A1_MDESCU1, E4_DESCRI
		FROM  SA1010, SE4010
		WHERE A1_COND=E4_CODIGO AND A1_COD='$glncliente' AND A1_LOJA = '00' AND  SA1010.D_E_L_E_T_ <> '*' AND SE4010.D_E_L_E_T_ <> '*'";//
	
		//echo $query.'<br>';
		$rs = querys($query, $tipobd_totvs, $conexion_totvs);
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
		$a1_mdescu1 = $fila['A1_MDESCU1'];
		$dconpag	= $fila['E4_DESCRI'];
		$valida_cli = "*";
	}else{
		$valida_cli = "N";
	}
}

/////////////////////////////////////////////ARRIBA PARA PROCESAR A TOTVS///////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function lista_precios($cod_monarch){
	global $tipobd_totvs,$conexion_totvs;
	
	$querysel = "SELECT NVL(MAX(DA1_PRCVEN),0) AS DA1_PRCVEN FROM DA1010 WHERE DA1_CODPRO='$cod_monarch' AND DA1_CODTAB='010'";
	$rss = querys($querysel, $tipobd_totvs, $conexion_totvs);
	$fila = ver_result($rss, $tipobd_totvs);
	$pr_venta = $fila['DA1_PRCVEN'];
	return $pr_venta;
}

function ver_errores($archivo){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$querysel = "SELECT ZC6_CLICOD, ZC6_CLIDES FROM ".TBL_ZC6010."  
					WHERE  ZC6_CANAL='4003' 
					and D_E_L_E_T_<>'*'
					and ZC6_INTCOD = '999999999999999'
					AND ZC6_ARCHIV = '$archivo'
					GROUP BY ZC6_CLICOD, ZC6_CLIDES";
	$rss = querys($querysel, $tipobd_totvsDev2, $conexion_totvsDev2);
	while($v = ver_result($rss, $tipobd_totvsDev2)){
				$datos[]=array(
					
					"SKU"   		=> $v["ZC6_CLICOD"],
					"CLI_DES"   	=> $v["ZC6_CLIDES"]
				);
		}
	echo json_encode($datos);	
}

if(isset($_GET["ver_errores"])){
	$archivo = $_GET["archivo"];
    ver_errores($archivo);
}



function insert_data($filial, $zitem, $oc, $cliente, $canal,
					$local, $dlocal, $item, $intcod, $codbar,
					$prcven, $cantidad, $valor, $locpad, $um,
					$segum, $intdes, $factor, $clicod, $clibarcod,
					$clides, $fecoc, $fecemision, $fecentrega, 
					$archivo,  $adicional2,  $vAdicional3,
					$recno,  $okdigit, $conta, $itemcta, $clvl,
					$grupo, $cc, $usuario, $vDcto1, $vDcto2, $prcoc, $neto,$precio_cliente,
					$cod_dpto, $nombre_depto
					){
	
	
		
	global $tipobd_totvsDev2, $conexion_totvsDev2;

	

	//echo $sql."<br>";
}
function existe_oc_distribucion($oc){	
    global $tipobd_totvsDev2,$conexion_totvsDev2;
	
	
		$querysel_1 = "SELECT COUNT(ZC6_OC) AS FILAS FROM ".TBL_ZC6010."
						WHERE  ZC6_OC='$oc' and ZC6_CANAL='4003' and D_E_L_E_T_<>'*'";
			 //echo $querysel_1."<br>";
			$rss_1 = querys($querysel_1, $tipobd_totvsDev2, $conexion_totvsDev2);
			$v1 = ver_result($rss_1, $tipobd_totvsDev2);
			$filas = $v1["FILAS"];
			if($filas > 0){
				//echo '<script language="javascript">alert("ERROR : CODIGO '.$cod.' NO EXISTEN EN BODEGA DE DESTINO");</script>';
				echo "ERROR-01 : ORDEN DE COMPRA $oc YA EXISTE";
				die();
			}		
	
	
}
function valida_formato($archivo){
	// Especifica las columnas esperadas
	$expectedColumns = [
		'RUT',	'Razon Social',	'Numero OC',	'Cod Tipo OC',	'Tipo OC',	'Cod Estado OC',	
		'Estado OC',	'Cod Depto',	'Departamento',	'Cod Linea',	'Linea',	'Cod Suc Entrega',	
		'Suc Entrega',	'Cod Suc Destino',	'Suc Destino',	'Fecha Generacion',	'Fecha Vencimiento',	
		'Fecha Entrega',	'Tipo Negociacion',	'Moneda',	'Forma Pago',	'Cod Art Proveedor (Case Pack)',	
		'Cod Art Ripley',	'Cod Art Venta',	'Dimensión 1',	'Dimensión 2',	'Dimensión 3',	
		'Desc Art Proveedor (Case Pack)',	'Desc Art Ripley',	'Cant Solicitada',	
		'Costo Base Unitario',	'Costo Neto Unitario',	'Precio Unitario',	'Cant CasePack','Unidades por CasePack',
		'UPC',	'Tipo Manejo',	'Descuento 1',	'Descuento 2',	'Precio Venta',

	];
	
	// Ruta del archivo Excel
	$filePath = '../../archivos_subidos/archivos_distribucion/ripley_distribucion/' . $archivo;
	
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
			$errorMessage .= "<strong>Faltan las siguientes columnas: </strong>" . implode(', ', $missingColumns) . ".<br> ";
		}
		if (!empty($extraColumns)) {
			$errorMessage .= "<strong>Hay columnas adicionales no esperadas: </strong>" . implode(', ', $extraColumns) . "<br>";
		}
		echo $errorMessage;
		die();
	} 
	
}
function leer_archivo($archivo){
	global $tipobd_totvsDev2, $conexion_totvsDev2;

	valida_formato($archivo);
	$path="../../archivos_subidos/archivos_distribucion/ripley_distribucion/";
    $nombreArchivo = $path.$archivo;
	$objPHPExcel = PHPExcel_IOFactory::load($nombreArchivo);
	
	// Asigno la hoja de calculo activa
	$objPHPExcel->setActiveSheetIndex(0);
	// Obtengo el numero de filas del archivo
	$numRows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
	$cellOC	= $objPHPExcel->getActiveSheet()->getCell('C2')->getCalculatedValue();
	existe_oc_distribucion($cellOC);
	
	$hoy = date('Ymd');
	
	$bod_item = 0;

	for ($i = 2; $i <= $numRows; $i++) {
		
		$cellOC 		 	= $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue(); // OK
		$cod_dpto 		 	= $objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue(); // OK
		$nombre_depto	 	= $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue(); // OK
		$cellFecemision	 	= $objPHPExcel->getActiveSheet()->getCell('P'.$i);
		$cellFecemision	 	= date('Ymd', PHPExcel_Shared_Date::ExcelToPHP($cellFecemision->getValue())); //OK
		$cellCodprov		= $objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue(); //OK
		$cellSKUcli			= $objPHPExcel->getActiveSheet()->getCell('W'.$i)->getCalculatedValue(); //OK
		$upc				= $objPHPExcel->getActiveSheet()->getCell('X'.$i)->getCalculatedValue(); //OK
		$cellDescli			= $objPHPExcel->getActiveSheet()->getCell('AC'.$i)->getCalculatedValue(); //OK
		$cellLocal			= $objPHPExcel->getActiveSheet()->getCell('N'.$i)->getCalculatedValue(); //OK
		$cellDlocal			= $objPHPExcel->getActiveSheet()->getCell('O'.$i)->getCalculatedValue(); //OK
		$cellUnidades		= $objPHPExcel->getActiveSheet()->getCell('AD'.$i)->getCalculatedValue(); //OK
		$cellPRCVEN			= $objPHPExcel->getActiveSheet()->getCell('AE'.$i)->getCalculatedValue(); //OK		
		$neto				= $objPHPExcel->getActiveSheet()->getCell('AF'.$i)->getCalculatedValue(); //OK		
		$cellDcto1			= $objPHPExcel->getActiveSheet()->getCell('AL'.$i)->getValue(); //OK 0.2		
		$cellDcto2			= $objPHPExcel->getActiveSheet()->getCell('AM'.$i)->getValue(); //OK 0.035		
		$precio_cliente		= $objPHPExcel->getActiveSheet()->getCell('AN'.$i)->getValue(); //OK 0.035		
		$cellSKUmch 	= convierte_codigomch(trim($cellSKUcli));


		
		articulo(trim($cellSKUmch));
		global $b1_cod, $b1_desc, $b1_um, $b1_locpad, $b1_segum, $b1_conv, $b1_grupo, $b1_cc, $b1_itemcc, $b1_clvl, $b1_conta, $b1_factor, $b1_codbar;

		$precios 		= trim($cellPRCVEN);
		$bod_item 		= $bod_item+1;
		$zitem 			= correlativo($bod_item,4);
		$zitem 			= str_pad($zitem,4,'0', STR_PAD_LEFT);

		$usuario = $_SESSION["user"];

		$vFilial = '01'; 
		$oc = trim($cellOC); 
		$cliente = '833827006'; 
		$canal = '4003';
		$local = trim($cellLocal);
		$dlocal = trim($cellDlocal);
		$item = '01'; 
		$intcod = trim($cellSKUmch);
		$vCodbar = trim($b1_codbar);

		//PRECIOS Y DESCUENTOS
		$vDcto1 = trim($cellDcto1);
		$vDcto2 = trim($cellDcto2);

		$vPrcini = $precios - ($precios * $vDcto1);
		$vPrcven = $vPrcini - ($vPrcini * $vDcto2);
		$vPrcven = round($vPrcven, 2, PHP_ROUND_HALF_UP);		

		$cantidad = trim($cellUnidades);
		$valor = $neto * $cantidad;
		//$vValor = round( $vValor, 2, PHP_ROUND_HALF_UP);


		$vLocpad = trim($b1_locpad);
		$vUM = trim($b1_um);
		$vSegum = trim($b1_segum); 
		$vIntdes = trim($b1_desc);
		$vFactor = trim($b1_factor); 
		$clicod = trim($cellSKUcli);
		$clibarcod = trim($upc);
		$clides = trim($cellDescli);
		
		$fecoc = date('Ymd', strtotime($cellFecemision. ' + 1 days'));
		$fecemision = $hoy; 
		$fecentrega = date('Ymd', strtotime($fecoc. ' + 7 days'));
		$vArchivo = $archivo; 
		$vAdicional2 = $vDcto2; 
		$vAdicional3 = trim($cellCodprov); 
		$prcLista = lista_precios($b1_cod);
		$recno = recno();
		$vOKdigit = 'N'; 
		$vConta = trim($b1_conta);
		$vItemcta = trim($b1_itemcc);  
		$vClvl = trim($b1_clvl);
		$vGrupo = trim($b1_grupo); 
		$vCC = trim($b1_cc); 
		$vUsuario = $usuario;
		$cod_dpto  = trim($cod_dpto);
		$nombre_depto = trim($nombre_depto);
	

		$prcoc = $precios;

				
		$hoy = date('Ymd');
		$fecha_subida = $hoy;

		

		$tabla=TBL_ZC6010;
    $mfield=genera_estructura_desarrollo($tabla);	
	$mfield['ZC6_FILIAL']['value']='01';
	$mfield['ZC6_ZITEM']['value']=$zitem;
	$mfield['ZC6_OC']['value']=$oc;
	$mfield['ZC6_CLIENT']['value']=$cliente;
	$mfield['ZC6_CANAL']['value']=$canal;
	$mfield['ZC6_LOCAL']['value']=$local;
	$mfield['ZC6_DLOCAL']['value']=$dlocal;
	$mfield['ZC6_ITEM']['value']=$item;
	$mfield['ZC6_INTCOD']['value']=$b1_cod;
	$mfield['ZC6_CODBAR']['value']=$b1_codbar;
	$mfield['ZC6_PRCVEN']['value']=$neto;
	$mfield['ZC6_CANT']['value']=$cantidad;
	$mfield['ZC6_VALOR']['value']=$valor;
	$mfield['ZC6_LOCPAD']['value']=$b1_locpad;
	$mfield['ZC6_UM']['value']=$b1_um;
	$mfield['ZC6_SEGUM']['value']=$b1_segum;
	$mfield['ZC6_INTDES']['value']=$b1_desc;
	$mfield['ZC6_FACTOR']['value']=$b1_factor;
	$mfield['ZC6_CLICOD']['value']=$clicod;
	$mfield['ZC6_CLIUPC']['value']=$clibarcod;
	$mfield['ZC6_CLIDES']['value']=$clides;
	$mfield['ZC6_FECOC']['value']=$fecoc;
	$mfield['ZC6_FEMIS']['value']=$fecemision;
	$mfield['ZC6_ENTREG']['value']=$fecentrega;
	$mfield['ZC6_ARCHIV']['value']=$archivo;
	$mfield['ZC6_ADD1']['value']=$vDcto1;
	$mfield['ZC6_ADD2']['value']=$vDcto2;
	$mfield['ZC6_ADD3']['value']=$vAdicional3;
	$mfield['R_E_C_N_O_']['value']=$recno;
	$mfield['ZC6_OKDIGI']['value']='N';	
	$mfield['ZC6_CONTA']['value']=$b1_conta;
	$mfield['ZC6_ITEMCT']['value']=$b1_itemcc;
	$mfield['ZC6_CLVL']['value']=$b1_clvl;
	$mfield['ZC6_GRUPO']['value']=$b1_grupo;
	$mfield['ZC6_CC']['value']=$b1_cc;
	$mfield['ZC6_USUARI']['value']=$usuario;
	// $mfield['ZC6_DCTO']['value']=$dcto;
	$mfield['ZC6_PRCOC']['value']=$prcoc;
	$mfield['ZC6_PRCCLI']['value']=$precio_cliente;
	$mfield['ZC6_LOJA']['value']='00';


	
	$sql=genera_insert($tabla,$mfield);
	$result = querys($sql,$tipobd_totvsDev2, $conexion_totvsDev2);

	// echo "SQL : ".$sql."<br>";

	$queryup = "update ".TBL_ZEQ." SET ZEQ_DEPTO='$cod_dpto', ZEQ_DDEPTO='$nombre_depto' 
					WHERE ZEQ_CLICOD='$clicod' AND ZEQ_CANAL='$canal'";
	$rsu = querys($queryup, $tipobd_totvsDev2, $conexion_totvsDev2);

		if($cellSKUmch == '999999999999999'){
			echo "ERROR-01: <strong> $cellCodprov, $cellSKUcli, $cellDescli </strong> <br>";
		}
	
			
	}
	actualiza_item($archivo);
	revisa_errores($archivo);
	valida_inner($archivo);
	
	
	cierra_conexion($tipobd_totvsDev2, $conexion_totvsDev2);
}

function actualiza_item($archivo){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
		$bod_item = 0;
	$hoy = date('Ymd');
	$querycount = "SELECT DISTINCT ZC6_INTCOD, ZC6_CLICOD FROM ".TBL_ZC6010." WHERE ZC6_ARCHIV='$archivo' ORDER BY ZC6_CLICOD ASC";
	$rsc = querys($querycount, $tipobd_totvsDev2, $conexion_totvsDev2);
	while($v = ver_result($rsc, $tipobd_totvsDev2)){
		$intcod = trim($v["ZC6_INTCOD"]);
		// $producto = $v["C6_PRODUTO"];
		
		$querysel = "SELECT * FROM ".TBL_ZC6010." WHERE ZC6_INTCOD='$intcod' and ZC6_ARCHIV='$archivo' ORDER BY ZC6_CLICOD ASC";
		$rss = querys($querysel, $tipobd_totvsDev2, $conexion_totvsDev2);
		$x = ver_result($rss, $tipobd_totvsDev2);
			$bod_item =	$bod_item+1;
			$item = 	correlativo($bod_item,2);
			$item = 	str_pad($item,2,'0', STR_PAD_LEFT);
			$num2 = trim($x["R_E_C_N_O_"]);
			 $producto = trim($x["ZC6_INTCOD"]);
			 $oc = trim($x["ZC6_OC"]);
			 
			$queryup = "UPDATE  ".TBL_ZC6010." SET ZC6_ITEM='$item' where  ZC6_INTCOD='$producto' AND ZC6_OC='$oc'";
			// echo "Query : ".$queryup. "<br>"; 
			$rsu = querys($queryup, $tipobd_totvsDev2, $conexion_totvsDev2);			
				
	}
	cierra_conexion($tipobd_totvsDev2, $conexion_totvsDev2);
}

function revisa_errores($archivo){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$querycount = "SELECT count(*) as FILAS FROM ".TBL_ZC6010." 
					WHERE  ZC6_CANAL='4003' 
					and D_E_L_E_T_<>'*'
					and ZC6_INTCOD = '999999999999999'
					AND ZC6_ARCHIV = '$archivo'";
	$rsc =  querys($querycount, $tipobd_totvsDev2, $conexion_totvsDev2);
	$v = ver_result($rsc, $tipobd_totvsDev2);
	$filas = $v["FILAS"];
	
	//echo "FILAS : ".$filas;
	
	if($filas > 0){
		$queryup = "UPDATE ".TBL_ZC6010." SET ZC6_OKDIGI='E' WHERE ZC6_ARCHIV = '$archivo'";
		$rsu = querys($queryup, $tipobd_totvsDev2, $conexion_totvsDev2);
	}
	cierra_conexion($tipobd_totvsDev2, $conexion_totvsDev2);
}
function valida_inner($archivo){
	global $tipobd_totvsDev2, $conexion_totvsDev2;

	$querysel = "SELECT Z.ZC6_OC, ZC6_LOCAL, ZC6_DLOCAL,ZC6_INTCOD, ZC6_CANT , B1_INNER, ZC6_CANT/B1_INNER AS VALIDACION 
		FROM ZC6010 Z 
			LEFT JOIN SB1010 S ON TRIM(ZC6_INTCOD)=TRIM(B1_COD) 
		 WHERE ZC6_ARCHIV='$archivo'";
	$rss = querys($querysel, $tipobd_totvsDev2, $conexion_totvsDev2);
	while($v = ver_result($rss, $tipobd_totvsDev2)){

		$cantidad 	= $v["ZC6_CANT"];
		$inner 		= $v["B1_INNER"];
		
		$r = fmod($cantidad, $inner);
		
		if($r != 0){
			$queryup = "UPDATE ".TBL_ZC6010." SET ZC6_OKDIGI='I' WHERE ZC6_ARCHIV = '$archivo'";
			$rsu = querys($queryup, $tipobd_totvsDev2, $conexion_totvsDev2);
		}
	}
	cierra_conexion($tipobd_totvsDev2, $conexion_totvsDev2);
}


function existe_planilla_zc6($archivo){	
    global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$querysel_1 = "SELECT COUNT(ZC6_OC) AS FILAS FROM ".TBL_ZC6010."
	WHERE ZC6_ARCHIV='$archivo' AND ZC6_CLIENT='833827006' AND D_E_L_E_T_<>'*'";
		
	$rss_1 = querys($querysel_1, $tipobd_totvsDev2, $conexion_totvsDev2);
	$v1 = ver_result($rss_1, $tipobd_totvsDev2);
	$filas = $v1["FILAS"];
	if($filas > 0){
		//echo '<script language="javascript">alert("ERROR : CODIGO '.$cod.' NO EXISTEN EN BODEGA DE DESTINO");</script>';
		echo "ERROR : PLANILLA '$archivo' YA CARGADA";
		http_response_code(400);
		die();
	}
}


///////////////////////////////////////////////////ARRIBA///////////////////////////////////////////////////////////
///////////////////////////////////////////////CODIGO NUEVO/////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////ABAJO FUNCIONES PROCESA A TOTVS//////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function reprocesar_planilla($archivo){
	//global $tipobd_ptl,$conexion_ptl;
	global $tipobd_totvsDev2, $conexion_totvsDev2;

	$querysel = "SELECT 
		ZC6_OC, ZC6_OKDIGI, ZC6_NUM, ZC6_CANAL
	FROM ".TBL_ZC6010."
	WHERE ZC6_ARCHIV = '$archivo'
	GROUP BY ZC6_OC, ZC6_OKDIGI, ZC6_NUM, ZC6_CANAL";


	$rss1 = querys($querysel,$tipobd_totvsDev2, $conexion_totvsDev2);
	$v = ver_result($rss1, $tipobd_totvsDev2);
	$oc 			= $v["ZC6_OC"];
	$ok_digitacion  = $v["ZC6_OKDIGI"];
	$num_totvs 		= $v["ZC6_NUM"];
	$canal 			= $v["ZC6_CANAL"];
	
	$queryrep = "UPDATE ".TBL_ZC6010." SET ZC6_OKDIGI='N', ZC6_NUM=' ', ZC6_WMSNUM=' ', ZC6_TICKET=0 WHERE  ZC6_OC='$oc' AND ZC6_CANAL='$canal'";
	//echo "quuery 1: ".$queryrep."<br>";
	$rz6 = querys($queryrep, $tipobd_totvsDev2, $conexion_totvsDev2);
	
	$querydel_c5 = "DELETE FROM SC5010 WHERE C5_NUM='$num_totvs'";
	//echo "quuery 2: ".$querydel_c5."<br>";
	$rc5 = querys($querydel_c5, $tipobd_totvsDev2, $conexion_totvsDev2);
	
	$querydel_c6 = "DELETE FROM SC6010 WHERE c6_num='$num_totvs'";
	//echo "quuery 3: ".$querydel_c6."<br>";
	$rc6 = querys($querydel_c6, $tipobd_totvsDev2, $conexion_totvsDev2);
	
	echo "OC $oc Revertida";
	
	
	cierra_conexion($tipobd_totvsDev2, $conexion_totvsDev2);

}





function graba_head_pedido($oc){
	global $tipobd_totvsDev2, $conexion_totvsDev2;

	$select = "SELECT * FROM ".TBL_ZC6010." WHERE ROWNUM=1 AND ZC6_OC='$oc' AND ZC6_CANAL='4003' ORDER BY ZC6_OC ASC";
	$rss = querys($select,$tipobd_totvsDev2, $conexion_totvsDev2);
	
	if($rss){
		while($fila_oc =ver_result($rss, $tipobd_totvsDev2)){
				$rut = '833827006';
				cliente($rut);
				global $a1_cod, $a1_nreduz, $a1_cond, $a1_naturez, $a1_tabela, $a1_grpven, $a1_loja, $a1_vend, $dconpag;
				global $valida_cli;
				
				$fcompromiso = $fila_oc['ZC6_ENTREG'];
				$femision 	 = $fila_oc["ZC6_FEMIS"];
				
				$total_oc=contar_uni($oc);


				$filial = 	'01';
				$num = 		c5_num();
				$tipo = 	'N';
				$mtipven = 	'01';
				$cliente = 	$a1_cod;
				$uniresp = 	'9';
				$lojacli = 	$a1_loja;
				$transp = 	' ';
				$local = 	$a1_nreduz;
				$tipocli = 	'A';
				$condpag = 	$a1_cond;
				$tabela = 	$a1_tabela;
				$vend1 = 	$a1_vend;
				$dconpag1=   trim($dconpag);
				$comis1 = 	'0';
				$entrega = 	$fcompromiso;
				$orcom = 	trim($oc);
				$mcantot = 	$total_oc;

				$desc1 = 	$fila_oc['ZC6_ADD1'];
				// $desc1 = 	(float)$desc1 * 100;

				$desc2 = 	$fila_oc['ZC6_ADD2'];
				// $desc2 = 	(float)$desc2 * 100;


				$desc3 = 	'0';
				$emissao = 	$femision;
				$moeda = 	1;
				$tiplib = 	'1';
				$tiporem = 	0;
				$naturez = 	$a1_naturez;
				$txmoeda = 	1;
				$tpcarga = 	2;
				$docger = 	2;
				$gerawms = 	1;
				$fecent = 	1;
				$solopc = 	1;
				$liqprod = 	2;
				$userlgi = 	"CONECTOR";
				$userlga = 	"CONECTOR";
				$dte = 		1;
				$recno		= recno_head();
				//$xagrupa = '1';
				$xintra ='1';
				$xdsitra='OPERACIÓN CONSTITUYE VENTA';
				// $xdsitra = utf8_encode($xdsitra_x);
				
				
				//echo $num.'<br>';
				//echo $recno.'<br>';
				$insert = "insert into SC5010
				(c5_filial, 	c5_num, 	c5_tipo, 	c5_mtipven, 	c5_cliente,	c5_uniresp,	c5_lojacli, 	c5_client,
				c5_lojaent, 	c5_transp,	c5_local,	c5_tipocli,	c5_condpag,	c5_tabela,	c5_vend1,	c5_dconpag,
				c5_comis1,	c5_entrega,	c5_orcom,	c5_mcantot,	c5_desc1,	c5_desc2,	c5_desc3,	c5_emissao,
				c5_moeda,	c5_tiplib,	c5_tiporem,	c5_naturez,	c5_txmoeda,	c5_tpcarga,	c5_docger,	c5_gerawms,
				c5_fecent,	c5_solopc,	c5_liqprod,	c5_dte, R_E_C_N_O_,C5_USERLGA,C5_XINDTRA,C5_XDSITRA, C5_IDWMS)
				values
				('$filial',	'$num',		'$tipo','$mtipven',	'$cliente',	'$uniresp',	'$lojacli',	'$cliente',
				'$lojacli', 	'$transp',	'$local',	'$tipocli',	'$condpag',	'$tabela',	'$vend1',	'$dconpag1',
				$comis1,	'$entrega',	'$orcom',	$mcantot,	$desc1, 	$desc2,		$desc3,		'$emissao',
				$moeda,		'$tiplib',	'$tiporem',	'$naturez',	$txmoeda,	'$tpcarga',	'$docger',	'$gerawms',
				'$fecent',	'$solopc',	'$liqprod',	'$dte', $recno,'$userlga','$xintra','$xdsitra',	'$orcom')";
				//echo $insert.'<br>';
				//die();
				$rs = querys($insert,$tipobd_totvsDev2, $conexion_totvsDev2);
			
			$queryup = "UPDATE ".TBL_ZC6010." SET ZC6_OKDIGI ='S', ZC6_NUM='$num' WHERE ZC6_OC='$orcom' AND ZC6_CANAL='4003'";
			$rsu = querys($queryup,$tipobd_totvsDev2, $conexion_totvsDev2);

			if(oci_num_rows($rs)<>0 or oci_num_rows($rs)<>false){
			 echo 'ORDEN DE COMPRA: <strong>'.$orcom.'</strong> - <strong>'.$num.'</strong></br>';
			 graba_detail_pedido($oc,$num);
						
			}
		
		}
		
	}

	
}




function graba_detail_pedido($oc, $num){
	global $tipobd_totvsDev2, $conexion_totvsDev2;

	$hoy = 		date('Ymd');
	$select = "SELECT 
	ZC6_OC, ZC6_ITEM, ZC6_CLIENT, ZC6_CANAL, ZC6_INTCOD, ZC6_CODBAR, ZC6_PRCVEN, ZC6_CLICOD,ZC6_FEMIS,ZC6_ENTREG, ZC6_PRCOC,SUM(ZC6_CANT) AS UNIDADES
FROM ZC6010 
WHERE ZC6_OC = '$oc' AND ZC6_CANAL='4003'
GROUP BY ZC6_OC, ZC6_ITEM, ZC6_CLIENT, ZC6_CANAL, ZC6_INTCOD, ZC6_CODBAR, ZC6_PRCVEN, ZC6_CLICOD, ZC6_FEMIS, ZC6_ENTREG , ZC6_PRCOC
ORDER BY ZC6_CLICOD ASC";
		
	//echo "GRABA DETALLE:".$select;
	//die();
	
	$rss = querys($select, $tipobd_totvsDev2, $conexion_totvsDev2); /////HASTA AQUI ESTA OK

	//HASTA AQUI ESTA OK

	$resulta =false;
	if($rss){	 
		$bod_item = 0;
		while($fila = ver_result($rss, $tipobd_totvsDev2)){
			$codigo_monarch = trim($fila['ZC6_INTCOD']);
			$codbar_monarch = trim($fila['ZC6_CODBAR']);

			

			//echo "COD_MONARCH: ".$codigo_monarch." , ITEM: ".$itemt;
			//echo "<br>";
					
			articulo($codigo_monarch); //consulta datos propios del artículo o producto como, codigo monarch, descripción, bodega, factor de convesión
			global $b1_cod, $b1_codbar, $b1_desc, $b1_um, $b1_locpad, $b1_segum, $b1_conv, $b1_grupo, $b1_cc, $b1_itemcc, $b1_clvl, $b1_conta, $b1_factor;
			
			// $fec_entrega = date("Ymd", strtotime($fila['FEC_ENTREGA'])+7);
			//$itemt 			= $fila['ZC6_ITEM'];
			$itemt 			= $fila['ZC6_ITEM'];
			$fec_entrega 	= $fila['ZC6_ENTREG'];
			$cliente	 	= trim($fila['ZC6_CLIENT']);
			$canal 			= trim($fila['ZC6_CANAL']);
			$unidades 		= $fila['UNIDADES'];
			$precio_lista 	= $fila['ZC6_PRCVEN'];
			$precio_oc 	= $fila['ZC6_PRCOC'];
			$valor_descuento = ($precio_oc-$precio_lista)*$unidades;
			$valor 			= $unidades*$precio_lista;
			$pru2um_2		= $precio_lista*$b1_factor;
			
             			
			$filial = 	'01';			
			$item = 	$itemt;
			$produto = 	trim($codigo_monarch);
			$um = 		$b1_um;
			$unsven = 	$unidades;
			$qtdven = 	$unidades;
			$prunit = 	$precio_oc;
			$pru2um = 	$pru2um_2;
			$descuento = 0;
			$segum = 	$b1_segum;
			$prcven = 	$precio_lista;
			$valor = 	round($valor,2);
			$local = 	$b1_locpad;
			$tes = 		'501';				
			$conta = 	$b1_conta;
			$entreg = 	$fec_entrega;
			$cc = 		$b1_cc;
			$itemcta = 	$b1_itemcc;
			$clvl = 	$b1_clvl;
			$mcanal = 	$canal;
			$grupo = 	$b1_grupo;
			$cf = 		'511';
			$cli = 		$cliente;
			$valor_descuento = 		round($valor_descuento);											////////
			$loja = 	'00';
			$num = 		$num;
			$descri = 	$b1_desc;
			$tpop = 	'F';
			$geranf = 	'S';
			$sugentr = 	$fec_entrega;
			$bkpprun = 	$precio_lista;
			$rateio = 	2;
			$codbar = 	trim($b1_codbar);
			$unempq = 	0;
			$capac = 	0;
			$recno = 	recno_detail();
			
			
			$insert = "insert into SC6010
			 (c6_filial,	c6_item,        c6_produto,		c6_um,          c6_unsven,      c6_qtdven,      c6_prunit,
			c6_pru2um,	c6_descont,						c6_segum,	c6_prcven,         	c6_valor,       c6_local,       c6_tes,         c6_conta,
			c6_entreg,      c6_cc,          c6_itemcta,         	c6_clvl,        c6_mcanal,      c6_grupo,       c6_cf,
			c6_cli,      c6_valdesc   ,c6_loja,        c6_num,         	c6_descri,      c6_tpop,        c6_geranf,      c6_sugentr,
			c6_bkpprun,	c6_rateio,	c6_codbar,		c6_unempq,	c6_capac,      	r_e_c_n_o_,C6_PRCLIST, C6_FACTOR)
			values
			('$filial',	'$item',	'$produto',		'$um',		$unsven,	$qtdven,	$prunit,
			$pru2um, '$descuento',	'$segum',	$prcven,		$valor,		'$local',	'$tes',		'$conta',
			'$entreg',	'$cc',		'$itemcta',		'$clvl',	'$mcanal',	'$grupo',	'$cf',
			'$cli',	$valor_descuento	,'$loja',	'$num',			'$descri',	'$tpop',	'$geranf',	'$sugentr',
			$bkpprun,	'$rateio',	'$codbar',		$unempq,	'$capac',	$recno, $precio_lista,$b1_factor)";
			
			//echo "SC6: ".$insert.'<br>';		

			$reintentos = 5;
            $intento_actual = 0;

            while ($intento_actual < $reintentos) {
                try {
                    $rs = querys($insert, $tipobd_totvsDev2, $conexion_totvsDev2);
                    $queryup_rec = "UPDATE ".TBL_ZC6010." SET ZC6_SC6REC=$recno WHERE ZC6_FILIAL='01' AND zc6_intcod='$produto' AND zc6_num='$num' AND D_E_L_E_T_<>'*'";
                    $rsur = querys($queryup_rec, $tipobd_totvsDev2, $conexion_totvsDev2);
                    //echo "Inserción exitosa.";
                    break;
                } catch (Exception $e) {
                    $intento_actual++;
                    if ($intento_actual == $reintentos) {
                        echo "Error al insertar en la base de datos: " . $e->getMessage();
                        // Puedes registrar el error en un archivo log o notificar al administrador
                    } else {
                        echo "Reintentando la inserción... Intento $intento_actual de $reintentos.\n";
                    }
                }
            }
			
		}
	}
}


function existe_oc_totvs($oc){	
    global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$querysel_1 = "SELECT COUNT(C5_ORCOM) AS FILAS FROM SC5010
					WHERE  C5_ORCOM='$oc' and C5_CLIENT='833827006'  and C5_LOJACLI='00' and D_E_L_E_T_<>'*'";
		
	$rss_1 = querys($querysel_1, $tipobd_totvsDev2, $conexion_totvsDev2);
	$v1 = ver_result($rss_1, $tipobd_totvsDev2);
	$filas = $v1["FILAS"];
	if($filas > 0){
		//echo '<script language="javascript">alert("ERROR : CODIGO '.$cod.' NO EXISTEN EN BODEGA DE DESTINO");</script>';
		echo "ERROR : ORDEN DE COMPRA $oc YA EXISTE, DIGITACION NO FUE CARGADA";
		die();
	}
}
function carga_itemCostumer($oc){
	global $tipobd_totvs,$conexion_totvs;

	$querysel = "select ZC6_CANAL,ZC6_CLICOD from zc6010
				where ZC6_FILIAL='01' 
				AND ZC6_OC='$oc' 
				AND D_E_L_E_T_<>'*'
				GROUP BY ZC6_CANAL,ZC6_CLICOD";
	$rss = querys($querysel, $tipobd_totvs, $conexion_totvs);
	while($v = ver_result($rss, $tipobd_totvs)){
		$canal  = trim($v["ZC6_CANAL"]);
		$clicod = trim($v["ZC6_CLICOD"]);

		ws_carga_itemCostumer($canal, $clicod);

	}
}
function pedidos_wms($oc){
	global $tipobd_totvsDev2, $conexion_totvsDev2;

	$querysel = "SELECT ZC6_WMSNUM FROM ZC6010 WHERE ZC6_OC='$oc' GROUP BY ZC6_WMSNUM order by ZC6_WMSNUM";
	$rss = querys($querysel , $tipobd_totvsDev2, $conexion_totvsDev2);
	while ($v = ver_result($rss, $tipobd_totvsDev2)){

		$wms_num = trim($v["ZC6_WMSNUM"]);

		WS_OutBoundOrder($wms_num);

	}
}
function ver_documentos_wms($oc){
	global $tipobd_totvsDev2,$conexion_totvsDev2;

	$querysel = "SELECT Z.ZC6_WMSNUM,Z.ZC6_TICKET,    T.ESTADO
					FROM     ZC6010 Z LEFT JOIN  TICKET T ON Z.ZC6_TICKET = T.TICKET
					WHERE Z.ZC6_TICKET > 0
					AND Z.ZC6_OC='$oc'
					GROUP BY Z.ZC6_TICKET, Z.ZC6_WMSNUM, T.ESTADO
					ORDER BY ZC6_WMSNUM";
	$rss = querys($querysel, $tipobd_totvsDev2, $conexion_totvsDev2);
	while($v = ver_result($rss, $tipobd_totvsDev2)){
		$ticket[]= array(
			"ZC6_WMSNUM" 	=> $v["ZC6_WMSNUM"],
			"ZC6_TICKET" 	=> $v["ZC6_TICKET"],
			"ESTADO" 		=> $v["ESTADO"]
		);

	}
	echo json_encode($ticket);
}
function digitacion_totvs($oc){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	//existe_oc_totvs($oc); //FUNCIONANDO -- DESCOMENTAR
	
	$select = "SELECT 
		ZC6_OC, COUNT(DISTINCT ZC6_OC) AS TOTAL 
	FROM ".TBL_ZC6010." WHERE ZC6_OC='$oc'
	AND ZC6_OKDIGI='N'
	AND ZC6_CANAL='4003'
	GROUP BY ZC6_OC
	ORDER BY ZC6_OC ASC";
	//echo $select;
	// $oc="";
	$contador=1;
	// echo $select;
	// die();
	$rss = querys($select,$tipobd_totvsDev2, $conexion_totvsDev2);
	if($rss){
		while($fila_oc = ver_result($rss, $tipobd_totvsDev2)){
		
			$oc=$fila_oc['ZC6_OC'];
		  // if($contador<4){

		graba_head_pedido($oc);
		actualiza_wmspedido($oc);
		actualiza_itemwal($oc);
		actualiza_precios($oc);
		//carga_itemCostumer($oc);
		}
	  echo 'ARCHIVO CARGADO EN ERP CON EXITO';
	  die();
	 }
	 
}
function actualiza_precios($oc){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$hoy = date('Ymd');
	$querysel = "SELECT ZC6_CANAL,ZC6_INTCOD, ZC6_CLICOD, ZC6_PRCCLI, (SELECT ZEQ_PRCCLI FROM ".TBL_ZEQ." WHERE ZEQ_CLICOD=ZC6_CLICOD) AS PRC_ZEQ,
				ZC6_PRCCLI-(SELECT ZEQ_PRCCLI FROM ".TBL_ZEQ." WHERE ZEQ_CLICOD=ZC6_CLICOD) AS DIFERECIA
			   FROM ".TBL_ZC6010." WHERE ZC6_OC='$oc'
			   group by ZC6_CANAL,ZC6_INTCOD, ZC6_CLICOD, ZC6_PRCCLI
			   order by ZC6_INTCOD";
	$rss = querys($querysel, $tipobd_totvsDev2, $conexion_totvsDev2);
	while($v = ver_result($rss, $tipobd_totvsDev2)){
		$canal 				= trim($v["ZC6_CANAL"]);
		$sku_cliente 		= trim($v["ZC6_CLICOD"]);
		$precio_cliente 	= $v["ZC6_PRCCLI"];
		$diferencia 		= $v["DIFERECIA"];
		
		if($diferencia > 0){
				$queryup = "UPDATE ".TBL_ZEQ." SET ZEQ_PRCCLI=$precio_cliente, ZEQ_DTMOD='$hoy', ZEQ_OKWMS='N' WHERE ZEQ_CLICOD='$sku_cliente' AND ZEQ_CANAL='$canal'";
				$rsu = querys($queryup, $tipobd_totvsDev2, $conexion_totvsDev2);
				
				//echo "PRECIO ACTUALIZADO DE SKU $sku_cliente  <br>";
		}
	}
}
function actualiza_wmspedido($oc_numero){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$i = 0;
	$querysel = "select DISTINCT  ZC6_LOCAL, ZC6_NUM  from ".TBL_ZC6010."  where ZC6_OC='$oc_numero'";
	$rss = querys($querysel, $tipobd_totvsDev2, $conexion_totvsDev2);
	while($v = ver_result($rss, $tipobd_totvsDev2)){
		$i = $i+1;
		$local = $v["ZC6_LOCAL"];
		$pedido = $v["ZC6_NUM"];
		
		$wms_num = $pedido.'-'.str_pad($i,3,'0', STR_PAD_LEFT);
		$wmsitem = str_pad($i,2,'0', STR_PAD_LEFT);
		
		
		$queryup = "UPDATE ZC6010 SET ZC6_WMSNUM ='$wms_num' where ZC6_OC='$oc_numero' AND ZC6_LOCAL='$local'";
		$rsu = querys($queryup, $tipobd_totvsDev2, $conexion_totvsDev2);		
	}
}
function actualiza_itemwal($oc_numero){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	
	$querysel = "select DISTINCT  ZC6_LOCAL  from ".TBL_ZC6010."  where ZC6_OC='$oc_numero'";
	$rss = querys($querysel, $tipobd_totvsDev2, $conexion_totvsDev2);
	while($v = ver_result($rss, $tipobd_totvsDev2)){
		//$articulo = $v["ZC6_INTCOD"];
		$local = $v["ZC6_LOCAL"];
		
		$i = 0;
		$querysel2 ="SELECT DISTINCT ZC6_INTCOD,ZC6_LOCAL FROM ".TBL_ZC6010."  WHERE  ZC6_OC='$oc_numero' and ZC6_LOCAL='$local'";
		//echo $querysel2."<br>";
		$rs2 = querys($querysel2, $tipobd_totvsDev2, $conexion_totvsDev2);
		while($v2 = ver_result($rs2, $tipobd_totvsDev2)){
		$i = $i+1;
			$articulo2 = $v2["ZC6_INTCOD"];
			$local2 = $v2["ZC6_LOCAL"];
			//$wms_num = $pedido.'-'.str_pad($i,2,'0', STR_PAD_LEFT);
			$wmsitem = 	correlativo($i,2);
			$wmsitem = str_pad($wmsitem,2,'0', STR_PAD_LEFT);
			
			
			$queryup = "UPDATE ".TBL_ZC6010."  SET ZC6_WMSITE ='$wmsitem' where ZC6_OC='$oc_numero' AND ZC6_INTCOD='$articulo2' AND ZC6_LOCAL='$local2'";
			$rsu = querys($queryup, $tipobd_totvsDev2, $conexion_totvsDev2);		
		}
	}
}   

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function convierte_codigomch($sku){
	global $tipobd_totvsDev2,$conexion_totvsDev2;
	
	$codigo ="";
	$queryin = "SELECT ZEQ_COD FROM ".TBL_ZEQ." WHERE ZEQ_FILIAL='01' AND  ZEQ_CLICOD='$sku' AND D_E_L_E_T_<>'*'";
	//echo "<br>". $queryin . "<br>";
	$rss = querys($queryin,$tipobd_totvsDev2,$conexion_totvsDev2);	
	while ($row2 = ver_result($rss, $tipobd_totvsDev2)) {                
       $codigo = trim($row2['ZEQ_COD']);
    }

	if($codigo == ''){
		return $codigo='999999999999999';
	}else{
		return $codigo;
	}
	cierra_conexion($tipobd_totvsDev2,$conexion_totvsDev2);
}
function convierte_precio($codigo_monarch){
    global $tipobd_totvs,$conexion_totvs;
	
	$precio ="";
	$queryin = "SELECT DA1_PRCVEN FROM DA1010 WHERE DA1_CODPRO='$codigo_monarch' AND DA1_CODTAB='3' AND D_E_L_E_T_<>'*'";
	$rss = querys($queryin,$tipobd_totvs,$conexion_totvs);
	while ($row2 = ver_result($rss, $tipobd_totvs)) {                
       $precio = trim($row2['DA1_PRCVEN']);
    }

	if($precio == ''){
		return $precio=0;
	}else{
		return $precio;
	}

	cierra_conexion($tipobd_totvs, $conexion_totvs);
}
function rellena($variable,$largo,$caracter,$direccion){
        $cont = strlen($variable);
        for ($i = $cont; $i < $largo; $i++) {
                switch ($direccion) {
                    case 'I':
                                $variable = $caracter.$variable;
                        break;
                    case 'D':
                                $variable = $variable.$caracter;
                        break;
                        default:
                                $variable = $variable;
                }
        }
        return ($variable);
}
//============================================================================================
//============================================================================================
//============================================================================================


if(isset($_FILES['file_cventas']['name'])){
	
	$nombre_archivo = $_FILES['file_cventas']['name'];
	// existe_planilla_zc6($nombre_archivo);
	
    subirArchivo();
	//$nombre_archivo = 'corona.csv';
	leer_archivo($nombre_archivo);
	

}
if(isset($_GET["confirma_digitacion"])){
	
	//$articulo = $_GET["articulo"];	
	$oc 	= $_GET["oc"];//secuencia	
    digitacion_totvs($oc);
	// correo_pedidos_digitados($ola);
}
if(isset($_GET["transferir_wms"])){
	
	//$articulo = $_GET["articulo"];	
	$oc 	= $_GET["oc"];//secuencia	
    pedidos_wms($oc);
	// correo_pedidos_digitados($ola);
}
if(isset($_GET["ver_docu_wms"])){
	
	//$articulo = $_GET["articulo"];	
	$oc 	= $_GET["oc"];//secuencia	
    ver_documentos_wms($oc);
	// correo_pedidos_digitados($ola);
}
if(isset($_GET["reprocesar"])){
	$archivo = $_GET["archivo"];
    reprocesar_planilla($archivo);
}
if(isset($_GET["borrarPlanilla"])){
	$archivo = $_GET["archivo"];
    borrarPlanilla($archivo);
}


if(isset($_GET["ver"])){
    datos_subidos();
}


?>