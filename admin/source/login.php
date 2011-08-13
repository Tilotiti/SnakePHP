<?php
$page->title('login');

if(isset($_POST['login'])):
    $user->login($_POST['login']['username'], $_POST['login']['password']);
endif;
?>
