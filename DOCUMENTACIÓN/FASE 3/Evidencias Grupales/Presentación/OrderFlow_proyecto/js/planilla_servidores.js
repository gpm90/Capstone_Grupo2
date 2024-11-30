$(document).ready(onLoad);
function onLoad() {
    servidores();      
	$("#guardar_cambios").click(confirmar_ingreso);
	$("#limpiar_datos").click(limpiar_datos);
    $("#tbl_planilla").on('click','#btn_editar',cargar_servidor);
}
function limpiar_datos() {
    $("#sv_id").val('');
    $("#nombre").val('');
    $("#ip").val('');
    $("#ip_ext").val('');
    $("#usuario").val('');
    $("#clave").val('');
    $("#puerto").val('');
    $("#estado").val('');
    //$("#frm_update").reset();
}
function limpiarTabla() {
    $("#tbl_planilla tbody tr").remove();
    //$("#frm_update").reset();
}
function servidores() {
    limpiarTabla();
   
    //var planilla = $(this).attr('planilla');
    //alert(oc);
    $.ajax({
        url:'planilla_servidores.php',
        type: 'GET',
        dataType: 'json',
        //contentType: false,
        data:{'ver':'ver'},
        //processData: false,
        //cache: false
        //beforeSend: cargando,
        success:function(jsonphp){
            //$("#empresa_seleccionada").empty().text(empresa);
            //$("#btn_guardar_local").attr('planilla', planilla);
            //$("#tbl_selectLocal tbody tr").remove();
            var i=0;
                //alert(i);
            $.each(jsonphp, function(indice, valores){
                i+=1;
                var tr = $("<tr/>");
				if(valores.ESTADO=='N'){tr.attr('style','background-color:  #F4848D');}
                var btn_procesa = $("<button/>").text('Editar');
                btn_procesa.attr({'type':'button','class':'btn btn-block btn-success btn-sm','id':'btn_editar','ip':valores.IP});
                $("<td/>").html(i).appendTo(tr);
                $('<td/>').html(valores.NOMBRE).appendTo(tr);
                $('<td/>').html(valores.IP).appendTo(tr);
                $('<td/>').html(valores.IPEXT).appendTo(tr);
                $('<td/>').html(valores.USUARIO).appendTo(tr);
                $('<td/>').html("***************").appendTo(tr);
                $('<td/>').html(valores.PUERTO).appendTo(tr);
				if(valores.ESTADO =='S'){
					$('<td/>').html("SERVIDOR ACTIVO").appendTo(tr);					
				}else{
					$('<td/>').html("SERVIDOR INACTIVO").appendTo(tr);
				}
                $('<td/>').html(btn_procesa).appendTo(tr);
               
                tr.appendTo("#tbl_planilla");
                //alert(valores.LOCAL);
                
            });

            //$("#tbl_pedido_falabella").dataTable();
            //$("html, body").animate({ scrollTop: 680 }, "fast");

        },
      
    });

}
function cargar_servidor() { //CARGAR INPUT TABLA CON DATOS DEL TRABAJADOR
  //limpiarForm();
    var ip = $(this).attr('ip');
    //alert(nro);
    $.ajax({
        url:'planilla_servidores.php',
        type: 'GET',
        dataType: 'json',
        //contentType: false,
        data:{'cargar':'cargar','ip':ip},
        //processData: false,
        //cache: false
        //beforeSend:cargando,
        success:function(jsonphp){            
              $.each(jsonphp,function(indice, valores){
              $("#frm_admin");
                    $("#sv_id").val(valores.SV_ID);
                    $("#nombre").val(valores.NOMBRE);
                    $("#ip").val(valores.IP);
                    $("#ip_ext").val(valores.IPEXT);
                    $("#usuario").val(valores.USUARIO);
                    $("#clave").val(valores.CLAVE);
                    $("#puerto").val(valores.PUERTO);
                    $("#estado").val(valores.ESTADO);
                   
                   //alert(valores.TRABAJADOR);
            });
            $("html, body").animate({ scrollTop: 0 }, "fast");
        },
        //error:problemas
    });
}
function confirmar_ingreso(){

     Swal.fire({
          title: 'Desea Guardar Cambios?',
          text: "Datos se guardaran en Planilla Servidores",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Si, Guardar Cambios'
        }).then((resultado) => {

          if (resultado.value) {
            enviar_formulario();
          }

        });

}
function enviar_formulario() {
    var dataForm = $("#frm_pventa").serialize();
    //alert(dataForm);
    var data = dataForm+"&insertar=insertar";
    $.ajax({
        url:'planilla_servidores.php',
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
            $("#mensajes").html("<div class='alert alert-success' role='alert'>"+data+"</div>");
            servidores();
            Swal.fire('Datos Guardados !<br>', data, 'info');
        },
        error:function(data){
            $("#mensajes").html("<div class='alert alert-danger' role='alert'>"+data+"</div>");
        }
    });
}


