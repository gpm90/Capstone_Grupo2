$(document).ready(onLoad);
function onLoad() {
    $("#btn_agregar").click(validarForm);
    $("#btn_guardar").click(guardarMenu);
    $("#btn_limpiar").click(limpiarForm);
    $("#cmb_nivel").change(cargaParametrosNivel);
    $("#tbl_menu").on('click','#btnEditar',cargarMenu);
    $("#tbl_menu").on('click','#btn_borrar',removerMenu);
    $("#tbl_itemsMenu").on('click','#btn_editar',cargarInputMenu);
    $("#tbl_itemsMenu").on('click','#btnBorrar',borrarMenu);
    $("#btn_update_menu").click(updateMenu);
    getMenuItems();
    
}
function limpiarForm() {
    $("#frm_menu #rowOne:first-child").remove();
    $("#tbl_menu tbody tr").remove();
    $("#cmb_nivel").val("");
    $("#cmb_nivel_padre").val("");
    $("#txt_nombre").val("");
    $("#txt_url").val("");
}
function removerMenu(){
      codigo = $(this).attr('cod');
    $(".modal-body").html("Desea eliminar el registro : "+codigo+" ?");
    $("#modal-delete").modal({
        backdrop:'static'
    });
    $("#si-del").click(function(){
        $("#"+codigo).remove();
  });
}
function borrarMenu() {
    codigo = $(this).attr('cod');
       Swal.fire({
            icon: 'question',
            title: "Desea Eliminar Menu Codigo : "+codigo+" ?",
            // text: "Estos datos seran enviandos de manera automatica a TOTVS",
            width: '500px',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Eliminar !'
            //showLoaderOnConfirm: true
    }).then(resultado => {
        if (resultado.value) {
        $("#"+codigo).remove();
			$.ajax({
				url:'menu.php',
				type: 'GET',
				dataType: 'text',
				//contentType: false,
				data:{'deleteMenu':'deleteMenu','codMenu':codigo},
				 
				//processData: false,
				//cache: false
				//beforeSend:cargando,
				success:function(textphp){
					$("#mensajes").html("<div class='alert alert-success' role='alert'>"+textphp+"</div>");
					
				},
				//error:problemas
			});
	    } else {
            // Dijeron que no
           Swal.fire('No Eliminado ! ', 'Cancelado', 'info');
        }
    });

}
function updateMenu() {
    var trId = $(this).attr('trid');
    var fila = $(this).attr('fila');
    
    //alert(fila);
    var nivelVal       = $("#cmb_nivel option:selected").val();
    var nivel          = $("#cmb_nivel option:selected").text();
    var nivelPadreVal  = $("#cmb_nivel_padre option:selected").val();
    var nivelPadre     = $("#cmb_nivel_padre option:selected").text();
    var nombre         = $("#txt_nombre").val();
    var url            = $("#txt_url").val();
   
    
    $("#"+trId).empty();
    
    $("#"+trId).append($("<td />").html(nivelVal));
    $("#"+trId).append($("<td />").html(nivel).append($("<input/>").attr({'type':'hidden','value':nivelVal,'name':'men-nivel'+fila,'id':'men-nivel'+fila})));
    $("#"+trId).append($("<td />").html(nivelPadre).append($("<input/>").attr({'type':'hidden','value':nivelPadreVal,'name':'men-nivelP'+fila,'id':'men-nivelP'+fila})));
    $("#"+trId).append($("<td />").html(nombre).append($("<input/>").attr({'type':'hidden','value':nombre,'name':'men-nombre'+fila,'id':'men-nombre'+fila})));
    $("#"+trId).append($("<td />").html(url).append($("<input/>").attr({'type':'hidden','value':url,'name':'men-url'+fila,'id':'men-url'+fila})));
    $("#"+trId).append($("<td />").html('Actualizado'));
    //limpiarForm();
    alert(trId);
}
function cargarMenu() {    
    //alert($(this).attr('cod'));    
    $.ajax({
        url:'menu.php',
        type: 'GET',
        dataType: 'json',
        //contentType: false,
        data:{'editMenu':'editMenu','codMenu':$(this).attr('cod')},
        //processData: false,
        //cache: false,
        //beforeSend:cargando,
        success:function(jsonphp){
            $.each(jsonphp,function(indice, valores){
                 
                 if ($.isNumeric(indice)) {
                        var tr = $('<tr/>');/*.attr({'id':valores.CODIGO});*/
                        var btnEditar = $("<button/>").text('Editar');
                        btnEditar.attr({'type':'button','class':'btn btn-info btn-xs','id':'btnEditar','cod':valores.CODIGO_ITEM});
                        $("<td />").html(valores.CODIGO).appendTo(tr);
                        $("<td />").html(valores.NIVEL).appendTo(tr);
                        $("<td />").html(valores.ORDEN).appendTo(tr);
                        $("<td />").html(valores.CODIGO_ITEM).appendTo(tr);
                        $("<td />").html(valores.NOMBRE).appendTo(tr);
                        $("<td />").html(valores.URL).appendTo(tr); 
                        $("<td />").html(btnEditar).appendTo(tr);
                        
                        tr.appendTo("#tbl_menu");
                         
                    }
                
            });
            $("#cmb_nivel").focus();
        },
        //error:problemas
    });
}
function cargarInputMenu() {
    var cod = $(this).attr('cod');
    var haber = $(this).attr('haber');
    var fila = $(this).attr('fila');
    /* se guarda id de fila a editar en atributo trid ubicado en boton actualizar haber */
    $("#btn_update_menu").attr({'trid':haber,'fila':fila});
    //alert($(this).attr('cod'));    
    $.ajax({
        url:'menu.php',
        type: 'GET',
        dataType: 'json',
        //contentType: false,
        data:{'editMenu':'editMenu','codMenu': cod},
        //processData: false,
        //cache: false,
        //beforeSend:cargando,
        success:function(jsonphp){
            $.each(jsonphp,function(indice, valores){
              $("#frm_menu #rowOne:first-child").remove();
                var row         = $("<div/>").attr({'class':'row','id':'rowOne'});
                var formGroup   = $("<div/>").attr({'class':'form-group'});
                 formGroup.append($("<label/>").attr({'class':'col-xs-1 control-label'}).text('Codigo')).appendTo(row);
                var codigo      = $("<input/>").attr({'type':'text','class':'form-control input-sm','value':valores.RECNO,'disabled':true});
                var codigohd    = $("<input/>").attr({'type':'hidden','value':valores.RECNO,'id':'txt_codigo_menu','name':'txt_codigo_menu'});
                formGroup.append($("<div/>").attr({'class':'col-xs-2'}).append(codigo).append(codigohd)).appendTo(row);
                row.prependTo("#frm_menu");
                
                //$("#cmb_nivel").val(valores.NIVEL).attr({'selected':true});
                $("#cmb_nivel_padre").val(valores.CODIGO).attr({'selected':true});
                $("#txt_nombre").val(valores.NOMBRE);
                $("#txt_url").val(valores.URL);
                  
            });
        $("#cmb_nivel").focus();
        //$("#tbl_menu tbody tr").remove();
        },
        //error:problemas
    });
}
function cargaParametrosNivel() {
    $("#cmb_nivel option:selected").each(function(){
        var valor = $(this).val();
        if (valor==1) {
            $("#txt_url").val("#");
            
        }else{
            $.ajax({
                url:'menu.php',
                type: 'GET',
                dataType: 'json',
                //contentType: false,
                data:{'nivelPadre':'nivelPadre','nivelHijo':valor},
                //processData: false,
                //cache: false
                //beforeSend:cargando,
                success:function(jsonphp){
                    $("#cmb_nivel_padre").empty();
                    $.each(jsonphp, function(indice, valores){
                        var option = $("<option />").attr({'value':valores.CODIGO});
                        option.text(valores.NOMBRE);
                                
                        option.appendTo("#cmb_nivel_padre");
                    });
                },
                //error:problemas
            });
        }
    });
}
function validarForm() {
    contador=0;
    $("input[class~='required'], select[class~='required']").each(function(){
        var valor = $(this).val();
        if (valor=='') {
            contador+=1;
            var nombre = $(this).attr("name");
            //var lbl = nombre.substring(4);
            var texto = $("label[for~='"+nombre+"']").text();
            
            alert("Rellenar campo "+texto);
            $(this).css("border-color","orange");
        }
    });
    if (contador==0) {
        agregarMenu();
    }
}
function agregarMenu() {
    var nivel = $("#cmb_nivel option:selected").val();
    var codigo = (nivel!=1)?$("#cmb_nivel_padre").val():$("#txt_nombre").val().substr(0,3).toUpperCase();
    $.ajax({
        url:'menu.php',
        type: 'GET',
        dataType: 'text',
        //contentType: false,
        data:{'ordenItemMenu':'ordenItemMenu','cod':codigo,'nivel':nivel},
        //processData: false,
        //cache: false
        //beforeSend:cargando,
        success:function(textphp){
            
            var fila = $("#tbl_menu tbody tr").length;
            var numFila = fila+1;
            
            //var nivel = $("#cmb_nivel option:selected").val();
            //var codigo = (nivel!=1)?$("#cmb_nivel_padre").val():$("#txt_nombre").val().substr(0,3).toUpperCase();
            
            var orden = (fila!=0)?parseInt(textphp)+parseInt(fila):textphp;
            var codigoItem = codigo.trim()+nivel.trim()+orden.trim();
            var nombre = $("#txt_nombre").val();
            var url = $("#txt_url").val();
           
            
             var tr = $("<tr/>").attr({'id':numFila});
             var btn_borrar = $("<button />").text("Borrar");
             btn_borrar.attr({'type':'button','class':'btn btn-block bg-gradient-danger btn-sm', 'id':'btn_borrar','cod':numFila});
             //var btnEditar = $("<button />").text("Editar");
             //btnEditar.attr({'type':'button','class':'btn btn-success btn-xs', 'id':'btnEditar','cod':numFila});
            $("<td/>").html(codigo).append($("<input/>").attr({'type':'hidden','value':codigo,'id':'codigo'+numFila,'name':'codigo'+numFila})).appendTo(tr);
            $("<td/>").html(nivel).append($("<input/>").attr({'type':'hidden','value':nivel,'id':'nivel'+numFila,'name':'nivel'+numFila})).appendTo(tr);
            $("<td/>").html(orden).append($("<input/>").attr({'type':'hidden','value':orden,'id':'orden'+numFila,'name':'orden'+numFila})).appendTo(tr);
            $("<td/>").html(codigoItem).append($("<input/>").attr({'type':'hidden','value':codigoItem,'id':'cod_item'+numFila,'name':'cod_item'+numFila})).appendTo(tr);
            $("<td/>").html(nombre).append($("<input/>").attr({'type':'hidden','value':nombre,'id':'nombre'+numFila,'name':'nombre'+numFila})).appendTo(tr);
            $("<td/>").html(url).append($("<input/>").attr({'type':'hidden','value':url,'id':'url'+numFila,'name':'url'+numFila})).appendTo(tr);
            $("<td/>").html(btn_borrar).appendTo(tr);
            //$("<td/>").html(btnEditar).appendTo(tr);
            
            tr.appendTo("#tbl_menu");
            
        },
        //error:problemas
    });
    
    
}

function guardarMenu() {
    var fila = $("#tbl_menu tbody tr").length;
    if (fila>0) {
        var data = $("#tbl_menu tbody tr input[type='hidden']").serialize()+"&max="+fila+"&ingresoMenu=ingresoMenu";
        //alert(data);
        $.ajax({
            url:'menu.php',
            type: 'POST',
            dataType: 'text',
            //contentType: false,
            data:data,
            //processData: false,
            //cache: false
            //beforeSend:cargando,
            success:function(textphp){
                $("#mensajes").html("<div class='alert alert-success' role='alert'>"+textphp+"</div>");
            },
            //error:problemas
        });
        
    }else{
        alert("Debe agregar item de menu!");
    }
    
    
}

function getMenuItems() {
    $.ajax({
        url:'menu.php',
        type: 'GET',
        dataType: 'json',
        //contentType: false,
        data:{'verMenu':'verMenu'},
        //processData: false,
        //cache: false
        //beforeSend:cargando,
        success:function(jsonphp){
            var i=0;
            $.each(jsonphp, function(indice, valores){
                i+=1;
                var tr = $("<tr/>");
                  var btn_editar = $("<button/>").attr({'type':'button','class':'btn btn-block bg-gradient-success btn-sm ','id':'btn_editar','cod':valores.CODITEM}).text('Editar');
                 var btnBorrar = $("<button/>").attr({'type':'button','class':'btn btn-block bg-gradient-danger btn-sm','id':'btnBorrar','cod':valores.CODITEM}).text('Borrar');
                if (valores.NIVEL==1) {
                    tr.attr({'class':'bg-success disabled color-palette'});
                }
                $("<td/>").html(i).appendTo(tr);
                $("<td/>").html(valores.CODMENU).appendTo(tr);
                $("<td/>").html(valores.NIVEL).appendTo(tr);
                $("<td/>").html(valores.ORDEN).appendTo(tr);
                $("<td/>").html(valores.CODITEM).appendTo(tr);
                $("<td/>").html(valores.NOMBRE).appendTo(tr);
                $("<td/>").html(valores.URL).appendTo(tr);
                //$("<td />").html(btn_editar).appendTo(tr);
                $("<td />").html(btnBorrar).appendTo(tr);
                
                tr.appendTo("#tbl_itemsMenu");
            });
            
        },
        //error:problemas
    });
    
}
