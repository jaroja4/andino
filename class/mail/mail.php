<?php
    // require 'Exception.php';
    // require 'PHPMailer.php';
    // require 'SMTP.php';
    // require 'OAuth.php';
    setlocale(LC_ALL,"es_ES");
    mb_internal_encoding('UTF-8');

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\OAuth;
    use PHPMailer\PHPMailer\Exception;
    use League\OAuth2\Client\Provider\Google;

    require 'vendor/autoload.php';

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
            //$phpmailer->MailerDebug = false;
            
            try {
                // consent account.
                $phpmailer->oauthUserEmail = "carlos.echc11@gmail.com";
                $phpmailer->oauthClientId = "403994346860-otmp39fqt5sb4s6ks969fn1d7qifcvfd.apps.googleusercontent.com";
                $phpmailer->oauthClientSecret = "wgVcFFueIkj2Wo9tuW0WM07n";
                $phpmailer->oauthRefreshToken = "1/5FtM6mCpMNnW2feYcjdvgb-erRZuTj0JzWTSaabNrVQ";
                // user mail settings.
                $phpmailer->Username = $this->email_user;
                $phpmailer->Password = $this->email_password;                 
                $provider = new Google(
                    [
                        'clientId' => $phpmailer->oauthClientId,
                        'clientSecret' => $phpmailer->oauthClientSecret
                    ]
                );
                //Server settings
                $phpmailer->Host = $this->email_Host;
                $phpmailer->SMTPSecure = $this->email_SMTPSecure;
                $phpmailer->Port = (int)$this->email_Port;

                $phpmailer->IsSMTP(); // use SMTP Gmail
                $phpmailer->SMTPDebug = 2;
                $phpmailer->SMTPAuth = true;
                $phpmailer->AuthType = 'XOAUTH2';                
    
                $phpmailer->setFrom($phpmailer->Username, $this->email_from_name);

                foreach ($this->email_array_address_to as $address_to) {
                    $phpmailer->AddAddress($address_to); // recipients email

                    $phpmailer->setOAuth(
                        new OAuth(
                            [
                                'provider' => $provider,
                                'clientId' => $phpmailer->Username,
                                'clientSecret' => $phpmailer->Password,
                                'refreshToken' =>  $phpmailer->oauthRefreshToken,
                                'userName' => $address_to,
                            ]
                        )
                    );
                }

                $phpmailer->Subject = $this->email_subject;	

                foreach ($this->email_addAttachment as $Attachment) {
                    $phpmailer->addAttachment($Attachment);
                }           

                $phpmailer->Body = $this->email_body;

                $phpmailer->IsHTML(true);
                if(!$phpmailer->Send()) {
                    error_log("****** Message was not sent. ******");
                } else {
                    error_log("Message has been sent.");
                }
            } catch (Exception $e) {
                error_log("[ERROR]  Mailer Error: (".$e->getCode()."): ". $e->getMessage());
            }
        }

        function sendOauth(){
            $phpmailer  = new PHPMailer(true);   // Passing `true` enables exceptions
            $phpmailer->CharSet = "UTF-8";
            //$phpmailer->MailerDebug = true;
            $phpmailer->oauthUserEmail = "carlos.echc11@gmail.com";
            $phpmailer->oauthClientId = "403994346860-otmp39fqt5sb4s6ks969fn1d7qifcvfd.apps.googleusercontent.com";
            $phpmailer->oauthClientSecret = "wgVcFFueIkj2Wo9tuW0WM07n";
            $phpmailer->oauthRefreshToken = "1/5FtM6mCpMNnW2feYcjdvgb-erRZuTj0JzWTSaabNrVQ";
            try {
                //Server settings
                $phpmailer->Username = $this->email_user;
                $phpmailer->Password = $this->email_password; 
    
    
                $phpmailer->Host = $this->email_Host;
                $phpmailer->SMTPSecure = $this->email_SMTPSecure;
                $phpmailer->Port = (int)$this->email_Port;
                //$phpmailer->SMTPAuth = $this->email_SMTPAuth;
    
            
                $phpmailer->IsSMTP(); // use SMTP Gmail
                $phpmailer->SMTPDebug = 2;
                $phpmailer->SMTPAuth = true;
                $phpmailer->AuthType = 'XOAUTH2';
                $provider = new Google(
                    [
                        'clientId' => $phpmailer->oauthClientId,
                        'clientSecret' => $phpmailer->oauthClientSecret,
                    ]
                );
                

                $phpmailer->setFrom($phpmailer->Username,$this->email_from_name);

                foreach ($this->email_array_address_to as $address_to) {
                    $phpmailer->AddAddress($address_to); // recipients email

                    $phpmailer->setOAuth(
                        new OAuth(
                            [
                                'provider' => $provider,
                                'clientId' => $phpmailer->oauthClientId,
                                'clientSecret' => $phpmailer->oauthClientSecret,
                                'refreshToken' =>  $phpmailer->oauthRefreshToken,
                                'userName' => $address_to,
                            ]
                        )
                    );
                }

                $phpmailer->Subject = $this->email_subject;	

                foreach ($this->email_addAttachment as $Attachment) {
                    $phpmailer->addAttachment($Attachment);
                }           

                $phpmailer->Body = $this->email_body;

                $phpmailer->IsHTML(true);
                if(!$phpmailer->Send()) {
                    error_log("****** Message was not sent. ******");
                } else {
                    error_log("Message has been sent.");
                }
            } catch (Exception $e) {
                error_log("[ERROR]  Mailer Error: (".$e->getCode()."): ". $e->getMessage());
            }
        }
    }


?>