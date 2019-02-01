<?php
// Session
if (!isset($_SESSION))
    session_start();
if(!isset($_SESSION["userSession"]->id)){
    header('HTTP/1.0 401 Unauthorized ');
    die(json_encode(array(
        'code' => 401 ,
        'msg' => 'Sesion Expirada, por favor ingrese sus credenciales.'))
    );
}
?>