<?php
class historico{
    public static function create($idFactura, $idEntidad, $idDocumentoReferencia, $idEstadoComprobante, $respuesta= null, $xml= null){
        try {
            $sql="INSERT INTO historicoComprobante (id, idFactura, idEntidad, idDocumentoReferencia, idEstadoComprobante, respuesta, xml)                                       
                VALUES  (uuid(), :idFactura, :idEntidad, :idDocumentoReferencia, :idEstadoComprobante, :respuesta, :xml)";        
            $param= array(':idFactura'=>$idFactura, ':idEntidad'=>$idEntidad, ':idDocumentoReferencia'=>$idDocumentoReferencia, ':idEstadoComprobante'=>$idEstadoComprobante, ':respuesta'=>$respuesta, ':xml'=>$xml);
            $data = DATA::Ejecutar($sql,$param, false);
            if($data)
                return true;
            else throw new Exception('Error al guardar el histórico.', 03);            
        }     
        catch(Exception $e) {
            error_log("[ERROR] No se puede crear el histórico de comprobantes  (".$e->getCode()."): ". $e->getMessage());
            // debe notificar que no se esta almacenando el historico de comprobantes.
        }
    }
}
?>