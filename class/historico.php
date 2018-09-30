<?php
class historico{
    public static function create($idFactura, $idEstadoComprobante, $respuesta= null, $xml= null){
        try {
            $sql="INSERT INTO historicoComprobante (id, idFactura, idEstadoComprobante, respuesta, xml)                                       
                VALUES  (uuid(), :idFactura, :idEstadoComprobante, :respuesta, :xml)";        
            $param= array(':idFactura'=>$idFactura, ':idEstadoComprobante'=>$idEstadoComprobante, ':respuesta'=>$respuesta, ':xml'=>$xml);
            $data = DATA::Ejecutar($sql,$param, false);
            if($data)
                return true;
            else throw new Exception('Error al guardar el histórico.', 03);            
        }     
        catch(Exception $e) {
            error_log("error: ". $e->getMessage());
            // debe notificar que no se esta almacenando el historico de comprobantes.
        }
    }
}
?>