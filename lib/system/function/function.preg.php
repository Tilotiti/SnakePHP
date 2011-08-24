<?php
function preg($patern, $string = '') {
    switch($patern):
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
            if(is_int($patern)):
                if(is_int($string)):
                    if(empty($string) || $patern >= $string):
                        return true;
                    else:
                        return false;
                    endif;
                else:
                    if($patern <= strlen($string)):
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
?>