<?php    
    include_once("conexion.php");
    include_once("facturacionElectronica.php");
    include_once("entidad.php");
    include_once("receptor.php");
    include_once("factura.php");
    include_once("encdes.php");
    require_once("productosXFactura.php");
    require_once("mensajeReceptor.php");
    try{
        // timedout
        // Documentos 1-4-8.
        $sql='SELECT id
            from factura
            where idEstadoComprobante = 6
            order by idEntidad';
        $data= DATA::Ejecutar($sql);
        foreach ($data as $key => $transaccion){
            error_log("[INFO] Iniciando Consulta FE - TimedOut");
            $factura = new Factura();
            $factura->id = $transaccion['id'];
            $factura = $factura->Read();
            FacturacionElectronica::APIConsultaComprobante($factura);
            error_log("[INFO] Finaliza Consulta de Comprobantes - TimedOut");
        }
        // Consulta Documentos 1-4-8.
        $sql='SELECT id
            from factura
            where idEstadoComprobante = 2
            order by idEntidad';
        $data= DATA::Ejecutar($sql);
        foreach ($data as $key => $transaccion){
            error_log("[INFO] Iniciando Consulta de Comprobantes");
            $factura = new Factura();
            $factura->id = $transaccion['id'];
            $factura = $factura->read();
            FacturacionElectronica::APIConsultaComprobante($factura);
            error_log("[INFO] Finaliza Consulta de Comprobantes");
        }
        // Notas de crédito. Documento 3
        $sql='SELECT id
            from factura
            where idEstadoNC = 2
            order by idEntidad';
        $data= DATA::Ejecutar($sql);
        foreach ($data as $key => $transaccion){
            error_log("[INFO] Iniciando Consulta de Notas de Credito");
            $factura = new Factura();
            $factura->id = $transaccion['id'];
            $factura = $factura->read();
            // clave  & idDocumento de NC
            $factura->clave = $factura->claveNC;
            $factura->idDocumento = $factura->idDocumentoNC;
            FacturacionElectronica::APIConsultaComprobante($factura);
            error_log("[INFO] Finaliza Consulta de Notas de Credito");
        }
        // Mensaje Receptor Documentos 5-6-7.
        $sql='SELECT id
            from mensajeReceptor
            where idEstadoComprobante = 1 -- 2
            order by idReceptor';
        $data= DATA::Ejecutar($sql);
        foreach ($data as $key => $transaccion){
            error_log("[INFO] Iniciando Consulta MR");
            $factura = new mensajeReceptor();
            $factura->id = $transaccion['id'];
            $factura = $factura->Read();
            $entidad = new entidad();
            $entidad->id = $factura->idReceptor;
            $factura->datosReceptor = $entidad->read();
            $factura->clave = $factura->clave.'-'.$factura->consecutivoFE;
            FacturacionElectronica::APIConsultaComprobante($factura);
            error_log("[INFO] Finaliza Consulta MR");
        }
    } 
    catch(Exception $e) {
        error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
    }    
?>