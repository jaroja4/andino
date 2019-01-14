class Factura {
    // Constructor
    constructor(id, cajero, productos, descuento, total, fechaCreacion, importe, idusuario, idcliente, idMedioPago, tipoFactura, tieneIV) {
        this.id = id || null;
        this.cajero = cajero || '';
        this.idusuario = idusuario || '';
        this.idcliente = idcliente || '';
        this.descuento = descuento || 0;
        this.producto = producto || new Array(new Array());
        this.total = total || '';
        this.fechaCreacion = fechaCreacion || null;
        this.importe = importe || 0;
        this.idMedioPago = idMedioPago || 1;
        this.tipoFactura = tipoFactura || 0; // 0: producto | 1: servicio
        this.tieneIV = tieneIV || 1; // tiene impuesto de ventas.
    }

    //Agregar aqui las funciones
    checkProfileContribuyente() {
        $(".main_container").attr("style", "visibility:hidden");
        $.ajax({
                type: "POST",
                url: "class/entidad.php",
                data: {
                    action: "checkProfile"
                }
            })
            .done(function (e) {
                var data = JSON.parse(e);
                if (data.status == false) {
                    swal({
                        type: 'warning',
                        title: 'Contribuyente',
                        text: 'Contribuyente no registrado para Facturación Electrónica',
                        footer: '<a href="contribuyente.html">Agregar Contribuyente</a>',
                    }).then((result) => {
                        if (result.value)
                            location.href = "dashboard.html";
                    })
                } else {
                    $(".call_idDocumento").text(data.idDocumento == 1 ? 'Factura Electrónica' : 'Tiquete Electrónico');
                    $(".main_container").removeAttr("style");
                }
            })
            .fail(function (e) {
                showError(e);
            });
    };

}

let factura = new Factura();

//Carga en el modal el html para pagar con tarjeta
function facCard() {
    factura.idMedioPago = 2;
    $("#formapago").empty();
    var DivCard =
        `<div class="row">
        <div class="col-md-4 col-md-offset-4 col-sm-offset-0 col-xs-6 col-xs-offset-0">
            <h3 class="text-left" >Ingrese Ref.:</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <input id="pagotarjeta" class="input-lg valPago" type="number" onkeyup="valPago(this.value)" placeholder="Ingrese Numero Referencia" required="" minlength="5" autofocus="">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-4 col-sm-offset-0 col-xs-6 col-xs-offset-0">
            <button type="button" onclick="CreateFact()" class="btn btn-primary procesarFac" disabled style="margin-top:10px;">Facturar</button>
        </div>
    </div>`;
    $("#formapago").append(DivCard);


    $("#btn-formapago").empty();
    var DivCard =
        `<button type="button" id="btn_open_modal_agregar_cliente" onclick="btn_open_modal_agregar_cliente()" class="btn btn-warning disableBTN">Receptor</button>  
    <button type="button" id="modalFormaPago" onclick="btnFormaPago()"class="btn btn-primary disableBTN">Atras</button>`;
    $("#btn-formapago").append(DivCard);

    $('#pagotarjeta').on('keyup', function (e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            if($('#pagotarjeta').val()!='')
                CreateFact();
        }
    });
    //
    $('#pagotarjeta').focus();
};

//Carga en modal el html para pagar con efectivo
function facCash() {
    factura.idMedioPago = 1;
    $("#formapago").empty();

    var DivCash =
        `<div class="row">
        <div class="col-md-4 col-md-offset-4 col-sm-offset-0 col-xs-6 col-xs-offset-0">
            <h3 class="text-left" >Paga con:</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <input id="pagocash" class="input-lg valPago" onkeyup="valPago(this.value)" type="number" placeholder="Ingrese Monto en Efectivo"  required="" minlength="5" autofocus="">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-4 col-sm-offset-0 col-xs-6 col-xs-offset-0">
            <button type="button" onclick="CreateFact()" class="btn btn-primary procesarFac" disabled style="margin-top:10px;">Facturar</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-4 col-sm-offset-0 col-xs-6 col-xs-offset-0">
            <h3 class="text-left" id="vuelto">Su vuelto:</h3>
        </div>
    </div>`;
    $("#formapago").append(DivCash);


    $("#btn-formapago").empty();
    var DivCash =
        `<button type="button" id="btn_open_modal_agregar_cliente" onclick="btn_open_modal_agregar_cliente()" class="btn btn-warning disableBTN">Receptor</button>  
    <button type="button" id="modalFormaPago" onclick="btnFormaPago()" class="btn btn-primary disableBTN">Atras</button>`;
    $("#btn-formapago").append(DivCash);

    $('#pagocash').on('keyup', function (e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            CreateFact();
        }
    });
    $('#pagocash').focus();

};

//agrega la linea a la lista
function agregarProducto() {
    AgregaProductodManual($("#inp_descripcion").val(), $("#inp_precio").val());
    $("#inp_descripcion").val("");
    $("#inp_precio").val("");
    $('#btn_agregarProducto').attr('disabled', 'disabled');
    $("#inp_descripcion").focus();
}

function abrirModalPago() {
    var attr = $('#btn_open_modal_fac').attr("disabled");
    if (typeof attr !== typeof undefined && attr !== false) {
        swal({
            type: 'warning',
            text: 'Debe agregar productos a la lista.',
            timer: 2000
        });
        $("#inp_descripcion").focus();
        return false;
    }
    var totalTemp = "";

    $('#total_pagar').empty();

    // totalTemp = $("#total")[0].textContent;
    // totalTemp = totalTemp.replace("¢", "");
    // totalTemp = parseFloat(totalTemp).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ".");

    $('#total_pagar').append("Total a Pagar: ¢" + factura.totalComprobante.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, "."));
    btnFormaPago();
    $(".factura-modal-lg").modal("show");
    $('.factura-modal-lg').bind('keydown', function(e){
        if (e.keyCode == 37) {
            facCard();
        }});
        $('.factura-modal-lg').bind('keydown', function(e){
            if (e.keyCode == 39) {
                facCash();
            }});
    // $('.factura-modal-lg').bind('keydown', '3', btn_open_modal_agregar_cliente);
    // $('.factura-modal-lg').bind('keydown', '4', btnFormaPago);
}

//Carga en el modal las dos opciones de forma de pago
function btnFormaPago() {
    $("#formapago").empty();
    var DivCash =
        `<div class="col-md-2"></div>
    <div class="col-md-3" onclick="facCard()">
        <img id="fac-ccard" src="images/credit-cards.png" class="modal-img-pago">
        <strong><p class="text-center">Tarjeta</p></strong>
    </div>
    <div class="col-md-2"></div>
    <div class="col-md-3" onclick="facCash()">
        <img id="fac-cash" src="images/cash.png" class="modal-img-pago">
        <strong><p class="text-center">Efectivo</p></strong>
    </div>`;
    $("#formapago").append(DivCash);

    $("#btn-formapago").empty();
    var DivCash =
        `<button type="button" id="btn_open_modal_agregar_cliente" onclick="btn_open_modal_agregar_cliente()" class="btn btn-warning">Receptor</button>  
    <button type="button" id="modalPago" class="btn btn-primary" data-dismiss="modal">Atras</button>`;
    $("#btn-formapago").append(DivCash);    
};

//Valida el pago
function valPago(val) {
    //xPagar = parseFloat(($("#total")[0].textContent).replace("¢", ""));
    pago = parseFloat($('.valPago').val());
    if (isNaN($('.valPago').val())) {
        // alert("numero");
        val = val.replace(/[^0-9]/g, '');
        $(".valPago").val(val);
    } else {
        if (pago >= factura.totalComprobante.toFixed(2)) {
            $(".procesarFac").prop('disabled', false);
            calcVuelto(pago, factura.totalComprobante);
        } else {
            $(".procesarFac").prop('disabled', true);
        }
    }
};

//Informa que la factura fue agregada
function alertFact() {
    swal({
        type: 'success',
        text: 'Factura Lista!',
        timer: 2000
    });
    $(".procesarFac").prop('disabled', true);
    // calcVuelto();
    setTimeout(function () {
        location.reload();
    }, 2000);
    //factura = new Factura();
}

function calcVuelto(pago, xPagar) {
    vuelto = ((pago - xPagar).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ".")).toString();
    $("#vuelto")["0"].textContent = "Su cambio: " + vuelto;
}

// Muestra errores en ventana
function showError(e) {
    var data = JSON.parse(e.responseText);
    alert("ERROR");
};

function CleanCtls() {
    $("#p_searh").val('');
};

//Agrega los productos desde los inputs en facturacion.html
function AgregaProductodManual(descripcion, precio) {
    if (precio < 1) {
        swal({
            type: 'warning',
            text: 'El precio unitario no puede ser negativo.',
            timer: 2000
        });
        $("#inp_descripcion").focus();
        return false;
    }
    //
    t.row.add([
        descripcion,
        precio,
        null,
        precio
    ]).draw(false);
    calcTotal();
    $('#btn_open_modal_fac').attr("disabled", false);
}

//Calcula los totales cada vez que un producto es modificado
function calcTotal() {
    var subT = 0;
    if ($(document.getElementById("productos").rows)["0"].childElementCount > 2) {

        $(document.getElementById("productos").rows).each(function (i, item) {
            // alert(item.childNodes[3].innerText);
            rowTotal = item.childNodes[3].textContent.replace("¢", "");
            rowTotal = rowTotal.replace(/,/g, "");

            if (document.getElementById("rd_conImpuestos").checked == true) {
                subT = subT + parseFloat(rowTotal) / 1.13;
            } else {
                subT = subT + parseFloat(rowTotal)
            }
        });

        factura.totalVentaneta = subT;
        factura.totalImpuesto = factura.totalVentaneta * (parseFloat(($("#iv_100")[0].textContent).replace("%", "")) / 100); // %iv /100
        factura.totalComprobante = factura.totalVentaneta + factura.totalImpuesto;
        $("#subtotal")[0].textContent = "¢" + factura.totalVentaneta.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        $("#iv_val")[0].textContent = "¢" + factura.totalImpuesto.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        $("#total")[0].textContent = "¢" + factura.totalComprobante.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    } else {
        $('#open_modal_fac').attr("disabled", true);
        $("#subtotal")[0].textContent = "¢0";
        // $("#desc_val")[0].textContent = "¢0";
        $("#iv_val")[0].textContent = "¢0";
        $("#total")[0].textContent = "¢0";
    }
};


// Envia los datos PHP para la creacion y almacenamiento de la factura
function CreateFact() {
    var attr = $('.procesarFac').attr("disabled", 'disabled');
    var attr = $('.disableBTN').attr("disabled", 'disabled');
    // if ((typeof attr !== typeof undefined && attr !== false)) {
    //     swal({
    //         type: 'warning',
    //         text: 'Debe digitar el monto de pago o el número de referencia.',
    //         timer: 2000
    //     });
    //     return false;
    // }
    //
    var miAccion = 'create';
    factura.totalVenta = 0;
    factura.totalDescuentos = 0;
    factura.totalVentaneta = 0;
    factura.totalImpuesto = 0;
    factura.totalComprobante = 0;

    // detalle.
    factura.detalleFactura = [];
    //Esta tabla no se puede recorrer de esta forma xq no se logra adquirir el valor de la cantidad que esta dentro de un input tipo number
    // $(t.rows().data()).each(function (i, item) {  

    //Por lo tanto se debe recorrer de la forma "tracicional" de esta forma si es accesible el campo de cantidad
    $(document.getElementById("productos").rows).each(function (i, item) {
        var precioUnitarioTemporal = "";

        var objetoDetalleFactura = new Object();
        ////////////////////////////////////////////////////////////////
        ////////////////////////Datos de factura////////////////////////
        ////////////////////////////////////////////////////////////////
        objetoDetalleFactura.cantidad = parseFloat(item.cells[2].children[0].value);
        objetoDetalleFactura.detalle = item.cells[0].textContent;

        precioUnitarioTemporal = item.cells[1].textContent.replace("¢", "");
        precioUnitarioTemporal = precioUnitarioTemporal.replace(/,/g, "");

        /*********************************************************/
        /*Debe tomar el CÓDIGO DE CONFIGURACIÓN DEL CONTRIBUYENTE*/
        /*********************************************************/
        /*********************************************************/
        /*********** Debe tomar el IV de la tabla ****************/
        /*********************************************************/
        if (document.getElementById("rd_conImpuestos").checked == true) {
            objetoDetalleFactura.codigoImpuesto = 1; // 1 = Impuesto General sobre las Ventas. 
            objetoDetalleFactura.tarifaImpuesto = 13;
        } else {
            objetoDetalleFactura.tarifaImpuesto = 0;
            objetoDetalleFactura.codigoImpuesto = 00; // codigoImpuesto == '00' no lleva IV
        }

        objetoDetalleFactura.precioUnitario = parseFloat((precioUnitarioTemporal / (1 + (objetoDetalleFactura.tarifaImpuesto / 100))).toFixed(5));
        ////////////////////////////////////////////////////////////////

        objetoDetalleFactura.numeroLinea = i + 1;
        objetoDetalleFactura.idTipoCodigo = 1; // 1 = codigo de vendedor  Jason: Es necesario para Hacienda?
        objetoDetalleFactura.codigo = item[1]; // Jason: Los productos tienen que tener un codigo??        
        objetoDetalleFactura.idUnidadMedida = 78; // 78 =  unidades. 
        objetoDetalleFactura.montoTotal = parseFloat((objetoDetalleFactura.precioUnitario * objetoDetalleFactura.cantidad).toFixed(5));
        objetoDetalleFactura.montoDescuento = 0;
        objetoDetalleFactura.naturalezaDescuento = 'No aplican descuentos';
        objetoDetalleFactura.subTotal = parseFloat((objetoDetalleFactura.montoTotal - objetoDetalleFactura.montoDescuento).toFixed(5));
        // exoneracion
        //objetoDetalleFactura.idExoneracionImpuesto = null;
        // iv


        //
        objetoDetalleFactura.montoImpuesto = parseFloat((objetoDetalleFactura.subTotal * (objetoDetalleFactura.tarifaImpuesto / 100)).toFixed(5)); // debe tomar el impuesto como parametro de un tabla).
        objetoDetalleFactura.montoTotalLinea = parseFloat((objetoDetalleFactura.subTotal + objetoDetalleFactura.montoImpuesto).toFixed(5));
        factura.detalleFactura.push(objetoDetalleFactura);
        // actualiza totales de factura.
        factura.totalVenta = parseFloat((factura.totalVenta + objetoDetalleFactura.montoTotal).toFixed(5));
        factura.totalDescuentos = parseFloat((factura.totalDescuentos + objetoDetalleFactura.montoDescuento).toFixed(5));
        factura.totalImpuesto = parseFloat((factura.totalImpuesto + objetoDetalleFactura.montoImpuesto).toFixed(5));
        //
    });
    // totales de factura.
    // exonera y grava de mercancias y servicios.
    if(factura.tipoFactura==0) {
        factura.totalServGravados = 0;
        factura.totalServExentos = 0;
        factura.totalMercanciasGravadas = factura.totalVenta;
        factura.totalMercanciasExentas = 0;
    }
    else{
        factura.totalServGravados = factura.totalVenta;;
        factura.totalServExentos = 0;
        factura.totalMercanciasGravadas = 0;
        factura.totalMercanciasExentas = 0;
    }
    

    factura.totalGravado = parseFloat((factura.totalServGravados + factura.totalMercanciasGravadas).toFixed(5));
    factura.totalExento = parseFloat((factura.totalServExentos + factura.totalMercanciasExentas).toFixed(5));
    factura.totalVenta = parseFloat((factura.totalGravado + factura.totalExento).toFixed(5));
    // total venta neta.
    factura.totalVentaneta = parseFloat((factura.totalVenta - factura.totalDescuentos).toFixed(5));
    // total comprobante.
    factura.totalComprobante = parseFloat((factura.totalVentaneta + factura.totalImpuesto).toFixed(5));
    factura.idReceptor = receptor.id;

    $.ajax({
            type: "POST",
            url: "class/factura.php",
            data: {
                action: miAccion,
                obj: JSON.stringify(factura),
                dataReceptor: JSON.stringify(receptor)
            }
        })
        .done(function () {
            alertFact();
        })
        .fail(function (e) {
            producto.showError(e);
        })
        .always(function () {
            setTimeout($("#btnProducto").removeAttr("disabled"), 5000);
            producto = new Producto();
            producto.ClearCtls();
            producto.Read;
            $("#inp_descripcion").focus();
        });
}