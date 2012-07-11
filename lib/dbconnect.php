<?php
/*
 * Connexion à la base de donnée (PDO)
 */
 
if(DBHOST):
	// Gestion des options d'instanciation de PDO
	$options = array(
    	PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8",
    	PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
    );
  
	try {
        $queryConnexion = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME, DBUSER, DBPASS, $options);
    }
 
    catch(Exception $error) {
        debug::error("sql", $error->getMessage(), LIB."/dbconnect.php");
    }
endif;

?>