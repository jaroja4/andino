<?php
    error_log("[INFO] Iniciando Consulta");    
    include_once("conexion.php");
    include_once("facturaElectronica.php");
    include_once("entidad.php");
    try{
        // busca comprobantes enviados    
        $sql='SELECT clave
            FROM factura
            WHERE idEstadoComprobante=2';
        //$param= array(':id'=>$id);
        $data= DATA::Ejecutar($sql);
        if(count($data)){
            // Session.
            if (!isset($_SESSION))
                session_start();            
            error_log("[INFO] login api");
            // token del api.
            $entidad = new Entidad();            
            $entidad->username = 'cpf-01-1187-0763@stag.comprobanteselectronicos.go.cr';
            $entidad->password = '9zgr)L#szb^Z=%*+;%c|';
            $_SESSION['API'] = $entidad;
            //
            if(!$entidad->APILogin()){
                error_log("[ERROR] api token (-501): No es posible generar token de api");
                exit;
            }
            // consulta de comprobantes.
            foreach ($data as $key => $value){                
                $_SESSION['API']->clave = $value['clave'];                
                error_log("[INFO] consulta factura: " . $_SESSION['API']->clave);
                error_log("[info] session username: " . $_SESSION['API']->username);
                error_log("[info] session pw: " . $_SESSION['API']->password);
                facturaElectronica::APIConsultaComprobante();
                //checkComprobante($value['clave']);
            }
        }
    } 
    catch(Exception $e) {
        error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
    }


?>