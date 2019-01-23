<?php
require_once("usuario.php");
require_once("session.php");
require_once("conexion.php");
if (!isset($_SESSION))session_start();
$sql='SELECT email_logo FROM smtpXEntidad where idEntidad=:idEntidad';
$param= array(':idEntidad'=>$_SESSION['userSession']->idEntidad);
$data= DATA::Ejecutar($sql,$param);
$file = $data[0]['email_logo'];
if (!file_exists($file))exit;
header("Content-disposition: attachment; filename=".$_GET['email_logo']);
header("Content-type: plain/text");
readfile($file);
?>