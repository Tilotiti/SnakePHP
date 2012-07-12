<?php
debug::timer('Index start');

$page->title('index');
debug::timer('Inititation de la sidebar Index');
$page->pushSidebar('menu');

debug::timer('Début de la requête');

$query = new query();
$query->select()
      ->from('test')
      ->exec();
      
debug::timer('Fin de la requête');

debug::dump($query, 'index');
debug::timer('Dump');
debug::dump($start, 'test');
debug::timer('Index end');
?>