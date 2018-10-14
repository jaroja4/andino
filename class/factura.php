<?php
//ACTION
if(isset($_POST["action"])){
    $opt= $_POST["action"];
    unset($_POST['action']);
    // Classes
    require_once("conexion.php");
    require_once("usuario.php");
    require_once("entidad.php");
    require_once("facturacionElectronica.php");
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
        case "contingencia":
            $factura->contingencia();
            break;
        case "notaCredito":
            // Nota de Credito.
            $factura->idReferencia= $_POST["idReferencia"];
            $factura->razon= $_POST["razon"];
            $factura->notaCredito();
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
    public $clave=null;
    public $consecutivoFE=null;
    public $idSituacionComprobante=null;
    public $idEstadoComprobante= null;
    public $idMedioPago=null;
    public $idDocumentoReferencia = null; // FE - TE - ND - NC ...  documento para envio MH
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
    // NC
    public $idReferencia = null;
    public $razon=null;
    //
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
            $this->idDocumentoReferencia = $obj["idDocumentoReferencia"] ?? $_SESSION["userSession"]->idDocumentoReferencia; //codigo de documento de Referencia.            
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
        }
    }

    function readAll(){
        try {
            $sql='SELECT id, fechaCreacion, consecutivo, idEstadoComprobante, totalComprobante 
                FROM storylabsFE.factura
                WHERE idEntidad= "0dbcefdc-23f4-4d69-ba0e-491f773c16a1"
                ORDER BY consecutivo DESC;';
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
            $sql='SELECT idEntidad, fechaCreacion, consecutivo, clave, consecutivoFE, local, terminal, idCondicionVenta, idSituacionComprobante, idEstadoComprobante, plazoCredito, 
                    idMedioPago, idCodigoMoneda, tipoCambio, totalServGravados, totalServExentos, totalMercanciasGravadas, totalMercanciasExentas, totalGravado, totalExento, fechaEmision, idDocumentoReferencia, 
                    totalVenta, totalDescuentos, totalVentaneta, totalImpuesto, totalComprobante, idReceptor, idEmisor, idUsuario, idReferencia, razon
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
                $this->idDocumentoReferencia = $data[0]['idDocumentoReferencia'];
                $this->totalVenta = $data[0]['totalVenta'];
                $this->totalDescuentos = $data[0]['totalDescuentos'];
                $this->totalVentaneta = $data[0]['totalVentaneta'];
                $this->totalImpuesto = $data[0]['totalImpuesto'];
                $this->totalComprobante = $data[0]['totalComprobante'];
                $this->idReceptor = $data[0]['idReceptor'];
                $this->idEmisor = $data[0]['idEmisor'];
                $this->idUsuario = $data[0]['idUsuario'];
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

    function create(){
        try {
            if (strlen($this->datosReceptor["identificacion"]) != 0){
                if( Receptor::CheckidReceptor($this->datosReceptor["identificacion"])['status'] == 0){
                    Receptor::create($this->datosReceptor);
                    $this->idReceptor = $this->datosReceptor['id'];
                }             
            }
            $sql="INSERT INTO factura   (id, idEntidad, local, terminal, idCondicionVenta, idSituacionComprobante, idEstadoComprobante, plazoCredito, 
                idMedioPago, idCodigoMoneda, tipoCambio, totalServGravados, totalServExentos, totalMercanciasGravadas, totalMercanciasExentas, totalGravado, totalExento, idDocumentoReferencia, 
                totalVenta, totalDescuentos, totalVentaneta, totalImpuesto, totalComprobante, idReceptor, idEmisor, idUsuario, montoEfectivo)
            VALUES  (:uuid, :idEntidad, :local, :terminal, :idCondicionVenta, :idSituacionComprobante, :idEstadoComprobante, :plazoCredito,
                :idMedioPago, :idCodigoMoneda, :tipoCambio, :totalServGravados, :totalServExentos, :totalMercanciasGravadas, :totalMercanciasExentas, :totalGravado, :totalExento, :idDocumentoReferencia, 
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
                ':idDocumentoReferencia'=> $this->idDocumentoReferencia,
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
                    $this->enviarDocumentoElectronico();
                    //$this->temporalContingencia(); // pruebas de contingencia
                    $this->temporalPruebaNC(); // pruebas de nota de credito. 
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

    /******************************* temporalContingencia ******************************/
    public function temporalContingencia(){        
        $sql="SELECT id    
            FROM factura            
            WHERE idEntidad=:idEntidad and idEstadoComprobante = 5";
        $param= array(':idEntidad'=>'0cf4f234-9479-4dcb-a8c0-faa4efe82db0');
        $data = DATA::Ejecutar($sql,$param);
        foreach ($data as $key => $value){
            $this->id = $value['id'];
            $this->contingencia();                
        }        
    }    
    /******************************* temporalContingencia ******************************/

    /******************************* temporalPruebaNC ******************************/
    public function temporalPruebaNC(){        
        $sql="SELECT id    
            FROM factura            
            WHERE idEntidad=:idEntidad and idEstadoComprobante = 4";
        // $param= array(':idEntidad'=>'0cf4f234-9479-4dcb-a8c0-faa4efe82db0');
        $param= array(':idEntidad'=>'f787b579-8306-4d68-a7ba-9ae328975270'); // carlos.echc11.
        $data = DATA::Ejecutar($sql,$param);
        foreach ($data as $key => $value){
            $this->id = $value['id'];
            $this->razon= 'proceso interno.';
            $this->idReferencia= 1;
            $this->notaCredito();           
        }     
        include_once('feCallback.php');   
    }    
    /******************************* temporalPruebaNC ******************************/

    public function contingencia(){
        try {
            // idDocumentoReferencia 08 = Comprobante emitido en contingencia.
            // SituacionComprobante 02 = Contingencia
            // Estado de Comprobante 01 = Sin enviar.
            $sql="UPDATE factura
                SET idSituacionComprobante=:idSituacionComprobante , idDocumentoReferencia=:idDocumentoReferencia, idEstadoComprobante=:idEstadoComprobante
                WHERE id=:id";
            $param= array(':id'=>$this->id, ':idSituacionComprobante'=>2 , ':idDocumentoReferencia'=>8, ':idEstadoComprobante'=>1);
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

    function notaCredito(){
        try {
            $sql="UPDATE factura
                SET idReferencia=:idReferencia, razon=:razon, idDocumentoReferencia=:idDocumentoReferencia , idEstadoComprobante=:idEstadoComprobante
                WHERE id=:id";
            $param= array(
                ':id'=>$this->id,
                ':idReferencia'=>$this->idReferencia,
                ':razon'=>$this->razon,
                ':idDocumentoReferencia'=>3 , 
                ':idEstadoComprobante'=>1);
            $data = DATA::Ejecutar($sql,$param, false);
            if($data)
            {
                $this->enviarDocumentoElectronico();
                return true;
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

    public static function updateEstado($idFactura, $idEstadoComprobante, $fechaEmision, $clave=null, $consecutivoFE=null){
        try {
            $sql="UPDATE factura
                SET idEstadoComprobante=:idEstadoComprobante, fechaEmision=:fechaEmision, clave=:clave, consecutivoFE=:consecutivoFE
                WHERE id=:idFactura";
            $param= array(':idFactura'=>$idFactura, ':idEstadoComprobante'=>$idEstadoComprobante, ':fechaEmision'=>$fechaEmision, ':clave'=>$clave, ':consecutivoFE'=>$consecutivoFE);
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

    public static function updateIdEstadoComprobante($idFactura, $idEstadoComprobante){
        try {
            $sql="UPDATE factura
                SET idEstadoComprobante=:idEstadoComprobante
                WHERE id=:idFactura";
            $param= array(':idFactura'=>$idFactura, ':idEstadoComprobante'=>$idEstadoComprobante);
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


}

?>