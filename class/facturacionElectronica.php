<?php
include_once('historico.php');
require_once("invoice.php");
//
define('ERROR_USERS_NO_VALID', '-500');
define('ERROR_TOKEN_NO_VALID', '-501');
define('ERROR_CLAVE_NO_VALID', '-502');
define('ERROR_FEXML_NO_VALID', '-503');
define('ERROR_ENVIO_NO_VALID', '-504');
define('ERROR_ENVIOERR_NO_VALID', '-505');
define('ERROR_CONSULTA_NO_VALID', '-506');
define('ERROR_IMPUESTO_NO_VALID', '-507');
define('ERROR_MEDIOPAGO_NO_VALID', '-508');
define('ERROR_CERTIFICADOURL_NO_VALID', '-509');
define('ERROR_UBICACION_NO_VALID', '-510');
define('ERROR_SITUACION_COMPROBANTE_NO_VALID', '-511');
define('ERROR_TIPO_IDENTIFICACION_NO_VALID', '-512');
define('ERROR_UNIDAD_MEDIDA_NO_VALID', '-513');
define('ERROR_CONDICIONVENTA_NO_VALID', '-514');
define('ERROR_MONEDA_NO_VALID', '-515');
define('ERROR_ESTADO_COMPROBANTE_NO_VALID', '-516');
define('ERROR_CIFRAR_NO_VALID', '-517');
define('ERROR_CODIGO_REFERENCIA_NO_VALID', '-518');
define('ERROR_INICIAL', '-519');
define('ERROR_LECTURA_CONFIG', '-520');
define('ERROR_NDXML_NO_VALID', '-521');
define('ERROR_NCXML_NO_VALID', '-522');
define('ERROR_REFERENCIA_NO_VALID', '-523');

class FacturacionElectronica{
    static $transaccion;
    static $fechaEmision;
    static $apiUrl;
    static $accessToken;
    static $expiresIn;
    static $refreshExpiresIn;
    static $refreshToken;
    static $clave;
    static $consecutivoFE;
    static $xml;
    static $xmlFirmado;
    static $apiMode;


    public static function iniciarNC($t){
        try{
            //date_default_timezone_set('America/Costa_Rica');
            self::$transaccion= $t;
            self::$fechaEmision= date_create();
            // fe o nc
            self::$transaccion->idDocumento = 3; // NC
            if(self::getApiUrl()){
                if(self::APICrearClave()){
                    if(self::APICrearNCXML()){
                        if(self::APICifrarXml()){
                            if(self::APIEnviar()){
                                //self::APIConsultaComprobante();
                                //include_once('feCallback.php');
                                return true;
                            }
                        }
                    }
                }
            }
        }
        catch(Exception $e) {
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_INICIAL: '. $e->getMessage());
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
        }
    }

    public static function iniciar($t){
        try{
            //date_default_timezone_set('America/Costa_Rica');
            self::$transaccion= $t;
            self::$fechaEmision= date_create();
            // fe o nc
            // if(self::$transaccion->idDocumentoNC!=null)
            //     self::$transaccion->idDocumento = self::$transaccion->idDocumentoNC;
            if(self::getApiUrl()){
                $resCreaXml = false;
                if(self::APICrearClave()){
                    switch(self::$transaccion->idDocumento){
                        case 1: 
                        case 4:
                        case 8: // contingencia crea xml de FE.
                            $resCreaXml = self::APICrearXML();
                        break;
                        case 2: //$resCreaXml = self::APICrearNDXML();
                        break;
                        case 3: $resCreaXml = self::APICrearNCXML();
                        break;
                        break;
                    }
                    //
                    if($resCreaXml){
                        if(self::APICifrarXml()){
                            if(self::APIEnviar()){
                                //self::APIConsultaComprobante();
                                //include_once('feCallback.php');
                                return true;
                            }
                        }
                    }
                }
            }
        }
        catch(Exception $e) {
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_INICIAL: '. $e->getMessage());
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
        }
    }

    public static function getApiUrl(){
        try{
            require_once('globals.php');
            if (file_exists(Globals::configFile)) {
                $set = parse_ini_file(Globals::configFile,true); 
                self::$apiUrl= $set[Globals::app]['apiurl'];
                return true;
            }         
            else {
                throw new Exception('Acceso denegado al Archivo de configuración.', ERROR_LECTURA_CONFIG); 
            }
        }
        catch(Exception $e) {
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_LECTURA_CONFIG: '. $e->getMessage());
            error_log("[ERROR]  Acceso denegado al Archivo de configuración. (".$e->getCode()."): ". $e->getMessage());
        }        
    }

    private static function getIdentificacionCod($id){
        try{
            $sql='SELECT codigo
            FROM tipoIdentificacion
            WHERE id=:id';
            $param= array(':id'=>$id);
            $data= DATA::Ejecutar($sql,$param);     
            if($data)
                return $data[0]['codigo'];
            else throw new Exception('Error al consultar el codigo de tipod de identificacion' , ERROR_TIPO_IDENTIFICACION_NO_VALID);
        }
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_TIPO_IDENTIFICACION_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
        }
    }

    private static function getSituacionComprobanteCod($id){
        try{
            $sql='SELECT codigo
            FROM situacionComprobante
            WHERE id=:id';
            $param= array(':id'=>$id);
            $data= DATA::Ejecutar($sql,$param);     
            if($data)
            {
                switch($data[0]['codigo']){
                    case '1':
                        return 'normal';
                    break;
                        case '2':
                        return 'contingencia';
                    break;
                        case '3':
                        return 'sinInternet';
                    break;
                    
                }
            }
            else throw new Exception('Error al consultar el codigo de situacion comprobante' , ERROR_SITUACION_COMPROBANTE_NO_VALID);
        }
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_SITUACION_COMPROBANTE_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
        }
    }

    private static function getDocumentoReferencia($id){
        try{
            switch($id){
                case '1':
                case '8': // El API  no tiene opción para enviar documento por contingencia.
                    return 'FE';
                    break;
                case '2':
                    return 'ND';
                    break;
                case '3':
                    return 'NC';
                    break;
                case '4':
                    return 'TE';
                    break;
                default: 
                    throw new Exception('Error al consultar el codigo de referencia' , ERROR_CODIGO_REFERENCIA_NO_VALID);
                    break;
            }            
        }
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_CODIGO_REFERENCIA_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
        }
    }

    private static function getDocumentoReferenciaCod($id){
        try{
            $sql='SELECT codigo
                FROM documentoReferencia
                WHERE id=:id';
            $param= array(':id'=>$id);
            $data= DATA::Ejecutar($sql,$param);     
            if($data)
                return $data[0]['codigo'];
            else throw new Exception('Error al consultar el codigo de tipod de identificacion' , ERROR_CODIGO_REFERENCIA_NO_VALID);
        }
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_CODIGO_REFERENCIA_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
        }
    }

    private static function getReferenciaCod($id){
        try{
            $sql='SELECT codigo
                FROM referencia
                WHERE id=:id';
            $param= array(':id'=>$id);
            $data= DATA::Ejecutar($sql,$param);     
            if($data)
                return $data[0]['codigo'];
            else throw new Exception('Error al consultar el codigo de tipod de identificacion' , ERROR_REFERENCIA_NO_VALID);
        }
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_REFERENCIA_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
        }
    }

    private static function getImpuestoCod($id){
        try{
            $sql='SELECT codigo
            FROM impuesto
            WHERE id=:id';
            $param= array(':id'=>$id);
            $data= DATA::Ejecutar($sql,$param);     
            if($data)
                return $data[0]['codigo'];
            else throw new Exception('Error al consultar el codigo del impuesto' , ERROR_IMPUESTO_NO_VALID);
        }
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_IMPUESTO_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
        }
    }

    private static function getUnidadMedidaCod($id){
        try{
            $sql='SELECT simbolo
                FROM unidadMedida
                WHERE id=:id';
            $param= array(':id'=>$id);
            $data= DATA::Ejecutar($sql,$param);     
            if($data)
                return $data[0]['simbolo'];
            else throw new Exception('Error al consultar el codigo de unidad medida' , ERROR_UNIDAD_MEDIDA_NO_VALID);
        }
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_UNIDAD_MEDIDA_NO_VALID '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
        }
    }

    private static function getUbicacionCod($idProvincia, $idCanton, $idDistrito, $idBarrio){
        try{
            $sql='SELECT p.codigo as provincia, c.codigo as canton, d.codigo as distrito, b.codigo as barrio
                FROM provincia p, canton c , distrito d, barrio b        
                where p.id=:provincia and c.id=:canton and d.id=:distrito and b.id=:barrio';
            $param= array(':provincia'=>$idProvincia, 
                ':canton'=>$idCanton,
                ':distrito'=>$idDistrito,
                ':barrio'=>$idBarrio,
            );
            $data= DATA::Ejecutar($sql,$param);
            $ubicacion= [];
            if($data){
                $item= new UbicacionCod();
                $item->provincia= $data[0]['provincia'];
                $item->canton= $data[0]['canton'];
                $item->distrito= $data[0]['distrito'];
                $item->barrio= $data[0]['barrio'];
                array_push($ubicacion, $item);
            }
            else throw new Exception('Error al consultar el codigo de la ubicacion' , ERROR_UBICACION_NO_VALID);
            return $ubicacion;            
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_UBICACION_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
        }
    }

    private static function getMedioPagoCod($id){
        try{
            $sql='SELECT codigo
            FROM medioPago
            WHERE id=:id';
            $param= array(':id'=>$id);
            $data= DATA::Ejecutar($sql,$param);     
            if($data)
                return $data[0]['codigo'];
            else throw new Exception('Error al consultar el codigo del medio de pago' , ERROR_MEDIOPAGO_NO_VALID);
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_MEDIOPAGO_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
        }
    }

    private static function getCodigoMonedaCod($id){
        try{
            $sql='SELECT codigo
            FROM moneda
            WHERE id=:id';
            $param= array(':id'=>$id);
            $data= DATA::Ejecutar($sql,$param);     
            if($data)
                return $data[0]['codigo'];
            else throw new Exception('Error al consultar el codigo de moneda' , ERROR_MONEDA_NO_VALID);
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_MONEDA_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
        }
    }

    private static function getEstadoComprobanteCod($id){
        try{
            $sql='SELECT codigo
            FROM estadoComprobante
            WHERE id=:id';
            $param= array(':id'=>$id);
            $data= DATA::Ejecutar($sql,$param);     
            if($data)
                return $data[0]['codigo'];
            else throw new Exception('Error al consultar el codigo de estado del comprobante' , ERROR_ESTADO_COMPROBANTE_NO_VALID);
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_ESTADO_COMPROBANTE_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
        }
    }

    private static function getCondicionVentaCod($id){
        try{
            $sql='SELECT codigo
            FROM impuesto
            WHERE id=:id';
            $param= array(':id'=>$id);
            $data= DATA::Ejecutar($sql,$param);     
            if($data)
                return $data[0]['codigo'];
            else throw new Exception('Error al consultar el codigo de Condicion venta' , ERROR_CONDICIONVENTA_NO_VALID);
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_CONDICIONVENTA_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
        }
    }

    public static function APIGetToken(){
        try{
            $username = self::$transaccion->datosEntidad->username;
            self::$apiMode = strpos($username, 'prod');
            if (self::$apiMode === false) 
                self::$apiMode = 'api-stag';
            else self::$apiMode = 'api-prod';
            $ch = curl_init();
            $post = [
                'w' => 'token',
                'r' => 'gettoken',
                'grant_type'=>'password', 
                'client_id'=>  self::$apiMode,
                'username' => $username,
                'password'=>  self::$transaccion->datosEntidad->password
            ];
            curl_setopt_array($ch, array(
                CURLOPT_URL => self::$apiUrl,
                CURLOPT_RETURNTRANSFER => true,   
                CURLOPT_VERBOSE => true,                      
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post
            ));
            $server_output = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($server_output, 0, $header_size);
            $body = substr($server_output, $header_size);
            $error_msg = "";
            if (curl_error($ch)) {
                $error_msg = curl_error($ch);
                throw new Exception('Error al adquirir token. '. $error_msg , ERROR_TOKEN_NO_VALID);
            }
            $sArray= json_decode($server_output);
            if(!isset($sArray->resp->access_token)){
                // ERROR CRITICO:
                // debe notificar al contibuyente. 
                throw new Exception('Error CRITICO al Solicitar token MH. DEBE COMUNICARSE CON SOPORTE TECNICO: '. $server_output , ERROR_TOKEN_NO_VALID);
            }
            self::$accessToken=$sArray->resp->access_token;
            self::$expiresIn=$sArray->resp->expires_in;
            self::$refreshExpiresIn=$sArray->resp->refresh_expires_in;
            self::$refreshToken=$sArray->resp->refresh_token;
            error_log("[INFO] GET ACCESS TOKEN API MH = " . $server_output);
            curl_close($ch);
            return true;
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_TOKEN_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
            return false;
        }
    }

    public static function APICrearClave(){
        try{
            error_log("[INFO] INICIO API CLAVE");
            $ch = curl_init();
            $post = [
                'w' => 'clave',
                'r' => 'clave',
                'tipoCedula'=> self::getIdentificacionCod(self::$transaccion->datosEntidad->idTipoIdentificacion) == '01'?'fisico':'juridico',
                'cedula'=> self::$transaccion->datosEntidad->identificacion,
                'situacion' => self::getSituacionComprobanteCod(self::$transaccion->idSituacionComprobante),
                'codigoPais'=> '506',
                'consecutivo'=> self::$transaccion->consecutivo,
                'codigoSeguridad'=> self::$transaccion->datosEntidad->codigoSeguridad,
                'tipoDocumento'=> self::getDocumentoReferencia(self::$transaccion->idDocumento),
                'terminal'=> self::$transaccion->terminal,
                'sucursal'=> self::$transaccion->local
            ];
            curl_setopt_array($ch, array(
                CURLOPT_URL => self::$apiUrl,
                CURLOPT_RETURNTRANSFER => true,   
                CURLOPT_VERBOSE => true,                      
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post
            ));
            $server_output = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($server_output, 0, $header_size);
            $body = substr($server_output, $header_size);
            $error_msg = "";
            if (curl_error($ch)) {
                $error_msg = curl_error($ch);
                throw new Exception('Error al crear clave. '. $error_msg , ERROR_CLAVE_NO_VALID);
            }
            $sArray= json_decode($server_output);
            if(!isset($sArray->resp->clave)){
                // ERROR CRITICO:
                // debe notificar al contibuyente. 
                throw new Exception('Error CRITICO al crear clave MH. DEBE COMUNICARSE CON SOPORTE TECNICO: '.$server_output, ERROR_CLAVE_NO_VALID);
            }
            self::$clave= $sArray->resp->clave;
            self::$consecutivoFE= $sArray->resp->consecutivo;
            curl_close($ch);
            error_log("[INFO] API CLAVE: ".  self::$clave);
            Factura::setClave(self::$transaccion->idDocumento, self::$transaccion->id, self::$clave, self::$consecutivoFE);
            return true;
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_CLAVE_NO_VALID: '.$e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
            return false;
        }
    }
    
    public static function APICrearXML(){
        try{
            error_log("[INFO] INICIO API CREAR XML");
            $ch = curl_init();
            // detalle de la factura
            $detalles=[];            
            foreach(self::$transaccion->detalleFactura as $d){
                if($d->codigoImpuesto != '00')
                    array_push($detalles, array('cantidad'=> $d->cantidad,
                        'unidadMedida'=> self::getUnidadMedidaCod($d->idUnidadMedida),
                        'detalle'=> $d->detalle,
                        'precioUnitario'=> $d->precioUnitario,
                        'montoTotal'=> $d->montoTotal,
                        'subtotal'=> $d->subTotal,
                        'montoTotalLinea'=> $d->montoTotalLinea,                        
                        'impuesto'=> array(array(
                            'codigo'=> self::getImpuestoCod($d->codigoImpuesto),
                            'tarifa'=> $d->tarifaImpuesto,
                            'monto'=> $d->montoImpuesto)
                            )
                        )
                    );
                else 
                    array_push($detalles, array('cantidad'=> $d->cantidad,
                        'unidadMedida'=> self::getUnidadMedidaCod($d->idUnidadMedida),
                        'detalle'=> $d->detalle,
                        'precioUnitario'=> $d->precioUnitario,
                        'montoTotal'=> $d->montoTotal,
                        'subtotal'=> $d->subTotal,
                        'montoTotalLinea'=> $d->montoTotalLinea
                        )
                    );
            }
            // codigo ubicacion
            $ubicacionEntidadCod= self::getUbicacionCod(self::$transaccion->datosEntidad->idProvincia, self::$transaccion->datosEntidad->idCanton, self::$transaccion->datosEntidad->idDistrito, self::$transaccion->datosEntidad->idBarrio);
            $ubicacionReceptorCod= self::getUbicacionCod(self::$transaccion->datosReceptor->idProvincia, self::$transaccion->datosReceptor->idCanton, self::$transaccion->datosReceptor->idDistrito, self::$transaccion->datosReceptor->idBarrio);
            //
            $post = [
                'w' => 'genXML',
                'r' => 'gen_xml_fe',  // self::$transaccion->idDocumento == 1 ? 'gen_xml_fe' : 'gen_xml_te', // define si es FE - TE.
                'clave'=> self::$clave,
                'consecutivo'=> self::$consecutivoFE,
                'fecha_emision' => self::$fechaEmision->format("c"), // ej: '2018-09-09T13:41:00-06:00',
                /** Emisor **/
                'emisor_nombre'=> self::$transaccion->datosEntidad->nombre,
                'emisor_tipo_indetif'=> self::getIdentificacionCod(self::$transaccion->datosEntidad->idTipoIdentificacion),
                'emisor_num_identif'=> self::$transaccion->datosEntidad->identificacion,
                'nombre_comercial'=> self::$transaccion->datosEntidad->nombreComercial,
                'emisor_provincia'=> $ubicacionEntidadCod[0]->provincia,
                'emisor_canton'=> $ubicacionEntidadCod[0]->canton,
                'emisor_distrito'=> $ubicacionEntidadCod[0]->distrito,
                'emisor_barrio'=> $ubicacionEntidadCod[0]->barrio,
                'emisor_otras_senas'=> self::$transaccion->datosEntidad->otrasSenas,
                // 'emisor_cod_pais_tel'=> '506',
                // 'emisor_tel'=> self::$transaccion->datosEntidad->numTelefono,
                // 'emisor_cod_pais_fax'=> '506',
                // 'emisor_fax'=> '00000000',
                'emisor_email'=> self::$transaccion->datosEntidad->correoElectronico,
                /** Receptor **/  
                'receptor_nombre'=>  self::$transaccion->datosReceptor->nombre,
                'receptor_tipo_identif'=> self::getIdentificacionCod(self::$transaccion->datosReceptor->idTipoIdentificacion),
                'receptor_num_identif'=>  self::$transaccion->datosReceptor->identificacion,
                'receptor_provincia'=> $ubicacionReceptorCod[0]->provincia,
                'receptor_canton'=> $ubicacionReceptorCod[0]->canton,
                'receptor_distrito'=> $ubicacionReceptorCod[0]->distrito,
                'receptor_barrio'=> $ubicacionReceptorCod[0]->barrio,
                //'receptor_cod_pais_tel'=> '506',
                //'receptor_tel'=> self::$transaccion->datosReceptor->numTelefono,
                // 'receptor_cod_pais_fax'=> '506',
                // 'receptor_fax'=> '00000000',
                'receptor_email'=> self::$transaccion->datosReceptor->correoElectronico,
                /** Datos de la venta **/
                'condicion_venta'=> self::getCondicionVentaCod(self::$transaccion->idCondicionVenta),
                // 'plazo_credito'=> self::$transaccion->plazoCredito, 
                'medio_pago'=> self::getMedioPagoCod(self::$transaccion->idMedioPago),
                'cod_moneda'=> self::getCodigoMonedaCod(self::$transaccion->idCodigoMoneda),
                'tipo_cambio'=> self::$transaccion->tipoCambio,
                'total_serv_gravados'=> self::$transaccion->totalServGravados,
                'total_serv_exentos'=> self::$transaccion->totalServExentos,
                'total_merc_gravada'=> self::$transaccion->totalMercanciasGravadas,
                'total_merc_exenta'=> self::$transaccion->totalMercanciasExentas,
                'total_gravados'=> self::$transaccion->totalGravado,
                'total_exentos'=> self::$transaccion->totalExento,
                'total_ventas'=> self::$transaccion->totalVenta,
                'total_descuentos'=>  self::$transaccion->totalDescuentos,
                'total_ventas_neta'=>  self::$transaccion->totalVentaneta,
                'total_impuestos'=>  self::$transaccion->totalImpuesto,
                'total_comprobante'=>  self::$transaccion->totalComprobante,
                'otros'=> 'Factura Electronica',
                /** Detalle **/
                'detalles'=>  json_encode($detalles, JSON_FORCE_OBJECT)
            ];
            /** Referencia PROBAR CUANDO ES UN COMPROBANTE EMITIDO DESPUES DE UNA NC **/
            if(isset(self::$transaccion->idDocumentoReferencia)){
                array_push($post['infoRefeTipoDoc']=  self::getDocumentoReferenciaCod(self::$transaccion->idDocumentoReferencia),
                    $post['infoRefeNumero']=  self::$transaccion->claveReferencia,
                    $post['infoRefeFechaEmision']=  self::$transaccion->fechaEmisionReferencia->format("c"),
                    $post['infoRefeCodigo']=  self::getReferenciaCod(self::$transaccion->idReferencia),
                    $post['infoRefeRazon']=  self::$transaccion->razon);
            }
            //            
            curl_setopt_array($ch, array(
                CURLOPT_URL => self::$apiUrl,
                CURLOPT_RETURNTRANSFER => true,   
                CURLOPT_VERBOSE => true,                      
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post
            ));
            $server_output = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($server_output, 0, $header_size);
            $body = substr($server_output, $header_size);
            $error_msg = "";
            if (curl_error($ch)) {
                $error_msg = curl_error($ch);
                throw new Exception('Error al crear xml. '. $error_msg , ERROR_FEXML_NO_VALID);
            }
            $sArray= json_decode($server_output);
            if(!isset($sArray->resp->xml)){
                // ERROR CRITICO:
                // debe notificar al contibuyente. 
                throw new Exception('Error CRITICO al crear xml de comprobante. DEBE COMUNICARSE CON SOPORTE TECNICO: '. $server_output, ERROR_FEXML_NO_VALID);
            }
            self::$xml= $sArray->resp->xml;
            // ESTA LINEA ES DE PRUEBAS PARA VALIDAR EL XML A ENVIAR.
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 1, 'XML a enviar', base64_decode($sArray->resp->xml));
            //*******************************************************/
            curl_close($ch);
            error_log("[INFO] API CREAR XML EXITOSO!" );
            return true;
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_FEXML_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
            return false;
        }
    }

    public static function APICrearNCXML(){
        try{
            error_log("[INFO] INICIO API CREAR NC XML");
            $ch = curl_init();
            // detalle de la factura
            $detalles=[];
            foreach(self::$transaccion->detalleFactura as $d){
                if($d->codigoImpuesto != '00')
                    array_push($detalles, array('cantidad'=> $d->cantidad,
                        'unidadMedida'=> self::getUnidadMedidaCod($d->idUnidadMedida),
                        'detalle'=> $d->detalle,
                        'precioUnitario'=> $d->precioUnitario,
                        'montoTotal'=> $d->montoTotal,
                        'subtotal'=> $d->subTotal,
                        'montoTotalLinea'=> $d->montoTotalLinea,
                        'impuesto'=> array(array(
                            'codigo'=> self::getImpuestoCod($d->codigoImpuesto),
                            'tarifa'=> $d->tarifaImpuesto,
                            'monto'=> $d->montoImpuesto)
                            )
                        )
                    );
                else
                    array_push($detalles, array('cantidad'=> $d->cantidad,
                        'unidadMedida'=> self::getUnidadMedidaCod($d->idUnidadMedida),
                        'detalle'=> $d->detalle,
                        'precioUnitario'=> $d->precioUnitario,
                        'montoTotal'=> $d->montoTotal,
                        'subtotal'=> $d->subTotal,
                        'montoTotalLinea'=> $d->montoTotalLinea
                        )
                    );
            }
            // codigo ubicacion
            $ubicacionEntidadCod= self::getUbicacionCod(self::$transaccion->datosEntidad->idProvincia, self::$transaccion->datosEntidad->idCanton, self::$transaccion->datosEntidad->idDistrito, self::$transaccion->datosEntidad->idBarrio);
            $ubicacionReceptorCod= self::getUbicacionCod(self::$transaccion->datosReceptor->idProvincia, self::$transaccion->datosReceptor->idCanton, self::$transaccion->datosReceptor->idDistrito, self::$transaccion->datosReceptor->idBarrio);
            //
            $post = [
                'w' => 'genXML',
                'r' => 'gen_xml_nc',
                'clave'=> self::$clave,
                'consecutivo'=> self::$consecutivoFE,
                'fecha_emision' => self::$fechaEmision->format("c"), // ej: '2018-09-09T13:41:00-06:00',
                /** Emisor **/
                'emisor_nombre'=> self::$transaccion->datosEntidad->nombre,
                'emisor_tipo_indetif'=> self::getIdentificacionCod(self::$transaccion->datosEntidad->idTipoIdentificacion),
                'emisor_num_identif'=> self::$transaccion->datosEntidad->identificacion,
                'nombre_comercial'=> self::$transaccion->datosEntidad->nombreComercial,
                'emisor_provincia'=> $ubicacionEntidadCod[0]->provincia,
                'emisor_canton'=> $ubicacionEntidadCod[0]->canton,
                'emisor_distrito'=> $ubicacionEntidadCod[0]->distrito,
                'emisor_barrio'=> $ubicacionEntidadCod[0]->barrio,
                'emisor_otras_senas'=> self::$transaccion->datosEntidad->otrasSenas,
                // 'emisor_cod_pais_tel'=> '506',
                // 'emisor_tel'=> self::$transaccion->datosEntidad->numTelefono,
                // 'emisor_cod_pais_fax'=> '506',
                // 'emisor_fax'=> '00000000',
                'emisor_email'=> self::$transaccion->datosEntidad->correoElectronico,
                /** Receptor **/  
                'receptor_nombre'=>  self::$transaccion->datosReceptor->nombre,
                'receptor_tipo_identif'=> self::getIdentificacionCod(self::$transaccion->datosReceptor->idTipoIdentificacion),
                'receptor_num_identif'=>  self::$transaccion->datosReceptor->identificacion,
                'receptor_provincia'=> $ubicacionReceptorCod[0]->provincia,
                'receptor_canton'=> $ubicacionReceptorCod[0]->canton,
                'receptor_distrito'=> $ubicacionReceptorCod[0]->distrito,
                'receptor_barrio'=> $ubicacionReceptorCod[0]->barrio,
                //'receptor_cod_pais_tel'=> '506',
                //'receptor_tel'=> self::$transaccion->datosReceptor->numTelefono,
                // 'receptor_cod_pais_fax'=> '506',
                // 'receptor_fax'=> '00000000',
                'receptor_email'=> self::$transaccion->datosReceptor->correoElectronico,
                /** Datos de la venta **/
                'condicion_venta'=> self::getCondicionVentaCod(self::$transaccion->idCondicionVenta),
                // 'plazo_credito'=> self::$transaccion->plazoCredito, 
                'medio_pago'=> self::getMedioPagoCod(self::$transaccion->idMedioPago),
                'cod_moneda'=> self::getCodigoMonedaCod(self::$transaccion->idCodigoMoneda),
                'tipo_cambio'=> self::$transaccion->tipoCambio,
                'total_serv_gravados'=> self::$transaccion->totalServGravados,
                'total_serv_exentos'=> self::$transaccion->totalServExentos,
                'total_merc_gravada'=> self::$transaccion->totalMercanciasGravadas,
                'total_merc_exenta'=> self::$transaccion->totalMercanciasExentas,
                'total_gravados'=> self::$transaccion->totalGravado,
                'total_exentos'=> self::$transaccion->totalExento,
                'total_ventas'=> self::$transaccion->totalVenta,
                'total_descuentos'=>  self::$transaccion->totalDescuentos,
                'total_ventas_neta'=>  self::$transaccion->totalVentaneta,
                'total_impuestos'=>  self::$transaccion->totalImpuesto,
                'total_comprobante'=>  self::$transaccion->totalComprobante,
                'otros'=> 'Nota de Credito',
                /** Detalle **/
                'detalles'=>  json_encode($detalles, JSON_FORCE_OBJECT),
                /** Referencia **/
                'infoRefeTipoDoc'=>  self::getDocumentoReferenciaCod(self::$transaccion->idDocumento),
                'infoRefeNumero'=>  self::$transaccion->clave,
                'infoRefeFechaEmision'=>  self::$transaccion->fechaEmision,
                'infoRefeCodigo'=>  self::getReferenciaCod(self::$transaccion->idReferencia),
                'infoRefeRazon'=>  self::$transaccion->razon
            ];
            curl_setopt_array($ch, array(
                CURLOPT_URL => self::$apiUrl,
                CURLOPT_RETURNTRANSFER => true,   
                CURLOPT_VERBOSE => true,                      
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post
            ));
            $server_output = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($server_output, 0, $header_size);
            $body = substr($server_output, $header_size);
            $error_msg = "";
            if (curl_error($ch)) {
                $error_msg = curl_error($ch);
                throw new Exception('Error al crear NC xml. '. $error_msg , ERROR_NCXML_NO_VALID);
            }
            $sArray= json_decode($server_output);
            if(!isset($sArray->resp->xml)){
                // ERROR CRITICO:
                // debe notificar al contibuyente. 
                throw new Exception('Error CRITICO al crear xml de Nota de Credito. DEBE COMUNICARSE CON SOPORTE TECNICO: '. $server_output, ERROR_NCXML_NO_VALID);
            }
            self::$xml= $sArray->resp->xml;
            // ESTA LINEA ES DE PRUEBAS PARA VALIDAR EL XML A ENVIAR.
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 1, 'NC xml a enviar', base64_decode($sArray->resp->xml));
            //*******************************************************/
            curl_close($ch);
            error_log("[INFO] API CREAR NC XML EXITOSO!" );
            return true;
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_NCXML_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
            return false;
        }
    }

    public static function APICifrarXml(){
        try{
            error_log("[INFO] INICIO API CIFRAR XML: ");
            $ch = curl_init();
            $post = [
                'w' => 'signXML',
                'r' => 'signFE',
                'p12Url'=> self::$transaccion->datosEntidad->downloadCode,
                'inXml'=> self::$xml,
                'pinP12' => self::$transaccion->datosEntidad->pinp12,
                'tipodoc'=> self::getDocumentoReferencia(self::$transaccion->idDocumento)
            ];
            curl_setopt_array($ch, array(
                CURLOPT_URL => self::$apiUrl,
                CURLOPT_RETURNTRANSFER => true,   
                CURLOPT_VERBOSE => true,                      
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post
            ));
            $server_output = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($server_output, 0, $header_size);
            $body = substr($server_output, $header_size);
            $error_msg = "";
            if (curl_error($ch)) {
                $error_msg = curl_error($ch);
                throw new Exception('Error al cifrar xml. '. $error_msg , ERROR_CIFRAR_NO_VALID);
            }
            $sArray= json_decode($server_output);            
            if(!isset($sArray->resp->xmlFirmado)){
                // ERROR CRITICO:
                // debe notificar al contibuyente. 
                throw new Exception('Error CRITICO al Cifrar xml de comprobante. DEBE COMUNICARSE CON SOPORTE TECNICO: '.$server_output, ERROR_CIFRAR_NO_VALID);
            }
            self::$xmlFirmado= $sArray->resp->xmlFirmado;
            error_log("[INFO] API CIFRADO XML EXITOSO!" );
            curl_close($ch);
            return true;
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_CIFRAR_NO_VALID:'. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
            return false;
        }
    }

    public static function APIEnviar(){
        try{
            error_log("[INFO] INICIO API ENVIO" );
            self::APIGetToken();
            $ch = curl_init();
            $r = '';
            switch (self::$transaccion->idDocumento){
                case 1:
                case 2:
                case 3:
                case 8: // Contingencia..
                    $r = 'json';
                    break;
                case 4:
                    $r = 'sendTE';
                    break;
                case 5:
                case 6:
                case 7:
                    $r = 'sendMensaje';
                    break;
            }
            $post = [
                'w' => 'send',
                'r' => $r,
                'token'=>self::$accessToken,
                'clave'=> self::$clave,
                'fecha' => self::$fechaEmision->format("c"),
                'emi_tipoIdentificacion'=> self::getIdentificacionCod(self::$transaccion->datosEntidad->idTipoIdentificacion),
                'emi_numeroIdentificacion'=> self::$transaccion->datosEntidad->identificacion,
                'recp_tipoIdentificacion'=>  self::getIdentificacionCod(self::$transaccion->datosReceptor->idTipoIdentificacion),
                'recp_numeroIdentificacion'=> self::$transaccion->datosReceptor->identificacion,
                'comprobanteXml'=>	self::$xmlFirmado,
                'client_id'=> self::$apiMode,
                'consecutivoReceptor'=> self::$transaccion->consecutivoFE ?? null
            ];
            curl_setopt_array($ch, array(
                CURLOPT_URL => self::$apiUrl,
                CURLOPT_RETURNTRANSFER => true,   
                CURLOPT_VERBOSE => true,                      
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post
            ));
            $server_output = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($server_output, 0, $header_size);
            $body = substr($server_output, $header_size);
            $error_msg = "";
            if (curl_error($ch)) {
                $error_msg = curl_error($ch);
                $timedOut = strpos($error_msg, 'Operation timed out');
                if($timedOut===false)
                    throw new Exception('Error CRITICO al ENVIAR el comprobante. DEBE COMUNICARSE CON SOPORTE TECNICO: '.$error_msg, ERROR_ENVIO_NO_VALID);
                else {
                    //timed out.
                    error_log("[ERROR]  (-600): ". $error_msg);
                    historico::create(self::$transaccion->id, self::$transaccion->idEmisor, self::$transaccion->idDocumento, 6, $error_msg);
                    Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 6, self::$fechaEmision->format("c"));
                    return false;
                }
            }
            $sArray= json_decode($server_output);       
            if(!isset($sArray->resp->Status)){
                // ERROR CRITICO: almacena estado= 5 (otros) - error al enviar comprobante.
                throw new Exception('Error CRITICO al ENVIAR el comprobante. DEBE COMUNICARSE CON SOPORTE TECNICO: '. $server_output, ERROR_ENVIO_NO_VALID);
            }
            //
            if($sArray->resp->Status==400){
                $resp400 = strpos($sArray->resp->text[17], 'ya fue recibido anteriormente');
                if ($resp400 === false)
                    throw new Exception('Error CRITICO al ENVIAR el comprobante. DEBE COMUNICARSE CON SOPORTE TECNICO, STATUS('.$sArray->resp->Status.'):  '.$sArray->resp->text[17], ERROR_ENVIO_NO_VALID);
                else {
                    error_log("[WARNING] El documento (". self::$clave .") Ya fue recibido anteriormente" );
                    historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, null, 'El documento ya fue recibido anteriormente, STATUS('.$sArray->resp->Status.')');
                    //Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 2, self::$fechaEmision->format("c"));
                    // curl_close($ch);
                    return true;
                }
            }
            if($sArray->resp->Status!=202){
                throw new Exception('Error CRITICO al ENVIAR el comprobante. DEBE COMUNICARSE CON SOPORTE TECNICO, STATUS('.$sArray->resp->Status.'):  '.$server_output, ERROR_ENVIO_NO_VALID);                
            }
            else {
                // almacena estado: enviado (202).
                historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 2, 'Comprobante ENVIADO EXITOSAMENTE, STATUS('.$sArray->resp->Status.')');
                Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 2, self::$fechaEmision->format("c"));
            }
            //
            error_log("[INFO] API ENVIO EXITOSO!" );
            curl_close($ch);
            return true;
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 5, 'ERROR_ENVIO_NO_VALID: '. $e->getMessage());
            Factura::updateEstado(self::$transaccion->idDocumento, self::$transaccion->id, 5, self::$fechaEmision->format("c"));
            return false;
        }
    }

    public static function APIConsultaComprobante($t){
        try{
            self::$transaccion= $t;
            error_log("[INFO] API CONSULTA CLAVE: ". self::$transaccion->clave);
            self::getApiUrl();
            self::APIGetToken();
            $ch = curl_init();
            $post = [
                'w' => 'consultar',
                'r' => 'consultarCom',
                'token'=> self::$accessToken,
                'clave'=> self::$transaccion->clave,
                'client_id'=> self::$apiMode
            ];  
            curl_setopt_array($ch, array(
                CURLOPT_URL => self::$apiUrl,
                CURLOPT_RETURNTRANSFER => true,   
                CURLOPT_VERBOSE => true,      
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post
            ));
            $server_output = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($server_output, 0, $header_size);
            $body = substr($server_output, $header_size);
            $error_msg = "";
            if (curl_error($ch)) {
                $error_msg = curl_error($ch);
                throw new Exception('Error al consultar API MH: '. $error_msg , ERROR_CONSULTA_NO_VALID);
            }            
            $sArray=json_decode($server_output);
            if(!isset($sArray->resp->clave)){
                throw new Exception('Error CRITICO al consultar el comprobante. DEBE COMUNICARSE CON SOPORTE TECNICO: '.$server_output, ERROR_CONSULTA_NO_VALID);
            }
            $respuestaXml='';
            if(!isset($sArray->resp->clave)){
                $null = strpos($server_output, 'null');
                if($null===false){
                    throw new Exception('Error CRITICO al consultar el comprobante. DEBE COMUNICARSE CON SOPORTE TECNICO: '.$server_output, ERROR_CONSULTA_NO_VALID);                    
                }
                else {
                    // clave inválida, no existe en ATV.
                    Factura::updateIdEstadoComprobante(self::$transaccion->id, self::$transaccion->idDocumento, 5);
                    historico::create(self::$transaccion->id, self::$transaccion->idEmisor, self::$transaccion->idDocumento, 5, 'La transacción no fue enviada a los sistemas de ATV.');
                    throw new Exception('Documento no registrado en ATV: '.$server_output, ERROR_CONSULTA_NO_VALID);                    
                }
                
            }         
            // si el estado es procesando debe consultar de nuevo.
            if($estadoTransaccion=='procesando'){
                historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 2, $estadoTransaccion );
                //self::APIConsultaComprobante();
            }
            else if($estadoTransaccion=='aceptado'){
                $xml= base64_decode($respuestaXml);
                $fxml = simplexml_load_string($xml);
                historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 3, '['.$estadoTransaccion.'] '.$fxml->DetalleMensaje, $xml);
                Factura::updateIdEstadoComprobante(self::$transaccion->id, self::$transaccion->idDocumento, 3);
                //AQUI VA ENVIAR EMAIL
                if(Invoice::create(self::$transaccion)){
                    return true;
                }    
            }
            else if($estadoTransaccion=='rechazado'){
                // genera informe con los datos del rechazo. y pone estado de la transaccion pendiente para ser enviada cuando sea corregida.
                $xml= base64_decode($respuestaXml);
                $fxml = simplexml_load_string($xml);
                $resp400 = strpos($fxml->DetalleMensaje, 'ya existe en nuestras bases de datos');
                if ($resp400 === false){
                    historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, 4, '['.$estadoTransaccion.'] '.$fxml->DetalleMensaje, $xml);
                    Factura::updateIdEstadoComprobante(self::$transaccion->id, self::$transaccion->idDocumento, 4);
                }
                else { // ya existe en base de datos de MH. No modifica el estado
                    error_log("[WARNING] El documento (". self::$transaccion->clave .") Ya fue recibido anteriormente" );
                    historico::create(self::$transaccion->id, self::$transaccion->idEntidad, self::$transaccion->idDocumento, null, "[WARNING]". $fxml->DetalleMensaje, $xml);
                    return true;
                }
            }            
            error_log("[INFO] API CONSULTA, estado de la transaccion(".self::$transaccion->id."): ". $estadoTransaccion);
            curl_close($ch);
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            return false;
        }
    }
}
?>