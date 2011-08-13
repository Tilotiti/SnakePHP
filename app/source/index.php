<?php
$page->title('index');
$page->pushSidebar('test', 'left');
$page->pushSidebar('test', 'left');
$page->pushSidebar('test', 'right');
$page->pushSidebar('test', 'right');

$query = new query();
$query->Select()
      ->From('user')
      ->exec('ALL');

$query->debug();

?>