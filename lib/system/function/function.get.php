<?php
/**
 * SnakePHP URL parser.
 * This is a multifunction with 3 uses, to be used for URL slash-separated parameters.
 * 1) no parameter : returns URI
 * 2) 1 parameter (integer) : returns one specific URI parameter
 * 3) 2 parameters : replace specified URI parameter with $replace param.
 * 
 * @param Integer $get[optional] URI parameter index to get - default: none (all URI)
 *  @param String $replace[optional] String which will replace specified URI parameter - default: none
 */
function get($get = '', $replace = false) {
    $uri = $_SERVER['REQUEST_URI'];
        	
    if(strpos($uri, '?')):
    	$explode = explode('?', $uri);
    	$uri = $explode[0];
    endif;
    
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
		if ($getArray[0]==='') {
			$getArray = array_slice($getArray, 1);
		}
        for($i = 0; $i < $get; $i++):
            if(!isset($getArray[$i])):
                $getArray[$i] = 'index';
            endif;
        endfor;
        $getArray[$get-1] = $replace;
		$return = preg_replace('#\/\/#','/','/'.implode('/', $getArray).'/');
		$return = preg_replace('#\/index\/$#', '/', $return);
        return $return;
    endif;
}
