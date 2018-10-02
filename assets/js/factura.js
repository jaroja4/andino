class Factura {
    // Constructor
    constructor(id, cajero, productos, descuento, total, fechaCreacion, importe, idusuario, idcliente) {
        this.id = id || null;
        this.cajero = cajero || '';
        this.idusuario = idusuario || '';
        this.idcliente = idcliente || '';
        this.descuento=descuento || 0;
        this.producto=producto || new Array(new Array ());
        this.total= total || '';
        this.fechaCreacion= fechaCreacion || null;
        this.importe=importe || 0;
    }

//Agregar aqui las funciones

}

let factura = new Factura();

//Carga en el modal el html para pagar con tarjeta
function facCard (){
    $("#formapago").empty();
    var DivCard =
    `<div class="row">
        <div class="col-md-4 col-md-offset-4 col-sm-offset-0 col-xs-6 col-xs-offset-0">
            <h3 class="text-left" >Ingrese Ref.:</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <input class="input-lg valPago" type="text" onkeyup="valPago(this.value)" placeholder="Ingrese Numero Referencia" required="" minlength="5" autofocus="">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-4 col-sm-offset-0 col-xs-6 col-xs-offset-0">
            <button type="button" onclick="CreateFact()" class="btn btn-primary procesarFac" disabled style="margin-top:10px;">Procesar</button>
        </div>
    </div>`;
    $("#formapago").append(DivCard);


    $("#btn-formapago").empty();
    var DivCash =
    `<button type="button" id="btn_open_modal_agregar_cliente" onclick="btn_open_modal_agregar_cliente()" class="btn btn-warning">Agregar Cliente</button>  
    <button type="button" id="modalFormaPago" onclick="btnFormaPago()"class="btn btn-primary">Atras</button>`;
    $("#btn-formapago").append(DivCash);
};

//Carga en modal el html para pagar con efectivo
function facCash(){
    $("#formapago").empty();

    var DivCash =
    `<div class="row">
        <div class="col-md-4 col-md-offset-4 col-sm-offset-0 col-xs-6 col-xs-offset-0">
            <h3 class="text-left" >Paga con:</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <input id="pagocash" class="input-lg valPago" onkeyup="valPago(this.value)" type="text" placeholder="Ingrese Monto en Efectivo"  required="" minlength="5" autofocus="">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-4 col-sm-offset-0 col-xs-6 col-xs-offset-0">
            <button type="button" onclick="CreateFact()" class="btn btn-primary procesarFac" disabled style="margin-top:10px;">Procesar</button>
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
    `<button type="button" id="btn_open_modal_agregar_cliente" onclick="btn_open_modal_agregar_cliente()" class="btn btn-warning">Agregar Cliente</button>  
    <button type="button" id="modalFormaPago" onclick="btnFormaPago()" class="btn btn-primary">Atras</button>`;
    $("#btn-formapago").append(DivCash);
};

//Carga en el modal las dos opciones de forma de pago
function btnFormaPago() {
    $("#formapago").empty();
    var DivCash =
    `<div class="col-md-2"></div>
    <div class="col-md-3" onclick="facCard()">
        <img id="fac-ccard" src="images/credit-cards.png" class="modal-img-pago">
        <p class="text-center">Tarjeta</p>
    </div>
    <div class="col-md-2"></div>
    <div class="col-md-3" onclick="facCash()">
        <img id="fac-cash" src="images/cash.png" class="modal-img-pago">
        <p class="text-center">Efectivo</p>
    </div>`;
    $("#formapago").append(DivCash);

    $("#btn-formapago").empty();
    var DivCash =
    `<button type="button" id="btn_open_modal_agregar_cliente" onclick="btn_open_modal_agregar_cliente()" class="btn btn-warning">Agregar Cliente</button>  
    <button type="button" id="modalPago" class="btn btn-primary" data-dismiss="modal">Atras</button>`;
    $("#btn-formapago").append(DivCash);
};

//Valida el pago
function valPago(val){    
    xPagar = parseFloat(($("#total")[0].textContent).replace("¢",""));
    pago = parseFloat($('.valPago').val());    
    if (isNaN($('.valPago').val())){
        // alert("numero");
        val = val.replace(/[^0-9]/g, '');
        $(".valPago").val(val);
    }else{
        if(pago >= xPagar){
            $(".procesarFac").prop('disabled', false);
            calcVuelto(pago, xPagar);
        }
        else{
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
    setTimeout(function() {location.reload();},2000);
}

function calcVuelto(pago, xPagar) {
    vuelto = ((pago-xPagar).toFixed(2)).toString();
    $("#vuelto")["0"].textContent = "Su cambio: "+vuelto;
}

// Muestra errores en ventana
function showError(e) {    
    var data = JSON.parse(e.responseText);
    alert("ERROR");
};

function CleanCtls() {
    $("#p_searh").val('');
};

//Agrega los productos desde los inputs en Facturacion.html
function AgregaProductodManual(descripcion, precio){  
    t.row.add( [
        descripcion,
        precio,
        null,
        precio
    ] ).draw( false );
    calcTotal();
    $('#btn_open_modal_fac').attr("disabled", false);
}

//Calcula los totales cada vez que un producto es modificado
function calcTotal(){
    var subT=0; 
    if($(document.getElementById("productos").rows)["0"].childElementCount>2){
     
        $(document.getElementById("productos").rows).each(function(i,item){
            // alert(item.childNodes[3].innerText);
            
            subT= subT + parseFloat((item.childNodes[3].textContent).replace("¢",""))/1.13; 
        });
        $("#subtotal")[0].textContent = "¢"+subT.toFixed(2); 
        // factura.descuento = $("#desc_val")[0].textContent = "¢"+ (subT * (parseFloat(($("#desc_100")[0].textContent).replace("%",""))) / 100).toFixed(2) ;
        factura.impuesto = $("#iv_val")[0].textContent = "¢"+ (subT * (parseFloat(   (  $("#iv_100")[0].textContent  ).replace("%","")) /100)).toFixed(2);

        $("#total")[0].textContent = "¢" + (parseFloat($("#subtotal")[0].textContent.replace("¢","")) + parseFloat($("#iv_val")[0].textContent.replace("¢","")));
    }
    else{
        $('#open_modal_fac').attr("disabled", true);
        $("#subtotal")[0].textContent = "¢0"; 
        $("#desc_val")[0].textContent = "¢0";
        $("#iv_val")[0].textContent = "¢0";
        $("#total")[0].textContent = "¢0"; 
    }
};


// Envia los datos PHP para la creacion y almacenamiento de la factura
function CreateFact(){
    var miAccion = 'create';
    factura.totalVenta = 0;
    factura.totalDescuentos = 0;
    factura.totalVentaneta = 0;    
    factura.totalImpuesto = 0;
    factura.totalComprobante=0;
    
    // detalle.
    factura.detalleFactura = [];
    //Esta tabla no se puede recorrer así xq no se logra adquirir el valor de la cantidad que esta dentro de un input tipo number
    // $(t.rows().data()).each(function (i, item) {  

    //Por lo tanto se debe recorrer de la forma "tracicional" de esta forma si es accesible el campo de cantidad
    $(document.getElementById("productos").rows).each(function(i,item){
        var precioUnitario;
        
        var objetoDetalleFactura = new Object();
        ////////////////////////////////////////////////////////////////
        ////////////////////////Datos de factura////////////////////////
        ////////////////////////////////////////////////////////////////
        objetoDetalleFactura.cantidad = item.cells[2].children[0].value;
        objetoDetalleFactura.detalle = item.cells[0].textContent;       
        objetoDetalleFactura.precioUnitario = parseFloat(item.cells[1].textContent/1.13);
        ////////////////////////////////////////////////////////////////
        objetoDetalleFactura.numeroLinea = i+1;
        objetoDetalleFactura.idTipoCodigo = 1; // 1 = codigo de vendedor  Jason: Es necesario para Hacienda?
        objetoDetalleFactura.codigo = item[1]; // Jason: Los productos tienen que tener un codigo??        
        objetoDetalleFactura.idUnidadMedida = 78; // 78 =  unidades. 
        objetoDetalleFactura.montoTotal =  objetoDetalleFactura.precioUnitario *  objetoDetalleFactura.cantidad;
        objetoDetalleFactura.montoDescuento = 0;
        objetoDetalleFactura.naturalezaDescuento = 'No aplican descuentos';
        objetoDetalleFactura.subTotal = objetoDetalleFactura.montoTotal - objetoDetalleFactura.montoDescuento;        
        // exoneracion
        //objetoDetalleFactura.idExoneracionImpuesto = null;
        // iv
        objetoDetalleFactura.codigoImpuesto = 1; // 1 = Impuesto General sobre las Ventas.
        objetoDetalleFactura.tarifaImpuesto = 13;
        objetoDetalleFactura.montoImpuesto = objetoDetalleFactura.subTotal * (objetoDetalleFactura.tarifaImpuesto/100); // debe tomar el impuesto como parametro de un tabla.
        objetoDetalleFactura.montoTotalLinea = objetoDetalleFactura.subTotal + objetoDetalleFactura.montoImpuesto;
        factura.detalleFactura.push(objetoDetalleFactura);


        // actualiza totales de factura.
        factura.totalVenta = factura.totalVenta + objetoDetalleFactura.montoTotal;
        factura.totalDescuentos = factura.totalDescuentos + objetoDetalleFactura.montoDescuento;
        factura.totalImpuesto =  factura.totalImpuesto + objetoDetalleFactura.montoImpuesto;
        //
    });
    // totales de factura.
    // exonera y grava de mercancias y servicios
    factura.totalServGravados = 0;
    factura.totalServExentos = 0;
    factura.totalMercanciasGravadas = factura.totalVenta;
    factura.totalMercanciasExentas = 0;
    factura.totalGravado = factura.totalServGravados + factura.totalMercanciasGravadas;
    factura.totalExento = factura.totalServExentos  + factura.totalMercanciasExentas;
    factura.totalVenta = factura.totalGravado + factura.totalExento;
    // total venta neta.
    factura.totalVentaneta =  factura.totalVenta - factura.totalDescuentos;
    // total comprobante.
    factura.totalComprobante = factura.totalVentaneta + factura.totalImpuesto;

    $.ajax({
        type: "POST",
        url: "class/factura.php",
        data: {
            action: miAccion,
            obj: JSON.stringify(factura),
            dataReceptor: JSON.stringify(receptor)
        }
    })
        .done(alertFact()
    
        )
        .fail(function (e) {
            producto.showError(e);
        })
        .always(function () {
            setTimeout('$("#btnProducto").removeAttr("disabled")', 1000);
            producto = new Producto();
            producto.ClearCtls();
            producto.Read;
            $("#inp_descripcion").focus();
        });
}