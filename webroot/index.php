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
$template->assign('page',    $page);
$template->assign('nb',      $nb);
$template->assign('user',    $user);
$template->assign('save',    $_SESSION['save']);
$template->assign('message', $_SESSION['message']);
$template->assign('get',     get());
$template->assign('debug',   $debug);
$template->assign('sql',     page::$sql);

$template->display("template.tpl");

$page->clear();
exit();
?>