<?php /* Smarty version 2.6.19, created on 2011-05-12 14:33:22
         compiled from exportNew.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'strtoupper', 'exportNew.tpl', 5, false),array('modifier', 'strtolower', 'exportNew.tpl', 5, false),array('modifier', 'capitalize', 'exportNew.tpl', 5, false),)), $this); ?>
<p>Un nouvel export <b><?php echo $this->_tpl_vars['mail']['title']; ?>
</b> vient d'être créé sur <a href='http://www.radarvo.com'>RadarVO</a>.</p>
<p>Les sites suivants ont été ajoutés :</p>
<ul>
    <?php $_from = $this->_tpl_vars['mail']['site']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['site']):
?>
        <li><?php echo strtoupper($this->_tpl_vars['site']['code']); ?>
 - <?php echo ((is_array($_tmp=strtolower($this->_tpl_vars['site']['title']))) ? $this->_run_mod_handler('capitalize', true, $_tmp) : smarty_modifier_capitalize($_tmp)); ?>
 (<?php echo $this->_tpl_vars['site']['flux']; ?>
)</li>
    <?php endforeach; endif; unset($_from); ?>
</ul>
<?php if ($this->_tpl_vars['mail']['type'] == 'put'): ?>
    <p>Les fichiers seront déposés chaque nuit à 5h00 (heure française).</p>
<?php else: ?>
    <p>Les fichiers seront mis à jour chaque nuit à 5h00 (heure française).</p>
    <p>Vos informations de connexion sont :</p>
    <ul>
        <li><b>Serveur FTP :</b> ftp.radarvo.com</li>
        <li><b>Nom d'utilisateur</b> : <?php echo $this->_tpl_vars['mail']['username']; ?>
@radarvo.com</li>
        <li><b>Mot de passe</b> : <?php echo $this->_tpl_vars['mail']['password']; ?>
</li>
    </ul>
<?php endif; ?>
<p>Le premier export sera mis à jour dès cette nuit.</p>

<p>Cordialement,</p>
<p>L'équipe RadarVO.</p>