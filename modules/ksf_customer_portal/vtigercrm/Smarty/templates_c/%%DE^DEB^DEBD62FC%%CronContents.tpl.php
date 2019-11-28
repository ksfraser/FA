<?php /* Smarty version 2.6.18, created on 2015-03-18 23:38:08
         compiled from modules/CronTasks/CronContents.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'vtiger_imageurl', 'modules/CronTasks/CronContents.tpl', 36, false),)), $this); ?>
<table width="100%" cellpadding="5" cellspacing="0" class="listTable" >
	<tr>
	<td class="colHeader small" width="5%">#</td>
	<td class="colHeader small" width="20%">Cron Job</td>
	<td class="colHeader small" width="15%"><?php echo $this->_tpl_vars['MOD']['LBL_FREQUENCY']; ?>
<?php echo $this->_tpl_vars['MOD']['LBL_HOURMIN']; ?>
</td>
	<td class="colHeader small" width="10%"><?php echo $this->_tpl_vars['CMOD']['LBL_STATUS']; ?>
</td>
        <td class="colHeader small" width="20%"><?php echo $this->_tpl_vars['MOD']['LAST_START']; ?>
</td>
        <td class="colHeader small" width="15%"><?php echo $this->_tpl_vars['MOD']['LAST_END']; ?>
</td>
        <td class="colHeader small" width='10%'><?php echo $this->_tpl_vars['MOD']['LBL_SEQUENCE']; ?>
</td>
        <td class="colHeader small" width="5%"><?php echo $this->_tpl_vars['MOD']['LBL_TOOLS']; ?>
</td>
	</tr>
	<?php $_from = $this->_tpl_vars['CRON']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['cronlist'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['cronlist']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['elements']):
        $this->_foreach['cronlist']['iteration']++;
?>
	<tr>
	<td class="listTableRow small"><?php echo $this->_foreach['cronlist']['iteration']; ?>
</td>
	<td class="listTableRow small"><?php echo $this->_tpl_vars['elements']['cronname']; ?>
</td>
	<td class="listTableRow small"><?php echo $this->_tpl_vars['elements']['days']; ?>
 <?php echo $this->_tpl_vars['elements']['hours']; ?>
:<?php echo $this->_tpl_vars['elements']['mins']; ?>
</td>
	<?php if ($this->_tpl_vars['elements']['status'] == 'Active'): ?>
	<td class="listTableRow small active"><?php echo $this->_tpl_vars['elements']['status']; ?>
</td>
	<?php else: ?>
	<td class="listTableRow small inactive"><?php echo $this->_tpl_vars['elements']['status']; ?>
</td>
	<?php endif; ?>
        <td class="listTableRow small"><?php echo $this->_tpl_vars['elements']['laststart']; ?>
</td>
        <td class="listTableRow small"><?php echo $this->_tpl_vars['elements']['lastend']; ?>
</td>
	<?php if (($this->_foreach['cronlist']['iteration'] <= 1) != true): ?>
		<td  align="center" class="listTableRow"><a href="javascript:move_module('<?php echo $this->_tpl_vars['elements']['id']; ?>
','Up');" ><img src="<?php echo vtiger_imageurl('arrow_up.png', $this->_tpl_vars['THEME']); ?>
" style="width:16px;height:16px;" border="0" /></a>
	<?php endif; ?>
	<?php if (($this->_foreach['cronlist']['iteration'] == $this->_foreach['cronlist']['total']) == true): ?>
		<img src="<?php echo vtiger_imageurl('blank.gif', $this->_tpl_vars['THEME']); ?>
" style="width:16px;height:16px;" border="0" />
	<?php endif; ?>	
	<?php if (($this->_foreach['cronlist']['iteration'] <= 1) == true): ?>
		<td align="center" class="listTableRow"><img src="<?php echo vtiger_imageurl('blank.gif', $this->_tpl_vars['THEME']); ?>
" style="width:16px;height:16px;" border="0" />
			<a href="javascript:move_module('<?php echo $this->_tpl_vars['elements']['id']; ?>
','Down');" ><img src="<?php echo vtiger_imageurl('arrow_down.png', $this->_tpl_vars['THEME']); ?>
" style="width:16px;height:16px;" border="0" /></a></td>
	<?php endif; ?>
	
	<?php if (($this->_foreach['cronlist']['iteration'] == $this->_foreach['cronlist']['total']) != true && ($this->_foreach['cronlist']['iteration'] <= 1) != true): ?>
		<a href="javascript:move_module('<?php echo $this->_tpl_vars['elements']['id']; ?>
','Down');" ><img src="<?php echo vtiger_imageurl('arrow_down.png', $this->_tpl_vars['THEME']); ?>
" style="width:16px;height:16px;" border="0" /></a></td>
	<?php endif; ?>
        <td class="listTableRow small" align="center" ><img onClick="fnvshobj(this,'editdiv');fetchEditCron('<?php echo $this->_tpl_vars['elements']['id']; ?>
');" style="cursor:pointer;" src="<?php echo vtiger_imageurl('editfield.gif', $this->_tpl_vars['THEME']); ?>
" title="<?php echo $this->_tpl_vars['APP']['LBL_EDIT']; ?>
"></td>
        </tr>
	<?php endforeach; endif; unset($_from); ?>
	</table>
