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
    }
}

class NotaCredito extends  FacturacionElectronica{
    //
    function __construct(){
        if(isset($_POST["id"])){
            $this->id= $_POST["id"];
        }
    }

    function readAll(){
        try {
            $sql='';
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
                    idMedioPago, idCodigoMoneda, tipoCambio, totalServGravados, totalServExentos, totalMercanciasGravadas, totalMercanciasExentas, totalGravado, totalExento, fechaEmision, codigoReferencia, 
                    totalVenta, totalDescuentos, totalVentaneta, totalImpuesto, totalComprobante, idReceptor, idEmisor, idUsuario
                from factura
                where id=:id';
            $param= array(':id'=>$this->id);
            $data= DATA::Ejecutar($sql,$param);     
            foreach ($data as $key => $value){
                $this->idEntidad = $value['idEntidad'];
                // $this->nombreEntidad = Debe mostrar el nombre de la entidad.
                $this->fechaCreacion = $value['fechaCreacion'];
                $this->consecutivo = $value['consecutivo'] ?? null;
                $this->clave = $value['clave'] ?? null;
                $this->consecutivoFE = $value['consecutivoFE'] ?? null;
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
        catch(Exception $e) { 
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar el factura'))
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
                idMedioPago, idCodigoMoneda, tipoCambio, totalServGravados, totalServExentos, totalMercanciasGravadas, totalMercanciasExentas, totalGravado, totalExento, codigoReferencia, 
                totalVenta, totalDescuentos, totalVentaneta, totalImpuesto, totalComprobante, idReceptor, idEmisor, idUsuario, montoEfectivo)
            VALUES  (:uuid, :idEntidad, :local, :terminal, :idCondicionVenta, :idSituacionComprobante, :idEstadoComprobante, :plazoCredito,
                :idMedioPago, :idCodigoMoneda, :tipoCambio, :totalServGravados, :totalServExentos, :totalMercanciasGravadas, :totalMercanciasExentas, :totalGravado, :totalExento, :codigoReferencia, 
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
                ':codigoReferencia'=> $this->codigoReferencia,
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
                    $this->enviarFE();
                    //$this->temporal();
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

    function enviarFE(){
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

    public function temporal(){
        /******************************* temporal ***/
        $sql="SELECT id    
            FROM factura            
            WHERE idEntidad=:idEntidad and idEstadoComprobante = 5";
        $param= array(':idEntidad'=>'0cf4f234-9479-4dcb-a8c0-faa4efe82db0');
        $data = DATA::Ejecutar($sql,$param);
        foreach ($data as $key => $value){
            $this->id = $value['id'];
            $this->enviarContingencia();                
        }
        /***************************************** */
    }

    public function enviarContingencia(){
        try {
            // codigoReferencia 08 = Comprobante emitido en contingencia.
            // SituacionComprobante 02 = Contingencia
            $sql="UPDATE factura
                SET idSituacionComprobante=:idSituacionComprobante /*, codigoReferencia=:codigoReferencia*/
                WHERE id=:id";
            $param= array(':id'=>$this->id, ':idSituacionComprobante'=>2 /*, ':codigoReferencia'=>8*/);
            $data = DATA::Ejecutar($sql,$param, false);
            if($data){
                // lee la transaccion completa y re envia
                $this->read();
                $this->enviarFE();                
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