<?php
    error_log("[INFO] Iniciando Consulta");    
    include_once("conexion.php");
    include_once("facturaElectronica.php");
    // Session
    // if (!isset($_SESSION))
    //     session_start();
    $accessToken='';
    try{
        // busca comprobantes enviados
        error_log("[INFO] Config file: " + Globals::configFile);
        $sql='SELECT clave
            FROM factura
            WHERE idEstadoComprobante=2';
        //$param= array(':id'=>$id);
        $data= DATA::Ejecutar($sql);
        if($data){
            // token del api.
            if(!facturaElectronica::getApiUrl()){
                error_log("[ERROR] api token (-501): No es posible generar token de api");
                exit;
            }
            // consulta de comprobantes.
            foreach ($data as $key => $value){
                $_SESSION['API']->clave = $value['clave'];
                facturaElectronica::APIConsultaComprobante();
                //checkComprobante($value['clave']);
            }
        }
    } 
    catch(Exception $e) {
        error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
    }


?>