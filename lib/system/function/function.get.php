<?php
/**
 * SnakePHP URL parser.
 * This is a multifunction with 3 uses, to be used for URL slash-separated parameters.
 * 1) no parameter : returns URI
 * 2) 1 parameter (integer) : returns one specific URI parameter
 * 3) 2 parameters : replace specified URI parameter with $replace param.
 * 
 * @param Integer[optional] $get URI parameter index to get - default: none (all URI)
 *  @param String[optional] $replace String which will replace specified URI parameter - default: none
 */
function get($get = '', $replace = false) {
    $uri = $_SERVER['REQUEST_URI'];
    if(!$replace):
        if(!empty($uri)):
            if(empty($get)):
                return (string) $uri;
            else:

                if(preg_match('#^\/#', $uri)):
                    $uri = substr($uri, 1);
                endif;

                if(preg_match('#\/$#', $uri)):
                    $uri = substr($uri, 0, -1);
                endif;

                if(empty($uri)):
                    return 'index';
                endif;

                $list = array();
                $var = explode('/', $uri);
                $i = 1;
                foreach($var as $key):
                    $list[$i] = $key;
                    $i++;
                endforeach;

                if(isset($list[$get])):
                   return $list[$get];
                else:
                   return 'index';
                endif;
            endif;
        else:
            return 'index';
        endif;
    else:
        $getArray = explode('/', substr($uri, 1, -1));
        for($i = 0; $i < $get; $i++):
            if(!isset($getArray[$i])):
                $getArray[$i] = 'index';
            endif;
        endfor;
        $getArray[$get-1] = $replace;
        return '/'.implode('/', $getArray).'/';
    endif;
}