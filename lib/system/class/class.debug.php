<?php
class debug {
    static $html;
    static $error;
    static $debug;
    static $sql;
    static $dump;
    static $start = 0;
    static $timer = array();
    
    public function __construct() {
    	if(PAGE_LOADER):
		    error_reporting(0);
		    set_error_handler(array($this, 'error'));
        endif;
    }
    
    static function error($errno, $errstr, $errfile = "unknow", $errline = "unknow") {
        switch ($errno):
            case E_USER_ERROR:
            	$error  = '';
                $error .= '<p>A <b>Fatal Error</b> has occured.</p>';
				$error .= '<dl>';
				$error .= '<dt>'.$errstr.'</dt>';
				$error .= '<dd>'.$errfile.' ('.$errline.')</dd>';
				$error .= '</dl>';
                
                fatalError($error);
            break;

            case E_USER_WARNING:
                $type = 'warning';
                self::$error[] = array('type' => 'warning',
                                       'file' => $errfile,
                                       'line' => $errline,
                                       'str'  => $errstr);
            break;

            case E_USER_NOTICE:
                $type = 'notice';
                self::$error[] = array('type' => 'notice',
                                       'file' => $errfile,
                                       'line' => $errline,
                                       'str'  => $errstr);
            break;
 
            default:
            	$type = $errno;
                self::$error[] = array('type' => $type,
                                       'file' => $errfile,
                                       'line' => $errline,
                                       'str'  => $errstr);
            break;
        endswitch;
        
        debug("------------- Error : ".$type."--------------", 'error');
        debug("File : ".$errfile, 'error');
        debug("Line : ".$errline, 'error');
        debug("Message : ".$errstr, 'error');
        debug("----------------------------------------", 'error');

        return true;
    }

    static public function dump($array, $title = false) {
    	if(PAGE_LOADER):
		    $dump = array();
		    $dump['title'] = $title;
		    $dump['array'] = $array;
		        
		    self::$dump[]  = $dump;
        else:
            if(($_SERVER["REMOTE_ADDR"] == IPADMIN || in_array($_SERVER["REMOTE_ADDR"], explode('|', IPADMIN))) && DEV):
            	if(!$title):
		    	    $title = lang::text('debug:dump:default');
		    	endif;
		    			
		        echo "<hr />";
		        echo "<b><big>".$title."</big></b>";
		        echo "<pre>";
		        var_dump($array);
		        echo "</pre>";
		        echo "<hr />";
            endif;
        endif;
    }
    
    public static function sql($req, $count, $cached) {
		$sql           = array();
        $sql['req']    = $req;
        $sql['count']  = $count;
        $sql['cached'] = $cached;
        
        self::$sql[] = $sql;
    }
    
    public static function timer($title) {
		$time = explode(" ", microtime());
        $time = ($time[1] + $time[0]);
        
        if(self::$start == 0):
             self::$start = $time;
        endif;
        
        if(PAGE_LOADER):
            self::$timer[] = array(
        		'title' => $title,
        		'time'  => $time - self::$start
            );
        endif;
    }

}