<?php
    require_once("Conexion.php");

    if(isset($_POST["action"])){
        $opt= $_POST["action"];
        unset($_POST['action']);
        // Classes
        require_once("Usuario.php");
        // Session
        if (!isset($_SESSION))
            session_start();
            
        // Instance
        $productoXFactura= new ProductoXFactura();
        switch($opt){
            case "ReadByIdFactura":
                echo json_encode($productoXFactura->ReadByIdFactura($_POST['id']));
                break;
        }
        
    }
    
class productosXFactura{

    public static function read($idFactura){
        try{
            $sql="SELECT id, idFactura, idPrecio, numeroLinea, idTipoCodigo, codigo, cantidad, idUnidadMedida, unidadMedidaComercial, detalle, precioUnitario, montoTotal, montoDescuento, naturalezaDescuento, subTotal, codigoImpuesto, tarifaImpuesto, montoImpuesto, idExoneracionImpuesto, montoTotalLinea
                from productosXFactura
                where idFactura = :id";
            $param= array(':id'=>$idFactura);
            $data = DATA::Ejecutar($sql,$param);            
            $lista = [];
            foreach ($data as $key => $value){
                $producto = new ProductoXFactura();
                $producto->id = $value['id'];
                $producto->idFactura = $value['idFactura'];                
                $producto->idPrecio = $value['idPrecio'];
                $producto->numeroLinea = $value['numeroLinea'];
                $producto->idTipoCodigo = $value['idTipoCodigo'];
                $producto->codigo = $value['codigo'];
                $producto->cantidad = $value['cantidad'];
                $producto->idUnidadMedida = $value['idUnidadMedida'];
                $producto->unidadMedidaComercial = $value['unidadMedidaComercial'];
                $producto->detalle = $value['detalle'];
                $producto->precioUnitario = $value['precioUnitario'];
                $producto->montoTotal = $value['montoTotal'];
                $producto->montoDescuento = $value['montoDescuento'];
                $producto->naturalezaDescuento = $value['naturalezaDescuento'];
                $producto->subTotal = $value['subTotal'];
                $producto->codigoImpuesto = $value['codigoImpuesto'];
                $producto->tarifaImpuesto = $value['tarifaImpuesto'];
                $producto->montoImpuesto = $value['montoImpuesto'];
                $producto->idExoneracionImpuesto = $value['idExoneracionImpuesto'];
                $producto->montoTotalLinea = $value['montoTotalLinea'];
                //
                array_push ($lista, $producto);
            }
            return $lista;
        }
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            return false;
        }
    }

    public static function create($obj){
        try {
            $created = true;
            //$idUnidadMedida= 78;  // Unid.
            foreach ($obj as $item) {
                $sql="INSERT INTO productosXFactura (id, idFactura, idPrecio, numeroLinea, idTipoCodigo, codigo, cantidad, idUnidadMedida, detalle, precioUnitario, montoTotal, montoDescuento, naturalezaDescuento,
                    subTotal, codigoImpuesto, tarifaImpuesto, montoImpuesto, idExoneracionImpuesto, montoTotalLinea)
                VALUES (uuid(), :idFactura, :idPrecio, :numeroLinea, :idTipoCodigo, :codigo, :cantidad, :idUnidadMedida, :detalle, :precioUnitario, :montoTotal, :montoDescuento, :naturalezaDescuento,
                    :subTotal, :codigoImpuesto, :tarifaImpuesto, :montoImpuesto, :idExoneracionImpuesto, :montoTotalLinea)";              
                $param= array(
                    ':idFactura'=>$item->idFactura,
                    ':idPrecio'=>$item->idPrecio,
                    ':numeroLinea'=>$item->numeroLinea,
                    ':idTipoCodigo'=> $item->idTipoCodigo,
                    ':codigo'=> $item->codigo,                    
                    ':cantidad'=>$item->cantidad,
                    ':idUnidadMedida'=>$item->idUnidadMedida,
                    ':detalle'=>$item->detalle,
                    ':precioUnitario'=>$item->precioUnitario,
                    ':montoTotal'=>$item->montoTotal,
                    ':montoDescuento'=>$item->montoDescuento,
                    ':naturalezaDescuento'=>$item->naturalezaDescuento,                    
                    ':subTotal'=>$item->subTotal,                    
                    ':codigoImpuesto'=>$item->codigoImpuesto,
                    ':tarifaImpuesto'=>$item->tarifaImpuesto,
                    ':montoImpuesto'=>$item->montoImpuesto,
                    ':idExoneracionImpuesto'=>$item->idExoneracionImpuesto,
                    ':montoTotalLinea'=>$item->montoTotalLinea);
                $data = DATA::Ejecutar($sql, $param, false);
            }
            return $created;
        }     
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            return false;
        }
    }
}
?> 