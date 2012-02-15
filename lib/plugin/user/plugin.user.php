<?php
/*
 * Plugin : User
 * Description : Gestion d'une base de donnée utilisateur et de leurs actions sur le site
 * Auteur : Tilotiti
 * Site : http://www.tiloweb.com
 */

 require_once 'class.user.php';    // Gestion des utilisateurs
 require_once 'class.session.php'; // Gestion des sessions utilisateurs

define('SESSION_REFRESH', 10);  // Temps maximum d'une session avant rafraichissement des données automatique depuis la base de donnée

// Création de la session utilisateur par défault
if(!isset($_SESSION['user'])):
	$_SESSION['user'] = false;
endif;

// Instanciation de la session utilisateur 
$session  = new session();

// Assignation de la session dans le template
$template->assign('session', $session);

?>