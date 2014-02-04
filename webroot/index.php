<?php
define('PAGE_LOADER', true);
require_once '../config.php'; // Chargement des configurations
require_once LIB.'/init.php'; // Initialisation du Framework

// Inclusion par défaut des plugins
$session = new session(); // Plugin session

// Inclusion des fichiers CSS par défaut
$page->pushCSS('snakephp/bootstrap');
$page->pushCSS('global');

// Inclusion des fichiers JS par défaut
$page->pushJS('jquery');
$page->pushJS('bootstrap');
$page->pushJS('script');

// Initalisation du premier dispatcher
debug::timer('Index dispatcher');
include $page->dispatcher("/");

// Assignation final au template
$page->template('page',    $page);
$page->template('save',    $_SESSION['save']);
$page->template('message', $_SESSION['message']);

// Génération du template
$page->display('template');

// Nettoyage des sessions et des variables
$page->clear();
exit();
?>