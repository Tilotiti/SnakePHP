<?php
/**
 * Message manager - redirect and send messages to user after actions
 * Use class lang for i18n support
 * @see lang
 * @author Tilotiti
 */
class message {
	/**
	 * Redirection only
	 * @param String $uri[optional] URI to redirect to - default: current URI
	 * @return void
	 */
    static function blank($uri = false) {
        self::send("success", "", $uri);
    }
    /**
	 * Error message
	 * @param String $lang lang code
	 * @param String $uri[optional] URI to redirect to - default: current URI
	 * @param Array $arg[optional] arguments for translated message
	 * @return void
	 */
    static function error($lang, $uri = false, $arg = false) {
        self::send("error", $lang, $uri, $arg);
    }
    /**
	 * Success message
	 * @param String $lang lang code
	 * @param String $uri[optional] URI to redirect to - default: current URI
	 * @param Array $arg[optional] arguments for translated message
	 * @return void
	 */
    static function success($lang, $uri = false, $arg = false) {
        self::send("success", $lang, $uri, $arg);
    }
    /**
	 * Warning message
	 * @param String $lang lang code
	 * @param String $uri[optional] URI to redirect to - default: current URI
	 * @param Array $arg[optional] arguments for translated message
	 * @return void
	 */
    static function warning($lang, $uri = false, $arg = false) {
    	// XXX should be 'warning' lowercase, shouldn't it ?
        self::send("Warning", $lang, $uri, $arg);
    }
    /**
	 * Generic method for sending message
	 * @param String $type[optional] message type - default: success
	 * @param String $lang[optional] lang code - default: none
	 * @param String $uri[optional] URI to redirect to - default: current URI
	 * @param Array $arg[optional] arguments for translated message
	 * @return void
	 */
    static function send($type = "success", $lang = "", $uri = false, $arg = false) {
        
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