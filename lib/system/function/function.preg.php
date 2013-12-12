<?php

/**
 * Check a string according to predefined pattern
 * 2 ways of use :
 * 1) pattern is one of these strings: mail,phone,password,date,login,username,zipcode,doc,name,url
 * 	will check string as mail, phone, etc.
 * 2) pattern is a number : check if string is longer than pattern
 * 
 * @param Integer|String $pattern name of the pattern
 * @param String $string string to check
 * @return Array|Boolean true or equivalent if success, false otherwise
 */
function preg($pattern, $string = '') {
    switch($pattern):
        case 'mail':
            $preg = "#^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])(([a-z0-9-])*([a-z0-9]))+(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$#i";
        break;
        case 'phone':
            $preg = "#(^\+[0-9]{2}|^\+[0-9]{2}\(0\)|^\(\+[0-9]{2}\)\(0\)|^00[0-9]{2}|^0)([0-9]{9}$|[0-9\-\s]{10}$)#i";
        break;
        case 'password':
            $preg = "#(^[0-9a-z]{6,12}$)|(^[0-9a-fA-F]{32}$)#i";
        break;
        case 'date':
            $preg = "#^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$#i";
        break;
        case 'login':
        case 'username':
            $preg = "#^[0-9a-z._@]{3,50}$#i";
        break;
        case 'zipcode':
            $preg = "#^[0-9]{5}$#i";
        break;
        case 'doc':
            $preg = "#^(.+)\.doc$#i";
        break;
        case 'name':
            $preg = "#(.+){3,20}$#i";
        break;
        case 'url':
            $preg = "#^(((ht|f)tp(s?))\://)?(www.|[a-zA-Z].)[a-zA-Z0-9\-\.]+\.([a-z]{2,5})(\:[0-9]+)*(/($|[a-zA-Z0-9\.\,\;\?\'\\\+&amp;%\$#\=~_\-]+))*$#i";
        break;
        default:
            if(is_int($pattern)):
                if(is_int($string)):
                    if(empty($string) || $pattern >= $string):
                        return true;
                    else:
                        return false;
                    endif;
                else:
                    if($pattern <= strlen($string)):
                        return true;
                    else:
                        return false;
                    endif;
                endif;
            else:
                 return false;
            endif;
        break;
    endswitch;
    return preg_match($preg, $string);
}