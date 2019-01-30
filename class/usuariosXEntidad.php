<?php 
require_once("conexion.php");

class UsuariosXEntidad{
    public $idEntidad;
    public $idUsuario;
    public $nombre;
    public $clasificacion;
    //
    public static function read($idUsuario){
        try{
            $sql='SELECT ue.idEntidad, e.nombre, e.idDocumento, e.clasificacion
                FROM usuariosXEntidad ue INNER JOIN entidad e on e.id=ue.idEntidad
                where ue.idUsuario=:idUsuario';
            $param= array(':idUsuario'=>$idUsuario);
            $data= DATA::Ejecutar($sql,$param);
            $lista = [];
            foreach ($data as $key => $value){
                $entidad = new UsuariosXEntidad();
                $entidad->idEntidad = $value['idEntidad'];
                $entidad->nombre = $value['nombre'];
                $entidad->idDocumento = $value['idDocumento'];
                $entidad->clasificacion = $value['clasificacion'];
                array_push ($lista, $entidad);
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
            foreach ($obj as $item) {
                $sql="INSERT INTO usuariosXEntidad   (idEntidad, idUsuario)
                VALUES (:idEntidad, :idUsuario)";
                //
                $param= array(':idEntidad'=>$item->idEntidad, ':idUsuario'=>$item->idUsuario);
                $data = DATA::Ejecutar($sql,$param,false);
                if(!$data)
                    $created= false;
            }
            return $created;
        }     
        catch(Exception $e) { 
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            return false;
        }
    }

    public static function update($obj){
        try {
            $updated = true;
            // elimina todos los objetos relacionados
            $updated= self::Delete($obj[0]->idUsuario);
            // crea los nuevos objetos
            $updated= self::Create($obj);
            return $updated;
        }     
        catch(Exception $e) { 
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            return false;
        }
    }

    public static function delete($_idusuario){
        try {                 
            $sql='DELETE FROM usuariosXEntidad  
                WHERE idUsuario= :idUsuario';
            $param= array(':idUsuario'=> $_idusuario);
            $data= DATA::Ejecutar($sql, $param, false);
            if($data)
                return true;
            else false;
        }
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            return false;
        }
    }
}
?>