<?php
function smarty_modifier_capitalize($string, $uc_digits = false) {
    return ucwords(strtolower($string));
}
