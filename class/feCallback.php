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
            facturaElectronica::APIConsultaComprobante($factura);
        }
    } 
    catch(Exception $e) {
        error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
    }
    error_log("[INFO] Finaliza Consulta de Comprobantes");


?>