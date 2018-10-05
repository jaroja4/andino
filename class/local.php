<?php
require_once("conexion.php");

class Local{
    public $id=null;
    public $idEntidad=null;
    public $nombre='';
    public $descripcion='';    
    public $ubicacion='';
    public $contacto='';
    public $telefono='';
    public $numeroLocal= '001';    

    public static function Read($idEntidad){
        try {
            $sql='SELECT l.id, l.nombre, l.descripcion, l.ubicacion, l.contacto, l.telefono, l.numeroLocal
                FROM local  l
                where l.id=:id';
            $param= array(':id'=>$idEntidad);
            $data= DATA::Ejecutar($sql,$param);     
            return $data;
        }     
        catch(Exception $e) { 
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            return false;
        }
    }

    public static function create($obj){
        try {
            $created = true;
            foreach ($obj as $item) {
                $sql="INSERT INTO local   (id, idEntidad, nombre, ubicacion, descripcion, contacto, telefono, numeroLocal)
                VALUES (uuid(), :idEntidad, :nombre, :ubicacion, :descripcion, :contacto, :telefono, :numeroLocal);";
                //
                $param= array(':idEntidad'=>$item->idEntidad, ':nombre'=>$item->nombre, ':ubicacion'=>$item->ubicacion, ':descripcion'=>$item->descripcion, 
                ':contacto'=>$item->contacto, ':telefono'=>$item->telefono, ':numeroLocal'=>$item->numeroLocal);
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

    static function Update($obj){
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
    static function Delete($idEntidad){
        try {
            $sql='DELETE FROM local  
                WHERE idEntidad= :idEntidad';
            $param= array(':idEntidad'=> $idEntidad);
            $data= DATA::Ejecutar($sql, $param, false);
            if($data)
                return true;
            else false;
        }
        catch(Exception $e) { 
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            return false;
        }
    }

    

}



?>