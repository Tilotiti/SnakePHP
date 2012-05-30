<?php
// Connexion
if(DBHOST):
    if(mysql_connect(DBHOST, DBUSER, DBPASS)):
        if(!mysql_select_db(DBNAME)):
            debug::error('sql', 'Unable to find the database.');
        endif;
    else:
        debug::error('sql', 'Unable to connect the database server.');
    endif;
endif;

?>