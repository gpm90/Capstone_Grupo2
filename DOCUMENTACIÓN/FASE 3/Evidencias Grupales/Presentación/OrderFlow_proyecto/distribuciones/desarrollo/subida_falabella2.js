$(document).ready(onLoad);    
function onLoad() {

    datos_subidos();
    //dateModal();
    //$("#enviar").click(cargarInforme);
    $("#btn_guardar").click(confimacion_subida);
    //$("#ver_docwms").click(ver_documentos);
    $("#tbl_planillas_falabella").on('click','#btn_borrar',borrarPlanilla);
    $("#tbl_planillas_falabella").on('click','#btn_digitar',digita_totvs);
    $("#tbl_planillas_falabella").on('click','#btn_wms',envio_wms);
    $("#tbl_planillas_falabella").on('click','#btn_docwms',ver_documentos);
    $("#tbl_planillas_falabella").on('click','#btn_reprocesar',reprocesar_planilla); 
     $("#tbl_planillas_falabella").on('click','#btn_ver_error',ver_error);


}
function ver_documentos(){
    oc = $(this).attr('oc');

      $.ajax({
        url:'distribuciones/desarrollo/subida_falabella2.php',
         type: 'GET',
        dataType: 'json',
        data:{'ver_docu_wms':'ver_docu_wms','oc':oc},
        beforeSend:function(){
            //$("#mensaje1").html('<img src="img/cargando2.gif">Cargando Pedidos, Por Favor Espere...</img>');
            Swal.fire({
                      title: 'Consultando Ticket en WMS !',
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

            var tableContent = ''; // Initialize an empty string to store the table content

            $.each(jsonphp, function (indice, valores) {
                var pedido = valores.ZC6_WMSNUM;
                var ticket = valores.ZC6_TICKET;
                var estado = valores.ESTADO;

                // Append each row to the tableContent
                tableContent +=
                '<tr>' +
                '<td>' + pedido + '</td>' +
                '<td>' + ticket + '</td>' +
                '<td>' + estado + '</td>' +
                '</tr>';
            });

            var sweetAlertContent =
            '<table border="1" cellpadding="5" style="width: 500px;">' +
            '<tr>' +
            '<th>Pedido</th>' +
            '<th>Ticket</th>' +
            '<th>Estado</th>' +
            '</tr>' +
            tableContent + // Add the complete table content here
            '</table>';

            Swal.fire({
                title: '<strong>SubPedidos enviados a WMS</strong>',
                 width: 600,
                 icon: 'info',                 
                 html: sweetAlertContent,
                 showCloseButton: true,
                 showCancelButton: true,
                 focusConfirm: false,
                 confirmButtonText:
                   '<i class="fa fa-thumbs-up"></i> OK!',
                 confirmButtonAriaLabel: 'Thumbs up, great!',
                 
                 cancelButtonAriaLabel: 'Thumbs down'
            });  

        },
    });
}
function ver_error(){
    
    archivo = $(this).attr('archivo');
      $.ajax({
        url:'distribuciones/desarrollo/subida_falabella2.php',
         type: 'GET',
        dataType: 'json',
        data:{'ver_errores':'ver_errores','archivo':archivo},
        success:function(jsonphp){

            var tableContent = ''; // Initialize an empty string to store the table content

            $.each(jsonphp, function (indice, valores) {
                var sku = valores.SKU;
                var descr = valores.CLI_DES;

                // Append each row to the tableContent
                tableContent +=
                '<tr>' +
                '<td>' + sku + '</td>' +
                '<td>' + descr + '</td>' +
                '</tr>';
            });

            var sweetAlertContent =
            '<table border="1" cellpadding="10" style="width: 500px;">' +
            '<tr>' +
            '<th>Sku</th>' +
            '<th>Descripcion</th>' +
            '</tr>' +
            tableContent + // Add the complete table content here
            '</table>';

            Swal.fire({
                title: '<strong>Producto(s) Sin Equivalencia</strong>',
                 width: 600,
                 icon: 'warning',                 
                 html: sweetAlertContent,
                 showCloseButton: true,
                 showCancelButton: true,
                 focusConfirm: false,
                 confirmButtonText:
                   '<i class="fa fa-thumbs-up"></i> OK!',
                 confirmButtonAriaLabel: 'Thumbs up, great!',
                 
                 cancelButtonAriaLabel: 'Thumbs down'
            });  

        },
    });
}
function digita_totvs() { 
    //alert($(this).attr('cod'));
    
 
    oc = $(this).attr('oc');
       Swal.fire({
           icon: 'question',
            title: "Desea Digitar OC "+oc+" en ERP ?",
             text: "Estos datos seran enviandos de manera automatica al ERP",
            width: '500px',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Confirmar !',
            showLoaderOnConfirm: true
    }).then(resultado => {
        if (resultado.value) {
      $.ajax({
            url:'distribuciones/desarrollo/subida_falabella2.php',
            type: 'GET',
            dataType: 'text',
            //contentType: false,
            data:{'confirma_digitacion':'confirma_digitacion','oc':oc},
             
            //processData: false,
            //cache: false,
            //beforeSend:cargando,
             beforeSend:function(){
            $("#mensajes").html('<img src="img/cargando2.gif">Cargando...</img>');
						Swal.fire({
						title: 'Digitando Pedido en ERP !',
						  html: 'Por favor espere...',
						  timerProgressBar: true,
						  // showLoaderOnConfirm: true,
						  didOpen: () => {
							Swal.showLoading();
							//const b = Swal.getHtmlContainer().querySelector('b')
							//timerInterval = setInterval(() => {
							//  b.textContent = Swal.getTimerLeft()
							//}, 100)
						  },
						});
            },
            success:function(data){
                $("#mensajes").html("<div class='alert alert-success' role='alert'>"+data+"</div>");
				datos_subidos();
				Swal.fire('Procesado !', data, 'success');
			
            },
                //error:problemas
        });
        } else {
            // Dijeron que no
           Swal.fire('No Procesado ! ', 'Cancelado', 'info');
        }
    });
	// datos_subidos();
   
    $("html, body").animate({ scrollTop: 0 }, "fast");
            //$(data).val(''); //limpia registros que quedan en el js
}
function envio_wms() { 
    //alert($(this).attr('cod'));
    oc = $(this).attr('oc');
    digitacion = $(this).attr('digitacion');

    if(digitacion=='N' || digitacion=='E' || digitacion=='I'){
        Swal.fire('Pedido sin Digitación ', 'Pedido debe estar digitado para ser enviado a WMS', 'error');
    }else{
 
        oc = $(this).attr('oc');
        Swal.fire({
            icon: 'question',
                title: "Desea Enviar OC "+oc+" en WMS ?",
                text: "Estos datos seran enviandos de manera automatica a WMS",
                width: '500px',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si, Confirmar !',
                showLoaderOnConfirm: true
        }).then(resultado => {
            if (resultado.value) {
        $.ajax({
                url:'distribuciones/desarrollo/subida_falabella2.php',
                type: 'GET',
                dataType: 'text',
                //contentType: false,
                data:{'transferir_wms':'transferir_wms','oc':oc},
                
                //processData: false,
                //cache: false,
                //beforeSend:cargando,
                beforeSend:function(){
                $("#mensajes").html('<img src="img/cargando2.gif">Cargando...</img>');
                            Swal.fire({
                            title: 'Transfiriendo OC a WMS !',
                            html: 'Por favor espere...',
                            timerProgressBar: true,
                            // showLoaderOnConfirm: true,
                            didOpen: () => {
                                Swal.showLoading();
                                //const b = Swal.getHtmlContainer().querySelector('b')
                                //timerInterval = setInterval(() => {
                                //  b.textContent = Swal.getTimerLeft()
                                //}, 100)
                            },
                            });
                },
                success:function(data){
                    var error = data.substr(0,5);
                    $("#mensajes").html("<div class='alert alert-success' role='alert'>"+data+"</div>");
                    if(error === 'ERROR'){
                        Swal.fire('ERROR ! !', data, 'error');
                    }else{
                        datos_subidos();
                        Swal.fire('Procesado !', data, 'success');
                    }
                
                },
                    //error:problemas
            });
            } else {
                // Dijeron que no
            Swal.fire('No Procesado ! ', 'Cancelado', 'info');
            }
        });
	// datos_subidos();
    }
    $("html, body").animate({ scrollTop: 0 }, "fast");
            //$(data).val(''); //limpia registros que quedan en el js
}
function borrarPlanilla(){

    archivo = $(this).attr('archivo');
    indice = $(this).attr('indice');
	
	 Swal.fire({
           icon: 'question',
            title: "Desea eliminar archivo "+archivo+"  ?",
             text: "Archivo se eliminara por completo.",
            width: '500px',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Confirmar !',
            showLoaderOnConfirm: true
    }).then(resultado => {
        if (resultado.value) {
				// alert(indice);
				$("#"+indice).remove();

				$.ajax({

					url:'distribuciones/desarrollo/subida_falabella2.php',
					type: 'GET',
					dataType: 'text',
					//contentType: false,
					data:{'borrarPlanilla':'borrarPlanilla','archivo':archivo},
					 
					success:function(textphp){
						$("#mensajes").html("<div class='alert alert-success alert-dismissible fade show' role='alert'>"+textphp+"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>");
						Swal.fire('Procesado !', textphp, 'success');
					},
				
				});
            } else {
            // Dijeron que no
           Swal.fire('No Procesado ! ', 'Cancelado', 'info');
        }
    });

}
function reprocesar_planilla(){

    archivo = $(this).attr('archivo');
	
	 Swal.fire({
           icon: 'question',
            title: "Desea reprocesar archivo "+archivo+"  ?",
             text: "La Digitacion se eliminara y prodrá volver a Digitar",
            width: '500px',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Confirmar !',
            showLoaderOnConfirm: true
    }).then(resultado => {
        if (resultado.value) {
				// alert(indice);
				// $("#"+indice).remove();

				$.ajax({

					url:'distribuciones/desarrollo/subida_falabella2.php',
					type: 'GET',
					dataType: 'text',
					//contentType: false,
					data:{'reprocesar':'reprocesar','archivo':archivo},
					 
					success:function(textphp){
						$("#mensajes").html("<div class='alert alert-success alert-dismissible fade show' role='alert'>"+textphp+"<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>");
						datos_subidos();
						Swal.fire('Procesado !', textphp, 'success');
					},
				
				});
            } else {
            // Dijeron que no
           Swal.fire('No Procesado ! ', 'Cancelado', 'info');
        }
    });
	
}



function confimacion_subida(){

  
        Swal.fire({

            title: 'Desea Cargar Datos?',
            text: "Se Iniciara el proceso de carga",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Subir Datos'

            }).then((result) => {

            if (result.isConfirmed) {
                validarArchivo();
            }

            });
    

}
function limpiarTabla() {
    $("#tbl_planillas_falabella tbody tr").remove();
    //$("#frm_update").reset();
}
function datos_subidos() {
limpiarTabla();
    $.ajax({
        
        url:'distribuciones/desarrollo/subida_falabella2.php',
        type: 'GET',
        dataType: 'json',
        //contentType: false,
        data:{'ver':'ver'},
        //processData: false,
        //cache: false
        // beforeSend:function(){
        //     // $("#mensajes").html('<img src="img/cargando2.gif">Cargando Pedidos, Por Favor Espere...</img>');
        //     Swal.fire({
        //         title: 'Cargando Pedidos',
        //         html: 'Por favor espere...',
        //         timerProgressBar: true,
        //         timer: 1500,
        //         didOpen: () => {
        //             Swal.showLoading()
        //             const b = Swal.getHtmlContainer().querySelector('b')
        //             timerInterval = setInterval(() => {
        //             //b.textContent = Swal.getTimerLeft()
        //                 }, 100)
        //               },
        //             });
        // },
        success:function(jsonphp){
            // swal.close()
            var i=0;

            $.each(jsonphp, function(indice, valores){

                    i=i+1;
                    var tr = $("<tr/>").attr({'id':i});
                     if (valores.OK_DIGITACION == 'E'){
                        tr.attr('style','background-color:  #EF9B9B; border:1px solid;');
                     }
                    // TODO:
                   var btn_digitar = $("<button/>").html("<i class='far fa-check-circle'></i>");//Digitar en Totvs
                    btn_digitar.attr({'title':'Digita Planilla a TOTVS','type':'button','class':'btn btn-block bg-gradient-success','id':'btn_digitar','oc':valores.NRO_ORDEN});
                    
                    var btn_documentos = $("<button/>").html("<p class='text-center' style='margin-bottom: 0px;'><img src='img/icon/check.png'></img>");//Digitar en Totvs
                    btn_documentos.attr({'title':'Transferido a WMS','type':'button','class':'btn btn-link','id':'btn_docwms','oc':valores.NRO_ORDEN});

                    var btn_wms = $("<button/>").html("<i class='far fa-check-circle'></i>");//Digitar en Totvs
                    btn_wms.attr({'title':'Enviar WMS','type':'button','class':'btn btn-block bg-gradient-warning','id':'btn_wms','oc':valores.NRO_ORDEN,'digitacion':valores.OK_DIGITACION});
					
					var btn_reprocesar = $("<button/>").html("<i class='fa fa-undo' aria-hidden='true'></i>");//'Revertir Digitación'
                    btn_reprocesar.attr({'title':'Borrar Digitacion','type':'button','class':'btn btn-block bg-gradient-primary','id':'btn_reprocesar','oc':valores.NRO_ORDEN,'archivo':valores.NOM_ARCHIVO});
					
					var btn_borrar = $("<button/>").html("<i class='fa fa-trash' aria-hidden='true'></i>");//"Borrar Planilla"
                    btn_borrar.attr({'title':'Eliminar planilla','type':'button','class':'btn btn-block bg-gradient-danger', 'id':'btn_borrar','indice':i,'archivo':valores.NOM_ARCHIVO});
                    
                     var btn_informe_error = $("<button/>").html("DISTRIBUCIÓN CON ERRORES  <img src='img/icon/close.png'></img>");//"Borrar Planilla"
                    btn_informe_error.attr({'title':'Ver Errores','type':'button','class':'btn btn-link btn-sm', 'id':'btn_ver_error','indice':i,'archivo':valores.NOM_ARCHIVO});
                    
                    $('<td/>').html(i).appendTo(tr);
                   $('<td/>').html(valores.NOM_ARCHIVO_DESCARGA).appendTo(tr);
                    $('<td/>').html(valores.NRO_ORDEN).appendTo(tr);
                    $('<td/>').html(valores.FECHA_SUBIDA).appendTo(tr);
                    $('<td/>').html(valores.LOCALES).appendTo(tr);
                    $('<td/>').html(valores.SOLICITADO).appendTo(tr);
                    $('<td/>').html(valores.ARTICULOS).appendTo(tr);
                    if (valores.OK_DIGITACION == 'S') {
                        $('<td />').html("DIGITADO N° " + valores.NUM_TOTVS + " <img src='img/icon/check.png'></img>").appendTo(tr);
                    } else if (valores.OK_DIGITACION == 'E') {
                        $('<td />').html(btn_informe_error).appendTo(tr);
                    } else if (valores.OK_DIGITACION == 'F') {
                        $('<td />').html("FACTURADO  <img src='img/icon/check.png'></img> <img src='img/icon/check.png'></img> "+valores.FEC_FACT+" ").appendTo(tr);
                    } else if  (valores.OK_DIGITACION == 'I') {
                        $('<td />').html("ERROR EN VALIDACION INNER <img src='img/icon/close.png'></img>").appendTo(tr);
                    }else {
                        $('<td />').html("DISTRIBUCIÓN CARGADA <img src='img/icon/upload.png'></img>").appendTo(tr);
                    }
        
                    //columna valida Inner
                    if (valores.OK_DIGITACION == 'I') {
                        $('<td />').html('<a href="archivos_subidos/valida_inner.php?archivo=' + valores.NOM_ARCHIVO + '"> <p class="text-center" style="margin-bottom: 0px;"><img src="img/icon/close.png"></img></p></a>').attr({
                        'title': 'Error en validar Inner'
                        }).appendTo(tr);
                    }else{ 
                        $('<td />').html("<p class='text-center' style='margin-bottom: 0px;'><img src='img/icon/check.png'></img></p>").appendTo(tr);
                    }
        
                    //columna Estadoo WMS
                    if (valores.EN_WMS == 'S') {
                        $('<td />').html(btn_documentos).appendTo(tr);

                    }else{ 
                        $('<td />').html("<p class='text-center' style='margin-bottom: 0px;'><img src='img/icon/close.png'></img>").attr({
                        'title': 'Pendiente'
                        }).appendTo(tr);
                    }
                     $('<td />').html((valores.OK_DIGITACION=='N')?btn_digitar:(btn_digitar).attr({'disabled':true})).appendTo(tr);
                   $('<td />').html((valores.OK_DIGITACION=='S')?btn_reprocesar:(btn_reprocesar).attr({'disabled':true})).appendTo(tr);
                   $('<td />').html((valores.OK_DIGITACION=='N' || valores.OK_DIGITACION=='E')?btn_borrar:(btn_borrar).attr({'disabled':true})).appendTo(tr);

                   $('<td />').html(btn_wms).appendTo(tr);
                    
                    tr.appendTo("#tbl_planillas_falabella");
               
            });
            
        },
        
    });
    
}



function validarArchivo() {

    var archivo = $("#file_cventas").val();
    var xls  = archivo.substr(-3);
    var xlsx = archivo.substr(-4);
    var txt  = archivo.substr(-3);
    var csv  = archivo.substr(-3);

    //alert(zip);
    
    if (archivo === null || archivo === '') {
        
        Swal.fire('Debe llenar los campos obligatorios', 'Cargue planilla para continuar', 'error');

    
        
    }else if(xls != 'xls' && xlsx != 'xlsx' && txt != 'txt' && csv != 'csv'){
        
        Swal.fire('Tipo de archivo incorrecto', ' Debe ser XLS o XLSX', 'error');
       
    }else{
        
        cargarArchivo();
        
    }


}



function cargarArchivo() {


    var input_file = document.getElementById('file_cventas');
    var file = input_file.files[0];
    var data = new FormData();

   

        data.append("file_cventas", file);
        //alert(file);
        //alert(data);
        // alert(j);
        $.ajax({

            url: 'distribuciones/desarrollo/subida_falabella2.php',
            type: 'POST',
            dataType: 'text',
            contentType: false,
            data:data,
            processData: false,
            //cache: false
            beforeSend:function(){

                $("#mensajes").html('<img src="img/cargando2.gif">Subiendo Planilla de Distribución, Porfavor Espere...</img>');
                        Swal.fire({
                            title: 'Subiendo Planilla de Distribución !',
                            html: 'Por favor espere...',
                            timerProgressBar: true,
                            didOpen: () => {
                            Swal.showLoading();
                                /*
                                const b = Swal.getHtmlContainer().querySelector('b');
                                timerInterval = setInterval(() => {
                                b.textContent = Swal.getTimerLeft();
                                }, 100);
                                */
                            },

                        });

            },


            success:function(data){
                var error = data.substr(0,8);
                //alert(error);
                if(error === 'ERROR-01'){
                    Swal.fire('Archivo con Errores ! !', '<strong>Posibles Errores: </strong><br> 1.- Orden de Compra o Archivo ya existe en Base de Datos<br> 2.- 1 o mas SKU sin equivalencia<br> En pantalla esta el listado de sku sin equivalencia', 'error');
                    $("#mensajes").html("<div class='alert alert-danger alert-dismissible fade show' role='alert'>" + data + "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>");
                }else if(error == 'ERROR-02'){
                    Swal.fire('Archivo con Errores ! !', '<strong>Error de Formato: </strong><br>' + data + '', 'error');
                    $("#mensajes").html("<div class='alert alert-danger alert-dismissible fade show' role='alert'>" + data + "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>");
                }else{
                    $("#mensajes").html("<div class='alert alert-info alert-dismissible fade show' role='alert'>" + data + "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>");
                    Swal.fire('Datos Cargados ! !', 'Planilla Subida con Exito !', 'success');
                    datos_subidos();

                }
                    

                //datos_subidos();

            },


        });
    
    
}



