<?php
/**
 * Smarty {icone} function plugin
 *
 * Type:     function<br>
 * Name:     Icone<br>
 * Purpose:  Insere un icone automatiquement<br>
 * @link http://wwww.tilowebcom
 * @author Tilotiti <contact at tiloweb dot com>
 * @param array
 * @param Smarty
 */
function smarty_function_icone($params, &$smarty) {
        return icone($params['src'], $params['class'], $params['alt'], $params['id'], $params['onclick']);
}

?>
