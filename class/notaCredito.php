<?php
//ACTION
if(isset($_POST["action"])){
    $opt= $_POST["action"];
    unset($_POST['action']);
    // Classes
    require_once("conexion.php");
    //require_once("usuario.php");
    //require_once("entidad.php");
    require_once("factura.php");
    require_once("facturacionElectronica.php");
    //require_once("tipoCambio.php");    
    //require_once("receptor.php");
    //require_once("invoice.php");
    //require_once("productosXFactura.php");
    //require_once("encdes.php");
    // 
    // Session
    if (!isset($_SESSION))
        session_start();
    // Instance
    $notaCredito= new NotaCredito();
    switch($opt){
        case "readAll":
            echo json_encode($notaCredito->readAll());
            break;
        case "read":
            echo json_encode($notaCredito->read());
            break;
        case "create":
            echo json_encode($notaCredito->create());
            break;       
    }
}

class NotaCredito {
    public $idFactura = null;
    public $codigoReferencia = null;
    public $razon=null;
    //
    function __construct(){
        if(isset($_POST["id"])){
            $this->id= $_POST["id"];
        }
        if(isset($_POST["obj"])){
            $obj= json_decode($_POST["obj"],true);
            $this->idFactura= $obj["idFactura"];
            $this->idDocumentoReferencia= $obj["idDocumentoReferencia"] ?? 1;
            $this->codigoReferencia= $obj["codigoReferencia"] ?? 1;
            $this->razon= $obj["razon"] ?? null;
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
            $sql='';
            $param= array(':id'=>$this->id);
            $data= DATA::Ejecutar($sql,$param);     
            if(count($data)){
                $this->fechaCreacion = $data[0]['fechaCreacion'];
                return $this;
            }
            else return null;
        }     
        catch(Exception $e) { 
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la nota de crédito'))
            );
        }
    }

    function create(){
        try {
            $sql="INSERT INTO notaCredito   (id, idFactura, codigoReferencia, razon)
            VALUES  (uuid(), :idFactura, :codigoReferencia, :razon)";
            $param= array(
                ':idFactura'=>$this->idFactura,
                ':codigoReferencia'=>$this->codigoReferencia,
                ':razon'=>$this->razon);
            $data = DATA::Ejecutar($sql,$param, false);
            if($data)
            {
                $this->enviarNotaCredito();
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

    public function enviarNotaCredito(){
        try {
            // idDocumentoReferencia 03 = Comprobante cancelado, se realiza nota de credito.
            // Estado de Comprobante 01 = Sin enviar.
            $sql="UPDATE factura
                SET idDocumentoReferencia=:idDocumentoReferencia , idEstadoComprobante=:idEstadoComprobante
                WHERE id=:id";
            $param= array(':id'=>$this->idFactura, ':idDocumentoReferencia'=>3 , ':idEstadoComprobante'=>1);
            $data = DATA::Ejecutar($sql,$param, false);
            if($data){
                // lee la transaccion completa y re envia
                $factura= new Factura();
                $factura->id = $this->idFactura;
                $factura->read();
                $factura->codigoReferencia= $this->codigoReferencia;
                $factura->razon= $this->razon;
                FacturacionElectronica::iniciar($factura);
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
}

?>