<?php
/*
 * Plugin : CSRF
 * Description : Protects website from "Cross-site request forgery" attacks
 * Autor : Pierre de Vésian
 * Site : https://twitter.com/pvesian
*/

// Load the CSRF class
require_once 'class.csrf.php';

/*
 * FYI: CSRF-token are built by two parameters: their NAME and their VALUE
 */