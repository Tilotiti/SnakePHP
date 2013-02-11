<?php

/* Configuration du Framework */
define('ROOT',      $_SERVER['DOCUMENT_ROOT']); // Chemin de la racine de votre site
define('SITE',     'SnakePHP');                  // Nom du site
define('URL',      'http://snakephp.dev');      // URL du site
define('CHARSET',  'utf-8');                    // Encodage du site
define('YEAR',     2012);                       // Début du développement
define('DEV',      true);                       // Mode développement
define('TIMEZONE', 'Europe/Paris');             // Fuseau Horaire
define('IPADMIN',  $_SERVER['REMOTE_ADDR']);    // IP du poste de développement, pour plusieurs posts, séparer les IPs par un pipe "|"

/* Configuration de la base de données */
define('DBHOST',  'localhost:3306'); // Hôte
define('DBUSER',  'root'); // Utilisateur
define('DBPASS',  ''); // Mot de passe
define('DBNAME',  'test'); // Base de données
define('DBPREF',  'snake_'); // Préfixe des tables

/* Chemins des dossiers */
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
