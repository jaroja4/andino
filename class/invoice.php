<?php
    //Classes
    include("WebToPDF/InvoicePrinter.php");
    require_once("UUID.php");
    require "mail/mail.php";
    // Instance        
    $invoice= new Invoice();

class Invoice{
    
    public static function Create($receptor, $detalleFacura){
        try {
            
            $nameCompany ="Andino Store";
            $address ="San Jose, San Jose, Pavas";
            $contact ="Telefono: 2231 4047";
            $cedula ="Ced. Juridica: xxxxxxx";
            $email ="andinotechcr@gmail.com";
            
            $InvoicePrinter = new InvoicePrinter("A4", "$", "es");
    
            /* Header Settings */
            $InvoicePrinter->setTimeZone('America/Costa_Rica');
            $InvoicePrinter->setLogo("../images/andino_logo.png");
            $InvoicePrinter->setColor("#007fff");//Numero de contrato
            $InvoicePrinter->setType($nameCompany);
            $InvoicePrinter->setReference($detalleFacura[0]->idFactura); 
            $InvoicePrinter->setDate(date('M dS ,Y',time()));
            $InvoicePrinter->setTime(date('h:i:s A',time()));
            $InvoicePrinter->setDue(date('M dS ,Y',strtotime('+1 months')));
            $InvoicePrinter->setFrom(array($nameCompany,$address, $contact, $cedula, $email));
    
            $InvoicePrinter->setTo(array("Nombre de cliente", $receptor['identificacion'], $receptor['numTelefono'],$receptor['correoElectronico'], date('M dS ,Y',time()))); 
                
            $totalComprobante = 0;
            $total_iv = 0;
    
            foreach ($detalleFacura as $key => $value){                
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
            $InvoicePrinter->addBadge("Factura Notificada");
            /* Add title */
            $InvoicePrinter->addTitle("Detalle:");
            /* Add Paragraph */
            $InvoicePrinter->addParagraph("FECHA DE EMISIÓN: " . date("d/m/Y") . ", HORA: 07:20 - AUTORIZADO MEDIANTE EL OFICIO DE LA DGT NO. 11-97 DEL 12 DE AGOSTO DE 1997.");
            /* Set footer note */
            $InvoicePrinter->setFooternote("StoryLabsCR");
            /* Render */
            $path_fecha = "../Invoices/" . date("dmYHi") ."_". str_replace(' ', '', $receptor['identificacion']) . ".pdf";
        
            // $InvoicePrinter->Output($path_fecha, 'I'); //Con esta funcion imprime el archivo en otra ubicacion
        
            $InvoicePrinter->render($path_fecha,'F'); /* I => Display on browser, D => Force Download, F => local path save, S => return document path */
            
            
            $mail = new Send_Mail();
            $mail->address_to = $receptor['correoElectronico'];
            $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
    
            $mail->the_subject = "Factura Monitoreo: - " . $meses[date('n')-1] . " ".date('Y');   
            $mail->addAttachment = $path_fecha;
        
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