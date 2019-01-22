class Entidad {
    // Constructor
    constructor(id, nombre, idCodigoPais, idTipoIdentificacion, identificacion, nombreComercial, idProvincia, idCanton, idDistrito, idBarrio, otrasSenas,
        idCodigoPaisTel, numTelefono, idCodigoPaisFax, numTelefonoFax, correoElectronico, username, password, certificado, filename, filesize, filetype, estadoCertificado, pinp12, idDocumento) {
        this.id = id || null;
        this.nombre = nombre || '';
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
        this.certificado = certificado || null; //ATV
        this.filename = filename || null;
        this.filesize = filesize || null;
        this.filetype = filetype || null;
        this.estadoCertificado = estadoCertificado || 0;
        this.pinp12 = pinp12 || null;
        this.idDocumento = idDocumento || null;
    };

    get tUpdate() {
        return this.update = "update";
    };

    get tSelect() {
        return this.select = "select";
    };

    set viewEventHandler(_t) {
        this.viewType = _t;
    };

    //Getter
    get read() {
        //NProgress.start();
        var miAccion = this.id == null ? 'readAll' : 'read';
        if (miAccion == 'readAll' && $('#tclientefe tbody').length == 0)
            return;
        $.ajax({
                type: "POST",
                url: "class/entidad.php",
                data: {
                    action: miAccion,
                    id: this.id
                }
            })
            .done(function (e) {
                entidad.reload(e);
            })
            .fail(function (e) {
                entidad.showError(e);
            });
        //.always(NProgress.done());
    };

    get readProfile() {
        //NProgress.start();
        var miAccion = 'readProfile';
        $.ajax({
                type: "POST",
                url: "class/entidad.php",
                data: {
                    action: miAccion
                }
            })
            .done(function (e) {
                entidad.showItemData(e);
            })
            .fail(function (e) {
                entidad.showError(e);
            });
    };

    get APILogin() {
        //NProgress.start();
        var miAccion = 'APILogin';
        $.ajax({
                type: "POST",
                url: "class/entidad.php",
                data: {
                    action: miAccion
                }
            })
            .fail(function (e) {
                entidad.showError(e);
            });
    };

    get readAllTipoIdentificacion() {
        var miAccion = 'readAllTipoIdentificacion';
        $.ajax({
                type: "POST",
                url: "class/entidad.php",
                data: {
                    action: miAccion
                }
            })
            .done(function (e) {
                entidad.showList(e, $('#idTipoIdentificacion'));
                // luego de cargar las listas, lee el clienteFE.

            })
            .fail(function (e) {
                entidad.showError(e);
            });
    };

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
                url: "class/entidad.php",
                data: {
                    action: miAccion,
                    idProvincia: this.idProvincia,
                    idCanton: this.idCanton,
                    idDistrito: this.idDistrito
                }
            })
            .done(function (e) {
                entidad.showListUbicacion(e);
                $("#btnSubmit").removeAttr("disabled");
            })
            .fail(function (e) {
                entidad.showError(e);
            });
    };

    get readAllProvincia() {
        var miAccion = 'readAllProvincia';
        $('#btnSubmit').attr("disabled", "disabled");
        $.ajax({
                type: "POST",
                url: "class/entidad.php",
                data: {
                    action: miAccion
                }
            })
            .done(function (e) {
                entidad.showList(e, $('#idProvincia'));
                $('#idProvincia option[value=' + entidad.idProvincia + ']').prop("selected", true);
                $("#idProvincia").selectpicker("refresh");
                entidad.readAllCanton;
            })
            .fail(function (e) {
                entidad.showError(e);
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
                url: "class/entidad.php",
                data: {
                    action: miAccion,
                    idProvincia: this.idProvincia
                }
            })
            .done(function (e) {
                entidad.showList(e, $('#idCanton'));
                $('#idCanton option[value=' + entidad.idCanton + ']').prop("selected", true);
                $("#idCanton").selectpicker("refresh");
                // modifica la lista de distritos según la selección de cantón.
                entidad.readAllDistrito;
            })
            .fail(function (e) {
                entidad.showError(e);
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
                url: "class/entidad.php",
                data: {
                    action: miAccion,
                    idCanton: this.idCanton
                }
            })
            .done(function (e) {
                entidad.showList(e, $('#idDistrito'));
                $('#idDistrito option[value=' + entidad.idDistrito + ']').prop("selected", true);
                $("#idDistrito").selectpicker("refresh");
                // modifica la lista de barrios según la selección de distrito.
                entidad.readAllBarrio;
            })
            .fail(function (e) {
                entidad.showError(e);
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
                url: "class/entidad.php",
                data: {
                    action: miAccion,
                    idDistrito: this.idDistrito
                }
            })
            .done(function (e) {
                entidad.showList(e, $('#idBarrio'));
                $('#idBarrio option[value=' + entidad.idBarrio + ']').prop("selected", true);
                $("#idBarrio").selectpicker("refresh");
                $("#btnSubmit").removeAttr("disabled");
            })
            .fail(function (e) {
                entidad.showError(e);
            });
    };

    get save() {
        this.idDocumento = $('#idDocumento option:selected').val();
        if (this.idDocumento != 99) {
            if (!testConn.res) {
                swal({
                    type: 'warning',
                    title: 'Conexión...',
                    text: 'Debe probar la conexión antes de guardar'
                });
                return;
            }
        }
        // NProgress.start();        
        var miAccion = this.id == null ? 'create' : 'update';
        this.nombre = $("#nombre").val();
        // codigo de seguridad autogenerado
        var max = 11111111;
        var min = 99999999;
        this.codigoSeguridad = Math.floor(Math.random() * (max - min + 1) + min);
        this.idCodigoPais = '52'; //$("#codigoPais").val(); 52 = 506 Costa Rica.
        this.idTipoIdentificacion = $('#idTipoIdentificacion option:selected').val();
        this.identificacion = $("#identificacion").val();
        this.nombreComercial = $("#nombreComercial").val();
        if ($('#idProvincia option:selected').val() != "null" && $('#idProvincia option:selected').val() != undefined && $('#idProvincia option:selected').val() != 0)
            this.idProvincia = $('#idProvincia option:selected').val();
        else {
            swal({
                type: 'warning',
                title: 'Ubicación...',
                text: 'Debe seleccionar la Provincia'
            });
            return false;
        }
        if ($('#idCanton option:selected').val() != "null" && $('#idCanton option:selected').val() != undefined && $('#idCanton option:selected').val() != 0)
            this.idCanton = $('#idCanton option:selected').val();
        else {
            swal({
                type: 'warning',
                title: 'Ubicación...',
                text: 'Debe seleccionar el Cantón'
            });
            return false;
        }
        if ($('#idDistrito option:selected').val() != "null" && $('#idDistrito option:selected').val() != undefined && $('#idDistrito option:selected').val() != 0)
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
        this.idCodigoPaisTel = '52'; //$("#codigoPais").val(); // mismo código del país.
        this.numTelefono = $("#numTelefono").val();
        this.correoElectronico = $("#correoElectronico").val();
        this.username = $("#username").val();
        this.password = $("#password").val();
        this.pinp12 = $("#pinp12").val();
        //
        if (this.idDocumento != 99) {
            if (this.certificado == null) {
                swal({
                    type: 'warning',
                    title: 'Cerfificado...',
                    text: 'Debe agregar el certificado'
                });
                return false;
            }
        }
        $('#btnSubmit').attr("disabled", "disabled");
        $.ajax({
                type: "POST",
                url: "class/entidad.php",
                data: {
                    action: miAccion,
                    objC: JSON.stringify(this)
                }
            })
            .done(function () {
                // Sube el certificado y crea/actualiza cliente.
                if (dz != undefined)
                    dz.processQueue();
                else // No hay cola para subir.
                    entidad.showInfo();
            })
            .fail(function (e) {
                entidad.showError(e);
            })
            .always(function () {
                $("#btnSubmit").removeAttr("disabled");
                //entidad = new Entidad();
                //entidad.clearCtls();
                //entidad.readProfile;
                //$("#nombre").focus();
                // NProgress.done();
            });
    };

    get delete() {
        $.ajax({
                type: "POST",
                url: "class/entidad.php",
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
                entidad.showError(e);
            })
            .always(function () {
                entidad = new Entidad();
                entidad.read;
            });
    };

    get deleteCertificado() {
        $.ajax({
                type: "POST",
                url: "class/entidad.php",
                data: {
                    action: 'deleteCertificado',
                    certificado: entidad.certificado,
                    id: entidad.id
                }
            })
            .done(function () {
                $('#filelist').html('');
                entidad.certificado = null;
                swal({
                    //
                    type: 'success',
                    title: 'Eliminado!',
                    showConfirmButton: false,
                    timer: 1000
                });
            })
            .fail(function (e) {
                entidad.showError(e);
            });
    };

    get downloadCertificado() {
        $.ajax({
                type: "GET",
                url: "class/downloadCert.php",
                data: {
                    action: 'downloadCertificado',
                    certificado: entidad.certificado,
                    id: entidad.id
                }
            })
            .done(function () {

            })
            .fail(function (e) {
                entidad.showError(e);
            });
        // var xhr = new XMLHttpRequest();
        // xhr.open("GET", "class/downloadCert.php");
        // xhr.send();
    };

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
        } else {
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
        } else {
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
            title: 'Good!',
            showConfirmButton: false,
            timer: 1000
        });
    };

    // Muestra errores en ventana
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

    clearCtls() {
        $("#id").val('');
        $("#nombre").val('');
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
        // $('.update').click(entidad.updateEventHandler);
        // $('.delete').click(entidad.DeleteEventHandler);
        // $('.open').click(entidad.OpenEventHandler);
        // $('#tclientefe tbody tr').click(entidad.viewType==undefined || entidad.viewType==entidad.tUpdate ? entidad.UpdateEventHandler : entidad.SelectEventHandler);
    };

    showItemData(e) {
        // Limpia el controles
        this.clearCtls();
        if (e != "null" && e != "") {
            // carga objeto.
            var data = JSON.parse(e);
            if (data.id == null) {
                entidad.readAllUbicacion;
                return;
            }
            if (data.id == null) return;
            entidad = new Entidad(data.id, data.nombre, data.idCodigoPais, data.idTipoIdentificacion, data.identificacion, data.nombreComercial, data.idProvincia, data.idCanton, data.idDistrito, data.idBarrio, data.otrasSenas, data.idCodigoPaisTel, data.numTelefono, data.idCodigoPaisFax, data.numTelefonoFax, data.correoElectronico, data.username, data.password, data.certificado,
                data.filename, data.filesize, data.filetype, data.estadoCertificado, data.pinp12, data.idDocumento
            );
            // Asigna objeto a controles        
            $("#id").val(entidad.id);
            $("#nombre").val(entidad.nombre);
            $("#entidad").html('<h3>Registro de Contribuyente de Factura Electrónica: ' + $('.call_empresa').text() + '<h3>');
            $("#idCodigoPais").val(entidad.idCodigoPais);
            $('#idTipoIdentificacion option[value=' + entidad.idTipoIdentificacion + ']').prop("selected", true);
            $("#idTipoIdentificacion").selectpicker("refresh");
            $('#idDocumento option[value=' + entidad.idDocumento + ']').prop("selected", true);
            $("#idDocumento").selectpicker("refresh");
            entidad.reglasTipoIdentificacion(entidad.idTipoIdentificacion);
            $("#identificacion").val(entidad.identificacion);
            $("#nombreComercial").val(entidad.nombreComercial);
            // lee las provincias - cantones - distritos - barrios de la provincia seleccionada.
            entidad.readAllProvincia;
            //
            $("#otrasSenas").val(entidad.otrasSenas);
            $("#numTelefono").val(entidad.numTelefono);
            $("#correoElectronico").val(entidad.correoElectronico);
            $("#username").val(entidad.username);
            $("#password").val(entidad.password);
            $("#pinp12").val(entidad.pinp12);
            //
            if (entidad.idDocumento != 99) {
                $('#filelist').append(`
                <div class="btn-group">
                    <button type="button" class="btn ${entidad.estadoCertificado == 1 ? `btn-success` : `btn-danger`}">${entidad.certificado}</button>
                    <button type="button" class="btn ${entidad.estadoCertificado == 1 ? `btn-success` : `btn-danger`} dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <span class="caret"></span>
                         <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    ${entidad.estadoCertificado == 1 ? `
                        <ul class="dropdown-menu" role="menu">
                            <li><a id='certEliminar'>Eliminar</a></li>
                            <li class="divider"></li>
                            <li><a href='class/downloadCert.php?certificado=${entidad.certificado}' id='certDescargar'>Descargar</a></li>
                        </ul>`
                    : ``}
                </div>           
            `).fadeIn();
                if (entidad.estadoCertificado == 0)
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
                            entidad.deleteCertificado;
                        }
                    })

                });
            }
            $('#idDocumento').change();
            // datos sin modificar = conexion valida.
            testConn.res = true;
            // $('#certDescargar').click(function(){
            //     entidad.downloadCertificado;
            // });
            //var mockFile = { name: entidad.filename, size: entidad.filesize, type: 'application/x-pkcs12' };
            // dz.options.addedfile.call(dz, mockFile);
        } else {
            entidad.readAllUbicacion;
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
        // entidad.Init();
        var validator = new FormValidator({
            "events": ['blur', 'input', 'change']
        }, document.forms["frm"]);
    };

    checkUsername() {
        if ($('#username').val() == "")
            return;
        $('#btnSubmit').attr("disabled", "disabled");
        var miAccion = 'checkUsername';
        this.username = $("#username").val();
        $.ajax({
                type: "POST",
                url: "class/entidad.php",
                data: {
                    action: miAccion,
                    username: this.username
                }
            })
            .done(function (e) {
                var data = JSON.parse(e);
                if (data.status == 0) { //0= unico; 1= usado.
                    $('#checkusername').removeClass('fa-times-circle');
                    $('#checkusername').addClass('fa-check-circle');
                    $("#btnSubmit").removeAttr("disabled");
                    // $('#checkusername').text(' Entidad contribuyente.');
                } else {
                    $('#checkusername').removeClass('fa-check-circle');
                    $('#checkusername').addClass('fa-times-circle');
                    $('#checkusername').text(' El contribuyente ya está registrado.');
                }

            })
            .fail(function (e) {
                usuario.showError(e);
            });

    };

    probarConexion(showMess = false) {
        if ($('#username').val() == "" || $('#password').val() == "") {
            // swal({
            //     type: 'warning',
            //     title: 'Conexión...',
            //     text: 'Debe llenar el formulario para probar la conexión.',
            //     footer: '<a href>Contacte a Soporte Técnico</a>',
            // });
            return;
        }
        var miAccion = 'testConnection';
        this.username = $("#username").val();
        this.password = $("#password").val();
        $.ajax({
                type: "POST",
                url: "class/entidad.php",
                data: {
                    action: miAccion,
                    username: this.username,
                    password: this.password,
                    correoElectronico: this.correoElectronico
                }
            })
            .done(function (e) {
                if (e == 'true') {
                    if (showMess)
                        swal({
                            type: 'info',
                            title: 'Conexión...',
                            text: 'Conexión Exitosa!',
                        });
                    testConn.res = true;
                } else {
                    if (showMess)
                        swal({
                            type: 'error',
                            title: 'Conexión...',
                            text: 'Conexión Fallida, revise su usuario y contraseña de ATV.',
                            footer: '<a href>Contacte a Soporte Técnico</a>'
                        });
                    testConn.res = false;
                }

            })
            .fail(function (e) {
                usuario.showError(e);
            });
    };

    init() {
        // validator.js
        var validator = new FormValidator({
            "events": ['blur', 'input', 'change']
        }, document.forms["frm"]);
        $('#frm').submit(function (e) {
            e.preventDefault();
            var validatorResult = validator.checkAll(this);
            if (validatorResult.valid)
                entidad.save;
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
        //
        $('#idDocumento').on('change', function (e) {
            if (this.value == 99) {
                $('#username').prop('readonly', true);
                $('#username').prop('required', false);
                $('#password').prop('readonly', true);
                $('#password').prop('required', false);
                $('#pinp12').prop('readonly', true);
                $('#pinp12').prop('required', false);
                //$('#frmLlave').prop('visibility', false);
                $('#frameLlave').hide();
                $('#btnTest').hide();
            } else {
                $('#username').prop('readonly', false);
                $('#username').prop('required', true);
                $('#password').prop('readonly', false);
                $('#password').prop('required', true);
                $('#pinp12').prop('readonly', false);
                $('#pinp12').prop('required', true);
                //$('#frmLlave').prop('visibility', false);
                $('#frameLlave').show();
                $('#btnTest').show();
            }
            validator.reset();

            //entidad.reglasTipoIdentificacion($(this).val());
        });
        // validaciones segun el tipo de ident.
        $('#idTipoIdentificacion').on('change', function (e) {
            validator.reset();
            entidad.reglasTipoIdentificacion($(this).val());
        });
        // ubicaciones
        $('#idProvincia').on('change', function (e) {
            entidad.readAllCanton;
        });
        $('#idCanton').on('change', function (e) {
            entidad.readAllDistrito;
        });
        $('#idDistrito').on('change', function (e) {
            entidad.readAllBarrio;
        });
        // prueba de conexion// test conexion
        $('#btnTest').click(function () {
            entidad.probarConexion(true);
        });
        $('#username').on('change', function (e) {
            entidad.probarConexion();
        });
        $('#password').on('change', function (e) {
            entidad.probarConexion();
        });
        // Check username
        $('#username').focusout(function () {
            // entidad.checkUsername();
        });
        // dropzone
        Dropzone.options.frmLlave = {
            init: function () {
                this.on("addedfile", function (file) {
                    dz = this;
                    entidad.certificado = dz.files[0].name;
                });
                this.on("complete", function (file) {
                    if (file.xhr.response != 'UPLOADED') {
                        swal({
                            type: 'error',
                            title: 'Oops...',
                            text: 'Ha ocurrido un error al subir el Certificado.',
                            footer: '<a href>Contacte a Soporte Técnico</a>',
                        });
                        $(file.previewElement).addClass('dz-error-message');
                        $('#filelist').html('');
                        entidad.certificado = null;
                    } else entidad.showInfo();
                });
                this.on("error", function (file) {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Certificado con error',
                        footer: '<a href>Contacte a Soporte Técnico</a>',
                    })
                    this.removeFile(file);

                });
                this.on("canceled", function (file) {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Certificado cancelado',
                        footer: '<a href>Contacte a Soporte Técnico</a>',
                    })
                });
                // this.on("queuecomplete", function(file) {
                //     this.removeAllFiles();
                // });
            },
            autoProcessQueue: false,
            acceptedFiles: "application/x-pkcs12",
            maxFiles: 1,
            addRemoveLinks: true,
            autoDiscover: false
        };
        // submit
        $('#btnSubmit').click(function () {
            $('#frm').submit();
        });
    };
}
//Class Instance
var dz;
let entidad = new Entidad();
var testConn = {
    aInternal: false,
    aListener: function (val) {},
    set res(val) {
        this.aInternal = val;
        this.aListener(val);
    },
    get res() {
        return this.aInternal;
    },
    registerListener: function (listener) {
        this.aListener = listener;
    }
}
testConn.registerListener(function (val) {
    if (!val) {
        $("#btnTest").text('Probar Conexión');
        $("#btnTest").attr('class', 'btn btn-danger');
    } else {
        $("#btnTest").text('Conexión Ok!');
        $("#btnTest").attr('class', 'btn btn-success');
    }
});