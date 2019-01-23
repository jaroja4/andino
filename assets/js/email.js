class Email {
    // Constructor
    constructor(id, email_name, email_user, email_password,
        email_SMTPAuth, email_Host, email_port, activa, email_subject, email_SMTPSecure, email_body, email_logo, estadoLogo, html, email_footer) {
        this.id = id || null;
        this.email_name = email_name || '';
        this.email_user = email_user || '';
        this.email_password = email_password || '';
        this.email_SMTPAuth = email_SMTPAuth || 'true';
        this.email_Host = email_Host || '';
        this.email_port = email_port || '';
        this.activa = activa || 1;
        this.email_subject = email_subject || '';
        this.email_SMTPSecure = email_SMTPSecure || 'ssl';
        this.email_body = email_body || '';
        this.email_logo = email_logo || '';
        this.estadoLogo = estadoLogo || false;
        this.html = html || '';
        this.email_footer = email_footer || '';
    };

    get read() {
        $.ajax({
                type: "POST",
                url: "class/email.php",
                data: {
                    action: 'read'
                }
            })
            .done(function (e) {
                if (e != "null")
                    email.showItemData(e);
            })
            .fail(function (e) {
                email.showError(e);
            });
    };

    get save() {
        var miAccion = this.id == null ? 'create' : 'update';
        this.email_name = $("#email_name").val();
        this.email_user =  $("#email_user").val();
        this.email_password = $("#email_password").val();
        this.email_Host = $('#email_Host option:selected').text();
        this.email_port = $("#email_port").val();
        this.email_subject = $("#email_subject").val();
        this.email_SMTPSecure = $("#email_SMTPSecure").val();
        this.email_body = $("#email_body").val();
        this.html = $("#html").val();
        this.email_footer = $("#email_footer").val();
        //        
        // if (this.email_logo == null) {
        //     swal({
        //         type: 'warning',
        //         title: 'Logo...',
        //         text: 'Debe agregar la imagen del logo'
        //     });
        //     return false;
        // }
        $('#btnSubmit').attr("disabled", "disabled");
        $.ajax({
                type: "POST",
                url: "class/email.php",
                data: {
                    action: miAccion,
                    obj: JSON.stringify(this)
                }
            })
            .done(function (e) {
                // Sube el email_logo y crea/actualiza cliente.
                if (dz != undefined)
                    dz.processQueue();
                else // No hay cola para subir.
                    email.showInfo();
                email.id = JSON.parse(e);
            })
            .fail(function (e) {
                email.showError(e);
            })
            .always(function () {
                $("#btnSubmit").removeAttr("disabled");
                //email = new email();
                //email.clearCtls();
                //email.readProfile;
                //$("#email_name").focus();
                // NProgress.done();
            });
    };

    // Muestra información en ventana
    showInfo() {
        //$(".modal").css({ display: "none" });   
        $(".close").click();
        swal({

            type: 'success',
            title: 'Good!',
            showConfirmButton: false,
            timer: 750
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
        $("#email_name").val('');
        $("#email_user").val('');
        $("#email_password").val('');
        $('#email_Host option').prop("selected", false);
        $("#email_Host").selectpicker("refresh");        
        $("#email_port").val('');
        $("#email_subject").val('');
        $("#email_SMTPSecure").val('');
        $("#email_SMTPAuth").val('');
        $("#email_body").val('');
        $("#html").val('');
        $("#email_footer").val('');
        $("#filelist").html('');
        if (dz != undefined)
            dz.removeAllFiles();
    };

    showItemData(e) {
        // Limpia el controles
        this.clearCtls();
        if (e != "null" && e != "") {
            // carga objeto.
            var data = JSON.parse(e);
            if(data.id==null) return;
            email = new Email(data.id, data.email_name, data.email_user, data.email_password, data.email_SMTPAuth, data.email_Host, data.email_port, data.activa, data.email_subject, data.email_SMTPSecure,
               data.email_body, data.email_logo, data.estadoLogo, data.html, data.email_footer);
            // Asigna objeto a controles        
            //$("#id").val(email.id);
            $("#email_name").val(email.email_name);
            $("#email_user").val(email.email_user);
            $("#email_password").val(email.email_password);
            $("#email_SMTPAuth").val(email.email_SMTPAuth);
            email.email_Host = email.email_Host == 'smtp.gmail.com' ? 1 : 2;
            $('#email_Host option[value=' + email.email_Host + ']').prop("selected", true);
            $("#email_Host").selectpicker("refresh");
            $("#email_port").val(email.email_port);
            $("#email_subject").val(email.email_subject);
            $("#email_SMTPSecure").val(email.email_SMTPSecure);
            $("#email_body").val(email.email_body);
            $("#html").val(email.html);
            $("#email_footer").val(email.email_footer);
            //            
            $('#filelist').append(`
                <div class="btn-group">
                    <button type="button" class="btn ${email.estadoLogo == 1 ? `btn-success` : `btn-danger`}">${email.email_logo}</button>
                    <button type="button" class="btn ${email.estadoLogo == 1 ? `btn-success` : `btn-danger`} dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <span class="caret"></span>
                         <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    ${email.estadoLogo == 1 ? `
                        <ul class="dropdown-menu" role="menu">
                            <li><a id='certEliminar'>Eliminar</a></li>
                            <li class="divider"></li>
                            <li><a href='class/downloadCert.php?email_logo=${email.email_logo}' id='certDescargar'>Descargar</a></li>
                        </ul>`
                    : ``}
                </div>           
            `).fadeIn();
            if (email.estadoLogo == 0)
                swal({
                    type: 'error',
                    title: 'Oops...',
                    text: 'Ha ocurrido un error al localizar el email_logo.',
                    footer: '<a href>Contacte a Soporte Técnico</a>',
                });
            // eventos
            // $('#certEliminar').click(function () {
            //     swal({
            //         title: 'Eliminar email_logo?',
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
            //         // elimina email_logo del servidor
            //         if (result.value) {
            //             email.deleteemail_logo;
            //         }
            //     })

            // });
            // $('#certDescargar').click(function(){
            //     email.downloademail_logo;
            // });
            //var mockFile = { name: email.filename, size: email.filesize, type: 'application/x-pkcs12' };
            // dz.options.addedfile.call(dz, mockFile);
        }
    };    

    init() {
        // validator.js
        var validator = new FormValidator({ "events": ['blur', 'input', 'change'] }, document.forms["frm"]);
        $('#frm').submit(function (e) {
            e.preventDefault();
            var validatorResult = validator.checkAll(this);
            if (validatorResult.valid)
                email.save;
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
        // dropzone
        Dropzone.options.frmLogo = {
            init: function () {
                this.on("addedfile", function (file) {
                    dz = this;
                    email.email_logo = dz.files[0].name;
                });
                this.on("complete", function (file) {
                    if (file.xhr.response != 'UPLOADED') {
                        swal({
                            type: 'error',
                            title: 'Oops...',
                            text: 'Ha ocurrido un error al subir la imagen.',
                            footer: '<a href>Contacte a Soporte Técnico</a>',
                        });
                        $(file.previewElement).addClass('dz-error-message');
                        $('#filelist').html('');
                        email.email_logo = null;
                    }
                    else email.showInfo();
                });
                this.on("error", function (file) {                    
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Imagen con error',
                        footer: '<a href>Contacte a Soporte Técnico</a>',
                    })
                    this.removeFile(file);

                });
                this.on("canceled", function (file) {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Imagen cancelada',
                        footer: '<a href>Contacte a Soporte Técnico</a>',
                    })
                });
                // this.on("queuecomplete", function(file) {
                //     this.removeAllFiles();
                // });
            },
            autoProcessQueue: false,
            acceptedFiles: "image/png",
            maxFiles: 1,
            addRemoveLinks: true,
            autoDiscover: false
        };
        // submit
        $('#btnSubmit').click(function () {
            $('#frm').submit();
        });
        // prueba
        $("#btnPrueba").click(function () {

            // email.id = $("#idFactura").text();
            swal({
                title: "Correo electrónico a enviar",
                input: "text",
                showCancelButton: true,
                confirmButtonColor: "#1FAB45",
                confirmButtonText: "Enviar",
                cancelButtonText: "Cancelar",
                buttonsStyling: true,
                // preConfirm: (iput_mail) => {
                //     email.extraMails = iput_mail;
                // },
                preConfirm: function (iput_mail) {
                    return new Promise(function (resolve) {
    
                        email.extraMails = iput_mail;
                        $.ajax({
                            type: "POST",
                            url: "class/email.php",
                            data: {
                                action: "test",
                                mailAddress: JSON.stringify(email.extraMails)
                            },
                            cache: false,
                            success: function (response) {
                                swal({
                                    position: 'top-end',
                                    type: 'success',
                                    title: 'Prueba enviada!',
                                    showConfirmButton: false,
                                    timer: 750
                                })
                            },
                            failure: function (response) {
                                swal(
                                    "Internal Error",
                                    "Oops, el correo no fue enviado.", 
                                    "error"
                                )
                            }
                        });
                    });
                },
                allowOutsideClick: false
            });
        });
    };

}
let email = new Email();
var dz;