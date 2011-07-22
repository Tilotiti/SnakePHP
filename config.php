<?php

// Framework config
define('ROOT',      'C:/Program Files/wamp/www/edenphp');             // Root
define('SITE',      'EdenPHP');                                       // Website Title
define('URL',       'http://dev.edenphp.net/');                       // Website URL
define('CHARSET',   'utf-8');                                         // Website charset
define('DBHOST',    'localhost');                                     // DB host
define('DBUSER',    '');                                              // DB user
define('DBPASS',    '');                                              // DB password
define('DBNAME',    'edenphp');                                       // DB name
define('DBPREF',    'eden_');                                         // DB prefix
define('YEAR',      2010);                                            // Start year

// Please, do not change anything
define('LIB',        ROOT    .'/lib');
define('WEBROOT',    ROOT    .'/webroot');
define('APP',        ROOT    .'/app');
define('LANG',       ROOT    .'/lang');
define('LOG',        ROOT    .'/log');
define('SMARTY_DIR', LIB     .'/system/class/smarty/');
define('TEMPLATE',   APP     .'/template/');
define('SOURCE',     APP     .'/source/');
define('CACHE',      ROOT    .'/cache/');
define('SYSTEM',     LIB     .'/system');
define('PLUGIN',     LIB     .'/plugin');
define('FILE',       WEBROOT .'/file');

?>
