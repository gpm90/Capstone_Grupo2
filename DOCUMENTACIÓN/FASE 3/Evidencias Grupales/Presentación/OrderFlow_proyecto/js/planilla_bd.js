$(document).ready(onLoad);
function onLoad() {
      base_datos();
     $("#limpiar_datos").click(limpiar_datos);
     $("#guardar_cambios").click(confirmar_ingreso);
      $("#tbl_bd").on('click','#btn_editar',cargar_bd);
}
function limpiar_datos() {
    $("#bd_id").val('');
    $("#nombre").val('');
    $("#tipo").val('');
    $("#host").val('');
    $("#usuario").val('');
    $("#clave").val('');
    $("#puerto").val('');
    $("#sid").val('');
    $("#estado").val('');
    //$("#frm_update").reset();
}
function limpiarTabla() {
    $("#tbl_bd tbody tr").remove();
    //$("#frm_update").reset();
}
function base_datos() {
    limpiarTabla();
   
    //var planilla = $(this).attr('planilla');
    //alert(oc);
    $.ajax({
        url:'planilla_bd.php',
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
                var btn_procesa = $("<button/>").text('Editar');
                btn_procesa.attr({'type':'button','class':'btn btn-block btn-success btn-sm','id':'btn_editar','id_bd':valores.BD_ID});
                $("<td/>").html(i).appendTo(tr);
                $('<td/>').html(valores.NOMBRE).appendTo(tr);
                $('<td/>').html(valores.TIPO).appendTo(tr);
                $('<td/>').html(valores.HOST).appendTo(tr);
                $('<td/>').html(valores.USUARIO).appendTo(tr);
                $('<td/>').html("***************").appendTo(tr);
                $('<td/>').html(valores.PUERTO).appendTo(tr);
                $('<td/>').html(valores.SID).appendTo(tr);
                if(valores.ESTADO =='1'){
					$('<td/>').html("SERVIDOR ACTIVO").appendTo(tr);					
				}else{
					$('<td/>').html("SERVIDOR INACTIVO").appendTo(tr);
				}
                $('<td/>').html(btn_procesa).appendTo(tr);
               
                tr.appendTo("#tbl_bd");
                //alert(valores.LOCAL);
                
            });

            //$("#tbl_pedido_falabella").dataTable();
            //$("html, body").animate({ scrollTop: 680 }, "fast");

        },
      
    });

}
function cargar_bd() { //CARGAR INPUT TABLA CON DATOS DEL TRABAJADOR
  //limpiarForm();
    var id_bd = $(this).attr('id_bd');
    //alert(nro);
    $.ajax({
        url:'planilla_bd.php',
        type: 'GET',
        dataType: 'json',
        //contentType: false,
        data:{'cargar':'cargar','id_bd':id_bd},
        //processData: false,
        //cache: false
        //beforeSend:cargando,
        success:function(jsonphp){            
              $.each(jsonphp,function(indice, valores){
              $("#frm_admin");
                    $("#bd_id").val(valores.BD_ID);
                    $("#nombre").val(valores.NOMBRE);
                    $("#tipo").val(valores.TIPO);
                    $("#host").val(valores.HOST);
                    $("#usuario").val(valores.USUARIO);
                    $("#clave").val(valores.CLAVE);
                    $("#puerto").val(valores.PUERTO);
                    $("#sid").val(valores.SID);
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
          text: "Datos se guardaran en BD Monarch",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Si, Guardar Cambios'
        }).then((resultado) => {

          if (resultado.value) {
            insertTienda();
          }

        });

}
function insertTienda() {
    var dataForm = $("#frm_pventa").serialize();
    //alert(dataForm);
    var data = dataForm+"&insertar=insertar";
    $.ajax({
        url:'planilla_bd.php',
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
            base_datos();
            Swal.fire('Datos Guardados !<br>', data, 'info');
        },
        error:function(data){
            $("#mensajes").html("<div class='alert alert-danger' role='alert'>"+data+"</div>");
        }
    });
}