<?php
    //Classes
    include("WebToPDF/InvoicePrinter.php");
    require_once("UUID.php");
    require "mail/mail.php";
    // Instance        
    $invoice= new Invoice();

class Invoice{
    
    public static function Create($transaccion){
        try {
            $sql='SELECT s.email_name, s.email_user, s.email_password, s.email_ssl, s.email_smtpout, s.email_port, e.numTelefono, e.identificacion
                    FROM smtpXEntidad s
                    INNER JOIN entidad e ON s.idEntidad = e.id
                    WHERE idEntidad=:idEntidad
                    AND activa = "1";';

            $param= array(':idEntidad'=>$transaccion->idEmisor);
            $data= DATA::Ejecutar($sql,$param);     
            if ($data){
                $nameCompany = $data[0]["email_name"];
                $address ="San Jose, San Jose, Pavas";
                $contact = $data[0]["numTelefono"];
                $cedula =$data[0]["identificacion"];
                $email =$data[0]["email_user"];
            }




            $tipoComprobanteElectronicoTitulo = "TIPO COMPROBANTE ELECTRONICO: ";
            $tipoComprobanteElectronico = " FACTURA ELECTRÓNICA";
            $consecutivoFE = $transaccion->consecutivo;
            $claveFETitulo = "ClaveFE";
            $claveFE = $transaccion->clave;
            
            $InvoicePrinter = new InvoicePrinter("A4", "¢", "es");
    
            /* Header Settings */
            $InvoicePrinter->setTimeZone('America/Costa_Rica');
            $InvoicePrinter->setLogo("../images/andino_logo.png");
            $InvoicePrinter->setColor("#007fff");//Numero de contrato
            $InvoicePrinter->setType($nameCompany);
            $InvoicePrinter->setAddress($address); 
            $InvoicePrinter->setPhone($contact);
            $InvoicePrinter->setLegal_Document($cedula);
            $InvoicePrinter->setEmail($email);
            $InvoicePrinter->setFrom(array($tipoComprobanteElectronicoTitulo,$tipoComprobanteElectronico,"Consecutivo FE: ".$consecutivoFE, $claveFETitulo,$claveFE));
    
            $InvoicePrinter->setTo(array("Nombre de cliente", $transaccion->datosReceptor[0]["nombre"], $transaccion->datosReceptor[0]["numTelefono"],$transaccion->datosReceptor[0]["correoElectronico"], date('M dS ,Y',time()))); 
                
            $totalComprobante = 0;
            $total_iv = 0;
    
            foreach ($transaccion->detalleFactura as $key => $value){                
                $InvoicePrinter->addItem($key+1, $value->detalle, $value->cantidad, $value->montoImpuesto, $value->precioUnitario, 0, $value->montoTotalLinea);                
                // $InvoicePrinter->addItem($this->description, $this->frequencyPay, $this->cant, $item_iv, $this->priceMonitoring,$discount,$item_total);
                $totalComprobante = $value->cantidad * ($value->montoImpuesto + $value->precioUnitario);
                $total_iv = $total_iv + $value->montoImpuesto;        
            }                   
            
            /* Add totals */
            $InvoicePrinter->addTotal("Descuento","0");
            $InvoicePrinter->addTotal("IV 13%",($total_iv));
            $InvoicePrinter->addTotal("Total+IV",($totalComprobante),true);
           
            /* Set badge */ 
            // $InvoicePrinter->addBadge("Payment Paid");
            $InvoicePrinter->addBadge("Factura Aprobada");
            /* Add title */
            $InvoicePrinter->addTitle("Detalle:");
            /* Add Paragraph */
            // $InvoicePrinter->addParagraph("FECHA DE EMISIÓN: " . date("d/m/Y") . ", HORA: 07:20 - AUTORIZADO MEDIANTE EL OFICIO DE LA DGT NO. 11-97 DEL 12 DE AGOSTO DE 1997.");
            
            $InvoicePrinter->addParagraph("ESTE DOCUMENTO se emite bajo las condiciones de la Resolución DGT-R-48-2016 del 7 de octubre del 2016, y queda sujeta a las siguientes condiciones:
                                        Toda mercadería viaja por cuenta del comprador. Después de un día hábil de recibida la factura NO SE ACEPTAN RECLAMOS sobre el detalle de la misma. Por atraso en el pago, se reconocerán
                                        intereses moratorios del 3.5% mensual. La Fecha y firma se presumen ciertas en original y en posesión del emisor hasta su pago y retiro por parte del deudor.
                                        Este documento cumple con todas las formalidades normativas, de conformidad con las disposiciones de la resolución DGT-R-048-2016 del 7 de octubre de 2016, por lo que su formato se presume
                                        correcto. Este documento constituye título ejecutivo conforme lo determina el artículo 460 del Código del Comercio, cuando esté firmado por el comprador, por lo que puede ser ejecutado sin necesidad de
                                        un proceso judicial de cobro, y sin necesidad de más de un requerimiento de pago indicado por el acreedor; en caso de tener que acudir a este último el deudor se compromete al pago de las costas
                                        personal y procesales de resultar vencido. Cualquier abono o cancelación de una factura de crédito, debe de estar amparada a un recibo debidamente membretado.");
            /* Set footer note */
            $InvoicePrinter->setFooternote("StoryLabsCR");
            /* Render */
            $path_fecha = "../Invoices/" . date("dmYHi") ."_". str_replace(' ', '', $transaccion->datosReceptor[0]["identificacion"]) . ".pdf";
        
            // $InvoicePrinter->Output($path_fecha, 'I'); //Con esta funcion imprime el archivo en otra ubicacion
        
            $InvoicePrinter->render($path_fecha,'F'); /* I => Display on browser, D => Force Download, F => local path save, S => return document path */
            
            
            $mail = new Send_Mail();
            // public $email_address_to="";
            // public $email_subject = "";
            // public $email_addAttachment = null;
            // public $email_user = "";
            // public $email_password = "";
            // public $email_from_name = "";
            // public $email_body = "";
            $mail->email_address_to = $transaccion->datosReceptor[0]["correoElectronico"];
            $mail->email_subject = "Factura Lista" . $nameCompany;
            $mail->email_user = $data[0]["email_user"];
            $mail->email_password = $data[0]["email_password"];
            $mail->email_from_name = $nameCompany;

            $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
       
            $mail->email_addAttachment = $path_fecha;
        
            $mail->send();
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