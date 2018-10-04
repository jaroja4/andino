<?php 
define('ERROR_CONFI_FILE_NOT_FOUND', '-001');
define('ERROR_CONN_ERR', '-002');

class DATA {
    
	public static $conn;    
    private static $config="";
    
	private static function ConfiguracionIni(){
        require_once('Globals.php');
        if (file_exists(globals::configFile)) {
            self::$config = parse_ini_file(globals::configFile, true); 
        }         
        else throw new Exception('Acceso denegado al Archivo de configuración.',ERROR_CONFI_FILE_NOT_FOUND);
    }  

    private static function Conectar(){
        try {          
            self::ConfiguracionIni();
            if(!isset(self::$conn)) {
                self::$conn = new PDO('mysql:host='. self::$config[globals::app]['host'] .';port='. self::$config[globals::app]['port'] .';dbname=' . self::$config[globals::app]['dbname'].';charset=utf8', self::$config[globals::app]['username'],   self::$config[globals::app]['password']); 
                return self::$conn;
            }
        } catch (PDOException $e) {
            throw new Exception($e->getMessage(),$e->getCode());
        }
        catch(Exception $e){
            throw new Exception($e->getMessage(),$e->getCode());
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
                throw new Exception('Error al ejecutar.',00);
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