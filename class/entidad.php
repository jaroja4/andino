<?php
if(isset($_POST["action"])){
    $opt= $_POST["action"];
    unset($_POST['action']);
    // Classes
    require_once("conexion.php");
    require_once("usuario.php");
    require_once("encdes.php");
    require_once("UUID.php");
    require_once("local.php");
    require_once("usuariosXEntidad.php");
    // Session
    if (!isset($_SESSION))
        session_start();
    // Instance
    $entidad= new Entidad();
    switch($opt){
        case "readAll":
            echo json_encode($entidad->readAll());
            break;
        case "readProfile":
            echo json_encode($entidad->readProfile());
            break;
        case "checkProfile":
            echo json_encode($entidad->checkProfile());
            break;
        case "readAllTipoIdentificacion":
            echo json_encode($entidad->readAllTipoIdentificacion());
            break;
        case "readAllUbicacion":
            $entidad->idProvincia = $_POST['idProvincia'];
            $entidad->idCanton = $_POST['idCanton'];
            $entidad->idDistrito = $_POST['idDistrito'];
            echo json_encode($entidad->readAllUbicacion());
            break;        
        case "readAllProvincia":
            echo json_encode($entidad->readAllProvincia());
            break;
        case "readAllCanton":
            $entidad->idProvincia = $_POST['idProvincia'];
            echo json_encode($entidad->readAllCanton());
            break;
        case "readAllDistrito":
            $entidad->idCanton = $_POST['idCanton'];
            echo json_encode($entidad->readAllDistrito());
            break;
        case "readAllBarrio":
            $entidad->idDistrito = $_POST['idDistrito'];
            echo json_encode($entidad->readAllBarrio());
            break;
        case "create":
            echo $entidad->create();
            break;
        case "update":
            $entidad->update();
            break;
        case "delete":
            $entidad->delete();
            break;
        case "deleteCertificado":
            $entidad->certificado = $_POST['certificado'];
            $entidad->deleteCertificado();
            break;
        case "checkUsername":
            $entidad->username= $_POST["username"];
            echo json_encode($entidad->checkUsername());
            break;

    }
}

class Provincia{
    public $id;
    public $value;
    public static function read(){
        try {
            $sql= 'SELECT id, provincia as value
                FROM provincia';
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
        catch(Exception $e) { 
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
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
    public static function read($idProvincia){
        try {
            $sql= 'SELECT id, canton as value
                FROM canton
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
    public static function read($idCanton){
        try {
            $sql= 'SELECT id, distrito as value
                FROM distrito
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
    public static function read($idDistrito){
        try {
            $sql= 'SELECT id, barrio as value
                FROM barrio
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

class Entidad{
    public $id=null;
    public $codigoSeguridad='';
    public $idCodigoPais='';    
    public $idDocumento= '';
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
    public $username=null;
    public $correoElectronico=null;
    public $pinp12=null;
    public $filesize= null;
    public $filename= null;
    public $filetype= null;
    public $estadoCertificado= 1;
    public $sessionKey;
    public $downloadCode; // codigo de descarga del certificado para cifrar xml.
    public $apiUrl;
    //
    public $ubicacion= [];
    public $locales= [];
    public $listaUsuarios= [];

    function __construct(){
        // identificador único
        if(isset($_POST["id"])){
            $this->id= $_POST["id"];
        }
        if(isset($_POST["objC"])){
            $obj= json_decode($_POST["objC"],true);            
            $this->id= $obj["id"] ?? UUID::v4();         
            $this->codigoSeguridad= $obj["codigoSeguridad"];
            $this->nombre= $obj["nombre"] ?? '';   
            $this->idCodigoPais= $obj["idCodigoPais"] ?? null;
            $this->idDocumento= $obj["idDocumento"] ?? 1; //1: FE.
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

    function readAll(){
        try {
            $sql= '';
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

    function readAllUbicacion(){
        try {
            array_push ($this->ubicacion,Provincia::read());
            array_push ($this->ubicacion,Canton::read($this->idProvincia));
            array_push ($this->ubicacion,Distrito::read($this->idCanton));
            array_push ($this->ubicacion,Barrio::read($this->idDistrito));
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

    function readAllProvincia(){
        try {
            return Provincia::read();            
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
            return Canton::read($this->idProvincia);
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
            return Distrito::read($this->idCanton);
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

    function readAllBarrio(){
        try {
            return Barrio::read($this->idDistrito);
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    function read(){
        try {
            $sql='SELECT id, codigoSeguridad, idCodigoPais, nombre, idTipoIdentificacion, identificacion, nombreComercial, idProvincia,idCanton, idDistrito, idBarrio, otrasSenas, 
            idCodigoPaisTel, numTelefono, correoElectronico, username, password, certificado, pinp12
                FROM entidad  
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

    static function checkProfile(){
        if(!isset($_SESSION['userSession']->idEntidad)){
            return false;
        }
        else {
            return true;
        }
    }

    function readProfile($apilogin=true){
        try {
            if(!isset($_SESSION['userSession']->idEntidad)){
                unset($_SESSION['API']);
                $this->id = null;
                $_SESSION['API'] = $this;
                return $_SESSION['API'];
            }
            //
            $sql='SELECT id, codigoSeguridad, idCodigoPais, nombre, idTipoIdentificacion, identificacion, nombreComercial, idProvincia, idCanton, idDistrito, 
                idBarrio, otrasSenas, numTelefono, correoElectronico, username, password, pinp12, downloadCode, certificado, cpath
                FROM entidad  
                where id=:id';
            $param= array(':id'=>$_SESSION['userSession']->idEntidad);
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
                $this->certificado= $data[0]['certificado'];
                $this->cpath = $data[0]['cpath'];
                // estado del certificado.
                //error_log('Buscando certificado:'.Globals::certDir.$this->id.DIRECTORY_SEPARATOR.$this->cpath);
                if(file_exists(Globals::certDir.$this->id.DIRECTORY_SEPARATOR.$this->cpath))
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
        catch(Exception $e) { 
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar el producto'))
            );
        }
    }

    function checkUsername(){
        try{
            // debe desencriptar el username almacenado en bd.
            $sql="SELECT username
                FROM entidad 
                WHERE username= :username";
            $param= array(':username'=>$this->username);
            $data= DATA::Ejecutar($sql, $param);
            if(count($data))
                $sessiondata['status']=1; // usuario duplicado
            else $sessiondata['status']=0; // usuario unico
            return $sessiondata;
        }
        catch(Exception $e){
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    function create(){
        try {
            $sql="INSERT INTO entidad  (id, codigoSeguridad, idCodigoPais, idDocumento, nombre, idTipoIdentificacion, identificacion, nombreComercial, idProvincia,idCanton, idDistrito, idBarrio, otrasSenas, 
                idCodigoPaisTel, numTelefono, correoElectronico, username, password, certificado, pinp12)
                VALUES (:id, :codigoSeguridad, :idCodigoPais, :idDocumento, :nombre, :idTipoIdentificacion, :identificacion, :nombreComercial, :idProvincia, :idCanton, :idDistrito, :idBarrio, :otrasSenas, 
                    :idCodigoPaisTel, :numTelefono, :correoElectronico, :username, :password, :certificado, :pinp12);";
            $param= array(':id'=>$this->id,
                ':codigoSeguridad'=>$this->codigoSeguridad, 
                ':idCodigoPais'=>$this->idCodigoPais,
                ':idDocumento'=>$this->idDocumento,
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
                ':pinp12'=>encdes::cifrar($this->pinp12),
            );
            $data = DATA::Ejecutar($sql,$param,false);
            if($data)
            {
                $_SESSION['userSession']->idEntidad= $this->id;
                $_SESSION['userSession']->nombreEntidad= $this->nombre;
                $_SESSION['userSession']->idDocumento= $this->idDocumento;
                //guarda api_base.users
                $this->getApiUrl();
                $ch = curl_init();
                $post = [
                    'w' => 'users',
                    'r' => 'users_register',
                    'fullName'   => $this->nombre,
                    'userName'   => $this->username, // username dentro del API es el correo electronico del entidad.
                    'email'   => $this->username,
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
                // Crea el local por defecto de la entidad.
                $localDef = new Local();
                $localDef->idEntidad = $this->id;
                $localDef->nombre = 'Local Inicial (por defecto)';
                $localDef->numeroLocal = '001';
                array_push ($this->locales, $localDef);
                Local::create($this->locales);
                // asigna el usuario a la entidad.
                $usuario= new usuariosXEntidad();
                $usuario->idUsuario = $_SESSION['userSession']->id; // usuario conectado.
                $usuario->idEntidad = $this->id; // id de entidad.
                array_push ($this->listaUsuarios, $usuario);
                UsuariosXEntidad::create($this->listaUsuarios);
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

    function update(){
        try {
            $sql="UPDATE entidad 
                SET nombre=:nombre, codigoSeguridad=:codigoSeguridad, idCodigoPais=:idCodigoPais, idTipoIdentificacion=:idTipoIdentificacion, 
                    identificacion=:identificacion, nombreComercial=:nombreComercial, idProvincia=:idProvincia, idCanton=:idCanton, idDistrito=:idDistrito, 
                    idBarrio=:idBarrio, otrasSenas=:otrasSenas, numTelefono=:numTelefono, correoElectronico=:correoElectronico, username=:username, password=:password, 
                    certificado=:certificado, pinp12= :pinp12
                WHERE id=:id";
            $param= array(':id'=>$this->id, ':nombre'=>$this->nombre, ':codigoSeguridad'=>$this->codigoSeguridad, ':idCodigoPais'=>$this->idCodigoPais, ':idTipoIdentificacion'=>$this->idTipoIdentificacion,
                ':identificacion'=>$this->identificacion, ':nombreComercial'=>$this->nombreComercial, ':idProvincia'=>$this->idProvincia,
                ':idCanton'=>$this->idCanton, ':idDistrito'=>$this->idDistrito, ':idBarrio'=>$this->idBarrio,
                ':otrasSenas'=>$this->otrasSenas, ':numTelefono'=>$this->numTelefono, ':correoElectronico'=>$this->correoElectronico,
                ':username'=>encdes::cifrar($this->username), ':password'=>encdes::cifrar($this->password), ':certificado'=>encdes::cifrar($this->certificado),
                ':pinp12'=>encdes::cifrar($this->pinp12)
            );
            $data = DATA::Ejecutar($sql,$param,false);
            if($data){
                // ... modifica datos del entidad en el api ...//
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
        require_once('globals.php');
        if (file_exists(Globals::configFile)) {
            $set = parse_ini_file(Globals::configFile,true); 
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
                'userName'   => $this->username, // al API loguea con email
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
            $_SESSION['API']->username= $this->username;
            error_log("sessionKey: ". $sArray->resp->sessionKey);
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
                'iam'=>$_SESSION['API']->username
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
            error_log("****** Certificado ****** : ". $server_output);
            $sArray= json_decode($server_output);
            if(!isset($sArray->resp->downloadCode)){
                // ERROR CRITICO:
                // debe notificar al contibuyente. 
                throw new Exception('Error CRITICO al leer downloadCode: '.$server_output, 0344);
            }
            // almacena dowloadCode en entidad
            $sql="UPDATE entidad
                SET downloadCode=:downloadCode
                WHERE id=:id";
            $param= array(':id'=>$_SESSION['userSession']->idEntidad, ':downloadCode'=>$sArray->resp->downloadCode);
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

    private function checkRelatedItems(){
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

    function delete(){
        try {
            $sql='DELETE FROM entidad  
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

    function deleteCertificado(){
        try {
            //borra el certificado fisico
            $sql='SELECT cpath
                FROM entidad
                where id=:id';
            $param= array(':id'=>$_SESSION['userSession']->idEntidad);
            $data= DATA::Ejecutar($sql,$param);
            $cpath = $data[0]['cpath'];
            unlink(Globals::certDir.$_SESSION['userSession']->idEntidad.'/'.$cpath);   
            //borra registro
            $sql='UPDATE entidad
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