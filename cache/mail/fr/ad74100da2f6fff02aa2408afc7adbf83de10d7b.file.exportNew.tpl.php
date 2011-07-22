<?php /* Smarty version Smarty-3.0.8, created on 2011-07-13 10:27:02
         compiled from "/home/admin/domains/radarvo.com/public_html/lang/fr/mail/exportNew.tpl" */ ?>
<?php /*%%SmartyHeaderCode:15782862554e1d56d674fa80-21997277%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ad74100da2f6fff02aa2408afc7adbf83de10d7b' => 
    array (
      0 => '/home/admin/domains/radarvo.com/public_html/lang/fr/mail/exportNew.tpl',
      1 => 1310482355,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '15782862554e1d56d674fa80-21997277',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_modifier_capitalize')) include '/home/admin/domains/radarvo.com/public_html/lib/system/class/smarty/plugins/modifier.capitalize.php';
?><p>Un nouvel export <b><?php echo $_smarty_tpl->getVariable('mail')->value['title'];?>
</b> vient d'être créé sur <a href='http://www.radarvo.com'>RadarVO</a>.</p>
<p>Les sites suivants ont été ajoutés :</p>
<ul>
    <?php  $_smarty_tpl->tpl_vars["site"] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('mail')->value['site']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars["site"]->key => $_smarty_tpl->tpl_vars["site"]->value){
?>
        <li><?php echo strtoupper($_smarty_tpl->getVariable('site')->value['code']);?>
 - <?php echo smarty_modifier_capitalize(strtolower($_smarty_tpl->getVariable('site')->value['title']));?>
 (<?php echo $_smarty_tpl->getVariable('site')->value['flux'];?>
)</li>
    <?php }} ?>
</ul>
<?php if ($_smarty_tpl->getVariable('mail')->value['type']=="put"){?>
    <p>Les fichiers seront déposés chaque nuit à 5h00 (heure française).</p>
<?php }else{ ?>
    <p>Les fichiers seront mis à jour chaque nuit à 5h00 (heure française).</p>
    <p>Vos informations de connexion sont :</p>
    <ul>
        <li><b>Serveur FTP :</b> ftp.radarvo.com</li>
        <li><b>Nom d'utilisateur</b> : <?php echo $_smarty_tpl->getVariable('mail')->value['username'];?>
@radarvo.com</li>
        <li><b>Mot de passe</b> : <?php echo $_smarty_tpl->getVariable('mail')->value['password'];?>
</li>
    </ul>
<?php }?>
<p>Le premier export sera mis à jour dès cette nuit.</p>

<p>Cordialement,</p>
<p>L'équipe RadarVO.</p>