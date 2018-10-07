<?php
    class encdes{
        static function cifrar($txt){
            $enc_method = 'AES-128-CTR';
            $enc_key = openssl_digest('f8e051e4-5d0b-415b-bd33-cc96617a5934', 'SHA256', TRUE);
            $enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($enc_method));
            $crypted_token = openssl_encrypt($txt, $enc_method, $enc_key, 0, $enc_iv) . "::" . bin2hex($enc_iv);
            unset($txt, $enc_method, $enc_key, $enc_iv);
            return $crypted_token;
        }

        static function decifrar($crypted_token){
            if(preg_match("/^(.*)::(.*)$/", $crypted_token, $regs)) {
                list(, $crypted_token, $enc_iv) = $regs;
                $enc_method = 'AES-128-CTR';
                $enc_key = openssl_digest('f8e051e4-5d0b-415b-bd33-cc96617a5934', 'SHA256', TRUE);
                $decrypted_token = openssl_decrypt($crypted_token, $enc_method, $enc_key, 0, hex2bin($enc_iv));
                unset($crypted_token, $enc_method, $enc_key, $enc_iv, $regs);
                return $decrypted_token;
            }            
        }
    }
?>