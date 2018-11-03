<?php
    // require 'Exception.php';
    require 'PHPMailer.php';
    require 'SMTP.php';
    setlocale(LC_ALL,"es_ES");
    mb_internal_encoding('UTF-8');

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    class Send_Mail {

        public $email_array_address_to = null;
        public $email_subject = "";
        public $email_addAttachment = null;
        public $email_user = "";
        public $email_password = "";
        public $email_from_name = "";
        public $email_body = "";
        public $email_SMTPSecure = "";
        public $email_Host = "";
        public $email_SMTPAuth = "";
        public $email_Port = "";

        function send(){
            $phpmailer  = new PHPMailer(true);   // Passing `true` enables exceptions
            $phpmailer->CharSet = "UTF-8";
            $phpmailer->MailerDebug = false;
            try {
                //Server settings
                $phpmailer->Username = $this->email_user;
                $phpmailer->Password = $this->email_password; 
    
    
                $phpmailer->Host = $this->email_Host;
                $phpmailer->SMTPSecure = $this->email_SMTPSecure;
                $phpmailer->Port = (int)$this->email_Port;
                $phpmailer->SMTPAuth = $this->email_SMTPAuth;
    
            
                $phpmailer->IsSMTP(); // use SMTP Gmail
                $phpmailer->SMTPAuth = true;
                $phpmailer->setFrom($phpmailer->Username,$this->email_from_name);

                foreach ($this->email_array_address_to as $address_to) {
                    $phpmailer->AddAddress($address_to); // recipients email    
                }                   
                $phpmailer->Subject = $this->email_subject;	

                foreach ($this->email_addAttachment as $Attachment) {
                    $phpmailer->addAttachment($Attachment);
                }           

                $phpmailer->Body = $this->email_body;

                $phpmailer->IsHTML(true);
                if(!$phpmailer->Send()) {
                    echo 'Message was not sent.';
                    echo 'Mailer error: ' . $mail->ErrorInfo;
                } else {
                    echo 'Message has been sent.';
                }
            } catch (Exception $e) {
                echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
            }
        }
    }
?>