<?php
/**
 * Autoload function
 * Whenever an unknown class/function is invoked, this function is called to include correct file(s)
 * Outputs an error if resource not found
 * 
 * @param String $class class or function name to invoke
 * @return void
 */
function autoload($class) {
    if ($class == "smarty" || !preg_match("#smarty#i", $class)):
	    if (file_exists(PLUGIN.'/'.$class.'/plugin.'.$class.'.php')):
	    	require_once PLUGIN.'/'.$class.'/plugin.'.$class.'.php';
		elseif (file_exists(PLUGIN.'/'.strtolower($class).'/plugin.'.strtolower($class).'.php')):
	    	require_once PLUGIN.'/'.strtolower($class).'/plugin.'.strtolower($class).'.php';
	    elseif(file_exists(SYSTEM.'/class/class.'.$class.'.php')):
	        require_once SYSTEM.'/class/class.'.$class.'.php';
		elseif (preg_match('#^swift_#i', $class)):
			require_once SYSTEM.'/class/swift/swift_required.php';
	    else:
	    	debug::error("Plugin autoload", "Plugin could not be loaded", PLUGIN.'/'.$class.'/plugin.'.$class.'.php');
	    endif;
	endif;
}