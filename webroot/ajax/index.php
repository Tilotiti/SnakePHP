<?php
define('PAGE_LOADER', false);
require_once '../../config.php'; // Loading data
require_once LIB.'/init.php';    // Framework initiation

return json_encode(array('message' => "Hello World !"));
?>
