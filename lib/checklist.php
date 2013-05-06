<?php
/*
 * Test if snakePHP is complete and can work normally
 */
 
$error = false;

// /cache/ is writable
$error = !is_writable(CACHE) ? "The folder <code>".CACHE."</code> must be writable. Please, make a <code>CHMOD 777</code> on it." : $error;

// /lang/ is writable
$error = !is_writable(LANG) ? "The folder <code>".LANG."</code> must be writable. Please, make a <code>CHMOD 777</code> on it." : $error;

// /webroot/file/ is writable
$error = !is_writable(FILE) ? "The folder <code>".FILE."</code> must be writable. Please, make a <code>CHMOD 777</code> on it." : $error;

// /log/ is writable
$error = !is_writable(LOG) ? "The folder <code>".LOG."</code> must be writable. Please, make a <code>CHMOD 777</code> on it." : $error;

// /lib/ is NOT writable
$error = is_writable(LIB) ? "The folder <code>".LIB."</code> must be writable. Please, make a <code>CHMOD 644</code> on it." : $error;

// /app/ is NOT writable
$error = is_writable(APP) ? "The folder <code>".APP."</code> must be writable. Please, make a <code>CHMOD 644</code> on it." : $error;

// Trigger the fatal Error
if($error != false):
	fatalError($error);
endif;