<?php

// Connexion
if(!empty(DBHOST)):
    mysql_connect(DBHOST, DBUSER, DBPASS);
    mysql_select_db(DBNAME);
    mysql_query("SET NAMES UTF8");
endif;

?>