<?php
/*
 * Plugin : Bruteforce
 * Description : Protects user from bruteforce attacks
 * Autor : Pierre de Vésian
 * Site : https://twitter.com/pvesian
*/

// Maximum bruteforce attacks
define('BRUTEFORCEMAX', 10); 

// Load the bruteforce class
require_once 'class.bruteforce.php';

/*
* Troubleshooting : if you are blocked by the counter beacuse of too many attempts,
*					just close your browser and/or delete the cookies
*/