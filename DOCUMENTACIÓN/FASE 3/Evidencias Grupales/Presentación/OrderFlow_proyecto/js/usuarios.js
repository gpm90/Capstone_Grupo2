$(document).ready(onLoad);
function onLoad() {
    // updateUsuarios();
    getUsers();
    $("#btn_guardar").click(validaForm);
    $("#btn_limpiar").click(limpiarForm);
    $("#tbl_usuario").on('click','#btn_editar',editarUsuario);
    $("#tbl_usuario").on('click','#btn_borrar',borrarUsuario);
    $("#tbl_usuario").on('click','#btn_permisos',verPermisos);
    $("#btn_guardar_permiso").click(guardarPermiso);

}
function limpiarForm() {
    $("#txt_user_name").val("");
    $("#txt_name").val("");
    $("#txt_pass").val("");
    $("#txt_pass_conf").val("");
    $("#txt_correo").val("");
}
function limpiar_tabla(){
    $("#tbl_usuario tbody tr").remove();
}
function borrarUsuario() {
    codigo = $(this).attr('user');
       Swal.fire({
            icon: 'question',
            title: "Desea Eliminar usuario : "+codigo+" ?",
            // text: "Estos datos seran enviandos de manera automatica a TOTVS",
            width: '500px',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, Eliminar !',
            showLoaderOnConfirm: true
    }).then(resultado => {
        if (resultado.value) {
        //$("#"+codigo).remove();
        $.ajax({
            url:'usuarios.php',
            type: 'GET',
            dataType: 'text',
            //contentType: false,
            data:{'deleteUsuario':'deleteUsuario','codUsuario':codigo},
             
            //processData: false,
            //cache: false
            //beforeSend:cargando,
            success:function(textphp){
                $("#mensajes").html("<div class='alert alert-success' role='alert'>"+textphp+"</div>");
                getUsers();
                
            },
            //error:problemas
        });
   } else {
            // Dijeron que no
           Swal.fire('No Eliminado ! ', 'Cancelado', 'info');
        }
    });
}
function editarUsuario() {    
    $.ajax({
        url:'usuarios.php',
        type: 'GET',
        dataType: 'json',
        //contentType: false,
        data:{'editUsuario':'editUsuario','codUsuario':$(this).attr('user')},
        //processData: false,
        //cache: false,
        //beforeSend:cargando,
        success:function(jsonphp){
            $.each(jsonphp,function(indice, valores){
                $("#frm_usuario #rowOne:first-child").remove();
                var row = $("<div/>").attr({'class':'row','id':'rowOne'});
                row.prependTo("#frm_usuario");                
                
                $("#txt_user_name").val(valores.USER);
                $("#txt_name").val(valores.NOMBRE);
                //$("#txt_pass").val(valores.PASS);
                //$("#txt_pass_conf").val(valores.PASS);
                //$("#txt_pass_conf").val(valores.NOMBRE);
            });
            $("html, body").animate({ scrollTop: 0 }, "fast");
        },
        //error:problemas
    });
    
}
function guardarPermiso() {
    var dataCheckbox ='';
    $("input:checkbox").each(function(){
        var valor = $(this).attr('name');
        var check = $(this).prop('checked');
        dataCheckbox +=valor+'='+check+'&';
    });
    
    data = dataCheckbox+"user="+$(this).attr('user')+"&insertPermiso=insertPermiso";
    //alert(data);
    $.ajax({
        url:'usuarios.php',
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
    $("html, body").animate({ scrollTop: 1 }, "fast");
}


function verPermisos() {
    var user = $(this).attr('user');
    //alert(user);
    $.ajax({
        url:'usuarios.php',
        type: 'GET',
        dataType: 'json',
        //contentType: false,
        data:{'verPermisos':'verPermisos','user':user},
        //processData: false,
        //cache: false
        //beforeSend:cargando,
        success:function(jsonphp){
            $("#user").empty().text(user);
            $("#btn_guardar_permiso").attr('user',user);
            $("#tbl_permiso tbody tr").remove();
            var i=0;
            $.each(jsonphp, function(indice, valores){
                i+=1;
                var tr = $("<tr/>");
                if (valores.NIVEL==1) {
                    tr.attr({'class':'bg-danger color-palette'});
                }
                var checkbox = $("<input/>");
                checkbox.attr({'type':'checkbox','name':valores.CODITEM.trim(),'checked':valores.CHECKED});
                $("<td/>").html(i).appendTo(tr);
                $("<td/>").html(valores.CODMENU).appendTo(tr);
                $("<td/>").html(valores.CODITEM).appendTo(tr);
                $("<td/>").html(valores.NOMBRE).appendTo(tr);
                $("<td/>").html(checkbox).appendTo(tr);
                tr.appendTo("#tbl_permiso");
                
            });
             $("html, body").animate({ scrollTop: 680 }, "fast");
        },
        //error:problemas
    });
}
function updateUsuarios() {
    setTimeout(function(){
    getUsers();
    done();
    },1);
}

function getUsers() {
   limpiar_tabla();
    $.ajax({
        url:'usuarios.php',
        type: 'GET',
        dataType: 'json',
        //contentType: false,
        data:{'getUsers':'getUsers'},
        //processData: false,
        //cache: false
        //beforeSend:cargando,
        success:function(jsonphp){
            $("#tbl_usuario tbody tr").remove();
            var i = 0;
            $.each(jsonphp, function(indice,valores){
                i+=1;
                var tr = $("<tr />");
                var btn_editar = $("<button />").text("Editar ");
                btn_editar.attr({'type':'button','class':'btn btn-block bg-gradient-success btn-sm', 'id':'btn_editar','user':valores.USER}); // far fa-edit
                var btn_permisos = $("<button />").text("Permisos ");
                btn_permisos.attr({'type':'button','class':'btn btn-block bg-gradient-info btn-sm', 'id':'btn_permisos','user':valores.USER});
                var btn_borrar = $("<button />").text("Borrar ");// far fa-address-book
                btn_borrar.attr({'type':'button','class':'btn btn-block bg-gradient-danger btn-sm', 'id':'btn_borrar','user':valores.USER}); //far fa-trash-alt
				
				var botones = btn_editar+btn_permisos+btn_borrar;
                $('<td />').html(i).appendTo(tr);
                $('<td />').html(valores.USER).appendTo(tr);
                $('<td />').html(valores.NOMBRE).appendTo(tr);
                $('<td />').html(btn_editar).appendTo(tr);
                $('<td />').html(btn_permisos).appendTo(tr);
                $('<td />').html(btn_borrar).appendTo(tr);
                // $('<td />').html(botones).appendTo(tr);
                tr.appendTo("#tbl_usuario");
            });
            //}
            // $("#tbl_usuario").dataTable();
        },
        //error:problemas
    });
}
function validaForm() {
    if (validarVacio() && validaPass()) {
        guardarUsuario();
        getUsers();
    }
}
function validaPass() {
    var pass = $("#txt_pass").val();
    var passc = $("#txt_pass_conf").val();
    if (pass==passc) {
        return true;
    }else{
        alert("Contraseñas no coinciden!");
    }
}
function validarVacio() {
    var contador=0;
    $("input[class~='requerid']").each(function(){
        var valor = $(this).val();
        if (valor=='') {
            contador=contador+1;
            var nombre = $(this).attr("name");
            //var lbl = nombre.substring(4);
            var texto = $("label[for~='"+nombre+"']").text();
            
            alert("Rellenar campo "+texto);
            $(this).css("border-color","orange");
        }
    });
    if (contador==0) {
        //alert("Form validado!")
        return true;
    }
}
function guardarUsuario() {
    
    var data = $("form").serialize()+"&insertUser=insertUser";
    
    $.ajax({
        url:'usuarios.php',
        type: 'POST',
        dataType: 'text',
        //contentType: false,
        data:data,
        //processData: false,
        //cache: false
        beforeSend:function(){
            $("#mensajes").html('<img src="img/cargando2.gif">Cargando...</img>');
        },
        success:function(textphp){
            $("#mensajes").html("<div class='alert alert-success' role='alert'>"+textphp+"</div>");
            getUsers();
        },
        //error:problemas
    });
    
    
}

