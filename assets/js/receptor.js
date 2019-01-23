class Receptor {
    // Constructor
    constructor(id, nombre, codigoSeguridad, idCodigoPais, idTipoIdentificacion, identificacion, nombreComercial, idProvincia, idCanton, idDistrito, idBarrio, otrasSenas,
        idCodigoPaisTel, numTelefono, idCodigoPaisFax, numTelefonoFax, correoElectronico, username, password, certificado, filename, filesize, filetype, estadoCertificado, pinp12, identificacionExtranjero) {
        this.id = id || null;
        this.nombre = nombre || '';
        this.codigoSeguridad = codigoSeguridad || '';
        this.idCodigoPais = idCodigoPais || '';
        this.idTipoIdentificacion = idTipoIdentificacion || '';
        this.identificacion = identificacion || '';
        this.nombreComercial = nombreComercial || '';
        this.idProvincia = idProvincia || null;
        this.idCanton = idCanton || null;
        this.idDistrito = idDistrito || null;
        this.idBarrio = idBarrio || null;
        this.otrasSenas = otrasSenas || null;
        this.idCodigoPaisTel = idCodigoPaisTel || null;
        this.numTelefono = numTelefono || null;
        this.idCodigoPaisFax = idCodigoPaisFax || null;
        this.numTelefonoFax = numTelefonoFax || null;
        this.correoElectronico = correoElectronico || null;
        this.username = username || null; //ATV
        this.password = password || null; //ATV
        this.certificado = certificado || null;       //ATV
        this.filename = filename || null;
        this.filesize = filesize || null;
        this.filetype = filetype || null;
        this.estadoCertificado = estadoCertificado || 0;
        this.pinp12 = pinp12 || null;
        this.identificacionExtranjero = identificacionExtranjero || null;
        
    }

    get tUpdate() {
        return this.update = "update";
    }

    get tSelect() {
        return this.select = "select";
    }

    set viewEventHandler(_t) {
        this.viewType = _t;
    }

    //Getter
    get read() {
        //NProgress.start();
        var miAccion = this.id == null ? 'readAll' : 'read';
        if (miAccion == 'readAll' && $('#tclientefe tbody').length == 0)
            return;
        $.ajax({
            type: "POST",
            url: "class/receptor.php",
            data: {
                action: miAccion,
                id: this.id
            }
        })
            .done(function (e) {
                receptor.reload(e);
            })
            .fail(function (e) {
                receptor.showError(e);
            });
        //.always(NProgress.done());
    }

    get readProfile() {
        //NProgress.start();
        var miAccion = 'readProfile';
        $.ajax({
            type: "POST",
            url: "class/receptor.php",
            data: {
                action: miAccion
            }
        })
            .done(function (e) {
                receptor.showItemData(e);
            })
            .fail(function (e) {
                receptor.showError(e);
            });
    }

    get readAllTipoIdentificacion() {
        var miAccion = 'readAllTipoIdentificacion';
        $.ajax({
            type: "POST",
            url: "class/receptor.php",
            data: {
                action: miAccion
            }
        })
            .done(function (e) {
                receptor.showList(e, $('#idTipoIdentificacion'));
                // luego de cargar las listas, lee el clienteFE.

            })
            .fail(function (e) {
                receptor.showError(e);
            });
    }

    get readAllUbicacion() {
        this.idProvincia = $('#idProvincia option:selected').val() || 1;
        this.idCanton = $('#idCanton option:selected').val() || 1;
        this.idDistrito = $('#idDistrito option:selected').val() || 1;
        $('#idProvincia').html("");
        $('#idCanton').html("");
        $('#idDistrito').html("");
        $('#idBarrio').html("");
        $('#idProvincia').attr("title", "Cargando ...");
        $('#idCanton').attr("title", "Cargando ...");
        $('#idDistrito').attr("title", "Cargando ...");
        $('#idBarrio').attr("title", "Cargando ...");
        $('#idProvincia').selectpicker("refresh");
        $('#idCanton').selectpicker("refresh");
        $('#idDistrito').selectpicker("refresh");
        $('#idBarrio').selectpicker("refresh");
        var miAccion = 'readAllUbicacion';
        $('#btnSubmit').attr("disabled", "disabled");
        $.ajax({
            type: "POST",
            url: "class/receptor.php",
            data: {
                action: miAccion,
                idProvincia: this.idProvincia,
                idCanton: this.idCanton,
                idDistrito: this.idDistrito
            }
        })
            .done(function (e) {
                receptor.showListUbicacion(e);
                $("#btnSubmit").removeAttr("disabled");
            })
            .fail(function (e) {
                receptor.showError(e);
            });
    };

    get readAllProvincia() {
        var miAccion = 'readAllProvincia';
        $('#btnSubmit').attr("disabled", "disabled");
        $.ajax({
            type: "POST",
            url: "class/receptor.php",
            data: {
                action: miAccion
            }
        })
            .done(function (e) {
                receptor.showList(e, $('#idProvincia'));
                $('#idProvincia option[value=' + receptor.idProvincia + ']').prop("selected", true);
                $("#idProvincia").selectpicker("refresh");
                receptor.readAllCanton;
            })
            .fail(function (e) {
                receptor.showError(e);
            });
    };

    get readAllCanton() {
        $('#idCanton').html("");
        $('#idDistrito').html("");
        $('#idBarrio').html("");
        $('#idCanton').attr("title", "Cargando ...");
        $('#idDistrito').attr("title", "Cargando ...");
        $('#idBarrio').attr("title", "Cargando ...");
        $('#idCanton').selectpicker("refresh");
        $('#idDistrito').selectpicker("refresh");
        $('#idBarrio').selectpicker("refresh");
        var miAccion = 'readAllCanton';
        this.idProvincia = $('#idProvincia option:selected').val() || 1;
        $.ajax({
            type: "POST",
            url: "class/receptor.php",
            data: {
                action: miAccion,
                idProvincia: this.idProvincia
            }
        })
            .done(function (e) {
                receptor.showList(e, $('#idCanton'));
                $('#idCanton option[value=' + receptor.idCanton + ']').prop("selected", true);
                $("#idCanton").selectpicker("refresh");
                // modifica la lista de distritos según la selección de cantón.
                receptor.readAllDistrito;
            })
            .fail(function (e) {
                receptor.showError(e);
            });
    };

    get readAllDistrito() {
        $('#idDistrito').html("");
        $('#idBarrio').html("");
        $('#idDistrito').attr("title", "Cargando ...");
        $('#idBarrio').attr("title", "Cargando ...");
        $('#idDistrito').selectpicker("refresh");
        $('#idBarrio').selectpicker("refresh");
        var miAccion = 'readAllDistrito';
        this.idCanton = $('#idCanton option:selected').val() || 1;
        $.ajax({
            type: "POST",
            url: "class/receptor.php",
            data: {
                action: miAccion,
                idCanton: this.idCanton
            }
        })
            .done(function (e) {
                receptor.showList(e, $('#idDistrito'));
                $('#idDistrito option[value=' + receptor.idDistrito + ']').prop("selected", true);
                $("#idDistrito").selectpicker("refresh");
                // modifica la lista de barrios según la selección de distrito.
                receptor.readAllBarrio;
            })
            .fail(function (e) {
                receptor.showError(e);
            });
    };

    get readAllBarrio() {
        $('#idBarrio').html("");
        $('#idBarrio').attr("title", "Cargando ...");
        $('#idBarrio').selectpicker("refresh");
        var miAccion = 'readAllBarrio';
        this.idDistrito = $('#idDistrito option:selected').val() || 1;
        $.ajax({
            type: "POST",
            url: "class/receptor.php",
            data: {
                action: miAccion,
                idDistrito: this.idDistrito
            }
        })
            .done(function (e) {
                receptor.showList(e, $('#idBarrio'));
                $('#idBarrio option[value=' + receptor.idBarrio + ']').prop("selected", true);
                $("#idBarrio").selectpicker("refresh");
                $("#btnSubmit").removeAttr("disabled");
            })
            .fail(function (e) {
                receptor.showError(e);
            });
    };

    get save() {
        // NProgress.start();        
        var miAccion = this.id == null ? 'create' : 'update';
        this.nombre = $("#nombre").val();
        this.codigoSeguridad = $("#codigoSeguridad").val();
        this.idCodigoPais = '52'; //$("#codigoPais").val(); 52 = 506 Costa Rica.
        this.idTipoIdentificacion = $('#idTipoIdentificacion option:selected').val();
        this.identificacion = $("#identificacion").val();
        this.nombreComercial = $("#nombreComercial").val();
        if ($('#idProvincia option:selected').val() != "null" && $('#idProvincia option:selected').val() != undefined)
            this.idProvincia = $('#idProvincia option:selected').val();
        else {
            swal({
                type: 'warning',
                title: 'Ubicación...',
                text: 'Debe seleccionar la Provincia'
            });
            return false;
        }
        if ($('#idCanton option:selected').val() != "null" && $('#idCanton option:selected').val() != undefined)
            this.idCanton = $('#idCanton option:selected').val();
        else {
            swal({
                type: 'warning',
                title: 'Ubicación...',
                text: 'Debe seleccionar el Cantón'
            });
            return false;
        }
        if ($('#idDistrito option:selected').val() != "null" && $('#idDistrito option:selected').val() != undefined)
            this.idDistrito = $('#idDistrito option:selected').val();
        else {
            swal({
                type: 'warning',
                title: 'Ubicación...',
                text: 'Debe seleccionar el Cantón'
            });
            return false;
        }
        this.idBarrio = $('#idBarrio option:selected').val();
        this.otrasSenas = $("#otrasSenas").val();
        this.idCodigoPaisTel = '52';//$("#codigoPais").val(); // mismo código del país.
        this.numTelefono = $("#numTelefono").val();
        this.correoElectronico = $("#correoElectronico").val();
        this.username = $("#username").val();
        this.password = $("#password").val();
        this.pinp12 = $("#pinp12").val();
        //        
        $('#btnSubmit').attr("disabled", "disabled");
        receptor.showInfo();
    }

    get readIdentificacionReceptor() {
        var searchIdentificacion = $("#lbl_cedulaReceptor").val();
        var miAccion = 'readIdentificacionReceptor';
        if (searchIdentificacion.length>8){
            $.ajax({
                type: "POST",
                url: "class/receptor.php",
                data: {
                    action: miAccion,
                    identificacion: searchIdentificacion
                }
                })
            .done(function (e) {
                receptor.setReceptor(e);
            })
            .fail(function (e) {
                receptor.showError(e);
            });
        }
    }

    setReceptor(e){
        if (e!= "null"){
            var dataReceptor = JSON.parse(e);
            $('#nombre').val(dataReceptor.nombre);        
            
            $('#idTipoIdentificacion option[value=' + dataReceptor.idtipoidentificacion + ']').prop("selected", true);
            $("#idTipoIdentificacion").selectpicker("refresh");
            receptor.reglasTipoIdentificacion(dataReceptor.idtipoidentificacion);
    
            $('#identificacion').val(dataReceptor.identificacion);
            $('#otrasSenas').val(dataReceptor.otrasSenas);
            $('#numTelefono').val(dataReceptor.numtelefono);        
            $('#correoElectronico').val(dataReceptor.correoelectronico);
    
            receptor.id = dataReceptor.id;
            receptor.idProvincia = dataReceptor.idProvincia;
            receptor.idCanton = dataReceptor.idCanton;
            receptor.idDistrito = dataReceptor.idDistrito;
            receptor.idBarrio = dataReceptor.idBarrio;
            receptor.readAllProvincia;

            $("#btnSubmit").removeAttr("disabled");
        }
        else{
            swal({
                    type: 'warning',
                    title: 'Receptor no encontrado',
                    text: 'Debe ingresar los datos del receptor'
                });
            return false;
            }
    }    


///////////BK=
    // get save() {
    //     // NProgress.start();        
    //     var miAccion = this.id == null ? 'create' : 'update';
    //     this.nombre = $("#nombre").val();
    //     this.codigoSeguridad = $("#codigoSeguridad").val();
    //     this.idCodigoPais = '52'; //$("#codigoPais").val(); 52 = 506 Costa Rica.
    //     this.idTipoIdentificacion = $('#idTipoIdentificacion option:selected').val();
    //     this.identificacion = $("#identificacion").val();
    //     this.nombreComercial = $("#nombreComercial").val();
    //     if ($('#idProvincia option:selected').val() != "null" && $('#idProvincia option:selected').val() != undefined)
    //         this.idProvincia = $('#idProvincia option:selected').val();
    //     else {
    //         swal({
    //             type: 'warning',
    //             title: 'Ubicación...',
    //             text: 'Debe seleccionar la Provincia'
    //         });
    //         return false;
    //     }
    //     if ($('#idCanton option:selected').val() != "null" && $('#idCanton option:selected').val() != undefined)
    //         this.idCanton = $('#idCanton option:selected').val();
    //     else {
    //         swal({
    //             type: 'warning',
    //             title: 'Ubicación...',
    //             text: 'Debe seleccionar el Cantón'
    //         });
    //         return false;
    //     }
    //     if ($('#idDistrito option:selected').val() != "null" && $('#idDistrito option:selected').val() != undefined)
    //         this.idDistrito = $('#idDistrito option:selected').val();
    //     else {
    //         swal({
    //             type: 'warning',
    //             title: 'Ubicación...',
    //             text: 'Debe seleccionar el Cantón'
    //         });
    //         return false;
    //     }
    //     this.idBarrio = $('#idBarrio option:selected').val();
    //     this.otrasSenas = $("#otrasSenas").val();
    //     this.idCodigoPaisTel = '52';//$("#codigoPais").val(); // mismo código del país.
    //     this.numTelefono = $("#numTelefono").val();
    //     this.correoElectronico = $("#correoElectronico").val();
    //     this.username = $("#username").val();
    //     this.password = $("#password").val();
    //     this.pinp12 = $("#pinp12").val();
    //     //        
        
    //     $('#btnSubmit').attr("disabled", "disabled");
    //     $.ajax({
    //         type: "POST",
    //         url: "class/receptor.php",
    //         data: {
    //             action: miAccion,
    //             objC: JSON.stringify(this)
    //         }
    //     })
    //         .done(function () {
    //             // Sube el certificado y crea/actualiza cliente.
    //             if (dz != undefined)
    //                 dz.processQueue();
    //             else // No hay cola para subir.
    //                 receptor.showInfo();
    //         })
    //         .fail(function (e) {
    //             receptor.showError(e);
    //         })
    //         .always(function () {
    //             $("#btnSubmit").removeAttr("disabled");
    //             receptor = new Receptor();
    //             // receptor.clearCtls();
    //             receptor.readProfile;
    //             //$("#nombre").focus();
    //             // NProgress.done();
    //         });
    // }



    get delete() {
        $.ajax({
            type: "POST",
            url: "class/receptor.php",
            data: {
                action: 'delete',
                id: this.id
            }
        })
            .done(function () {
                swal({
                    //
                    type: 'success',
                    title: 'Eliminado!',
                    showConfirmButton: false,
                    timer: 1000
                });
            })
            .fail(function (e) {
                receptor.showError(e);
            })
            .always(function () {
                receptor = new Receptor();
                receptor.read;
            });
    }

    get deleteCertificado() {
        $.ajax({
            type: "POST",
            url: "class/receptor.php",
            data: {
                action: 'deleteCertificado',
                certificado: receptor.certificado,
                id: receptor.id
            }
        })
            .done(function () {
                $('#filelist').html('');
                receptor.certificado = null;
                swal({
                    //
                    type: 'success',
                    title: 'Eliminado!',
                    showConfirmButton: false,
                    timer: 1000
                });
            })
            .fail(function (e) {
                receptor.showError(e);
            });
    }


    // Methods
    reload(e) {
        if (this.id == null)
            this.showAll(e);
        else this.showItemData(e);
    };

    showListUbicacion(e) {
        if (e != '[]') {
            // carga lista con datos.
            var data = JSON.parse(e);
            // Recorre arreglo.
            var selector;
            $.each(data, function (i, item) {
                switch (i) {
                    case 0:
                        selector = $('#idProvincia');
                        break;
                    case 1:
                        selector = $('#idCanton');
                        break;
                    case 2:
                        selector = $('#idDistrito');
                        break;
                    case 3:
                        selector = $('#idBarrio');
                        break;
                }
                selector.html('');
                $.each(item, function (n, d) {
                    selector.append(`
                        <option value=${d.id} ${n == 0 ? `selected` : ``}> ${d.value}</option>
                    `);
                })
                selector.selectpicker("refresh");
            })
        }
        else {
            swal({
                type: 'error',
                title: 'Oops...',
                text: 'Algo no está bien, La lista de ubicaciones no puede ser cargada',
                footer: '<a href>Contacte a Soporte Técnico</a>',
            })
        }
    };

    showList(e, selector) {
        if (e != '[]' && e != "") {
            // carga lista con datos.
            var data = JSON.parse(e);
            //selector.html('<option value=null >Sin seleccionar </option>');
            // Recorre arreglo.
            selector.html('');
            $.each(data, function (i, item) {
                selector.append(`
                    <option value=${item.id} ${i == 0 ? `selected` : ``} >${item.value}</option>
                `);
            })
            selector.selectpicker("refresh");
        }
        else {
            swal({
                type: 'error',
                title: 'Oops...',
                text: 'Algo no está bien, La lista no puede ser cargada',
                footer: '<a href>Contacte a Soporte Técnico</a>',
            })
        }
    };

    // Muestra información en ventana
    showInfo() {
        //$(".modal").css({ display: "none" });   
        $(".close").click();
        swal({

            type: 'success',
            title: 'Cliente Agregado!',
            showConfirmButton: false,
            timer: 1500
        });
    };

    // Muestra errores en ventana
    showError(e) {
        //$(".modal").css({ display: "none" });  
        var data = JSON.parse(e.responseText);
        if(session.in(data))
            swal({
                type: 'error',
                title: 'Oops...',
                text: 'Algo no está bien (' + data.code + '): ' + data.msg,
                footer: '<a href>Contacte a Soporte Técnico</a>',
            });
    };

    clearCtls() {
        $("#id").val('');
        $("#nombre").val('');
        $("#codigoSeguridad").val('');
        $("#idCodigoPais").val('');
        $('#idTipoIdentificacion option').prop("selected", false);
        $("#idTipoIdentificacion").selectpicker("refresh");
        $("#identificacion").val('');
        $("#nombreComercial").val('');
        $('#idProvincia option').prop("selected", false);
        $('#idCanton option').prop("selected", false);
        $('#idDistrito option').prop("selected", false);
        $('#idBarrio option').prop("selected", false);
        $("#otrasSenas").val('');
        $("#numTelefono").val('');
        $("#correoElectronico").val('');
        $("#username").val('');
        $("#password").val('');
        $("#pinp12").val('');
        $("#filelist").html('');
        if (dz != undefined)
            dz.removeAllFiles();
    };

    showAll(e) {
        var t = $('#tclientefe').DataTable();
        t.clear();
        t.rows.add(JSON.parse(e));
        t.draw();
        // $('.update').click(receptor.updateEventHandler);
        // $('.delete').click(receptor.DeleteEventHandler);
        // $('.open').click(receptor.OpenEventHandler);
        // $('#tclientefe tbody tr').click(receptor.viewType==undefined || receptor.viewType==receptor.tUpdate ? receptor.UpdateEventHandler : receptor.SelectEventHandler);
    };

    showItemData(e) {
        // Limpia el controles
        //this.clearCtls();
        if (e != "") {
            // carga objeto.
            var data = JSON.parse(e);
            receptor = new Receptor(data.id, data.nombre, data.codigoSeguridad, data.idCodigoPais, data.idTipoIdentificacion, data.identificacion, data.nombreComercial, data.idProvincia, data.idCanton, data.idDistrito, data.idBarrio, data.otrasSenas, data.
                idCodigoPaisTel, data.numTelefono, data.idCodigoPaisFax, data.numTelefonoFax, data.correoElectronico, data.username, data.password, data.certificado, 
                data.filename, data.filesize, data.filetype, data.estadoCertificado, data.pinp12
            );
            // Asigna objeto a controles        
            $("#id").val(receptor.id);
            $("#nombre").val(receptor.nombre);
            $("#receptor").html('<h3>Registro de Receptor de Factura Electrónica: ' + $('.call_empresa').text() + '<h3>');
            $("#codigoSeguridad").val(receptor.codigoSeguridad);
            $("#idCodigoPais").val(receptor.idCodigoPais);
            $('#idTipoIdentificacion option[value=' + receptor.idTipoIdentificacion + ']').prop("selected", true);
            $("#idTipoIdentificacion").selectpicker("refresh");
            receptor.reglasTipoIdentificacion(receptor.idTipoIdentificacion);
            $("#identificacion").val(receptor.identificacion);
            $("#nombreComercial").val(receptor.nombreComercial);
            // lee las provincias - cantones - distritos - barrios de la provincia seleccionada.
            receptor.readAllProvincia;
            //
            $("#otrasSenas").val(receptor.otrasSenas);
            $("#numTelefono").val(receptor.numTelefono);
            $("#correoElectronico").val(receptor.correoElectronico);
            $("#username").val(receptor.username);
            $("#password").val(receptor.password);
            $("#pinp12").val(receptor.pinp12);
            //            
            $('#filelist').append(`
                <div class="btn-group">
                    <button type="button" class="btn ${receptor.estadoCertificado == 1 ? `btn-success` : `btn-danger`}">${receptor.certificado}</button>
                    <button type="button" class="btn ${receptor.estadoCertificado == 1 ? `btn-success` : `btn-danger`} dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    ${receptor.estadoCertificado == 1 ? `
                        <ul class="dropdown-menu" role="menu">
                            <li><a id='certEliminar'>Eliminar</a></li>
                            <li class="divider"></li>
                            <li><a href='class/downloadCert.php?certificado=${receptor.certificado}' id='certDescargar'>Descargar</a></li>
                        </ul>`
                    : ``}
                </div>           
            `).fadeIn();
            if (receptor.estadoCertificado == 0)
                swal({
                    type: 'error',
                    title: 'Oops...',
                    text: 'Ha ocurrido un error al localizar el Certificado.',
                    footer: '<a href>Contacte a Soporte Técnico</a>',
                });
            // eventos
            $('#certEliminar').click(function () {
                swal({
                    title: 'Eliminar Certificado?',
                    text: "Esta acción es irreversible!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Si, eliminar!',
                    cancelButtonText: 'No, cancelar!',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonClass: 'btn btn-danger'
                }).then((result) => {
                    // elimina certificado del servidor
                    if (result.value) {
                        receptor.deleteCertificado;
                    }
                })

            });
            // $('#certDescargar').click(function(){
            //     receptor.downloadCertificado;
            // });
            //var mockFile = { name: receptor.filename, size: receptor.filesize, type: 'application/x-pkcs12' };
            // dz.options.addedfile.call(dz, mockFile);
        }
        else {
            receptor.readAllUbicacion;
        }
    };

    reglasTipoIdentificacion(opt) {
        var p, lr, ph;
        switch (opt) {
            case '1': // física
                p = "([0-9])";
                lr = "9,9";
                ph = "9 digitos, sin cero al inicio y sin guiones.";
                break;
            case '2': // jurídica
                p = "([0-9]){9,10}$";
                lr = "10,10";
                ph = "10 digitos y sin guiones.";
                break;
            case '3': // DIMEX
                p = "([0-9]){9,10}$";
                lr = "11,12";
                ph = "11 o 12 digitos, sin ceros al inicio y sin guiones.";
                break;
            case '4': // NITE
                p = "([0-9]){10,10}$";
                lr = "10,10";
                ph = "10 digitos y sin guiones.";
                break;
        }
        $('#identificacion').attr('pattern', p);
        $('#identificacion').attr('data-validate-length-range', lr);
        $('#identificacion').attr('placeholder', ph);
        // receptor.Init();
        var validator = new FormValidator({ "events": ['blur', 'input', 'change'] }, document.forms["frm"]);
    }


    CheckidReceptor() {
        if ($('#identificacion').val() == "")
            return;
        $('#btnSubmit').attr("disabled", "disabled");
        var miAccion = 'CheckidReceptor';
        this.identificacion = $("#identificacion").val();
        $.ajax({
            type: "POST",
            url: "class/receptor.php",
            data: {
                action: miAccion,
                identificacion: this.identificacion
            }
        })
            .done(function (e) {
                var data = JSON.parse(e);
                if (data.status == 0) {//0= unico; 1= usado.
                    $('#checkIdentificacion').removeClass('fa-times-circle');
                    $('#checkIdentificacion').addClass('fa-check-circle');
                    $("#btnSubmit").removeAttr("disabled");
                    $('#checkIdentificacion').text(' Valida.');
                }
                else {
                    $('#checkIdentificacion').removeClass('fa-check-circle');
                    $('#checkIdentificacion').addClass('fa-times-circle');
                    $('#checkIdentificacion').text(' Identificación Repetida.');
                }

            })
            .fail(function (e) {
                receptor.showError(e);
            });

    }


    init() {
        // validator.js
        var validator = new FormValidator({ "events": ['blur', 'input', 'change'] }, document.forms["frm"]);
        $('#frm').submit(function (e) {
            e.preventDefault();
            var validatorResult = validator.checkAll(this);
            if (validatorResult.valid)
                receptor.save;
            return false;
        });
        // on form "reset" event
        document.forms["frm"].onreset = function (e) {
            validator.reset();
        }
        //NProgress
        $(function () {
            $(document)
                .ajaxStart(NProgress.start)
                .ajaxStop(NProgress.done);
        });
        // Btn disable
        window.onbeforeunload = function () {
            $("input[type=button], input[type=submit]").attr("disabled", "disabled");
        };
        // validaciones segun el tipo de ident.
        $('#idTipoIdentificacion').on('change', function (e) {
            validator.reset();
            receptor.reglasTipoIdentificacion($(this).val());
        });
        // ubicaciones
        $('#idProvincia').on('change', function (e) {
            receptor.readAllCanton;
        });
        $('#idCanton').on('change', function (e) {
            receptor.readAllDistrito;
        });
        $('#idDistrito').on('change', function (e) {
            receptor.readAllBarrio;
        });

        // Check idReceptor
        $('#identificacion').focusout(function () {
            receptor.CheckidReceptor();
        });
        
        // submit
        // $('#btnSubmit').click(function () {
        //     $('#frm').submit();
        // });
        // $('#btnEliminar').click(function () {

        // });
    };

}
//Class Instance
var dz;
let receptor = new Receptor();