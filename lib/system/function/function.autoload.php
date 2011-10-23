<?php

function autoload($class) {
    if(file_exists(SYSTEM.'/class/class.'.$class.'.php')):
        require_once SYSTEM.'/class/class.'.$class.'.php';
    endif;
}

?>
