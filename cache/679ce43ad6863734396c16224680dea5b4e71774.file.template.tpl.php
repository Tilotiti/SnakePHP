<?php /* Smarty version Smarty-3.0.8, created on 2011-07-22 21:44:29
         compiled from "C:/Program Files/wamp/www/edenphp/app/template/template.tpl" */ ?>
<?php /*%%SmartyHeaderCode:149524e29ef3debafb1-13618244%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '679ce43ad6863734396c16224680dea5b4e71774' => 
    array (
      0 => 'C:/Program Files/wamp/www/edenphp/app/template/template.tpl',
      1 => 1311371068,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '149524e29ef3debafb1-13618244',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!doctype html>
<html lang="fr">
<head>
    <?php $_template = new Smarty_Internal_Template("tools/meta.tpl", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate(); $_template->rendered_content = null;?><?php unset($_template);?>
</head>
<body>
    <header>
        <?php $_template = new Smarty_Internal_Template("tools/header.tpl", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate(); $_template->rendered_content = null;?><?php unset($_template);?>
        <nav id="menu">
            <?php $_template = new Smarty_Internal_Template("tools/menu.tpl", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate(); $_template->rendered_content = null;?><?php unset($_template);?>
        </nav>
        <div class="clear"></div>
    </header>
    <nav id="ariane">
        <?php echo $_smarty_tpl->getVariable('page')->value->ariane();?>

    </nav>
    <aside id="sidebarLeft">
        <?php  $_smarty_tpl->tpl_vars['sidebar'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('page')->value->getSidebar('left'); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['sidebar']->key => $_smarty_tpl->tpl_vars['sidebar']->value){
?>
            <div class="sidebox">
                <?php $_template = new Smarty_Internal_Template("sidebar/".($_smarty_tpl->tpl_vars['sidebar']->value).".tpl", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate(); $_template->rendered_content = null;?><?php unset($_template);?>
            </div>
        <?php }} ?>
    </aside>
    <section>
	<?php if ($_smarty_tpl->getVariable('message')->value){?>
            <h4 class="alert_<?php echo $_smarty_tpl->getVariable('message')->value['type'];?>
"><?php echo $_smarty_tpl->getVariable('message')->value['text'];?>
</h4>
        <?php }?>
        <h1><?php echo $_smarty_tpl->getVariable('page')->value->get('title');?>
</h1>
        <article>
            <?php $_template = new Smarty_Internal_Template(($_smarty_tpl->getVariable('page')->value->get('template')).".tpl", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate(); $_template->rendered_content = null;?><?php unset($_template);?>
        </article>
        <?php echo $_smarty_tpl->getVariable('debug')->value->clear();?>

    </section>
    <aside id="sidebarRight">
        <?php  $_smarty_tpl->tpl_vars['sidebar'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('page')->value->getSidebar('right'); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['sidebar']->key => $_smarty_tpl->tpl_vars['sidebar']->value){
?>
            <div class="sidebox">
                <?php $_template = new Smarty_Internal_Template("sidebar/".($_smarty_tpl->tpl_vars['sidebar']->value).".tpl", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate(); $_template->rendered_content = null;?><?php unset($_template);?>
            </div>
        <?php }} ?>
    </aside>
    <footer><?php echo $_smarty_tpl->getVariable('page')->value->copyright();?>
</footer>
</body>
</html>