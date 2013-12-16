<?php

/* Framework configuration */
// Way to the root of your application
define('ROOT',      $_SERVER['DOCUMENT_ROOT']);

// Your application's name
define('SITE',     'SnakePHP');

// Your application's version
define('VERSION', '0.9');

// Your application's URL
define('URL',      'http://snakephp.dev');

// Your application's charset
define('CHARSET',  'utf-8');

// Your application's first year of development
define('YEAR',     2012);

// Is this a development version?
define('DEV',      true);

// Include queries in timer ?
define('QUERYTIMER',false);

// Timezone
define('TIMEZONE', 'Europe/Paris');

// Developement client IP, For several IPs, separate them with a pipe "|"
define('IPADMIN',  $_SERVER['REMOTE_ADDR']);


/* Database configuration */
// Host
define('DBHOST',  'localhost:3306');

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


/* Directories */
define('APP',        ROOT    .'/app');
define('WEBROOT',    ROOT    .'/webroot');
define('LIB',        ROOT    .'/lib');
define('LANG',       ROOT    .'/lang');
define('LOG',        ROOT    .'/log');
define('CACHE',      ROOT    .'/cache/');
define('SQLCACHE',   CACHE   .'/sql/');
define('PLUGIN',     LIB     .'/plugin');
define('SYSTEM',     LIB     .'/system');
define('SMARTY_DIR', SYSTEM  .'/class/smarty/');
define('FILE',       WEBROOT .'/file');
define('TEMPLATE',   APP     .'/template');
define('SOURCE',     APP     .'/source/');