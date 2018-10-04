<?php
require_once("conexion.php");
//
// if (!isset($_SESSION))
//     session_start();

// if(isset($_POST["action"])){
//     $opt= $_POST["action"];
//     unset($_POST['action']);
//     //
//     $local= new Local();
//     switch($opt){
//         case "ReadAll":
//             echo json_encode($local->ReadAll());
//             break;
//         case "Read":
//             echo json_encode($local->Read());
//             break;
//         case "List":
//             echo json_encode($local->List());
//             break;
//         case "readByUser":
//             echo json_encode($local->readByUser());
//             break;
//         case "Create":
//             $local->Create();
//             break;
//         case "Update":
//             $local->Update();
//             break;
//         case "Delete":
//             $local->Delete();
//             break;   
//     }    
// }

class Local{
    public static $id=null;
    public static $nombre='';
    public static $descripcion='';    
    public static $ubicacion='';
    public static $contacto='';
    public static $telefono='';
    public static $tipo= null;    

    // function __construct(){
    //     // identificador único
    //     if(isset($_POST["id"])){
    //         $this->id= $_POST["id"];
    //     }
    //     if(isset($_POST["obj"])){
    //         $obj= json_decode($_POST["obj"],true);
    //         require_once("UUID.php");
    //         $this->id= $obj["id"] ?? UUID::v4();
    //         $this->nombre= $obj["nombre"] ?? '';
    //         $this->ubicacion= $obj["ubicacion"] ?? '';
    //         $this->descripcion= $obj["descripcion"] ?? '';
    //         $this->contacto= $obj["contacto"] ?? '';            
    //         $this->telefono= $obj["telefono"] ?? '';            
    //         $this->tipo= $obj["tipo"] ?? null;
    //     }
    // }


    public static function Read($idEntidad){
        try {
            $sql='SELECT l.id, l.nombre, l.descripcion, l.ubicacion, l.contacto, l.telefono
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

    static function create($obj){
        try {
            $created = true;
            foreach ($obj as $item) {
                $sql="INSERT INTO local   (id, nombre, ubicacion, descripcion, contacto, telefono)
                VALUES (:id, :nombre, :ubicacion, :descripcion, :contacto, :telefono);";
                //
                $param= array(':id'=>$item->id, ':nombre'=>$item->nombre, ':ubicacion'=>$item->ubicacion, ':descripcion'=>$item->descripcion, 
                ':contacto'=>$item->contacto, ':telefono'=>$item->telefono);
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