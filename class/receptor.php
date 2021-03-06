<?php
//
// datos del emisor de la factura
//
if(isset($_POST["action"])){
    $opt= $_POST["action"];
    unset($_POST['action']);
    // Classes
    require_once("conexion.php");
    require_once("entidad.php");
    // require_once("usuario.php");
    // require_once("encdes.php");
    // require_once("UUID.php");
    // Session
    if (!isset($_SESSION))
        session_start();
    // Instance
    $receptor= new Receptor();
    switch($opt){
        case "readAllTipoIdentificacion":
            echo json_encode($receptor->readAllTipoIdentificacion());
            break;
        case "readAllUbicacion":
            $receptor->idProvincia = $_POST['idProvincia'];
            $receptor->idCanton = $_POST['idCanton'];
            $receptor->idDistrito = $_POST['idDistrito'];
            echo json_encode($receptor->readAllUbicacion());
            break;      
        case "readAllProvincia":
            echo json_encode($receptor->readAllProvincia());
            break;
        case "readAllCanton":
            $receptor->idProvincia = $_POST['idProvincia'];
            echo json_encode($receptor->readAllCanton());
            break;
        case "readAllDistrito":
            $receptor->idCanton = $_POST['idCanton'];
            echo json_encode($receptor->readAllDistrito());
            break;
        case "readAllBarrio":
            $receptor->idDistrito = $_POST['idDistrito'];
            echo json_encode($receptor->readAllBarrio());
            break;
        case "CheckidReceptor":
            // $receptor->identificacion = $_POST['identificacion'];
            echo json_encode($receptor->CheckidReceptor($_POST['identificacion']));
            break; 
        case "create":
            echo $receptor->create();
            break;
        case "read":
            echo json_encode($receptor->read());
            break;
        case "readIdentificacionReceptor":
            echo json_encode($receptor->readIdentificacionReceptor($_POST['identificacion']));
            break; 
        // case "readAll":
        //     echo json_encode($receptor->readAll());
        //     break;
        // case "readProfile":
        //     echo json_encode($receptor->readProfile());
        //     break; 
        // case "update":
        //     $receptor->update();
        //     break;
        // case "APILogin":
        //     $receptor->readProfile(); // lee el perfil del entidad y loguea al API.
        //     break;
        // case "delete":
        //     $receptor->delete();
        //     break;
        // case "deleteCertificado":
        //     $receptor->certificado = $_POST['certificado'];
        //     $receptor->deleteCertificado();
        //     break;               
    }
}

class Receptor{
    public $id= null;
    public $nombre= null;
    public $idTipoIdentificacion= null;
    public $identificacion= null;
    public $identificacionExtranjero= null;
    public $nombreComercial= null;
    public $idProvincia= null;
    public $idCanton= null;
    public $idDistrito= null;
    public $idBarrio= null;
    public $otrasSenas= null;
    public $idCodigoPaisTel= null;
    public $numTelefono= null;
    public $idCodigoPaisFax= null;
    public $numTelefonoFax= null;
    public $correoElectronico= null;
    public $ubicacion= [];

    function __construct(){
        // identificador único
        if(isset($_POST["id"])){
            $this->id= $_POST["id"];
        }
        // if(isset($_POST["obj"])){}
    }
    
    public function read(){
        try{
            $sql= 'SELECT  id, nombre, idTipoIdentificacion, identificacion, identificacionExtranjero, nombreComercial, idProvincia, idCanton, idDistrito, idBarrio, otrasSenas, idCodigoPaisTel, numTelefono, idCodigoPaisFax, numTelefonoFax, correoElectronico
            FROM receptor
            WHERE id= :id';
            $param= array(':id'=> $this->id);
            $data= DATA::Ejecutar($sql, $param);        
            if($data){
                $this->nombre= $data[0]['nombre'];
                $this->idTipoIdentificacion= $data[0]['idTipoIdentificacion'];
                $this->identificacion= $data[0]['identificacion'];
                $this->identificacionExtranjero= $data[0]['identificacionExtranjero'];
                $this->nombreComercial= $data[0]['nombreComercial'];
                $this->idProvincia= $data[0]['idProvincia'];
                $this->idCanton= $data[0]['idCanton'];
                $this->idDistrito= $data[0]['idDistrito'];
                $this->idBarrio= $data[0]['idBarrio'];
                $this->otrasSenas= $data[0]['otrasSenas'];
                $this->idCodigoPaisTel= $data[0]['idCodigoPaisTel'];
                $this->numTelefono= $data[0]['numTelefono'];
                $this->idCodigoPaisFax= $data[0]['idCodigoPaisFax'];
                $this->numTelefonoFax= $data[0]['numTelefonoFax'];
                $this->correoElectronico= $data[0]['correoElectronico']; 
                return  $this;
            }
            else return null;
        }
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            if (!headers_sent()) {
                    header('HTTP/1.0 400 Error al generar al enviar el email');
                }
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar el receptor'))
            );
        }
    }

    public static function default(){
        $sql='SELECT r.id, nombre, idTipoIdentificacion, identificacion, identificacionExtranjero, nombreComercial, idProvincia, idCanton, idDistrito, idBarrio, otrasSenas, idCodigoPaisTel, numTelefono, idCodigoPaisFax, numTelefonoFax, correoElectronico
            FROM receptor r
            WHERE r.nombre="default"';
        $data= DATA::Ejecutar($sql);
        $receptor = new Receptor();
        if(count($data)){
            $receptor->id= $data[0]['id'];
            $receptor->nombre= $data[0]['nombre'];
            $receptor->idTipoIdentificacion= $data[0]['idTipoIdentificacion'];
            $receptor->identificacion= $data[0]['identificacion'];
            $receptor->identificacionExtranjero= $data[0]['identificacionExtranjero'];
            $receptor->nombreComercial= $data[0]['nombreComercial'];
            $receptor->idProvincia= $data[0]['idProvincia'];
            $receptor->idCanton= $data[0]['idCanton'];
            $receptor->idDistrito= $data[0]['idDistrito'];
            $receptor->idBarrio= $data[0]['idBarrio'];
            $receptor->otrasSenas= $data[0]['otrasSenas'];
            $receptor->idCodigoPaisTel= $data[0]['idCodigoPaisTel'];
            $receptor->numTelefono= $data[0]['numTelefono'];
            $receptor->idCodigoPaisFax= $data[0]['idCodigoPaisFax'];
            $receptor->numTelefonoFax= $data[0]['numTelefonoFax'];
            $receptor->correoElectronico= $data[0]['correoElectronico'];
            //return new self();
            return $receptor;
        }
        else return null;
    }

    function readAllUbicacion(){
        try {
            array_push ($this->ubicacion,Provincia::Read());
            array_push ($this->ubicacion,Canton::Read($this->idProvincia));
            array_push ($this->ubicacion,Distrito::Read($this->idCanton));
            array_push ($this->ubicacion,Barrio::Read($this->idDistrito));
            return $this->ubicacion;
        }     
        catch(Exception $e) { 
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            if (!headers_sent()) {
                    header('HTTP/1.0 400 Error al generar al enviar el email');
                }
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    function readAllTipoIdentificacion(){
        try {
            $sql= 'SELECT id, codigo, tipo as value
                FROM tipoIdentificacion';
            $data= DATA::Ejecutar($sql);
            return $data;
        }     
        catch(Exception $e) { 
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            if (!headers_sent()) {
                    header('HTTP/1.0 400 Error al generar al enviar el email');
                }
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    
    function readAllProvincia(){
        try {
            return Provincia::Read();            
        }     
        catch(Exception $e) { 
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            if (!headers_sent()) {
                    header('HTTP/1.0 400 Error al generar al enviar el email');
                }
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    function readAllCanton(){
        try {
            return Canton::Read($this->idProvincia);
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            if (!headers_sent()) {
                    header('HTTP/1.0 400 Error al generar al enviar el email');
                }
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    function readAllDistrito(){
        try {
            return Distrito::Read($this->idCanton);
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            if (!headers_sent()) {
                    header('HTTP/1.0 400 Error al generar al enviar el email');
                }
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    function readAllBarrio(){
        try {
            return Barrio::Read($this->idDistrito);
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            if (!headers_sent()) {
                    header('HTTP/1.0 400 Error al generar al enviar el email');
                }
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    public static function CheckidReceptor($identificacion){
        try{
            $sql="SELECT identificacion 
            FROM receptor
            WHERE identificacion = :identificacion;";
            $param= array(':identificacion'=>$identificacion);
            $data= DATA::Ejecutar($sql, $param);
            if($data)
                $identificacionData['status']=1; // usuario duplicado
            else $identificacionData['status']=0; // usuario unico
            return $identificacionData;
        }
        catch(Exception $e){
            if (!headers_sent()) {
                    header('HTTP/1.0 400 Error al generar al enviar el email');
                }
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    public static function create(&$receptor){
        try {
            $receptor['id'] = UUID::v4();
            $sql="INSERT INTO receptor (	id, nombre, idTipoIdentificacion, identificacion, identificacionExtranjero, nombreComercial, idProvincia, idCanton, idDistrito,
            idBarrio, otrasSenas, idCodigoPaisTel, numTelefono, correoElectronico)

            values(:id, :nombre, :idTipoIdentificacion, :identificacion, :identificacionExtranjero, :nombreComercial, :idProvincia, :idCanton, :idDistrito,
            :idBarrio, :otrasSenas, :idCodigoPaisTel, :numTelefono, :correoElectronico);";
            $param= array(':id'=>$receptor['id'],
                ':nombre'=>$receptor['nombre'],       
                ':idTipoIdentificacion'=>$receptor['idTipoIdentificacion'],                          
                ':identificacion'=>$receptor['identificacion'],                       
                ':identificacionExtranjero'=>$receptor['identificacionExtranjero'],                     
                ':nombreComercial'=>"Default",                   
                ':idProvincia'=>$receptor['idProvincia'],               
                ':idCanton'=>$receptor['idCanton'],                
                ':idDistrito'=>$receptor['idDistrito'],                   
                ':idBarrio'=>$receptor['idBarrio'],                    
                ':otrasSenas'=>$receptor['otrasSenas'],                 
                ':idCodigoPaisTel'=>$receptor['idCodigoPaisTel'],                    
                ':numTelefono'=>$receptor['numTelefono'],                  
                ':correoElectronico'=>$receptor['correoElectronico']               
            );
            $data = DATA::Ejecutar($sql,$param,false);
            if($data)
            {             
                return true;               
            }
            else throw new Exception('Error al guardar.', 02);
        }     
        catch(Exception $e) {
            error_log("error: ". $e->getMessage());
            if (!headers_sent()) {
                    header('HTTP/1.0 400 Error al generar al enviar el email');
                }
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    } 

    
    function readIdentificacionReceptor($id){
        $sql='SELECT 	id, nombre, idtipoidentificacion, identificacion, identificacionExtranjero, idProvincia, idCanton, idDistrito, idBarrio, otrasSenas, 
                numtelefono, correoelectronico
            FROM receptor 
            WHERE identificacion = :identificacion or identificacionExtranjero = :identificacion;';
        $param= array(':identificacion'=> $id);
        $data= DATA::Ejecutar($sql, $param);
        if($data){
            return  $data[0];
        }
        else return null;
    }

}

?>