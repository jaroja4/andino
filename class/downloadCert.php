<?php
require_once("Usuario.php");
require_once("Conexion.php");
if (!isset($_SESSION))session_start();
$sql='SELECT certificado, cpath FROM clienteFE where idBodega=:idBodega';
$param= array(':idBodega'=>$_SESSION['userSession']->idBodega);
$data= DATA::Ejecutar($sql,$param);
$cpath = $data[0]['cpath'];
//$cfile= encdes::cifrar($_GET['certificado']);
$file= '../../CU/'.$_SESSION['userSession']->idBodega.'/'.$cpath;
if (!file_exists($file))exit;
header("Content-disposition: attachment; filename=".$_GET['certificado']);
header("Content-type: plain/text");
readfile($file);
?>