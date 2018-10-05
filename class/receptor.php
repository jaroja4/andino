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
            $receptor->identificacion = $_POST['identificacion'];
            echo json_encode($receptor->CheckidReceptor());
            break; 
        case "create":
            echo $receptor->create();
            break;
        case "read":
            echo json_encode($receptor->read($_POST['identificacion']));
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


///////////////////////////////
// class Provincia{
//     public $id;
//     public $value;
//     public static function Read(){
//         try {
//             $sql= 'SELECT id, provincia as value
//                 FROM provincia';
//             $data= DATA::Ejecutar($sql);
//             $lista = [];
//             foreach ($data as $key => $value){
//                 $item = new Provincia();
//                 $item->id = $value['id']; 
//                 $item->value = $value['value'];
//                 array_push ($lista, $item);
//             }
//             return $lista;
//         }     
//         catch(Exception $e) { 
//             error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
//             header('HTTP/1.0 400 Bad error');
//             die(json_encode(array(
//                 'code' => $e->getCode() ,
//                 'msg' => 'Error al cargar la lista'))
//             );
//         }
//     }
// }

// class Canton{
//     public $id;
//     public $value;
//     public static function Read($idProvincia){
//         try {
//             $sql= 'SELECT id, canton as value
//                 FROM canton
//                 WHERE idProvincia=:idProvincia';
//             $param= array(':idProvincia'=>$idProvincia);
//             $data= DATA::Ejecutar($sql,$param);
//             $lista = [];
//             foreach ($data as $key => $value){
//                 $item = new Canton();
//                 $item->id = $value['id']; 
//                 $item->value = $value['value'];
//                 array_push ($lista, $item);
//             }
//             return $lista;
//         }     
//         catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
//             header('HTTP/1.0 400 Bad error');
//             die(json_encode(array(
//                 'code' => $e->getCode() ,
//                 'msg' => 'Error al cargar la lista'))
//             );
//         }
//     }
// }

// class Distrito{
//     public $id;
//     public $value;
//     public static function Read($idCanton){
//         try {
//             $sql= 'SELECT id, distrito as value
//                 FROM distrito
//                 WHERE idCanton=:idCanton';
//             $param= array(':idCanton'=>$idCanton);
//             $data= DATA::Ejecutar($sql,$param);
//             $lista = [];
//             foreach ($data as $key => $value){
//                 $item = new Distrito();
//                 $item->id = $value['id']; 
//                 $item->value = $value['value'];
//                 array_push ($lista, $item);
//             }
//             return $lista;
//         }     
//         catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
//             header('HTTP/1.0 400 Bad error');
//             die(json_encode(array(
//                 'code' => $e->getCode() ,
//                 'msg' => 'Error al cargar la lista'))
//             );
//         }
//     }
// }

// class Barrio{
//     public $id;
//     public $value;
//     public static function Read($idDistrito){
//         try {
//             $sql= 'SELECT id, barrio as value
//                 FROM barrio
//                 WHERE idDistrito=:idDistrito';
//             $param= array(':idDistrito'=>$idDistrito);
//             $data= DATA::Ejecutar($sql,$param);
//             $lista = [];
//             foreach ($data as $key => $value){
//                 $item = new Barrio();
//                 $item->id = $value['id']; 
//                 $item->value = $value['value'];
//                 array_push ($lista, $item);
//             }
//             return $lista;
//         }     
//         catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
//             header('HTTP/1.0 400 Bad error');
//             die(json_encode(array(
//                 'code' => $e->getCode() ,
//                 'msg' => 'Error al cargar la lista'))
//             );
//         }
//     }
// }

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

    //Validar si son usadas
    public $idCodigoPais=''; 
    public $sessionKey;
    // --------------------//
    
    public static function read($id){
        $sql='SELECT r.id, nombre, idtipoidentificacion, identificacion, identificacionExtranjero, nombrecomercial, idProvincia, idCanton, idDistrito, idBarrio, otrasSenas, idCodigoPaisTel, numtelefono, idcodigopaisfax, numtelefonofax, correoelectronico
            FROM receptor r inner join factura f on f.idreceptor=r.id
            WHERE r.id= :identificacion';
        $param= array(':identificacion'=> $id);
        $data= DATA::Ejecutar($sql, $param);
        if(count($data)){
            self::$nombre= $data[0]['nombre'];
            self::$idTipoIdentificacion= $data[0]['idTipoIdentificacion'];
            self::$identificacion= $data[0]['identificacion'];
            self::$identificacionExtranjero= $data[0]['identificacionExtranjero'];
            self::$nombreComercial= $data[0]['nombreComercial'];
            self::$idProvincia= $data[0]['idProvincia'];
            self::$idCanton= $data[0]['idCanton'];
            self::$idDistrito= $data[0]['idDistrito'];
            self::$idBarrio= $data[0]['idBarrio'];
            self::$otrasSenas= $data[0]['otrasSenas'];
            self::$idCodigoPaisTel= $data[0]['idCodigoPaisTel'];
            self::$numTelefono= $data[0]['numTelefono'];
            self::$idCodigoPaisFax= $data[0]['idCodigoPaisFax'];
            self::$numTelefonoFax= $data[0]['numTelefonoFax'];
            self::$correoElectronico= $data[0]['correoElectronico']; 
            return  self;
        }
        else return null;
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
            header('HTTP/1.0 400 Bad error');
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
            header('HTTP/1.0 400 Bad error');
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
            header('HTTP/1.0 400 Bad error');
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
            header('HTTP/1.0 400 Bad error');
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
            header('HTTP/1.0 400 Bad error');
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
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    function CheckidReceptor(){
        try{
            $sql="SELECT identificacion 
            FROM receptor
            WHERE identificacion = :identificacion;";
            $param= array(':identificacion'=>$this->identificacion);
            $data= DATA::Ejecutar($sql, $param);
            if($data)
                $identificacionData['status']=1; // usuario duplicado
            else $identificacionData['status']=0; // usuario unico
            return $identificacionData;
        }
        catch(Exception $e){
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    public static function create($receptor){
        try {
            $id = $receptor['id'] ?? UUID::v4();  
            $sql="INSERT INTO receptor (	id, nombre, idTipoIdentificacion, identificacion, identificacionExtranjero, nombreComercial, idProvincia, idCanton, idDistrito,
            idBarrio, otrasSenas, idCodigoPaisTel, numTelefono, correoElectronico)

            values(:id, :nombre, :idTipoIdentificacion, :identificacion, :identificacionExtranjero, :nombreComercial, :idProvincia, :idCanton, :idDistrito,
            :idBarrio, :otrasSenas, :idCodigoPaisTel, :numTelefono, :correoElectronico);";
            $param= array(':id'=>$id,
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
                //guarda api_base.users
                // $this->getApiUrl();
                // $ch = curl_init();
                // $post = [
                //     'w' => 'users',
                //     'r' => 'users_register',
                //     'fullName'   => $this->nombre,
                //     'userName'   => $this->correoElectronico, // username dentro del API es el correo electronico del entidad.
                //     'email'   => $this->correoElectronico,
                //     'about'   => 'StoryLabsUser',
                //     'country'   => 'CR',
                //     'pwd'   => $this->password
                // ];  
                // curl_setopt_array($ch, array(
                //     CURLOPT_URL => $this->apiUrl,
                //     CURLOPT_RETURNTRANSFER => true,   
                //     CURLOPT_VERBOSE => true,      
                //     CURLOPT_MAXREDIRS => 10,
                //     CURLOPT_TIMEOUT => 300,
                //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //     CURLOPT_CUSTOMREQUEST => "POST",
                //     CURLOPT_POSTFIELDS => $post
                // ));
                // $server_output = curl_exec($ch);
                // $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                // $header = substr($server_output, 0, $header_size);
                // $body = substr($server_output, $header_size);
                // $error_msg = "";
                // if (curl_error($ch)) {
                //     $error_msg = curl_error($ch);
                //     error_log("error: ". $error_msg);
                //     throw new Exception('Error al crear usuario API MH. Comunicarse con Soporte Técnico', 055);
                // }     
                // error_log("error: ". $server_output);
                // curl_close($ch);
                // $this->APILogin();                
                return true;               
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



}

?>