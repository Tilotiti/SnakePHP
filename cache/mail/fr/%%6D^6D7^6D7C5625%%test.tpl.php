<?php /* Smarty version 2.6.19, created on 2011-05-12 11:56:48
         compiled from test.tpl */ ?>

Login : <?php echo $this->_tpl_vars['mail']['user']; ?>

Foreach : <br />
<?php $_from = $this->_tpl_vars['mail']['test']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['test']):
?>
    <?php echo $this->_tpl_vars['test']; ?>
<br />
<?php endforeach; endif; unset($_from); ?>