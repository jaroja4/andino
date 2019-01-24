<?php
    setlocale(LC_ALL,"es_ES");
    mb_internal_encoding('UTF-8');
    //Classes
    include("WebToPDF/InvoicePrinter.php");
    require_once("UUID.php");
    require_once("globals.php");
    require "mail/mail.php";
    // Instance        
    $invoice= new Invoice();

class Invoice{
    
    public static $reimpresion = 0;
    public static $email_array_address_to = [];

    public static function Create($transaccion){
        try {
            array_push(self::$email_array_address_to, $transaccion->datosReceptor->correoElectronico);
            if (($key = array_search('default@default.com', self::$email_array_address_to)) !== false) {
                unset(self::$email_array_address_to[$key]);
            }
            if(self::$email_array_address_to==[]){
                error_log("[INFO] No hay destinatarios para el envío de comprobantes.");
                return false;
            }
            $archivosAdjunto = [];
            $sql='SELECT s.email_name, s.email_subject, s.email_SMTPSecure, s.email_Host, s.email_SMTPAuth, s.email_user, s.email_password, s.email_ssl, 
                    s.email_smtpout, s.email_port, s.email_body, s.email_logo, s.html, s.email_footer, e.numTelefono, e.identificacion, p.provincia, c.canton
                FROM smtpXEntidad s
                    INNER JOIN entidad e ON s.idEntidad = e.id
                    INNER JOIN provincia p ON e.idProvincia = p.id
                    INNER JOIN canton c ON e.idCanton = c.id
                WHERE idEntidad=:idEntidad
                AND activa = "1";';
            $param= array(':idEntidad'=>$transaccion->idEmisor);
            $data= DATA::Ejecutar($sql,$param);     
            if ($data){
                $nameCompany = $data[0]["email_name"];                
                $address = $data[0]["provincia"] . ", " . $data[0]["canton"];
                $contact = $data[0]["numTelefono"];
                $cedula =$data[0]["identificacion"];
                $email_user =$data[0]["email_user"];
                $email_subject =$data[0]["email_subject"];
                $email_SMTPSecure =$data[0]["email_SMTPSecure"];
                $email_Host =$data[0]["email_Host"];
                $email_SMTPAuth =$data[0]["email_SMTPAuth"];
                $email_password =$data[0]["email_password"];
                $email_ssl =$data[0]["email_ssl"];
                $email_smtpout =$data[0]["email_smtpout"];
                $email_port =$data[0]["email_port"];
                $email_SMTPAuth =$data[0]["email_SMTPAuth"];
                $email_body =$data[0]["email_body"];
                $email_logo =$data[0]["email_logo"];
                $html =$data[0]["html"];
                $email_footer =$data[0]["email_footer"];
            }
            // busca el comprobante enviado.
            $sql='SELECT xml FROM storylabsFE.historicoComprobante
                    WHERE idFactura = :idFactura
                    AND idEstadoComprobante = "1" 
                    order by fecha desc
                    LIMIT 1';
            $param= array(':idFactura'=>$transaccion->id);
            $xml= DATA::Ejecutar($sql,$param); 
            if(!$xml){
                error_log("[WARNING]  (-9058): No hay archivos xml a enviar. Clave: ". $transaccion->clave);
                return json_encode(array(
                    'code' => -9058,
                    'msg' => 'No hay archivos xml a enviar'));
            }
            $dirInvoicePath = "../../Invoices/xml/";
            if (!file_exists($dirInvoicePath))
                mkdir($dirInvoicePath, 0755, true);
            array_push($archivosAdjunto, $path_xml =  $dirInvoicePath. $transaccion->clave ."_". str_replace(' ', '', $transaccion->datosReceptor->identificacion) . ".xml");
            $file_xml = fopen($path_xml, "w") or die(
                json_encode(array(
                    'code' => -9059,
                    'msg' => 'No se puede crear el archivo xml'))
            );
            fwrite($file_xml, $xml[0]["xml"]."\n");
            fclose($file_xml);
            // busca el acuse de recibo.
            $sql='SELECT xml FROM storylabsFE.historicoComprobante
                    WHERE idFactura = :idFactura
                    AND idEstadoComprobante = "3" 
                    order by fecha desc
                    LIMIT 1';
            $param= array(':idFactura'=>$transaccion->id);
                $acuse= DATA::Ejecutar($sql,$param); 
            if(!$acuse){
                error_log("[WARNING]  (-9058): No hay archivos de acuse xml a enviar. Clave: ". $transaccion->clave);
                return json_encode(array(
                    'code' => -9058,
                    'msg' => 'No hay archivos xml a enviar'));
            }
            $dirAcusePath = "../../Invoices/acuse/";
            if (!file_exists($dirAcusePath))
                mkdir($dirAcusePath, 0755, true);
            array_push($archivosAdjunto, $path_acuse = $dirAcusePath . $transaccion->clave ."_". str_replace(' ', '', $transaccion->datosReceptor->identificacion) . ".xml");
            $file_acuse = fopen($path_acuse, "w") or die(                
                json_encode(array(
                    'code' => -9059,
                    'msg' => 'No se puede crear el archivo acuse xml'))
            );
            fwrite($file_acuse, $acuse[0]["xml"]."\n");
            fclose($file_acuse);            
            // datos del correo.
            $doc= "FACTURA ELECTRÓNICA";
            switch($transaccion->idDocumento){
                case 2:
                    $doc = ' NOTA DE CREDITO';
                break;
                case 3:
                    $doc = ' NOTA DE DEBITO';
                break;
                case 4:
                    $doc = ' TIQUETE ELECTRONICO';
                break;
            }
            //
            $InvoicePrinter = new InvoicePrinter("A4", "¢", "es");    
            /* Header Settings */
            $InvoicePrinter->setTimeZone('America/Costa_Rica');
            if($email_logo!=null)
                $InvoicePrinter->setLogo($email_logo); // validar si es necesario un logo por defecto.
            $InvoicePrinter->setColor("#007fff");
            $InvoicePrinter->setType($nameCompany);
            $InvoicePrinter->setAddress($address); 
            $InvoicePrinter->setPhone($contact);
            $InvoicePrinter->setLegal_Document($cedula);
            $InvoicePrinter->setEmail($email_user);
            $InvoicePrinter->setFrom(array(
                "TIPO COMPROBANTE ELECTRONICO: ", $doc, 
                "Consecutivo: ".$transaccion->consecutivoFE, 
                "Clave: ", $transaccion->clave)
            );
            $InvoicePrinter->setTo(array(
                "Nombre de cliente", $transaccion->datosReceptor->nombre, 
                $transaccion->datosReceptor->numTelefono, 
                $transaccion->datosReceptor->correoElectronico, 
                date('M dS ,Y',time()))
            );             
            /* Totales */  
            foreach ($transaccion->detalleFactura as $key => $value){                
                $InvoicePrinter->addItem($key+1, $value->detalle, $value->cantidad, $value->montoImpuesto, $value->precioUnitario, 0, $value->montoTotalLinea);    
            }            
            $InvoicePrinter->addTotal("Descuento","0");
            $InvoicePrinter->addTotal("IV 13%",($transaccion->totalImpuesto));
            $InvoicePrinter->addTotal("Total+IV",($transaccion->totalComprobante),true);           
            /* Set badge */ 
            $InvoicePrinter->addBadge("Factura Aprobada");
            if(self::$reimpresion)
                $InvoicePrinter->addBadge("Reimpresion");
            /* Add title */
            $InvoicePrinter->addTitle("Detalle:");
            /* Add Paragraph */
            $defParagraph = "FECHA DE EMISIÓN: " . $transaccion->fechaEmision ."<br>ESTE DOCUMENTO se emite bajo las condiciones de la Resolución DGT-R-48-2016 del 7 de octubre del 2016.";
            $InvoicePrinter->addParagraph( $email_footer. '<br><br>' .$defParagraph);
            /* Set footer note */
            // <a href="http://storylabscr.com">Powerd by Story Labs CR</a>
            $InvoicePrinter->setFooternote("Factura Electronica por StoryLabsCR.com");
            /* Render */
            $dirPathPDF = "../../Invoices/pdf/";
            if (!file_exists($dirPathPDF))
                mkdir($dirPathPDF, 0755, true);
            array_push($archivosAdjunto, $path_pdf = $dirPathPDF . $transaccion->clave ."_". str_replace(' ', '', $transaccion->datosReceptor->identificacion) . ".pdf");          
            // $InvoicePrinter->Output($path_pdf, 'I'); //Con esta funcion imprime el archivo en otra ubicacion
            $InvoicePrinter->render($path_pdf,'F'); /* I => Display on browser, D => Force Download, F => local path save, S => return document path */            
            // set mail.
            $mail = new Send_Mail();            
            $mail->email_array_address_to = self::$email_array_address_to;
            $mail->email_subject = $email_subject;
            $mail->email_user = $email_user;
            $mail->email_password = $email_password;
            $mail->email_from_name = $nameCompany;
            $mail->email_SMTPSecure = $email_SMTPSecure;
            $mail->email_Host = $email_Host;
            $mail->email_SMTPAuth = $email_SMTPAuth;
            $mail->email_Port = $email_port;
            if($html!=null)
                $mail->email_body = $html;
            else {                
                $email_body=  "<h1 style='color:#3498db;'>".$email_body."</h1><br><br><br>" .                 
                    '<a href="https://facturaelectronica.storylabscr.com">por storylabsCR.com</a>';
                $mail->email_body = $email_body;
            }
            $mail->email_addAttachment = $archivosAdjunto;
            $mail->send();
        }     
        catch(Exception $e) {
            error_log("[ERROR]  (".$e->getCode()."): ". $e->getMessage());
            return false;
        }        
    }
    
    public static function test($email){
        try {
            $sendMail = new Send_Mail();
            $sendMail->email_array_address_to = self::$email_array_address_to;
            $sendMail->email_subject = 'PRUEBA: '.$email->email_subject;
            $sendMail->email_user = $email->email_user;
            $sendMail->email_password = $email->email_password;
            $sendMail->email_from_name = $email->email_name;
            $sendMail->email_SMTPSecure = $email->email_SMTPSecure;
            $sendMail->email_Host = $email->email_Host;
            $sendMail->email_SMTPAuth = $email->email_SMTPAuth;
            $sendMail->email_Port = $email->email_port;
            $sendMail->email_body = $email->email_body;
            $sendMail->email_addAttachment = []; 
            $sendMail->send();
        }
        catch(Exception $e) {
            header('HTTP/1.0 400 Error al generar la factura');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }  
    }






}
?>