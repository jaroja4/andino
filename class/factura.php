<?php
//ACTION
if(isset($_POST["action"])){
    $opt= $_POST["action"];
    unset($_POST['action']);
    // Classes
    require_once("conexion.php");
    require_once("usuario.php");
    require_once("entidad.php");
    require_once("facturaElectronica.php");
    //require_once("tipoCambio.php");    
    require_once("receptor.php");
    require_once("invoice.php");
    require_once("productosXFactura.php");
    require_once("encdes.php");
    // 
    // Session
    if (!isset($_SESSION))
        session_start();
    // Instance
    $factura= new Factura();
    switch($opt){
        case "readAll":
            echo json_encode($factura->readAll());
            break;
        case "read":
            echo json_encode($factura->read());
            break;
        case "create":
            echo json_encode($factura->create());
            break;
        case "enviarFE":
            $factura->enviarFE();
            break;
        case "update":
            $factura->update();
            break;
        case "delete":
            echo json_encode($factura->delete());
            break; 
    }
}

class Factura{
    //Factura
    public $local="";
    public $terminal="";
    public $idCondicionVenta=null;
    public $idSituacionComprobante=null;
    public $idEstadoComprobante= null;
    public $idMedioPago=null;
    public $fechaEmision="";
    public $totalVenta=null; //Precio del producto.
    public $totalDescuentos=null;
    public $totalVentaneta=null;
    public $totalImpuesto=null;
    public $totalComprobante=null;
    public $idEmisor=null;
    public $detalleFactura = [];
    public $datosReceptor = [];
    public $lista= [];// Se usa para retornar los detalles de una factura
    public $consecutivo= [];
    public $usuario="";
    public $empresa="";
    public $tipoDocumento=""; // FE - TE - ND - NC ...  documento para envio MH    
    public $plazoCredito= null;
    public $idCodigoMoneda= null;
    public $tipoCambio= null;
    public $montoEfectivo= null;
    public $montoTarjeta= null;
    //
    function __construct(){
        //
        // Inicia sesion de entidad FE sin login al api (false).
        // identificador único
        if(isset($_POST["id"])){
            $this->id= $_POST["id"];
        }
        if(isset($_POST["obj"])){
            $obj= json_decode($_POST["obj"],true);
            //Necesarias para la factura (Segun M Hacienda)
            require_once("UUID.php");
            // a. Datos de encabezado
            $this->id= $obj["id"] ?? UUID::v4();     
            $this->fechaCreacion= $obj["fechaCreacion"] ?? null;
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
            $this->totalServGravados= $obj['totalServGravados'];
            $this->totalServExentos= $obj['totalServExentos'];
            $this->totalMercanciasGravadas= $obj['totalMercanciasGravadas'];
            $this->totalMercanciasExentas= $obj['totalMercanciasExentas'];
            $this->totalGravado= $obj['totalGravado'];
            $this->totalExento= $obj['totalExento'];
            $this->totalVenta= $obj["totalVenta"];
            $this->totalDescuentos= $obj["totalDescuentos"];
            $this->totalVentaneta= $obj["totalVentaneta"];
            $this->totalImpuesto= $obj["totalImpuesto"];
            $this->totalComprobante= $obj["totalComprobante"];
            // $this->montoEfectivo= $obj["montoEfectivo"]; //Jason: Lo comente temporalmente
            // $this->montoTarjeta= $obj["montoTarjeta"];   //Jason: Lo comente temporalmente
            // d. Informacion de referencia
            $this->tipoDocumento = $obj["tipoDocumento"] ?? "FE"; // documento de Referencia.
            $this->codigoReferencia = $obj["codigoReferencia"] ?? "01"; //codigo de documento de Referencia.            
            $this->fechaEmision= $obj["fechaEmision"] ?? null; // emision del comprobante electronico.
            //
            $this->idReceptor = $obj['idReceptor'] ?? receptor::default()->id; // si es null, utiliza el receptor por defecto.
            $this->idEmisor =  $_SESSION["userSession"]->idEntidad;  //idEmisor no es necesario, es igual al idEntidad.
            $this->idUsuario=  $_SESSION["userSession"]->id; //Exception has occurred. Notice: Undefined variable: _SESSION //Jason: Lo comente temporalmente          
           
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
            
            if(isset($_POST["dataReceptor"] )){
                $this->datosReceptor = new receptor();
                $this->datosReceptor = json_decode($_POST["dataReceptor"],true);
            }        
        }
    }

    function readAll(){
        try {
            $sql='SELECT f.id, f.consecutivo, f.fechaCreacion, f.totalComprobante, f.montoEfectivo, f.montoTarjeta, b.nombre, u.userName
                FROM factura f
                INNER JOIN entidad b on f.idEntidad = b.id
                INNER JOIN usuario u on u.id = f.idUsuario   
                ORDER BY f.consecutivo asc';
            $data= DATA::Ejecutar($sql);
            return $data;
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

    function read(){
        try { 
            $sql='SELECT idEntidad, fechaCreacion, consecutivo, local, terminal, idCondicionVenta, idSituacionComprobante, idEstadoComprobante, plazoCredito, 
                    idMedioPago, idCodigoMoneda, tipoCambio, totalServGravados, totalServExentos, totalMercanciasGravadas, totalMercanciasExentas, totalGravado, totalExento, fechaEmision, codigoReferencia, 
                    totalVenta, totalDescuentos, totalVentaneta, totalImpuesto, totalComprobante, idReceptor, idEmisor, idUsuario, tipoDocumento
                from factura
                where id=:id';
            $param= array(':id'=>$this->id);
            $data= DATA::Ejecutar($sql,$param);     
            foreach ($data as $key => $value){
                $this->idEntidad = $value['idEntidad'];
                // $this->empresa = Debe mostrar el nombre de la entidad.
                $this->fechaCreacion = $value['fechaCreacion'];
                $this->consecutivo = $value['consecutivo'];
                $this->local = $value['local'];
                $this->terminal = $value['terminal'];
                $this->idCondicionVenta = $value['idCondicionVenta'];
                $this->idSituacionComprobante = $value['idSituacionComprobante'];
                $this->idEstadoComprobante = $value['idEstadoComprobante'];
                $this->plazoCredito = $value['plazoCredito'];
                $this->idMedioPago = $value['idMedioPago'];
                $this->idCodigoMoneda = $value['idCodigoMoneda'];
                $this->tipoCambio = $value['tipoCambio'];
                $this->totalServGravados = $value['totalServGravados'];
                $this->totalServExentos = $value['totalServExentos'];
                $this->totalMercanciasGravadas = $value['totalMercanciasGravadas'];
                $this->totalMercanciasExentas = $value['totalMercanciasExentas'];
                $this->totalGravado = $value['totalGravado'];
                $this->totalExento = $value['totalExento'];
                $this->fechaEmision = $value['fechaEmision'];
                $this->codigoReferencia = $value['codigoReferencia'];
                $this->totalVenta = $value['totalVenta'];
                $this->totalDescuentos = $value['totalDescuentos'];
                $this->totalVentaneta = $value['totalVentaneta'];
                $this->totalImpuesto = $value['totalImpuesto'];
                $this->totalComprobante = $value['totalComprobante'];
                $this->idReceptor = $value['idReceptor'];
                $this->idEmisor = $value['idEmisor'];
                $this->idUsuario = $value['idUsuario'];
                // $this->usuario =  nombre de la persona que hizo la transaccion
                $this->tipoDocumento = $value["tipoDocumento"];
                $this->detalleFactura= ProductosXFactura::read($this->id);
                $receptor = new Receptor();
                $receptor->id = $this->idReceptor;
                $this->datosReceptor = $receptor->read();
                $entidad = new Entidad();
                $entidad->id = $this->idEntidad;
                $this->datosEntidad = $entidad->read();
            }
            return $this;
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar el factura'))
            );
        }
    }

    function create(){
        try {
            $sql="INSERT INTO factura   (id, idEntidad, local, terminal, idCondicionVenta, idSituacionComprobante, idEstadoComprobante, plazoCredito, 
                idMedioPago, idCodigoMoneda, tipoCambio, totalServGravados, totalServExentos, totalMercanciasGravadas, totalMercanciasExentas, totalGravado, totalExento, codigoReferencia, 
                totalVenta, totalDescuentos, totalVentaneta, totalImpuesto, totalComprobante, idReceptor, idEmisor, idUsuario, tipoDocumento, montoEfectivo)
            VALUES  (:uuid, :idEntidad, :local, :terminal, :idCondicionVenta, :idSituacionComprobante, :idEstadoComprobante, :plazoCredito,
                :idMedioPago, :idCodigoMoneda, :tipoCambio, :totalServGravados, :totalServExentos, :totalMercanciasGravadas, :totalMercanciasExentas, :totalGravado, :totalExento, :codigoReferencia, 
                :totalVenta, :totalDescuentos, :totalVentaneta, :totalImpuesto, :totalComprobante, :idReceptor, :idEmisor, :idUsuario, :tipoDocumento, :montoEfectivo)"; 
           
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
                ':codigoReferencia'=> $this->codigoReferencia,
                ':totalVenta'=>$this->totalVenta,
                ':totalDescuentos'=>$this->totalDescuentos,
                ':totalVentaneta'=>$this->totalVentaneta,
                ':totalImpuesto'=>$this->totalImpuesto,
                ':totalComprobante'=>$this->totalComprobante,
                ':idReceptor'=>$this->idReceptor,
                ':idEmisor'=>$this->idEmisor,
                ':idUsuario'=>$this->idUsuario,
                ':tipoDocumento'=>$this->tipoDocumento,
                ':montoEfectivo'=>$this->montoEfectivo);
            $data = DATA::Ejecutar($sql,$param, false);
            if($data)
            {
                 //save array obj
                 if(ProductosXFactura::create($this->detalleFactura)){
                    if (strlen($this->datosReceptor["identificacion"]) != 0){
                        // $r = Receptor::CheckidReceptor($this->datosReceptor["identificacion"]);
                        if( Receptor::CheckidReceptor($this->datosReceptor["identificacion"])['status'] == 0){
                            if(Receptor::create($this->datosReceptor)){
                                // if(Invoice::create($this->datosReceptor, $this->detalleFactura)){                
                                // return true;
                                // }                     
                                return true;
                            }
                        }             
                        return true;
                    }
                    self::EnviarFE($this);
                    return true;
                }
                else throw new Exception('Error al guardar los productos.', 03);
            }
            else throw new Exception('Error al guardar.', 02);
        }     
        catch(Exception $e) {
            error_log("error: ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    function EnviarFE($datosFactura){
        try {
            // consulta datos de factura en bd.
            $this->read();
            // envía la factura
            FacturaElectronica::iniciar($this);
        }
        catch(Exception $e){}
    }

    public static function updateEstado($idFactura, $idEstadoComprobante, $fechaEmision, $clave){
        try {
            $sql="UPDATE factura
                SET idEstadoComprobante=:idEstadoComprobante, fechaEmision=:fechaEmision, clave=:clave
                WHERE id=:idFactura";
            $param= array(':idFactura'=>$idFactura, ':idEstadoComprobante'=>$idEstadoComprobante, ':fechaEmision'=>$fechaEmision, ':clave'=>$clave);
            $data = DATA::Ejecutar($sql,$param, false);
            if($data)
                return true;
            else throw new Exception('Error al guardar el histórico.', 03);            
        }     
        catch(Exception $e) {
            error_log("error: ". $e->getMessage());
            // debe notificar que no se esta actualizando el historico de comprobantes.
        }
    }

    public static function updateIdEstadoComprobante($idFactura, $idEstadoComprobante){
        try {
            $sql="UPDATE factura
                SET idEstadoComprobante=:idEstadoComprobante
                WHERE id=:idFactura";
            $param= array(':idFactura'=>$idFactura, ':idEstadoComprobante'=>$idEstadoComprobante);
            $data = DATA::Ejecutar($sql,$param, false);
            if($data)
                return true;
            else throw new Exception('Error al guardar el histórico.', 03);            
        }     
        catch(Exception $e) {
            error_log("error: ". $e->getMessage());
            // debe notificar que no se esta actualizando el historico de comprobantes.
        }
    }


}

?>