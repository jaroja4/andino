<?php
    if(isset($_POST["action"])){
        $opt= $_POST["action"];
        unset($_POST['action']);
        require_once("conexion.php");
        require_once("usuario.php");
        require_once("encdes.php");
        require_once("invoice.php");
        require_once("globals.php");
        require_once("UUID.php");
         // Session
        if (!isset($_SESSION))
            session_start();
        // Valida la sesión (idEntidad) del usuario.
        usuario::inSession();
        // Instance
        $email= new eMail();
        switch($opt){
        case "read":
            echo json_encode($email->read());
            break;
        case "create":
            echo json_encode($email->create());
            break;
        case "update":
            echo json_encode($email->update());
            break;
        case "test":
            $email->extraMails= $_POST["mailAddress"];
            echo $email->test();
            break;
        }
    }

    class eMail{
        public $id;
        public $idEntidad;
        public $email_name;
        public $email_user;
        public $email_password;
        public $email_Host;
        public $email_port;
        public $activa=1;
        public $email_subject;
        public $email_SMTPSecure;
        public $email_SMTPAuth=null;
        public $email_body;
        public $email_logo;
        public $html;
        public $email_footer;
        public $estadoLogo;

        function __construct(){
            // identificador único
            if(isset($_POST["id"])){
                $this->id= $_POST["id"];
            }
            if(isset($_POST["idEntidad"])){
                $this->idEntidad= $_POST["idEntidad"];
            }
            else $this->idEntidad= $_SESSION['userSession']->idEntidad;
            //
            if(isset($_POST["obj"])){
                $obj= json_decode($_POST["obj"],true);    
                unset($_POST['obj']);        
                $this->id= $obj["id"] ?? UUID::v4();             
                $this->email_name= $obj["email_name"];
                $this->email_user= $obj["email_user"];   
                $this->email_password= $obj["email_password"];
                $this->email_Host= $obj["email_Host"] == 'Gmail' ? 'smtp.gmail.com' : 'imap-mail.outlook.com';
                $this->email_port= $obj["email_port"];
                $this->activa= $obj["activa"] ?? 1;
                $this->email_subject= $obj["email_subject"];
                $this->email_SMTPSecure= $obj["email_SMTPSecure"] ?? 'ssl';
                $this->email_SMTPAuth= $obj["email_SMTPAuth"] ?? 'true';
                $this->email_body= $obj["email_body"];
                $this->email_logo= $obj["email_logo"];
                $this->html= $obj["html"];
                $this->email_footer= $obj["email_footer"];
            }
        }

        function read(){
            try {
                // if(!isset($_SESSION['userSession']->idemail)){
                //     $this->id = null;
                //     return $this;
                // }
                //
                $sql='SELECT id,
                    email_name,
                    email_user,
                    email_password,
                    email_Host,
                    email_port,
                    activa,
                    email_subject,
                    email_SMTPSecure,
                    email_SMTPAuth,
                    email_body,
                    email_logo,
                    html,
                    email_footer
                FROM smtpXEntidad
                where idEntidad=:idEntidad';
                $param= array(':idEntidad'=>$this->idEntidad);
                $data= DATA::Ejecutar($sql,$param);
                if($data){
                    $this->id= $data[0]['id'];
                    $this->email_name= $data[0]['email_name'];
                    $this->email_user= $data[0]['email_user'];
                    $this->email_password= encdes::decifrar( $data[0]['email_password']);
                    $this->email_Host= $data[0]['email_Host'];
                    $this->email_port= $data[0]['email_port'];
                    $this->activa= $data[0]['activa'];
                    $this->email_subject= $data[0]['email_subject'];
                    $this->email_SMTPSecure= $data[0]['email_SMTPSecure'];
                    $this->email_Host= $data[0]['email_Host'];
                    $this->email_SMTPAuth= $data[0]['email_SMTPAuth'];
                    $this->email_body= $data[0]['email_body'];
                    $this->email_logo= $data[0]['email_logo'];      
                    $this->html= $data[0]['html'];
                    $this->email_footer= $data[0]['email_footer'];
                    // estado del certificado.
                    if(file_exists(Globals::emailLogoDir.$this->idEntidad.'/'.$this->email_logo))
                        $this->estadoLogo=1;
                    else $this->estadoLogo=0;
                    return $this;
                }
                return null;
            }
            catch(Exception $e) { 
                error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
                header('HTTP/1.0 400 Bad error');
                die(json_encode(array(
                    'code' => $e->getCode() ,
                    'msg' => 'Error al cargar la información de correo.'))
                );
            }
        }
    
        function create(){
            try {
                $sql='INSERT INTO  smtpXEntidad (id, idEntidad, email_name, email_user, email_password, email_Host, email_port, activa, email_subject, email_SMTPSecure, email_SMTPAuth, email_body, /*email_logo,*/ html, email_footer)
                    values (
                        :id,
                        :idEntidad,
                        :email_name,
                        :email_user,
                        :email_password,
                        :email_Host,
                        :email_port,
                        :activa,
                        :email_subject,
                        :email_SMTPSecure,
                        :email_SMTPAuth,
                        :email_body,
                        /*:email_logo,*/
                        :html,
                        :email_footer
                    )';
                $param= array(
                    ':id'=>$this->id,
                    ':idEntidad'=>$this->idEntidad,
                    ':email_name'=>$this->email_name, 
                    ':email_user'=>$this->email_user,
                    ':email_password'=>encdes::cifrar($this->email_password),
                    ':email_Host'=>$this->email_Host,
                    ':email_port'=>$this->email_port,
                    ':activa'=>1,
                    ':email_subject'=>$this->email_subject,
                    ':email_SMTPSecure'=>$this->email_SMTPSecure,
                    ':email_SMTPAuth'=>$this->email_SMTPAuth,
                    ':email_body'=>$this->email_body,
                    //':email_logo'=>Globals::$emailLogoDir.$this->idEntidad.$this->email_logo,
                    ':html'=>$this->html,
                    ':email_footer'=>$this->email_footer

                );
                $data = DATA::Ejecutar($sql,$param,false);
                if($data)
                {
                    return $this->id;               
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
    
        function update(){
            try {
                    
                $sql='UPDATE smtpXEntidad SET
                    email_name =:email_name,
                    email_user =:email_user,
                    email_password =:email_password,
                    email_port =:email_port,
                    activa =:activa,
                    email_subject =:email_subject,
                    email_SMTPSecure =:email_SMTPSecure,
                    email_Host =:email_Host,
                    email_SMTPAuth =:email_SMTPAuth,
                    email_body =:email_body,
                    /*email_logo =:email_logo,*/
                    html =:html,
                    email_footer =:email_footer
                where idEntidad=:idEntidad';
                
                $param= array(':idEntidad'=>$this->idEntidad,
                    ':email_name'=>$this->email_name, 
                    ':email_user'=>$this->email_user,
                    ':email_password'=>encdes::cifrar($this->email_password),
                    ':email_port'=>$this->email_port,
                    ':activa'=>$this->activa,
                    ':email_subject'=>$this->email_subject,
                    ':email_SMTPSecure'=>$this->email_SMTPSecure,
                    ':email_Host'=>$this->email_Host,
                    ':email_SMTPAuth'=>'true',
                    ':email_body'=>$this->email_body,
                    /*':email_logo'=>$this->email_logo,*/
                    ':html'=>$this->html,
                    ':email_footer'=>$this->email_footer
                );
                $data = DATA::Ejecutar($sql,$param,false);
                if($data)
                {
                    return $this->id;               
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

        function test(){
            if ($this->extraMails){
                $this->extraMails = preg_replace('/\s+/', '', $this->extraMails);
                $this->extraMails = str_replace('"', "",  $this->extraMails);
                //                
                if ( $this->extraMails[ strlen($this->extraMails)-1 ]  == ";"){
                    $this->extraMails = substr( $this->extraMails, 0 , strlen($this->extraMails)-1);
                }
                $this->extraMails = explode(";",$this->extraMails);
                // envio de correo de prueba.
                $this->read();
                invoice::$email_array_address_to = $this->extraMails;
                invoice::test($this);
            }
        }
    }

    //*********************************************/
    //************ sube imagen logo ***************/
    //*********************************************/
    if (!empty($_FILES)) {
        require_once("conexion.php");
        require_once("usuario.php");
        require_once("UUID.php");
        require_once("globals.php");
        usuario::inSession();
        $uploaddir= Globals::emailLogoDir . $_SESSION['userSession']->idEntidad . '/';
        $uploadfile = $uploaddir . $_FILES['file']['name'];
        if (!isset($_SESSION))
            session_start();
        if (!file_exists($uploaddir))
            mkdir($uploaddir, 0755, true);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            $sql="UPDATE smtpXEntidad 
                SET email_logo=:email_logo
                WHERE idEntidad=:idEntidad";
            $param= array(
                ':idEntidad'=>$_SESSION['userSession']->idEntidad,
                ':email_logo'=> $uploadfile
            );
            $data = DATA::Ejecutar($sql,$param,false);
            if($data){                
                echo "UPLOADED";
                return true;
            }
        }
    }
?>