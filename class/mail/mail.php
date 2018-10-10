<?php
    require 'Exception.php';
    require 'PHPMailer.php';
    require 'SMTP.php';
    setlocale(LC_ALL,"es_ES");
    mb_internal_encoding('UTF-8');

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    class Send_Mail {

        public $email_address_to="";
        public $email_subject = "";
        public $email_addAttachment = null;
        public $email_user = "";
        public $email_password = "";
        public $email_from_name = "";
        public $email_body = "";

        function send(){
            $phpmailer  = new PHPMailer(true);   // Passing `true` enables exceptions
            $phpmailer -> CharSet = "UTF-8";
            try {
                //Server settings
            $phpmailer->Username = $this->email_user;
            $phpmailer->Password = $this->email_password; 
            //-----------------------------------------------------------------------
            // $phpmailer->SMTPDebug = 1;
            $phpmailer->SMTPSecure = 'ssl';
            // $phpmailer->Host = "smtp.gmail.com"; // GMail
            $phpmailer->Host = "smtpout.asia.secureserver.net";
            // $phpmailer->Port = 465; // Gmail
            $phpmailer->Port = 80;
            $phpmailer->IsSMTP(); // use SMTP Gmail
            $phpmailer->SMTPSecure = "none"; // GD
            // $phpmailer->SMTPAuth = true;
            $phpmailer->SMTPAuth = true;
            $phpmailer->setFrom($phpmailer->Username,$this->email_from_name);
            $phpmailer->AddAddress($this->email_address_to); // recipients email
            $phpmailer->Subject = $this->email_subject;	

            //Attachments
            // $phpmailer->addAttachment('../Invoices/example2.pdf');         // Add attachments
            $phpmailer->addAttachment($this->email_addAttachment);

            $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
            $mes = $meses[date('n')-2];

            $phpmailer->Body .="<h1 style='color:#3498db;'>Listo, aquí tienes tu factura!</h1>";
            $phpmailer->Body .= "<p>Estimado(a) Cliente,</p>
                                <br>
                                Adjunto encontrara su Factura electrónica correspondiente a los articulos o servicios brindados a su nombre. <br>
                                <br>
                                <br>
                                <p>Si tuviese algún problema al abrir el archivo, puede contactar al teléfono 2231 4047 o escribir a andinotechcr@gmail.com</p>";
            
            $phpmailer->Body .= "<br><br> <img src='https://scontent.fsyq1-1.fna.fbcdn.net/v/t1.0-9/17951786_1521066834591084_4286966647105088183_n.png?_nc_cat=110&oh=88204692be049fd40a95032c32d86c4a&oe=5C1EB196' border='0' />
                                <p>Soporte al Cliente
                                <br><br>
                                San Jose<br>
                                SanJose<br>
                                Costa Rica<br>
                                CEL: +(506) 84903674<br>
                                TEL: +(506) 22323265<br>
                                web: facebook.com/andinostore</p>";
            
            $phpmailer->IsHTML(true);
            $phpmailer->Send();
            } catch (Exception $e) {
                echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
            }
        }
    }
?>