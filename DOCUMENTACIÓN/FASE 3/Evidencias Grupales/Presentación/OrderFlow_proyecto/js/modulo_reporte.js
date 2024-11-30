$(document).ready(onLoad);
function onLoad() {
    $("#buscar").click(ver_pedidos);
}


function limpiarTabla() {
    $("#tbl_reporte tbody tr").remove();
    //$("#frm_update").reset();
}

function ver_pedidos() {
    limpiarTabla();
    
    var canal = $("#canal").val();
    var cod_monarch = $("#cod_monarch").val();
    var fec_ini = $("#fec_ini").val();
    var fec_fin = $("#fec_fin").val();
    //var planilla = $(this).attr('planilla');
    //alert(canal_venta);

    if(canal == '' || fec_ini=='' || fec_fin==''){
        Swal.fire('ERROR', 'Debe seleccionar Campos Obligatorios', 'error');
    }else{

        $.ajax({
            url:'modulo_reporte.php',
            type: 'GET',
            dataType: 'json',
            //contentType: false,
            data:{'ver_pedido':'ver_pedido','canal':canal,'cod_monarch':cod_monarch, 'fec_ini':fec_ini, 'fec_fin':fec_fin},
            //processData: false,
            //cache: false
            beforeSend:function(){
                //$("#mensaje1").html('<img src="img/cargando2.gif">Cargando Pedidos, Por Favor Espere...</img>');
                Swal.fire({
                          title: 'Obteniendo Datos !',
                          html: 'Por favor espere...',
                          timerProgressBar: true,
                          didOpen: () => {
                            Swal.showLoading()
                            const b = Swal.getHtmlContainer().querySelector('b')
                            timerInterval = setInterval(() => {
                            //   b.textContent = Swal.getTimerLeft()
                            }, 100)
                          },
                        });
            },
            success:function(jsonphp){
                Swal.close() ;
                $("#fechas").empty().text('Desde '+ fec_ini +' Hasta ' +fec_fin );
                var i=0;
                $.each(jsonphp, function(indice, valores){
                    i+=1;
                    var tr = $("<tr />");
            //         if (valores.DIFERENCIA > '0') {
            //             tr.attr('style','background-color:  #e86d51');
            //  }
                    $("<td />").html(i).appendTo(tr); 
                    $('<td />').html(valores.CANAL).appendTo(tr);
                    $('<td />').html(valores.OC).appendTo(tr);
                    $('<td />').html(valores.LOCAL).appendTo(tr);
                    // $('<td />').html(valores.DLOCAL).appendTo(tr);;
                    $('<td />').html(valores.INTCOD).appendTo(tr);
                    $('<td />').html(valores.INTDES).appendTo(tr);
                    $('<td />').html(valores.CANTIDAD).appendTo(tr);
                    $('<td />').html(valores.PRVEN).appendTo(tr);
                    $('<td />').html(valores.VALOR).appendTo(tr);
                   
                    
                    tr.appendTo("#tbl_reporte");
                    //alert(valores.LOCAL);
                    
                });
                // $("#tbl_ola").DataTable();
                 //$("html, body").animate({ scrollTop: 680 }, "fast");
            },
            error:function(textphp){
                limpiarTabla();    
              Swal.fire('No hay datos por el momento !', textphp, 'info');
               //$("#mensajes").html("<div class='alert alert-danger' role='alert'>"+textphp+"</div>");
                return false;        
            }
        });
    }

}
if (!download_xls) {
    let download_xls = document.querySelector("#download_xls");   
}

if (!download_csv) {
    let download_csv = document.querySelector("#download_csv");    
}

if (!download_xlsx) {
    let download_xlsx = document.querySelector("#download_xlsx");    
}


download_xls.addEventListener("click", ()=>{   
    ExcellentExport.convert({ anchor: download_xls, filename: 'Reporte Distribucion Cargada  ' , format: 'xls'},[{name: 'Reporte', from: {table: 'tbl_reporte'}}])   ;
});
download_csv.addEventListener("click", ()=>{   
     ExcellentExport.convert({ anchor: download_csv, filename: 'Reporte Distribucion Cargada ', format: 'csv'},[{name: 'Reporte', from: {table: 'tbl_reporte'}}])   ;
});
download_xlsx.addEventListener("click", ()=>{  
    ExcellentExport.convert({ anchor: download_xlsx, filename:'Reporte Distribucion Cargada ', format: 'xlsx'},[{name: 'Reporte', from: {table: 'tbl_reporte'}}])  ;
});
