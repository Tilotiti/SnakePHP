<?php
/**
 * Debug manager
 * Catch errors and log into debug bar and log files
 * @author Tilotiti
 */
class debug {
	
    static $html;
    static $error;
    static $debug;
    static $sql;
    static $dump;
    static $start = 0;
    static $timer = array();
	static $timeQueries = false;
    
	/**
	 * Initialise the debug object
	 * If you don't include queries in timer, time of each query will even so appear in the SQL panel
	 * @param Boolean $queries set to true to include queries in timer - default: false
	 */
    public function __construct($timeQueries=false) {
    	if(PAGE_LOADER):
		    error_reporting(0);
		    set_error_handler(array($this, 'error'));
        endif;
		self::$timeQueries = $timeQueries;
    }
    
	/**
	 * Adds an error log to debug bar and log file
	 * @static
	 * @param Integer $errno error level (E_USER_xxx)
	 * @param String $errstr Error message
	 * @param String $errfile[optional] name of the file where error occurred - default: unknown
	 * @param Integer $errline[optional] line number where error thrown - default: unknown
	 * @return Boolean should always return true
	 */
    static function error($errno, $errstr, $errfile = "unknown", $errline = "unknown") {
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

	/**
	 * Dumps data in debug bar
	 * @static
	 * @param mixed $array any possible data
	 * @param String $title[optional] title of the dump - default: none
	 * @return void
	 */
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
    
    /**
	 * Dumps SQL queries with some information
	 * @param String $req raw SQL query
	 * @param Integer $count number of rows returned or updated
	 * @param Boolean $cached was this request previously in cache
	 * @param Boolean|String $cache true if "simple-cached", name of the category if "category-cached", false otherwise
	 * @param Float $time time needed for query execution (if cached, time to read the cache)
	 * @return void
	 */
    public static function sql($req, $count, $cached, $cache, $time) {
		$sql           = array();
		$sql['number'] = query::$queryNumber;
        $sql['req']    = $req;
        $sql['count']  = $count;
    	$sql['cache']  = $cache;
        $sql['cached'] = $cached;
		$sql['time']   = $time;
        
        self::$sql[] = $sql;
    }
    
	/**
	 * Add a timer entry. Query timer entries are not processed if self::$timeQueries is false
	 * @param String $title title of the entry
	 * @param Boolean $query[optional] process (or not) $query entries - default: false
	 * @return void
	 */
    public static function timer($title, $query=false) {
    	if ($query && !self::$timeQueries):
    		return;
		endif;
		
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