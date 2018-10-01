<?php
//
// datos del emisor de la factura
//
require_once('Conexion.php');

class Receptor{
    public $id= null;
    public $nombre= null;
    public $idTipoIdentificacion= null;
    public $identificacion= null;
    public $identificacionExtranjero= null;
    public $nombreComercial= null;
    public $idProvincia= null;
    public $idCanton= null;
    public $idDistrito= null;
    public $idBarrio= null;
    public $otrasSenas= null;
    public $idCodigoPaisTel= null;
    public $numTelefono= null;
    public $idCodigoPaisFax= null;
    public $numTelefonoFax= null;
    public $correoElectronico= null;

    public static function read($id){
        $sql='SELECT r.id, nombre, idtipoidentificacion, identificacion, identificacionExtranjero, nombrecomercial, idProvincia, idCanton, idDistrito, idBarrio, otrasSenas, idCodigoPaisTel, numtelefono, idcodigopaisfax, numtelefonofax, correoelectronico
            FROM receptor r inner join factura f on f.idreceptor=r.id
            WHERE r.id= :identificacion';
        $param= array(':identificacion'=> $id);
        $data= DATA::Ejecutar($sql, $param);
        if(count($data)){
            self::$nombre= $data[0]['nombre'];
            self::$idTipoIdentificacion= $data[0]['idTipoIdentificacion'];
            self::$identificacion= $data[0]['identificacion'];
            self::$identificacionExtranjero= $data[0]['identificacionExtranjero'];
            self::$nombreComercial= $data[0]['nombreComercial'];
            self::$idProvincia= $data[0]['idProvincia'];
            self::$idCanton= $data[0]['idCanton'];
            self::$idDistrito= $data[0]['idDistrito'];
            self::$idBarrio= $data[0]['idBarrio'];
            self::$otrasSenas= $data[0]['otrasSenas'];
            self::$idCodigoPaisTel= $data[0]['idCodigoPaisTel'];
            self::$numTelefono= $data[0]['numTelefono'];
            self::$idCodigoPaisFax= $data[0]['idCodigoPaisFax'];
            self::$numTelefonoFax= $data[0]['numTelefonoFax'];
            self::$correoElectronico= $data[0]['correoElectronico']; 
            return  self;
        }
        else return null;
    }

    public static function default(){
        $sql='SELECT r.id, nombre, idTipoIdentificacion, identificacion, identificacionExtranjero, nombreComercial, idProvincia, idCanton, idDistrito, idBarrio, otrasSenas, idCodigoPaisTel, numTelefono, idCodigoPaisFax, numTelefonoFax, correoElectronico
            FROM receptor r
            WHERE r.nombre="default"';
        $data= DATA::Ejecutar($sql);
        $receptor = new Receptor();
        if(count($data)){
            $receptor->id= $data[0]['id'];
            $receptor->nombre= $data[0]['nombre'];
            $receptor->idTipoIdentificacion= $data[0]['idTipoIdentificacion'];
            $receptor->identificacion= $data[0]['identificacion'];
            $receptor->identificacionExtranjero= $data[0]['identificacionExtranjero'];
            $receptor->nombreComercial= $data[0]['nombreComercial'];
            $receptor->idProvincia= $data[0]['idProvincia'];
            $receptor->idCanton= $data[0]['idCanton'];
            $receptor->idDistrito= $data[0]['idDistrito'];
            $receptor->idBarrio= $data[0]['idBarrio'];
            $receptor->otrasSenas= $data[0]['otrasSenas'];
            $receptor->idCodigoPaisTel= $data[0]['idCodigoPaisTel'];
            $receptor->numTelefono= $data[0]['numTelefono'];
            $receptor->idCodigoPaisFax= $data[0]['idCodigoPaisFax'];
            $receptor->numTelefonoFax= $data[0]['numTelefonoFax'];
            $receptor->correoElectronico= $data[0]['correoElectronico'];
            //return new self();
            return $receptor;
        }
        else return null;
    }
}

?>