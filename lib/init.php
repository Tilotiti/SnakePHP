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

// Implantation de tous les plugins
$dir = opendir(PLUGIN.'/');
while ($file = readdir($dir)):
    if ($file !="." && $file != ".."):
        if(preg_match('#^function.([\w]+).php$#', $file, $match)): // Simple fonction
            if(!function_exists($match[1])):
                require_once PLUGIN.'/'.$file;
            endif;
        elseif(file_exists(PLUGIN.'/'.$file.'/plugin.'.$file.'.php')):
            require_once PLUGIN.'/'.$file.'/plugin.'.$file.'.php';
        endif;
    endif;
endwhile;
closedir($dir);

// Instanciation des classes nécessaires au framework
            new lang('fr');
$debug    = new debug();

// Définition des Sessions
if(!isset($_SESSION['message'])):
    $_SESSION['message'] = false;
endif;

if(!isset($_SESSION['save'])):
    $_SESSION['save'] = false;
endif;

// Initialisation du template
if(PAGE_LOADER):
    
    $page     = new page();
    $user     = new user();
                
    // initiation de smarty
    $template               = new smarty();
    $template->template_dir = TEMPLATE;
    $template->compile_dir  = CACHE;

    if(!isset($_SESSION['error'])):
        $_SESSION['error'] = false;
    endif;

endif;

?>