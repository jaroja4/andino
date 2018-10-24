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
        public $email_SMTPSecure = "";
        public $email_Host = "";
        public $email_SMTPAuth = "";
        public $email_Port = "";

        function send(){
            $phpmailer  = new PHPMailer(true);   // Passing `true` enables exceptions
            $phpmailer->CharSet = "UTF-8";
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
                $phpmailer->AddAddress($this->email_address_to); // recipients email
                $phpmailer->Subject = $this->email_subject;	
                
                if ( strlen($this->email_addAttachment) > 1){
                    $phpmailer->addAttachment();
                }               

                $phpmailer->Body = $email_body;

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