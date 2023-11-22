<?php

class csrf {

    /*
     *
     *
     *
     */
    public static function get():string{
        if(isset($_SESSION['csrf_token'])){
            return $_SESSION['csrf_token'];
        } else {
            $token = self::create();
            $_SESSION['csrf_token'] = $token;
            return $token;
        }

    }

    /*
     *
     *
     *
     */
    public static function validate(string $token):void{
       if($token == self::get()){
           $_SESSION['csrf_token'] = NULL;
           self::get();
       }  else {
         throw new Exception('CSRF is not valid!');
       }

    }

    /*
     *
     *
     *
     */
    public static function create():string{
      $token = bin2hex(random_bytes(32));
      return $token;
    }


    public static function toform(string $token){
        return '<input type="hidden" name="csrf_token" value="'.$token.'>';
    }


    /* Usages:
     *
     * With AJAX/JQuery-POSTS:
     *
     *  In your html Header:
     *  <meta name="X-CSRF-Token" content="<?= csrf::get() ?>">
     *
     *  AND THIS:
     *  <script type="text/javascript">
     *   // This adds the csrf Token automaticly to all your ajax Posts
     *  $.ajaxPrefilter(function(options, originalOptions, jqXHR){
     *   if(options.type.toLowerCase() === "post"){
     *
     *   // Fetch new Token, if your Page do not Reload
     *    $.ajax({
     *    url: : "Path/To/CSRFData",  // For this you need an Endpoint where the csrf token is generated and showed as json
     *    type: 'GET',
     *    success: function(data){
     *     document.querySelector('meta[name="X-CSRF-Token"]').setAttribute("content",data.token);
     *     }
     *   });
     *
     *      // Empty data if not exists
     *     options.data = options.data || "";
     *
     *     // add connection between data if it is not empty
     *     options.data += options.data?"&":"";
     *
     *    // add the token from Header
     *    options.data += "csrf_token=" + encodeURIComponent($('meta[name="X-CSRF-Token"]').attr('content'));
     *   }
     *  });
     *  </script>
     *
     *  IN YOUR PHP POST Formulars put THIS:
     *
     *   <?= csrf::form(csrf::get()); ?>
     *
     *
     * TO CHECK DO THIS:
     *
     *  // When your Post arrive the PHP Target put this before execute the rest
     *   try{
     *   csrf::validate($_POST['csrf_token']);
     *   } catch (Exception $e) {
     *     // Handle the Exception
     *     $errormessage = htmlspecialchars($e->getMessage());
     *   }
     *
     *  Example for csrf json Endpoint:
     *
     *
     *
     *  header('Content-Type: application/json');
     *  if(login::isaktiv()){
     *  $data['status'] = 'ok';
     *  $data['message'] = '';
     *  $data['token'] = $_SESSION['csrf_token'];
     *  } else {
     *  $data['status'] = 'fail';
     *  $data['message'] = 'Keine aktive Session';
     * }
     *  echo json_encode($data);
     *
     *
     */

}
