<?php
/*
 * Plugin : User
 * Description : Gestion d'une base de donnée utilisateur et de leurs actions sur le site
 * Auteur : Thibault HENRY
 * Site : http://www.tiloweb.com
 */

// Temps maximum d'une session avant rafraichissement des données automatique depuis la base de donnée
define('SESSION_REFRESH', 1200); 

// Insertion des classes User et Session
require_once 'class.user.php';    // Gestion des utilisateurs
require_once 'class.session.php'; // Gestion des sessions utilisateurs

// Création de la session utilisateur par défault
if(!isset($_SESSION['user'])):
	$_SESSION['user'] = false;
endif;

// Instanciation de la session utilisateur 
$session  = new session();

if(PAGE_LOADER):
	// Assignation de la session dans le template
	$page->template('session', $session);
endif;
?>