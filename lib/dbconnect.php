<?php

// Connexion
if(DBHOST):
    if(@mysql_connect(DBHOST, DBUSER, DBPASS)):
        if(@mysql_select_db(DBNAME)):
            mysql_query("SET NAMES UTF8");
        else:
            debug::error('sql', 'Unable to find the database.', __FILE__, __LINE__);
        endif;
    else:
        var_dump(debug::error('sql', 'Unable to connect the database server.', __FILE__, __LINE__));
    endif;
endif;

?>