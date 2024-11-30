<?php
error_reporting(E_ALL);
/**
  *  Tablas MYSQL
  */
define('USUARIOS','USUARIOS');
define('MENU','MENU');
define('PERMISOS','PERMISOS');




/**
  *  Tablas totvs 
  */
define('TBL_COMPRA_DICOTEX','TOTVS.Z2B_XCOMPRA');
define('TBL_SC5','TOTVS.SC5010');
define('TBL_SC6','TOTVS.SC6010');
define('TBL_SC9','TOTVS.SC9010');
define('TBL_SA1','TOTVS.SA1010');
define('TBL_SA2','TOTVS.SA2010');
define('TBL_SB1','TOTVS.SB1010');
define('TBL_SB2','TOTVS.SB2010');
define('TBL_ACY','TOTVS.ACY010');
define('TBL_ZEQ','TOTVS.ZEQ010');
define('TBL_ZC6010','TOTVS.ZC6010');
define('TBL_ZC7010','TOTVS.ZC7010');
define('TBL_SC7','TOTVS.SC7010');
define('TBL_XD3010','TOTVS.XD3010');
define('TBL_XC7','TOTVS.XC7010');
// define('TBL_ZC6010','TOTVS.ZZ6010');
/**
  *  arreglos con valores de campos estaticos 
  */
$ec     = array('S'=>'SOLTERO(A)','C'=>'CASADO(A)','E'=>'SEPARADO(A)','V'=>'VIUDO(A)','0'=>'NO INFORMADO');
$conta  = array('N'=>'Concepto General','S'=>'Concepto Adicional');
$tc     = array('P'=>'PERMANENTE','F'=>'PLAZO FIJO','O'=>'OBRA O FAENA');
$tc1    = array('G'=>'PERMANENTE','PH'=>'PART TIME HORAS','PD'=>'PART TIME DIAS');

/**
  *  Ruta archivos excel ventas tiendas clientes 
  */
define('PATH_XLS','xlsVentas/');
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

function MwriteSql($nombre_archivo, $query){

   $archivo = $nombre_archivo;
   $handle = fopen($archivo, 'w'); 
   fwrite($handle, $query);   
   fclose($handle);
}


function envia_correo($to, $asunto, $msj, $adjunto,$adjunto2) {
  global $conexion;

  $mail = new PHPMailer();        // crea un nuevo object
  $mail->CharSet = 'UTF-8';
  $mail->IsSMTP();                // habilita SMTP
  $mail->SMTPDebug = 0;           // depuración: 1 = errores y messages, 2 = mensajes solamente
  $mail->Debugoutput = 'html';
  $mail->SMTPAuth = true;         // autenticación habilitada
  $mail->SMTPSecure = 'ssl';      // transferencia segura habilitada requerida por Gmail
  $mail->Host = 'smtp.gmail.com';
  $mail->Port = 465;
  $mail->Username = C_USER_MCH; 
  $mail->Password = C_PASS_MCH;
  
  $mail->SetFrom(FROM_MCH, FROM_NAME_MCH);  //remitente
  $mail->Subject = $asunto;          //asunto
  if(is_array($to)){
      foreach($to as $destinatario){
      $mail->AddAddress($destinatario);             //destinatario
      //echo "ENVIANDO MAIL A: ".$destinatario."<br>\n";
      }
  }else{
      $mail->AddAddress($to);
  }
  
  if($adjunto <> ''){
      $mail->addAttachment($adjunto);     //archivo adjunto
  }    
if($adjunto2 <> ''){
      $mail->addAttachment($adjunto2);     //archivo adjunto
  }
  
  $mail->Body = $msj;                //cuerpo mensaje
  $mail->AltBody = $msj;             //cuerpo mensaje no html

  if($mail->Send()){
      return true;
  }else{
      echo "Mailer Error: " . $mail->ErrorInfo ."<br>";
  }
}




/**
  *  funciones genéricas
  * 
  */
function formatDate($cadena){
    //global $conexion;
    if($cadena<>''){
        return substr($cadena,6,2).'/'.substr($cadena,4,2).'/'.substr($cadena,0,4);
    }
}
function formatDateSave($cadena){
    if($cadena<>''){
        return substr($cadena,4,4).substr($cadena,2,2).substr($cadena,0,2);
    }
}
function formatRut( $rut ) {
  return number_format( substr ( $rut, 0 , -1 ) , 0, "", ".") . '-' . substr ( $rut, strlen($rut) -1 , 1 );
}
//mail
define('C_USER',    'informatica@promer.cl');
define('C_PASS',    'promer2016');
define('FROM',      'informatica@promer.cl');
define('FROM_NAME', 'Informática Promer');
define('FIRMA',     '<br><br>Este mensaje se ha generado automaticamente, por favor NO RESPONDER.<br><br>Atte.<br>Informática Grupomonarch.');
define('MSJNUEVOUSER','
        Ud. ha sido registrado como usuario del sistema de Recepción, Trazabilidad y Control de Pedidos de Grupo Empresas Monarch.<br><br>
        Sus credenciales son las siguientes:<br>');


///////////////////////////////////////////////////////////////////////////////////////////////////
//CORREO MONARCH
define('C_USER_MCH',    'informatica@grupomonarch.cl');
define('C_PASS_MCH',    'informatica1234');
define('FROM_MCH',      'informatica@grupomonarch.cl');
define('FROM_NAME_MCH', 'Informatica Monarch');
define('FIRMA_MCH',     '<br><br>Este mensaje se ha generado automaticamente, por favor NO RESPONDER.<br><br>Atte.<br>InformÃ¡tica Grupomonarch.');
define('MSJNUEVOUSER_MCH','
        Ud. ha sido registrado como usuario del sistema de RecepciÃ³n, Trazabilidad y Control de Pedidos de Grupo Empresas Monarch.<br><br>
        Sus credenciales son las siguientes:<br>');     
        
function articulo_sb1($codartMCH){
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
function cliente_sa2($glncliente){
  global $tipobd_totvs, $conexion_totvs;

  global $a2_cod, $a2_nome, $a2_nreduz, $a2_cond, $a2_naturez,  $a2_grpven, $a2_loja,  $dconpag, $a2_end,$a2_comuna,$a2_dcomuna, $a2_ciudad, $a2_contacto;
  global $valida_cli;

  $count = "SELECT COUNT(*) AS NUMFILAS FROM sa2010, SE4010
  WHERE A2_COND=E4_CODIGO AND A2_COD='$glncliente' AND A2_LOJA = '00' AND  SA2010.D_E_L_E_T_ <> '*' AND SE4010.D_E_L_E_T_ <> '*'";//
  $rs = querys($count, $tipobd_totvs, $conexion_totvs);
  $filac = ver_result($rs, $tipobd_totvs);
    if($filac['NUMFILAS'] == 1){
      $query = "  SELECT A2_COD, A2_NOME, A2_NREDUZ, A2_COND, A2_NATUREZ, A2_GRPVEN, A2_LOJA, E4_DESCRI,A2_END, A2_BAIRRO,A2_DESBAI, A2_MUN, A2_CONTATO
      FROM  SA2010, SE4010
      WHERE A2_COND=E4_CODIGO AND A2_COD='$glncliente' AND A2_LOJA = '00' AND  SA2010.D_E_L_E_T_ <> '*' AND SE4010.D_E_L_E_T_ <> '*'";//

      //echo $query.'<br>';
      $rs = querys($query, $tipobd_totvs, $conexion_totvs);
      //OBTENER RESULTADO
      $fila=ver_result($rs, $tipobd_totvs);
      $a2_cod 	  = $fila['A2_COD'];
      $a2_nome 	  = $fila['A2_NOME'];
      $a2_nreduz 	= $fila['A2_NREDUZ'];
      $a2_cond 	  = $fila['A2_COND'];
      $a2_end 	  = $fila['A2_END'];
      $a2_comuna 	  = $fila['A2_BAIRRO'];
      $a2_dcomuna 	  = $fila['A2_DESBAI'];
      $a2_ciudad	  = $fila['A2_MUN'];
      $a2_contacto	  = $fila['A2_CONTATO'];
      $a2_grpven 	= $fila['A2_GRPVEN'];
      $a2_loja	  = $fila['A2_LOJA'];
      $dconpag	  = $fila['E4_DESCRI'];
      $valida_cli = "*";
    }else{
      $valida_cli = "N";
    }
}
function correlativo_pm($var, $largo){
//echo $var.'__'.$largo.'<br>';
      $limite[0] = '1'.rellena_pm('',$largo,'0','D');
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
      $decim = rellena_pm($decimal,($largo-$can_let),'0','D');
      $retorna = $retorna.chr(64+((int) ($post_letra[$x])));

    }

    $retorna = substr($retorna.$decim,0,$largo);

      }

      return($retorna);
  
}
function rellena_pm($variable,$largo,$caracter,$direccion){

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
?>