<?php
require_once LIB.'/dbconnect.php';
header('Content-Type: text/html; charset='.CHARSET);

// Implantation de toutes les fonctions système
$dir = opendir(SYSTEM.'/function');
while ($file = readdir($dir)):
    if(preg_match('#^function.([\w]+).php$#', $file)):
        require_once SYSTEM.'/function/'.$file;
    endif;
endwhile;
closedir($dir);

session_start();

function autoload($class) {
    if(file_exists(SYSTEM.'/class/class.'.$class.'.php')):
        require_once SYSTEM.'/class/class.'.$class.'.php';
    endif;
}

spl_autoload_register("autoload");

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

            new lang('fr');
$debug    = new debug();
$compte   = array();
$nb       = array();
$selected = array();

// Définition des Sessions
if(!isset($_SESSION['message'])):
    $_SESSION['message'] = false;
endif;

if(!isset($_SESSION['save'])):
    $_SESSION['save'] = false;
endif;

if(PAGE_LOADER):
    
    $page     = new page();
    $user     = new user();
                new debug();
    
    // initiation de smarty
    $smarty   = new smarty();
    $smarty->template_dir = TEMPLATE;
    $smarty->compile_dir  = CACHE;
    $smarty->assign('pagination', array());

    if(!isset($_SESSION['error'])):
        $_SESSION['error'] = false;
    endif;

endif;

?>