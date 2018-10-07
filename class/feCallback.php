<?php
    error_log("[INFO] Iniciando Consulta");
    include_once("conexion.php");
    include_once("facturaElectronica.php");
    include_once("entidad.php");
    include_once("factura.php");
    require_once("encdes.php");
    try{
        // Entidades con transacciones enviadas.
        $sql='SELECT e.id, e.username, e.password
            from entidad e inner join factura f on f.idEntidad = e.id
            where f.idEstadoComprobante = 2
            group by e.id';
        $data= DATA::Ejecutar($sql);
        foreach ($data as $key => $valEntidad){
            // Session.
            if (!isset($_SESSION))
                session_start();
            // entidad.
            $entidad = new Entidad();            
            $entidad->id = $valEntidad['id'];
            $entidad->username = encdes::decifrar($valEntidad['username']);
            $entidad->password = encdes::decifrar($valEntidad['password']);
            $_SESSION['API'] = $entidad;
            // busca comprobantes de la entidad    
            $sql='SELECT id, clave
                FROM factura
                WHERE idEstadoComprobante=2 and idEntidad=:idEntidad';
            $param= array(':idEntidad'=>$entidad->id);
            $data= DATA::Ejecutar($sql, $param);
            // api login
            if(!$entidad->APILogin()){
                error_log("[ERROR] api token (-501): No es posible generar token de api");
                exit;
            }
            // consulta de comprobantes.
            foreach ($data as $key => $value){       
                $_SESSION['API']->clave = $value['clave'];                
                error_log("[INFO] consulta factura: " . $_SESSION['API']->clave);
                error_log("[info] session username: " . $_SESSION['API']->username);
                error_log("[info] session pw: " . $_SESSION['API']->password);
                facturaElectronica::APIConsultaComprobante($value['id']);
            }
        }
    } 
    catch(Exception $e) {
        error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
    }
    error_log("[INFO] Finaliza Consulta de Comprobantes");


?>