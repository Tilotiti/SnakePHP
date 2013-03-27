<?php
/*
 * Plugin : User
 * Description : Gestion d'une base de donnée utilisateur et de leurs actions sur le site
 * Auteur : Thibault HENRY
 * Site : http://www.tiloweb.com
 */

// Détection de la dépendance au plugin user
if(file_exists(PLUGIN.'/user/plugin.user.php')):

    // Temps maximum d'une session avant rafraichissement des données automatique depuis la base de donnée
    define('SESSION_REFRESH', 1200); 
    
    // Insertion des classes User et Session
    require_once 'class.session.php'; // Gestion des sessions utilisateurs
    
    // Création de la session utilisateur par défault
    if(!isset($_SESSION['user'])):
    	$_SESSION['user'] = false;
    endif;
    
else:
    class session {}
    debug::error('Plugin Session', 'The user plugin must be activated', __FILE__, __LINE__);
endif;
?>