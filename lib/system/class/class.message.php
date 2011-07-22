<?php

class message {
    static function blank($uri = false) {
        self::send("success", "", $uri);
    }
    
    static function error($lang, $uri = false, $arg = false) {
        self::send("error", $lang, $uri, $arg);
    }
    
    static function success($lang, $uri = false, $arg = false) {
        self::send("success", $lang, $uri, $arg);
    }
    
    static function warning($lang, $uri = false, $arg = false) {
        self::send("Warning", $lang, $uri, $arg);
    }
    
    private function send($type = "success", $lang = "", $uri = false, $arg = false) {
        
        $_SESSION['message']['type'] = $type;
         if(!empty($lang)):
            $_SESSION['message']['text']  = lang::find($type, $lang, $arg);
        endif;

        $_SESSION['debug'] = debug::$html;

        if(!$uri):
            $page = get();
        else:
            $page = $uri;
        endif;

        header("location: ".$page);
        exit();
    }
}
?>
