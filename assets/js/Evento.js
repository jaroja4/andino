class Evento {
    // Constructor
    constructor(id, nombre, url) {
        this.id = id || null;
        this.nombre = nombre || '';
        this.url = this.url || '';
    }

    //Getter
    // get Read() {
    //     var miAccion= this.id==null ? 'ReadAll' : 'Read';
    //     $.ajax({
    //         type: "POST",
    //         url: "class/Evento.php",
    //         data: { 
    //             action: miAccion,
    //             id: this.id
    //         }
    //     })
    //     .done(function( e ) {
    //         evento.Reload(e);
    //     })    
    //     .fail(function (e) {
    //         evento.showError(e);
    //     });
    // }

    get List() {
        var miAccion= 'ReadAll';
        $.ajax({
            type: "POST",
            url: "class/Evento.php",
            data: { 
                action: miAccion
            }
        })
        .done(function( e ) {
            evento.ShowList(e);
        })    
        .fail(function (e) {
            evento.showError(e);
        })
        .always(function (e){
            $("#evento").selectpicker("refresh");
        });
    }

    // Reload(e){
    //     if (this.id == null)
    //         this.ShowAll(e);
    //     else this.ShowItemData(e);
    // };

    // showInfo() {
    //     //$(".modal").css({ display: "none" });   
    //     $(".close").click();
    //     swal({
    //         position: 'top-end',
    //         type: 'success',
    //         title: 'Good!',
    //         showConfirmButton: false,
    //         timer: 1000
    //     });
    // };

    showError(e) {
        //$(".modal").css({ display: "none" });  
        var data = JSON.parse(e.responseText);
        swal({
            type: 'error',
            title: 'Oops...',
            text: 'Algo no está bien (' + data.code + '): ' + data.msg,
            footer: '<a href>Contacte a Soporte Técnico</a>',
        })
    };

    // ClearCtls() {
    //     $("#id").val('');
    //     $("#nombre").val('');    
    //     $("#url").val('');
    // };

    // ShowAll(e) {
    //     // Limpia el div que contiene la tabla.
    //     $('#tableBody-evento').html("");
    //     // Carga lista
    //     var data = JSON.parse(e);
    //     $.each(data, function (i, item) {
    //         $('#tableBody-evento').append(`
    //             <tr> 
    //                 <td class="a-center ">
    //                     <input type="checkbox" class="flat" name="table_records">
    //                 </td>
    //                 <td class="itemId" style="display: none" >${item.id}</td>
    //                 <td>${item.nombre}</td>
    //                 <td>${item.url}</td>
    //                 <td class=" last"> 
    //                     <a id="update${item.id}" > <i class="glyphicon glyphicon-edit"> </i> Editar </a> 
    //                     <a id="delete${item.id}" > <i class="glyphicon glyphicon-trash"> </i> Eliminar </a> 
    //                 </td>
    //             </tr>
    //         `);
    //         // event Handler
    //         $('#update' + item.id).click(evento.UpdateEventHandler);
    //         $('#delete' + item.id).click(evento.DeleteEventHandler);
    //     })
    // };

    // UpdateEventHandler() {
    //     evento.id = $(this).parents("tr").find(".itemId").text();  //Class itemId = ID del objeto.
    //     evento.Read;
    // };

    ShowList(e) {
        // carga lista con datos.
        var data = JSON.parse(e);
        // Recorre arreglo.
        $.each(data, function (i, item) {
            $('#evento').append(`
                <option value=${item.id}>${item.nombre}</option>
            `);
        })
    };

    // ShowItemData(e) {
    //     // Limpia el controles
    //     this.ClearCtls();    
    //     // carga objeto.
    //     var data = JSON.parse(e)[0];
    //     evento = new evento(data.id, data.nombre, data.url);
    //     // Asigna objeto a controles
    //     $("#id").val(evento.id);
    //     $("#nombre").val(evento.nombre);
    //     $("#url").val(evento.url);
    // };

    // DeleteEventHandler() {
    //     evento.id = $(this).parents("tr").find(".itemId").text();  //Class itemId = ID del objeto.
    //     // Mensaje de borrado:
    //     swal({
    //         title: 'Eliminar?',
    //         text: "Esta acción es irreversible!",
    //         type: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#3085d6',
    //         cancelButtonColor: '#d33',
    //         confirmButtonText: 'Si, eliminar!',
    //         cancelButtonText: 'No, cancelar!',
    //         confirmButtonClass: 'btn btn-success',
    //         cancelButtonClass: 'btn btn-danger'
    //     }).then((result) => {
    //         if (result.value) {
    //             evento.Delete;
    //         }
    //     })
    // };
}

//Class Instance
let evento = new Evento();














