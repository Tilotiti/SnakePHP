<?php
// Connexion
if(DBHOST):
    if(DBSLAVE !== false): // Si un second serveur SQL est configuré
		ini_set('mysql.connect_timeout', 2); // Si le serveur ne répond pas dans les 2 secondes, on considère qu'il est en TimeOut
	endif;
    if(@mysql_connect(DBHOST, DBUSER, DBPASS)):
        if(@mysql_select_db(DBNAME)):
            mysql_query("SET NAMES UTF8");
        else:
            debug::error('sql', 'Unable to find the database. (master : '.DBHOST.')', __FILE__, __LINE__);
        endif;
    elseif(DBSLAVE !== false && @mysql_connect(DBSLAVE, DBUSER, DBPASS)):
    	if(@mysql_select_db(DBNAME)):
            mysql_query("SET NAMES UTF8");
        else:
            debug::error('sql', 'Unable to find the database. (slave : '.DBSLAVE.')', __FILE__, __LINE__);
        endif;
    else:
        debug::error('sql', 'Unable to connect the database server.', __FILE__, __LINE__);
    endif;
endif;

?>