<?php
include_once('historico.php');
define('ERROR_USERS_NO_VALID', '-500');
define('ERROR_TOKEN_NO_VALID', '-501');
define('ERROR_CLAVE_NO_VALID', '-502');
define('ERROR_XML_NO_VALID', '-503');
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

class FacturaElectronica{
    static $transaccion;
    static $fechaEmision;
    static $apiUrl;
    public static function iniciar($t){
        try{
            self::$transaccion= $t;
            if(!isset($_SESSION['API']))
                throw new Exception('Error al leer informacion del contribuyente. '. $error_msg , ERROR_USERS_NO_VALID);            
            self::$fechaEmision= date_create(self::$transaccion->fechaEmision);
            if(self::getApiUrl()){
                if(self::APICrearClave()){
                    if(self::APICrearXML()){
                        if(self::APICifrarXml()){
                            if(self::APIEnviar()){
                                self::APIConsultaComprobante();
                            }
                        }
                    }
                }
            }
        }
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
        }
    }

    private static function getApiUrl(){
        require_once('globals.php');
        if (file_exists(Globals::configFile)) {
            $set = parse_ini_file(Globals::configFile,true); 
            self::$apiUrl= $set[Globals::app]['apiurl'];
            return true;
        }         
        else {
            throw new Exception('Acceso denegado al Archivo de configuración.',-1);            
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
            historico::create(self::$transaccion->id, 1, 'ERROR_TIPO_IDENTIFICACION_NO_VALID: '. $e->getMessage());
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
            historico::create(self::$transaccion->id, 1, 'ERROR_SITUACION_COMPROBANTE_NO_VALID: '. $e->getMessage());
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
            historico::create(self::$transaccion->id, 1, 'ERROR_IMPUESTO_NO_VALID: '. $e->getMessage());
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
            historico::create(self::$transaccion->id, 1, 'ERROR_UNIDAD_MEDIDA_NO_VALID '. $e->getMessage());
        }
    }

    private static function getUbicacionCod(){
        try{
            $sql='SELECT p.codigo as provincia, c.codigo as canton, d.codigo as distrito, b.codigo as barrio
                FROM provincia p, canton c , distrito d, barrio b        
                where p.id=:provincia and c.id=:canton and d.id=:distrito and b.id=:barrio';
            $param= array(':provincia'=>$_SESSION['API']->idProvincia, 
                ':canton'=>$_SESSION['API']->idCanton,
                ':distrito'=>$_SESSION['API']->idDistrito,
                ':barrio'=>$_SESSION['API']->idBarrio,
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
            historico::create(self::$transaccion->id, 1, 'ERROR_UBICACION_NO_VALID: '. $e->getMessage());
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
            historico::create(self::$transaccion->id, 1, 'ERROR_MEDIOPAGO_NO_VALID: '. $e->getMessage());
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
            historico::create(self::$transaccion->id, 1, 'ERROR_MONEDA_NO_VALID: '. $e->getMessage());
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
            historico::create(self::$transaccion->id, 1, 'ERROR_ESTADO_COMPROBANTE_NO_VALID: '. $e->getMessage());
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
            historico::create(self::$transaccion->id, 1, 'ERROR_CONDICIONVENTA_NO_VALID: '. $e->getMessage());
        }
    }

    public static function APIGetToken(){
        try{
            $ch = curl_init();
            $post = [
                'w' => 'token',
                'r' => 'gettoken',
                'grant_type'=>'password', 
                'client_id'=> 'api-stag', 
                'username' => $_SESSION['API']->username,
                'password'=>  $_SESSION['API']->password
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
            $_SESSION['API']->accessToken=$sArray->resp->access_token;
            $_SESSION['API']->expiresIn=$sArray->resp->expires_in;
            $_SESSION['API']->refreshExpiresIn=$sArray->resp->refresh_expires_in;
            $_SESSION['API']->refreshToken=$sArray->resp->refresh_token;
            error_log("[INFO] INICIO API CLAVE" . $server_output);
            curl_close($ch);
            return true;
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, 1, 'ERROR_TOKEN_NO_VALID: '. $e->getMessage());
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
                'tipoCedula'=> self::getIdentificacionCod($_SESSION['API']->idTipoIdentificacion) == '01'?'fisico':'juridico',
                'cedula'=> $_SESSION['API']->identificacion,
                'situacion' => self::getSituacionComprobanteCod(self::$transaccion->idSituacionComprobante),
                'codigoPais'=> '506',
                'consecutivo'=> self::$transaccion->consecutivo,
                'codigoSeguridad'=> $_SESSION['API']->codigoSeguridad,
                'tipoDocumento'=> self::$transaccion->tipoDocumento,
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
            $_SESSION['API']->clave= $sArray->resp->clave;
            $_SESSION['API']->consecutivo= $sArray->resp->consecutivo;
            error_log(" Resp Clave: ". $server_output);
            curl_close($ch);
            error_log("[INFO] API CLAVE: ".  $_SESSION['API']->clave);
            return true;
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, 1, 'ERROR_CLAVE_NO_VALID: '.$e->getMessage());
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
            }
            // codigo ubicacion
            $ubicacionCod= self::getUbicacionCod();
            //
            $post = [
                'w' => 'genXML',
                'r' => 'gen_xml_fe',
                'clave'=> $_SESSION['API']->clave,
                'consecutivo'=> $_SESSION['API']->consecutivo,
                'fecha_emision' => self::$fechaEmision->format("c"), // ej: '2018-09-09T13:41:00-06:00',
                /** Emisor **/
                'emisor_nombre'=> $_SESSION['API']->nombre,
                'emisor_tipo_indetif'=> self::getIdentificacionCod($_SESSION['API']->idTipoIdentificacion),
                'emisor_num_identif'=> $_SESSION['API']->identificacion,
                'nombre_comercial'=> $_SESSION['API']->nombreComercial,
                'emisor_provincia'=> $ubicacionCod[0]->provincia,
                'emisor_canton'=> $ubicacionCod[0]->canton,
                'emisor_distrito'=> $ubicacionCod[0]->distrito,
                'emisor_barrio'=> $ubicacionCod[0]->barrio,
                'emisor_otras_senas'=> $_SESSION['API']->otrasSenas,
                'emisor_cod_pais_tel'=> '506',
                'emisor_tel'=> $_SESSION['API']->numTelefono,
                // 'emisor_cod_pais_fax'=> '506',
                // 'emisor_fax'=> '00000000',
                'emisor_email'=> $_SESSION['API']->correoElectronico,
                /** Receptor **/  // deben ser los datos reales del receptor o un receptor generico.
                'receptor_nombre'=> $_SESSION['API']->nombre,
                'receptor_tipo_identif'=> self::getIdentificacionCod($_SESSION['API']->idTipoIdentificacion),
                'receptor_num_identif'=> $_SESSION['API']->identificacion,
                'receptor_provincia'=> $ubicacionCod[0]->provincia,
                'receptor_canton'=> $ubicacionCod[0]->canton,
                'receptor_distrito'=> $ubicacionCod[0]->distrito,
                'receptor_barrio'=> $ubicacionCod[0]->barrio,
                'receptor_cod_pais_tel'=> '506',
                'receptor_tel'=> '84922891',
                // 'receptor_cod_pais_fax'=> '506',
                // 'receptor_fax'=> '00000000',
                'receptor_email'=> $_SESSION['API']->correoElectronico,
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
                'otros'=> 'Tropical SNO',
                /** Detalle **/
                'detalles'=>  json_encode($detalles, JSON_FORCE_OBJECT)
            ];
            curl_setopt_array($ch, array(
                CURLOPT_URL => self::$apiUrl,
                CURLOPT_RETURNTRANSFER => true,   
                CURLOPT_VERBOSE => true,                      
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30000000,
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
                throw new Exception('Error al crear xml. '. $error_msg , ERROR_XML_NO_VALID);
            }
            $sArray= json_decode($server_output);
            if(!isset($sArray->resp->xml)){
                // ERROR CRITICO:
                // debe notificar al contibuyente. 
                throw new Exception('Error CRITICO al crear xml de comprobante. DEBE COMUNICARSE CON SOPORTE TECNICO: '. $server_output, ERROR_XML_NO_VALID);
            }
            $_SESSION['API']->xml= $sArray->resp->xml;
            historico::create(self::$transaccion->id, 1, 'xml a enviar', base64_decode($sArray->resp->xml));
            curl_close($ch);
            error_log("[INFO] API CREAR XML EXITOSO!" );
            return true;
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, 1, 'ERROR_XML_NO_VALID: '. $e->getMessage());
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
                'p12Url'=> $_SESSION['API']->downloadCode,
                'inXml'=> $_SESSION['API']->xml,
                'pinP12' => $_SESSION['API']->pinp12,
                'tipodoc'=> self::$transaccion->tipoDocumento
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
            $_SESSION['API']->xmlFirmado= $sArray->resp->xmlFirmado;
            error_log("[INFO] API CIFRADO XML EXITOSO!" );
            curl_close($ch);
            return true;
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, 1, 'ERROR_CIFRAR_NO_VALID:'. $e->getMessage());
            return false;
        }
    }

    public static function APIEnviar(){
        try{
            error_log("[INFO] INICIO API ENVIO" );
            self::APIGetToken();
            $ch = curl_init();
            $post = [
                'w' => 'send',
                'r' => 'json',
                'token'=>$_SESSION['API']->accessToken,
                'clave'=> $_SESSION['API']->clave,
                'fecha' => self::$fechaEmision->format("c"),
                'emi_tipoIdentificacion'=> $_SESSION['API']->idTipoIdentificacion,
                'emi_numeroIdentificacion'=> $_SESSION['API']->identificacion,
                'recp_tipoIdentificacion'=> '01',
                'recp_numeroIdentificacion'=> '000000000',
                'comprobanteXml'=>	$_SESSION['API']->xmlFirmado,
                'client_id'=> 'api-stag' // api-prod
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
                historico::create(self::$transaccion->id, 5, 'ERROR_ENVIO_NO_VALID'. $error_msg);
                Factura::updateEstado(self::$transaccion->id, 5, self::$fechaEmision->format("c"));
                error_log("[ERROR] : ". $error_msg);
                // debe notificar al contibuyente. 
                //
                curl_close($ch);
                return false;
            }
            $sArray= json_decode($server_output);       
            if(!isset($sArray->resp->Status)){
                // ERROR CRITICO: almacena estado= 5 (otros) - error al enviar comprobante.
                historico::create(self::$transaccion->id, 5, 'ERROR_ENVIO_NO_VALID'. $server_output);
                Factura::updateEstado(self::$transaccion->id, 5, self::$fechaEmision->format("c"));
                error_log("[ERROR] : ". $error_msg);
                // debe notificar al contibuyente. 
                //
                curl_close($ch);
                return false;
            }
            //
            if($sArray->resp->Status!=202){
                historico::create(self::$transaccion->id, 5, 'Comprobante ENVIADO con error, STATUS('.$sArray->resp->Status.'): '. $server_output);
                Factura::updateEstado(self::$transaccion->id, 5, self::$fechaEmision->format("c"));
                error_log("[ERROR] : ". $server_output);
                // debe notificar al contibuyente. 
                //
                curl_close($ch);
                return false;
            }
            else {
                // almacena estado: enviado (202).
                historico::create(self::$transaccion->id, 2, 'Comprobante ENVIADO, STATUS('.$sArray->resp->Status.')');
                Factura::updateEstado(self::$transaccion->id, 2, self::$fechaEmision->format("c"));
            }
            //
            error_log("[INFO] API ENVIO EXITOSO!" );
            curl_close($ch);
            return true;
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            historico::create(self::$transaccion->id, 1, 'ERROR_ENVIO_NO_VALID: '. $e->getMessage());
            return false;
        }
    }

    public static function APIConsultaComprobante(){
        try{
            error_log("[INFO] API CONSULTA");
            $ch = curl_init();
            $post = [
                'w' => 'consultar',
                'r' => 'consultarCom',
                'token'=>$_SESSION['API']->accessToken,
                'clave'=> $_SESSION['API']->clave,
                'client_id'=> 'api-stag' // api-prod
            ];  
            curl_setopt_array($ch, array(
                CURLOPT_URL => self::$apiUrl,
                CURLOPT_RETURNTRANSFER => true,   
                CURLOPT_VERBOSE => true,      
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 300,
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
            // session de usuario ATV
            $sArray=json_decode($server_output);
            if(!isset($sArray->resp->clave)){
                throw new Exception('Error CRITICO al consultar el comprobante. DEBE COMUNICARSE CON SOPORTE TECNICO: '.$server_output, ERROR_CONSULTA_NO_VALID);
            }
            $respuestaXml='';
            foreach($sArray->resp as $key=> $r){
                if($key=='ind-estado')
                    self::$transaccion->estado= $r;
                if($key=='respuesta-xml')
                    $respuestaXml= $r;
            }           
            // si el estado es procesando debe consultar de nuevo.
            if(self::$transaccion->estado=='procesando'){
                historico::create(self::$transaccion->id, 2, self::$transaccion->estado );
                self::APIConsultaComprobante();
            }
            else if(self::$transaccion->estado=='aceptado'){
                $xml= base64_decode($respuestaXml);
                historico::create(self::$transaccion->id, 3, self::$transaccion->estado, $xml);
                Factura::updateEstado(self::$transaccion->id, 3, self::$fechaEmision->format("c"));
            }
            else if(self::$transaccion->estado=='rechazado'){
                // genera informe con los datos del rechazo. y pone estado de la transaccion pendiente para ser enviada cuando sea corregida.
                $errores= base64_decode($respuestaXml);
                historico::create(self::$transaccion->id, 4, self::$transaccion->estado, $errores);
                Factura::updateEstado(self::$transaccion->id, 4, self::$fechaEmision->format("c"));
            }            
            error_log("[INFO] API CONSULTA, estado de la transaccion(".self::$transaccion->id."): ". self::$transaccion->estado);
            curl_close($ch);
        } 
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            return false;
        }
    }
}
?>