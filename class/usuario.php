<?php
if(isset($_POST["action"])){
    $opt= $_POST["action"];
    unset($_POST['action']);
    // Classes
    require_once("conexion.php");
    require_once("clienteFE.php");
    require_once("encdes.php");
    // Session
    if (!isset($_SESSION))
        session_start();
    // Instance
    $usuario= new Usuario();
    switch($opt){       
        case "Login":
            $usuario->correoElectronico= $_POST["correoElectronico"];
            $usuario->password= $_POST["password"];
            $usuario->Login();
            echo json_encode($_SESSION['userSession']);
            break;   
        case "CheckSession":
            $usuario->CheckSession();
            echo json_encode($_SESSION['userSession']);
            break;
        case "EndSession":
            $usuario->EndSession();
            break;        
        case "CheckUsername":
            $usuario->correoElectronico= $_POST["correoElectronico"];
            echo json_encode($usuario->CheckUsername());
            break;
    }
}

abstract class userSessionStatus
{
    const invalido = 'invalido'; // login invalido
    const login = 'login'; // login ok; credencial ok
    const nocredencial= 'nocredencial'; // login ok; sin credenciales
    const inactivo= 'inactivo';
    const noexiste= 'noexiste';
}

class Usuario{
    public $id;
    public $correoElectronico;
    public $password;
    public $activo = 0;
    public $status = userSessionStatus::invalido; // estado de la sesion de usuario.
    public $url;
    public $idEmpresa; // empresa seleccionada en la sesión.
    public $empresa;

    function __construct(){
        // identificador único
        if(isset($_POST["id"])){
            $this->id= $_POST["id"];
        }
        if(isset($_POST["obj"])){
            $obj= json_decode($_POST["obj"],true);
            require_once("UUID.php");
            $this->id= $obj["id"] ?? UUID::v4();
            $this->nombre= $obj["nombre"] ?? '';  
            $this->correoElectronico= $obj["correoElectronico"] ?? '';
            $this->password= $obj["password"] ?? '';  
            $this->email= $obj["email"] ?? '';  
            $this->activo= $obj["activo"] ?? '';
        }
    }

    // login and user session

    function CheckSession(){
        if(isset($_SESSION["userSession"]->id)){
            // VALIDA SI TIENE CREDENCIALES PARA LA URL CONSULTADA
            $_SESSION['userSession']->status= userSessionStatus::nocredencial;
            $_SESSION['userSession']->url = $_POST["url"];
            $urlarr = explode('/', $_SESSION['userSession']->url);
            $myUrl = end($urlarr)==''?'index.html':end($urlarr);
            $_SESSION['userSession']->status= userSessionStatus::login;
        }
        else {
            $this->status= userSessionStatus::invalido;
            $this->url = $_POST["url"];
            $_SESSION["userSession"]= $this;
        }
    }

    function EndSession(){
        unset($_SESSION['userSession']);
    }

    function Login(){
        try {
            //Check activo & password.
            $sql= 'SELECT DISTINCT c.id, c.correoElectronico, c.nombre, activo, password, nombreComercial
                FROM contribuyente c
                where correoElectronico=:correoElectronico';
            $param= array(':correoElectronico'=>$this->correoElectronico);
            $data= DATA::Ejecutar($sql, $param);
            if($data){
                if($data[0]['activo']==0){
                    unset($_SESSION["userSession"]);
                    $this->status= userSessionStatus::inactivo;
                }
                else {
                    // usuario activo; check password
                    if(password_verify($this->password, $data[0]['password'])){
                        $this->id = $value['id'];
                        $this->correoElectronico = $value['correoElectronico'];
                        $this->nombre = $value['nombre'];
                        $this->nombreComercial = $value['nombreComercial'];
                        $this->activo = $value['activo'];
                        $this->status = userSessionStatus::login;
                        $this->url = isset($_SESSION['userSession']->url)? $_SESSION['userSession']->url : 'Dashboard.html'; // Url consultada
                    }
                    else { // password invalido
                        unset($_SESSION["userSession"]);
                        $this->status= userSessionStatus::invalido;
                    }
                }
            }
            else {
                unset($_SESSION["userSession"]);
                $this->status= userSessionStatus::noexiste;
            }
            // set user session.
            $_SESSION["userSession"]= $this;
        }     
        catch(Exception $e) { 
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            unset($_SESSION["userSession"]);
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        } 
    }

    function CheckUsername(){
        try{
            $sql="SELECT id
                FROM contribuyente
                WHERE correoElectronico= :correoElectronico";
            $param= array(':correoElectronico'=>$this->correoElectronico);
            $data= DATA::Ejecutar($sql, $param);
            if(count($data))
                $sessiondata['status']=1; // usuario duplicado
            else $sessiondata['status']=0; // usuario unico
            return $sessiondata;
        }
        catch(Exception $e){
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