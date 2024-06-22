<?php

class keycrypt
{


    const mainlength = KEYLENGTH; // 1024 , 2048 , 4096



    public static function short_public_encrypt(string $input, string $filename):?string
    {
        $pub = file_get_contents($filename);
        if (!openssl_public_encrypt($input, $encrypted, $pub, OPENSSL_PKCS1_OAEP_PADDING)){
            throw new \Exception(openssl_error_string());
        }
        return base64_encode($encrypted);
    }

    public static function short_private_decrypt(string $input, string $filename):?string
    {
        $priv = file_get_contents($filename);
        $encrypted = base64_decode($input);
        if (!openssl_private_decrypt($encrypted, $decrypted, $priv, OPENSSL_PKCS1_OAEP_PADDING)){
            throw new \Exception(openssl_error_string());
        }
        return $decrypted;
    }

    public static function short_private_encrypt(string $input, string $filename):?string
    {
        $pub = file_get_contents($filename);


        if (!openssl_private_encrypt($input, $encrypted, $pub)) {
            throw new \Exception(openssl_error_string());
        }


        return base64_encode($encrypted);
    }

    public static function short_public_decrypt(string $input, string $filename):?string
    {
        $priv = file_get_contents($filename);
        $encrypted = base64_decode($input);
        if (!openssl_public_decrypt($encrypted, $decrypted, $priv)){
            throw new \Exception(openssl_error_string());
        }

        return $decrypted;
    }



    public static function encrypt(string $input, string $filename,bool $isPub = true):?string
    {
        $length = self::mainlength / 8;
        $length = $length - 42;

        $pub = file_get_contents($filename);
        $plain = str_split($input, $length); // 470 für 4096
        $return = '';
        foreach ($plain AS $part) {

            if($isPub) {

                if (!openssl_public_encrypt($part, $encrypted, $pub, OPENSSL_PKCS1_OAEP_PADDING)){
                    throw new \Exception(openssl_error_string());
                }
            } else {
                if (!openssl_private_encrypt($part, $encrypted, $pub)){
                    throw new \Exception(openssl_error_string());
                }
            }

            $return .= $encrypted;
        }

        return base64_encode($return);
    }

    public static function decrypt(string $input, string $filename, bool $isPub = false):?string
    {
        $length = self::mainlength / 8;

        $priv = file_get_contents($filename);
        $encrypted = base64_decode($input);

        $plain = str_split($encrypted, $length); // 512
        $return = '';
        foreach ($plain AS $part) {

            if($isPub){
                if (!openssl_public_decrypt($part, $decrypted, $priv)){
                    throw new \Exception(openssl_error_string());
                }
            } else {
                if (!openssl_private_decrypt($part, $decrypted, $priv, OPENSSL_PKCS1_OAEP_PADDING)){
                    throw new \Exception(openssl_error_string());
                }
            }

            $return .= $decrypted;
        }
        return $return;
    }


    public static function createkeys($privfile,$pubfile){
        $config = [
            "private_key_bits" => self::mainlength,    // 4096
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $private_key = openssl_pkey_new($config);
        if (!openssl_pkey_export($private_key , $privKey)){
            throw new \Exception(openssl_error_string());
        }

        file_put_contents($privfile, $privKey);
        if (!file_exists(dirname($pubfile))){
            if (!mkdir($concurrentDirectory = dirname($pubfile), 0777, true) && !is_dir($concurrentDirectory)) {

                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        $public_key_pem = openssl_pkey_get_details($private_key)['key'];
        file_put_contents($pubfile, $public_key_pem);

    }





}