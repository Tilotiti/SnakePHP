<?php
/**
 * JS parseInt function, PHP transposed
 * @param String $string string to parse
 * @return Integer string converted to int
 */
function parseInt($string) {
    if(preg_match('/(\d+)/', $string, $array)):
        return $array[1];
    else:
        return 0;
    endif;
}