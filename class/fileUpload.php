<?php 
require_once("session.php");
require_once("conexion.php");
require_once("usuario.php");
require_once("encdes.php");
require_once("entidad.php");
require_once("globals.php");
if (!isset($_SESSION))
    session_start();
error_log("[INFO] Iniciando subida de certificado.");
$uploaddir= Globals::certDir.$_SESSION['userSession']->idEntidad.DIRECTORY_SEPARATOR;
if (!file_exists($uploaddir))
    mkdir($uploaddir, 0755, true);
$cfile= encdes::cifrar($_FILES['file']['name']);
// busca si el string cifrado tiene un caracter: / ó \
$continuar = false;
while ($continuar==false) {
    if(strpos($cfile, '/') || strpos($cfile, '\\') ){
        $cfile= encdes::cifrar($_FILES['file']['name']);
        $continuar= false;
    }
    else $continuar= true;
}
//
$uploadfile = $uploaddir . explode('::', $cfile)[0];
if (!empty($_FILES)) {
    // elimina archivos previos, solo debe existir un certificado por agencia.
    $files = glob($uploaddir.'*'); // get all file names
    error_log("[INFO] Eliminando archivos existentes ");
    foreach($files as $file){
        if(is_file($file))
            unlink($file);
    }
    // mueve nuevo certificado.
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {        
        $sql="UPDATE entidad 
                SET cpath=:cpath, nkey=:nkey
                WHERE id=:id";
        $param= array(':id'=>$_SESSION['userSession']->idEntidad,
            ':cpath'=>explode('::', $cfile)[0], 
            ':nkey'=>explode('::', $cfile)[1]);
        $data = DATA::Ejecutar($sql,$param,false);
        if($data){
            error_log("[INFO] Local move and data ok");
            // sesion del usuario
            $entidad= new Entidad();
            $entidad->certificado= realpath($uploaddir) .DIRECTORY_SEPARATOR. $_FILES['file']['name'];            
            // crea copia temporal sin cifrar para mover al API.
            copy($uploadfile, $entidad->certificado);
            chmod($entidad->certificado, 0777); 
            if($entidad->APIUploadCert()){
                unlink($entidad->certificado);
                error_log("[INFO] Certificado OK");
                echo "UPLOADED";
                return true;
            }
            else {
                error_log('[ERROR] No se almacena el certificado en el api (-654).');
                echo "APIFAILED";
            }
        }
        else {
            error_log('[ERROR] No se almacena la data del path de certificado (-655).');
            echo "upload err!";
            return false;
        }
    } else {
        error_log('[ERROR] Local move failed (-656).');
        echo "upload attack!";
        return false;
    }
}
?>