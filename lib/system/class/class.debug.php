<?php
class debug {
    static $html;
    static $array;
    public function __construct() {
        if(isset($_SESSION['debug'])):
            debug::$html = $_SESSION['debug'];
        endif;
    }

    static function html($title, $message, $file = __FILE__, $line = __LINE__) {
        self::$html .= "<div class='debug'><div class='debug_info'>Fichier : ".$file."<br />Ligne : ".$line."</div><h1>".$title."</h1>".$message."</div>";
        debug("----------------- Debug -----------------");
        debug("Fichier : ".$file);
        debug("Ligne : ".$line);
        debug("Title : ".$title);
        debug("Message : ".$message);
        debug("-----------------------------------------");
    }

    static public function display($array, $title = false) {
        $debug['title'] = $title;
        $debug['array'] = $array;
        $_SESSION['debug'][] = $debug;
        self::$array[] = $debug;
    }

    public function clear() {
        if(count(self::$array)>0):
            echo '<article class="module width_full">';
            echo '<header><h3>Debug</h3></header>';
            echo '<div class="module_content">';
            foreach(self::$array as $error):
                if($error['title']):
                    echo '<h4>'.$error['title'].'</h4>';
                else:
                    echo '<h4>Var_dump</h4>';
                endif;
                echo '<pre>';
                var_dump($error['array']);
                echo '</pre>';
            endforeach;
            echo '</div>';
            echo '</article>';
            echo '<div class="spacer"></div>';
        endif;
        unset($_SESSION['debug']);
    }

}
?>
