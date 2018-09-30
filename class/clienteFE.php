<?php
if(isset($_POST["action"])){
    $opt= $_POST["action"];
    unset($_POST['action']);
    // Classes
    require_once("Conexion.php");
    require_once("Usuario.php");
    require_once("encdes.php");
    // Session
    if (!isset($_SESSION))
        session_start();
    // Instance
    $clientefe= new ClienteFE();
    switch($opt){
        case "ReadAll":
            echo json_encode($clientefe->ReadAll());
            break;
        case "ReadProfile":
            echo json_encode($clientefe->ReadProfile());
            break;
        case "ReadAllTipoIdentificacion":
            echo json_encode($clientefe->ReadAllTipoIdentificacion());
            break;
        case "ReadAllUbicacion":
            $clientefe->idProvincia = $_POST['idProvincia'];
            $clientefe->idCanton = $_POST['idCanton'];
            $clientefe->idDistrito = $_POST['idDistrito'];
            echo json_encode($clientefe->ReadAllUbicacion());
            break;        
        case "ReadAllProvincia":
            echo json_encode($clientefe->ReadAllProvincia());
            break;
        case "ReadAllCanton":
            $clientefe->idProvincia = $_POST['idProvincia'];
            echo json_encode($clientefe->ReadAllCanton());
            break;
        case "ReadAllDistrito":
            $clientefe->idCanton = $_POST['idCanton'];
            echo json_encode($clientefe->ReadAllDistrito());
            break;
        case "ReadAllBarrio":
            $clientefe->idDistrito = $_POST['idDistrito'];
            echo json_encode($clientefe->ReadAllBarrio());
            break;
        case "Create":
            echo $clientefe->Create();
            break;
        case "Update":
            $clientefe->Update();
            break;
        case "APILogin":
            $clientefe->ReadProfile(); // lee el perfil del contribuyente y loguea al API.
            break;
        case "Delete":
            $clientefe->Delete();
            break;
        case "DeleteCertificado":
            $clientefe->certificado = $_POST['certificado'];
            $clientefe->DeleteCertificado();
            break;               
    }
}

class Provincia{
    public $id;
    public $value;
    public static function Read(){
        try {
            $sql= 'SELECT id, provincia as value
                FROM tropical.provincia';
            $data= DATA::Ejecutar($sql);
            $lista = [];
            foreach ($data as $key => $value){
                $item = new Provincia();
                $item->id = $value['id']; 
                $item->value = $value['value'];
                array_push ($lista, $item);
            }
            return $lista;
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }
}

class Canton{
    public $id;
    public $value;
    public static function Read($idProvincia){
        try {
            $sql= 'SELECT id, canton as value
                FROM tropical.canton
                WHERE idProvincia=:idProvincia';
            $param= array(':idProvincia'=>$idProvincia);
            $data= DATA::Ejecutar($sql,$param);
            $lista = [];
            foreach ($data as $key => $value){
                $item = new Canton();
                $item->id = $value['id']; 
                $item->value = $value['value'];
                array_push ($lista, $item);
            }
            return $lista;
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }
}

class Distrito{
    public $id;
    public $value;
    public static function Read($idCanton){
        try {
            $sql= 'SELECT id, distrito as value
                FROM tropical.distrito
                WHERE idCanton=:idCanton';
            $param= array(':idCanton'=>$idCanton);
            $data= DATA::Ejecutar($sql,$param);
            $lista = [];
            foreach ($data as $key => $value){
                $item = new Distrito();
                $item->id = $value['id']; 
                $item->value = $value['value'];
                array_push ($lista, $item);
            }
            return $lista;
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }
}

class Barrio{
    public $id;
    public $value;
    public static function Read($idDistrito){
        try {
            $sql= 'SELECT id, barrio as value
                FROM tropical.barrio
                WHERE idDistrito=:idDistrito';
            $param= array(':idDistrito'=>$idDistrito);
            $data= DATA::Ejecutar($sql,$param);
            $lista = [];
            foreach ($data as $key => $value){
                $item = new Barrio();
                $item->id = $value['id']; 
                $item->value = $value['value'];
                array_push ($lista, $item);
            }
            return $lista;
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }
}

class UbicacionCod{
    public $provincia;
    public $canton;
    public $distrito;
    public $barrio;
}

class ClienteFE{
    public $id=null;
    public $codigoSeguridad='';
    public $idCodigoPais='';    
    public $nombre='';
    public $idTipoIdentificacion=null;
    public $identificacion='';
    public $nombreComercial=null;
    public $idProvincia=null;
    public $idCanton=null;
    public $idDistrito=null;
    public $idBarrio=null;
    public $otrasSenas=null;
    public $idCodigoPaisTel=null;
    public $numTelefono=null;
    public $idCodigoPaisFax=null;
    public $numTelefonoFax=null;
    public $correoElectronico=null;
    public $pinp12=null;
    public $idBodega=null;
    public $filesize= null;
    public $filename= null;
    public $filetype= null;
    public $estadoCertificado= 1;
    public $sessionKey;
    public $downloadCode; // codigo de descarga del certificado para cifrar xml.
    public $apiUrl;
    //
    public $ubicacion= [];

    function __construct(){
        // identificador único
        if(isset($_POST["id"])){
            $this->id= $_POST["id"];
        }
        if(isset($_POST["idBodega"]))
            $this->idBodega= $obj["idBodega"];
        else $this->idBodega= $_SESSION['userSession']->idBodega;
        if(isset($_POST["objC"])){
            $obj= json_decode($_POST["objC"],true);
            require_once("UUID.php");
            $this->id= $obj["id"] ?? UUID::v4();         
            $this->codigoSeguridad= $obj["codigoSeguridad"];
            $this->nombre= $obj["nombre"] ?? '';   
            $this->idCodigoPais= $obj["idCodigoPais"] ?? null;                  
            $this->idTipoIdentificacion= $obj["idTipoIdentificacion"] ?? null;
            $this->identificacion= $obj["identificacion"] ?? null;
            $this->nombreComercial= $obj["nombreComercial"] ?? null;
            $this->idProvincia= $obj["idProvincia"] ?? null;
            $this->idCanton= $obj["idCanton"] ?? null;
            $this->idDistrito= $obj["idDistrito"] ?? null;
            $this->idBarrio= $obj["idBarrio"] ?? null;
            $this->otrasSenas= $obj["otrasSenas"] ?? null;
            $this->idCodigoPaisTel= $obj["idCodigoPaisTel"] ?? null;
            $this->numTelefono= $obj["numTelefono"] ?? null;
            //$this->idCodigoPaisFax= $obj["idCodigoPaisFax"] ?? null;
            //$this->numTelefonoFax= $obj["numTelefonoFax"] ?? null;
            $this->correoElectronico= $obj["correoElectronico"] ?? null;
            $this->username= $obj["username"] ?? null;
            $this->password= $obj["password"] ?? null;
            $this->certificado= $obj["certificado"] ?? null;
            $this->pinp12= $obj["pinp12"] ?? null;            
        }
    }

    function ReadAll(){
        try {
            $sql= '';
            $data= DATA::Ejecutar($sql);
            return $data;
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    function ReadAllTipoIdentificacion(){
        try {
            $sql= 'SELECT id, codigo, tipo as value
                FROM tipoIdentificacion';
            $data= DATA::Ejecutar($sql);
            return $data;
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    function ReadAllUbicacion(){
        try {
            array_push ($this->ubicacion,Provincia::Read());
            array_push ($this->ubicacion,Canton::Read($this->idProvincia));
            array_push ($this->ubicacion,Distrito::Read($this->idCanton));
            array_push ($this->ubicacion,Barrio::Read($this->idDistrito));
            return $this->ubicacion;
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    function ReadAllProvincia(){
        try {
            return Provincia::Read();            
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    function ReadAllCanton(){
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

    function ReadAllDistrito(){
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

    function ReadAllBarrio(){
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

    function Read(){
        try {
            $sql='SELECT id, codigoSeguridad, idCodigoPais, nombre, idTipoIdentificacion, identificacion, nombreComercial, idProvincia,idCanton, idDistrito, idBarrio, otrasSenas, 
            idCodigoPaisTel, numTelefono, correoElectronico, username, password, certificado, idBodega, pinp12
                FROM clienteFE  
                where id=:id';
            $param= array(':id'=>$this->id);
            $data= DATA::Ejecutar($sql,$param);
            return $data;
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar el producto'))
            );
        }
    }

    function ReadProfile($apilogin=true){
        try {
            $sql='SELECT id, codigoSeguridad, idCodigoPais, nombre, idTipoIdentificacion, identificacion, nombreComercial, idProvincia, idCanton, idDistrito, 
                idBarrio, otrasSenas, numTelefono, correoElectronico, username, password, pinp12, downloadCode
                FROM clienteFE  
                where idBodega=:idBodega';
            $param= array(':idBodega'=>$_SESSION['userSession']->idBodega);
            $data= DATA::Ejecutar($sql,$param);
            if($data){
                $this->id= $data[0]['id'];
                $this->codigoSeguridad= $data[0]['codigoSeguridad'];
                $this->idCodigoPais= $data[0]['idCodigoPais'];
                $this->nombre= $data[0]['nombre'];
                $this->idTipoIdentificacion= $data[0]['idTipoIdentificacion'];
                $this->identificacion= $data[0]['identificacion'];
                $this->nombreComercial= $data[0]['nombreComercial'];
                $this->idProvincia= $data[0]['idProvincia'];
                $this->idCanton= $data[0]['idCanton'];
                $this->idDistrito= $data[0]['idDistrito'];
                $this->idBarrio= $data[0]['idBarrio'];
                $this->otrasSenas= $data[0]['otrasSenas'];
                $this->numTelefono= $data[0]['numTelefono']; 
                $this->correoElectronico= $data[0]['correoElectronico'];
                $this->username= encdes::decifrar($data[0]['username']);
                $this->password= encdes::decifrar($data[0]['password']);
                $this->pinp12= encdes::decifrar($data[0]['pinp12']);
                $this->downloadCode= $data[0]['downloadCode'];
                // certificado
                $sql='SELECT certificado, cpath
                    FROM clienteFE  
                    where idBodega=:idBodega';
                $param= array(':idBodega'=>$this->idBodega);
                $data= DATA::Ejecutar($sql,$param);
                $this->certificado= $data[0]['certificado'];
                $cpath = $data[0]['cpath'];
                // estado del certificado.
                if(file_exists('../../CU/'.$_SESSION['userSession']->idBodega.'/'.$cpath))
                    $this->estadoCertificado=1;
                else $this->estadoCertificado=0;      
                $this->certificado= encdes::decifrar($data[0]['certificado']);
                $_SESSION['API']= $this;
                if($apilogin)
                    $this->APILogin();
                return $this;
            }
            return null;
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar el producto'))
            );
        }
    }

    function Check(){
        try {
            $sql='SELECT id
                FROM clienteFE  
                where idBodega=:idBodega';
            $param= array(':idBodega'=>$_SESSION['userSession']->idBodega);
            $data= DATA::Ejecutar($sql,$param);
            if($data){
                return true;
            }
            return false;
        }     
        catch(Exception $e) {
            error_log("error: ". $e->getMessage());
            return false;
        }
    }

    function Create(){
        try {
            $sql="INSERT INTO clienteFE  (id, codigoSeguridad, idCodigoPais, nombre, idTipoIdentificacion, identificacion, nombreComercial, idProvincia,idCanton, idDistrito, idBarrio, otrasSenas, 
                idCodigoPaisTel, numTelefono, correoElectronico, username, password, certificado, idBodega, pinp12)
                VALUES (:id, :codigoSeguridad, :idCodigoPais, :nombre, :idTipoIdentificacion, :identificacion, :nombreComercial, :idProvincia, :idCanton, :idDistrito, :idBarrio, :otrasSenas, 
                    :idCodigoPaisTel, :numTelefono, :correoElectronico, :username, :password, :certificado, :idBodega, :pinp12);";
            $param= array(':id'=>$this->id,
                ':codigoSeguridad'=>$this->codigoSeguridad, 
                ':idCodigoPais'=>$this->idCodigoPais, 
                ':nombre'=>$this->nombre,
                ':idTipoIdentificacion'=>$this->idTipoIdentificacion,
                ':identificacion'=>$this->identificacion,
                ':nombreComercial'=>$this->nombreComercial,
                ':idProvincia'=>$this->idProvincia,
                ':idCanton'=>$this->idCanton,
                ':idDistrito'=>$this->idDistrito,
                ':idBarrio'=>$this->idBarrio,
                ':otrasSenas'=>$this->otrasSenas,
                ':idCodigoPaisTel'=>$this->idCodigoPaisTel,
                ':numTelefono'=>$this->numTelefono,
                ':correoElectronico'=>$this->correoElectronico,
                ':username'=>encdes::cifrar($this->username),
                ':password'=>encdes::cifrar($this->password),
                ':certificado'=>encdes::cifrar($this->certificado),
                ':idBodega'=>$this->idBodega,
                ':pinp12'=>encdes::cifrar($this->pinp12),
            );
            $data = DATA::Ejecutar($sql,$param,false);
            if($data)
            {
                //guarda api_base.users
                $this->getApiUrl();
                $ch = curl_init();
                $post = [
                    'w' => 'users',
                    'r' => 'users_register',
                    'fullName'   => $this->nombre,
                    'userName'   => $this->correoElectronico, // username dentro del API es el correo electronico del contribuyente.
                    'email'   => $this->correoElectronico,
                    'about'   => 'StoryLabsUser',
                    'country'   => 'CR',
                    'pwd'   => $this->password
                ];  
                curl_setopt_array($ch, array(
                    CURLOPT_URL => $this->apiUrl,
                    CURLOPT_RETURNTRANSFER => true,   
                    CURLOPT_VERBOSE => true,      
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 300,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $post
                ));
                $server_output = curl_exec($ch);
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $header = substr($server_output, 0, $header_size);
                $body = substr($server_output, $header_size);
                $error_msg = "";
                if (curl_error($ch)) {
                    $error_msg = curl_error($ch);
                    error_log("error: ". $error_msg);
                    throw new Exception('Error al crear usuario API MH. Comunicarse con Soporte Técnico', 055);
                }     
                error_log("error: ". $server_output);
                curl_close($ch);
                $this->APILogin();                
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

    function Update(){
        try {
            $sql="UPDATE clienteFE 
                SET nombre=:nombre, codigoSeguridad=:codigoSeguridad, idCodigoPais=:idCodigoPais, idTipoIdentificacion=:idTipoIdentificacion, 
                    identificacion=:identificacion, nombreComercial=:nombreComercial, idProvincia=:idProvincia, idCanton=:idCanton, idDistrito=:idDistrito, 
                    idBarrio=:idBarrio, otrasSenas=:otrasSenas, numTelefono=:numTelefono, correoElectronico=:correoElectronico, username=:username, password=:password, 
                    certificado=:certificado, idBodega=:idBodega, pinp12= :pinp12
                WHERE id=:id";
            $param= array(':id'=>$this->id, ':nombre'=>$this->nombre, ':codigoSeguridad'=>$this->codigoSeguridad, ':idCodigoPais'=>$this->idCodigoPais, ':idTipoIdentificacion'=>$this->idTipoIdentificacion,
                ':identificacion'=>$this->identificacion, ':nombreComercial'=>$this->nombreComercial, ':idProvincia'=>$this->idProvincia,
                ':idCanton'=>$this->idCanton, ':idDistrito'=>$this->idDistrito, ':idBarrio'=>$this->idBarrio,
                ':otrasSenas'=>$this->otrasSenas, ':numTelefono'=>$this->numTelefono, ':correoElectronico'=>$this->correoElectronico,
                ':username'=>encdes::cifrar($this->username), ':password'=>encdes::cifrar($this->password), ':certificado'=>encdes::cifrar($this->certificado), ':idBodega'=>$this->idBodega, 
                ':pinp12'=>encdes::cifrar($this->pinp12)
            );
            $data = DATA::Ejecutar($sql,$param,false);
            if($data){
                // ... modifica datos del cliente en el api ...//
                // ... sube el nuevo certificado ...//
                $this->APILogin();
                return true;
            }   
            else throw new Exception('Error al guardar.', 123);
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

    private function getApiUrl(){
        require_once('Globals.php');
        if (file_exists('../../../ini/config.ini')) {
            $set = parse_ini_file('../../../ini/config.ini',true); 
            $this->apiUrl = $set[Globals::app]['apiurl'];
        }         
        else throw new Exception('Acceso denegado al Archivo de configuración.',-1);
    }

    public function APILogin(){
        try{
            error_log("... API LOGIN ... ");
            //
            $this->getApiUrl();
            $ch = curl_init();
            $post = [
                'w' => 'users',
                'r' => 'users_log_me_in',
                'userName'   => $this->correoElectronico, // al API loguea con email
                'pwd'   => $this->password
            ];  
            curl_setopt_array($ch, array(
                CURLOPT_URL => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,   
                CURLOPT_VERBOSE => true,      
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post
            ));
            $server_output = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($server_output, 0, $header_size);
            $body = substr($server_output, $header_size);
            $error_msg = "";
            if (curl_error($ch)) {
                $error_msg = curl_error($ch);
                error_log("error: ". $error_msg);
                throw new Exception('Error al iniciar login API. '. $error_msg , 02);
            }
            curl_close($ch);
            // session de usuario ATV
            $sArray=json_decode($header);
            if(!isset($sArray->resp->sessionKey)){
                // ERROR CRITICO:
                // debe notificar al contibuyente. 
                throw new Exception('Error CRITICO al inciar sesion del API. DEBE COMUNICARSE CON SOPORTE TECNICO'. $error_msg , '66612');
            }
            $this->sessionKey= $sArray->resp->sessionKey;
            $_SESSION['API']->sessionKey= $this->sessionKey;
            error_log("sessionKey: ". $sArray->resp->sessionKey);
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

    public function APIUploadCert(){
        try{
            error_log(" subiendo certificado API CRL: ". $this->certificado);
            if (!file_exists($this->certificado)){
                throw new Exception('Error al guardar el certificado. El certificado no existe' , 002256);
            }
            $this->getApiUrl();
            $ch = curl_init();
            $post = [
                'w' => 'fileUploader',
                'r' => 'subir_certif',
                'sessionKey'=>$_SESSION['API']->sessionKey,
                'fileToUpload' => new CurlFile($this->certificado, 'application/x-pkcs12'),
                'iam'=>$_SESSION['API']->correoElectronico
            ];
            curl_setopt_array($ch, array(
                CURLOPT_URL => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,   
                CURLOPT_VERBOSE => true,                      
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post
            ));
            $server_output = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($server_output, 0, $header_size);
            $body = substr($server_output, $header_size);
            $error_msg = "";
            if (curl_error($ch)) {
                $error_msg = curl_error($ch);
                throw new Exception('Error al guardar el certificado. '. $error_msg , 033);
            }
            error_log("****** buscar : ". $server_output);
            $sArray= json_decode($server_output);
            if(!isset($sArray->resp->downloadCode)){
                // ERROR CRITICO:
                // debe notificar al contibuyente. 
                throw new Exception('Error CRITICO al leer downloadCode: '.$server_output, 0344);
            }
            // almacena dowloadCode en clienteFE
            $sql="UPDATE clienteFE
                SET downloadCode=:downloadCode
                WHERE idBodega=:idBodega";
            $param= array(':idBodega'=>$_SESSION['userSession']->idBodega, ':downloadCode'=>$sArray->resp->downloadCode);
            $data = DATA::Ejecutar($sql,$param, false);
            if($data)
                return true;
            else throw new Exception('Error al guardar el downloadCode.', 0345);
            //
            curl_close($ch);
            return true;
        } 
        catch(Exception $e) {
            error_log("****** Error: ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    private function CheckRelatedItems(){
        try{
            $sql="SELECT id
                FROM /*  definir relacion */ R
                WHERE R./*definir campo relacion*/= :id";                
            $param= array(':id'=>$this->id);
            $data= DATA::Ejecutar($sql, $param);
            if(count($data))
                return true;
            else return false;
        }
        catch(Exception $e){
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    function Delete(){
        try {              
            $sql='DELETE FROM clienteFE  
            WHERE id= :id';
            $param= array(':id'=>$this->id);
            $data= DATA::Ejecutar($sql, $param, false);
            if($data){
                return $sessiondata['status']=0; 
            }
            else throw new Exception('Error al eliminar.', 978);
        }
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    function DeleteCertificado(){
        try {
            //borra el certificado fisico
            $sql='SELECT cpath
                FROM clienteFE
                where idBodega=:idBodega';
            $param= array(':idBodega'=>$_SESSION['userSession']->idBodega);
            $data= DATA::Ejecutar($sql,$param);
            $cpath = $data[0]['cpath'];
            unlink('../../CU/'.$_SESSION['userSession']->idBodega.'/'.$cpath);   
            //borra registro
            $sql='UPDATE clienteFE
                SET certificado= "<eliminado por el usuario>", cpath= "", nkey= ""
                WHERE id= :id';
            $param= array(':id'=>$this->id);
            DATA::Ejecutar($sql, $param, false);                         
        }
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

}

?>