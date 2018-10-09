<?php
    error_log("[INFO] Iniciando Consulta");
    include_once("conexion.php");
    include_once("facturaElectronica.php");
    include_once("entidad.php");
    include_once("receptor.php");
    include_once("factura.php");
    require_once("encdes.php");
    require_once("productosXFactura.php");
    try{
        // Entidades con transacciones enviadas.
        $sql='SELECT id
            from factura
            where idEstadoComprobante = 2
            order by idEntidad';
        $data= DATA::Ejecutar($sql);
        foreach ($data as $key => $transaccion){
            $factura = new Factura();
            $factura->id = $transaccion['id'];
            $factura = $factura->read();
            // api login
            // $entidad = new Entidad();
            // $_SESSION['APISERVER-username'] = $entidad->username = encdes::decifrar($factura->datosEntidad[0]['username']);
            // $_SESSION['APISERVER-password'] = $entidad->password = encdes::decifrar($factura->datosEntidad[0]['password']);
            // if(!$entidad->APILogin()){
            //     error_log("[ERROR] api token (-501): No es posible generar token de api");
            //     exit;
            // }
            // consulta de comprobantes.
            //$_SESSION['userSession'] = $entidad;
            facturaElectronica::APIConsultaComprobante($factura);
        }
    } 
    catch(Exception $e) {
        error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
    }
    error_log("[INFO] Finaliza Consulta de Comprobantes");


?>