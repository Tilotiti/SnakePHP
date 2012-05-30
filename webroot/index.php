<?php
define('PAGE_LOADER', true);
require_once '../config.php'; // Chargement des configurations
require_once LIB.'/init.php'; // Initialisation du Framework

// Inclusion des fichiers CSS par défaut
$page->pushCSS('bootstrap');
$page->pushCSS('global');

// Inclusion des fichiers JS par défaut
$page->pushJS('bootstrap');
$page->pushJS('script');

// Initalisation du premier dispatcher
include $page->dispatcher("/");

// Assignation final au template
$template->assign('page',    $page);
$template->assign('save',    $_SESSION['save']);
$template->assign('message', $_SESSION['message']);
$template->assign('debug',   $debug);

// Génération du template
$template->display("template.tpl");

// Nettoyage des sessions et des variables
$page->clear();
exit();
?>