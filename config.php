<?php

// Framework config

/* Chemin de la racine de votre site ($_SERVER['DOCUMENT_ROOT']) */
define('ROOT','C:/Program Files/wamp/www/edenphp');
/* Nom du site (titre) */
define('SITE','EdenPHP');
/* URL du site */
define('URL','http://dev.edenphp.net/');
/* charset */
define('CHARSET','utf-8');

/* Configuration de la base de donnée */
/* Hôte */                define('DBHOST','localhost');
/* Utilisateur */         define('DBUSER','root');
/* Mot de passe */        define('DBPASS','');
/* Nom de la base */      define('DBNAME','edenphp');
/* Prefixe des tables */  define('DBPREF','eden_');

/* Année de développement */
define('YEAR',      2011);

/* Mode développeur : */
define('DEV',       false);
/*
    DEV = true
      - Les erreurs sont affichés
    
    DEV = false
      - Les erreurs sont cachés
*/

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