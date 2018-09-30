<?php
require_once("usuario.php");
require_once("conexion.php");
if (!isset($_SESSION))session_start();
$sql='SELECT certificado, cpath FROM contribuyente where idEmpresa=:idEmpresa';
$param= array(':idEmpresa'=>$_SESSION['userSession']->idEmpresa);
$data= DATA::Ejecutar($sql,$param);
$cpath = $data[0]['cpath'];
//$cfile= encdes::cifrar($_GET['certificado']);
$file= globals::certDir.$_SESSION['userSession']->idEmpresa.'/'.$cpath;
if (!file_exists($file))exit;
header("Content-disposition: attachment; filename=".$_GET['certificado']);
header("Content-type: plain/text");
readfile($file);
?>