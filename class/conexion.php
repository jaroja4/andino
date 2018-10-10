<?php 
define('ERROR_CONFI_FILE_NOT_FOUND', '-001');
define('ERROR_CONN_ERR', '-002');

class DATA {
    
	public static $conn;    
    private static $config="";
    
	private static function ConfiguracionIni(){
        require_once('globals.php');
        if (file_exists(Globals::configFile)) {
            self::$config = parse_ini_file(Globals::configFile, true); 
        }         
        else throw new Exception('[ERROR] Acceso denegado al Archivo de configuración.',ERROR_CONFI_FILE_NOT_FOUND);
    }  

    private static function Conectar(){
        try {          
            self::ConfiguracionIni();
            if(!isset(self::$conn)) {
                self::$conn = new PDO('mysql:host='. self::$config[Globals::app]['host'] .';port='. self::$config[Globals::app]['port'] .';dbname=' . self::$config[Globals::app]['dbname'].';charset=utf8', self::$config[Globals::app]['username'],   self::$config[Globals::app]['password']); 
                return self::$conn;
            }
        } catch (PDOException $e) {
            throw new Exception('[ERROR] '.$e->getMessage(), $e->getCode());
        }
        catch(Exception $e){
            throw new Exception('[ERROR] '.$e->getMessage(),$e->getCode());
        }
    }
    
    // Ejecuta consulta SQL, $fetch = false envía los datos en 'crudo', $fetch=TRUE envía los datos en arreglo (fetchAll).
    public static function Ejecutar($sql, $param=NULL, $fetch=true) {
        try{
            //conecta a BD
            self::Conectar();
            $st=self::$conn->prepare($sql);
            self::$conn->beginTransaction(); 
            if($st->execute($param))
            {
                self::$conn->commit(); 
                if($fetch)
                    return  $st->fetchAll();
                else return $st;    
            } else {
                throw new Exception('[ERROR] Error al ejecutar el comando en base de datos.',00);
            }            
        } catch (Exception $e) {
            if(isset(self::$conn))
                self::$conn->rollback(); 
            if(isset($st))
                throw new Exception($st->errorInfo()[2],$st->errorInfo()[1]);
            else throw new Exception($e->getMessage(),$e->getCode());
        }
    }
}
?>