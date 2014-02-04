<?php

/* Framework configuration */
// Way to the root of your application
define('ROOT',      $_SERVER['DOCUMENT_ROOT']);

// Your application's name
define('SITE',     'SnakePHP');

// Your application's URL
define('URL',      'http://snakephp.dev');

// Your application's charset
define('CHARSET',  'utf-8');

// Is this a development version?
define('DEV',      true);

// Timezone
define('TIMEZONE', 'Europe/Paris');

// Developement client IP, For several IPs, separate them with a pipe "|"
define('IPADMIN',  $_SERVER['REMOTE_ADDR']);


/* Database configuration */
// Host
define('DBHOST',  false);

// User
define('DBUSER',  'root');

// Password
define('DBPASS',  '');

// Database
define('DBNAME',  'test');

// Table's prefixes
define('DBPREF',  'snake_');

// SQL-cache lifetime, default: 20min
define('SQLCACHETIME', 1200);

// Include queries in timer ?
define('QUERYTIMER',false);

/* Directories */
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