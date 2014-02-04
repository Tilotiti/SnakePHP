<?php
define('PAGE_LOADER', false);
require_once '../config.php'; // Loading data
require_once LIB.'/init.php';    // Framework initiation

$ajax = new ajax();

include $ajax->dispatcher('/');