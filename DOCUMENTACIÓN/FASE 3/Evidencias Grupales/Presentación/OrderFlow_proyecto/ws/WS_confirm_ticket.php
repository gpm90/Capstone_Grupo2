<?php 
// require_once "../conexion.php";
// require_once "../config.php"; 

function confirm_ticket($oc){
    global $tipobd_totvsDev2, $conexion_totvsDev2;
    
    $querysel = "SELECT Z.ZC6_TICKET, T.TICKET, T.ESTADO
    FROM  ZC6010 Z
               LEFT JOIN  TICKET T ON Z.ZC6_TICKET = T.TICKET
                                    AND T.ESTADO = ' '
    WHERE Z.ZC6_TICKET > 0
    AND Z.ZC6_OC='$oc'
    GROUP BY  Z.ZC6_TICKET, T.TICKET, T.ESTADO";
    $rss = querys($querysel, $tipobd_totvsDev2, $conexion_totvsDev2);
    while($v = ver_result($rss, $tipobd_totvsDev2)){

        $ticket = $v["ZC6_TICKET"];

        $wsdlUrl = 'http://192.168.100.188/WMSTekWS/wsImportIfz.asmx?WSDL';//produccion
    
        try {
    
            // Crear una nueva instancia de SoapClient con la URL del WSDL
            $client = new SoapClient($wsdlUrl, array('trace' => 1));
    
            // Número de ticket que deseas confirmar (reemplaza 123 con tu número de ticket)
            $nroTicket = $ticket;
    
            // Llamar al método ConfirmNroTicketImport del servicio web con el número de ticket
            $response = $client->ConfirmNroTicketImport(array("nroTicket" => $nroTicket));
    
            // Obtener la respuesta del servicio web
            $confirmNroTicketImportResult = $response->ConfirmNroTicketImportResult;
    
            // Utilizar $confirmNroTicketImportResult según sea necesario
            // echo "<br> Respuesta del servicio web: " . $confirmNroTicketImportResult."\n";
    
            $queryx01 = "UPDATE TOTVS.TICKET SET ESTADO='$confirmNroTicketImportResult' WHERE TICKET=$ticket ";
            $rsx01 = querys($queryx01, $tipobd_totvsDev2, $conexion_totvsDev2);
    
        } catch (SoapFault $e) {
            // Manejar errores de la llamada al servicio web
            echo "Error: " . $e->getMessage();
        }
    }


}   

// confirm_ticket();
?>