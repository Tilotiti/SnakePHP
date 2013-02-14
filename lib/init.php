<?php
header('Content-Type: text/html; charset='.CHARSET);

// Chargement du fuseau horaire par défaut
if(function_exists('date_default_timezone_set')):
    date_default_timezone_set(TIMEZONE);
endif;

// Implantation de toutes les fonctions système
$dir = opendir(SYSTEM.'/function');
while ($file = readdir($dir)):
    if(preg_match('#^function.([\w]+).php$#', $file)):
        require_once SYSTEM.'/function/'.$file;
    endif;
endwhile;
closedir($dir);

spl_autoload_register("autoload");

// Connexion à la BDD
require_once LIB.'/dbconnect.php';

// Démarrage de la session
session_start();

// Instanciation des classes nécessaires au framework
new lang('fr');
new debug();

// Initialisation de la page
if(PAGE_LOADER):
    $page = new page();
    
    // Initialisation du référencement naturel
	$page->description(lang::text('meta:description')); // Description du site par défaut
	$page->pushKeyword(lang::text('meta:keywords')); // Mots clefs par défaut
endif;



// Implantation de tous les plugins
debug::timer('timer:plugin', true);
$dir = opendir(PLUGIN.'/');
while ($file = readdir($dir)):
    if ($file !="." && $file != ".."):
        if(preg_match('#^function.([\w]+).php$#', $file, $match)): // Simple fonction
            if(!function_exists($match[1])):
                require_once PLUGIN.'/'.$file;
            endif;
        endif;
    endif;
endwhile;
closedir($dir);
unset($dir);

?>