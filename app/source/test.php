<?php
$query = new query();
$query->select()
      ->from('test')
      ->exec('FIRST');
?>