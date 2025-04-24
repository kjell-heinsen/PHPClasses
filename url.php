<?php

class url
{

    public static function redirect($url, $status)
    {
        header('Location: ' . DIR . $url, true, $status);
        exit;
    }

    public static function Refdirect($url, $status)
    {
        header('Location: ' . $url, true, $status);
        exit;
    }

    public static function halt($status = 404, $message = 'Da ist etwas schief gelaufen.')
    {
        if (ob_get_level() !== 0) {
            ob_clean();
        }
        http_response_code($status);
        $data['status'] = $status;
        $data['message'] = $message;

        if (!file_exists(DOCROOT . "/app/error/$status.php")) {
            $status = 'default';
        }
        require DOCROOT . "/app/views/error/$status.php";

        exit;
    }

    public static function JScript($filename = false, $path = 'assets/')
    {
        $debug = '';
        if(DEBUG){
            $debug = '?v='.md5(time().random_bytes(8));
        }
        if ($filename) {
            $path .= "$filename.js".$debug;
        }
        return DIR . $path;
    }



    public static function STYLES($filename = false, $path = 'assets/')
    {
        $debug = '';
        if(DEBUG){
            $debug = '?v='.md5(time().random_bytes(8));
        }
        if ($filename) {
            $path .= "$filename.css".$debug;
        }
        return DIR . $path;
    }


    public static function IMAGES($filename = false, $path = 'assets/', $ext = '.jpg')
    {
        if ($filename) {
            $path .= "$filename$ext";
        }
        return DIR . $path;
    }


    public static function LINK($path)
    {
        return DIR . $path;

    }



}
