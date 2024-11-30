<?php
// require_once "../conexion.php";
// require_once "../config.php"; 

// require_once "conexion.php";
// require_once "config.php"; 
// require_once "itemUom.php"; 

function WS_OutBoundOrder($wms_num){
    global $tipobd_totvsDev2, $conexion_totvsDev2;

 
    $wsdlUrl = 'http://192.168.100.188/WMSTekWS/wsImportIfz.asmx?WSDL';

        $querysel_2 = "SELECT Z.ZC6_WMSNUM, A.A1_TYPECOD, Z.ZC6_OC, Z.ZC6_LOCAL, Z.ZC6_FEMIS, Z.ZC6_ENTREG, Z.ZC6_CLIENT,
                    Z.ZC6_CANAL, A.A1_NOME, A.A1_END, A.A1_MUN, A.A1_BAIRRO, S1.X5_DESCRI AS STATENAMEFACT, S2.X5_DESCRI AS CITYNAMEFACT, 
                    A.A1_NREDUZ, Z.ZC6_LOCAL AS A1_BRANCH, A.A1_LOJA,C.R_E_C_N_O_ AS RECNO_C5
            FROM ZC6010 Z
            LEFT JOIN SA1010 A ON TRIM(A.A1_COD) = TRIM(Z.ZC6_CLIENT) 
                                AND TRIM(A.A1_LOJA) = TRIM(Z.ZC6_LOJA)
                                AND A.A1_FILIAL = ' ' 
                                AND A.D_E_L_E_T_ <> '*'
            LEFT JOIN SX5010 S1 ON S1.X5_TABELA = 'ZS' AND S1.X5_CHAVE = A.A1_MUN
            LEFT JOIN SX5010 S2 ON S2.X5_TABELA = 'ZC' AND S2.X5_CHAVE = A.A1_BAIRRO
            LEFT JOIN SC5010 C ON C.C5_FILIAL = '01' 
                                AND C.D_E_L_E_T_ <> '*' 
                                AND TRIM(C.C5_CLIENTE) = TRIM(A.A1_COD) 
                                AND TRIM(C.C5_LOJACLI) = TRIM(A.A1_LOJA)
                                AND TRIM(C5_NUM)=TRIM(ZC6_NUM)
            WHERE Z.ZC6_WMSNUM = '$wms_num'
            AND Z.D_E_L_E_T_<>'*'
            GROUP BY Z.ZC6_WMSNUM, A.A1_TYPECOD,Z.ZC6_OC,Z.ZC6_LOCAL,  Z.ZC6_FEMIS,Z.ZC6_ENTREG, Z.ZC6_CLIENT, Z.ZC6_CANAL,
                    A.A1_NOME, A.A1_MUN, A.A1_BAIRRO, A.A1_END, A.A1_BRANCH, A.A1_LOJA, A.A1_NREDUZ, S1.X5_DESCRI,
                    S2.X5_DESCRI, C.R_E_C_N_O_";
        $rs2 = querys($querysel_2, $tipobd_totvsDev2, $conexion_totvsDev2);
        $v2 = ver_result($rs2, $tipobd_totvsDev2);
            $wms_num            = trim($v2["ZC6_WMSNUM"]);
            $type_cod           = trim($v2["A1_TYPECOD"]);
            $wms_oc             = trim($v2["ZC6_OC"]);
            $fecha_emision      = trim($v2["ZC6_FEMIS"]);
            $fecha_entrega      = trim($v2["ZC6_ENTREG"]);
            $rut_cliente        = trim($v2["ZC6_CLIENT"]);
            $canal              = trim($v2["ZC6_CANAL"]);
            $nombre_cliente     = trim($v2["A1_NOME"]);
            $direccion          = trim($v2["A1_END"]);
            $state              = trim($v2["STATENAMEFACT"]);
            $ciudad             = trim($v2["CITYNAMEFACT"]);
            $sucursal           = trim($v2["A1_NREDUZ"]);
            $branch             = trim($v2["ZC6_LOCAL"]);
            $recno_c5           = trim($v2["RECNO_C5"]);

            $customer_code = $rut_cliente.'-'.$canal;

            $ffecha_emision = substr($fecha_emision, 0, 4) . "-" . substr($fecha_emision, 4, 2) . "-" . substr($fecha_emision, 6, 2) . "T10:00:00";

            $fecha_entrega = date('Ymd', strtotime($fecha_emision . ' +7 days'));
            $ffecha_entrega = substr($fecha_entrega, 0, 4) . "-" . substr($fecha_entrega, 4, 2) . "-" . substr($fecha_entrega, 6, 2) . "T10:00:00";
            // die();
            try {
                // Crear una nueva instancia de SoapClient con la URL del WSDL
                $client = new SoapClient($wsdlUrl, array('trace' => 1));

                // Crear un objeto OutboundOrderIfz según la estructura definida en el XML
                $outboundOrderIfz = new stdClass();
                $outboundOrderIfz->Comment =  null;
                $outboundOrderIfz->Marketplace =  null;
                $outboundOrderIfz->WhsCode = "D01";
                $outboundOrderIfz->OwnCode = "90991000-5";
                $outboundOrderIfz->Number = $wms_num;
                $outboundOrderIfz->OutboundTypeCode = $type_cod;
                $outboundOrderIfz->Status = true; // Cambiar a false si es necesario
                $outboundOrderIfz->ReferenceNumber = $wms_oc;
                $outboundOrderIfz->LoadCode =  null;
                $outboundOrderIfz->LoadSeq =  null;
                $outboundOrderIfz->Priority = 0; // Cambiar al valor adecuado
                $outboundOrderIfz->InmediateProcess = false; // Cambiar a false si es necesario
                $outboundOrderIfz->EmissionDate = $ffecha_emision; // Cambiar a la fecha adecuada
                $outboundOrderIfz->ExpectedDate = $ffecha_entrega; // Cambiar a la fecha adecuada
                $outboundOrderIfz->ShipmentDate = $ffecha_entrega; // Cambiar a la fecha adecuada
                $outboundOrderIfz->ExpirationDate = $ffecha_entrega; // Cambiar a la fecha adecuada
                $outboundOrderIfz->CancelDate = $ffecha_entrega; // Cambiar a la fecha adecuada
                $outboundOrderIfz->CancelUser = "";
                $outboundOrderIfz->CustomerCode = $customer_code;
                $outboundOrderIfz->CustomerName = $nombre_cliente;
                $outboundOrderIfz->DeliveryAddress1 = $direccion;
                $outboundOrderIfz->DeliveryAddress2 = $direccion;
                $outboundOrderIfz->CountryNameDelivery = "CHILE";
                $outboundOrderIfz->StateNameDelivery = $state;
                $outboundOrderIfz->CityNameDelivery = $ciudad;
                $outboundOrderIfz->DeliveryPhone =  null;
                $outboundOrderIfz->DeliveryEmail =  null;
                $outboundOrderIfz->WhsCodeTarget =  null;
                $outboundOrderIfz->FullShipment = 0; // Cambiar a false si es necesario
                $outboundOrderIfz->CarrierCode =  null;
                $outboundOrderIfz->RouteCode =  null;
                $outboundOrderIfz->Plate =  null;
                $outboundOrderIfz->Invoice =  null;
                $outboundOrderIfz->FactAddress1 = $direccion;
                $outboundOrderIfz->FactAddress2 = $direccion;
                $outboundOrderIfz->CountryNameFact = "CHILE";
                $outboundOrderIfz->StateNameFact = $state;
                $outboundOrderIfz->CityNameFact = $ciudad;
                $outboundOrderIfz->FactPhone =  null;
                $outboundOrderIfz->FactEmail =  null;
                $outboundOrderIfz->AllowCrossDock = 0; // Cambiar a false si es necesario
                $outboundOrderIfz->AllowBackOrder = 0; // Cambiar a false si es necesario
                $outboundOrderIfz->BranchCode = $branch;
                $outboundOrderIfz->SpecialField1 = $sucursal;
                $outboundOrderIfz->SpecialField2 = $recno_c5;
                $outboundOrderIfz->SpecialField3 = $recno_c5;
                $outboundOrderIfz->SpecialField4 = $recno_c5;
                $outboundOrderIfz->StateInterface = "C";
                $outboundOrderIfz->DateCreatedERP = "2023-12-01T12:00:00"; // Cambiar a la fecha adecuada
                $outboundOrderIfz->DateReadWMS = "2023-12-01T12:00:00"; // Cambiar a la fecha adecuada
                
                // Crear un array para almacenar los detalles
                $arrayOfOutboundDetailIfz = new stdClass();
                $arrayOfOutboundDetailIfz->OutboundDetailIfz = array();
                // var_dump($arrayOfOutboundDetailIfz);
                // $lastRequest = $client->__getLastRequest();
                // echo "Solicitud XML: \n" . $lastRequest . "\n\n";
            
                $query_detail = "SELECT TO_NUMBER(ZC6_ZITEM) AS ZC6_ZITEM, ZC6_ITEM,ZC6_INTCOD,ZC6_CANT ,Z.ZC6_WMSNUM,
                        A.A1_TYPECOD, Z.ZC6_OC, Z.ZC6_FEMIS, Z.ZC6_ENTREG, Z.ZC6_CLIENT,
                        Z.ZC6_CANAL, A.A1_NOME, A.A1_END,
                        A.A1_NREDUZ, Z.ZC6_SC6REC AS RECNO_C6
                FROM ZC6010 Z
                LEFT JOIN SA1010 A ON TRIM(A.A1_COD) = TRIM(Z.ZC6_CLIENT) 
                                    AND TRIM(A.A1_LOJA) = TRIM(Z.ZC6_LOJA)
                                    AND A.A1_FILIAL = ' ' 
                                    AND A.D_E_L_E_T_ <> '*'
                LEFT JOIN SC6010 C ON C.C6_FILIAL = '01' 
                                    AND C.D_E_L_E_T_ <> '*' 
                                    AND TRIM(C6_PRODUTO)=TRIM(ZC6_INTCOD)
                                    AND TRIM(C.C6_CLI) = TRIM(A.A1_COD) 
                                    AND TRIM(C.C6_LOJA) = TRIM(A.A1_LOJA)
                                    AND TRIM(C6_NUM)=TRIM(ZC6_NUM)
                WHERE Z.ZC6_WMSNUM = '$wms_num'
                AND Z.D_E_L_E_T_<>'*'
                ORDER BY TO_NUMBER(ZC6_ZITEM)";
                $rsd = querys($query_detail, $tipobd_totvsDev2, $conexion_totvsDev2);
            
                // Recorrer los detalles y agregarlos al array
                while ($v = ver_result($rsd, $tipobd_totvsDev2)) {
                    $line_number    = $v["ZC6_ZITEM"];
                    $item           = $v["ZC6_ITEM"];
                    $cod_monarch    = $v["ZC6_INTCOD"];
                    $cantidad    = $v["ZC6_CANT"];
                    $wms_num        = trim($v["ZC6_WMSNUM"]);
                    $sucursal       = $v["A1_NREDUZ"];
                    $recno_c6         = $v["RECNO_C6"];
                    
                    // Crear un objeto InboundDetailIfz según la estructura definida en el XML
                     // Crear un objeto OutboundDetailIfz según la estructura definida en el XML
                    $outboundDetailIfz = new stdClass();
                    $outboundDetailIfz->LineNumber = $line_number;
                    $outboundDetailIfz->LineCode = $item;
                    $outboundDetailIfz->ItemCode = $cod_monarch;
                    $outboundDetailIfz->CtgCode =  null;
                    $outboundDetailIfz->ItemQty = $cantidad; // Cambiar al valor adecuado
                    $outboundDetailIfz->Status = true; // Cambiar a false si es necesario
                    $outboundDetailIfz->LotNumber =  null;
                    $outboundDetailIfz->FifoDate = '0001-01-01T00:00:00'; // Cambiar a la fecha adecuada
                    $outboundDetailIfz->ExpirationDate = '0001-01-01T00:00:00'; // Cambiar a la fecha adecuada
                    $outboundDetailIfz->FabricationDate = '0001-01-01T00:00:00'; // Cambiar a la fecha adecuada
                    $outboundDetailIfz->GrpClass1 =  null;
                    $outboundDetailIfz->GrpClass2 =  null;
                    $outboundDetailIfz->GrpClass3 =  null;
                    $outboundDetailIfz->GrpClass4 =  null;
                    $outboundDetailIfz->GrpClass5 =  null;
                    $outboundDetailIfz->GrpClass6 =  null;
                    $outboundDetailIfz->GrpClass7 =  null;
                    $outboundDetailIfz->GrpClass8 =  null;
                    $outboundDetailIfz->SpecialField1 = $sucursal;
                    $outboundDetailIfz->SpecialField2 = $recno_c6;
                    $outboundDetailIfz->SpecialField3 = $sucursal;
                    $outboundDetailIfz->SpecialField4 = $recno_c6;
                    $outboundDetailIfz->StateInterface = "C";
                    $outboundDetailIfz->DateCreatedERP = "2023-12-01T12:00:00"; // Cambiar a la fecha adecuada
                    $outboundDetailIfz->DateReadWMS = "2023-12-01T12:00:00"; // Cambiar a la fecha adecuada

                    // Agregar el objeto OutboundDetailIfz al array de detalles
                    $arrayOfOutboundDetailIfz->OutboundDetailIfz[] = $outboundDetailIfz;
                    
                }
            
                // Asignar el array de detalles al objeto OutboundOrderIfz
                $outboundOrderIfz->OutboundDetailsIfz = $arrayOfOutboundDetailIfz;

                // Crear un array de OutboundOrderIfz y agregar el objeto OutboundOrderIfz
                $listOutboundOrderIfz = array($outboundOrderIfz);

                // Crear un objeto ArrayOfOutboundOrderIfz y asignar el array de OutboundOrderIfz
                $arrayOfOutboundOrderIfz = new stdClass();
                $arrayOfOutboundOrderIfz->OutboundOrderIfz = $listOutboundOrderIfz;

                // Crear un objeto OutboundOrderIfzFuntional y asignar el número de ticket y el objeto ArrayOfOutboundOrderIfz
                $outboundOrderIfzFunctional = new stdClass();
                $outboundOrderIfzFunctional->NroTicket = 1;
                $outboundOrderIfzFunctional->ListOutboundOrderIfz = $arrayOfOutboundOrderIfz;
                // echo "<pre>";
                // print_r($arrayOfOutboundOrderIfz);       
                // echo "</pre>";
                
                // Llamar al método ImportOutboundOrder del servicio web con los datos
                $response = $client->ImportOutboundOrder(array("outboundOrderIfzFun" => $outboundOrderIfzFunctional));
                // Obtener la respuesta XML
                // $lastResponse = $client->__getLastResponse();
                // echo "Respuesta XML: \n" . $lastResponse . "\n";
                
                // Obtener la respuesta del servicio web
                $importOutboundOrderResult = $response->ImportOutboundOrderResult;      
                // Utilizar $importInboundOrderResult según sea necesario
                echo "Respuesta del servicio web || Pedido: $wms_num || " . $importOutboundOrderResult."<br>";

                $ticket = substr($importOutboundOrderResult,11,9);
                $OK = substr($importOutboundOrderResult,0,2);
                $hoy = date('Ymd');
                $recno_x01 = recno_x01();
                // confirm_ticket($ticket);
                if($OK == 'OK'){
                    //UPDATE NUMERO DE TICKET ZC6
                    $queryup_2="update ".TBL_ZC6010." set  ZC6_TICKET='$ticket' where ZC6_WMSNUM='$wms_num'";
                    $r2=querys($queryup_2, $tipobd_totvsDev2, $conexion_totvsDev2);
                    //UPDATE NUMERO DE TICKET SC5
                    $pedido_c5 = substr(trim($wms_num),0,6);
                    $queryup_c5="update ".TBL_SC5." set  C5_WMSTICK='$ticket' where C5_FILIAL='01' AND C5_NUM='$pedido_c5'";
                    $rc5=querys($queryup_c5, $tipobd_totvsDev2, $conexion_totvsDev2);

                    $queryx01 = "INSERT INTO TOTVS.TICKET(TICKET, ESTADO) 
                                                        VALUES($ticket,' ')";
                    $rsx01 = querys($queryx01, $tipobd_totvsDev2, $conexion_totvsDev2);
                }else{echo "ERROR EN WS \n";}
            
            } catch (SoapFault $e) {
                // Manejar errores de la llamada al servicio web
                echo "Error: " . $e->getMessage();
            }
    
        
}
function recno_x01(){
	global $tipobd_totvsDev2, $conexion_totvsDev2;
	//global $conexion3;
	
	$select = "SELECT NVL(MAX(R_E_C_N_O_),0)+1 AS CORRELATIVO FROM X01010";
	$rs = querys($select,$tipobd_totvsDev2, $conexion_totvsDev2);
	$fila = ver_result($rs, $tipobd_totvsDev2);
	$recno = $fila['CORRELATIVO'];
	return $recno;
	
}

    // WS_OutBoundOrder();
    
    ?>