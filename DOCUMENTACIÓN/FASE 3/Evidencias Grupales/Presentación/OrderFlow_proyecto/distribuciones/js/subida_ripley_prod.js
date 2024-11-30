$(document).ready(onLoad);

function onLoad() {

   datos_subidos();
   //dateModal();
   //$("#enviar").click(cargarInforme);
   $("#btn_guardar").click(confimacion_subida);
   $("#btn_limpiar").click(clear_inputFile);

}

function clear_inputFile() {
   document.getElementById("form-subida-ripley").reset();
}

function clear_table() {

   $("#tbl_planillas_ripley tbody tr").remove();

}

function ver_error(archivo) {

   $.ajax({
      url: 'distribuciones/subida_ripley_prod.php',
      type: 'GET',
      dataType: 'json',
      data: {
         'ver_errores': 'ver_errores',
         'archivo': archivo
      },
      success: function (jsonphp) {

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
            confirmButtonText: '<i class="fa fa-thumbs-up"></i> OK!',
            confirmButtonAriaLabel: 'Thumbs up, great!',

            cancelButtonAriaLabel: 'Thumbs down'
         });

      },
   });
}

function borrarPlanilla(archivo) {

   Swal.fire({

      title: 'Desea Eliminar la Planilla?',
      text: "Se Iniciara el proceso de borrado",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e82020',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Si, Eliminar Datos'

   }).then((result) => {

      if (result.isConfirmed) {
         $.ajax({

            url: 'distribuciones/subida_ripley_prod.php',
            type: 'GET',
            dataType: 'text',
            //contentType: false,
            data: {
               'borrarPlanilla': 'borrarPlanilla',
               'archivo': archivo
            },
            beforeSend: function () {
               $("#mensajes").html('<img src="img/cargando2.gif">Cargando...</img>');
               Swal.fire({
                  title: 'Borrando Pedido.. !',
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
            success: function (textphp) {
               $("#mensajes").html("<div class='alert alert-success' role='alert'>" + textphp + "</div>");
               Swal.fire('Borrado Finalizado!', 'Se eliminó la planilla correctamente', 'success');
               clear_table();
               datos_subidos();
            },

         });
      }

   });


}


function confimacion_subida() {

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


function datos_subidos() {

   $.ajax({

      url: 'distribuciones/subida_ripley_prod.php',
      type: 'GET',
      dataType: 'json',
      //contentType: false,
      data: {
         'ver': 'ver'
      },
      //processData: false,
      //cache: false
      //beforeSend:cargando,
      success: function (jsonphp) {
         //if (data.success) {
         var i = 0;

         $.each(jsonphp, function (indice, valores) {

            i = i + 1;
            var tr = $("<tr/>").attr({
               'id': i
            });
            if (valores.OK_DIGITACION == 'E') {
               tr.attr('style', 'background-color:  #EF9B9B; border:1px solid;');
            }

            var btn_borrar = $("<button/>").text("Borrar Planilla");
            btn_borrar.attr({
               'type': 'button',
               'class': 'btn btn-block bg-gradient-danger btn-sm',
               'id': 'btn_borrar',
               'indice': i,
               'archivo': valores.ARCHIVO
            });

            var nomArchivo = valores.ARCHIVO.trim();
            var orcom = valores.OC.trim();

            var digitarURL = 'javascript:procesa_c5(' + "'" + orcom + "'" + ')';
            var btnA_Digitar = $("<a/>").html("<i class='far fa-check-circle'></i>");
            btnA_Digitar.attr({
               'class': 'btn btn-block bg-gradient-success btn-sm',
               'id': 'btnA_Digitar',
               'href': digitarURL,
               'indice': i
            });

            var reprocesaURL = 'javascript:reprocesa(' + "'" + nomArchivo + "'" + ')';
            var btnA_Revertir = $("<a/>").html("<i class='fa fa-undo' aria-hidden='true'></i>");
            btnA_Revertir.attr({
               'class': 'btn btn-block bg-gradient-primary btn-sm',
               'id': 'btnA_Revertir',
               'href': reprocesaURL,
               'indice': i
            });

            var deleteURL = 'javascript:borrarPlanilla(' + "'" + nomArchivo + "'" + ')';
            var btnA_Delete = $("<a/>").html("<i class='fa fa-trash' aria-hidden='true'></i>");
            btnA_Delete.attr({
               'class': 'btn btn-block bg-gradient-danger btn-sm',
               'id': 'btnA_Delete',
               'href': deleteURL,
               'indice': i
            });

            var informeURL = 'javascript:ver_error(' + "'" + nomArchivo + "'" + ')';
            var btnA_Informe = $("<a/>").html("<p>DISTRIBUCIÓN CON ERRORES</p>");
            btnA_Informe.attr({
               'id': 'btnA_Informe',
               'href': informeURL,
               'indice': i
            });

            $('<td/>').html(i).appendTo(tr);
            $('<td/>').html(valores.ARCHIVO_DESCARGA).appendTo(tr);
            $('<td/>').html(valores.OC).appendTo(tr);
            $('<td/>').html(valores.FECEMISION).appendTo(tr);
            $('<td/>').html(valores.UNIDADES).appendTo(tr);
            $('<td/>').html(valores.CANT_TOTAL).appendTo(tr);
            //columna estado//
            if (valores.OK_DIGITACION == 'S') {
               $('<td />').html("DIGITADO N° " + valores.NUM_TOTVS + " <img src='img/icon/check.png'></img>").appendTo(tr);
            } else if (valores.OK_DIGITACION == 'E') {
               $('<td />').html(btnA_Informe).appendTo(tr);
            } else if (valores.OK_DIGITACION == 'F') {
               $('<td />').html("FACTURADO  <img src='img/icon/check.png'></img> <img src='img/icon/check.png'></img> "+valores.FEC_FACT+" ").appendTo(tr);
            } else if  (valores.OK_DIGITACION == 'I') {
               $('<td />').html("ERROR EN VALIDACION INNER <img src='img/icon/close.png'></img>").appendTo(tr);
            }else {
               $('<td />').html("DISTRIBUCIÓN CARGADA <img src='img/icon/upload.png'></img>").appendTo(tr);
            }

            //columna valida Inner
            if (valores.OK_DIGITACION == 'I') {
               $('<td />').html('<a href="archivos_subidos/valida_inner.php?archivo=' + valores.ARCHIVO + '"> <p class="text-center" style="margin-bottom: 0px;"><img src="img/icon/close.png"></img></p></a>').attr({
                  'title': 'Error en validar Inner'
               }).appendTo(tr);
            }else{ 
               $('<td />').html("<p class='text-center' style='margin-bottom: 0px;'><img src='img/icon/check.png'></img></p>").appendTo(tr);
            }

            //columna Estadoo WMS
            if (valores.EN_WMS == 'S') {
               $('<td />').html("<p class='text-center' style='margin-bottom: 0px;'><img src='img/icon/check.png'></img>").attr({
                  'title': 'Traspasado a WMS'
               }).appendTo(tr);
            }else{ 
               $('<td />').html("<p class='text-center' style='margin-bottom: 0px;'><img src='img/icon/close.png'></img>").attr({
                  'title': 'Pendiente'
               }).appendTo(tr);
            }
            //boton digitar
            $('<td />').html((valores.OK_DIGITACION == 'N') ? btnA_Digitar : (btnA_Digitar).attr({
               'class': 'btn btn-block bg-gradient-success btn-sm disabled'
            })).appendTo(tr);

            //Boton Revertir
            $('<td />').html((valores.OK_DIGITACION == 'S') ? btnA_Revertir : (btnA_Revertir).attr({
               'class': 'btn btn-block bg-gradient-primary btn-sm disabled'
            })).appendTo(tr);

            //boton Borrar
            $('<td />').html((valores.OK_DIGITACION == 'N' || valores.OK_DIGITACION == 'E' || valores.OK_DIGITACION == 'I') ? btnA_Delete : (btnA_Delete).attr({
               'class': 'btn btn-block bg-gradient-danger btn-sm disabled'
            })).appendTo(tr);


            tr.appendTo("#tbl_planillas_ripley");
   //          $("#tbl_planillas_ripley").DataTable(/*{
	// 				"retrieve": true,
	// 				"paging": true,
	// 				"lengthChange": true,
	// 				"searching": true,
	// 				"ordering": true,
	// 				"info": true,
	// 				"autoWidth": true,
	// 				"responsive": true,
   //  }*/);

         });

      },

   });

}


function validarArchivo() {

   var archivo = $("#file_cventas").val();
   var xls = archivo.substr(-3);
   var xlsx = archivo.substr(-4);
   var txt = archivo.substr(-3);
   var csv = archivo.substr(-3);

   //alert(zip);

   if (archivo === null || archivo === '') {

      alert('Ningun archivo seleccionado');

   } else if (xls != 'xls' && xlsx != 'xlsx' && txt != 'txt' && csv != 'csv') {

      alert('Tipo de archivo incorrecto');

   } else {

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
   $.ajax({

      url: 'distribuciones/subida_ripley_prod.php',
      type: 'POST',
      dataType: 'text',
      contentType: false,
      data: data,
      processData: false,
      //cache: false
      beforeSend: function () {
         Swal.fire({
            title: 'Preparando la Carga!',
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
      success: function (data) {

         $("#mensajes").html("<div class='alert alert-info alert-dismissible fade show' role='alert'>" + data + "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button></div>");
         Swal.fire('Datos Cargados ! !', 'Datos Generados con exito !', 'success');

         clear_table();
         datos_subidos();


      },
      error: function (data) {
         Swal.fire('ERROR!', 'No se pudo realizar la carga, puede que ya exista.', 'warning');
      }


   });


}

function procesa_c5(oc) {

   Swal.fire({
      icon: 'question',
      title: "Desea procesar la Planilla?",
      text: "Estos datos seran enviados de manera automática a TOTVS",
      width: '500px',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, Procesar!',
      //showLoaderOnConfirm: true
   }).then(resultado => {
      if (resultado.value) {
         $.ajax({
            url: 'distribuciones/subida_ripley_prod.php',
            type: 'POST',
            dataType: 'text',
            //contentType: false,
            data: {
               'procesaC5': 'procesaC5',
               'orcom': oc
            },
            beforeSend: function () {
               $("#mensajes").html('<img src="img/cargando2.gif">Cargando...</img>');
               Swal.fire({
                  title: 'Digitando Pedido en TOTVS !',
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
            //processData: false,
            //cache: false
            success: function (data) {
               clear_table();
               datos_subidos();
               $("#mensajes").html("<div class='alert alert-success' role='alert'>" + data + "</div>");

               Swal.fire('PROCESO CORRECTO!', data, 'success');

            },
            error: function (data) {
               Swal.fire('ERROR!', 'ERROR NO SE REALIZÓ EL PROCESO', 'warning');
            }
         });
      } else {
         Swal.fire('No Procesado ! ', 'Cancelado', 'info');
      }
   });

}

function reprocesa(archivo) {

   Swal.fire({
      icon: 'question',
      title: "Desea reprocesar la Planilla?",
      text: "Estos datos seran enviados de manera automática a TOTVS",
      width: '500px',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, Reprocesar!',
      //showLoaderOnConfirm: true
   }).then(resultado => {
      if (resultado.value) {
         $.ajax({
            url: 'distribuciones/subida_ripley_prod.php',
            type: 'POST',
            dataType: 'text',
            //contentType: false,
            data: {
               'reprocesa': 'reprocesa',
               'archivo': archivo
            },
            //processData: false,
            //cache: false
            success: function (data) {
               clear_table();
               datos_subidos();

               $("#mensajes").html("<div class='alert alert-success' role='alert'>" + data + "</div>");
               Swal.fire('REPROCESO COMPLETO!', 'Se revirtió la información', 'success');

            },
            error: function (data) {
               Swal.fire('ERROR!', 'ERROR NO SE REALIZÓ EL PROCESO', 'warning');
            }
         });
      } else {
         Swal.fire('No Procesado ! ', 'Cancelado', 'info');
      }
   });

}