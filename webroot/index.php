<?php
define('PAGE_LOADER', true);
require_once '../config.php';  // Loading data
require_once LIB.'/init.php'; // Framework initiation

$page->pushCSS('script');
$page->pushCSS('global');
$page->pushJS('jquery');
$page->pushJS('plugin');
$page->pushJS('script');

if(ADMIN):
    if($user->connect() && !$user->allow('admin')):
        message::warning('admin:rank', '/index/');
    elseif(get(2) != 'login'):
        message::warning('admin:login', '/admin/login/');
    endif;
endif;

include $page->dispatcher("/");

// Assign final
$smarty->assign('page',    $page);
$smarty->assign('nb',      $nb);
$smarty->assign('user',    $user);
$smarty->assign('save',    $_SESSION['save']);
$smarty->assign('message', $_SESSION['message']);
$smarty->assign('get',     get());
$smarty->assign('debug',   $debug);
$smarty->assign('sql',     page::$sql);

$smarty->display("template.tpl");

$page->clear();
exit();
?>