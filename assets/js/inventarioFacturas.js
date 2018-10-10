class InventarioFacturas {
    // Constructor
    constructor(facturas, tb_facturas, tb_prdXFact) {
        this.facturas = facturas || new Array();
        this.tb_facturas = tb_facturas || null;
    };

    CargaFacturas() {
        $.ajax({
            type: "POST",
            url: "class/factura.php",
            data: {
                action: "readAll"
            }
        })
            .done(function (e) {
                inventarioFacturas.drawFac(e)
            });
    };

    drawFac(e) {
        var facturas = JSON.parse(e);

        this.tb_facturas = $('#tb_facturas').DataTable({
            data: facturas,                               
            "language": {
                "infoEmpty":  "Sin Productos Ingresados",
                "emptyTable": "Sin Productos Ingresados",
                "search":     "Buscar",
                "zeroRecords": "No hay resultados",
                "lengthMenu":  "Mostar _MENU_ registros",
                "paginate": {
                    "first":   "Primera",
                    "last":    "Ultima",
                    "next":    "Siguiente",
                    "previous":"Anterior"
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
                    mRender: function ( e ) {
                        switch (e) {
                            case "1":
                                return '<i class="fa fa-paper-plane" aria-hidden="true" style="color:red"></i>';
                                break;
                            case "2":
                                return '<i class="fa fa-paper-plane" aria-hidden="true" style="color:green"></i>';
                                break;
                            case "3":
                                return '<i class="fa fa-check-square-o" aria-hidden="true" style="color:green"></i>';
                                break;
                            case "4":
                                return '<i class="fa fa-times-circle" aria-hidden="true" style="color:red"></i>';
                                break;
                            case "5":
                                return '<i class="fa fa-exclamation-triangle" aria-hidden="true" style="color:#FF6F00"></i>';
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
                    mRender: function ( e ) {
                        return '¢'+ parseFloat(e).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                },
            ]
        });
    };

    ReadbyID(id) {
        $("#detalleFac").empty();
        var detalleFac =
            `<button type="button" class="close" data-dismiss="modal">
                <span aria-hidden="true">X</span>
            </button>
            <h4 class="modal-title" id="myModalLabel">Factura #${id.consecutivo}.</h4>
            <div class="row">
                
                <div class="col-md-6 col-sm-6 col-xs-6">
                    <p>Fecha: ${id.fechaCreacion}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-6">
                    <p>Cajero: ${id.userName}</p>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6">
                    <p>Bodega: ${id.nombre}</p>
                </div>
            </div>`;
        $("#detalleFac").append(detalleFac);


        $("#totalFact").empty();

        // var totalFact =
        //     `<h4>Total: ¢${Math.round(id.totalVenta)}</h4>`;
        // $("#totalFact").append(totalFact);


        $.ajax({
            type: "POST",
            url: "class/ProductoXFactura.php",
            data: {
                action: "ReadbyID",
                obj: JSON.stringify(id.id)
            }
        })
            .done(function (e) {
                inventarioFacturas.drawFactDetail(e);
            });
    };

    drawFactDetail(e) {
        var facturas = JSON.parse(e);

        this.tb_prdXFact = $('#tb_detalle_fact').DataTable({
            data: facturas,
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
                    title: "Cantidad",
                    data: "cantidad"
                },
                {
                    title: "Precio",
                    data: "montoTotalLinea"
                }
            ]
        });

        $('#modalFac').modal('toggle');

    };

}
//Class Instance
let inventarioFacturas = new InventarioFacturas();




