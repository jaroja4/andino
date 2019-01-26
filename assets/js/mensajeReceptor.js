class MensajeReceptor {
    // Constructor
    constructor(id, clave, identificacion, montoImpuesto, totalComprobante, identificacionReceptor, consecutivoFE, mensaje, detalle) {
        this.id = id || null;
        this.clave = clave || '';
        this.identificacion = identificacion || '';
        this.montoImpuesto = montoImpuesto || '';
        this.totalComprobante = totalComprobante || '';
        this.identificacionReceptor = identificacionReceptor || '';
        this.consecutivoFE = consecutivoFE || null;
        this.mensaje = mensaje || null;
        this.detalle = detalle || null;
    }

    // get Save() {
    //     // NProgress.start();        
    //     var miAccion = this.id == null ? 'Create' : 'Update';
    //     this.mensaje = $('#tipoMensaje option:selected').val();
    //     this.detalle = $("#detalle").val();
    //     $('#btnSubmit').attr("disabled", "disabled");
    //     $.ajax({
    //         type: "POST",
    //         url: "class/mensajeReceptor.php",
    //         data: {
    //             action: miAccion,
    //             obj: JSON.stringify(this)
    //         }
    //     })
    //         .done(function(){
    //             if(dz!=undefined)
    //                 dz.processQueue();
    //             else // No hay cola para subir.
    //                 mr.showInfo();
    //         })
    //         .fail(function (e) {
    //             mr.showError(e);
    //         })
    //         .always(function () {
    //             $("#btnSubmit").removeAttr("disabled");
    //         });
    // }

    // Muestra información en ventana
    showInfo() {
        //$(".modal").css({ display: "none" });   
        $(".close").click();
        swal({
            type: 'success',
            title: 'Listo!',
            showConfirmButton: false,
            timer: 1000
        });
    };

    // Muestra errores en ventana
    showError(e) {
        //$(".modal").css({ display: "none" });  
        var data = JSON.parse(e.responseText);
        session.in(data);
        swal({
            type: 'error',
            title: 'Oops...',
            text: 'Algo no está bien (' + data.code + '): ' + data.msg,
            // // footer: '<a href>Contacte a Soporte Técnico</a>',
        });
    };

    drawRespuesta(e) {
        var tRespuesta = $('#tRespuesta').DataTable({
            data: e,
            destroy: true,
            "searching": false,
            "paging": false,
            "info": false,
            "ordering": false,
            // "retrieve": true,
            "order": [[0, "desc"]],
            columns: [
                {
                    title: "CLAVE",
                    data: "clave"
                },
                {
                    title: "ESTADO",
                    data: "estado"
                }
            ]
        });
    };

    Init() {
        // validator.js
        var validator = new FormValidator({ "events": ['blur', 'input', 'change'] }, document.forms["frmXml"]);
        $('#frmXml').submit(function (e) {
            e.preventDefault();
            var validatorResult = validator.checkAll(this);
            if (validatorResult.valid)
                if (dz != undefined)
                    dz.processQueue();
            //     mr.Save;
            return false;
        });
        // on form "reset" event
        document.forms["frmXml"].onreset = function (e) {
            validator.reset();
        }
        //NProgress
        $(function () {
            $(document)
                .ajaxStart(NProgress.start)
                .ajaxStop(NProgress.done);
        });
        // submit
        $('#btnSubmit').click(function () {
            $('#m').val( $('#tipoMensaje option:selected').val());
            $('#d').val( $('#detalleMensaje').val());
            $('#frmXml').submit();            
        });
        // dropzone        
        Dropzone.options.frmXml = {
            init: function () {
                this.on("addedfile", function (file) {
                    dz = this;
                    // mr.certificado= dz.files[0].name;
                });
                this.on("complete", function (file) {
                    // estado de los envios.
                    var data= JSON.parse(file.xhr.response);
                    mr.drawRespuesta(data);                    
                    $('#modalRespuesta').show();      
                    return true;
                    //mr.showInfo();   <option value=${d.id} ${n == 0 ? `selected` : ``}> ${d.value}</option>
                    // if (file.xhr.response != 'UPLOADED') {
                    //     //JSON.parse(file.xhr.response);
                    //     swal({
                    //         type: 'error',
                    //         title: 'Oops...',
                    //         text: 'Ha ocurrido un error al subir los xml.',
                    //         // // footer: '<a href>Contacte a Soporte Técnico</a>',
                    //     });
                    //     $(file.previewElement).addClass('dz-error-message');
                    //     $('#filelist').html('');
                    //     // mr.certificado= null;
                    // } else {
                    //     // var data= JSON.parse(file.xhr.response)
                    //     // sesion.in(data);
                    //     mr.showInfo();
                    // }
                });
                this.on("error", function (file) {
                    var data= JSON.parse(file.xhr.response);
                    sesion.in(data);
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Archivo no válido.',
                        // // footer: '<a href>Contacte a Soporte Técnico</a>',
                    })
                    this.removeFile(file);
                });
                this.on("canceled", function (file) {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Certificado cancelado',
                        // // footer: '<a href>Contacte a Soporte Técnico</a>',
                    })
                });
            },
            autoProcessQueue: false,
            acceptedFiles: "text/xml",
            // maxFiles: 1,
            parallelUploads : 100,
            uploadMultiple: true,
            addRemoveLinks: true,
            autoDiscover: false
        };
    };
}
var dz;
let mr = new MensajeReceptor();