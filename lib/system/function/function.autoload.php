<?php

function autoload($class) {
    if ($class == "smarty" || !preg_match("#smarty#i", $class)):
	    if (file_exists(PLUGIN.'/'.$class.'/plugin.'.$class.'.php')):
	    	require_once PLUGIN.'/'.$class.'/plugin.'.$class.'.php';
	    elseif(file_exists(SYSTEM.'/class/class.'.$class.'.php')):
	        require_once SYSTEM.'/class/class.'.$class.'.php';
	    else:
	    	debug::error("Plugin autoload", "Plugin could not be loaded", PLUGIN.'/'.$class.'/plugin.'.$class.'.php');
	    endif;
	endif;
}