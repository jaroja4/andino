<?php 
require_once("Conexion.php");
require_once("Usuario.php");
require_once("encdes.php");
require_once("ClienteFE.php");
if (!isset($_SESSION))
    session_start();
error_log("*** INICIO: subir certificado ***");
$uploaddir= '../../CU/'.$_SESSION['userSession']->idBodega.'/';
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
        $sql="UPDATE clienteFE 
                SET cpath=:cpath, nkey=:nkey
                WHERE idBodega=:idBodega";
        $param= array(':idBodega'=>$_SESSION['userSession']->idBodega, 
            ':cpath'=>explode('::', $cfile)[0], 
            ':nkey'=>explode('::', $cfile)[1]);
        $data = DATA::Ejecutar($sql,$param,false);
        if($data){
            error_log("mv and data ok");
            // sesion del usuario
            $cliente= new ClienteFE();
            $cliente->certificado= realpath($uploaddir) .DIRECTORY_SEPARATOR. $_FILES['file']['name'];            
            // crea copia temporal sin cifrar para mover al API.
            copy($uploadfile, $cliente->certificado);
            chmod($cliente->certificado, 0777); 
            if($cliente->APIUploadCert()){
                //unlink($cliente->certificado);
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