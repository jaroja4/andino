<?php 
require_once("conexion.php");
require_once("usuario.php");
require_once("encdes.php");
require_once("contribuyente.php");
require_once("globals.php");
if (!isset($_SESSION))
    session_start();
error_log("*** INICIO: subir certificado ***");
$uploaddir= globals::certDir.$_SESSION['userSession']->idContribuyente.'/';
if (!file_exists($uploaddir)) 
    mkdir($uploaddir, 0777, true);
$cfile= encdes::cifrar($_FILES['file']['name']);
$uploadfile = $uploaddir . explode('::', $cfile)[0];
if (!empty($_FILES)) {
    // elimina archivos previos, solo debe existir un certificado por agencia.
    $files = glob($uploaddir.'*'); // get all file names
    error_log("Eliminando archivos existentes ");
    foreach($files as $file){
        if(is_file($file))
            unlink($file);
    }
    // mueve nuevo certificado.
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {        
        $sql="UPDATE contribuyente 
                SET cpath=:cpath, nkey=:nkey
                WHERE idEmpresa=:idEmpresa";
        $param= array(':idEmpresa'=>$_SESSION['userSession']->idContribuyente, 
            ':cpath'=>explode('::', $cfile)[0], 
            ':nkey'=>explode('::', $cfile)[1]);
        $data = DATA::Ejecutar($sql,$param,false);
        if($data){
            error_log("mv and data ok");
            // sesion del usuario
            $contribuyente= new contribuyente();
            $contribuyente->certificado= realpath($uploaddir) .DIRECTORY_SEPARATOR. $_FILES['file']['name'];            
            // crea copia temporal sin cifrar para mover al API.
            copy($uploadfile, $contribuyente->certificado);
            chmod($contribuyente->certificado, 0777); 
            if($contribuyente->APIUploadCert()){
                //unlink($contribuyente->certificado);
                error_log("Certificado OK");
                echo "UPLOADED";
                return true;
            }
            else {
                error_log('no se almacena el certificado en el api.');
                echo "APIFAILED";
            }
        }
        else {
            error_log('no se almacena la data del path de certificado.');
            echo "upload err!";
            return false;
        }
    } else {
        error_log('mv failed');
        echo "upload attack!";
        return false;
    }
}
?>