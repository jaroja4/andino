<?php
//ACTION
if(isset($_POST["action"])){
    $opt= $_POST["action"];
    unset($_POST['action']);
    // Classes
    require_once("conexion.php");
    require_once("usuario.php");
    require_once("entidad.php");
    require_once("invoice.php");
    require_once("facturacionElectronica.php");
    //require_once("tipoCambio.php");    
    require_once("receptor.php");    
    require_once("productosXFactura.php");
    require_once("encdes.php");
    // 
    // Session
    if (!isset($_SESSION))
        session_start();
    // Instance
    $factura= new Factura();
    switch($opt){
        case "ReadAllbyRange":
            echo json_encode($factura->ReadAllbyRange());
            break;
        case "read":
            echo json_encode($factura->read());
            break;
        case "create":
            echo json_encode($factura->create());
            break;
        case "sendContingencia":
            echo json_encode($factura->contingencia());
            break;
        case "sendContingenciaMasiva":
            $factura->sendContingenciaMasiva();
            break;
        case "sendNotaCredito":
            // Nota de Credito.
            $factura->idDocumentoNC= $_POST["idDocumentoNC"] ?? 3; // documento tipo 3: NC
            $factura->idReferencia= $_POST["idReferencia"] ?? 1; // código de referencia: 1 : Referencia a otro documento.
            $factura->razon= $_POST["razon"]; // Referencia a otro documento.
            $factura->notaCredito();
            break;
        case "update":
            $factura->update();
            break;
        case "delete":
            echo json_encode($factura->delete());
            break; 
        case "checkAll":
            echo json_encode($factura->checkAll());
            break; 
        case "enviarManual":
            $factura->enviarManual();
            break; 
        case "estado":
            echo json_encode($factura->estado());            
            break;
        case "resumenFacturacion":
            echo json_encode($factura->resumenFacturacion());            
            break; 
    }
}

class Factura{
    //Factura
    public $local="";
    public $terminal="";
    public $idCondicionVenta=null;
    public $clave=null;
    public $consecutivoFE=null;
    public $idSituacionComprobante=null;
    public $idEstadoComprobante= null;
    public $idMedioPago=null;
    public $idDocumento = null; // FE - TE - ND - NC ...  documento para envio MH
    public $fechaEmision="";
    public $totalVenta=null; //Precio del producto.
    public $totalDescuentos=null;
    public $totalVentaneta=null;
    public $totalImpuesto=null;
    public $totalComprobante=null;
    public $idEmisor=null;
    public $detalleFactura = [];
    public $datosReceptor = [];
    public $datosEntidad = [];
    public $lista= [];// Se usa para retornar los detalles de una factura
    public $consecutivo= [];
    public $usuario="";
    public $nombreEntidad="";
    public $plazoCredito= null;
    public $idCodigoMoneda= null;
    public $tipoCambio= null;
    public $montoEfectivo= null;
    public $montoTarjeta= null;
    // Referencia
    public $idDocumentoReferencia = null;  // utilizado en COMPROBANTE EMITIDO DESPUES DE UNA NC.
    public $claveReferencia = null;
    public $fechaEmisionReferencia = null;
    // NC
    public $idDocumentoNC = null;
    public $claveNC = null;
    public $idReferencia = null;
    public $fechaEmisionNC = null;
    public $razon=null;
    //
    public $extraMails=null;
    public $fechaInicial = "";
    public $fechaFinal = "";


    function __construct(){
        if(isset($_POST["id"])){
            $this->id= $_POST["id"];
        }
        if(isset($_POST["obj"])){
            $obj= json_decode($_POST["obj"],true);
            //Necesarias para la factura (Segun M Hacienda)
            require_once("UUID.php");
            // a. Datos de encabezado
            $this->id= $obj["id"] ?? UUID::v4();
            $this->extraMails= $obj["extraMails"] ?? null;  //  fecha de creacion en base de datos      
            $this->fechaCreacion= $obj["fechaCreacion"] ?? null;  //  fecha de creacion en base de datos 
            $this->idEntidad= $obj["idEntidad"] ?? $_SESSION["userSession"]->idEntidad;            
            $this->consecutivo= $obj["consecutivo"] ?? null;
            $this->local= '001';//$obj["local"] ?? $_SESSION["userSession"]->local;
            $this->terminal= $obj["terminal"] ?? '00001'; //$obj["terminal"] ?? $_SESSION["userSession"]->terminal;
            $this->idCondicionVenta= $obj["idCondicionVenta"] ?? 1;
            $this->idSituacionComprobante= $obj["idSituacionComprobante"] ?? 1;
            $this->idEstadoComprobante= $obj["idEstadoComprobante"] ?? 1;
            $this->plazoCredito= $obj["plazoCredito"] ?? 0;
            $this->idMedioPago= $obj["idMedioPago"] ?? 1;
            // c. Resumen de la factura/Total de la Factura 
            // definir si es servicio o mercancia (producto).
            $this->idCodigoMoneda= $obj["idCodigoMoneda"] ?? 55; // CRC
            $this->tipoCambio= $obj['tipoCambio'] ?? 582.83; // tipo de cambio dinamico con BCCR
            $this->totalServGravados= $obj['totalServGravados'] ?? null;
            $this->totalServExentos= $obj['totalServExentos'] ?? null;
            $this->totalMercanciasGravadas= $obj['totalMercanciasGravadas'] ?? null;
            $this->totalMercanciasExentas= $obj['totalMercanciasExentas'] ?? null;
            $this->totalGravado= $obj['totalGravado'] ?? null;
            $this->totalExento= $obj['totalExento'] ?? null;
            $this->totalVenta= $obj["totalVenta"] ?? null;
            $this->totalDescuentos= $obj["totalDescuentos"] ?? null;
            $this->totalVentaneta= $obj["totalVentaneta"] ?? null;
            $this->totalImpuesto= $obj["totalImpuesto"] ?? null;
            $this->totalComprobante= $obj["totalComprobante"] ?? null;
            // $this->montoEfectivo= $obj["montoEfectivo"]; //Jason: Lo comente temporalmente. Carlos: temporalmente para siempre?
            // $this->montoTarjeta= $obj["montoTarjeta"];   //Jason: Lo comente temporalmente. Carlos: temporalmente para siempre?
            // d. Informacion de referencia

            
            $this->fechaInicial= $obj["fechaInicial"] ?? null;
            $this->fechaFinal= $obj["fechaFinal"] ?? null;


            $this->idDocumento = $obj["idDocumento"] ?? $_SESSION["userSession"]->idDocumento; // Documento de Referencia.            
            $this->fechaEmision= $obj["fechaEmision"] ?? null; // emision del comprobante electronico.
            //
            $this->idReceptor = $obj['idReceptor'] ?? Receptor::default()->id; // si es null, utiliza el Receptor por defecto.
            $this->idEmisor =  $_SESSION["userSession"]->idEntidad;  //idEmisor no es necesario, es igual al idEntidad.
            $this->idUsuario=  $_SESSION["userSession"]->id;
            // Detalle.
            if(isset($obj["detalleFactura"] )){
                foreach ($obj["detalleFactura"] as $itemDetalle) {
                    // b. Detalle de la mercancía o servicio prestado
                    $item= new ProductosXFactura();
                    $item->idFactura = $this->id;
                    $item->numeroLinea= $itemDetalle['numeroLinea'];
                    $item->idTipoCodigo= $itemDetalle['idTipoCodigo']?? 1;
                    $item->codigo= $itemDetalle['codigo'] ?? 999;
                    $item->cantidad= $itemDetalle['cantidad'] ?? 1;
                    $item->idUnidadMedida= $itemDetalle['idUnidadMedida'] ?? 78;
                    $item->detalle= $itemDetalle['detalle'];
                    $item->precioUnitario= $itemDetalle['precioUnitario'];                    
                    $item->montoTotal= $itemDetalle['montoTotal'];
                    $item->montoDescuento= $itemDetalle['montoDescuento'];
                    $item->naturalezaDescuento= $itemDetalle['naturalezaDescuento']??'No aplican descuentos'; 
                    $item->subTotal= $itemDetalle['subTotal'];
                    $item->idExoneracionImpuesto= $itemDetalle['idExoneracionImpuesto'] ?? null;
                    $item->codigoImpuesto= $itemDetalle['codigoImpuesto'] ?? 1; // impuesto ventas = 1
                    $item->tarifaImpuesto= $itemDetalle['tarifaImpuesto'];
                    $item->montoImpuesto= $itemDetalle['montoImpuesto'];                    
                    $item->montoTotalLinea= $itemDetalle['montoTotalLinea']; // subtotal + impuesto.
                    array_push ($this->detalleFactura, $item);
                }
            }
            // Receptor ó Cliente.
            if(isset($_POST["dataReceptor"] )){
                $this->datosReceptor = new Receptor();
                $this->datosReceptor = json_decode($_POST["dataReceptor"],true);
            }
            // Referencias.
            if(isset($obj["ref"] )){
                foreach ($obj["ref"] as $ref) {
                    $factura->idDocumentoNC= $ref["idDocumentoNC"]; // documento al que se hace referencia.
                    $factura->idReferencia= $ref["idReferencia"]; // código de referencia: 4 : Referencia a otro documento.
                    $factura->razon= $ref["razon"]; // Referencia a otro documento.
                }                
            }

        }
    }

    function ReadAllbyRange(){
        try {
            $efectivo = 0;
            $tarjeta = 0;
            $sql='SELECT id, fechaCreacion, consecutivo, idEstadoComprobante, totalComprobante, idMedioPago
                FROM storylabsFE.factura
                WHERE idEntidad= :idEntidad AND
                fechaCreacion Between :fechaInicial and :fechaFinal
                ORDER BY consecutivo DESC;';
            $param= array(':idEntidad'=>$_SESSION["userSession"]->idEntidad, ':fechaInicial'=>$this->fechaInicial, ':fechaFinal'=>$this->fechaFinal);            
            $data= DATA::Ejecutar($sql, $param);
            if($data){
                foreach ($data as $key => $factura){
                    switch ($factura['idMedioPago']) {
                        case "1":
                            $efectivo = $efectivo + $factura['totalComprobante'];
                            break;
                        case "2":
                            $tarjeta = $tarjeta + $factura['totalComprobante'];
                            break;
                    }
                    $objFacturas = new stdClass();
                    $objFacturas->facturas = $data;
                    $objFacturas->totalEfectivo = $efectivo;
                    $objFacturas->totalTarjeta = $tarjeta;          
                }
                return $objFacturas;
            }
        }     
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }
    
    function enviarManual(){        
        if ($this->extraMails){
            $this->extraMails = preg_replace('/\s+/', '', $this->extraMails);            
            if ( $this->extraMails[ strlen($this->extraMails)-1 ]  == ";"){
                $this->extraMails = substr( $this->extraMails, 0 , strlen($this->extraMails)-1);
            }
            $this->extraMails = explode(";",$this->extraMails);            
            Invoice::$email_array_address_to = $this->extraMails;
        }
        Invoice::Create($this->read());
    }

    function read(){
        try {
            $sql='SELECT idEntidad, fechaCreacion, consecutivo, clave, consecutivoFE, local, terminal, idCondicionVenta, idSituacionComprobante, idEstadoComprobante, plazoCredito, 
                    idMedioPago, idCodigoMoneda, tipoCambio, totalServGravados, totalServExentos, totalMercanciasGravadas, totalMercanciasExentas, totalGravado, totalExento, fechaEmision, idDocumento, 
                    totalVenta, totalDescuentos, totalVentaneta, totalImpuesto, totalComprobante, idReceptor, idEmisor, idUsuario, idDocumentoNC, claveNC, fechaEmisionNC,
                    idReferencia, razon, idEstadoNC
                from factura
                where id=:id';
            $param= array(':id'=>$this->id);
            $data= DATA::Ejecutar($sql,$param);     
            if(count($data)){
                $this->idEntidad = $data[0]['idEntidad'];
                // $this->nombreEntidad = Debe mostrar el nombre de la entidad.
                $this->fechaCreacion = $data[0]['fechaCreacion'];
                $this->consecutivo = $data[0]['consecutivo'] ?? null;
                $this->clave = $data[0]['clave'] ?? null;
                $this->consecutivoFE = $data[0]['consecutivoFE'] ?? null;
                $this->local = $data[0]['local'];
                $this->terminal = $data[0]['terminal'];
                $this->idCondicionVenta = $data[0]['idCondicionVenta'];
                $this->idSituacionComprobante = $data[0]['idSituacionComprobante'];
                $this->idEstadoComprobante = $data[0]['idEstadoComprobante'];
                $this->plazoCredito = $data[0]['plazoCredito'];
                $this->idMedioPago = $data[0]['idMedioPago'];
                $this->idCodigoMoneda = $data[0]['idCodigoMoneda'];
                $this->tipoCambio = $data[0]['tipoCambio'];
                $this->totalServGravados = $data[0]['totalServGravados'];
                $this->totalServExentos = $data[0]['totalServExentos'];
                $this->totalMercanciasGravadas = $data[0]['totalMercanciasGravadas'];
                $this->totalMercanciasExentas = $data[0]['totalMercanciasExentas'];
                $this->totalGravado = $data[0]['totalGravado'];
                $this->totalExento = $data[0]['totalExento'];
                $this->fechaEmision = $data[0]['fechaEmision'];
                $this->idDocumento = $data[0]['idDocumento'];
                $this->totalVenta = $data[0]['totalVenta'];
                $this->totalDescuentos = $data[0]['totalDescuentos'];
                $this->totalVentaneta = $data[0]['totalVentaneta'];
                $this->totalImpuesto = $data[0]['totalImpuesto'];
                $this->totalComprobante = $data[0]['totalComprobante'];
                $this->idReceptor = $data[0]['idReceptor'];
                $this->idEmisor = $data[0]['idEmisor'];
                $this->idUsuario = $data[0]['idUsuario'];
                $this->idDocumentoNC = $data[0]['idDocumentoNC'];
                $this->claveNC = $data[0]['claveNC'];
                $this->fechaEmisionNC = $data[0]['fechaEmisionNC'];
                $this->idReferencia = $data[0]['idReferencia'];
                $this->razon = $data[0]['razon'];
                $this->idEstadoNC = $data[0]['idEstadoNC'];
                // $this->usuario =  nombre de la persona que hizo la transaccion
                $this->detalleFactura= ProductosXFactura::read($this->id);
                $receptor = new Receptor();
                $receptor->id = $this->idReceptor;
                $this->datosReceptor = $receptor->read();
                $entidad = new Entidad();
                $entidad->id = $this->idEntidad;
                $this->datosEntidad = $entidad->read();
                //
                return $this;
            }
            else return null;
        }     
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al leer el factura'))
            );
        }
    }

    function estado(){
        try {
            $sql='SELECT idEstadoComprobante, count(idEstadoComprobante)  as cantidad
            FROM storylabsFE.factura
            where idEntidad=:idEntidad
            group by idEstadoComprobante
            order by fechaCreacion desc';
            $param= array(':idEntidad'=>$_SESSION['userSession']->idEntidad);
            $data= DATA::Ejecutar($sql,$param);
            if(count($data)){
                $estado = array();
                foreach ($data as $key => $transaccion){
                    $resp = array();
                    $resp["idEstadoComprobante"] = $transaccion['idEstadoComprobante'];
                    $resp["cantidad"] = $transaccion['cantidad'];
                    array_push ($estado, $resp);
                }
                return $estado;
            }
            else return null;
        }     
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al leer el factura'))
            );
        }
    }

    function resumenFacturacion(){
        try {
            $sql='SELECT 
                DATE_FORMAT(fechaCreacion, "%M") as mes, 
                count(DATE_FORMAT(fechaCreacion, "%M")) as cantidad, 
                truncate(sum(totalVentaneta), 2) as totalVentaneta,
                truncate(sum(totalImpuesto), 2) as totalImpuesto,
                truncate(sum(totalComprobante), 2) as totalComprobante
                FROM
                    storylabsFE.factura
                WHERE
                    idEntidad =:idEntidad
                GROUP BY mes
                ORDER BY fechaCreacion;';
            $param= array(':idEntidad'=>$_SESSION['userSession']->idEntidad);
            $data= DATA::Ejecutar($sql,$param);
            if(count($data)){
                $reporte = array();
                $label = array();
                $totales = array();
                // resumen de totales
                $resp = new factura;
                foreach ($data as $key => $transaccion){
                    // $resp["cantidad"] += $transaccion['cantidad'];
                    $resp->totalVentaneta += $transaccion['totalVentaneta'];
                    $resp->totalImpuesto += $transaccion['totalImpuesto'];
                    $resp->totalComprobante += $transaccion['totalComprobante'];
                    //
                    array_push ($label, $transaccion['mes']);
                    array_push ($totales, $transaccion['totalComprobante']);
                }
                array_push($reporte, $label);
                array_push($reporte, $totales);
                array_push($reporte, $resp);
                return $reporte;
            }
            else return null;
        }     
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al leer el factura'))
            );
        }
    }

    function create(){
        try {
            if (strlen($this->datosReceptor["identificacion"]) != 0){
                if( Receptor::CheckidReceptor($this->datosReceptor["identificacion"])['status'] == 0){
                    Receptor::create($this->datosReceptor);
                    $this->idReceptor = $this->datosReceptor['id'];
                }             
            }
            if($this->idDocumento==99)
                $this->idEstadoComprobante=99;
            $sql="INSERT INTO factura   (id, idEntidad, local, terminal, idCondicionVenta, idSituacionComprobante, idEstadoComprobante, plazoCredito, 
                idMedioPago, idCodigoMoneda, tipoCambio, totalServGravados, totalServExentos, totalMercanciasGravadas, totalMercanciasExentas, totalGravado, totalExento, idDocumento, 
                totalVenta, totalDescuentos, totalVentaneta, totalImpuesto, totalComprobante, idReceptor, idEmisor, idUsuario, montoEfectivo)
            VALUES  (:uuid, :idEntidad, :local, :terminal, :idCondicionVenta, :idSituacionComprobante, :idEstadoComprobante, :plazoCredito,
                :idMedioPago, :idCodigoMoneda, :tipoCambio, :totalServGravados, :totalServExentos, :totalMercanciasGravadas, :totalMercanciasExentas, :totalGravado, :totalExento, :idDocumento, 
                :totalVenta, :totalDescuentos, :totalVentaneta, :totalImpuesto, :totalComprobante, :idReceptor, :idEmisor, :idUsuario, :montoEfectivo)";
            $param= array(':uuid'=>$this->id,
                ':idEntidad'=>$this->idEntidad,
                ':local'=>$this->local,
                ':terminal'=>$this->terminal,
                ':idCondicionVenta'=>$this->idCondicionVenta,
                ':idSituacionComprobante'=>$this->idSituacionComprobante,
                ':idEstadoComprobante'=>$this->idEstadoComprobante,
                ':plazoCredito'=> $this->plazoCredito,                    
                ':idMedioPago'=>$this->idMedioPago,
                ':idCodigoMoneda'=>$this->idCodigoMoneda,
                ':tipoCambio'=>$this->tipoCambio,
                ':totalServGravados'=> $this->totalServGravados,
                ':totalServExentos'=> $this->totalServExentos,
                ':totalMercanciasGravadas'=> $this->totalMercanciasGravadas,
                ':totalMercanciasExentas'=> $this->totalMercanciasExentas,
                ':totalGravado'=> $this->totalGravado,
                ':totalExento'=> $this->totalExento,
                ':idDocumento'=> $this->idDocumento,
                ':totalVenta'=>$this->totalVenta,
                ':totalDescuentos'=>$this->totalDescuentos,
                ':totalVentaneta'=>$this->totalVentaneta,
                ':totalImpuesto'=>$this->totalImpuesto,
                ':totalComprobante'=>$this->totalComprobante,
                ':idReceptor'=>$this->idReceptor,
                ':idEmisor'=>$this->idEmisor,
                ':idUsuario'=>$this->idUsuario,
                ':montoEfectivo'=>$this->montoEfectivo);
            $data = DATA::Ejecutar($sql,$param, false);
            if($data)
            {
                //save array obj
                if(ProductosXFactura::create($this->detalleFactura)){
                    if($this->idDocumento!=99)
                        $this->enviarDocumentoElectronico();
                    //$this->temporalContingencia(); // pruebas de contingencia
                    //$this->temporalPruebaNC(); // pruebas de nota de credito. 
                    return true;
                }
                else throw new Exception('Error al guardar los productos.', 03);
            }
            else throw new Exception('Error al guardar.', 02);
        }     
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    function enviarDocumentoElectronico(){
        try {
            // consulta datos de factura en bd.
            $this->read();
            // envía la factura
            FacturacionElectronica::iniciar($this);
        }
        catch(Exception $e){
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
        }
    }

    public function sendContingenciaMasiva(){
        // busca facturas con error (5) y las reenvia con contingencia, para los documentos 1 - 4  (FE - TE)
        error_log("************************************************************");
        error_log("************************************************************");
        error_log("     [INFO] Iniciando Ejecución masiva de contingencia      ");
        error_log("************************************************************");
        error_log("************************************************************");
        $sql="SELECT f.id, e.nombre as entidad, consecutivo
            from factura f inner join entidad e on e.id = f.idEntidad
            WHERE  f.idEstadoComprobante = 5 and (f.idDocumento = 1 or  f.idDocumento = 4 or  f.idDocumento = 8) 
            ORDER BY consecutivo asc";
            //idEntidad=:idEntidad and
        // $param= array(':idEntidad'=>'0cf4f234-9479-4dcb-a8c0-faa4efe82db0');
        // $param= array(':idEntidad'=>'f787b579-8306-4d68-a7ba-9ae328975270'); // carlos.echc11.
        $data = DATA::Ejecutar($sql);
        error_log("[INFO] Total de transacciones en Contingencia: ". count($data));
        foreach ($data as $key => $transaccion){
            error_log("[INFO] Contingencia Entidad (". $transaccion['entidad'] .") Transaccion (".$transaccion['consecutivo'].")");
            $this->id = $transaccion['id'];
            $this->contingencia();
        }
        error_log("[INFO] Finaliza Contingencia Masiva de Comprobantes");
    }

    public function contingencia(){
        try {
            // idDocumento 08 = Comprobante emitido en contingencia.
            // SituacionComprobante 02 = Contingencia
            // Estado de Comprobante 01 = Sin enviar.
            $sql="UPDATE factura
                SET idSituacionComprobante=:idSituacionComprobante , idDocumento=:idDocumento, idEstadoComprobante=:idEstadoComprobante
                WHERE id=:id";
            $param= array(':id'=>$this->id, ':idSituacionComprobante'=>2 , ':idDocumento'=>8, ':idEstadoComprobante'=>1);
            $data = DATA::Ejecutar($sql,$param, false);
            if($data){
                // lee la transaccion completa y re envia
                $this->enviarDocumentoElectronico();                
                return true;
            }
            else throw new Exception('Error al actualizar la situación del comprobante en Contingencia.', 45656);            
        }     
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    public function notaCredito(){
        try {
            // check si ya existe la NC.
            $sql="SELECT id
                FROM factura
                WHERE id=:id and (idEstadoNC IS NULL OR idEstadoNC = 5 OR idEstadoNC = 1)";
            $param= array(':id'=>$this->id);
            $data = DATA::Ejecutar($sql,$param);
            // si hay comprobante sin NC, continua:
            if($data){
                // actualiza estado de comprobante con NC.
                $sql="UPDATE factura
                    SET idDocumentoNC=:idDocumentoNC, idReferencia=:idReferencia, razon=:razon, idEstadoNC=:idEstadoNC
                    WHERE id=:id";
                $param= array(
                    ':id'=>$this->id,
                    ':idDocumentoNC'=>$this->idDocumentoNC,
                    ':idReferencia'=>$this->idReferencia,
                    ':razon'=>$this->razon,
                    ':idEstadoNC'=>1);
                $data = DATA::Ejecutar($sql,$param, false);
                if($data)
                {
                    $this->read();
                    // envía la factura
                    FacturacionElectronica::iniciarNC($this);
                    return true;
                }
                else throw new Exception('Error al guardar.', 02);
            } else throw new Exception('Warning, el comprobante ('. $this->id .') ya tiene una Nota de Crédito asignada.', 0763);
        }     
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    public static function setClave($documento, $idFactura, $clave, $consecutivoFE=null){
        try {
            $sql='';
            $param= [];
            switch($documento){
                case 1: //fe
                case 4: //te
                case 8: //contingencia
                    $sql="UPDATE factura
                        SET clave=:clave, consecutivoFE=:consecutivoFE
                        WHERE id=:idFactura";
                    $param= array(':idFactura'=>$idFactura, ':clave'=>$clave, ':consecutivoFE'=>$consecutivoFE);
                break;
                case 3: // NC
                    $sql="UPDATE factura
                        SET claveNC=:claveNC
                        WHERE id=:idFactura";
                    $param= array(':idFactura'=>$idFactura, ':claveNC'=>$clave);
                break;
                case 5: // CCE 
                case 6: // CPCE 
                case 7: // RCE 
                    $sql="UPDATE mensajeReceptor
                        SET consecutivoFE=:consecutivoFE
                        WHERE id=:id";
                    $param= array(':id'=>$idFactura, ':consecutivoFE'=>$consecutivoFE);
                break;
            }
            //
            $data = DATA::Ejecutar($sql,$param, false);
            if($data)
                return true;
            else throw new Exception('Error al guardar el histórico.', 03);            
        }     
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            // debe notificar que no se esta actualizando el historico de comprobantes.
        }
    }

    public static function updateEstado($documento, $idFactura, $idEstadoComprobante, $fechaEmision){
        try {
            $sql='';
            $param= [];
            switch($documento){
                case 1: //fe
                case 4: //te
                case 8: //contingencia
                    $sql="UPDATE factura
                        SET idEstadoComprobante=:idEstadoComprobante, fechaEmision=:fechaEmision
                        WHERE id=:idFactura";
                    $param= array(':idFactura'=>$idFactura, ':idEstadoComprobante'=>$idEstadoComprobante, ':fechaEmision'=>$fechaEmision);
                break;
                case 3: // NC
                    $sql="UPDATE factura
                        SET idEstadoNC=:idEstadoNC, fechaEmisionNC=:fechaEmisionNC
                        WHERE id=:idFactura";
                    $param= array(':idFactura'=>$idFactura, ':idEstadoNC'=>$idEstadoComprobante, ':fechaEmisionNC'=>$fechaEmision);
                break;
                case 5: // CCE 
                case 6: // CPCE 
                case 7: // RCE 
                    $sql="UPDATE mensajeReceptor
                        SET idEstadoComprobante=:idEstadoComprobante, fechaEmision=:fechaEmision
                        WHERE id=:id";
                    $param= array(':id'=>$idFactura, ':idEstadoComprobante'=>$idEstadoComprobante, ':fechaEmision'=>$fechaEmision);
                break;
            }
            //
            $data = DATA::Ejecutar($sql,$param, false);
            if($data)
                return true;
            else throw new Exception('Error al guardar el histórico.', 03);            
        }     
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            // debe notificar que no se esta actualizando el historico de comprobantes.
        }
    }

    public static function updateIdEstadoComprobante($idFactura, $documento, $idEstadoComprobante){
        try {
            $sql='';
            $param= [];
            switch($documento){
                case 1: //fe
                case 4: //te
                case 8: //contingencia
                    $sql="UPDATE factura
                        SET idEstadoComprobante=:idEstadoComprobante
                        WHERE id=:idFactura";
                    $param= array(':idFactura'=>$idFactura, ':idEstadoComprobante'=>$idEstadoComprobante);
                break;
                case 3: // NC
                    $sql="UPDATE factura
                        SET idEstadoNC=:idEstadoNC
                        WHERE id=:idFactura";
                    $param= array(':idFactura'=>$idFactura, ':idEstadoNC'=>$idEstadoComprobante);
                break;
                case 5: // CCE 
                case 6: // CPCE 
                case 7: // RCE 
                    $sql="UPDATE mensajeReceptor
                        SET idEstadoComprobante=:idEstadoComprobante
                        WHERE id=:id";
                    $param= array(':id'=>$idFactura, ':idEstadoComprobante'=>$idEstadoComprobante);
                break;
            }
            //
            $data = DATA::Ejecutar($sql,$param, false);
            if($data)
                return true;
            else throw new Exception('Error al actualizar el estado del comprobante.', 0456);            
        }     
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            // debe notificar que no se esta actualizando el historico de comprobantes.
        }
    }

    public function checkAll(){
        try{
            // revisa todas las transacciones de la base de datos para actualizar su estado.            
            error_log("************************************************************");
            error_log("************************************************************");
            error_log("      [INFO] Iniciando Consulta masiva de comprobantes      ");
            error_log("************************************************************");
            error_log("************************************************************");
            $totalConsultas=0;
            $sql='SELECT f.id, consecutivo, e.nombre as entidad, consecutivo
                from factura f inner join entidad e on e.id = f.idEntidad
                /*WHERE f.idEntidad= "ea7a6cbd-5106-4712-a53d-37ab3cc04090" and consecutivo=266*/
                ORDER BY consecutivo asc';
            $data= DATA::Ejecutar($sql);
            error_log("[INFO] Total de transacciones a comprobar: ". count($data));
            foreach ($data as $key => $transaccion){
                error_log("[INFO] Consulta Entidad (". $transaccion['entidad'] .") Transaccion (".$transaccion['consecutivo'].")");
                $factura = new Factura();
                $factura->id = $transaccion['id'];
                $factura = $factura->read();
                FacturacionElectronica::APIConsultaComprobante($factura);
                $totalConsultas = $key;
            }
            error_log("[INFO] Finaliza Consulta de Comprobantes");
            return $totalConsultas+1;
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
        }        
    }


}

?>