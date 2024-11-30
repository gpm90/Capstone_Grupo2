$(document).ready(onLoad);    
function onLoad() {

    //datos_subidos();
    //dateModal();
    //$("#enviar").click(cargarInforme);
    $("#btn_guardar").click(confimacion_subida);
    $("#btn_guardar_1").click(confirmar_ingreso);
    $("#btn_limpiar").click(limpiardatos);
    $("#descarga_info").click(descargar_info);
    $("#btn_buscar").click(ver_datos);
    $("#tbl_articulos").on('click','#btn_borrar',borrar_articulo);
    //$("#tbl_planillas_falabella").on('click','#btn_digitar',digita_totvs);
    $("#tbl_articulos").on('click','#btn_editar',cargar_codigo);


}
function descargar_info(){
      
      
    var canal_venta = $("#canal_descarga").val();
    if(canal_venta ===''){
      Swal.fire('¡Seleccionar Canal de Venta! ', '', 'warning');
      //alert('Seleccionar Canal de Venta');
    }else{
      //Swal.fire('¡SELECCIONASTE '+canal_venta+'! ', '', 'warning');
      window.open("archivos_subidos/info_equivalencia.php?canal="+canal_venta+"","Nueva ventana",'width=1200,height=900'); 
    }
}
function cargar_codigo() { //CARGAR INPUT TABLA CON DATOS DEL TRABAJADOR
  //limpiarForm();
    var articulo = $(this).attr('articulo');
    var canal = $(this).attr('canal');
    //alert(nro);
    $.ajax({
        url:'subida_equivalencias.php',
        type: 'GET',
        dataType: 'json',
        //contentType: false,
        data:{'cargar':'cargar','articulo':articulo,'canal':canal},
        //processData: false,
        //cache: false
        //beforeSend:cargando,
        success:function(jsonphp){            
              $.each(jsonphp,function(indice, valores){
              $("#frm_admin");
                    $("#canal_1").val(valores.ZEQ_CANAL);
                    $("#cod_mch_1").val(valores.ZEQ_COD);
                    $("#sku_cliente_1").val(valores.ZEQ_CLICOD);
                    $("#barra_cliente_1").val(valores.ZEQ_CLIBAR);
                    $("#descr_cliente_1").val(valores.ZEQ_CLIDES);
                    $("#precio_venta_1").val(valores.ZEQ_PRCCLI);
                    $("#recno").val(valores.RECNO);
                   
                   //alert(valores.TRABAJADOR);
            });
            $("html, body").animate({ scrollTop: 0 }, "fast");
        },
        //error:problemas
    });
}

function borrar_articulo(){

   
    cliente_codigo = $(this).attr('sku');
    recno = $(this).attr('recno');
	
	 Swal.fire({
           icon: 'question',
            title: "Desea Eliminar SKU "+cliente_codigo+"  ?",
             text: "Articulo quedara eliminado",
            width: '500px',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Confirmar !',
            showLoaderOnConfirm: true
    }).then(resultado => {
        if (resultado.value) {
				// alert(indice);
				$("#"+recno).remove();

				$.ajax({

					url:'subida_equivalencias.php',
					type: 'GET',
					dataType: 'text',
					//contentType: false,
					data:{'borrar_articulo':'borrar_articulo','cliente_codigo':cliente_codigo},
					 
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
            console.log(result);
          if (result.isConfirmed) {
            validarArchivo();
          }

        });

}
function limpiarTabla() {
    $("#tbl_articulos tbody tr").remove();
    //$("#frm_update").reset();
}
function limpiardatos() {
    $("#canal_1").val('');
    $("#cod_mch_1").val('');
    $("#sku_cliente_1").val('');
    $("#barra_cliente_1").val('');
    $("#descr_cliente_1").val('');
    $("#precio_venta_1").val('');
    $("#recno").val('');
                   
}
function ver_datos() {
    limpiarTabla();
    
    var canal = $("#canal").val();
    var cod_monarch = $("#cod_monarch").val();
     if(canal == '' || cod_monarch == ''){
        Swal.fire('¡Debes completar el formulario! ', '', 'warning');
    }else{
        
        $.ajax({
            
            url:'subida_equivalencias.php',
            type: 'GET',
            dataType: 'json',
            //contentType: false,
            data:{'ver':'ver','canal':canal,'cod_monarch':cod_monarch},
            //processData: false,
            //cache: false
            //beforeSend:cargando,
            success:function(jsonphp){
                //if (data.success) {
                var i=0;
    
                $.each(jsonphp, function(indice, valores){
    
                        i=i+1;
                        var tr = $("<tr/>").attr({'id':i});
                        // TODO:
                      /* var btn_digitar = $("<button/>").html("<i class='far fa-check-circle'></i>");//Digitar en Totvs
                        btn_digitar.attr({'title':'Digita Planilla a TOTVS','type':'button','class':'btn btn-block bg-gradient-success','id':'btn_digitar','oc':valores.NRO_ORDEN});
                        */
                        var btn_editar = $("<button/>").html("<i class='fa fa-undo' aria-hidden='true'></i>");//'Revertir Digitación'
                        btn_editar.attr({'title':'Editar','type':'button','class':'btn btn-block bg-gradient-primary','id':'btn_editar','articulo':valores.ZEQ_CLICOD,'canal':valores.ZEQ_CANAL});
                        
                        var btn_borrar = $("<button/>").html("<i class='fa fa-trash' aria-hidden='true'></i>");//"Borrar Planilla"
                        btn_borrar.attr({'title':'Eliminar','type':'button','class':'btn btn-block bg-gradient-danger', 'id':'btn_borrar','indice':valores.RECNO,'sku':valores.ZEQ_CLICOD});
                        
                        $('<td/>').html(i).appendTo(tr);
                        $('<td/>').html(valores.ZEQ_CANAL).appendTo(tr);
                        $('<td/>').html(valores.ZEQ_COD).appendTo(tr);
                        $('<td/>').html(valores.ZEQ_BARCOD).appendTo(tr);
                        $('<td/>').html(valores.ZEQ_CLICOD).appendTo(tr);
                        $('<td/>').html(valores.ZEQ_CLIBAR).appendTo(tr);
                        $('<td/>').html(valores.ZEQ_PRCCLI).appendTo(tr);
                        $('<td/>').html(btn_editar).appendTo(tr);
                        $('<td/>').html(btn_borrar).appendTo(tr);
                        
                        tr.appendTo("#tbl_articulos");
                   
                });
                
            },
            
        });
    }
}



function validarArchivo() {

    var archivo = $("#file_cventas").val();
    var xls  = archivo.substr(-3);
    var xlsx = archivo.substr(-4);
    var txt  = archivo.substr(-3);
    var csv  = archivo.substr(-3);

    //alert(zip);
    
    if (archivo === null || archivo === '') {
        
        alert('Ningun archivo seleccionado');
        
    }else if(xls != 'xls' && xlsx != 'xlsx' && txt != 'txt' && csv != 'csv'){
        
        alert('Tipo de archivo incorrecto');
       
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

        url: 'subida_equivalencias.php',
        type: 'POST',
        dataType: 'text',
        contentType: false,
        data:data,
        processData: false,
        //cache: false
        beforeSend:function(){

            $("#mensajes").html('<img src="img/cargando2.gif">Subiendo Planilla de Equivalencias , Porfavor Espere...</img>');
                    Swal.fire({
						  title: 'Subiendo Planilla de Equivalencias !',
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
            // alert(error);
               if(error === 'ERROR-01'){
                   Swal.fire('Error de Validacion ! !', ''+ data +' ', 'error');
                   $("#mensajes").html("<div class='alert alert-danger alert-dismissible fade show' role='alert'>" + data + "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>");
            }else if(error == 'ERROR-02'){
                   Swal.fire('Archivo con Errores ! !', '<strong>Error de Formato: </strong><br>' + data + '', 'error');
                   $("#mensajes").html("<div class='alert alert-danger alert-dismissible fade show' role='alert'>" + data + "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>");
               }else{
                   $("#mensajes").html("<div class='alert alert-info alert-dismissible fade show' role='alert'>" + data + "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>");
                   Swal.fire('Datos Cargados ! !', 'Planilla Subida con Exito !', 'success');
                   datos_subidos();
      
               }
				

            ////datos_subidos();

        },


    });
    
    
    
}
function confirmar_ingreso(){

     Swal.fire({
          title: 'Desea Guardar Cambios?',
          text: "Datos se guardaran en BD Monarch",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Si, Guardar Cambios'
        }).then((resultado) => {

          if (resultado.value) {
            insert();
          }

        });

}
function insert() {
    var dataForm = $("#equivalencias").serialize();
    //alert(dataForm);
    var data = dataForm+"&insertar=insertar";
    $.ajax({
        url:'subida_equivalencias.php',
        type: 'POST',
        dataType: 'text',
        //contentType: false,
        data:data,
        //processData: false,
        //cache: false
        beforeSend:function(){
            $("#mensajes").html('<img src="img/cargando2.gif">Cargando...</img>');
        },
        success:function(data){
            var error = data.substr(0,8);
            // alert(error);
               if(error === 'ERROR-01'){
                   Swal.fire('Error de Validacion ! !', ''+ data +' ', 'error');
                   $("#mensajes").html("<div class='alert alert-danger alert-dismissible fade show' role='alert'>" + data + "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>");
            }else if(error == 'ERROR-02'){
                   Swal.fire('Archivo con Errores ! !', '<strong>Error de Formato: </strong><br>' + data + '', 'error');
                   $("#mensajes").html("<div class='alert alert-danger alert-dismissible fade show' role='alert'>" + data + "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>");
               }else{
                   $("#mensajes").html("<div class='alert alert-info alert-dismissible fade show' role='alert'>" + data + "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>");
                   Swal.fire('Datos Cargados ! !', 'Planilla Subida con Exito !', 'success');
                   datos_subidos();
      
               }
        },
       
    });
}


