<?php /* Smarty version Smarty-3.0.8, created on 2011-07-22 21:31:19
         compiled from "C:/Program Files/wamp/www/edenphp/app/template/tools/meta.tpl" */ ?>
<?php /*%%SmartyHeaderCode:313914e29ec276c9463-35501985%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '5ceb4e29ee5c519f58a800f1ad1f5fbd7ecf7d87' => 
    array (
      0 => 'C:/Program Files/wamp/www/edenphp/app/template/tools/meta.tpl',
      1 => 1311352410,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '313914e29ec276c9463-35501985',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<meta charset="<?php echo @CHARSET;?>
"/>
<title><?php echo @SITE;?>
 - <?php echo $_smarty_tpl->getVariable('page')->value->title;?>
</title>

<?php  $_smarty_tpl->tpl_vars['css'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('page')->value->getCSS(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['css']->key => $_smarty_tpl->tpl_vars['css']->value){
?>
<link rel="stylesheet" media="screen" type="text/css" href="/css/<?php echo $_smarty_tpl->tpl_vars['css']->value;?>
.css" />
<?php }} ?>
<!--[if lt IE 9]>
<link rel="stylesheet" href="/css/ie.css" type="text/css" media="screen" />
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<?php  $_smarty_tpl->tpl_vars['js'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('page')->value->getJS(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['js']->key => $_smarty_tpl->tpl_vars['js']->value){
?>
<script type="text/javascript" src="/js/<?php echo $_smarty_tpl->tpl_vars['js']->value;?>
.js"></script>
<?php }} ?>