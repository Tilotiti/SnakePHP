<?php
$page->title('index');
$page->pushSidebar('test', 'left');
$page->pushSidebar('test', 'left');
$page->pushSidebar('test', 'right');
$page->pushSidebar('test', 'right');

$query = new query();
$query->select(array('DISTANCE' => array(
		  	"lat",
		  	"long",
		  	 48.882578,
		  	 2.2893605
		  ),'AS' => "distance"),
		  "username"
	)
	->from('user')
	->orderBy(array('ALLIAS' => 'distance'), "ASC")
	->exec('ALL');
	
$query->debug();
debug::display($query->getArray(), "Résultats de la requête");
?>