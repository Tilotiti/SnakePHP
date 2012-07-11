<?php
class debug {
    static $html;
    static $error;
    static $debug;
    
    public function __construct() {
        set_error_handler(array($this, 'error'));
        if(isset($_SESSION['debug'])):
            debug::$html = $_SESSION['debug'];
        endif;
    }
    
    static function error($errno, $errstr, $errfile = "unknow", $errline = "unknow") {

        switch ($errno):
            case E_USER_ERROR:

                echo "<h1>Fatal error</h1>";
                echo "<p>An unexpected error has occured.</p>";
                echo "<ul>";
                echo "    <li><b>File</b> : ".$errfile."</li>";
                echo "    <li><b>Line</b> : ".$errline."</li>";
                echo "    <li><b>Message</b> : ".$errstr."</li>";
                echo "</ul>";
                
                debug("------------- Fatal Error --------------", 'error');
                debug("File : ".$errfile, 'error');
                debug("Line : ".$errline, 'error');
                debug("Message : ".$errstr, 'error');
                debug("----------------------------------------", 'error');
                
                exit(1);
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
        
            case "sql":
                $type = 'sql';
                self::$error[] = array('type' => 'sql',
                                       'file' => $errfile,
                                       'line' => $errline,
                                       'str'  => $errstr);
            break;

            default:
                $type = "unknow";
                self::$error[] = array('type' => 'unknow',
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

    static public function display($array, $title = false) {
    	$debug = array();
        $debug['title'] = $title;
        $debug['array'] = $array;
        
        self::$debug[]  = $debug;
    }
    
    public static function clear() {
        // Debug
        if(count(self::$debug)>0):
            if(DEV):
                echo '<div id="debug">';
                foreach(self::$debug as $debug):
                    if($debug['title']):
                        echo '<h4>'.$debug['title'].'</h4>';
                    else:
                        echo '<h4>Var_dump</h4>';
                    endif;
                    echo '<pre>';
                    var_dump($debug['array']);
                    echo '</pre>';
                endforeach;
                echo '</div>';
            else:
                foreach(self::$debug as $debug):
                    echo "<!-- \n";
                    if($debug['title']):
                        echo $debug['title']."\n";
                    endif;
                    var_dump($debug['array']);
                    echo "\n -->"."\n";
                endforeach;
            endif;
        endif;
        unset($_SESSION['debug']);

        // Error
        if(count(self::$error) > 0):
            if(DEV):
                echo '<div class="debug">';
                foreach(self::$error as $error):
                    echo '<h4>'.lang::error('error:'.$error['type']).'</h4>';
                    echo "<ul>";
                    echo "    <li><b>File</b> : ".$error['file']."</li>";
                    echo "    <li><b>Line</b> : ".$error['line']."</li>";
                    echo "    <li><b>Message</b> : ".$error['str']."</li>";
                    echo "</ul>";
                endforeach;
                echo '</div>';
            else:
                foreach(self::$error as $error):
                    echo "<!-- \n".lang::error('error:'.$error['type'])."\n";
                    echo "File : ".$error['file']."\n";
                    echo "Line : ".$error['line']."\n";
                    echo "Message : ".$error['str']."\n";
                    echo "\n -->"."\n";
                endforeach;
            endif;
        endif;
    }

}
?>
