<?php
    error_log("[INFO] Iniciando Consulta");
    include_once("conexion.php");
    include_once("facturacionElectronica.php");
    include_once("entidad.php");
    include_once("receptor.php");
    include_once("factura.php");
    include_once("encdes.php");
    require_once("productosXFactura.php");
    try{
        // Comprobantes 1-4-8.
        $sql='SELECT id
            from factura
            where idEstadoComprobante = 2
            order by idEntidad';
        $data= DATA::Ejecutar($sql);
        foreach ($data as $key => $transaccion){
            $factura = new Factura();
            $factura->id = $transaccion['id'];
            $factura = $factura->read();
            FacturacionElectronica::APIConsultaComprobante($factura);
        }
        // Notas de crédito.
        $sql='SELECT id
            from factura
            where idEstadoNC = 2
            order by idEntidad';
        $data= DATA::Ejecutar($sql);
        foreach ($data as $key => $transaccion){
            $factura = new Factura();
            $factura->id = $transaccion['id'];
            $factura = $factura->read();
            // clave = claveNC
            $factura->clave = $factura->claveNC;
            FacturacionElectronica::APIConsultaComprobante($factura);
        }
    } 
    catch(Exception $e) {
        error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
    }
    error_log("[INFO] Finaliza Consulta de Comprobantes");
?>