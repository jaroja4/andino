<?php 
require_once("conexion.php");

class usuariosXEntidad{
    public static $idEntidad;
    public static $idUsuario;
    public static $nombre;
    //
    public static function Read($idUsuario){
        try{
            $sql='SELECT ue.idEntidad, c.nombre, c.descripcion, c.ubicacion, c.local
                FROM usuariosXEntidad ue INNER JOIN contribuyente b on c.id=ue.idEntidad
                where ue.idUsuario=:idUsuario';
            $param= array(':idUsuario'=>$idUsuario);
            $data= DATA::Ejecutar($sql,$param);
            $lista = [];
            foreach ($data as $key => $value){
                $entidad = new usuariosXEntidad();
                $entidad->idEntidad = $value['idEntidad'];
                $entidad->nombre = $value['nombre'];
                array_push ($lista, $entidad);
            }
            return $lista;
        }
        catch(Exception $e) { error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            return false;
        }
    }

    public static function Create($obj){
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

    public static function Update($obj){
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

    public static function Delete($_idusuario){
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