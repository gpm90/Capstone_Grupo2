<?php

require_once "conexion.php";
require_once "config.php";

function genera_insert($tabla, $vfield){
	
	$sql_insert = "INSERT INTO $tabla (";
	$sql1 = "";
	$i = 0;
	foreach ($vfield as $key => $value) {
		$sql1 = $sql1 . $key . ",";
		$i++;
	}
	$sql1 = chop($sql1, ",");
	$sql2 = " ) VALUES (";
	$i = 0;

//	 echo"<pre>";
//    print_r($vfield);
//    echo"</pre>";
	
	foreach ($vfield as $key => $value) {
		$tipo = trim($vfield[$key]['tipo']);
		$valor = trim($vfield[$key]['value']);
		$xkey = strtoupper($key);
		$txt= $vfield[$xkey]['value'];
	
		$lsep=$vfield[$xkey]['lsep'] ;
	    $rsep=$vfield[$xkey]['rsep'] ;
		
		if (($tipo == 'varchar2' or $tipo == 'char' or $tipo == 'number') and ($valor=="")) {$txt = "' '"; ;$lsep='';$rsep='';
		}
		
		//echo "<br>tipo: $tipo  key:$key - valor: $valor - texto:$txt";
		//echo "---- :".var_dump(is_null($valor));
		
		//if (($tipo == 'number' or $tipo == 'float' or $tipo == 'money') and (trim($valor) == "")) {$txt = 0;
		//}
		if ($tipo == 'date') {$txt = 'NULL';
		}
		if ($tipo == 'blob' and $valor == '') {
			$txt = 'NULL';
		}
		$texto = $lsep.$txt  . $rsep;
		
		$sql2 = $sql2 . $texto . ",";
		$i++;

	}
	
	$sql2 = chop($sql2, ",");
	$sql2 = $sql2 . ")";

	return ($sql_insert . $sql1 . $sql2);

}

function genera_estructura($tabla){

	global $tipobd_totvs,$conexion_totvs;
	
	$sql = "select * from $tabla where 1=0";

	$result = querys($sql, $tipobd_totvs,$conexion_totvs);
	$ncols = oci_num_fields($result);
	for ($n = 1; $n <= $ncols; $n++) {
		$field_name = strtoupper(oci_field_name($result, $n));
		$vtabla_columnas[$field_name] = $field_name;
		$vfield[$field_name]['field'] = $field_name;
		$vfield[$field_name]['len'] = oci_field_size($result, $n);
		$tipo = strtolower(trim(oci_field_type($result, $n)));
		$vfield[$field_name]['tipo'] = strtolower(oci_field_type($result, $n));
		$vfield[$field_name]['lsep'] = "";
		$vfield[$field_name]['rsep'] = "";
		if ($tipo=='long raw' or $tipo == 'varchar' or $tipo == 'varchar2' or $tipo == 'nvarchar' or $tipo == 'char'  or $tipo == 'text' or $tipo == 'image') {$vfield[$field_name]['lsep'] = "'";
		}
		if ($tipo=='long raw' or $tipo == 'varchar' or $tipo == 'varchar2' or $tipo == 'nvarchar' or $tipo == 'char'  or $tipo == 'text' or $tipo == 'image') {$vfield[$field_name]['rsep'] = "'";
		}
		$vfield[$field_name]['value'] = ($tipo=='long raw' or $tipo == 'date' or $tipo == 'varchar2' or $tipo == 'nvarchar' or $tipo == 'char' or $tipo == 'text' or $tipo == 'image') ? ' ' : 0;
	}

	return ($vfield);

}

function genera_estructura_desarrollo($tabla){

	global $tipobd_totvsDev2,$conexion_totvsDev2;
	
	$sql = "select * from $tabla where 1=0";

	$result = querys($sql, $tipobd_totvsDev2,$conexion_totvsDev2);
	$ncols = oci_num_fields($result);
	for ($n = 1; $n <= $ncols; $n++) {
		$field_name = strtoupper(oci_field_name($result, $n));
		$vtabla_columnas[$field_name] = $field_name;
		$vfield[$field_name]['field'] = $field_name;
		$vfield[$field_name]['len'] = oci_field_size($result, $n);
		$tipo = strtolower(trim(oci_field_type($result, $n)));
		$vfield[$field_name]['tipo'] = strtolower(oci_field_type($result, $n));
		$vfield[$field_name]['lsep'] = "";
		$vfield[$field_name]['rsep'] = "";
		if ($tipo=='long raw' or $tipo == 'varchar' or $tipo == 'varchar2' or $tipo == 'nvarchar' or $tipo == 'char'  or $tipo == 'text' or $tipo == 'image') {$vfield[$field_name]['lsep'] = "'";
		}
		if ($tipo=='long raw' or $tipo == 'varchar' or $tipo == 'varchar2' or $tipo == 'nvarchar' or $tipo == 'char'  or $tipo == 'text' or $tipo == 'image') {$vfield[$field_name]['rsep'] = "'";
		}
		$vfield[$field_name]['value'] = ($tipo=='long raw' or $tipo == 'date' or $tipo == 'varchar2' or $tipo == 'nvarchar' or $tipo == 'char' or $tipo == 'text' or $tipo == 'image') ? ' ' : 0;
	}

	return ($vfield);

}
function genera_estructura_validacion($tabla){

	global $tipobd_totvs_valid,$conexion_totvs_valid;
	
	$sql = "select * from $tabla where 1=0";

	$result = querys($sql, $tipobd_totvs_valid,$conexion_totvs_valid);
	$ncols = oci_num_fields($result);
	for ($n = 1; $n <= $ncols; $n++) {
		$field_name = strtoupper(oci_field_name($result, $n));
		$vtabla_columnas[$field_name] = $field_name;
		$vfield[$field_name]['field'] = $field_name;
		$vfield[$field_name]['len'] = oci_field_size($result, $n);
		$tipo = strtolower(trim(oci_field_type($result, $n)));
		$vfield[$field_name]['tipo'] = strtolower(oci_field_type($result, $n));
		$vfield[$field_name]['lsep'] = "";
		$vfield[$field_name]['rsep'] = "";
		if ($tipo=='long raw' or $tipo == 'varchar' or $tipo == 'varchar2' or $tipo == 'nvarchar' or $tipo == 'char'  or $tipo == 'text' or $tipo == 'image') {$vfield[$field_name]['lsep'] = "'";
		}
		if ($tipo=='long raw' or $tipo == 'varchar' or $tipo == 'varchar2' or $tipo == 'nvarchar' or $tipo == 'char'  or $tipo == 'text' or $tipo == 'image') {$vfield[$field_name]['rsep'] = "'";
		}
		$vfield[$field_name]['value'] = ($tipo=='long raw' or $tipo == 'date' or $tipo == 'varchar2' or $tipo == 'nvarchar' or $tipo == 'char' or $tipo == 'text' or $tipo == 'image') ? ' ' : 0;
	}

	return ($vfield);

}


function genera_estructura_portal($tabla){

	global $tipobd_portal, $conexion_portal;
	
	$sql = "select * from $tabla where 1=0";

	$result = querys($sql, $tipobd_portal,$conexion_portal);
	$ncols = oci_num_fields($result);
	for ($n = 1; $n <= $ncols; $n++) {
		$field_name = strtoupper(oci_field_name($result, $n));
		$vtabla_columnas[$field_name] = $field_name;
		$vfield[$field_name]['field'] = $field_name;
		$vfield[$field_name]['len'] = oci_field_size($result, $n);
		$tipo = strtolower(trim(oci_field_type($result, $n)));
		$vfield[$field_name]['tipo'] = strtolower(oci_field_type($result, $n));
		$vfield[$field_name]['lsep'] = "";
		$vfield[$field_name]['rsep'] = "";
		if ($tipo=='long raw' or $tipo == 'varchar' or $tipo == 'varchar2' or $tipo == 'nvarchar' or $tipo == 'char'  or $tipo == 'text' or $tipo == 'image') {$vfield[$field_name]['lsep'] = "'";
		}
		if ($tipo=='long raw' or $tipo == 'varchar' or $tipo == 'varchar2' or $tipo == 'nvarchar' or $tipo == 'char'  or $tipo == 'text' or $tipo == 'image') {$vfield[$field_name]['rsep'] = "'";
		}
		$vfield[$field_name]['value'] = ($tipo=='long raw' or $tipo == 'date' or $tipo == 'varchar2' or $tipo == 'nvarchar' or $tipo == 'char' or $tipo == 'text' or $tipo == 'image') ? ' ' : 0;
	}

	return ($vfield);

}

?>