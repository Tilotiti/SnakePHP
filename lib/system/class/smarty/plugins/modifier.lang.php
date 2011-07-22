<?php
/**
 * Smarty date_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     lang<br>
 * Purpose:  Traduit le code renseigné<br>
 * Input:<br>
 *         - code: Code de traduction
 *         - type: Type de variable
 *         - pref: Préfixe avant le code
 * @link http://www.tiloweb.com
 * @author   Tilotiti <contact at tiloweb dot com>
 * @param string
 * @return string|void
 */
function smarty_modifier_lang($code, $type = "text", $pref = false) {
    
    if($pref):
        return lang::find($type, $pref.":".$code);
    else:
        return lang::find($type, $code);
    endif;
}


?>
