<?php

function parseInt($string) {
    if(preg_match('/(\d+)/', $string, $array)):
        return $array[1];
    else:
        return 0;
    endif;
}

?>