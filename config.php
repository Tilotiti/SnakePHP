<?php

// Framework config
define('ROOT',      'C:/Program Files/wamp/www/edenphp');             // Root
define('SITE',      'EdenPHP');                                       // Website Title
define('URL',       'http://dev.edenphp.net/');                       // Website URL
define('CHARSET',   'utf-8');                                         // Website charset
define('DBHOST',    'localhost');                                     // DB host
define('DBUSER',    'root');                                          // DB user
define('DBPASS',    '');                                              // DB password
define('DBNAME',    'edenphp');                                       // DB name
define('DBPREF',    'eden_');                                         // DB prefix
define('YEAR',      2011);                                            // Start year
define('DEV',       false);                                           // Debug

// Path global
define('APP',        ROOT    .'/app');
define('WEBROOT',    ROOT    .'/webroot');
define('LIB',        ROOT    .'/lib');
define('LANG',       ROOT    .'/lang');
define('LOG',        ROOT    .'/log');
define('CACHE',      ROOT    .'/cache/');
define('PLUGIN',     LIB     .'/plugin');
define('SYSTEM',     LIB     .'/system');
define('SMARTY_DIR', SYSTEM  .'/class/smarty/');
define('FILE',       WEBROOT .'/file');
define('TEMPLATE',   APP     .'/template');
define('SOURCE',     APP     .'/source/');

?>