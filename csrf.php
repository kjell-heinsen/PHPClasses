<?php

class csrf {

    public static function get():string{
        if(isset($_SESSION['csrf_token'])){
            return $_SESSION['csrf_token'];
        } else {
            $token = self::create();
            $_SESSION['csrf_token'] = $token;
            return $token;
        }

    }


    public static function validate(string $token):void{
       if($token == self::get()){
           $_SESSION['csrf_token'] = NULL;
           self::get();
       }  else {
         throw new Exception('CSRF is not valid!');
       }

    }

    public static function create():string{
      $token = bin2hex(random_bytes(32));
      return $token;
    }

}
