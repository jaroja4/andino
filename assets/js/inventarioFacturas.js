class InventarioFacturas {
    // Constructor
    constructor(id, facturas, tb_facturas, extraMails, fechaInicial, fechaFinal) {
        this.id = id || null;
        this.facturas = facturas || new Array();
        this.tb_facturas = tb_facturas || null;
        this.extraMails = extraMails || null;
        this.fechaInicial = fechaInicial || "";
        this.fechaFinal = fechaFinal || "";
    };

    CargaFacturas() {
        $.ajax({
            type: "POST",
            url: "class/factura.php",
            data: {
                action: "ReadAllbyRange",
                obj: JSON.stringify(inventarioFacturas)
            }
        })
            .done(function (e) {
                if (e != "null"){
                    inventarioFacturas.drawFac(e)
                }else{
                    swal({
                        type: 'success',
                        title: 'Listo, no hay facturas que cargar!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            });
    };

    drawFac(e) {
        var facturas = JSON.parse(e);

        this.tb_facturas = $('#tb_facturas').DataTable({
            data: facturas.facturas,
            destroy: true,
            "language": {
                "infoEmpty": "Sin Productos Ingresados",
                "emptyTable": "Sin Productos Ingresados",
                "search": "Buscar",
                "zeroRecords": "No hay resultados",
                "lengthMenu": "Mostar _MENU_ registros",
                "paginate": {
                    "first": "Primera",
                    "last": "Ultima",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [[1, "desc"]],
            columns: [
                {
                    title: "ID Factura",
                    data: "id",
                    visible: false
                },
                {
                    title: "#Factura",
                    data: "consecutivo"
                },
                {
                    title: "Fecha",
                    data: "fechaCreacion"
                },
                {
                    title: "Estado",
                    data: "idEstadoComprobante",
                    mRender: function (e) {
                        switch (e) {
                            case "1":
                                return '<i class="fa fa-paper-plane" aria-hidden="true" style="color:red"> Sin Enviar</i>';
                                break;
                            case "2":
                                return '<i class="fa fa-paper-plane" aria-hidden="true" style="color:green"> Enviado</i>';
                                break;
                            case "3":
                                return '<i class="fa fa-check-square-o" aria-hidden="true" style="color:green"> Aceptado</i>';
                                break;
                            case "4":
                                return '<i class="fa fa-times-circle" aria-hidden="true" style="color:red"> Rechazado</i>';
                                break;
                            case "5":
                                return '<i class="fa fa-exclamation-triangle" aria-hidden="true" style="color:#FF6F00"> Otro</i>';
                                break;
                            default:
                                return 'Desconocido';
                                break;

                        }
                    }
                },
                {
                    title: "Total",
                    data: "totalComprobante",
                    mRender: function (e) {
                        return '¢' + parseFloat(e).toFixed(2).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            ],
            //////////////////


            "footerCallback": function (row, data, start, end, display) {
                var api = this.api();
                // Remove the formatting to get integer data for summation
                var intVal = function (i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                            i : 0;
                };

                // Total over all pages
                var total = api
                    .column(4)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    });

                // Total over this page
                var pageTotal = api
                    .column(4, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    });

                // Update footer
                $(api.column(4).footer()).html(
                    '$' + pageTotal.toFixed(2).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ' ( $' + total.toFixed(2).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ' total)'
                );
            },
            //////////////////

        });

        ///////////////////////
        // var table = $('#example').DataTable();
        // var column = this.tb_facturas.column(4);

        // $(column.footer()).html(
        //     column.data().reduce(function (a, b) {
        //         return parseFloat(a) + parseFloat(b);
        //     })
        // );
        ///////////////////////
    };

    ReadbyID(id) {
        $.ajax({
            type: "POST",
            url: "class/factura.php",
            data: {
                action: "read",
                id: id.id
            }
        })
            .done(function (e) {
                inventarioFacturas.drawFacturaByID(e);
            });
    };

    drawFacturaByID(e) {
        var factura = JSON.parse(e);

        $("#idFactura").text(factura.id);

        switch (factura.idEstadoComprobante) {
            case "1":
                factura.idEstadoComprobanteDetallado = '<i class="fa fa-paper-plane" aria-hidden="true" style="color:red"> Sin Enviar</i>';
                break;
            case "2":
                factura.idEstadoComprobanteDetallado = '<i class="fa fa-paper-plane" aria-hidden="true" style="color:green"> Enviado</i>';
                break;
            case "3":
                factura.idEstadoComprobanteDetallado = '<i class="fa fa-check-square-o" aria-hidden="true" style="color:green"> Aceptado</i>';
                break;
            case "4":
                factura.idEstadoComprobanteDetallado = '<i class="fa fa-times-circle" aria-hidden="true" style="color:red"> Rechazado</i>';
                break;
            case "5":
                factura.idEstadoComprobanteDetallado = '<i class="fa fa-exclamation-triangle" aria-hidden="true" style="color:#FF6F00"> Otro</i>';
                break;
            default:
                factura.idEstadoComprobanteDetallado = 'Desconocido';
                break;

        }

        $("#detalleFac").empty();
        var detalleFac =
            `<button type="button" class="close" data-dismiss="modal">
                <span aria-hidden="true">X</span>
            </button>
            <h4 class="modal-title" id="myModalLabel">Factura #${factura.consecutivo}.</h4>
            <br>
            <div class="row">                
                <div class="col-md-6 col-sm-12 col-xs-12">
                    <p>Estado: ${factura.idEstadoComprobanteDetallado}</p>
                    <p>Fecha: ${factura.fechaCreacion}</p>
                    <p>Clave: ${factura.clave}</p>
                </div>  
                <div class="col-md-6 col-sm-12 col-xs-12">
                    <p>Cliente: ${factura.datosReceptor.nombre}</p>
                    <p>Telefono: ${factura.datosReceptor.numTelefono}</p>
                    <p>Correo: ${factura.datosReceptor.correoElectronico}</p>
                </div>                
            </div>`;

        $("#detalleFac").append(detalleFac);

        var tb_detalleFactura = $('#tb_detalle_fact').DataTable({
            data: factura.detalleFactura,
            destroy: true,
            "searching": false,
            "paging": false,
            "info": false,
            "ordering": false,
            // "retrieve": true,
            "order": [[0, "desc"]],
            columns: [
                {
                    title: "Producto",
                    data: "detalle"
                },
                {
                    title: "Precio/U",
                    data: "precioUnitario",
                    mRender: function (e) {
                        return '¢' + parseFloat(e).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                },
                {
                    title: "Cantidad",
                    data: "cantidad",
                    mRender: function (e) {
                        return parseFloat(e).toFixed(2);
                    }
                },
                {
                    title: "Impuestos",
                    data: "montoImpuesto",
                    mRender: function (e) {
                        return '¢' + parseFloat(e).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                },
                {
                    title: "Descuentos",
                    data: "montoDescuento",
                    mRender: function (e) {
                        return '¢' + parseFloat(e).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                },
                {
                    title: "Total Linea",
                    data: "montoTotalLinea",
                    mRender: function (e) {
                        return '¢' + parseFloat(e).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            ]
        });


        $("#totalFact").empty();

        var totalFact =
            `<div class="row">
                <div class="col-md-9 col-sm-9 col-xs-9">
                    <h4>Total Impuesto:</h4>
                </div>
                <div class="col-md-3 col-sm-3 col-xs-3">
                    <h4>¢${parseFloat(factura.totalImpuesto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ".")}</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9 col-sm-9 col-xs-9">
                    <h4>Total Descuento:</h4>
                </div>
                <div class="col-md-3 col-sm-3 col-xs-3">
                    <h4>-${parseFloat(factura.totalDescuentos).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ".")}</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9 col-sm-9 col-xs-9">
                    <h4>Total:</h4>
                </div>
                <div class="col-md-3 col-sm-3 col-xs-3">
                    <h4>¢${(parseFloat(factura.totalVentaneta) + parseFloat(factura.totalImpuesto) - parseFloat(factura.totalDescuentos)).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ".")}</h4>
                </div>
            </div>`;
        // parseFloat(e).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        $("#totalFact").append(totalFact);


        $('#modalFac').modal('toggle');

    };

}
//Class Instance
let inventarioFacturas = new InventarioFacturas();




