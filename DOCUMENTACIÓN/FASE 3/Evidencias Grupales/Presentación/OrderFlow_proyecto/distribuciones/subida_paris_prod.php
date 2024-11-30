<?php

error_reporting();
require_once "../config.php";
require_once "../conexion.php";
require_once "../generar_insert.php";
require '../PHPExcel-1.8/Classes/PHPExcel.php';
require_once "../ws/wmsImport/itemCostumer.php"; 
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
	
	$dir_subida = '../archivos_subidos/archivos_distribucion/paris_distribucion/';
	$fichero_subido = $dir_subida.basename($_FILES['file_cventas']['name']);
	$nombre = $_FILES['file_cventas']['tmp_name'];	
	
	if (move_uploaded_file($_FILES['file_cventas']['tmp_name'], $fichero_subido)) {
		
		$error = $_FILES['file_cventas']['error'];		 
		$type  = $_FILES['file_cventas']['type'];

		if($error == 1){
			echo "TAMAÑO ARCHIVO EXCEDE MAXIMO PERMITIDO";
		}else{
			//echo "El fichero es valido y subido con Exito !\n<br>";
			//echo "Tipo Archivo : ".$type."<br>";
		}	

	}

}
function datos_subidos(){
	// global $tipobd_totvs,$conexion_totvs;
	global $tipobd_totvs, $conexion_totvs;
	
	$hoy = date('Ymd');
	$querysel = "SELECT ZC6_ARCHIV, ZC6_OC, ZC6_FECOC,ZC6_OKDIGI,ZC6_NUM,
					SUM(ZC6_CANT) AS SOLICITADO, count(distinct ZC6_CLICOD) as ARTICULOS,
						COUNT(DISTINCT ZC6_CLICOD) AS CANT_TOTAL, NVL((SELECT MAX(D2_DTDIGIT) FROM SD2010 WHERE D2_FILIAL='01' and D2_PEDIDO=ZC6_NUM AND D_E_L_E_T_<>'*'),' ') AS FEC_FACT,
							CASE WHEN COUNT( DISTINCT ZC6_TICKET) >1 THEN 'S' ELSE 'N' END AS EN_WMS
						FROM ZC6010
						WHERE ZC6_CANAL='4002'
						AND ZC6_FILIAL='01'
						AND D_E_L_E_T_<>'*'
						GROUP BY ZC6_ARCHIV, ZC6_OC, ZC6_FECOC,ZC6_OKDIGI,ZC6_NUM
						ORDER BY ZC6_FECOC DESC";
	$rss = querys($querysel,$tipobd_totvs,$conexion_totvs);
	MwriteSql('../sql/datos_subidos_subida_paris.sql', $querysel);
	while($v = ver_result($rss, $tipobd_totvs)){
		$nombre_archivo = $v["ZC6_ARCHIV"];
		$datos[]=array(
					"NOM_ARCHIVO" 	=> trim($v["ZC6_ARCHIV"]),
					"NOM_ARCHIVO_DESCARGA" 	=> "<a href='archivos_subidos/archivos_distribucion/paris_distribucion/$nombre_archivo'>$nombre_archivo</a>",
					"NRO_ORDEN"   	=> $v["ZC6_OC"], 
					"FECHA_SUBIDA" 	=> formatDate($v["ZC6_FECOC"]),
					"SOLICITADO"   	=> $v["SOLICITADO"], 
					"ARTICULOS"   	=> $v["ARTICULOS"],
					"OK_DIGITACION"   	=> $v["ZC6_OKDIGI"],
					"NUM_TOTVS"   	=> $v["ZC6_NUM"],
					"FEC_FACT"   	=> trim(formatDate($v["FEC_FACT"])),					
					"EN_WMS"   	=> $v["EN_WMS"]
				);
		}

	echo json_encode($datos);
	
	cierra_conexion($tipobd_totvs,$conexion_totvs);
}

function recno_tabla(){
	global $tipobd_totvs,$conexion_totvs;
	
	$select = "SELECT nvl(MAX(R_E_C_N_O_),0)+1 AS R_E_C_N_O_ FROM ".TBL_ZC6010."";
	$rs = querys($select,$tipobd_totvs,$conexion_totvs);
	$fila = ver_result($rs, $tipobd_totvs);
	$recno = $fila['R_E_C_N_O_'];

	return $recno;

	cierra_conexion($tipobd_totvs,$conexion_totvs);
}


function convierte_codigomch($sku){
	global $tipobd_totvs,$conexion_totvs;
	
	$codigo ="";
	$queryin = "SELECT ZEQ_COD FROM ".TBL_ZEQ." WHERE ZEQ_FILIAL='01' AND  ZEQ_CLICOD='$sku' AND D_E_L_E_T_<>'*'";
	//echo "<br>". $queryin . "<br>";
	$rss = querys($queryin,$tipobd_totvs,$conexion_totvs);	
	while ($row2 = ver_result($rss, $tipobd_totvs)) {                
       $codigo = trim($row2['ZEQ_COD']);
    }

	if($codigo == ''){
		return $codigo='999999999999999';
	}else{
		return $codigo;
	}
	cierra_conexion($tipobd_totvs,$conexion_totvs);
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
		$b1_factor	= 0;
		
		$da1_prcven	= 0;
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
function existe_oc_distribucion($oc){	
    global $tipobd_totvs,$conexion_totvs;
	
	
		$querysel_1 = "SELECT COUNT(ZC6_OC) AS FILAS FROM ".TBL_ZC6010."
						WHERE  ZC6_OC='$oc' and ZC6_CANAL='4002' and D_E_L_E_T_<>'*'";
			 //echo $querysel_1."<br>";
			$rss_1 = querys($querysel_1, $tipobd_totvs, $conexion_totvs);
			$v1 = ver_result($rss_1, $tipobd_totvs);
			$filas = $v1["FILAS"];
			if($filas > 0){
				//echo '<script language="javascript">alert("ERROR : CODIGO '.$cod.' NO EXISTEN EN BODEGA DE DESTINO");</script>';
				echo "ERROR : ORDEN DE COMPRA $oc YA EXISTE";
				die();
			}		
	
	
}

function leer_archivo($archivo){
	global $tipobd_totvs,$conexion_totvs;
	global $tipobd_totvs, $conexion_totvs;
	
	
	$path="../archivos_subidos/archivos_distribucion/paris_distribucion/";
    $nombreArchivo = $path.$archivo;
	$objPHPExcel = PHPExcel_IOFactory::load($nombreArchivo);
	
	// Asigno la hoja de calculo activa
	$objPHPExcel->setActiveSheetIndex(0);
	// Obtengo el numero de filas del archivo
	$numRows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
	
	$hoy = date('Ymd');
	
	$bod_item 	= 0;
	$nro_orden	= $objPHPExcel->getActiveSheet()->getCell('A2')->getCalculatedValue();
	existe_oc_distribucion($nro_orden);
	
	
	//die();

	for ($i = 2; $i <= $numRows; $i++) {
		
		$nro_orden 		 	= $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
		$cod_depto 		 	= $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
		$departamento 		= $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
		$estado 		 	= $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
		$tipo_orden 		= $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
		$cod_local_entrega  = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();
		$local_entrega 		= $objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue();
		$cod_local_destino  = $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue();
		$local_destino 		= $objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue();
		$fecha_vigencia		= $objPHPExcel->getActiveSheet()->getCell('K'.$i);
		$fecha_vigencia 	= date('Ymd', PHPExcel_Shared_Date::ExcelToPHP($fecha_vigencia->getValue()));
		$fecha_vencimiento	= $objPHPExcel->getActiveSheet()->getCell('L'.$i);
		$fecha_vencimiento 	= date('Ymd', PHPExcel_Shared_Date::ExcelToPHP($fecha_vencimiento->getValue()));
		$fecha_emision		= $objPHPExcel->getActiveSheet()->getCell('M'.$i);
		$fecha_emision	 	= date('Ymd',PHPExcel_Shared_Date::ExcelToPHP($fecha_emision->getValue()));
		// $fecha_emision	 	= convertExcelDateToDateTime($fecha_emision);
		$sku	 			= $objPHPExcel->getActiveSheet()->getCell('N'.$i)->getCalculatedValue();
		$cod_prod_prov 		= $objPHPExcel->getActiveSheet()->getCell('O'.$i)->getCalculatedValue();
		$cod_barra_paris 	= $objPHPExcel->getActiveSheet()->getCell('P'.$i)->getCalculatedValue();
		$tipo_flujo 		= $objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getCalculatedValue();
		$descript_paris 	= $objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue();
		$color 				= $objPHPExcel->getActiveSheet()->getCell('S'.$i)->getCalculatedValue();
		$dimension 			= $objPHPExcel->getActiveSheet()->getCell('T'.$i)->getCalculatedValue();
		$talla 				= trim($objPHPExcel->getActiveSheet()->getCell('U'.$i)->getCalculatedValue());
		$innerpack 			= $objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue();
		$precio_normal 		= $objPHPExcel->getActiveSheet()->getCell('W'.$i)->getCalculatedValue();
		$precio_oferta 		= $objPHPExcel->getActiveSheet()->getCell('X'.$i)->getCalculatedValue();
		$precio_costo 		= $objPHPExcel->getActiveSheet()->getCell('Y'.$i)->getCalculatedValue();
		$precio_oc 			= $objPHPExcel->getActiveSheet()->getCell('Y'.$i)->getCalculatedValue();
		$descuento 			= $objPHPExcel->getActiveSheet()->getCell('Z'.$i)->getCalculatedValue();
		$costo_neto 		= $objPHPExcel->getActiveSheet()->getCell('AA'.$i)->getCalculatedValue();
		$solicitado 		= $objPHPExcel->getActiveSheet()->getCell('AB'.$i)->getCalculatedValue();
		//$solicitado 		= $objPHPExcel->getActiveSheet()->getCell('AB'.$i)->getCalculatedValue();
		$descuento_1 		= $objPHPExcel->getActiveSheet()->getCell('AK'.$i)->getCalculatedValue();
		$descuento_2 		= $objPHPExcel->getActiveSheet()->getCell('AL'.$i)->getCalculatedValue();
		$recno = recno_tabla();

		$hoy = date('Ymd');
		$fecha_subida = $hoy;

		$item = ' ';

		// //$recno = recno();
		// print $fecha_subida."<br>";
		// echo $fecha_emision;
		// die();
		
		$codigo_monarch = convierte_codigomch($sku);
		$precio_venta = lista_precios($codigo_monarch);
		///APLICANDO DESCUENTOS
		//primer descuento
		$costo_desc1 = round(($precio_costo*$descuento_1)/100,2);
		$precio_costo = $precio_costo-$costo_desc1;
		
		//segundo descuento
		$costo_desc2 = round(($precio_costo*$descuento_2)/100,2);
		$precio_costo = $precio_costo-$costo_desc2;
		
		//Precio final
		$costo_con_descuento = round($precio_costo,2);
		///FIN APLICANDO DESCUENTOS
		$valor = $solicitado*$costo_con_descuento;
		$valor = round($valor, 2);
		$usuario = $_SESSION["user"];
		
		articulo($codigo_monarch); //consulta datos propios del artículo o producto como, codigo monarch, descripción, bodega, factor de convesión
		global $b1_cod, $b1_codbar, $b1_desc, $b1_um, $b1_locpad, $b1_segum, $b1_conv, $b1_grupo, $b1_cc, $b1_itemcc, $b1_clvl, $b1_conta, $b1_factor;
		
		$bod_item =	$bod_item+1;
		$zitem = 	correlativo($bod_item,4);
		$zitem = 	str_pad($zitem,4,'0', STR_PAD_LEFT);
				
		if($talla=='Extra Large'){$talla="XL";}
		elseif($talla=='Talla A'){$talla="A";}
		elseif($talla=='Talla B'){$talla="B";}
		elseif($talla=='Talla 1/2'){$talla="1/2";}
		elseif($talla=='Talla 2'){$talla="2";}
		elseif($talla=='Talla 3'){$talla="3";}
		elseif($talla=='Talla 3/4'){$talla="3/4";}
		elseif($talla=='Talla 4'){$talla="4";}
		elseif($talla=='Talla L/XL'){$talla="L/XL";}
		elseif($talla=='Talla S/M'){$talla="S/M";}
		elseif($talla=='Talla 10/12'){$talla="10/12";}
		elseif($talla=='Talla 10'){$talla="10";}
		elseif($talla=='Talla 10M'){$talla="10M";}
		elseif($talla=='Talla 11M'){$talla="11M";}
		elseif($talla=='Única'){$talla="U";}
		elseif($talla=='TALLA UNICA'){$talla="U";}
		else{$talla='U';}
			
	$tabla=TBL_ZC6010;
    $mfield=genera_estructura($tabla);	
	$mfield['ZC6_FILIAL']['value']='01';
	$mfield['ZC6_ZITEM']['value']=$zitem;
	$mfield['ZC6_OC']['value']=$nro_orden;
	$mfield['ZC6_CLIENT']['value']='81201000K';
	$mfield['ZC6_CANAL']['value']='4002';
	$mfield['ZC6_LOCAL']['value']=$cod_local_destino;
	$mfield['ZC6_DLOCAL']['value']=$local_destino;
	$mfield['ZC6_ITEM']['value']='01';
	$mfield['ZC6_INTCOD']['value']=$codigo_monarch;
	$mfield['ZC6_CODBAR']['value']=$b1_codbar;
	$mfield['ZC6_PRCVEN']['value']=$costo_con_descuento;
	$mfield['ZC6_CANT']['value']=$solicitado;
	$mfield['ZC6_VALOR']['value']=$valor;
	$mfield['ZC6_LOCPAD']['value']=$b1_locpad;
	$mfield['ZC6_UM']['value']=$b1_um;
	$mfield['ZC6_SEGUM']['value']=$b1_segum;
	$mfield['ZC6_INTDES']['value']=$b1_desc;
	$mfield['ZC6_FACTOR']['value']=$b1_factor;
	$mfield['ZC6_CLICOD']['value']=$sku;
	$mfield['ZC6_CLIUPC']['value']=$cod_barra_paris;
	$mfield['ZC6_CLIDES']['value']=$descript_paris;
	$mfield['ZC6_FECOC']['value']=$hoy;
	$mfield['ZC6_FEMIS']['value']=$fecha_emision;
	$mfield['ZC6_ENTREG']['value']=$fecha_vencimiento;
	$mfield['ZC6_ARCHIV']['value']=$archivo;
	$mfield['ZC6_ADD4']['value']=$talla;
	$mfield['ZC6_OKDIGI']['value']='N';
	$mfield['ZC6_CONTA']['value']=$b1_conta;
	$mfield['ZC6_ITEMCT']['value']=$b1_itemcc;
	$mfield['ZC6_CLVL']['value']=$b1_clvl;
	$mfield['ZC6_GRUPO']['value']=$b1_grupo;
	$mfield['ZC6_CC']['value']=$b1_cc;
	$mfield['ZC6_USUARI']['value']=$usuario;
	$mfield['ZC6_EMPQ']['value']=$innerpack;
	$mfield['ZC6_PRCOC']['value']=$precio_oc;
	$mfield['ZC6_DCTO']['value']=$descuento_1;
	$mfield['R_E_C_N_O_']['value']=$recno;
	$mfield['ZC6_PRCCLI']['value']=$precio_normal;
	
	$sql=genera_insert($tabla,$mfield);
	$result = querys($sql,$tipobd_totvs,$conexion_totvs);
	
	// echo "SQL : ".$sql."<br>";
	
	$queryup = "update ".TBL_ZEQ." SET ZEQ_PRCCLI='$precio_normal', ZEQ_DEPTO='$cod_depto', ZEQ_DDEPTO='$departamento', ZEQ_ADD1='$talla'  
					WHERE ZEQ_CLICOD='$sku' AND ZEQ_CANAL='4002'";
	$rsu = querys($queryup, $tipobd_totvs, $conexion_totvs);

			
	}
	actualiza_item($archivo);
	revisa_errores($archivo);
	valida_inner($archivo);


	cierra_conexion($tipobd_totvs,$conexion_totvs);
}
function valida_inner($archivo){
	global $tipobd_totvs,$conexion_totvs;

	$querysel = "SELECT Z.ZC6_OC, ZC6_LOCAL, ZC6_DLOCAL,ZC6_INTCOD, ZC6_CANT , B1_INNER, ZC6_CANT/B1_INNER AS VALIDACION 
		FROM ZC6010 Z 
			LEFT JOIN SB1010 S ON TRIM(ZC6_INTCOD)=TRIM(B1_COD) 
		 WHERE ZC6_ARCHIV='$archivo'";
	$rss = querys($querysel, $tipobd_totvs, $conexion_totvs);
	while($v = ver_result($rss, $tipobd_totvs)){

		$cantidad 	= $v["ZC6_CANT"];
		$inner 		= $v["B1_INNER"];
		
		$r = fmod($cantidad, $inner);
		
		if($r != 0){
			$queryup = "UPDATE ".TBL_ZC6010." SET ZC6_OKDIGI='I' WHERE ZC6_ARCHIV = '$archivo'";
			$rsu = querys($queryup, $tipobd_totvs, $conexion_totvs);
		}
	}
	cierra_conexion($tipobd_totvs,$conexion_totvs);
}
function revisa_errores($archivo){
	global $tipobd_totvs,$conexion_totvs;
	
	$querycount = "SELECT count(*) as FILAS FROM ".TBL_ZC6010."  
					WHERE  ZC6_CANAL='4002' 
					and D_E_L_E_T_<>'*'
					and ZC6_INTCOD = '999999999999999'
					AND ZC6_ARCHIV = '$archivo'";
	$rsc =  querys($querycount, $tipobd_totvs, $conexion_totvs);
	$v = ver_result($rsc, $tipobd_totvs);
	$filas = $v["FILAS"];
	
	//echo "FILAS : ".$filas;
	
	if($filas > 0){
		$queryup = "UPDATE ".TBL_ZC6010."  SET ZC6_OKDIGI='E' WHERE ZC6_ARCHIV = '$archivo'";
		$rsu = querys($queryup, $tipobd_totvs, $conexion_totvs);
		die();
	}
	cierra_conexion($tipobd_totvs,$conexion_totvs);
}
function ver_errores($archivo){
	global $tipobd_totvs,$conexion_totvs;
	
	$querysel = "SELECT ZC6_CLICOD, ZC6_CLIDES FROM ".TBL_ZC6010."  
					WHERE  ZC6_CANAL='4002' 
					and D_E_L_E_T_<>'*'
					and ZC6_INTCOD = '999999999999999'
					AND ZC6_ARCHIV = '$archivo'
					GROUP BY ZC6_CLICOD, ZC6_CLIDES";
	$rss = querys($querysel, $tipobd_totvs, $conexion_totvs);
	while($v = ver_result($rss, $tipobd_totvs)){
				$datos[]=array(
					
					"SKU"   		=> $v["ZC6_CLICOD"],
					"CLI_DES"   	=> $v["ZC6_CLIDES"]
				);
		}
	echo json_encode($datos);	
}
function actualiza_item($archivo){
	global $tipobd_totvs,$conexion_totvs;
	
		$bod_item = 0;
	$hoy = date('Ymd');
	$querycount = "SELECT DISTINCT ZC6_INTCOD FROM ".TBL_ZC6010." WHERE ZC6_ARCHIV='$archivo' ORDER BY  ZC6_INTCOD";
	$rsc = querys($querycount, $tipobd_totvs,$conexion_totvs);
	while($v = ver_result($rsc, $tipobd_totvs)){
		$intcod = trim($v["ZC6_INTCOD"]);
		// $producto = $v["C6_PRODUTO"];
		
		$querysel = "SELECT * FROM ".TBL_ZC6010." WHERE ZC6_INTCOD='$intcod' and ZC6_ARCHIV='$archivo' ORDER BY ZC6_INTCOD";
		$rss = querys($querysel, $tipobd_totvs,$conexion_totvs);
		$x = ver_result($rss, $tipobd_totvs);
			$bod_item =	$bod_item+1;
			$item = 	correlativo($bod_item,2);
			$item = 	str_pad($item,2,'0', STR_PAD_LEFT);
			$num2 = trim($x["R_E_C_N_O_"]);
			 $producto = trim($x["ZC6_INTCOD"]);
			 $oc = trim($x["ZC6_OC"]);
			 
			$queryup = "UPDATE  ".TBL_ZC6010." SET ZC6_ITEM='$item' where  ZC6_INTCOD='$producto' AND ZC6_OC='$oc'";
			// echo "Query : ".$queryup. "<br>"; 
			$rsu = querys($queryup, $tipobd_totvs, $conexion_totvs);			
				
	}
	cierra_conexion($tipobd_totvs,$conexion_totvs);
}

function borrarPlanilla($archivo){
	global $tipobd_totvs,$conexion_totvs;

	$query = "DELETE FROM  ".TBL_ZC6010." WHERE ZC6_ARCHIV = '$archivo'";
	$rss = querys($query,$tipobd_totvs,$conexion_totvs);

	echo "Archivo <strong>$archivo</strong> borrado con exito!!";

}
function reprocesar_planilla($archivo){
	//global $tipobd_ptl,$conexion_ptl;
	global $tipobd_totvs,$conexion_totvs;

	$querysel = "SELECT ZC6_OC,ZC6_OKDIGI,ZC6_NUM,ZC6_CANAL
				FROM  ".TBL_ZC6010." 
				WHERE ZC6_ARCHIV = '$archivo'
				GROUP BY ZC6_OC,ZC6_OKDIGI,ZC6_NUM,ZC6_CANAL";
	$rss1 = querys($querysel,$tipobd_totvs,$conexion_totvs);
	$v = ver_result($rss1, $tipobd_totvs);
	$oc 			= $v["ZC6_OC"];
	$ok_digitacion  = $v["ZC6_OKDIGI"];
	$num_totvs 		= $v["ZC6_NUM"];
	$canal 			= $v["ZC6_CANAL"];
	
	$queryrep = "UPDATE  ".TBL_ZC6010." SET ZC6_OKDIGI='N',ZC6_NUM=' ' WHERE  ZC6_OC='$oc' and ZC6_CANAL='$canal'";
	echo "quuery 1: ".$queryrep."<br>";
	$rsp = querys($queryrep, $tipobd_totvs,$conexion_totvs);
	
	$querydel_c5 = "DELETE FROM SC5010 WHERE C5_NUM='$num_totvs'";
	echo "quuery 2: ".$querydel_c5."<br>";
	$rc5 = querys($querydel_c5, $tipobd_totvs, $conexion_totvs);
	
	$querydel_c6 = "DELETE FROM SC6010 WHERE c6_num='$num_totvs'";
	echo "quuery 3: ".$querydel_c6."<br>";
	$rc6 = querys($querydel_c6, $tipobd_totvs, $conexion_totvs);
	
	
	
	
	cierra_conexion($tipobd_totvs,$conexion_totvs);

}

//============================================================================================
//============================================================================================
//============================================================================================

function lista_precios($cod_monarch){
	global $tipobd_totvs,$conexion_totvs;
	
	$querysel = "SELECT NVL(MAX(DA1_PRCVEN),0) AS DA1_PRCVEN FROM DA1010 WHERE DA1_CODPRO='$cod_monarch' AND DA1_CODTAB='011'";
	$rss = querys($querysel, $tipobd_totvs, $conexion_totvs);
	$fila = ver_result($rss, $tipobd_totvs);
	$pr_venta = $fila['DA1_PRCVEN'];
	return $pr_venta;
}
function recno_detail(){
	global $tipobd_totvs,$conexion_totvs;
	
	$select = "SELECT NVL(MAX(R_E_C_N_O_),0)+1 AS CORRELATIVO FROM SC6010";
	$rs = querys($select,$tipobd_totvs,$conexion_totvs);
	$fila = ver_result($rs, $tipobd_totvs);
	$recno = $fila['CORRELATIVO'];
	return $recno;
	
}
function contar_uni($oc){
	global $tipobd_totvs,$conexion_totvs;
	
	$select = "SELECT SUM(ZC6_CANT) AS TOTAL FROM ".TBL_ZC6010." WHERE ZC6_OC='$oc' AND ZC6_CANAL='4002'";
	// echo "CONTAR UNI ".$select.'<br>';
	$rs = querys($select,$tipobd_totvs,$conexion_totvs);
	$fila = ver_result($rs, $tipobd_totvs);
	$num = $fila['TOTAL'];
	return $num;
}

function recno(){
	global $contar_oc;
	global $tipobd_totvs,$conexion_totvs;
	
	$select = "SELECT NVL(MAX(R_E_C_N_O_),0)+1 AS CORRELATIVO FROM SC5010";
	$rs = querys($select,$tipobd_totvs,$conexion_totvs);
	$fila = ver_result($rs, $tipobd_totvs);
	$recno = $fila['CORRELATIVO'];
	return $recno;
}
function c5_num(){
	global $tipobd_totvs,$conexion_totvs;
	//global $conexion3;
	
	
	$select = "SELECT MAX(TO_NUMBER(C5_NUM))+1 AS NUM FROM SC5010 WHERE C5_NUM BETWEEN '100000' AND '899999'";
	$rs = querys($select, $tipobd_totvs, $conexion_totvs);
	$fila = ver_result($rs, $tipobd_totvs);
	$num = $fila['NUM'];
	return $num;
}

function cliente($glncliente){
	global $tipobd_totvs,$conexion_totvs;
	global $a1_cod, $a1_nreduz, $a1_cond, $a1_naturez, $a1_tabela, $a1_grpven, $a1_loja, $a1_vend, $dconpag;
	global $valida_cli;
	
	$count = "SELECT COUNT(*) AS NUMFILAS FROM SA1010, SE4010
	WHERE A1_COND=E4_CODIGO AND A1_COD='$glncliente' AND A1_LOJA = '00' AND  SA1010.D_E_L_E_T_ <> '*' AND SE4010.D_E_L_E_T_ <> '*'";//
	$rs = querys($count,$tipobd_totvs,$conexion_totvs);
	$filac = ver_result($rs, $tipobd_totvs);
	if($filac['NUMFILAS'] == 1){
		$query = "SELECT A1_COD, A1_NREDUZ, A1_COND, A1_NATUREZ, A1_TABELA, A1_GRPVEN, A1_LOJA, A1_VEND, E4_DESCRI
		FROM  SA1010, SE4010
		WHERE A1_COND=E4_CODIGO AND A1_COD='$glncliente' AND A1_LOJA = '00' AND  SA1010.D_E_L_E_T_ <> '*' AND SE4010.D_E_L_E_T_ <> '*'";//
	
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

function existe_oc_totvs($oc){	
    global $tipobd_totvs,$conexion_totvs;
	
	$querysel_1 = "SELECT COUNT(C5_ORCOM) AS FILAS FROM SC5010
					WHERE  C5_ORCOM='$oc' and C5_CLIENT='81201000K'  and C5_LOJACLI='00' and D_E_L_E_T_<>'*'";
		// echo $querysel_1."<br>";
		$rss_1 = querys($querysel_1, $tipobd_totvs, $conexion_totvs);
		$v1 = ver_result($rss_1, $tipobd_totvs);
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
function digitacion_totvs($oc){
	global $tipobd_totvs,$conexion_totvs;
	
	 //existe_oc_totvs($oc); //FUNCIONANDO -- DESCOMENTAR
	
	$select = "select ZC6_OC, COUNT(DISTINCT ZC6_OC) AS TOTAL 
				from ".TBL_ZC6010." WHERE ZC6_OC='$oc'
				AND ZC6_OKDIGI='N'
				AND ZC6_CANAL='4002'
				GROUP BY ZC6_OC
                ORDER BY ZC6_OC ASC";//and RPY_IBOLETA='4675'
	//echo $select;
	// $oc="";
	$contador=1;
	// echo $select;
	// die();
	$rss = querys($select,$tipobd_totvs,$conexion_totvs);
	if($rss){
		while($fila_oc = ver_result($rss, $tipobd_totvs)){
		 
		  $oc=$fila_oc['ZC6_OC'];
		  // if($contador<4){

		graba_head_pedido($oc);	
		actualiza_precios($oc);
		actualiza_wmspedido($oc);
		actualiza_itemwal($oc);			
		carga_itemCostumer($oc);			
		  
		}
	  echo 'ARCHIVO CARGADO EN TOTVS CON EXITO';
	  die();
	 }
	 
  }
  function actualiza_precios($oc){
	global $tipobd_totvs, $conexion_totvs;
	
	$hoy = date('Ymd');
	$querysel = "SELECT ZC6_CANAL,ZC6_INTCOD, ZC6_CLICOD, ZC6_PRCCLI, (SELECT ZEQ_PRCCLI FROM ".TBL_ZEQ." WHERE ZEQ_CLICOD=ZC6_CLICOD) AS PRC_ZEQ,
				ZC6_PRCCLI-(SELECT ZEQ_PRCCLI FROM ".TBL_ZEQ." WHERE ZEQ_CLICOD=ZC6_CLICOD) AS DIFERECIA
			   FROM ".TBL_ZC6010." WHERE ZC6_OC='$oc'
			   group by ZC6_CANAL,ZC6_INTCOD, ZC6_CLICOD, ZC6_PRCCLI
			   order by ZC6_INTCOD";
	$rss = querys($querysel, $tipobd_totvs, $conexion_totvs);
	while($v = ver_result($rss, $tipobd_totvs)){
		$canal 				= trim($v["ZC6_CANAL"]);
		$sku_cliente 		= trim($v["ZC6_CLICOD"]);
		$precio_cliente 	= $v["ZC6_PRCCLI"];
		$diferencia 		= $v["DIFERECIA"];
		
		if($diferencia > 0){
				$queryup = "UPDATE ".TBL_ZEQ." SET ZEQ_PRCCLI=$precio_cliente, ZEQ_DTMOD='$hoy', ZEQ_OKWMS='N' WHERE ZEQ_CLICOD='$sku_cliente' AND ZEQ_CANAL='$canal'";
				$rsu = querys($queryup, $tipobd_totvs, $conexion_totvs);
				
				//echo "PRECIO ACTUALIZADO DE SKU $sku_cliente  <br>";
		}
	}
}
function actualiza_wmspedido($oc_numero){
	global $tipobd_totvs, $conexion_totvs;
	
	$i = 0;
	$querysel = "select DISTINCT  ZC6_LOCAL, ZC6_NUM  from ".TBL_ZC6010."  where ZC6_OC='$oc_numero'";
	$rss = querys($querysel, $tipobd_totvs, $conexion_totvs);
	while($v = ver_result($rss, $tipobd_totvs)){
		$i = $i+1;
		$local = $v["ZC6_LOCAL"];
		$pedido = $v["ZC6_NUM"];
		
		$wms_num = $pedido.'-'.str_pad($i,3,'0', STR_PAD_LEFT);
		$wmsitem = str_pad($i,2,'0', STR_PAD_LEFT);
		
		
		$queryup = "UPDATE ZC6010 SET ZC6_WMSNUM ='$wms_num' where ZC6_OC='$oc_numero' AND ZC6_LOCAL='$local'";
		$rsu = querys($queryup, $tipobd_totvs, $conexion_totvs);		
	}
}
function actualiza_itemwal($oc_numero){
	global $tipobd_totvs, $conexion_totvs;
	
	$querysel = "select DISTINCT  ZC6_LOCAL  from ".TBL_ZC6010."  where ZC6_OC='$oc_numero'";
	$rss = querys($querysel, $tipobd_totvs, $conexion_totvs);
	while($v = ver_result($rss, $tipobd_totvs)){
		//$articulo = $v["ZC6_INTCOD"];
		$local = $v["ZC6_LOCAL"];
		
		$i = 0;
		$querysel2 ="SELECT DISTINCT ZC6_INTCOD,ZC6_LOCAL FROM ".TBL_ZC6010."  WHERE  ZC6_OC='$oc_numero' and ZC6_LOCAL='$local'";
		//echo $querysel2."<br>";
		$rs2 = querys($querysel2, $tipobd_totvs, $conexion_totvs);
		while($v2 = ver_result($rs2, $tipobd_totvs)){
		$i = $i+1;
			$articulo2 = $v2["ZC6_INTCOD"];
			$local2 = $v2["ZC6_LOCAL"];
			//$wms_num = $pedido.'-'.str_pad($i,2,'0', STR_PAD_LEFT);
			$wmsitem = 	correlativo($i,2);
			$wmsitem = str_pad($wmsitem,2,'0', STR_PAD_LEFT);
			
			
			$queryup = "UPDATE ".TBL_ZC6010."  SET ZC6_WMSITE ='$wmsitem' where ZC6_OC='$oc_numero' AND ZC6_INTCOD='$articulo2' AND ZC6_LOCAL='$local2'";
			$rsu = querys($queryup, $tipobd_totvs, $conexion_totvs);		
		}
	}
}   

function graba_head_pedido($oc){
	global $tipobd_totvs,$conexion_totvs;
	global $tipobd_ptl,$conexion_ptl;
	
	$select = "SELECT * FROM ".TBL_ZC6010." WHERE ROWNUM=1 AND ZC6_OC='$oc' AND ZC6_CANAL='4002' ORDER BY ZC6_OC ASC";
	
	// echo 'graba head_:'.$select;
	// die();
		
	$rss = querys($select,$tipobd_totvs,$conexion_totvs);
	if($rss){
		while($fila_oc =ver_result($rss, $tipobd_totvs)){
				$rut = '81201000K';
				cliente($rut);
				global $a1_cod, $a1_nreduz, $a1_cond, $a1_naturez, $a1_tabela, $a1_grpven, $a1_loja, $a1_vend, $dconpag;
				global $valida_cli;
				
				$fcompromiso = $fila_oc['ZC6_ENTREG'];
				$femision 	 = $fila_oc["ZC6_FEMIS"];
				
				// echo "FECHA ".$fcompromiso."<br>";
				// echo "FECHAe ".$femision."<br>";
				 
				 // die();
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
				$desc1 = 	'0';
				$desc2 = 	'0';
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
				$recno		= recno();
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
				$rs = querys($insert,$tipobd_totvs,$conexion_totvs);
			
			$queryup = "UPDATE ".TBL_ZC6010." SET ZC6_OKDIGI ='S',ZC6_NUM='$num' WHERE ZC6_OC='$orcom' AND ZC6_CANAL='4002'";
			// echo "UPDATE falabella : ".$queryup."<br>";
			$rsu = querys($queryup,$tipobd_totvs,$conexion_totvs);
			if(oci_num_rows($rs)<>0 or oci_num_rows($rs)<>false){
			 echo 'ORDEN DE COMPRA: <strong>'.$orcom.'</strong> - <strong>'.$num.'</strong></br>';
    // die();
			 graba_detail_pedido($oc,$num);
						
			}
		
		}
		
	}
}
function graba_detail_pedido($oc,$num){
	global $tipobd_totvs,$conexion_totvs;
	global $tipobd_ptl,$conexion_ptl;
	//global $e_falabella_pas;
	//variables detalle
		

		$select = "SELECT ZC6_ITEM,ZC6_CLIENT,ZC6_CANAL, ZC6_INTCOD,ZC6_PRCVEN, ZC6_PRCOC, ZC6_FEMIS,ZC6_ENTREG,SUM(ZC6_CANT) AS UNIDADES
					FROM ".TBL_ZC6010." 
					WHERE ZC6_OC='$oc'
					AND ZC6_CANAL='4002'
					GROUP BY ZC6_ITEM,ZC6_CLIENT,ZC6_CANAL, ZC6_INTCOD,ZC6_PRCVEN, ZC6_PRCOC, ZC6_FEMIS,ZC6_ENTREG
					ORDER BY ZC6_ITEM,NLSSORT(ZC6_INTCOD,'NLS_SORT=BINARY_AI')";		

	
	// echo "GRABA DETALLE:".$select."<br>";
	//die();
	
	$rss = querys($select,$tipobd_totvs,$conexion_totvs);
	$resulta =false;
	if($rss){	 
		$bod_item = 0;
		while($fila = ver_result($rss, $tipobd_totvs)){
			$codigo_monarch = trim($fila['ZC6_INTCOD']);
					
			articulo($codigo_monarch); //consulta datos propios del artículo o producto como, codigo monarch, descripción, bodega, factor de convesión
			global $b1_cod, $b1_codbar, $b1_desc, $b1_um, $b1_locpad, $b1_segum, $b1_conv, $b1_grupo, $b1_cc, $b1_itemcc, $b1_clvl, $b1_conta, $b1_factor;
			
			// $fec_entrega = date("Ymd", strtotime($fila['FEC_ENTREGA'])+7);
			$itemt 			= $fila['ZC6_ITEM'];
			$fec_entrega 	= $fila['ZC6_ENTREG'];
			$cliente	 	= trim($fila['ZC6_CLIENT']);
			$canal 			= trim($fila['ZC6_CANAL']);
			$unidades 		= $fila['UNIDADES'];
			$precio_lista 	= $fila['ZC6_PRCVEN'];
			$precio_oc 		= $fila['ZC6_PRCOC'];
			$valor_descuento = 0;
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
			$prcven = 	round($precio_lista);
			$valor = 	round($valor);
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
			
			
			$insert = "INSERT into SC6010
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
			
			 //echo "SC6 : ".$insert.'<br>';
			$reintentos = 5;
            $intento_actual = 0;

            while ($intento_actual < $reintentos) {
                try {
                    $rs = querys($insert, $tipobd_totvs, $conexion_totvs);
                    $queryup_rec = "UPDATE ".TBL_ZC6010." SET ZC6_SC6REC=$recno WHERE ZC6_FILIAL='01' AND zc6_intcod='$produto' AND zc6_num='$num' AND D_E_L_E_T_<>'*'";
                    $rsur = querys($queryup_rec, $tipobd_totvs, $conexion_totvs);
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




//============================================================================================
//============================================================================================
//============================================================================================

if(isset($_FILES['file_cventas']['name'])){
	
	$nombre_archivo = $_FILES['file_cventas']['name'];
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

if(isset($_GET["borrarPlanilla"])){
	$archivo = $_GET["archivo"];
    borrarPlanilla($archivo);
}
if(isset($_GET["reprocesar"])){
	$archivo = $_GET["archivo"];
    reprocesar_planilla($archivo);
}

if(isset($_GET["ver"])){
    datos_subidos();
}
if(isset($_GET["ver_errores"])){
	$archivo = $_GET["archivo"];
    ver_errores($archivo);
}




?>